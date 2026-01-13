/**
 * Script pour le tableau de bord candidat - Social Media Awards
 * Version optimisée avec animations et fonctionnalités avancées
 */

class CandidateDashboard {
    constructor() {
        this.init();
    }

    init() {
        // Initialisation des composants
        this.initAnimations();
        this.initTooltips();
        this.initCountdowns();
        this.initNotifications();
        this.initSidebar();
        this.bindEvents();
        
        // Vérification de session
        this.checkSession();
        
        console.log('Dashboard candidat initialisé');
    }

    /**
     * Initialise les animations au scroll
     */
    initAnimations() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    
                    // Animation spécifique pour les cartes de stats
                    if (entry.target.classList.contains('stat-card')) {
                        this.animateStatCard(entry.target);
                    }
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            observer.observe(el);
        });

        // Animation d'entrée initiale
        setTimeout(() => {
            document.querySelectorAll('.animate-on-scroll').forEach((el, index) => {
                el.style.animationDelay = `${index * 0.1}s`;
            });
        }, 100);
    }

    /**
     * Anime les cartes de statistiques
     */
    animateStatCard(card) {
        const numberEl = card.querySelector('.stat-number');
        if (!numberEl) return;

        const target = parseInt(numberEl.textContent);
        if (isNaN(target)) return;

        let current = 0;
        const increment = target / 50;
        const duration = 1500;
        const stepTime = duration / 50;

        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            numberEl.textContent = Math.floor(current).toLocaleString();
        }, stepTime);
    }

    /**
     * Initialise les tooltips Bootstrap
     */
    initTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(tooltipTriggerEl => {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                trigger: 'hover'
            });
        });
    }

    /**
     * Initialise les compteurs à rebours
     */
    initCountdowns() {
        const countdownElements = document.querySelectorAll('.countdown');
        
        countdownElements.forEach(countdown => {
            this.updateCountdown(countdown);
            // Mettre à jour toutes les minutes
            setInterval(() => this.updateCountdown(countdown), 60000);
        });
    }

    /**
     * Met à jour un compteur à rebours
     */
    updateCountdown(countdown) {
        const daysEl = countdown.querySelector('.countdown-number:nth-child(1)');
        const hoursEl = countdown.querySelector('.countdown-number:nth-child(2)');
        
        if (!daysEl || !hoursEl) return;

        // Pour l'exemple, on simule une date de fin
        // Dans la réalité, vous récupéreriez cette date depuis le DOM
        const endDate = new Date();
        endDate.setDate(endDate.getDate() + parseInt(daysEl.textContent));
        endDate.setHours(endDate.getHours() + parseInt(hoursEl.textContent));

        const now = new Date();
        const diff = endDate - now;

        if (diff <= 0) {
            daysEl.textContent = '0';
            hoursEl.textContent = '0';
            countdown.parentElement.innerHTML = '<div class="text-danger fw-bold">Terminé</div>';
            return;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));

        daysEl.textContent = days;
        hoursEl.textContent = hours;
    }

    /**
     * Gère les notifications
     */
    initNotifications() {
        // Fermeture auto des alertes après 5 secondes
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });

        // Vérification des nouvelles notifications
        this.checkNewNotifications();
    }

    /**
     * Vérifie les nouvelles notifications
     */
    checkNewNotifications() {
        // Simuler une vérification périodique
        setInterval(() => {
            this.fetchNotifications();
        }, 30000); // Toutes les 30 secondes
    }

    /**
     * Récupère les notifications depuis le serveur
     */
    async fetchNotifications() {
        try {
            const response = await fetch('/Social-Media-Awards/api/notifications.php');
            if (!response.ok) return;
            
            const data = await response.json();
            if (data.new > 0) {
                this.showNotificationBadge(data.new);
            }
        } catch (error) {
            console.error('Erreur de récupération des notifications:', error);
        }
    }

    /**
     * Affiche un badge de notification
     */
    showNotificationBadge(count) {
        let badge = document.querySelector('.notification-badge');
        if (!badge) {
            const navItem = document.querySelector('.nav-item.dropdown');
            badge = document.createElement('span');
            badge.className = 'notification-badge badge bg-danger rounded-pill position-absolute';
            badge.style.top = '5px';
            badge.style.right = '5px';
            badge.style.fontSize = '0.6rem';
            badge.style.padding = '2px 5px';
            navItem.appendChild(badge);
        }
        badge.textContent = count;
        badge.classList.add('animate__animated', 'animate__pulse');
    }

    /**
     * Initialise la sidebar
     */
    initSidebar() {
        // Gestion du menu actif
        const currentPath = window.location.pathname;
        const menuItems = document.querySelectorAll('.sidebar-item');
        
        menuItems.forEach(item => {
            if (item.getAttribute('href') === currentPath) {
                item.classList.add('active');
            }
        });

        // Toggle sidebar sur mobile
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                document.querySelector('.dashboard-sidebar').classList.toggle('collapsed');
            });
        }
    }

    /**
     * Vérifie la session utilisateur
     */
    checkSession() {
        // Vérifier l'inactivité
        let idleTime = 0;
        
        const resetIdleTime = () => {
            idleTime = 0;
        };

        // Événements qui réinitialisent le compteur d'inactivité
        ['mousemove', 'keypress', 'click', 'scroll'].forEach(event => {
            document.addEventListener(event, resetIdleTime);
        });

        // Vérifier l'inactivité toutes les minutes
        setInterval(() => {
            idleTime++;
            if (idleTime > 30) { // 30 minutes d'inactivité
                this.showSessionWarning();
            }
        }, 60000);
    }

    /**
     * Affiche un avertissement de session
     */
    showSessionWarning() {
        if (document.querySelector('#sessionWarning')) return;

        const warning = document.createElement('div');
        warning.id = 'sessionWarning';
        warning.className = 'alert alert-warning alert-dismissible fade show position-fixed bottom-0 end-0 m-3';
        warning.style.zIndex = '9999';
        warning.style.maxWidth = '300px';
        warning.innerHTML = `
            <i class="fas fa-clock me-2"></i>
            <strong>Session inactives</strong>
            <p class="mb-0 small">Vous serez déconnecté dans 5 minutes.</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(warning);

        // Fermer après 10 secondes
        setTimeout(() => {
            if (warning.parentNode) {
                warning.remove();
            }
        }, 10000);
    }

    /**
     * Bind des événements
     */
    bindEvents() {
        // Export des données
        document.querySelectorAll('.export-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.exportData(btn.dataset.type);
            });
        });

        // Refresh des données
        const refreshBtn = document.querySelector('.refresh-btn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.refreshData();
            });
        }

        // Confirmation avant suppression
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
                    e.preventDefault();
                }
            });
        });
    }

    /**
     * Exporte des données
     */
    exportData(type) {
        // Implémentation de l'export
        console.log(`Export des données: ${type}`);
        
        // Simulation de téléchargement
        const data = `Données exportées pour ${type} - ${new Date().toLocaleString()}`;
        const blob = new Blob([data], { type: 'text/plain' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `export-${type}-${Date.now()}.txt`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }

    /**
     * Rafraîchit les données
     */
    refreshData() {
        const refreshBtn = document.querySelector('.refresh-btn');
        if (refreshBtn) {
            refreshBtn.disabled = true;
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Actualisation...';
            
            // Simuler un rafraîchissement
            setTimeout(() => {
                location.reload();
            }, 1000);
        }
    }

    /**
     * Affiche une notification toast
     */
    showToast(message, type = 'success') {
        const toastContainer = document.getElementById('toastContainer') || this.createToastContainer();
        
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }

    /**
     * Crée un conteneur pour les toasts
     */
    createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
        return container;
    }

    /**
     * Met à jour le temps de session
     */
    updateSessionTime() {
        const sessionTimeEl = document.getElementById('sessionTime');
        if (sessionTimeEl) {
            const now = new Date();
            const loginTime = new Date(sessionTimeEl.dataset.loginTime);
            const diff = now - loginTime;
            const hours = Math.floor(diff / 3600000);
            const minutes = Math.floor((diff % 3600000) / 60000);
            
            sessionTimeEl.textContent = `${hours}h ${minutes}m`;
        }
    }
}

// Initialisation lorsque le DOM est chargé
document.addEventListener('DOMContentLoaded', () => {
    const dashboard = new CandidateDashboard();
    
    // Exposer l'instance globalement si nécessaire
    window.candidateDashboard = dashboard;
    
    // Mettre à jour le temps de session toutes les minutes
    setInterval(() => dashboard.updateSessionTime(), 60000);
});

// Gestion de la fermeture de page
window.addEventListener('beforeunload', (e) => {
    // Vous pouvez ajouter une sauvegarde automatique ici
    // e.preventDefault();
    // e.returnValue = '';
});