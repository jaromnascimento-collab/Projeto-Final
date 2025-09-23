<?php
session_start();
header('Content-Type: application/json');

require_once 'auth.php';
require_once 'conexao.php';

$response = ['sucesso' => false, 'mensagem' => 'Ocorreu um erro desconhecido.'];

// Verifica login
if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
    $response['mensagem'] = 'Usuário não autenticado.';
    echo json_encode($response);
    exit();
}

// Apenas admin pode excluir
if (!isAdmin()) {
    $response['mensagem'] = 'Acesso negado. Você não tem permissão.';
    http_response_code(403);
    echo json_encode($response);
    exit();
}

// ID recebido
$id = $_POST['id'] ?? null;

if ($id) {
    $sql = "DELETE FROM registros_clientes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $response['sucesso'] = true;
        $response['mensagem'] = 'Registro excluído com sucesso!';
    } else {
        $response['mensagem'] = 'Erro ao tentar excluir no banco de dados.';
    }

    $stmt->close();
} else {
    $response['mensagem'] = 'Nenhum ID foi fornecido.';
}

echo json_encode($response);
exit();
