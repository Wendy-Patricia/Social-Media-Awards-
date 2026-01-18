<?php

namespace App\Services;

use PDO;
use App\Models\Edition;

/**
 * Service de gestion des éditions.
 */
class EditionService
{
    private PDO $pdo;

    /**
     * Constructeur du service.
     *
     * @param PDO $pdo Connexion à la base de données.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère toutes les éditions.
     *
     * @return Edition[] Liste des éditions sous forme d'objets Edition.
     */
    public function getAllEditions(): array
    {
        $sql = "
        SELECT 
            e.*,
            COALESCE(cat.nb_categories, 0) AS nb_categories,
            COALESCE(cand.nb_candidatures, 0) AS nb_candidatures,
            COALESCE(vot.nb_votants, 0) AS nb_votants,
            CASE 
                WHEN NOW() >= e.date_debut_candidatures AND NOW() <= e.date_fin THEN 1
                ELSE 0
            END AS est_active_calculated
        FROM edition e
        LEFT JOIN (SELECT id_edition, COUNT(*) AS nb_categories FROM categorie GROUP BY id_edition) cat 
            ON e.id_edition = cat.id_edition
        LEFT JOIN (SELECT c.id_edition, COUNT(*) AS nb_candidatures 
                   FROM candidature cand JOIN categorie c ON cand.id_categorie = c.id_categorie 
                   GROUP BY c.id_edition) cand 
            ON e.id_edition = cand.id_edition
        LEFT JOIN (SELECT c.id_edition, COUNT(DISTINCT ta.id_compte) AS nb_votants 
                   FROM token_anonyme ta 
                   JOIN categorie c ON ta.id_categorie = c.id_categorie
                   GROUP BY c.id_edition) vot 
            ON e.id_edition = vot.id_edition
        GROUP BY e.id_edition
        ORDER BY e.annee DESC
    ";

        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetchAll();

        $editions = [];
        foreach ($data as $row) {
            $row['est_active'] = $row['est_active_calculated'];  // Sobrescreve com cálculo atual
            unset($row['est_active_calculated']);  // Remove campo temporário
            $editions[] = new Edition($row);
        }
        return $editions;
    }

