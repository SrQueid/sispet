<?php
// get_user_pets.php
session_start();
require_once 'config/db_connect.php';

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado: usuário não é administrador']);
    exit;
}

$user_id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
if ($user_id === false || $user_id === null) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de tutor inválido']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, pet_name AS name FROM pets WHERE user_id = :user_id ORDER BY pet_name");
    $stmt->execute([':user_id' => $user_id]);
    $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($pets);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar pets: ' . $e->getMessage()]);
    error_log("Erro ao buscar pets: " . $e->getMessage());
}
?>