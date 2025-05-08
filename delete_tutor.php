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

if (!$tutorId) {
    echo json_encode(['success' => false, 'error' => 'ID do tutor inválido.']);
    exit;
}

try {
    // Iniciar transação
    $pdo->beginTransaction();

    // Excluir registros relacionados (pets, agendamentos, pacotes, etc.)
    $stmt = $pdo->prepare("DELETE FROM pets WHERE user_id = :id");
    $stmt->execute([':id' => $tutorId]);

    $stmt = $pdo->prepare("DELETE FROM appointments WHERE user_id = :id");
    $stmt->execute([':id' => $tutorId]);

    $stmt = $pdo->prepare("DELETE FROM package_tutors WHERE user_id = :id");
    $stmt->execute([':id' => $tutorId]);

    $stmt = $pdo->prepare("DELETE FROM service_package_usage WHERE user_id = :id");
    $stmt->execute([':id' => $tutorId]);

    // Excluir o tutor
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id AND is_admin = 0");
    $stmt->execute([':id' => $tutorId]);

    if ($stmt->rowCount() > 0) {
        $pdo->commit();
        echo json_encode(['success' => true]);
    } else {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Tutor não encontrado.']);
    }
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => 'Erro ao excluir tutor: ' . $e->getMessage()]);
}

exit;
?>