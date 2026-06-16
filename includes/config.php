<?php
// ============================================
// GALACTIC ACADEMY - Konfigurasi Database
// ============================================

define('DB_HOST', getenv('MYSQLHOST'));
define('DB_USER', getenv('MYSQLUSER'));
define('DB_PASS', getenv('MYSQLPASSWORD'));
define('DB_NAME', getenv('MYSQLDATABASE'));
define('DB_PORT', getenv('MYSQLPORT'));

define('APP_NAME', 'Galactic Academy');
define('APP_VERSION', '1.0');

// ============================================
// Koneksi Database
// ============================================

function getConnection()
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conn = new mysqli(
            DB_HOST,
            DB_USER,
            DB_PASS,
            DB_NAME,
            (int) DB_PORT
        );

        $conn->set_charset("utf8mb4");

        return $conn;
    } catch (Exception $e) {

        die("
        <div style='font-family:Arial;padding:20px'>
            <h2>Database Error</h2>

            <p><strong>HOST:</strong> " . DB_HOST . "</p>
            <p><strong>USER:</strong> " . DB_USER . "</p>
            <p><strong>DATABASE:</strong> " . DB_NAME . "</p>
            <p><strong>PORT:</strong> " . DB_PORT . "</p>

            <hr>

            <p><strong>ERROR:</strong><br>" . $e->getMessage() . "</p>
        </div>
        ");
    }
}

// ============================================
// Session
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// Login Check
// ============================================

function requireLogin()
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php');
        exit();
    }
}

// ============================================
// Sanitize
// ============================================

function sanitize($input)
{
    return htmlspecialchars(strip_tags(trim($input)));
}

// ============================================
// Flash Message
// ============================================

function setFlash($type, $message)
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlash()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }

    return null;
}
?>