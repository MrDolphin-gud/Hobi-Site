<?php
require_once 'config/database.php';
require_once 'config/Session.php';
Session::oturumKontrol();
$db = Database::getInstance()->getConnection();
$kullanici_id = Session::kullaniciBilgisiAl('kullanici_id');
$mesaj = '';
$hata = '';
// Talep durumu güncelleme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['talep_id'], $_POST['durum'])) {
    try {
        Session::csrfTokenKontrol($_POST['csrf_token']);
        $talep_id = (int)$_POST['talep_id'];
        $yeni_durum = $_POST['durum'];
        // Geçerli durum kontrolü
        $gecerli_durumlar = ['onaylandi', 'reddedildi', 'tamamlandi', 'iptal'];
        if (!in_array($yeni_durum, $gecerli_durumlar)) {
            throw new Exception('Geçersiz durum değeri.');
        }
        $db->beginTransaction();
        // Talebin mevcut durumunu ve yetkiyi kontrol et
        $stmt = $db->prepare("SELECT o.*, e.durum as ekipman_durum 
                             FROM ekipman_odunc o 
                             JOIN ekipmanlar e ON o.ekipman_id = e.id 
                             WHERE o.id = :id AND 
                             (o.odunc_veren_id = :kullanici_id OR o.odunc_alan_id = :kullanici_id)");
        $stmt->bindParam(':id', $talep_id);
        $stmt->bindParam(':kullanici_id', $kullanici_id);
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            throw new Exception('Bu talebi güncelleme yetkiniz yok.');
        }
        $talep = $stmt->fetch();
        // Durum değişikliği kontrolleri
        if ($yeni_durum == 'onaylandi' && $talep['odunc_veren_id'] != $kullanici_id) {
            throw new Exception('Sadece ekipman sahibi onaylayabilir.');
        }
        if ($yeni_durum == 'tamamlandi' && $talep['odunc_alan_id'] != $kullanici_id) {
            throw new Exception('Sadece ödünç alan kişi tamamlandı olarak işaretleyebilir.');
        }
        if ($yeni_durum == 'iptal' && $talep['odunc_alan_id'] != $kullanici_id) {
            throw new Exception('Sadece ödünç alan kişi talebi iptal edebilir.');
        }
        // Talebi güncelle
        $stmt = $db->prepare("UPDATE ekipman_odunc SET durum = :durum WHERE id = :id");
        $stmt->bindParam(':durum', $yeni_durum);
        $stmt->bindParam(':id', $talep_id);
        if (!$stmt->execute()) {
            throw new Exception('Talep güncellenirken bir hata oluştu.');
        }
        // Ekipman durumunu güncelle
        if ($yeni_durum == 'onaylandi') {
            $stmt = $db->prepare("UPDATE ekipmanlar SET durum = 'ödünç_verildi' WHERE id = :ekipman_id");
            $stmt->bindParam(':ekipman_id', $talep['ekipman_id']);
            $stmt->execute();
        } elseif ($yeni_durum == 'tamamlandi' || $yeni_durum == 'reddedildi') {
            $stmt = $db->prepare("UPDATE ekipmanlar SET durum = 'müsait' WHERE id = :ekipman_id");
            $stmt->bindParam(':ekipman_id', $talep['ekipman_id']);
            $stmt->execute();
        }
        $db->commit();
        $mesaj = 'Talep durumu başarıyla güncellendi.';
    } catch (Exception $e) {
        $db->rollBack();
        $hata = 'Bir hata oluştu: ' . $e->getMessage();
    }
}
// Gelen talepler (ekipman sahibi olarak)
$stmt = $db->prepare("SELECT o.*, e.ad as ekipman_adi, k.kullanici_adi as talep_eden,
                     (SELECT kullanici_adi FROM kullanicilar WHERE id = o.odunc_veren_id) as ekipman_sahibi
                     FROM ekipman_odunc o
                     JOIN ekipmanlar e ON o.ekipman_id = e.id
                     JOIN kullanicilar k ON o.odunc_alan_id = k.id
                     WHERE o.odunc_veren_id = :kullanici_id
                     ORDER BY o.created_at DESC");
$stmt->bindParam(':kullanici_id', $kullanici_id);
$stmt->execute();
$gelen_talepler = $stmt->fetchAll();
// Giden talepler (ödünç alan olarak)
$stmt = $db->prepare("SELECT o.*, e.ad as ekipman_adi, k.kullanici_adi as ekipman_sahibi
                     FROM ekipman_odunc o
                     JOIN ekipmanlar e ON o.ekipman_id = e.id
                     JOIN kullanicilar k ON o.odunc_veren_id = k.id
                     WHERE o.odunc_alan_id = :kullanici_id
                     ORDER BY o.created_at DESC");
$stmt->bindParam(':kullanici_id', $kullanici_id);
$stmt->execute();
$giden_talepler = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ekipman Taleplerim - Hobi Kulübü</title>
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
            <!-- Gelen Talepler -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Gelen Talepler</h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($gelen_talepler)): ?>
                            <p class="text-muted">Henüz gelen talep bulunmuyor.</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($gelen_talepler as $talep): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h5 class="mb-1"><?= htmlspecialchars($talep['ekipman_adi']) ?></h5>
                                            <small class="text-muted">
                                                <?= date('d.m.Y H:i', strtotime($talep['created_at'])) ?>
                                            </small>
                                        </div>
                                        <p class="mb-1">
                                            Talep Eden: <?= htmlspecialchars($talep['talep_eden']) ?><br>
                                            Tarih: <?= date('d.m.Y', strtotime($talep['baslangic_tarihi'])) ?> - 
                                                  <?= date('d.m.Y', strtotime($talep['bitis_tarihi'])) ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-<?= $talep['durum'] == 'beklemede' ? 'warning' : 
                                                ($talep['durum'] == 'onaylandi' ? 'success' : 
                                                ($talep['durum'] == 'reddedildi' ? 'danger' : 'info')) ?>">
                                                <?= ucfirst(str_replace('_', ' ', $talep['durum'])) ?>
                                            </span>
                                            <?php if ($talep['durum'] == 'beklemede'): ?>
                                                <form method="POST" action="" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= Session::csrfTokenOlustur() ?>">
                                                    <input type="hidden" name="talep_id" value="<?= $talep['id'] ?>">
                                                    <input type="hidden" name="durum" value="onaylandi">
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        <i class="fas fa-check"></i> Onayla
                                                    </button>
                                                </form>
                                                <form method="POST" action="" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= Session::csrfTokenOlustur() ?>">
                                                    <input type="hidden" name="talep_id" value="<?= $talep['id'] ?>">
                                                    <input type="hidden" name="durum" value="reddedildi">
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-times"></i> Reddet
                                                    </button>
                                                </form>
                                            <?php elseif ($talep['durum'] == 'onaylandi' && strtotime($talep['bitis_tarihi']) <= time()): ?>
                                                <form method="POST" action="" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= Session::csrfTokenOlustur() ?>">
                                                    <input type="hidden" name="talep_id" value="<?= $talep['id'] ?>">
                                                    <input type="hidden" name="durum" value="tamamlandi">
                                                    <button type="submit" class="btn btn-info btn-sm">
                                                        <i class="fas fa-check-double"></i> Tamamlandı
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- Giden Talepler -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Giden Talepler</h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($giden_talepler)): ?>
                            <p class="text-muted">Henüz giden talep bulunmuyor.</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($giden_talepler as $talep): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h5 class="mb-1"><?= htmlspecialchars($talep['ekipman_adi']) ?></h5>
                                            <small class="text-muted">
                                                <?= date('d.m.Y H:i', strtotime($talep['created_at'])) ?>
                                            </small>
                                        </div>
                                        <p class="mb-1">
                                            Ekipman Sahibi: <?= htmlspecialchars($talep['ekipman_sahibi']) ?><br>
                                            Tarih: <?= date('d.m.Y', strtotime($talep['baslangic_tarihi'])) ?> - 
                                                  <?= date('d.m.Y', strtotime($talep['bitis_tarihi'])) ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-<?= $talep['durum'] == 'beklemede' ? 'warning' : 
                                                ($talep['durum'] == 'onaylandi' ? 'success' : 
                                                ($talep['durum'] == 'reddedildi' ? 'danger' : 'info')) ?>">
                                                <?= ucfirst(str_replace('_', ' ', $talep['durum'])) ?>
                                            </span>
                                            <?php if ($talep['durum'] == 'beklemede'): ?>
                                                <form method="POST" action="" class="d-inline">
                                                    <input type="hidden" name="talep_id" value="<?= $talep['id'] ?>">
                                                    <input type="hidden" name="durum" value="iptal">
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-times"></i> İptal Et
                                                    </button>
                                                </form>
                                            <?php endif; ?>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
