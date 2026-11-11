
<?php
session_start();
require_once "koneksi.php";

$pesan = "";

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    $user = mysqli_fetch_assoc($query);

    if ($user && $password === $user['password']) {
        $_SESSION['id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] == 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: anggota.php");
        }
        exit;
    } else {
        $pesan = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk | Perpustakaan Digital</title>
    <link rel="stylesheet" href="assets/css/perpustakaan.css">
</head>
<body class="legacy-document">
<?php require "partials/site-header.php"; ?>
<main class="legacy-page" id="konten" tabindex="-1">
    <div class="page-intro">
        <p class="eyebrow eyebrow--ink">Ruang anggota</p>
        <h1>Masuk ke perpustakaan.</h1>
        <p class="page-intro__lead">Masuk untuk melihat koleksi dan mengelola aktivitas peminjaman.</p>
    </div>

    <?php if ($pesan !== "") { ?>
        <p class="message message--error" role="alert"><?php echo htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php } ?>

    <form method="POST" class="form-panel form-panel--narrow">
        <div class="form-field">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" autocomplete="username" required>
        </div>
        <div class="form-field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" autocomplete="current-password" required>
        </div>
        <div class="form-actions">
            <button class="button button--primary" type="submit" name="login">Masuk</button>
            <a class="text-link" href="register.php">Daftar anggota</a>
        </div>
    </form>
</main>
<footer class="site-footer">
    <div class="page-container site-footer__inner">
        <p>Perpustakaan Digital</p>
        <a href="index.php">Kembali ke beranda</a>
    </div>
</footer>
</body>
</html>
