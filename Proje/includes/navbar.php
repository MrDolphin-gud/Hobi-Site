<?php
require_once __DIR__ . '/../config/Session.php';
?>
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
                <?php if (Session::oturumVarMi()): ?>
                    <!-- Etkinlikler Menüsü -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-calendar"></i> Etkinlikler
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="etkinlikler.php">Tüm Etkinlikler</a></li>
                            <li><a class="dropdown-item" href="etkinlik_olustur.php">Etkinlik Oluştur</a></li>
                            <li><a class="dropdown-item" href="etkinliklerim.php">Etkinliklerim</a></li>
                            <li><a class="dropdown-item" href="katildigim_etkinlikler.php">Katıldığım Etkinlikler</a></li>
                        </ul>
                    </li>

                    <!-- Ekipmanlar Menüsü -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-tools"></i> Ekipmanlar
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="ekipmanlar.php">Tüm Ekipmanlar</a></li>
                            <li><a class="dropdown-item" href="ekipman_ekle.php">Ekipman Ekle</a></li>
                            <li><a class="dropdown-item" href="ekipman_taleplerim.php">Taleplerim</a></li>
                        </ul>
                    </li>

                    <!-- Forum Menüsü -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-comments"></i> Forum
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="forum.php">Forum Ana Sayfa</a></li>
                            <li><a class="dropdown-item" href="forum_yeni_konu.php">Yeni Konu Aç</a></li>
                            <li><a class="dropdown-item" href="forum_konularim.php">Konularım</a></li>
                            <?php if (Session::kullaniciBilgisiAl('rol') == 'admin'): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="forum_kategoriler.php">Kategorileri Yönet</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <?php if (Session::oturumVarMi()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars(Session::kullaniciBilgisiAl('ad_soyad')); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profil.php">Profilim</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Çıkış Yap</a></li>
                        </ul>
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