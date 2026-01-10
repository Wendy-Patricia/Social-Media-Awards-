
<?php
// FICHIER : nominees.php
// DESCRIPTION : Page affichant les nominés avec système de vote intelligent
// FONCTIONNALITÉ : Boutons de vote adaptatifs selon l'authentification et le rôle

require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/permissions.php';

/**
 * Obtient le lien du bouton de vote selon l'état d'authentification
 * 
 * @param string $category Catégorie du nominé (optionnel)
 * @return string URL de destination du bouton
 */
function getVoteButtonLink($category = '') {
    if (isAuthenticated()) {
        $userType = getUserType();
        if ($userType === 'voter') {
            // Électeur authentifié → page de vote avec catégorie
            $link = '/Social-Media-Awards-/views/user/Vote.php';
            if ($category) {
                $link .= '?category=' . urlencode($category);
            }
            return $link;
        } else {
            // Non-électeur (candidat/admin) → action désactivée
            return 'javascript:void(0)';
        }
    } else {
        // Non authentifié → login avec redirection vers vote
        $redirect = '/Social-Media-Awards-/views/user/Vote.php';
        if ($category) {
            $redirect .= '?category=' . urlencode($category);
        }
        return '/Social-Media-Awards-/views/login.php?redirect=' . urlencode($redirect);
    }
}

/**
 * Vérifie si l'utilisateur actuel peut voter
 * 
 * @return bool True si utilisateur est authentifié et électeur
 */
function canVote() {
    return isAuthenticated() && getUserType() === 'voter';
}

/**
 * Détermine le texte à afficher sur le bouton de vote
 * 
 * @return string Texte du bouton selon l'état d'authentification
 */
function getVoteButtonText() {
    if (!isAuthenticated()) {
        return 'Connectez-vous pour voter';
    } elseif (getUserType() !== 'voter') {
        return 'Non éligible pour voter';
    } else {
        return 'Voter';
    }
}

/**
 * Détermine la classe CSS du bouton de vote
 * 
 * @return string Classe CSS selon l'éligibilité au vote
 */
