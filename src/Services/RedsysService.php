<?php

namespace App\Services;

/**
 * Class RedsysService
 * Handles the preparation of payment parameters and HMAC-SHA256 signature generation
 * required for the Redsys (TPV Virtual) Form Post integration.
 */
class RedsysService
{
    /**
     * Prepares the payment payload and generates the signature.
     *
     * @param float $amount The transaction amount (e.g., 10.50)
     * @param string $orderId The unique order reference
     * @param string $successUrl The URL Redsys should redirect to after a successful payment
     * @param string $notificationUrl The URL Redsys should call for server-to-server notification
     * @return array Contains Ds_MerchantParameters, Ds_Signature, and the Redsys API URL.
     */
    public function preparePayment(float $amount, string $orderId, string $successUrl, string $notificationUrl): array
    {
        // 1. Prepare JSON Payload (Ds_MerchantParameters)
        // Amount must be multiplied by 100 as Redsys expects cents (e.g., 10.50 EUR -> 1050)
        $amountInCents = (int) round($amount * 100);

        $params = [
            'DS_MERCHANT_AMOUNT' => $amountInCents,
            'DS_MERCHANT_ORDER' => $orderId,
            // Change env() to getenv() here:
            'DS_MERCHANT_MERCHANTCODE' => getenv('REDSYS_FUC'),
            'DS_MERCHANT_TERMINAL' => getenv('REDSYS_TERMINAL_ID'),
            'DS_MERCHANT_CURRENCY' => getenv('REDSYS_CURRENCY'),
            'DS_MERCHANT_TRANSACTIONTYPE' => getenv('REDSYS_TRANSACTION_TYPE'),
            'DS_MERCHANT_MERCHANTURL' => $notificationUrl,
            'DS_MERCHANT_URLOK' => $successUrl,
            'DS_MERCHANT_URLKO' => $successUrl,
            'DS_MERCHANT_CONSUMERLANGUAGE' => getenv('REDSYS_CONSUMER_LANGUAGE'),
            'DS_MERCHANT_PRODUCTDESCRIPTION' => 'Compra en Brutal Shop',
        ];
        $paramsJson = json_encode($params, JSON_UNESCAPED_SLASHES);

        // 2. Base64 Encode the JSON parameters
        // Redsys uses a URL-safe Base64 encoding scheme (Base64URL)
        $paramsBase64 = $this->toBase64Url($paramsJson);

        // 3. Generate the HMAC-SHA256 signature
        $signature = $this->createSignature($paramsBase64, $orderId);

        return [
            'Ds_SignatureVersion' => 'HMAC_SHA256_V1',
            'Ds_MerchantParameters' => $paramsBase64,
            'Ds_Signature' => $signature,
            // Change env() to getenv() here:
            'RedsysUrl' => getenv('REDSYS_API_URL'),
        ];
    }

    /**
     * Generates the HMAC-SHA256 signature for the Redsys payment request.
     * The key is derived from the transaction secret key and the order ID.
     *
     * @param string $paramsBase64 The Base64URL encoded JSON parameters.
     * @param string $orderId The unique order reference.
     * @return string The Base64URL encoded signature.
     */
// /var/www/html/src/Services/RedsysService.php

    private function createSignature(string $paramsBase64, string $orderId): string
    {
        $secretKey = (string) getenv('REDSYS_SECRET_KEY');

        if (empty($secretKey)) {
            throw new \RuntimeException("REDSYS_SECRET_KEY environment variable is missing or empty.");
        }

        // 1. Get the Key and derive the KCV (Key Confirmation Value) for the specific order.
        // **CRITICAL FIX:** Use the key directly, as it appears to be the raw 32-byte key.
        $key = $secretKey; 

        // 2. KCV Derivation: Order ID is used to dynamically derive the final key.
        // The final key is hash_hmac('sha256', $orderId, $key, true)
        $keyKcv = hash_hmac('sha256', (string)$orderId, $key, true);

        // 3. Signature Calculation: Apply HMAC-SHA256 to the Base64 parameters using the KCV key.
        $hmac = hash_hmac('sha256', $paramsBase64, $keyKcv, true);

        // 4. Base64URL encode the signature result.
        return $this->toBase64Url($hmac);
    }    /**
     * Encodes data to URL-safe Base64 (Base64URL).
     *
     * @param string $data The data to encode.
     * @return string The Base64URL encoded data.
     */
    private function toBase64Url(string $data): string
    {
        // Base64URL replaces '+' with '-', '/' with '_', and removes padding '='
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}