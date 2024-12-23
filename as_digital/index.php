<?php
// Inclui o arquivo de configuração e funções
$config = include 'config.php';
include 'signature.php';

// Mensagens para o usuário
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['signature'])) {
    try {
        $signaturePath = saveSignature($_POST['signature'], $config['signature_dir']);
        $message = "Assinatura salva com sucesso.";
    } catch (Exception $e) {
        $message = "Erro: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $config['addon_name']; ?></title>
    <link rel="stylesheet" href="/path/to/mk-auth/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
</head>
<body>
    <div class="container">
        <h1><?php echo $config['addon_name']; ?></h1>
        <p><?php echo $config['description']; ?></p>

        <?php if (!empty($message)): ?>
            <div class="alert"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Área para assinatura manuscrita -->
        <h2>Assine o Documento</h2>
        <div style="border: 1px solid #ccc; width: 100%; height: 300px;">
            <canvas id="signatureCanvas" style="width: 100%; height: 100%;"></canvas>
        </div>
        <button id="clearButton" style="margin-top: 10px;">Limpar</button>

        <!-- Formulário para enviar assinatura -->
        <form method="POST" action="">
            <input type="hidden" name="signature" id="signatureData">
            <button type="submit" style="margin-top: 10px;">Enviar Assinatura</button>
        </form>
    </div>

    <script>
        // Configurar Signature Pad
        const canvas = document.getElementById('signatureCanvas');
        const signaturePad = new SignaturePad(canvas);

        // Ajustar tamanho do canvas
        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            signaturePad.clear();
        }
        window.addEventListener("resize", resizeCanvas);
        resizeCanvas();

        // Botão para limpar assinatura
        document.getElementById('clearButton').addEventListener('click', function () {
            signaturePad.clear();
        });

        // Salvar a assinatura no campo oculto antes de enviar
        document.querySelector('form').addEventListener('submit', function (e) {
            if (signaturePad.isEmpty()) {
                alert("Por favor, insira sua assinatura antes de enviar.");
                e.preventDefault();
            } else {
                document.getElementById('signatureData').value = signaturePad.toDataURL();
            }
        });
    </script>
</body>
</html>
