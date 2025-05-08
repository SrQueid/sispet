<?php
session_start();

// Definir o fuso horário para PDT
date_default_timezone_set('America/Los_Angeles');

// Include navbar
include 'navbar.php';

// Verifica se o usuário está logado e é administrador
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: auth.php");
    exit;
}

// Incluir o arquivo de conexão com o banco de dados
require_once 'db_connect.php';

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

// Processar a criação de agendamento pelo administrador
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'admin_add_agendamento' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $pet_id = filter_input(INPUT_POST, 'pet_id', FILTER_VALIDATE_INT);
    $service_id = filter_input(INPUT_POST, 'service_id', FILTER_VALIDATE_INT);
    $package_id = filter_input(INPUT_POST, 'package_id', FILTER_VALIDATE_INT);
    $scheduled_at = filter_input(INPUT_POST, 'scheduled_at', FILTER_SANITIZE_STRING);
    $transport_type = filter_input(INPUT_POST, 'transport_type', FILTER_SANITIZE_STRING);

    // Validar entradas
    if (!$user_id || !$pet_id || !$service_id || !$scheduled_at) {
        $message = '<div class="alert alert-danger">Todos os campos obrigatórios devem ser preenchidos.</div>';
        writeLog($pdo, "Erro ao Criar Agendamento (Admin)", "Campos obrigatórios não preenchidos", $_SESSION['user_id']);
    } else {
        try {
            // Converter a data para o formato correto
            $scheduled_at = date('Y-m-d H:i:s', strtotime($scheduled_at));

            // Verificar se a data é futura
            if (strtotime($scheduled_at) <= time()) {
                throw new Exception("A data do agendamento deve ser futura.");
            }

            // Verificar se o pet pertence ao tutor selecionado
            $stmt = $pdo->prepare("SELECT id FROM pets WHERE id = :pet_id AND user_id = :user_id");
            $stmt->execute([':pet_id' => $pet_id, ':user_id' => $user_id]);
            if (!$stmt->fetch()) {
                throw new Exception("Pet não pertence ao tutor.");
            }

            // Se um pacote promocional foi selecionado, verificar a quantidade restante
            if ($package_id) {
                $stmt = $pdo->prepare("
                    SELECT remaining_quantity
                    FROM service_package_usage
                    WHERE package_id = :package_id AND user_id = :user_id AND service_id = :service_id
                ");
                $stmt->execute([
                    ':package_id' => $package_id,
                    ':user_id' => $user_id,
                    ':service_id' => $service_id
                ]);
                $usage = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$usage || $usage['remaining_quantity'] <= 0) {
                    throw new Exception("Pacote promocional não disponível ou esgotado para este serviço.");
                }

                // Iniciar transação
                $pdo->beginTransaction();

                // Inserir o agendamento
                $stmt = $pdo->prepare("
                    INSERT INTO appointments (user_id, pet_id, service_id, package_id, scheduled_at, status, transport_type)
                    VALUES (:user_id, :pet_id, :service_id, :package_id, :scheduled_at, 'PENDING', :transport_type)
                ");
                $stmt->execute([
                    ':user_id' => $user_id,
                    ':pet_id' => $pet_id,
                    ':service_id' => $service_id,
                    ':package_id' => $package_id ?: null,
                    ':scheduled_at' => $scheduled_at,
                    ':transport_type' => $transport_type ?: null
                ]);

                // Decrementar a quantidade restante no pacote
                $stmt = $pdo->prepare("
                    UPDATE service_package_usage
                    SET remaining_quantity = remaining_quantity - 1
                    WHERE package_id = :package_id AND user_id = :user_id AND service_id = :service_id
                ");
                $stmt->execute([
                    ':package_id' => $package_id,
                    ':user_id' => $user_id,
                    ':service_id' => $service_id
                ]);

                // Commit da transação
                $pdo->commit();

                $message = '<div class="alert alert-success">Agendamento criado com sucesso usando o pacote promocional!</div>';
                writeLog($pdo, "Agendamento Criado (Admin)", "Agendamento com pacote ID: $package_id", $_SESSION['user_id']);
            } else {
                // Inserir o agendamento sem pacote
                $stmt = $pdo->prepare("
                    INSERT INTO appointments (user_id, pet_id, service_id, scheduled_at, status, transport_type)
                    VALUES (:user_id, :pet_id, :service_id, :scheduled_at, 'PENDING', :transport_type)
                ");
                $stmt->execute([
                    ':user_id' => $user_id,
                    ':pet_id' => $pet_id,
                    ':service_id' => $service_id,
                    ':scheduled_at' => $scheduled_at,
                    ':transport_type' => $transport_type ?: null
                ]);

                $message = '<div class="alert alert-success">Agendamento criado com sucesso!</div>';
                writeLog($pdo, "Agendamento Criado (Admin)", "Agendamento sem pacote", $_SESSION['user_id']);
            }

            header("Location: admin.php");
            exit;
        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $message = '<div class="alert alert-danger">Erro ao criar agendamento: ' . $e->getMessage() . '</div>';
            writeLog($pdo, "Erro ao Criar Agendamento (Admin)", "Erro: {$e->getMessage()}", $_SESSION['user_id']);
        }
    }
}

// Listar tutores
$tutores = [];
try {
    $query = "
        SELECT id, name
        FROM users
        WHERE is_admin = 0
        ORDER BY name ASC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $tutores = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = '<div class="alert alert-danger">Erro ao listar tutores: ' . $e->getMessage() . '</div>';
    writeLog($pdo, "Erro ao Listar Tutores (Admin)", "Erro: {$e->getMessage()}", $_SESSION['user_id']);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área de Administrador - Petshop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Área de Administrador - Petshop</h1>

        <div class="text-end mb-3">
            <a href="agendamentos.php" class="btn btn-primary">Gerenciar Agendamentos</a>
            <a href="services.php" class="btn btn-primary">Gerenciar Serviços</a>
            <a href="index.php" class="btn btn-secondary">Voltar</a>
        </div>

        <?php if ($message): ?>
            <div class="mb-4"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Pesquisa de Tutor ou Pet -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Pesquisar Tutor ou Pet</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="search_tutor_pet" class="form-label">Buscar Tutor ou Pet</label>
                    <input type="text" class="form-control" id="search_tutor_pet" placeholder="Digite o nome do tutor ou pet">
                </div>
            </div>
        </div>

        <!-- Formulário de Agendamento -->
        <div class="card mb-4" id="agendamento_form_card" style="display: none;">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Novo Agendamento</h5>
            </div>
            <div class="card-body">
                <form id="admin_agendamento_form" method="POST" action="">
                    <input type="hidden" name="action" value="admin_add_agendamento">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" id="selected_user_id" name="user_id">
                    <input type="hidden" id="selected_pet_id" name="pet_id">
                    <div class="mb-3">
                        <label for="selected_tutor_name" class="form-label">Tutor</label>
                        <input type="text" class="form-control" id="selected_tutor_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="selected_pet_name" class="form-label">Pet</label>
                        <input type="text" class="form-control" id="selected_pet_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="service" class="form-label">Serviço</label>
                        <select class="form-select" id="service" name="service_id" required>
                            <option value="">Selecione um serviço</option>
                            <?php
                            try {
                                $stmt = $pdo->prepare("SELECT id, name FROM services");
                                $stmt->execute();
                                $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($services as $service) {
                                    echo "<option value='{$service['id']}'>" . htmlspecialchars($service['name']) . "</option>";
                                }
                            } catch (PDOException $e) {
                                echo "<option value=''>Erro ao carregar serviços: " . htmlspecialchars($e->getMessage()) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="package_id" class="form-label">Pacote Promocional (Opcional)</label>
                        <select class="form-select" id="package_id" name="package_id">
                            <option value="">Nenhum pacote</option>
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

        <!-- Tabela de Tutores -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Lista de Tutores</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="tutores_table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tutores)): ?>
                                <tr>
                                    <td colspan="3" class="text-center">Nenhum tutor encontrado.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tutores as $tutor): ?>
                                    <tr data-tutor-id="<?php echo $tutor['id']; ?>" data-tutor-name="<?php echo htmlspecialchars($tutor['name']); ?>">
                                        <td><?php echo htmlspecialchars($tutor['id']); ?></td>
                                        <td><?php echo htmlspecialchars($tutor['name']); ?></td>
                                        <td>
                                            <button class="btn btn-primary btn-sm" onclick="showPetsModal(<?php echo $tutor['id']; ?>, '<?php echo htmlspecialchars($tutor['name']); ?>')">Ver Pets</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal de Pets -->
        <div class="modal fade" id="petsModal" tabindex="-1" aria-labelledby="petsModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="petsModalLabel">Pets do Tutor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="pets_list">
                            <p>Carregando pets...</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/forms.js"></script>
    <script>
        // Função para buscar tutores e pets
        document.getElementById('search_tutor_pet').addEventListener('input', async function() {
            const query = this.value.trim();
            const tutoresTableBody = document.querySelector('#tutores_table tbody');

            if (query.length < 2) {
                // Recarregar a lista completa de tutores
                try {
                    const response = await fetch('search_tutor_pet.php?q=');
                    if (!response.ok) {
                        throw new Error(`Erro na requisição: ${response.status} ${response.statusText}`);
                    }
                    const results = await response.json();

                    if (results.error) {
                        tutoresTableBody.innerHTML = `<tr><td colspan="3" class="text-center text-danger">Erro: ${results.error}</td></tr>`;
                        return;
                    }

                    updateTutoresTable(results);
                } catch (error) {
                    tutoresTableBody.innerHTML = `<tr><td colspan="3" class="text-center text-danger">Erro na requisição: ${error.message}</td></tr>`;
                }
                return;
            }

            try {
                const response = await fetch(`search_tutor_pet.php?q=${encodeURIComponent(query)}`);
                if (!response.ok) {
                    throw new Error(`Erro na requisição: ${response.status} ${response.statusText}`);
                }
                const results = await response.json();

                if (results.error) {
                    tutoresTableBody.innerHTML = `<tr><td colspan="3" class="text-center text-danger">Erro: ${results.error}</td></tr>`;
                    return;
                }

                updateTutoresTable(results);
            } catch (error) {
                tutoresTableBody.innerHTML = `<tr><td colspan="3" class="text-center text-danger">Erro na requisição: ${error.message}</td></tr>`;
            }
        });

        // Função para atualizar a tabela de tutores
        function updateTutoresTable(results) {
            const tutoresTableBody = document.querySelector('#tutores_table tbody');
            const tutores = results.filter(item => item.type === 'tutor');

            if (tutores.length === 0) {
                tutoresTableBody.innerHTML = '<tr><td colspan="3" class="text-center">Nenhum tutor encontrado.</td></tr>';
                return;
            }

            tutoresTableBody.innerHTML = tutores.map(tutor => `
                <tr data-tutor-id="${tutor.id}" data-tutor-name="${tutor.display_name}">
                    <td>${tutor.id}</td>
                    <td>${tutor.display_name}</td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="showPetsModal(${tutor.id}, '${tutor.display_name}')">Ver Pets</button>
                    </td>
                </tr>
            `).join('');
        }

        // Função para abrir o modal e listar os pets do tutor
        async function showPetsModal(tutorId, tutorName) {
            const modalTitle = document.getElementById('petsModalLabel');
            const petsList = document.getElementById('pets_list');
            modalTitle.textContent = `Pets do Tutor: ${tutorName}`;

            try {
                const response = await fetch(`get_pets.php?user_id=${tutorId}`);
                if (!response.ok) {
                    throw new Error(`Erro na requisição: ${response.status} ${response.statusText}`);
                }
                const data = await response.json();

                if (data.error) {
                    petsList.innerHTML = `<p class="text-danger">Erro: ${data.error}</p>`;
                    return;
                }

                const pets = data.pets || [];
                if (pets.length === 0) {
                    petsList.innerHTML = '<p>Nenhum pet encontrado para este tutor.</p>';
                    return;
                }

                petsList.innerHTML = pets.map(pet => `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>${pet.name}</span>
                        <button class="btn btn-success btn-sm" onclick="selectPetForAgendamento(${tutorId}, '${tutorName}', ${pet.id}, '${pet.name}')">Agendar</button>
                    </div>
                `).join('');
            } catch (error) {
                petsList.innerHTML = `<p class="text-danger">Erro ao carregar pets: ${error.message}</p>`;
            }

            const modal = new bootstrap.Modal(document.getElementById('petsModal'));
            modal.show();
        }

        // Função para preencher o formulário de agendamento
        async function selectPetForAgendamento(tutorId, tutorName, petId, petName) {
            // Preencher os campos do formulário
            document.getElementById('selected_user_id').value = tutorId;
            document.getElementById('selected_pet_id').value = petId;
            document.getElementById('selected_tutor_name').value = tutorName;
            document.getElementById('selected_pet_name').value = petName;

            // Carregar pacotes promocionais
            const packageSelect = document.getElementById('package_id');
            packageSelect.innerHTML = '<option value="">Nenhum pacote</option>';

            try {
                const response = await fetch(`get_packages.php?user_id=${tutorId}`);
                if (!response.ok) {
                    throw new Error(`Erro na requisição: ${response.status} ${response.statusText}`);
                }
                const packages = await response.json();

                if (packages.error) {
                    packageSelect.innerHTML += `<option value="">Erro: ${packages.error}</option>`;
                    return;
                }

                packages.forEach(pkg => {
                    const option = document.createElement('option');
                    option.value = pkg.id;
                    option.textContent = pkg.name;
                    packageSelect.appendChild(option);
                });
            } catch (error) {
                packageSelect.innerHTML += `<option value="">Erro ao carregar pacotes: ${error.message}</option>`;
            }

            // Exibir o formulário de agendamento
            document.getElementById('agendamento_form_card').style.display = 'block';

            // Fechar o modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('petsModal'));
            modal.hide();
        }
    </script>
</body>
</html>