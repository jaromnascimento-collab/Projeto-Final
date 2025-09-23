<?php
// Configurações do banco de dados
$localhost = "localhost";        // Servidor do banco de dados
$username = "root";              // Usuário padrão do XAMPP/WAMP (pode ser diferente)
$password = "root";              // Senha padrão do XAMPP/WAMP (geralmente vazia)
$port = "3306";                  // Porta do MySQL (geralmente 3306)
$database = "autolist";          // Nome do banco de dados

// Cria a conexão com o banco de dados
$conn = new mysqli($localhost, $username, $password, $database, $port);

// Verifica se ocorreu algum erro na conexão
if ($conn->connect_error) {
    // Exibe a mensagem de erro com detalhes
    die("Falha na conexão: " . $conn->connect_error);
} 

// Define o charset para UTF-8
$conn->set_charset("utf8mb4");
?>
