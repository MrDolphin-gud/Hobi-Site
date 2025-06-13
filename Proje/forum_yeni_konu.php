<?php
require_once 'config/database.php';
require_once 'config/Session.php';

Session::oturumKontrol();
$db = Database::getInstance()->getConnection();
$mesaj = '';
$hata = '';
// Forum kategorilerini getir
$stmt = $db->prepare("SELECT f.*, h.ad as hobi_adi 
                     FROM forum_kategorileri f
                     JOIN hobi_kategorileri h ON f.kategori_id = h.id
                     ORDER BY h.ad, f.ad");
$stmt->execute();
$kategoriler = $stmt->fetchAll();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $baslik = trim($_POST['baslik'] ?? '');
    $icerik = trim($_POST['icerik'] ?? '');
    $kategori_id = (int)($_POST['kategori_id'] ?? 0);
    $kullanici_id = Session::kullaniciBilgisiAl('kullanici_id');
    if (empty($baslik) || empty($icerik) || empty($kategori_id)) {
        $hata = 'Lütfen tüm alanları doldurun.';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO forum_konulari (baslik, icerik, kategori_id, kullanici_id, 
                                 goruntulenme, olusturma_tarihi, son_guncelleme) 
                                 VALUES (:baslik, :icerik, :kategori_id, :kullanici_id, 
                                 0, NOW(), NOW())");
            $stmt->bindParam(':baslik', $baslik);
            $stmt->bindParam(':icerik', $icerik);
            $stmt->bindParam(':kategori_id', $kategori_id);
            $stmt->bindParam(':kullanici_id', $kullanici_id);
            if ($stmt->execute()) {
                $konu_id = $db->lastInsertId();
                header("Location: forum_konu.php?id=" . $konu_id);
                exit();
            } else {
                $hata = 'Konu oluşturulurken bir hata oluştu.';
            }
        } catch (PDOException $e) {
            $hata = 'Veritabanı hatası: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Konu Aç - Hobi Kulübü Forum</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- TinyMCE -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js"></script>
    <script>
        tinymce.init({
            selector: '#icerik',
            plugins: 'link lists',
            toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | link',
            height: 300
        });
    </script>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Yeni Konu Aç</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($hata): ?>
                            <div class="alert alert-danger"><?= $hata ?></div>
                        <?php endif; ?>

                        <form method="POST" action="" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="baslik" class="form-label">Konu Başlığı *</label>
                                <input type="text" class="form-control" id="baslik" name="baslik" 
                                       value="<?= htmlspecialchars($baslik ?? '') ?>" required>
                                <div class="form-text">
                                    Konunuzu açık ve anlaşılır bir şekilde ifade eden bir başlık seçin.
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="kategori_id" class="form-label">Kategori *</label>
                                <select class="form-select" id="kategori_id" name="kategori_id" required>
                                    <option value="">Kategori Seçin</option>
                                    <?php 
                                    $current_hobi = '';
                                    foreach ($kategoriler as $kategori): 
                                        if ($current_hobi != $kategori['hobi_adi']):
                                            if ($current_hobi != '') echo '</optgroup>';
                                            $current_hobi = $kategori['hobi_adi'];
                                    ?>
                                        <optgroup label="<?= htmlspecialchars($kategori['hobi_adi']) ?>">
                                    <?php endif; ?>
                                        <option value="<?= $kategori['id'] ?>" 
                                                <?= ($kategori_id ?? 0) == $kategori['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($kategori['ad']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <?php if ($current_hobi != '') echo '</optgroup>'; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="icerik" class="form-label">İçerik *</label>
                                <textarea class="form-control" id="icerik" name="icerik" required><?= htmlspecialchars($icerik ?? '') ?></textarea>
                                <div class="form-text">
                                    Konunuzu detaylı bir şekilde açıklayın. Gerekirse resim ekleyebilir veya bağlantılar paylaşabilirsiniz.
                                </div>
                            </div>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Forum Kuralları:
                                <ul class="mb-0 mt-2">
                                    <li>Saygılı ve yapıcı bir dil kullanın.</li>
                                    <li>Konuyla ilgili olmayan paylaşımlardan kaçının.</li>
                                    <li>Kişisel bilgilerinizi paylaşmaktan kaçının.</li>
                                    <li>Telif hakkı olan içerikleri paylaşmayın.</li>
                                </ul>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Konuyu Oluştur
                                </button>
                                <a href="forum.php" class="btn btn-secondary">
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