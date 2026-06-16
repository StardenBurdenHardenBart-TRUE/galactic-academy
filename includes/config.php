<?php
// ============================================
// GALACTIC ACADEMY - Konfigurasi Database
// ============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Sesuaikan dengan username MySQL Anda
define('DB_PASS', 'Pisangcoklat1');           // Sesuaikan dengan password MySQL Anda
define('DB_NAME', 'galactic_academy');
define('APP_NAME', 'galactic Academy');
define('APP_VERSION', '1.0');

// Koneksi Database
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die('<div style="font-family:sans-serif;padding:2rem;color:#ff6b6b;">
            <h2>⚠️ Koneksi Database Gagal</h2>
            <p>Error: ' . $conn->connect_error . '</p>
            <p>Pastikan MySQL berjalan dan database <strong>' . DB_NAME . '</strong> sudah dibuat.</p>
            <p>Import file <code>database.sql</code> terlebih dahulu.</p>
        </div>');
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek login
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php');
        exit();
    }
}

// Sanitize input
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// Flash message
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
?>