<?php
require_once "koneksi.php";

$pesan = "";

if (isset($_POST['daftar'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = "INSERT INTO users (nama, username, email, password, role)
              VALUES ('$nama', '$username', '$email', '$password', 'anggota')";

    if (mysqli_query($conn, $query)) {
        $pesan = "Pendaftaran berhasil! Silakan login.";
    } else {
        $pesan = "Pendaftaran gagal. Username atau email mungkin sudah digunakan.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Anggota | Perpustakaan Digital</title>
    <link rel="stylesheet" href="assets/css/perpustakaan.css">
</head>
<body class="legacy-document">
<?php require "partials/site-header.php"; ?>
<main class="legacy-page" id="konten" tabindex="-1">
    <div class="page-intro">
        <p class="eyebrow eyebrow--ink">Ruang anggota</p>
        <h1>Daftar sebagai anggota.</h1>
        <p class="page-intro__lead">Buat akun untuk mengakses katalog dan layanan peminjaman.</p>
    </div>

    <?php if ($pesan !== "") { ?>
        <p class="message <?php echo strpos($pesan, 'berhasil') !== false ? 'message--success' : 'message--error'; ?>" role="status">
            <?php echo htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8'); ?>
        </p>
    <?php } ?>

    <form method="POST" class="form-panel form-panel--narrow">
        <div class="form-field">
            <label for="nama">Nama lengkap</label>
            <input type="text" id="nama" name="nama" autocomplete="name" required>
        </div>
        <div class="form-field">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" autocomplete="username" required>
        </div>
        <div class="form-field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" autocomplete="email" required>
        </div>
        <div class="form-field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" autocomplete="new-password" required>
        </div>
        <div class="form-actions">
            <button class="button button--primary" type="submit" name="daftar">Daftar</button>
            <a class="text-link" href="login.php">Sudah punya akun? Masuk</a>
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
