<?php
// views/candidate/reglement.php
session_start();

// Verificar se o usuário está logado como candidato
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
    header('Location: /Social-Media-Awards/views/login.php');
    exit;
}

// Incluir configurações
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../app/autoload.php';

use App\Services\CandidatService;
use App\Services\CategoryService;
use App\Services\EditionService;

// Inicializar conexão
$pdo = Database::getInstance()->getConnection();

// Inicializar serviços
$candidatService = new CandidatService($pdo);
$categoryService = new CategoryService($pdo);
$editionService = new EditionService($pdo);

// Verificar status
$userId = $_SESSION['user_id'];
$isNominee = $candidatService->isNominee($userId);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Règlement - Social Media Awards</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="/Social-Media-Awards/assets/css/candidat.css">
    
    <style>
    .reglement-section {
        background: white;
        border-radius: var(--border-radius-xl);
        padding: var(--spacing-xl);
        margin-bottom: var(--spacing-lg);
        border: none;
        box-shadow: var(--shadow-lg);
    }
    
    .reglement-section h3 {
        color: var(--principal-dark);
        border-bottom: 3px solid var(--principal-light);
        padding-bottom: var(--spacing-sm);
        margin-bottom: var(--spacing-lg);
    }
    
    .reglement-item {
        margin-bottom: var(--spacing-lg);
        padding-left: var(--spacing-lg);
        position: relative;
    }
    
    .reglement-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 10px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--principal);
    }
    
    .penalite-card {
        background: linear-gradient(135deg, rgba(220, 53, 69, 0.05), rgba(220, 53, 69, 0.02));
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-lg);
        border-left: 4px solid var(--danger);
        margin: var(--spacing-md) 0;
    }
    
    .accordion-reglement .accordion-button {
        background: linear-gradient(135deg, rgba(79, 189, 171, 0.1), rgba(79, 189, 171, 0.05));
        font-weight: 600;
    }
    
    .accordion-reglement .accordion-button:not(.collapsed) {
        background: linear-gradient(135deg, var(--principal), var(--principal-dark));
        color: white;
    }
    
    .acceptation-container {
        background: linear-gradient(135deg, rgba(40, 167, 69, 0.1), rgba(40, 167, 69, 0.05));
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-xl);
        border: 2px solid var(--success);
        text-align: center;
    }
    
    .badge-regle {
        display: inline-block;
        background: var(--principal);
        color: white;
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius-sm);
        font-size: 0.75rem;
        font-weight: 600;
        margin-right: var(--spacing-xs);
        margin-bottom: var(--spacing-xs);
    }
    
    .badge-interdiction {
        background: var(--danger);
    }
    
    .badge-obligation {
        background: var(--success);
    }
    
    .badge-conseil {
        background: var(--info);
    }
    </style>
