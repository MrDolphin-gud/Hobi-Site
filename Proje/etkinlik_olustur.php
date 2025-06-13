<?php
session_start();
require_once 'config/database.php';
require_once 'models/Etkinlik.php';
require_once 'models/Kategori.php';
// Oturum kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit;
}
$etkinlik = new Etkinlik();
$kategori = new Kategori();
// Form değişkenlerini başlat
$baslik = '';
$aciklama = '';
$tarih = '';
$konum = '';
$kisi_limit = 0;
$kategori_id = '';
$hata = '';
$basarili = '';
// Kategorileri getir
$kategoriler = $kategori->tumKategorileriGetir();
// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form verilerini al ve temizle
    $baslik = trim($_POST['baslik'] ?? '');
    $aciklama = trim($_POST['aciklama'] ?? '');
    $tarih = trim($_POST['tarih'] ?? '');
    $konum = trim($_POST['konum'] ?? '');
    $kisi_limit = (int)($_POST['kisi_limit'] ?? 0);
    $kategori_id = (int)($_POST['kategori_id'] ?? 0);
    // Eğlenceli hata mesajları
    $hata_mesajlari = [
        'baslik' => 'Başlıksız etkinlik mi? Olmaz öyle şey! ✨',
        'aciklama' => 'Biraz detay verelim, herkes merak ediyor! 📝',
        'tarih' => 'Tarih belirlemeden kimse katılamaz ki! 🗓️',
        'gecmis_tarih' => 'Ups! Geçmişe yolculuk henüz mümkün değil. Gelecekte bir tarih seçelim mi? ⏰',
        'konum' => 'Nerede buluşacağız? Herkes kaybolacak! 🗺️',
        'kisi_limit' => 'Negatif kişi sayısı mı? Matematik kurallarına aykırı! 👥',
        'kategori' => 'Kategorisiz etkinlik olmaz, hadi bir tane seçelim! 🏷️',
        'genel_hata' => 'Bir şeyler ters gitti! Tekrar dener misin? 🔄'
    ];
    // Validasyon
    if (empty($baslik)) {
        $hata = $hata_mesajlari['baslik'];
    } elseif (empty($aciklama)) {
        $hata = $hata_mesajlari['aciklama'];
    } elseif (empty($tarih)) {
        $hata = $hata_mesajlari['tarih'];
    } elseif (strtotime($tarih) < strtotime('today')) {
        $hata = $hata_mesajlari['gecmis_tarih'];
    } elseif (empty($konum)) {
        $hata = $hata_mesajlari['konum'];
    } elseif ($kisi_limit < 0) {
        $hata = $hata_mesajlari['kisi_limit'];
    } elseif ($kategori_id <= 0) {
        $hata = $hata_mesajlari['kategori'];
    } else {
        // Etkinliği ekle
        if ($etkinlik->etkinlikEkle($baslik, $aciklama, $tarih, $konum, $kisi_limit, $_SESSION['kullanici_id'], $kategori_id)) {
            $basarili = 'Harika! Etkinliğin başarıyla oluşturuldu! 🎉 Artık herkes katılabilir.';
            // Form alanlarını temizle
            $baslik = '';
            $aciklama = '';
            $tarih = '';
            $konum = '';
            $kisi_limit = 0;
            $kategori_id = '';
        } else {
            $hata = $hata_mesajlari['genel_hata'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etkinlik Oluştur - Hobi Kulübü</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        .navbar-brand i {
            margin-right: 8px;
        }
        .form-floating > label {
            color: #6c757d;
        }
    </style>
</head>
<body class="bg-dark text-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom border-secondary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-users"></i> Hobi Kulübü
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home"></i> Ana Sayfa
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="etkinlik_olustur.php">
                            <i class="fas fa-plus"></i> Etkinlik Oluştur
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="etkinliklerim.php">
                            <i class="fas fa-calendar-check"></i> Etkinliklerim
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="katildigim_etkinlikler.php">
                            <i class="fas fa-calendar-alt"></i> Katıldığım Etkinlikler
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="profil.php">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['kullanici_adi']); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Çıkış
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Ana İçerik -->
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card bg-dark border-secondary">
                    <div class="card-header bg-dark border-secondary">
                        <h2 class="card-title mb-0">
                            <i class="fas fa-plus-circle"></i> Yeni Etkinlik Oluştur
                        </h2>
                    </div>
                    <div class="card-body">
                        <?php if ($hata): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <span class="fw-bold">Oups!</span> <?php echo htmlspecialchars($hata); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($basarili): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <span class="fw-bold">Tebrikler!</span> <?php echo htmlspecialchars($basarili); ?>
                            </div>
                        <?php endif; ?>
                        <form method="POST" action="" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="kategori_id" class="form-label">Kategori</label>
                                <select class="form-select" id="kategori_id" name="kategori_id" required>
                                    <option value="">Kategori Seçin</option>
                                    <?php foreach ($kategoriler as $kat): ?>
                                        <option value="<?php echo $kat['id']; ?>" <?php echo ($kategori_id == $kat['id']) ? 'selected' : ''; ?>>
                                            <i class="fas <?php echo htmlspecialchars($kat['ikon']); ?>"></i>
                                            <?php echo htmlspecialchars($kat['ad']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Kategorisiz etkinlik olmaz, hadi bir tane seçelim! 🏷️</div>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="baslik" name="baslik" 
                                       value="<?php echo htmlspecialchars($baslik); ?>" required>
                                <label for="baslik">Etkinlik Başlığı</label>
                                <div class="invalid-feedback">Başlıksız etkinlik mi? Olmaz öyle şey! ✨</div>
                            </div>
                            <div class="form-floating mb-3">
                                <textarea class="form-control" id="aciklama" name="aciklama" 
                                          style="height: 100px" required><?php echo htmlspecialchars($aciklama); ?></textarea>
                                <label for="aciklama">Etkinlik Açıklaması</label>
                                <div class="invalid-feedback">Biraz detay verelim, herkes merak ediyor! 📝</div>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="datetime-local" class="form-control" id="tarih" name="tarih" 
                                       value="<?php echo htmlspecialchars($tarih); ?>" required>
                                <label for="tarih">Etkinlik Tarihi</label>
                                <div class="invalid-feedback">Tarih belirlemeden kimse katılamaz ki! 🗓️</div>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="konum" name="konum" 
                                       value="<?php echo htmlspecialchars($konum); ?>" required>
                                <label for="konum">Etkinlik Konumu</label>
                                <div class="invalid-feedback">Nerede buluşacağız? Herkes kaybolacak! 🗺️</div>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="number" class="form-control" id="kisi_limit" name="kisi_limit" 
                                       value="<?php echo htmlspecialchars($kisi_limit); ?>" min="0" required>
                                <label for="kisi_limit">Katılımcı Limiti (0 = Sınırsız)</label>
                                <div class="invalid-feedback">Negatif kişi sayısı mı? Matematik kurallarına aykırı! 👥</div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Etkinliği Oluştur
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> İptal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer -->
    <footer class="bg-dark text-light border-top border-secondary py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Hobi Kulübü Yönetim Sistemi. Tüm hakları saklıdır.</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form doğrulama
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html> 