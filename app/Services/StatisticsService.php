<?php
// app/Services/StatisticsService.php

require_once __DIR__ . '/../Models/DBModel.php';

class StatisticsService {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obter estatísticas para a página inicial (mantendo estrutura visual)
     */
    public function getHomePageStats() {
        try {
            // Tentar obter dados reais
            $realStats = $this->getRealHomeStats();
            
            // Se tivermos dados reais, usá-los
            if ($realStats['has_data']) {
                return [
                    'categories' => $realStats['categories'] ?? 12,
                    'platforms' => $realStats['platforms'] ?? 5,
                    'votes' => $realStats['votes'] ?? '50K+',
                    'candidatures' => $realStats['candidatures'] ?? 1000
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erreur getHomePageStats: " . $e->getMessage());
        }
        
        // Fallback para os valores atuais
        return [
            'categories' => 12,
            'platforms' => 5,
            'votes' => '50K+',
            'candidatures' => 1000
        ];
    }
    
    /**
     * Obter estatísticas reais
     */
    private function getRealHomeStats() {
        // Verificar se temos dados na BD
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM categorie");
        $categorieCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Se não houver categorias, retornar indicador de falta de dados
        if ($categorieCount == 0) {
            return ['has_data' => false];
        }
        
        // Obter dados reais
        $stmt = $this->db->prepare("
            SELECT 
                (SELECT COUNT(*) FROM categorie WHERE id_edition = 
                    (SELECT id_edition FROM edition WHERE est_active = 1 LIMIT 1)
                ) as categories,
                (SELECT COUNT(DISTINCT plateforme_cible) FROM categorie WHERE plateforme_cible IS NOT NULL) as platforms,
                (SELECT COUNT(*) FROM vote) as votes,
                (SELECT COUNT(*) FROM candidature) as candidatures
        ");
        
        $stmt->execute();
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'has_data' => true,
            'categories' => $stats['categories'] ?? 12,
            'platforms' => $stats['platforms'] ?? 5,
            'votes' => $this->formatVotes($stats['votes'] ?? 50000),
            'candidatures' => $stats['candidatures'] ?? 1000
        ];
    }
    
    /**
     * Formatar número de votos (50K+)
     */
    private function formatVotes($votes) {
        if ($votes >= 1000) {
            return floor($votes / 1000) . 'K+';
        }
        return $votes . '+';
    }
    
    /**
     * Obter estatísticas para a seção de stats (parte inferior)
     */
    public function getStatsSection() {
        try {
            $realStats = $this->getRealStatsSection();
            
            if ($realStats['has_data']) {
                return [
                    'categories' => $realStats['categories'] ?? 50,
                    'candidatures' => $realStats['candidatures'] ?? 1000,
                    'votes' => $realStats['votes'] ?? '50K+',
                    'platforms' => $realStats['platforms'] ?? 5
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erreur getStatsSection: " . $e->getMessage());
        }
        
        // Fallback para valores atuais
        return [
            'categories' => 50,
            'candidatures' => 1000,
            'votes' => '50K+',
            'platforms' => 5
        ];
    }
    
    private function getRealStatsSection() {
        // Similar ao getRealHomeStats mas com formatação diferente
        $stats = $this->getRealHomeStats();
        
        if (!$stats['has_data']) {
            return ['has_data' => false];
        }
        
        return [
            'has_data' => true,
            'categories' => $stats['categories'] ?? 50,
            'candidatures' => $stats['candidatures'] ?? 1000,
            'votes' => $stats['votes'] ?? '50K+',
            'platforms' => $stats['platforms'] ?? 5
        ];
    }
}
?>