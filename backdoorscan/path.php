<?php

$documentRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
function collectPathsAndFiles($root) {
    $paths = [];
    $phpFiles = [];

    if (!is_dir($root)) {
        die("Invalid document root: $root");
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $path = $item->getPathname();

        if ($item->isDir()) {
            $paths[] = rtrim($path, '/\\') . '/';
        } elseif ($item->isFile()) {
            if (strtolower($item->getExtension()) === 'php') {
                $phpFiles[] = $item->getFilename();
            }
        }
    }

    $phpFiles = array_unique($phpFiles);

    sort($paths);
    sort($phpFiles);

    return [$paths, $phpFiles];
}

if (isset($_GET['download'])) {
    list($paths, $phpFiles) = collectPathsAndFiles($documentRoot);

    if ($_GET['download'] === 'paths') {
        $content = implode("\n", $paths);
        $filename = 'path.txt';
    } elseif ($_GET['download'] === 'phpfiles') {
        $content = implode("\n", $phpFiles);
        $filename = 'file.txt';
    } else {
        die('Invalid request.');
    }

    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($content));
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    echo $content;
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PHP Path Maker</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f7f9fc;
            margin: 40px;
            color: #333;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
        }
        p {
            font-size: 16px;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 10px 5px;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-path {
            background-color: #3498db;
            color: white;
        }
        .btn-path:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }
        .btn-file {
            background-color: #e67e22;
            color: white;
        }
        .btn-file:hover {
            background-color: #d35400;
            transform: translateY(-2px);
        }
        .note {
            margin-top: 30px;
            padding: 15px;
            background: #f0f7ff;
            border-left: 4px solid #3498db;
            font-size: 14px;
            color: #555;
            max-width: 700px;
        }
        code {
            background: #eee;
            padding: 2px 6px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <h1>📁 PHP Path Maker</h1>
    <p>
        This script recursively scans <strong><?= htmlspecialchars($_SERVER['DOCUMENT_ROOT']) ?></strong> and generates:
    </p>
    <ul>
        <li><strong>path.txt</strong>: All directories, <strong>ending with <code>/</code></strong></li>
        <li><strong>file.txt</strong>: <strong>Only .php file names</strong>, <strong>unique (no duplicates)</strong></li>
    </ul>

    <form method="GET">
        <input type="hidden" name="key" value="<?= htmlspecialchars($access_key) ?>">
        <button type="submit" name="download" value="paths" class="btn btn-path">
            🔽 Download path.txt
        </button>
    </form>

    <form method="GET">
        <input type="hidden" name="key" value="<?= htmlspecialchars($access_key) ?>">
        <button type="submit" name="download" value="phpfiles" class="btn btn-file">
            🔽 Download file.txt
        </button>
    </form>
</body>
</html>
