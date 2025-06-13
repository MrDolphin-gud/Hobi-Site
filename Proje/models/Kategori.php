<?php
require_once __DIR__ . '/../config/database.php';
class Kategori {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function tumKategorileriGetir() {
        $query = "SELECT k.*, 
                        (SELECT COUNT(*) FROM etkinlikler WHERE kategori_id = k.id) as etkinlik_sayisi
                 FROM hobi_kategorileri k
                 ORDER BY k.ad ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function kategoriGetir($id) {
        $query = "SELECT k.*, 
                        (SELECT COUNT(*) FROM etkinlikler WHERE kategori_id = k.id) as etkinlik_sayisi
                 FROM hobi_kategorileri k
                 WHERE k.id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function kategoriEkle($ad, $ikon) {
        $query = "INSERT INTO hobi_kategorileri (ad, ikon) VALUES (?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$ad, $ikon]);
    }

    public function kategoriGuncelle($id, $ad, $ikon) {
        $query = "UPDATE hobi_kategorileri SET ad = ?, ikon = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$ad, $ikon, $id]);
    }

    public function kategoriSil($id) {
        // Önce bu kategoriye ait etkinlikleri kontrol et
        $query = "SELECT COUNT(*) FROM etkinlikler WHERE kategori_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        
        if ($stmt->fetchColumn() > 0) {
            return false; // Kategoriye ait etkinlikler varsa silme
        }

        $query = "DELETE FROM hobi_kategorileri WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }

    public function kategoriVarMi($ad, $id = null) {
        $query = "SELECT COUNT(*) FROM hobi_kategorileri WHERE ad = ?";
        $params = [$ad];
        
        if ($id !== null) {
            $query .= " AND id != ?";
            $params[] = $id;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
} 