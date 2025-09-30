<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/auth.php';
verificarLogin();

header('Content-Type: application/json'); // Retorna JSON

require_once __DIR__ . '/../config/conexao.php';

$response = ['sucesso' => false, 'mensagem' => 'Erro desconhecido'];

// Verifica se usuário é admin
if (!isAdmin()) {
    $response['mensagem'] = "Acesso Negado! Você não tem permissão.";
    echo json_encode($response);
    exit;
}

// Verifica se ID foi enviado
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    $response['mensagem'] = "ID inválido.";
    echo json_encode($response);
    exit;
}

$id = intval($_POST['id']);

// Prepara e executa a exclusão
$stmt = $conn->prepare("DELETE FROM registros_clientes WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $response['sucesso'] = true;
    $response['mensagem'] = "Cliente excluído com sucesso.";
} else {
    $response['mensagem'] = "Erro ao excluir cliente: " . $conn->error;
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
