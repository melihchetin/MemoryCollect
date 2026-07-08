<?php
require_once 'config/db.php';

$mesaj = '';
$mesajTuru = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $username = trim(htmlspecialchars($_POST['username']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        $mesaj = "Lütfen tüm alanları doldurun.";
        $mesajTuru = "danger";
    } 
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mesaj = "Lütfen geçerli bir e-posta adresi girin.";
        $mesajTuru = "warning";
    } 
    else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $role = 'user';

        try {
            $sorgu = $db->prepare("INSERT INTO user (username, email, password_hash, role) VALUES (:username, :email, :password_hash, :role)");
            
            $sorgu->bindParam(':username', $username);
            $sorgu->bindParam(':email', $email);
            $sorgu->bindParam(':password_hash', $password_hash);
            $sorgu->bindParam(':role', $role);

            if ($sorgu->execute()) {
                $mesaj = "Kayıt işlemi başarılı! Şimdi giriş yapabilirsiniz.";
                $mesajTuru = "success";
                
                $username = '';
                $email = '';
            }
  } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $mesaj = "Bu kullanıcı adı veya e-posta zaten kullanılıyor.";
                $mesajTuru = "warning";
            } else {
                $mesaj = "Veritabanı Hatası: " . $e->getMessage();
                $mesajTuru = "danger";
            }
        }
            }
        }

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MemoryCollect - Kayıt Ol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white text-center">
                    <h4>MemoryCollect'e Katıl</h4>
                </div>
                <div class="card-body p-4">
                    
                    <?php if(!empty($mesaj)): ?>
                        <div class="alert alert-<?php echo $mesajTuru; ?> alert-dismissible fade show" role="alert">
                            <?php echo $mesaj; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
                        </div>
                    <?php endif; ?>

                    <form action="register.php" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Kullanıcı Adı</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">E-Posta Adresi</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Şifre</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Kayıt Ol</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p>Zaten bir hesabın var mı? <a href="login.php" class="text-decoration-none">Giriş Yap</a></p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>