# Etkinlik Yönetim Sistemi

Modern ve güvenli bir etkinlik yönetim sistemi.

## Özellikler

### Kullanıcı Yönetimi
- Güvenli kayıt ve giriş sistemi
- Şifre sıfırlama özelliği
- Profil yönetimi
- Oturum yönetimi ve kontrolü

### Etkinlik Yönetimi
- Etkinlik oluşturma ve düzenleme
- Etkinlik detayları görüntüleme
- Etkinlik silme
- Etkinlik arama ve filtreleme
- Kategori bazlı etkinlik organizasyonu
- Etkinlik katılımcı yönetimi
- Etkinlik kontenjan kontrolü

## Dizin Yapısı

```
├── config/                 # Yapılandırma dosyaları
│   ├── database.php       # Veritabanı bağlantısı
│   ├── config.php         # Genel ayarlar
│   ├── mail.php          # E-posta ayarları
│   └── Session.php       # Oturum yönetimi
├── models/                # Veritabanı modelleri
│   ├── Etkinlik.php      # Etkinlik işlemleri
│   ├── Kategori.php      # Kategori işlemleri
│   └── Kullanici.php     # Kullanıcı işlemleri
├── uploads/              # Yüklenen dosyalar
│   └── etkinlikler/     # Etkinlik görselleri
├── assets/              # Statik dosyalar
│   ├── css/            # Stil dosyaları
│   ├── js/             # JavaScript dosyaları
│   └── img/            # Görseller
├── includes/           # Yardımcı fonksiyonlar
├── index.php          # Ana sayfa
├── etkinlikler.php    # Etkinlik listesi
├── etkinlik_ekle.php  # Etkinlik oluşturma
├── etkinlik_detay.php # Etkinlik detayları
└── README.md          # Bu dosya
```

## Kullanım

### Etkinlik Oluşturma
1. Giriş yapın
2. "Etkinlik Oluştur" butonuna tıklayın
3. Etkinlik bilgilerini doldurun:
   - Başlık
   - Açıklama
   - Tarih ve saat
   - Konum
   - Kontenjan
   - Kategori
4. "Oluştur" butonuna tıklayın

### Etkinliğe Katılma
1. Etkinlik detay sayfasına gidin
2. "Katıl" butonuna tıklayın
3. Katılım onaylanacaktır

### Etkinlik Yönetimi
- Etkinlik sahibi etkinliği düzenleyebilir
- Katılımcı listesini görüntüleyebilir
- Etkinliği silebilir (katılımcı yoksa)

# Proje Videosu
https://youtu.be/dukyXug2Jko
