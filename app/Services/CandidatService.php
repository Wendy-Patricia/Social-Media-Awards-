<?php
// app/Services/CandidatService.php

namespace App\Services;

use PDO;
use PDOException;
use App\Models\Candidature;
use App\Models\Categorie;
use App\Models\Edition;

/**
 * Service de gestion des candidats.
 */
class CandidatService
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
     * Obtient les données du candidat par ID.
     *
     * @param int $userId ID du candidat.
     * @return array|null Données du candidat ou null si non trouvé.
     */
    public function getCandidatById(int $userId): ?array
    {
        $sql = "SELECT 
                ca.id_compte,
                ca.nom_legal_ou_societe,
                ca.type_candidature,
                ca.est_nomine,
                co.pseudonyme,
                co.email,
                co.date_naissance,
                co.pays,
                co.genre,
                co.photo_profil,
                co.date_creation
                FROM candidat ca
                JOIN compte co ON ca.id_compte = co.id_compte
                WHERE ca.id_compte = :userId";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':userId' => $userId]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    /**
     * Met à jour les données du candidat.
     *
     * @param int $userId ID du candidat.
     * @param array $data Données à mettre à jour.
     * @return bool Succès de l'opération.
     */
    public function updateCandidat(int $userId, array $data): bool
    {
        // Mise à jour de la table candidat
        $sqlCandidat = "UPDATE candidat SET
            nom_legal_ou_societe = :nom_legal_ou_societe,
            type_candidature = :type_candidature
            WHERE id_compte = :userId";

        $stmtCandidat = $this->pdo->prepare($sqlCandidat);

        $successCandidat = $stmtCandidat->execute([
            ':nom_legal_ou_societe' => $data['nom_legal_ou_societe'] ?? null,
            ':type_candidature' => $data['type_candidature'] ?? 'Créateur',
            ':userId' => $userId
        ]);

        // Mise à jour de la table compte - SEULEMENT LES CHAMPS EXISTANTS
        $sqlCompte = "UPDATE compte SET
            pseudonyme = :pseudonyme,
            email = :email,
            photo_profil = :photo_profil,
            pays = :pays,
            genre = :genre,
            date_modification = NOW()
            WHERE id_compte = :userId";

        $stmtCompte = $this->pdo->prepare($sqlCompte);

        $successCompte = $stmtCompte->execute([
            ':pseudonyme' => $data['pseudonyme'],
            ':email' => $data['email'],
            ':photo_profil' => $data['photo_profil'] ?? null,
            ':pays' => $data['pays'] ?? null,
            ':genre' => $data['genre'] ?? null,
            ':userId' => $userId
        ]);

        return $successCandidat && $successCompte;
    }

    /**
     * Vérifie si l'utilisateur est un nominé (a des candidatures approuvées).
     *
     * @param int $userId ID du candidat.
     * @return bool True si nominé, false sinon.
     */
    public function isNominee(int $userId): bool
    {
        $sql = "SELECT COUNT(*) as count 
                FROM candidature 
                WHERE id_compte = :userId 
                AND statut = 'Approuvée'";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':userId' => $userId]);
        $result = $stmt->fetch();

        return $result['count'] > 0;
    }

    /**
     * Obtient toutes les nominations actives du utilisateur.
     *
     * @param int $userId ID du candidat.
     * @return array Liste des nominations.
     */
    public function getActiveNominations(int $userId): array
    {
        $sql = "SELECT n.*, cat.nom as categorie_nom, edi.nom as edition_nom,
                       cat.date_debut_votes, cat.date_fin_votes,
                       edi.date_debut, edi.date_fin
                FROM nomination n
                JOIN categorie cat ON n.id_categorie = cat.id_categorie
                JOIN edition edi ON cat.id_edition = edi.id_edition
                WHERE n.id_compte = :userId
                ORDER BY cat.date_fin_votes ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':userId' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtient le statut de vote pour une nomination.
     *
     * @param array $nomination Données de la nomination.
     * @return string Statut de vote ('in_progress', 'ended', 'not_started').
     */
    public function getVotingStatus(array $nomination): string
    {
        $now = new \DateTime();

        // Vérifier d'abord les dates de vote spécifiques de la catégorie
        if (isset($nomination['date_debut_votes']) && isset($nomination['date_fin_votes'])) {
            $startVotes = new \DateTime($nomination['date_debut_votes']);
            $endVotes = new \DateTime($nomination['date_fin_votes']);

            if ($now >= $startVotes && $now <= $endVotes) {
                return 'in_progress';
            } elseif ($now > $endVotes) {
                return 'ended';
            } else {
                return 'not_started';
            }
        }

        // Fallback: utiliser les dates de l'édition
        if (isset($nomination['date_debut']) && isset($nomination['date_fin'])) {
            $startEdition = new \DateTime($nomination['date_debut']);
            $endEdition = new \DateTime($nomination['date_fin']);

            if ($now >= $startEdition && $now <= $endEdition) {
                return 'in_progress';
            } elseif ($now > $endEdition) {
                return 'ended';
            } else {
                return 'not_started';
            }
        }

        return 'not_started';
    }

    /**
     * Obtient les statistiques du candidat.
     *
     * @param int $userId ID du candidat.
     * @return array Statistiques du candidat.
     */
    public function getCandidatStats(int $userId): array
    {
        $sql = "SELECT 
                COUNT(CASE WHEN statut = 'En attente' THEN 1 END) as pending,
                COUNT(CASE WHEN statut = 'Approuvée' THEN 1 END) as approved,
                COUNT(CASE WHEN statut = 'Rejetée' THEN 1 END) as rejected,
                COUNT(*) as total
                FROM candidature 
                WHERE id_compte = :userId";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':userId' => $userId]);
        $result = $stmt->fetch();

        return $result ?: [
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0,
            'total' => 0
        ];
    }

    /**
     * Obtient toutes les candidatures de l'utilisateur.
     *
     * @param int $userId ID du candidat.
     * @return array Liste des candidatures.
     */
    public function getUserCandidatures(int $userId): array
    {
        $sql = "SELECT c.*, cat.nom as categorie_nom, ed.nom as edition_nom
                FROM candidature c
                JOIN categorie cat ON c.id_categorie = cat.id_categorie
                JOIN edition ed ON cat.id_edition = ed.id_edition
                WHERE c.id_compte = :userId
                ORDER BY c.date_soumission DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':userId' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtient les catégories disponibles pour candidature.
     *
     * @param int $userId ID du candidat.
     * @return Categorie[] Liste des catégories disponibles sous forme d'objets Categorie.
     */
    public function getAvailableCategoriesForCandidature(int $userId): array
    {
        // 1. Obtenir les éditions actives
        $sqlEditions = "SELECT id_edition 
                    FROM edition 
                    WHERE est_active = 1 
                    AND date_fin_candidatures >= NOW()";

        $stmtEditions = $this->pdo->query($sqlEditions);
        $activeEditionIds = $stmtEditions->fetchAll(PDO::FETCH_COLUMN);

        if (empty($activeEditionIds)) {
            return [];
        }

        $editionIdsString = implode(',', $activeEditionIds);

        // 2. Obtenir les catégories de ces éditions
        $sqlCategories = "SELECT c.*, e.nom as edition_nom, e.date_fin_candidatures
                      FROM categorie c
                      JOIN edition e ON c.id_edition = e.id_edition
                      WHERE c.id_edition IN ($editionIdsString)
                      AND e.date_fin_candidatures >= NOW()
                      ORDER BY e.annee DESC, c.nom ASC";

        $stmtCategories = $this->pdo->prepare($sqlCategories);
        $stmtCategories->execute();
        $allCategoriesData = $stmtCategories->fetchAll();

        $allCategories = [];
        foreach ($allCategoriesData as $data) {
            $allCategories[] = new Categorie($data);
        }

        // 3. Obtenir les catégories où l'utilisateur s'est déjà candidaté
        $sqlUserCandidatures = "SELECT id_categorie 
                            FROM candidature 
                            WHERE id_compte = :userId 
                            AND statut != 'Rejetée'";

        $stmtUser = $this->pdo->prepare($sqlUserCandidatures);
        $stmtUser->execute([':userId' => $userId]);
        $userCategoryIds = $stmtUser->fetchAll(PDO::FETCH_COLUMN);

        // 4. Filtrer les catégories disponibles
        return array_filter($allCategories, function ($category) use ($userCategoryIds) {
            return !in_array($category->getIdCategorie(), $userCategoryIds);
        });
    }

    /**
     * Compte les catégories disponibles.
     *
     * @param int $userId ID du candidat.
     * @return int Nombre de catégories disponibles.
     */
    public function countAvailableCategories(int $userId): int
    {
        $availableCategories = $this->getAvailableCategoriesForCandidature($userId);
        return count($availableCategories);
    }

    /**
     * Vérifie si le candidat a déjà une candidature dans la catégorie pour la même plateforme.
     *
     * @param int $userId ID du candidat.
     * @param int $categoryId ID de la catégorie.
     * @param string $platform Plateforme.
     * @return bool True si déjà candidaté, false sinon.
     */
    public function hasCandidatureInCategoryForPlatform(int $userId, int $categoryId, string $platform): bool
    {
        $sql = "SELECT COUNT(*) as count 
            FROM candidature 
            WHERE id_compte = :userId 
            AND id_categorie = :categoryId
            AND plateforme = :platform
            AND statut != 'Rejetée'";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':userId' => $userId,
            ':categoryId' => $categoryId,
            ':platform' => $platform
        ]);
        $result = $stmt->fetch();

        return $result['count'] > 0;
    }

    /**
     * Obtient toutes les candidatures du utilisateur dans une catégorie.
     *
     * @param int $userId ID du candidat.
     * @param int $categoryId ID de la catégorie.
     * @return Candidature[] Liste des candidatures sous forme d'objets Candidature.
     */
    public function getCandidaturesInCategory(int $userId, int $categoryId): array
    {
        $sql = "SELECT * 
            FROM candidature 
            WHERE id_compte = :userId 
            AND id_categorie = :categoryId
            AND statut != 'Rejetée'
            ORDER BY date_soumission DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':userId' => $userId,
            ':categoryId' => $categoryId
        ]);
        $data = $stmt->fetchAll();

        $candidatures = [];
        foreach ($data as $row) {
            $candidatures[] = new Candidature($row);
        }
        return $candidatures;
    }

    /**
     * Obtient une candidature spécifique par ID avec vérification de propriétaire.
     *
     * @param int $candidatureId ID de la candidature.
     * @param int $userId ID du candidat.
     * @return Candidature|null Objet Candidature ou null si non trouvé.
     */
    public function getCandidature(int $candidatureId, int $userId): ?Candidature
    {
        $sql = "SELECT c.*, cat.nom as categorie_nom, ed.nom as edition_nom
                FROM candidature c
                JOIN categorie cat ON c.id_categorie = cat.id_categorie
                JOIN edition ed ON cat.id_edition = ed.id_edition
                WHERE c.id_candidature = :id
                AND c.id_compte = :userId";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $candidatureId,
            ':userId' => $userId
        ]);

        $data = $stmt->fetch();

        return $data ? new Candidature($data) : null;
    }

    /**
     * Crée une nouvelle candidature.
     *
     * @param array $data Données de la candidature.
     * @param int $userId ID du candidat.
     * @return bool Succès de l'opération.
     */
    public function createCandidature(array $data, int $userId): bool
    {
        try {
            // Vérifier s'il existe déjà une candidature pour cette catégorie et plateforme
            $checkSql = "SELECT COUNT(*) as count 
                         FROM candidature 
                         WHERE id_compte = :userId 
                         AND id_categorie = :categoryId
                         AND plateforme = :platform
                         AND statut != 'Rejetée'";

            $checkStmt = $this->pdo->prepare($checkSql);
            $checkStmt->execute([
                ':userId' => $userId,
                ':categoryId' => $data['id_categorie'],
                ':platform' => $data['plateforme']
            ]);

            $checkResult = $checkStmt->fetch();

            if ($checkResult['count'] > 0) {
                return false; // Candidature déjà existante
            }

            // Insérer la nouvelle candidature
            $insertSql = "INSERT INTO candidature (
                libelle, 
                plateforme, 
                url_contenu, 
                argumentaire, 
                image,
                id_categorie,
                id_compte,
                date_soumission,
                statut
            ) VALUES (
                :libelle,
                :plateforme,
                :url_contenu,
                :argumentaire,
                :image,
                :id_categorie,
                :id_compte,
                NOW(),
                'En attente'
            )";

            $insertStmt = $this->pdo->prepare($insertSql);

            return $insertStmt->execute([
                ':libelle' => $data['libelle'],
                ':plateforme' => $data['plateforme'],
                ':url_contenu' => $data['url_contenu'],
                ':argumentaire' => $data['argumentaire'],
                ':image' => $data['image'] ?? null,
                ':id_categorie' => $data['id_categorie'],
                ':id_compte' => $userId
            ]);
        } catch (PDOException $e) {
            error_log("Erreur création candidature: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Met à jour une candidature existante.
     *
     * @param int $candidatureId ID de la candidature.
     * @param array $data Nouvelles données.
     * @param int $userId ID du candidat.
     * @return bool Succès de l'opération.
     */
    public function updateCandidature(int $id, array $data, int $userId): bool
    {
        try {
            // Buscar a candidatura atual para manter imagem antiga se não for enviada nova
            $current = $this->getCandidature($id, $userId);
            if (!$current) {
                error_log("Candidature $id não encontrada ou não pertence ao user $userId");
                return false;
            }

            // Preparar imagem: manter antiga se não houver nova
            $image = $data['image'] ?? $current->getImage();

            $sql = "UPDATE candidature SET 
                    libelle = :libelle,
                    plateforme = :plateforme,
                    url_contenu = :url_contenu,
                    argumentaire = :argumentaire,
                    id_categorie = :id_categorie,
                    image = :image
                WHERE id_candidature = :id 
                  AND id_compte = :user_id";

            $stmt = $this->pdo->prepare($sql);

            $success = $stmt->execute([
                ':libelle'       => $data['libelle'],
                ':plateforme'    => $data['plateforme'],
                ':url_contenu'   => $data['url_contenu'],
                ':argumentaire'  => $data['argumentaire'],
                ':id_categorie'  => (int)$data['id_categorie'],
                ':image'         => $image,
                ':id'            => $id,
                ':user_id'       => $userId
            ]);

            if (!$success) {
                error_log("Update failed for candidature $id: " . print_r($stmt->errorInfo(), true));
            }

            return $success;
        } catch (PDOException $e) {
            error_log("PDO error in updateCandidature: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime une candidature.
     *
     * @param int $candidatureId ID de la candidature.
     * @param int $userId ID du candidat.
     * @return bool Succès de l'opération.
     */
    public function deleteCandidature(int $candidatureId, int $userId): bool
    {
        try {
            // Vérifier que la candidature peut être supprimée
            $checkSql = "SELECT statut 
                         FROM candidature 
                         WHERE id_candidature = :candidatureId 
                         AND id_compte = :userId";

            $checkStmt = $this->pdo->prepare($checkSql);
            $checkStmt->execute([
                ':candidatureId' => $candidatureId,
                ':userId' => $userId
            ]);

            $checkResult = $checkStmt->fetch();

            if (!$checkResult) {
                return false; // Candidature non trouvée
            }

            // Ne peut supprimer que les candidatures en attente
            if ($checkResult['statut'] != 'En attente') {
                return false;
            }

            // Supprimer l'image associée si elle existe
            if (!empty($checkResult['image'])) {
                $imagePath = __DIR__ . '/../../public/' . $checkResult['image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            // Supprimer la candidature
            $deleteSql = "DELETE FROM candidature 
                          WHERE id_candidature = :candidatureId 
                          AND id_compte = :userId";

            $deleteStmt = $this->pdo->prepare($deleteSql);

            return $deleteStmt->execute([
                ':candidatureId' => $candidatureId,
                ':userId' => $userId
            ]);
        } catch (PDOException $e) {
            error_log("Erreur suppression candidature: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Upload une image pour une candidature.
     *
     * @param array $fileData Données du fichier.
     * @return string|null Chemin du fichier ou null en cas d'erreur.
     */
    public function uploadImage(array $fileData): ?string
    {
        if ($fileData['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        // Vérifier le type
        $fileType = mime_content_type($fileData['tmp_name']);
        if (!in_array($fileType, $allowedTypes)) {
            return null;
        }

        // Vérifier la taille
        if ($fileData['size'] > $maxSize) {
            return null;
        }

        // Créer le répertoire s'il n'existe pas
        $uploadDir = __DIR__ . '/../../public/uploads/candidatures/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Générer un nom de fichier unique
        $ext = pathinfo($fileData['name'], PATHINFO_EXTENSION);
        $filename = uniqid('cand_', true) . '.' . strtolower($ext);
        $dest = $uploadDir . $filename;

        // Déplacer le fichier
        if (move_uploaded_file($fileData['tmp_name'], $dest)) {
            return 'uploads/candidatures/' . $filename;
        }

        return null;
    }

    /**
     * Upload une photo de profil.
     *
     * @param array $fileData Données du fichier.
     * @param int $userId ID de l'utilisateur.
     * @return string|null Chemin du fichier ou null en cas d'erreur.
     */
    public function uploadProfilePhoto(array $fileData, int $userId): ?string
    {
        if ($fileData['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        // Vérifier le type
        $fileType = mime_content_type($fileData['tmp_name']);
        if (!in_array($fileType, $allowedTypes)) {
            return null;
        }

        // Vérifier la taille
        if ($fileData['size'] > $maxSize) {
            return null;
        }

        // Créer le répertoire s'il n'existe pas
        $uploadDir = __DIR__ . '/../../public/uploads/profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Supprimer l'ancienne photo si elle existe
        $oldPhotoSql = "SELECT photo_profil FROM compte WHERE id_compte = :userId";
        $oldStmt = $this->pdo->prepare($oldPhotoSql);
        $oldStmt->execute([':userId' => $userId]);
        $oldPhoto = $oldStmt->fetchColumn();

        if ($oldPhoto && file_exists(__DIR__ . '/../../public/' . $oldPhoto)) {
            unlink(__DIR__ . '/../../public/' . $oldPhoto);
        }

        // Générer un nom de fichier unique
        $ext = pathinfo($fileData['name'], PATHINFO_EXTENSION);
        $filename = 'profile_' . $userId . '_' . time() . '.' . strtolower($ext);
        $dest = $uploadDir . $filename;

        // Déplacer le fichier
        if (move_uploaded_file($fileData['tmp_name'], $dest)) {
            return 'uploads/profiles/' . $filename;
        }

        return null;
    }

    /**
     * Vérifie si le candidat peut modifier son profil.
     *
     * @param int $userId ID du candidat.
     * @return bool True si peut modifier, false sinon.
     */
    public function canEditProfile(int $userId): bool
    {
        // Vérifier si le candidat est nominé
        $isNominee = $this->isNominee($userId);

        if (!$isNominee) {
            return true; // Les non-nominés peuvent toujours modifier
        }

        // Pour les nominés, vérifier s'il y a des votes en cours
        $nominations = $this->getActiveNominations($userId);

        foreach ($nominations as $nomination) {
            $status = $this->getVotingStatus($nomination);
            if ($status == 'in_progress') {
                return false; // Ne peut pas modifier pendant les votes
            }
        }

        return true;
    }

    /**
     * Vérifie si un pseudonyme est disponible.
     *
     * @param string $pseudonyme Pseudonyme à vérifier.
     * @param int $userId ID de l'utilisateur à exclure.
     * @return bool True si disponible, false sinon.
     */
    public function isPseudonymeAvailable(string $pseudonyme, int $userId): bool
    {
        $sql = "SELECT COUNT(*) as count 
                FROM compte 
                WHERE pseudonyme = :pseudonyme 
                AND id_compte != :userId";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':pseudonyme' => $pseudonyme,
            ':userId' => $userId
        ]);

        $result = $stmt->fetch();

        return $result['count'] == 0;
    }

    /**
     * Vérifie si un email est disponible.
     *
     * @param string $email Email à vérifier.
     * @param int $userId ID de l'utilisateur à exclure.
     * @return bool True si disponible, false sinon.
     */
    public function isEmailAvailable(string $email, int $userId): bool
    {
        $sql = "SELECT COUNT(*) as count 
                FROM compte 
                WHERE email = :email 
                AND id_compte != :userId";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':email' => $email,
            ':userId' => $userId
        ]);

        $result = $stmt->fetch();

        return $result['count'] == 0;
    }

    /**
     * Obtient les données du profil public du nominé.
     *
     * @param int $userId ID du candidat.
     * @return array|null Données du profil ou null.
     */
    public function getNomineeData(int $userId): ?array
    {
        $sql = "SELECT 
                c.id_compte,
                c.nom_legal_ou_societe,
                c.type_candidature,
                co.pseudonyme,
                co.email,
                co.photo_profil,
                co.pays,
                co.genre,
                co.bio,
                co.url_instagram,
                co.url_tiktok,
                co.url_youtube,
                co.url_twitter,
                co.date_creation
                FROM candidat c
                JOIN compte co ON c.id_compte = co.id_compte
                WHERE c.id_compte = :userId
                AND c.est_nomine = 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':userId' => $userId]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Met à jour le profil public du nominé.
     *
     * @param int $userId ID du candidat.
     * @param array $data Données à mettre à jour.
     * @return bool Succès de l'opération.
     */
    public function updateNomineeProfile(int $userId, array $data): bool
    {
        try {
            $sql = "UPDATE compte SET
                bio = :bio,
                url_instagram = :url_instagram,
                url_tiktok = :url_tiktok,
                url_youtube = :url_youtube,
                url_twitter = :url_twitter,
                date_modification = NOW()";

            // Ajouter la photo de profil si fournie
            if (isset($data['photo_profil_path'])) {
                $sql .= ", photo_profil = :photo_profil";
            }

            $sql .= " WHERE id_compte = :userId";

            $stmt = $this->pdo->prepare($sql);

            $params = [
                ':bio' => $data['bio'] ?? null,
                ':url_instagram' => $data['url_instagram'] ?? null,
                ':url_tiktok' => $data['url_tiktok'] ?? null,
                ':url_youtube' => $data['url_youtube'] ?? null,
                ':url_twitter' => $data['url_twitter'] ?? null,
                ':userId' => $userId
            ];

            if (isset($data['photo_profil_path'])) {
                $params[':photo_profil'] = $data['photo_profil_path'];
            }

            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erreur mise à jour profil nominé: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtient les résultats des nominations.
     *
     * @param int $nominationId ID de la nomination.
     * @return array|null Résultats ou null.
     */
    public function getNominationResults(int $nominationId): ?array
    {
        $sql = "SELECT 
                r.position,
                r.nombre_votes,
                r.pourcentage,
                r.date_resultat
                FROM resultat_nomination r
                WHERE r.id_nomination = :nominationId
                ORDER BY r.position ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':nominationId' => $nominationId]);

        return $stmt->fetchAll() ?: null;
    }

    /**
     * Obtient les éditions actives pour candidatures.
     *
     * @return Edition[] Liste des éditions actives.
     */
    public function getActiveEditionsForCandidature(): array
    {
        $sql = "SELECT * 
                FROM edition 
                WHERE est_active = 1 
                AND date_fin_candidatures >= NOW()
                ORDER BY annee DESC, nom ASC";

        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetchAll();

        $editions = [];
        foreach ($data as $row) {
            $editions[] = new Edition($row);
        }

        return $editions;
    }

    /**
     * Obtient toutes les candidatures (pour l'administration).
     *
     * @return array Liste des candidatures.
     */
    public function getAllCandidatures(): array
    {
        $sql = "SELECT 
                c.*,
                co.pseudonyme,
                co.email,
                cat.nom as categorie_nom,
                ed.nom as edition_nom
                FROM candidature c
                JOIN compte co ON c.id_compte = co.id_compte
                JOIN categorie cat ON c.id_categorie = cat.id_categorie
                JOIN edition ed ON cat.id_edition = ed.id_edition
                ORDER BY c.date_soumission DESC";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtient les candidatures par statut.
     *
     * @param string $status Statut.
     * @return array Liste des candidatures.
     */
    public function getCandidaturesByStatus(string $status): array
    {
        $sql = "SELECT 
                c.*,
                co.pseudonyme,
                cat.nom as categorie_nom
                FROM candidature c
                JOIN compte co ON c.id_compte = co.id_compte
                JOIN categorie cat ON c.id_categorie = cat.id_categorie
                WHERE c.statut = :status
                ORDER BY c.date_soumission DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':status' => $status]);
        return $stmt->fetchAll();
    }

    /**
     * Approuve une candidature.
     *
     * @param int $candidatureId ID de la candidature.
     * @param int $adminId ID de l'administrateur.
     * @return bool Succès de l'opération.
     */
    public function approveCandidature(int $candidatureId, int $adminId): bool
    {
        try {
            $this->pdo->beginTransaction();

            // 1. Mettre à jour le statut de la candidature
            $sqlUpdate = "UPDATE candidature 
                         SET statut = 'Approuvée',
                             date_modification = NOW()
                         WHERE id_candidature = :id
                         AND statut = 'En attente'";

            $stmtUpdate = $this->pdo->prepare($sqlUpdate);
            $stmtUpdate->execute([':id' => $candidatureId]);

            // 2. Créer une nomination si nécessaire
            $sqlNomination = "INSERT INTO nomination (
                id_candidature,
                id_categorie,
                id_compte,
                date_creation
            ) SELECT 
                id_candidature,
                id_categorie,
                id_compte,
                NOW()
              FROM candidature 
              WHERE id_candidature = :id";

            $stmtNomination = $this->pdo->prepare($sqlNomination);
            $stmtNomination->execute([':id' => $candidatureId]);

            // 3. Marquer le candidat comme nominé
            $sqlCandidat = "UPDATE candidat c
                           JOIN candidature ca ON c.id_compte = ca.id_compte
                           SET c.est_nomine = 1
                           WHERE ca.id_candidature = :id";

            $stmtCandidat = $this->pdo->prepare($sqlCandidat);
            $stmtCandidat->execute([':id' => $candidatureId]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Erreur approbation candidature: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Rejette une candidature.
     *
     * @param int $candidatureId ID de la candidature.
     * @param int $adminId ID de l'administrateur.
     * @return bool Succès de l'opération.
     */
    public function rejectCandidature(int $candidatureId, int $adminId): bool
    {
        $sql = "UPDATE candidature 
                SET statut = 'Rejetée',
                    date_modification = NOW()
                WHERE id_candidature = :id
                AND statut = 'En attente'";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $candidatureId]);
    }

    /**
     * Obtient les catégories par édition.
     *
     * @param int $editionId ID de l'édition.
     * @return Categorie[] Liste des catégories.
     */
    public function getCategoriesByEdition(int $editionId): array
    {
        $sql = "SELECT *
                FROM categorie
                WHERE id_edition = :editionId
                ORDER BY nom ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':editionId' => $editionId]);
        $data = $stmt->fetchAll();

        $categories = [];
        foreach ($data as $row) {
            $categories[] = new Categorie($row);
        }

        return $categories;
    }

    /**
     * Obtient les statistiques par édition.
     *
     * @param int $editionId ID de l'édition.
     * @return array Statistiques.
     */
    public function getEditionStats(int $editionId): array
    {
        $sql = "SELECT 
                COUNT(CASE WHEN c.statut = 'En attente' THEN 1 END) as pending,
                COUNT(CASE WHEN c.statut = 'Approuvée' THEN 1 END) as approved,
                COUNT(CASE WHEN c.statut = 'Rejetée' THEN 1 END) as rejected,
                COUNT(DISTINCT c.id_compte) as unique_candidates,
                COUNT(*) as total
                FROM candidature c
                JOIN categorie cat ON c.id_categorie = cat.id_categorie
                WHERE cat.id_edition = :editionId";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':editionId' => $editionId]);
        return $stmt->fetch();
    }

    /**
     * Obtient le nombre total de candidats.
     *
     * @return int Nombre de candidats.
     */
    public function getTotalCandidates(): int
    {
        $sql = "SELECT COUNT(*) as total FROM candidat";
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch();

        return $result['total'] ?? 0;
    }

    /**
     * Obtient le nombre total de nominés.
     *
     * @return int Nombre de nominés.
     */
    public function getTotalNominees(): int
    {
        $sql = "SELECT COUNT(*) as total FROM candidat WHERE est_nomine = 1";
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch();

        return $result['total'] ?? 0;
    }

    /**
     * Obtient les candidats récemment inscrits.
     *
     * @param int $limit Limite.
     * @return array Liste des candidats.
     */
    public function getRecentCandidates(int $limit = 10): array
    {
        $sql = "SELECT 
                c.id_compte,
                c.nom_legal_ou_societe,
                c.type_candidature,
                co.pseudonyme,
                co.email,
                co.date_creation
                FROM candidat c
                JOIN compte co ON c.id_compte = co.id_compte
                ORDER BY co.date_creation DESC
                LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obtient les candidats avec le plus de candidatures.
     *
     * @param int $limit Limite.
     * @return array Liste des candidats.
     */
    public function getTopCandidates(int $limit = 10): array
    {
        $sql = "SELECT 
                c.id_compte,
                c.nom_legal_ou_societe,
                co.pseudonyme,
                COUNT(ca.id_candidature) as total_candidatures,
                SUM(CASE WHEN ca.statut = 'Approuvée' THEN 1 ELSE 0 END) as approved_candidatures
                FROM candidat c
                JOIN compte co ON c.id_compte = co.id_compte
                LEFT JOIN candidature ca ON c.id_compte = ca.id_compte
                GROUP BY c.id_compte, c.nom_legal_ou_societe, co.pseudonyme
                ORDER BY total_candidatures DESC
                LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obtient la connexion PDO.
     *
     * @return PDO Connexion à la base de données.
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}
