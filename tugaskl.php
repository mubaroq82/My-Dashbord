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

// 1. PROSES TAMBAH TUGAS BARU
if (isset($_POST['tambah_tugas'])) {
    $mata_kuliah = mysqli_real_escape_string($conn, $_POST['mata_kuliah']);
    $deskripsi   = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $deadline    = mysqli_real_escape_string($conn, $_POST['deadline']);
    $status      = 'Belum Selesai';

    if (!empty($mata_kuliah) && !empty($deadline)) {
        $query_insert = "INSERT INTO tugas_kuliah (user_id, mata_kuliah, deskripsi, deadline, status) 
                         VALUES ('$user_id', '$mata_kuliah', '$deskripsi', '$deadline', '$status')";
        if (mysqli_query($conn, $query_insert)) {
            $pesan_sukses = "Tugas kuliah berhasil ditambahkan!";
        } else {
            $pesan_error = "Gagal menyimpan: " . mysqli_error($conn);
        }
    } else {
        $pesan_error = "Mata kuliah dan deadline wajib diisi!";
    }
}

// 2. PROSES UBAH STATUS TUGAS (SELESAI / BELUM) VIA CHECKBOX
if (isset($_GET['ubah_status'])) {
    $id_tugas = mysqli_real_escape_string($conn, $_GET['ubah_status']);
    $q_cek = mysqli_query($conn, "SELECT status FROM tugas_kuliah WHERE id = '$id_tugas' AND user_id = '$user_id'");
    if ($row = mysqli_fetch_assoc($q_cek)) {
        $status_baru = ($row['status'] == 'Selesai') ? 'Belum Selesai' : 'Selesai';
        mysqli_query($conn, "UPDATE tugas_kuliah SET status = '$status_baru' WHERE id = '$id_tugas' AND user_id = '$user_id'");
    }
    header("Location: tugaskl.php#daftar-tugas");
    exit;
}

// 3. PROSES HAPUS TUGAS
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($conn, $_GET['hapus']);
    mysqli_query($conn, "DELETE FROM tugas_kuliah WHERE id = '$id_hapus' AND user_id = '$user_id'");
    header("Location: tugaskl.php#daftar-tugas");
    exit;
}

// 4. AMBIL STATISTIK & DAFTAR TUGAS
$total_tugas = 0;
$tugas_selesai = 0;
$tugas_belum = 0;
$list_tugas = false;

