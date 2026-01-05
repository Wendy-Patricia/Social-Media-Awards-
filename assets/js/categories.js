// assets/js/categories.js - Versão atualizada

document.addEventListener('DOMContentLoaded', function() {
    // Filtro por plataforma
    const filterButtons = document.querySelectorAll('.filter-btn');
    const categoryCards = document.querySelectorAll('.category-card');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remover classe active de todos os botões
            filterButtons.forEach(btn => btn.classList.remove('active'));
            
            // Adicionar classe active ao botão clicado
            this.classList.add('active');
            
            const filterValue = this.getAttribute('data-filter');
            
            // Filtrar cartões
            categoryCards.forEach(card => {
                if (filterValue === 'all') {
                    card.style.display = 'block';
                } else {
                    const platforms = card.getAttribute('data-platform');
                    if (platforms && platforms.includes(filterValue)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                }
                
                // Adicionar animação
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100);
            });
        });
    });
    
    // Botão "Voir les Nominés"
    const viewButtons = document.querySelectorAll('.btn-view-nominees');
    
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const categoryId = this.getAttribute('data-category');
            
            if (categoryId && categoryId !== '0') {
                // Redirecionar para a página de nominees com filtro de categoria
                window.location.href = `nominees.php?category=${categoryId}`;
            } else {
                // Fallback para página geral de nominees
                window.location.href = 'nominees.php';
            }
        });
    });
    
    // Animação inicial dos cartões
    categoryCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});