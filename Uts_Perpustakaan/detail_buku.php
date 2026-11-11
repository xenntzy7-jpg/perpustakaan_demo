<?php
session_start();
require_once "koneksi.php";
require_once "sampul_helper.php";
require_once "ebook_helper.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'anggota') {
    header("Location: login.php");
    exit;
}

$bookId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$statement = mysqli_prepare($conn, "SELECT * FROM buku WHERE id = ?");
if (!$statement) {
    http_response_code(500);
    die("Detail buku belum dapat dimuat. Silakan coba lagi.");
}

mysqli_stmt_bind_param($statement, "i", $bookId);
if (!mysqli_stmt_execute($statement)) {
    http_response_code(500);
    die("Detail buku belum dapat dimuat. Silakan coba lagi.");
}

$bookResult = mysqli_stmt_get_result($statement);
if (!$bookResult) {
    mysqli_stmt_close($statement);
    http_response_code(500);
    die("Detail buku belum dapat dibaca. Silakan coba lagi.");
}
$book = mysqli_fetch_assoc($bookResult);
mysqli_stmt_close($statement);

if (!$book) {
    http_response_code(404);
}

$cover = $book ? ukuran_tampilan_sampul_buku($book['sampul'] ?? null, 440, 660) : null;
$ebookTersedia = $book && lokasi_file_ebook($book['file_ebook'] ?? null) !== null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $book ? "Informasi buku " . htmlspecialchars($book['judul'], ENT_QUOTES, 'UTF-8') . " di Perpustakaan Digital." : "Buku tidak ditemukan di katalog Perpustakaan Digital."; ?>">
    <title><?php echo $book ? htmlspecialchars($book['judul'], ENT_QUOTES, 'UTF-8') . " | Perpustakaan Digital" : "Buku tidak ditemukan | Perpustakaan Digital"; ?></title>
    <link rel="stylesheet" href="assets/css/perpustakaan.css">
</head>
<body>
<?php require "partials/site-header.php"; ?>

<main id="konten" class="page-container detail-page" tabindex="-1">
    <nav class="breadcrumbs" aria-label="Lokasi halaman">
        <a href="index.php">Beranda</a>
        <span aria-hidden="true">/</span>
        <a href="katalog.php">Katalog</a>
        <?php if ($book) { ?>
            <span aria-hidden="true">/</span>
            <span aria-current="page"><?php echo htmlspecialchars($book['judul'], ENT_QUOTES, 'UTF-8'); ?></span>
        <?php } ?>
    </nav>

    <?php if ($book) { ?>
        <article class="book-detail">
            <div class="book-detail__visual">
                <?php if ($cover !== null) { ?>
                    <img
                        class="book-detail__image"
                        src="<?php echo htmlspecialchars($cover['src'], ENT_QUOTES, 'UTF-8'); ?>"
                        width="<?php echo $cover['width']; ?>"
                        height="<?php echo $cover['height']; ?>"
                        alt="Sampul buku <?php echo htmlspecialchars($book['judul'], ENT_QUOTES, 'UTF-8'); ?>"
                    >
                <?php } else { ?>
                    <div class="book-cover-placeholder book-cover-placeholder--detail" aria-label="Sampul belum tersedia">
                        <span>Sampul belum tersedia</span>
                    </div>
                <?php } ?>
            </div>

            <div class="book-detail__content">
                <p class="eyebrow eyebrow--ink"><?php echo htmlspecialchars($book['kategori'], ENT_QUOTES, 'UTF-8'); ?></p>
                <h1><?php echo htmlspecialchars($book['judul'], ENT_QUOTES, 'UTF-8'); ?></h1>
                <p class="book-detail__author">Oleh <?php echo htmlspecialchars($book['penulis'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="book-detail__availability">
                    <?php echo (int) $book['stok'] > 0
                        ? "Tersedia, stok " . (int) $book['stok'] . " buku."
                        : "Stok buku sedang habis."; ?>
                </p>

                <dl class="book-facts">
                    <div><dt>Kode buku</dt><dd><?php echo htmlspecialchars($book['kode_buku'], ENT_QUOTES, 'UTF-8'); ?></dd></div>
                    <div><dt>Penerbit</dt><dd><?php echo htmlspecialchars($book['penerbit'], ENT_QUOTES, 'UTF-8'); ?></dd></div>
                    <div><dt>Tahun terbit</dt><dd><?php echo htmlspecialchars($book['tahun_terbit'], ENT_QUOTES, 'UTF-8'); ?></dd></div>
                    <div><dt>Kategori</dt><dd><?php echo htmlspecialchars($book['kategori'], ENT_QUOTES, 'UTF-8'); ?></dd></div>
                </dl>

                <div class="action-row">
                    <?php if ((int) $book['stok'] > 0) { ?>
                        <a class="button button--primary" href="pinjam.php?id=<?php echo (int) $book['id']; ?>">Ajukan peminjaman</a>
                    <?php } else { ?>
                        <span class="button button--unavailable" aria-disabled="true">Stok habis</span>
                    <?php } ?>
                    <?php if ($ebookTersedia) { ?>
                        <a class="text-link" href="baca_buku.php?id=<?php echo (int) $book['id']; ?>">Baca e-book</a>
                    <?php } ?>
                    <a class="text-link" href="katalog.php">Kembali ke katalog</a>
                </div>
            </div>
        </article>
    <?php } else { ?>
        <section class="empty-state empty-state--page" aria-labelledby="not-found-title">
            <p class="eyebrow eyebrow--ink">Katalog</p>
            <h1 id="not-found-title">Buku tidak ditemukan.</h1>
            <p>Buku ini tidak tersedia di katalog. Kembali ke katalog untuk memilih buku lain.</p>
            <a class="button button--primary" href="katalog.php">Kembali ke katalog</a>
        </section>
    <?php } ?>
</main>

<footer class="site-footer">
    <div class="page-container site-footer__inner">
        <p>Perpustakaan Digital</p>
        <a href="anggota.php">Dashboard anggota</a>
    </div>
</footer>
</body>
</html>
