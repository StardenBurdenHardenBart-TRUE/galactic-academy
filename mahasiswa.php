<?php
require_once 'includes/config.php';
requireLogin();

$conn = getConnection();

// Handle POST (tambah / edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action'] ?? '';
    $nim      = sanitize($_POST['nim'] ?? '');
    $nama     = sanitize($_POST['nama'] ?? '');
    $email    = sanitize($_POST['email'] ?? '');
    $jurusan  = (int)($_POST['jurusan_id'] ?? 0);
    $angkatan = (int)($_POST['angkatan'] ?? date('Y'));
    $status   = sanitize($_POST['status'] ?? 'aktif');
    $ipk      = (float)($_POST['ipk'] ?? 0);

    if ($action === 'tambah') {
        $stmt = $conn->prepare("INSERT INTO mahasiswa (nim,nama,email,jurusan_id,angkatan,status,ipk) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("sssiisd", $nim, $nama, $email, $jurusan, $angkatan, $status, $ipk);
        if ($stmt->execute()) {
            setFlash('success', '✅ Mahasiswa ' . $nama . ' berhasil ditambahkan ke sistem.');
        } else {
            setFlash('danger', '❌ Gagal menambahkan. NIM mungkin sudah ada: ' . $conn->error);
        }
        $stmt->close();

    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $conn->prepare("UPDATE mahasiswa SET nim=?,nama=?,email=?,jurusan_id=?,angkatan=?,status=?,ipk=? WHERE id=?");
        $stmt->bind_param("sssiisd i", $nim, $nama, $email, $jurusan, $angkatan, $status, $ipk, $id);
        if ($stmt->execute()) {
            setFlash('success', '✅ Data mahasiswa berhasil diperbarui.');
        } else {
            setFlash('danger', '❌ Gagal memperbarui data.');
        }
        $stmt->close();
    }
    header('Location: mahasiswa.php');
    exit();
}

// Handle DELETE
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $stmt = $conn->prepare("DELETE FROM mahasiswa WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        setFlash('success', '🗑️ Data mahasiswa berhasil dihapus dari sistem.');
    } else {
        setFlash('danger', '❌ Gagal menghapus data.');
    }
    $stmt->close();
    header('Location: mahasiswa.php');
    exit();
}

// Data untuk edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $r = $conn->prepare("SELECT * FROM mahasiswa WHERE id=?");
    $r->bind_param("i", $id);
    $r->execute();
    $edit_data = $r->get_result()->fetch_assoc();
    $r->close();
}

// Filter & Search
$search   = sanitize($_GET['q'] ?? '');
$fil_jur  = (int)($_GET['jurusan'] ?? 0);
$fil_stat = sanitize($_GET['status'] ?? '');
$fil_ang  = (int)($_GET['angkatan'] ?? 0);

$where = [];
$params = [];
$types = '';
if ($search) {
    $like = "%$search%";
    $where[] = "(m.nim LIKE ? OR m.nama LIKE ? OR m.email LIKE ?)";
    $params = array_merge($params, [$like, $like, $like]);
    $types .= 'sss';
}
if ($fil_jur) { $where[] = "m.jurusan_id = ?"; $params[] = $fil_jur; $types .= 'i'; }
if ($fil_stat) { $where[] = "m.status = ?"; $params[] = $fil_stat; $types .= 's'; }
if ($fil_ang) { $where[] = "m.angkatan = ?"; $params[] = $fil_ang; $types .= 'i'; }

$sql = "SELECT m.*, j.nama AS jurusan_nama FROM mahasiswa m LEFT JOIN jurusan j ON m.jurusan_id = j.id";
if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY m.created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$data = $stmt->get_result();

