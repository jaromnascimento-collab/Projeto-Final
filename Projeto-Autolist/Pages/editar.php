<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../config/auth.php';
verificarLogin();
require_once __DIR__ . '/../config/conexao.php';

// --- FUNÇÃO PARA CHAMAR A API FIPE ---
function callFipeApi(string $endpoint)
{
    $url = "https://parallelum.com.br/fipe/api/v1/carros/{$endpoint}";
    // Usar cURL é mais robusto que file_get_contents para APIs
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Apenas para localhost/desenvolvimento
    $data = curl_exec($ch);
    curl_close($ch);
    return $data ? json_decode($data, true) : null;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: listar.php");
    exit;
}

$mensagem_erro = '';
$marcas = [];
$modelos = [];
$anos = [];
$fipeResult = null;

$selectedMarca = $_POST['marca'] ?? '';
$selectedModelo = $_POST['modelo'] ?? '';
$selectedAno = $_POST['ano'] ?? '';

// Busca o registro atual para preencher o formulário
$sql = "SELECT * FROM registros_clientes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$registro = $result->fetch_assoc();

if (!$registro) {
    header("Location: listar.php");
    exit;
}
$stmt->close();

// Preenche os selects com base nos dados do POST ou do registro existente
if (empty($_POST)) {
    // Se não houver POST, tentamos carregar os dados FIPE com base no que está no banco
    // Isso é opcional, mas melhora a experiência ao carregar a página pela primeira vez
}

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

