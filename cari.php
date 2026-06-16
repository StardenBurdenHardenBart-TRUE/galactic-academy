<?php
require_once 'includes/config.php';
requireLogin();

$conn = getConnection();

// Tambah
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action    = $_POST['action'] ?? '';
    $kode      = strtoupper(sanitize($_POST['kode'] ?? ''));
    $nama      = sanitize($_POST['nama'] ?? '');
    $fakultas  = sanitize($_POST['fakultas'] ?? '');
    $kapasitas = (int)($_POST['kapasitas'] ?? 100);

    if ($action === 'tambah') {
        $stmt = $conn->prepare("INSERT INTO jurusan (kode,nama,fakultas,kapasitas) VALUES (?,?,?,?)");
        $stmt->bind_param("sssi", $kode, $nama, $fakultas, $kapasitas);
        $stmt->execute() ? setFlash('success','✅ Jurusan berhasil ditambahkan.') : setFlash('danger','❌ Gagal: ' . $conn->error);
        $stmt->close();

    } elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE jurusan SET kode=?,nama=?,fakultas=?,kapasitas=? WHERE id=?");
        $stmt->bind_param("sssii", $kode, $nama, $fakultas, $kapasitas, $id);
        $stmt->execute() ? setFlash('success','✅ Jurusan berhasil diperbarui.') : setFlash('danger','❌ Gagal memperbarui.');
        $stmt->close();
    }
    header('Location: jurusan.php'); exit();
}

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    // Cek apakah ada mahasiswa
    $cek = $conn->query("SELECT COUNT(*) as c FROM mahasiswa WHERE jurusan_id=$id")->fetch_assoc()['c'];
    if ($cek > 0) {
        setFlash('danger', '❌ Tidak dapat menghapus. Masih ada ' . $cek . ' mahasiswa di jurusan ini.');
    } else {
        $stmt = $conn->prepare("DELETE FROM jurusan WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute() ? setFlash('success','🗑️ Jurusan berhasil dihapus.') : setFlash('danger','❌ Gagal menghapus.');
        $stmt->close();
    }
    header('Location: jurusan.php'); exit();
}

$edit_data = null;
if (isset($_GET['edit'])) {
    $r = $conn->prepare("SELECT * FROM jurusan WHERE id=?");
    $r->bind_param("i", (int)$_GET['edit']);
    $r->execute();
    $edit_data = $r->get_result()->fetch_assoc();
}

