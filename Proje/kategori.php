<?php
session_start();
require_once 'config/database.php';
require_once 'models/Etkinlik.php';
require_once 'models/Kategori.php';
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}
$kategori = new Kategori();
$etkinlik = new Etkinlik();

$kategori_bilgi = $kategori->kategoriGetir($_GET['id']);
if (!$kategori_bilgi) {
    header('Location: index.php');
    exit;
}
$etkinlikler = $etkinlik->kategoriEtkinlikleriGetir($_GET['id']);
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($kategori_bilgi['ad']); ?> - Hobi Kulübü</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        .event-card {
            transition: transform 0.2s;
        }
        .event-card:hover {
            transform: translateY(-5px);
        }
        .navbar-brand i {
            margin-right: 8px;
        }
        .category-header {
            background: linear-gradient(45deg, #1a1a1a, #2d2d2d);
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .category-icon {
            font-size: 3rem;
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
                    <?php if (isset($_SESSION['kullanici_id'])): ?>
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
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['kullanici_id'])): ?>
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
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt"></i> Giriş
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                <i class="fas fa-user-plus"></i> Kayıt Ol
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Ana İçerik -->
    <div class="container py-4">
        <!-- Kategori Başlığı -->
        <div class="category-header text-center">
            <i class="fas <?php echo htmlspecialchars($kategori_bilgi['ikon']); ?> category-icon text-primary"></i>
            <h1 class="display-4 mb-3"><?php echo htmlspecialchars($kategori_bilgi['ad']); ?></h1>
            <p class="lead text-secondary"><?php echo htmlspecialchars($kategori_bilgi['aciklama']); ?></p>
        </div>

        <!-- Etkinlikler -->
        <h2 class="mb-4">
            <i class="fas fa-calendar"></i> <?php echo htmlspecialchars($kategori_bilgi['ad']); ?> Etkinlikleri
        </h2>
        <?php if (empty($etkinlikler)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Bu kategoride henüz etkinlik bulunmamaktadır.
                <?php if (isset($_SESSION['kullanici_id'])): ?>
                    <a href="etkinlik_olustur.php" class="alert-link">Yeni bir etkinlik oluşturmak için tıklayın</a>.
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($etkinlikler as $e): ?>
                    <div class="col">
                        <div class="card h-100 bg-dark border-secondary event-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title"><?php echo htmlspecialchars($e['baslik']); ?></h5>
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
        <?php endif; ?>
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