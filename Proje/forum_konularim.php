<?php
require_once 'config/database.php';
require_once 'config/Session.php';

Session::oturumKontrol();
$db = Database::getInstance()->getConnection();
$kullanici_id = Session::kullaniciBilgisiAl('kullanici_id');
$mesaj = '';
$hata = '';
// Konu silme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['konu_id']) && isset($_POST['sil'])) {
    try {
        Session::csrfTokenKontrol($_POST['csrf_token']);
        $konu_id = (int)$_POST['konu_id'];
        $db->beginTransaction();
        
        // Önce konunun kullanıcıya ait olup olmadığını kontrol et
        $stmt = $db->prepare("SELECT id, baslik FROM forum_konulari WHERE id = :id AND kullanici_id = :kullanici_id");
        $stmt->bindParam(':id', $konu_id);
        $stmt->bindParam(':kullanici_id', $kullanici_id);
        $stmt->execute();
        
        if ($stmt->rowCount() == 0) {
            throw new Exception('Bu konuyu silme yetkiniz yok.');
        }
        
        $konu = $stmt->fetch();
        
        // Yorum sayısını kontrol et
        $stmt = $db->prepare("SELECT COUNT(*) FROM forum_yorumlari WHERE konu_id = :konu_id");
        $stmt->bindParam(':konu_id', $konu_id);
        $stmt->execute();
        $yorum_sayisi = $stmt->fetchColumn();
        
        if ($yorum_sayisi > 0) {
            throw new Exception("Bu konuya {$yorum_sayisi} yorum yapılmış. Önce yorumları silmelisiniz.");
        }
        
        // Konuyu sil
        $stmt = $db->prepare("DELETE FROM forum_konulari WHERE id = :id");
        $stmt->bindParam(':id', $konu_id);
        $stmt->execute();
        
        $db->commit();
        $mesaj = 'Konu başarıyla silindi.';
        
    } catch (Exception $e) {
        $db->rollBack();
        $hata = 'Bir hata oluştu: ' . $e->getMessage();
    }
}
// Kullanıcının konularını getir
$stmt = $db->prepare("SELECT k.*, f.ad as kategori_adi, h.ad as hobi_adi,
                     (SELECT COUNT(*) FROM forum_yorumlari WHERE konu_id = k.id) as yorum_sayisi
                     FROM forum_konulari k
                     JOIN forum_kategorileri f ON k.kategori_id = f.id
                     JOIN hobi_kategorileri h ON f.kategori_id = h.id
                     WHERE k.kullanici_id = :kullanici_id
                     ORDER BY k.son_guncelleme DESC");
$stmt->bindParam(':kullanici_id', $kullanici_id);
$stmt->execute();
$konular = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konularım - Hobi Kulübü Forum</title>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Konularım</h2>
            <div>
                <a href="forum_yeni_konu.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Yeni Konu Aç
                </a>
                <a href="forum.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Foruma Dön
                </a>
            </div>
        </div>
        <?php if (empty($konular)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Henüz hiç konu açmamışsınız.
                <a href="forum_yeni_konu.php" class="alert-link">İlk konunuzu açmak için tıklayın</a>.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Başlık</th>
                            <th>Kategori</th>
                            <th>Hobi</th>
                            <th>Yorumlar</th>
                            <th>Görüntülenme</th>
                            <th>Son Güncelleme</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($konular as $konu): ?>
                            <tr>
                                <td>
                                    <a href="forum_konu.php?id=<?= $konu['id'] ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($konu['baslik']) ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-primary">
                                        <i class="fas fa-folder"></i> <?= htmlspecialchars($konu['kategori_adi']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <i class="fas fa-tag"></i> <?= htmlspecialchars($konu['hobi_adi']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-comments"></i> <?= $konu['yorum_sayisi'] ?>
                                    </span>
                                </td>
                                <td>
                                    <i class="fas fa-eye"></i> <?= $konu['goruntulenme'] ?>
                                </td>
                                <td>
                                    <?= date('d.m.Y H:i', strtotime($konu['son_guncelleme'])) ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="forum_konu.php?id=<?= $konu['id'] ?>" 
                                           class="btn btn-primary" 
                                           title="Görüntüle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-danger" 
                                                title="Sil"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#silModal"
                                                data-konu-id="<?= $konu['id'] ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <!-- Silme Modal -->
    <div class="modal fade" id="silModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konuyu Sil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bu konuyu silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.</p>
                    <p class="text-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Dikkat: Konuya yapılmış yorumlar varsa, önce yorumları silmelisiniz.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <form method="POST" action="" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= Session::csrfTokenOlustur() ?>">
                        <input type="hidden" name="konu_id" value="">
                        <button type="submit" name="sil" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Konuyu Sil
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Silme modalını açarken konu ID'sini form'a aktar
        const silModal = document.getElementById('silModal');
        silModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const konuId = button.getAttribute('data-konu-id');
            const form = this.querySelector('form');
            form.querySelector('input[name="konu_id"]').value = konuId;
        });
    });
    </script>
</body>
</html> 