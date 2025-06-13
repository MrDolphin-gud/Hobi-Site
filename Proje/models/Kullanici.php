<?php
require_once __DIR__ . '/../config/database.php';

class Kullanici {
    private $db;
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    public function kullaniciGetir($id) {
        $query = "SELECT k.*, 
                        (SELECT COUNT(*) FROM etkinlikler WHERE kullanici_id = k.id) as etkinlik_sayisi,
                        (SELECT COUNT(*) FROM etkinlik_katilimcilar WHERE kullanici_id = k.id) as katilim_sayisi
                 FROM kullanicilar k
                 WHERE k.id = ?";   
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    public function kullaniciGetirByEmail($email) {
        $query = "SELECT * FROM kullanicilar WHERE email = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    public function kullaniciEkle($kullanici_adi, $email, $sifre, $ad_soyad) {
        $query = "INSERT INTO kullanicilar (kullanici_adi, email, sifre, ad_soyad, kayit_tarihi) 
                 VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$kullanici_adi, $email, password_hash($sifre, PASSWORD_DEFAULT), $ad_soyad]);
    }
    public function kullaniciGuncelle($id, $kullanici_adi, $email, $sifre = null) {
        if ($sifre !== null) {
            $query = "UPDATE kullanicilar 
                     SET kullanici_adi = ?, email = ?, sifre = ? 
                     WHERE id = ?";
            $params = [$kullanici_adi, $email, password_hash($sifre, PASSWORD_DEFAULT), $id];
        } else {
            $query = "UPDATE kullanicilar 
                     SET kullanici_adi = ?, email = ? 
                     WHERE id = ?";
            $params = [$kullanici_adi, $email, $id];
        }
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }
    public function kullaniciSil($id) {
        // Önce kullanıcının etkinliklerini kontrol et
        $query = "SELECT COUNT(*) FROM etkinlikler WHERE kullanici_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);   
        if ($stmt->fetchColumn() > 0) {
            return false; // Kullanıcının etkinlikleri varsa silme
        }
        // Kullanıcının katılımlarını sil
        $query = "DELETE FROM etkinlik_katilimcilar WHERE kullanici_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        // Kullanıcıyı sil
        $query = "DELETE FROM kullanicilar WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }
    public function emailVarMi($email, $id = null) {
        $query = "SELECT COUNT(*) FROM kullanicilar WHERE email = ?";
        $params = [$email];   
        if ($id !== null) {
            $query .= " AND id != ?";
            $params[] = $id;
        }
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
    public function kullaniciAdiVarMi($kullanici_adi, $id = null) {
        $query = "SELECT COUNT(*) FROM kullanicilar WHERE kullanici_adi = ?";
        $params = [$kullanici_adi];   
        if ($id !== null) {
            $query .= " AND id != ?";
            $params[] = $id;
        }
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
    public function sifreDogrula($email, $sifre) {
        $kullanici = $this->kullaniciGetirByEmail($email);
        if ($kullanici && password_verify($sifre, $kullanici['sifre'])) {
            return $kullanici;
        }
        return false;
    }
} 