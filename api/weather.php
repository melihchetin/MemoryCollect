<?php
function get_antalya_weather() {
    $url = "https://api.open-meteo.com/v1/forecast?latitude=36.8841&longitude=30.7056&current_weather=true";
    
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        return $data['current_weather']['temperature'] . "°C";
    } catch (Exception $e) {
        return "Veri alınamadı";
    }
}
?>