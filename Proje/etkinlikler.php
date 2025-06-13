<?php
require_once 'includes/header.php';
require_once 'models/Etkinlik.php';
// Oturum kontrolü
Session::oturumKontrol();

$etkinlik = new Etkinlik();
$etkinlikler = $etkinlik->tumEtkinlikleriGetir();
?>
<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Etkinlikler</h2>
            <a href="etkinlik_olustur.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Yeni Etkinlik
            </a>
        </div>
        <div class="row">
            <?php
            $etkinlik_sayisi = 0;
            foreach ($etkinlikler as $row):
                $etkinlik_sayisi++;
                $tarih = strtotime($row['tarih']);
                $durum = $tarih < time() ? 'danger' : 'success';
            ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($row['baslik']); ?></h5>
                        <p class="card-text">
                            <i class="fas fa-calendar"></i> 
                            <span class="text-<?php echo $durum; ?>">
                                <?php echo date('d.m.Y H:i', $tarih); ?>
                            </span>
                            <br>
                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['konum']); ?>
                            <br>
                            <i class="fas fa-users"></i> <?php echo $row['kayitli_kisi']; ?>/<?php echo $row['kisi_limit']; ?> katılımcı
                            <br>
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($row['kullanici_adi']); ?>
                        </p>
                        <p class="card-text"><?php echo htmlspecialchars(substr($row['aciklama'], 0, 100)) . '...'; ?></p>
                        <div class="d-flex justify-content-between">
                            <a href="etkinlik_detay.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">Detaylar</a>
                            <?php if ($row['kullanici_id'] == Session::kullaniciBilgisiAl('kullanici_id')): ?>
                            <div class="btn-group">
                                <a href="etkinlik_duzenle.php?id=<?php echo $row['id']; ?>" class="btn btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-danger" 
                                        onclick="etkinlikSil(<?php echo $row['id']; ?>, '<?php echo Session::csrfTokenOlustur(); ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if ($etkinlik_sayisi == 0): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    Henüz hiç etkinlik bulunmamaktadır. 
                    <a href="etkinlik_olustur.php" class="alert-link">İlk etkinliği oluşturmak için tıklayın</a>.
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function etkinlikSil(id, csrfToken) {
    if (confirm('Bu etkinliği silmek istediğinizden emin misiniz?')) {
        // Form elementini oluştur
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'etkinlik_sil.php';
        form.style.display = 'none'; // Formu gizle
        // ID input
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = id;
        // CSRF token input
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = 'csrf_token';
        tokenInput.value = csrfToken;
        // Inputları forma ekle
        form.appendChild(idInput);
        form.appendChild(tokenInput);
        // Formu sayfaya ekle
        document.body.appendChild(form);
        // Formu submit et
        form.submit();
        // Formu DOM'dan kaldır
        setTimeout(() => {
            document.body.removeChild(form);
        }, 100);
    }
}
</script>
<?php require_once 'includes/footer.php'; ?> 