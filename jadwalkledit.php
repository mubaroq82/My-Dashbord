<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$nama_user = $_SESSION['nama'] ?? 'User';
$pesan_sukses = '';
$pesan_error  = '';

// 1. PROSES HAPUS JADWAL & DOSEN
if (isset($_GET['hapus_jadwal'])) {
    $id_hapus = mysqli_real_escape_string($conn, $_GET['hapus_jadwal']);
    $query_hapus = "DELETE FROM schedules WHERE id = '$id_hapus' AND user_id = '$user_id'";
    if (mysqli_query($conn, $query_hapus)) {
        $pesan_sukses = "Jadwal berhasil dihapus!";
    } else {
        $pesan_error = "Gagal menghapus jadwal: " . mysqli_error($conn);
    }
}

if (isset($_GET['hapus_dosen'])) {
    $id_hapus_dosen = mysqli_real_escape_string($conn, $_GET['hapus_dosen']);
    $query_hapus_dosen = "DELETE FROM lecturers WHERE id = '$id_hapus_dosen' AND user_id = '$user_id'";
    if (mysqli_query($conn, $query_hapus_dosen)) {
        $pesan_sukses = "Data dosen berhasil dihapus!";
    } else {
        $pesan_error = "Gagal menghapus dosen: " . mysqli_error($conn);
    }
}

// 2. PROSES SIMPAN / UPDATE JADWAL
if (isset($_POST['simpan_jadwal'])) {
    $id_jadwal   = mysqli_real_escape_string($conn, $_POST['id_jadwal']);
    $hari        = mysqli_real_escape_string($conn, $_POST['hari']);
    $nama_matkul = mysqli_real_escape_string($conn, $_POST['mata_kuliah']);
    
    $jam_mulai   = !empty($_POST['jam_mulai']) ? mysqli_real_escape_string($conn, $_POST['jam_mulai']) : '00:00:00';
    $jam_selesai = !empty($_POST['jam_selesai']) ? mysqli_real_escape_string($conn, $_POST['jam_selesai']) : '00:00:00';
    $ruangan     = !empty($_POST['ruangan']) ? mysqli_real_escape_string($conn, $_POST['ruangan']) : '-';

    if (!empty($id_jadwal)) {
        $query_jadwal = "UPDATE schedules SET 
                            hari = '$hari', 
                            nama_matkul = '$nama_matkul', 
                            jam_mulai = '$jam_mulai', 
                            jam_selesai = '$jam_selesai', 
                            ruangan = '$ruangan' 
                         WHERE id = '$id_jadwal' AND user_id = '$user_id'";
        $pesan_teks = "Jadwal berhasil diperbarui!";
    } else {
        $query_jadwal = "INSERT INTO schedules (user_id, hari, nama_matkul, jam_mulai, jam_selesai, ruangan) 
                         VALUES ('$user_id', '$hari', '$nama_matkul', '$jam_mulai', '$jam_selesai', '$ruangan')";
        $pesan_teks = "Jadwal kuliah berhasil ditambahkan!";
    }

    if (mysqli_query($conn, $query_jadwal)) {
        $pesan_sukses = $pesan_teks;
    } else {
        $pesan_error = "Gagal menyimpan jadwal: " . mysqli_error($conn);
    }
}

// 3. PROSES SIMPAN / UPDATE DOSEN
if (isset($_POST['simpan_dosen'])) {
    $id_dosen   = mysqli_real_escape_string($conn, $_POST['id_dosen']);
    $nama_dosen = mysqli_real_escape_string($conn, $_POST['nama_dosen']);
    $matkul     = mysqli_real_escape_string($conn, $_POST['matkul']);
    $no_telp    = mysqli_real_escape_string($conn, $_POST['no_telp']);

    if (!empty($id_dosen)) {
        $query_dosen = "UPDATE lecturers SET 
                            nama_dosen = '$nama_dosen', 
                            matkul = '$matkul', 
                            no_telp = '$no_telp' 
                         WHERE id = '$id_dosen' AND user_id = '$user_id'";
        $pesan_teks = "Data dosen berhasil diperbarui!";
    } else {
        $query_dosen = "INSERT INTO lecturers (user_id, nama_dosen, matkul, no_telp) 
                        VALUES ('$user_id', '$nama_dosen', '$matkul', '$no_telp')";
        $pesan_teks = "Data dosen berhasil ditambahkan!";
    }

    if (mysqli_query($conn, $query_dosen)) {
        $pesan_sukses = $pesan_teks;
    } else {
        $pesan_error = "Gagal menyimpan data dosen: " . mysqli_error($conn);
    }
}

