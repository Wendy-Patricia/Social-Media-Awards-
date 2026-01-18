<?php
namespace App\Services;

use PDO;
use PDOException;
use App\Models\Nomination;
require_once __DIR__ . '/../Models/Nomination.php';
/**
 * Service de gestion des nominations.
 */
class NominationService
{
    private $pdo;
    
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
     * Récupère toutes les nominations.
     *
     * @return Nomination[] Liste des nominations sous forme d'objets Nomination.
     */
    public function getAllNominations(): array
    {
        try {
            $sql = "SELECT n.*, c.nom as categorie_nom 
                    FROM nomination n
                    JOIN categorie c ON n.id_categorie = c.id_categorie
                    ORDER BY n.libelle ASC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $nominations = [];
            foreach ($data as $row) {
                $nominations[] = new Nomination($row);
            }
            return $nominations;
            
        } catch (PDOException $e) {
            error_log("Erreur récupération nominations: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupère les nominations par catégorie.
     *
     * @param int $categoryId ID de la catégorie.
     * @return Nomination[] Liste des nominations sous forme d'objets Nomination.
     */
    public function getNominationsByCategory(int $categoryId): array
    {
        try {
            $sql = "SELECT n.*, c.nom as categorie_nom 
                    FROM nomination n
                    JOIN categorie c ON n.id_categorie = c.id_categorie
                    WHERE n.id_categorie = :category_id
                    ORDER BY n.libelle ASC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':category_id' => $categoryId]);
            
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $nominations = [];
            foreach ($data as $row) {
                $nominations[] = new Nomination($row);
            }
            return $nominations;
            
        } catch (PDOException $e) {
            error_log("Erreur récupération nominations par catégorie: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Compte les votes pour une nomination.
     *
     * @param int $nominationId ID de la nomination.
     * @return int Nombre de votes.
     */
    public function countVotesForNomination(int $nominationId): int
    {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM vote 
                    WHERE id_nomination = :nomination_id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':nomination_id' => $nominationId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['total'] ?? 0;
            
        } catch (PDOException $e) {
            error_log("Erreur comptage votes: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Récupère toutes les catégories.
     *
     * @return array Liste des catégories.
     */
    public function getAllCategories(): array
    {
        try {
            $sql = "SELECT id_categorie, nom 
                    FROM categorie 
                    ORDER BY nom ASC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur récupération catégories: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupère une catégorie par ID.
     *
     * @param int $categoryId ID de la catégorie.
     * @return array|null Données de la catégorie ou null si non trouvée.
     */
    public function getCategoryById(int $categoryId): ?array
    {
        try {
            $sql = "SELECT * FROM categorie WHERE id_categorie = :category_id LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':category_id' => $categoryId]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            
        } catch (PDOException $e) {
            error_log("Erreur récupération catégorie: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Récupère toutes les plateformes uniques.
     *
     * @return array Liste des plateformes.
     */
    public function getAllPlatforms(): array
    {
        try {
            $sql = "SELECT DISTINCT plateforme 
                    FROM nomination 
                    WHERE plateforme IS NOT NULL 
                    AND plateforme != ''
                    UNION
                    SELECT DISTINCT plateforme_cible 
                    FROM categorie 
                    WHERE plateforme_cible IS NOT NULL 
                    AND plateforme_cible != ''
                    ORDER BY 1";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return array_filter($results);
            
        } catch (PDOException $e) {
            error_log("Erreur récupération plateformes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Crée une nomination à partir d'une candidature.
     *
     * @param int $candidatureId ID de la candidature.
     * @param int $adminId ID de l'administrateur.
     * @return bool Succès de l'opération.
     */
    public function createFromCandidature(int $candidatureId, int $adminId): bool
    {
        try {
            // Récupérer les données de la candidature
            $sqlCandidature = "SELECT * FROM candidature WHERE id_candidature = :id";
            $stmt = $this->pdo->prepare($sqlCandidature);
            $stmt->execute([':id' => $candidatureId]);
            $candidature = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$candidature) {
                return false;
            }

            // Insérer dans nomination
            $sql = "INSERT INTO nomination 
                    (libelle, plateforme, url_contenu, url_image, argumentaire, date_approbation, 
                     id_candidature, id_categorie, id_compte, id_admin)
                    VALUES (:libelle, :plateforme, :url_contenu, :url_image, :argumentaire, NOW(), 
                            :id_candidature, :id_categorie, :id_compte, :id_admin)";

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':libelle' => $candidature['libelle'],
                ':plateforme' => $candidature['plateforme'],
                ':url_contenu' => $candidature['url_contenu'],
                ':url_image' => $candidature['image'] ?? null,
                ':argumentaire' => $candidature['argumentaire'],
                ':id_candidature' => $candidatureId,
                ':id_categorie' => $candidature['id_categorie'],
                ':id_compte' => $candidature['id_compte'],
                ':id_admin' => $adminId
            ]);
        } catch (PDOException $e) {
            error_log("Erreur création nomination à partir de candidature: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère une nomination par ID.
     *
     * @param int $id ID de la nomination.
     * @return Nomination|null Objet Nomination ou null si non trouvée.
     */
    public function getNominationById(int $id): ?Nomination
    {
        try {
            $sql = "SELECT n.*, c.nom as categorie_nom 
                    FROM nomination n
                    JOIN categorie c ON n.id_categorie = c.id_categorie
                    WHERE n.id_nomination = :id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? new Nomination($data) : null;
        } catch (PDOException $e) {
            error_log("Erreur récupération nomination: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Met à jour une nomination.
     *
     * @param int $id ID de la nomination.
     * @param array $data Données à mettre à jour.
     * @param array|null $imageFile Nouvelle image (optionnel).
     * @param int $adminId ID de l'administrateur.
     * @return bool Succès de l'opération.
     */
    public function updateNomination(int $adminId, int $id, array $data, ?array $imageFile = null): bool
    {
        $nomination = $this->getNominationById($id);
        if (!$nomination) {
            return false;
        }

        $imagePath = $nomination->getUrlImage();

        if ($imageFile && !empty($imageFile['name'])) {
            $newPath = $this->uploadImage($imageFile);
            if ($newPath && $imagePath && file_exists(__DIR__ . '/../../public/' . $imagePath)) {
                unlink(__DIR__ . '/../../public/' . $imagePath);
            }
            $imagePath = $newPath;
        }

        if (isset($data['remove_image']) && $data['remove_image'] == '1') {
            if ($imagePath && file_exists(__DIR__ . '/../../public/' . $imagePath)) {
                unlink(__DIR__ . '/../../public/' . $imagePath);
            }
            $imagePath = null;
        }

        try {
            $updatedNomination = new Nomination($data);

            $sql = "UPDATE nomination SET
                    libelle = :libelle,
                    plateforme = :plateforme,
                    url_contenu = :url_contenu,
                    url_image = :url_image,
                    argumentaire = :argumentaire,
                    id_admin = :id_admin
                    WHERE id_nomination = :id";

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':libelle' => $updatedNomination->getLibelle(),
                ':plateforme' => $updatedNomination->getPlateforme(),
                ':url_contenu' => $updatedNomination->getUrlContenu(),
                ':url_image' => $imagePath,
                ':argumentaire' => $updatedNomination->getArgumentaire(),
                ':id_admin' => $adminId,
                ':id' => $id
            ]);
        } catch (\InvalidArgumentException $e) {
            error_log("Validation échouée lors de la mise à jour: " . $e->getMessage());
            return false;
        } catch (PDOException $e) {
            error_log("Erreur mise à jour nomination: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime une nomination.
     *
     * @param int $id ID de la nomination.
     * @return bool Succès de l'opération.
     */
    public function deleteNomination(int $id): bool
    {
        $nomination = $this->getNominationById($id);
        if ($nomination && $nomination->getUrlImage() && file_exists(__DIR__ . '/../../public/' . $nomination->getUrlImage())) {
            unlink(__DIR__ . '/../../public/' . $nomination->getUrlImage());
        }

        try {
            $sql = "DELETE FROM nomination WHERE id_nomination = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Erreur suppression nomination: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Télécharge une image pour la nomination.
     *
     * @param array|null $file Fichier image.
     * @return string|null Chemin de l'image ou null si échec.
     */
    private function uploadImage(?array $file): ?string
    {
        if (!$file || empty($file['name']) || $file['error'] !== 0) return null;

        $uploadDir = __DIR__ . '/../../public/uploads/nominations/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $allowed = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024;

        if ($file['size'] > $maxSize || !in_array($file['type'], $allowed)) return null;

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('nom_') . '.' . strtolower($ext);
        $dest = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return 'uploads/nominations/' . $filename;
        }
        return null;
    }
}