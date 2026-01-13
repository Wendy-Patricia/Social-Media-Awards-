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
                       comp.pseudonyme, comp.bio, comp.photo_profil, comp.url_instagram,
                       comp.url_tiktok, comp.url_youtube, comp.url_twitter
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
        return $stmt->fetch();
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
     * Cria uma nova candidatura
     */
    public function createCandidature(array $data, int $userId): bool
    {
        $sql = "INSERT INTO candidature 
                (libelle, plateforme, url_contenu, image, argumentaire, 
                 date_soumission, statut, id_compte, id_categorie)
                VALUES (:libelle, :plateforme, :url_contenu, :image, :argumentaire,
                        NOW(), 'En attente', :id_compte, :id_categorie)";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            ':libelle' => $data['libelle'],
            ':plateforme' => $data['plateforme'],
            ':url_contenu' => $data['url_contenu'],
            ':image' => $data['image'] ?? null,
            ':argumentaire' => $data['argumentaire'],
            ':id_compte' => $userId,
            ':id_categorie' => $data['id_categorie']
        ]);
    }

    /**
     * Atualiza uma candidatura
     */
    public function updateCandidature(int $id, array $data, int $userId): bool
    {
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
        
        return $stmt->execute([
            ':libelle' => $data['libelle'],
            ':plateforme' => $data['plateforme'],
            ':url_contenu' => $data['url_contenu'],
            ':image' => $data['image'] ?? null,
            ':argumentaire' => $data['argumentaire'],
            ':id_categorie' => $data['id_categorie'],
            ':id' => $id,
            ':userId' => $userId
        ]);
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

    // Adicionar estes métodos ao CandidatService.php existente

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
            bio = :bio,
            photo_profil = :photo_profil,
            url_instagram = :url_instagram,
            url_tiktok = :url_tiktok,
            url_youtube = :url_youtube,
            url_twitter = :url_twitter,
            date_modification = NOW()
            WHERE id_compte = :userId";
    
    $stmt = $this->pdo->prepare($sql);
    
    return $stmt->execute([
        ':bio' => $data['bio'] ?? null,
        ':photo_profil' => $data['photo_profil_path'] ?? null,
        ':url_instagram' => $data['url_instagram'] ?? null,
        ':url_tiktok' => $data['url_tiktok'] ?? null,
        ':url_youtube' => $data['url_youtube'] ?? null,
        ':url_twitter' => $data['url_twitter'] ?? null,
        ':userId' => $userId
    ]);
}

/**
 * Obter dados do nomeado
 */
public function getNomineeData(int $userId): array
{
    $sql = "SELECT bio, photo_profil, url_instagram, url_tiktok, url_youtube, url_twitter
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
}