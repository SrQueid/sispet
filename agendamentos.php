<?php
session_start();

// Definir o fuso horário para PDT
date_default_timezone_set('America/Los_Angeles');

// Include navbar com verificação
$navbar_path = 'navbar.php';
if (!file_exists($navbar_path)) {
    die('Erro: ' . $navbar_path . ' não encontrado.');
}
include $navbar_path;

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

// Verifica se o usuário é administrador
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
error_log("Usuário é administrador: " . ($is_admin ? 'Sim' : 'Não'));

// Incluir o arquivo de conexão com o banco de dados
$db_connect_path = 'db_connect.php';
if (!file_exists($db_connect_path)) {
    die('Erro: ' . $db_connect_path . ' não encontrado.');
}
require_once $db_connect_path;

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

// Gerar token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';

// Processar a criação de agendamento (apenas para tutores)
if (!$is_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_agendamento' && isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $user_id = $_SESSION['user_id'];
    $pet_id = filter_input(INPUT_POST, 'pet_id', FILTER_VALIDATE_INT);
    $service_id = filter_input(INPUT_POST, 'service_id', FILTER_VALIDATE_INT);
    $package_id = filter_input(INPUT_POST, 'package_id', FILTER_VALIDATE_INT);
    $scheduled_at = filter_input(INPUT_POST, 'scheduled_at', FILTER_SANITIZE_STRING);
    $transport_type = filter_input(INPUT_POST, 'transport_type', FILTER_SANITIZE_STRING);

    if (!$pet_id || !$service_id || !$scheduled_at) {
        $message = '<div class="alert alert-danger">Todos os campos obrigatórios devem ser preenchidos.</div>';
        writeLog($pdo, "Erro ao Criar Agendamento", "Campos obrigatórios não preenchidos", $user_id);
    } else {
        try {
            $scheduled_at = date('Y-m-d H:i:s', strtotime($scheduled_at));
            if (strtotime($scheduled_at) <= time()) {
                throw new Exception("A data do agendamento deve ser futura.");
            }

            $stmt = $pdo->prepare("SELECT id FROM pets WHERE id = :pet_id AND user_id = :user_id");
            $stmt->execute([':pet_id' => $pet_id, ':user_id' => $user_id]);
            if (!$stmt->fetch()) {
                throw new Exception("Pet não pertence ao tutor.");
            }

            if ($package_id) {
                $stmt = $pdo->prepare("SELECT remaining_quantity FROM service_package_usage WHERE package_id = :package_id AND user_id = :user_id AND service_id = :service_id");
                $stmt->execute([':package_id' => $package_id, ':user_id' => $user_id, ':service_id' => $service_id]);
                $usage = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$usage || $usage['remaining_quantity'] <= 0) {
                    throw new Exception("Pacote promocional não disponível ou esgotado para este serviço.");
                }

                $pdo->beginTransaction();
                $stmt = $pdo->prepare("INSERT INTO appointments (user_id, pet_id, service_id, package_id, scheduled_at, status, transport_type) VALUES (:user_id, :pet_id, :service_id, :package_id, :scheduled_at, 'PENDING', :transport_type)");
                $stmt->execute([
                    ':user_id' => $user_id,
                    ':pet_id' => $pet_id,
                    ':service_id' => $service_id,
                    ':package_id' => $package_id,
                    ':scheduled_at' => $scheduled_at,
                    ':transport_type' => $transport_type ?: null
                ]);

                $stmt = $pdo->prepare("UPDATE service_package_usage SET remaining_quantity = remaining_quantity - 1 WHERE package_id = :package_id AND user_id = :user_id AND service_id = :service_id");
                $stmt->execute([':package_id' => $package_id, ':user_id' => $user_id, ':service_id' => $service_id]);
                $pdo->commit();

                $message = '<div class="alert alert-success">Agendamento criado com sucesso usando o pacote promocional!</div>';
                writeLog($pdo, "Agendamento Criado", "Agendamento com pacote ID: $package_id", $user_id);
            } else {
                $stmt = $pdo->prepare("INSERT INTO appointments (user_id, pet_id, service_id, scheduled_at, status, transport_type) VALUES (:user_id, :pet_id, :service_id, :scheduled_at, 'PENDING', :transport_type)");
                $stmt->execute([
                    ':user_id' => $user_id,
                    ':pet_id' => $pet_id,
                    ':service_id' => $service_id,
                    ':scheduled_at' => $scheduled_at,
                    ':transport_type' => $transport_type ?: null
                ]);

                $message = '<div class="alert alert-success">Agendamento criado com sucesso!</div>';
                writeLog($pdo, "Agendamento Criado", "Agendamento sem pacote", $user_id);
            }

            header("Location: agendamentos.php");
            exit;
        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $message = '<div class="alert alert-danger">Erro ao criar agendamento: ' . htmlspecialchars($e->getMessage()) . '</div>';
            writeLog($pdo, "Erro ao Criar Agendamento", "Erro: " . $e->getMessage(), $user_id);
        }
    }
}

