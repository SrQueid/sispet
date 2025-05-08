<?php
session_start();

// Incluir o arquivo de conexão com o banco de dados
require_once 'db_connect.php';

// Definir o cabeçalho para retornar JSON
header('Content-Type: application/json');

// Verifica se o usuário está logado e é administrador
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    echo json_encode(['error' => 'Acesso não autorizado.']);
    exit;
}

try {
    // Buscar tutores (usuários que não são administradores)
    $query = "
        SELECT id, name, email, phone
        FROM users
        WHERE is_admin = 0
        ORDER BY name ASC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $tutores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retornar os tutores no formato esperado pelo JavaScript
    echo json_encode(['tutors' => $tutores]);
} catch (PDOException $e) {
    // Retornar erro em caso de falha
    echo json_encode(['error' => 'Erro ao buscar tutores: ' . $e->getMessage()]);
}

exit;
?>