</head>
<body class="bg-light">
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="/Social-Media-Awards/index.php">
                <i class="fas fa-trophy me-2"></i>Social Media Awards
            </a>
            
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="candidate-dashboard.php">
                    <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                </a>
                <span class="navbar-text me-3">
                    <i class="fas fa-user me-1"></i> <?= htmlspecialchars($_SESSION['user_pseudonyme'] ?? ($isNominee ? 'Nominé' : 'Candidat')) ?>
                </span>
                <a class="nav-link" href="/Social-Media-Awards/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="sidebar-container">
                    <?php include __DIR__ . '/../partials/sidebar-candidat.php'; ?>
                </div>
            </div>
            
            <!-- Conteúdo principal -->
            <div class="col-md-9">
                <!-- Cabeçalho -->
                <div class="page-header mb-4">
                    <h1 class="page-title">
                        <i class="fas fa-file-contract me-2"></i>Règlement Officiel
                    </h1>
                    <p class="page-subtitle">Règles et conditions de participation</p>
                </div>
                
                <!-- Aviso importante -->
                <div class="alert alert-warning">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-exclamation-triangle fa-2x me-3 mt-1"></i>
                        <div>
                            <h5 class="mb-2">Important : Lisez attentivement</h5>
                            <p class="mb-0">
                                En participant aux Social Media Awards, vous acceptez l'intégralité de ce règlement.
                                <?php if ($isNominee): ?>
                                <strong class="text-danger">En tant que nominé, certaines règles supplémentaires s'appliquent.</strong>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Sumário -->
                <div class="main-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            Sommaire
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <a href="#section1" class="text-decoration-none">
                                            <i class="fas fa-arrow-right me-2 text-primary"></i>
                                            1. Éligibilité
                                        </a>
                                    </li>
                                    <li class="mb-2">
                                        <a href="#section2" class="text-decoration-none">
                                            <i class="fas fa-arrow-right me-2 text-primary"></i>
                                            2. Candidatures
                                        </a>
                                    </li>
                                    <li class="mb-2">
                                        <a href="#section3" class="text-decoration-none">
                                            <i class="fas fa-arrow-right me-2 text-primary"></i>
                                            3. Votes
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <a href="#section4" class="text-decoration-none">
                                            <i class="fas fa-arrow-right me-2 text-primary"></i>
                                            4. Promotion
                                        </a>
                                    </li>
                                    <li class="mb-2">
                                        <a href="#section5" class="text-decoration-none">
                                            <i class="fas fa-arrow-right me-2 text-primary"></i>
                                            5. Interdictions
                                        </a>
                                    </li>
                                    <li class="mb-2">
                                        <a href="#section6" class="text-decoration-none">
                                            <i class="fas fa-arrow-right me-2 text-primary"></i>
                                            6. Sanctions
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <a href="#section7" class="text-decoration-none">
                                            <i class="fas fa-arrow-right me-2 text-primary"></i>
                                            7. Résultats
                                        </a>
                                    </li>
                                    <li class="mb-2">
                                        <a href="#section8" class="text-decoration-none">
                                            <i class="fas fa-arrow-right me-2 text-primary"></i>
                                            8. Contact
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Seção 1: Éligibilité -->
                <div id="section1" class="reglement-section">
                    <h3>
                        <i class="fas fa-user-check me-2"></i>
                        1. Conditions d'éligibilité
                    </h3>
                    
                    <div class="reglement-item">
                        <h5>Âge minimum</h5>
                        <p>Les participants doivent avoir au moins 13 ans au moment de l'inscription.</p>
                        <span class="badge-regle badge-obligation">Obligatoire</span>
                    </div>
                    
                    <div class="reglement-item">
                        <h5>Contenu original</h5>
                        <p>Tout contenu soumis doit être original et détenu par le candidat.</p>
                        <span class="badge-regle badge-obligation">Obligatoire</span>
                    </div>
                    
                    <div class="reglement-item">
                        <h5>Droit à l'image</h5>
                        <p>Le candidat doit détenir les droits nécessaires pour soumettre le contenu.</p>
                        <span class="badge-regle badge-obligation">Obligatoire</span>
                    </div>
                    
                    <?php if ($isNominee): ?>
                    <div class="alert alert-info mt-4">
                        <h6><i class="fas fa-info-circle me-2"></i>Information pour les nominés</h6>
                        <p class="mb-0">En tant que nominé, vous devez maintenir votre éligibilité pendant toute la durée des votes.</p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Seção 2: Candidatures -->
                <div id="section2" class="reglement-section">
                    <h3>
                        <i class="fas fa-paper-plane me-2"></i>
                        2. Soumission des candidatures
                    </h3>
                    
                    <div class="reglement-item">
                        <h5>Délais de soumission</h5>
                        <p>Les candidatures doivent être soumises avant la date limite indiquée pour chaque catégorie.</p>
                        <span class="badge-regle badge-obligation">Obligatoire</span>
                    </div>
                    
                    <div class="reglement-item">
                        <h5>Contenu approprié</h5>
                        <p>Le contenu ne doit pas contenir de matériel offensant, discriminatoire ou illégal.</p>
                        <span class="badge-regle badge-interdiction">Interdit</span>
                    </div>
                    
                    <div class="reglement-item">
                        <h5>Modifications</h5>
                        <p>Les candidatures peuvent être modifiées tant qu'elles sont en attente de validation.</p>
                        <span class="badge-regle badge-conseil">Conseil</span>
                    </div>
                </div>
                
                <!-- Seção 3: Votes (especial para nomeados) -->
                <div id="section3" class="reglement-section">
                    <h3>
                        <i class="fas fa-vote-yea me-2"></i>
                        3. Système de votes
                    </h3>
                    
                    <?php if ($isNominee): ?>
                    <div class="alert alert-success">
                        <h6><i class="fas fa-star me-2"></i>Règles spécifiques pour les nominés</h6>
                        <p class="mb-0">En tant que nominé, vous êtes directement concerné par cette section.</p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="reglement-item">
                        <h5>Période de votes</h5>
                        <p>Les votes sont ouverts pendant une période définie pour chaque catégorie.</p>
                        <span class="badge-regle badge-obligation">Obligatoire</span>
                    </div>
                    
                    <div class="reglement-item">
                        <h5>Anonymat des votes</h5>
                        <p>Les votes sont anonymes. Les nominés ne peuvent pas voir les résultats en temps réel.</p>
                        <span class="badge-regle badge-interdiction">Interdit</span>
                    </div>
                    
                    <div class="reglement-item">
                        <h5>Un vote par personne</h5>
                        <p>Chaque électeur ne peut voter qu'une seule fois par catégorie.</p>
                        <span class="badge-regle badge-obligation">Obligatoire</span>
                    </div>
                    
                    <div class="accordion accordion-reglement mt-4" id="accordionVotes">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#collapseVotes1">
                                    Pourquoi ne puis-je pas voir mon nombre de votes ?
                                </button>
                            </h2>
                            <div id="collapseVotes1" class="accordion-collapse collapse" 
                                 data-bs-parent="#accordionVotes">
                                <div class="accordion-body">
                                    Pour garantir l'équité et éviter toute pression ou manipulation, les résultats ne sont pas visibles avant la fin officielle des votes.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Seção 4: Promotion (CRÍTICO para nomeados) -->
                <div id="section4" class="reglement-section">
                    <h3>
                        <i class="fas fa-share-alt me-2"></i>
                        4. Promotion des nominations
                    </h3>
                    
                    <?php if ($isNominee): ?>
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Attention : Règles strictes</h6>
                        <p class="mb-0">Le non-respect de ces règles peut entraîner une disqualification immédiate.</p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="reglement-item">
                        <h5>Promotion autorisée</h5>
                        <p>Les nominés sont encouragés à promouvoir leur nomination via leurs réseaux sociaux.</p>
                        <span class="badge-regle badge-conseil">Conseillé</span>
                    </div>
                    
                    <div class="reglement-item">
                        <h5>Utilisation du kit officiel</h5>
                        <p>Utilisez les outils de promotion fournis sur votre dashboard nominé.</p>
                        <span class="badge-regle badge-conseil">Conseillé</span>
                    </div>
                    
                    <div class="reglement-item">
                        <h5>Hashtags officiels</h5>
                        <p>Utilisez les hashtags officiels #SocialMediaAwards2025 et #VotezPourMoi.</p>
                        <span class="badge-regle badge-conseil">Conseillé</span>
                    </div>
                    
                    <!-- FAQ Promotion -->
                    <div class="accordion accordion-reglement mt-4" id="accordionPromotion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#collapsePromo1">
                                    Comment promouvoir ma nomination efficacement ?
                                </button>
                            </h2>
                            <div id="collapsePromo1" class="accordion-collapse collapse" 
                                 data-bs-parent="#accordionPromotion">
                                <div class="accordion-body">
                                    <ul>
                                        <li>Partagez votre lien public dans votre bio Instagram/TikTok</li>
                                        <li>Créez des stories ou des posts engageants</li>
                                        <li>Utilisez les hashtags officiels</li>
                                        <li>Remerciez vos supporters</li>
                                        <li>Utilisez le kit promotionnel fourni</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Seção 5: Interdictions (MUITO IMPORTANTE) -->
                <div id="section5" class="reglement-section">
                    <h3>
                        <i class="fas fa-ban me-2"></i>
                        5. Interdictions strictes
                    </h3>
                    
                    <div class="penalite-card">
                        <h5 class="text-danger">
                            <i class="fas fa-skull-crossbones me-2"></i>
                            Ces actions entraînent une disqualification immédiate
                        </h5>
                    </div>
                    
                    <div class="reglement-item">
                        <h5>Achat de votes</h5>
                        <p>Il est strictement interdit d'acheter des votes ou des services de vote.</p>
                        <span class="badge-regle badge-interdiction">Interdit</span>
                    </div>
                    
                    <div class="reglement-item">
                        <h5>Utilisation de bots</h5>
                        <p>L'utilisation de robots, scripts ou programmes automatisés est interdite.</p>
                        <span class="badge-regle badge-interdiction">Interdit</span>
                    </div>
                    
                    <div class="reglement-item">
                        <h5>Fausses promesses</h5>
                        <p>Ne pas promettre des récompenses en échange de votes.</p>
                        <span class="badge-regle badge-interdiction">Interdit</span>
                    </div>
                    
                    <div class="reglement-item">
                        <h5>Harcèlement</h5>
                        <p>Le harcèlement d'autres candidats ou électeurs est interdit.</p>
                        <span class="badge-regle badge-interdiction">Interdit</span>
                    </div>
                    
                    <div class="reglement-item">
                        <h5>Fausse identité</h5>
                        <p>Ne pas se faire passer pour quelqu'un d'autre.</p>
                        <span class="badge-regle badge-interdiction">Interdit</span>
                    </div>
                </div>
                
                <!-- Seção 6: Sanctions -->
                <div id="section6" class="reglement-section">
                    <h3>
                        <i class="fas fa-gavel me-2"></i>
                        6. Sanctions et pénalités
                    </h3>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Infraction</th>
                                    <th>Sanction</th>
                                    <th>Application</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Achat de votes</td>
                                    <td class="text-danger">Disqualification immédiate + bannissement permanent</td>
                                    <td>Automatique</td>
                                </tr>
                                <tr>
                                    <td>Utilisation de bots</td>
                                    <td class="text-danger">Disqualification + annulation de tous les votes</td>
                                    <td>Automatique</td>
                                </tr>
                                <tr>
                                    <td>Contenu inapproprié</td>
                                    <td class="text-warning">Disqualification de la candidature</td>
                                    <td>Après vérification</td>
                                </tr>
                                <tr>
                                    <td>Harcèlement</td>
                                    <td class="text-warning">Disqualification + avertissement</td>
                                    <td>Après enquête</td>
                                </tr>
                                <tr>
                                    <td>Fausse identité</td>
                                    <td class="text-danger">Bannissement du compte</td>
                                    <td>Automatique</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="alert alert-danger mt-4">
                        <h6><i class="fas fa-exclamation-circle me-2"></i>Procédure de contestation</h6>
                        <p class="mb-0">
                            En cas de sanction, vous pouvez contester dans les 7 jours à l'adresse :
                            <a href="mailto:contestation@socialmediaawards.fr" class="text-white">
                                contestation@socialmediaawards.fr
                            </a>
                        </p>
                    </div>
                </div>
                
                <!-- Seção 7: Résultats -->
                <div id="section7" class="reglement-section">
                    <h3>
                        <i class="fas fa-flag-checkered me-2"></i>
                        7. Publication des résultats
                    </h3>
                    
                    <div class="reglement-item">
                        <h5>Délai de publication</h5>
                        <p>Les résultats sont publiés 3 jours ouvrables après la fin des votes.</p>
                        <span class="badge-regle badge-obligation">Obligatoire</span>
                    </div>
                    
                    <div class="reglement-item">
                        <h5>Finalité des résultats</h5>
                        <p>Les résultats sont définitifs et ne sont pas sujets à modification.</p>
                        <span class="badge-regle badge-obligation">Obligatoire</span>
                    </div>
                    
                    <div class="reglement-item">
                        <h5>Certificats de participation</h5>
                        <p>Tous les nominés reçoivent un certificat de participation numérique.</p>
                        <span class="badge-regle badge-conseil">Avantage</span>
                    </div>
                    
                    <?php if ($isNominee): ?>
                    <div class="alert alert-info mt-4">
                        <h6><i class="fas fa-trophy me-2"></i>Pour les gagnants</h6>
                        <p class="mb-0">
                            Les gagnants seront contactés par email pour les modalités de réception des prix.
                            Les prix doivent être réclamés dans les 30 jours suivant l'annonce.
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Seção 8: Contact -->
                <div id="section8" class="reglement-section">
                    <h3>
                        <i class="fas fa-headset me-2"></i>
                        8. Support et contact
                    </h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="reglement-item">
                                <h5>Support technique</h5>
                                <p>
                                    <i class="fas fa-envelope me-2"></i>
                                    <a href="mailto:support@socialmediaawards.fr">support@socialmediaawards.fr</a>
                                </p>
                                <p>
                                    <i class="fas fa-phone me-2"></i>
                                    +33 1 23 45 67 89 (10h-18h)
                                </p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="reglement-item">
                                <h5>Support nominés</h5>
                                <?php if ($isNominee): ?>
                                <p>
                                    <i class="fas fa-envelope me-2"></i>
                                    <a href="mailto:nominations@socialmediaawards.fr">nominations@socialmediaawards.fr</a>
                                </p>
                                <p>
                                    <i class="fas fa-comment-alt me-2"></i>
                                    Support prioritaire pour les nominés
                                </p>
                                <?php else: ?>
                                <p class="text-muted">Disponible uniquement pour les nominés</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="reglement-item">
                        <h5>Délais de réponse</h5>
                        <p>Réponse sous 48h ouvrables pour toutes les demandes.</p>
                        <p>Urgences : réponse sous 24h.</p>
                    </div>
                </div>
                
                <!-- Aceitação do regulamento -->
                <div class="acceptation-container mb-4">
                    <h4 class="mb-3">
                        <i class="fas fa-handshake me-2"></i>
                        Acceptation du règlement
                    </h4>
                    <p class="mb-4">
                        En participant aux Social Media Awards, vous reconnaissez avoir lu, compris et accepté l'intégralité de ce règlement.
                    </p>
                    
                    <div class="form-check d-inline-block me-4">
                        <input class="form-check-input" type="checkbox" id="acceptReglement" checked disabled>
                        <label class="form-check-label" for="acceptReglement">
                            J'ai lu le règlement
                        </label>
                    </div>
                    
                    <div class="form-check d-inline-block">
                        <input class="form-check-input" type="checkbox" id="acceptConditions" checked disabled>
                        <label class="form-check-label" for="acceptConditions">
                            J'accepte les conditions
                        </label>
                    </div>
                    
                    <div class="mt-4">
                        <p class="small text-muted">
                            Dernière mise à jour : <?= date('d/m/Y') ?> | 
                            Version : 2.3
                        </p>
                    </div>
                </div>
                
                <!-- Download do regulamento -->
                <div class="text-center">
                    <a href="#" class="btn btn-primary">
                        <i class="fas fa-download me-2"></i>
                        Télécharger le règlement (PDF)
                    </a>
                    <a href="candidate-dashboard.php" class="btn btn-outline-secondary ms-2">
                        <i class="fas fa-arrow-left me-2"></i>
                        Retour au dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div>
                    <h5>Social Media Awards</h5>
                    <p>Célébrons la créativité numérique ensemble.</p>
                </div>
                <div>
                    <h5>Support juridique</h5>
                    <ul class="footer-links">
                        <li><a href="mailto:juridique@socialmediaawards.fr">juridique@socialmediaawards.fr</a></li>
                        <li><a href="#">Mentions légales</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p class="mb-0">&copy; <?= date('Y') ?> Social Media Awards. Tous droits réservés.</p>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>