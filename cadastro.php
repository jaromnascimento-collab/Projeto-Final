<?php
require_once 'auth.php';
verificarLogin();

$localhost = "localhost";      // Servidor do banco de dados
$username = "root";            // Usuário padrão do XAMPP/WAMP (pode ser diferente)
$password = "root";            // Senha padrão do XAMPP/WAMP (geralmente vazia)
$database = "autolist";        // Nome do seu banco de dados

// --- FUNÇÃO PARA CHAMAR A API FIPE ---
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

// --- LÓGICA PARA SALVAR O REGISTRO ---
if (isset($_POST['save_record'])) {
    
    // CORREÇÃO 1: Nome da classe era 'mysqlii', corrigido para 'mysqli'.
    // CORREÇÃO 2: Adicionado '$' nas variáveis de conexão.
    $conn = new mysqli($localhost, $username, $password, $database);

    // CORREÇÃO 3: Removido o bloco 'if' duplicado que estava aqui.
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");

    $sql = "INSERT INTO registros_clientes (nome_cliente, endereco_cliente, telefone_cliente, fipe_valor, fipe_marca, fipe_modelo, fipe_ano_modelo, fipe_combustivel, fipe_codigo, fipe_mes_referencia) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
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
        
        // Garante que $fipeResult não é nulo antes de tentar acessar seus índices
        if ($fipeResult) {
            $fipe_valor = $fipeResult['Valor'];
            $fipe_marca = $fipeResult['Marca'];
            $fipe_modelo = $fipeResult['Modelo'];
            $fipe_ano_modelo = $fipeResult['AnoModelo'];
            $fipe_combustivel = $fipeResult['Combustivel'];
            $fipe_codigo = $fipeResult['CodigoFipe'];
            $fipe_mes_referencia = $fipeResult['MesReferencia'];
        } else {
            // Define valores padrão ou mostra um erro se $fipeResult for nulo
            $message = "<div class='message error'>❌ Erro: Dados da FIPE não encontrados para salvar.</div>";
            // Para a execução se não houver dados FIPE
            $fipe_valor = $fipe_marca = $fipe_modelo = $fipe_combustivel = $fipe_codigo = $fipe_mes_referencia = '';
            $fipe_ano_modelo = 0;
        }

        if ($message === '' && $stmt->execute()) {
            // Redireciona para a página de listagem após salvar
            header("Location: listar.php?success=1");
            exit();
        } else {
            $message = "<div class='message error'>❌ Erro ao salvar o registro: " . $stmt->error . "</div>";
        }
        // CORREÇÃO 4: Removida a linha de mensagem de sucesso que estava aqui fora do lugar.
        $stmt->close();
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
            margin-top: 20px;
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
        .success { 
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724; 
            border: 1px solid #c3e6cb; 
        }
        .error { 
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24; 
            border: 1px solid #f5c6cb; 
        }
        .back-link {
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
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-car me-2"></i>AutoList
        </a>
        <div class="navbar-nav ms-auto">
            <a href="index.php" class="nav-link">
                <i class="fas fa-home me-2"></i>Dashboard
            </a>
            <a href="listar.php" class="nav-link">
                <i class="fas fa-list me-2"></i>Clientes
            </a>
        </div>
    </div>
</nav>

<div class="container">
    <h1><i class="fas fa-user-plus me-3"></i>Captação de Clientes</h1>
    <p class="subtitle">Cadastre clientes interessados em veículos para futuras oportunidades de vendas</p>
    
    <?php echo $message; ?>

    <form id="main-form" method="POST" action="">
        
        <div class="card-section">
            <h2><i class="fas fa-user me-2"></i>Dados Pessoais</h2>
<div class="form-group">
    <label for="nome_cliente">Nome Completo</label>
    <input type="text" id="nome_cliente" name="nome_cliente"
           value="<?= htmlspecialchars($_POST['nome_cliente'] ?? '') ?>"
           required
           autocomplete="off"
           minlength="3"
           pattern=".*\s.*"
           title="Por favor, insira o nome completo (nome e sobrenome).">
</div>
<div class="form-group">
    <label for="endereco_cliente">Endereço Completo</label>
    <textarea id="endereco_cliente" name="endereco_cliente"
              placeholder="Ex: Rua das Flores, 123, Bairro Centro, Cidade-UF"
              required
              autocomplete="off"
              rows="3"><?= htmlspecialchars($_POST['endereco_cliente'] ?? '') ?></textarea>
</div>
<div class="form-group">
    <label for="telefone_cliente">Telefone</label>
    <input type="tel" id="telefone_cliente" name="telefone_cliente"
           inputmode="numeric"
           pattern="[0-9]*"
           oninput="this.value = this.value.replace(/\D/g, '')"
           value="<?= htmlspecialchars($_POST['telefone_cliente'] ?? '') ?>"
           autocomplete="off">
</div>

        </div>

        <div class="card-section">
            <h2><i class="fas fa-car me-2"></i>Interesse do Cliente (Consulta FIPE)</h2>
            <p class="text-muted mb-3">Selecione o veículo de interesse do cliente para obter informações atualizadas</p>

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

        <?php if ($fipeResult): ?>
            <div id="fipe-result">
                <h3><i class="fas fa-check-circle me-2"></i>Informações do Veículo</h3>
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
        </div>
        
        <input type="submit" name="save_record" value="💾 Cadastrar Cliente">
    </form>
</div>

</body>
</html>