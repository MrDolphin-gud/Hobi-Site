<?php
require_once 'config/database.php';
require_once 'config/Session.php';
require_once 'models/Etkinlik.php';

// Session başlat
Session::baslat();
// Oturum kontrolü
Session::oturumKontrol();
// CSRF token kontrolü
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['csrf_token']) || !Session::csrfTokenKontrol($_POST['csrf_token'])) {
    $_SESSION['hata'] = "Geçersiz istek.";
    header("Location: etkinlikler.php");
    exit();
}
// Etkinlik ID kontrolü
if (!isset($_POST['id'])) {
    $_SESSION['hata'] = "Etkinlik ID'si belirtilmedi.";
    header("Location: etkinlikler.php");
    exit();
}
$db = Database::getInstance()->getConnection();
$etkinlik_id = (int)$_POST['id'];
$kullanici_id = Session::kullaniciBilgisiAl('kullanici_id');
try {
    $db->beginTransaction();
    // Etkinlik detaylarını ve yetkiyi kontrol et
    $stmt = $db->prepare("SELECT id, kullanici_id FROM etkinlikler WHERE id = :id");
    $stmt->bindParam(':id', $etkinlik_id);
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        throw new Exception("Etkinlik bulunamadı.");
    }
    $etkinlik = $stmt->fetch();
    if ($etkinlik['kullanici_id'] != $kullanici_id) {
        throw new Exception("Bu etkinliği silme yetkiniz yok.");
    }
    // Katılımcıları kontrol et
    $stmt = $db->prepare("SELECT COUNT(*) FROM etkinlik_katilimcilar WHERE etkinlik_id = :etkinlik_id");
    $stmt->bindParam(':etkinlik_id', $etkinlik_id);
    $stmt->execute();
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("Bu etkinliğe katılımcılar var. Önce katılımcıların ayrılmasını bekleyin.");
    }
    // Önce katılımcı kayıtlarını sil
    $stmt = $db->prepare("DELETE FROM etkinlik_katilimcilar WHERE etkinlik_id = :etkinlik_id");
    $stmt->bindParam(':etkinlik_id', $etkinlik_id);
    $stmt->execute();
    // Sonra etkinliği sil
    $stmt = $db->prepare("DELETE FROM etkinlikler WHERE id = :id");
    $stmt->bindParam(':id', $etkinlik_id);
    $stmt->execute();
    $db->commit();
    $_SESSION['mesaj'] = "Etkinlik başarıyla silindi.";
    
} catch (Exception $e) {
    $db->rollBack();
    error_log("Etkinlik silme hatası: " . $e->getMessage());
    $_SESSION['hata'] = "Etkinlik silinirken bir hata oluştu: " . $e->getMessage();
}

header("Location: etkinlikler.php");
exit();
?> 