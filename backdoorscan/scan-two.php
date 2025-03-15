<?php
set_time_limit(0);
error_reporting(0);
@ini_set('zlib.output_compression', 0);
header("Content-Encoding: none");
ob_start();

// Pola aturan untuk mendeteksi kode mencurigakan dalam file PHP
$aturan = [
    'eval' => '/\beval\b.*\b(base64_decode|gzinflate|str_rot13)\b/',
    'remote_code' => '/\b(shell_exec|exec|system|passthru|proc_open|popen|curl_exec)\b/',
    'file_mod' => '/\b(file_put_contents|fopen|fwrite|unlink|move_uploaded_file)\b/',
    'global_vars' => '/\b(GLOBALS|_COOKIE|_REQUEST|_SERVER)\b.*\beval\b/',
    'preg_replace' => '/@preg_replace\b|\b(preg_replace)\b.*\b(e\'\'|\"\")\b/',
    'htaccess' => '/<IfModule mod_rewrite.c>/',
    'phpinfo' => '/\bphpinfo\b.*\(/'
];

// Aksi untuk membaca atau menghapus file
if (isset($_GET['aksi']) && isset($_GET['berkas'])) {
    $berkas = realpath($_GET['berkas']);
    if (strpos($berkas, realpath(".")) !== 0) {
        die("Akses Ditolak!");
    }
    if ($_GET['aksi'] == 'tinggali') {
        echo '<pre>' . htmlspecialchars(file_get_contents($berkas)) . '</pre>';
    } elseif ($_GET['aksi'] == 'hapus') {
        if (unlink($berkas)) {
            echo "Berkas berhasil dihapus!";
        } else {
            echo "Gagal menghapus berkas.";
        }
    }
    exit;
}

// Fungsi untuk mencari semua file PHP di dalam direktori
function daptar_berkas($dir, &$hasil = array()) {
    $ext_php = ['php', 'php3', 'php4', 'php5', 'php7', 'phtml', 'phps']; // Semua varian ekstensi PHP
    $scan = scandir($dir);

    foreach ($scan as $nilai) {
        $lokasi = $dir . DIRECTORY_SEPARATOR . $nilai;
        if (!is_dir($lokasi)) {
            $ext = pathinfo($lokasi, PATHINFO_EXTENSION);
            if (in_array(strtolower($ext), $ext_php)) { // Hanya ambil file dengan ekstensi PHP
                $hasil[] = $lokasi;
            }
        } else if ($nilai != "." && $nilai != "..") {
            daptar_berkas($lokasi, $hasil);
        }
    }
    return $hasil;
}

// Fungsi membaca isi file dengan batasan ukuran maksimal 2MB
function maca($berkas) {
    $ukuran = filesize($berkas) / 1024 / 1024;
    if ($ukuran > 2) return false;
    return file_get_contents($berkas);
}

// Fungsi memeriksa kode mencurigakan berdasarkan aturan regex
function mariosan($konten) {
    global $aturan;
    $hasil = [];
    foreach ($aturan as $nama => $pola) {
        if (preg_match($pola, $konten)) {
            $hasil[] = $nama;
        }
    }
    return $hasil;
}

