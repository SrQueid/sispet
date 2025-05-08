<?php
session_start();
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado.']);
    exit;
}
try {
    require_once 'config/db_connect.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Falha ao conectar ao banco de dados: ' . $e->getMessage()]);
    exit;
}
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['error' => 'Falha na conexão com o banco de dados']);
    exit;
}
$search_term = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING);
if (empty($search_term) || strlen($search_term) < 2) {
    echo json_encode([]);
    exit;
}
try {
    $query = "
        SELECT 'tutor' AS type, id, name AS display_name, NULL AS tutor_id
        FROM users 
        WHERE name LIKE :search_term AND is_admin = 0
        UNION
        SELECT 'pet' AS type, id, pet_name AS display_name, user_id AS tutor_id
        FROM pets 
        WHERE pet_name LIKE :search_term
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':search_term' => "%$search_term%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($results);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar: ' . $e->getMessage()]);
    error_log("Erro ao buscar tutores/pets: " . $e->getMessage());
}
?>