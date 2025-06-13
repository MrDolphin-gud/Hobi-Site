-- Veritabanını oluştur
CREATE DATABASE IF NOT EXISTS dbstorage23360859455 CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;
USE dbstorage23360859455;

-- Kullanıcılar tablosu
CREATE TABLE IF NOT EXISTS kullanicilar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_adi VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    sifre VARCHAR(255) NOT NULL,
    ad_soyad VARCHAR(100) NOT NULL,
    kayit_tarihi DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    telefon VARCHAR(20) NULL,
    adres TEXT NULL,
    hakkinda TEXT NULL,
    profil_resmi VARCHAR(255) NULL,
    deneyim_seviyesi ENUM('yeni_başlayan', 'orta_seviye', 'ileri_seviye', 'uzman') NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Hobi kategorileri tablosu
CREATE TABLE IF NOT EXISTS hobi_kategorileri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(50) NOT NULL UNIQUE,
    aciklama TEXT,
    ikon VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Varsayılan hobi kategorileri
INSERT INTO hobi_kategorileri (ad, aciklama, ikon) VALUES
('Çizgi Roman', 'Çizgi roman okuma ve koleksiyonculuğu', 'fa-book'),
('Film', 'Film izleme ve tartışma', 'fa-film'),
('Müzik', 'Müzik dinleme ve enstrüman çalma', 'fa-music'),
('Spor', 'Çeşitli spor aktiviteleri', 'fa-futbol'),
('Fotoğrafçılık', 'Fotoğraf çekme ve düzenleme', 'fa-camera'),
('Yemek', 'Yemek yapma ve tatma', 'fa-utensils'),
('Seyahat', 'Gezi ve keşif', 'fa-plane'),
('Sanat', 'Resim, heykel ve diğer sanat dalları', 'fa-palette'),
('Oyun', 'Bilgisayar ve masa oyunları', 'fa-gamepad'),
('Kitap', 'Kitap okuma ve tartışma', 'fa-book-open'),
('Teknoloji', 'Teknoloji ve yazılım', 'fa-laptop-code'),
('Doğa', 'Doğa aktiviteleri ve kamp', 'fa-tree');