try {
    $q_stats = mysqli_query($conn, "SELECT status, COUNT(*) as total FROM tugas_kuliah WHERE user_id = '$user_id' GROUP BY status");
    while ($row = mysqli_fetch_assoc($q_stats)) {
        if ($row['status'] == 'Selesai') {
            $tugas_selesai = $row['total'];
        } else if ($row['status'] == 'Belum Selesai') {
            $tugas_belum = $row['total'];
        }
    }
    $total_tugas = $tugas_selesai + $tugas_belum;
    $list_tugas = mysqli_query($conn, "SELECT * FROM tugas_kuliah WHERE user_id = '$user_id' ORDER BY deadline ASC, id DESC");
} catch (Exception $e) {
    // Tangani jika tabel belum ada
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tugas Kuliah - MyDashboard</title>
    <link rel="stylesheet" href="dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="tugaskl.css?v=<?= time(); ?>">
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
        textarea.form-control {
            width: 100%;
            padding: 12px 16px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            background: var(--bg-main);
            color: var(--text-primary);
            font-family: inherit;
            resize: vertical;
            min-height: 90px;
        }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="brand">📌 MyDashboard</div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php">🏠 Home</a></li>
                <li><a href="profil.php">👤 Profil Saya</a></li>
                <li><a href="todolist.php">📝 To-Do List</a></li>
                <li><a href="jadwalkuliah.php">📅 Jadwal Kuliah</a></li>
                <li><a href="tugaskl.php" class="active">📚 Tugas Kuliah</a></li>
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
                        <h1>Tugas Kuliah 📚</h1>
                        <p style="color: var(--text-muted); font-size: 14px;">Kelola catatan tugas kuliahmu dan Kerjakan Tepat Waktu.</p>
                    </div>
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

            <!-- STATISTIK -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Total Tugas</h3>
                        <p style="font-size: 22px; color: var(--accent-color);"><?= $total_tugas; ?></p>
                    </div>
                    <div class="stat-icon"><i class="fas fa-tasks"></i></div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Belum Selesai</h3>
                        <p style="font-size: 22px; color: #ee5d50;"><?= $tugas_belum; ?></p>
                    </div>
                    <div class="stat-icon" style="color: #ee5d50; background: rgba(238, 93, 80, 0.1);"><i class="fas fa-clock"></i></div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Selesai</h3>
                        <p style="font-size: 22px; color: #047857;"><?= $tugas_selesai; ?></p>
                    </div>
                    <div class="stat-icon" style="color: #047857; background: rgba(4, 120, 87, 0.1);"><i class="fas fa-check-circle"></i></div>
                </div>
            </div>

            <!-- FORM TAMBAH TUGAS (Action diarahkan ke #daftar-tugas) -->
            <div class="form-card">
                <h3>➕ Tambah Tugas Kuliah Baru</h3>
                <form action="tugaskl.php#daftar-tugas" method="POST" autocomplete="off" style="margin-top: 15px;">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Mata Kuliah</label>
                            <input type="text" name="mata_kuliah" placeholder="Contoh: Metode Numerik" required>
                        </div>

                        <div class="form-group">
                            <label>Deadline Tugas</label>
                            <input type="date" name="deadline" value="<?= date('Y-m-d'); ?>" required>
                        </div>

                        <div class="form-group full-width">
                            <label>Deskripsi / Catatan Tugas</label>
                            <textarea name="deskripsi" class="form-control" placeholder="Tuliskan detail atau instruksi tugas di sini..."></textarea>
                        </div>
                    </div>

                    <div style="margin-top: 18px;">
                        <button type="submit" name="tambah_tugas" class="btn-save">
                            <i class="fas fa-plus"></i> Simpan Tugas
                        </button>
                    </div>
                </form>
            </div>

            <!-- DAFTAR TUGAS KULIAH -->
            <div class="form-card" id="daftar-tugas">
                <h3>📋 Daftar Tugas Kuliah</h3>
                
                <div class="task-list-container">
                    <?php if ($list_tugas && mysqli_num_rows($list_tugas) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($list_tugas)): ?>
                            <div class="task-card <?= $row['status'] == 'Selesai' ? 'completed' : ''; ?>">
                                
                                <div class="task-header">
                                    <div class="task-info-group">
                                        <span class="task-badge-matkul">
                                            Mata Kuliah : <?= htmlspecialchars($row['mata_kuliah']); ?>
                                        </span>
                                        <span class="task-badge-deadline">
                                            <i class="fas fa-calendar-alt"></i> Deadline : <?= date('d M Y', strtotime($row['deadline'])); ?>
                                        </span>
                                    </div>

                                    <a href="tugaskl.php?ubah_status=<?= $row['id']; ?>#daftar-tugas" style="text-decoration: none;">
                                        <label class="task-status-toggle" style="cursor: pointer;">
                                            <span>Selesai</span>
                                            <input type="checkbox" <?= $row['status'] == 'Selesai' ? 'checked' : ''; ?> onclick="window.location.href='tugaskl.php?ubah_status=<?= $row['id']; ?>#daftar-tugas'">
                                        </label>
                                    </a>
                                </div>

                                <div class="task-desc">
                                    <?= nl2br(htmlspecialchars($row['deskripsi'] ?? 'Tidak ada catatan tugas.')); ?>
                                </div>

                                <div class="task-footer">
                                    <a href="tugaskl.php?hapus=<?= $row['id']; ?>#daftar-tugas" class="btn-delete-task" onclick="return confirm('Yakin ingin menghapus catatan tugas ini?');">
                                        <i class="fas fa-trash-can"></i> Hapus Catatan
                                    </a>
                                </div>

                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: var(--text-muted); padding: 30px;">
                            Belum ada tugas kuliah tersimpan.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="theme.js?v=<?= time(); ?>"></script>

    <!-- SCRIPT HAMBURGER & MENJAGA POSISI SCROLL -->
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

        document.querySelectorAll('.task-status-toggle input, .btn-delete-task, .btn-save').forEach(element => {
            element.addEventListener('click', function() {
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