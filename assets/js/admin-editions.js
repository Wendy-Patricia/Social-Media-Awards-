// admin-editions.js
document.addEventListener('DOMContentLoaded', function() {
    // Filtros
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const yearFilter = document.getElementById('yearFilter');
    const tableRows = document.querySelectorAll('#editionsTable tbody tr');
    
    if (searchInput) {
        searchInput.addEventListener('input', filterEditions);
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterEditions);
    }
    
    if (yearFilter) {
        yearFilter.addEventListener('change', filterEditions);
    }
    
    function filterEditions() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
        const statusValue = statusFilter ? statusFilter.value : '';
        const yearValue = yearFilter ? yearFilter.value : '';
        
        tableRows.forEach(row => {
            const name = row.getAttribute('data-name') || '';
            const status = row.getAttribute('data-status') || '';
            const year = row.getAttribute('data-year') || '';
            
            let show = true;
            
            if (searchTerm && !name.includes(searchTerm)) {
                show = false;
            }
            
            if (statusValue && status !== statusValue) {
                show = false;
            }
            
            if (yearValue && year !== yearValue) {
                show = false;
            }
            
            row.style.display = show ? '' : 'none';
        });
    }
    
    // Modal de exclusão
    const deleteButtons = document.querySelectorAll('.btn-delete');
    const deleteModal = document.getElementById('deleteModal');
    const deleteEditionName = document.getElementById('deleteEditionName');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    
    let editionToDelete = null;
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            editionToDelete = this.getAttribute('data-id');
            const editionName = this.getAttribute('data-name');
            deleteEditionName.textContent = editionName;
            deleteModal.style.display = 'flex';
        });
    });
    
    if (deleteModal) {
        const modalClose = deleteModal.querySelector('.modal-close');
        modalClose.addEventListener('click', () => {
            deleteModal.style.display = 'none';
        });
        
        cancelDeleteBtn.addEventListener('click', () => {
            deleteModal.style.display = 'none';
        });
        
        confirmDeleteBtn.addEventListener('click', () => {
            if (editionToDelete) {
                // Aqui você faria a requisição AJAX para deletar
                console.log('Deletando edição:', editionToDelete);
                
                // Simulação de sucesso
                deleteModal.style.display = 'none';
                
                // Recarregar a página ou remover a linha
                location.reload();
            }
        });
        
        // Fechar modal ao clicar fora
        deleteModal.addEventListener('click', (e) => {
            if (e.target === deleteModal) {
                deleteModal.style.display = 'none';
            }
        });
    }
    
    // Modal de mudança de status
    const statusButtons = document.querySelectorAll('.btn-toggle-status');
    const statusModal = document.getElementById('statusModal');
    const statusEditionName = document.getElementById('statusEditionName');
    const cancelStatusBtn = document.getElementById('cancelStatusBtn');
    const confirmStatusBtn = document.getElementById('confirmStatusBtn');
    const statusRadios = document.querySelectorAll('input[name="newStatus"]');
    
    let editionToUpdate = null;
    let currentStatus = null;
    
    statusButtons.forEach(button => {
        button.addEventListener('click', function() {
            editionToUpdate = this.getAttribute('data-id');
            currentStatus = this.getAttribute('data-status');
            const editionName = this.closest('tr').querySelector('strong').textContent;
            statusEditionName.textContent = editionName;
            
            // Selecionar o radio correspondente ao status atual
            statusRadios.forEach(radio => {
                if (radio.value === currentStatus) {
                    radio.checked = true;
                    radio.closest('.status-option').classList.add('selected');
                } else {
                    radio.closest('.status-option').classList.remove('selected');
                }
            });
            
            statusModal.style.display = 'flex';
        });
    });
    
    // Adicionar classe selected ao clicar nas opções
    document.querySelectorAll('.status-option').forEach(option => {
        option.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;
            
            document.querySelectorAll('.status-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            
            this.classList.add('selected');
        });
    });
    
    if (statusModal) {
        const modalClose = statusModal.querySelector('.modal-close');
        modalClose.addEventListener('click', () => {
            statusModal.style.display = 'none';
        });
        
        cancelStatusBtn.addEventListener('click', () => {
            statusModal.style.display = 'none';
        });
        
        confirmStatusBtn.addEventListener('click', () => {
            if (editionToUpdate) {
                const newStatus = document.querySelector('input[name="newStatus"]:checked').value;
                
                // Aqui você faria a requisição AJAX para atualizar o status
                console.log('Atualizando status da edição:', editionToUpdate, 'para:', newStatus);
                
                // Simulação de sucesso
                statusModal.style.display = 'none';
                
                // Atualizar visualmente
                const button = document.querySelector(`.btn-toggle-status[data-id="${editionToUpdate}"]`);
                const statusBadge = button.closest('tr').querySelector('.status-badge');
                const statusCell = button.closest('tr').querySelector('td:nth-child(4)');
                
                // Atualizar classes e texto
                statusBadge.className = `status-badge status-${newStatus}`;
                statusBadge.innerHTML = `<i class="fas fa-circle"></i> ${
                    newStatus === 'active' ? 'Active' : 
                    newStatus === 'upcoming' ? 'À venir' : 
                    'Terminée'
                }`;
                
                // Atualizar tooltip do botão
                button.setAttribute('title', newStatus === 'active' ? 'Désactiver' : 'Activer');
                button.setAttribute('data-status', newStatus);
                
                // Recarregar para atualizar estatísticas
                setTimeout(() => location.reload(), 1000);
            }
        });
        
        // Fechar modal ao clicar fora
        statusModal.addEventListener('click', (e) => {
            if (e.target === statusModal) {
                statusModal.style.display = 'none';
            }
        });
    }
    
    // Preview de imagem no formulário
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('image-preview');
    
    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    imagePreview.innerHTML = `
                        <img src="${e.target.result}" alt="Prévisualisation">
                        <p style="margin-top: 10px; color: #666;">${file.name} (${(file.size / 1024).toFixed(2)} KB)</p>
                    `;
                }
                
                reader.readAsDataURL(file);
            } else {
                imagePreview.innerHTML = '';
            }
        });
    }
    
    // Validação do formulário
    const createEditionForm = document.getElementById('createEditionForm');
    
    if (createEditionForm) {
        createEditionForm.addEventListener('submit', function(e) {
            const dateDebut = document.getElementById('date_debut');
            const dateFin = document.getElementById('date_fin');
            const dateFinVotes = document.getElementById('date_fin_votes');
            
            // Validação de datas
            if (dateDebut.value && dateFin.value) {
                const debut = new Date(dateDebut.value);
                const fin = new Date(dateFin.value);
                
                if (fin <= debut) {
                    e.preventDefault();
                    alert('La date de fin doit être postérieure à la date de début.');
                    dateFin.focus();
                    return false;
                }
                
                if (dateFinVotes.value) {
                    const finVotes = new Date(dateFinVotes.value);
                    if (finVotes > fin) {
                        e.preventDefault();
                        alert('La date limite des votes ne peut pas être après la date de fin de l\'édition.');
                        dateFinVotes.focus();
                        return false;
                    }
                }
            }
            
            // Validação de imagem
            const imageInput = document.getElementById('image');
            if (imageInput.files.length > 0) {
                const file = imageInput.files[0];
                const maxSize = 5 * 1024 * 1024; // 5MB
                
                if (file.size > maxSize) {
                    e.preventDefault();
                    alert('L\'image ne peut pas dépasser 5MB.');
                    return false;
                }
                
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    e.preventDefault();
                    alert('Format d\'image non supporté. Utilisez JPG, PNG ou GIF.');
                    return false;
                }
            }
            
            return true;
        });
    }
});