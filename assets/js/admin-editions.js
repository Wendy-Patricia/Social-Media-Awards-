// assets/js/admin-editions.js

document.addEventListener('DOMContentLoaded', function() {
    // Fonction de confirmation de suppression
    window.confirmDelete = function(id, name) {
        if (confirm(`Voulez-vous vraiment supprimer l'édition "${name}" ?\n\n⚠️ Cette action supprimera également toutes les catégories et candidatures associées.`)) {
            window.location.href = `gerer-editions.php?delete=${id}`;
        }
        return false;
    };
    
    // Suppression avec confirmation
    const deleteButtons = document.querySelectorAll('.action-btn.delete');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name') || 'cette édition';
            
            if (confirm(`Voulez-vous vraiment supprimer "${name}" ?\n\n⚠️ Cette action est irréversible.`)) {
                window.location.href = `gerer-editions.php?delete=${id}`;
            }
        });
    });
});