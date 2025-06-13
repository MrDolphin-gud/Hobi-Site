<?php
// Hata raporlamayı etkinleştir
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Dosya yollarını tanımla
define('ROOT_PATH', __DIR__);
define('CONFIG_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'config');
define('MODELS_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'models');
// Gerekli dosyaları yükle
$config_files = [
    'Session.php',
    'database.php'
];
$model_files = [
    'Etkinlik.php',
    'Kategori.php'
];
// Config dosyalarını yükle
foreach ($config_files as $file) {
    $file_path = CONFIG_PATH . DIRECTORY_SEPARATOR . $file;
    if (!file_exists($file_path)) {
        die("Kritik dosya bulunamadı: " . $file);
    }
    require_once $file_path;
}
// Model dosyalarını yükle
foreach ($model_files as $file) {
    $file_path = MODELS_PATH . DIRECTORY_SEPARATOR . $file;
    if (!file_exists($file_path)) {
        die("Kritik dosya bulunamadı: " . $file);
    }
    require_once $file_path;
}

// Oturumu başlat
Session::baslat();
$etkinlik = new Etkinlik();
$kategori = new Kategori();
$etkinlikler = $etkinlik->tumEtkinlikleriGetir();
$kategoriler = $kategori->tumKategorileriGetir();
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hobi Kulübü Yönetim Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        .category-card {
            transition: transform 0.2s;
            cursor: pointer;
        }
        .category-card:hover {
            transform: translateY(-5px);
        }
        .event-card {
            transition: transform 0.2s;
        }
        .event-card:hover {
            transform: translateY(-5px);
        }
        .navbar-brand i {
            margin-right: 8px;
        }
        .category-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body class="bg-dark text-light">
    <?php include 'includes/navbar.php'; ?>
    <div class="container mt-4">
        <!-- Kategoriler -->
        <h2 class="mb-4">
            <i class="fas fa-tags"></i> Hobi Kategorileri
        </h2>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-5">
            <?php foreach ($kategoriler as $k): ?>
                <div class="col">
                    <a href="kategori.php?id=<?php echo $k['id']; ?>" class="text-decoration-none">
                        <div class="card h-100 bg-dark border-secondary category-card">
                            <div class="card-body text-center">
                                <i class="fas <?php echo htmlspecialchars($k['ikon']); ?> category-icon text-primary"></i>
                                <h5 class="card-title text-light"><?php echo htmlspecialchars($k['ad']); ?></h5>
                                <p class="card-text text-secondary"><?php echo htmlspecialchars($k['aciklama']); ?></p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <!-- Etkinlikler -->
        <h2 class="mb-4">
            <i class="fas fa-calendar"></i> Son Etkinlikler
        </h2>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($etkinlikler as $e): ?>
                <div class="col">
                    <div class="card h-100 bg-dark border-secondary event-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title">
                                    <i class="fas <?php echo htmlspecialchars($e['kategori_ikon']); ?> text-primary"></i>
                                    <?php echo htmlspecialchars($e['baslik']); ?>
                                </h5>
                                <span class="badge bg-primary">
                                    <i class="fas fa-users"></i> <?php echo $e['kayitli_kisi']; ?>/<?php echo $e['kisi_limit'] ?: '∞'; ?>
                                </span>
                            </div>
                            <p class="card-text text-secondary"><?php echo htmlspecialchars($e['aciklama']); ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($e['konum']); ?>
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-clock"></i> <?php echo date('d.m.Y H:i', strtotime($e['tarih'])); ?>
                                </small>
                            </div>
                        </div>
                        <div class="card-footer bg-dark border-secondary">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($e['kullanici_adi']); ?>
                                </small>
                                <a href="etkinlik_detay.php?id=<?php echo $e['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-info-circle"></i> Detaylar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
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