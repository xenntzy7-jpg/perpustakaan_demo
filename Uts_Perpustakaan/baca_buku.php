<?php
session_start();

if (!isset($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'anggota') {
    header("Location: login.php");
    exit;
}

require_once "koneksi.php";
require_once "ebook_helper.php";

function tampilkan_error_pembaca_buku(int $status, string $judul, string $pesan, string $returnHref = "katalog.php", string $returnLabel = "Kembali ke katalog"): void
{
    http_response_code($status);
    $errorTitle = $judul;
    $errorMessage = $pesan;
    require "partials/error-page.php";
    exit;
}

$bookId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($bookId === false || $bookId === null || $bookId < 1) {
    tampilkan_error_pembaca_buku(400, "Permintaan tidak valid", "Identitas buku tidak valid.");
}

$statement = mysqli_prepare($conn, "SELECT id, judul, file_ebook FROM buku WHERE id = ?");
if (!$statement) {
    error_log("Gagal menyiapkan halaman baca e-book: " . mysqli_error($conn));
    tampilkan_error_pembaca_buku(500, "E-book belum dapat dibuka", "Terjadi kesalahan saat memeriksa file. Silakan coba lagi.");
}

mysqli_stmt_bind_param($statement, "i", $bookId);
if (!mysqli_stmt_execute($statement)) {
    error_log("Gagal menjalankan halaman baca e-book: " . mysqli_stmt_error($statement));
    mysqli_stmt_close($statement);
    tampilkan_error_pembaca_buku(500, "E-book belum dapat dibuka", "Terjadi kesalahan saat memeriksa file. Silakan coba lagi.");
}

$bookResult = mysqli_stmt_get_result($statement);
if (!$bookResult) {
    mysqli_stmt_close($statement);
    tampilkan_error_pembaca_buku(500, "E-book belum dapat dibuka", "Terjadi kesalahan saat membaca data buku. Silakan coba lagi.");
}

$book = mysqli_fetch_assoc($bookResult);
mysqli_stmt_close($statement);

if (!$book) {
    tampilkan_error_pembaca_buku(404, "Buku tidak ditemukan", "Buku ini tidak tersedia di katalog.");
}

if (lokasi_file_ebook($book['file_ebook'] ?? null) === null) {
    tampilkan_error_pembaca_buku(
        404,
        "E-book tidak tersedia",
        "File e-book untuk buku ini belum tersedia.",
        "detail_buku.php?id=" . (int) $book['id'],
        "Kembali ke detail buku"
    );
}

$bookTitle = htmlspecialchars($book['judul'], ENT_QUOTES, 'UTF-8');
$bookId = (int) $book['id'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Baca e-book <?php echo $bookTitle; ?> secara online di Perpustakaan Digital.">
    <title>Baca <?php echo $bookTitle; ?> | Perpustakaan Digital</title>
    <link rel="stylesheet" href="assets/css/perpustakaan.css">
</head>
<body>
<?php require "partials/site-header.php"; ?>

<main id="konten" class="page-container detail-page ebook-reader-page" tabindex="-1">
    <nav class="breadcrumbs" aria-label="Lokasi halaman">
        <a href="katalog.php">Katalog</a>
        <span aria-hidden="true">/</span>
        <a href="detail_buku.php?id=<?php echo $bookId; ?>"><?php echo $bookTitle; ?></a>
        <span aria-hidden="true">/</span>
        <span aria-current="page">Baca e-book</span>
    </nav>

    <section aria-labelledby="reader-title">
        <header class="ebook-reader__heading">
            <p class="eyebrow eyebrow--ink">Ruang baca</p>
            <h1 id="reader-title"><?php echo $bookTitle; ?></h1>
            <p class="ebook-reader__note">E-book dibuka di halaman ini. Gunakan kontrol pembaca PDF untuk menavigasi halaman.</p>
        </header>

        <iframe
            class="ebook-reader__frame"
            src="baca_file_ebook.php?id=<?php echo $bookId; ?>"
            title="Pembaca PDF: <?php echo $bookTitle; ?>"
            width="1280"
            height="760"
            loading="eager"
        >Browser Anda tidak dapat menampilkan PDF. Kembali ke detail buku untuk pilihan lain.</iframe>

        <div class="action-row ebook-reader__actions">
            <a class="button button--primary" href="detail_buku.php?id=<?php echo $bookId; ?>">Kembali ke detail buku</a>
            <a class="text-link" href="katalog.php">Kembali ke katalog</a>
        </div>
    </section>
</main>

<footer class="site-footer">
    <div class="page-container site-footer__inner">
        <p>Perpustakaan Digital</p>
        <a href="anggota.php">Dashboard anggota</a>
    </div>
</footer>
</body>
</html>
