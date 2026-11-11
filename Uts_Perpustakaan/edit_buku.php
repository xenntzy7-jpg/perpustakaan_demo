<?php
session_start();
require_once "koneksi.php";
require_once "sampul_helper.php";
require_once "ebook_helper.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$id = (int) ($_GET['id'] ?? 0);

$query = mysqli_query($conn, "SELECT * FROM buku WHERE id = $id");
$buku = mysqli_fetch_assoc($query);

if (!$buku) {
    die("Data buku tidak ditemukan!");
}

$pesan = "";

if (isset($_POST['update'])) {
    $kode = $_POST['kode_buku'];
    $judul = $_POST['judul'];
    $penulis = $_POST['penulis'];
    $penerbit = $_POST['penerbit'];
    $tahun = $_POST['tahun_terbit'];
    $kategori = $_POST['kategori'];
    $stok = (int) $_POST['stok'];
    $sampulBaru = null;
    $ebookBaru = null;

    try {
        $sampulBaru = simpan_sampul_buku($_FILES['sampul'] ?? []);
        $ebookBaru = simpan_file_ebook($_FILES['ebook'] ?? []);
    } catch (RuntimeException $error) {
        $pesan = $error->getMessage();
    }

    if ($pesan === "") {
        $sampulTersimpan = $sampulBaru ?? $buku['sampul'];
        $ebookTersimpan = $ebookBaru ?? ($buku['file_ebook'] ?? null);
        $stmt = mysqli_prepare($conn,
            "UPDATE buku SET kode_buku=?, judul=?, penulis=?,
            penerbit=?, tahun_terbit=?, kategori=?, stok=?, sampul=?, file_ebook=?
            WHERE id=?");

        if (!$stmt) {
            $pesan = "SQL Error: " . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param(
                $stmt, "ssssisissi",
                $kode, $judul, $penulis, $penerbit,
                $tahun, $kategori, $stok, $sampulTersimpan, $ebookTersimpan, $id
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);

                $sampulLama = $buku['sampul'];
                $ebookLama = $buku['file_ebook'] ?? null;
                $buku['kode_buku'] = $kode;
                $buku['judul'] = $judul;
                $buku['penulis'] = $penulis;
                $buku['penerbit'] = $penerbit;
                $buku['tahun_terbit'] = $tahun;
                $buku['kategori'] = $kategori;
                $buku['stok'] = $stok;
                $buku['sampul'] = $sampulTersimpan;
                $buku['file_ebook'] = $ebookTersimpan;

                if ($sampulBaru !== null && $sampulLama !== $sampulBaru) {
                    try {
                        hapus_sampul_buku_jika_tidak_dipakai($conn, $sampulLama);
                    } catch (RuntimeException $error) {
                        $pesan = "Data buku berhasil diperbarui, tetapi sampul lama gagal dihapus. "
                            . $error->getMessage();
                    }
                }

                if ($ebookBaru !== null && $ebookLama !== $ebookBaru) {
                    try {
                        hapus_file_ebook_jika_tidak_dipakai($conn, $ebookLama);
                    } catch (RuntimeException $error) {
                        $pesan = "Data buku berhasil diperbarui, tetapi e-book lama gagal dihapus. "
                            . $error->getMessage();
                    }
                }

                if ($pesan === "") {
                    header("Location: buku.php");
                    exit;
                }
            } else {
                $pesan = "Gagal memperbarui data buku: " . mysqli_stmt_error($stmt);
                mysqli_stmt_close($stmt);
            }
        }

    }

    if ($pesan !== "") {
        if ($sampulBaru !== null && $buku['sampul'] !== $sampulBaru) {
            try {
                hapus_sampul_buku_jika_tidak_dipakai($conn, $sampulBaru);
            } catch (RuntimeException $error) {
                $pesan .= " " . $error->getMessage();
            }
        }

        if ($ebookBaru !== null && ($buku['file_ebook'] ?? null) !== $ebookBaru) {
            try {
                hapus_file_ebook_jika_tidak_dipakai($conn, $ebookBaru);
            } catch (RuntimeException $error) {
                $pesan .= " " . $error->getMessage();
            }
        }
    }
}

$previewSampulSaatIni = ukuran_tampilan_sampul_buku($buku['sampul'] ?? null, 160, 240);
$fileEbookSaatIni = lokasi_file_ebook($buku['file_ebook'] ?? null);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Buku | Perpustakaan Digital</title>
    <link rel="stylesheet" href="assets/css/perpustakaan.css">
