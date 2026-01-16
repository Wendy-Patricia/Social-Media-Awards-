# Social-Media-Awards-
Le site Social Media Awards (SMA) permet de voter pour les créateurs de contenu sur les réseaux sociaux et declarer le mieux en chaque categorie. Les utilisateurs peuvent consulter les informations sur le processus de vote et participer en choisissant leurs favoris, le tout dans une interface claire et moderne.

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