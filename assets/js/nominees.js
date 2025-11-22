// Nominees Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const platformFilter = document.getElementById('platformFilter');
    const nomineeCards = document.querySelectorAll('.nominee-card');

    // Filter function
    function filterNominees() {
        const searchTerm = searchInput.value.toLowerCase();
        const categoryValue = categoryFilter.value;
        const platformValue = platformFilter.value;

        nomineeCards.forEach(card => {
            const nomineeName = card.querySelector('h3').textContent.toLowerCase();
            const nomineeCategory = card.getAttribute('data-category');
            const nomineePlatform = card.getAttribute('data-platform');
            
            const matchesSearch = nomineeName.includes(searchTerm);
            const matchesCategory = !categoryValue || nomineeCategory === categoryValue;
            const matchesPlatform = !platformValue || nomineePlatform === platformValue;

            if (matchesSearch && matchesCategory && matchesPlatform) {
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
        });
    }

    // Event listeners
    searchInput.addEventListener('input', filterNominees);
    categoryFilter.addEventListener('change', filterNominees);
    platformFilter.addEventListener('change', filterNominees);

    // Vote button functionality
    const voteButtons = document.querySelectorAll('.btn-vote');
    
    voteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const nomineeCard = this.closest('.nominee-card');
            const nomineeName = nomineeCard.querySelector('h3').textContent;
            
            // Animation de clic
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Vote en cours...';
            this.disabled = true;
            
            // Simulation d'envoi de vote
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-check"></i> Vote envoyé!';
                this.style.background = 'var(--success-color)';
                
                // Notification
                showNotification(`Vote pour ${nomineeName} enregistré !`, 'success');
                
                // Réinitialiser après 2 secondes
                setTimeout(() => {
                    this.innerHTML = 'Voter';
                    this.disabled = false;
                    this.style.background = '';
                }, 2000);
            }, 1500);
        });
    });

    // URL parameters for filtering
    const urlParams = new URLSearchParams(window.location.search);
    const categoryParam = urlParams.get('category');
    
    if (categoryParam) {
        categoryFilter.value = getCategorySlug(categoryParam);
        filterNominees();
    }

    function getCategorySlug(categoryName) {
        const slugs = {
            'Créateur Révélation de l\'Année': 'revelation',
            'Meilleur Podcast en Ligne': 'podcast',
            'Campagne Branded Content': 'branded'
            // Ajouter d'autres mappings au besoin
        };
        return slugs[categoryName] || '';
    }

    // Animation on scroll
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    // Observe nominee cards for animation
    nomineeCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease, display 0.3s ease';
        observer.observe(card);
    });

    // Notification function
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        notification.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            background: ${type === 'success' ? '#32D583' : '#FF6668'};
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            z-index: 10000;
            display: flex;
            align-items: center;
            gap: 15px;
            max-width: 400px;
            animation: slideInRight 0.3s ease;
        `;
        
        const closeButton = notification.querySelector('.notification-close');
        closeButton.addEventListener('click', () => {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        });
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }
        }, 4000);
        
        document.body.appendChild(notification);
        
        // Add styles if not already present
        if (!document.querySelector('#notification-styles')) {
            const style = document.createElement('style');
            style.id = 'notification-styles';
            style.textContent = `
                @keyframes slideInRight {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes slideOutRight {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
                .notification-close {
                    background: none;
                    border: none;
                    color: white;
                    cursor: pointer;
                    padding: 5px;
                    border-radius: 4px;
                    transition: background 0.3s ease;
                }
                .notification-close:hover {
                    background: rgba(255,255,255,0.2);
                }
            `;
            document.head.appendChild(style);
        }
    }
});