// Variables globales
let categoryToDelete = null;
let categoryNameToDelete = '';

// Attendre que le DOM soit chargé
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les écouteurs d'événements
    initEventListeners();
    
    // Initialiser les fonctionnalités
    initSearch();
    initFilters();
    initDeleteModal();
});

// Initialiser tous les écouteurs d'événements
function initEventListeners() {
    // Recherche en temps réel
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', searchCategories);
        
        // Bouton de recherche
        const searchBtn = searchInput.nextElementSibling;
        if (searchBtn && searchBtn.classList.contains('btn')) {
            searchBtn.addEventListener('click', searchCategories);
        }
    }
    
    // Filtres
    const editionFilter = document.getElementById('editionFilter');
    const platformFilter = document.getElementById('platformFilter');
    
    if (editionFilter) {
        editionFilter.addEventListener('change', filterCategories);
    }
    
    if (platformFilter) {
        platformFilter.addEventListener('change', filterCategories);
    }
    
    // Boutons de suppression
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            confirmDelete(id, name);
        });
    });
    
    // Fermeture du modal avec Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDeleteModal();
        }
    });
}

// Fonction de recherche
function searchCategories() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
    const rows = document.querySelectorAll('#categoriesBody tr');
    
    rows.forEach(row => {
        const name = row.getAttribute('data-name');
        const displayName = row.querySelector('td:first-child strong').textContent.toLowerCase();
        const description = row.querySelector('.text-muted').textContent.toLowerCase();
        
        // Rechercher dans le nom et la description
        if (name.includes(searchTerm) || 
            displayName.includes(searchTerm) || 
            description.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Fonction de filtrage
function filterCategories() {
    const editionFilter = document.getElementById('editionFilter').value;
    const platformFilter = document.getElementById('platformFilter').value;
    const rows = document.querySelectorAll('#categoriesBody tr');
    
    rows.forEach(row => {
        const edition = row.getAttribute('data-edition');
        const platform = row.getAttribute('data-platform');
        let showRow = true;
        
        // Appliquer le filtre d'édition
        if (editionFilter && edition !== editionFilter) {
            showRow = false;
        }
        
        // Appliquer le filtre de plateforme
        if (platformFilter && platform !== platformFilter) {
            showRow = false;
        }
        
        // Appliquer l'affichage
        if (showRow && row.style.display !== 'none') {
            row.style.display = '';
        } else if (!showRow) {
            row.style.display = 'none';
        }
    });
}

// Fonctionnalités de recherche
function initSearch() {
    // Recherche rapide avec Debounce
    let searchTimeout;
    const searchInput = document.getElementById('searchInput');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(searchCategories, 300);
        });
    }
}

// Fonctionnalités de filtrage
function initFilters() {
    // Réinitialiser les filtres
    const resetBtn = document.querySelector('.btn-reset-filters');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            document.getElementById('editionFilter').value = '';
            document.getElementById('platformFilter').value = '';
            filterCategories();
        });
    }
}

// Modal de suppression
function initDeleteModal() {
    const modal = document.getElementById('deleteModal');
    const closeBtn = modal.querySelector('.modal-close');
    const cancelBtn = document.getElementById('cancelDeleteBtn');
    
    // Fermer le modal
    closeBtn.addEventListener('click', closeDeleteModal);
    cancelBtn.addEventListener('click', closeDeleteModal);
    
    // Fermer en cliquant en dehors
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeDeleteModal();
        }
    });
    
    // Confirmer la suppression
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    confirmBtn.addEventListener('click', function(e) {
        // Remover e.preventDefault() para seguir o link
        if (categoryToDelete) {
            // O href já está definido em confirmDelete()
            // Permet redirecionamento natural
        }
    });
}

// Afficher le modal de suppression
function confirmDelete(id, name) {
    categoryToDelete = id;
    categoryNameToDelete = name;
    
    // Mettre à jour le contenu du modal
    document.getElementById('deleteCategoryName').textContent = name;
    
    // Afficher le modal
    const modal = document.getElementById('deleteModal');
    modal.style.display = 'flex';
    
    // Definir a rota para supprimer
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    confirmDeleteBtn.href = `/Social-Media-Awards-/admin/categories/supprimer?id=${id}`;
}

// Fermer le modal de suppression
function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.style.display = 'none';
    categoryToDelete = null;
    categoryNameToDelete = '';
}

// Fonction utilitaire pour formater les nombres
function formatNumber(num) {
    return new Intl.NumberFormat('fr-FR').format(num);
}

// Exporter les fonctions pour une utilisation externe si nécessaire
window.categoryManager = {
    searchCategories,
    filterCategories,
    confirmDelete,
    closeDeleteModal,
    formatNumber
};