<?php
session_start();
require_once "koneksi.php";
require_once "sampul_helper.php";

$booksQuery = mysqli_query($conn, "SELECT * FROM buku ORDER BY id DESC LIMIT 3");
if (!$booksQuery) {
    http_response_code(500);
    die("Katalog belum dapat dimuat. Silakan coba lagi nanti.");
}

$isMember = isset($_SESSION['id']) && ($_SESSION['role'] ?? '') === 'anggota';
$isAdmin = isset($_SESSION['id']) && ($_SESSION['role'] ?? '') === 'admin';
$catalogUrl = $isMember ? "katalog.php" : "login.php";
$accountUrl = $isAdmin ? "admin.php" : ($isMember ? "anggota.php" : "login.php");
$accountLabel = $isAdmin ? "Dashboard admin" : ($isMember ? "Dashboard anggota" : "Masuk");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Temukan koleksi buku Perpustakaan Digital dan ajukan peminjaman setelah masuk sebagai anggota.">
    <title>Perpustakaan Digital</title>
    <link rel="stylesheet" href="assets/css/perpustakaan.css">
</head>
<body>
<?php require "partials/site-header.php"; ?>

<main id="konten" tabindex="-1">
    <section class="home-hero">
        <div class="page-container home-hero__inner">
            <div class="home-hero__copy">
                <p class="eyebrow">Ruang baca digital</p>
                <h1>Temukan bacaan untuk halaman berikutnya.</h1>
                <p class="home-hero__lead">Telusuri koleksi perpustakaan, lihat ketersediaan buku, lalu ajukan peminjaman sebagai anggota.</p>
                <div class="action-row">
                    <a class="button button--light" href="<?php echo htmlspecialchars($catalogUrl, ENT_QUOTES, 'UTF-8'); ?>">Lihat katalog</a>
                    <?php if (!$isMember && !$isAdmin) { ?>
                        <a class="button button--outline-light" href="register.php">Daftar anggota</a>
                    <?php } else { ?>
                        <a class="button button--outline-light" href="<?php echo htmlspecialchars($accountUrl, ENT_QUOTES, 'UTF-8'); ?>">Buka dashboard</a>
                    <?php } ?>
                </div>
            </div>
            <div class="home-hero__note">
                <p>Buku, penulis, dan stok yang ditampilkan berasal dari katalog perpustakaan.</p>
            </div>
        </div>
    </section>

    <section class="page-container section-block" aria-labelledby="latest-books">
        <div class="section-heading">
            <div>
                <p class="eyebrow eyebrow--ink">Dari koleksi</p>
                <h2 id="latest-books">Buku di katalog</h2>
            </div>
            <a class="text-link" href="<?php echo htmlspecialchars($catalogUrl, ENT_QUOTES, 'UTF-8'); ?>">Lihat semua buku</a>
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
        <a href="<?php echo htmlspecialchars($catalogUrl, ENT_QUOTES, 'UTF-8'); ?>">Katalog buku</a>
    </div>
</footer>
</body>
</html>
