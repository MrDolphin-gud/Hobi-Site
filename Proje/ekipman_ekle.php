<?php
require_once 'config/database.php';
require_once 'config/Session.php';
Session::oturumKontrol();
$db = Database::getInstance()->getConnection();
$mesaj = '';
$hata = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ad = trim($_POST['ad'] ?? '');
    $aciklama = trim($_POST['aciklama'] ?? '');
    $kategori_id = (int)($_POST['kategori_id'] ?? 0);
    $kullanici_id = Session::kullaniciBilgisiAl('kullanici_id');
    if (empty($ad) || empty($kategori_id)) {
        $hata = 'Lütfen gerekli alanları doldurun.';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO ekipmanlar (ad, aciklama, kategori_id, kullanici_id, durum, olusturma_tarihi) 
                                 VALUES (:ad, :aciklama, :kategori_id, :kullanici_id, 'müsait', NOW())");
            $stmt->bindParam(':ad', $ad);
            $stmt->bindParam(':aciklama', $aciklama);
            $stmt->bindParam(':kategori_id', $kategori_id);
            $stmt->bindParam(':kullanici_id', $kullanici_id);
            if ($stmt->execute()) {
                $mesaj = 'Ekipman başarıyla eklendi.';
                // Formu temizle
                $ad = $aciklama = '';
                $kategori_id = 0;
            } else {
                $hata = 'Ekipman eklenirken bir hata oluştu.';
            }
        } catch (PDOException $e) {
            $hata = 'Veritabanı hatası: ' . $e->getMessage();
        }
    }
}
// Kategorileri getir
$stmt = $db->query("SELECT * FROM hobi_kategorileri ORDER BY ad");
$kategoriler = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Ekipman Ekle - Hobi Kulübü</title>
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
                        <h3 class="card-title mb-0">Yeni Ekipman Ekle</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($mesaj): ?>
                            <div class="alert alert-success"><?= $mesaj ?></div>
                        <?php endif; ?>
                        
                        <?php if ($hata): ?>
                            <div class="alert alert-danger"><?= $hata ?></div>
                        <?php endif; ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="ad" class="form-label">Ekipman Adı *</label>
                                <input type="text" class="form-control" id="ad" name="ad" 
                                       value="<?= htmlspecialchars($ad ?? '') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="kategori_id" class="form-label">Kategori *</label>
                                <select class="form-select" id="kategori_id" name="kategori_id" required>
                                    <option value="">Kategori Seçin</option>
                                    <?php foreach ($kategoriler as $kategori): ?>
                                        <option value="<?= $kategori['id'] ?>" 
                                                <?= ($kategori_id ?? 0) == $kategori['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($kategori['ad']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="aciklama" class="form-label">Açıklama</label>
                                <textarea class="form-control" id="aciklama" name="aciklama" rows="4"><?= htmlspecialchars($aciklama ?? '') ?></textarea>
                                <div class="form-text">
                                    Ekipmanın durumu, özellikleri ve kullanım koşulları hakkında bilgi verin.
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Ekipmanı Kaydet
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
</body>
</html> 