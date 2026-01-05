<?php
require_once __DIR__ . '/../../../config/session.php';
require_once __DIR__ . '/../../../config/permissions.php';
requireAdmin();

require_once __DIR__ . '/../../../app/Controllers/AdminController.php';

$controller = new App\Controllers\AdminController();
$editions = $controller->getEditionsList();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nom' => trim($_POST['nom'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'plateforme_cible' => $_POST['plateforme_cible'] ?? 'Toutes',
        'date_debut_votes' => $_POST['date_debut_votes'] ?? null,
        'date_fin_votes' => $_POST['date_fin_votes'] ?? null,
        'id_edition' => (int)($_POST['id_edition'] ?? 0),
        'limite_nomines' => (int)($_POST['limite_nomines'] ?? 10)
    ];

    if (empty($data['nom']) || $data['id_edition'] <= 0) {
        $error = "Champs obligatoires manquants.";
    } else {
        $image = $_FILES['image'] ?? null;
        if ($controller->createCategory($data, $image)) {
            header("Location: gerer-categories.php?success=1");
            exit;
        } else {
            $error = "Erreur lors de la création.";
        }
    }
}

require_once __DIR__ . '/../../../views/partials/admin-header.php';
?>

<h1>Créer une nouvelle catégorie</h1>

<?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <label>Nom * <input type="text" name="nom" required value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"></label><br><br>

    <label>Description * <textarea name="description" rows="5" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea></label><br><br>

    <label>Plateforme cible
        <select name="plateforme_cible">
            <option value="Toutes">Toutes</option>
            <option value="TikTok">TikTok</option>
            <option value="Instagram">Instagram</option>
            <option value="YouTube">YouTube</option>
        </select>
    </label><br><br>

    <label>Édition *
        <select name="id_edition" required>
            <option value="">Choisir...</option>
            <?php foreach ($editions as $e): ?>
                <option value="<?= $e['id_edition'] ?>"><?= htmlspecialchars($e['nom']) ?></option>
            <?php endforeach; ?>
        </select>
    </label><br><br>

    <label>Limite de nominés <input type="number" name="limite_nomines" min="1" max="50" value="10"></label><br><br>

    <label>Date début votes <input type="datetime-local" name="date_debut_votes"></label><br><br>
    <label>Date fin votes <input type="datetime-local" name="date_fin_votes"></label><br><br>

    <label>Image <input type="file" name="image" accept="image/*"></label><br><br>

    <button type="submit" class="btn btn-primary">Créer la catégorie</button>
    <a href="gerer-categories.php" class="btn btn-secondary">Annuler</a>
</form>

<?php require_once __DIR__ . '/../../../views/partials/admin-footer.php'; ?>