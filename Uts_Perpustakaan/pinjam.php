
<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'anggota') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: katalog.php");
    exit;
}

$id_buku = (int) $_GET['id'];
$id_user = $_SESSION['id'];

$query = mysqli_query($conn, "SELECT * FROM buku WHERE id = $id_buku");
$buku = mysqli_fetch_assoc($query);

if (!$buku) {
    die("Buku tidak ditemukan.");
}

if (isset($_POST['pinjam'])) {
    $tanggal_pinjam = date('Y-m-d');
    $tanggal_jatuh_tempo = date('Y-m-d', strtotime('+7 days'));
    $jumlah = 1;
    $status = 'menunggu';

    $stmt = mysqli_prepare($conn, "INSERT INTO peminjaman (user_id, buku_id, tanggal_pinjam, tanggal_jatuh_tempo, jumlah, status) VALUES (?, ?, ?, ?, ?, ?)");

    mysqli_stmt_bind_param(
        $stmt,
        "iissis",
        $id_user,
        $id_buku,
        $tanggal_pinjam,
        $tanggal_jatuh_tempo,
        $jumlah,
        $status
    );

    if (mysqli_stmt_execute($stmt)) {
        $pesan = "Permintaan peminjaman berhasil dikirim. Menunggu persetujuan admin.";
        $berhasil = true;
    } else {
        $pesan = "Gagal mengajukan peminjaman.";
        $berhasil = false;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Buku | Perpustakaan Digital</title>
    <link rel="stylesheet" href="assets/css/perpustakaan.css">
</head>
<body class="legacy-document">
<?php require "partials/site-header.php"; ?>
<main class="legacy-page" id="konten" tabindex="-1">
    <div class="page-intro">
        <p class="eyebrow eyebrow--ink">Layanan anggota</p>
        <h1>Ajukan peminjaman</h1>
        <p class="page-intro__lead">Periksa informasi buku sebelum mengirim permintaan kepada admin.</p>
    </div>
    <?php if (isset($pesan)) { ?>
        <p class="message <?php echo $berhasil ? 'message--success' : 'message--error'; ?>" role="status">
            <?php echo htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8'); ?>
        </p>
    <?php } ?>
    <section class="form-panel form-panel--narrow" aria-label="Informasi peminjaman">
        <h2><?php echo htmlspecialchars($buku['judul'], ENT_QUOTES, 'UTF-8'); ?></h2>
        <p>Stok tersedia: <strong><?php echo (int) $buku['stok']; ?></strong></p>
        <p>Lama peminjaman: <strong>7 hari</strong></p>
        <?php if (!isset($berhasil) && (int) $buku['stok'] > 0) { ?>
            <form method="POST" class="form-actions">
                <button class="button button--primary" type="submit" name="pinjam">Ajukan peminjaman</button>
            </form>
        <?php } elseif ((int) $buku['stok'] < 1) { ?>
            <p class="message">Stok buku sedang habis.</p>
        <?php } ?>
        <a class="text-link" href="detail_buku.php?id=<?php echo (int) $buku['id']; ?>">Kembali ke detail buku</a>
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
