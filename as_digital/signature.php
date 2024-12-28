<?php

// Fun  o para verificar se o diret rio de assinaturas existe
function ensureSignatureDir($path) {
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
}

// Fun  o para salvar a assinatura manuscrita
function saveSignature($signatureBase64, $dir) {
    ensureSignatureDir($dir);

    // Decodifica a assinatura do formato Base64
    $signatureData = str_replace('data:image/png;base64,', '', $signatureBase64);
    $signatureData = str_replace(' ', '+', $signatureData);
    $signatureBinary = base64_decode($signatureData);

    // Cria um nome de arquivo  nico
    $fileName = $dir . 'signature_' . time() . '.png';

    // Salva a assinatura como arquivo PNG
    if (!file_put_contents($fileName, $signatureBinary)) {
        echo "Erro ao salvar o arquivo.<br>";
        echo "Caminho: $fileName<br>";
        echo "Permiss es do diret rio: " . substr(sprintf('%o', fileperms(dirname($fileName))), -4) . "<br>";
        echo "Usu rio do Apache: " . exec('whoami') . "<br>";
        echo "Usu rio do arquivo: " . get_current_user() . "<br>";
        exit;
    } 

    return $fileName;
}

?>
