<?php
require_once 'includes/config.php';
requireLogin();

$conn = getConnection();

$keyword = sanitize($_GET['keyword'] ?? '');

$sql = "
SELECT 
    m.*,
    j.nama AS jurusan_nama
FROM mahasiswa m
LEFT JOIN jurusan j ON m.jurusan_id = j.id
";

$params = [];
$types = '';

if (!empty($keyword)) {
    $sql .= "
    WHERE 
        m.nim LIKE ?
        OR m.nama LIKE ?
        OR m.email LIKE ?
        OR j.nama LIKE ?
    ";

    $search = "%$keyword%";

    $params = [$search, $search, $search, $search];
    $types = 'ssss';
}

$sql .= " ORDER BY m.nama ASC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencarian Data — Galactic Academy</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
</head>
<body>

<div class="app-wrapper">

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">

        <header class="topbar">
            <div class="topbar-breadcrumb">
                <a href="dashboard.php" style="color:var(--text-muted);text-decoration:none;">
                    Dashboard
                </a>
                &nbsp;/&nbsp;
                <span>Pencarian Data</span>
            </div>

            <div class="status-online">
                <span class="pulse-dot"></span>
                LIVE
            </div>
        </header>

        <main class="page-content">

            <div class="page-header">
                <div>
                    <div class="page-title">🔭 Pencarian Data Mahasiswa</div>
                    <div class="page-subtitle">
                        Cari mahasiswa berdasarkan NIM, Nama, Email, atau Jurusan
                    </div>
                </div>
            </div>

            <div class="card">
                <form method="GET">

                    <div class="search-wrapper">

                        <div class="search-box" style="flex:1;">
                            <span class="search-icon">
                                <i class="ti ti-search"></i>
                            </span>

                            <input
                                type="text"
                                name="keyword"
                                class="form-control"
                                placeholder="Masukkan kata kunci pencarian..."
                                value="<?= htmlspecialchars($keyword) ?>"
                            >
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-search"></i>
                            Cari
                        </button>

                        <?php if (!empty($keyword)): ?>
                            <a href="cari.php" class="btn btn-cyan">
                                <i class="ti ti-refresh"></i>
                                Reset
                            </a>
                        <?php endif; ?>

                    </div>

                </form>
            </div>

            <div class="card">

                <div class="card-header">
                    <div class="card-title">
                        📋 Hasil Pencarian
                    </div>

                    <span style="font-size:.85rem;color:var(--text-muted);">
                        <?= $result->num_rows ?> data ditemukan
                    </span>
                </div>

                <div class="table-wrapper">

                    <?php if ($result->num_rows > 0): ?>

                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIM</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Jurusan</th>
                                <th>Angkatan</th>
                                <th>Status</th>
                                <th>IPK</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php
                        $no = 1;
                        while ($row = $result->fetch_assoc()):
                        ?>

                            <tr>

                                <td><?= $no++ ?></td>

                                <td>
                                    <?= htmlspecialchars($row['nim']) ?>
                                </td>

                                <td style="font-weight:600;">
                                    <?= htmlspecialchars($row['nama']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['email']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['jurusan_nama'] ?? '-') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['angkatan']) ?>
                                </td>

                                <td>
                                    <span class="badge badge-<?= $row['status'] ?>">
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= number_format($row['ipk'], 2) ?>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                        </tbody>

                    </table>

                    <?php else: ?>

                        <div class="empty-state">
                            <div class="empty-state-icon">🔭</div>
                            <div class="empty-state-text">
                                Tidak ada data yang ditemukan.
                            </div>
                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </main>

        <footer class="page-footer">
            © 2024 GALACTIC ACADEMY · SEARCH MODULE
        </footer>

    </div>

</div>

</body>
</html>