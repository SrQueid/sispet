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
        event.preventDefault();
        alert(errorMessage);
    }
});

document.getElementById('forgotPasswordForm')?.addEventListener('submit', function(event) {
    const email = document.getElementById('email').value.trim();
    const errorMessageDiv = document.getElementById('errorMessage');
    let errorMessage = '';

    errorMessageDiv.style.display = 'none';
    errorMessageDiv.textContent = '';

    if (!email) {
        errorMessage += 'O campo Email é obrigatório.';
    } else {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            errorMessage += 'Por favor, insira um email válido.';
        }
    }

    if (errorMessage) {
        event.preventDefault();
        errorMessageDiv.textContent = errorMessage;
        errorMessageDiv.style.display = 'block';
    }
});

document.getElementById('addAdminForm')?.addEventListener('submit', function(event) {
    const name = document.getElementById('name').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const address = document.getElementById('address').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const errorMessageDiv = document.getElementById('addAdminErrorMessage');
    let errorMessage = '';

    errorMessageDiv.style.display = 'none';
    errorMessageDiv.innerHTML = '';

    if (!name) errorMessage += 'O campo Nome é obrigatório.<br>';
    if (!phone) errorMessage += 'O campo Telefone é obrigatório.<br>';
    if (!address) errorMessage += 'O campo Endereço é obrigatório.<br>';
    if (!email) {
        errorMessage += 'O campo Email é obrigatório.<br>';
    } else {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            errorMessage += 'Por favor, insira um email válido.<br>';
        }
    }
    if (!password) errorMessage += 'O campo Senha é obrigatório.<br>';

    if (errorMessage) {
        event.preventDefault();
        errorMessageDiv.innerHTML = errorMessage;
        errorMessageDiv.style.display = 'block';
    }
});

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

        errorMessageDiv.style.display = 'none';
        errorMessageDiv.innerHTML = '';

        if (!name) errorMessage += 'O campo Nome é obrigatório.<br>';
        if (!phone) errorMessage += 'O campo Telefone é obrigatório.<br>';
        if (!address) errorMessage += 'O campo Endereço é obrigatório.<br>';
        if (!email) {
            errorMessage += 'O campo Email é obrigatório.<br>';
        } else {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                errorMessage += 'Por favor, insira um email válido.<br>';
            }
        }
        if (!role) errorMessage += 'O campo Role é obrigatório.<br>';

        if (errorMessage) {
            event.preventDefault();
            errorMessageDiv.innerHTML = errorMessage;
            errorMessageDiv.style.display = 'block';
        }
    });
});

document.getElementById('petForm')?.addEventListener('submit', function(event) {
    const petName = document.getElementById('pet_name').value.trim();
    const petType = document.getElementById('pet_type').value.trim();
    const petSize = document.getElementById('pet_size').value;
    const tutorPhone = document.getElementById('tutor_phone').value.trim();
    const petPhoto = document.getElementById('pet_photo').files[0];
    const errorMessageDiv = document.getElementById('petErrorMessage');
    let errorMessage = '';

    errorMessageDiv.style.display = 'none';
    errorMessageDiv.innerHTML = '';

    if (!petName) errorMessage += 'O campo Nome do Pet é obrigatório.<br>';
    if (!petType) errorMessage += 'O campo Tipo do Pet é obrigatório.<br>';
    if (!petSize) errorMessage += 'O campo Porte do Pet é obrigatório.<br>';
    if (tutorPhone) {
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
        event.preventDefault();
        errorMessageDiv.innerHTML = errorMessage;
        errorMessageDiv.style.display = 'block';
        const modalBody = errorMessageDiv.closest('.modal-body');
        if (modalBody) modalBody.scrollTop = 0;
    }
});

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