// Listar agendamentos
$agendamentos = [];
$services = [];
try {
    $query = "SELECT a.id, a.user_id, a.service_id, a.package_id, a.scheduled_at, a.status, a.pet_id, a.rejection_reason, u.name AS user_name, s.name AS service_name, sp.name AS package_name, p.pet_name AS pet_name FROM appointments a JOIN users u ON a.user_id = u.id JOIN pets p ON a.pet_id = p.id LEFT JOIN services s ON a.service_id = s.id LEFT JOIN service_packages sp ON a.package_id = sp.id";
    if (!$is_admin) {
        $query .= " WHERE a.user_id = :user_id";
    }
    $query .= " ORDER BY a.scheduled_at DESC";

    $stmt = $pdo->prepare($query);
    if (!$is_admin) {
        $stmt->execute([':user_id' => $_SESSION['user_id']]);
    } else {
        $stmt->execute();
    }
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT id, name FROM services");
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = '<div class="alert alert-danger">Erro ao listar agendamentos: ' . htmlspecialchars($e->getMessage()) . '</div>';
    writeLog($pdo, "Erro ao Listar Agendamentos", "Erro: " . $e->getMessage(), $_SESSION['user_id']);
}

// Processar aprovação (apenas para administradores)
if ($is_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'approve_agendamento' && isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $agendamento_id = filter_input(INPUT_POST, 'agendamento_id', FILTER_VALIDATE_INT);
    if ($agendamento_id === false || $agendamento_id === null) {
        $message = '<div class="alert alert-danger">ID de agendamento inválido.</div>';
        writeLog($pdo, "Aprovação de Agendamento Falhou", "ID de agendamento inválido", $_SESSION['user_id']);
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE appointments SET status = 'APPROVED' WHERE id = :id AND status = 'PENDING'");
            $stmt->execute([':id' => $agendamento_id]);
            if ($stmt->rowCount() === 0) {
                throw new Exception("Agendamento não encontrado ou não está no status PENDING.");
            }
            $message = '<div class="alert alert-success">Agendamento aprovado com sucesso!</div>';
            writeLog($pdo, "Agendamento Aprovado", "Agendamento ID: $agendamento_id", $_SESSION['user_id']);
            header("Location: agendamentos.php");
            exit;
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Erro ao aprovar agendamento: ' . htmlspecialchars($e->getMessage()) . '</div>';
            writeLog($pdo, "Erro ao Aprovar Agendamento", "Erro: " . $e->getMessage(), $_SESSION['user_id']);
        }
    }
}

