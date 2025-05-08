// Mostrar/esconder o campo de tipo de transporte com base no serviço selecionado
document.getElementById('service')?.addEventListener('change', function() {
    const transportTypeField = document.getElementById('transport_type_field');
    if (this.value === 'TaxiPet') {
        transportTypeField.style.display = 'block';
    } else {
        transportTypeField.style.display = 'none';
        document.getElementById('transport_type').value = ''; // Limpa a seleção
    }
});

// Alerta sobre cobrança de horários de transporte
document.getElementById('transport_type')?.addEventListener('change', function() {
    if (this.value) {
        alert('Atenção: Os horários de transporte (busca e devolução) terão cobrança adicional.');
    }
});

// Validação do formulário de login no lado do cliente
document.getElementById('loginForm')?.addEventListener('submit', function(event) {
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    let errorMessage = '';

    if (!email) {
        errorMessage += 'O campo Email é obrigatório.\n';
    }
    if (!password) {
        errorMessage += 'O campo Senha é obrigatório.\n';
    }

    if (errorMessage) {
        event.preventDefault(); // Impede o envio do formulário
        alert(errorMessage);
    }
});

// Validação do formulário de recuperação de senha no lado do cliente
document.getElementById('forgotPasswordForm')?.addEventListener('submit', function(event) {
    const email = document.getElementById('email').value.trim();
    const errorMessageDiv = document.getElementById('errorMessage');
    let errorMessage = '';

    // Limpa mensagens de erro anteriores
    errorMessageDiv.style.display = 'none';
    errorMessageDiv.textContent = '';

    if (!email) {
        errorMessage += 'O campo Email é obrigatório.';
    } else {
        // Verifica se o email tem um formato válido
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            errorMessage += 'Por favor, insira um email válido.';
        }
    }

    if (errorMessage) {
        event.preventDefault(); // Impede o envio do formulário
        errorMessageDiv.textContent = errorMessage;
        errorMessageDiv.style.display = 'block';
    }
});

// Validação do formulário de cadastro de administrador
document.getElementById('addAdminForm')?.addEventListener('submit', function(event) {
    const name = document.getElementById('name').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const address = document.getElementById('address').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const errorMessageDiv = document.getElementById('addAdminErrorMessage');
    let errorMessage = '';

    // Limpa mensagens de erro anteriores
    errorMessageDiv.style.display = 'none';
    errorMessageDiv.textContent = '';

    if (!name) {
        errorMessage += 'O campo Nome é obrigatório.<br>';
    }
    if (!phone) {
        errorMessage += 'O campo Telefone é obrigatório.<br>';
    }
    if (!address) {
        errorMessage += 'O campo Endereço é obrigatório.<br>';
    }
    if (!email) {
        errorMessage += 'O campo Email é obrigatório.<br>';
    } else {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            errorMessage += 'Por favor, insira um email válido.<br>';
        }
    }
    if (!password) {
        errorMessage += 'O campo Senha é obrigatório.<br>';
    }

    if (errorMessage) {
        event.preventDefault(); // Impede o envio do formulário
        errorMessageDiv.innerHTML = errorMessage;
        errorMessageDiv.style.display = 'block';
    }
});

// Validação dos formulários de edição de usuário (nos modais)
document.querySelectorAll('.editUserForm')?.forEach(form => {
    form.addEventListener('submit', function(event) {
        const userId = form.querySelector('input[name="user_id"]').value;
        const name = form.querySelector(`#name_${userId}`).value.trim();
        const phone = form.querySelector(`#phone_${userId}`).value.trim();
        const address = form.querySelector(`#address_${userId}`).value.trim();
        const email = form.querySelector(`#email_${userId}`).value.trim();
        const role = form.querySelector(`#role_${userId}`).value;
        const errorMessageDiv = form.querySelector(`#editUserErrorMessage_${userId}`);
        let errorMessage = '';

        // Limpa mensagens de erro anteriores
        errorMessageDiv.style.display = 'none';
        errorMessageDiv.textContent = '';

        if (!name) {
            errorMessage += 'O campo Nome é obrigatório.<br>';
        }
        if (!phone) {
            errorMessage += 'O campo Telefone é obrigatório.<br>';
        }
        if (!address) {
            errorMessage += 'O campo Endereço é obrigatório.<br>';
        }
        if (!email) {
            errorMessage += 'O campo Email é obrigatório.<br>';
        } else {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                errorMessage += 'Por favor, insira um email válido.<br>';
            }
        }
        if (!role) {
            errorMessage += 'O campo Role é obrigatório.<br>';
        }

        if (errorMessage) {
            event.preventDefault(); // Impede o envio do formulário
            errorMessageDiv.innerHTML = errorMessage;
            errorMessageDiv.style.display = 'block';
        }
    });
});

