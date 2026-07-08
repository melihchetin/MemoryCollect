<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$current_user_id = $_SESSION['user_id'];
$current_user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';

try {
    $eventSorgu = $db->prepare("SELECT id, user_id, title, event_date, description FROM events WHERE id = :event_id");
    $eventSorgu->bindParam(':event_id', $event_id);
    $eventSorgu->execute();
    $event = $eventSorgu->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        die("Etkinlik bulunamadı.");
    }

    $dugunSahibiMi = ($current_user_role === 'admin' || $event['user_id'] == $current_user_id);

    if ($dugunSahibiMi) {
        $photoSorgu = $db->prepare("SELECT id, user_id, file_name, uploaded_at FROM photos WHERE event_id = :event_id ORDER BY uploaded_at DESC");
        $photoSorgu->bindParam(':event_id', $event_id);
    } else {
        $photoSorgu = $db->prepare("SELECT id, user_id, file_name, uploaded_at FROM photos WHERE event_id = :event_id AND user_id = :user_id ORDER BY uploaded_at DESC");
        $photoSorgu->bindParam(':event_id', $event_id);
        $photoSorgu->bindParam(':user_id', $current_user_id);
    }
    
    $photoSorgu->execute();
    $fotoğraflar = $photoSorgu->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}

// QR Kod Üretim API Bağlantısı
$misafirLinki = "http://localhost:8888/MEMORYCOLLECT/upload.php?event_id=" . $event['id'];
$qrCodeApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($misafirLinki);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event['title']); ?> - Albüm Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f7fe; color: #2b3674; }
        .event-header-card { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white; border-radius: 24px; padding: 35px; box-shadow: 0px 20px 40px rgba(0,0,0,0.1); border: none; }
        .qr-card { background: white; border-radius: 15px; padding: 15px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        .gallery-card { border: none; border-radius: 20px; overflow: hidden; background: white; box-shadow: 0px 15px 35px rgba(112, 144, 176, 0.06); }
        .gallery-img-wrapper { height: 240px; position: relative; }
        .gallery-img { width: 100%; height: 100%; object-fit: cover; }
        .btn-custom-success { background: #05cd99; color: white; border-radius: 12px; font-weight: 600; border: none; padding: 12px 24px; }
    </style>
</head>
<body>

<div class="container mt-4">
    <div class="mb-3">
        <a href="index.php" class="btn btn-sm btn-outline-dark rounded-3">← Panoya Dön</a>
    </div>

    <div class="card event-header-card mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <span class="badge bg-primary mb-2 p-2 rounded-3">
                    <?php echo $dugunSahibiMi ? "👑 Yönetici Paneli (Tüm Yetkiler Açık)" : "👤 Katılımcı / Misafir"; ?>
                </span>
                <h2 class="fw-bold mb-2"><?php echo htmlspecialchars($event['title']); ?></h2>
                <p class="text-light opacity-75 mb-3">📅 Tarih: <?php echo date('d.m.Y', strtotime($event['event_date'])); ?></p>
                <p class="mb-0 text-light opacity-75"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
            </div>
            
            <div class="col-md-4 mt-3 mt-md-0 d-flex justify-content-md-end justify-content-center">
                <div class="qr-card">
                    <img src="<?php echo $qrCodeApiUrl; ?>" alt="Düğün QR Kodu" class="img-fluid mb-2" style="border-radius: 8px;">
                    <small class="d-block text-dark fw-bold" style="font-size: 0.75rem;">Masadaki QR Kod</small>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">
            <?php echo $dugunSahibiMi ? "📸 Tüm Misafirlerin Fotoğrafları" : "📸 Sadece Senin Yüklediğin Fotoğraflar"; ?>
        </h3>
        <div>
            <a href="upload.php?event_id=<?php echo $event['id']; ?>" class="btn btn-custom-success me-2">➕ Fotoğraf Ekle</a>
            <span class="badge bg-dark px-3 py-2 rounded-pill fs-6"><?php echo count($fotoğraflar); ?> Fotoğraf</span>
        </div>
    </div>

    <div class="row">
        <?php if(count($fotoğraflar) > 0): ?>
            <?php foreach($fotoğraflar as $foto): ?>
                <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                    <div class="card gallery-card h-100">
                        <div class="gallery-img-wrapper">
                            <img src="uploads/<?php echo $foto['file_name']; ?>" class="gallery-img">
                        </div>
                        <div class="card-footer bg-white d-flex justify-content-between align-items-center border-0 p-3">
                            <small class="text-muted">⏳ <?php echo date('H:i', strtotime($foto['uploaded_at'])); ?></small>
                            
                            <div>
                                <a href="uploads/<?php echo $foto['file_name']; ?>" download class="btn btn-sm btn-outline-primary rounded-3 me-1">⬇️ İndir</a>
                                
                                <?php if($dugunSahibiMi || $foto['user_id'] == $current_user_id): ?>
                                    <a href="delete_photo.php?id=<?php echo $foto['id']; ?>" class="btn btn-sm btn-outline-danger rounded-3" onclick="return confirm('Silmek istediğinize emin misiniz?');">
                                        🗑️ Sil
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="card border-0 text-center py-5 shadow-sm" style="border-radius: 20px;">
                    <h4 class="text-muted mb-2">Henüz fotoğraf yok.</h4>
                    <p class="text-secondary mb-0">İlk anıyı siz yükleyin!</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>