// Processar conclusão (apenas para administradores)
if ($is_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'complete_agendamento' && isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $agendamento_id = filter_input(INPUT_POST, 'agendamento_id', FILTER_VALIDATE_INT);
    if ($agendamento_id === false || $agendamento_id === null) {
        $message = '<div class="alert alert-danger">ID de agendamento inválido.</div>';
        writeLog($pdo, "Conclusão de Agendamento Falhou", "ID de agendamento inválido", $_SESSION['user_id']);
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE appointments SET status = 'COMPLETED' WHERE id = :id AND status = 'APPROVED'");
            $stmt->execute([':id' => $agendamento_id]);
            if ($stmt->rowCount() === 0) {
                throw new Exception("Agendamento não encontrado ou não está no status APPROVED.");
            }
            $message = '<div class="alert alert-success">Agendamento marcado como concluído com sucesso!</div>';
            writeLog($pdo, "Agendamento Concluído", "Agendamento ID: $agendamento_id", $_SESSION['user_id']);
            header("Location: agendamentos.php");
            exit;
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Erro ao marcar agendamento como concluído: ' . htmlspecialchars($e->getMessage()) . '</div>';
            writeLog($pdo, "Erro ao Concluir Agendamento", "Erro: " . $e->getMessage(), $_SESSION['user_id']);
        }
    }
}

// Processar rejeição
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reject_agendamento' && isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $agendamento_id = filter_input(INPUT_POST, 'agendamento_id', FILTER_VALIDATE_INT);
    $rejection_reason = filter_input(INPUT_POST, 'rejection_reason', FILTER_SANITIZE_STRING) ?: null;
    if ($agendamento_id === false || $agendamento_id === null) {
        $message = '<div class="alert alert-danger">ID de agendamento inválido.</div>';
        writeLog($pdo, "Rejeição de Agendamento Falhou", "ID de agendamento inválido", $_SESSION['user_id']);
    } else {
        try {
            $stmt = $pdo->prepare("SELECT user_id, service_id, package_id, status FROM appointments WHERE id = :id");
            $stmt->execute([':id' => $agendamento_id]);
            $agendamento = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$agendamento) {
                throw new Exception("Agendamento não encontrado.");
            }

            if (!$is_admin && $agendamento['user_id'] != $_SESSION['user_id']) {
                throw new Exception("Você não tem permissão para rejeitar este agendamento.");
            }

            if (!in_array($agendamento['status'], ['PENDING', 'APPROVED'])) {
                throw new Exception("Este agendamento não pode ser rejeitado no status atual.");
            }

            if ($agendamento['package_id'] !== null) {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("UPDATE appointments SET status = 'REJECTED', rejection_reason = :rejection_reason WHERE id = :id");
                $stmt->execute([':rejection_reason' => $rejection_reason, ':id' => $agendamento_id]);

                $stmt = $pdo->prepare("UPDATE service_package_usage SET remaining_quantity = remaining_quantity + 1 WHERE package_id = :package_id AND user_id = :user_id AND service_id = :service_id");
                $stmt->execute([
                    ':package_id' => $agendamento['package_id'],
                    ':user_id' => $agendamento['user_id'],
                    ':service_id' => $agendamento['service_id']
                ]);
                $pdo->commit();

                $message = '<div class="alert alert-warning">Agendamento rejeitado com sucesso e quantidade restaurada no pacote!</div>';
                writeLog($pdo, "Agendamento Rejeitado", "Agendamento ID: $agendamento_id, Pacote ID: {$agendamento['package_id']}, Motivo: " . ($rejection_reason ?: 'Sem motivo'), $_SESSION['user_id']);
            } else {
                $stmt = $pdo->prepare("UPDATE appointments SET status = 'REJECTED', rejection_reason = :rejection_reason WHERE id = :id");
                $stmt->execute([':rejection_reason' => $rejection_reason, ':id' => $agendamento_id]);

                $message = '<div class="alert alert-warning">Agendamento rejeitado com sucesso!</div>';
                writeLog($pdo, "Agendamento Rejeitado", "Agendamento ID: $agendamento_id, Motivo: " . ($rejection_reason ?: 'Sem motivo'), $_SESSION['user_id']);
            }

            header("Location: agendamentos.php");
            exit;
        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $message = '<div class="alert alert-danger">Erro ao rejeitar agendamento: ' . htmlspecialchars($e->getMessage()) . '</div>';
            writeLog($pdo, "Erro ao Rejeitar Agendamento", "Erro: " . $e->getMessage(), $_SESSION['user_id']);
        }
    }
}

