<?php
/**
 * Configuration Example untuk Sistem C4.5
 * 
 * Copy file ini ke frontend/database/config.php
 * dan sesuaikan dengan environment Anda
 */

// Database Configuration
$databaseConnection = mysqli_connect(
    "localhost",     // Host
    "root",          // Username
    "",              // Password (kosong untuk XAMPP default)
    "c45"            // Database name
);

// Error handling
if (!$databaseConnection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($databaseConnection, "utf8");

// Optional: Set timezone
date_default_timezone_set('Asia/Jakarta');

// Optional: Error reporting (set to false in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Optional: Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Optional: Upload configuration
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');
ini_set('max_execution_time', 300);

// Optional: Backend API URL
define('BACKEND_URL', 'http://localhost:5000');

// Optional: Application settings
define('APP_NAME', 'Sistem C4.5 Decision Tree');
define('APP_VERSION', '1.0.0');
define('APP_DEBUG', true); // Set to false in production

// Optional: File upload settings
define('UPLOAD_PATH', '../uploads/');
define('ALLOWED_EXTENSIONS', ['xlsx', 'xls']);
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB

// Optional: Security settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutes

// Optional: Email settings (if needed)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_FROM', 'noreply@yourdomain.com');

// Optional: Logging
define('LOG_ENABLED', true);
define('LOG_PATH', '../logs/');
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR

// Optional: Cache settings
define('CACHE_ENABLED', false);
define('CACHE_PATH', '../cache/');
define('CACHE_DURATION', 3600); // 1 hour

// Optional: Backup settings
define('BACKUP_ENABLED', true);
define('BACKUP_PATH', '../backups/');
define('BACKUP_RETENTION', 7); // days

// Optional: API rate limiting
define('RATE_LIMIT_ENABLED', true);
define('RATE_LIMIT_REQUESTS', 100); // requests per hour
define('RATE_LIMIT_WINDOW', 3600); // 1 hour

// Optional: Database backup settings
define('DB_BACKUP_ENABLED', true);
define('DB_BACKUP_PATH', '../database/backups/');
define('DB_BACKUP_RETENTION', 30); // days

// Optional: File cleanup settings
define('CLEANUP_ENABLED', true);
define('CLEANUP_INTERVAL', 86400); // 24 hours
define('TEMP_FILE_RETENTION', 3600); // 1 hour

// Optional: Performance settings
define('QUERY_CACHE_ENABLED', true);
define('QUERY_CACHE_DURATION', 300); // 5 minutes
define('PAGE_CACHE_ENABLED', false);
define('PAGE_CACHE_DURATION', 600); // 10 minutes

// Optional: Monitoring settings
define('MONITORING_ENABLED', true);
define('MONITORING_PATH', '../monitoring/');
define('PERFORMANCE_LOG_ENABLED', true);

// Optional: Development settings
define('DEV_MODE', true);
define('DEBUG_MODE', true);
define('SHOW_QUERIES', false);
define('SHOW_ERRORS', true);

// Optional: Production settings (set these in production)
if (!defined('PRODUCTION_MODE')) {
    define('PRODUCTION_MODE', false);
}

if (PRODUCTION_MODE) {
    error_reporting(0);
    ini_set('display_errors', 0);
    define('APP_DEBUG', false);
    define('DEV_MODE', false);
    define('DEBUG_MODE', false);
    define('SHOW_QUERIES', false);
    define('SHOW_ERRORS', false);
    ini_set('session.cookie_secure', 1);
}

// Optional: Custom functions
function logError($message, $level = 'ERROR') {
    if (LOG_ENABLED) {
        $logFile = LOG_PATH . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
}

function logInfo($message) {
    logError($message, 'INFO');
}

function logWarning($message) {
    logError($message, 'WARNING');
}

function logDebug($message) {
    if (DEBUG_MODE) {
        logError($message, 'DEBUG');
    }
}

// Optional: Database helper functions
function executeQuery($query) {
    global $databaseConnection;
    $result = mysqli_query($databaseConnection, $query);
    if (!$result) {
        logError("Query failed: " . mysqli_error($databaseConnection));
        return false;
    }
    return $result;
}

function getSingleRow($query) {
    $result = executeQuery($query);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

function getAllRows($query) {
    $result = executeQuery($query);
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

// Optional: Security helper functions
function sanitizeInput($input) {
    global $databaseConnection;
    return mysqli_real_escape_string($databaseConnection, trim($input));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Optional: File helper functions
function createUploadDirectory() {
    if (!file_exists(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0755, true);
    }
}

function validateFileUpload($file) {
    $allowedExtensions = ALLOWED_EXTENSIONS;
    $maxFileSize = MAX_FILE_SIZE;
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    if ($file['size'] > $maxFileSize) {
        return false;
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions)) {
        return false;
    }
    
    return true;
}

// Optional: Session helper functions
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    }
    
    if (time() - $_SESSION['created'] > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['created'] = time();
    }
}

function isLoggedIn() {
    return isset($_SESSION['user']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

// Optional: API helper functions
function callBackendAPI($endpoint, $method = 'GET', $data = null) {
    $url = BACKEND_URL . $endpoint;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen(json_encode($data))
            ]);
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    
    logError("API call failed: $endpoint, HTTP Code: $httpCode");
    return false;
}

// Optional: Performance monitoring
function startTimer() {
    return microtime(true);
}

function endTimer($startTime) {
    $endTime = microtime(true);
    $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
    
    if (PERFORMANCE_LOG_ENABLED) {
        logInfo("Execution time: {$executionTime}ms");
    }
    
    return $executionTime;
}

// Optional: Cleanup functions
function cleanupTempFiles() {
    if (!CLEANUP_ENABLED) {
        return;
    }
    
    $tempPath = UPLOAD_PATH . 'temp/';
    if (!is_dir($tempPath)) {
        return;
    }
    
    $files = glob($tempPath . '*');
    $currentTime = time();
    
    foreach ($files as $file) {
        if (is_file($file)) {
            $fileTime = filemtime($file);
            if ($currentTime - $fileTime > TEMP_FILE_RETENTION) {
                unlink($file);
                logInfo("Cleaned up temp file: $file");
            }
        }
    }
}

// Optional: Database backup function
function backupDatabase() {
    if (!DB_BACKUP_ENABLED) {
        return;
    }
    
    $backupPath = DB_BACKUP_PATH;
    if (!is_dir($backupPath)) {
        mkdir($backupPath, 0755, true);
    }
    
    $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    $filepath = $backupPath . $filename;
    
    $command = "mysqldump -u root -p c45 > $filepath";
    exec($command);
    
    logInfo("Database backup created: $filename");
    
    // Cleanup old backups
    $files = glob($backupPath . 'backup_*.sql');
    $currentTime = time();
    
    foreach ($files as $file) {
        $fileTime = filemtime($file);
        if ($currentTime - $fileTime > (DB_BACKUP_RETENTION * 86400)) {
            unlink($file);
            logInfo("Cleaned up old backup: $file");
        }
    }
}

// Optional: Initialize cleanup and backup
if (CLEANUP_ENABLED) {
    register_shutdown_function('cleanupTempFiles');
}

if (DB_BACKUP_ENABLED) {
    // Run backup daily at 2 AM
    $currentHour = (int)date('H');
    if ($currentHour === 2) {
        backupDatabase();
    }
}

// Optional: Error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    if (SHOW_ERRORS) {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin: 10px; border-radius: 5px;'>";
        echo "<strong>Error:</strong> $errstr<br>";
        echo "<strong>File:</strong> $errfile<br>";
        echo "<strong>Line:</strong> $errline<br>";
        echo "</div>";
    }
    
    logError("PHP Error: $errstr in $errfile on line $errline");
    
    return true;
}

set_error_handler('customErrorHandler');

// Optional: Exception handler
function customExceptionHandler($exception) {
    if (SHOW_ERRORS) {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin: 10px; border-radius: 5px;'>";
        echo "<strong>Exception:</strong> " . $exception->getMessage() . "<br>";
        echo "<strong>File:</strong> " . $exception->getFile() . "<br>";
        echo "<strong>Line:</strong> " . $exception->getLine() . "<br>";
        echo "</div>";
    }
    
    logError("Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
}

set_exception_handler('customExceptionHandler');

// Optional: Start secure session
startSecureSession();

// Optional: Create necessary directories
createUploadDirectory();

// Optional: Log application start
logInfo("Application started");

?> 