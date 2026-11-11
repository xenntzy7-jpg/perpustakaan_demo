
<?php
session_start();
require_once "koneksi.php";
require_once "sampul_helper.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$pesan = "";

if (isset($_POST['simpan'])) {
    $kode = trim($_POST['kode_buku']);
    $judul = trim($_POST['judul']);
    $penulis = trim($_POST['penulis']);
    $penerbit = trim($_POST['penerbit']);
    $tahun = (int) $_POST['tahun_terbit'];
    $kategori = trim($_POST['kategori']);
    $stok = (int) $_POST['stok'];
    $sampul = null;

    try {
        $sampul = simpan_sampul_buku($_FILES['sampul'] ?? []);
    } catch (RuntimeException $error) {
        $pesan = $error->getMessage();
    }

    if ($pesan === "") {
        $stmt = mysqli_prepare($conn, "
            INSERT INTO buku
            (kode_buku, judul, penulis, penerbit, tahun_terbit, kategori, stok, sampul)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            $pesan = "SQL Error: " . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param(
                $stmt,
                "ssssisis",
                $kode,
                $judul,
                $penulis,
                $penerbit,
                $tahun,
                $kategori,
                $stok,
                $sampul
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: buku.php");
                exit;
            }

            $pesan = "Database Error: " . mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
        }

        if ($sampul !== null) {
            try {
                hapus_sampul_buku_jika_tidak_dipakai($conn, $sampul);
            } catch (RuntimeException $error) {
                $pesan .= " " . $error->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Buku | Perpustakaan Digital</title>
    <link rel="stylesheet" href="assets/css/perpustakaan.css">
</head>
<body class="legacy-document">
<?php require "partials/site-header.php"; ?>
<main class="legacy-page" id="konten" tabindex="-1">
<div class="page-intro">
    <p class="eyebrow eyebrow--ink">Koleksi perpustakaan</p>
    <h1>Tambah buku</h1>
    <p class="page-intro__lead">Lengkapi metadata dan, jika tersedia, tambahkan foto sampul buku.</p>
</div>
<?php if ($pesan != "") { ?>
    <p class="message message--error" role="alert">
        <?php echo htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8'); ?>
    </p>
<?php } ?>

<form method="POST" enctype="multipart/form-data" class="form-panel">
    <div class="form-grid">
        <div class="form-field">
            <label for="kode_buku">Kode buku</label>
            <input type="text" id="kode_buku" name="kode_buku" required>
        </div>
        <div class="form-field">
            <label for="judul">Judul buku</label>
            <input type="text" id="judul" name="judul" required>
        </div>
        <div class="form-field">
            <label for="penulis">Penulis</label>
            <input type="text" id="penulis" name="penulis" required>
        </div>
        <div class="form-field">
            <label for="penerbit">Penerbit</label>
            <input type="text" id="penerbit" name="penerbit">
        </div>
        <div class="form-field">
            <label for="tahun_terbit">Tahun terbit</label>
            <input type="number" id="tahun_terbit" name="tahun_terbit" min="1901" max="2155">
        </div>
        <div class="form-field">
            <label for="kategori">Kategori</label>
            <input type="text" id="kategori" name="kategori">
        </div>
        <div class="form-field">
            <label for="stok">Jumlah stok</label>
            <input type="number" id="stok" name="stok" min="0" value="0" required>
        </div>
        <div class="form-field form-field--wide">
            <label for="sampul">Foto sampul</label>
            <input
                type="file"
                id="sampul"
                name="sampul"
                accept="image/jpeg,image/png,image/webp"
                data-sampul-preview="preview-sampul"
                data-sampul-status="status-preview-sampul"
            >
            <p class="form-hint">JPG, PNG, atau WebP. Ukuran maksimal 5 MB.</p>
            <p class="form-hint" id="status-preview-sampul" aria-live="polite">Belum ada file dipilih.</p>
            <img id="preview-sampul" alt="Pratinjau foto sampul yang dipilih" hidden width="160" height="240">
        </div>
    </div>
    <div class="form-actions">
        <button class="button button--primary" type="submit" name="simpan">Simpan buku</button>
        <a class="text-link" href="buku.php">Kembali ke daftar buku</a>
    </div>
</form>
</main>
<footer class="site-footer">
    <div class="page-container site-footer__inner">
        <p>Perpustakaan Digital</p>
        <a href="admin.php">Dashboard admin</a>
    </div>
</footer>
<script src="assets/js/preview-sampul.js" defer></script>

</body>
</html>