// Processar exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_agendamento' && isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $agendamento_id = filter_input(INPUT_POST, 'agendamento_id', FILTER_VALIDATE_INT);
    if ($agendamento_id === false || $agendamento_id === null) {
        $message = '<div class="alert alert-danger">ID de agendamento inválido.</div>';
        writeLog($pdo, "Exclusão de Agendamento Falhou", "ID de agendamento inválido", $_SESSION['user_id']);
    } else {
        try {
            $stmt = $pdo->prepare("SELECT user_id, service_id, package_id, status FROM appointments WHERE id = :id");
            $stmt->execute([':id' => $agendamento_id]);
            $agendamento = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$agendamento) {
                throw new Exception("Agendamento não encontrado.");
            }

            if ($agendamento['user_id'] != $_SESSION['user_id']) {
                throw new Exception("Você não tem permissão para excluir este agendamento.");
            }

            if ($agendamento['status'] === 'COMPLETED') {
                throw new Exception("Agendamentos concluídos não podem ser excluídos.");
            }

            if ($agendamento['package_id'] !== null) {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = :id");
                $stmt->execute([':id' => $agendamento_id]);

                $stmt = $pdo->prepare("UPDATE service_package_usage SET remaining_quantity = remaining_quantity + 1 WHERE package_id = :package_id AND user_id = :user_id AND service_id = :service_id");
                $stmt->execute([
                    ':package_id' => $agendamento['package_id'],
                    ':user_id' => $agendamento['user_id'],
                    ':service_id' => $agendamento['service_id']
                ]);
                $pdo->commit();

                $message = '<div class="alert alert-success">Agendamento excluído com sucesso e quantidade restaurada no pacote!</div>';
                writeLog($pdo, "Agendamento Excluído", "Agendamento ID: $agendamento_id, Pacote ID: {$agendamento['package_id']}", $_SESSION['user_id']);
            } else {
                $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = :id");
                $stmt->execute([':id' => $agendamento_id]);

                $message = '<div class="alert alert-success">Agendamento excluído com sucesso!</div>';
                writeLog($pdo, "Agendamento Excluído", "Agendamento ID: $agendamento_id", $_SESSION['user_id']);
            }

            header("Location: agendamentos.php");
            exit;
        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $message = '<div class="alert alert-danger">Erro ao excluir agendamento: ' . htmlspecialchars($e->getMessage()) . '</div>';
            writeLog($pdo, "Erro ao Excluir Agendamento", "Erro: " . $e->getMessage(), $_SESSION['user_id']);
        }
    }
}

// Verificar se os arquivos JavaScript existem
$agendamentos_js_path = 'js/agendamentos.js';
$forms_js_path = 'js/forms.js';
$agendamentos_js_exists = file_exists($agendamentos_js_path);
$forms_js_exists = file_exists($forms_js_path);

