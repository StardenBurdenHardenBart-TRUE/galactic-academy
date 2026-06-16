<?php
$current_page = basename($_SERVER['PHP_SELF']);
$initials = strtoupper(substr($_SESSION['nama'] ?? 'A', 0, 2));
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">🌌</div>
        <div>
            <div class="logo-text">Galactic</div>
            <div class="logo-sub">Academy</div>
        </div>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar"><?= $initials ?></div>
        <div>
            <div class="user-info-name"><?= htmlspecialchars($_SESSION['nama'] ?? 'User') ?></div>
            <div class="user-info-role"><?= htmlspecialchars($_SESSION['role'] ?? 'staff') ?></div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Main</div>
        <a href="dashboard.php" class="nav-item <?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
            <span class="nav-icon">🛸</span> Dashboard
        </a>

        <div class="nav-section-label">Data Akademik</div>
        <a href="mahasiswa.php" class="nav-item <?= $current_page === 'mahasiswa.php' ? 'active' : '' ?>">
            <span class="nav-icon">👨‍🚀</span> Data Mahasiswa
        </a>
        <a href="jurusan.php" class="nav-item <?= $current_page === 'jurusan.php' ? 'active' : '' ?>">
            <span class="nav-icon">🪐</span> Data Jurusan
        </a>
        <a href="cari.php" class="nav-item <?= $current_page === 'cari.php' ? 'active' : '' ?>">
            <span class="nav-icon">🔭</span> Pencarian Data
        </a>

        <div class="nav-section-label">Akun</div>
        <a href="profil.php" class="nav-item <?= $current_page === 'profil.php' ? 'active' : '' ?>">
            <span class="nav-icon">⭐</span> Profil Saya
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php" class="btn-logout">
            <i class="ti ti-logout"></i> Keluar Sistem
        </a>
    </div>
</aside>