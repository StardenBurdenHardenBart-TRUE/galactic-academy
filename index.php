<?php
require_once 'includes/config.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi.';
    } else {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT id, username, password, nama_lengkap, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['nama']      = $user['nama_lengkap'];
                $_SESSION['role']      = $user['role'];
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Password salah. Coba lagi.';
            }
        } else {
            $error = 'Username tidak ditemukan.';
        }
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Galactic Academy</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <div class="login-logo">
            <div class="login-logo-icon">🚀</div>
            <div class="login-logo-title">Galactic Academy</div>
            <div class="login-logo-sub">Sistem Informasi Akademik</div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="ti ti-alert-circle"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label"><i class="ti ti-user"></i> Username</label>
                <input type="text" name="username" class="form-control" placeholder="Masukkan username..." 
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" autocomplete="username" required>
            </div>
            <div class="form-group" style="margin-bottom:1.5rem;">
                <label class="form-label"><i class="ti ti-lock"></i> Password</label>
                <input type="password" name="password" class="form-control" placeholder="Masukkan password..." autocomplete="current-password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:.75rem;font-size:1rem;">
                <i class="ti ti-rocket"></i> Masuk ke Sistem
            </button>
        </form>

        <hr class="login-divider">
        <div class="login-footer">
            <div>🔐 SECURE CONNECTION ESTABLISHED</div>
            <div style="margin-top:6px;color:#4a6fa5;">Demo: admin / admin123</div>
        </div>
    </div>
</div>
</body>
</html>