-- Etkinlikler tablosu
CREATE TABLE IF NOT EXISTS etkinlikler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    baslik VARCHAR(200) NOT NULL,
    aciklama TEXT,
    tarih DATETIME NOT NULL,
    konum VARCHAR(200) NOT NULL,
    kisi_limit INT NOT NULL DEFAULT 0,
    kayitli_kisi INT NOT NULL DEFAULT 0,
    kullanici_id INT NOT NULL,
    kategori_id INT NOT NULL,
    olusturma_tarihi DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    FOREIGN KEY (kategori_id) REFERENCES hobi_kategorileri(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Etkinlik katılımcıları tablosu
CREATE TABLE IF NOT EXISTS etkinlik_katilimcilar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etkinlik_id INT NOT NULL,
    kullanici_id INT NOT NULL,
    katilim_tarihi DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (etkinlik_id) REFERENCES etkinlikler(id) ON DELETE CASCADE,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    UNIQUE KEY unique_katilim (etkinlik_id, kullanici_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Ekipman tablosu
CREATE TABLE IF NOT EXISTS ekipmanlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(100) NOT NULL,
    aciklama TEXT,
    kategori_id INT NOT NULL,
    kullanici_id INT NOT NULL,
    durum ENUM('müsait', 'ödünç_verildi', 'tamirde') NOT NULL DEFAULT 'müsait',
    olusturma_tarihi DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES hobi_kategorileri(id) ON DELETE RESTRICT,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Ekipman ödünç alma tablosu
CREATE TABLE IF NOT EXISTS ekipman_odunc (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ekipman_id INT NOT NULL,
    odunc_alan_id INT NOT NULL,
    odunc_veren_id INT NOT NULL,
    baslangic_tarihi DATETIME NOT NULL,
    bitis_tarihi DATETIME NOT NULL,
    durum ENUM('beklemede', 'onaylandi', 'reddedildi', 'tamamlandi', 'iptal') NOT NULL DEFAULT 'beklemede',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ekipman_id) REFERENCES ekipmanlar(id) ON DELETE CASCADE,
    FOREIGN KEY (odunc_alan_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    FOREIGN KEY (odunc_veren_id) REFERENCES kullanicilar(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Forum kategorileri tablosu
CREATE TABLE IF NOT EXISTS forum_kategorileri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(100) NOT NULL,
    aciklama TEXT,
    kategori_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES hobi_kategorileri(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Varsayılan forum kategorileri
INSERT INTO forum_kategorileri (ad, aciklama, kategori_id) VALUES
-- Çizgi Roman kategorisi için
('Çizgi Roman Önerileri', 'Yeni çıkan ve önerilen çizgi romanlar hakkında tartışmalar', 1),
('Çizgi Roman Koleksiyonculuğu', 'Koleksiyon yapma ve değerli çizgi romanlar hakkında bilgiler', 1),
('Çizgi Roman Etkinlikleri', 'Çizgi roman fuarları ve etkinlikleri hakkında bilgiler', 1),
-- Film kategorisi için
('Film Önerileri', 'İzlenmesi gereken filmler ve film önerileri', 2),
('Film Tartışmaları', 'Filmler hakkında tartışmalar ve yorumlar', 2),
('Film Festivalleri', 'Film festivalleri ve etkinlikleri hakkında bilgiler', 2),
-- Müzik kategorisi için
('Enstrüman Çalma', 'Enstrüman çalma teknikleri ve ipuçları', 3),
('Müzik Önerileri', 'Yeni çıkan albümler ve müzik önerileri', 3),
('Müzik Etkinlikleri', 'Konserler ve müzik festivalleri hakkında bilgiler', 3),
-- Spor kategorisi için
('Spor Aktiviteleri', 'Çeşitli spor aktiviteleri ve organizasyonlar', 4),
('Spor Ekipmanları', 'Spor ekipmanları hakkında bilgiler ve öneriler', 4),
('Spor Turnuvaları', 'Spor turnuvaları ve yarışmalar hakkında bilgiler', 4),
-- Fotoğrafçılık kategorisi için
('Fotoğraf Teknikleri', 'Fotoğraf çekme teknikleri ve ipuçları', 5),
('Fotoğraf Ekipmanları', 'Fotoğraf makineleri ve ekipmanları hakkında bilgiler', 5),
('Fotoğraf Gezileri', 'Fotoğraf çekimi için gezi ve etkinlik organizasyonları', 5),
-- Yemek kategorisi için
('Tarifler', 'Yemek tarifleri ve mutfak ipuçları', 6),
('Restoran Önerileri', 'Restoran ve kafe önerileri', 6),
('Yemek Etkinlikleri', 'Yemek festivalleri ve etkinlikleri', 6),
-- Seyahat kategorisi için
('Gezi Önerileri', 'Gezi ve seyahat önerileri', 7),
('Seyahat İpuçları', 'Seyahat planlaması ve ipuçları', 7),
('Gezgin Deneyimleri', 'Gezginlerin deneyimleri ve anıları', 7),
-- Sanat kategorisi için
('Sanat Teknikleri', 'Sanat teknikleri ve ipuçları', 8),
('Sanat Eserleri', 'Sanat eserleri hakkında tartışmalar', 8),
('Sanat Etkinlikleri', 'Sanat sergileri ve etkinlikleri', 8),
-- Oyun kategorisi için
('Oyun Önerileri', 'Oyun önerileri ve incelemeleri', 9),
('Oyun Stratejileri', 'Oyun stratejileri ve ipuçları', 9),
('Oyun Turnuvaları', 'Oyun turnuvaları ve etkinlikleri', 9),
-- Kitap kategorisi için
('Kitap Önerileri', 'Kitap önerileri ve incelemeleri', 10),
('Kitap Kulüpleri', 'Kitap kulüpleri ve okuma grupları', 10),
('Yazar Söyleşileri', 'Yazar söyleşileri ve imza günleri', 10),
-- Teknoloji kategorisi için
('Teknoloji Haberleri', 'Teknoloji dünyasından haberler', 11),
('Yazılım Geliştirme', 'Yazılım geliştirme ve programlama', 11),
('Teknoloji Ürünleri', 'Teknoloji ürünleri hakkında tartışmalar', 11),
-- Doğa kategorisi için
('Doğa Aktiviteleri', 'Doğa aktiviteleri ve organizasyonlar', 12),
('Kamp Rehberi', 'Kamp yapma ve doğada yaşam ipuçları', 12),
('Doğa Koruma', 'Doğa koruma ve çevre bilinci', 12);

-- Forum konuları tablosu
CREATE TABLE IF NOT EXISTS forum_konulari (
    id INT AUTO_INCREMENT PRIMARY KEY,
    baslik VARCHAR(200) NOT NULL,
    icerik TEXT NOT NULL,
    kategori_id INT NOT NULL,
    kullanici_id INT NOT NULL,
    goruntulenme INT NOT NULL DEFAULT 0,
    olusturma_tarihi DATETIME NOT NULL,
    son_guncelleme DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES forum_kategorileri(id) ON DELETE CASCADE,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Forum yorumları tablosu
CREATE TABLE IF NOT EXISTS forum_yorumlari (
    id INT AUTO_INCREMENT PRIMARY KEY,
    konu_id INT NOT NULL,
    kullanici_id INT NOT NULL,
    icerik TEXT NOT NULL,
    olusturma_tarihi DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (konu_id) REFERENCES forum_konulari(id) ON DELETE CASCADE,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Kullanıcı-hobi ilişki tablosu
CREATE TABLE IF NOT EXISTS kullanici_hobileri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_id INT NOT NULL,
    kategori_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    FOREIGN KEY (kategori_id) REFERENCES hobi_kategorileri(id) ON DELETE CASCADE,
    UNIQUE KEY unique_kullanici_hobi (kullanici_id, kategori_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci; 