// Função para preencher o formulário ao editar um pet
function editPet(pet) {
    document.getElementById('formAction').value = 'edit_pet';
    document.getElementById('petId').value = pet.id;
    document.getElementById('pet_name').value = pet.pet_name;
    
    // Preencher o campo "Tipo do Pet"
    const petTypeSelect = document.getElementById('pet_type');
    petTypeSelect.value = pet.pet_type || '';
    petTypeSelect.disabled = false; // Garantir que o campo não esteja desabilitado
    
    document.getElementById('pet_breed').value = pet.pet_breed || '';
    
    // Preencher o campo "Porte do Pet"
    const petSizeSelect = document.getElementById('pet_size');
    petSizeSelect.value = pet.pet_size || '';
    petSizeSelect.disabled = false; // Garantir que o campo não esteja desabilitado
    
    document.getElementById('tutor_phone').value = pet.tutor_phone || '';
    document.getElementById('petModalLabel').innerText = 'Editar Pet';
    document.getElementById('submitButton').innerText = 'Salvar Alterações';
    document.getElementById('pet_photo').value = ''; // Limpa o campo de upload
    document.getElementById('photoPreview').style.display = 'none';
    document.getElementById('previewImage').src = '';
    if (pet.photo) {
        document.getElementById('existingPhoto').style.display = 'block';
        document.getElementById('existingImage').src = pet.photo;
        document.getElementById('removePhotoPetId').value = pet.id;
    } else {
        document.getElementById('existingPhoto').style.display = 'none';
        document.getElementById('existingImage').src = '';
        document.getElementById('removePhotoPetId').value = '';
    }
}

// Função para visualizar a foto em um modal
function viewPhoto(photoSrc, petName) {
    document.getElementById('modalPhoto').src = photoSrc;
    document.getElementById('photoModalLabel').innerText = `Foto de ${petName}`;
    const photoModal = new bootstrap.Modal(document.getElementById('photoModal'));
    photoModal.show();
}

// Validação do formulário de cadastro/edição de pet
document.getElementById('petForm')?.addEventListener('submit', function(event) {
    const petName = document.getElementById('pet_name').value.trim();
    const petType = document.getElementById('pet_type').value.trim();
    const petSize = document.getElementById('pet_size').value;
    const tutorPhone = document.getElementById('tutor_phone').value.trim();
    const petPhoto = document.getElementById('pet_photo').files[0];
    const errorMessageDiv = document.getElementById('petErrorMessage');
    let errorMessage = '';

    // Limpa mensagens de erro anteriores
    errorMessageDiv.style.display = 'none';
    errorMessageDiv.textContent = '';

    if (!petName) {
        errorMessage += 'O campo Nome do Pet é obrigatório.<br>';
    }
    if (!petType) {
        errorMessage += 'O campo Tipo do Pet é obrigatório.<br>';
    }
    if (!petSize) {
        errorMessage += 'O campo Porte do Pet é obrigatório.<br>';
    }
    if (tutorPhone) {
        // Ajustar a validação do telefone para aceitar números simples ou o formato (XX) XXXX-XXXX
        const phoneRegex = /^(\d{9,11}|\(\d{2}\)\s\d{4,5}-\d{4})$/;
        if (!phoneRegex.test(tutorPhone)) {
            errorMessage += 'O Telefone do Tutor deve ter 9 a 11 dígitos ou estar no formato (XX) XXXX-XXXX.<br>';
        }
    }
    if (petPhoto) {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        const maxSize = 5 * 1024 * 1024; // 5MB

        if (!allowedTypes.includes(petPhoto.type)) {
            errorMessage += 'A foto deve ser do tipo JPEG, PNG ou GIF.<br>';
        }
        if (petPhoto.size > maxSize) {
            errorMessage += 'A foto excede o tamanho máximo de 5MB.<br>';
        }
    }

    if (errorMessage) {
        event.preventDefault(); // Impede o envio do formulário
        errorMessageDiv.innerHTML = errorMessage;
        errorMessageDiv.style.display = 'block';
        // Garantir que o modal role para o topo para exibir a mensagem de erro
        const modalBody = errorMessageDiv.closest('.modal-body');
        if (modalBody) {
            modalBody.scrollTop = 0;
        }
    }
});

