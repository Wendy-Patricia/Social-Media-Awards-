<?php
// app/Services/CandidatService.php

namespace App\Services;

use PDO;
use PDOException;

class CandidatService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Obtém dados do candidato pelo ID
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
     * Atualiza dados do candidato
     */
    /**
     * Atualiza dados do candidato
     */
public function updateCandidat(int $userId, array $data): bool
{
    // Atualizar tabela candidat
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

    // Atualizar tabela compte - APENAS CAMPOS QUE EXISTEM
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
     * Verifica se o usuário é um nomeado (tem candidaturas aprovadas)
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
     * Obtém todas as nomeações ativas do usuário
     */
    public function getActiveNominations(int $userId): array
    {
        $sql = "SELECT n.*, cat.nom as categorie_nom, edi.nom as edition_nom,
                       cat.date_debut_votes, cat.date_fin_votes,
                       edi.date_debut, edi.date_fin,
                       c.libelle, c.plateforme, c.url_contenu, c.argumentaire,
                       comp.pseudonyme, comp.photo_profil
                FROM nomination n
                JOIN candidature c ON n.id_candidature = c.id_candidature
                JOIN categorie cat ON n.id_categorie = cat.id_categorie
                JOIN edition edi ON cat.id_edition = edi.id_edition
                JOIN compte comp ON c.id_compte = comp.id_compte
                WHERE c.id_compte = :userId
                ORDER BY n.date_approbation DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':userId' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtém o estado atual da votação
     */
    public function getVotingStatus(array $nomination): string
    {
        $now = date('Y-m-d H:i:s');
        $start = $nomination['date_debut_votes'] ?? $nomination['date_debut'];
        $end = $nomination['date_fin_votes'] ?? $nomination['date_fin'];

        if (!$end) return 'not_started';

        if ($now < $start) return 'not_started';
        if ($now <= $end) return 'in_progress';
        return 'ended';
    }

    /**
     * Verifica se o usuário pode editar o perfil
     */
    public function canEditProfile(int $userId): bool
    {
        $nominations = $this->getActiveNominations($userId);

        foreach ($nominations as $nom) {
            $status = $this->getVotingStatus($nom);
            if ($status === 'in_progress') {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtém estatísticas do candidato
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
     * Obtém todas as candidaturas do usuário
     */
    public function getUserCandidatures(int $userId): array
    {
        $sql = "SELECT c.*, cat.nom as categorie_nom, edi.nom as edition_nom,
                       edi.date_fin_candidatures
                FROM candidature c
                JOIN categorie cat ON c.id_categorie = cat.id_categorie
                JOIN edition edi ON cat.id_edition = edi.id_edition
                WHERE c.id_compte = :userId
                ORDER BY c.date_soumission DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':userId' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtém uma candidatura específica
     */
    public function getCandidature(int $id, int $userId): ?array
    {
        $sql = "SELECT c.*, cat.nom as categorie_nom, edi.nom as edition_nom
                FROM candidature c
                JOIN categorie cat ON c.id_categorie = cat.id_categorie
                JOIN edition edi ON cat.id_edition = edi.id_edition
                WHERE c.id_candidature = :id 
                AND c.id_compte = :userId";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id, ':userId' => $userId]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    /**
     * Cria uma nova candidatura (com verificação de duplicação por plataforma)
     */
    public function createCandidature(array $data, int $userId): array
    {
        // Verificar se já tem candidatura na categoria PARA A MESMA PLATAFORMA
        if ($this->hasCandidatureInCategoryForPlatform(
            $userId,
            $data['id_categorie'],
            $data['plateforme']
        )) {
            return [
                'success' => false,
                'error' => 'Vous avez déjà une candidature dans cette catégorie pour la plateforme ' .
                    htmlspecialchars($data['plateforme']) . '. ' .
                    'Vous pouvez soumettre une candidature différente pour une autre plateforme.'
            ];
        }

        // Verificar se a categoria ainda aceita candidaturas
        if (!$this->isCategoryOpenForCandidature($data['id_categorie'])) {
            return [
                'success' => false,
                'error' => 'Cette catégorie n\'accepte plus de candidatures.'
            ];
        }

        $sql = "INSERT INTO candidature 
            (libelle, plateforme, url_contenu, image, argumentaire, 
             date_soumission, statut, id_compte, id_categorie)
            VALUES (:libelle, :plateforme, :url_contenu, :image, :argumentaire,
                    NOW(), 'En attente', :id_compte, :id_categorie)";

        $stmt = $this->pdo->prepare($sql);

        $success = $stmt->execute([
            ':libelle' => $data['libelle'],
            ':plateforme' => $data['plateforme'],
            ':url_contenu' => $data['url_contenu'],
            ':image' => $data['image'] ?? null,
            ':argumentaire' => $data['argumentaire'],
            ':id_compte' => $userId,
            ':id_categorie' => $data['id_categorie']
        ]);

        if ($success) {
            return [
                'success' => true,
                'id' => $this->pdo->lastInsertId(),
                'message' => 'Candidature soumise avec succès !'
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Erreur technique lors de la soumission.'
            ];
        }
    }

    /**
     * Verifica se a categoria está aberta para candidaturas
     */
    private function isCategoryOpenForCandidature(int $categoryId): bool
    {
        $sql = "SELECT COUNT(*) as count 
            FROM categorie c
            JOIN edition e ON c.id_edition = e.id_edition
            WHERE c.id_categorie = :categoryId
            AND e.date_fin_candidatures >= NOW()
            AND e.est_active = 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':categoryId' => $categoryId]);
        $result = $stmt->fetch();

        return $result['count'] > 0;
    }

    /**
     * Atualiza uma candidatura (verificar duplicação ao mudar plataforma)
     */
    public function updateCandidature(int $id, array $data, int $userId): array
    {
        // Primeiro obter a candidatura atual
        $currentCandidature = $this->getCandidature($id, $userId);

        if (!$currentCandidature) {
            return [
                'success' => false,
                'error' => 'Candidature non trouvée.'
            ];
        }

        // Se está tentando mudar de categoria
        if (isset($data['id_categorie']) && $data['id_categorie'] != $currentCandidature['id_categorie']) {
            // Verificar se já tem candidatura na nova categoria PARA A MESMA PLATAFORMA
            if ($this->hasCandidatureInCategoryForPlatform(
                $userId,
                $data['id_categorie'],
                $data['plateforme']
            )) {
                return [
                    'success' => false,
                    'error' => 'Vous avez déjà une candidature dans cette catégorie pour la plateforme ' .
                        htmlspecialchars($data['plateforme']) . '.'
                ];
            }
        }

        // Se está tentando mudar de plataforma (mesma categoria)
        if (isset($data['plateforme']) && $data['plateforme'] != $currentCandidature['plateforme']) {
            // Verificar se já tem candidatura na mesma categoria para a NOVA plataforma
            if ($this->hasCandidatureInCategoryForPlatform(
                $userId,
                $currentCandidature['id_categorie'],
                $data['plateforme']
            )) {
                return [
                    'success' => false,
                    'error' => 'Vous avez déjà une candidature dans cette catégorie pour la plateforme ' .
                        htmlspecialchars($data['plateforme']) . '.'
                ];
            }
        }

        $sql = "UPDATE candidature SET
            libelle = :libelle,
            plateforme = :plateforme,
            url_contenu = :url_contenu,
            image = :image,
            argumentaire = :argumentaire,
            id_categorie = :id_categorie
            WHERE id_candidature = :id 
            AND id_compte = :userId
            AND statut = 'En attente'";

        $stmt = $this->pdo->prepare($sql);

        $success = $stmt->execute([
            ':libelle' => $data['libelle'],
            ':plateforme' => $data['plateforme'],
            ':url_contenu' => $data['url_contenu'],
            ':image' => $data['image'] ?? $currentCandidature['image'],
            ':argumentaire' => $data['argumentaire'],
            ':id_categorie' => $data['id_categorie'] ?? $currentCandidature['id_categorie'],
            ':id' => $id,
            ':userId' => $userId
        ]);

        if ($success) {
            return [
                'success' => true,
                'message' => 'Candidature modifiée avec succès !'
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Erreur lors de la modification.'
            ];
        }
    }


    /**
     * Exclui uma candidatura
     */
    public function deleteCandidature(int $id, int $userId): bool
    {
        // Primeiro obtém a candidatura para excluir a imagem
        $candidature = $this->getCandidature($id, $userId);

        if ($candidature && $candidature['image'] && file_exists(__DIR__ . '/../../public/' . $candidature['image'])) {
            unlink(__DIR__ . '/../../public/' . $candidature['image']);
        }

        $sql = "DELETE FROM candidature 
                WHERE id_candidature = :id 
                AND id_compte = :userId
                AND statut = 'En attente'";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id, ':userId' => $userId]);
    }

    /**
     * Upload de imagem
     */
    public function uploadImage(array $file): ?string
    {
        if (!$file || empty($file['name']) || $file['error'] !== 0) return null;

        $uploadDir = __DIR__ . '/../../public/uploads/candidatures/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if ($file['size'] > $maxSize || !in_array($file['type'], $allowed)) return null;

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('cand_') . '.' . strtolower($ext);
        $dest = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return 'uploads/candidatures/' . $filename;
        }

        return null;
    }

    /**
     * Upload de foto de perfil
     */
    public function uploadProfilePhoto(array $file, int $userId): ?string
    {
        if (!$file || empty($file['name']) || $file['error'] !== 0) return null;

        $uploadDir = __DIR__ . '/../../public/uploads/profiles/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if ($file['size'] > $maxSize || !in_array($file['type'], $allowed)) return null;

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'profile_' . $userId . '_' . time() . '.' . strtolower($ext);
        $dest = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return 'uploads/profiles/' . $filename;
        }

        return null;
    }

    /**
     * Atualizar perfil do nomeado
     */
    public function updateNomineeProfile(int $userId, array $data): bool
    {
        $sql = "UPDATE compte SET
                photo_profil = :photo_profil,
                date_modification = NOW()
                WHERE id_compte = :userId";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':photo_profil' => $data['photo_profil_path'] ?? null,
            ':userId' => $userId
        ]);
    }

    /**
     * Obter dados do nomeado
     */
    public function getNomineeData(int $userId): array
    {
        $sql = "SELECT photo_profil
                FROM compte 
                WHERE id_compte = :userId";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':userId' => $userId]);
        $result = $stmt->fetch();

        return $result ?: [];
    }

    /**
     * Obter resultados da nomeação
     */
    public function getNominationResults(int $nominationId): array
    {
        $sql = "SELECT r.rang, r.nombre_votes, n.libelle, c.nom as categorie_nom
                FROM resultat r
                JOIN nomination n ON r.id_nomination = n.id_nomination
                JOIN categorie c ON n.id_categorie = c.id_categorie
                WHERE r.id_nomination = :nominationId";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':nominationId' => $nominationId]);
        $result = $stmt->fetch();

        return $result ?: [];
    }

    /**
     * Verificar se nomeação tem certificado
     */
    public function hasCertificate(int $userId, int $nominationId): bool
    {
        $sql = "SELECT COUNT(*) as count 
                FROM certificat_participation 
                WHERE id_compte = :userId 
                AND id_categorie = (
                    SELECT id_categorie FROM nomination WHERE id_nomination = :nominationId
                )";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':userId' => $userId, ':nominationId' => $nominationId]);
        $result = $stmt->fetch();

        return $result['count'] > 0;
    }

    /**
     * Verificar se pseudônimo já existe
     */
    public function isPseudonymeAvailable(string $pseudonyme, int $excludeUserId = 0): bool
    {
        $sql = "SELECT COUNT(*) as count 
                FROM compte 
                WHERE pseudonyme = :pseudonyme 
                AND id_compte != :excludeUserId";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':pseudonyme' => $pseudonyme,
            ':excludeUserId' => $excludeUserId
        ]);
        $result = $stmt->fetch();

        return $result['count'] == 0;
    }

    /**
     * Verificar se email já existe
     */
    public function isEmailAvailable(string $email, int $excludeUserId = 0): bool
    {
        $sql = "SELECT COUNT(*) as count 
                FROM compte 
                WHERE email = :email 
                AND id_compte != :excludeUserId";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':email' => $email,
            ':excludeUserId' => $excludeUserId
        ]);
        $result = $stmt->fetch();

        return $result['count'] == 0;
    }

    /**
     * Obtém a conexão PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }



    /**
     * Obtém categorias disponíveis para o candidato se candidatar
     */
    public function getAvailableCategoriesForCandidature(int $userId): array
    {
        // 1. Obter edições ativas
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

        // 2. Obter categorias dessas edições
        $sqlCategories = "SELECT c.*, e.nom as edition_nom, e.date_fin_candidatures
                      FROM categorie c
                      JOIN edition e ON c.id_edition = e.id_edition
                      WHERE c.id_edition IN ($editionIdsString)
                      AND e.date_fin_candidatures >= NOW()
                      ORDER BY e.annee DESC, c.nom ASC";

        $stmtCategories = $this->pdo->prepare($sqlCategories);
        $stmtCategories->execute();
        $allCategories = $stmtCategories->fetchAll();

        // 3. Obter categorias onde o usuário já se candidatou
        $sqlUserCandidatures = "SELECT id_categorie 
                            FROM candidature 
                            WHERE id_compte = :userId 
                            AND statut != 'Rejetée'";

        $stmtUser = $this->pdo->prepare($sqlUserCandidatures);
        $stmtUser->execute([':userId' => $userId]);
        $userCategoryIds = $stmtUser->fetchAll(PDO::FETCH_COLUMN);

        // 4. Filtrar categorias disponíveis
        return array_filter($allCategories, function ($category) use ($userCategoryIds) {
            return !in_array($category['id_categorie'], $userCategoryIds);
        });
    }

    /**
     * Conta quantas categorias disponíveis existem
     */
    public function countAvailableCategories(int $userId): int
    {
        $availableCategories = $this->getAvailableCategoriesForCandidature($userId);
        return count($availableCategories);
    }

    /**
     * Verifica se candidato já tem candidatura na categoria PARA A MESMA PLATAFORMA
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
     * Obtém todas as candidaturas do usuário em uma categoria
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
        return $stmt->fetchAll();
    }

    
}
