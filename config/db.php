<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. .env Dosyasını Güvenli Şekilde Okuma Fonksiyonu
function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        return false;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Yorum satırlarını süzgeçten geçir
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Anahtar ve değeri ayır
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
    return true;
}

// .env dosyasının yolunu belirtip yüklüyoruz
loadEnv(__DIR__ . '/.env');

// 2. Veritabanı Bilgilerini Çevre Değişkenlerinden Çekme
$host   = getenv('DB_HOST') ?: '127.0.0.1';
$port   = getenv('DB_PORT') ?: '8889';
$dbname = getenv('DB_NAME') ?: 'MemoryCollect';
$user   = getenv('DB_USER') ?: 'root';
$pass   = getenv('DB_PASS') ?: 'root';

try {
    // Güvenli PDO Bağlantısı oluşturuluyor
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Hataları fırlat
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Verileri ilişkisel dizi dön
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Gerçek prepared statements kullan (Güvenlik için şart)
    ];

    $db = new PDO($dsn, $user, $pass, $options);

} catch (PDOException $e) {
    // Proje geliştirme aşamasında hatayı net görmek için die kullanıyoruz
    // Canlıya alınırken bu mesaj loglanmalı, dışarı sızdırılmamalıdır (Hocanın loglama şartı)
    die("Veritabanı Bağlantı Hatası: " . $e->getMessage());
}
?>