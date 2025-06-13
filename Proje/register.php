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
foreach ($config_files as $file) {
    $file_path = CONFIG_PATH . DIRECTORY_SEPARATOR . $file;
    if (!file_exists($file_path)) {
        die("Kritik dosya bulunamadı: " . $file);
    }
    require_once $file_path;
}
// Zaten giriş yapmış kullanıcıyı ana sayfaya yönlendir
if (Session::oturumVarMi()) {
    header('Location: index.php');
    exit;
}
$hata = '';
$basarili = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        Session::csrfTokenKontrol($_POST['csrf_token']);
        $kullanici_adi = trim($_POST['kullanici_adi'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $sifre = trim($_POST['sifre'] ?? '');
        $sifre_tekrar = trim($_POST['sifre_tekrar'] ?? '');
        $ad_soyad = trim($_POST['ad_soyad'] ?? '');
        // Validasyon
        if (empty($kullanici_adi)) {
            $hata = 'Kullanıcı adı gereklidir.';
        } elseif (empty($email)) {
            $hata = 'E-posta adresi gereklidir.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $hata = 'Geçerli bir e-posta adresi giriniz.';
        } elseif (empty($ad_soyad)) {
            $hata = 'Ad Soyad gereklidir.';
        } elseif (empty($sifre)) {
            $hata = 'Şifre gereklidir.';
        } elseif (strlen($sifre) < 6) {
            $hata = 'Şifre en az 6 karakter olmalıdır.';
        } elseif ($sifre !== $sifre_tekrar) {
            $hata = 'Şifreler eşleşmiyor.';
        } else {
            $db = Database::getInstance()->getConnection();
            // E-posta adresi kontrolü
            $stmt = $db->prepare('SELECT COUNT(*) FROM kullanicilar WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $hata = 'Bu e-posta adresi zaten kullanılıyor.';
            } else {
                // Kullanıcı adı kontrolü
                $stmt = $db->prepare('SELECT COUNT(*) FROM kullanicilar WHERE kullanici_adi = ?');
                $stmt->execute([$kullanici_adi]);
                if ($stmt->fetchColumn() > 0) {
                    $hata = 'Bu kullanıcı adı zaten kullanılıyor.';
                } else {
                    // Yeni kullanıcı kaydı
                    $stmt = $db->prepare('INSERT INTO kullanicilar (kullanici_adi, email, sifre, ad_soyad, kayit_tarihi) VALUES (?, ?, ?, ?, NOW())');
                    if ($stmt->execute([$kullanici_adi, $email, password_hash($sifre, PASSWORD_DEFAULT), $ad_soyad])) {
                        $basarili = 'Kayıt başarıyla tamamlandı! 2 saniye içinde giriş sayfasına yönlendirileceksiniz...';
                        // 2 saniye sonra giriş sayfasına yönlendir
                        header("refresh:2;url=login.php");
                        exit;
                    } else {
                        $hata = 'Kayıt sırasında bir hata oluştu.';
                    }
                }
            }
        }
    } catch (Exception $e) {
        $hata = 'Bir hata oluştu: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - Hobi Kulübü</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .register-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }
        .register-card {
            background: linear-gradient(45deg, #1a1a1a, #2d2d2d);
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
        }
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .register-header i {
            font-size: 3rem;
            color: #0d6efd;
            margin-bottom: 1rem;
        }
        .form-floating > label {
            color: #6c757d;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
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
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="fas fa-sign-in-alt"></i> Giriş Yap
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Kayıt Formu -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card bg-dark text-light border-secondary">
                    <div class="card-header bg-dark border-secondary">
                        <h4 class="mb-0">Kayıt Ol</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($basarili): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo $basarili; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        <?php if ($hata): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo $hata; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        <form method="POST" action="" class="needs-validation" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= Session::csrfTokenOlustur() ?>">
                            <div class="mb-3">
                                <label for="ad_soyad" class="form-label">Ad Soyad</label>
                                <input type="text" class="form-control bg-dark text-light border-secondary" id="ad_soyad" name="ad_soyad" required>
                            </div>
                            <div class="mb-3">
                                <label for="kullanici_adi" class="form-label">Kullanıcı Adı</label>
                                <input type="text" class="form-control bg-dark text-light border-secondary" id="kullanici_adi" name="kullanici_adi" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">E-posta</label>
                                <input type="email" class="form-control bg-dark text-light border-secondary" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="sifre" class="form-label">Şifre</label>
                                <input type="password" class="form-control bg-dark text-light border-secondary" id="sifre" name="sifre" required>
                            </div>
                            <div class="mb-3">
                                <label for="sifre_tekrar" class="form-label">Şifre Tekrar</label>
                                <input type="password" class="form-control bg-dark text-light border-secondary" id="sifre_tekrar" name="sifre_tekrar" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Kayıt Ol</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer bg-dark border-secondary text-center">
                        <p class="mb-0">Zaten hesabınız var mı? <a href="login.php" class="text-primary">Giriş Yap</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer -->
    <footer class="bg-dark text-light border-top border-secondary py-4">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Hobi Kulübü Yönetim Sistemi. Tüm hakları saklıdır.</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 