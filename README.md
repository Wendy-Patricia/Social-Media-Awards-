# Social-Media-Awards
Le site Social Media Awards (SMA) permet de voter pour les créateurs de contenu sur les réseaux sociaux et de désigner les meilleurs dans chaque catégorie.
Les utilisateurs peuvent consulter les informations sur le processus de vote, explorer les catégories, découvrir les candidats et choisir leurs favoris dans une interface claire et moderne.

# 📁 Structure du projet
/
├── index.php              → Page d'accueil  
├── contact.php            → Page de contact  
├── categories.php         → Liste des catégories de vote  
├── candidats.php          → Page affichant les candidats d'une catégorie  
├── vote.php               → Page pour soumettre un vote  
├── results.php            → Résultats des votes  

├── header.php             → En-tête du site  
├── footer.php             → Pied de page  

├── config/
│   └── database.php       → Connexion à la base de données

├── controllers/
│   ├── VoteController.php → Gestion des votes  
│   └── UserController.php → Gestion des interactions utilisateur

├── assets/
│   ├── css/               → Styles CSS  
│   │   └── style.css
│   ├── js/                → Scripts JS  
│   │   └── app.js
│   └── images/            → Images, logos et icônes

├── uploads/
│   └── candidats/         → Photos des candidats  

└── README.md              → Documentation du projet

# Fonctionnalités principales

Vote en ligne pour les créateurs de contenu

Affichage des catégories et candidats

Système de vote simple et intuitif

Résultats mis à jour automatiquement

Interface responsive et moderne

# Technologies utilisées

PHP (structure du site et gestion des votes)

HTML / CSS / JavaScript (interface utilisateur)

MySQL (stockage des votes, catégories et candidats)