// Lógica de UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_record'])) {

    // Se um novo veículo foi consultado via FIPE, use esses dados.
    if ($fipeResult) {
        $fipe_valor = $fipeResult['Valor'];
        $fipe_marca = $fipeResult['Marca'];
        $fipe_modelo = $fipeResult['Modelo'];
        $fipe_ano_modelo = $fipeResult['AnoModelo'];
        $fipe_combustivel = $fipeResult['Combustivel'];
        $fipe_codigo = $fipeResult['CodigoFipe'];
        $fipe_mes_referencia = $fipeResult['MesReferencia'];
    } else {
        // Se não, mantenha os dados do veículo que já estavam no registro.
        $fipe_valor = $registro['fipe_valor'];
        $fipe_marca = $registro['fipe_marca'];
        $fipe_modelo = $registro['fipe_modelo'];
        $fipe_ano_modelo = $registro['fipe_ano_modelo'];
        $fipe_combustivel = $registro['fipe_combustivel'];
        $fipe_codigo = $registro['fipe_codigo'];
        $fipe_mes_referencia = $registro['fipe_mes_referencia'];
    }

    $sql = "UPDATE registros_clientes SET 
                nome_cliente = ?,
                endereco_cliente = ?,
                telefone_cliente = ?,
                fipe_valor = ?,
                fipe_marca = ?,
                fipe_modelo = ?,
                fipe_ano_modelo = ?,
                fipe_combustivel = ?,
                fipe_codigo = ?,
                fipe_mes_referencia = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssisssi",
        $_POST['nome_cliente'],
        $_POST['endereco_cliente'],
        $_POST['telefone_cliente'],
        $fipe_valor,
        $fipe_marca,
        $fipe_modelo,
        $fipe_ano_modelo,
        $fipe_combustivel,
        $fipe_codigo,
        $fipe_mes_referencia,
        $id
    );

    if ($stmt->execute()) {
        header("Location: listar.php?updated=1");
        exit;
    } else {
        $mensagem_erro = "Erro ao atualizar: " . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Registro - AutoList</title>
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
        .container {
            max-width: 800px;
            margin: 20px auto;
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 10px;
            font-weight: 700;
        }
        .subtitle {
            text-align: center;
            color: #6c757d;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }
        h2 {
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
            color: #667eea;
            margin-top: 30px;
            font-weight: 600;
        }
        .form-group { margin-bottom: 20px; }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
        }
        input[type="text"], input[type="tel"], select, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        input[type="text"]:focus, input[type="tel"]:focus, select:focus, textarea:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            outline: none;
        }
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 10px;
            width: 100%;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        #fipe-result {
            margin-top: 25px;
            padding: 20px;
            background: linear-gradient(135deg, #e8f6fd 0%, #d1ecf1 100%);
            border-left: 5px solid #667eea;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        #fipe-result h3 {
            color: #667eea;
            margin-bottom: 15px;
            font-weight: 600;
        }
        #fipe-result p {
            margin: 8px 0;
            font-size: 1rem;
        }
        .card-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid #e9ecef;
        }
        .form-actions {
            display: flex;
            gap: 15px;
            align-items: center;
            margin-top: 30px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-car me-2"></i>AutoList
        </a>
        <div class="navbar-nav ms-auto">
            <a href="index.php" class="nav-link"><i class="fas fa-home me-1"></i>Dashboard</a>
            <a href="listar.php" class="nav-link"><i class="fas fa-list me-1"></i>Clientes</a>
        </div>
    </div>
</nav>

<div class="container">
    <h1><i class="fas fa-edit me-3"></i>Editar Registro</h1>
    <p class="subtitle">Atualize as informações do cliente e do veículo de interesse.</p>

    <?php if ($mensagem_erro): ?>
        <div class="alert alert-danger"><?= $mensagem_erro ?></div>
    <?php endif; ?>

    <div class="card-section">
        <h3><i class="fas fa-car-side me-2"></i>Veículo Atual Salvo</h3>
        <p class="mb-1"><strong><?= htmlspecialchars($registro['fipe_marca'] . ' ' . $registro['fipe_modelo']) ?></strong></p>
        <p class="text-muted"><strong>Ano:</strong> <?= htmlspecialchars($registro['fipe_ano_modelo']) ?> | <strong>Valor:</strong> <?= htmlspecialchars($registro['fipe_valor']) ?></p>
    </div>

    <form action="editar.php?id=<?= $id ?>" method="POST">

        <div class="card-section">
            <h2><i class="fas fa-user me-2"></i>Dados Pessoais</h2>
            <div class="form-group">
                <label for="nome_cliente">Nome Completo</label>
                <input type="text" id="nome_cliente" name="nome_cliente" value="<?= htmlspecialchars($registro['nome_cliente']) ?>" required>
            </div>
            <div class="form-group">
                <label for="endereco_cliente">Endereço Completo</label>
                <textarea id="endereco_cliente" name="endereco_cliente" rows="3"><?= htmlspecialchars($registro['endereco_cliente']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="telefone_cliente">Telefone</label>
                <input type="tel" id="telefone_cliente" name="telefone_cliente" value="<?= htmlspecialchars($registro['telefone_cliente']) ?>">
            </div>
        </div>

        <div class="card-section">
            <h2><i class="fas fa-search-dollar me-2"></i>Alterar Veículo (Consulta FIPE)</h2>
             <p class="text-muted mb-3">Para alterar o veículo, faça uma nova busca. Se não selecionar um novo veículo, os dados atuais serão mantidos.</p>

            <div class="form-group">
                <label for="marcas">Marca</label>
                <select id="marcas" name="marca" onchange="this.form.submit()">
                    <option value="">Selecione...</option>
                    <?php if ($marcas): foreach ($marcas as $marca): ?>
                        <option value="<?= $marca['codigo'] ?>" <?= ($selectedMarca == $marca['codigo']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($marca['nome']) ?>
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="modelos">Modelo</label>
                <select id="modelos" name="modelo" onchange="this.form.submit()" <?= !$selectedMarca ? 'disabled' : '' ?>>
                    <option value="">Selecione uma marca</option>
                    <?php if ($modelos): foreach ($modelos as $modelo): ?>
                        <option value="<?= $modelo['codigo'] ?>" <?= ($selectedModelo == $modelo['codigo']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($modelo['nome']) ?>
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="anos">Ano</label>
                <select id="anos" name="ano" onchange="this.form.submit()" <?= !$selectedModelo ? 'disabled' : '' ?>>
                    <option value="">Selecione um modelo</option>
                    <?php if ($anos): foreach ($anos as $ano): ?>
                        <option value="<?= $ano['codigo'] ?>" <?= ($selectedAno == $ano['codigo']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ano['nome']) ?>
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
        </div>

        <?php if ($fipeResult): ?>
            <div id="fipe-result">
                <h3><i class="fas fa-check-circle me-2"></i>Novo Veículo Selecionado</h3>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong><i class="fas fa-tag me-2"></i>Valor:</strong> <?= htmlspecialchars($fipeResult['Valor']) ?></p>
                        <p><strong><i class="fas fa-industry me-2"></i>Marca:</strong> <?= htmlspecialchars($fipeResult['Marca']) ?></p>
                        <p><strong><i class="fas fa-car me-2"></i>Modelo:</strong> <?= htmlspecialchars($fipeResult['Modelo']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><i class="fas fa-calendar me-2"></i>Ano:</strong> <?= htmlspecialchars($fipeResult['AnoModelo']) ?></p>
                        <p><strong><i class="fas fa-gas-pump me-2"></i>Combustível:</strong> <?= htmlspecialchars($fipeResult['Combustivel']) ?></p>
                        <p><strong><i class="fas fa-barcode me-2"></i>Código FIPE:</strong> <?= htmlspecialchars($fipeResult['CodigoFipe']) ?></p>
                    </div>
                </div>
                <p class="mt-3 mb-0"><strong><i class="fas fa-calendar-alt me-2"></i>Mês de Referência:</strong> <?= htmlspecialchars($fipeResult['MesReferencia']) ?></p>
            </div>
        <?php endif; ?>

        <div class="form-actions">
             <button type="submit" name="save_record" class="btn-submit">
                <i class="fas fa-save me-2"></i>Atualizar Registro
            </button>
            <a href="listar.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

</body>
</html>