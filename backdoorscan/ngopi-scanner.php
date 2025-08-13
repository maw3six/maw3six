<?php

$access_password = 'maw3six';
if (!isset($_GET['pass']) || $_GET['pass'] !== $access_password) {
    http_response_code(403);
    die('<h3 style="color:red;">Access Denied. Wrong password.</h3>');
}

$default_dir = $_SERVER['DOCUMENT_ROOT'] ?? '/var/www/html';
if (!is_dir($default_dir)) {
    $default_dir = '/home';
}

$scan_dir = $default_dir;

if (isset($_POST['scan_dir']) && !empty(trim($_POST['scan_dir']))) {
    $proposed = realpath(trim($_POST['scan_dir']));
    if ($proposed && is_dir($proposed)) {
        $scan_dir = $proposed;
    } else {
        $error = "❌ Directory not found: " . htmlspecialchars(trim($_POST['scan_dir']));
    }
} elseif (isset($_GET['dir'])) {
    $proposed = realpath($_GET['dir']);
    if ($proposed && is_dir($proposed)) {
        $scan_dir = $proposed;
    }
}

$suspicious_patterns = [
    'title>Gecko ',
    'date_default_timezone_set("Asia/Jakarta");',
    'goto sHNkh; sHNkh: $EnoeA = tmpfile();',

    'if(!isset($_COOKIE[\'Pass\'])',
    'WSO_SHELL',
    'eval($_POST[\'code\'])',
    'action=cmd&',
    'name="cmd"',

    'c99sh',
    'c99shell',
    'cmd=' . chr(36) . '_POST[\'cmd\']',
    'goto FORM_ACTION',

    'r57shell',
    'Sistem: ' . chr(36) . '_SERVER[\'SERVER_SOFTWARE\']',

    'b374k',
    'b374k - Priv8',

    'antichat',
    'Antichat.ru Shell',

    'eval(base64_decode(',
    'assert(base64_decode(',
    'preg_replace("/.*/e",',
    'create_function(',
    'system($_GET[',
    'exec($_POST[',
    'shell_exec($_REQUEST[',
    'passthru($_GET[',
    'popen($_POST[',
    'proc_open($_GET[',
    'include($_GET[',
    'require($_POST[',

    'file_get_contents($_GET[',
    'curl_exec(',
    'fsockopen(',
    'pfsockopen(',

    'gzinflate(base64_decode(',
    'str_rot13(',
    'pack("H*",',
    'hex2bin(',

    'auto_prepend_file',
    'php_value auto_prepend_file',

    '$auth_pass =',
    'if(isset($_POST[\'code\'])',
    'function actionphp()',
    'cmd=' . chr(36) . '_POST',
    'cmd=' . chr(36) . '_GET',
];

function scan_for_patterns($dir, $patterns, $extensions = ['php', 'phtml', 'shtml', 'php7', 'phar']) {
    $results = [];
    $files = @scandir($dir);
    if (!$files) return $results;

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;

        $path = $dir . DIRECTORY_SEPARATOR . $file;
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (is_dir($path)) {
            $results = array_merge($results, scan_for_patterns($path, $patterns, $extensions));
        } elseif (is_file($path) && in_array($ext, $extensions)) {
            $content = @file_get_contents($path);
            if ($content === false) continue;

            foreach ($patterns as $pattern) {
                if (stripos($content, $pattern) !== false) {
                    $lines = explode("\n", $content);
                    $preview = implode("\n", array_slice($lines, 0, 10));
                    $results[] = [
                        'path' => $path,
                        'pattern' => htmlspecialchars($pattern),
                        'size' => filesize($path),
                        'modified' => date("Y-m-d H:i:s", filemtime($path)),
                        'preview' => htmlspecialchars($preview)
                    ];
                    break;
                }
            }
        }
    }

    return $results;
}

function scan_min_files($dir) {
    $results = [];
    $files = @scandir($dir);
    if (!$files) return $results;

    foreach ($files as $file) {
        if (in_array($file, ['.', '..'])) continue;

        $path = $dir . DIRECTORY_SEPARATOR . $file;

        if (is_dir($path)) {
            $results = array_merge($results, scan_min_files($path));
        } elseif (is_file($path)) {
            if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'min') {
                $content = @file_get_contents($path);
                $lines = $content ? implode("\n", array_slice(explode("\n", $content), 0, 10)) : '';
                $results[] = [
                    'path' => $path,
                    'size' => filesize($path),
                    'modified' => date("Y-m-d H:i:s", filemtime($path)),
                    'preview' => htmlspecialchars($lines)
                ];
            }
        }
    }

    return $results;
}

$deleted_files = [];
if (isset($_POST['mass_delete']) && !empty($_POST['to_delete'])) {
    foreach ($_POST['to_delete'] as $file_path) {
        $file_path = trim($file_path);
        if (file_exists($file_path) && is_file($file_path)) {
            $realpath = realpath($file_path);
            if (!$realpath) continue;

            $forbidden_paths = ['/etc', '/bin', '/sbin', '/usr/bin', '/root'];
            $root = substr($realpath, 0, stripos($realpath, '/') === 0 ? 1 : 0);
            if (in_array($root, $forbidden_paths)) continue;

            if (@unlink($file_path)) {
                $deleted_files[] = $file_path;
            }
        }
    }
}

$min_files = scan_min_files($scan_dir);
$malware_hits = scan_for_patterns($scan_dir, $suspicious_patterns);

