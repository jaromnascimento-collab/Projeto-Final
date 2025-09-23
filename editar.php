<?php
require_once 'auth.php';
verificarLogin();
require_once 'conexao.php';

// --- FUNÇÃO PARA CHAMAR A API FIPE ---
function callFipeApi(string $endpoint) {
    $url = "https://parallelum.com.br/fipe/api/v1/carros/{$endpoint}";
    $data = @file_get_contents($url);
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
    // Se um novo veículo foi selecionado, use os dados da FIPE. Senão, mantenha os dados antigos.
    $dados_veiculo_para_salvar = $fipeResult ? [
        'valor' => $fipeResult['Valor'],
        'marca' => $fipeResult['Marca'],
        'modelo' => $fipeResult['Modelo'],
        'ano' => $fipeResult['AnoModelo'],
        'combustivel' => $fipeResult['Combustivel'],
        'codigo' => $fipeResult['CodigoFipe'],
        'mes_referencia' => $fipeResult['MesReferencia']
    ] : [ // Caso contrário, usa os dados que já estavam no registro
        'valor' => $_POST['fipe_valor_hidden'],
        'marca' => $_POST['fipe_marca_hidden'],
        'modelo' => $_POST['fipe_modelo_hidden'],
        'ano' => $_POST['fipe_ano_modelo_hidden'],
        'combustivel' => $_POST['fipe_combustivel_hidden'],
        'codigo' => $_POST['fipe_codigo_hidden'],
        'mes_referencia' => $_POST['fipe_mes_referencia_hidden']
    ];

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
    
    $stmt->bind_param("ssssssisssi", 
        $_POST['nome_cliente'],
        $_POST['endereco_cliente'],
        $_POST['telefone_cliente'],
        $dados_veiculo_para_salvar['valor'],
        $dados_veiculo_para_salvar['marca'],
        $dados_veiculo_para_salvar['modelo'],
        $dados_veiculo_para_salvar['ano'],
        $dados_veiculo_para_salvar['combustivel'],
        $dados_veiculo_para_salvar['codigo'],
        $dados_veiculo_para_salvar['mes_referencia'],
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
$conn->close();

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Registro</title>
    <style>
        body { 
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
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
            margin-bottom: 30px;
            font-weight: 700;
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
        input[type="submit"] { 
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
        input[type="submit"]:hover { 
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
            margin-top: 0;
            margin-bottom: 15px;
            font-weight: 600;
        }
        #fipe-result p { 
            margin: 8px 0; 
            font-size: 1rem;
        }
        .message { 
            padding: 15px; 
            border-radius: 8px; 
            margin: 20px 0; 
            text-align: center; 
            font-weight: 600;
            font-size: 1rem;
        }
        .error { 
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24; 
            border: 1px solid #f5c6cb; 
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .back-link:hover {
            color: #5a6fd8;
        }
        .card-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }
        .card-section h4 {
            margin-top: 0;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Editar Registro</h1>
        
        <?php if ($mensagem_erro): ?>
            <div class="message error"><?= $mensagem_erro ?></div>
        <?php endif; ?>

        <div class="card-section">
            <h4>Veículo Atual</h4>
            <p><strong><?= htmlspecialchars($registro['fipe_marca'] . ' ' . $registro['fipe_modelo']) ?></strong></p>
            <p><strong>Ano:</strong> <?= htmlspecialchars($registro['fipe_ano_modelo']) ?> | <strong>Valor:</strong> <?= htmlspecialchars($registro['fipe_valor']) ?></p>
        </div>

        <form action="editar.php?id=<?= $id ?>" method="POST">
            <input type="hidden" name="fipe_valor_hidden" value="<?= htmlspecialchars($registro['fipe_valor']) ?>">
            <input type="hidden" name="fipe_marca_hidden" value="<?= htmlspecialchars($registro['fipe_marca']) ?>">
            <input type="hidden" name="fipe_modelo_hidden" value="<?= htmlspecialchars($registro['fipe_modelo']) ?>">
            <input type="hidden" name="fipe_ano_modelo_hidden" value="<?= htmlspecialchars($registro['fipe_ano_modelo']) ?>">
            <input type="hidden" name="fipe_combustivel_hidden" value="<?= htmlspecialchars($registro['fipe_combustivel']) ?>">
            <input type="hidden" name="fipe_codigo_hidden" value="<?= htmlspecialchars($registro['fipe_codigo']) ?>">
            <input type="hidden" name="fipe_mes_referencia_hidden" value="<?= htmlspecialchars($registro['fipe_mes_referencia']) ?>">

            <h2>Dados Pessoais</h2>
            <div class="form-group">
                <label for="nome_cliente">Nome do Cliente:</label>
                <input type="text" name="nome_cliente" id="nome_cliente" value="<?= htmlspecialchars($registro['nome_cliente']) ?>" required>
            </div>
            <div class="form-group">
                <label for="endereco_cliente">Endereço:</label>
                <textarea name="endereco_cliente" id="endereco_cliente" rows="3"><?= htmlspecialchars($registro['endereco_cliente']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="telefone_cliente">Telefone:</label>
                <input type="tel" name="telefone_cliente" id="telefone_cliente" value="<?= htmlspecialchars($registro['telefone_cliente']) ?>">
            </div>

            <h2>Alterar Veículo (Opcional)</h2>

            <div class="form-group">
                <label for="marcas">Marca</label>
                <select id="marcas" name="marca" onchange="this.form.submit()">
                    <option value="">Selecione para alterar...</option>
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

            <?php if ($fipeResult): ?>
                <div id="fipe-result">
                    <h3>Novo Veículo Selecionado</h3>
                    <p><strong>Valor:</strong> <?= htmlspecialchars($fipeResult['Valor']) ?></p>
                    <p><strong>Marca:</strong> <?= htmlspecialchars($fipeResult['Marca']) ?></p>
                    <p><strong>Modelo:</strong> <?= htmlspecialchars($fipeResult['Modelo']) ?></p>
                    <p><strong>Ano:</strong> <?= htmlspecialchars($fipeResult['AnoModelo']) ?></p>
                    <p><strong>Combustível:</strong> <?= htmlspecialchars($fipeResult['Combustivel']) ?></p>
                    <p><strong>Código FIPE:</strong> <?= htmlspecialchars($fipeResult['CodigoFipe']) ?></p>
                    <p><strong>Mês de Referência:</strong> <?= htmlspecialchars($fipeResult['MesReferencia']) ?></p>
                </div>
            <?php endif; ?>

            <input type="submit" name="save_record" value="Atualizar Registro">
            <a href="listar.php" class="back-link">Cancelar</a>
        </form>
    </div>
</body>
</html>