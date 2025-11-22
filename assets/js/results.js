// Results Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    const tabButtons = document.querySelectorAll('.tab-btn');
    const resultCards = document.querySelectorAll('.result-card');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            tabButtons.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            const filterValue = this.getAttribute('data-tab');
            
            // Filter results
            resultCards.forEach(card => {
                if (filterValue === 'all') {
                    card.style.display = 'block';
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 50);
                } else {
                    const platform = card.getAttribute('data-platform');
                    if (platform === filterValue) {
                        card.style.display = 'block';
                        setTimeout(() => {
                            card.style.opacity = '1';
                            card.style.transform = 'translateY(0)';
                        }, 50);
                    } else {
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

    // Edition selector functionality
    const editionSelect = document.getElementById('editionSelect');
    
    editionSelect.addEventListener('change', function() {
        // Animation de chargement
        const mainContent = document.querySelector('.main-content');
        mainContent.style.opacity = '0.7';
        
        // Simulation de chargement des données
        setTimeout(() => {
            mainContent.style.opacity = '1';
            // Ici tu pourrais faire un appel AJAX pour charger les données de l'édition sélectionnée
            console.log(`Chargement des données de l'édition ${this.value}`);
        }, 1000);
    });

    // Animation on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe elements for animation
    const animatedElements = document.querySelectorAll('.result-card, .stat-card, .top-winner');
    
    animatedElements.forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(30px)';
        element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(element);
    });

    // Winner item hover effects
    const winnerItems = document.querySelectorAll('.winner-item');
    
    winnerItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(10px)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });

    // Share functionality (exemple)
    const shareButtons = document.createElement('div');
    shareButtons.className = 'share-buttons';
    shareButtons.innerHTML = `
        <button class="share-btn" onclick="shareOnTwitter()">
            <i class="fab fa-twitter"></i>
            Partager sur Twitter
        </button>
        <button class="share-btn" onclick="shareOnFacebook()">
            <i class="fab fa-facebook"></i>
            Partager sur Facebook
        </button>
    `;
    
    // Ajoute les boutons de partage après le hero section
    document.querySelector('.results-hero').appendChild(shareButtons);
});

// Functions de partage (exemple)
function shareOnTwitter() {
    const text = "Découvrez les résultats des Social Media Awards 2025 !";
    const url = window.location.href;
    window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`, '_blank');
}

function shareOnFacebook() {
    const url = window.location.href;
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank');
}