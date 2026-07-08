<?php
session_start();
require_once 'config/db.php';

$mesaj = '';
$mesajTuru = '';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_or_username = trim(htmlspecialchars($_POST['email_or_username']));
    $password = $_POST['password'];

    if (empty($email_or_username) || empty($password)) {
        $mesaj = "Lütfen tüm alanları doldurun.";
        $mesajTuru = "warning";
    } else {
        try {
            $sorgu = $db->prepare("SELECT id, username, password_hash, role FROM user WHERE email = :email_input OR username = :user_input");
            $sorgu->bindParam(':email_input', $email_or_username);
            $sorgu->bindParam(':user_input', $email_or_username);
            $sorgu->execute();
            $kullanici = $sorgu->fetch(PDO::FETCH_ASSOC);

            if ($kullanici && password_verify($password, $kullanici['password_hash'])) {
                
                $_SESSION['user_id'] = $kullanici['id'];
                $_SESSION['username'] = $kullanici['username'];
                $_SESSION['role'] = $kullanici['role'];

                $ip_address = $_SERVER['REMOTE_ADDR'];
                $logSorgu = $db->prepare("INSERT INTO logs (user_id, action, ip_address) VALUES (:user_id, 'Giriş yaptı', :ip_address)");
                $logSorgu->bindParam(':user_id', $kullanici['id']);
                $logSorgu->bindParam(':ip_address', $ip_address);
                $logSorgu->execute();

                header("Location: index.php");
                exit;
            } else {
                $mesaj = "Hatalı e-posta/kullanıcı adı veya şifre.";
                $mesajTuru = "danger";
            }
        } catch (PDOException $e) {
            $mesaj = "Sistem Hatası: " . $e->getMessage();
            $mesajTuru = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - MemoryCollect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #f4f7fe; 
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card { 
            border: none; 
            border-radius: 24px; 
            box-shadow: 0px 20px 40px rgba(112, 144, 176, 0.12); 
            background: white;
            padding: 40px;
        }
        .btn-custom { 
            background-color: #4318ff; 
            color: white; 
            border-radius: 12px; 
            padding: 12px; 
            font-weight: 600; 
            border: none; 
            transition: 0.3s;
        }
        .btn-custom:hover { 
            background-color: #3311cc; 
            color: white; 
            box-shadow: 0px 10px 20px rgba(67, 24, 255, 0.2);
        }
        .form-control {
            border-radius: 12px;
            padding: 12px;
            border: 1px solid #e2e8f0;
        }
        .form-control:focus {
            border-color: #4318ff;
            box-shadow: 0 0 0 0.25rem rgba(67, 24, 255, 0.15);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            
            <div class="text-center mb-4">
                <h2 class="fw-bold" style="color: #1b2559;">📸 MemoryCollect</h2>
                <p class="text-muted">Anıları biriktirmek için giriş yapın</p>
            </div>

            <div class="login-card">
                
                <?php if(!empty($mesaj)): ?>
                    <div class="alert alert-<?php echo $mesajTuru; ?> rounded-3 text-center">
                        <?php echo $mesaj; ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">E-Posta veya Kullanıcı Adı</label>
                        <input type="text" class="form-control" name="email_or_username" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark">Şifre</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>

                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-custom w-100">Giriş Yap</button>
                    </div>
                </form>

                <div class="text-center pt-3" style="border-top: 1px solid #e2e8f0;">
                    <p class="text-muted mb-1">Henüz hesabınız yok mu?</p>
                    <a href="register.php" class="text-decoration-none fw-bold" style="color: #4318ff;">Hemen Kayıt Ol</a>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>