echo "<!DOCTYPE html>
<html>
<head>
    <title>🔍 Jangan Lupa Ngopi Scanner</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        h2, h3 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; background: white; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
        th { background: #e0e0e0; }
        .warning { color: #d80000; }
        .ok { color: green; }
        .btn { padding: 10px 15px; background: #c00; color: white; border: none; cursor: pointer; }
        .btn:hover { background: #900; }
        input[type=checkbox] { transform: scale(1.2); }
        .preview { font-family: monospace; background: #f0f0f0; padding: 10px; border: 1px solid #ccc; white-space: pre-wrap; word-wrap: break-word; margin: 0; }
        .form-group { margin: 10px 0; }
        .error { color: red; }
        .help { font-size: 0.9em; color: #555; }
    </style>
</head>
<body>";

if (isset($error)) {
    echo "<p class='error'>$error</p>";
}

echo "<h3>📁 Scan Location</h3>
<form method='POST'>
    <div class='form-group'>
        <label>Change scan directory:</label><br>
        <input type='text' name='scan_dir' value='" . htmlspecialchars($scan_dir) . "' size='80' placeholder='/home/amvm/public_html'>
        <button type='submit'>🔍 Scan</button>
    </div>
    <div class='form-group'>
        <button type='button' onclick=\"document.querySelector('input[name=\\'scan_dir\\']').value = '" . htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? '/var/www/html') . "';\">📁 Use Document Root</button>
        <button type='button' onclick=\"document.querySelector('input[name=\\'scan_dir\\']').value = '/home';\">Home (/home)</button>
        <button type='button' onclick=\"document.querySelector('input[name=\\'scan_dir\\']').value = '/tmp';\">📁 /tmp</button>
    </div>
</form>
<p class='help'>
    💡 You can scan any readable directory. Examples: 
    <code>/home/amvm/public_html</code>, 
    <code>/var/www/html</code>, 
    <code>/tmp</code>
</p>
<hr>";

if (!empty($deleted_files)) {
    echo "<p class='ok'>✅ Successfully deleted (" . count($deleted_files) . "):</p><ul>";
    foreach ($deleted_files as $file) {
        echo "<li><code>" . htmlspecialchars(basename($file)) . "</code></li>";
    }
    echo "</ul><hr>";
}

echo "<h3>📁 .min Files Found</h3>";
if (empty($min_files)) {
    echo "<p class='ok'>✅ No .min files found in <code>" . htmlspecialchars($scan_dir) . "</code></p>";
} else {
    echo "<form method='POST'>
            <input type='hidden' name='scan_dir' value='" . htmlspecialchars($scan_dir) . "'>
            <button type='submit' name='mass_delete' class='btn'>🗑️ Delete Selected</button>
            <table>
                <tr>
                    <th><input type='checkbox' onchange='toggle(this, \"to_delete[]\")'></th>
                    <th>Path</th>
                    <th>Size</th>
                    <th>Modified</th>
                    <th>Preview (first 10 lines)</th>
                </tr>";
    foreach ($min_files as $file) {
        echo "<tr>
                <td><input type='checkbox' name='to_delete[]' value='" . htmlspecialchars($file['path']) . "'></td>
                <td><code>" . htmlspecialchars($file['path']) . "</code></td>
                <td>" . number_format($file['size']) . " B</td>
                <td>" . $file['modified'] . "</td>
                <td><div class='preview'>" . $file['preview'] . "</div></td>
              </tr>";
    }
    echo "</table>
          <button type='submit' name='mass_delete' class='btn'>🗑️ Delete Selected</button>
          </form>";
}

echo "<h3>🧨 Suspicious Files Detected</h3>";
if (empty($malware_hits)) {
    echo "<p class='ok'>✅ No malicious code found in <code>" . htmlspecialchars($scan_dir) . "</code></p>";
} else {
    echo "<form method='POST'>
            <input type='hidden' name='scan_dir' value='" . htmlspecialchars($scan_dir) . "'>
            <button type='submit' name='mass_delete' class='btn'>🗑️ Delete Selected</button>
            <table>
                <tr>
                    <th><input type='checkbox' onchange='toggle(this, \"to_delete[]\")'></th>
                    <th>File</th>
                    <th>Pattern</th>
                    <th>Size</th>
                    <th>Modified</th>
                    <th>Preview (first 10 lines)</th>
                </tr>";
    foreach ($malware_hits as $hit) {
        echo "<tr>
                <td><input type='checkbox' name='to_delete[]' value='" . htmlspecialchars($hit['path']) . "'></td>
                <td><code>" . htmlspecialchars($hit['path']) . "</code></td>
                <td><code>" . $hit['pattern'] . "</code></td>
                <td>" . number_format($hit['size']) . " B</td>
                <td>" . $hit['modified'] . "</td>
                <td><div class='preview'>" . $hit['preview'] . "</div></td>
              </tr>";
    }
    echo "</table>
          <button type='submit' name='mass_delete' class='btn'>🗑️ Delete Selected</button>
          </form>";
}

echo "<hr>
<p><small>
    🔐 Use only by trusted admin. Delete this file after finishing.<br>
    💡 The preview helps differentiate real webshells from safe files (e.g., base64 in libraries).
</small></p>

<script>
function toggle(source, name) {
    document.querySelectorAll('input[name=\"' + name + '\"]').forEach(cb => {
        cb.checked = source.checked;
    });
}
document.querySelectorAll('form').forEach(form => {
    if (form.querySelector('[type=\"submit\"][name=\"mass_delete\"]')) {
        form.onsubmit = () => confirm('Are you sure you want to delete the selected files?');
    }
});
</script>
</body>
</html>";
?>
