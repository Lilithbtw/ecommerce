<?php

namespace App\Controllers;

use App\DB;
use App\Services\RedsysService;
use Exception;

/**
 * Handles the Redsys payment gateway integration.
 */
class PaymentController
{
    private DB $db;
    private RedsysService $redsysService;

    public function __construct(DB $db, RedsysService $redsysService)
    {
        $this->db = $db;
        $this->redsysService = $redsysService;
    }

    /**
     * Initializes the Redsys payment form redirection.
     * This is called immediately after a new order is created in the database.
     * The cart total is fetched dynamically from the database using the order ID.
     * * @param array $vars Contains the 'id' (order_id) from the route.
     * @return void
     */
    public function initPayment(array $vars): void
    {
        // El FastRoute lo llama 'id', pero representa el orderId
        $orderId = $vars['id'] ?? null; 
        if (!$orderId) {
            header("Location: /cart");
            exit;
        }

        try {
            // 1. Fetch order details (total amount, etc.) - THIS ENSURES THE DYNAMIC CART TOTAL IS USED
            $stmt = $this->db->pdo()->prepare('SELECT total, client_email FROM orders WHERE id = ?');
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();

            if (!$order) {
                // Log and redirect if the order does not exist in the database
                error_log("Attempted payment for non-existent Order ID: " . htmlspecialchars($orderId));
                $_SESSION['error_message'] = "El pedido no fue encontrado.";
                header("Location: /cart");
                exit;
            }
            
            $amountInCents = (int) round($order['total'], 100, 0); // más preciso con números grandes
            $clientEmail = $order['client_email'];

            // 2. Define Redsys specific URLs. Must be absolute URLs.
            // Ensure $_SERVER['HTTP_HOST'] is safe and correctly configured in your environment (e.g., Nginx)
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $baseUrl = $protocol . $_SERVER['HTTP_HOST'];
            
            $responseUrl = $baseUrl . '/payment/redsys/response';
            $notificationUrl = $baseUrl . '/payment/redsys/notification'; // URL de notificación asíncrona

            // 3. Prepare parameters using the Redsys Service
            $paymentData = $this->redsysService->preparePayment(
                $amountInCents, 
                $orderId, 
                $responseUrl, // URL de retorno del navegador (síncrona)
                $notificationUrl // URL de notificación (servidor a servidor)
            );
            
            // 4. Load the auto-submitting form view (will contain the form action, parameters, and signature)
            extract($paymentData);
            
            $orderContext = [
                'orderId' => $orderId,
                'amount' => $amountInCents,
                'clientEmail' => $clientEmail
            ];

            require __DIR__ . '/../../views/redsys_form.php';
        
        } catch (Exception $e) {
            error_log("Redsys Init Error: " . $e->getMessage());
            $_SESSION['error_message'] = "Error al iniciar el pago. Inténtelo de nuevo.";
            header("Location: /cart");
            exit;
        }
    }

    /**
     * Handles the synchronous response POST from Redsys (browser return).
     *
     * @return void
     */
    public function processResponse(): void
    {
        // Delegar la lógica de procesamiento al método privado. No es una notificación.
        $this->handlePaymentProcessing(false);
    }

    /**
     * Handles the asynchronous notification POST from Redsys (server-to-server).
     *
     * @return void
     */
    public function processNotification(): void
    {
        // Delegar la lógica de procesamiento al método privado. Es una notificación.
        $this->handlePaymentProcessing(true);
    }
    
