<?php
require_once 'includes/config.php';
requireLogin();

$conn = getConnection();

// Ambil statistik
$total_mhs    = $conn->query("SELECT COUNT(*) as c FROM mahasiswa")->fetch_assoc()['c'];
$total_aktif  = $conn->query("SELECT COUNT(*) as c FROM mahasiswa WHERE status='aktif'")->fetch_assoc()['c'];
$total_jurusan= $conn->query("SELECT COUNT(*) as c FROM jurusan")->fetch_assoc()['c'];
$avg_ipk      = $conn->query("SELECT ROUND(AVG(ipk),2) as c FROM mahasiswa WHERE ipk > 0")->fetch_assoc()['c'];

// Mahasiswa terbaru
$recent = $conn->query("
    SELECT m.nim, m.nama, m.status, m.ipk, m.angkatan, j.nama AS jurusan
    FROM mahasiswa m
    LEFT JOIN jurusan j ON m.jurusan_id = j.id
    ORDER BY m.created_at DESC
    LIMIT 5
");

// Distribusi per angkatan
$angkatan_data = $conn->query("
    SELECT angkatan, COUNT(*) as total
    FROM mahasiswa
    GROUP BY angkatan
    ORDER BY angkatan DESC
    LIMIT 5
");

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Galactic Academy</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
</head>
<body>
<div class="app-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <header class="topbar">
            <button id="menuToggle" style="background:none;border:none;color:var(--text-secondary);font-size:1.3rem;cursor:pointer;display:none;" class="mobile-menu-btn">
                <i class="ti ti-menu-2"></i>
            </button>
            <div class="topbar-breadcrumb">
                <span>Dashboard</span> &nbsp;/&nbsp; Beranda Sistem
            </div>
            <div class="status-online">
                <span class="pulse-dot"></span>
                SYSTEM ONLINE
            </div>
        </header>

        <main class="page-content">
            <?php $flash = getFlash(); if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>"><?= $flash['message'] ?></div>
            <?php endif; ?>

            <div class="page-header">
                <div>
                    <div class="page-title">🛸 Mission Control</div>
                    <div class="page-subtitle">Selamat datang, <?= htmlspecialchars($_SESSION['nama']) ?>. Semua sistem beroperasi normal.</div>
                </div>
            </div>

            <!-- Stat Cards -->
            <div class="stats-grid">
                <div class="stat-card cyan">
                    <div class="stat-icon">👨‍🚀</div>
                    <div class="stat-value"><?= $total_mhs ?></div>
                    <div class="stat-label">Total Mahasiswa</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-icon">🟢</div>
                    <div class="stat-value"><?= $total_aktif ?></div>
                    <div class="stat-label">Mahasiswa Aktif</div>
                </div>
                <div class="stat-card purple">
                    <div class="stat-icon">🪐</div>
                    <div class="stat-value"><?= $total_jurusan ?></div>
                    <div class="stat-label">Program Studi</div>
                </div>
                <div class="stat-card orange">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-value"><?= $avg_ipk ?></div>
                    <div class="stat-label">Rata-rata IPK</div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;flex-wrap:wrap;">

                <!-- Mahasiswa Terbaru -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">🌟 Mahasiswa Terbaru</div>
                        <a href="mahasiswa.php" class="btn btn-cyan btn-sm">Lihat Semua</a>
                    </div>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>NIM</th>
                                    <th>Nama</th>
                                    <th>Jurusan</th>
                                    <th>IPK</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php while($row = $recent->fetch_assoc()): ?>
                                <tr>
                                    <td class="td-nim"><?= $row['nim'] ?></td>
                                    <td><?= htmlspecialchars($row['nama']) ?></td>
                                    <td style="color:var(--text-secondary);font-size:.82rem;"><?= htmlspecialchars($row['jurusan'] ?? '-') ?></td>
                                    <td>
                                        <div class="ipk-bar">
                                            <div class="ipk-track">
                                                <div class="ipk-fill" style="width:<?= ($row['ipk']/4)*100 ?>%"></div>
                                            </div>
                                            <span class="ipk-text"><?= $row['ipk'] ?></span>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-<?= $row['status'] ?>"><?= $row['status'] ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Distribusi Angkatan -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">📡 Per Angkatan</div>
                    </div>
                    <?php while($row = $angkatan_data->fetch_assoc()): 
                        $pct = ($row['total'] / $total_mhs) * 100;
                    ?>
                    <div style="margin-bottom:1rem;">
                        <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                            <span style="font-family:var(--font-mono);font-size:.8rem;color:var(--star-cyan);">Angkatan <?= $row['angkatan'] ?></span>
                            <span style="font-size:.8rem;color:var(--text-muted);"><?= $row['total'] ?> mahasiswa</span>
                        </div>
                        <div class="ipk-track" style="height:6px;">
                            <div class="ipk-fill" style="width:<?= $pct ?>%"></div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

            </div>
        </main>
        <footer class="page-footer">© 2024 GALACTIC ACADEMY · SISTEM INFORMASI AKADEMIK v1.0 · ALL SYSTEMS NOMINAL</footer>
    </div>
</div>
<script>
const toggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
if(toggle) toggle.addEventListener('click', () => sidebar.classList.toggle('open'));
</script>
</body>
</html>