// Pré-visualização da foto ao selecionar um arquivo
document.getElementById('pet_photo')?.addEventListener('change', function(event) {
    const file = event.target.files[0];
    const previewDiv = document.getElementById('photoPreview');
    const previewImage = document.getElementById('previewImage');

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            previewDiv.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        previewDiv.style.display = 'none';
        previewImage.src = '';
    }
});

// Validação do formulário de cadastro no lado do cliente
document.getElementById('registerForm')?.addEventListener('submit', function(event) {
    const name = document.getElementById('name').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const address = document.getElementById('address').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const errorMessageDiv = document.getElementById('registerErrorMessage');
    let errorMessage = '';

    // Limpa mensagens de erro anteriores
    errorMessageDiv.style.display = 'none';
    errorMessageDiv.textContent = '';

    if (!name) {
        errorMessage += 'O campo Nome é obrigatório.<br>';
    }
    if (!phone) {
        errorMessage += 'O campo Telefone é obrigatório.<br>';
    } else {
        // Valida o formato do telefone (exemplo: (XX) XXXX-XXXX ou (XX) 9XXXX-XXXX)
        const phoneRegex = /^\(\d{2}\)\s\d{4,5}-\d{4}$/;
        if (!phoneRegex.test(phone)) {
            errorMessage += 'O Telefone deve estar no formato (XX) XXXX-XXXX ou (XX) 9XXXX-XXXX.<br>';
        }
    }
    if (!address) {
        errorMessage += 'O campo Endereço é obrigatório.<br>';
    }
    if (!email) {
        errorMessage += 'O campo Email é obrigatório.<br>';
    } else {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            errorMessage += 'Por favor, insira um email válido.<br>';
        }
    }
    if (!password) {
        errorMessage += 'O campo Senha é obrigatório.<br>';
    }

    if (errorMessage) {
        event.preventDefault(); // Impede o envio do formulário
        errorMessageDiv.innerHTML = errorMessage;
        errorMessageDiv.style.display = 'block';
    }
});

// Validação do formulário de cadastro de serviço
document.getElementById('addServiceForm')?.addEventListener('submit', function(event) {
    const serviceName = document.getElementById('service_name').value.trim();
    const serviceValue = document.getElementById('service_value').value.trim();
    const errorMessageDiv = document.getElementById('addServiceErrorMessage');
    let errorMessage = '';

    // Limpa mensagens de erro anteriores
    errorMessageDiv.style.display = 'none';
    errorMessageDiv.textContent = '';

    if (!serviceName) {
        errorMessage += 'O campo Nome do Serviço é obrigatório.<br>';
    } else {
        const nameRegex = /^[a-zA-Z0-9\s]+$/;
        if (!nameRegex.test(serviceName)) {
            errorMessage += 'O Nome do Serviço deve conter apenas letras, números e espaços.<br>';
        }
    }
    if (!serviceValue) {
        errorMessage += 'O campo Valor do Serviço é obrigatório.<br>';
    } else if (isNaN(serviceValue) || parseFloat(serviceValue) < 0) {
        errorMessage += 'O Valor do Serviço deve ser um número positivo.<br>';
    } else if (parseFloat(serviceValue) > 10000) {
        errorMessage += 'O Valor do Serviço não pode exceder R$ 10.000,00.<br>';
    }

    if (errorMessage) {
        event.preventDefault(); // Impede o envio do formulário
        errorMessageDiv.innerHTML = errorMessage;
        errorMessageDiv.style.display = 'block';
    }
});

