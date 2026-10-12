<?php
session_start();
require 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$nama_user = $_SESSION['nama'] ?? 'User';

// Zona waktu & Tanggal Hari Ini
date_default_timezone_set('Asia/Jakarta');
$tanggal_hari_ini = date('Y-m-d');

// Otomatis hapus tugas kemarin yang belum selesai (menyamakan dengan todolist.php)
mysqli_query($conn, "DELETE FROM todolist WHERE user_id = '$user_id' AND tanggal < '$tanggal_hari_ini'");

// Toggle Status To-Do dari Dashboard
if (isset($_GET['toggle_todo'])) {
    $id_todo = (int)$_GET['toggle_todo'];
    $status_skrg = $_GET['status_skrg'];
    $status_baru = ($status_skrg == 'Belum') ? 'Selesai' : 'Belum';

    mysqli_query($conn, "UPDATE todolist SET status = '$status_baru' WHERE id = '$id_todo' AND user_id = '$user_id'");
    header("Location: dashboard.php#todo-section");
    exit;
}

// Menerjemahkan hari bahasa inggris ke indonesia
$hari_inggris = date('l');
$map_hari = [
    'Monday'    => 'Senin',
    'Tuesday'   => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday'  => 'Kamis',
    'Friday'    => 'Jumat',
    'Saturday'  => 'Sabtu',
    'Sunday'    => 'Minggu'
];
$hari_ini = $map_hari[$hari_inggris] ?? 'Senin';

$bulan_array = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', 
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', 
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];
$tanggal_indo = date('d') . ' ' . $bulan_array[date('m')] . ' ' . date('Y');

// 1. Ambil Jadwal Kuliah Hari Ini
$query_jadwal_hari_ini = mysqli_query($conn, "SELECT * FROM schedules WHERE user_id = '$user_id' AND hari = '$hari_ini' ORDER BY jam_mulai ASC");

// 2. Ambil Tugas Kuliah yang Belum Selesai
$query_tugas_belum = mysqli_query($conn, "SELECT * FROM tugas_kuliah WHERE user_id = '$user_id' AND status = 'Belum Selesai' ORDER BY deadline ASC");