    /**
     * Common logic to handle Redsys POST data for both synchronous and asynchronous calls.
     *
     * @param bool $isNotification True if this is the server-to-server notification call.
     * @return void
     */
    private function handlePaymentProcessing(bool $isNotification): void
    {
        try {
            // Redsys envía los datos en el cuerpo como URL-safe Base64
            $paramsBase64 = $_POST['Ds_MerchantParameters'] ?? '';
            $signatureReceived = $_POST['Ds_Signature'] ?? '';
            
            if (empty($paramsBase64) || empty($signatureReceived)) {
                if ($isNotification) {
                    // Redsys requiere un 200 OK en la notificación aunque falten parámetros
                    // Aunque lo ideal es un 500 para forzar el reintento si los parámetros no están
                    error_log("Redsys Notification Error: Missing parameters.");
                    http_response_code(500); // Forzar reintento
                    echo "Missing parameters";
                    exit;
                }
                throw new Exception("Missing Redsys parameters in response.");
            }

            // 1. Decode parameters
            $paramsJson = $this->redsysService->decodeParameters($paramsBase64);
            $params = json_decode($paramsJson, true);

            $orderId = $params['Ds_Order'] ?? null;
            $responseCode = $params['Ds_Response'] ?? null;
            
            if (!$orderId || $responseCode === null) {
                if ($isNotification) {
                    error_log("Redsys Notification Error: Incomplete decoded data for notification.");
                    http_response_code(500); // Forzar reintento
                    echo "Incomplete data";
                    exit;
                }
                throw new Exception("Incomplete data in Redsys parameters.");
            }

            // 2. Re-generate signature for verification (CRITICAL SECURITY STEP)
            $signatureLocal = $this->redsysService->createSignature($paramsBase64, $orderId);

            if ($signatureLocal !== $signatureReceived) {
                error_log("Redsys Signature mismatch for Order ID {$orderId}. Received: {$signatureReceived}, Calculated: {$signatureLocal}");
                if ($isNotification) {
                    // Devolver 500 para que Redsys reintente la notificación
                    http_response_code(500); 
                    echo "Signature mismatch";
                    exit;
                }
                throw new Exception("Redsys Signature mismatch. Potential tampering.");
            }

            // 3. Process the response code (codes < 100 are successful, or codes 900-999 for pre-authorizations)
            $responseInt = (int)$responseCode;
            $isSuccessful = ($responseInt >= 0 && $responseInt <= 99); // Standard successful payment

            // 4. Update order status ONLY if not already paid, to prevent double processing (CRITICAL IDEMPOTENCY)
            $stmt = $this->db->pdo()->prepare('SELECT status FROM orders WHERE id = ?');
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();
            
            if ($order && $order['status'] !== 'paid') {
                if ($isSuccessful) {
                    // PAYMENT SUCCESS
                    $stmt = $this->db->pdo()->prepare('UPDATE orders SET status = "paid", payment_details = ? WHERE id = ?');
                    $paymentDetails = json_encode([
                        'redsys_response_code' => $responseCode, 
                        'redsys_tx_id' => $params['Ds_AuthorisationCode'] ?? 'N/A',
                        'card_type' => $params['Ds_Card_Type'] ?? 'N/A'
                    ]);
                    $stmt->execute([$paymentDetails, $orderId]);
    
                    if (!$isNotification) {
                        // Si es respuesta síncrona, redirigir al usuario y limpiar sesión
                        unset($_SESSION['cart']); 
                        $_SESSION['last_order_id'] = $orderId;
                        header("Location: /order-success");
                        exit;
                    }
                } else {
                    // PAYMENT FAILURE/CANCELLATION
                    $stmt = $this->db->pdo()->prepare('UPDATE orders SET status = "failed" WHERE id = ?');
                    $stmt->execute([$orderId]);
    
                    error_log("Redsys Payment Failed for Order ID {$orderId}. Response: {$responseCode}");
                    
                    if (!$isNotification) {
                        // Si es respuesta síncrona, redirigir al carrito con error
                        $_SESSION['error_message'] = "El pago no pudo ser procesado o fue cancelado. Código de respuesta: {$responseCode}.";
                        header("Location: /cart");
                        exit;
                    }
                }
            }
            
            // 5. Final response for Notification
            if ($isNotification) {
                // Confirmar a Redsys que la notificación fue recibida y procesada
                http_response_code(200); 
                echo "OK";
                exit;
            }

        } catch (Exception $e) {
            // Error crítico durante el procesamiento
            error_log("Redsys Processing Critical Error: " . $e->getMessage());
            
            if ($isNotification) {
                // Devolver 500 para que Redsys reintente
                http_response_code(500); 
                echo "Error processing notification";
                exit;
            }

            // Redirección de error para el navegador
            $_SESSION['error_message'] = "Ocurrió un error inesperado al procesar el pago. Por favor, contacte con soporte.";
            header("Location: /cart");
            exit;
        }
    }
}