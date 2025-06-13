<?php
require_once 'config/database.php';
require_once 'config/Session.php';
Session::oturumKontrol();
$db = Database::getInstance()->getConnection();
$mesaj = '';
$hata = '';
$ekipman = null;
// Ekipman ID kontrolü
$ekipman_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($ekipman_id <= 0) {
    header('Location: ekipmanlar.php');
    exit();
}
// Ekipman bilgilerini getir
$stmt = $db->prepare("SELECT e.*, k.kullanici_adi, h.ad as hobi_adi 
                      FROM ekipmanlar e 
                      JOIN kullanicilar k ON e.kullanici_id = k.id 
                      JOIN hobi_kategorileri h ON e.kategori_id = h.id 
                      WHERE e.id = :id AND e.durum = 'müsait'");
$stmt->bindParam(':id', $ekipman_id);
$stmt->execute();
$ekipman = $stmt->fetch();
if (!$ekipman) {
    header('Location: ekipmanlar.php');
    exit();
}
// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $baslangic_tarihi = trim($_POST['baslangic_tarihi'] ?? '');
    $bitis_tarihi = trim($_POST['bitis_tarihi'] ?? '');
    $odunc_alan_id = Session::kullaniciBilgisiAl('kullanici_id');
    $odunc_veren_id = $ekipman['kullanici_id'];
    if (empty($baslangic_tarihi) || empty($bitis_tarihi)) {
        $hata = 'Lütfen tarih aralığını belirtin.';
    } elseif (strtotime($baslangic_tarihi) >= strtotime($bitis_tarihi)) {
        $hata = 'Bitiş tarihi başlangıç tarihinden sonra olmalıdır.';
    } elseif (strtotime($baslangic_tarihi) < strtotime('today')) {
        $hata = 'Başlangıç tarihi bugünden önce olamaz.';
    } else {
        try {
            $db->beginTransaction();
            // Ödünç alma kaydı oluştur
            $stmt = $db->prepare("INSERT INTO ekipman_odunc (ekipman_id, odunc_alan_id, odunc_veren_id, 
                                 baslangic_tarihi, bitis_tarihi, durum) 
                                 VALUES (:ekipman_id, :odunc_alan_id, :odunc_veren_id, 
                                 :baslangic_tarihi, :bitis_tarihi, 'beklemede')");
            $stmt->bindParam(':ekipman_id', $ekipman_id);
            $stmt->bindParam(':odunc_alan_id', $odunc_alan_id);
            $stmt->bindParam(':odunc_veren_id', $odunc_veren_id);
            $stmt->bindParam(':baslangic_tarihi', $baslangic_tarihi);
            $stmt->bindParam(':bitis_tarihi', $bitis_tarihi);
            if ($stmt->execute()) {
                $db->commit();
                $mesaj = 'Ödünç alma talebi başarıyla oluşturuldu. Ekipman sahibi onayladıktan sonra size bilgi verilecektir.';
            } else {
                throw new Exception('Ödünç alma talebi oluşturulamadı.');
            }
        } catch (Exception $e) {
            $db->rollBack();
            $hata = 'Bir hata oluştu: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ekipman Ödünç Al - Hobi Kulübü</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Ekipman Ödünç Al</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($mesaj): ?>
                            <div class="alert alert-success"><?= $mesaj ?></div>
                        <?php endif; ?>
                        <?php if ($hata): ?>
                            <div class="alert alert-danger"><?= $hata ?></div>
                        <?php endif; ?>
                        <div class="mb-4">
                            <h4><?= htmlspecialchars($ekipman['ad']) ?></h4>
                            <p class="text-muted">
                                Kategori: <?= htmlspecialchars($ekipman['hobi_adi']) ?><br>
                                Sahip: <?= htmlspecialchars($ekipman['kullanici_adi']) ?>
                            </p>
                            <?php if ($ekipman['aciklama']): ?>
                                <div class="alert alert-info">
                                    <?= nl2br(htmlspecialchars($ekipman['aciklama'])) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="baslangic_tarihi" class="form-label">Başlangıç Tarihi *</label>
                                <input type="date" class="form-control" id="baslangic_tarihi" name="baslangic_tarihi" 
                                       min="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="bitis_tarihi" class="form-label">Bitiş Tarihi *</label>
                                <input type="date" class="form-control" id="bitis_tarihi" name="bitis_tarihi" 
                                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                            </div>
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle"></i> Önemli Bilgiler:
                                <ul class="mb-0 mt-2">
                                    <li>Ödünç alma talebiniz ekipman sahibi tarafından onaylanmalıdır.</li>
                                    <li>Ekipmanı zamanında ve iyi durumda iade etmeyi unutmayın.</li>
                                    <li>Herhangi bir hasar durumunda ekipman sahibiyle iletişime geçin.</li>
                                </ul>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-handshake"></i> Ödünç Alma Talebi Oluştur
                                </button>
                                <a href="ekipmanlar.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Geri Dön
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tarih seçicilerin birbirini etkilemesi
        document.getElementById('baslangic_tarihi').addEventListener('change', function() {
            document.getElementById('bitis_tarihi').min = this.value;
            if (document.getElementById('bitis_tarihi').value && 
                document.getElementById('bitis_tarihi').value < this.value) {
                document.getElementById('bitis_tarihi').value = this.value;
            }
        });
    </script>
</body>
</html> 