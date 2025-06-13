<?php
require_once 'config/database.php';
require_once 'config/Session.php';

Session::oturumKontrol();
$db = Database::getInstance()->getConnection();
$mesaj = '';
$hata = '';
// Kategori ekleme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ekle'])) {
    try {
        Session::csrfTokenKontrol($_POST['csrf_token']);
        $ad = trim($_POST['ad']);
        $aciklama = trim($_POST['aciklama']);
        $kategori_id = (int)$_POST['kategori_id'];
        if (empty($ad)) {
            $hata = 'Kategori adı boş olamaz.';
        } else {
            $stmt = $db->prepare("INSERT INTO forum_kategorileri (ad, aciklama, kategori_id) VALUES (:ad, :aciklama, :kategori_id)");
            $stmt->bindParam(':ad', $ad);
            $stmt->bindParam(':aciklama', $aciklama);
            $stmt->bindParam(':kategori_id', $kategori_id);
            if ($stmt->execute()) {
                $mesaj = 'Kategori başarıyla eklendi.';
                // Formu temizle
                $_POST['ad'] = '';
                $_POST['aciklama'] = '';
                $_POST['kategori_id'] = '';
            } else {
                throw new Exception('Kategori eklenirken bir hata oluştu.');
            }
        }
    } catch (Exception $e) {
        $hata = 'Bir hata oluştu: ' . $e->getMessage();
    }
}
// Kategori düzenleme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['duzenle'])) {
    try {
        Session::csrfTokenKontrol($_POST['csrf_token']);
        $kategori_id = (int)$_POST['kategori_id'];
        $ad = trim($_POST['ad']);
        $aciklama = trim($_POST['aciklama']);
        $hobi_kategori_id = (int)$_POST['hobi_kategori_id'];
        
        if (empty($ad)) {
            $hata = 'Kategori adı boş olamaz.';
        } else {
            $stmt = $db->prepare("UPDATE forum_kategorileri 
                                 SET ad = :ad, aciklama = :aciklama, kategori_id = :hobi_kategori_id 
                                 WHERE id = :id");
            $stmt->bindParam(':id', $kategori_id);
            $stmt->bindParam(':ad', $ad);
            $stmt->bindParam(':aciklama', $aciklama);
            $stmt->bindParam(':hobi_kategori_id', $hobi_kategori_id);
            if ($stmt->execute()) {
                $mesaj = 'Kategori başarıyla güncellendi.';
            } else {
                throw new Exception('Kategori güncellenirken bir hata oluştu.');
            }
        }
    } catch (Exception $e) {
        $hata = 'Bir hata oluştu: ' . $e->getMessage();
    }
}
// Kategori silme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sil'])) {
    try {
        Session::csrfTokenKontrol($_POST['csrf_token']);
        $kategori_id = (int)$_POST['kategori_id'];   
        $db->beginTransaction();
        
        // Önce bu kategoriye ait konuları kontrol et
        $stmt = $db->prepare("SELECT COUNT(*) as konu_sayisi, 
                             (SELECT GROUP_CONCAT(baslik SEPARATOR ', ') 
                              FROM forum_konulari 
                              WHERE kategori_id = :kategori_id 
                              LIMIT 5) as son_konular
                             FROM forum_konulari 
                             WHERE kategori_id = :kategori_id");
        $stmt->bindParam(':kategori_id', $kategori_id);
        $stmt->execute();
        $konu_bilgisi = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($konu_bilgisi['konu_sayisi'] > 0) {
            $son_konular = $konu_bilgisi['son_konular'] ? 
                          'Son konular: ' . $konu_bilgisi['son_konular'] : '';
            throw new Exception("Bu kategoriye ait {$konu_bilgisi['konu_sayisi']} konu bulunuyor. " . $son_konular);
        }
        
        // Alt kategorileri kontrol et
        $stmt = $db->prepare("SELECT COUNT(*) as alt_kategori_sayisi,
                             (SELECT GROUP_CONCAT(ad SEPARATOR ', ') 
                              FROM forum_kategorileri 
                              WHERE kategori_id = :kategori_id 
                              LIMIT 5) as alt_kategoriler
                             FROM forum_kategorileri 
                             WHERE kategori_id = :kategori_id");
        $stmt->bindParam(':kategori_id', $kategori_id);
        $stmt->execute();
        $alt_kategori_bilgisi = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($alt_kategori_bilgisi['alt_kategori_sayisi'] > 0) {
            $alt_kategoriler = $alt_kategori_bilgisi['alt_kategoriler'] ? 
                              'Alt kategoriler: ' . $alt_kategori_bilgisi['alt_kategoriler'] : '';
            throw new Exception("Bu kategoriye ait {$alt_kategori_bilgisi['alt_kategori_sayisi']} alt kategori bulunuyor. " . $alt_kategoriler);
        }
        
        // Kategoriyi sil
        $stmt = $db->prepare("DELETE FROM forum_kategorileri WHERE id = :id");
        $stmt->bindParam(':id', $kategori_id);
        $stmt->execute();
        $db->commit();
        $mesaj = 'Kategori başarıyla silindi.';
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Forum kategori silme hatası: " . $e->getMessage());
        $hata = 'Bir hata oluştu: ' . $e->getMessage();
    }
}

try {
    // Hobi kategorilerini getir
    $stmt = $db->query("SELECT * FROM hobi_kategorileri ORDER BY ad");
    $hobi_kategorileri = $stmt->fetchAll();

    // Forum kategorilerini getir
    $stmt = $db->query("SELECT f.*, h.ad as hobi_adi,
                       (SELECT COUNT(*) FROM forum_konulari WHERE kategori_id = f.id) as konu_sayisi
                       FROM forum_kategorileri f
                       JOIN hobi_kategorileri h ON f.kategori_id = h.id
                       ORDER BY h.ad, f.ad");
    $forum_kategorileri = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Forum kategorileri getirme hatası: " . $e->getMessage());
    $hata = 'Kategoriler yüklenirken bir hata oluştu.';
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum Kategorileri - Hobi Kulübü Forum</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
        <div class="row">
            <!-- Kategori Ekleme Formu -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Yeni Kategori Ekle</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" class="needs-validation" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= Session::csrfTokenOlustur() ?>">
                            <div class="mb-3">
                                <label for="kategori_id" class="form-label">Hobi Kategorisi</label>
                                <select class="form-select" id="kategori_id" name="kategori_id" required>
                                    <option value="">Seçiniz...</option>
                                    <?php foreach ($hobi_kategorileri as $hobi): ?>
                                        <option value="<?= $hobi['id'] ?>" <?= isset($_POST['kategori_id']) && $_POST['kategori_id'] == $hobi['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($hobi['ad']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="ad" class="form-label">Kategori Adı</label>
                                <input type="text" class="form-control" id="ad" name="ad" 
                                       value="<?= htmlspecialchars($_POST['ad'] ?? '') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="aciklama" class="form-label">Açıklama</label>
                                <textarea class="form-control" id="aciklama" name="aciklama" rows="3"><?= htmlspecialchars($_POST['aciklama'] ?? '') ?></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="ekle" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Kategori Ekle
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Kategori Listesi -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Forum Kategorileri</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($forum_kategorileri)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Henüz hiç forum kategorisi eklenmemiş.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Kategori Adı</th>
                                            <th>Hobi</th>
                                            <th>Açıklama</th>
                                            <th>Konular</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($forum_kategorileri as $kategori): ?>
                                            <tr>
                                                <td>
                                                    <form method="POST" action="" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?= Session::csrfTokenOlustur() ?>">
                                                        <input type="hidden" name="kategori_id" value="<?= $kategori['id'] ?>">
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" name="ad" value="<?= htmlspecialchars($kategori['ad']) ?>" required>
                                                            <button type="submit" name="duzenle" class="btn btn-primary btn-sm" title="Düzenle">
                                                                <i class="fas fa-save"></i>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </td>
                                                <td>
                                                    <select class="form-select" name="hobi_kategori_id">
                                                        <?php foreach ($hobi_kategorileri as $hobi): ?>
                                                            <option value="<?= $hobi['id'] ?>" <?= $hobi['id'] == $kategori['kategori_id'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($hobi['ad']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <textarea class="form-control" name="aciklama" rows="2"><?= htmlspecialchars($kategori['aciklama']) ?></textarea>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary"><?= $kategori['konu_sayisi'] ?></span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <form method="POST" action="" class="d-inline" 
                                                              onsubmit="return confirm('Bu kategoriyi silmek istediğinizden emin misiniz?');">
                                                            <input type="hidden" name="csrf_token" value="<?= Session::csrfTokenOlustur() ?>">
                                                            <input type="hidden" name="kategori_id" value="<?= $kategori['id'] ?>">
                                                            <button type="submit" name="sil" class="btn btn-danger" title="Sil">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 