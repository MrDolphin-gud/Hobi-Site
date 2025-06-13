<?php
require_once 'config/database.php';
require_once 'config/Session.php';

// Oturum kontrolü
Session::oturumKontrol();

// Ekipman ID kontrolü
if (!isset($_GET['id'])) {
    header("Location: ekipmanlar.php");
    exit();
}

$db = Database::getInstance()->getConnection();
$ekipman_id = (int)$_GET['id'];
$kullanici_id = Session::kullaniciBilgisiAl('kullanici_id');

try {
    $db->beginTransaction();
    
    // Önce ekipmanın kullanıcıya ait olup olmadığını ve durumunu kontrol et
    $stmt = $db->prepare("SELECT id, durum FROM ekipmanlar WHERE id = :id AND kullanici_id = :kullanici_id");
    $stmt->bindParam(':id', $ekipman_id);
    $stmt->bindParam(':kullanici_id', $kullanici_id);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        throw new Exception('Bu ekipmanı silme yetkiniz yok.');
    }
    
    $ekipman = $stmt->fetch();
    
    // Ekipman ödünç verilmiş durumdaysa silmeye izin verme
    if ($ekipman['durum'] == 'ödünç_verildi') {
        throw new Exception('Ödünç verilmiş ekipman silinemez. Önce ekipmanın iade edilmesini bekleyin.');
    }
    
    // Bekleyen ödünç taleplerini kontrol et
    $stmt = $db->prepare("SELECT COUNT(*) FROM ekipman_odunc WHERE ekipman_id = :ekipman_id AND durum = 'beklemede'");
    $stmt->bindParam(':ekipman_id', $ekipman_id);
    $stmt->execute();
    
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Bekleyen ödünç talepleri olan ekipman silinemez. Önce talepleri reddedin.');
    }
    
    // Önce ödünç kayıtlarını sil
    $stmt = $db->prepare("DELETE FROM ekipman_odunc WHERE ekipman_id = :ekipman_id");
    $stmt->bindParam(':ekipman_id', $ekipman_id);
    $stmt->execute();
    
    // Sonra ekipmanı sil
    $stmt = $db->prepare("DELETE FROM ekipmanlar WHERE id = :id");
    $stmt->bindParam(':id', $ekipman_id);
    $stmt->execute();
    
    $db->commit();
    $_SESSION['mesaj'] = "Ekipman başarıyla silindi.";
    
} catch (Exception $e) {
    $db->rollBack();
    $_SESSION['hata'] = "Ekipman silinirken bir hata oluştu: " . $e->getMessage();
}

header("Location: ekipmanlar.php");
exit();
?> 