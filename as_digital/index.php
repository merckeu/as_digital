<?php
// Conexão com o banco de dados
$host = "localhost";
$user = "root";
$password = "vertrigo";
$dbname = "mkradius";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_cliente = $_POST['nome_cliente'] ?? '';
    $assinatura = $_POST['assinatura'] ?? '';

    // Validar se o nome do cliente e a assinatura estão presentes
    if (!empty($nome_cliente) && !empty($assinatura)) {
        $nome_imagem = uniqid() . '.png';
        $caminho_imagem = "/opt/mk-auth/admin/addons/as_digital/uploads/signatures/" . $nome_imagem;

        // Decodifica a imagem da assinatura enviada
        $dados_imagem = explode(',', $assinatura);
        if (isset($dados_imagem[1])) {
            $dados_imagem = base64_decode($dados_imagem[1]);
            file_put_contents($caminho_imagem, $dados_imagem);

            // Insere no banco de dados
            $stmt = $conn->prepare("INSERT INTO ass_cliente (nome, assinatura) VALUES (?, ?)");
            $stmt->bind_param("ss", $nome_cliente, $nome_imagem);

            if ($stmt->execute()) {
                echo "Assinatura salva com sucesso!";
            } else {
                echo "Erro ao salvar a assinatura: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "Dados da assinatura inválidos.";
        }
    } else {
        echo "Por favor, preencha todos os campos.";
    }
}

// Consulta para buscar clientes existentes
$result = $conn->query("SELECT id, nome FROM sis_cliente");
$clientes = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assinatura Digital</title>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
</head>
<body>
    <h1>Assinatura Digital</h1>

    <form method="POST">
        <label for="nome_cliente">Pesquisar Cliente:</label>
        <input type="text" id="pesquisa_cliente" oninput="filtrarClientes()" placeholder="Digite para pesquisar...">
        <br><br>
        <label for="nome_cliente">Selecione o Cliente:</label>
        <select name="nome_cliente" id="nome_cliente" required>
            <option value="">-- Selecione um Cliente --</option>
            <?php foreach ($clientes as $cliente): ?>
                <option value="<?php echo htmlspecialchars($cliente['nome']); ?>">
                    <?php echo htmlspecialchars($cliente['nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <br><br>
        <label for="assinatura">Assinatura:</label>
        <div style="border: 1px solid #000; width: 400px; height: 200px;">
            <canvas id="signature-pad" width="400" height="200"></canvas>
        </div>
        <button type="button" onclick="limparAssinatura()">Limpar</button>
        <input type="hidden" name="assinatura" id="assinatura">

        <br><br>
        <button type="submit" onclick="salvarAssinatura()">Salvar</button>
    </form>

    <script>
        const signaturePad = new SignaturePad(document.getElementById('signature-pad'));

        function limparAssinatura() {
            signaturePad.clear();
        }

        function salvarAssinatura() {
            if (!signaturePad.isEmpty()) {
                document.getElementById('assinatura').value = signaturePad.toDataURL();
            } else {
                alert('Por favor, insira a assinatura antes de salvar.');
            }
        }

        function filtrarClientes() {
            const pesquisa = document.getElementById('pesquisa_cliente').value.toLowerCase();
            const select = document.getElementById('nome_cliente');
            const options = select.options;

            for (let i = 1; i < options.length; i++) {
                const texto = options[i].text.toLowerCase();
                options[i].style.display = texto.includes(pesquisa) ? '' : 'none';
            }
        }
    </script>
</body>
</html>
