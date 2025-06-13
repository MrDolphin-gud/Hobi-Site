<?php
require_once 'config/database.php';
require_once 'config/Session.php';
require_once 'models/Etkinlik.php';
require_once 'models/Kategori.php';

// Session başlat
Session::baslat();
// Oturum kontrolü
Session::oturumKontrol();

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$etkinlik = new Etkinlik();
$etkinlik_bilgi = $etkinlik->etkinlikGetir($_GET['id']);
if (!$etkinlik_bilgi) {
    header('Location: index.php');
    exit;
}

$katilimcilar = $etkinlik->katilimcilarGetir($_GET['id']);
$katilimci_sayisi = count($katilimcilar);
$kullanici_id = Session::kullaniciBilgisiAl('kullanici_id');
$katilimci_mi = isset($kullanici_id) ? $etkinlik->katilimciKontrol($_GET['id'], $kullanici_id) : false;
$hata = '';
$basarili = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($kullanici_id)) {
    // CSRF token kontrolü
    if (!isset($_POST['csrf_token']) || !Session::csrfTokenKontrol($_POST['csrf_token'])) {
        $hata = "Geçersiz istek.";
    } else {
        if (isset($_POST['katil'])) {
            if ($etkinlik->katilimciEkle($_GET['id'], $kullanici_id)) {
                $basarili = 'Etkinliğe başarıyla katıldınız!';
                $katilimci_mi = true;
                $katilimcilar = $etkinlik->katilimcilarGetir($_GET['id']);
                $katilimci_sayisi = count($katilimcilar);
            } else {
                $hata = 'Etkinliğe katılırken bir hata oluştu veya etkinlik kontenjanı dolu.';
            }
        } elseif (isset($_POST['ayril'])) {
            if ($etkinlik->katilimciCikar($_GET['id'], $kullanici_id)) {
                $basarili = 'Etkinlikten başarıyla ayrıldınız.';
                $katilimci_mi = false;
                $katilimcilar = $etkinlik->katilimcilarGetir($_GET['id']);
                $katilimci_sayisi = count($katilimcilar);
            } else {
                $hata = 'Etkinlikten ayrılırken bir hata oluştu.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($etkinlik_bilgi['baslik']); ?> - Hobi Kulübü</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        .navbar-brand i {
            margin-right: 8px;
        }
        .event-header {
            background: linear-gradient(45deg, #1a1a1a, #2d2d2d);
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .category-icon {
            font-size: 2rem;
            margin-right: 1rem;
        }
        .participant-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #0d6efd;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 10px;
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
        <!-- Etkinlik Başlığı -->
        <div class="event-header">
            <div class="d-flex align-items-center mb-3">
                <i class="fas <?php echo htmlspecialchars($etkinlik_bilgi['kategori_ikon']); ?> category-icon text-primary"></i>
                <div>
                    <h1 class="mb-1"><?php echo htmlspecialchars($etkinlik_bilgi['baslik']); ?></h1>
                    <p class="text-secondary mb-0">
                        <i class="fas fa-tag"></i> <?php echo htmlspecialchars($etkinlik_bilgi['kategori_adi']); ?>
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-2">
                        <i class="fas fa-user"></i> Oluşturan: <?php echo htmlspecialchars($etkinlik_bilgi['kullanici_adi']); ?>
                    </p>
                    <p class="mb-2">
                        <i class="fas fa-calendar"></i> Tarih: <?php echo date('d.m.Y H:i', strtotime($etkinlik_bilgi['tarih'])); ?>
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-map-marker-alt"></i> Konum: <?php echo htmlspecialchars($etkinlik_bilgi['konum']); ?>
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-2">
                        <i class="fas fa-users"></i> Katılımcılar: <?php echo $katilimci_sayisi; ?>/<?php echo $etkinlik_bilgi['kisi_limit'] ?: '∞'; ?>
                    </p>
                    <?php if (isset($_SESSION['kullanici_id'])): ?>
                        <?php if ($_SESSION['kullanici_id'] == $etkinlik_bilgi['kullanici_id']): ?>
                            <div class="btn-group">
                                <a href="etkinlik_duzenle.php?id=<?php echo $etkinlik_bilgi['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Düzenle
                                </a>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#silModal">
                                    <i class="fas fa-trash"></i> Sil
                                </button>
                            </div>
                        <?php else: ?>
                            <form method="POST" class="d-inline">
                                <?php if ($katilimci_mi): ?>
                                    <button type="submit" name="ayril" class="btn btn-danger">
                                        <i class="fas fa-times"></i> Ayrıl
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="katil" class="btn btn-success" 
                                            <?php echo ($etkinlik_bilgi['kisi_limit'] > 0 && $katilimci_sayisi >= $etkinlik_bilgi['kisi_limit']) ? 'disabled' : ''; ?>>
                                        <i class="fas fa-plus"></i> Katıl
                                    </button>
                                <?php endif; ?>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Katılmak için Giriş Yapın
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Etkinlik Detayları -->
        <div class="row">
            <div class="col-md-8">
                <div class="card bg-dark border-secondary mb-4">
                    <div class="card-header bg-dark border-secondary">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-info-circle"></i> Etkinlik Detayları
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($etkinlik_bilgi['aciklama'])); ?></p>
                    </div>
                </div>
            </div>
            <!-- Katılımcılar -->
            <div class="col-md-4">
                <div class="card bg-dark border-secondary">
                    <div class="card-header bg-dark border-secondary">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-users"></i> Katılımcılar
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($katilimcilar)): ?>
                            <p class="text-secondary mb-0">Henüz katılımcı yok.</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($katilimcilar as $k): ?>
                                    <div class="list-group-item bg-dark border-secondary d-flex align-items-center">
                                        <div class="participant-avatar">
                                            <?php echo strtoupper(substr($k['kullanici_adi'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?php echo htmlspecialchars($k['kullanici_adi']); ?></div>
                                            <small class="text-secondary">
                                                <?php echo date('d.m.Y H:i', strtotime($k['katilim_tarihi'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Silme Modal -->
    <div class="modal fade" id="silModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Etkinliği Sil</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bu etkinliği silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.</p>
                    <?php if ($katilimci_sayisi > 0): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> Bu etkinliğe <?php echo $katilimci_sayisi; ?> katılımcı var. Etkinliği silmek için önce katılımcıların ayrılmasını beklemelisiniz.
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <?php if ($katilimci_sayisi == 0): ?>
                        <form method="POST" action="etkinlik_sil.php" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?php echo Session::csrfTokenOlustur(); ?>">
                            <input type="hidden" name="id" value="<?php echo $etkinlik_bilgi['id']; ?>">
                            <button type="submit" class="btn btn-danger" <?php echo $katilimci_sayisi > 0 ? 'disabled' : ''; ?>>
                                <i class="fas fa-trash"></i> Etkinliği Sil
                            </button>
                        </form>
                    <?php endif; ?>
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