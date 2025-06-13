<?php
session_start();
// Tüm oturum verilerini temizle
session_unset();
session_destroy();
// Ana sayfaya yönlendir
header('Location: index.php');
exit; 