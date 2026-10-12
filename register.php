<?php
require 'koneksi.php';

$pesan = '';

if (isset($_POST['register'])) {
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    // Membersihkan input email dari karakter ilegal
    $email    = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // 1. Validasi format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan = "Format email tidak valid atau tidak nyata!";
    } 
    // 2. Validasi panjang password
    elseif (strlen($password) > 9) {
        $pesan = "Password maksimal 8 karakter!";
    } else {
        // Safe escape untuk query database
        $email_db = mysqli_real_escape_string($conn, $email);
        
        // 3. Cek apakah email sudah terdaftar di database
        $cek_email = mysqli_query($conn, "SELECT email FROM users WHERE email = '$email_db'");
        if (mysqli_num_rows($cek_email) > 0) {
            $pesan = "Email sudah terdaftar!";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (nama, email, password) VALUES ('$nama', '$email_db', '$password_hash')";
            
            // Menggunakan SweetAlert2 agar tidak muncul tulisan "localhost says"
            if (mysqli_query($conn, $query)) {
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
                            title: 'Registrasi Berhasil! 🚀',
                            text: 'Akun kamu sudah terdaftar. Silakan login!',
                            icon: 'success',
                            confirmButtonColor: '#2b388f',
                            confirmButtonText: 'OK, Login Sekarang'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location = 'login.php';
                            }
                        });
                    </script>
                </body>
                </html>
                ";
                exit;
            } else {
                $pesan = "Gagal mendaftar!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun -> MyDashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="login.css">
    <link rel="icon" href="baru.png">
</head>
<body>

    <div class="register-card">
        <h2>Daftar Akun 🚀</h2>
        <p class="subtitle">Buat akun untuk mengelola dashboard-mu</p>

        <?php if ($pesan): ?>
            <div class="alert-error"><?= $pesan; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" placeholder="Masukkan nama lengkap" required autocomplete="off">
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="contoh@email.com" required autocomplete="off">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Maximal 8 Digit" required>
            </div>

            <button type="submit" name="register" class="btn-submit">Daftar Sekarang</button>
        </form>

        <div class="auth-footer">
            Sudah punya akun? <a href="login.php">Login di sini</a>
        </div>
    </div>

    <script src="auth.js"></script>
</body>
</html>