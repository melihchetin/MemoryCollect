<?php
session_start();
require_once 'config/db.php';


if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: login.php");
    exit;
}

$photo_id = (int)$_GET['id'];
$current_user_id = $_SESSION['user_id'];

try {
    
    $sorgu = $db->prepare("
        SELECT p.file_name, p.user_id as photo_owner, p.event_id, e.user_id as event_owner 
        FROM photos p 
        JOIN events e ON p.event_id = e.id 
        WHERE p.id = :photo_id
    ");
    $sorgu->execute([':photo_id' => $photo_id]);
    $photo = $sorgu->fetch(PDO::FETCH_ASSOC);

    if ($photo) {
        
        if ($photo['photo_owner'] == $current_user_id || $photo['event_owner'] == $current_user_id) {
            
            
            $dosyaYolu = 'uploads/' . $photo['file_name'];
            if (file_exists($dosyaYolu)) {
                unlink($dosyaYolu); // Dosyayı yok et
            }

            
            $silSorgu = $db->prepare("DELETE FROM photos WHERE id = :photo_id");
            $silSorgu->execute([':photo_id' => $photo_id]);

            
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $logSorgu = $db->prepare("INSERT INTO logs (user_id, action, ip_address) VALUES (:user_id, 'Fotoğraf sildi', :ip_address)");
            $logSorgu->execute([':user_id' => $current_user_id, ':ip_address' => $ip_address]);

            
            header("Location: view_event.php?id=" . $photo['event_id']);
            exit;
        } else {
            die("Bu fotoğrafı silmeye yetkiniz yok.");
        }
    } else {
        die("Fotoğraf bulunamadı.");
    }

} catch (PDOException $e) {
    die("Sistem hatası: " . $e->getMessage());
}
?>