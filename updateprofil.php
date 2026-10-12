<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$nama_user = $_SESSION['nama'] ?? 'User';
$pesan_error = '';

// PROSES SIMPAN / UPDATE DATA PROFIL
if (isset($_POST['simpan_profil'])) {
    $nama       = mysqli_real_escape_string($conn, $_POST['nama']);
    $nim        = mysqli_real_escape_string($conn, $_POST['nim']);
    $jurusan    = mysqli_real_escape_string($conn, $_POST['jurusan']);
    $semester   = mysqli_real_escape_string($conn, $_POST['semester']);
    $target_ipk = mysqli_real_escape_string($conn, $_POST['target_ipk']);
    $status_mhs = mysqli_real_escape_string($conn, $_POST['status_mhs']);
    $no_hp      = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $instagram  = mysqli_real_escape_string($conn, $_POST['instagram']);
    $github     = mysqli_real_escape_string($conn, $_POST['github']);
    $bio        = mysqli_real_escape_string($conn, $_POST['bio']);

    $new_filename = null;

    if (isset($_FILES['foto']['name']) && $_FILES['foto']['name'] != '') {
        $filename    = $_FILES['foto']['name'];
        $tmp_name    = $_FILES['foto']['tmp_name'];
        $file_ext    = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed_ext = array('jpg', 'jpeg', 'png');

        if (in_array($file_ext, $allowed_ext)) {
            if (!file_exists('uploads')) {
                mkdir('uploads', 0777, true);
            }
            $new_filename = 'user_' . $user_id . '_' . time() . '.' . $file_ext;
            $upload_path  = 'uploads/' . $new_filename;

            if (!move_uploaded_file($tmp_name, $upload_path)) {
                $pesan_error = "Gagal mengunggah foto ke folder uploads!";
            }
        } else {
            $pesan_error = "Format foto harus JPG, JPEG, atau PNG!";
        }
    }

    if (empty($pesan_error)) {
        if ($new_filename) {
            $query_update = "UPDATE users SET 
                nama='$nama', nim='$nim', jurusan='$jurusan', semester='$semester', 
                target_ipk='$target_ipk', status_mhs='$status_mhs', no_hp='$no_hp', 
                instagram='$instagram', github='$github', bio='$bio', foto='$new_filename' 
                WHERE id='$user_id'";
        } else {
            $query_update = "UPDATE users SET 
                nama='$nama', nim='$nim', jurusan='$jurusan', semester='$semester', 
                target_ipk='$target_ipk', status_mhs='$status_mhs', no_hp='$no_hp', 
                instagram='$instagram', github='$github', bio='$bio' 
                WHERE id='$user_id'";
        }

        try {
            if (mysqli_query($conn, $query_update)) {
                $_SESSION['nama'] = $nama;
                echo "
                <!DOCTYPE html>
                <html lang='id'>
                <head>
                    <meta charset='UTF-8'>
                    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                </head>
                <body style='font-family: sans-serif; background-color: #f0f3fa;'>
                    <script>
                        Swal.fire({
                            title: 'Berhasil! 🚀',
                            text: 'Data profil berhasil diperbarui.',
                            icon: 'success',
                            confirmButtonColor: '#2b388f',
                            confirmButtonText: 'Kembali ke Profil'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location = 'profil.php';
                            }
                        });
                    </script>
                </body>
                </html>
                ";
                exit;
            } else {
                $pesan_error = "Gagal memperbarui database: " . mysqli_error($conn);
            }
        } catch (Exception $e) {
            $pesan_error = "Struktur tabel database belum lengkap!";
        }
    }
}

// AMBIL DATA USER TERKINI
$query_user = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($query_user);

