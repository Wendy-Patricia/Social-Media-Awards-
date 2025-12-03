<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/results.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Résultats - Social Media Awards 2025</title>
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <div class="main-content">
        <section class="results-hero">
            <div class="container">
                <h1>Résultats 2025</h1>
                <p>Découvrez les gagnants de cette édition des Social Media Awards</p>
                <div class="edition-selector">
                    <select id="editionSelect">
                        <option value="2025" selected>Édition 2025</option>
                        <option value="2024">Édition 2024</option>
                        <option value="2023">Édition 2023</option>
                    </select>
                    <div class="vote-stats">
                        <div class="stat">
                            <div class="stat-number">50,247</div>
                            <div class="stat-label">Votes Totaux</div>
                        </div>
                        <div class="stat">
                            <div class="stat-number">72%</div>
                            <div class="stat-label">Participation</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="winners-overview">
            <div class="container">
                <h2>Les Grands Gagnants</h2>
                <div class="winners-grid">

                    <div class="grand-winner">
                        <div class="winner-crown">
                            <i class="fas fa-crown"></i>
                        </div>
                        <div class="winner-image">
                            <img src="assets/images/Winners/winner1.jpg" alt="Grand Gagnant">
                        </div>
                        <div class="winner-info">
                            <h3>@CreateurStar</h3>
                            <p class="winner-category">Créateur de l'Année</p>
                            <div class="winner-stats">
                                <div class="stat">
                                    <div class="stat-number">15,892</div>
                                    <div class="stat-label">Votes</div>
                                </div>
                                <div class="stat">
                                    <div class="stat-number">32%</div>
                                    <div class="stat-label">Part des votes</div>
                                </div>
                            </div>
                            <div class="platform-badge instagram">
                                <i class="fab fa-instagram"></i>
                                Instagram
                            </div>
                        </div>
                    </div>

                    <div class="top-winners">
                        <div class="top-winner">
                            <div class="winner-rank">2</div>
                            <div class="winner-image">
                                <img src="assets/images/Winners/winner2.jpg" alt="Deuxième">
                            </div>
                            <div class="winner-info">
                                <h4>@TechGuru</h4>
                                <p>Meilleur Podcast</p>
                                <div class="votes">12,456 votes</div>
                            </div>
                        </div>
                        <div class="top-winner">
                            <div class="winner-rank">3</div>
                            <div class="winner-image">
                                <img src="assets/images/Winners/winner3.jpg" alt="Troisième">
                            </div>
                            <div class="winner-info">
                                <h4>@FunnyClips</h4>
                                <p>Contenu Humoristique</p>
                                <div class="votes">10,123 votes</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="category-results">
            <div class="container">
                <h2>Résultats par Catégorie</h2>
                <div class="results-tabs">
                    <button class="tab-btn active" data-tab="all">Toutes</button>
                    <button class="tab-btn" data-tab="tiktok">TikTok</button>
                    <button class="tab-btn" data-tab="instagram">Instagram</button>
                    <button class="tab-btn" data-tab="youtube">YouTube</button>
                </div>

                <div class="results-grid">

                    <div class="result-card" data-platform="tiktok">
                        <div class="category-header">
                            <h3>Créateur Révélation de l'Année</h3>
                            <span class="platform-tag tiktok">TikTok</span>
                        </div>
                        <div class="winners-list">
                            <div class="winner-item gold">
                                <div class="winner-rank">🥇</div>
                                <div class="winner-details">
                                    <h4>@NouveauTalent</h4>
                                    <p>8,456 votes</p>
                                </div>
                                <div class="vote-percentage">42%</div>
                            </div>
                            <div class="winner-item silver">
                                <div class="winner-rank">🥈</div>
                                <div class="winner-details">
                                    <h4>@RisingStar</h4>
                                    <p>5,123 votes</p>
                                </div>
                                <div class="vote-percentage">26%</div>
                            </div>
                            <div class="winner-item bronze">
                                <div class="winner-rank">🥉</div>
                                <div class="winner-details">
                                    <h4>@FreshContent</h4>
                                    <p>3,210 votes</p>
                                </div>
                                <div class="vote-percentage">16%</div>
                            </div>
                        </div>
                        <div class="category-stats">
                            <div class="stat">
                                <span>Total votes:</span>
                                <strong>19,847</strong>
                            </div>
                            <div class="stat">
                                <span>Participants:</span>
                                <strong>25</strong>
                            </div>
                        </div>
                    </div>


                    <div class="result-card" data-platform="youtube">
                        <div class="category-header">
                            <h3>Meilleur Podcast en Ligne</h3>
                            <span class="platform-tag youtube">YouTube</span>
                        </div>
                        <div class="winners-list">
                            <div class="winner-item gold">
                                <div class="winner-rank">🥇</div>
                                <div class="winner-details">
                                    <h4>TechTalk Podcast</h4>
                                    <p>12,456 votes</p>
                                </div>
                                <div class="vote-percentage">38%</div>
                            </div>
                            <div class="winner-item silver">
                                <div class="winner-rank">🥈</div>
                                <div class="winner-details">
                                    <h4>Culture Stream</h4>
                                    <p>8,901 votes</p>
                                </div>
                                <div class="vote-percentage">27%</div>
                            </div>
                            <div class="winner-item bronze">
                                <div class="winner-rank">🥉</div>
                                <div class="winner-details">
                                    <h4>Digital Insights</h4>
                                    <p>5,678 votes</p>
                                </div>
                                <div class="vote-percentage">17%</div>
                            </div>
                        </div>
                        <div class="category-stats">
                            <div class="stat">
                                <span>Total votes:</span>
                                <strong>32,567</strong>
                            </div>
                            <div class="stat">
                                <span>Participants:</span>
                                <strong>18</strong>
                            </div>
                        </div>
                    </div>

                    <div class="result-card" data-platform="instagram">
                        <div class="category-header">
                            <h3>Meilleur Contenu Éducatif</h3>
                            <span class="platform-tag instagram">Instagram</span>
                        </div>
                        <div class="winners-list">
                            <div class="winner-item gold">
                                <div class="winner-rank">🥇</div>
                                <div class="winner-details">
                                    <h4>@ScienceDaily</h4>
                                    <p>9,876 votes</p>
                                </div>
                                <div class="vote-percentage">45%</div>
                            </div>
                            <div class="winner-item silver">
                                <div class="winner-rank">🥈</div>
                                <div class="winner-details">
                                    <h4>@LearnWithMe</h4>
                                    <p>6,543 votes</p>
                                </div>
                                <div class="vote-percentage">30%</div>
                            </div>
                            <div class="winner-item bronze">
                                <div class="winner-rank">🥉</div>
                                <div class="winner-details">
                                    <h4>@KnowledgeHub</h4>
                                    <p>3,210 votes</p>
                                </div>
                                <div class="vote-percentage">15%</div>
                            </div>
                        </div>
                        <div class="category-stats">
                            <div class="stat">
                                <span>Total votes:</span>
                                <strong>21,629</strong>
                            </div>
                            <div class="stat">
                                <span>Participants:</span>
                                <strong>22</strong>
                            </div>
                        </div>
                    </div>


                    <div class="result-card" data-platform="tiktok">
                        <div class="category-header">
                            <h3>Meilleur Contenu Humoristique</h3>
                            <span class="platform-tag tiktok">TikTok</span>
                        </div>
                        <div class="winners-list">
                            <div class="winner-item gold">
                                <div class="winner-rank">🥇</div>
                                <div class="winner-details">
                                    <h4>@FunnyMoments</h4>
                                    <p>11,234 votes</p>
                                </div>
                                <div class="vote-percentage">40%</div>
                            </div>
                            <div class="winner-item silver">
                                <div class="winner-rank">🥈</div>
                                <div class="winner-details">
                                    <h4>@ComedyKing</h4>
                                    <p>7,890 votes</p>
                                </div>
                                <div class="vote-percentage">28%</div>
                            </div>
                            <div class="winner-item bronze">
                                <div class="winner-rank">🥉</div>
                                <div class="winner-details">
                                    <h4>@LaughFactory</h4>
                                    <p>4,567 votes</p>
                                </div>
                                <div class="vote-percentage">16%</div>
                            </div>
                        </div>
                        <div class="category-stats">
                            <div class="stat">
                                <span>Total votes:</span>
                                <strong>28,123</strong>
                            </div>
                            <div class="stat">
                                <span>Participants:</span>
                                <strong>20</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="statistics-section">
            <div class="container">
                <h2>Statistiques Globales</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">50,247</div>
                            <div class="stat-label">Votes Totaux</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">12</div>
                            <div class="stat-label">Catégories</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-user-friends"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">156</div>
                            <div class="stat-label">Nominés</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">72%</div>
                            <div class="stat-label">Taux de Participation</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include 'partials/footer.php'; ?>
    <script src="assets/js/results.js"></script>
</body>
</html>