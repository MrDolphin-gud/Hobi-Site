<?php
require_once 'config/database.php';
require_once 'config/Session.php';
Session::oturumKontrol();
$db = Database::getInstance()->getConnection();
// Kategori filtresi
$kategori_id = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$secilen_kategori = $kategori_id; // Seçili kategoriyi sakla
// Forum kategorilerini getir
$stmt = $db->prepare("SELECT f.*, h.ad as hobi_adi, 
                     (SELECT COUNT(*) FROM forum_konulari WHERE kategori_id = f.id) as konu_sayisi
                     FROM forum_kategorileri f
                     JOIN hobi_kategorileri h ON f.kategori_id = h.id
                     ORDER BY h.ad, f.ad");
$stmt->execute();
$kategoriler = $stmt->fetchAll();
// Son konuları getir
$sql = "SELECT k.*, f.ad as kategori_adi, h.ad as hobi_adi, u.kullanici_adi,
        (SELECT COUNT(*) FROM forum_yorumlari WHERE konu_id = k.id) as yorum_sayisi
        FROM forum_konulari k
        JOIN forum_kategorileri f ON k.kategori_id = f.id
        JOIN hobi_kategorileri h ON f.kategori_id = h.id
        JOIN kullanicilar u ON k.kullanici_id = u.id
        WHERE 1=1";

if ($kategori_id > 0) {
    $sql .= " AND k.kategori_id = :kategori_id";
}
$sql .= " ORDER BY k.son_guncelleme DESC LIMIT 10";
$stmt = $db->prepare($sql);
if ($kategori_id > 0) {
    $stmt->bindParam(':kategori_id', $kategori_id);
}
$stmt->execute();
$son_konular = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum - Hobi Kulübü</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container mt-4">
        <div class="row">
            <!-- Kategoriler -->
            <div class="col-md-3 mb-4">
                <div class="card bg-dark text-light">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Kategoriler</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php foreach ($kategoriler as $kategori): ?>
                                <a href="?kategori=<?php echo $kategori['id']; ?>" 
                                   class="list-group-item list-group-item-action bg-dark text-light border-secondary <?php echo ($secilen_kategori == $kategori['id']) ? 'active' : ''; ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?php echo htmlspecialchars($kategori['ad']); ?></strong>
                                            <small class="d-block text-muted"><?php echo htmlspecialchars($kategori['hobi_adi']); ?></small>
                                        </div>
                                        <span class="badge bg-primary rounded-pill"><?php echo $kategori['konu_sayisi']; ?></span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Son Konular -->
            <div class="col-md-9">
                <div class="card bg-dark text-light">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Son Konular</h5>
                        <a href="forum_yeni_konu.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Yeni Konu
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($son_konular)): ?>
                            <div class="alert alert-info m-3">
                                <i class="fas fa-info-circle"></i> Henüz hiç konu bulunmamaktadır.
                                <a href="forum_yeni_konu.php" class="alert-link">İlk konuyu açmak için tıklayın</a>.
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($son_konular as $konu): ?>
                                    <a href="forum_konu.php?id=<?= $konu['id'] ?>" class="list-group-item list-group-item-action bg-dark text-light border-secondary">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?= htmlspecialchars($konu['baslik']) ?></h6>
                                            <small class="text-muted">
                                                <?= date('d.m.Y H:i', strtotime($konu['son_guncelleme'])) ?>
                                            </small>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="fas fa-folder me-1"></i> <?= htmlspecialchars($konu['kategori_adi']) ?> 
                                                (<?= htmlspecialchars($konu['hobi_adi']) ?>)<br>
                                                <i class="fas fa-user me-1"></i> <?= htmlspecialchars($konu['kullanici_adi']) ?>
                                            </small>
                                            <div>
                                                <span class="badge bg-secondary me-1">
                                                    <i class="fas fa-eye"></i> <?= $konu['goruntulenme'] ?>
                                                </span>
                                                <span class="badge bg-info">
                                                    <i class="fas fa-comments"></i> <?= $konu['yorum_sayisi'] ?>
                                                </span>
                                            </div>
                                        </div>
                                    </a>
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
