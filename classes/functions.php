<?php
// GÜVENLİK: Form verilerini temizler
function temizle($veri) {
    $veri = trim($veri);
    $veri = stripslashes($veri);
    $veri = htmlspecialchars($veri, ENT_QUOTES, 'UTF-8');
    return $veri;
}

// LOGLAMA: Sistemdeki hareketleri dosyaya yazar
function log_yaz($mesaj) {
    $zaman = date('Y-m-d H:i:s');
    $dosya_yolu = __DIR__ . '/../loglar.txt';
    $metin = "[$zaman] " . $mesaj . PHP_EOL;
    file_put_contents($dosya_yolu, $metin, FILE_APPEND);
}
?>