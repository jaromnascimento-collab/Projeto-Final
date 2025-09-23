<?php
require_once 'auth.php';
verificarLogin();
require_once 'conexao.php';

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
            border-radius: 8px;
            padding: 8px 12px;
            margin: 2px;
            transition: all 0.3s ease;
        }
        .btn-action:hover {
            transform: translateY(-2px);
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        /* Estilo para a linha sumindo suavemente */
        tr.removing {
            transition: opacity 0.5s ease-out;
            opacity: 0;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-car me-2"></i>AutoList
        </a>
        <div class="navbar-nav ms-auto">
            <a href="index.php" class="nav-link">
                <i class="fas fa-home me-2"></i>Dashboard
            </a>
            <a href="logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt me-2"></i>Sair
            </a>
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
            <a href="cadastro.php" class="btn btn-primary btn-action">
                <i class="fas fa-plus me-2"></i>Novo Cliente
            </a>
        </div>
    </div>
    
    <div id="alert-placeholder"></div>

    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="alert alert-success">✅ Cliente cadastrado com sucesso!</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
        <div class="alert alert-info">✅ Cliente atualizado com sucesso!</div>
    <?php endif; ?>

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
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr id="registro-<?= $row['id'] ?>">
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['nome_cliente']) ?></td>
                            <td><?= htmlspecialchars($row['endereco_cliente']) ?></td>
                            <td><?= htmlspecialchars($row['telefone_cliente']) ?></td>
                            <td><?= htmlspecialchars($row['fipe_marca']) ?></td>
                            <td><?= htmlspecialchars($row['fipe_modelo']) ?></td>
                            <td><?= htmlspecialchars($row['fipe_ano_modelo']) ?></td>
                            <td><?= htmlspecialchars($row['fipe_valor']) ?></td>
                            <td>
                                <a href="editar.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm btn-action" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <?php if (isAdmin()): ?>
                                <button onclick="excluirRegistro(<?= $row['id'] ?>)" class="btn btn-danger btn-sm btn-action" title="Excluir">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>

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
// Fecha a conexão com o banco de dados para liberar recursos.
$conn->close();
?>

<script>
function showAlert(message, type) {
    const alertPlaceholder = document.getElementById('alert-placeholder');
    const wrapper = document.createElement('div');
    wrapper.innerHTML = [
        `<div class="alert alert-${type} alert-dismissible" role="alert">`,
        `   <div>${message}</div>`,
        '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
        '</div>'
    ].join('');

    alertPlaceholder.innerHTML = ''; // Limpa alertas anteriores
    alertPlaceholder.append(wrapper);
}

function excluirRegistro(id) {
    if (!confirm('Tem certeza que deseja excluir este cliente?')) {
        return;
    }

    const dados = new FormData();
    dados.append('id', id);

    fetch('excluir_ajax.php', {
        method: 'POST',
        body: dados
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            const linhaParaRemover = document.getElementById('registro-' + id);
            if (linhaParaRemover) {
                // Adiciona uma classe para a animação e remove o elemento após a transição
                linhaParaRemover.classList.add('removing');
                setTimeout(() => {
                    linhaParaRemover.remove();
                }, 500); // Tempo deve ser igual ao da transição no CSS
            }
            // Mostra o alerta de sucesso do Bootstrap
            showAlert('🗑️ Cliente excluído com sucesso!', 'warning');
        } else {
            // Mostra o alerta de erro do Bootstrap
            showAlert('❌ Erro: ' + data.mensagem, 'danger');
        }
    })
    .catch(error => {
        console.error('Erro na requisição:', error);
        showAlert('❌ Não foi possível se comunicar com o servidor.', 'danger');
    });
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>