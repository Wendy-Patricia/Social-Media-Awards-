// JavaScript pour la page des Catégories - Social Media Awards

// Attend que le DOM soit complètement chargé
document.addEventListener('DOMContentLoaded', function() {
    // Fonctionnalité de filtrage des catégories
    const filterButtons = document.querySelectorAll('.filter-btn');
    const categoryCards = document.querySelectorAll('.category-card');

    // Ajoute les écouteurs d'événements pour chaque bouton de filtre
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Retire la classe active de tous les boutons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            
            // Ajoute la classe active au bouton cliqué
            this.classList.add('active');
            
            const filterValue = this.getAttribute('data-filter');
            
            // Filtre les cartes de catégories selon la plateforme sélectionnée
            categoryCards.forEach(card => {
                if (filterValue === 'all') {
                    // Affiche toutes les cartes
                    card.style.display = 'block';
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 50);
                } else {
                    const platforms = card.getAttribute('data-platform').split(',');
                    if (platforms.includes(filterValue)) {
                        // Affiche les cartes correspondant au filtre
                        card.style.display = 'block';
                        setTimeout(() => {
                            card.style.opacity = '1';
                            card.style.transform = 'translateY(0)';
                        }, 50);
                    } else {
                        // Cache les cartes ne correspondant pas au filtre avec animation
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(20px)';
                        setTimeout(() => {
                            card.style.display = 'none';
                        }, 300);
                    }
                }
            });
        });
    });

    // Fonctionnalité des boutons "Voir les Nominés"
    const viewNomineeButtons = document.querySelectorAll('.btn-view-nominees');
    
    viewNomineeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const categoryCard = this.closest('.category-card');
            const categoryName = categoryCard.querySelector('h3').textContent;
            
            // Animation de feedback au clic
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
            
            // Redirection vers la page des nominés avec paramètre de catégorie
            setTimeout(() => {
                window.location.href = `nominees.php?category=${encodeURIComponent(categoryName)}`;
            }, 300);
        });
    });

    // Animation au défilement (scroll)
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    // Observer pour l'animation des cartes au défilement
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Applique l'animation à chaque carte de catégorie
    categoryCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });

    // Amélioration des effets de survol
    categoryCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.zIndex = '10'; // Met la carte au premier plan
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.zIndex = '1'; // Remet la carte en arrière-plan
        });
    });

    // Console log pour le débogage (peut être retiré en production)
    console.log('Page des catégories initialisée avec succès');
});