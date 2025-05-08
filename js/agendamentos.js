// Função para carregar pets dinamicamente com base no tutor selecionado
function updatePetOptions(userId) {
    const petSelect = document.getElementById('pet_id');
    const petSelectEdit = document.getElementById('pet_id_edit');
    const targetSelect = petSelectEdit && document.activeElement.id === 'user_id_edit' ? petSelectEdit : petSelect;

    // Limpa as opções existentes
    console.log('Limpando opções do select:', targetSelect.id);
    targetSelect.innerHTML = '<option value="">Selecione um pet</option>';

    if (userId) {
        console.log('Enviando tutor_id:', userId);
        fetch('get_user_pets.php?user_id=' + encodeURIComponent(userId))
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