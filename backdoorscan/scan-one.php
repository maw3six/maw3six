<?php
function checkPermissions() {
    $testDir = sys_get_temp_dir();
    return is_readable($testDir);
}

$currentPath = realpath(dirname(__FILE__));
$pathOptions = [];
$pathParts = explode(DIRECTORY_SEPARATOR, $currentPath);

for ($i = 1; $i <= count($pathParts); $i++) {
    $path = implode(DIRECTORY_SEPARATOR, array_slice($pathParts, 0, $i));
    if (!empty($path)) {
        $pathOptions[] = $path . DIRECTORY_SEPARATOR;
    }
}

$scanPath = isset($_POST['scanPath']) ? $_POST['scanPath'] : $_SERVER['DOCUMENT_ROOT'];
$fileExtensions = ['php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phps', 'pht', 'inc', 'cgi'];
$excludeDirs = ['.git', '.svn', 'node_modules'];

$message = '';
$error = '';

if (isset($_GET['action']) && $_GET['action'] == 'get_content' && isset($_GET['file'])) {
    $file = $_GET['file'];
    if (file_exists($file) && is_readable($file)) {
        echo file_get_contents($file);
    } else {
        echo "Error: Cannot Read This File.";
    }
    exit;
}

if (isset($_POST['action'])) {
    $file = $_POST['file'] ?? '';
    $action = $_POST['action'];
    
    if (!empty($file) && file_exists($file)) {
        switch ($action) {
            case 'delete':
                if (is_writable($file)) {
                    if (@unlink($file)) {
                        $message = "Success Deleted: $file";
                    } else {
                        $error = "Failed: $file";
                    }
                } else {
                    $error = "Failed Error Permission: $file";
                }
                break;
                
            case 'rename':
                $newName = $_POST['new_name'] ?? '';
                if (!empty($newName)) {
                    $newPath = dirname($file) . DIRECTORY_SEPARATOR . $newName;
                    if (is_writable(dirname($file))) {
                        if (@rename($file, $newPath)) {
                            $message = "Success $file to $newName";
                        } else {
                            $error = "Failed to Rename: $file";
                        }
                    } else {
                        $error = "Failed Error Permission: $file";
                    }
                }
                break;
        }
    } else {
        $error = "File Not Found: $file";
    }
}

$rules = [
    'eval' => '/\beval\b.*\b(base64_decode|gzinflate|str_rot13)\b/',
    'remote_code' => '/\b(shell_exec|exec|system|passthru|proc_open|popen|curl_exec)\b/',
    'file_mod' => '/\b(file_put_contents|fopen|fwrite|unlink|move_uploaded_file)\b/',
    'global_vars' => '/\b(GLOBALS|_COOKIE|_REQUEST|_SERVER)\b.*\beval\b/',
    'preg_replace' => '/@preg_replace\b|\b(preg_replace)\b.*\b(e\'\'|\"\")\b/',
    'htaccess' => '/<IfModule mod_rewrite.c>/',
    'phpinfo' => '/\bphpinfo\b.*\(/'
];

function scanFile($filePath, $rules) {
    if (!is_readable($filePath)) {
        return [
            'detected' => false,
            'matches' => [],
            'content' => '',
            'error' => 'File Cannot Read!'
        ];
    }
    
    try {
        $content = file_get_contents($filePath);
        $matches = [];
        $detected = false;
        
        foreach ($rules as $ruleName => $pattern) {
            if (preg_match($pattern, $content)) {
                $matches[] = $ruleName;
                $detected = true;
            }
        }
        
        return [
            'detected' => $detected,
            'matches' => $matches,
            'content' => $content,
            'error' => ''
        ];
    } catch (Exception $e) {
        return [
            'detected' => false,
            'matches' => [],
            'content' => '',
            'error' => $e->getMessage()
        ];
    }
}

function scanDirectory($dir, $rules, $fileExtensions, $excludeDirs) {
    $results = [];
    $errors = [];
    
    if (!is_readable($dir)) {
        return ['results' => $results, 'errors' => ["Dir Cannot Read: $dir"]];
    }
    
    try {
        $files = new DirectoryIterator($dir);
        
        foreach ($files as $file) {
            if ($file->isDot()) {
                continue;
            }
            
            $path = $file->getPathname();
            $basename = $file->getBasename();
            
            if ($file->isDir() && in_array($basename, $excludeDirs)) {
                continue;
            }
            
            if ($file->isDir()) {
                $subResults = scanDirectory($path, $rules, $fileExtensions, $excludeDirs);
                $results = array_merge($results, $subResults['results']);
                $errors = array_merge($errors, $subResults['errors']);
            } elseif ($file->isFile()) {
                $extension = pathinfo($path, PATHINFO_EXTENSION);
                if (in_array(strtolower($extension), $fileExtensions)) {
                    $scanResult = scanFile($path, $rules);
                    if ($scanResult['detected']) {
                        $results[] = [
                            'file' => $path,
                            'matches' => $scanResult['matches'],
                            'content' => $scanResult['content']
                        ];
                    }
                    if (!empty($scanResult['error'])) {
                        $errors[] = "Error scanning file $path: " . $scanResult['error'];
                    }
                }
            }
        }
    } catch (Exception $e) {
        $errors[] = "Error scanning directory $dir: " . $e->getMessage();
    }
    
    return ['results' => $results, 'errors' => $errors];
}

