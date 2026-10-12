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

// 1. PROSES TAMBAH TRANSAKSI KEUANGAN
if (isset($_POST['tambah_transaksi'])) {
    $jenis       = mysqli_real_escape_string($conn, $_POST['jenis']); // Pemasukan / Pengeluaran
    $nominal     = intval(str_replace(['.', ','], '', $_POST['nominal'])); 
    $kategori    = mysqli_real_escape_string($conn, $_POST['kategori']);
    $keterangan  = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $tanggal     = !empty($_POST['tanggal']) ? mysqli_real_escape_string($conn, $_POST['tanggal']) : date('Y-m-d');

    if ($nominal > 0 && !empty($kategori)) {
        $query_insert = "INSERT INTO keuangan (user_id, jenis, nominal, kategori, keterangan, tanggal) 
                         VALUES ('$user_id', '$jenis', '$nominal', '$kategori', '$keterangan', '$tanggal')";
        if (mysqli_query($conn, $query_insert)) {
            $pesan_sukses = "Catatan keuangan berhasil ditambahkan!";
        } else {
            $pesan_error = "Gagal menyimpan: " . mysqli_error($conn);
        }
    } else {
        $pesan_error = "Nominal harus lebih besar dari 0 dan kategori wajib diisi!";
    }
}

// 2. PROSES HAPUS TRANSAKSI (Diperbarui dengan anchor #tabel-keuangan)
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($conn, $_GET['hapus']);
    mysqli_query($conn, "DELETE FROM keuangan WHERE id = '$id_hapus' AND user_id = '$user_id'");
    header("Location: pencatatankeuangan.php#tabel-keuangan");
    exit;
}

// 3. AMBIL RINGKASAN SALDO & STATISTIK
$total_pemasukan = 0;
$total_pengeluaran = 0;
$list_transaksi = false;

