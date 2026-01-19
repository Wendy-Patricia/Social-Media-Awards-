# Social-Media-Awards-
Le site Social Media Awards (SMA) permet de voter pour les créateurs de contenu sur les réseaux sociaux et declarer le mieux en chaque categorie. Les utilisateurs peuvent consulter les informations sur le processus de vote et participer en choisissant leurs favoris, le tout dans une interface claire et moderne.

### 🎯 Objectifs principaux
- Créer une expérience de vote transparente et engageante
- Permettre aux créateurs de contenu de se porter candidats
- Offrir aux électeurs un accès facile aux nominations
- Fournir aux administrateurs des outils de gestion complets

# Structure du projet :

SOCIAL-MEDIA-AWARDS/
├── app/
│   ├── Controllers/
│   │   ├── AdminController.php
│   │   ├── NominationController.php
│   │   └── UserController.php
│   ├── Interfaces/
│   │   ├── CategoryServiceInterface.php
│   │   └── UserServiceInterface.php
│   ├── Models/
│   │   ├── Category.php
│   │   ├── Edtion.php
│   │   ├── Candidature.php
│   │   ├── Nomination.php
│   │   └── User.php
│   ├── Services/
│   │   ├── CategoryService.php
│   │   ├──EditionService.php
│   │   ├──CandidatureService.php
│   │   ├──NominationService.php
│   │   └── UserService.php
│   └── autoload.php
├── assets/
│   ├── css/
│   ├── images/
│   └── js/
├── config/
│   ├── database.php
│   ├── permissions.php
│   └── session.php
├── database/
├── views/
│   ├── admin/
│   │   └── candidatures/
│   │       ├── manage-candidatures.php
│   │       └── view-candidatures.php
│   │   ├── categories/
│   │       ├── ajouter-categorie.php
│   │       ├── gerer-categories.php
│   │       └── modifier-categorie.php
│   │   ├── editions/
│   │       ├── ajouter-edition.php
│   │       ├── gerer-edition.php
│   │       └── modifier-edition.php
│   │   ├── nominations/
│   │       ├── edit-nomination.php
│   │       └── manage-nominations.php
│   │   └── dashboard.php
│   ├── candidate/
│   │   ├── candidate-dashboard.php
│   │   ├── candidate-status.php
│   │   ├── edit-profile.php
│   │   └── submit-application.php
│   ├── partials/
│   │   ├── admin-header.php
│   │   ├── admin-sidebar.php
│   │   ├── footer.php
│   │   └── header.php
│   ├── user/
│   │   ├── change-password.php
│   │   ├── edit-profile.php
│   │   └── user-dashboard.php
│   ├── login.php
├──  about.php
├──  categories.php
├── check_dashboards.php
├──  clear_session.php
├── contact.php
├── create_new_user.php
├──  index.php
├── inscription.php
├── login-test.php
├──  logout.php
├── nominees.php
├──  results.php
└──  README.md


##  Rôles Utilisateurs

### 1. **Administrateur**
- Gestion complète de la plateforme
- Gestion des éditions, catégories et nominations
- Modération des candidatures
- Accès aux statistiques détaillées

### 2. **Candidat**
- Soumission de candidatures
- Suivi du statut des nominations
- Gestion du profil public
- Accès au tableau de bord personnel

### 3. **Électeur**
- Consultation des catégories et nominés
- Vote dans les catégories disponibles
- Suivi de l'historique des votes
- Gestion du profil utilisateur

## Fonctionnalités principales

### Système d'authentification
- Inscription avec validation
- Connexion sécurisée
- Rôles multiples (admin, candidat, électeur)
- Gestion des sessions

### Système de vote
- Interface de vote intuitive
- Validation en temps réel
- Sécurité anti-fraude
- Historique des votes

### Tableaux de bord
- **Administrateur** : Statistiques complètes, gestion utilisateurs
- **Candidat** : Suivi des candidatures, catégories disponibles
- **Électeur** : Progression de vote, élections actives

### Pages principales
- **index.php** : Page d'accueil
- **categories.php** : Liste des catégories avec filtres
- **nominees.php** : Galerie des nominés
- **results.php** : Résultats des votes
- **about.php** & **contact.php** : Pages informatives

## Technologies utilisées

### Backend
- **PHP 8.0+** avec programmation orientée objet
- **MySQL** avec PDO
- Architecture MVC modulaire
- Sessions PHP sécurisées

### Frontend
- **HTML5** sémantique
- **CSS3** avec variables et animations
- **JavaScript** vanilla pour l'interactivité

### Sécurité
- Validation des données côté serveur
- Protection CSRF
- Hashage des mots de passe (password_hash)
- Gestion des permissions par rôle
- Sécurisation des sessions

## Prérequis d'installation

### Serveur Web
- PHP 8.0+
- MySQL 5.7+ ou MariaDB 10.2+

### Extensions PHP requises
- PDO MySQL
- Session
- MBString (recommandé)

### Base de données
- Créer une base de données `social_media_awards`
- Importer le schéma depuis `database/`
- Configurer les accès dans `config/database.php`

## Installation

### 1. Configuration de l'environnement

# Cloner le projet
git clone [url-du-projet]

# Déplacer dans le dossier
cd Social-Media-Awards

# Configurer les permissions
chmod 755 assets/images/profiles/
chmod 644 config/database.php

### 2. Configuration de la base de données
-- Créer la base de données
CREATE DATABASE social_media_awards CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;


### 3. Configuration des fichiers
define('DB_HOST', 'localhost');
define('DB_NAME', 'social_media_awards');
define('DB_USER', 'votre_utilisateur');
define('DB_PASS', 'votre_mot_de_passe');


### 4. Premier démarrage
1. Accéder à `http://localhost/Social-Media-Awards-`
2. Créer un compte via le formulaire d'inscription


## Fonctionnalités avancées

### Gestion des éditions
- Création et gestion d'éditions annuelles
- Dates de candidature et de vote configurables
- Édition active unique

### Système de candidature
- Interface de soumission complète
- Validation des pièces jointes
- Modération par les administrateurs
- Notifications par email (à implémenter)

### Statistiques et rapports
- Nombre de votes par catégorie
- Participation des électeurs
- Performance des nominés
- Export des données (à implémenter)


## Améliorations futures

### Court terme
- [ ] Système de notifications par email
- [ ] Export CSV des résultats
- [ ] Interface responsive améliorée
- [ ] Recherche avancée

### Moyen terme
- [ ] API REST pour applications mobiles
- [ ] Intégration OAuth (Google, Facebook)
- [ ] Tableau de bord en temps réel
- [ ] Système de parrainage

### Long terme
- [ ] Application mobile dédiée
- [ ] Analyse prédictive des votes
- [ ] Intégration réseaux sociaux
- [ ] Certificats numériques pour les gagnants


## Licence et crédits

### Licence
Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

### Crédits
- Développé par Wendy Mechisso et Eunice Ligeiro
- Design inspiré des meilleures pratiques web modernes
- Icônes par Font Awesome

### Contributions
Les contributions sont les bienvenues ! Veuillez :
1. Fork le projet
2. Créer une branche pour votre fonctionnalité
3. Commiter vos changements
4. Pousser vers la branche
5. Ouvrir une Pull Request

## Support

### Contact développement
- Email : [contact@socialmediaawards.com]

---

**Version**: 2.0.0  
**Dernière mise à jour**: [18/01/2026]  
**Statut**: En production