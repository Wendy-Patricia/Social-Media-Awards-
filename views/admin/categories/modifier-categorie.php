<?php
require_once __DIR__ . '/../../../config/session.php';
require_once __DIR__ . '/../../../config/permissions.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: gerer-categories.php");
    exit;
}

require_once __DIR__ . '/../../../app/Controllers/AdminController.php';

$controller = new App\Controllers\AdminController();
$category = $controller->getCategoryById($id);
$editions = $controller->getEditionsList();

if (!$category) {
    header("Location: gerer-categories.php");
    exit;
}

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
        if ($controller->updateCategory($id, $data, $image)) {
            header("Location: gerer-categories.php?success=1");
            exit;
        } else {
            $error = "Erreur lors de la mise à jour.";
        }
    }
}

require_once __DIR__ . '/../../../views/partials/admin-header.php';
?>

<h1>Modifier la catégorie : <?= htmlspecialchars($category['nom']) ?></h1>

<?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <label>Nom * <input type="text" name="nom" required value="<?= htmlspecialchars($category['nom']) ?>"></label><br><br>
    <label>Description * <textarea name="description" rows="5" required><?= htmlspecialchars($category['description']) ?></textarea></label><br><br>

    <label>Plateforme cible
        <select name="plateforme_cible">
            <?php $options = ['Toutes', 'TikTok', 'Instagram', 'YouTube']; ?>
            <?php foreach ($options as $opt): ?>
                <option value="<?= $opt ?>" <?= $category['plateforme_cible'] === $opt ? 'selected' : '' ?>><?= $opt ?></option>
            <?php endforeach; ?>
        </select>
    </label><br><br>

    <label>Édition *
        <select name="id_edition" required>
            <?php foreach ($editions as $e): ?>
                <option value="<?= $e['id_edition'] ?>" <?= $category['id_edition'] == $e['id_edition'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($e['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label><br><br>

    <label>Limite de nominés <input type="number" name="limite_nomines" min="1" max="50" value="<?= $category['limite_nomines'] ?>"></label><br><br>

    <label>Date début votes <input type="datetime-local" name="date_debut_votes" value="<?= str_replace(' ', 'T', $category['date_debut_votes'] ?? '') ?>"></label><br><br>
    <label>Date fin votes <input type="datetime-local" name="date_fin_votes" value="<?= str_replace(' ', 'T', $category['date_fin_votes'] ?? '') ?>"></label><br><br>

    <label>Nouvelle image (optionnel) <input type="file" name="image" accept="image/*"></label><br>
    <?php if ($category['image']): ?>
        <p>Image actuelle : <img src="../../../public/<?= htmlspecialchars($category['image']) ?>" width="200"></p>
    <?php endif; ?><br>

    <button type="submit" class="btn btn-primary">Sauvegarder</button>
    <a href="gerer-categories.php" class="btn btn-secondary">Annuler</a>
</form>

<?php require_once __DIR__ . '/../../../views/partials/admin-footer.php'; ?>