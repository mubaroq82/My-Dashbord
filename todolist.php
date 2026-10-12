<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$nama_user = $_SESSION['nama'] ?? 'User';

// Tanggal hari ini (format zona waktu lokal)
date_default_timezone_set('Asia/Jakarta');
$tanggal_hari_ini = date('Y-m-d');

// OTOMATIS HAPUS TUGAS JIKA SUDAH GANTI HARI (Tanggal tugas < hari ini)
mysqli_query($conn, "DELETE FROM todolist WHERE user_id = '$user_id' AND tanggal < '$tanggal_hari_ini'");

// 1. TAMBAH TO-DO BARU (Disertai tanggal hari ini)
if (isset($_POST['tambah_todo'])) {
    $tugas = mysqli_real_escape_string($conn, $_POST['tugas']);
    if (!empty($tugas)) {
        mysqli_query($conn, "INSERT INTO todolist (user_id, tugas, status, tanggal) VALUES ('$user_id', '$tugas', 'Belum', '$tanggal_hari_ini')");
    }
    header("Location: todolist.php");
    exit;
}

// 2. TOGGLE STATUS (Selesai / Belum)
if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];
    $status_sekarang = $_GET['status'];
    $status_baru = ($status_sekarang == 'Belum') ? 'Selesai' : 'Belum';

    mysqli_query($conn, "UPDATE todolist SET status = '$status_baru' WHERE id = '$id' AND user_id = '$user_id'");
    header("Location: todolist.php");
    exit;
}

// 3. HAPUS TO-DO
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM todolist WHERE id = '$id' AND user_id = '$user_id'");
    header("Location: todolist.php");
    exit;
}

// AMBIL HANYA TO-DO UNTUK HARI INI
$query_todo = mysqli_query($conn, "SELECT * FROM todolist WHERE user_id = '$user_id' AND tanggal = '$tanggal_hari_ini' ORDER BY id DESC");

// Array terjemahan hari ke bahasa Indonesia
$hari_array = [
    'Sun' => 'Minggu', 'Mon' => 'Senin', 'Tue' => 'Selasa', 
    'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu'
];
$bulan_array = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', 
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', 
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];

$nama_hari = $hari_array[date('D')];
$tanggal_indo = date('d') . ' ' . $bulan_array[date('m')] . ' ' . date('Y');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List - MyDashboard</title>
    <!-- CSS UTAMA & CSS KHUSUS TODOLIST -->
    <link rel="stylesheet" href="dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="todolist.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="tampilanhp.css?v=<?= time(); ?>">
    <link rel="icon" href="baru.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Tambahan style khusus info hari & tanggal */
        .date-info-box {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            font-size: 15px;
            font-weight: 600;
            color: var(--text-primary);
        }
        .date-badge {
            background-color: var(--accent-color);
            color: white;
            padding: 6px 14px;
            border-radius: 10px;
            font-size: 14px;
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
                <li><a href="todolist.php" class="active">📝 To-Do List</a></li>
                <li><a href="jadwalkuliah.php">📅 Jadwal Kuliah</a></li>
                <li><a href="tugaskl.php">📚 Tugas Kuliah</a></li>
                <li><a href="pencatatankeuangan.php">💰 Catatan Keuangan</a></li>
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
                        <h1>Daftar Kegiatan Harian 📝</h1>
                        <p style="color: var(--text-muted); font-size: 14px;">Catat dan selesaikan kegiatan harianmu di sini.</p>
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

            <div class="todo-wrapper">
                
                <!-- TAMPILAN HARI & TANGGAL SESUAI SKetsa -->
                <div class="date-info-box">
                    <div class="date-badge">
                        <i class="far fa-calendar-alt"></i> <?= $nama_hari; ?>
                    </div>
                    <span style="color: var(--text-muted);"><?= $tanggal_indo; ?></span>
                </div>

                <!-- FORM INPUT TO-DO -->
                <form action="" method="POST" class="todo-form">
                    <input type="text" name="tugas" placeholder="Ketik rencana tugas hari ini..." required autocomplete="off">
                    <button type="submit" name="tambah_todo" class="btn-add">
                        <i class="fas fa-plus"></i> Tambah
                    </button>
                </form>

                <!-- DAFTAR TO-DO ITEM -->
                <div class="todo-list">
                    <?php if (mysqli_num_rows($query_todo) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($query_todo)): ?>
                            <div class="todo-item <?= $row['status'] == 'Selesai' ? 'completed' : ''; ?>">
                                <div class="todo-text">
                                    <a href="todolist.php?toggle=<?= $row['id']; ?>&status=<?= $row['status']; ?>" class="checkbox-btn">
                                        <i class="far <?= $row['status'] == 'Selesai' ? 'fa-check-square' : 'fa-square'; ?>"></i>
                                    </a>
                                    <span><?= htmlspecialchars($row['tugas']); ?></span>
                                </div>
                                <div class="todo-actions">
                                    <a href="todolist.php?hapus=<?= $row['id']; ?>" class="btn-delete" onclick="return confirm('Yakin ingin menghapus tugas ini?');">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="todo-empty">
                            <p>🎉 Belum ada tugas untuk hari ini. Yuk tambah rencana barumu!</p>
                        </div>
                    <?php endif; ?>
                </div>
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