if (!$agendamentos_js_exists) {
    error_log("Arquivo js/agendamentos.js não encontrado.");
}
if (!$forms_js_exists) {
    error_log("Arquivo js/forms.js não encontrado.");
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Agendamentos - Petshop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4"><?php echo $is_admin ? 'Gerenciar Agendamentos - Petshop' : 'Meus Agendamentos'; ?></h1>

        <div class="text-end mb-3">
            <?php if ($is_admin): ?>
                <a href="services.php" class="btn btn-primary">Gerenciar Serviços</a>
            <?php else: ?>
                <button class="btn btn-primary" onclick="showAgendamentoForm()">Novo Agendamento</button>
            <?php endif; ?>
            <a href="index.php" class="btn btn-secondary">Voltar</a>
        </div>

        <?php if ($message): ?>
            <?php echo $message; ?>
        <?php endif; ?>

        <?php if (!$is_admin): ?>
            <div class="card mb-4" id="agendamento_form_card" style="display: none;">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Novo Agendamento</h5>
                </div>
                <div class="card-body">
                    <form id="tutor_agendamento_form" method="POST" action="">
                        <input type="hidden" name="action" value="add_agendamento">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <div class="mb-3">
                            <label for="pet_id" class="form-label">Pet</label>
                            <select class="form-select" id="pet_id" name="pet_id" required>
                                <option value="">Selecione um pet</option>
                                <?php
                                try {
                                    $stmt = $pdo->prepare("SELECT id, pet_name FROM pets WHERE user_id = :user_id");
                                    $stmt->execute([':user_id' => $_SESSION['user_id']]);
                                    $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($pets as $pet) {
                                        echo "<option value='" . htmlspecialchars($pet['id']) . "'>" . htmlspecialchars($pet['pet_name']) . "</option>";
                                    }
                                } catch (PDOException $e) {
                                    echo "<option value=''>Erro ao carregar pets: " . htmlspecialchars($e->getMessage()) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="service_id" class="form-label">Serviço</label>
                            <select class="form-select" id="service_id" name="service_id" required>
                                <option value="">Selecione um serviço</option>
                                <?php
                                foreach ($services as $service) {
                                    echo "<option value='" . htmlspecialchars($service['id']) . "' data-service-name='" . htmlspecialchars($service['name']) . "'>" . htmlspecialchars($service['name']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="package_id" class="form-label">Pacote Promocional (Opcional)</label>
                            <select class="form-select" id="package_id" name="package_id">
                                <option value="">Nenhum pacote</option>
                                <?php
                                try {
                                    $stmt = $pdo->prepare("SELECT sp.id, sp.name FROM service_packages sp JOIN service_package_usage spu ON sp.id = spu.package_id WHERE spu.user_id = :user_id AND spu.remaining_quantity > 0");
                                    $stmt->execute([':user_id' => $_SESSION['user_id']]);
                                    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($packages as $package) {
                                        echo "<option value='" . htmlspecialchars($package['id']) . "'>" . htmlspecialchars($package['name']) . "</option>";
                                    }
                                } catch (PDOException $e) {
                                    echo "<option value=''>Erro ao carregar pacotes: " . htmlspecialchars($e->getMessage()) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="scheduled_at" class="form-label">Data e Hora</label>
                            <input type="datetime-local" class="form-control" id="scheduled_at" name="scheduled_at" required>
                        </div>
                        <div class="mb-3" id="transport_type_field" style="display: none;">
                            <label for="transport_type" class="form-label">Tipo de Transporte</label>
                            <select class="form-select" id="transport_type" name="transport_type">
                                <option value="">Selecione o tipo de transporte</option>
                                <option value="ida">Ida</option>
                                <option value="volta">Volta</option>
                                <option value="ida_e_volta">Ida e Volta</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Agendar</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><?php echo $is_admin ? 'Agendamentos' : 'Meus Agendamentos'; ?></h5>
            </div>
            <div class="card-body">
                <?php if (empty($agendamentos)): ?>
                    <div class="alert alert-info">Nenhum agendamento encontrado.</div>
                <?php else: ?>
                    <!-- Depuração dos status dos agendamentos -->
                    <?php foreach ($agendamentos as $agendamento) {
                        error_log("Agendamento ID: " . $agendamento['id'] . ", Status: " . $agendamento['status']);
                    } ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tutor</th>
                                    <th>Pet</th>
                                    <th>Serviço</th>
                                    <th>Pacote</th>
                                    <th>Data e Hora</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($agendamentos as $agendamento): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($agendamento['id']); ?></td>
                                        <td><?php echo htmlspecialchars($agendamento['user_name']); ?></td>
                                        <td><?php echo htmlspecialchars($agendamento['pet_name']); ?></td>
                                        <td><?php echo htmlspecialchars($agendamento['service_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo $agendamento['package_name'] ? htmlspecialchars($agendamento['package_name']) : 'N/A'; ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($agendamento['scheduled_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($agendamento['status']); ?></td>
                                        <td>
                                            <?php if ($is_admin): ?>
                                                <!-- Depuração do status -->
                                                <?php error_log("Verificando ações para Agendamento ID: " . $agendamento['id'] . ", Status: " . $agendamento['status']); ?>
                                                <?php if ($agendamento['status'] === 'PENDING'): ?>
                                                    <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja aprovar este agendamento?');">
                                                        <input type="hidden" name="action" value="approve_agendamento">
                                                        <input type="hidden" name="agendamento_id" value="<?php echo htmlspecialchars($agendamento['id']); ?>">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                        <button type="submit" class="btn btn-success btn-sm">Aprovar</button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if ($agendamento['status'] === 'APPROVED'): ?>
                                                    <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja marcar este agendamento como concluído?');">
                                                        <input type="hidden" name="action" value="complete_agendamento">
                                                        <input type="hidden" name="agendamento_id" value="<?php echo htmlspecialchars($agendamento['id']); ?>">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                        <button type="submit" class="btn btn-success btn-sm">Concluído</button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if (in_array($agendamento['status'], ['PENDING', 'APPROVED'])): ?>
                                                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal_<?php echo htmlspecialchars($agendamento['id']); ?>">Rejeitar</button>
                                                    <div class="modal fade" id="rejectModal_<?php echo htmlspecialchars($agendamento['id']); ?>" tabindex="-1" aria-labelledby="rejectModalLabel_<?php echo htmlspecialchars($agendamento['id']); ?>" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="rejectModalLabel_<?php echo htmlspecialchars($agendamento['id']); ?>">Rejeitar Agendamento</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <form method="POST" action="">
                                                                        <input type="hidden" name="action" value="reject_agendamento">
                                                                        <input type="hidden" name="agendamento_id" value="<?php echo htmlspecialchars($agendamento['id']); ?>">
                                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                                        <div class="mb-3">
                                                                            <label for="rejection_reason_<?php echo htmlspecialchars($agendamento['id']); ?>" class="form-label">Motivo da Rejeição (opcional)</label>
                                                                            <textarea class="form-control" id="rejection_reason_<?php echo htmlspecialchars($agendamento['id']); ?>" name="rejection_reason" rows="3"></textarea>
                                                                        </div>
                                                                        <button type="submit" class="btn btn-warning">Confirmar Rejeição</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            <?php elseif ($agendamento['user_id'] == $_SESSION['user_id'] && $agendamento['status'] !== 'COMPLETED'): ?>
                                                <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir este agendamento?');">
                                                    <input type="hidden" name="action" value="delete_agendamento">
                                                    <input type="hidden" name="agendamento_id" value="<?php echo htmlspecialchars($agendamento['id']); ?>">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <?php if ($agendamentos_js_exists): ?>
        <script src="js/agendamentos.js" defer></script>
    <?php else: ?>
        <script>console.error("Erro: js/agendamentos.js não encontrado.");</script>
    <?php endif; ?>
    <?php if ($forms_js_exists): ?>
        <script src="js/forms.js" defer></script>
    <?php else: ?>
        <script>console.error("Erro: js/forms.js não encontrado.");</script>
    <?php endif; ?>
    <script>
        function showAgendamentoForm() {
            const formCard = document.getElementById('agendamento_form_card');
            if (formCard) {
                formCard.style.display = 'block';
            } else {
                console.error("Erro: Elemento 'agendamento_form_card' não encontrado.");
            }
        }
    </script>
</body>
</html>