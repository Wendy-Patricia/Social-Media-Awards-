/**
 * Script de gestion du header responsive - Social Media Awards
 * @file Gère la navigation mobile et les interactions du header
 * @version 1.0
 * @author Social Media Awards Team
 */

/**
 * Initialise toutes les fonctionnalités du header
 * @function initHeader
 * @description Configure le menu mobile, les écouteurs d'événements et les interactions
 */
function initHeader() {
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.navbar ul');
    
    if (!navToggle || !navMenu) {
        console.warn('Éléments du header non trouvés');
        return;
    }
    
    /**
     * Ouvre ou ferme le menu mobile
     * @function toggleMobileMenu
     */
    function toggleMobileMenu() {
        navToggle.classList.toggle('active');
        navMenu.classList.toggle('active');
        
        // Empêche le défilement de la page quand le menu est ouvert
        if (navMenu.classList.contains('active')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }
    
    /**
     * Ferme le menu mobile
     * @function closeMobileMenu
     */
    function closeMobileMenu() {
        navToggle.classList.remove('active');
        navMenu.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    /**
     * Vérifie si le menu mobile est actuellement ouvert
     * @function isMobileMenuOpen
     * @returns {boolean} True si le menu est ouvert
     */
    function isMobileMenuOpen() {
        return navMenu.classList.contains('active');
    }
    
    // Écouteur pour le bouton du menu hamburger
    navToggle.addEventListener('click', function(event) {
        event.stopPropagation();
        toggleMobileMenu();
    });
    
    // Fermer le menu quand on clique sur un lien
    const navLinks = document.querySelectorAll('.navbar a');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (isMobileMenuOpen()) {
                closeMobileMenu();
            }
        });
    });
    
    // Fermer le menu quand on clique en dehors
    document.addEventListener('click', function(event) {
        const isClickInsideNav = navToggle.contains(event.target) || navMenu.contains(event.target);
        
        if (!isClickInsideNav && isMobileMenuOpen()) {
            closeMobileMenu();
        }
    });
    
    // Fermer le menu avec la touche Échap
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && isMobileMenuOpen()) {
            closeMobileMenu();
        }
    });
    
    /**
     * Gère le redimensionnement de la fenêtre
     * @function handleResize
     * @description Ferme le menu mobile quand la fenêtre devient trop large
     */
    function handleResize() {
        if (window.innerWidth > 768 && isMobileMenuOpen()) {
            closeMobileMenu();
        }
    }
    
    // Écouteur pour le redimensionnement de la fenêtre
    window.addEventListener('resize', handleResize);
    
    // Gestion des boutons de connexion/inscription
    initAuthButtons();
}

/**
 * Initialise les boutons d'authentification
 * @function initAuthButtons
 * @description Gère les interactions des boutons de connexion et d'inscription
 */
function initAuthButtons() {
    const loginButton = document.querySelector('.login-button');
    const signupButton = document.querySelector('.signup-button');
    
    /**
     * Redirige vers la page de connexion
     * @function redirectToLogin
     */
    function redirectToLogin() {
        // À remplacer par l'URL réelle de connexion
        window.location.href = 'login.php';
    }
    
    /**
     * Redirige vers la page d'inscription
     * @function redirectToSignup
     */
    function redirectToSignup() {
        // À remplacer par l'URL réelle d'inscription
        window.location.href = 'signup.php';
    }
    
    if (loginButton) {
        loginButton.addEventListener('click', redirectToLogin);
    }
    
    if (signupButton) {
        signupButton.addEventListener('click', redirectToSignup);
    }
}

/**
 * Ajoute une classe au header lors du défilement
 * @function initScrollEffects
 * @description Ajoute des effets visuels quand l'utilisateur scroll
 */
function initScrollEffects() {
    const navbar = document.querySelector('.navbar');
    let lastScrollTop = 0;
    
    if (!navbar) return;
    
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Ajouter une ombre plus prononcée quand on scroll
        if (scrollTop > 10) {
            navbar.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.15)';
        } else {
            navbar.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
        }
        
        // Cacher/montrer le header au scroll (optionnel)
        if (scrollTop > lastScrollTop && scrollTop > 100) {
            // Scroll vers le bas - cacher le header
            navbar.style.transform = 'translateY(-100%)';
        } else {
            // Scroll vers le haut - montrer le header
            navbar.style.transform = 'translateY(0)';
        }
        
        lastScrollTop = scrollTop;
    });
}

/**
 * Initialise les indicateurs de page active
 * @function initActivePageIndicator
 * @description Met en évidence la page courante dans la navigation
 */
function initActivePageIndicator() {
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.navbar a');
    
    navLinks.forEach(link => {
        const linkPage = link.getAttribute('href');
        
        if (linkPage === currentPage) {
            link.style.color = '#4fbdab';
            link.style.fontWeight = '600';
            link.style.backgroundColor = 'rgba(79, 189, 171, 0.1)';
        }
    });
}

/**
 * Point d'entrée principal - Initialise le header quand le DOM est chargé
 * @event DOMContentLoaded
 */
document.addEventListener('DOMContentLoaded', function() {
    try {
        initHeader();
        initScrollEffects();
        initActivePageIndicator();
        
        console.log('✅ Header initialisé avec succès');
        console.log('📱 Menu mobile fonctionnel');
        console.log('🎨 Effets de scroll activés');
        console.log('📍 Indicateurs de page active configurés');
        
    } catch (error) {
        console.error('❌ Erreur lors de l\'initialisation du header:', error);
    }
});

/**
 * Export des fonctions pour une utilisation externe (si nécessaire)
 * @module Header
 */
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        initHeader,
        initAuthButtons,
        initScrollEffects,
        initActivePageIndicator
    };
}