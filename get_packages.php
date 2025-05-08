<?php
session_start();

// Desabilitar exibição de erros em produção
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Definir cabeçalhos para JSON e CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Verifica se o usuário é administrador
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado.']);
    exit;
}

// Incluir o arquivo de conexão com o banco de dados
try {
    require_once 'config/db_connect.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Falha ao conectar ao banco de dados: ' . $e->getMessage()]);
    exit;
}

// Verificar conexão com o banco
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['error' => 'Falha na conexão com o banco de dados']);
    exit;
}

$user_id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);

if (!$user_id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID do usuário não fornecido ou inválido']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT sp.id, sp.name
        FROM service_packages sp
        JOIN service_package_usage spu ON sp.id = spu.package_id
        WHERE spu.user_id = :user_id AND spu.remaining_quantity > 0
    ");
    $stmt->execute([':user_id' => $user_id]);
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($packages);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar pacotes: ' . $e->getMessage()]);
    error_log("Erro ao buscar pacotes: " . $e->getMessage());
}
?>