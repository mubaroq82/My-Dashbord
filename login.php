<?php
session_start();
require 'koneksi.php';

$error = '';

if (isset($_POST['login'])) {
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Validasi panjang password di sisi PHP
    if (strlen($password) > 9) {
        $error = "Password maksimal 8 karakter!";
    } else {
        $result = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");

        if (mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);
            if (password_verify($password, $row['password'])) {
                $_SESSION['login']   = true;
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['nama']    = $row['nama'];

                header("Location: dashboard.php");
                exit;
            }
        }
        $error = "Email atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login -> MyDashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="login.css">
    <link rel="icon" href="baru.png">
</head>
<body>

    <div class="login-card">
        <h2>Selamat Datang! 👋</h2>
        <p class="subtitle">Masuk ke akun MyDashboard kamu</p>

        <?php if ($error): ?>
            <div class="alert-error"><?= $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="contoh@email.com" required autocomplete="off">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Maximal 8 Digit" required>
            </div>

            <button type="submit" name="login" class="btn-submit">Masuk</button>
        </form>

        <div class="auth-footer">
            Belum punya akun? <a href="register.php">Daftar di sini</a>
        </div>
    </div>

    <script src="auth.js"></script>
</body>
</html>