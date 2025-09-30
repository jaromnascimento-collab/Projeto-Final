<?php

// Inicia a sessão se ainda não foi iniciada.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Configurações de login com senhas criptografadas
$usuarios = [
    'jaromnascmento@gmail.com' => [
        'senha' => password_hash('poasul2022', PASSWORD_DEFAULT), // SENHA CRIPTOGRAFADA
        'nome' => 'Administrador',
        'nivel' => 'admin'
    ],
    'vendedor' => [
        'senha' => password_hash('vendedor123', PASSWORD_DEFAULT), // SENHA CRIPTOGRAFADA
        'nome' => 'Vendedor',
        'nivel' => 'vendedor'
    ]
];

// Função para verificar se usuário está logado
function verificarLogin() {
    if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
        header("Location: pages/login.php");
        exit();
    }
}

// Função para fazer logout
function fazerLogout() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Limpa todas as variáveis de sessão
    $_SESSION = [];

    // Remove o cookie de sessão
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Destroi a sessão
    session_destroy();

    // Redireciona para a página de login (ajusta caminho se precisar)
    header("Location: pages/login.php");
    exit;
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