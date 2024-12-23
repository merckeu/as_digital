<?php
// buscar_cliente.php
include('conexao.php'); // Conexão com o banco de dados

$query = $_GET['query'];
$result = mysqli_query($conn, "SELECT nome FROM clientes WHERE nome LIKE '%$query%'");

$clientes = [];
while ($row = mysqli_fetch_assoc($result)) {
    $clientes[] = $row;
}

echo json_encode($clientes);
?>
