// admin-nominations.js
document.addEventListener('DOMContentLoaded', function() {
    // Filtros da tabela
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const categoryFilter = document.getElementById('categoryFilter');
    const platformFilter = document.getElementById('platformFilter');
    const tableRows = document.querySelectorAll('#nominationsTable tbody tr');
    
    if (searchInput) {
        searchInput.addEventListener('input', filterNominations);
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterNominations);
    }
    
    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterNominations);
    }
    
    if (platformFilter) {
        platformFilter.addEventListener('change', filterNominations);
    }
    
    function filterNominations() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
        const statusValue = statusFilter ? statusFilter.value : '';
        const categoryValue = categoryFilter ? categoryFilter.value : '';
        const platformValue = platformFilter ? platformFilter.value : '';
        
        tableRows.forEach(row => {
            const candidate = row.getAttribute('data-candidate') || '';
            const status = row.getAttribute('data-status') || '';
            const category = row.getAttribute('data-category') || '';
            const platform = row.getAttribute('data-platform') || '';
            
            let show = true;
            
            // Filtro por busca
            if (searchTerm && !candidate.includes(searchTerm)) {
                const titre = row.querySelector('td:nth-child(2) strong')?.textContent.toLowerCase() || '';
                if (!titre.includes(searchTerm)) {
                    show = false;
                }
            }
            
            // Filtro por status
            if (statusValue && status !== statusValue) {
                show = false;
            }
            
            // Filtro por categoria (simplificado)
            if (categoryValue && category.toLowerCase() !== categoryValue.toLowerCase()) {
                show = false;
            }
            
            // Filtro por plataforma
            if (platformValue && platform !== platformValue) {
                show = false;
            }
            
            row.style.display = show ? '' : 'none';
        });
    }
    
    // Modal de aprovação
    const approveButtons = document.querySelectorAll('.btn-approve');
    const approveModal = document.getElementById('approveModal');
    const approveNominationTitle = document.getElementById('approveNominationTitle');
    const cancelApproveBtn = document.getElementById('cancelApproveBtn');
    const confirmApproveBtn = document.getElementById('confirmApproveBtn');
    
    let nominationToApprove = null;
    
    approveButtons.forEach(button => {
        button.addEventListener('click', function() {
            nominationToApprove = this.getAttribute('data-id');
            const nominationTitle = this.getAttribute('data-title');
            approveNominationTitle.textContent = nominationTitle;
            approveModal.style.display = 'flex';
        });
    });
    
    if (approveModal) {
        const modalClose = approveModal.querySelector('.modal-close');
        modalClose.addEventListener('click', () => {
            approveModal.style.display = 'none';
        });
        
        cancelApproveBtn.addEventListener('click', () => {
            approveModal.style.display = 'none';
        });
        
        confirmApproveBtn.addEventListener('click', () => {
            if (nominationToApprove) {
                const notes = document.getElementById('approvalNotes').value;
                
                // Aqui você faria a requisição AJAX para aprovar
                console.log('Aprovando nomeação:', nominationToApprove, 'Notas:', notes);
                
                // Simulação de sucesso
                approveModal.style.display = 'none';
                
                // Atualizar visualmente
                const button = document.querySelector(`.btn-approve[data-id="${nominationToApprove}"]`);
                const row = button.closest('tr');
                const statusCell = row.querySelector('td:nth-child(5)');
                const actionButtons = row.querySelector('.action-buttons');
                
                // Atualizar status para "approved"
                statusCell.innerHTML = `
                    <span class="status-badge status-approved">
                        <i class="fas fa-check-circle"></i> Approuvée
                    </span>
                `;
                
                // Remover botões de aprovação/rejeição
                const approveBtn = actionButtons.querySelector('.btn-approve');
                const rejectBtn = actionButtons.querySelector('.btn-reject');
                if (approveBtn) approveBtn.remove();
                if (rejectBtn) rejectBtn.remove();
                
                // Adicionar data de aprovação
                const dateCell = row.querySelector('td:nth-child(7)');
                const now = new Date();
                const formattedDate = now.toLocaleDateString('fr-FR');
                dateCell.innerHTML += `
                    <div><i class="fas fa-check"></i> ${formattedDate}</div>
                `;
                
                // Atualizar atributos da linha
                row.setAttribute('data-status', 'approved');
                
                // Recarregar estatísticas após 1 segundo
                setTimeout(() => location.reload(), 1000);
            }
        });
        
        // Fechar modal ao clicar fora
        approveModal.addEventListener('click', (e) => {
            if (e.target === approveModal) {
                approveModal.style.display = 'none';
            }
        });
    }
    
    // Modal de rejeição
    const rejectButtons = document.querySelectorAll('.btn-reject');
    const rejectModal = document.getElementById('rejectModal');
    const rejectNominationTitle = document.getElementById('rejectNominationTitle');
    const cancelRejectBtn = document.getElementById('cancelRejectBtn');
    const confirmRejectBtn = document.getElementById('confirmRejectBtn');
    const rejectReason = document.getElementById('rejectReason');
    
    let nominationToReject = null;
    
    rejectButtons.forEach(button => {
        button.addEventListener('click', function() {
            nominationToReject = this.getAttribute('data-id');
            const nominationTitle = this.getAttribute('data-title');
            rejectNominationTitle.textContent = nominationTitle;
            rejectModal.style.display = 'flex';
        });
    });
    
    if (rejectModal) {
        const modalClose = rejectModal.querySelector('.modal-close');
        modalClose.addEventListener('click', () => {
            rejectModal.style.display = 'none';
        });
        
        cancelRejectBtn.addEventListener('click', () => {
            rejectModal.style.display = 'none';
        });
        
        confirmRejectBtn.addEventListener('click', () => {
            if (nominationToReject) {
                const reason = rejectReason.value;
                const notes = document.getElementById('rejectNotes').value;
                
                if (!reason) {
                    alert('Veuillez sélectionner un motif de rejet.');
                    rejectReason.focus();
                    return;
                }
                
                // Aqui você faria a requisição AJAX para rejeitar
                console.log('Rejeitando nomeação:', nominationToReject, 'Motivo:', reason, 'Notas:', notes);
                
                // Simulação de sucesso
                rejectModal.style.display = 'none';
                
                // Atualizar visualmente
                const button = document.querySelector(`.btn-reject[data-id="${nominationToReject}"]`);
                const row = button.closest('tr');
                const statusCell = row.querySelector('td:nth-child(5)');
                const actionButtons = row.querySelector('.action-buttons');
                
                // Atualizar status para "rejected"
                statusCell.innerHTML = `
                    <span class="status-badge status-rejected">
                        <i class="fas fa-times-circle"></i> Rejetée
                    </span>
                `;
                
                // Remover botões de aprovação/rejeição
                const approveBtn = actionButtons.querySelector('.btn-approve');
                const rejectBtn = actionButtons.querySelector('.btn-reject');
                if (approveBtn) approveBtn.remove();
                if (rejectBtn) rejectBtn.remove();
                
                // Atualizar atributos da linha
                row.setAttribute('data-status', 'rejected');
                
                // Recarregar estatísticas após 1 segundo
                setTimeout(() => location.reload(), 1000);
            }
        });
        
        // Fechar modal ao clicar fora
        rejectModal.addEventListener('click', (e) => {
            if (e.target === rejectModal) {
                rejectModal.style.display = 'none';
            }
        });
    }
    
    // Modal de exclusão
    const deleteButtons = document.querySelectorAll('.btn-delete');
    const deleteModal = document.getElementById('deleteModal');
    const deleteNominationTitle = document.getElementById('deleteNominationTitle');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    
    let nominationToDelete = null;
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            nominationToDelete = this.getAttribute('data-id');
            const nominationTitle = this.getAttribute('data-title');
            deleteNominationTitle.textContent = nominationTitle;
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
            if (nominationToDelete) {
                // Aqui você faria a requisição AJAX para deletar
                console.log('Deletando nomeação:', nominationToDelete);
                
                // Simulação de sucesso
                deleteModal.style.display = 'none';
                
                // Remover a linha da tabela
                const button = document.querySelector(`.btn-delete[data-id="${nominationToDelete}"]`);
                const row = button.closest('tr');
                row.style.opacity = '0.5';
                row.style.transition = 'opacity 0.3s';
                
                setTimeout(() => {
                    row.remove();
                    
                    // Verificar se a tabela está vazia
                    const remainingRows = document.querySelectorAll('#nominationsTable tbody tr').length;
                    if (remainingRows === 0) {
                        const tableContainer = document.querySelector('.nominations-table .table-responsive');
                        tableContainer.innerHTML = `
                            <div class="empty-state">
                                <i class="fas fa-award"></i>
                                <h3>Aucune nomination trouvée</h3>
                                <p>Il n'y a pas encore de nominations créées.</p>
                                <a href="create-nomination.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Créer une nomination
                                </a>
                            </div>
                        `;
                    }
                    
                    // Recarregar estatísticas
                    location.reload();
                }, 300);
            }
        });
        
        // Fechar modal ao clicar fora
        deleteModal.addEventListener('click', (e) => {
            if (e.target === deleteModal) {
                deleteModal.style.display = 'none';
            }
        });
    }
    
    // Validação do formulário de edição
    const editNominationForm = document.getElementById('editNominationForm');
    
    if (editNominationForm) {
        editNominationForm.addEventListener('submit', function(e) {
            // Validação da argumentação
            const argumentation = document.getElementById('argumentation');
            if (argumentation && argumentation.value.length < 200) {
                e.preventDefault();
                alert('L\'argumentation doit contenir au moins 200 caractères.');
                argumentation.focus();
                return false;
            }
            
            // Validação do link
            const lienContenu = document.getElementById('lien_contenu');
            if (lienContenu && lienContenu.value) {
                try {
                    new URL(lienContenu.value);
                } catch (error) {
                    e.preventDefault();
                    alert('Veuillez entrer un lien valide (commençant par http:// ou https://).');
                    lienContenu.focus();
                    return false;
                }
            }
            
            // Validação da imagem
            const imageFile = document.getElementById('image_file');
            if (imageFile && imageFile.files.length > 0) {
                const file = imageFile.files[0];
                const maxSize = 2 * 1024 * 1024; // 2MB
                
                if (file.size > maxSize) {
                    e.preventDefault();
                    alert('L\'image ne peut pas dépasser 2MB.');
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
    
    // Ordenação da tabela
    const tableHeaders = document.querySelectorAll('#nominationsTable th');
    
    tableHeaders.forEach((header, index) => {
        if (index < tableHeaders.length - 1) { // Excluir coluna de ações
            header.style.cursor = 'pointer';
            header.addEventListener('click', function() {
                sortTable(index);
            });
        }
    });
    
    function sortTable(columnIndex) {
        const table = document.getElementById('nominationsTable');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        // Determinar direção da ordenação
        const currentDir = table.getAttribute('data-sort-dir') || 'asc';
        const newDir = currentDir === 'asc' ? 'desc' : 'asc';
        table.setAttribute('data-sort-dir', newDir);
        
        // Remover indicadores de ordenação anteriores
        tableHeaders.forEach(header => {
            header.classList.remove('sort-asc', 'sort-desc');
        });
        
        // Adicionar indicador à coluna atual
        tableHeaders[columnIndex].classList.add(`sort-${newDir}`);
        
        // Ordenar linhas
        rows.sort((a, b) => {
            let aValue = getCellValue(a, columnIndex);
            let bValue = getCellValue(b, columnIndex);
            
            // Converter para números se possível
            if (!isNaN(aValue) && !isNaN(bValue)) {
                aValue = parseFloat(aValue);
                bValue = parseFloat(bValue);
            }
            
            if (newDir === 'asc') {
                return aValue > bValue ? 1 : -1;
            } else {
                return aValue < bValue ? 1 : -1;
            }
        });
        
        // Reorganizar linhas na tabela
        rows.forEach(row => tbody.appendChild(row));
    }
    
    function getCellValue(row, columnIndex) {
        const cell = row.cells[columnIndex];
        
        if (columnIndex === 4) { // Coluna de status
            const badge = cell.querySelector('.status-badge');
            return badge ? badge.textContent.trim() : '';
        } else if (columnIndex === 5) { // Coluna de votos
            const strong = cell.querySelector('strong');
            return strong ? parseInt(strong.textContent.replace(/,/g, '')) : 0;
        } else if (columnIndex === 0) { // Coluna de candidato
            const strong = cell.querySelector('strong');
            return strong ? strong.textContent : '';
        } else if (columnIndex === 2) { // Coluna de categoria
            const badge = cell.querySelector('.badge-category');
            return badge ? badge.textContent : '';
        } else if (columnIndex === 3) { // Coluna de plataforma
            const tag = cell.querySelector('.platform-tag');
            return tag ? tag.textContent : '';
        } else {
            const strong = cell.querySelector('strong');
            return strong ? strong.textContent : cell.textContent;
        }
    }
});