// 4. AMBIL DATA UNTUK MODE EDIT (JADWAL & DOSEN)
$edit_data = null;
if (isset($_GET['edit_jadwal'])) {
    $id_edit = mysqli_real_escape_string($conn, $_GET['edit_jadwal']);
    $q_edit  = mysqli_query($conn, "SELECT * FROM schedules WHERE id = '$id_edit' AND user_id = '$user_id'");
    if (mysqli_num_rows($q_edit) > 0) {
        $edit_data = mysqli_fetch_assoc($q_edit);
    }
}

$edit_dosen = null;
if (isset($_GET['edit_dosen'])) {
    $id_edit_dosen = mysqli_real_escape_string($conn, $_GET['edit_dosen']);
    $q_edit_dosen  = mysqli_query($conn, "SELECT * FROM lecturers WHERE id = '$id_edit_dosen' AND user_id = '$user_id'");
    if (mysqli_num_rows($q_edit_dosen) > 0) {
        $edit_dosen = mysqli_fetch_assoc($q_edit_dosen);
    }
}

// 5. AMBIL SEMUA DATA USER (JADWAL & DOSEN)
$list_jadwal = mysqli_query($conn, "SELECT * FROM schedules WHERE user_id = '$user_id' ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), jam_mulai ASC");
$list_dosen  = mysqli_query($conn, "SELECT * FROM lecturers WHERE user_id = '$user_id' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jadwal Kuliah & Dosen - MyDashboard</title>
    <link rel="stylesheet" href="dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="tampilanhp.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="baru.png">
    <style>
        .form-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 28px !important;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px 24px;
        }
        .full-width {
            grid-column: span 2;
        }
        .btn-submit-custom {
            background-color: #99e9f2;
            color: #1b2559;
            font-weight: 700;
            border: 1px solid #15aabf;
            padding: 12px 24px;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: 0.2s;
        }
        .btn-submit-custom:hover {
            background-color: #66d9e8;
        }
        .alert-success {
            background: #e6fffa;
            color: #047857;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-danger {
            background: #ffe5e5;
            color: #ee5d50;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .table-custom {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .table-custom th, .table-custom td {
            padding: 12px 14px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            font-size: 14px;
        }
        .table-custom th {
            background-color: var(--bg-main);
            color: var(--text-primary);
        }
        .btn-action-edit {
            color: #2b6cb0;
            margin-right: 12px;
            text-decoration: none;
            font-weight: 600;
        }
        .btn-action-delete {
            color: #e53e3e;
            text-decoration: none;
            font-weight: 600;
        }
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .full-width {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <!-- SIDEBAR NAVIGASI -->
        <aside class="sidebar">
            <div class="brand">📌 MyDashboard</div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php">🏠 Home</a></li>
                <li><a href="profil.php">👤 Profil Saya</a></li>
                <li><a href="todolist.php">📝 To-Do List</a></li>
                <li><a href="jadwalkuliah.php" class="active">📅 Jadwal Kuliah</a></li>
                <li><a href="tugaskl.php">📚 Tugas Kuliah</a></li>
                <li><a href="pencatatankeuangan.php">💰 Catatan Keuangan</a></li>
                <li class="logout-item"><a href="logout.php" class="logout-btn">🚪 Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <!-- HEADER UTAMA DENGAN HAMBURGER & TOGGLE MODE -->
            <header class="top-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <div class="welcome-text" style="display: flex; align-items: center; gap: 12px;">
                    <!-- TOMBOL HAMBURGER -->
                    <button id="sidebarToggle" class="hamburger-btn" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1>Pengelolaan Jadwal & Dosen ✏️</h1>
                </div>

                <div style="display: flex; align-items: center; gap: 20px;">
                    <div class="theme-switch-wrapper">
                        <label class="theme-switch" for="checkboxTheme">
                            <input type="checkbox" id="checkboxTheme" onchange="toggleTheme()" />
                            <div class="slider round">
                                <span>☀️</span>
                                <span>🌙</span>
                            </div>
                        </label>
                    </div>

                    <a href="jadwalkuliah.php" class="btn-submit-custom">SELESAI & KEMBALI</a>

                    <div class="user-profile-badge">
                        <div class="user-avatar"><?= strtoupper(substr($nama_user, 0, 1)); ?></div>
                        <span><?= htmlspecialchars($nama_user); ?></span>
                    </div>
                </div>
            </header>

            <?php if ($pesan_sukses): ?>
                <div class="alert-success"><?= $pesan_sukses; ?></div>
            <?php endif; ?>

            <?php if ($pesan_error): ?>
                <div class="alert-danger"><?= $pesan_error; ?></div>
            <?php endif; ?>

            <!-- FORM TAMBAH / EDIT JADWAL KULIAH -->
            <div class="form-card">
                <h3><?= $edit_data ? '✏️ Edit Jadwal Kuliah' : '➕ Tambah Jadwal Kuliah'; ?></h3>
                <form action="jadwalkledit.php" method="POST" style="margin-top: 15px;">
                    <input type="hidden" name="id_jadwal" value="<?= $edit_data['id'] ?? ''; ?>">

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Hari</label>
                            <select name="hari" required>
                                <?php 
                                $hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                                foreach ($hari_list as $h):
                                    $selected = ($edit_data && $edit_data['hari'] == $h) ? 'selected' : '';
                                ?>
                                    <option value="<?= $h; ?>" <?= $selected; ?>><?= $h; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Mata Kuliah / Keterangan</label>
                            <input type="text" name="mata_kuliah" value="<?= htmlspecialchars($edit_data['nama_matkul'] ?? ''); ?>" placeholder="Contoh: Pemrograman Web" required autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label>Jam Mulai (Opsional)</label>
                            <input type="time" name="jam_mulai" value="<?= ($edit_data && $edit_data['jam_mulai'] != '00:00:00') ? $edit_data['jam_mulai'] : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Jam Selesai (Opsional)</label>
                            <input type="time" name="jam_selesai" value="<?= ($edit_data && $edit_data['jam_selesai'] != '00:00:00') ? $edit_data['jam_selesai'] : ''; ?>">
                        </div>
                        <div class="form-group full-width">
                            <label>Ruangan (Opsional)</label>
                            <input type="text" name="ruangan" value="<?= htmlspecialchars(($edit_data && $edit_data['ruangan'] != '-') ? $edit_data['ruangan'] : ''); ?>" placeholder="Contoh: Lab Komputer 2" autocomplete="off">
                        </div>
                    </div>
                    
                    <div style="margin-top: 15px;">
                        <button type="submit" name="simpan_jadwal" class="btn-save">
                            <?= $edit_data ? '💾 Simpan Perubahan Jadwal' : '+ Tambah Jadwal'; ?>
                        </button>
                        <?php if ($edit_data): ?>
                            <a href="jadwalkledit.php" style="margin-left: 10px; color: var(--text-muted); text-decoration: none; font-size: 14px;">Batal Edit</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- TABEL DAFTAR JADWAL TERPASANG -->
            <div class="form-card">
                <h3>📋 Daftar Jadwal Kuliah Saat Ini</h3>
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Hari</th>
                            <th>Mata Kuliah</th>
                            <th>Jam</th>
                            <th>Ruangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($list_jadwal) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($list_jadwal)): ?>
                                <tr>
                                    <td><strong><?= $row['hari']; ?></strong></td>
                                    <td><?= htmlspecialchars($row['nama_matkul']); ?></td>
                                    <td>
                                        <?= ($row['jam_mulai'] != '00:00:00') ? date('H:i', strtotime($row['jam_mulai'])) . ' - ' . date('H:i', strtotime($row['jam_selesai'])) : '-'; ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['ruangan']); ?></td>
                                    <td>
                                        <a href="jadwalkledit.php?edit_jadwal=<?= $row['id']; ?>" class="btn-action-edit"><i class="fas fa-edit"></i> Edit</a>
                                        <a href="jadwalkledit.php?hapus_jadwal=<?= $row['id']; ?>" class="btn-action-delete" onclick="return confirm('Yakin ingin menghapus jadwal ini?');"><i class="fas fa-trash"></i> Hapus</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--text-muted);">Belum ada jadwal tersimpan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- FORM TAMBAH / EDIT DOSEN -->
            <div class="form-card">
                <h3><?= $edit_dosen ? '✏️ Edit Data Dosen' : '👨‍🏫 Tambah Data Dosen'; ?></h3>
                <form action="jadwalkledit.php" method="POST" style="margin-top: 15px;">
                    <input type="hidden" name="id_dosen" value="<?= $edit_dosen['id'] ?? ''; ?>">

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Nama Dosen</label>
                            <input type="text" name="nama_dosen" value="<?= htmlspecialchars($edit_dosen['nama_dosen'] ?? ''); ?>" placeholder="Contoh: Dr. Budi Santoso, M.T." required autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label>Mata Kuliah</label>
                            <input type="text" name="matkul" value="<?= htmlspecialchars($edit_dosen['matkul'] ?? ''); ?>" placeholder="Contoh: Basis Data" required autocomplete="off">
                        </div>
                        <div class="form-group full-width">
                            <label>No. Telp / WhatsApp Dosen</label>
                            <input type="text" name="no_telp" value="<?= htmlspecialchars($edit_dosen['no_telp'] ?? ''); ?>" placeholder="Contoh: 081234567890" required autocomplete="off">
                        </div>
                    </div>

                    <div style="margin-top: 15px;">
                        <button type="submit" name="simpan_dosen" class="btn-save">
                            <?= $edit_dosen ? '💾 Simpan Perubahan Dosen' : '+ Tambah Dosen'; ?>
                        </button>
                        <?php if ($edit_dosen): ?>
                            <a href="jadwalkledit.php" style="margin-left: 10px; color: var(--text-muted); text-decoration: none; font-size: 14px;">Batal Edit</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- TABEL DAFTAR DOSEN -->
            <div class="form-card">
                <h3>👥 Daftar Dosen Saat Ini</h3>
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Nama Dosen</th>
                            <th>Mata Kuliah</th>
                            <th>No. Telp / WA</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($list_dosen) > 0): ?>
                            <?php while ($d = mysqli_fetch_assoc($list_dosen)): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($d['nama_dosen']); ?></strong></td>
                                    <td><?= htmlspecialchars($d['matkul']); ?></td>
                                    <td><?= htmlspecialchars($d['no_telp']); ?></td>
                                    <td>
                                        <a href="jadwalkledit.php?edit_dosen=<?= $d['id']; ?>" class="btn-action-edit"><i class="fas fa-edit"></i> Edit</a>
                                        <a href="jadwalkledit.php?hapus_dosen=<?= $d['id']; ?>" class="btn-action-delete" onclick="return confirm('Yakin ingin menghapus data dosen ini?');"><i class="fas fa-trash"></i> Hapus</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--text-muted);">Belum ada data dosen tersimpan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>

    <!-- SCRIPT UTAMA & HAMBURGER -->
    <script src="theme.js?v=<?= time(); ?>"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('sidebar-active');
        }

        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const toggleBtn = document.getElementById('sidebarToggle');
            
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
                    sidebar.classList.remove('sidebar-active');
                }
            }
        });
    </script>
</body>
</html>