<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $db;
    private $table_name = "kullanicilar";
    public $id;
    public $kullanici_adi;
    public $email;
    public $sifre;
    public $ad_soyad;
    public $kayit_tarihi;
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    public function kayit() {
        try {
            $query = "INSERT INTO " . $this->table_name . "
                    (kullanici_adi, email, sifre, ad_soyad, kayit_tarihi)
                    VALUES
                    (:kullanici_adi, :email, :sifre, :ad_soyad, :kayit_tarihi)";
            $stmt = $this->db->prepare($query);
            // Verileri temizle
            $this->kullanici_adi = htmlspecialchars(strip_tags($this->kullanici_adi));
            $this->email = htmlspecialchars(strip_tags($this->email));
            $this->ad_soyad = htmlspecialchars(strip_tags($this->ad_soyad));
            // Şifreyi hashle
            $this->sifre = password_hash($this->sifre, PASSWORD_DEFAULT);
            // Tarihi ayarla
            $this->kayit_tarihi = date('Y-m-d H:i:s');
            // Parametreleri bağla
            $stmt->bindParam(":kullanici_adi", $this->kullanici_adi);
            $stmt->bindParam(":email", $this->email);
            $stmt->bindParam(":sifre", $this->sifre);
            $stmt->bindParam(":ad_soyad", $this->ad_soyad);
            $stmt->bindParam(":kayit_tarihi", $this->kayit_tarihi);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Kayıt hatası: " . $e->getMessage());
            return false;
        }
    }
    public function giris() {
        try {
            $query = "SELECT id, kullanici_adi, sifre, ad_soyad 
                    FROM " . $this->table_name . "
                    WHERE kullanici_adi = :kullanici_adi
                    LIMIT 0,1";
            $stmt = $this->db->prepare($query);
            $this->kullanici_adi = htmlspecialchars(strip_tags($this->kullanici_adi));
            $stmt->bindParam(":kullanici_adi", $this->kullanici_adi);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Giriş hatası: " . $e->getMessage());
            return false;
        }
    }
    public function kullaniciAdiKontrol() {
        $query = "SELECT id FROM " . $this->table_name . " 
                WHERE kullanici_adi = :kullanici_adi LIMIT 0,1";
        $stmt = $this->db->prepare($query);
        $this->kullanici_adi = htmlspecialchars(strip_tags($this->kullanici_adi));
        $stmt->bindParam(":kullanici_adi", $this->kullanici_adi);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
    public function emailKontrol() {
        $query = "SELECT id FROM " . $this->table_name . " 
                WHERE email = :email LIMIT 0,1";
        $stmt = $this->db->prepare($query);
        $this->email = htmlspecialchars(strip_tags($this->email));
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
?> 