document.getElementById('registerForm')?.addEventListener('submit', function(event) {
    const name = document.getElementById('name').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const address = document.getElementById('address').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const errorMessageDiv = document.getElementById('registerErrorMessage');
    let errorMessage = '';

    errorMessageDiv.style.display = 'none';
    errorMessageDiv.innerHTML = '';

    if (!name) errorMessage += 'O campo Nome é obrigatório.<br>';
    if (!phone) {
        errorMessage += 'O campo Telefone é obrigatório.<br>';
    } else {
        const phoneRegex = /^\(\d{2}\)\s\d{4,5}-\d{4}$/;
        if (!phoneRegex.test(phone)) {
            errorMessage += 'O Telefone deve estar no formato (XX) XXXX-XXXX ou (XX) 9XXXX-XXXX.<br>';
        }
    }
    if (!address) errorMessage += 'O campo Endereço é obrigatório.<br>';
    if (!email) {
        errorMessage += 'O campo Email é obrigatório.<br>';
    } else {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            errorMessage += 'Por favor, insira um email válido.<br>';
        }
    }
    if (!password) errorMessage += 'O campo Senha é obrigatório.<br>';

    if (errorMessage) {
        event.preventDefault();
        errorMessageDiv.innerHTML = errorMessage;
        errorMessageDiv.style.display = 'block';
    }
});

document.getElementById('addServiceForm')?.addEventListener('submit', function(event) {
    const serviceName = document.getElementById('service_name').value.trim();
    const serviceValue = document.getElementById('service_value').value.trim();
    const errorMessageDiv = document.getElementById('addServiceErrorMessage');
    let errorMessage = '';

    errorMessageDiv.style.display = 'none';
    errorMessageDiv.innerHTML = '';

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
        event.preventDefault();
        errorMessageDiv.innerHTML = errorMessage;
        errorMessageDiv.style.display = 'block';
    }
});

document.getElementById('addPackageForm')?.addEventListener('submit', function(event) {
    const packageName = document.getElementById('package_name').value.trim();
    const promotionalPrice = document.getElementById('promotional_price').value.trim();
    const services = document.querySelectorAll('input[name="services[]"]:checked');
    const tutors = document.querySelectorAll('input[name="tutors[]"]:checked');
    const errorMessageDiv = document.getElementById('addPackageErrorMessage');
    let errorMessage = '';

    errorMessageDiv.style.display = 'none';
    errorMessageDiv.innerHTML = '';

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
        event.preventDefault();
        errorMessageDiv.innerHTML = errorMessage;
        errorMessageDiv.style.display = 'block';
    }
});

document.querySelectorAll('.editServiceForm')?.forEach(form => {
    form.addEventListener('submit', function(event) {
        const serviceId = form.querySelector('input[name="service_id"]').value;
        const serviceName = form.querySelector(`#service_name_${serviceId}`).value.trim();
        const serviceValue = form.querySelector(`#service_value_${serviceId}`).value.trim();
        const errorMessageDiv = form.querySelector(`#editServiceErrorMessage_${serviceId}`);
        let errorMessage = '';

        errorMessageDiv.style.display = 'none';
        errorMessageDiv.innerHTML = '';

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
            event.preventDefault();
            errorMessageDiv.innerHTML = errorMessage;
            errorMessageDiv.style.display = 'block';
        }
    });
});

document.querySelectorAll('.editPackageForm')?.forEach(form => {
    form.addEventListener('submit', function(event) {
        const packageId = form.querySelector('input[name="package_id"]').value;
        const packageName = form.querySelector(`#package_name_${packageId}`).value.trim();
        const promotionalPrice = form.querySelector(`#promotional_price_${packageId}`).value.trim();
        const services = form.querySelectorAll(`input[name="services[]"]:checked`);
        const tutors = form.querySelectorAll(`input[name="tutors[]"]:checked`);
        const errorMessageDiv = form.querySelector(`#editPackageErrorMessage_${packageId}`);
        let errorMessage = '';

        errorMessageDiv.style.display = 'none';
        errorMessageDiv.innerHTML = '';

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
            event.preventDefault();
            errorMessageDiv.innerHTML = errorMessage;
            errorMessageDiv.style.display = 'block';
        }
    });
});

document.querySelectorAll('.deleteServiceForm')?.forEach(form => {
    form.addEventListener('submit', function(event) {
        if (!confirm('Tem certeza que deseja excluir este serviço? Esta ação não pode ser desfeita.')) {
            event.preventDefault();
        }
    });
});

document.querySelectorAll('.deletePackageForm')?.forEach(form => {
    form.addEventListener('submit', function(event) {
        if (!confirm('Tem certeza que deseja excluir este pacote promocional? Esta ação não pode ser desfeita.')) {
            event.preventDefault();
        }
    });
});

