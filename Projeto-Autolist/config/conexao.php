<?php
// ========================================================================
// CONFIGURAÇÕES DO BANCO DE DADOS
// ========================================================================
$localhost = "localhost";        // Servidor do banco de dados
$username  = "root";             // Usuário padrão do XAMPP/MAMP/WAMP
$password  = "root";             // Senha (MAMP usa "root", XAMPP geralmente "")
$port      = 8889;               // Porta do MySQL (MAMP = 8889, XAMPP = 3306)
$database  = "autolist";         // Nome do banco de dados

// ========================================================================
// CONEXÃO
// ========================================================================
$conn = new mysqli($localhost, $username, $password, $database, $port);

// ========================================================================
// FUNÇÃO AUXILIAR PARA SABER SE A REQUISIÇÃO É JSON (API) OU HTML (PÁGINA)
// ========================================================================
function isJsonRequest() {
    if (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        return true;
    }
    if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
        return true;
    }
    return false;
}

// ========================================================================
// TRATAMENTO DE ERRO DE CONEXÃO
// ========================================================================
if ($conn->connect_error) {
    if (isJsonRequest()) {
        // Resposta para APIs/AJAX
        http_response_code(500);
        echo json_encode([
            "sucesso"  => false,
            "mensagem" => "Erro: não foi possível conectar ao servidor de banco de dados.",
            "detalhe"  => $conn->connect_error, // pode remover em produção
            "codigo"   => 500
        ]);
    } else {
        // Resposta para páginas normais
        die("<h2 style='color:red'>Falha na conexão: " . htmlspecialchars($conn->connect_error) . "</h2>");
    }
    exit;
}

// ========================================================================
// AJUSTE DE CHARSET
// ========================================================================
$conn->set_charset("utf8mb4");
?>
