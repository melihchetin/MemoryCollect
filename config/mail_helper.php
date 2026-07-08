<?php
require_once __DIR__ . '/Exception.php';
require_once __DIR__ . '/PHPMailer.php';
require_once __DIR__ . '/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function anıMailGonder($aliciEmail, $konu, $mesajIcerigi) {
    $mail = new PHPMailer(true);

    try {
        // HATA AYIKLAMA (DEBUG) MODU: Açık! Bize adım adım ne yaptığını ekrana basacak.
        $mail->SMTPDebug = 0; 
        $mail->Debugoutput = 'html';

        // Sunucu Ayarları
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';                     
        $mail->SMTPAuth   = true;                                   
        $mail->Username   = 'omelihcetin03@gmail.com'; 
        $mail->Password   = 'avrr ecew vhkp kyua        '; // Boşluk bırakmadan yaz
        
        // MAC/MAMP İÇİN DAHA STABİL OLAN 587 PORTU VE TLS KULLANIYORUZ
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            
        $mail->Port       = 587;
        
        // MAMP SSL Sertifika hatalarını (geçici olarak) aşmak için güvenlik esnekliği
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        $mail->CharSet    = 'UTF-8';

        // Gönderici ve Alıcı
        $mail->setFrom($mail->Username, 'MemoryCollect Sistem');
        $mail->addAddress($aliciEmail);

        // İçerik
        $mail->isHTML(true);
        $mail->Subject = $konu;
        $mail->Body    = $mesajIcerigi;

        $mail->send();
        return true;
    } catch (Exception $e) {
        // HATA VARSA EKRANA DEV GİBİ KIRMIZI BİR KUTU İÇİNDE BASACAK
        die("<div style='background:#ffcccc; padding:20px; border:2px solid red;'>
                <h2>🚨 SMTP SİSTEM HATASI:</h2>
                <p><b>PHPMailer Diyor ki:</b> " . $mail->ErrorInfo . "</p>
             </div>");
    }
}
?>