    public function getActiveEditions(): array
    {
        $now = date('Y-m-d H:i:s');
        $sql = "
            SELECT 
                e.*,
                COALESCE(cat.nb_categories, 0) AS nb_categories,
                COALESCE(cand.nb_candidatures, 0) AS nb_candidatures,
                COALESCE(vot.nb_votants, 0) AS nb_votants,
                1 AS est_active_calculated
            FROM edition e
            LEFT JOIN (SELECT id_edition, COUNT(*) AS nb_categories FROM categorie GROUP BY id_edition) cat 
                ON e.id_edition = cat.id_edition
            LEFT JOIN (SELECT c.id_edition, COUNT(*) AS nb_candidatures 
                       FROM candidature cand JOIN categorie c ON cand.id_categorie = c.id_categorie 
                       GROUP BY c.id_edition) cand 
                ON e.id_edition = cand.id_edition
            LEFT JOIN (SELECT c.id_edition, COUNT(DISTINCT ta.id_compte) AS nb_votants 
                       FROM token_anonyme ta 
                       JOIN categorie c ON ta.id_categorie = c.id_categorie
                       WHERE ta.est_utilise = 1
                       GROUP BY c.id_edition) vot 
                ON e.id_edition = vot.id_edition
            WHERE e.date_fin_candidatures >= :now
            ORDER BY e.annee DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':now' => $now]);
        $data = $stmt->fetchAll();

        $editions = [];
        foreach ($data as $row) {
            $editions[] = new Edition($row);
        }
        return $editions;
    }

    /**
     * Récupère une édition par ID.
     *
     * @param int $id ID de l'édition.
     * @return array|null Données de l'édition ou null si non trouvée.
     */
    public function getEditionById(int $id): ?array
    {
        $sql = "SELECT * FROM edition WHERE id_edition = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Crée une nouvelle édition.
     *
     * @param array $data Données de l'édition.
     * @param array|null $imageFile Image.
     * @return bool Succès de l'opération.
     */
    public function createEdition(array $data, ?array $imageFile = null): bool
    {
        $checkSql = "SELECT id_edition FROM edition WHERE annee = :annee";
        $checkStmt = $this->pdo->prepare($checkSql);
        $checkStmt->execute([':annee' => $data['annee']]);

        if ($checkStmt->fetch()) {
            throw new \Exception("Une édition avec l'année {$data['annee']} existe déjà.");
        }

        $imagePath = $this->uploadImage($imageFile);

        try {
            $edition = new Edition($data);

            // Calculer est_active automaticamente baseado na data_fin_candidatures
            $dateFinCandidatures = $edition->getDateFinCandidatures();
            $now = new \DateTime();
            $finCandidatures = new \DateTime($dateFinCandidatures);
            $estActive = ($now <= $finCandidatures) ? 1 : 0;

            $sql = "INSERT INTO edition 
                    (annee, nom, description, image, date_debut_candidatures, date_fin_candidatures, 
                     date_debut, date_fin, theme, est_active)
                    VALUES (:annee, :nom, :description, :image, :date_debut_candidatures, 
                            :date_fin_candidatures, :date_debut, :date_fin, :theme, :est_active)";

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':annee' => $edition->getAnnee(),
                ':nom' => $edition->getNom(),
                ':description' => $edition->getDescription(),
                ':image' => $imagePath,
                ':date_debut_candidatures' => $edition->getDateDebutCandidatures(),
                ':date_fin_candidatures' => $edition->getDateFinCandidatures(),
                ':date_debut' => $edition->getDateDebut(),
                ':date_fin' => $edition->getDateFin(),
                ':theme' => $edition->getTheme(),
                ':est_active' => $estActive
            ]);
        } catch (\InvalidArgumentException $e) {
            error_log("Validation échouée: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Met à jour une édition.
     *
     * @param int $id ID de l'édition.
     * @param array $data Nouvelles données.
     * @param array|null $imageFile Nouvelle image.
     * @return bool Succès de l'opération.
     */
    public function updateEdition(int $id, array $data, ?array $imageFile = null): bool
    {
        $editionData = $this->getEditionById($id);
        if (!$editionData) return false;

        $edition = new Edition($editionData);
        $imagePath = $edition->getImage();

        if ($imageFile && !empty($imageFile['name'])) {
            $newPath = $this->uploadImage($imageFile);
            if ($newPath && $imagePath && file_exists(__DIR__ . '/../../public/' . $imagePath)) {
                unlink(__DIR__ . '/../../public/' . $imagePath);
            }
            $imagePath = $newPath;
        }

        if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
            if ($imagePath && file_exists(__DIR__ . '/../../public/' . $imagePath)) {
                unlink(__DIR__ . '/../../public/' . $imagePath);
            }
            $imagePath = null;
        }

        try {
            $updatedEdition = new Edition($data);

            // Recalcular est_active baseado na nova data_fin_candidatures
            $dateFinCandidatures = $updatedEdition->getDateFinCandidatures();
            $now = new \DateTime();
            $finCandidatures = new \DateTime($dateFinCandidatures);
            $estActive = ($now <= $finCandidatures) ? 1 : 0;

            $sql = "UPDATE edition SET
                    annee = :annee,
                    nom = :nom,
                    description = :description,
                    image = :image,
                    date_debut_candidatures = :date_debut_candidatures,
                    date_fin_candidatures = :date_fin_candidatures,
                    date_debut = :date_debut,
                    date_fin = :date_fin,
                    theme = :theme,
                    est_active = :est_active
                    WHERE id_edition = :id";

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':annee' => $updatedEdition->getAnnee(),
                ':nom' => $updatedEdition->getNom(),
                ':description' => $updatedEdition->getDescription(),
                ':image' => $imagePath,
                ':date_debut_candidatures' => $updatedEdition->getDateDebutCandidatures(),
                ':date_fin_candidatures' => $updatedEdition->getDateFinCandidatures(),
                ':date_debut' => $updatedEdition->getDateDebut(),
                ':date_fin' => $updatedEdition->getDateFin(),
                ':theme' => $updatedEdition->getTheme(),
                ':est_active' => $estActive,
                ':id' => $id
            ]);
        } catch (\InvalidArgumentException $e) {
            error_log("Validation échouée: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime une édition.
     *
     * @param int $id ID de l'édition.
     * @return bool Succès de l'opération.
     */
    public function deleteEdition(int $id): bool
    {
        $editionData = $this->getEditionById($id);
        if ($editionData) {
            $edition = new Edition($editionData);
            if ($edition->getImage() && file_exists(__DIR__ . '/../../public/' . $edition->getImage())) {
                unlink(__DIR__ . '/../../public/' . $edition->getImage());
            }
        }

        $sql = "DELETE FROM edition WHERE id_edition = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Télécharge une image pour l'édition.
     *
     * @param array|null $file Fichier image.
     * @return string|null Chemin de l'image ou null si échec.
     */
    private function uploadImage(?array $file): ?string
    {
        if (!$file || empty($file['name']) || $file['error'] !== 0) return null;

        $uploadDir = __DIR__ . '/../../public/uploads/editions/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $allowed = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024;

        if ($file['size'] > $maxSize || !in_array($file['type'], $allowed)) return null;

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('edi_') . '.' . strtolower($ext);
        $dest = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return 'uploads/editions/' . $filename;
        }
        return null;
    }

    /**
     * Récupère l'édition active actuelle.
     *
     * @return array|null Données de l'édition active ou null.
     */
    public function getActiveEdition(): ?Edition
    {
        $now = date('Y-m-d H:i:s');
        $sql = "
            SELECT e.*,
                   COALESCE((SELECT COUNT(*) FROM categorie WHERE id_edition = e.id_edition), 0) AS nb_categories
            FROM edition e 
            WHERE ? >= e.date_debut_candidatures AND ? <= e.date_fin
            ORDER BY e.annee DESC
            LIMIT 1
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$now, $now]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? new Edition($data) : null;
    }


    public function updateAllEditionStatus(): bool
    {
        try {
            $sql = "UPDATE edition 
                SET est_active = CASE 
                    WHEN NOW() >= date_debut_candidatures AND NOW() <= date_fin THEN 1 
                    ELSE 0 
                END";

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Erro ao atualizar status das edições: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Vérifie si une édition est active.
     *
     * @param int $editionId ID de l'édition.
     * @return bool True si active, false sinon.
     */
    public function isEditionActive(int $editionId): bool
    {
        $now = date('Y-m-d H:i:s');
        $sql = "
            SELECT COUNT(*) as count 
            FROM edition 
            WHERE id_edition = ? 
            AND ? >= date_debut_candidatures 
            AND ? <= date_fin
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$editionId, $now, $now]);
        return $stmt->fetchColumn() > 0;
    }
}
