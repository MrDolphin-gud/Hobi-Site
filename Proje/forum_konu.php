<?php
require_once 'config/database.php';
require_once 'config/Session.php';

Session::oturumKontrol();
$db = Database::getInstance()->getConnection();
$mesaj = '';
$hata = '';
$konu = null;
// Konu ID kontrolü
$konu_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($konu_id <= 0) {
    header('Location: forum.php');
    exit();
}
// Konu bilgilerini getir ve görüntülenme sayısını artır
try {
    $db->beginTransaction();
    // Görüntülenme sayısını artır
    $stmt = $db->prepare("UPDATE forum_konulari SET goruntulenme = goruntulenme + 1 WHERE id = :id");
    $stmt->bindParam(':id', $konu_id);
    $stmt->execute();
    // Konu bilgilerini getir
    $stmt = $db->prepare("SELECT k.*, f.ad as kategori_adi, h.ad as hobi_adi, u.kullanici_adi,
                         (SELECT COUNT(*) FROM forum_yorumlari WHERE konu_id = k.id) as yorum_sayisi
                         FROM forum_konulari k
                         JOIN forum_kategorileri f ON k.kategori_id = f.id
                         JOIN hobi_kategorileri h ON f.kategori_id = h.id
                         JOIN kullanicilar u ON k.kullanici_id = u.id
                         WHERE k.id = :id");
    $stmt->bindParam(':id', $konu_id);
    $stmt->execute();
    $konu = $stmt->fetch();
    if (!$konu) {
        throw new Exception('Konu bulunamadı.');
    }
    $db->commit();
} catch (Exception $e) {
    $db->rollBack();
    error_log("Forum konu görüntüleme hatası: " . $e->getMessage());
    $hata = 'Bir hata oluştu: ' . $e->getMessage();
    header('Location: forum.php');
    exit();
}
// Yorum ekleme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['yorum_ekle'])) {
    try {
        Session::csrfTokenKontrol($_POST['csrf_token']);
        $icerik = trim($_POST['icerik'] ?? '');
        $kullanici_id = Session::kullaniciBilgisiAl('kullanici_id');
        
        if (empty($icerik)) {
            throw new Exception('Yorum içeriği boş olamaz.');
        }
        
        $stmt = $db->prepare("INSERT INTO forum_yorumlari (konu_id, kullanici_id, icerik, olusturma_tarihi) 
                             VALUES (:konu_id, :kullanici_id, :icerik, NOW())");
        $stmt->bindParam(':konu_id', $konu_id);
        $stmt->bindParam(':kullanici_id', $kullanici_id);
        $stmt->bindParam(':icerik', $icerik);
        
        if ($stmt->execute()) {
            // Konunun son güncelleme tarihini güncelle
            $stmt = $db->prepare("UPDATE forum_konulari SET son_guncelleme = NOW() WHERE id = :id");
            $stmt->bindParam(':id', $konu_id);
            $stmt->execute();
            
            $mesaj = 'Yorumunuz başarıyla eklendi.';
            header("Location: forum_konu.php?id=" . $konu_id . "#yorumlar");
            exit();
        } else {
            throw new Exception('Yorum eklenirken bir hata oluştu.');
        }
    } catch (Exception $e) {
        error_log("Forum yorum ekleme hatası: " . $e->getMessage());
        $hata = $e->getMessage();
    }
}
// Yorum silme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['yorum_id']) && isset($_POST['sil'])) {
    try {
        Session::csrfTokenKontrol($_POST['csrf_token']);
        $yorum_id = (int)$_POST['yorum_id'];
        $kullanici_id = Session::kullaniciBilgisiAl('kullanici_id');
        $db->beginTransaction();
        // Önce yorumun kullanıcıya ait olup olmadığını kontrol et
        $stmt = $db->prepare("SELECT id FROM forum_yorumlari WHERE id = :id AND kullanici_id = :kullanici_id");
        $stmt->bindParam(':id', $yorum_id);
        $stmt->bindParam(':kullanici_id', $kullanici_id);
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            throw new Exception('Bu yorumu silme yetkiniz yok.');
        }
        // Yorumu sil
        $stmt = $db->prepare("DELETE FROM forum_yorumlari WHERE id = :id");
        $stmt->bindParam(':id', $yorum_id);
        $stmt->execute();
        // Konunun son güncelleme tarihini güncelle
        $stmt = $db->prepare("UPDATE forum_konulari SET son_guncelleme = NOW() WHERE id = :konu_id");
        $stmt->bindParam(':konu_id', $konu_id);
        $stmt->execute();
        $db->commit();
        $mesaj = 'Yorum başarıyla silindi.';
    } catch (Exception $e) {
        $db->rollBack();
        $hata = 'Bir hata oluştu: ' . $e->getMessage();
    }
}
// Yorumları getir
try {
    $stmt = $db->prepare("SELECT y.*, u.kullanici_adi, u.profil_resmi
                         FROM forum_yorumlari y
                         JOIN kullanicilar u ON y.kullanici_id = u.id
                         WHERE y.konu_id = :konu_id
                         ORDER BY y.olusturma_tarihi ASC");
    $stmt->bindParam(':konu_id', $konu_id);
    $stmt->execute();
    $yorumlar = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Forum yorumları getirme hatası: " . $e->getMessage());
    $hata = 'Yorumlar yüklenirken bir hata oluştu.';
    $yorumlar = [];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($konu['baslik']) ?> - Hobi Kulübü Forum</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- TinyMCE -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js"></script>
    <script>
        tinymce.init({
            selector: '#yorum',
            plugins: 'link lists',
            toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | link',
            height: 200
        });
    </script>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container mt-4">
        <?php if ($mesaj): ?>
            <div class="alert alert-success"><?= $mesaj ?></div>
        <?php endif; ?>
        <?php if ($hata): ?>
            <div class="alert alert-danger"><?= $hata ?></div>
        <?php endif; ?>
        <!-- Konu Başlığı ve Bilgiler -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0"><?= htmlspecialchars($konu['baslik']) ?></h3>
                    <div>
                        <a href="forum.php" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Foruma Dön
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <span class="badge bg-primary me-2">
                            <i class="fas fa-folder"></i> <?= htmlspecialchars($konu['kategori_adi']) ?>
                        </span>
                        <span class="badge bg-info me-2">
                            <i class="fas fa-tag"></i> <?= htmlspecialchars($konu['hobi_adi']) ?>
                        </span>
                        <span class="badge bg-secondary">
                            <i class="fas fa-eye"></i> <?= $konu['goruntulenme'] ?> görüntülenme
                        </span>
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-user"></i> <?= htmlspecialchars($konu['kullanici_adi']) ?> tarafından
                        <?= date('d.m.Y H:i', strtotime($konu['olusturma_tarihi'])) ?> tarihinde açıldı
                    </small>
                </div>
                <div class="forum-content">
                    <?= $konu['icerik'] ?>
                </div>
            </div>
        </div>
        <!-- Yorumlar -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    Yorumlar
                    <span class="badge bg-primary ms-2"><?= count($yorumlar) ?></span>
                </h4>
            </div>
            <div class="card-body">
                <?php if (empty($yorumlar)): ?>
                    <p class="text-muted text-center">Henüz yorum yapılmamış. İlk yorumu siz yapın!</p>
                <?php else: ?>
                    <?php foreach ($yorumlar as $yorum): ?>
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <i class="fas fa-user"></i> <?= htmlspecialchars($yorum['kullanici_adi']) ?>
                                    <small class="text-muted ms-2">
                                        <?= date('d.m.Y H:i', strtotime($yorum['olusturma_tarihi'])) ?>
                                    </small>
                                </div>
                                <?php if ($yorum['kullanici_id'] == Session::kullaniciBilgisiAl('kullanici_id')): ?>
                                    <form method="POST" action="" class="d-inline" 
                                          onsubmit="return confirm('Bu yorumu silmek istediğinizden emin misiniz?');">
                                        <input type="hidden" name="csrf_token" value="<?= Session::csrfTokenOlustur() ?>">
                                        <input type="hidden" name="yorum_id" value="<?= $yorum['id'] ?>">
                                        <button type="submit" name="sil" class="btn btn-danger btn-sm" title="Yorumu Sil">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <div class="forum-content">
                                <?= htmlspecialchars($yorum['icerik']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <!-- Yorum Formu -->
                <form method="POST" action="" class="mt-4">
                    <input type="hidden" name="csrf_token" value="<?= Session::csrfTokenOlustur() ?>">
                    <div class="mb-3">
                        <label for="yorum" class="form-label">Yorum Yap</label>
                        <textarea class="form-control" id="yorum" name="icerik" required><?= htmlspecialchars($_POST['icerik'] ?? '') ?></textarea>
                    </div>
                    <div class="d-grid">
                        <button type="submit" name="yorum_ekle" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Yorumu Gönder
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .forum-content {
            line-height: 1.6;
        }
        .forum-content img {
            max-width: 100%;
            height: auto;
        }
    </style>
</body>
</html> 