// Jurusan list
$jurusan_list = $conn->query("SELECT * FROM jurusan ORDER BY nama");
$angkatan_list= $conn->query("SELECT DISTINCT angkatan FROM mahasiswa ORDER BY angkatan DESC");

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mahasiswa — Galactic Academy</title>
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
                <span>Data Mahasiswa</span>
            </div>
            <div class="status-online"><span class="pulse-dot"></span> LIVE</div>
        </header>

        <main class="page-content">
            <?php $flash = getFlash(); if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>"><?= $flash['message'] ?></div>
            <?php endif; ?>

            <div class="page-header">
                <div>
                    <div class="page-title">👨‍🚀 Data Mahasiswa</div>
                    <div class="page-subtitle">Kelola seluruh data mahasiswa aktif dalam sistem</div>
                </div>
                <button class="btn btn-primary" onclick="toggleForm()">
                    <i class="ti ti-plus"></i> Tambah Mahasiswa
                </button>
            </div>

            <!-- Form Tambah / Edit -->
            <div class="card" id="formCard" style="<?= $edit_data ? '' : 'display:none;' ?>">
                <div class="card-header">
                    <div class="card-title">
                        <?= $edit_data ? '✏️ Edit Data Mahasiswa' : '➕ Tambah Mahasiswa Baru' ?>
                    </div>
                    <?php if (!$edit_data): ?>
                        <button onclick="toggleForm()" style="background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:1.3rem;">
                            <i class="ti ti-x"></i>
                        </button>
                    <?php endif; ?>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="<?= $edit_data ? 'edit' : 'tambah' ?>">
                    <?php if ($edit_data): ?>
                        <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
                    <?php endif; ?>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">NIM</label>
                            <input type="text" name="nim" class="form-control" placeholder="Contoh: 2024001" required
                                   value="<?= htmlspecialchars($edit_data['nim'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" placeholder="Nama mahasiswa..." required
                                   value="<?= htmlspecialchars($edit_data['nama'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="email@galaxy.ac.id" required
                                   value="<?= htmlspecialchars($edit_data['email'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Program Studi</label>
                            <select name="jurusan_id" class="form-control" required>
                                <option value="">-- Pilih Jurusan --</option>
                                <?php $jurusan_list->data_seek(0); while($j = $jurusan_list->fetch_assoc()): ?>
                                    <option value="<?= $j['id'] ?>" <?= ($edit_data['jurusan_id'] ?? '') == $j['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($j['nama']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Angkatan</label>
                            <input type="number" name="angkatan" class="form-control" placeholder="2024" min="2015" max="2030"
                                   value="<?= $edit_data['angkatan'] ?? date('Y') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <?php foreach(['aktif','cuti','lulus','dropout'] as $s): ?>
                                    <option value="<?= $s ?>" <?= ($edit_data['status'] ?? 'aktif') === $s ? 'selected' : '' ?>>
                                        <?= ucfirst($s) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">IPK (0.00 – 4.00)</label>
                            <input type="number" name="ipk" class="form-control" placeholder="3.50" min="0" max="4" step="0.01"
                                   value="<?= $edit_data['ipk'] ?? '' ?>">
                        </div>
                    </div>
                    <div style="margin-top:1.25rem;display:flex;gap:.75rem;flex-wrap:wrap;">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy"></i> <?= $edit_data ? 'Simpan Perubahan' : 'Tambahkan' ?>
                        </button>
                        <?php if ($edit_data): ?>
                            <a href="mahasiswa.php" class="btn btn-cyan">
                                <i class="ti ti-x"></i> Batal
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Search & Filter -->
            <div class="card">
                <form method="GET" action="mahasiswa.php">
                    <div class="search-wrapper">
                        <div class="search-box" style="flex:2;min-width:220px;">
                            <span class="search-icon"><i class="ti ti-search"></i></span>
                            <input type="text" name="q" class="form-control" placeholder="Cari NIM, nama, atau email..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <select name="jurusan" class="form-control" style="min-width:160px;">
                            <option value="">Semua Jurusan</option>
                            <?php $jurusan_list->data_seek(0); while($j = $jurusan_list->fetch_assoc()): ?>
                                <option value="<?= $j['id'] ?>" <?= $fil_jur == $j['id'] ? 'selected' : '' ?>><?= htmlspecialchars($j['nama']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <select name="status" class="form-control" style="min-width:130px;">
                            <option value="">Semua Status</option>
                            <?php foreach(['aktif','cuti','lulus','dropout'] as $s): ?>
                                <option value="<?= $s ?>" <?= $fil_stat === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="angkatan" class="form-control" style="min-width:120px;">
                            <option value="">Semua Angkatan</option>
                            <?php while($a = $angkatan_list->fetch_assoc()): ?>
                                <option value="<?= $a['angkatan'] ?>" <?= $fil_ang == $a['angkatan'] ? 'selected' : '' ?>><?= $a['angkatan'] ?></option>
                            <?php endwhile; ?>
                        </select>
                        <button type="submit" class="btn btn-cyan"><i class="ti ti-search"></i> Filter</button>
                        <?php if ($search || $fil_jur || $fil_stat || $fil_ang): ?>
                            <a href="mahasiswa.php" class="btn" style="border:1px solid var(--border-faint);color:var(--text-muted);">
                                <i class="ti ti-x"></i> Reset
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Tabel Data -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">📋 Daftar Mahasiswa</div>
                    <span style="font-size:.8rem;color:var(--text-muted);"><?= $data->num_rows ?> data ditemukan</span>
                </div>
                <div class="table-wrapper">
                    <?php if ($data->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>NIM</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Jurusan</th>
                                <th>Angkatan</th>
                                <th>IPK</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $no = 1; while($row = $data->fetch_assoc()): ?>
                            <tr>
                                <td style="color:var(--text-muted);font-size:.8rem;"><?= $no++ ?></td>
                                <td class="td-nim"><?= $row['nim'] ?></td>
                                <td style="font-weight:600;"><?= htmlspecialchars($row['nama']) ?></td>
                                <td style="color:var(--text-secondary);font-size:.83rem;"><?= htmlspecialchars($row['email']) ?></td>
                                <td style="font-size:.83rem;"><?= htmlspecialchars($row['jurusan_nama'] ?? '-') ?></td>
                                <td style="font-family:var(--font-mono);font-size:.82rem;"><?= $row['angkatan'] ?></td>
                                <td>
                                    <div class="ipk-bar">
                                        <div class="ipk-track" style="width:60px;">
                                            <div class="ipk-fill" style="width:<?= ($row['ipk']/4)*100 ?>%"></div>
                                        </div>
                                        <span class="ipk-text"><?= $row['ipk'] ?></span>
                                    </div>
                                </td>
                                <td><span class="badge badge-<?= $row['status'] ?>"><?= $row['status'] ?></span></td>
                                <td>
                                    <div style="display:flex;gap:5px;flex-wrap:nowrap;">
                                        <a href="mahasiswa.php?edit=<?= $row['id'] ?>" class="btn btn-warning btn-sm" title="Edit">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        <a href="mahasiswa.php?hapus=<?= $row['id'] ?>" class="btn btn-danger btn-sm" title="Hapus"
                                           onclick="return confirm('Hapus mahasiswa <?= addslashes($row['nama']) ?>?\nTindakan ini tidak dapat dibatalkan.')">
                                            <i class="ti ti-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">🔭</div>
                            <div class="empty-state-text">Tidak ada data yang cocok dengan pencarian Anda.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
        <footer class="page-footer">© 2024 GALACTIC ACADEMY · DATA MAHASISWA MODULE</footer>
    </div>
</div>

<script>
function toggleForm() {
    const f = document.getElementById('formCard');
    f.style.display = f.style.display === 'none' ? 'block' : 'none';
    if (f.style.display === 'block') f.scrollIntoView({behavior:'smooth'});
}
</script>
</body>
</html>