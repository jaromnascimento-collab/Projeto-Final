<?php
session_start();
require_once 'auth.php';

// Array de usuários (em produção você puxaria do banco de dados)
$usuarios = [
    'admin' => [
        'senha' => 'admin123',
        'nome'  => 'Administrador',
        'nivel' => 'admin'
    ],
    'vendedor' => [
        'senha' => 'vendedor123',
        'nome'  => 'Vendedor',
        'nivel' => 'vendedor'
    ]
];

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $senha   = $_POST['senha'] ?? '';

    // Confere se existe usuário no array e senha está correta
    if (isset($usuarios[$usuario]) && $usuarios[$usuario]['senha'] === $senha) {
        // Marca login como válido
        $_SESSION['usuario_logado'] = true;

        // Salva todos os dados do usuário na sessão
        $_SESSION['usuario_dados'] = [
            'usuario' => $usuario,
            'nome'    => $usuarios[$usuario]['nome'],
            'nivel'   => $usuarios[$usuario]['nivel']
        ];

        // Redireciona para o painel
        header("Location: painel.php");
        exit();
    } else {
        $erro = "Usuário ou senha inválidos!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AutoList Loja de Veículos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo i {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 15px;
        }
        .logo h1 {
            color: #333;
            font-weight: 700;
            margin: 0;
        }
        .logo p {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 0.9rem;
        }
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            width: 100%;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
            border-radius: 10px 0 0 10px;
        }
        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }
        .demo-credentials {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            font-size: 0.9rem;
        }
        .demo-credentials h6 {
            color: #495057;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .demo-credentials p {
            margin: 5px 0;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <i class="fas fa-car"></i>
            <h1>AutoList</h1>
            <p>Sistema de Gestão de Clientes</p>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="login" class="form-label">Usuário</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" class="form-control" id="login" name="login" 
                           placeholder="Digite seu usuário" required>
                </div>
            </div>

            <div class="mb-4">
                <label for="senha" class="form-label">Senha</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" class="form-control" id="senha" name="senha" 
                           placeholder="Digite sua senha" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-login">
                <i class="fas fa-sign-in-alt me-2"></i>
                Entrar
            </button>
        </form>

        <div class="demo-credentials">
            <h6><i class="fas fa-info-circle me-2"></i>Credenciais de Demonstração</h6>
            <p><strong>Admin:</strong> admin / admin123</p>
            <p><strong>Vendedor:</strong> vendedor / vendedor123</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