// Mulai pencarian hanya di file PHP dalam root server
$daptar = daptar_berkas($_SERVER['DOCUMENT_ROOT']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Scanner Panto Tukang</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400&family=Roboto:wght@300;400&display=swap');

    :root {
        --card-bg: rgba(0, 0, 0, 0.6);
        --border-radius: 10px;
    }

    body { 
        font-family: 'Poppins', 'Roboto', 'Segoe UI', sans-serif; 
        text-align: center; 
        background: url('https://images-wixmp-ed30a86b8c4ca887773594c2.wixmp.com/f/2854c4d2-c631-460f-8522-87daf7696438/d8unpnf-307f2546-889c-4739-bd6c-94c04ba5c48d.jpg') no-repeat center center fixed; 
        background-size: cover;
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 1000px;
        margin: 0 auto;
        background-color: var(--card-bg);
        padding: 20px;
        border-radius: var(--border-radius);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin: 15px 0;
        background-color: var(--card-bg);
		color: #ffffff;
		font-size: 10px;
		font-family: 'Poppins', 'Roboto', 'Segoe UI', sans-serif;
    }

    th, td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #444;
        word-wrap: break-word;
        word-break: break-all;
		color: #ffffff;
    }

    .aman { 
        color: #4caf50; 
        font-weight: bold;
    }

    .bahaya { 
        color: #f44336; 
        font-weight: bold;
    }

    .tombol { 
        padding: 6px 12px; 
        margin: 3px; 
        cursor: pointer; 
        border: none; 
        border-radius: 5px; 
        transition: all 0.3s ease; 
        font-size: 10px;
        font-family: 'Poppins', 'Roboto', 'Segoe UI', sans-serif;
		display: flex;
        gap: 15px; /* Jarak antar tombol */
        justify-content: left;
        align-items: center;
    }
	
    .tombol-lihat { 
        background-color: #007bff; 
        color: white; 
		        font-size: 10px;
        font-family: 'Poppins', 'Roboto', 'Segoe UI', sans-serif;
		display: flex;
        gap: 15px; /* Jarak antar tombol */
        justify-content: left;
        align-items: center;
    }

    .tombol-lihat:hover { 
        background-color: #0056b3;
    }

    .tombol-hapus { 
        background-color: #dc3545; 
        color: white;
		        font-size: 10px;
        font-family: 'Poppins', 'Roboto', 'Segoe UI', sans-serif;
		display: flex;
        gap: 15px; /* Jarak antar tombol */
        justify-content: left;
        align-items: center;
    }

    .tombol-hapus:hover { 
        background-color: #a71d2a;
    }

    .respon { 
        overflow-x: auto; 
        background-color: rgba(30, 30, 30, 0.7); 
        padding: 10px; 
        border-radius: 5px; 
        box-shadow: 0 2px 5px rgba(255, 255, 255, 0.1);
        max-height: auto;
        overflow-y: auto;
    }
</style>



</head>
<body>
<div class="container">
    <h1>Ngahapus Panto Tukang</h1>
    <div class="respon">
        <table>
            <tr>
                <th>Berkas</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
            <?php foreach ($daptar as $nilai): if (is_file($nilai)): ?>
                <?php $konten = maca($nilai); $cek = $konten ? mariosan($konten) : []; ?>
                <tr>
                    <td><?php echo $nilai; ?></td>
                    <td class='<?php echo empty($cek) ? "aman" : "bahaya"; ?>'>
                        <?php echo empty($cek) ? "Aman" : "Kapanggih (" . implode(", ", $cek) . ")"; ?>
                    </td>
                    <td>
                        <?php if (!empty($cek)): ?>
                            <button class='tombol tombol-lihat' onclick='tinggaliBerkas("<?php echo addslashes($nilai); ?>")'>Tempo</button>
                            <button class='tombol tombol-hapus' onclick='hapusBerkas("<?php echo addslashes($nilai); ?>")'>Hapus</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; endforeach; ?>
        </table>
    </div>
</div>
    
    <script>
        function tinggaliBerkas(berkas) {
            fetch('?aksi=tinggali&berkas=' + encodeURIComponent(berkas))
            .then(response => response.text())
            .then(data => alert(data))
            .catch(error => alert('Gagal muka berkas'));
        }
        
        function hapusBerkas(berkas) {
            if (confirm('Naha bener rek ngahapus ' + berkas + '?')) {
                fetch('?aksi=hapus&berkas=' + encodeURIComponent(berkas))
                .then(response => response.text())
                .then(data => alert(data))
                .catch(error => alert('Gagal ngahapus berkas'));
            }
        }
    </script>
</body>
</html>
