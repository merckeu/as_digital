<?php
// Conexão com o banco de dados MySQL
$host = 'localhost'; // Host
$dbname = 'mkradius'; // Nome do banco de dados
$username = 'root'; // Nome de usuário
$password = 'vertrigo'; // Senha

$conn = mysqli_connect($host, $username, $password, $dbname);

// Verifica a conexão
if (!$conn) {
    die("Falha na conexão: " . mysqli_connect_error());
}
?>
