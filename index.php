<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$kullanici_adi = $_SESSION['username'];
$user_id = $_SESSION['user_id'];
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';

$kendiEtkinlikleri = [];
$digerEtkinlikler = [];


$havaDurumuYazisi = "🌤️ Hava Durumu Alınamadı";
try {

    $apiUrl = "https://api.open-meteo.com/v1/forecast?latitude=38.75&longitude=30.55&current_weather=true";
    
    
    $apiCevap = @file_get_contents($apiUrl);
    
    if ($apiCevap) {
        $veri = json_decode($apiCevap, true);
        if (isset($veri['current_weather']['temperature'])) {
            $sicaklik = $veri['current_weather']['temperature'];
            $havaDurumuYazisi = "🌤️ Afyon: " . $sicaklik . " °C";
        }
    }
} catch (Exception $e) {
    
}

try {
    if ($user_role === 'admin') {
        $sorguTüm = $db->prepare("SELECT id, title, description, event_date FROM events ORDER BY event_date DESC");
        $sorguTüm->execute();
        $kendiEtkinlikleri = $sorguTüm->fetchAll();
    } else {
        $sorguKendi = $db->prepare("SELECT id, title, description, event_date FROM events WHERE user_id = :user_id ORDER BY event_date DESC");
        $sorguKendi->execute([':user_id' => $user_id]);
        $kendiEtkinlikleri = $sorguKendi->fetchAll();

        $sorguDiger = $db->prepare("SELECT id, title, description, event_date FROM events WHERE user_id != :user_id ORDER BY event_date DESC");
        $sorguDiger->execute([':user_id' => $user_id]);
        $digerEtkinlikler = $sorguDiger->fetchAll();
    }
} catch (PDOException $e) {
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>MemoryCollect - Ana Sayfa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f7fe; }
        .event-card { background: white; border: none; border-radius: 20px; box-shadow: 0px 20px 40px rgba(112,144,176,0.05); }
        .weather-badge { background: rgba(255, 255, 255, 0.2); padding: 8px 15px; border-radius: 12px; font-weight: 600; font-size: 0.9rem; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark mb-4 p-3">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="navbar-brand fw-bold" href="index.php">📸 MemoryCollect</a>
        
        <div class="d-flex align-items-center">
            <span class="text-light me-4 weather-badge">
                <?php echo $havaDurumuYazisi; ?>
            </span>
            
            <span class="text-light me-3">Selam, <strong><?php echo htmlspecialchars($kullanici_adi); ?></strong> (<?php echo strtoupper($user_role); ?>)</span>
            <a href="logout.php" class="btn btn-sm btn-outline-light">Çıkış Yap</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold" style="color: #1b2559;">
            <?php echo $user_role === 'admin' ? "👑 Sistemdeki Tüm Albümler (Yönetici Modu)" : "👑 Kendi Oluşturduğum Albümler"; ?>
        </h3>
        <a href="add_event.php" class="btn btn-success rounded-3">✨ Yeni Etkinlik Ekle</a>
    </div>

    <div class="row mb-5 mt-2">
        <div class="col-md-4">
            <div class="card event-card p-3" style="border-left: 5px solid #4318ff;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small fw-bold">SİSTEMDEKİ TOPLAM ALBÜM</p>
                        <h3 class="mb-0 fw-bold" style="color: #1b2559;">
                            <?php echo count($kendiEtkinlikleri) + count($digerEtkinlikler); ?> Adet
                        </h3>
                    </div>
                    <h1 style="color: #4318ff; opacity: 0.2;">📁</h1>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card event-card p-3" style="border-left: 5px solid #05cd99;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small fw-bold">SUNUCU DURUMU</p>
                        <h3 class="mb-0 fw-bold" style="color: #1b2559;">%100 Aktif</h3>
                    </div>
                    <h1 style="color: #05cd99; opacity: 0.2;">🟢</h1>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card event-card p-3" style="border-left: 5px solid #ffb547;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small fw-bold">SMTP MAIL MOTORU</p>
                        <h3 class="mb-0 fw-bold" style="color: #1b2559;">Hazır (Port 587)</h3>
                    </div>
                    <h1 style="color: #ffb547; opacity: 0.2;">📨</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="card p-3 mb-4 shadow-sm" style="border: none; border-radius: 15px; background: white;">
        <input type="text" id="liveSearchInput" class="form-control bg-light border-0 py-2" placeholder="🔍 Albüm adı yazarak anında ara..." style="border-radius: 10px; font-weight: 500;">
    </div>

    <div class="row mb-5">
        <?php foreach($kendiEtkinlikleri as $etkinlik): ?>
            <div class="col-md-4 mb-4 arama-item">
                <div class="card event-card p-4 h-100">
                    <h5 class="fw-bold album-baslik"><?php echo htmlspecialchars($etkinlik['title']); ?></h5>
                    <p class="text-muted small">📅 <?php echo date('d.m.Y', strtotime($etkinlik['event_date'])); ?></p>
                    <a href="view_event.php?id=<?php echo $etkinlik['id']; ?>" class="btn btn-primary mt-auto rounded-3">Albümü Yönet</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if($user_role !== 'admin' && count($digerEtkinlikler) > 0): ?>
        <h3 class="fw-bold mb-4" style="color: #1b2559;">👤 Misafir Olarak Katıldığınız Etkinlikler</h3>
        <div class="row">
            <?php foreach($digerEtkinlikler as $etkinlik): ?>
                <div class="col-md-4 mb-4 arama-item">
                    <div class="card event-card p-4 h-100" style="border-top: 4px solid #05cd99;">
                        <h5 class="fw-bold album-baslik"><?php echo htmlspecialchars($etkinlik['title']); ?></h5>
                        <p class="text-muted small">📅 <?php echo date('d.m.Y', strtotime($etkinlik['event_date'])); ?></p>
                        <a href="view_event.php?id=<?php echo $etkinlik['id']; ?>" class="btn btn-outline-success mt-auto rounded-3">Görüntüle ve Fotoğraf Ekle</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<footer class="container text-center mt-5 mb-4" style="border-top: 1px solid #e2e8f0; padding-top: 20px;">
        <p class="mb-1 fw-bold" style="color: #1b2559; font-size: 0.95rem;">© 2026 MemoryCollect by Melih. Tüm Hakları Saklıdır.</p>
        <p class="text-muted small mb-0">Sistem Sürümü: v1.4.2 (Stable Release) | Altyapı: PHP 8 & PDO | Sunucu: Localhost</p>
</footer>

<script>
document.getElementById('liveSearchInput').addEventListener('keyup', function() {
    let aranacakKelime = this.value.toLowerCase().trim();
    let kartlar = document.querySelectorAll('.arama-item');

    kartlar.forEach(function(kart) {
        let baslikEl = kart.querySelector('.album-baslik');
        if (baslikEl) {
            let baslik = baslikEl.textContent.toLowerCase();
            if (baslik.indexOf(aranacakKelime) !== -1) {
                kart.style.display = 'block';
            } else {
                kart.style.display = 'none';
            }
        }
    });
});
</script>
</body>
</html>