<?php 
require_once 'config/db.php'; 
require_once 'classes/functions.php'; 
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MemoryCollect | Etkinlik Portalı</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand" href="index.php">📸 MemoryCollect</a>
    <div class="navbar-nav ms-auto">
      <?php if(isset($_SESSION['user_id'])): ?>
        <span class="nav-link text-white">Merhaba, <?= $_SESSION['username'] ?></span>
        <a class="nav-link btn btn-outline-danger btn-sm ms-2" href="logout.php">Çıkış</a>
      <?php else: ?>
        <a class="nav-link" href="login.php">Giriş Yap</a>
        <a class="nav-link" href="register.php">Kayıt Ol</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
<div class="container">