function getVoteButtonClass() {
    if (!isAuthenticated() || getUserType() !== 'voter') {
        return 'btn-vote btn-disabled';
    } else {
        return 'btn-vote';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/nominees.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Nominés - Social Media Awards 2026</title>
    <style>
        /* STYLES POUR BOUTONS DÉSACTIVÉS */
        .btn-vote.btn-disabled {
            opacity: 0.6;
            cursor: not-allowed;
            background-color: #ccc !important;
            color: #666 !important;
        }
        
        .btn-vote.btn-disabled:hover {
            background-color: #ccc !important;
            color: #666 !important;
            transform: none;
        }
    </style>
</head>
<body>
    <?php require_once 'views/partials/header.php'; ?>

    <div class="main-content">
        <!-- SECTION HERO DES NOMINÉS -->
        <section class="nominees-hero">
            <div class="hero-container">
                <h1>Nos Nominés 2026</h1>
                <p>Découvrez les talents exceptionnels sélectionnés pour cette édition des Social Media Awards</p>
                <div class="search-filter">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Rechercher un nominé..." id="searchInput">
                    </div>
                    <select id="categoryFilter">
                        <option value="">Toutes les catégories</option>
                        <option value="revelation">Créateur Révélation</option>
                        <option value="podcast">Podcast en Ligne</option>
                        <option value="branded">Branded Content</option>
                    </select>
                    <select id="platformFilter">
                        <option value="">Toutes les plateformes</option>
                        <option value="tiktok">TikTok</option>
                        <option value="instagram">Instagram</option>
                        <option value="youtube">YouTube</option>
                    </select>
                </div>
            </div>
        </section>

        <!-- GRILLE DES NOMINÉS -->
        <section class="nominees-section">
            <div class="container">
                <div class="nominees-grid">
                    <!-- NOMINÉ 1 : CRÉATEUR RÉVÉLATION -->
                    <div class="nominee-card" data-category="revelation" data-platform="tiktok">
                        <div class="nominee-image">
                            <img src="assets/images/Nominees/nominee1.jpg" alt="Nominee Name">
                            <div class="platform-badge tiktok">
                                <i class="fab fa-tiktok"></i>
                            </div>
                        </div>
                        <div class="nominee-info">
                            <h3>@CreateurTikTok</h3>
                            <p class="nominee-category">Créateur Révélation de l'Année</p>
                            <p class="nominee-description">Contenu humoristique et créatif qui a conquis TikTok</p>
                            <div class="nominee-stats">
                                <div class="stat">
                                    <i class="fas fa-heart"></i>
                                    <span>2.5M</span>
                                </div>
                                <div class="stat">
                                    <i class="fas fa-share"></i>
                                    <span>150K</span>
                                </div>
                            </div>
                            <!-- BOUTON DE VOTE ADAPTATIF -->
                            <!-- Le lien et l'apparence changent selon l'authentification -->
                            <a href="<?php echo getVoteButtonLink('revelation'); ?>" 
                               class="<?php echo getVoteButtonClass(); ?>"
                               onclick="return handleVoteClick(this, event)">
                                <?php echo getVoteButtonText(); ?>
                            </a>
                        </div>
                    </div>

                    <!-- NOMINÉ 2 : PODCAST -->
                    <div class="nominee-card" data-category="podcast" data-platform="youtube">
                        <div class="nominee-image">
                            <img src="assets/images/Nominees/nominee2.jpg" alt="Nominee Name">
                            <div class="platform-badge youtube">
                                <i class="fab fa-youtube"></i>
                            </div>
                        </div>
                        <div class="nominee-info">
                            <h3>TechTalk Podcast</h3>
                            <p class="nominee-category">Meilleur Podcast en Ligne</p>
                            <p class="nominee-description">Analyse approfondie des tendances tech et digitales</p>
                            <div class="nominee-stats">
                                <div class="stat">
                                    <i class="fas fa-play-circle"></i>
                                    <span>1.2M</span>
                                </div>
                                <div class="stat">
                                    <i class="fas fa-comment"></i>
                                    <span>45K</span>
                                </div>
                            </div>
                            <!-- BOUTON DE VOTE ADAPTATIF -->
                            <a href="<?php echo getVoteButtonLink('podcast'); ?>" 
                               class="<?php echo getVoteButtonClass(); ?>"
                               onclick="return handleVoteClick(this, event)">
                                <?php echo getVoteButtonText(); ?>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>

    <?php include 'views/partials/footer.php'; ?>
    <script src="assets/js/nominees.js"></script>
    
    <script>
    /**
     * Gère le clic sur un bouton de vote désactivé
     * Affiche des messages contextuels selon l'état d'authentification
     * 
     * @param {HTMLElement} link Élément du bouton cliqué
     * @param {Event} event Événement de clic
     * @return {boolean} False pour annuler la navigation
     */
    function handleVoteClick(link, event) {
        const isDisabled = link.classList.contains('btn-disabled');
        
        if (isDisabled) {
            event.preventDefault();
            
            <?php if (!isAuthenticated()): ?>
                // CAS : UTILISATEUR NON AUTHENTIFIÉ
                // Propose la connexion avec redirection vers la page actuelle
                if (confirm('Vous devez être connecté pour voter. Voulez-vous vous connecter maintenant?')) {
                    const currentPage = window.location.pathname + window.location.search;
                    window.location.href = '/Social-Media-Awards-/views/login.php?redirect=' + encodeURIComponent(currentPage);
                }
            <?php else: ?>
                // CAS : UTILISATEUR AUTHENTIFIÉ MAIS NON-ÉLECTEUR
                alert('Seuls les électeurs peuvent voter. Votre compte n\'a pas les permissions nécessaires.');
            <?php endif; ?>
            
            return false;
        }
        
        return true;
    }
    
    // Initialisation : Ajoute les gestionnaires d'événements aux boutons désactivés
    document.addEventListener('DOMContentLoaded', function() {
        const voteButtons = document.querySelectorAll('.btn-vote.btn-disabled');
        
        voteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                return handleVoteClick(this, e);
            });
        });
    });
    </script>
</body>
</html>