try {
    $q_sum = mysqli_query($conn, "SELECT jenis, SUM(nominal) as total FROM keuangan WHERE user_id = '$user_id' GROUP BY jenis");
    while ($row = mysqli_fetch_assoc($q_sum)) {
        if ($row['jenis'] == 'Pemasukan') {
            $total_pemasukan = $row['total'];
        } else if ($row['jenis'] == 'Pengeluaran') {
            $total_pengeluaran = $row['total'];
        }
    }
    $sisa_saldo = $total_pemasukan - $total_pengeluaran;

    // AMBIL SEMUA DATA RIWAYAT TRANSAKSI
    $list_transaksi = mysqli_query($conn, "SELECT * FROM keuangan WHERE user_id = '$user_id' ORDER BY tanggal DESC, id DESC");
} catch (Exception $e) {
    // Tabel mungkin belum dibuat
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catatan Keuangan - MyDashboard</title>
    <link rel="stylesheet" href="dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="tampilanhp.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="baru.png">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        .form-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 28px;
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
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            font-size: 14px;
        }
        .table-custom th {
            background-color: var(--bg-main);
            color: var(--text-primary);
        }
        .badge-pemasukan {
            color: #047857;
            background: rgba(4, 120, 87, 0.1);
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 600;
        }
        .badge-pengeluaran {
            color: #ee5d50;
            background: rgba(238, 93, 80, 0.1);
            padding: 4px 10px;
            border-radius: 6px;
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
                <li><a href="jadwalkuliah.php">📅 Jadwal Kuliah</a></li>
                <li><a href="tugaskl.php">📚 Tugas Kuliah</a></li>
                <li><a href="pencatatankeuangan.php" class="active">💰 Catatan Keuangan</a></li>
                <li class="logout-item"><a href="logout.php" class="logout-btn">🚪 Logout</a></li>
            </ul>
        </aside>

        <!-- KONTEN UTAMA -->
        <main class="main-content">
            <header class="top-header">
                <div class="welcome-text" style="display: flex; align-items: center; gap: 12px;">
                    <!-- TOMBOL HAMBURGER KHUSUS HP -->
                    <button id="sidebarToggle" class="hamburger-btn" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div>
                        <h1>Catatan Keuangan 💰</h1>
                        <p style="color: var(--text-muted); font-size: 14px;">Kelola pemasukan dan pengeluaran keuangan harianmu.</p>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 20px;">
                    <!-- TOGGLE SWITCH PILL MODE GELAP/TERANG -->
                    <div class="theme-switch-wrapper">
                        <label class="theme-switch" for="checkboxTheme">
                            <input type="checkbox" id="checkboxTheme" onchange="toggleTheme()" />
                            <div class="slider round">
                                <span>☀️</span>
                                <span>🌙</span>
                            </div>
                        </label>
                    </div>

                    <!-- USER PROFILE BADGE -->
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

            <!-- KARTU STATISTIK KEUANGAN -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Total Pemasukan</h3>
                        <p style="font-size: 22px; color: #047857;">Rp <?= number_format($total_pemasukan, 0, ',', '.'); ?></p>
                    </div>
                    <div class="stat-icon" style="color: #047857; background: rgba(4, 120, 87, 0.1);">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Total Pengeluaran</h3>
                        <p style="font-size: 22px; color: #ee5d50;">Rp <?= number_format($total_pengeluaran, 0, ',', '.'); ?></p>
                    </div>
                    <div class="stat-icon" style="color: #ee5d50; background: rgba(238, 93, 80, 0.1);">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Sisa Saldo</h3>
                        <p style="font-size: 22px; color: var(--accent-color);">Rp <?= number_format($sisa_saldo ?? 0, 0, ',', '.'); ?></p>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
            </div>

            <!-- FORM TAMBAH TRANSAKSI -->
            <div class="form-card">
                <h3>➕ Tambah Catatan Transaksi Baru</h3>
                <form action="" method="POST" style="margin-top: 15px;">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Jenis Transaksi</label>
                            <select name="jenis" required>
                                <option value="Pemasukan">Pemasukan</option>
                                <option value="Pengeluaran" selected>Pengeluaran</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Nominal (Rp)</label>
                            <input type="text" name="nominal" placeholder="Contoh:5000" autocomplete="off" required>
                        </div>

                        <div class="form-group">
                            <label>Kategori</label>
                            <input type="text" name="kategori" placeholder="Contoh: Makan/Kebutuhan/Tranportasi/Kiriman" autocomplete="off" required>
                        </div>

                        <div class="form-group">
                            <label>Tanggal</label>
                            <input type="date" name="tanggal" value="<?= date('Y-m-d'); ?>" required>
                        </div>

                        <div class="form-group full-width">
                            <label>Keterangan (Opsional)</label>
                            <input type="text" name="keterangan" placeholder="Contoh: Kiriman bulanan orang tua" autocomplete="off">
                        </div>
                    </div>

                    <div style="margin-top: 18px;">
                        <button type="submit" name="tambah_transaksi" class="btn-save">
                            <i class="fas fa-plus"></i> Simpan Transaksi
                        </button>
                    </div>
                </form>
            </div>

            <!-- TABEL RIWAYAT TRANSAKSI (Diberi id="tabel-keuangan") -->
            <div class="form-card" id="tabel-keuangan">
                <h3>📋 Riwayat Transaksi Keuangan</h3>
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Kategori</th>
                            <th>Keterangan</th>
                            <th>Nominal</th>
                            <th>Hapus Riwayat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($list_transaksi && mysqli_num_rows($list_transaksi) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($list_transaksi)): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                                    <td>
                                        <span class="<?= $row['jenis'] == 'Pemasukan' ? 'badge-pemasukan' : 'badge-pengeluaran'; ?>">
                                            <?= $row['jenis']; ?>
                                        </span>
                                    </td>
                                    <td><strong><?= htmlspecialchars($row['kategori']); ?></strong></td>
                                    <td><?= htmlspecialchars($row['keterangan'] ?: '-'); ?></td>
                                    <td style="font-weight: 600; color: <?= $row['jenis'] == 'Pemasukan' ? '#047857' : '#ee5d50'; ?>;">
                                        <?= $row['jenis'] == 'Pemasukan' ? '+' : '-'; ?> Rp <?= number_format($row['nominal'], 0, ',', '.'); ?>
                                    </td>
                                    <td>
                                        <a href="pencatatankeuangan.php?hapus=<?= $row['id']; ?>#tabel-keuangan" class="btn-action-delete" onclick="return confirm('Yakin ingin menghapus catatan ini?');" style="color: var(--danger-color); text-decoration: none; font-weight: 600;">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 25px;">
                                    Belum ada catatan keuangan tersimpan.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- SCRIPT TEMA & HAMBURGER -->
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

        // SCRIPT MENJAGA POSISI SCROLL
        document.querySelectorAll('.btn-action-delete').forEach(btn => {
            btn.addEventListener('click', function() {
                localStorage.setItem('scrollPosition', window.scrollY || document.documentElement.scrollTop);
            });
        });

        window.addEventListener('load', function() {
            const savedScrollPos = localStorage.getItem('scrollPosition');
            if (savedScrollPos) {
                window.scrollTo(0, parseInt(savedScrollPos));
                localStorage.removeItem('scrollPosition');
            }
        });
    </script>
</body>
</html>