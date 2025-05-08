<?php
session_start();

// Cabeçalhos para evitar cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Include navbar with error handling
try {
    include 'navbar.php';
} catch (Exception $e) {
    error_log("Failed to include navbar.php: " . $e->getMessage());
    die('<div class="alert alert-danger">Erro ao carregar o menu de navegação: ' . $e->getMessage() . '</div>');
}

// Incluir o arquivo de conexão com o banco de dados
require_once 'db_connect.php';

// Função para verificar login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Função para registrar logs no banco
function writeLog($pdo, $action, $details, $user_id = null) {
    try {
        $stmt = $pdo->prepare("INSERT INTO logs (action, user_id, details) VALUES (:action, :user_id, :details)");
        $stmt->execute([
            ':action' => $action,
            ':user_id' => $user_id,
            ':details' => $details
        ]);
    } catch (PDOException $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }
}

// Processa logout
if (isset($_GET['logout'])) {
    if (isLoggedIn()) {
        writeLog($pdo, "Logout", "Email: {$_SESSION['email']}", $_SESSION['user_id']);
    }
    session_destroy();
    header("Location: auth.php");
    exit;
}

$message = '';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petshop PetsLove - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <!-- Mensagem -->
        <?php if (isset($_SESSION['message'])): ?>
            <?php echo $_SESSION['message']; ?>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
            <?php echo $message; ?>
        <?php endif; ?>

        <!-- Conteúdo Principal -->
        <div class="header-section">
            <h1>Bem-vindo ao Petshop PetsLove!</h1>
        </div>

        <!-- Quem Somos -->
        <h2 class="section-title">Quem Somos</h2>
        <p>O <strong>PetsLove</strong> é mais do que um petshop: é um lugar onde o amor pelos animais ganha vida! Nossa missão é oferecer serviços e produtos de qualidade para garantir o bem-estar e a felicidade do seu pet.</p>

        <!-- Nossos Serviços -->
        <h2 class="section-title">Nossos Serviços</h2>
        <ul>
            <li><strong>Banho & Tosa:</strong> Profissionais experientes para deixar seu pet sempre limpo e estiloso.</li>
            <li><strong>Veterinário:</strong> Atendimento especializado para cuidar da saúde do seu melhor amigo.</li>
            <li><strong>Taxi Dog:</strong> Transporte seguro e confortável para levar e trazer seu pet.</li>
            <li><strong>Loja de Produtos:</strong> Alimentos, brinquedos e acessórios para todas as necessidades.</li>
        </ul>

        <!-- Por que escolher a PetsLove? -->
        <h2 class="section-title">Por que escolher a PetsLove?</h2>
        <ul>
            <li>Atendimento personalizado e carinho com cada pet.</li>
            <li>Produtos e serviços de alta qualidade.</li>
            <li>Ambiente seguro e aconchegante para o seu bichinho.</li>
            <li>Equipe apaixonada por animais!</li>
        </ul>

        <!-- Entre em Contato -->
        <h2 class="section-title">Entre em Contato</h2>
        <div class="contact-info">
            <p>📍 <strong>Endereço:</strong> Rua dos Pets, 123 - Cidade Feliz</p>
            <p>📞 <strong>Telefone:</strong> (11) 98765-4321</p>
            <p>🌐 <strong>Site:</strong> <a href="http://www.petsloveCOPIAR.com.br" target="_blank">www.petslove.com.br</a></p>
            <p>📩 <strong>E-mail:</strong> <a href="mailto:contato@petslove.com.br">contato@petslove.com.br</a></p>
        </div>

        <!-- Botão de Agendamento -->
        <div class="text-center">
            <?php if (isLoggedIn()): ?>
                <a href="agendamentos.php" class="btn btn-primary btn-lg btn-agendamento">Fazer um Agendamento</a>
            <?php else: ?>
                <a href="auth.php" class="btn btn-primary btn-lg btn-agendamento">Faça login para agendar</a>
            <?php endif; ?>
        </div>
        <p class="mt-4 text-center">Venha nos visitar e proporcione o melhor para o seu pet! 🐶🐱💙</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>
</body>
</html>