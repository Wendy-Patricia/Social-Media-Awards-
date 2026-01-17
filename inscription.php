<?php
// inscription.php
require_once 'app/Controllers/UserController.php';
require_once 'config/session.php';

// Se já estiver autenticado, redirecionar
if (isAuthenticated()) {
    $redirect = match (getUserType()) {
        'admin' => '/Social-Media-Awards-/admin/admin-dashboard.php',
        'candidate' => '/Social-Media-Awards-/candidate/candidate-dashboard.php',
        'voter' => '/Social-Media-Awards-/user/user-dashboard.php',
        default => 'index.php'
    };
    header("Location: $redirect");
    exit();
}

$controller = new UserController();
$result = $controller->handleRegistration();

// Se registro for bem-sucedido, já será redirecionado pelo controller
// Se chegou aqui, é porque houve erro ou é a primeira carga da página
$errors = $result['errors'] ?? [];
$data = $result['data'] ?? [];
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Social Media Awards</title>
    <link rel="stylesheet" href="/Social-Media-Awards-/assets/css/base.css">
    <link rel="stylesheet" href="/Social-Media-Awards-/assets/css/inscription.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <main class="inscription-container">
        <div class="container">
            <div class="inscription-header">
                <h1><i class="fas fa-user-plus"></i> Créer un Compte</h1>
                <p>Rejoignez la communauté Social Media Awards</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <h3>Erreurs de validation</h3>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <div class="inscription-card">
                <form method="POST" action="" class="inscription-form" id="inscriptionForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="pseudonyme" class="form-label">
                                <i class="fas fa-user"></i> Pseudonyme *
                            </label>
                            <input type="text"
                                id="pseudonyme"
                                name="pseudonyme"
                                class="form-control"
                                value="<?php echo htmlspecialchars($data['pseudonyme'] ?? ''); ?>"
                                required
                                minlength="3"
                                maxlength="50"
                                placeholder="Votre nom public">
                            <small class="form-text">Votre nom public (3-50 caractères)</small>
                        </div>

                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i> Adresse email *
                            </label>
                            <input type="email"
                                id="email"
                                name="email"
                                class="form-control"
                                value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>"
                                required
                                placeholder="exemple@email.com">
                            <small class="form-text">Nous ne partagerons jamais votre email</small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="mot_de_passe" class="form-label">
                                <i class="fas fa-lock"></i> Mot de passe *
                            </label>
                            <input type="password"
                                id="mot_de_passe"
                                name="mot_de_passe"
                                class="form-control"
                                required
                                minlength="6"
                                placeholder="••••••">
                            <small class="form-text">Minimum 6 caractères</small>
                        </div>

                        <div class="form-group">
                            <label for="confirm_mot_de_passe" class="form-label">
                                <i class="fas fa-lock"></i> Confirmer le mot de passe *
                            </label>
                            <input type="password"
                                id="confirm_mot_de_passe"
                                name="confirm_mot_de_passe"
                                class="form-control"
                                required
                                placeholder="••••••">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="type_user" class="form-label">
                                <i class="fas fa-user-tag"></i> Type de compte *
                            </label>
                            <select id="type_user" name="type_user" class="form-control" required>
                                <option value="">Sélectionnez un type</option>
                                <option value="voter" <?php echo ($data['type_user'] ?? '') == 'voter' ? 'selected' : ''; ?>>
                                    Électeur - Je veux voter pour mes favoris
                                </option>
                                <option value="candidate" <?php echo ($data['type_user'] ?? '') == 'candidate' ? 'selected' : ''; ?>>
                                    Candidat - Je veux participer aux élections
                                </option>
                            </select>
                            <small class="form-text">Vous pourrez modifier ce choix plus tard</small>
                        </div>

                        <div class="form-group">
                            <label for="date_naissance" class="form-label">
                                <i class="fas fa-birthday-cake"></i> Date de naissance *
                            </label>
                            <input type="date"
                                id="date_naissance"
                                name="date_naissance"
                                class="form-control"
                                value="<?php echo htmlspecialchars($data['date_naissance'] ?? ''); ?>"
                                required
                                max="<?php echo date('Y-m-d', strtotime('-13 years')); ?>">
                            <small class="form-text">Vous devez avoir au moins 13 ans</small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="pays" class="form-label">
                                <i class="fas fa-globe"></i> Pays *
                            </label>
                            <select id="pays" name="pays" class="form-control" required>
                                <option value="">Sélectionnez votre pays</option>
                                <option value="Afghanistan" <?php echo ($data['pays'] ?? '') == 'Afghanistan' ? 'selected' : ''; ?>>Afghanistan</option>
                                <option value="Afrique du Sud" <?php echo ($data['pays'] ?? '') == 'Afrique du Sud' ? 'selected' : ''; ?>>Afrique du Sud</option>
                                <option value="Albanie" <?php echo ($data['pays'] ?? '') == 'Albanie' ? 'selected' : ''; ?>>Albanie</option>
                                <option value="Algérie" <?php echo ($data['pays'] ?? '') == 'Algérie' ? 'selected' : ''; ?>>Algérie</option>
                                <option value="Allemagne" <?php echo ($data['pays'] ?? '') == 'Allemagne' ? 'selected' : ''; ?>>Allemagne</option>
                                <option value="Andorre" <?php echo ($data['pays'] ?? '') == 'Andorre' ? 'selected' : ''; ?>>Andorre</option>
                                <option value="Angola" <?php echo ($data['pays'] ?? '') == 'Angola' ? 'selected' : ''; ?>>Angola</option>
                                <option value="Antigua-et-Barbuda" <?php echo ($data['pays'] ?? '') == 'Antigua-et-Barbuda' ? 'selected' : ''; ?>>Antigua-et-Barbuda</option>
                                <option value="Arabie saoudite" <?php echo ($data['pays'] ?? '') == 'Arabie saoudite' ? 'selected' : ''; ?>>Arabie saoudite</option>
                                <option value="Argentine" <?php echo ($data['pays'] ?? '') == 'Argentine' ? 'selected' : ''; ?>>Argentine</option>
                                <option value="Arménie" <?php echo ($data['pays'] ?? '') == 'Arménie' ? 'selected' : ''; ?>>Arménie</option>
                                <option value="Australie" <?php echo ($data['pays'] ?? '') == 'Australie' ? 'selected' : ''; ?>>Australie</option>
                                <option value="Autriche" <?php echo ($data['pays'] ?? '') == 'Autriche' ? 'selected' : ''; ?>>Autriche</option>
                                <option value="Azerbaïdjan" <?php echo ($data['pays'] ?? '') == 'Azerbaïdjan' ? 'selected' : ''; ?>>Azerbaïdjan</option>
                                <option value="Bahamas" <?php echo ($data['pays'] ?? '') == 'Bahamas' ? 'selected' : ''; ?>>Bahamas</option>
                                <option value="Bahreïn" <?php echo ($data['pays'] ?? '') == 'Bahreïn' ? 'selected' : ''; ?>>Bahreïn</option>
                                <option value="Bangladesh" <?php echo ($data['pays'] ?? '') == 'Bangladesh' ? 'selected' : ''; ?>>Bangladesh</option>
                                <option value="Barbade" <?php echo ($data['pays'] ?? '') == 'Barbade' ? 'selected' : ''; ?>>Barbade</option>
                                <option value="Belgique" <?php echo ($data['pays'] ?? '') == 'Belgique' ? 'selected' : ''; ?>>Belgique</option>
                                <option value="Belize" <?php echo ($data['pays'] ?? '') == 'Belize' ? 'selected' : ''; ?>>Belize</option>
                                <option value="Bénin" <?php echo ($data['pays'] ?? '') == 'Bénin' ? 'selected' : ''; ?>>Bénin</option>
                                <option value="Bhoutan" <?php echo ($data['pays'] ?? '') == 'Bhoutan' ? 'selected' : ''; ?>>Bhoutan</option>
                                <option value="Biélorussie" <?php echo ($data['pays'] ?? '') == 'Biélorussie' ? 'selected' : ''; ?>>Biélorussie</option>
                                <option value="Birmanie" <?php echo ($data['pays'] ?? '') == 'Birmanie' ? 'selected' : ''; ?>>Birmanie</option>
                                <option value="Bolivie" <?php echo ($data['pays'] ?? '') == 'Bolivie' ? 'selected' : ''; ?>>Bolivie</option>
                                <option value="Bosnie-Herzégovine" <?php echo ($data['pays'] ?? '') == 'Bosnie-Herzégovine' ? 'selected' : ''; ?>>Bosnie-Herzégovine</option>
                                <option value="Botswana" <?php echo ($data['pays'] ?? '') == 'Botswana' ? 'selected' : ''; ?>>Botswana</option>
                                <option value="Brésil" <?php echo ($data['pays'] ?? '') == 'Brésil' ? 'selected' : ''; ?>>Brésil</option>
                                <option value="Brunei" <?php echo ($data['pays'] ?? '') == 'Brunei' ? 'selected' : ''; ?>>Brunei</option>
                                <option value="Bulgarie" <?php echo ($data['pays'] ?? '') == 'Bulgarie' ? 'selected' : ''; ?>>Bulgarie</option>
                                <option value="Burkina Faso" <?php echo ($data['pays'] ?? '') == 'Burkina Faso' ? 'selected' : ''; ?>>Burkina Faso</option>
                                <option value="Burundi" <?php echo ($data['pays'] ?? '') == 'Burundi' ? 'selected' : ''; ?>>Burundi</option>
                                <option value="Cambodge" <?php echo ($data['pays'] ?? '') == 'Cambodge' ? 'selected' : ''; ?>>Cambodge</option>
                                <option value="Cameroun" <?php echo ($data['pays'] ?? '') == 'Cameroun' ? 'selected' : ''; ?>>Cameroun</option>
                                <option value="Canada" <?php echo ($data['pays'] ?? '') == 'Canada' ? 'selected' : ''; ?>>Canada</option>
                                <option value="Cap-Vert" <?php echo ($data['pays'] ?? '') == 'Cap-Vert' ? 'selected' : ''; ?>>Cap-Vert</option>
                                <option value="Chili" <?php echo ($data['pays'] ?? '') == 'Chili' ? 'selected' : ''; ?>>Chili</option>
                                <option value="Chine" <?php echo ($data['pays'] ?? '') == 'Chine' ? 'selected' : ''; ?>>Chine</option>
                                <option value="Chypre" <?php echo ($data['pays'] ?? '') == 'Chypre' ? 'selected' : ''; ?>>Chypre</option>
                                <option value="Colombie" <?php echo ($data['pays'] ?? '') == 'Colombie' ? 'selected' : ''; ?>>Colombie</option>
                                <option value="Comores" <?php echo ($data['pays'] ?? '') == 'Comores' ? 'selected' : ''; ?>>Comores</option>
                                <option value="Congo" <?php echo ($data['pays'] ?? '') == 'Congo' ? 'selected' : ''; ?>>Congo</option>
                                <option value="Corée du Nord" <?php echo ($data['pays'] ?? '') == 'Corée du Nord' ? 'selected' : ''; ?>>Corée du Nord</option>
                                <option value="Corée du Sud" <?php echo ($data['pays'] ?? '') == 'Corée du Sud' ? 'selected' : ''; ?>>Corée du Sud</option>
                                <option value="Costa Rica" <?php echo ($data['pays'] ?? '') == 'Costa Rica' ? 'selected' : ''; ?>>Costa Rica</option>
                                <option value="Croatie" <?php echo ($data['pays'] ?? '') == 'Croatie' ? 'selected' : ''; ?>>Croatie</option>
                                <option value="Cuba" <?php echo ($data['pays'] ?? '') == 'Cuba' ? 'selected' : ''; ?>>Cuba</option>
                                <option value="Danemark" <?php echo ($data['pays'] ?? '') == 'Danemark' ? 'selected' : ''; ?>>Danemark</option>
                                <option value="Djibouti" <?php echo ($data['pays'] ?? '') == 'Djibouti' ? 'selected' : ''; ?>>Djibouti</option>
                                <option value="Dominique" <?php echo ($data['pays'] ?? '') == 'Dominique' ? 'selected' : ''; ?>>Dominique</option>
                                <option value="Égypte" <?php echo ($data['pays'] ?? '') == 'Égypte' ? 'selected' : ''; ?>>Égypte</option>
                                <option value="Émirats arabes unis" <?php echo ($data['pays'] ?? '') == 'Émirats arabes unis' ? 'selected' : ''; ?>>Émirats arabes unis</option>
                                <option value="Équateur" <?php echo ($data['pays'] ?? '') == 'Équateur' ? 'selected' : ''; ?>>Équateur</option>
                                <option value="Érythrée" <?php echo ($data['pays'] ?? '') == 'Érythrée' ? 'selected' : ''; ?>>Érythrée</option>
                                <option value="Espagne" <?php echo ($data['pays'] ?? '') == 'Espagne' ? 'selected' : ''; ?>>Espagne</option>
                                <option value="Estonie" <?php echo ($data['pays'] ?? '') == 'Estonie' ? 'selected' : ''; ?>>Estonie</option>
                                <option value="États-Unis" <?php echo ($data['pays'] ?? '') == 'États-Unis' ? 'selected' : ''; ?>>États-Unis</option>
                                <option value="Éthiopie" <?php echo ($data['pays'] ?? '') == 'Éthiopie' ? 'selected' : ''; ?>>Éthiopie</option>
                                <option value="Fidji" <?php echo ($data['pays'] ?? '') == 'Fidji' ? 'selected' : ''; ?>>Fidji</option>
                                <option value="Finlande" <?php echo ($data['pays'] ?? '') == 'Finlande' ? 'selected' : ''; ?>>Finlande</option>
                                <option value="France" <?php echo ($data['pays'] ?? '') == 'France' ? 'selected' : ''; ?>>France</option>
                                <option value="Gabon" <?php echo ($data['pays'] ?? '') == 'Gabon' ? 'selected' : ''; ?>>Gabon</option>
                                <option value="Gambie" <?php echo ($data['pays'] ?? '') == 'Gambie' ? 'selected' : ''; ?>>Gambie</option>
                                <option value="Géorgie" <?php echo ($data['pays'] ?? '') == 'Géorgie' ? 'selected' : ''; ?>>Géorgie</option>
                                <option value="Ghana" <?php echo ($data['pays'] ?? '') == 'Ghana' ? 'selected' : ''; ?>>Ghana</option>
                                <option value="Grèce" <?php echo ($data['pays'] ?? '') == 'Grèce' ? 'selected' : ''; ?>>Grèce</option>
                                <option value="Grenade" <?php echo ($data['pays'] ?? '') == 'Grenade' ? 'selected' : ''; ?>>Grenade</option>
                                <option value="Guatemala" <?php echo ($data['pays'] ?? '') == 'Guatemala' ? 'selected' : ''; ?>>Guatemala</option>
                                <option value="Guinée" <?php echo ($data['pays'] ?? '') == 'Guinée' ? 'selected' : ''; ?>>Guinée</option>
                                <option value="Guinée équatoriale" <?php echo ($data['pays'] ?? '') == 'Guinée équatoriale' ? 'selected' : ''; ?>>Guinée équatoriale</option>
                                <option value="Guinée-Bissau" <?php echo ($data['pays'] ?? '') == 'Guinée-Bissau' ? 'selected' : ''; ?>>Guinée-Bissau</option>
                                <option value="Guyana" <?php echo ($data['pays'] ?? '') == 'Guyana' ? 'selected' : ''; ?>>Guyana</option>
                                <option value="Haïti" <?php echo ($data['pays'] ?? '') == 'Haïti' ? 'selected' : ''; ?>>Haïti</option>
                                <option value="Honduras" <?php echo ($data['pays'] ?? '') == 'Honduras' ? 'selected' : ''; ?>>Honduras</option>
                                <option value="Hongrie" <?php echo ($data['pays'] ?? '') == 'Hongrie' ? 'selected' : ''; ?>>Hongrie</option>
                                <option value="Îles Cook" <?php echo ($data['pays'] ?? '') == 'Îles Cook' ? 'selected' : ''; ?>>Îles Cook</option>
                                <option value="Îles Marshall" <?php echo ($data['pays'] ?? '') == 'Îles Marshall' ? 'selected' : ''; ?>>Îles Marshall</option>
                                <option value="Îles Salomon" <?php echo ($data['pays'] ?? '') == 'Îles Salomon' ? 'selected' : ''; ?>>Îles Salomon</option>
                                <option value="Inde" <?php echo ($data['pays'] ?? '') == 'Inde' ? 'selected' : ''; ?>>Inde</option>
                                <option value="Indonésie" <?php echo ($data['pays'] ?? '') == 'Indonésie' ? 'selected' : ''; ?>>Indonésie</option>
                                <option value="Irak" <?php echo ($data['pays'] ?? '') == 'Irak' ? 'selected' : ''; ?>>Irak</option>
                                <option value="Iran" <?php echo ($data['pays'] ?? '') == 'Iran' ? 'selected' : ''; ?>>Iran</option>
                                <option value="Irlande" <?php echo ($data['pays'] ?? '') == 'Irlande' ? 'selected' : ''; ?>>Irlande</option>
                                <option value="Islande" <?php echo ($data['pays'] ?? '') == 'Islande' ? 'selected' : ''; ?>>Islande</option>
                                <option value="Israël" <?php echo ($data['pays'] ?? '') == 'Israël' ? 'selected' : ''; ?>>Israël</option>
                                <option value="Italie" <?php echo ($data['pays'] ?? '') == 'Italie' ? 'selected' : ''; ?>>Italie</option>
                                <option value="Jamaïque" <?php echo ($data['pays'] ?? '') == 'Jamaïque' ? 'selected' : ''; ?>>Jamaïque</option>
                                <option value="Japon" <?php echo ($data['pays'] ?? '') == 'Japon' ? 'selected' : ''; ?>>Japon</option>
                                <option value="Jordanie" <?php echo ($data['pays'] ?? '') == 'Jordanie' ? 'selected' : ''; ?>>Jordanie</option>
                                <option value="Kazakhstan" <?php echo ($data['pays'] ?? '') == 'Kazakhstan' ? 'selected' : ''; ?>>Kazakhstan</option>
                                <option value="Kenya" <?php echo ($data['pays'] ?? '') == 'Kenya' ? 'selected' : ''; ?>>Kenya</option>
                                <option value="Kirghizistan" <?php echo ($data['pays'] ?? '') == 'Kirghizistan' ? 'selected' : ''; ?>>Kirghizistan</option>
                                <option value="Kiribati" <?php echo ($data['pays'] ?? '') == 'Kiribati' ? 'selected' : ''; ?>>Kiribati</option>
                                <option value="Kosovo" <?php echo ($data['pays'] ?? '') == 'Kosovo' ? 'selected' : ''; ?>>Kosovo</option>
                                <option value="Koweït" <?php echo ($data['pays'] ?? '') == 'Koweït' ? 'selected' : ''; ?>>Koweït</option>
                                <option value="Laos" <?php echo ($data['pays'] ?? '') == 'Laos' ? 'selected' : ''; ?>>Laos</option>
                                <option value="Lesotho" <?php echo ($data['pays'] ?? '') == 'Lesotho' ? 'selected' : ''; ?>>Lesotho</option>
                                <option value="Lettonie" <?php echo ($data['pays'] ?? '') == 'Lettonie' ? 'selected' : ''; ?>>Lettonie</option>
                                <option value="Liban" <?php echo ($data['pays'] ?? '') == 'Liban' ? 'selected' : ''; ?>>Liban</option>
                                <option value="Libéria" <?php echo ($data['pays'] ?? '') == 'Libéria' ? 'selected' : ''; ?>>Libéria</option>
                                <option value="Libye" <?php echo ($data['pays'] ?? '') == 'Libye' ? 'selected' : ''; ?>>Libye</option>
                                <option value="Liechtenstein" <?php echo ($data['pays'] ?? '') == 'Liechtenstein' ? 'selected' : ''; ?>>Liechtenstein</option>
                                <option value="Lituanie" <?php echo ($data['pays'] ?? '') == 'Lituanie' ? 'selected' : ''; ?>>Lituanie</option>
                                <option value="Luxembourg" <?php echo ($data['pays'] ?? '') == 'Luxembourg' ? 'selected' : ''; ?>>Luxembourg</option>
                                <option value="Macédoine du Nord" <?php echo ($data['pays'] ?? '') == 'Macédoine du Nord' ? 'selected' : ''; ?>>Macédoine du Nord</option>
                                <option value="Madagascar" <?php echo ($data['pays'] ?? '') == 'Madagascar' ? 'selected' : ''; ?>>Madagascar</option>
                                <option value="Malaisie" <?php echo ($data['pays'] ?? '') == 'Malaisie' ? 'selected' : ''; ?>>Malaisie</option>
                                <option value="Malawi" <?php echo ($data['pays'] ?? '') == 'Malawi' ? 'selected' : ''; ?>>Malawi</option>
                                <option value="Maldives" <?php echo ($data['pays'] ?? '') == 'Maldives' ? 'selected' : ''; ?>>Maldives</option>
                                <option value="Mali" <?php echo ($data['pays'] ?? '') == 'Mali' ? 'selected' : ''; ?>>Mali</option>
                                <option value="Malte" <?php echo ($data['pays'] ?? '') == 'Malte' ? 'selected' : ''; ?>>Malte</option>
                                <option value="Maroc" <?php echo ($data['pays'] ?? '') == 'Maroc' ? 'selected' : ''; ?>>Maroc</option>
                                <option value="Maurice" <?php echo ($data['pays'] ?? '') == 'Maurice' ? 'selected' : ''; ?>>Maurice</option>
                                <option value="Mauritanie" <?php echo ($data['pays'] ?? '') == 'Mauritanie' ? 'selected' : ''; ?>>Mauritanie</option>
                                <option value="Mexique" <?php echo ($data['pays'] ?? '') == 'Mexique' ? 'selected' : ''; ?>>Mexique</option>
                                <option value="Micronésie" <?php echo ($data['pays'] ?? '') == 'Micronésie' ? 'selected' : ''; ?>>Micronésie</option>
                                <option value="Moldavie" <?php echo ($data['pays'] ?? '') == 'Moldavie' ? 'selected' : ''; ?>>Moldavie</option>
                                <option value="Monaco" <?php echo ($data['pays'] ?? '') == 'Monaco' ? 'selected' : ''; ?>>Monaco</option>
                                <option value="Mongolie" <?php echo ($data['pays'] ?? '') == 'Mongolie' ? 'selected' : ''; ?>>Mongolie</option>
                                <option value="Monténégro" <?php echo ($data['pays'] ?? '') == 'Monténégro' ? 'selected' : ''; ?>>Monténégro</option>
                                <option value="Mozambique" <?php echo ($data['pays'] ?? '') == 'Mozambique' ? 'selected' : ''; ?>>Mozambique</option>
                                <option value="Namibie" <?php echo ($data['pays'] ?? '') == 'Namibie' ? 'selected' : ''; ?>>Namibie</option>
                                <option value="Nauru" <?php echo ($data['pays'] ?? '') == 'Nauru' ? 'selected' : ''; ?>>Nauru</option>
                                <option value="Népal" <?php echo ($data['pays'] ?? '') == 'Népal' ? 'selected' : ''; ?>>Népal</option>
                                <option value="Nicaragua" <?php echo ($data['pays'] ?? '') == 'Nicaragua' ? 'selected' : ''; ?>>Nicaragua</option>
                                <option value="Niger" <?php echo ($data['pays'] ?? '') == 'Niger' ? 'selected' : ''; ?>>Niger</option>
                                <option value="Nigéria" <?php echo ($data['pays'] ?? '') == 'Nigéria' ? 'selected' : ''; ?>>Nigéria</option>
                                <option value="Norvège" <?php echo ($data['pays'] ?? '') == 'Norvège' ? 'selected' : ''; ?>>Norvège</option>
                                <option value="Nouvelle-Zélande" <?php echo ($data['pays'] ?? '') == 'Nouvelle-Zélande' ? 'selected' : ''; ?>>Nouvelle-Zélande</option>
                                <option value="Oman" <?php echo ($data['pays'] ?? '') == 'Oman' ? 'selected' : ''; ?>>Oman</option>
                                <option value="Ouganda" <?php echo ($data['pays'] ?? '') == 'Ouganda' ? 'selected' : ''; ?>>Ouganda</option>
                                <option value="Ouzbékistan" <?php echo ($data['pays'] ?? '') == 'Ouzbékistan' ? 'selected' : ''; ?>>Ouzbékistan</option>
                                <option value="Pakistan" <?php echo ($data['pays'] ?? '') == 'Pakistan' ? 'selected' : ''; ?>>Pakistan</option>
                                <option value="Palaos" <?php echo ($data['pays'] ?? '') == 'Palaos' ? 'selected' : ''; ?>>Palaos</option>
                                <option value="Palestine" <?php echo ($data['pays'] ?? '') == 'Palestine' ? 'selected' : ''; ?>>Palestine</option>
                                <option value="Panama" <?php echo ($data['pays'] ?? '') == 'Panama' ? 'selected' : ''; ?>>Panama</option>
                                <option value="Papouasie-Nouvelle-Guinée" <?php echo ($data['pays'] ?? '') == 'Papouasie-Nouvelle-Guinée' ? 'selected' : ''; ?>>Papouasie-Nouvelle-Guinée</option>
                                <option value="Paraguay" <?php echo ($data['pays'] ?? '') == 'Paraguay' ? 'selected' : ''; ?>>Paraguay</option>
                                <option value="Pays-Bas" <?php echo ($data['pays'] ?? '') == 'Pays-Bas' ? 'selected' : ''; ?>>Pays-Bas</option>
                                <option value="Pérou" <?php echo ($data['pays'] ?? '') == 'Pérou' ? 'selected' : ''; ?>>Pérou</option>
                                <option value="Philippines" <?php echo ($data['pays'] ?? '') == 'Philippines' ? 'selected' : ''; ?>>Philippines</option>
                                <option value="Pologne" <?php echo ($data['pays'] ?? '') == 'Pologne' ? 'selected' : ''; ?>>Pologne</option>
                                <option value="Portugal" <?php echo ($data['pays'] ?? '') == 'Portugal' ? 'selected' : ''; ?>>Portugal</option>
                                <option value="Qatar" <?php echo ($data['pays'] ?? '') == 'Qatar' ? 'selected' : ''; ?>>Qatar</option>
                                <option value="République centrafricaine" <?php echo ($data['pays'] ?? '') == 'République centrafricaine' ? 'selected' : ''; ?>>République centrafricaine</option>
                                <option value="République démocratique du Congo" <?php echo ($data['pays'] ?? '') == 'République démocratique du Congo' ? 'selected' : ''; ?>>République démocratique du Congo</option>
                                <option value="République dominicaine" <?php echo ($data['pays'] ?? '') == 'République dominicaine' ? 'selected' : ''; ?>>République dominicaine</option>
                                <option value="République tchèque" <?php echo ($data['pays'] ?? '') == 'République tchèque' ? 'selected' : ''; ?>>République tchèque</option>
                                <option value="Roumanie" <?php echo ($data['pays'] ?? '') == 'Roumanie' ? 'selected' : ''; ?>>Roumanie</option>
                                <option value="Royaume-Uni" <?php echo ($data['pays'] ?? '') == 'Royaume-Uni' ? 'selected' : ''; ?>>Royaume-Uni</option>
                                <option value="Russie" <?php echo ($data['pays'] ?? '') == 'Russie' ? 'selected' : ''; ?>>Russie</option>
                                <option value="Rwanda" <?php echo ($data['pays'] ?? '') == 'Rwanda' ? 'selected' : ''; ?>>Rwanda</option>
                                <option value="Saint-Christophe-et-Niévès" <?php echo ($data['pays'] ?? '') == 'Saint-Christophe-et-Niévès' ? 'selected' : ''; ?>>Saint-Christophe-et-Niévès</option>
                                <option value="Sainte-Lucie" <?php echo ($data['pays'] ?? '') == 'Sainte-Lucie' ? 'selected' : ''; ?>>Sainte-Lucie</option>
                                <option value="Saint-Marin" <?php echo ($data['pays'] ?? '') == 'Saint-Marin' ? 'selected' : ''; ?>>Saint-Marin</option>
                                <option value="Saint-Vincent-et-les-Grenadines" <?php echo ($data['pays'] ?? '') == 'Saint-Vincent-et-les-Grenadines' ? 'selected' : ''; ?>>Saint-Vincent-et-les-Grenadines</option>
                                <option value="Salvador" <?php echo ($data['pays'] ?? '') == 'Salvador' ? 'selected' : ''; ?>>Salvador</option>
                                <option value="Samoa" <?php echo ($data['pays'] ?? '') == 'Samoa' ? 'selected' : ''; ?>>Samoa</option>
                                <option value="Sao Tomé-et-Principe" <?php echo ($data['pays'] ?? '') == 'Sao Tomé-et-Principe' ? 'selected' : ''; ?>>Sao Tomé-et-Principe</option>
                                <option value="Sénégal" <?php echo ($data['pays'] ?? '') == 'Sénégal' ? 'selected' : ''; ?>>Sénégal</option>
                                <option value="Serbie" <?php echo ($data['pays'] ?? '') == 'Serbie' ? 'selected' : ''; ?>>Serbie</option>
                                <option value="Seychelles" <?php echo ($data['pays'] ?? '') == 'Seychelles' ? 'selected' : ''; ?>>Seychelles</option>
                                <option value="Sierra Leone" <?php echo ($data['pays'] ?? '') == 'Sierra Leone' ? 'selected' : ''; ?>>Sierra Leone</option>
                                <option value="Singapour" <?php echo ($data['pays'] ?? '') == 'Singapour' ? 'selected' : ''; ?>>Singapour</option>
                                <option value="Slovaquie" <?php echo ($data['pays'] ?? '') == 'Slovaquie' ? 'selected' : ''; ?>>Slovaquie</option>
                                <option value="Slovénie" <?php echo ($data['pays'] ?? '') == 'Slovénie' ? 'selected' : ''; ?>>Slovénie</option>
                                <option value="Somalie" <?php echo ($data['pays'] ?? '') == 'Somalie' ? 'selected' : ''; ?>>Somalie</option>
                                <option value="Soudan" <?php echo ($data['pays'] ?? '') == 'Soudan' ? 'selected' : ''; ?>>Soudan</option>
                                <option value="Soudan du Sud" <?php echo ($data['pays'] ?? '') == 'Soudan du Sud' ? 'selected' : ''; ?>>Soudan du Sud</option>
                                <option value="Sri Lanka" <?php echo ($data['pays'] ?? '') == 'Sri Lanka' ? 'selected' : ''; ?>>Sri Lanka</option>
                                <option value="Suède" <?php echo ($data['pays'] ?? '') == 'Suède' ? 'selected' : ''; ?>>Suède</option>
                                <option value="Suisse" <?php echo ($data['pays'] ?? '') == 'Suisse' ? 'selected' : ''; ?>>Suisse</option>
                                <option value="Suriname" <?php echo ($data['pays'] ?? '') == 'Suriname' ? 'selected' : ''; ?>>Suriname</option>
                                <option value="Swaziland" <?php echo ($data['pays'] ?? '') == 'Swaziland' ? 'selected' : ''; ?>>Swaziland</option>
                                <option value="Syrie" <?php echo ($data['pays'] ?? '') == 'Syrie' ? 'selected' : ''; ?>>Syrie</option>
                                <option value="Tadjikistan" <?php echo ($data['pays'] ?? '') == 'Tadjikistan' ? 'selected' : ''; ?>>Tadjikistan</option>
                                <option value="Tanzanie" <?php echo ($data['pays'] ?? '') == 'Tanzanie' ? 'selected' : ''; ?>>Tanzanie</option>
                                <option value="Tchad" <?php echo ($data['pays'] ?? '') == 'Tchad' ? 'selected' : ''; ?>>Tchad</option>
                                <option value="Thaïlande" <?php echo ($data['pays'] ?? '') == 'Thaïlande' ? 'selected' : ''; ?>>Thaïlande</option>
                                <option value="Timor oriental" <?php echo ($data['pays'] ?? '') == 'Timor oriental' ? 'selected' : ''; ?>>Timor oriental</option>
                                <option value="Togo" <?php echo ($data['pays'] ?? '') == 'Togo' ? 'selected' : ''; ?>>Togo</option>
                                <option value="Tonga" <?php echo ($data['pays'] ?? '') == 'Tonga' ? 'selected' : ''; ?>>Tonga</option>
                                <option value="Trinité-et-Tobago" <?php echo ($data['pays'] ?? '') == 'Trinité-et-Tobago' ? 'selected' : ''; ?>>Trinité-et-Tobago</option>
                                <option value="Tunisie" <?php echo ($data['pays'] ?? '') == 'Tunisie' ? 'selected' : ''; ?>>Tunisie</option>
                                <option value="Turkménistan" <?php echo ($data['pays'] ?? '') == 'Turkménistan' ? 'selected' : ''; ?>>Turkménistan</option>
                                <option value="Turquie" <?php echo ($data['pays'] ?? '') == 'Turquie' ? 'selected' : ''; ?>>Turquie</option>
                                <option value="Tuvalu" <?php echo ($data['pays'] ?? '') == 'Tuvalu' ? 'selected' : ''; ?>>Tuvalu</option>
                                <option value="Ukraine" <?php echo ($data['pays'] ?? '') == 'Ukraine' ? 'selected' : ''; ?>>Ukraine</option>
                                <option value="Uruguay" <?php echo ($data['pays'] ?? '') == 'Uruguay' ? 'selected' : ''; ?>>Uruguay</option>
                                <option value="Vanuatu" <?php echo ($data['pays'] ?? '') == 'Vanuatu' ? 'selected' : ''; ?>>Vanuatu</option>
                                <option value="Vatican" <?php echo ($data['pays'] ?? '') == 'Vatican' ? 'selected' : ''; ?>>Vatican</option>
                                <option value="Venezuela" <?php echo ($data['pays'] ?? '') == 'Venezuela' ? 'selected' : ''; ?>>Venezuela</option>
                                <option value="Viêt Nam" <?php echo ($data['pays'] ?? '') == 'Viêt Nam' ? 'selected' : ''; ?>>Viêt Nam</option>
                                <option value="Yémen" <?php echo ($data['pays'] ?? '') == 'Yémen' ? 'selected' : ''; ?>>Yémen</option>
                                <option value="Zambie" <?php echo ($data['pays'] ?? '') == 'Zambie' ? 'selected' : ''; ?>>Zambie</option>
                                <option value="Zimbabwe" <?php echo ($data['pays'] ?? '') == 'Zimbabwe' ? 'selected' : ''; ?>>Zimbabwe</option>
                                <option value="Autre" <?php echo ($data['pays'] ?? '') == 'Autre' ? 'selected' : ''; ?>>Autre</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-venus-mars"></i> Genre
                            </label>
                            <div class="radio-group">
                                <label class="radio-label">
                                    <input type="radio" name="genre" value="Homme" <?php echo ($data['genre'] ?? '') == 'Homme' ? 'checked' : ''; ?>>
                                    <span>Homme</span>
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="genre" value="Femme" <?php echo ($data['genre'] ?? '') == 'Femme' ? 'checked' : ''; ?>>
                                    <span>Femme</span>
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="genre" value="Autre" <?php echo ($data['genre'] ?? '') == 'Autre' ? 'checked' : ''; ?>>
                                    <span>Autre</span>
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="genre" value="" <?php echo empty($data['genre'] ?? '') ? 'checked' : ''; ?>>
                                    <span>Préfère ne pas dire</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group terms-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="terms" id="terms" required>
                            <span>J'accepte les <a href="cgu.php" target="_blank" class="terms-link">conditions d'utilisation</a> et la <a href="privacy.php" target="_blank" class="privacy-link">politique de confidentialité</a> *</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        <i class="fas fa-user-plus"></i> Créer mon compte
                    </button>
                </form>

                <div class="inscription-footer">
                    <p>Déjà un compte ? <a href="views/login.php" class="login-link">Connectez-vous ici</a></p>
                    <p><a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour à l'accueil</a></p>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('inscriptionForm').addEventListener('submit', function(e) {
            const password = document.getElementById('mot_de_passe').value;
            const confirmPassword = document.getElementById('confirm_mot_de_passe').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas.');
                return false;
            }

            const terms = document.getElementById('terms');
            if (!terms.checked) {
                e.preventDefault();
                alert('Vous devez accepter les conditions d\'utilisation.');
                return false;
            }

            return true;
        });
    </script>
</body>

</html>