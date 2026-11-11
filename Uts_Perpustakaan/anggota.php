<?php
session_start();
require_once "koneksi.php";
require_once "sampul_helper.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'anggota') {
    header("Location: login.php");
    exit;
}

$booksQuery = mysqli_query($conn, "SELECT * FROM buku ORDER BY id DESC LIMIT 3");
if (!$booksQuery) {
    http_response_code(500);
    die("Katalog belum dapat dimuat. Silakan coba lagi nanti.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Dashboard anggota Perpustakaan Digital, akses katalog dan aktivitas peminjaman.">
    <title>Dashboard Anggota | Perpustakaan Digital</title>
    <link rel="stylesheet" href="assets/css/perpustakaan.css">
</head>
<body>
<?php require "partials/site-header.php"; ?>

<main id="konten" tabindex="-1">
    <section class="member-hero">
        <div class="page-container member-hero__inner">
            <div>
                <p class="eyebrow">Ruang anggota</p>
                <h1>Selamat datang, <?php echo htmlspecialchars($_SESSION['nama'], ENT_QUOTES, 'UTF-8'); ?>.</h1>
                <p class="member-hero__lead">Cari bacaan dari koleksi, lalu pantau pengajuan dan pengembalian buku dari menu anggota.</p>
                <a class="button button--light" href="katalog.php">Buka katalog buku</a>
            </div>
            <nav class="member-shortcuts" aria-label="Aktivitas anggota">
                <a href="riwayat_peminjaman.php">Riwayat peminjaman</a>
                <a href="pengembalian.php">Ajukan pengembalian</a>
            </nav>
        </div>
    </section>

    <section class="page-container section-block" aria-labelledby="member-latest-books">
        <div class="section-heading">
            <div>
                <p class="eyebrow eyebrow--ink">Koleksi terbaru</p>
                <h2 id="member-latest-books">Buku di katalog</h2>
            </div>
            <a class="text-link" href="katalog.php">Cari buku</a>
        </div>

        <?php if (mysqli_num_rows($booksQuery) > 0) { ?>
            <ul class="book-grid book-grid--featured">
                <?php while ($book = mysqli_fetch_assoc($booksQuery)) { ?>
                    <?php $cover = ukuran_tampilan_sampul_buku($book['sampul'] ?? null, 220, 330); ?>
                    <li>
                        <article class="book-card">
                            <a class="book-card__cover-link" href="detail_buku.php?id=<?php echo (int) $book['id']; ?>" aria-label="Lihat detail buku <?php echo htmlspecialchars($book['judul'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?php if ($cover !== null) { ?>
                                    <span class="book-cover-frame">
                                        <img
                                            class="book-cover-image"
                                            src="<?php echo htmlspecialchars($cover['src'], ENT_QUOTES, 'UTF-8'); ?>"
                                            width="<?php echo $cover['width']; ?>"
                                            height="<?php echo $cover['height']; ?>"
                                            alt="Sampul buku <?php echo htmlspecialchars($book['judul'], ENT_QUOTES, 'UTF-8'); ?>"
                                        >
                                    </span>
                                <?php } else { ?>
                                    <span class="book-cover-placeholder" aria-label="Sampul belum tersedia">
                                        <span>Sampul belum tersedia</span>
                                    </span>
                                <?php } ?>
                            </a>
                            <div class="book-card__body">
                                <p class="book-card__meta"><?php echo htmlspecialchars($book['kategori'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <h3><a href="detail_buku.php?id=<?php echo (int) $book['id']; ?>"><?php echo htmlspecialchars($book['judul'], ENT_QUOTES, 'UTF-8'); ?></a></h3>
                                <p class="book-card__author"><?php echo htmlspecialchars($book['penulis'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="book-card__availability"><?php echo (int) $book['stok'] > 0 ? "Tersedia untuk dipinjam" : "Stok habis"; ?></p>
                            </div>
                        </article>
                    </li>
                <?php } ?>
            </ul>
        <?php } else { ?>
            <p class="empty-state">Belum ada buku di katalog. Silakan periksa kembali nanti.</p>
        <?php } ?>
    </section>
</main>

<footer class="site-footer">
    <div class="page-container site-footer__inner">
        <p>Perpustakaan Digital</p>
        <a href="logout.php">Keluar dari akun</a>
    </div>
</footer>
</body>
</html>