$data = $conn->query("
    SELECT j.*, COUNT(m.id) as jumlah_mhs
    FROM jurusan j
    LEFT JOIN mahasiswa m ON m.jurusan_id = j.id
    GROUP BY j.id
    ORDER BY j.nama
");
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Jurusan — Galactic Academy</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
</head>
<body>
<div class="app-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <header class="topbar">
            <div class="topbar-breadcrumb">
                <a href="dashboard.php" style="color:var(--text-muted);text-decoration:none;">Dashboard</a> &nbsp;/&nbsp;
                <span>Data Jurusan</span>
            </div>
            <div class="status-online"><span class="pulse-dot"></span> LIVE</div>
        </header>

        <main class="page-content">
            <?php $flash = getFlash(); if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>"><?= $flash['message'] ?></div>
            <?php endif; ?>

            <div class="page-header">
                <div>
                    <div class="page-title">🪐 Data Jurusan / Program Studi</div>
                    <div class="page-subtitle">Kelola program studi yang tersedia di Galactic Academy</div>
                </div>
                <button class="btn btn-primary" onclick="toggleForm()">
                    <i class="ti ti-plus"></i> Tambah Jurusan
                </button>
            </div>

            <!-- Form -->
            <div class="card" id="formCard" style="<?= $edit_data ? '' : 'display:none;' ?>">
                <div class="card-header">
                    <div class="card-title"><?= $edit_data ? '✏️ Edit Jurusan' : '➕ Tambah Jurusan Baru' ?></div>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="<?= $edit_data ? 'edit' : 'tambah' ?>">
                    <?php if ($edit_data): ?>
                        <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
                    <?php endif; ?>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Kode Jurusan</label>
                            <input type="text" name="kode" class="form-control" placeholder="Contoh: TI" maxlength="10" required
                                   value="<?= htmlspecialchars($edit_data['kode'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nama Program Studi</label>
                            <input type="text" name="nama" class="form-control" placeholder="Teknik Informatika..." required
                                   value="<?= htmlspecialchars($edit_data['nama'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fakultas</label>
                            <input type="text" name="fakultas" class="form-control" placeholder="Fakultas Teknik..." required
                                   value="<?= htmlspecialchars($edit_data['fakultas'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kapasitas Mahasiswa</label>
                            <input type="number" name="kapasitas" class="form-control" placeholder="100" min="10" max="500"
                                   value="<?= $edit_data['kapasitas'] ?? 100 ?>" required>
                        </div>
                    </div>
                    <div style="margin-top:1.25rem;display:flex;gap:.75rem;">
                        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy"></i> Simpan</button>
                        <?php if ($edit_data): ?>
                            <a href="jurusan.php" class="btn btn-cyan"><i class="ti ti-x"></i> Batal</a>
                        <?php else: ?>
                            <button type="button" onclick="toggleForm()" class="btn" style="border:1px solid var(--border-faint);color:var(--text-muted);">Tutup</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Tabel -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">🎓 Daftar Program Studi</div>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode</th>
                                <th>Nama Prodi</th>
                                <th>Fakultas</th>
                                <th>Kapasitas</th>
                                <th>Mahasiswa</th>
                                <th>Occupancy</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $no = 1; while($row = $data->fetch_assoc()): 
                            $occ = $row['kapasitas'] > 0 ? ($row['jumlah_mhs'] / $row['kapasitas']) * 100 : 0;
                        ?>
                            <tr>
                                <td style="color:var(--text-muted);font-size:.8rem;"><?= $no++ ?></td>
                                <td>
                                    <span style="font-family:var(--font-mono);font-size:.85rem;color:var(--nebula-purple);
                                                 background:rgba(123,47,255,0.1);padding:2px 8px;border-radius:4px;">
                                        <?= $row['kode'] ?>
                                    </span>
                                </td>
                                <td style="font-weight:600;"><?= htmlspecialchars($row['nama']) ?></td>
                                <td style="color:var(--text-secondary);font-size:.85rem;"><?= htmlspecialchars($row['fakultas']) ?></td>
                                <td style="text-align:center;"><?= $row['kapasitas'] ?></td>
                                <td style="text-align:center;font-family:var(--font-mono);"><?= $row['jumlah_mhs'] ?></td>
                                <td style="min-width:120px;">
                                    <div class="ipk-bar">
                                        <div class="ipk-track" style="flex:1;">
                                            <div class="ipk-fill" style="width:<?= min($occ,100) ?>%;background:<?= $occ >= 90 ? 'var(--danger-red)' : ($occ >= 70 ? 'var(--comet-yellow)' : '') ?>;"></div>
                                        </div>
                                        <span style="font-size:.75rem;color:var(--text-muted);min-width:32px;"><?= round($occ) ?>%</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="display:flex;gap:5px;">
                                        <a href="jurusan.php?edit=<?= $row['id'] ?>" class="btn btn-warning btn-sm"><i class="ti ti-edit"></i></a>
                                        <a href="jurusan.php?hapus=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                                           onclick="return confirm('Hapus jurusan <?= addslashes($row['nama']) ?>?')">
                                            <i class="ti ti-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
        <footer class="page-footer">© 2024 GALACTIC ACADEMY · JURUSAN MODULE</footer>
    </div>
</div>
<script>
function toggleForm() {
    const f = document.getElementById('formCard');
    f.style.display = f.style.display === 'none' ? 'block' : 'none';
}
</script>
</body>
</html>