<?php
// get_user_packages.php
session_start();

// Incluir o arquivo de conexão com o banco de dados
require_once 'config/db_connect.php';

// Verifica se o usuário está logado e é administrador
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    http_response_code(403);
    exit(json_encode([]));
}

$user_id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
if ($user_id === false || $user_id === null) {
    http_response_code(400);
    exit(json_encode([]));
}

try {
    // Buscar pacotes associados ao tutor
    $stmt = $pdo->prepare("
        SELECT sp.id, sp.name, sp.promotional_price 
        FROM service_packages sp
        JOIN package_tutors pt ON sp.id = pt.package_id
        WHERE pt.user_id = :user_id
        ORDER BY sp.name
    ");
    $stmt->execute([':user_id' => $user_id]);
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($packages);
} catch (PDOException $e) {
    http_response_code(500);
    exit(json_encode([]));
    error_log("Erro ao buscar pacotes: " . $e->getMessage());
}
?>