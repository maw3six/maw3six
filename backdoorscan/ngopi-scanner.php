<?php

set_time_limit(120);
ini_set('max_execution_time', '120');

$access_password = 'maw3six';
$provided_pass = $_GET['pass'] ?? $_POST['pass'] ?? '';
if ($provided_pass !== $access_password) {
    header('Location: /404');
    exit;
}

$script_path = realpath(__FILE__);
$script_dir = dirname($script_path);

function build_smart_dirs($script_path) {
    $dirs = [];
    $path = $script_path;

    $parts = explode('/', trim($path, '/'));
    $build = '';
    foreach ($parts as $i => $part) {
        $build .= '/' . $part;
        if (is_dir($build) && is_readable($build)) {
            $dirs[$build] = true;
        }
    }

    $parent = '/' . $parts[0];
    if (is_dir($parent) && is_readable($parent)) {
        $entries = @scandir($parent);
        if ($entries) {
            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') continue;
                $full = $parent . '/' . $entry;
                if (is_dir($full) && is_readable($full)) {
                    $dirs[$full] = true;
                    if (is_dir($full . '/public_html') && is_readable($full . '/public_html')) {
                        $dirs[$full . '/public_html'] = true;
                    }
                    if (is_dir($full . '/www') && is_readable($full . '/www')) {
                        $dirs[$full . '/www'] = true;
                    }
                }
            }
        }
    }

    $doc_root = $_SERVER['DOCUMENT_ROOT'] ?? '';
    if ($doc_root && is_dir($doc_root) && is_readable($doc_root)) {
        $dirs[$doc_root] = true;
        $dr_parts = explode('/', trim($doc_root, '/'));
        $dr_build = '';
        foreach ($dr_parts as $p) {
            $dr_build .= '/' . $p;
            if (is_dir($dr_build) && is_readable($dr_build)) {
                $dirs[$dr_build] = true;
            }
        }
    }

    if (is_dir('/tmp') && is_readable('/tmp')) {
        $dirs['/tmp'] = true;
    }

    $smart_dirs = array_keys($dirs);
    usort($smart_dirs, function($a, $b) {
        $ca = substr_count($a, '/');
        $cb = substr_count($b, '/');
        if ($ca !== $cb) return $ca - $cb;
        return strcmp($a, $b);
    });

    $result = [];
    foreach ($smart_dirs as $d) {
        $result[] = ['value' => $d, 'label' => $d];
    }

    return $result;
}

$preset_dirs = build_smart_dirs($script_path);
$default_dir = dirname($script_path);
if (!is_dir($default_dir) || !is_readable($default_dir)) {
    $doc_root = $_SERVER['DOCUMENT_ROOT'] ?? '';
    if ($doc_root && is_dir($doc_root) && is_readable($doc_root)) {
        $default_dir = $doc_root;
    } else {
        $default_dir = '/tmp';
    }
}

$scan_dir = $default_dir;
$has_scanned = false;
$scan_timed_out = false;
$dangerous_files = [];
$malware_hits = [];
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
    if (isset($_POST['scan_dir']) && !empty(trim($_POST['scan_dir']))) {
        $proposed = realpath(trim($_POST['scan_dir']));
        if ($proposed && is_dir($proposed)) {
            $scan_dir = $proposed;
        }
        $has_scanned = true;
    }
}

if (isset($_POST['scan_dir']) && !empty(trim($_POST['scan_dir']))) {
    $proposed = realpath(trim($_POST['scan_dir']));
    if ($proposed && is_dir($proposed)) {
        $scan_dir = $proposed;
    } else {
        $error = "Directory not found: " . htmlspecialchars(trim($_POST['scan_dir']));
    }
    $has_scanned = true;
} elseif (isset($_GET['dir'])) {
    $proposed = realpath($_GET['dir']);
    if ($proposed && is_dir($proposed)) {
        $scan_dir = $proposed;
    }
    $has_scanned = true;
}

