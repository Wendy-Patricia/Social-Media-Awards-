<?php
session_start();

// Não requer login para ver o regulamento
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../app/autoload.php';

use App\Services\EditionService;

$pdo = Database::getInstance()->getConnection();
$editionService = new EditionService($pdo);

// Obter edição ativa
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Règlement Officiel - Social Media Awards</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .reglement-article {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
        }

        .reglement-article:hover {
            background-color: #f8f9fa;
            border-left-color: #4FBDAB;
            transform: translateX(5px);
        }

        .article-number {
            display: inline-block;
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #4FBDAB, #45a999);
            color: white;
            border-radius: 6px;
            text-align: center;
            line-height: 36px;
            font-weight: bold;
            margin-right: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .step-number {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #4FBDAB, #45a999);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.25rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 3px solid white;
        }

        .prize-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #4FBDAB, #45a999);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: white;
            font-size: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .data-icon {
            font-size: 2.5rem;
            color: #4FBDAB;
            margin-bottom: 15px;
            display: block;
        }

        .page-title {
            background: linear-gradient(135deg, #45a999, #4FBDAB);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .reglement-article {
                page-break-inside: avoid;
                border-left: 1px solid #ddd !important;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #4FBDAB, #45a999);">
        <div class="container-fluid">
            <a class="navbar-brand" href="candidate-dashboard.php">
                <i class="fas fa-trophy"></i>
                Social Media Awards
            </a>
            <div class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="candidate-dashboard.php" class="nav-link text-white">
                        <i class="fas fa-home"></i> Tableau de bord
                    </a>
                <?php else: ?>
                    <a href="/Social-Media-Awards/views/login.php" class="nav-link text-white">
                        <i class="fas fa-sign-in-alt"></i> Connexion
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="py-4">
        <div class="container">
            <!-- Page Header -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h1 class="page-title fw-bold">
                                <i class="fas fa-gavel"></i> Règlement Officiel
                            </h1>
                            <p class="text-muted">Social Media Awards</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button onclick="window.print()" class="btn btn-outline-primary no-print">
                                <i class="fas fa-print"></i> Imprimer
                            </button>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="#accept-reglement" class="btn btn-primary no-print">
                                    <i class="fas fa-check-circle"></i> Accepter
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>


                </div>

                <!-- Reglement Content -->
                <div class="card shadow-lg mb-5">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-book text-primary"></i> Règlement Général des Social Media Awards
                        </h5>
                    </div>
                    <div class="card-body">

                        <!-- Article 1: Objet -->
                        <div class="reglement-article">
                            <h3 class="mb-4">
                                <span class="article-number">1</span> Objet du Concours
                            </h3>
                            <div class="ps-4">
                                <p>Le présent règlement définit les conditions de participation aux Social Media Awards, organisé par <strong>[Nom de l'Organisateur]</strong>, société immatriculée au RCS de [Ville] sous le numéro [Numéro RCS].</p>
                                <p class="mb-0">Le Concours récompense les créateurs de contenus, les marques et les professionnels des médias sociaux pour la qualité et l'impact de leurs productions.</p>
                            </div>
                        </div>

                        <!-- Article 2: Conditions de Participation -->
                        <div class="reglement-article">
                            <h3 class="mb-4">
                                <span class="article-number">2</span> Conditions de Participation
                            </h3>
                            <div class="ps-4">
                                <p>Pour participer, les candidats doivent :</p>
                                <ol class="mb-4">
                                    <li>Être une personne physique majeure ou une personne morale légalement constituée</li>
                                    <li>Résider dans un pays autorisé à participer</li>
                                    <li>Posséder un compte valide sur au moins une plateforme sociale éligible</li>
                                    <li>Accepter sans réserve le présent règlement</li>
                                    <li>Avoir obtenu toutes les autorisations nécessaires pour les contenus soumis</li>
                                </ol>

                                <div class="alert alert-warning">
                                    <h6 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Restrictions :</h6>
                                    <ul class="mb-0">
                                        <li>Contenus discriminatoires, diffamatoires ou illégaux interdits</li>
                                        <li>Pas de promotion de produits/services illégaux</li>
                                        <li>Toute fraude entraîne l'exclusion immédiate</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Article 3: Catégories -->
                        <div class="reglement-article">
                            <h3 class="mb-4">
                                <span class="article-number">3</span> Catégories du Concours
                            </h3>
                            <div class="ps-4">
                                <p>Le Concours comprend les catégories suivantes :</p>

                                <div class="table-responsive mt-3">
                                    <table class="table table-bordered">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Catégorie</th>
                                                <th>Description</th>
                                                <th>Plateforme(s)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><strong>Meilleur Créateur de Contenu</strong></td>
                                                <td>Qualité et originalité des contenus</td>
                                                <td>YouTube, TikTok, Instagram</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Meilleure Marque</strong></td>
                                                <td>Meilleure présence sociale</td>
                                                <td>Toutes plateformes</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Meilleur Influenceur Mode</strong></td>
                                                <td>Influenceurs mode & lifestyle</td>
                                                <td>Instagram, TikTok</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Meilleur Podcast</strong></td>
                                                <td>Podcasts les plus populaires</td>
                                                <td>Spotify, Apple Podcasts</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Meilleur Streamer Gaming</strong></td>
                                                <td>Streamers de jeux vidéo</td>
                                                <td>Twitch, YouTube Gaming</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Article 4: Modalités de Candidature -->
                        <div class="reglement-article">
                            <h3 class="mb-4">
                                <span class="article-number">4</span> Modalités de Candidature
                            </h3>
                            <div class="ps-4">
                                <p class="mb-4">Processus de candidature en 4 étapes :</p>

                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="d-flex gap-3 mb-4">
                                            <div class="step-number">1</div>
                                            <div>
                                                <h6>Création de compte</h6>
                                                <p class="text-muted mb-0">Compte avec informations exactes</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="d-flex gap-3 mb-4">
                                            <div class="step-number">2</div>
                                            <div>
                                                <h6>Soumission</h6>
                                                <p class="text-muted mb-0">Formulaire en ligne complet</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="d-flex gap-3 mb-4">
                                            <div class="step-number">3</div>
                                            <div>
                                                <h6>Documents</h6>
                                                <p class="text-muted mb-0">Contenu, image, argumentaire</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="d-flex gap-3 mb-4">
                                            <div class="step-number">4</div>
                                            <div>
                                                <h6>Validation</h6>
                                                <p class="text-muted mb-0">Acceptation du règlement</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Article 5: Processus de Sélection -->
                        <div class="reglement-article">
                            <h3 class="mb-4">
                                <span class="article-number">5</span> Processus de Sélection
                            </h3>
                            <div class="ps-4">
                                <h5 class="mb-3">Phase 1 : Présélection</h5>
                                <p>Vérification par un comité :</p>
                                <ul class="mb-4">
                                    <li>Conformité au règlement</li>
                                    <li>Complétude du dossier</li>
                                    <li>Adéquation avec la catégorie</li>
                                    <li>Critères d'éligibilité</li>
                                </ul>

                                <h5 class="mb-3">Phase 2 : Vote</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <h6 class="card-title text-primary">
                                                    <i class="fas fa-users"></i> Vote du Public (50%)
                                                </h6>
                                                <p class="card-text">
                                                    Ouvert à tous les internautes<br>
                                                    <small class="text-muted">1 vote par personne et par catégorie</small>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <h6 class="card-title text-primary">
                                                    <i class="fas fa-user-tie"></i> Vote du Jury (50%)
                                                </h6>
                                                <p class="card-text">
                                                    Professionnels selon critères précis<br>
                                                    <small class="text-muted">Originalité, qualité, impact, innovation</small>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Article 6: Protection des Données -->
                        <div class="reglement-article">
                            <h3 class="mb-4">
                                <span class="article-number">6</span> Protection des Données
                            </h3>
                            <div class="ps-4">
                                <p class="mb-4">Conformément au RGPD et à la Loi Informatique et Libertés :</p>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-3 col-6">
                                        <div class="text-center p-3 border rounded">
                                            <i class="fas fa-shield-alt data-icon"></i>
                                            <h6 class="mb-1">Collecte limitée</h6>
                                            <p class="text-muted small mb-0">Données nécessaires uniquement</p>
                                        </div>
                                    </div>

                                    <div class="col-md-3 col-6">
                                        <div class="text-center p-3 border rounded">
                                            <i class="fas fa-lock data-icon"></i>
                                            <h6 class="mb-1">Sécurité</h6>
                                            <p class="text-muted small mb-0">Mesures de protection adaptées</p>
                                        </div>
                                    </div>

                                    <div class="col-md-3 col-6">
                                        <div class="text-center p-3 border rounded">
                                            <i class="fas fa-eye data-icon"></i>
                                            <h6 class="mb-1">Transparence</h6>
                                            <p class="text-muted small mb-0">Accès et modification possibles</p>
                                        </div>
                                    </div>

                                    <div class="col-md-3 col-6">
                                        <div class="text-center p-3 border rounded">
                                            <i class="fas fa-calendar-times data-icon"></i>
                                            <h6 class="mb-1">Conservation</h6>
                                            <p class="text-muted small mb-0">Durée nécessaire uniquement</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-secondary">
                                    <h6 class="alert-heading">Exercice de vos droits :</h6>
                                    <p class="mb-1"><strong>Email DPD :</strong> dpo@socialmediaawards.fr</p>
                                    <p class="mb-0"><strong>Poste :</strong> [Organisateur] - Service Juridique</p>
                                </div>
                            </div>
                        </div>

                        <!-- Article 7: Droits d'Auteur -->
                        <div class="reglement-article">
                            <h3 class="mb-4">
                                <span class="article-number">7</span> Droits d'Auteur
                            </h3>
                            <div class="ps-4">
                                <p>Les candidats garantissent :</p>
                                <ul class="mb-4">
                                    <li>Être titulaires des droits sur les contenus</li>
                                    <li>Avoir obtenu toutes les autorisations</li>
                                    <li>Contenus originaux sans violation de droits tiers</li>
                                </ul>

                                <div class="alert alert-danger">
                                    <h6 class="alert-heading"><i class="fas fa-exclamation-circle"></i> Attention :</h6>
                                    <p class="mb-0">Toute réclamation pour violation de droits entraîne la disqualification immédiate.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Article 8: Prix et Récompenses -->
                        <div class="reglement-article">
                            <h3 class="mb-4">
                                <span class="article-number">8</span> Prix et Récompenses
                            </h3>
                            <div class="ps-4">
                                <p>Les lauréats reçoivent :</p>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-3 col-6">
                                        <div class="text-center p-3">
                                            <div class="prize-icon">
                                                <i class="fas fa-trophy"></i>
                                            </div>
                                            <h6 class="mb-1">Trophée</h6>
                                            <p class="text-muted small mb-0">Trophée physique personnalisé</p>
                                        </div>
                                    </div>

                                    <div class="col-md-3 col-6">
                                        <div class="text-center p-3">
                                            <div class="prize-icon">
                                                <i class="fas fa-certificate"></i>
                                            </div>
                                            <h6 class="mb-1">Certificat</h6>
                                            <p class="text-muted small mb-0">Reconnaissance officielle</p>
                                        </div>
                                    </div>

                                    <div class="col-md-3 col-6">
                                        <div class="text-center p-3">
                                            <div class="prize-icon">
                                                <i class="fas fa-bullhorn"></i>
                                            </div>
                                            <h6 class="mb-1">Visibilité</h6>
                                            <p class="text-muted small mb-0">Promotion médiatique</p>
                                        </div>
                                    </div>

                                    <div class="col-md-3 col-6">
                                        <div class="text-center p-3">
                                            <div class="prize-icon">
                                                <i class="fas fa-gift"></i>
                                            </div>
                                            <h6 class="mb-1">Partenariats</h6>
                                            <p class="text-muted small mb-0">Collaborations exclusives</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <h6 class="alert-heading">Conditions :</h6>
                                    <ul class="mb-0">
                                        <li>Prix non transférables ni échangeables</li>
                                        <li>Taxes à charge des lauréats</li>
                                        <li>En cas d'égalité, prix partagés</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Article 9: Résultats et Litiges -->
                        <div class="reglement-article">
                            <h3 class="mb-4">
                                <span class="article-number">9</span> Résultats et Litiges
                            </h3>
                            <div class="ps-4">
                                <p>Publication des résultats :</p>
                                <ul class="mb-4">
                                    <li>Site officiel des Social Media Awards</li>
                                    <li>Réseaux sociaux officiels</li>
                                    <li>Cérémonie de remise des prix</li>
                                </ul>

                                <div class="alert alert-secondary">
                                    <h6 class="alert-heading"><i class="fas fa-balance-scale"></i> Règlement des litiges :</h6>
                                    <p class="mb-0">
                                        Solution amiable recherchée en premier lieu.<br>
                                        Tribunaux compétents : lieu du siège social de l'Organisateur.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Article 10: Modifications -->
                        <div class="reglement-article">
                            <h3 class="mb-4">
                                <span class="article-number">10</span> Modifications
                            </h3>
                            <div class="ps-4">
                                <p>L'Organisateur se réserve le droit de :</p>
                                <ul class="mb-4">
                                    <li>Modifier le règlement à tout moment</li>
                                    <li>Interrompre ou annuler le Concours</li>
                                    <li>Prendre mesures pour le bon déroulement</li>
                                </ul>

                                <div class="bg-light p-4 rounded text-center">
                                    <p class="mb-2"><strong>Date d'entrée en vigueur :</strong> <?= date('d/m/Y') ?></p>
                                    <p class="mb-0"><strong>Dernière mise à jour :</strong> <?= date('d/m/Y') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="text-center">
                <p class="mb-0">
                    &copy; <?= date('Y') ?> Social Media Awards. Tous droits réservés. |
                    <a href="#contact" class="text-white text-decoration-none">Contact</a> |
                    <a href="#mentions-legales" class="text-white text-decoration-none">Mentions légales</a> |
                    <a href="#politique-confidentialite" class="text-white text-decoration-none">Politique de confidentialité</a>
                </p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                if (this.getAttribute('href') !== '#') {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });
    </script>
</body>

</html>