// Validação do formulário de cadastro de pacote promocional
document.getElementById('addPackageForm')?.addEventListener('submit', function(event) {
    const packageName = document.getElementById('package_name').value.trim();
    const promotionalPrice = document.getElementById('promotional_price').value.trim();
    const services = document.querySelectorAll('input[name="services[]"]:checked');
    const tutors = document.querySelectorAll('input[name="tutors[]"]:checked');
    const errorMessageDiv = document.getElementById('addPackageErrorMessage');
    let errorMessage = '';

    // Limpa mensagens de erro anteriores
    errorMessageDiv.style.display = 'none';
    errorMessageDiv.textContent = '';

    if (!packageName) {
        errorMessage += 'O campo Nome do Pacote é obrigatório.<br>';
    } else {
        const nameRegex = /^[a-zA-Z0-9\s]+$/;
        if (!nameRegex.test(packageName)) {
            errorMessage += 'O Nome do Pacote deve conter apenas letras, números e espaços.<br>';
        }
    }
    if (!promotionalPrice) {
        errorMessage += 'O campo Preço Promocional é obrigatório.<br>';
    } else if (isNaN(promotionalPrice) || parseFloat(promotionalPrice) < 0) {
        errorMessage += 'O Preço Promocional deve ser um número positivo.<br>';
    } else if (parseFloat(promotionalPrice) > 10000) {
        errorMessage += 'O Preço Promocional não pode exceder R$ 10.000,00.<br>';
    }
    if (services.length === 0) {
        errorMessage += 'Selecione pelo menos um serviço para o pacote.<br>';
    }
    if (tutors.length === 0) {
        errorMessage += 'Selecione pelo menos um tutor para associar ao pacote promocional.<br>';
    }

    // Validar quantidades dos serviços selecionados
    let hasInvalidQuantity = false;
    services.forEach(function(checkbox) {
        const serviceId = checkbox.id.split('_')[1];
        const quantityInput = document.getElementById('quantity_' + serviceId);
        const quantity = parseInt(quantityInput.value);
        if (!quantity || quantity <= 0) {
            hasInvalidQuantity = true;
            quantityInput.classList.add('is-invalid');
        } else {
            quantityInput.classList.remove('is-invalid');
        }
    });

    if (hasInvalidQuantity) {
        errorMessage += 'Por favor, informe uma quantidade válida (maior que 0) para todos os serviços selecionados.<br>';
    }

    if (errorMessage) {
        event.preventDefault(); // Impede o envio do formulário
        errorMessageDiv.innerHTML = errorMessage;
        errorMessageDiv.style.display = 'block';
    }
});

// Validação dos formulários de edição de serviço (nos modais)
document.querySelectorAll('.editServiceForm')?.forEach(form => {
    form.addEventListener('submit', function(event) {
        const serviceId = form.querySelector('input[name="service_id"]').value;
        const serviceName = form.querySelector(`#service_name_${serviceId}`).value.trim();
        const serviceValue = form.querySelector(`#service_value_${serviceId}`).value.trim();
        const errorMessageDiv = form.querySelector(`#editServiceErrorMessage_${serviceId}`);
        let errorMessage = '';

        // Limpa mensagens de erro anteriores
        errorMessageDiv.style.display = 'none';
        errorMessageDiv.textContent = '';

        if (!serviceName) {
            errorMessage += 'O campo Nome do Serviço é obrigatório.<br>';
        } else {
            const nameRegex = /^[a-zA-Z0-9\s]+$/;
            if (!nameRegex.test(serviceName)) {
                errorMessage += 'O Nome do Serviço deve conter apenas letras, números e espaços.<br>';
            }
        }
        if (!serviceValue) {
            errorMessage += 'O campo Valor do Serviço é obrigatório.<br>';
        } else if (isNaN(serviceValue) || parseFloat(serviceValue) < 0) {
            errorMessage += 'O Valor do Serviço deve ser um número positivo.<br>';
        } else if (parseFloat(serviceValue) > 10000) {
            errorMessage += 'O Valor do Serviço não pode exceder R$ 10.000,00.<br>';
        }

        if (errorMessage) {
            event.preventDefault(); // Impede o envio do formulário
            errorMessageDiv.innerHTML = errorMessage;
            errorMessageDiv.style.display = 'block';
        }
    });
});