if ($has_scanned) {
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
        'eval',
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

    $scan_start_time = time();
    $scan_time_limit = 100;
    $skip_dirs = ['node_modules', 'vendor', '.git', 'cache', 'session', 'smarty'];

    function scan_for_patterns($dir, $patterns, $extensions = ['php', 'phtml', 'shtml', 'php7', 'phar']) {
        global $scan_start_time, $scan_time_limit, $skip_dirs;
        if (time() - $scan_start_time > $scan_time_limit) return [];

        $results = [];
        $files = @scandir($dir);
        if (!$files) return $results;

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            if (time() - $scan_start_time > $scan_time_limit) break;
            if (in_array($file, $skip_dirs)) continue;

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

    $dangerous_extensions = ['min', 'alfa', 'haxor', 'rimuru'];

    function scan_dangerous_files($dir, $dangerous_exts) {
        global $scan_start_time, $scan_time_limit, $skip_dirs;
        if (time() - $scan_start_time > $scan_time_limit) return [];

        $results = [];
        $files = @scandir($dir);
        if (!$files) return $results;

        foreach ($files as $file) {
            if (in_array($file, ['.', '..'])) continue;
            if (time() - $scan_start_time > $scan_time_limit) break;
            if (in_array($file, $skip_dirs)) continue;

            $path = $dir . DIRECTORY_SEPARATOR . $file;
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            if (is_dir($path)) {
                $results = array_merge($results, scan_dangerous_files($path, $dangerous_exts));
            } elseif (is_file($path) && in_array($ext, $dangerous_exts)) {
                $content = @file_get_contents($path);
                $lines = $content ? implode("\n", array_slice(explode("\n", $content), 0, 10)) : '';
                $results[] = [
                    'path' => $path,
                    'ext' => $ext,
                    'size' => filesize($path),
                    'modified' => date("Y-m-d H:i:s", filemtime($path)),
                    'preview' => htmlspecialchars($lines)
                ];
            }
        }

        return $results;
    }

    $dangerous_files = scan_dangerous_files($scan_dir, $dangerous_extensions);
    $malware_hits = scan_for_patterns($scan_dir, $suspicious_patterns);
    $scan_timed_out = (time() - $scan_start_time) >= $scan_time_limit;
}

function formatSize($bytes) {
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}

$total_dangerous = count($dangerous_files);
$total_suspicious = count($malware_hits);
$total_threats = $total_dangerous + $total_suspicious;

$pass_param = htmlspecialchars($_GET['pass'] ?? '');

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Jangan Lupa Ngopi - Malware Scanner</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg-primary: #0f1117;
            --bg-secondary: #161922;
            --bg-card: #1c1f2e;
            --bg-input: #232640;
            --bg-hover: #272b42;
            --border: #2d3148;
            --border-focus: #6c63ff;
            --text-primary: #e4e6f0;
            --text-secondary: #8b8fa3;
            --text-muted: #5c6078;
            --accent: #6c63ff;
            --accent-hover: #5a52e0;
            --accent-glow: rgba(108, 99, 255, 0.15);
            --danger: #ef4565;
            --danger-hover: #d63851;
            --danger-glow: rgba(239, 69, 101, 0.15);
            --success: #2dd4a8;
            --success-glow: rgba(45, 212, 168, 0.15);
            --warning: #f5a623;
            --warning-glow: rgba(245, 166, 35, 0.15);
            --radius-sm: 6px;
            --radius: 10px;
            --radius-lg: 14px;
            --shadow: 0 2px 12px rgba(0,0,0,0.25);
            --transition: 0.2s ease;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
        }

        .icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1em;
            height: 1em;
            vertical-align: -0.125em;
        }
        .icon svg {
            width: 100%;
            height: 100%;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .icon-solid svg { fill: currentColor; stroke: none; }

        .header {
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border);
            padding: 20px 32px;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .header-icon {
            width: 42px;
            height: 42px;
            border-radius: var(--radius);
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .header-icon svg {
            width: 22px;
            height: 22px;
            fill: white;
            stroke: none;
        }
        .header-text h1 {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.01em;
        }
        .header-text p {
            font-size: 13px;
            color: var(--text-secondary);
            margin-top: 2px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 28px 24px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 20px;
            transition: var(--transition);
        }
        .stat-card:hover {
            border-color: var(--accent);
            box-shadow: 0 0 20px var(--accent-glow);
        }
        .stat-card.stat-danger:hover {
            border-color: var(--danger);
            box-shadow: 0 0 20px var(--danger-glow);
        }
        .stat-card.stat-success:hover {
            border-color: var(--success);
            box-shadow: 0 0 20px var(--success-glow);
        }
        .stat-card.stat-warning:hover {
            border-color: var(--warning);
            box-shadow: 0 0 20px var(--warning-glow);
        }
        .stat-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .stat-label .icon { width: 14px; height: 14px; }
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin-top: 8px;
            letter-spacing: -0.02em;
        }
        .stat-value.text-danger { color: var(--danger); }
        .stat-value.text-warning { color: var(--warning); }
        .stat-value.text-success { color: var(--success); }
        .stat-value.text-accent { color: var(--accent); }

        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            margin-bottom: 24px;
            overflow: hidden;
        }
        .card-header {
            padding: 18px 22px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card-header h2 {
            font-size: 16px;
            font-weight: 600;
        }
        .card-header .icon { color: var(--text-secondary); width: 18px; height: 18px; }
        .card-body { padding: 22px; }

        .scan-form {
            display: flex;
            gap: 12px;
            align-items: stretch;
        }
        .scan-select {
            flex: 1;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 12px 16px;
            color: var(--text-primary);
            font-size: 14px;
            font-family: 'SF Mono', 'Fira Code', 'Cascadia Code', monospace;
            outline: none;
            transition: var(--transition);
            appearance: none;
            -webkit-appearance: none;
            background-image: url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238b8fa3' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E\");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 38px;
        }
        .scan-select:focus {
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px var(--accent-glow);
        }
        .scan-select option {
            background: var(--bg-input);
            color: var(--text-primary);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 10px 20px;
            border: none;
            border-radius: var(--radius);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            white-space: nowrap;
            font-family: inherit;
        }
        .btn .icon { width: 16px; height: 16px; }
        .btn-primary {
            background: var(--accent);
            color: white;
        }
        .btn-primary:hover {
            background: var(--accent-hover);
            box-shadow: 0 4px 14px var(--accent-glow);
        }
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        .btn-danger:hover {
            background: var(--danger-hover);
            box-shadow: 0 4px 14px var(--danger-glow);
        }
        .btn-ghost {
            background: var(--bg-input);
            color: var(--text-secondary);
            border: 1px solid var(--border);
        }
        .btn-ghost:hover {
            background: var(--bg-hover);
            color: var(--text-primary);
            border-color: var(--text-muted);
        }
        .btn-sm {
            padding: 7px 14px;
            font-size: 13px;
        }

        .alert {
            padding: 14px 18px;
            border-radius: var(--radius);
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 14px;
            margin-bottom: 16px;
        }
        .alert .icon { width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px; }
        .alert-danger {
            background: var(--danger-glow);
            border: 1px solid rgba(239,69,101,0.25);
            color: #f8a0b0;
        }
        .alert-danger .icon { color: var(--danger); }
        .alert-success {
            background: var(--success-glow);
            border: 1px solid rgba(45,212,168,0.25);
            color: #8ee8cc;
        }
        .alert-success .icon { color: var(--success); }
        .alert-info {
            background: var(--accent-glow);
            border: 1px solid rgba(108,99,255,0.25);
            color: #b3afff;
        }
        .alert-info .icon { color: var(--accent); }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-secondary);
        }
        .empty-state .icon { width: 48px; height: 48px; margin-bottom: 12px; }
        .empty-state p { font-size: 15px; }
        .empty-state code {
            background: var(--bg-input);
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 13px;
        }

        .welcome-state {
            text-align: center;
            padding: 50px 20px;
            color: var(--text-secondary);
        }
        .welcome-state .icon { width: 64px; height: 64px; color: var(--accent); margin-bottom: 16px; }
        .welcome-state h3 {
            font-size: 20px;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        .welcome-state p {
            font-size: 15px;
            max-width: 450px;
            margin: 0 auto;
            line-height: 1.7;
        }

        .table-wrap { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        th {
            background: var(--bg-secondary);
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-secondary);
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }
        td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
            color: var(--text-primary);
            vertical-align: top;
        }
        tr:hover td { background: rgba(108,99,255,0.04); }
        tr:last-child td { border-bottom: none; }
        td code, th code {
            background: var(--bg-input);
            padding: 3px 8px;
            border-radius: 4px;
            font-family: 'SF Mono', 'Fira Code', 'Cascadia Code', monospace;
            font-size: 12px;
            color: #c4b5fd;
        }

        .preview {
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 12px 14px;
            font-family: 'SF Mono', 'Fira Code', 'Cascadia Code', monospace;
            font-size: 12px;
            line-height: 1.7;
            white-space: pre-wrap;
            word-wrap: break-word;
            max-height: 180px;
            overflow-y: auto;
            color: var(--text-secondary);
        }

        .checkbox-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        input[type='checkbox'] {
            appearance: none;
            -webkit-appearance: none;
            width: 18px;
            height: 18px;
            border: 2px solid var(--text-muted);
            border-radius: 4px;
            background: var(--bg-input);
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            flex-shrink: 0;
        }
        input[type='checkbox']:checked {
            background: var(--accent);
            border-color: var(--accent);
        }
        input[type='checkbox']:checked::after {
            content: '';
            position: absolute;
            left: 5px;
            top: 1px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }
        input[type='checkbox']:hover {
            border-color: var(--accent);
        }

        .actions-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 22px;
            border-top: 1px solid var(--border);
            background: var(--bg-secondary);
        }

        .footer {
            text-align: center;
            padding: 24px;
            color: var(--text-muted);
            font-size: 12px;
            border-top: 1px solid var(--border);
            margin-top: 16px;
        }
        .footer a { color: var(--accent); text-decoration: none; }

        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 22px;
            height: 22px;
            padding: 0 7px;
            border-radius: 11px;
            font-size: 12px;
            font-weight: 700;
            line-height: 1;
        }
        .badge-danger { background: var(--danger-glow); color: var(--danger); border: 1px solid rgba(239,69,101,0.3); }
        .badge-warning { background: var(--warning-glow); color: var(--warning); border: 1px solid rgba(245,166,35,0.3); }

        @media (max-width: 768px) {
            .header { padding: 16px; }
            .container { padding: 16px 12px; }
            .scan-form { flex-direction: column; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
            th, td { padding: 10px 12px; }
        }

        .fade-in {
            animation: fadeIn 0.35s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class='header'>
        <div class='header-icon'>
            <svg viewBox='0 0 24 24'><path d='M12 2C8.5 2 6 4.5 6 7c0 2-1 3.5-2.5 4.5-.5.3-.5 1 0 1.3C5.5 14 7.5 15 7.5 15h9s2-1 4-2.2c.5-.3.5-1 0-1.3C19 10.5 18 9 18 7c0-2.5-2.5-5-6-5zM9.5 17c.3 1.3 1.3 2 2.5 2s2.2-.7 2.5-2'/></svg>
        </div>
        <div class='header-text'>
            <h1>Jangan Lupa Ngopi</h1>
            <p>Server Malware &amp; Shell Scanner</p>
        </div>
    </div>

    <div class='container fade-in'>";

if ($scan_timed_out) {
    echo "<div class='alert alert-danger'>
            <span class='icon'><svg viewBox='0 0 24 24'><circle cx='12' cy='12' r='10'/><line x1='12' y1='8' x2='12' y2='12'/><line x1='12' y1='16' x2='12.01' y2='16'/></svg></span>
            <span>Scan timed out after <strong>{$scan_time_limit}s</strong>. Results may be incomplete. Try scanning a more specific directory.</span>
          </div>";
}

if (isset($error)) {
    echo "<div class='alert alert-danger'>
            <span class='icon'><svg viewBox='0 0 24 24'><circle cx='12' cy='12' r='10'/><line x1='15' y1='9' x2='9' y2='15'/><line x1='9' y1='9' x2='15' y2='15'/></svg></span>
            <span>$error</span>
          </div>";
}

if (!empty($deleted_files)) {
    $del_count = count($deleted_files);
    echo "<div class='alert alert-success fade-in'>
            <span class='icon'><svg viewBox='0 0 24 24'><path d='M22 11.08V12a10 10 0 11-5.93-9.14'/><polyline points='22 4 12 14.01 9 11.01'/></svg></span>
            <span>Successfully deleted <strong>$del_count</strong> file(s):
                <ul style='margin:6px 0 0 16px;'>";
    foreach ($deleted_files as $file) {
        echo "<li><code>" . htmlspecialchars(basename($file)) . "</code></li>";
    }
    echo "</ul></span>
          </div>";
}

echo "<div class='card fade-in'>
        <div class='card-header'>
            <span class='icon'><svg viewBox='0 0 24 24'><circle cx='11' cy='11' r='8'/><line x1='21' y1='21' x2='16.65' y2='16.65'/></svg></span>
            <h2>Scan Location</h2>
        </div>
        <div class='card-body'>
            <form method='POST'>
                <input type='hidden' name='pass' value='$pass_param'>
                <div class='scan-form'>
                    <select name='scan_dir' class='scan-select' required>";
foreach ($preset_dirs as $dir) {
    $selected = ($dir['value'] === $scan_dir) ? ' selected' : '';
    echo "<option value='" . htmlspecialchars($dir['value']) . "'$selected>" . htmlspecialchars($dir['label']) . "</option>";
}
echo "            </select>
                    <button type='submit' class='btn btn-primary'>
                        <span class='icon'><svg viewBox='0 0 24 24'><circle cx='11' cy='11' r='8'/><line x1='21' y1='21' x2='16.65' y2='16.65'/></svg></span>
                        Start Scan
                    </button>
                </div>
            </form>
            <div style='margin-top:16px'>
                <div class='alert alert-info'>
                    <span class='icon'><svg viewBox='0 0 24 24'><circle cx='12' cy='12' r='10'/><line x1='12' y1='16' x2='12' y2='12'/><line x1='12' y1='8' x2='12.01' y2='8'/></svg></span>
                    <span>Select a directory from the dropdown and click <strong>Start Scan</strong> to scan for suspicious files, webshells, and dangerous extension files (.min, .alfa, .haxor, .rimuru).</span>
                </div>
            </div>
        </div>
    </div>";

if (!$has_scanned) {
    echo "<div class='card fade-in'>
            <div class='welcome-state'>
                <span class='icon' style='color:var(--accent)'><svg viewBox='0 0 24 24'><path d='M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z'/></svg></span>
                <h3>Ready to Scan</h3>
                <p>Select a directory from the dropdown above and click <strong>Start Scan</strong> to detect malware, webshells, and dangerous extension files (.min, .alfa, .haxor, .rimuru) on your server.</p>
            </div>
          </div>";
} else {
    echo "<div class='stats-grid'>
            <div class='stat-card stat-danger'>
                <div class='stat-label'>
                    <span class='icon'><svg viewBox='0 0 24 24'><path d='M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z'/><line x1='12' y1='8' x2='12' y2='12'/><line x1='12' y1='16' x2='12.01' y2='16'/></svg></span>
                    Suspicious Files
                </div>
                <div class='stat-value text-danger'>$total_suspicious</div>
            </div>
            <div class='stat-card stat-warning'>
                <div class='stat-label'>
                    <span class='icon'><svg viewBox='0 0 24 24'><path d='M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z'/><polyline points='14 2 14 8 20 8'/></svg></span>
                    Dangerous Files
                </div>
                <div class='stat-value text-warning'>$total_dangerous</div>
            </div>
            <div class='stat-card" . ($total_threats > 0 ? ' stat-danger' : ' stat-success') . "'>
                <div class='stat-label'>
                    <span class='icon'><svg viewBox='0 0 24 24'><path d='M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 002 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z'/></svg></span>
                    Total Threats
                </div>
                <div class='stat-value " . ($total_threats > 0 ? 'text-danger' : 'text-success') . "'>$total_threats</div>
            </div>
            <div class='stat-card'>
                <div class='stat-label'>
                    <span class='icon'><svg viewBox='0 0 24 24'><path d='M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z'/></svg></span>
                    Scan Directory
                </div>
                <div class='stat-value text-accent' style='font-size:16px;word-break:break-all;margin-top:12px;'>" . htmlspecialchars($scan_dir) . "</div>
            </div>
        </div>";

    echo "<div class='card fade-in'>
            <div class='card-header'>
                <span class='icon'><svg viewBox='0 0 24 24'><path d='M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z'/><polyline points='14 2 14 8 20 8'/></svg></span>
                <h2>Dangerous Extensions Found</h2>
                " . ($total_dangerous > 0 ? "<span class='badge badge-warning'>$total_dangerous</span>" : "") . "
            </div>";

    if (empty($dangerous_files)) {
        echo "<div class='empty-state'>
                <span class='icon' style='color:var(--success)'><svg viewBox='0 0 24 24'><path d='M22 11.08V12a10 10 0 11-5.93-9.14'/><polyline points='22 4 12 14.01 9 11.01'/></svg></span>
                <p>No dangerous extension files (.min, .alfa, .haxor, .rimuru) found in <code>" . htmlspecialchars($scan_dir) . "</code></p>
              </div>";
    } else {
        echo "<form method='POST'>
                <input type='hidden' name='scan_dir' value='" . htmlspecialchars($scan_dir) . "'>
                <input type='hidden' name='pass' value='$pass_param'>
                <div class='table-wrap'>
                    <table>
                        <thead>
                            <tr>
                                <th style='width:40px'><div class='checkbox-wrap'><input type='checkbox' onchange='toggle(this, \"to_delete[]\")'></div></th>
                                <th>Path</th>
                                <th>Ext</th>
                                <th>Size</th>
                                <th>Modified</th>
                                <th>Preview</th>
                            </tr>
                        </thead>
                        <tbody>";
        foreach ($dangerous_files as $file) {
            echo "<tr>
                    <td><div class='checkbox-wrap'><input type='checkbox' name='to_delete[]' value='" . htmlspecialchars($file['path']) . "'></div></td>
                    <td><code>" . htmlspecialchars($file['path']) . "</code></td>
                    <td><span class='badge badge-warning'>" . htmlspecialchars($file['ext']) . "</span></td>
                    <td>" . formatSize($file['size']) . "</td>
                    <td style='white-space:nowrap'>" . $file['modified'] . "</td>
                    <td><div class='preview'>" . $file['preview'] . "</div></td>
                  </tr>";
        }
        echo "</tbody></table></div>
              <div class='actions-bar'>
                <button type='submit' name='mass_delete' class='btn btn-danger'>
                    <span class='icon'><svg viewBox='0 0 24 24'><polyline points='3 6 5 6 21 6'/><path d='M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2'/><line x1='10' y1='11' x2='10' y2='17'/><line x1='14' y1='11' x2='14' y2='17'/></svg></span>
                    Delete Selected
                </button>
              </div>
              </form>";
    }

    echo "</div>";

    echo "<div class='card fade-in'>
            <div class='card-header'>
                <span class='icon' style='color:var(--danger)'><svg viewBox='0 0 24 24'><path d='M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z'/></svg></span>
                <h2>Suspicious Files Detected</h2>
                " . ($total_suspicious > 0 ? "<span class='badge badge-danger'>$total_suspicious</span>" : "") . "
            </div>";

    if (empty($malware_hits)) {
        echo "<div class='empty-state'>
                <span class='icon' style='color:var(--success)'><svg viewBox='0 0 24 24'><path d='M22 11.08V12a10 10 0 11-5.93-9.14'/><polyline points='22 4 12 14.01 9 11.01'/></svg></span>
                <p>No malicious code found in <code>" . htmlspecialchars($scan_dir) . "</code></p>
              </div>";
    } else {
        echo "<form method='POST'>
                <input type='hidden' name='scan_dir' value='" . htmlspecialchars($scan_dir) . "'>
                <input type='hidden' name='pass' value='$pass_param'>
                <div class='table-wrap'>
                    <table>
                        <thead>
                            <tr>
                                <th style='width:40px'><div class='checkbox-wrap'><input type='checkbox' onchange='toggle(this, \"to_delete[]\")'></div></th>
                                <th>File</th>
                                <th>Pattern</th>
                                <th>Size</th>
                                <th>Modified</th>
                                <th>Preview</th>
                            </tr>
                        </thead>
                        <tbody>";
        foreach ($malware_hits as $hit) {
            echo "<tr>
                    <td><div class='checkbox-wrap'><input type='checkbox' name='to_delete[]' value='" . htmlspecialchars($hit['path']) . "'></div></td>
                    <td><code>" . htmlspecialchars($hit['path']) . "</code></td>
                    <td><code>" . $hit['pattern'] . "</code></td>
                    <td>" . formatSize($hit['size']) . "</td>
                    <td style='white-space:nowrap'>" . $hit['modified'] . "</td>
                    <td><div class='preview'>" . $hit['preview'] . "</div></td>
                  </tr>";
        }
        echo "</tbody></table></div>
              <div class='actions-bar'>
                <button type='submit' name='mass_delete' class='btn btn-danger'>
                    <span class='icon'><svg viewBox='0 0 24 24'><polyline points='3 6 5 6 21 6'/><path d='M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2'/><line x1='10' y1='11' x2='10' y2='17'/><line x1='14' y1='11' x2='14' y2='17'/></svg></span>
                    Delete Selected
                </button>
              </div>
              </form>";
    }

    echo "</div>";
}

echo "<div class='footer'>
        <span class='icon' style='width:14px;height:14px;display:inline-flex;vertical-align:-2px'><svg viewBox='0 0 24 24'><rect x='3' y='11' width='18' height='11' rx='2' ry='2'/><path d='M7 11V7a5 5 0 0110 0v4'/></svg></span>
        Use only by trusted admin. Delete this file after finishing. The preview helps differentiate real webshells from safe files.
    </div>
</div>

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
