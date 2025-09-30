<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../config/auth.php';
verificarLogin();
require_once __DIR__ . '/../config/conexao.php';

// Cria o comando SQL para selecionar todos os clientes, ordenados por nome.
$sql = "SELECT * FROM `registros_clientes` ORDER BY nome_cliente ASC";

// Executa a consulta e armazena o resultado.
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gestão de Clientes - AutoList</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
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
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        .btn-action {
            width: 40px; /* Para alinhar os botões */
            height: 40px;
            border-radius: 8px;
            margin: 2px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-action:hover {
            transform: translateY(-2px);
        }
        /* Para as mensagens de alerta dinâmicas */
        #container-alertas {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 1050;
            width: auto;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
<a class="navbar-brand" href="../index.php">
    <i class="fas fa-car me-2"></i>AutoList
</a>
<div class="navbar-nav ms-auto">
    <a href="../index.php" class="nav-link">
        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
    </a>
    <a href="../logout.php" class="nav-link">
        <i class="fas fa-sign-out-alt me-2"></i>Sair
    </a>
</div>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-users me-3"></i>Gestão de Clientes</h2>
            <p class="text-muted">Gerencie seu banco de clientes e oportunidades de vendas</p>
        </div>
        <div>
            <a href="cadastro.php" class="btn btn-primary btn-action" style="width: auto; padding: 0 1rem;">
                <i class="fas fa-plus me-2"></i>Novo Cliente
            </a>
        </div>
    </div>
    
    <div id="container-alertas"></div>

    <div class="table-responsive shadow-sm">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Endereço</th>
                    <th>Telefone</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Ano</th>
                    <th>Valor FIPE</th>
                    <th class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr id="cliente-<?= $row['id'] ?>">
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['nome_cliente']) ?></td>
                            <td><?= htmlspecialchars($row['endereco_cliente']) ?></td>
                            <td><?= htmlspecialchars($row['telefone_cliente']) ?></td>
                            <td><?= htmlspecialchars($row['fipe_marca']) ?></td>
                            <td><?= htmlspecialchars($row['fipe_modelo']) ?></td>
                            <td><?= htmlspecialchars($row['fipe_ano_modelo']) ?></td>
                            <td><?= htmlspecialchars($row['fipe_valor']) ?></td>
                            <td class="text-center">
                                <a href="editar.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm btn-action" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="iniciarExclusao(<?= $row['id'] ?>)" class="btn btn-danger btn-sm btn-action" title="Excluir">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center">Nenhum cliente cadastrado ainda.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// MUDANÇA 3: Passa a permissão do usuário (admin ou não) para o JavaScript
echo "<script>const usuarioEAdmin = " . (isAdmin() ? 'true' : 'false') . ";</script>";

// Fecha a conexão com o banco de dados.
$conn->close();
?>

<script>
function mostrarAlerta(mensagem, tipo = 'success') {
    const container = document.getElementById('container-alertas');
    const tipoClasse = tipo === 'success' ? 'alert-success' : 'alert-danger';
    const icone = tipo === 'success' ? '✅' : '❌';

    const alertaDiv = document.createElement('div');
    alertaDiv.className = `alert ${tipoClasse} alert-dismissible fade show`;
    alertaDiv.role = 'alert';
    alertaDiv.innerHTML = `
        ${icone} ${mensagem}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    container.appendChild(alertaDiv);

    // Remove o alerta após 5 segundos
    setTimeout(() => {
        alertaDiv.classList.remove('show');
        setTimeout(() => alertaDiv.remove(), 150);
    }, 5000);
}

async function iniciarExclusao(idCliente) {
    // Primeiro, checa a permissão que o PHP nos deu
    if (!usuarioEAdmin) {
        mostrarAlerta("Acesso Negado! Você não tem permissão para excluir.", 'danger');
        return;
    }
    
    // Se for admin, continua com a confirmação
    const temCerteza = confirm("Tem certeza que deseja excluir este cliente?");
    if (!temCerteza) {
        return; 
    }

    try {
        const resposta = await fetch('excluir.php', {
 // Verifique se o nome do arquivo está correto!
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + idCliente
        });

        const resultado = await resposta.json();

        if (resultado.sucesso) {
            const linhaParaRemover = document.getElementById('cliente-' + idCliente);
            if (linhaParaRemover) {
                // Efeito suave de "fade out" antes de remover
                linhaParaRemover.style.transition = 'opacity 0.5s';
                linhaParaRemover.style.opacity = '0';
                setTimeout(() => linhaParaRemover.remove(), 500);
            }
            mostrarAlerta('Cliente excluído com sucesso!', 'success');
        } else {
            // Mostra a mensagem de erro vinda do PHP (pode ser o "Acesso Negado" novamente)
            mostrarAlerta(resultado.mensagem, 'danger');
        }

    } catch (error) {
        console.error('Falha na requisição:', error);
        mostrarAlerta('Não foi possível conectar ao servidor.', 'danger');
    }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>