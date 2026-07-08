<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$mesaj = '';
$mesajTuru = '';
$user_id = $_SESSION['user_id'];
$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['photos'])) {
    $event_id_post = (int)$_POST['event_id'];
    $izinVerilenUzantilar = ['jpg', 'jpeg', 'png'];
    $basariliYükleme = 0;
    $hataliYükleme = 0;

    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }

    $toplamDosya = count($_FILES['photos']['name']);
    
    for ($i = 0; $i < $toplamDosya; $i++) {
        $dosyaAdi = $_FILES['photos']['name'][$i];
        $tmpName = $_FILES['photos']['tmp_name'][$i];
        $boyut = $_FILES['photos']['size'][$i];
        
        if ($tmpName != "") {
            $dosyaUzantisi = strtolower(pathinfo($dosyaAdi, PATHINFO_EXTENSION));
            
            if (!in_array($dosyaUzantisi, $izinVerilenUzantilar) || $boyut > 5000000) {
                $hataliYükleme++;
                continue; // Hatalıysa atla, diğerine geç
            }

            $yeniDosyaAdi = uniqid('ani_') . '_' . $i . '.' . $dosyaUzantisi;
            $hedefKlasor = 'uploads/' . $yeniDosyaAdi;

            if (move_uploaded_file($tmpName, $hedefKlasor)) {
                // Veritabanına kullanıcının ID'si ile birlikte kaydediyoruz
                $sorgu = $db->prepare("INSERT INTO photos (event_id, user_id, file_name) VALUES (:event_id, :user_id, :file_name)");
                $sorgu->execute([
                    ':event_id' => $event_id_post,
                    ':user_id' => $user_id,
                    ':file_name' => $yeniDosyaAdi
                ]);
                $basariliYükleme++;
            }
        }
    }

    if ($basariliYükleme > 0) {
        $mesaj = "Tebrikler! Toplam {$basariliYükleme} fotoğraf albüme başarıyla eklendi.";
        $mesajTuru = "success";
    } else {
        $mesaj = "Fotoğraflar yüklenemedi. Lütfen formatın (JPG/PNG) ve boyutun uygun olduğundan emin olun.";
        $mesajTuru = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toplu Fotoğraf Yükle - MemoryCollect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f7fe; }
        .card { border: none; border-radius: 20px; box-shadow: 0px 15px 35px rgba(112, 144, 176, 0.08); }
        .upload-area { border: 2px dashed #4318ff; border-radius: 15px; padding: 30px; text-align: center; background: #f8f9fa; }
        .btn-custom { background-color: #4318ff; color: white; border-radius: 12px; padding: 12px 20px; font-weight: 600; }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card p-4">
                <div class="text-center mb-4">
                    <h3 class="fw-bold" style="color: #1b2559;">📸 Anıları Paylaş</h3>
                    <p class="text-muted">Galerinizden birden fazla fotoğraf seçebilirsiniz.</p>
                </div>
                
                <?php if(!empty($mesaj)): ?>
                    <div class="alert alert-<?php echo $mesajTuru; ?> rounded-3">
                        <?php echo $mesaj; ?>
                    </div>
                <?php endif; ?>

                <?php if($event_id > 0 || $_SERVER['REQUEST_METHOD'] == 'POST'): ?>
                    <form action="upload.php?event_id=<?php echo $event_id ?: $event_id_post; ?>" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="event_id" value="<?php echo $event_id ?: $event_id_post; ?>">
                        
                        <div class="upload-area mb-4">
                            <!-- 'multiple' anahtar kelimesi sayesinde misafir çoklu seçim yapabilir -->
                            <input class="form-control" type="file" name="photos[]" accept="image/jpeg, image/png" multiple required>
                            <small class="d-block mt-2 text-muted">Aynı anda istediğiniz kadar fotoğraf seçin.</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-custom">Seçilenleri Albüme Gönder</button>
                            <a href="view_event.php?id=<?php echo $event_id ?: $event_id_post; ?>" class="btn btn-outline-secondary rounded-3">Albüme Geri Dön</a>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning text-center">Lütfen listeden bir etkinlik seçin.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>