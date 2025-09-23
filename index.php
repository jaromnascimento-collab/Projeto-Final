<?php
require_once 'auth.php';
verificarLogin();

// Buscar estatísticas do banco
require_once 'conexao.php';

$total_clientes = 0;
$clientes_recentes = 0;

try {
    $sql = "SELECT COUNT(*) as total FROM registros_clientes";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        $total_clientes = $row['total'];
    }
    
    $sql = "SELECT COUNT(*) as recentes FROM registros_clientes WHERE DATE(created_at) = CURDATE()";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        $clientes_recentes = $row['recentes'];
    }
} catch (Exception $e) {
    // Ignora erros de estatísticas
}

$usuario = getUsuarioLogado();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AutoList Loja de Veículos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            border: none;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            margin-bottom: 20px;
        }
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        .btn-action {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 15px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            color: white;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        .welcome-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .feature-icon {
            font-size: 2.5rem;
            color: #667eea;
            margin-bottom: 15px;
        }
        .logout-btn {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-car me-2"></i>AutoList
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user me-2"></i>
                    Olá, <?= htmlspecialchars($usuario['nome']) ?>!
                </span>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt me-2"></i>Sair
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-6 fw-bold text-primary mb-3">
                        <i class="fas fa-tachometer-alt me-3"></i>Dashboard
                    </h1>
                    <p class="lead text-muted mb-0">
                        Gerencie seus clientes e oportunidades de vendas de forma eficiente
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <i class="fas fa-chart-line feature-icon"></i>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-number"><?= $total_clientes ?></div>
                    <div class="stat-label">
                        <i class="fas fa-users me-2"></i>Total de Clientes
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-number"><?= $clientes_recentes ?></div>
                    <div class="stat-label">
                        <i class="fas fa-user-plus me-2"></i>Novos Hoje
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-number">∞</div>
                    <div class="stat-label">
                        <i class="fas fa-chart-line me-2"></i>Oportunidades
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Cards -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user-plus me-2"></i>Captação de Clientes
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <i class="fas fa-handshake feature-icon"></i>
                        <h5 class="card-title">Cadastrar Novo Cliente</h5>
                        <p class="card-text text-muted">
                            Capture informações de clientes interessados em veículos e 
                            mantenha um banco de dados organizado para futuras oportunidades.
                        </p>
                        <a href="cadastro.php" class="btn-action">
                            <i class="fas fa-plus me-2"></i>Novo Cliente
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>Gestão de Clientes
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <i class="fas fa-database feature-icon"></i>
                        <h5 class="card-title">Listar e Gerenciar</h5>
                        <p class="card-text text-muted">
                            Visualize todos os clientes cadastrados, edite informações 
                            e mantenha contato para oportunidades de vendas.
                        </p>
                        <a href="listar.php" class="btn-action">
                            <i class="fas fa-list me-2"></i>Ver Clientes
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-star me-2"></i>Recursos do Sistema
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center mb-3">
                                <i class="fas fa-car-side feature-icon"></i>
                                <h6>Consulta FIPE</h6>
                                <small class="text-muted">Valores atualizados de veículos</small>
                            </div>
                            <div class="col-md-3 text-center mb-3">
                                <i class="fas fa-edit feature-icon"></i>
                                <h6>Edição Completa</h6>
                                <small class="text-muted">Atualize dados e veículos</small>
                            </div>
                            <div class="col-md-3 text-center mb-3">
                                <i class="fas fa-search feature-icon"></i>
                                <h6>Busca Avançada</h6>
                                <small class="text-muted">Encontre clientes rapidamente</small>
                            </div>
                            <div class="col-md-3 text-center mb-3">
                                <i class="fas fa-chart-bar feature-icon"></i>
                                <h6>Relatórios</h6>
                                <small class="text-muted">Acompanhe suas vendas</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>