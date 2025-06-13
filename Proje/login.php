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
session_start();
// Zaten giriş yapmış kullanıcıyı ana sayfaya yönlendir
if (Session::oturumVarMi()) {
    header('Location: index.php');
    exit;
}
$hata = '';
$basarili = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $sifre = trim($_POST['sifre'] ?? '');
    if (empty($email)) {
        $hata = 'E-posta adresi gereklidir.';
    } elseif (empty($sifre)) {
        $hata = 'Şifre gereklidir.';
    } else {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT * FROM kullanicilar WHERE email = ?');
        $stmt->execute([$email]);
        $kullanici = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($kullanici && password_verify($sifre, $kullanici['sifre'])) {
            // Giriş başarılı, oturum bilgilerini kaydet
            Session::oturumAc($kullanici['id'], $kullanici['kullanici_adi'], $kullanici['ad_soyad']);
            $basarili = 'Giriş başarılı! Yönlendiriliyorsunuz...';
            // 2 saniye sonra yönlendir
            header("refresh:2;url=index.php");
            exit;
        } else {
            $hata = 'E-posta adresi veya şifre hatalı.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - Hobi Kulübü</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .login-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }
        .login-card {
            background: linear-gradient(45deg, #1a1a1a, #2d2d2d);
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header i {
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
                        <a class="nav-link" href="register.php">
                            <i class="fas fa-user-plus"></i> Kayıt Ol
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Giriş Formu -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card bg-dark text-light border-secondary">
                    <div class="card-header bg-dark border-secondary">
                        <h4 class="mb-0">Giriş Yap</h4>
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
                                <label for="email" class="form-label">E-posta</label>
                                <input type="email" class="form-control bg-dark text-light border-secondary" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="sifre" class="form-label">Şifre</label>
                                <input type="password" class="form-control bg-dark text-light border-secondary" id="sifre" name="sifre" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Giriş Yap</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer bg-dark border-secondary text-center">
                        <p class="mb-0">Hesabınız yok mu? <a href="register.php" class="text-primary">Kayıt Ol</a></p>
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