// 3. Ambil To-Do List Hari Ini untuk Dashboard
$query_todo_home = mysqli_query($conn, "SELECT * FROM todolist WHERE user_id = '$user_id' AND tanggal = '$tanggal_hari_ini' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Utama - MyDashboard</title>
    <link rel="stylesheet" href="dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="todolist.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="tampilanhp.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="baru.png">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 25px;
            margin-bottom: 30px;
        }
        .home-section-title {
            color: #2b388f;
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 14px;
        }
        body.dark-mode .home-section-title {
            color: #818cf8;
        }
        .home-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
        }
        .home-card-inner {
            background: var(--bg-main);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 16px;
            margin-top: 10px;
        }
        .item-list-home {
            padding: 10px 0;
            border-bottom: 1px dashed var(--border-color);
            font-size: 0.95rem;
            color: var(--text-primary);
        }
        .item-list-home:last-child {
            border-bottom: none;
        }
        /* Style info hari di Dashboard */
        .date-info-box {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
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
            <div class="brand">
                📌 MyDashboard
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active">🏠 Home</a></li>
                <li><a href="profil.php">👤 Profil Saya</a></li>
                <li><a href="todolist.php">📝 To-Do List</a></li>
                <li><a href="jadwalkuliah.php">📅 Jadwal Kuliah</a></li>
                <li><a href="tugaskl.php">📚 Tugas Kuliah</a></li>
                <li><a href="pencatatankeuangan.php">💰 Catatan Keuangan</a></li>
                <li class="logout-item"><a href="logout.php" class="logout-btn">🚪 Logout</a></li>
            </ul>
        </aside>

        <!-- KONTEN UTAMA -->
        <main class="main-content">
            <!-- HEADER DENGAN SWITCH TEMA & PROFILE BADGE -->
            <header class="top-header">
                <div class="welcome-text" style="display: flex; align-items: center; gap: 12px;">
                    <!-- TOMBOL HAMBURGER KHUSUS HP -->
                    <button id="sidebarToggle" class="hamburger-btn" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div>
                        <h1>Selamat Datang, <?= htmlspecialchars($nama_user); ?>! 🎉</h1>
                        <p>Ini adalah halaman utama dashboard milikmu.</p>
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

            <!-- KARTU STATISTIK (DENGAN LAYOUT GRID) -->
            <div class="stats-grid">
                <!-- KARTU 1: JAM LIVE -->
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Jam Sekarang</h3>
                        <p id="liveTimeCard">00:00:00</p>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>

                <!-- KARTU 2: HARI -->
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Hari</h3>
                        <p id="liveDayCard">-</p>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>

                <!-- KARTU 3: TANGGAL -->
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Tanggal</h3>
                        <p id="liveDateCard" style="font-size: 20px;">-</p>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
            </div>

            <!-- AKTIVITAS UTAMA -->
            <div class="activity-card" style="margin-bottom: 24px;">
                <h2 style="font-size: 18px; color: var(--text-primary); margin-bottom: 8px;">Ringkasan Aktivitas</h2>
                <p style="color: var(--text-muted); font-size: 14px; margin: 0;">Silakan pilih menu di samping kiri untuk mengelola Aktivitas akademismu.</p>
            </div>

            <!-- KOTAK 1: JADWAL KULIAH HARI INI -->
            <div class="home-card">
                <h3 class="home-section-title">Ayo Kuliah Jangan Sampe Terlambat Apa Lagi Bolos !</h3>
                <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 10px;">Jadwal kuliah hari ini (<strong><?= $hari_ini; ?></strong>):</p>
                
                <div class="home-card-inner">
                    <?php if (mysqli_num_rows($query_jadwal_hari_ini) > 0): ?>
                        <?php while ($j = mysqli_fetch_assoc($query_jadwal_hari_ini)): ?>
                            <div class="item-list-home">
                                <i class="fas fa-book-open" style="color: var(--accent-color); margin-right: 8px;"></i>
                                <strong><?= htmlspecialchars($j['nama_matkul']); ?></strong> 
                                <?php if (!empty($j['jam_mulai']) && $j['jam_mulai'] != '00:00:00'): ?>
                                    <span style="color: var(--text-muted);">| ⏰ <?= date('H:i', strtotime($j['jam_mulai'])); ?> - <?= date('H:i', strtotime($j['jam_selesai'])); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($j['ruangan']) && $j['ruangan'] != '-'): ?>
                                    <span style="color: var(--text-muted);">| 📍 Ruang: <?= htmlspecialchars($j['ruangan']); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="color: var(--text-muted); text-align: center; margin: 10px 0; font-style: italic;">Tidak ada jadwal kuliah untuk hari ini. Santai tapi tetap produktif!</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- KOTAK 2: REMEMBER / TUGAS BELUM SELESAI -->
            <div class="home-card">
                <h3 class="home-section-title" style="color: #ee5d50;">Remember ! 📌</h3>
                <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 10px;">Ada beberapa tugas yang belum kamu kerjakan:</p>
                
                <div class="home-card-inner">
                    <?php if (mysqli_num_rows($query_tugas_belum) > 0): ?>
                        <?php while ($t = mysqli_fetch_assoc($query_tugas_belum)): ?>
                            <div class="item-list-home">
                                <i class="fas fa-exclamation-circle" style="color: #ee5d50; margin-right: 8px;"></i>
                                <strong><?= htmlspecialchars($t['mata_kuliah']); ?></strong> 
                                <span style="color: var(--text-muted);">(Deadline: <?= date('d M Y', strtotime($t['deadline'])); ?>)</span>
                                <div style="margin-top: 4px; font-size: 0.9rem; color: var(--text-muted); padding-left: 20px;">
                                    <?= htmlspecialchars($t['deskripsi']); ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 15px; color: #047857; font-weight: 700; font-size: 1.1rem;">
                            ✨ TIDAK ADA TUGAS ✨
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- KOTAK 3: DAFTAR KEGIATAN HARIAN (TO-DO LIST AT BOTTOM) -->
            <div class="home-card" id="todo-section">
                <h3 class="home-section-title">Daftar Kegiatan Harian 📝</h3>
                <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 16px;">Selesaikan tugas harianmu yang kamu catat di sini.</p>
                
                <!-- Badge Hari & Tanggal -->
                <div class="date-info-box">
                    <div class="date-badge">
                        <i class="far fa-calendar-alt"></i> <?= $hari_ini; ?>
                    </div>
                    <span style="color: var(--text-muted);"><?= $tanggal_indo; ?></span>
                </div>

                <div class="todo-list">
                    <?php if (mysqli_num_rows($query_todo_home) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($query_todo_home)): ?>
                            <div class="todo-item <?= $row['status'] == 'Selesai' ? 'completed' : ''; ?>" style="margin-bottom: 10px;">
                                <div class="todo-text">
                                    <a href="dashboard.php?toggle_todo=<?= $row['id']; ?>&status_skrg=<?= $row['status']; ?>#todo-section" class="checkbox-btn">
                                        <i class="far <?= $row['status'] == 'Selesai' ? 'fa-check-square' : 'fa-square'; ?>"></i>
                                    </a>
                                    <span><?= htmlspecialchars($row['tugas']); ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="todo-empty" style="padding: 15px; text-align: center; color: var(--text-muted);">
                            <p>🎉 Belum ada kegiatan hari ini. Tambahkan lewat menu To-Do List!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>

    <!-- SCRIPT UTAMA -->
    <script src="auth.js?v=<?= time(); ?>"></script>
    <script src="theme.js?v=<?= time(); ?>"></script>

    <!-- SCRIPT HAMBURGER MENU & SCROLL -->
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('sidebar-active');
        }

        // Menutup sidebar otomatis jika layar HP diklik di luar area sidebar
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const toggleBtn = document.getElementById('sidebarToggle');
            
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
                    sidebar.classList.remove('sidebar-active');
                }
            }
        });

        // Script menjaga posisi scroll
        document.querySelectorAll('.checkbox-btn').forEach(btn => {
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