function getAccessibleRootDirectories() {
    $roots = [];
    
    if (is_readable('/')) {
        $roots[] = '/';
    }
    
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        foreach (range('A', 'Z') as $drive) {
            $drivePath = $drive . ':\\';
            if (is_readable($drivePath)) {
                $roots[] = $drivePath;
            }
        }
    }

    $roots[] = '/';
    
    if (isset($_SERVER['DOCUMENT_ROOT']) && is_readable($_SERVER['DOCUMENT_ROOT'])) {
        $roots[] = $_SERVER['DOCUMENT_ROOT'];
    }
    
    return $roots;
}

$accessibleRoots = getAccessibleRootDirectories();

$autoScan = true;
if ($autoScan) {
    $scanResults = scanDirectory($scanPath, $rules, $fileExtensions, $excludeDirs);
    $results = $scanResults['results'];
    $scanErrors = $scanResults['errors'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@Maw3six Backdoor Scanner</title>
	<link href="https://fonts.googleapis.com/css2?family=Caesar+Dressing&display=swap" rel="stylesheet">
    <style>
	@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400&family=Roboto:wght@300;400&display=swap');
	
    :root {
        --primary-color: #4a6cf7;
        --success-color: #28a745;
        --danger-color: #dc3545;
        --warning-color: #ffc107;
        --info-color: #17a2b8;
        --light-color: #f8f9fa;
        --dark-color: #343a40;
        --body-bg: #333;
        --card-bg: #222;
        --text-color: #fff;
        --border-radius: 5px;
        --transition: all 0.3s ease;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
    font-family: 'Poppins', 'Roboto', 'Segoe UI', sans-serif; 
    line-height: 1.6;
    color: var(--text-color);
    background: url('https://media2.giphy.com/media/v1.Y2lkPTc5MGI3NjExd29pdTMxNTVwMGFndjkzcmU2cnJ1eWIxcjgwaXRkbmg4c2lhNGxmMyZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9Zw/hvecx8Ly57TE2U8EjQ/giphy.gif') no-repeat center center fixed;
    background-size: cover;
    padding: 20px;
	}

	h1 {
    font-family: 'Caesar Dressing', cursive;
    font-size: 38px; 
    color: #fff;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
    margin-bottom: 15px;
	text-align: center;
	}

    .container {
        max-width: 900px;
        margin: 0 auto;
        background-color: var(--card-bg);
        padding: 20px;
        border-radius: var(--border-radius);
    }

    .alert {
        padding: 10px;
        margin-bottom: 15px;
        border-radius: var(--border-radius);
        font-weight: 500;
    }

    .alert-success {
        background-color: rgba(40, 167, 69, 0.15);
        color: var(--success-color);
    }

    .alert-danger {
        background-color: rgba(220, 53, 69, 0.15);
        color: var(--danger-color);
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin: 15px 0;
        background-color: var(--card-bg);
		font-size: 10px;
		font-family: 'Poppins', 'Roboto', 'Segoe UI', sans-serif;
    }

    th, td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #444;
        word-wrap: break-word;
        word-break: break-all;
    }

    th {
        background-color: #333;
        color: var(--light-color);
    }

    tr:hover {
        background-color: #444;
    }

    .action-text {
        color: var(--primary-color);
        cursor: pointer;
        margin-right: 10px;
        transition: var(--transition);
    }

    .action-text:hover {
        color: var(--light-color);
    }

    .action-buttons {
        display: flex;
        gap: 5px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
    }

    select, input[type="text"] {
        width: 100%;
        padding: 8px;
        border: 1px solid #555;
        border-radius: var(--border-radius);
        background-color: #333;
        color: var(--text-color);
    }

    select:focus, input[type="text"]:focus {
        border-color: var(--primary-color);
        outline: none;
    }

    button {
        padding: 8px 16px;
        background-color: var(--primary-color);
        color: var(--text-color);
        border: none;
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: var(--transition);
		font-size: 10px;
		font-family: 'Poppins', 'Roboto', 'Segoe UI', sans-serif;
    }

    button:hover {
        background-color: #3b5bdb;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.8);
        animation: fadeIn 0.3s;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .modal-content {
        background-color: var(--card-bg);
        margin: 10% auto;
        padding: 20px;
        border-radius: var(--border-radius);
        width: 90%;
        color: var(--text-color);
		word-wrap: break-word;
        word-break: break-all;
    }

    .close {
        color: var(--light-color);
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        transition: var(--transition);
    }

    .close:hover {
        color: var(--primary-color);
    }

    textarea {
        width: 100%;
        height: 200px;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #555;
        border-radius: var(--border-radius);
        background-color: #333;
        color: var(--text-color);
        font-family: 'Courier New', Courier, monospace;
        font-size: 14px;
        line-height: 1.5;
        resize: vertical;
    }

    .footer {
        margin-top: 20px;
        text-align: center;
        color: var(--light-color);
        font-size: 0.9rem;
    }
	
	.scan-form {
        margin-bottom: 25px;
        padding: 20px;
        border-radius: var(--border-radius);
        background-color: var(--card-bg);
    }
</style>
</head>
<body>
    <div class="container">
        <h1>PHP Backdoor Scanner @Maw3six</h1>
        <div class="scan-form">
            <form method="post" class="form-inline">
                <div class="form-group">
                    <select name="scanPath" id="scanPath">
                        <?php foreach ($pathOptions as $option): ?>
                            <option value="<?php echo htmlspecialchars($option); ?>" <?php echo $option === $scanPath ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($option); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit">Start Scanning</button>
            </form>
        </div>
        <?php if (isset($results) || isset($_POST['scan'])): ?>
            <?php if (!empty($scanErrors)): ?>
                <div class="alert alert-warning">
                    <strong>Warning:</strong> Some scanning errors occur during the scanning process
                    <ul>
                        <?php foreach ($scanErrors as $err): ?>
                            <li><?php echo htmlspecialchars($err); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if (empty($results)): ?>
                <div class="alert alert-success">
                    <strong>Safe!</strong> Backdoor Not Found!
                </div>
            <?php else: ?>
                <div class="alert alert-danger">
                    <strong>Warning!</strong> Found <?php echo count($results); ?> Backdoor!
                </div>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 40%;">File</th>
                            <th style="width: 25%;">Matches</th>
                            <th style="width: 30%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($results as $result): ?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td><?php echo htmlspecialchars($result['file']); ?></td>
                            <td><?php echo htmlspecialchars(implode(', ', $result['matches'])); ?></td>
                            <td class="action-buttons">
                                <button class="btn btn-info" onclick="openViewModal('<?php echo htmlspecialchars(addslashes($result['file'])); ?>')">View</button>
                                <button class="btn btn-warning" onclick="openRenameModal('<?php echo htmlspecialchars(addslashes($result['file'])); ?>')">Rename</button>
                                <button class="btn btn-danger" onclick="confirmDelete('<?php echo htmlspecialchars(addslashes($result['file'])); ?>')">Delete</button>
                            </td>
                        </tr>
                        <?php $i++; endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
        
        <!-- Modal View -->
        <div id="viewModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeViewModal()">&times;</span>
                <h2>View File: <span id="viewFile"></span></h2>
                <pre id="fileContentView"></pre>
            </div>
        </div>
        
        <!-- Modal Rename -->
        <div id="renameModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeRenameModal()">&times;</span>
                <h2>Rename File</h2>
                <form method="post">
                    <input type="hidden" id="renameFile" name="file">
                    <input type="hidden" name="action" value="rename">
                    <div class="form-group">
                        <label for="newName">New Name:</label>
                        <input type="text" id="newName" name="new_name" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Rename</button>
                </form>
            </div>
        </div>
        
        <form id="deleteForm" method="post" style="display: none;">
            <input type="hidden" id="deleteFile" name="file">
            <input type="hidden" name="action" value="delete">
        </form>
        
        <script>
            function openViewModal(file) {
                document.getElementById('viewFile').textContent = file;
                
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        document.getElementById('fileContentView').textContent = this.responseText;
                    }
                };
                xhr.open("GET", "?action=get_content&file=" + encodeURIComponent(file), true);
                xhr.send();
                
                document.getElementById('viewModal').style.display = 'block';
            }

            function closeViewModal() {
                document.getElementById('viewModal').style.display = 'none';
            }

            function openRenameModal(file) {
                document.getElementById('renameFile').value = file;
                document.getElementById('newName').value = file;
                document.getElementById('renameModal').style.display = 'block';
            }

            function closeRenameModal() {
                document.getElementById('renameModal').style.display = 'none';
            }

            function confirmDelete(file) {
                if (confirm('Are You Sure To Delete This File: ' + file + '?')) {
                    document.getElementById('deleteFile').value = file;
                    document.getElementById('deleteForm').submit();
                }
            }

            window.onclick = function(event) {
                if (event.target == document.getElementById('viewModal')) {
                    closeViewModal();
                }
                if (event.target == document.getElementById('renameModal')) {
                    closeRenameModal();
                }
            }
        </script>
    </div>
</body>
</html>
