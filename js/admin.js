// Função para realizar a pesquisa
function searchTutorsPets() {
    const searchTerm = document.getElementById('searchInput').value.trim();
    const tutorsTableBody = document.getElementById('tutorsTableBody');

    if (searchTerm.length < 2) {
        // Se o campo de pesquisa estiver vazio ou com menos de 2 caracteres, recarregar a lista completa
        fetchTutors();
        return;
    }

    fetch(`search_tutors_pets.php?search=${encodeURIComponent(searchTerm)}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            tutorsTableBody.innerHTML = `<tr><td colspan="4">${data.error}</td></tr>`;
            return;
        }

        if (data.tutors.length === 0) {
            tutorsTableBody.innerHTML = '<tr><td colspan="4">Nenhum tutor ou pet encontrado.</td></tr>';
            return;
        }

        // Preencher a tabela com os tutores encontrados
        tutorsTableBody.innerHTML = data.tutors.map(tutor => `
            <tr>
                <td>${tutor.name}</td>
                <td>${tutor.email}</td>
                <td>${tutor.phone}</td>
                <td class="action-buttons">
                    <button onclick="showPets(${tutor.id}, '${tutor.name}')">Ver Pets</button>
                    <button onclick="editTutor(${tutor.id}, '${tutor.name}', '${tutor.email}', '${tutor.phone}')">Editar</button>
                    <button onclick="deleteTutor(${tutor.id})">Excluir</button>
                </td>
            </tr>
        `).join('');
    })
    .catch(error => {
        console.error('Erro ao buscar tutores/pets:', error);
        tutorsTableBody.innerHTML = '<tr><td colspan="4">Erro ao realizar a busca. Tente novamente.</td></tr>';
    });
}

// Função para carregar todos os tutores (usada ao limpar a pesquisa)
function fetchTutors() {
    fetch('get_tutors.php', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const tutorsTableBody = document.getElementById('tutorsTableBody');
        if (data.tutors.length === 0) {
            tutorsTableBody.innerHTML = '<tr><td colspan="4">Nenhum tutor encontrado.</td></tr>';
            return;
        }

        tutorsTableBody.innerHTML = data.tutors.map(tutor => `
            <tr>
                <td>${tutor.name}</td>
                <td>${tutor.email}</td>
                <td>${tutor.phone}</td>
                <td class="action-buttons">
                    <button onclick="showPets(${tutor.id}, '${tutor.name}')">Ver Pets</button>
                    <button onclick="editTutor(${tutor.id}, '${tutor.name}', '${tutor.email}', '${tutor.phone}')">Editar</button>
                    <button onclick="deleteTutor(${tutor.id})">Excluir</button>
                </td>
            </tr>
        `).join('');
    })
    .catch(error => {
        console.error('Erro ao carregar tutores:', error);
        document.getElementById('tutorsTableBody').innerHTML = '<tr><td colspan="4">Erro ao carregar tutores.</td></tr>';
    });
}

// Função para exibir o modal com os pets do tutor
function showPets(tutorId, tutorName) {
    const modal = document.getElementById('petsModal');
    const modalTutorName = document.getElementById('modalTutorName');
    const petsTableBody = document.getElementById('petsTableBody');

    modalTutorName.textContent = tutorName;
    petsTableBody.innerHTML = '<tr><td colspan="3">Carregando pets...</td></tr>';

    fetch(`get_pets.php?user_id=${tutorId}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            petsTableBody.innerHTML = `<tr><td colspan="3">${data.error}</td></tr>`;
            return;
        }

        if (data.pets.length === 0) {
            petsTableBody.innerHTML = '<tr><td colspan="3">Nenhum pet encontrado.</td></tr>';
            return;
        }

        petsTableBody.innerHTML = data.pets.map(pet => `
            <tr>
                <td>${pet.name}</td>
                <td>${pet.species}</td>
                <td>${pet.breed || '-'}</td>
            </tr>
        `).join('');
    })
    .catch(error => {
        console.error('Erro ao carregar pets:', error);
        petsTableBody.innerHTML = '<tr><td colspan="3">Erro ao carregar pets.</td></tr>';
    });

    modal.style.display = 'block';
}

// Função para abrir o modal de edição
function editTutor(id, name, email, phone) {
    const modal = document.getElementById('editModal');
    document.getElementById('editTutorId').value = id;
    document.getElementById('editTutorName').value = name;
    document.getElementById('editTutorEmail').value = email;
    document.getElementById('editTutorPhone').value = phone;
    modal.style.display = 'block';
}

// Função para atualizar o tutor
function updateTutor(event) {
    event.preventDefault();
    const form = document.getElementById('editTutorForm');
    const formData = new FormData(form);

    fetch('update_tutor.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Tutor atualizado com sucesso!');
            closeEditModal();
            searchTutorsPets(); // Atualizar a lista após edição
        } else {
            alert('Erro ao atualizar tutor: ' + (data.error || 'Tente novamente.'));
        }
    })
    .catch(error => {
        console.error('Erro ao atualizar tutor:', error);
        alert('Erro ao atualizar tutor.');
    });
}

// Função para excluir um tutor
function deleteTutor(tutorId) {
    if (!confirm('Tem certeza que deseja excluir este tutor?')) return;

    fetch(`delete_tutor.php?tutor_id=${tutorId}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Tutor excluído com sucesso!');
            searchTutorsPets(); // Atualizar a lista após exclusão
        } else {
            alert('Erro ao excluir tutor: ' + (data.error || 'Tente novamente.'));
        }
    })
    .catch(error => {
        console.error('Erro ao excluir tutor:', error);
        alert('Erro ao excluir tutor.');
    });
}

// Função para fechar o modal de pets
function closeModal() {
    const modal = document.getElementById('petsModal');
    modal.style.display = 'none';
}

// Função para fechar o modal de edição
function closeEditModal() {
    const modal = document.getElementById('editModal');
    modal.style.display = 'none';
}

// Fechar modais ao clicar fora deles
window.onclick = function(event) {
    const petsModal = document.getElementById('petsModal');
    const editModal = document.getElementById('editModal');
    if (event.target === petsModal) {
        petsModal.style.display = 'none';
    }
    if (event.target === editModal) {
        editModal.style.display = 'none';
    }
};

// Carregar tutores ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    fetchTutors();
});