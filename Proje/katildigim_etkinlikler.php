<?php
require_once 'config/database.php';
require_once 'config/Session.php';
require_once 'models/Etkinlik.php';
require_once 'models/Kategori.php';

Session::oturumKontrol();
$etkinlik = new Etkinlik();
$kategori = new Kategori();

// Kullanıcının katıldığı etkinlikleri getir
$etkinlikler = $etkinlik->kullaniciKatilimEtkinlikleriGetir(Session::kullaniciBilgisiAl('kullanici_id'));
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katıldığım Etkinlikler - Hobi Kulübü</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        .navbar-brand i {
            margin-right: 8px;
        }
        .card {
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .category-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .join-date {
            font-size: 0.9rem;
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
                        <a class="nav-link active" href="katildigim_etkinlikler.php">
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
        <h1 class="h3 mb-4">
            <i class="fas fa-calendar-alt"></i> Katıldığım Etkinlikler
        </h1>
        <?php if (empty($etkinlikler)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Henüz hiçbir etkinliğe katılmamışsınız.
                <a href="index.php" class="alert-link">Etkinlikleri görüntülemek için tıklayın</a>.
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($etkinlikler as $e): ?>
                    <div class="col">
                        <div class="card h-100 bg-dark border-secondary">
                            <div class="card-body">
                                <span class="badge bg-primary category-badge">
                                    <i class="fas <?php echo htmlspecialchars($e['kategori_ikon']); ?>"></i>
                                    <?php echo htmlspecialchars($e['kategori_adi']); ?>
                                </span>
                                <h5 class="card-title"><?php echo htmlspecialchars($e['baslik']); ?></h5>
                                <p class="card-text text-muted">
                                    <?php echo mb_substr(htmlspecialchars($e['aciklama']), 0, 100) . '...'; ?>
                                </p>
                                <ul class="list-unstyled mb-3">
                                    <li>
                                        <i class="fas fa-map-marker-alt text-danger"></i>
                                        <?php echo htmlspecialchars($e['konum']); ?>
                                    </li>
                                    <li>
                                        <i class="fas fa-calendar text-info"></i>
                                        <?php echo date('d.m.Y H:i', strtotime($e['tarih'])); ?>
                                    </li>
                                    <li>
                                        <i class="fas fa-users text-success"></i>
                                        <?php echo $e['kayitli_kisi']; ?> / 
                                        <?php echo $e['kisi_limit'] ? $e['kisi_limit'] : '∞'; ?> katılımcı
                                    </li>
                                    <li class="join-date">
                                        <i class="fas fa-clock text-warning"></i>
                                        Katılım: <?php echo date('d.m.Y H:i', strtotime($e['katilim_tarihi'])); ?>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-footer bg-dark border-secondary">
                                <div class="d-grid gap-2">
                                    <a href="etkinlik_detay.php?id=<?php echo $e['id']; ?>" 
                                       class="btn btn-outline-primary">
                                        <i class="fas fa-eye"></i> Detaylar
                                    </a>
                                    <form method="POST" action="etkinlik_detay.php?id=<?php echo $e['id']; ?>" 
                                          class="d-grid" onsubmit="return confirm('Bu etkinlikten ayrılmak istediğinizden emin misiniz?');">
                                        <input type="hidden" name="ayril" value="1">
                                        <button type="submit" class="btn btn-outline-danger">
                                            <i class="fas fa-sign-out-alt"></i> Ayrıl
                                        </button>
                                    </form>
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