<?php
// Conex�o com o banco de dados MySQL
$host = 'localhost'; // Host
$dbname = 'mkradius'; // Nome do banco de dados
$username = 'root'; // Nome de usu�rio
$password = 'vertrigo'; // Senha

$conn = mysqli_connect($host, $username, $password, $dbname);

// Verifica a conex�o
if (!$conn) {
    die("Falha na conex�o: " . mysqli_connect_error());
}
?>
