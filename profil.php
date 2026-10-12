<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$nama_user = $_SESSION['nama'] ?? 'User';

// Ambil data user
$query_user = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($query_user);

// Cek foto profil
$foto_profil = (!empty($user['foto']) && file_exists('uploads/' . $user['foto'])) 
                ? 'uploads/' . $user['foto'] 
                : 'https://via.placeholder.com/130?text=Foto';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - MyDashboard</title>
    <link rel="stylesheet" href="dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="tampilanhp.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="baru.png">
</head>
<body>

    <div class="dashboard-container">
        <!-- SIDEBAR NAVIGASI -->
        <aside class="sidebar">
            <div class="brand">📌 MyDashboard</div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php">🏠 Home</a></li>
                <li><a href="profil.php" class="active">👤 Profil Saya</a></li>
                <li><a href="todolist.php">📝 To-Do List</a></li>
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
                        <h1>Biodata Pribadi 👤</h1>
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

            <div class="profile-container">
                <!-- KARTU FOTO PROFIL (KIRI) -->
                <div class="profile-card">
                    <div class="avatar-upload">
                        <img src="<?= $foto_profil; ?>" class="avatar-preview" alt="Foto Profil">
                    </div>
                    <h3><?= htmlspecialchars($user['nama']); ?></h3>
                    <p style="color: var(--text-muted); font-size: 14px; margin-top: 4px;"><?= htmlspecialchars($user['email']); ?></p>
                    
                    <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid var(--border-color); text-align: left; font-size: 13px; color: var(--text-muted);">
                        <p><strong>Status:</strong> <?= htmlspecialchars($user['status_mhs'] ?? 'Aktif'); ?></p>
                        <p style="margin-top: 6px;"><strong>Target IPK:</strong> <?= htmlspecialchars($user['target_ipk'] ?? '-'); ?></p>
                    </div>

                    <a href="updateprofil.php" class="btn-save" style="display: block; text-align: center; margin-top: 20px; text-decoration: none;">
                        <i class="fas fa-edit"></i> Edit Profil
                    </a>
                </div>

                <!-- KARTU INFORMATION DISPLAY (KANAN) -->
                <div class="profile-form-card">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" value="<?= htmlspecialchars($user['nama']); ?>" disabled>
                        </div>

                        <div class="form-group">
                            <label>NIM</label>
                            <input type="text" value="<?= htmlspecialchars($user['nim'] ?? '-'); ?>" disabled>
                        </div>

                        <div class="form-group">
                            <label>Jurusan / Program Studi</label>
                            <input type="text" value="<?= htmlspecialchars($user['jurusan'] ?? '-'); ?>" disabled>
                        </div>

                        <div class="form-group">
                            <label>Semester Saat Ini</label>
                            <input type="text" value="<?= htmlspecialchars($user['semester'] ?? '-'); ?>" disabled>
                        </div>

                        <div class="form-group">
                            <label>Target IPK</label>
                            <input type="text" value="<?= htmlspecialchars($user['target_ipk'] ?? '-'); ?>" disabled>
                        </div>

                        <div class="form-group">
                            <label>Status Mahasiswa</label>
                            <input type="text" value="<?= htmlspecialchars($user['status_mhs'] ?? 'Aktif'); ?>" disabled>
                        </div>

                        <div class="form-group">
                            <label>Nomor WhatsApp / HP</label>
                            <input type="text" value="<?= htmlspecialchars($user['no_hp'] ?? '-'); ?>" disabled>
                        </div>

                        <div class="form-group">
                            <label>Instagram Username</label>
                            <input type="text" value="<?= htmlspecialchars($user['instagram'] ?? '-'); ?>" disabled>
                        </div>

                        <div class="form-group full-width">
                            <label>Nama Universitas</label>
                            <input type="text" value="<?= htmlspecialchars($user['github'] ?? '-'); ?>" disabled>
                        </div>

                        <div class="form-group full-width">
                            <label>Bio / Catatan Diri</label>
                            <textarea rows="3" disabled><?= htmlspecialchars($user['bio'] ?? '-'); ?></textarea>
                        </div>
                    </div>
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