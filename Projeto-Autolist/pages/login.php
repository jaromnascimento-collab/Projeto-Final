<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Se já estiver logado, redireciona para o dashboard
if (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado'] === true) {
    header("Location: ../index.php");
    exit();
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    require_once __DIR__ . '/../config/auth.php';
    
    if (isset($usuarios[$login]) && password_verify($senha, $usuarios[$login]['senha'])) {
        $_SESSION['usuario_logado'] = true;
        $_SESSION['usuario_dados'] = $usuarios[$login];
        header("Location: ../index.php");
        exit();
    } else {
        $erro = 'Login ou senha incorretos!';
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
        /* Seu CSS continua o mesmo */
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
        }
        .logo { text-align: center; margin-bottom: 30px; }
        .logo i { font-size: 4rem; color: #667eea; margin-bottom: 15px; }
        .logo h1 { color: #333; font-weight: 700; margin: 0; }
        .form-control { border: 2px solid #e9ecef; border-radius: 10px; padding: 12px 15px; font-size: 1rem; }
        /* Adicionado para melhorar a aparência do campo readonly */
        .form-control[readonly] {
            background-color: #e9ecef;
            cursor: text;
        }
        .btn-login { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 10px; padding: 12px; font-weight: 600; font-size: 1.1rem; width: 100%; }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }
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

        <form method="POST" action="">
            <div class="mb-3">
                <label for="login" class="form-label">Usuário</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control" id="login" name="login" placeholder="Digite seu usuário" required 
                           readonly onfocus="this.removeAttribute('readonly');">
                </div>
            </div>

            <div class="mb-4">
                <label for="senha" class="form-label">Senha</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="senha" name="senha" placeholder="Digite sua senha" required 
                           readonly onfocus="this.removeAttribute('readonly');">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-login">
                <i class="fas fa-sign-in-alt me-2"></i> Entrar
            </button>
        </form>
    </div>
</body>
</html>