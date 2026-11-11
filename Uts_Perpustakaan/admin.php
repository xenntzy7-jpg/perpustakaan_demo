
<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | Perpustakaan Digital</title>
    <link rel="stylesheet" href="assets/css/perpustakaan.css">
</head>
<body class="legacy-document">
<?php require "partials/site-header.php"; ?>
<main class="legacy-page admin-dashboard" id="konten" tabindex="-1">
    <div class="page-intro">
        <p class="eyebrow eyebrow--ink">Ruang pengelola</p>
        <h1>Dashboard admin</h1>
        <p class="page-intro__lead">Selamat datang, <?php echo htmlspecialchars($_SESSION['nama'], ENT_QUOTES, 'UTF-8'); ?>. Pilih aktivitas pengelolaan perpustakaan.</p>
    </div>
    <ul class="admin-menu">
        <li><a href="buku.php">Kelola data buku</a></li>
        <li><a href="tambah_buku.php">Tambah buku</a></li>
        <li><a href="persetujuan_peminjaman.php">Persetujuan peminjaman</a></li>
        <li><a href="persetujuan_pengembalian.php">Persetujuan pengembalian</a></li>
    </ul>
</main>
<footer class="site-footer">
    <div class="page-container site-footer__inner">
        <p>Perpustakaan Digital</p>
        <a href="logout.php">Keluar dari akun</a>
    </div>
</footer>
</body>
</html>