// Validação dos formulários de edição de pacote promocional (nos modais)
document.querySelectorAll('.editPackageForm')?.forEach(form => {
    form.addEventListener('submit', function(event) {
        const packageId = form.querySelector('input[name="package_id"]').value;
        const packageName = form.querySelector(`#package_name_${packageId}`).value.trim();
        const promotionalPrice = form.querySelector(`#promotional_price_${packageId}`).value.trim();
        const services = form.querySelectorAll(`input[name="services[]"]:checked`);
        const tutors = form.querySelectorAll(`input[name="tutors[]"]:checked`);
        const errorMessageDiv = form.querySelector(`#editPackageErrorMessage_${packageId}`);
        let errorMessage = '';

        // Limpa mensagens de erro anteriores
        errorMessageDiv.style.display = 'none';
        errorMessageDiv.textContent = '';

        if (!packageName) {
            errorMessage += 'O campo Nome do Pacote é obrigatório.<br>';
        } else {
            const nameRegex = /^[a-zA-Z0-9\s]+$/;
            if (!nameRegex.test(packageName)) {
                errorMessage += 'O Nome do Pacote deve conter apenas letras, números e espaços.<br>';
            }
        }
        if (!promotionalPrice) {
            errorMessage += 'O campo Preço Promocional é obrigatório.<br>';
        } else if (isNaN(promotionalPrice) || parseFloat(promotionalPrice) < 0) {
            errorMessage += 'O Preço Promocional deve ser um número positivo.<br>';
        } else if (parseFloat(promotionalPrice) > 10000) {
            errorMessage += 'O Preço Promocional não pode exceder R$ 10.000,00.<br>';
        }
        if (services.length === 0) {
            errorMessage += 'Selecione pelo menos um serviço para o pacote.<br>';
        }
        if (tutors.length === 0) {
            errorMessage += 'Selecione pelo menos um tutor para associar ao pacote promocional.<br>';
        }

        // Validar quantidades dos serviços selecionados
        let hasInvalidQuantity = false;
        services.forEach(function(checkbox) {
            const serviceId = checkbox.id.split('_')[1];
            const quantityInput = document.getElementById('quantity_' + serviceId);
            const quantity = parseInt(quantityInput.value);
            if (!quantity || quantity <= 0) {
                hasInvalidQuantity = true;
                quantityInput.classList.add('is-invalid');
            } else {
                quantityInput.classList.remove('is-invalid');
            }
        });

        if (hasInvalidQuantity) {
            errorMessage += 'Por favor, informe uma quantidade válida (maior que 0) para todos os serviços selecionados.<br>';
        }

        if (errorMessage) {
            event.preventDefault(); // Impede o envio do formulário
            errorMessageDiv.innerHTML = errorMessage;
            errorMessageDiv.style.display = 'block';
        }
    });
});

// Confirmação para exclusão de serviço
document.querySelectorAll('.deleteServiceForm')?.forEach(form => {
    form.addEventListener('submit', function(event) {
        if (!confirm('Tem certeza que deseja excluir este serviço? Esta ação não pode ser desfeita.')) {
            event.preventDefault();
        }
    });
});

// Confirmação para exclusão de pacote promocional
document.querySelectorAll('.deletePackageForm')?.forEach(form => {
    form.addEventListener('submit', function(event) {
        if (!confirm('Tem certeza que deseja excluir este pacote promocional? Esta ação não pode ser desfeita.')) {
            event.preventDefault();
        }
    });
});

// Função para carregar pets dinamicamente com base no tutor selecionado
console.log('scripts.js carregado com sucesso');

function updatePetOptions(userId) {
    const petSelect = document.getElementById('pet_id');
    const petSelectEdit = document.getElementById('pet_id_edit');
    const targetSelect = petSelectEdit && document.activeElement.id === 'user_id_edit' ? petSelectEdit : petSelect;

    // Limpa as opções existentes
    console.log('Limpando opções do select:', targetSelect.id);
    targetSelect.innerHTML = '<option value="">Selecione um pet</option>';

    if (userId) {
        console.log('Enviando tutor_id:', userId);
        fetch('get_pets.php?tutor_id=' + encodeURIComponent(userId))
            .then(response => {
                console.log('Status da resposta:', response.status);
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.error || 'Erro na requisição de pets: ' + response.status);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Dados recebidos:', data);
                if (data.error) {
                    console.log('Erro retornado pelo servidor:', data.error);
                    targetSelect.innerHTML = '<option value="">' + data.error + '</option>';
                    return;
                }
                if (!Array.isArray(data) || data.length === 0) {
                    console.log('Nenhum pet encontrado para o tutor ID:', userId);
                    targetSelect.innerHTML = '<option value="">Nenhum pet associado ao tutor (ID: ' + userId + '). Cadastre um pet para este tutor.</option>';
                    return;
                }
                data.forEach(pet => {
                    console.log('Adicionando pet:', pet);
                    const option = document.createElement('option');
                    option.value = pet.id;
                    option.textContent = pet.name + ' (Tutor: ' + pet.tutor_name + ')';
                    targetSelect.appendChild(option);
                });
                console.log('Select atualizado com sucesso:', targetSelect.innerHTML);
            })
            .catch(error => {
                console.error('Erro ao carregar pets:', error.message);
                targetSelect.innerHTML = '<option value="">Erro ao carregar pets: ' + error.message + '</option>';
            });
    } else {
        console.log('Nenhum userId fornecido');
    }
}

