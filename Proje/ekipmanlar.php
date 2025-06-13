<?php
require_once 'config/database.php';
require_once 'config/Session.php';
Session::oturumKontrol();
$db = Database::getInstance()->getConnection();
// Kategori filtresi
$kategori_id = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$durum = isset($_GET['durum']) ? $_GET['durum'] : 'müsait';
// Ekipmanları getir
$sql = "SELECT e.*, k.kullanici_adi, h.ad as hobi_adi 
        FROM ekipmanlar e 
        JOIN kullanicilar k ON e.kullanici_id = k.id 
        JOIN hobi_kategorileri h ON e.kategori_id = h.id 
        WHERE 1=1";

if ($kategori_id > 0) {
    $sql .= " AND e.kategori_id = :kategori_id";
}
if ($durum) {
    $sql .= " AND e.durum = :durum";
}
$sql .= " ORDER BY e.olusturma_tarihi DESC";
$stmt = $db->prepare($sql);
if ($kategori_id > 0) {
    $stmt->bindParam(':kategori_id', $kategori_id);
}
if ($durum) {
    $stmt->bindParam(':durum', $durum);
}
$stmt->execute();
$ekipmanlar = $stmt->fetchAll();
// Kategorileri getir
$stmt = $db->query("SELECT * FROM hobi_kategorileri ORDER BY ad");
$kategoriler = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ekipmanlar - Hobi Kulübü</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Filtreler</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="mb-3">
                                <label class="form-label">Kategori</label>
                                <select name="kategori" class="form-select">
                                    <option value="0">Tümü</option>
                                    <?php foreach ($kategoriler as $kategori): ?>
                                        <option value="<?= $kategori['id'] ?>" <?= $kategori_id == $kategori['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($kategori['ad']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Durum</label>
                                <select name="durum" class="form-select">
                                    <option value="müsait" <?= $durum == 'müsait' ? 'selected' : '' ?>>Müsait</option>
                                    <option value="ödünç_verildi" <?= $durum == 'ödünç_verildi' ? 'selected' : '' ?>>Ödünç Verildi</option>
                                    <option value="tamirde" <?= $durum == 'tamirde' ? 'selected' : '' ?>>Tamirde</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Filtrele</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Ekipmanlar</h2>
                    <a href="ekipman_ekle.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Yeni Ekipman Ekle
                    </a>
                </div>
                <div class="row">
                    <?php foreach ($ekipmanlar as $ekipman): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($ekipman['ad']) ?></h5>
                                    <h6 class="card-subtitle mb-2 text-muted">
                                        <?= htmlspecialchars($ekipman['hobi_adi']) ?>
                                    </h6>
                                    <p class="card-text"><?= htmlspecialchars($ekipman['aciklama']) ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-<?= $ekipman['durum'] == 'müsait' ? 'success' : 
                                            ($ekipman['durum'] == 'ödünç_verildi' ? 'warning' : 'danger') ?>">
                                            <?= ucfirst(str_replace('_', ' ', $ekipman['durum'])) ?>
                                        </span>
                                        <small class="text-muted">
                                            Sahip: <?= htmlspecialchars($ekipman['kullanici_adi']) ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <?php if ($ekipman['durum'] == 'müsait' && $ekipman['kullanici_id'] != Session::kullaniciBilgisiAl('kullanici_id')): ?>
                                        <a href="ekipman_odunc.php?id=<?= $ekipman['id'] ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-handshake"></i> Ödünç Al
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($ekipman['kullanici_id'] == Session::kullaniciBilgisiAl('kullanici_id')): ?>
                                        <a href="ekipman_duzenle.php?id=<?= $ekipman['id'] ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Düzenle
                                        </a>
                                        <a href="ekipman_sil.php?id=<?= $ekipman['id'] ?>" class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Bu ekipmanı silmek istediğinizden emin misiniz?')">
                                            <i class="fas fa-trash"></i> Sil
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 