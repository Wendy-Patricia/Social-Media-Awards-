// assets/js/admin-sidebar.js
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mainContent = document.querySelector('.main-content');
    
    // Toggle sidebar em mobile
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            sidebar.classList.toggle('active');
            
            // Adiciona overlay no conteúdo
            if (sidebar.classList.contains('active')) {
                createOverlay();
            } else {
                removeOverlay();
            }
        });
    }
    
    // Função para criar overlay
    function createOverlay() {
        const overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            display: block;
        `;
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            removeOverlay();
        });
        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden';
    }
    
    // Função para remover overlay
    function removeOverlay() {
        const overlay = document.querySelector('.sidebar-overlay');
        if (overlay) {
            overlay.remove();
        }
        document.body.style.overflow = '';
    }
    
    // Fechar sidebar ao redimensionar para desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('active');
            removeOverlay();
        }
    });
    
    // Auto-hightlight do link ativo
    highlightActiveLink();
});

function highlightActiveLink() {
    const currentPage = window.location.pathname.split('/').pop() || 'dashboard.php';
    const allLinks = document.querySelectorAll('.sidebar-nav a');
    
    allLinks.forEach(link => {
        link.parentElement.classList.remove('active');
        
        const href = link.getAttribute('href');
        if (href && currentPage.includes(href.replace('.php', ''))) {
            link.parentElement.classList.add('active');
            
            // Expandir seção pai se estiver em submenu
            const section = link.closest('.nav-section');
            if (section) {
                section.classList.add('expanded');
            }
        }
    });
}