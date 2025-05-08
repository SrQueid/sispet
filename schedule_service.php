<?php
session_start();
require_once 'config/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado.']);
    exit;
}

$user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
$pet_id = filter_input(INPUT_POST, 'pet_id', FILTER_VALIDATE_INT);
$package_id = filter_input(INPUT_POST, 'package_id', FILTER_VALIDATE_INT);
$service_id = filter_input(INPUT_POST, 'service_id', FILTER_VALIDATE_INT);
$scheduled_at = filter_input(INPUT_POST, 'scheduled_at', FILTER_SANITIZE_STRING);

if (!$user_id || !$pet_id || !$package_id || !$service_id || !$scheduled_at) {
    http_response_code(400);
    echo json_encode(['error' => 'Parâmetros inválidos.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Verificar se o pacote está associado ao tutor
    $stmt = $pdo->prepare("SELECT 1 FROM package_tutors WHERE package_id = :package_id AND user_id = :user_id");
    $stmt->execute([':package_id' => $package_id, ':user_id' => $user_id]);
    if (!$stmt->fetch()) {
        throw new Exception("Pacote não associado ao tutor.");
    }

    // Verificar se o serviço está no pacote
    $stmt = $pdo->prepare("SELECT quantity FROM service_package_items WHERE package_id = :package_id AND service_id = :service_id");
    $stmt->execute([':package_id' => $package_id, ':service_id' => $service_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$item) {
        throw new Exception("Serviço não está no pacote.");
    }
    $initial_quantity = $item['quantity'];

    // Verificar se já existe uma entrada em service_package_usage para este pacote, tutor, serviço e pet
    $stmt = $pdo->prepare("
        SELECT remaining_quantity 
        FROM service_package_usage 
        WHERE package_id = :package_id AND user_id = :user_id AND service_id = :service_id AND pet_id = :pet_id
    ");
    $stmt->execute([
        ':package_id' => $package_id,
        ':user_id' => $user_id,
        ':service_id' => $service_id,
        ':pet_id' => $pet_id
    ]);
    $usage = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usage) {
        // Inserir nova entrada
        $stmt = $pdo->prepare("
            INSERT INTO service_package_usage (package_id, user_id, service_id, pet_id, initial_quantity, remaining_quantity) 
            VALUES (:package_id, :user_id, :service_id, :pet_id, :initial_quantity, :remaining_quantity)
        ");
        $stmt->execute([
            ':package_id' => $package_id,
            ':user_id' => $user_id,
            ':service_id' => $service_id,
            ':pet_id' => $pet_id,
            ':initial_quantity' => $initial_quantity,
            ':remaining_quantity' => $initial_quantity - 1
        ]);
    } else {
        // Atualizar a quantidade restante
        $remaining_quantity = $usage['remaining_quantity'];
        if ($remaining_quantity <= 0) {
            throw new Exception("Quantidade restante esgotada para este serviço.");
        }
        $stmt = $pdo->prepare("
            UPDATE service_package_usage 
            SET remaining_quantity = remaining_quantity - 1 
            WHERE package_id = :package_id AND user_id = :user_id AND service_id = :service_id AND pet_id = :pet_id
        ");
        $stmt->execute([
            ':package_id' => $package_id,
            ':user_id' => $user_id,
            ':service_id' => $service_id,
            ':pet_id' => $pet_id
        ]);
    }

    // Registrar o agendamento
    $stmt = $pdo->prepare("
        INSERT INTO appointments (user_id, pet_id, service, package_id, scheduled_at, created_at, is_package) 
        VALUES (:user_id, :pet_id, (SELECT name FROM services WHERE id = :service_id), :package_id, :scheduled_at, NOW(), 1)
    ");
    $stmt->execute([
        ':user_id' => $user_id,
        ':pet_id' => $pet_id,
        ':service_id' => $service_id,
        ':package_id' => $package_id,
        ':scheduled_at' => $scheduled_at
    ]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Agendamento realizado com sucesso!']);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao processar agendamento: ' . $e->getMessage()]);
}
?>