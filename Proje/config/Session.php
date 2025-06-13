<?php
class Session {
    public static function baslat() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    public static function oturumAc($kullanici_id, $kullanici_adi, $ad_soyad) {
        self::baslat();
        $_SESSION['kullanici_id'] = $kullanici_id;
        $_SESSION['kullanici_adi'] = $kullanici_adi;
        $_SESSION['ad_soyad'] = $ad_soyad;
        $_SESSION['oturum_baslangic'] = time();
    }
    public static function oturumKapat() {
        self::baslat();
        session_unset();
        session_destroy();
    }
    public static function oturumKontrol() {
        self::baslat();
        if (!isset($_SESSION['kullanici_id'])) {
            header("Location: login.php");
            exit();
        }
    }
    public static function oturumVarMi() {
        self::baslat();
        return isset($_SESSION['kullanici_id']);
    }
    public static function kullaniciBilgisiAl($anahtar) {
        self::baslat();
        return isset($_SESSION[$anahtar]) ? $_SESSION[$anahtar] : null;
    }
    public static function csrfTokenOlustur() {
        self::baslat();
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    public static function csrfTokenKontrol($token) {
        self::baslat();
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            throw new Exception('CSRF token doğrulaması başarısız.');
        }
        return true;
    }
    public static function csrfTokenYenile() {
        self::baslat();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }
}
?> 