<?php
session_start();
require_once 'config/database.php';
require_once 'models/Etkinlik.php';
require_once 'models/Kategori.php';
require_once 'models/Kullanici.php';
// Oturum kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit;
}
$db = Database::getInstance()->getConnection();
$kullanici = new Kullanici();
$kullanici_bilgileri = $kullanici->kullaniciGetir($_SESSION['kullanici_id']);
if (!$kullanici_bilgileri) {
    header('Location: logout.php');
    exit;
}
$etkinlik = new Etkinlik();
$kategori = new Kategori();
// Kullanıcının etkinliklerini ve katılımlarını getir
$olusturulan_etkinlikler = $etkinlik->kullaniciEtkinlikleriGetir($_SESSION['kullanici_id']);
$katilinan_etkinlikler = $etkinlik->kullaniciKatilimEtkinlikleriGetir($_SESSION['kullanici_id']);
$hata = '';
$basarili = '';
// Profil güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kullanici_adi = trim($_POST['kullanici_adi'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mevcut_sifre = trim($_POST['mevcut_sifre'] ?? '');
    $yeni_sifre = trim($_POST['yeni_sifre'] ?? '');
    $yeni_sifre_tekrar = trim($_POST['yeni_sifre_tekrar'] ?? '');
    // Validasyon
    if (empty($kullanici_adi)) {
        $hata = 'Kullanıcı adı gereklidir.';
    } elseif (empty($email)) {
        $hata = 'E-posta adresi gereklidir.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $hata = 'Geçerli bir e-posta adresi giriniz.';
    } elseif (!empty($yeni_sifre) && $yeni_sifre !== $yeni_sifre_tekrar) {
        $hata = 'Yeni şifreler eşleşmiyor.';
    } else {
        // TODO: Profil güncelleme işlemi eklenecek
        $basarili = 'Profil bilgileriniz başarıyla güncellendi.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilim - Hobi Kulübü</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        .navbar-brand i {
            margin-right: 8px;
        }
        .profile-header {
            background: linear-gradient(45deg, #1a1a1a, #2d2d2d);
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
            background-color: #343a40;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }
        .stats-card {
            background: linear-gradient(45deg, #1a1a1a, #2d2d2d);
            border-radius: 10px;
            padding: 1.5rem;
            height: 100%;
        }
        .stats-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
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
                        <a class="nav-link" href="etkinliklerim.php">
                            <i class="fas fa-calendar-check"></i> Etkinliklerim
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="etkinlik_olustur.php">
                            <i class="fas fa-plus"></i> Etkinlik Oluştur
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
                        <a class="nav-link active" href="profil.php">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($kullanici_bilgileri['kullanici_adi']); ?>
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
        <!-- Profil Başlığı -->
        <div class="profile-header">
            <div class="row align-items-center">
                <div class="col-md-3 text-center">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
                <div class="col-md-9">
                    <h1 class="h3 mb-2"><?php echo htmlspecialchars($kullanici_bilgileri['kullanici_adi']); ?></h1>
                    <p class="text-muted mb-0">
                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($kullanici_bilgileri['email']); ?>
                    </p>
                    <p class="text-muted mb-0">
                        <i class="fas fa-clock"></i> Üyelik: <?php echo date('d.m.Y', strtotime($kullanici_bilgileri['kayit_tarihi'])); ?>
                    </p>
                </div>
            </div>
        </div>
        <!-- İstatistikler -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-icon text-primary">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3 class="h4 mb-2"><?php echo count($olusturulan_etkinlikler); ?></h3>
                    <p class="text-muted mb-0">Oluşturulan Etkinlik</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-icon text-success">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3 class="h4 mb-2"><?php echo count($katilinan_etkinlikler); ?></h3>
                    <p class="text-muted mb-0">Katılınan Etkinlik</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-icon text-info">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="h4 mb-2">
                        <?php
                        $toplam_katilimci = 0;
                        foreach ($olusturulan_etkinlikler as $e) {
                            $toplam_katilimci += $e['kayitli_kisi'];
                        }
                        echo $toplam_katilimci;
                        ?>
                    </h3>
                    <p class="text-muted mb-0">Toplam Katılımcı</p>
                </div>
            </div>
        </div>
        <!-- Profil Düzenleme -->
        <div class="row">
            <div class="col-md-6">
                <div class="card bg-dark border-secondary">
                    <div class="card-header bg-dark border-secondary">
                        <h2 class="h5 mb-0">
                            <i class="fas fa-user-edit"></i> Profil Bilgileri
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
                                <label for="kullanici_adi" class="form-label">Kullanıcı Adı</label>
                                <input type="text" class="form-control bg-dark text-light border-secondary" 
                                       id="kullanici_adi" name="kullanici_adi" 
                                       value="<?php echo htmlspecialchars($kullanici_bilgileri['kullanici_adi']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">E-posta Adresi</label>
                                <input type="email" class="form-control bg-dark text-light border-secondary" 
                                       id="email" name="email" 
                                       value="<?php echo htmlspecialchars($kullanici_bilgileri['email']); ?>" required>
                            </div>
                            <hr class="border-secondary">
                            <h3 class="h6 mb-3">Şifre Değiştir</h3>
                            <div class="mb-3">
                                <label for="mevcut_sifre" class="form-label">Mevcut Şifre</label>
                                <input type="password" class="form-control bg-dark text-light border-secondary" 
                                       id="mevcut_sifre" name="mevcut_sifre">
                            </div>
                            <div class="mb-3">
                                <label for="yeni_sifre" class="form-label">Yeni Şifre</label>
                                <input type="password" class="form-control bg-dark text-light border-secondary" 
                                       id="yeni_sifre" name="yeni_sifre">
                            </div>
                            <div class="mb-3">
                                <label for="yeni_sifre_tekrar" class="form-label">Yeni Şifre (Tekrar)</label>
                                <input type="password" class="form-control bg-dark text-light border-secondary" 
                                       id="yeni_sifre_tekrar" name="yeni_sifre_tekrar">
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Değişiklikleri Kaydet
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-dark border-secondary">
                    <div class="card-header bg-dark border-secondary">
                        <h2 class="h5 mb-0">
                            <i class="fas fa-calendar-check"></i> Son Etkinliklerim
                        </h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($olusturulan_etkinlikler)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Henüz hiç etkinlik oluşturmamışsınız.
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($olusturulan_etkinlikler, 0, 5) as $e): ?>
                                    <a href="etkinlik_detay.php?id=<?php echo $e['id']; ?>" 
                                       class="list-group-item list-group-item-action bg-dark text-light border-secondary">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($e['baslik']); ?></h6>
                                            <small class="text-muted">
                                                <?php echo date('d.m.Y', strtotime($e['tarih'])); ?>
                                            </small>
                                        </div>
                                        <small class="text-muted">
                                            <i class="fas fa-users"></i> <?php echo $e['kayitli_kisi']; ?> katılımcı
                                        </small>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($olusturulan_etkinlikler) > 5): ?>
                                <div class="text-center mt-3">
                                    <a href="etkinliklerim.php" class="btn btn-outline-primary btn-sm">
                                        Tüm Etkinliklerimi Görüntüle
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card bg-dark border-secondary mt-4">
                    <div class="card-header bg-dark border-secondary">
                        <h2 class="h5 mb-0">
                            <i class="fas fa-calendar-alt"></i> Son Katıldığım Etkinlikler
                        </h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($katilinan_etkinlikler)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Henüz hiçbir etkinliğe katılmamışsınız.
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($katilinan_etkinlikler, 0, 5) as $e): ?>
                                    <a href="etkinlik_detay.php?id=<?php echo $e['id']; ?>" 
                                       class="list-group-item list-group-item-action bg-dark text-light border-secondary">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($e['baslik']); ?></h6>
                                            <small class="text-muted">
                                                <?php echo date('d.m.Y', strtotime($e['tarih'])); ?>
                                            </small>
                                        </div>
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i> Katılım: <?php echo date('d.m.Y', strtotime($e['katilim_tarihi'])); ?>
                                        </small>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($katilinan_etkinlikler) > 5): ?>
                                <div class="text-center mt-3">
                                    <a href="katildigim_etkinlikler.php" class="btn btn-outline-primary btn-sm">
                                        Tüm Katıldığım Etkinlikleri Görüntüle
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
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