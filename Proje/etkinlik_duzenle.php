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
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}
$etkinlik = new Etkinlik();
$kategori = new Kategori();
$etkinlik_bilgi = $etkinlik->etkinlikGetir($_GET['id']);
if (!$etkinlik_bilgi || $etkinlik_bilgi['kullanici_id'] != $_SESSION['kullanici_id']) {
    header('Location: index.php');
    exit;
}
$kategoriler = $kategori->tumKategorileriGetir();
$hata = '';
$basarili = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $baslik = trim($_POST['baslik'] ?? '');
    $aciklama = trim($_POST['aciklama'] ?? '');
    $tarih = trim($_POST['tarih'] ?? '');
    $konum = trim($_POST['konum'] ?? '');
    $kisi_limit = (int)($_POST['kisi_limit'] ?? 0);
    $kategori_id = (int)($_POST['kategori_id'] ?? 0);
    // Validasyon
    if (empty($baslik)) {
        $hata = 'Etkinlik başlığı gereklidir.';
    } elseif (empty($aciklama)) {
        $hata = 'Etkinlik açıklaması gereklidir.';
    } elseif (empty($tarih)) {
        $hata = 'Etkinlik tarihi gereklidir.';
    } elseif (strtotime($tarih) < time()) {
        $hata = 'Etkinlik tarihi geçmiş bir tarih olamaz.';
    } elseif (empty($konum)) {
        $hata = 'Etkinlik konumu gereklidir.';
    } elseif ($kisi_limit < 0) {
        $hata = 'Kişi limiti 0 veya daha büyük olmalıdır.';
    } elseif ($kategori_id <= 0) {
        $hata = 'Lütfen bir kategori seçin.';
    } else {
        if ($etkinlik->etkinlikGuncelle($_GET['id'], $baslik, $aciklama, $tarih, $konum, $kisi_limit, $kategori_id)) {
            $basarili = 'Etkinlik başarıyla güncellendi!';
            $etkinlik_bilgi = $etkinlik->etkinlikGetir($_GET['id']); // Güncel bilgileri al
        } else {
            $hata = 'Etkinlik güncellenirken bir hata oluştu.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etkinlik Düzenle - Hobi Kulübü</title>
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
                        <a class="nav-link" href="etkinlik_olustur.php">
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
                            <i class="fas fa-edit"></i> Etkinlik Düzenle
                        </h2>
                    </div>
                    <div class="card-body">
                        <?php if ($hata): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $hata; ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($basarili): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <?php echo $basarili; ?>
                            </div>
                        <?php endif; ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="kategori" class="form-label">Kategori</label>
                                <select class="form-select bg-dark text-light border-secondary" id="kategori" name="kategori_id" required>
                                    <option value="">Kategori Seçin</option>
                                    <?php foreach ($kategoriler as $k): ?>
                                        <option value="<?php echo $k['id']; ?>" 
                                                <?php echo ($etkinlik_bilgi['kategori_id'] == $k['id']) ? 'selected' : ''; ?>>
                                            <i class="fas <?php echo htmlspecialchars($k['ikon']); ?>"></i>
                                            <?php echo htmlspecialchars($k['ad']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="baslik" class="form-label">Etkinlik Başlığı</label>
                                <input type="text" class="form-control bg-dark text-light border-secondary" 
                                       id="baslik" name="baslik" 
                                       value="<?php echo htmlspecialchars($etkinlik_bilgi['baslik']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="aciklama" class="form-label">Etkinlik Açıklaması</label>
                                <textarea class="form-control bg-dark text-light border-secondary" 
                                          id="aciklama" name="aciklama" rows="4" required><?php echo htmlspecialchars($etkinlik_bilgi['aciklama']); ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="tarih" class="form-label">Etkinlik Tarihi ve Saati</label>
                                    <input type="datetime-local" class="form-control bg-dark text-light border-secondary" 
                                           id="tarih" name="tarih" 
                                           value="<?php echo date('Y-m-d\TH:i', strtotime($etkinlik_bilgi['tarih'])); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="kisi_limit" class="form-label">Kişi Limiti (0 = Sınırsız)</label>
                                    <input type="number" class="form-control bg-dark text-light border-secondary" 
                                           id="kisi_limit" name="kisi_limit" 
                                           value="<?php echo $etkinlik_bilgi['kisi_limit']; ?>" min="0">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="konum" class="form-label">Etkinlik Konumu</label>
                                <input type="text" class="form-control bg-dark text-light border-secondary" 
                                       id="konum" name="konum" 
                                       value="<?php echo htmlspecialchars($etkinlik_bilgi['konum']); ?>" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Değişiklikleri Kaydet
                                </button>
                                <a href="etkinlik_detay.php?id=<?php echo $etkinlik_bilgi['id']; ?>" class="btn btn-secondary">
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
</body>
</html> 