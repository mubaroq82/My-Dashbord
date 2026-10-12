<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$nama_user = $_SESSION['nama'] ?? 'User';

// Menerjemahkan hari bahasa inggris ke indonesia untuk highlight
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

$daftar_hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

// Ambil data jadwal dari database
$query_jadwal = mysqli_query($conn, "SELECT * FROM schedules WHERE user_id = '$user_id' ORDER BY jam_mulai ASC");
$jadwal_per_hari = [];
while ($row = mysqli_fetch_assoc($query_jadwal)) {
    $jadwal_per_hari[$row['hari']][] = $row;
}

// Ambil data dosen dari database
$query_dosen = mysqli_query($conn, "SELECT * FROM lecturers WHERE user_id = '$user_id' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Kuliah - MyDashboard</title>
    <link rel="stylesheet" href="dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="tampilanhp.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="baru.png">
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        .btn-edit-page {
            padding: 10px 20px;
            background-color: var(--accent-color);
            color: #fff;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s;
        }
        .btn-edit-page:hover {
            background-color: var(--accent-hover);
        }

        /* GRID JADWAL HARI */
        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 40px;
        }
        .day-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 20px;
            border: 2px solid var(--border-color);
            transition: all 0.3s ease;
            min-height: 250px;
        }
        /* STYLING KHUSUS HARI INI (HIGHLIGHT) */
        .day-card.today {
            background-color: #99e9f2 !important;
            border-color: #15aabf !important;
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(21, 170, 191, 0.2);
        }
        body.dark-mode .day-card.today {
            background-color: #15aabf !important;
            color: #ffffff;
        }
        .day-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 15px;
            text-transform: uppercase;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 8px;
        }
        .item-jadwal {
            background: var(--bg-main);
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
            font-size: 13px;
        }

        /* TABEL LIST DOSEN */
        .dosen-section {
            background: var(--card-bg);
            padding: 24px;
            border-radius: 16px;
            box-shadow: var(--shadow);
            margin-top: 30px;
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
        }
        .table-custom th {
            background-color: var(--bg-main);
            color: var(--text-primary);
            font-weight: 600;
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

        <!-- KONTEN UTAMA -->
        <main class="main-content">
            <header class="top-header">
                <div class="welcome-text" style="display: flex; align-items: center; gap: 12px;">
                    <!-- TOMBOL HAMBURGER -->
                    <button id="sidebarToggle" class="hamburger-btn" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1>JADWAL KULIAH 📅</h1>
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

                    <a href="jadwalkledit.php" class="btn-edit-page">
                        <i class="fas fa-edit"></i> EDIT
                    </a>

                    <!-- USER PROFILE BADGE -->
                    <div class="user-profile-badge">
                        <div class="user-avatar"><?= strtoupper(substr($nama_user, 0, 1)); ?></div>
                        <span><?= htmlspecialchars($nama_user); ?></span>
                    </div>
                </div>
            </header>

            <!-- KARTU JADWAL PER HARI -->
            <div class="schedule-grid">
                <?php foreach ($daftar_hari as $hari): ?>
                    <?php $is_today = ($hari == $hari_ini); ?>
                    <div class="day-card <?= $is_today ? 'today' : ''; ?>">
                        <div class="day-title">
                            <?= $hari; ?> <?= $is_today ? '📌' : ''; ?>
                        </div>
                        <?php if (isset($jadwal_per_hari[$hari])): ?>
                            <?php foreach ($jadwal_per_hari[$hari] as $j): ?>
                                <div class="item-jadwal">
                                    <strong><?= htmlspecialchars($j['nama_matkul']); ?></strong><br>
                                    <?php if (!empty($j['jam_mulai']) && $j['jam_mulai'] != '00:00:00'): ?>
                                        <small>⏰ <?= date('H:i', strtotime($j['jam_mulai'])); ?> - <?= date('H:i', strtotime($j['jam_selesai'])); ?></small><br>
                                    <?php endif; ?>
                                    <?php if (!empty($j['ruangan']) && $j['ruangan'] != '-'): ?>
                                        <small>📍 Ruang: <?= htmlspecialchars($j['ruangan']); ?></small>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="font-size: 12px; color: var(--text-muted); font-style: italic;">Tidak ada kuliah</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <hr style="border: none; border-top: 1px solid var(--border-color); margin: 40px 0;">

            <!-- SEKSI TABEL LIST DOSEN -->
            <div class="dosen-section">
                <h2>List Dosen 👨‍🏫</h2>
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NAMA DOSEN</th>
                            <th>MATKUL</th>
                            <th>No Telp/WA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($query_dosen) > 0): ?>
                            <?php $no = 1; while ($d = mysqli_fetch_assoc($query_dosen)): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><strong><?= htmlspecialchars($d['nama_dosen']); ?></strong></td>
                                    <td><?= htmlspecialchars($d['matkul']); ?></td>
                                    <td><?= htmlspecialchars($d['no_telp']); ?></td>
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