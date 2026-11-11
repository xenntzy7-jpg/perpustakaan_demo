<?php
require_once "koneksi.php";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Koneksi | Perpustakaan Digital</title>
    <link rel="stylesheet" href="assets/css/perpustakaan.css">
</head>
<body class="legacy-document">
<?php require "partials/site-header.php"; ?>
<main class="legacy-page" id="konten" tabindex="-1">
    <section class="empty-state empty-state--page" aria-labelledby="connection-title">
        <p class="eyebrow eyebrow--ink">Status layanan</p>
        <h1 id="connection-title">Koneksi database berhasil.</h1>
        <p>Database perpustakaan dapat diakses.</p>
        <a class="button button--primary" href="index.php">Kembali ke beranda</a>
    </section>
</main>
<footer class="site-footer">
    <div class="page-container site-footer__inner">
        <p>Perpustakaan Digital</p>
    </div>
</footer>
</body>
</html>
