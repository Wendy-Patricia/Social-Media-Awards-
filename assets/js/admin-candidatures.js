// admin-applications.js
document.addEventListener('DOMContentLoaded', function() {
    // Toggle des filtres avancés
    const toggleFiltersBtn = document.getElementById('toggleFilters');
    const filtersContent = document.getElementById('filtersContent');
    
    if (toggleFiltersBtn && filtersContent) {
        let filtersVisible = false;
        
        toggleFiltersBtn.addEventListener('click', function() {
            filtersVisible = !filtersVisible;
            filtersContent.style.display = filtersVisible ? 'block' : 'none';
            
            const icon = this.querySelector('i');
            icon.className = filtersVisible ? 'fas fa-chevron-up' : 'fas fa-chevron-down';
        });
        
        // Cacher par défaut
        filtersContent.style.display = 'none';
    }
    
    // Sélection multiple pour les filtres
    const multiSelects = document.querySelectorAll('select[multiple]');
    multiSelects.forEach(select => {
        select.addEventListener('mousedown', function(e) {
            e.preventDefault();
            
            const option = e.target;
            if (option.tagName === 'OPTION') {
                option.selected = !option.selected;
                
                // Déclencher un événement change
                const event = new Event('change');
                this.dispatchEvent(event);
            }
        });
    });
    
    // Toggle entre vue tableau et cartes
    const viewOptions = document.querySelectorAll('.view-option');
    const tableView = document.getElementById('tableView');
    const cardsView = document.getElementById('cardsView');
    
    viewOptions.forEach(option => {
        option.addEventListener('click', function() {
            const view = this.getAttribute('data-view');
            
            // Mettre à jour les boutons actifs
            viewOptions.forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
            
            // Changer la vue
            if (view === 'table') {
                tableView.classList.add('active');
                cardsView.classList.remove('active');
            } else {
                tableView.classList.remove('active');
                cardsView.classList.add('active');
            }
        });
    });
    
    // Sélection multiple
    const selectAllCheckbox = document.getElementById('selectAll');
    const rowCheckboxes = document.querySelectorAll('.select-row');
    const cardCheckboxes = document.querySelectorAll('.card-select');
    const applyBulkActionBtn = document.getElementById('applyBulkAction');
    const bulkActionSelect = document.getElementById('bulkAction');
    
    // Fonction pour mettre à jour le bouton d'action groupée
    function updateBulkActionButton() {
        const selectedCount = document.querySelectorAll('.select-row:checked, .card-select:checked').length;
        const selectedCountElement = document.getElementById('selectedCount');
        
        if (selectedCountElement) {
            selectedCountElement.textContent = selectedCount;
        }
        
        if (applyBulkActionBtn) {
            applyBulkActionBtn.disabled = selectedCount === 0;
        }
    }
    
    // Sélectionner/désélectionner tout
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            
            rowCheckboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });
            
            cardCheckboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });
            
            updateBulkActionButton();
        });
    }
    
    // Mettre à jour "Sélectionner tout" quand les cases individuelles changent
    rowCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectAllCheckbox();
            updateBulkActionButton();
        });
    });
    
    cardCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectAllCheckbox();
            updateBulkActionButton();
        });
    });
    
    function updateSelectAllCheckbox() {
        if (!selectAllCheckbox) return;
        
        const allCheckboxes = document.querySelectorAll('.select-row, .card-select');
        const checkedCount = document.querySelectorAll('.select-row:checked, .card-select:checked').length;
        
        selectAllCheckbox.checked = checkedCount === allCheckboxes.length;
        selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < allCheckboxes.length;
    }
    
    // Action groupée
    if (bulkActionSelect && applyBulkActionBtn) {
        bulkActionSelect.addEventListener('change', function() {
            applyBulkActionBtn.disabled = !this.value;
        });
        
        applyBulkActionBtn.addEventListener('click', function() {
            const action = bulkActionSelect.value;
            const selectedIds = getSelectedApplicationIds();
            
            if (selectedIds.length === 0 || !action) {
                alert('Veuillez sélectionner des candidatures et une action.');
                return;
            }
            
            switch (action) {
                case 'set_review':
                case 'set_approved':
                case 'set_rejected':
                    showBulkModal(action, selectedIds);
                    break;
                case 'export':
                    exportSelectedApplications(selectedIds);
                    break;
                case 'delete':
                    deleteSelectedApplications(selectedIds);
                    break;
            }
        });
    }
    
    function getSelectedApplicationIds() {
        const selectedIds = [];
        
        // Récupérer depuis la vue tableau
        document.querySelectorAll('.select-row:checked').forEach(checkbox => {
            selectedIds.push(checkbox.value);
        });
        
        // Récupérer depuis la vue cartes
        document.querySelectorAll('.card-select:checked').forEach(checkbox => {
            selectedIds.push(checkbox.value);
        });
        
        return selectedIds;
    }
    
    // Modal d'action groupée
    const bulkModal = document.getElementById('bulkModal');
    const cancelBulkBtn = document.getElementById('cancelBulkBtn');
    const confirmBulkBtn = document.getElementById('confirmBulkBtn');
    
    let currentBulkAction = null;
    let currentSelectedIds = [];
    
    function showBulkModal(action, selectedIds) {
        currentBulkAction = action;
        currentSelectedIds = selectedIds;
        
        const actionText = getActionText(action);
        const selectedCount = selectedIds.length;
        
        document.getElementById('bulkModalText').innerHTML = 
            `Appliquer l'action <strong>${actionText}</strong> à <span id="selectedCount">${selectedCount}</span> candidature(s) sélectionnée(s) :`;
        
        bulkModal.style.display = 'flex';
    }
    
    function getActionText(action) {
        switch (action) {
            case 'set_review': return 'Mettre en analyse';
            case 'set_approved': return 'Approuver';
            case 'set_rejected': return 'Rejeter';
            default: return action;
        }
    }
    
    if (bulkModal) {
        const modalClose = bulkModal.querySelector('.modal-close');
        modalClose.addEventListener('click', () => {
            bulkModal.style.display = 'none';
        });
        
        cancelBulkBtn.addEventListener('click', () => {
            bulkModal.style.display = 'none';
        });
        
        confirmBulkBtn.addEventListener('click', () => {
            if (currentBulkAction && currentSelectedIds.length > 0) {
                const status = document.getElementById('bulkStatus').value;
                const notes = document.getElementById('bulkNotes').value;
                
                // Simuler la requête AJAX
                console.log(`Action groupée: ${currentBulkAction} -> ${status}`);
                console.log('IDs:', currentSelectedIds);
                console.log('Notes:', notes);
                
                // Fermer le modal
                bulkModal.style.display = 'none';
                
                // Afficher un message de confirmation
                alert(`Action appliquée à ${currentSelectedIds.length} candidature(s).`);
                
                // Recharger la page après 1 seconde
                setTimeout(() => location.reload(), 1000);
            }
        });
        
        // Fechar modal ao clicar fora
        bulkModal.addEventListener('click', (e) => {
            if (e.target === bulkModal) {
                bulkModal.style.display = 'none';
            }
        });
    }
    
    // Export des candidatures sélectionnées
    function exportSelectedApplications(selectedIds) {
        console.log('Export des candidatures:', selectedIds);
        
        // Simuler l'export
        const data = {
            ids: selectedIds,
            format: 'csv',
            timestamp: new Date().toISOString()
        };
        
        // Créer un blob et télécharger
        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        
        a.href = url;
        a.download = `candidatures_${new Date().toISOString().slice(0,10)}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        
        alert(`Export de ${selectedIds.length} candidature(s) démarré.`);
    }
    
    // Suppression des candidatures sélectionnées
    function deleteSelectedApplications(selectedIds) {
        if (confirm(`Êtes-vous sûr de vouloir supprimer ${selectedIds.length} candidature(s) ?`)) {
            console.log('Suppression des candidatures:', selectedIds);
            
            // Simuler la suppression
            selectedIds.forEach(id => {
                const row = document.querySelector(`tr[data-id="${id}"]`);
                const card = document.querySelector(`.application-card[data-id="${id}"]`);
                
                if (row) row.remove();
                if (card) card.remove();
            });
            
            // Mettre à jour le compteur
            updateBulkActionButton();
            
            alert(`${selectedIds.length} candidature(s) supprimée(s).`);
        }
    }
    
    // Modal de changement de statut (pour actions individuelles)
    const statusModal = document.getElementById('statusModal');
    const cancelStatusBtn = document.getElementById('cancelStatusBtn');
    const confirmStatusBtn = document.getElementById('confirmStatusBtn');
    const statusOptions = document.querySelectorAll('.status-option');
    const rejectionReasonGroup = document.getElementById('rejectionReasonGroup');
    
    let currentApplicationId = null;
    let currentApplicationTitle = '';
    let selectedStatus = null;
    
    // Configurer les boutons d'action individuels
    const reviewButtons = document.querySelectorAll('.btn-review');
    const approveButtons = document.querySelectorAll('.btn-approve');
    const rejectButtons = document.querySelectorAll('.btn-reject');
    const deleteButtons = document.querySelectorAll('.btn-delete');
    
    function setupStatusButton(buttons, status) {
        buttons.forEach(button => {
            button.addEventListener('click', function() {
                currentApplicationId = this.getAttribute('data-id');
                currentApplicationTitle = this.getAttribute('data-title') || 'Candidature';
                selectedStatus = status;
                
                showStatusModal(status, currentApplicationTitle);
            });
        });
    }
    
    setupStatusButton(reviewButtons, 'review');
    setupStatusButton(approveButtons, 'approved');
    setupStatusButton(rejectButtons, 'rejected');
    
    // Configurer les boutons de suppression
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const title = this.getAttribute('data-title');
            
            if (confirm(`Êtes-vous sûr de vouloir supprimer la candidature "${title}" ?`)) {
                console.log('Suppression de la candidature:', id);
                
                // Simuler la suppression
                const row = this.closest('tr');
                if (row) row.remove();
                
                alert('Candidature supprimée.');
            }
        });
    });
    
    // Configurer les options de statut dans le modal
    statusOptions.forEach(option => {
        option.addEventListener('click', function() {
            const status = this.getAttribute('data-status');
            selectedStatus = status;
            
            // Mettre à jour la sélection visuelle
            statusOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            
            // Afficher/masquer le champ de motif de rejet
            if (status === 'rejected') {
                rejectionReasonGroup.style.display = 'block';
            } else {
                rejectionReasonGroup.style.display = 'none';
            }
        });
    });
    
    function showStatusModal(status, title) {
        selectedStatus = status;
        
        // Mettre à jour le texte du modal
        document.getElementById('statusModalText').textContent = 
            `Changer le statut de la candidature "${title}" :`;
        
        // Sélectionner l'option correspondante
        statusOptions.forEach(option => {
            if (option.getAttribute('data-status') === status) {
                option.classList.add('selected');
                
                // Afficher/masquer le champ de motif de rejet
                if (status === 'rejected') {
                    rejectionReasonGroup.style.display = 'block';
                } else {
                    rejectionReasonGroup.style.display = 'none';
                }
            } else {
                option.classList.remove('selected');
            }
        });
        
        statusModal.style.display = 'flex';
    }
    
    if (statusModal) {
        const modalClose = statusModal.querySelector('.modal-close');
        modalClose.addEventListener('click', () => {
            statusModal.style.display = 'none';
        });
        
        cancelStatusBtn.addEventListener('click', () => {
            statusModal.style.display = 'none';
        });
        
        confirmStatusBtn.addEventListener('click', () => {
            if (currentApplicationId && selectedStatus) {
                const notes = document.getElementById('statusNotes').value;
                
                // Vérifier le motif de rejet si nécessaire
                if (selectedStatus === 'rejected') {
                    const rejectionReason = document.getElementById('rejectionReason').value;
                    if (!rejectionReason) {
                        alert('Veuillez sélectionner un motif de rejet.');
                        return;
                    }
                }
                
                // Simuler la requête AJAX
                console.log('Changement de statut:', currentApplicationId, '->', selectedStatus);
                console.log('Notes:', notes);
                
                // Fermer le modal
                statusModal.style.display = 'none';
                
                // Mettre à jour l'interface utilisateur
                updateApplicationStatus(currentApplicationId, selectedStatus);
                
                alert('Statut mis à jour avec succès.');
            }
        });
        
        // Fechar modal ao clicar fora
        statusModal.addEventListener('click', (e) => {
            if (e.target === statusModal) {
                statusModal.style.display = 'none';
            }
        });
    }
    
    // Fonction pour mettre à jour le statut d'une candidature dans l'interface
    function updateApplicationStatus(applicationId, newStatus) {
        // Mettre à jour dans la vue tableau
        const tableRow = document.querySelector(`tr[data-id="${applicationId}"]`);
        if (tableRow) {
            const statusCell = tableRow.querySelector('td:nth-child(5)');
            const actionButtons = tableRow.querySelector('.action-buttons');
            
            // Mettre à jour le badge de statut
            let statusClass = '';
            let statusIcon = '';
            let statusText = '';
            
            switch (newStatus) {
                case 'review':
                    statusClass = 'status-review';
                    statusIcon = 'search';
                    statusText = 'En analyse';
                    break;
                case 'approved':
                    statusClass = 'status-approved';
                    statusIcon = 'check-circle';
                    statusText = 'Approuvée';
                    break;
                case 'rejected':
                    statusClass = 'status-rejected';
                    statusIcon = 'times-circle';
                    statusText = 'Rejetée';
                    break;
            }
            
            statusCell.innerHTML = `
                <span class="status-badge ${statusClass}">
                    <i class="fas fa-${statusIcon}"></i> ${statusText}
                </span>
            `;
            
            // Mettre à jour les boutons d'action
            if (newStatus === 'approved' || newStatus === 'rejected') {
                const reviewBtn = actionButtons.querySelector('.btn-review');
                const approveBtn = actionButtons.querySelector('.btn-approve');
                const rejectBtn = actionButtons.querySelector('.btn-reject');
                
                if (reviewBtn) reviewBtn.remove();
                if (approveBtn) approveBtn.remove();
                if (rejectBtn) rejectBtn.remove();
            }
            
            // Mettre à jour l'attribut data-status
            tableRow.setAttribute('data-status', newStatus);
        }
        
        // Mettre à jour dans la vue cartes
        const card = document.querySelector(`.application-card[data-id="${applicationId}"]`);
        if (card) {
            const statusBadge = card.querySelector('.status-badge');
            const cardHeader = card.querySelector('.card-header');
            
            // Mettre à jour le badge de statut
            let statusClass = '';
            let statusText = '';
            
            switch (newStatus) {
                case 'review':
                    statusClass = 'status-review';
                    statusText = 'En analyse';
                    break;
                case 'approved':
                    statusClass = 'status-approved';
                    statusText = 'Approuvée';
                    break;
                case 'rejected':
                    statusClass = 'status-rejected';
                    statusText = 'Rejetée';
                    break;
            }
            
            statusBadge.className = `status-badge ${statusClass}`;
            statusBadge.innerHTML = `<i class="fas fa-circle"></i> ${statusText}`;
            
            // Mettre à jour la couleur de l'en-tête
            cardHeader.classList.remove('status-pending', 'status-review', 'status-approved', 'status-rejected');
            cardHeader.classList.add(statusClass);
            
            // Mettre à jour les boutons d'action
            const actionButtons = card.querySelector('.card-actions');
            if (newStatus === 'approved' || newStatus === 'rejected') {
                const reviewBtn = actionButtons.querySelector('.btn-review');
                const approveBtn = actionButtons.querySelector('.btn-approve');
                
                if (reviewBtn) reviewBtn.remove();
                if (approveBtn) approveBtn.remove();
            }
        }
    }
    
    // Filtres avancés
    const applyFiltersBtn = document.getElementById('applyFilters');
    const resetFiltersBtn = document.getElementById('resetFilters');
    const quickSearch = document.getElementById('quickSearch');
    
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', applyAdvancedFilters);
    }
    
    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', resetAdvancedFilters);
    }
    
    if (quickSearch) {
        quickSearch.addEventListener('input', debounce(applyQuickSearch, 300));
    }
    
    function applyAdvancedFilters() {
        const dateStart = document.getElementById('dateStart').value;
        const dateEnd = document.getElementById('dateEnd').value;
        const statusFilter = document.getElementById('statusFilter');
        const categoryFilter = document.getElementById('categoryFilter');
        const platformFilter = document.getElementById('platformFilter');
        const urgencyFilter = document.getElementById('urgencyFilter').value;
        const qualityMin = document.getElementById('qualityMin').value;
        const qualityMax = document.getElementById('qualityMax').value;
        const typeFilter = document.getElementById('typeFilter').value;
        
        // Récupérer les valeurs sélectionnées
        const selectedStatuses = Array.from(statusFilter.selectedOptions).map(opt => opt.value);
        const selectedCategories = Array.from(categoryFilter.selectedOptions).map(opt => opt.value);
        const selectedPlatforms = Array.from(platformFilter.selectedOptions).map(opt => opt.value);
        
        // Filtrer les lignes du tableau
        const tableRows = document.querySelectorAll('#applicationsTable tbody tr');
        const cards = document.querySelectorAll('.application-card');
        
        let visibleCount = 0;
        
        // Filtrer les lignes du tableau
        tableRows.forEach(row => {
            let show = true;
            
            // Filtre par date
            if (dateStart || dateEnd) {
                const rowDate = new Date(parseInt(row.getAttribute('data-date')) * 1000);
                const startDate = dateStart ? new Date(dateStart) : null;
                const endDate = dateEnd ? new Date(dateEnd + 'T23:59:59') : null;
                
                if (startDate && rowDate < startDate) show = false;
                if (endDate && rowDate > endDate) show = false;
            }
            
            // Filtre par statut
            if (selectedStatuses.length > 0) {
                const rowStatus = row.getAttribute('data-status');
                if (!selectedStatuses.includes(rowStatus)) show = false;
            }
            
            // Filtre par catégorie (simplifié)
            if (selectedCategories.length > 0) {
                const rowCategory = row.getAttribute('data-category');
                // Vérifier si la catégorie contient un des termes sélectionnés
                const matches = selectedCategories.some(cat => 
                    rowCategory.toLowerCase().includes(cat.toLowerCase())
                );
                if (!matches) show = false;
            }
            
            // Filtre par plateforme
            if (selectedPlatforms.length > 0) {
                const rowPlatform = row.getAttribute('data-platform');
                if (!selectedPlatforms.includes(rowPlatform)) show = false;
            }
            
            // Filtre par urgence
            if (urgencyFilter) {
                const rowUrgency = row.getAttribute('data-urgency');
                if (rowUrgency !== urgencyFilter) show = false;
            }
            
            // Filtre par score
            const rowScore = parseInt(row.getAttribute('data-score'));
            if (qualityMin && rowScore < parseInt(qualityMin)) show = false;
            if (qualityMax && rowScore > parseInt(qualityMax)) show = false;
            
            // Filtre par type
            if (typeFilter) {
                const typeBadge = row.querySelector('.badge-source');
                if (typeBadge) {
                    const rowType = typeBadge.textContent.trim().toLowerCase();
                    const filterType = typeFilter === 'auto' ? 'auto' : 'manuelle';
                    if (rowType !== filterType) show = false;
                }
            }
            
            // Afficher/masquer la ligne
            row.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });
        
        // Filtrer les cartes (logique similaire)
        cards.forEach(card => {
            // Logique de filtrage similaire pour les cartes
            // (implémentation simplifiée)
        });
        
        // Mettre à jour le compteur de résultats
        const resultsCount = document.getElementById('resultsCount');
        if (resultsCount) {
            resultsCount.textContent = `${visibleCount} candidature(s) trouvée(s)`;
        }
    }
    
    function resetAdvancedFilters() {
        // Réinitialiser tous les champs de filtre
        document.getElementById('dateStart').value = '';
        document.getElementById('dateEnd').value = '';
        document.getElementById('statusFilter').selectedIndex = -1;
        document.getElementById('categoryFilter').selectedIndex = -1;
        document.getElementById('platformFilter').selectedIndex = -1;
        document.getElementById('urgencyFilter').value = '';
        document.getElementById('qualityMin').value = '';
        document.getElementById('qualityMax').value = '';
        document.getElementById('typeFilter').value = '';
        
        // Réappliquer les filtres (cela affichera tout)
        applyAdvancedFilters();
    }
    
    function applyQuickSearch() {
        const searchTerm = quickSearch.value.toLowerCase();
        
        const tableRows = document.querySelectorAll('#applicationsTable tbody tr');
        const cards = document.querySelectorAll('.application-card');
        
        let visibleCount = 0;
        
        // Filtrer les lignes du tableau
        tableRows.forEach(row => {
            let show = false;
            
            // Rechercher dans le titre
            const title = row.getAttribute('data-title');
            if (title && title.includes(searchTerm)) {
                show = true;
            }
            
            // Rechercher dans le nom du candidat
            const candidate = row.getAttribute('data-candidate');
            if (candidate && candidate.includes(searchTerm)) {
                show = true;
            }
            
            // Rechercher dans l'email
            const emailCell = row.querySelector('.candidate-info .text-muted');
            if (emailCell && emailCell.textContent.toLowerCase().includes(searchTerm)) {
                show = true;
            }
            
            // Rechercher dans la catégorie
            const category = row.getAttribute('data-category');
            if (category && category.toLowerCase().includes(searchTerm)) {
                show = true;
            }
            
            // Afficher/masquer la ligne
            row.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });
        
        // Mettre à jour le compteur de résultats
        const resultsCount = document.getElementById('resultsCount');
        if (resultsCount) {
            resultsCount.textContent = `${visibleCount} candidature(s) trouvée(s)`;
        }
    }
    
    // Fonction debounce pour la recherche
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Tri du tableau
    const sortBySelect = document.getElementById('sortBy');
    if (sortBySelect) {
        sortBySelect.addEventListener('change', function() {
            const sortValue = this.value;
            sortApplications(sortValue);
        });
    }
    
    function sortApplications(sortValue) {
        const tbody = document.querySelector('#applicationsTable tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        rows.sort((a, b) => {
            let aValue, bValue;
            
            switch (sortValue) {
                case 'date_desc':
                    aValue = parseInt(a.getAttribute('data-date'));
                    bValue = parseInt(b.getAttribute('data-date'));
                    return bValue - aValue;
                    
                case 'date_asc':
                    aValue = parseInt(a.getAttribute('data-date'));
                    bValue = parseInt(b.getAttribute('data-date'));
                    return aValue - bValue;
                    
                case 'title_asc':
                    aValue = a.getAttribute('data-title').toLowerCase();
                    bValue = b.getAttribute('data-title').toLowerCase();
                    return aValue.localeCompare(bValue);
                    
                case 'title_desc':
                    aValue = a.getAttribute('data-title').toLowerCase();
                    bValue = b.getAttribute('data-title').toLowerCase();
                    return bValue.localeCompare(aValue);
                    
                case 'score_desc':
                    aValue = parseInt(a.getAttribute('data-score'));
                    bValue = parseInt(b.getAttribute('data-score'));
                    return bValue - aValue;
                    
                case 'score_asc':
                    aValue = parseInt(a.getAttribute('data-score'));
                    bValue = parseInt(b.getAttribute('data-score'));
                    return aValue - bValue;
                    
                default:
                    return 0;
            }
        });
        
        // Réorganiser les lignes
        rows.forEach(row => tbody.appendChild(row));
    }
    
    // Initialiser
    updateBulkActionButton();
});