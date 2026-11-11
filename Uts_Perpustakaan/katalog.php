<?php
session_start();
require_once "koneksi.php";
require_once "sampul_helper.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'anggota') {
    header("Location: login.php");
    exit;
}

$search = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : "";
$category = isset($_GET['kategori']) && is_string($_GET['kategori']) ? trim($_GET['kategori']) : "";
$availableOnly = isset($_GET['tersedia']) && $_GET['tersedia'] === "1" ? 1 : 0;
$sort = isset($_GET['urut']) && is_string($_GET['urut']) ? $_GET['urut'] : "terbaru";
$sortOptions = [
    "terbaru" => "id DESC",
    "judul" => "judul ASC",
    "penulis" => "penulis ASC",
    "tersedia" => "stok DESC, judul ASC",
];
$orderBy = $sortOptions[$sort] ?? $sortOptions['terbaru'];
$searchPattern = "%" . $search . "%";

$booksStatement = mysqli_prepare($conn, "
    SELECT *
    FROM buku
    WHERE (? = '' OR judul LIKE ? OR penulis LIKE ? OR kategori LIKE ?)
      AND (? = '' OR kategori = ?)
      AND (? = 0 OR stok > 0)
    ORDER BY $orderBy
");
if (!$booksStatement) {
    http_response_code(500);
    die("Katalog belum dapat dimuat. Silakan coba lagi nanti.");
}

mysqli_stmt_bind_param(
    $booksStatement,
    "ssssssi",
    $search,
    $searchPattern,
    $searchPattern,
    $searchPattern,
    $category,
    $category,
    $availableOnly
);
if (!mysqli_stmt_execute($booksStatement)) {
    http_response_code(500);
    die("Pencarian katalog gagal. Silakan coba lagi.");
}
$booksResult = mysqli_stmt_get_result($booksStatement);
if (!$booksResult) {
    http_response_code(500);
    die("Hasil katalog tidak dapat dibaca. Silakan coba lagi.");
}

$categoriesResult = mysqli_query($conn, "SELECT DISTINCT kategori FROM buku WHERE kategori <> '' ORDER BY kategori");
if (!$categoriesResult) {
    http_response_code(500);
    die("Daftar kategori belum dapat dimuat. Silakan coba lagi.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Cari buku berdasarkan judul, penulis, kategori, dan ketersediaan di Perpustakaan Digital.">
    <title>Katalog Buku | Perpustakaan Digital</title>
    <link rel="stylesheet" href="assets/css/perpustakaan.css">
</head>
<body>
<?php require "partials/site-header.php"; ?>

<main id="konten" tabindex="-1">
    <section class="catalog-intro">
        <div class="page-container catalog-intro__inner">
            <div>
                <p class="eyebrow eyebrow--ink">Koleksi perpustakaan</p>
                <h1>Jelajahi katalog buku.</h1>
                <p class="catalog-intro__lead">Cari judul, penulis, atau kategori. Ketersediaan mengikuti stok buku saat ini.</p>
            </div>
            <p class="catalog-intro__welcome">Selamat datang, <?php echo htmlspecialchars($_SESSION['nama'], ENT_QUOTES, 'UTF-8'); ?>.</p>
        </div>
    </section>

    <div class="page-container catalog-layout">
        <aside class="catalog-filters" aria-label="Pencarian dan filter katalog">
            <form method="GET" action="katalog.php" class="filter-form">
                <div class="form-field">
                    <label for="q">Cari judul, penulis, atau kategori</label>
                    <input type="search" id="q" name="q" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="form-field">
                    <label for="kategori">Kategori</label>
                    <select id="kategori" name="kategori">
                        <option value="">Semua kategori</option>
                        <?php while ($categoryRow = mysqli_fetch_assoc($categoriesResult)) { ?>
                            <option
                                value="<?php echo htmlspecialchars($categoryRow['kategori'], ENT_QUOTES, 'UTF-8'); ?>"
                                <?php echo $category === $categoryRow['kategori'] ? "selected" : ""; ?>
                            ><?php echo htmlspecialchars($categoryRow['kategori'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-field">
                    <label for="urut">Urutkan</label>
                    <select id="urut" name="urut">
                        <option value="terbaru" <?php echo $sort === "terbaru" ? "selected" : ""; ?>>Terbaru ditambahkan</option>
                        <option value="judul" <?php echo $sort === "judul" ? "selected" : ""; ?>>Judul A sampai Z</option>
                        <option value="penulis" <?php echo $sort === "penulis" ? "selected" : ""; ?>>Penulis A sampai Z</option>
                        <option value="tersedia" <?php echo $sort === "tersedia" ? "selected" : ""; ?>>Stok terbanyak</option>
                    </select>
                </div>
                <label class="filter-check" for="tersedia">
                    <input type="checkbox" id="tersedia" name="tersedia" value="1" <?php echo $availableOnly === 1 ? "checked" : ""; ?>>
                    <span>Hanya tampilkan buku yang tersedia</span>
                </label>
                <div class="action-row">
                    <button class="button button--primary" type="submit">Terapkan filter</button>
                    <a class="text-link" href="katalog.php">Hapus filter</a>
                </div>
            </form>
        </aside>

        <section class="catalog-results" aria-labelledby="catalog-results-title">
            <div class="section-heading section-heading--compact">
                <div>
                    <p class="eyebrow eyebrow--ink">Koleksi</p>
                    <h2 id="catalog-results-title">Buku perpustakaan</h2>
                </div>
                <p class="result-count"><?php echo mysqli_num_rows($booksResult); ?> buku</p>
            </div>

            <?php if (mysqli_num_rows($booksResult) > 0) { ?>
                <ul class="book-grid">
                    <?php while ($book = mysqli_fetch_assoc($booksResult)) { ?>
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
                                    <p class="book-card__availability"><?php echo (int) $book['stok'] > 0 ? "Stok tersedia: " . (int) $book['stok'] : "Stok habis"; ?></p>
                                    <a class="book-card__action" href="detail_buku.php?id=<?php echo (int) $book['id']; ?>">Lihat detail buku</a>
                                </div>
                            </article>
                        </li>
                    <?php } ?>
                </ul>
            <?php } else { ?>
                <p class="empty-state">Tidak ada buku yang cocok dengan pencarian ini. Coba ubah kata kunci atau hapus sebagian filter.</p>
            <?php } ?>
        </section>
    </div>
</main>

<footer class="site-footer">
    <div class="page-container site-footer__inner">
        <p>Perpustakaan Digital</p>
        <a href="anggota.php">Kembali ke dashboard</a>
    </div>
</footer>
</body>
</html>
<?php
mysqli_stmt_close($booksStatement);
?>