function updatePackageOptions(userId) {
    const packageSelect = document.getElementById('package_id');
    const packageSelectEdit = document.getElementById('package_id_edit');
    const targetSelect = packageSelectEdit && document.activeElement.id === 'user_id_edit' ? packageSelectEdit : packageSelect;
    targetSelect.innerHTML = '<option value="">Nenhum pacote</option>';

    if (userId) {
        fetch('get_user_packages.php?user_id=' + encodeURIComponent(userId))
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Resposta do servidor:', text);
                        throw new Error('Erro na requisição de pacotes: ' + response.status + ' ' + response.statusText);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (Array.isArray(data) && data.length > 0) {
                    data.forEach(pkg => {
                        const option = document.createElement('option');
                        option.value = pkg.id;
                        option.textContent = pkg.name + ' (R$ ' + parseFloat(pkg.promotional_price).toFixed(2) + ')';
                        targetSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Erro ao carregar pacotes:', error.message);
                targetSelect.innerHTML = '<option value="">Erro ao carregar pacotes: ' + error.message + '</option>';
            });
    }
}


// Inicializar eventos quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    const tutorSelect = document.getElementById('tutor_id');
    const userSelectEdit = document.getElementById('user_id_edit');

    // Para o formulário de cadastro
    if (tutorSelect) {
        console.log('Adicionando evento change ao tutorSelect');
        tutorSelect.addEventListener('change', function() {
            console.log('Evento change disparado para tutor_id:', this.value);
            updatePetOptions(this.value);
            updatePackageOptions(this.value);
        });

        // Dispara o evento change manualmente para carregar os pets do tutor selecionado inicialmente (se houver)
        if (tutorSelect.value) {
            console.log('Tutor inicial selecionado:', tutorSelect.value);
            tutorSelect.dispatchEvent(new Event('change'));
        }
    }

    // Para o formulário de edição
    if (userSelectEdit) {
        userSelectEdit.addEventListener('change', function() {
            updatePetOptions(this.value);
            updatePackageOptions(this.value);
        });
        // Carregar pets e pacotes ao carregar a página de edição
        const initialUserId = userSelectEdit.value;
        if (initialUserId) {
            updatePetOptions(initialUserId);
            updatePackageOptions(initialUserId);
        }
    }

    // Mostrar/esconder o campo de quantidade ao selecionar/desmarcar um serviço
    document.querySelectorAll('.service-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const serviceId = this.id.split('_')[1];
            const quantityInput = document.getElementById('quantity_' + serviceId);
            const quantityHidden = document.getElementById('quantity_hidden_' + serviceId);

            if (this.checked) {
                quantityInput.style.display = 'block';
                quantityInput.required = true;
                quantityInput.value = quantityInput.value || '1';
                quantityHidden.value = quantityInput.value;
            } else {
                quantityInput.style.display = 'none';
                quantityInput.required = false;
                quantityInput.value = '';
                quantityHidden.value = '0';
            }
        });
    });

    // Sincronizar o campo visível com o campo oculto ao alterar a quantidade
    document.querySelectorAll('.quantity-input').forEach(function(input) {
        input.addEventListener('input', function() {
            const serviceId = this.id.split('_')[1];
            const quantityHidden = document.getElementById('quantity_hidden_' + serviceId);
            quantityHidden.value = this.value || '0';
        });
    });

    // Validação de data futura no campo "Data e Hora"
    const scheduledAtInput = document.getElementById('scheduled_at');
    if (scheduledAtInput) {
        scheduledAtInput.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const currentDate = new Date();
            if (selectedDate <= currentDate) {
                alert('A data deve ser futura.');
                this.value = '';
            }
        });
    }
});