<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redireccionando al TPV</title>
    <link rel="stylesheet" href="/css/neobrutalist.css">
    <style>
        .container {
            text-align: center;
            padding-top: 5rem;
        }
        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #000;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body onload="document.getElementById('redsysForm').submit();"
    <div class="container card">
        <h1>Redireccionando a la Pasarela de Pago...</h1>
        <div class="loader"></div>
        <p>Por favor, espera mientras te redirigimos de forma segura a Redsys para completar el pago.</p>
        <p style="font-size: 0.8em; color: #555;">No cierres esta ventana.</p>

        <!-- Redsys Payment Form (Auto-submits on load) -->
        <form id="redsysForm" method="post" action="<?= htmlspecialchars($RedsysUrl) ?>">
            <!-- Required Version -->
            <input type="hidden" name="Ds_SignatureVersion" value="<?= htmlspecialchars($Ds_SignatureVersion) ?>">
            
            <!-- Encoded Payment Parameters -->
            <input type="hidden" name="Ds_MerchantParameters" value="<?= htmlspecialchars($Ds_MerchantParameters) ?>">
            
            <!-- Generated Signature -->
            <input type="hidden" name="Ds_Signature" value="<?= htmlspecialchars($Ds_Signature) ?>">
            
            <!-- Fallback button in case of JavaScript failure -->
            <button type="submit" class="btn" style="margin-top: 1.5rem; display:none;">
                Continuar al Pago
            </button>
        </form>
        
        <p style="margin-top: 2rem;">
            <!-- Displaying order info for user reassurance -->
            <small><strong>Pedido:</strong> <?= htmlspecialchars($orderContext['orderId']) ?></small><br>
            <small><strong>Monto:</strong> <?= number_format($orderContext['amount'], 2) ?>€</small>
        </p>
    </div>
</body>
</html>