</head>
<body class="legacy-document">
<?php require "partials/site-header.php"; ?>
<main class="legacy-page" id="konten" tabindex="-1">
    <div class="page-intro">
        <p class="eyebrow eyebrow--ink">Koleksi perpustakaan</p>
        <h1>Edit data buku</h1>
        <p class="page-intro__lead">Perbarui metadata, foto sampul, atau file e-book PDF.</p>
    </div>
    <?php if ($pesan !== "") { ?>
        <p class="message message--error" role="alert">
            <?php echo htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8'); ?>
        </p>
    <?php } ?>

<form method="POST" enctype="multipart/form-data" class="form-panel">
    <div class="form-grid">
        <div class="form-field">
            <label for="kode_buku">Kode buku</label>
            <input type="text" id="kode_buku" name="kode_buku" value="<?php echo htmlspecialchars($buku['kode_buku'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        <div class="form-field">
            <label for="judul">Judul buku</label>
            <input type="text" id="judul" name="judul" value="<?php echo htmlspecialchars($buku['judul'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        <div class="form-field">
            <label for="penulis">Penulis</label>
            <input type="text" id="penulis" name="penulis" value="<?php echo htmlspecialchars($buku['penulis'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        <div class="form-field">
            <label for="penerbit">Penerbit</label>
            <input type="text" id="penerbit" name="penerbit" value="<?php echo htmlspecialchars($buku['penerbit'], ENT_QUOTES, 'UTF-8'); ?>">
        </div>
        <div class="form-field">
            <label for="tahun_terbit">Tahun terbit</label>
            <input type="number" id="tahun_terbit" name="tahun_terbit" value="<?php echo htmlspecialchars($buku['tahun_terbit'], ENT_QUOTES, 'UTF-8'); ?>">
        </div>
        <div class="form-field">
            <label for="kategori">Kategori</label>
            <input type="text" id="kategori" name="kategori" value="<?php echo htmlspecialchars($buku['kategori'], ENT_QUOTES, 'UTF-8'); ?>">
        </div>
        <div class="form-field">
            <label for="stok">Jumlah stok</label>
            <input type="number" id="stok" name="stok" value="<?php echo htmlspecialchars($buku['stok'], ENT_QUOTES, 'UTF-8'); ?>" min="0" required>
        </div>
        <div class="form-field form-field--wide">
            <span class="form-field__label">Sampul saat ini</span>
            <?php if ($previewSampulSaatIni !== null) { ?>
                <img
                    class="data-table__cover"
                    src="<?php echo htmlspecialchars($previewSampulSaatIni['src'], ENT_QUOTES, 'UTF-8'); ?>"
                    width="<?php echo $previewSampulSaatIni['width']; ?>"
                    height="<?php echo $previewSampulSaatIni['height']; ?>"
                    alt="Sampul buku saat ini"
                >
            <?php } else { ?>
                <p class="form-hint">Sampul belum tersedia.</p>
            <?php } ?>
        </div>
        <div class="form-field form-field--wide">
            <label for="sampul">Ganti foto sampul</label>
            <input
                type="file"
                id="sampul"
                name="sampul"
                accept="image/jpeg,image/png,image/webp"
                data-sampul-preview="preview-sampul"
                data-sampul-status="status-preview-sampul"
            >
            <p class="form-hint">JPG, PNG, atau WebP. Ukuran maksimal 5 MB.</p>
            <p class="form-hint" id="status-preview-sampul" aria-live="polite">Belum ada file pengganti dipilih.</p>
            <img id="preview-sampul" alt="Pratinjau foto sampul pengganti" hidden width="160" height="240">
        </div>
        <div class="form-field form-field--wide">
            <span class="form-field__label">File e-book saat ini</span>
            <p class="form-hint">
                <?php echo $fileEbookSaatIni !== null
                    ? "PDF tersedia untuk anggota. Unggah file baru untuk menggantinya."
                    : "Belum ada file e-book."; ?>
            </p>
            <label for="ebook">Unggah file buku (PDF)</label>
            <input type="file" id="ebook" name="ebook" accept=".pdf,application/pdf">
            <p class="form-hint">PDF saja. Ukuran maksimal 20 MB. File dapat diunduh oleh anggota yang sudah masuk.</p>
        </div>
    </div>
    <div class="form-actions">
        <button class="button button--primary" type="submit" name="update">Simpan perubahan</button>
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