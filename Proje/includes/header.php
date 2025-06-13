<?php
// Hata raporlamayı etkinleştir
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Dosya yollarını tanımla
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'config');
define('MODELS_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'models');
// Dosya adlarını küçük harfe çevir
$config_files = [
    'database.php',
    'Session.php'
];
// Gerekli dosyaları yükle
foreach ($config_files as $file) {
    $file_path = CONFIG_PATH . DIRECTORY_SEPARATOR . $file;
    if (!file_exists($file_path)) {
        die("Kritik dosya bulunamadı: " . $file);
    }
    require_once $file_path;
}
// Models dosyasını yükle
$model_file = MODELS_PATH . DIRECTORY_SEPARATOR . 'Kullanici.php';
if (!file_exists($model_file)) {
    die("Kritik dosya bulunamadı: Kullanici.php");
}
require_once $model_file;
// Oturumu başlat
Session::baslat();
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hobi Kulübü Yönetim Sistemi</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #212529;
            color: #f8f9fa;
        }
        .card {
            background-color: #212529;
            border-color: #495057;
        }
        .card-header {
            background-color: #2c3034;
            border-color: #495057;
        }
        .card-footer {
            background-color: #2c3034;
            border-color: #495057;
        }
        .list-group-item {
            background-color: #212529;
            border-color: #495057;
            color: #f8f9fa;
        }
        .list-group-item:hover {
            background-color: #2c3034;
        }
        .list-group-item.active {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .form-control {
            background-color: #2c3034;
            border-color: #495057;
            color: #f8f9fa;
        }
        .form-control:focus {
            background-color: #2c3034;
            border-color: #0d6efd;
            color: #f8f9fa;
        }
        .form-control::placeholder {
            color: #adb5bd;
        }
        .btn-outline-secondary {
            color: #f8f9fa;
            border-color: #495057;
        }
        .btn-outline-secondary:hover {
            background-color: #495057;
            border-color: #495057;
            color: #f8f9fa;
        }
        .text-muted {
            color: #adb5bd !important;
        }
        .alert {
            background-color: #2c3034;
            border-color: #495057;
        }
        .alert-danger {
            background-color: #2c1f1f;
            border-color: #842029;
            color: #ea868f;
        }
        .alert-success {
            background-color: #1f2c1f;
            border-color: #0f5132;
            color: #75b798;
        }
        .modal-content {
            background-color: #212529;
            border-color: #495057;
        }
        .modal-header {
            background-color: #2c3034;
            border-color: #495057;
        }
        .modal-footer {
            background-color: #2c3034;
            border-color: #495057;
        }
        .dropdown-menu {
            background-color: #212529;
            border-color: #495057;
        }
        .dropdown-item {
            color: #f8f9fa;
        }
        .dropdown-item:hover {
            background-color: #2c3034;
            color: #f8f9fa;
        }
        .nav-link {
            color: #f8f9fa;
        }
        .nav-link:hover {
            color: #adb5bd;
        }
        .navbar {
            background-color: #212529 !important;
        }
        .navbar-brand {
            color: #f8f9fa !important;
        }
        .table {
            color: #f8f9fa;
        }
        .table-striped > tbody > tr:nth-of-type(odd) {
            background-color: #2c3034;
        }
        .table-hover tbody tr:hover {
            background-color: #343a40;
        }
        .pagination .page-link {
            background-color: #212529;
            border-color: #495057;
            color: #f8f9fa;
        }
        .pagination .page-link:hover {
            background-color: #2c3034;
            border-color: #495057;
            color: #f8f9fa;
        }
        .pagination .active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">Hobi Kulübü</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Ana Sayfa</a>
                    </li>
                    <?php if (Session::oturumVarMi()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="etkinlikler.php">Etkinlikler</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="etkinlik_olustur.php">Yeni Etkinlik</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="ekipmanDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-tools"></i> Ekipmanlar
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="ekipmanDropdown">
                            <li><a class="dropdown-item" href="ekipmanlar.php">Tüm Ekipmanlar</a></li>
                            <li><a class="dropdown-item" href="ekipman_ekle.php">Ekipman Ekle</a></li>
                            <li><a class="dropdown-item" href="ekipman_taleplerim.php">Taleplerim</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="forumDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-comments"></i> Forum
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="forumDropdown">
                            <li><a class="dropdown-item" href="forum.php">Forum Ana Sayfa</a></li>
                            <li><a class="dropdown-item" href="forum_yeni_konu.php">Yeni Konu Aç</a></li>
                            <li><a class="dropdown-item" href="forum_konularim.php">Konularım</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (Session::oturumVarMi()): ?>
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars(Session::kullaniciBilgisiAl('ad_soyad')); ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Çıkış Yap</a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Giriş Yap</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Kayıt Ol</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container"> 