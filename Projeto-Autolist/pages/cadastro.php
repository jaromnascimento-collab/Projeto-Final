<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/conexao.php';

verificarLogin();

function callFipeApi(string $endpoint) {
    $url = "https://parallelum.com.br/fipe/api/v1/carros/{$endpoint}";
    $data = @file_get_contents($url);
    return $data ? json_decode($data, true) : null;
}

$message = '';
$marcas = [];
$modelos = [];
$anos = [];
$fipeResult = null;

$selectedMarca = $_POST['marca'] ?? '';
$selectedModelo = $_POST['modelo'] ?? '';
$selectedAno = $_POST['ano'] ?? '';

$marcas = callFipeApi('marcas');

if ($selectedMarca) {
    $dataModelos = callFipeApi("marcas/{$selectedMarca}/modelos");
    if ($dataModelos) $modelos = $dataModelos['modelos'];
}

if ($selectedMarca && $selectedModelo) {
    $anos = callFipeApi("marcas/{$selectedMarca}/modelos/{$selectedModelo}/anos");
}

if ($selectedMarca && $selectedModelo && $selectedAno) {
    $fipeResult = callFipeApi("marcas/{$selectedMarca}/modelos/{$selectedModelo}/anos/{$selectedAno}");
}

if (isset($_POST['save_record'])) {
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");

    $sql = "INSERT INTO registros_clientes 
        (nome_cliente, endereco_cliente, telefone_cliente, fipe_valor, fipe_marca, fipe_modelo, fipe_ano_modelo, fipe_combustivel, fipe_codigo, fipe_mes_referencia) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param(
            "ssssssisss",
            $nome_cliente, $endereco_cliente, $telefone_cliente,
            $fipe_valor, $fipe_marca, $fipe_modelo, $fipe_ano_modelo,
            $fipe_combustivel, $fipe_codigo, $fipe_mes_referencia
        );

        $nome_cliente = $_POST['nome_cliente'];
        $endereco_cliente = $_POST['endereco_cliente'];
        $telefone_cliente = $_POST['telefone_cliente'];

        // Repopula o resultado FIPE para garantir que os dados sejam salvos
        if ($selectedMarca && $selectedModelo && $selectedAno) {
            $fipeResult = callFipeApi("marcas/{$selectedMarca}/modelos/{$selectedModelo}/anos/{$selectedAno}");
        }

        if ($fipeResult) {
            $fipe_valor = $fipeResult['Valor'];
            $fipe_marca = $fipeResult['Marca'];
            $fipe_modelo = $fipeResult['Modelo'];
            $fipe_ano_modelo = $fipeResult['AnoModelo'];
            $fipe_combustivel = $fipeResult['Combustivel'];
            $fipe_codigo = $fipeResult['CodigoFipe'];
            $fipe_mes_referencia = $fipeResult['MesReferencia'];
        } else {
             // Garante que a seleção FIPE seja completa antes de salvar
            if (empty($selectedMarca) || empty($selectedModelo) || empty($selectedAno)) {
                 $message = "<div class='message error'>❌ Erro: Por favor, selecione Marca, Modelo e Ano do veículo.</div>";
            } else {
                 $message = "<div class='message error'>❌ Erro: Dados FIPE não encontrados para a seleção.</div>";
            }
           
            $fipe_valor = $fipe_marca = $fipe_modelo = $fipe_combustivel = $fipe_codigo = $fipe_mes_referencia = '';
            $fipe_ano_modelo = 0;
        }

        if ($message === '' && $stmt->execute()) {
            header("Location: listar.php?success=1");
            exit();
            
        } else {
            if ($message === '') { // Evita sobreescrever a mensagem de erro FIPE
                $message = "<div class='message error'>❌ Erro ao salvar: " . htmlspecialchars($stmt->error) . "</div>";
            }
        }

        $stmt->close();
    } else {
        $message = "<div class='message error'>❌ Erro na preparação da query.</div>";
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Captação de Clientes - AutoList</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .container { max-width: 900px; margin: 30px auto; background: #fff; padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        h1 { text-align: center; margin-bottom: 20px; color: #2c3e50; }
        .subtitle { text-align: center; color: #6c757d; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { font-weight: 600; }
        select, input, textarea { border-radius: 8px; }
        input[type="submit"] { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px; border: none; font-weight: 600; width: 100%; }
        .message { padding: 15px; border-radius: 8px; text-align: center; margin-bottom: 20px; }
        .error { background: #f8d7da; color: #721c24; }
        #fipe-result { background: #e8f6fd; padding: 15px; border-left: 5px solid #667eea; border-radius: 8px; margin-top: 20px; }
        
        /* Adicionado para melhorar a aparência do campo readonly */
        input[readonly], textarea[readonly] {
            background-color: #e9ecef !important; /* !important para sobrepor estilos do bootstrap */
            cursor: text;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="../index.php"><i class="fas fa-car me-2"></i>AutoList</a>
        <div class="navbar-nav ms-auto">
            <a href="../index.php" class="nav-link">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
            </a>
            <a href="listar.php" class="nav-link">
                <i class="fas fa-users me-2"></i> Clientes
            </a>
        </div>
    </div>
</nav> 

<div class="container">
    <h1><i class="fas fa-user-plus me-2"></i>Captação de Clientes</h1>
    <p class="subtitle">Cadastre clientes interessados em veículos para futuras oportunidades de vendas</p>
    <?= $message ?>

    <form method="POST" action="">
        <input type="hidden" name="marca" value="<?= htmlspecialchars($selectedMarca) ?>">
        <input type="hidden" name="modelo" value="<?= htmlspecialchars($selectedModelo) ?>">
        <input type="hidden" name="ano" value="<?= htmlspecialchars($selectedAno) ?>">

        <div class="card-section">
            <h2>Dados Pessoais</h2>
            <div class="form-group">
                <label>Nome Completo</label>
                <input type="text" name="nome_cliente" required class="form-control" value="<?= htmlspecialchars($_POST['nome_cliente'] ?? '') ?>" 
                       readonly onfocus="this.removeAttribute('readonly');">
            </div>
            <div class="form-group">
                <label>Endereço</label>
                <textarea name="endereco_cliente" required class="form-control" 
                          readonly onfocus="this.removeAttribute('readonly');"><?= htmlspecialchars($_POST['endereco_cliente'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>Telefone</label>
                <input type="tel" name="telefone_cliente" class="form-control" value="<?= htmlspecialchars($_POST['telefone_cliente'] ?? '') ?>" 
                       readonly onfocus="this.removeAttribute('readonly');">
            </div>
        </div>

        <div class="card-section">
            <h2>Interesse do Cliente (Consulta FIPE)</h2>
            <p class="text-muted">A seleção de um item recarrega a página para atualizar as opções seguintes.</p>
            <div class="form-group">
                <label>Marca</label>
                <select name="marca" onchange="this.form.submit()" class="form-select">
                    <option value="">Selecione...</option>
                    <?php foreach ($marcas as $marca): ?>
                        <option value="<?= $marca['codigo'] ?>" <?= ($selectedMarca == $marca['codigo']) ? 'selected' : '' ?>><?= htmlspecialchars($marca['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Modelo</label>
                <select name="modelo" onchange="this.form.submit()" class="form-select" <?= !$selectedMarca ? 'disabled' : '' ?>>
                    <option value="">Selecione...</option>
                    <?php foreach ($modelos as $modelo): ?>
                        <option value="<?= $modelo['codigo'] ?>" <?= ($selectedModelo == $modelo['codigo']) ? 'selected' : '' ?>><?= htmlspecialchars($modelo['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Ano</label>
                <select name="ano" onchange="this.form.submit()" class="form-select" <?= !$selectedModelo ? 'disabled' : '' ?>>
                    <option value="">Selecione...</option>
                    <?php foreach ($anos as $ano): ?>
                        <option value="<?= $ano['codigo'] ?>" <?= ($selectedAno == $ano['codigo']) ? 'selected' : '' ?>><?= htmlspecialchars($ano['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if ($fipeResult): ?>
                <div id="fipe-result">
                    <h3>Informações do Veículo</h3>
                    <p><strong>Valor:</strong> <?= htmlspecialchars($fipeResult['Valor']) ?></p>
                    <p><strong>Marca:</strong> <?= htmlspecialchars($fipeResult['Marca']) ?></p>
                    <p><strong>Modelo:</strong> <?= htmlspecialchars($fipeResult['Modelo']) ?></p>
                    <p><strong>Ano:</strong> <?= htmlspecialchars($fipeResult['AnoModelo']) ?></p>
                    <p><strong>Combustível:</strong> <?= htmlspecialchars($fipeResult['Combustivel']) ?></p>
                    <p><strong>Código FIPE:</strong> <?= htmlspecialchars($fipeResult['CodigoFipe']) ?></p>
                    <p><strong>Mês de Referência:</strong> <?= htmlspecialchars($fipeResult['MesReferencia']) ?></p>
                </div>
            <?php endif; ?>
        </div>

        <input type="submit" name="save_record" value="💾 Cadastrar Cliente">
    </form>
</div>
</body>
</html>