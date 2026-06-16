<?php
// ============================================
// GALACTIC ACADEMY - Konfigurasi Database
// ============================================

// Railway Environment Variables
define('DB_HOST', getenv('MYSQLHOST') ?: 'localhost');
define('DB_USER', getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'galactic_academy');
define('DB_PORT', getenv('MYSQLPORT') ?: 3306);

define('APP_NAME', 'Galactic Academy');
define('APP_VERSION', '1.0');

// ============================================
// DEBUG RAILWAY (sementara)
// ============================================

echo "<div style='font-family:Arial;padding:20px'>";
echo "<h2>Railway Environment Check</h2>";
echo "HOST: " . DB_HOST . "<br>";
echo "USER: " . DB_USER . "<br>";
echo "PASSWORD: " . (DB_PASS ? "ADA" : "KOSONG") . "<br>";
echo "DATABASE: " . DB_NAME . "<br>";
echo "PORT: " . DB_PORT . "<br>";
echo "</div>";
exit;

// ============================================
// Koneksi Database
// ============================================

function getConnection()
{
    $conn = new mysqli(
        DB_HOST,
        DB_USER,
        DB_PASS,
        DB_NAME,
        DB_PORT
    );

    if ($conn->connect_error) {
        die(
            '<div style="font-family:sans-serif;padding:2rem;color:#ff6b6b;">
                <h2>⚠️ Koneksi Database Gagal</h2>
                <p>Error: ' . $conn->connect_error . '</p>
            </div>'
        );
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}

// Session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek login
function requireLogin()
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php');
        exit();
    }
}

// Sanitize input
function sanitize($input)
{
    return htmlspecialchars(strip_tags(trim($input)));
}

// Flash message
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