<?php
require_once __DIR__ . '/../config/database.php';

class Etkinlik {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function tumEtkinlikleriGetir() {
        $query = "SELECT e.*, k.ad as kategori_adi, k.ikon as kategori_ikon, 
                        u.kullanici_adi, 
                        (SELECT COUNT(*) FROM etkinlik_katilimcilar WHERE etkinlik_id = e.id) as kayitli_kisi
                 FROM etkinlikler e
                 LEFT JOIN hobi_kategorileri k ON e.kategori_id = k.id
                 LEFT JOIN kullanicilar u ON e.kullanici_id = u.id
                 ORDER BY e.tarih DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function etkinlikGetir($id) {
        $query = "SELECT e.*, k.ad as kategori_adi, k.ikon as kategori_ikon, 
                        u.kullanici_adi, 
                        (SELECT COUNT(*) FROM etkinlik_katilimcilar WHERE etkinlik_id = e.id) as kayitli_kisi
                 FROM etkinlikler e
                 LEFT JOIN hobi_kategorileri k ON e.kategori_id = k.id
                 LEFT JOIN kullanicilar u ON e.kullanici_id = u.id
                 WHERE e.id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function etkinlikEkle($baslik, $aciklama, $tarih, $konum, $kisi_limit, $kullanici_id, $kategori_id) {
        $query = "INSERT INTO etkinlikler (baslik, aciklama, tarih, konum, kisi_limit, kullanici_id, kategori_id, olusturma_tarihi) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$baslik, $aciklama, $tarih, $konum, $kisi_limit, $kullanici_id, $kategori_id]);
    }

    public function etkinlikGuncelle($id, $baslik, $aciklama, $tarih, $konum, $kisi_limit, $kategori_id) {
        $query = "UPDATE etkinlikler 
                 SET baslik = ?, aciklama = ?, tarih = ?, konum = ?, kisi_limit = ?, kategori_id = ? 
                 WHERE id = ?";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$baslik, $aciklama, $tarih, $konum, $kisi_limit, $kategori_id, $id]);
    }

    public function etkinlikSil($id) {
        // Önce katılımcıları sil
        $query = "DELETE FROM etkinlik_katilimcilar WHERE etkinlik_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);

        // Sonra etkinliği sil
        $query = "DELETE FROM etkinlikler WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }

    public function katilimciEkle($etkinlik_id, $kullanici_id) {
        // Etkinlik kapasitesi kontrolü
        $etkinlik = $this->etkinlikGetir($etkinlik_id);
        $kayitli_kisi = $this->katilimciSayisiGetir($etkinlik_id);

        if ($etkinlik['kisi_limit'] > 0 && $kayitli_kisi >= $etkinlik['kisi_limit']) {
            return false;
        }

        // Katılımcı zaten var mı kontrolü
        if ($this->katilimciKontrol($etkinlik_id, $kullanici_id)) {
            return false;
        }

        $query = "INSERT INTO etkinlik_katilimcilar (etkinlik_id, kullanici_id, katilim_tarihi) 
                 VALUES (?, ?, NOW())";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$etkinlik_id, $kullanici_id]);
    }

    public function katilimciSil($etkinlik_id, $kullanici_id) {
        $query = "DELETE FROM etkinlik_katilimcilar 
                 WHERE etkinlik_id = ? AND kullanici_id = ?";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$etkinlik_id, $kullanici_id]);
    }

    public function katilimcilarGetir($etkinlik_id) {
        $query = "SELECT k.*, ek.katilim_tarihi
                 FROM etkinlik_katilimcilar ek
                 JOIN kullanicilar k ON ek.kullanici_id = k.id
                 WHERE ek.etkinlik_id = ?
                 ORDER BY ek.katilim_tarihi ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$etkinlik_id]);
        return $stmt->fetchAll();
    }

    public function katilimciKontrol($etkinlik_id, $kullanici_id) {
        $query = "SELECT COUNT(*) FROM etkinlik_katilimcilar 
                 WHERE etkinlik_id = ? AND kullanici_id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$etkinlik_id, $kullanici_id]);
        return $stmt->fetchColumn() > 0;
    }

    public function katilimciSayisiGetir($etkinlik_id) {
        $query = "SELECT COUNT(*) FROM etkinlik_katilimcilar WHERE etkinlik_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$etkinlik_id]);
        return $stmt->fetchColumn();
    }

    public function kategoriEtkinlikleriGetir($kategori_id) {
        $query = "SELECT e.*, k.ad as kategori_adi, k.ikon as kategori_ikon, 
                        u.kullanici_adi, 
                        (SELECT COUNT(*) FROM etkinlik_katilimcilar WHERE etkinlik_id = e.id) as kayitli_kisi
                 FROM etkinlikler e
                 LEFT JOIN hobi_kategorileri k ON e.kategori_id = k.id
                 LEFT JOIN kullanicilar u ON e.kullanici_id = u.id
                 WHERE e.kategori_id = ?
                 ORDER BY e.tarih DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$kategori_id]);
        return $stmt->fetchAll();
    }

    public function kullaniciEtkinlikleriGetir($kullanici_id) {
        $query = "SELECT e.*, k.ad as kategori_adi, k.ikon as kategori_ikon, 
                        u.kullanici_adi, 
                        (SELECT COUNT(*) FROM etkinlik_katilimcilar WHERE etkinlik_id = e.id) as kayitli_kisi
                 FROM etkinlikler e
                 LEFT JOIN hobi_kategorileri k ON e.kategori_id = k.id
                 LEFT JOIN kullanicilar u ON e.kullanici_id = u.id
                 WHERE e.kullanici_id = ?
                 ORDER BY e.tarih DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$kullanici_id]);
        return $stmt->fetchAll();
    }

    public function kullaniciKatilimEtkinlikleriGetir($kullanici_id) {
        $query = "SELECT e.*, k.ad as kategori_adi, k.ikon as kategori_ikon, 
                        u.kullanici_adi, ek.katilim_tarihi,
                        (SELECT COUNT(*) FROM etkinlik_katilimcilar WHERE etkinlik_id = e.id) as kayitli_kisi
                 FROM etkinlik_katilimcilar ek
                 JOIN etkinlikler e ON ek.etkinlik_id = e.id
                 LEFT JOIN hobi_kategorileri k ON e.kategori_id = k.id
                 LEFT JOIN kullanicilar u ON e.kullanici_id = u.id
                 WHERE ek.kullanici_id = ?
                 ORDER BY e.tarih DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$kullanici_id]);
        return $stmt->fetchAll();
    }
}
?> 