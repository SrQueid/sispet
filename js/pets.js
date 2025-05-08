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
function showPhoto(photoSrc) {
    document.getElementById('modalPhoto').src = photoSrc;
    const photoModal = new bootstrap.Modal(document.getElementById('photoModal'));
    photoModal.show();
}

// Função para resetar o formulário de pet
function resetPetForm() {
    document.getElementById('petForm').reset();
    document.getElementById('formAction').value = 'add_pet';
    document.getElementById('petId').value = '';
    document.getElementById('petModalLabel').innerText = 'Cadastrar Novo Pet';
    document.getElementById('submitButton').innerText = 'Cadastrar Pet';
    document.getElementById('photoPreview').style.display = 'none';
    document.getElementById('previewImage').src = '';
    document.getElementById('existingPhoto').style.display = 'none';
    document.getElementById('existingImage').src = '';
    document.getElementById('removePhotoPetId').value = '';
    document.getElementById('petErrorMessage').style.display = 'none';
    document.getElementById('petErrorMessage').textContent = '';
}