$foto_profil = (!empty($user['foto']) && file_exists('uploads/' . $user['foto'])) 
                ? 'uploads/' . $user['foto'] 
                : 'https://via.placeholder.com/130?text=Foto';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil - MyDashboard</title>
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
                    <!-- TOMBOL HAMBURGER -->
                    <button id="sidebarToggle" class="hamburger-btn" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1>Edit Biodata Pribadi ✏️</h1>
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

            <?php if ($pesan_error): ?>
                <div style="background: #ffe5e5; color: #ee5d50; padding: 12px 16px; border-radius: 12px; margin-bottom: 20px; font-size: 14px;">
                    <?= $pesan_error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="profile-container">
                    
                    <!-- KARTU FOTO PROFIL (KIRI) -->
                    <div class="profile-card">
                        <div class="avatar-upload">
                            <img id="imagePreview" src="<?= $foto_profil; ?>" class="avatar-preview" alt="Foto Profil">
                            <div class="avatar-edit">
                                <input type='file' id="imageUpload" name="foto" accept=".png, .jpg, .jpeg" onchange="previewImage(this);" />
                                <label for="imageUpload"><i class="fas fa-pencil-alt"></i></label>
                            </div>
                        </div>
                        <h3><?= htmlspecialchars($user['nama']); ?></h3>
                        <p style="color: var(--text-muted); font-size: 14px; margin-top: 4px;"><?= htmlspecialchars($user['email']); ?></p>
                    </div>

                    <!-- KARTU FORM BIODATA (KANAN) -->
                    <div class="profile-form-card">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']); ?>" required autocomplete="off">
                            </div>

                            <div class="form-group">
                                <label>NIM</label>
                                <input type="text" name="nim" placeholder="Contoh: 220101001" value="<?= htmlspecialchars($user['nim'] ?? ''); ?>" autocomplete="off">
                            </div>

                            <div class="form-group">
                                <label>Jurusan / Program Studi</label>
                                <input type="text" name="jurusan" placeholder="Contoh: Teknik Informatika" value="<?= htmlspecialchars($user['jurusan'] ?? ''); ?>" autocomplete="off">
                            </div>

                            <div class="form-group">
                                <label>Semester Saat Ini</label>
                                <input type="number" name="semester" placeholder="Contoh: 4" value="<?= htmlspecialchars($user['semester'] ?? ''); ?>" autocomplete="off">
                            </div>

                            <div class="form-group">
                                <label>Target IPK</label>
                                <input type="text" name="target_ipk" placeholder="Contoh: 3.85" value="<?= htmlspecialchars($user['target_ipk'] ?? ''); ?>" autocomplete="off">
                            </div>

                            <div class="form-group">
                                <label>Status Mahasiswa</label>
                                <select name="status_mhs">
                                    <option value="Aktif" <?= ($user['status_mhs'] ?? '') == 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="Cuti" <?= ($user['status_mhs'] ?? '') == 'Cuti' ? 'selected' : ''; ?>>Cuti</option>
                                    <option value="Lulus" <?= ($user['status_mhs'] ?? '') == 'Lulus' ? 'selected' : ''; ?>>Lulus</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Nomor WhatsApp / HP</label>
                                <input type="text" name="no_hp" placeholder="Contoh: 08123456789" value="<?= htmlspecialchars($user['no_hp'] ?? ''); ?>" autocomplete="off">
                            </div>

                            <div class="form-group">
                                <label>Instagram Username</label>
                                <input type="text" name="instagram" placeholder="Contoh: @username" value="<?= htmlspecialchars($user['instagram'] ?? ''); ?>" autocomplete="off">
                            </div>

                            <div class="form-group full-width">
                                <label>Nama Universitas</label>
                                <input type="text" name="github" placeholder="Contoh: github.com/username" value="<?= htmlspecialchars($user['github'] ?? ''); ?>" autocomplete="off">
                            </div>

                            <div class="form-group full-width">
                                <label>Bio / Catatan Diri</label>
                                <textarea name="bio" rows="3" placeholder="Tulis deskripsi singkat tentang dirimu..."><?= htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div style="margin-top: 24px; text-align: right; display: flex; gap: 10px; justify-content: flex-end;">
                            <a href="profil.php" style="padding: 10px 20px; background: var(--border-color); color: var(--text-primary); text-decoration: none; border-radius: 8px; font-weight: 600;">Batal</a>
                            <button type="submit" name="simpan_profil" class="btn-save">Simpan Perubahan</button>
                        </div>
                    </div>

                </div>
            </form>
        </main>
    </div>

    <!-- SCRIPT UTAMA & HAMBURGER -->
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

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
    <script src="theme.js?v=<?= time(); ?>"></script>
</body>
</html>