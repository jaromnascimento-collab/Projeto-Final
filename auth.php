<?php
session_start();

// Configurações de login (em produção, use banco de dados)
$usuarios = [
    'admin' => [
        'senha' => 'admin123',
        'nome' => 'Administrador',
        'nivel' => 'admin'
    ],
    'vendedor' => [
        'senha' => 'vendedor123',
        'nome' => 'Vendedor',
        'nivel' => 'vendedor'
    ]
];

// Função para verificar se usuário está logado
function verificarLogin() {
    if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
        header("Location: login.php");
        exit();
    }
}

// Função para fazer logout
function fazerLogout() {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Função para obter dados do usuário logado
function getUsuarioLogado() {
    return $_SESSION['usuario_dados'] ?? null;
}

// Função para verificar se é admin
function isAdmin() {
    $usuario = getUsuarioLogado();
    return $usuario && $usuario['nivel'] === 'admin';
}
?>
