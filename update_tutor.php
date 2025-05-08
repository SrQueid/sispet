<?php
session_start();

// Incluir o arquivo de conexão com o banco de dados
require_once 'db_connect.php';

// Definir o cabeçalho para retornar JSON
header('Content-Type: application/json');

// Verifica se o usuário está logado e é administrador
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    echo json_encode(['success' => false, 'error' => 'Acesso não autorizado.']);
    exit;
}

// Receber os dados do POST
$input = json_decode(file_get_contents('php://input'), true);
$tutorId = isset($input['tutorId']) ? filter_var($input['tutorId'], FILTER_VALIDATE_INT) : null;
$name = isset($input['name']) ? trim($input['name']) : null;
$email = isset($input['email']) ? trim($input['email']) : null;
$phone = isset($input['phone']) ? trim($input['phone']) : null;

// Validar entradas
if (!$tutorId || !$name || !$email || !$phone || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Dados inválidos.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email, phone = :phone WHERE id = :id AND is_admin = 0");
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':phone' => $phone,
        ':id' => $tutorId
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Tutor não encontrado ou sem alterações.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erro ao atualizar tutor: ' . $e->getMessage()]);
}

exit;
?>