document.getElementById('service_id')?.addEventListener('change', function() {
    const transportTypeField = document.getElementById('transport_type_field');
    const selectedOption = this.options[this.selectedIndex];
    const serviceName = selectedOption.getAttribute('data-service-name');
    if (serviceName === 'TaxiPet') {
        transportTypeField.style.display = 'block';
    } else {
        transportTypeField.style.display = 'none';
        document.getElementById('transport_type').value = '';
    }
});

document.getElementById('transport_type')?.addEventListener('change', function() {
    if (this.value) {
        alert('Atenção: Os horários de transporte (busca e devolução) terão cobrança adicional.');
    }
});

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

document.querySelectorAll('.quantity-input').forEach(function(input) {
    input.addEventListener('input', function() {
        const serviceId = this.id.split('_')[1];
        const quantityHidden = document.getElementById('quantity_hidden_' + serviceId);
        quantityHidden.value = this.value || '0';
    });
});

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

document.getElementById('search_tutor_pet')?.addEventListener('input', async function() {
    const query = this.value.trim();
    const tutoresTableBody = document.querySelector('#tutores_table tbody');

    if (query.length < 2) {
        try {
            const response = await fetch('search_tutor_pet.php?q=');
            if (!response.ok) throw new Error(`Erro na requisição: ${response.status} ${response.statusText}`);
            const results = await response.json();

            tutoresTableBody.innerHTML = results.map(tutor => `
                <tr data-tutor-id="${tutor.id}" data-tutor-name="${tutor.display_name}">
                    <td>${tutor.id}</td>
                    <td>${tutor.display_name}</td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="showPetsModal(${tutor.id}, '${tutor.display_name}')">Ver Pets</button>
                    </td>
                </tr>
            `).join('');
        } catch (error) {
            tutoresTableBody.innerHTML = `<tr><td colspan="3" class="text-danger">Erro na requisição: ${error.message}</td></tr>`;
        }
        return;
    }

    try {
        const response = await fetch(`search_tutor_pet.php?q=${encodeURIComponent(query)}`);
        if (!response.ok) throw new Error(`Erro na requisição: ${response.status} ${response.statusText}`);
        const results = await response.json();

        if (results.error) {
            tutoresTableBody.innerHTML = `<tr><td colspan="3" class="text-danger">Erro: ${results.error}</td></tr>`;
            return;
        }

        const tutores = results.filter(item => item.type === 'tutor');
        if (tutores.length === 0) {
            tutoresTableBody.innerHTML = '<tr><td colspan="3">Nenhum tutor encontrado.</td></tr>';
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
    } catch (error) {
        tutoresTableBody.innerHTML = `<tr><td colspan="3" class="text-danger">Erro na requisição: ${error.message}</td></tr>`;
    }
});

async function showPetsModal(tutorId, tutorName) {
    const petsList = document.getElementById('pets_list');
    const modalLabel = document.getElementById('petsModalLabel');
    modalLabel.textContent = `Pets do Tutor: ${tutorName}`;

    try {
        const response = await fetch(`get_pets.php?user_id=${tutorId}`);
        if (!response.ok) throw new Error(`Erro na requisição: ${response.status} ${response.statusText}`);
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
                <button class="btn btn-success btn-sm" onclick="selectPetForScheduling(${tutorId}, '${tutorName}', ${pet.id}, '${pet.name}')">Agendar</button>
            </div>
        `).join('');
    } catch (error) {
        petsList.innerHTML = `<p class="text-danger">Erro ao carregar pets: ${error.message}</p>`;
    }

    const modal = new bootstrap.Modal(document.getElementById('petsModal'));
    modal.show();
}

async function selectPetForScheduling(tutorId, tutorName, petId, petName) {
    document.getElementById('selected_user_id').value = tutorId;
    document.getElementById('selected_tutor_name').value = tutorName;
    document.getElementById('selected_pet_id').value = petId;
    document.getElementById('selected_pet_name').value = petName;

    const packageSelect = document.getElementById('package_id');
    packageSelect.innerHTML = '<option value="">Nenhum pacote</option>';

    try {
        const response = await fetch(`get_packages.php?user_id=${tutorId}`);
        if (!response.ok) throw new Error(`Erro na requisição: ${response.status} ${response.statusText}`);
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

    document.getElementById('agendamento_form_card').style.display = 'block';

    const modal = bootstrap.Modal.getInstance(document.getElementById('petsModal'));
    modal.hide();
}