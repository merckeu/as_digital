<?php
// Inclui o arquivo de configuração e funções
$config = include 'config.php';
include 'signature.php';

// Mensagens para o usuário
$message = '';
$nome_cliente = '';
$cliente_id = '';
$resultados = [];

// Configurações de conexão com o banco de dados
$servername = "localhost";
$username = "root";
$password = "vertrigo";
$dbname = "mkradius";

// Criar a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar a conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Verifica se a tabela ass_cliente existe e cria se necessário
$sql_criar_tabela = "
CREATE TABLE IF NOT EXISTS ass_cliente (
    id INT(11) NOT NULL,
    nome VARCHAR(64) NOT NULL,
    assinatura TEXT,
    PRIMARY KEY (id)
) ENGINE=InnoDB;
";
if (!$conn->query($sql_criar_tabela)) {
    die("Erro ao criar/verificar tabela ass_cliente: " . $conn->error);
}

// Processar o formulário de envio da assinatura
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['signature']) && isset($_POST['cliente_id'])) {
    try {
        // Caminho para o diretório onde a assinatura será salva
        $signaturePath = saveSignature($_POST['signature'], $config['signature_dir']);

        // Vincular a assinatura ao cliente selecionado
        $cliente_id = (int) $_POST['cliente_id'];
        $nome_cliente = $conn->query("SELECT nome FROM sis_cliente WHERE id = $cliente_id")->fetch_assoc()['nome'];

        $sql = "INSERT INTO ass_cliente (id, nome, assinatura) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE assinatura = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $cliente_id, $nome_cliente, $signaturePath, $signaturePath);

        if ($stmt->execute()) {
            $message = "Assinatura salva e vinculada ao cliente com sucesso.";
        } else {
            $message = "Erro ao vincular assinatura ao cliente: " . $stmt->error;
        }
        $stmt->close();
    } catch (Exception $e) {
        $message = "Erro: " . $e->getMessage();
    }
}

// Processar o formulário de pesquisa de cliente
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nome_cliente'])) {
    $nome_cliente = $_POST['nome_cliente'];

    // Consulta ao banco de dados
    $sql = "SELECT id, nome FROM sis_cliente WHERE nome LIKE ?";
    $stmt = $conn->prepare($sql);
    $search_term = "%" . $nome_cliente . "%"; // Adiciona o caractere de wildcard para pesquisa parcial
    $stmt->bind_param("s", $search_term);

    // Executa a consulta
    $stmt->execute();
    $result = $stmt->get_result();

    // Armazenar os resultados
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $resultados[] = $row;
        }
    } else {
        $message = "Nenhum cliente encontrado.";
    }

    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
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

        <!-- Formulário de pesquisa de cliente -->
        <h2>Pesquisa de Cliente</h2>
        <form method="POST">
            <label for="nome_cliente">Nome do Cliente:</label>
            <input type="text" id="nome_cliente" name="nome_cliente" value="<?php echo htmlspecialchars($nome_cliente); ?>" required>
            <button type="submit">Pesquisar</button>
        </form>

        <?php if (!empty($resultados)): ?>
            <h2>Selecione o Cliente:</h2>
            <form method="POST" id="formSignature">
                <label for="cliente_id">Cliente:</label>
                <select name="cliente_id" id="cliente_id" required>
                    <option value="" disabled selected>Selecione um cliente</option>
                    <?php foreach ($resultados as $cliente): ?>
                        <option value="<?php echo $cliente['id']; ?>">
                            <?php echo htmlspecialchars($cliente['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Área para assinatura manuscrita -->
                <h2>Assine o Documento</h2>
                <div style="border: 1px solid #ccc; width: 100%; height: 300px;">
                    <canvas id="signatureCanvas" style="width: 100%; height: 100%;"></canvas>
                </div>
                <button type="button" id="clearButton" style="margin-top: 10px;">Limpar</button>

                <!-- Input oculto para a assinatura -->
                <input type="hidden" name="signature" id="signatureData">
                <button type="submit" style="margin-top: 10px;">Enviar Assinatura</button>
            </form>
        <?php endif; ?>
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
        document.getElementById('formSignature').addEventListener('submit', function (e) {
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
