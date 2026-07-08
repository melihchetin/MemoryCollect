<?php
session_start();
require_once 'config/db.php';
require_once 'config/mail_helper.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$mesaj = '';
$mesajTuru = '';

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    unset($_SESSION['canli_mail_onizleme']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim(htmlspecialchars($_POST['title']));
    $description = trim(htmlspecialchars($_POST['description']));
    $event_date = $_POST['event_date'];
    $user_id = $_SESSION['user_id'];

    if (empty($title) || empty($event_date)) {
        $mesaj = "Lütfen başlık ve tarih alanlarını doldurun.";
        $mesajTuru = "warning";
    } else {
        try {
            $sorgu = $db->prepare("INSERT INTO events (user_id, title, description, event_date) VALUES (:user_id, :title, :description, :event_date)");
            $sorgu->bindParam(':user_id', $user_id);
            $sorgu->bindParam(':title', $title);
            $sorgu->bindParam(':description', $description);
            $sorgu->bindParam(':event_date', $event_date);

            if ($sorgu->execute()) {
                $mesaj = "Etkinlik başarıyla kaydedildi ve sistem e-postası tetiklendi!";
                $mesajTuru = "success";

        
                $ip_address = $_SERVER['REMOTE_ADDR'];
                $logSorgu = $db->prepare("INSERT INTO logs (user_id, action, ip_address) VALUES (:user_id, 'Yeni etkinlik ekledi', :ip_address)");
                $logSorgu->execute([':user_id' => $user_id, ':ip_address' => $ip_address]);

                
                $mailKonu = "Yeni Canlı Albümünüz Hazır! - " . $title;
                $mailIcerik = "
                    <h3 style='color: #4318ff;'>Merhaba, " . htmlspecialchars($_SESSION['username']) . "!</h3>
                    <p><strong>" . htmlspecialchars($title) . "</strong> isimli anı albümünüz sistemde başarıyla oluşturulmuştur.</p>
                    <p>Misafirleriniz masalardaki QR kodları okutarak bu albüme toplu fotoğraf gönderebilirler.</p>
                    <p><strong>Etkinlik Tarihi:</strong> " . date('d.m.Y', strtotime($event_date)) . "</p>
                    <br>
                    <p style='color: #05cd99; font-weight:bold;'>MemoryCollect sistemini tercih ettiğiniz için teşekkür ederiz.</p>
                ";
                
                
                anıMailGonder("omelihcetin03@gmail.com", $mailKonu, $mailIcerik);
            }
        } catch (PDOException $e) {
            $mesaj = "Veritabanı Hatası: " . $e->getMessage();
            $mesajTuru = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yeni Etkinlik Ekle - MemoryCollect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f7fe; color: #2b3674; }
        .navbar { background: linear-gradient(135deg, #4318ff 0%, #868cff 100%); padding: 15px 0; }
        .card { border: none; border-radius: 24px; box-shadow: 0px 20px 40px rgba(112, 144, 176, 0.08); }
        .btn-custom { background-color: #4318ff; color: white; border-radius: 12px; padding: 12px; font-weight: 600; border: none; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">📸 MemoryCollect</a>
        <a href="index.php" class="btn btn-sm btn-outline-light rounded-3">Geri Dön</a>
    </div>
</nav>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card p-4 mb-5">
                <h3 class="fw-bold mb-4" style="color: #1b2559;">✨ Yeni Düğün / Etkinlik Albümü Oluştur</h3>
                
                <?php if(!empty($mesaj)): ?>
                    <div class="alert alert-<?php echo $mesajTuru; ?> alert-dismissible fade show" style="border-radius: 12px;">
                        <?php echo $mesaj; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form action="add_event.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Gelin & Damat Adı (Veya Etkinlik Başlığı)</label>
                        <input type="text" class="form-control rounded-3" name="title" placeholder="Örn: Mustafa & Merve Düğün Albümü" required style="padding: 10px;">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Etkinlik Tarihi</label>
                        <input type="date" class="form-control rounded-3" name="event_date" required style="padding: 10px;">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Misafirler İçin Karşılama Notu</label>
                        <textarea class="form-control rounded-3" name="description" rows="3" placeholder="Masalardaki davetlilere görünecek kısa bir not yazın..." style="padding: 10px;"></textarea>
                    </div>

                    <button type="submit" class="btn btn-custom w-100 shadow-sm">Albümü Aktifleştir ve Mail Gönder</button>
                </form>
            </div>

            <?php if (isset($_SESSION['canli_mail_onizleme'])): ?>
                <div class="card p-4 border-2" style="border: 2px dashed #4318ff; background: #fff;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-primary mb-0">📧 Sürpriz Durum: Tetiklenen E-Posta Simülatörü</h5>
                        <span class="badge bg-success">Localhost Aktif</span>
                    </div>
                    <p class="text-muted small">MAMP üzerinde dış portlar kapalı olduğundan, sistemin ürettiği şık şablon aşağıda simüle edilmiştir. Aynı dosya <code>uploads/son_giden_mail.html</code> olarak kaydedilmiştir.</p>
                    <div class="border rounded-4 overflow-hidden shadow-sm" style="background: #f4f7fe; padding: 10px;">
                        <?php echo $_SESSION['canli_mail_onizleme']; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>