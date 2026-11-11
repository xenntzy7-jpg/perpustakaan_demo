
<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'anggota') {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id'];

$stmt = mysqli_prepare($conn, "
    SELECT 
        peminjaman.id,
        buku.kode_buku,
        buku.judul,
        peminjaman.tanggal_pinjam,
        peminjaman.tanggal_jatuh_tempo,
        peminjaman.tanggal_kembali,
        peminjaman.jumlah,
        peminjaman.status
    FROM peminjaman
    JOIN buku ON peminjaman.buku_id = buku.id
    WHERE peminjaman.user_id = ?
    ORDER BY peminjaman.id DESC
");

mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Peminjaman | Perpustakaan Digital</title>
    <link rel="stylesheet" href="assets/css/perpustakaan.css">
</head>
<body class="legacy-document">
<?php require "partials/site-header.php"; ?>
<main class="legacy-page" id="konten" tabindex="-1">
    <div class="page-intro">
        <p class="eyebrow eyebrow--ink">Ruang anggota</p>
        <h1>Riwayat peminjaman</h1>
        <p class="page-intro__lead">Daftar permintaan, masa pinjam, dan status buku yang tercatat pada akun ini.</p>
    </div>
    <div class="table-wrap">
<table class="data-table">
    <tr>
        <th>No</th>
        <th>Kode Buku</th>
        <th>Judul Buku</th>
        <th>Tanggal Pinjam</th>
        <th>Jatuh Tempo</th>
        <th>Tanggal Kembali</th>
        <th>Jumlah</th>
        <th>Status</th>
    </tr>

    <?php
    $no = 1;

    if (mysqli_num_rows($result) > 0) {
        while ($data = mysqli_fetch_assoc($result)) {
    ?>
    <tr>
        <td><?php echo $no++; ?></td>
        <td><?php echo htmlspecialchars($data['kode_buku']); ?></td>
        <td><?php echo htmlspecialchars($data['judul']); ?></td>
        <td><?php echo $data['tanggal_pinjam']; ?></td>
        <td><?php echo $data['tanggal_jatuh_tempo']; ?></td>
        <td><?php echo $data['tanggal_kembali'] ?? '-'; ?></td>
        <td><?php echo $data['jumlah']; ?></td>
        <td><?php echo htmlspecialchars($data['status']); ?></td>
    </tr>
    <?php
        }
    } else {
        echo "<tr><td colspan='8'>Belum ada riwayat peminjaman.</td></tr>";
    }
    ?>
</table>
</div>
<div class="page-actions">
    <a class="text-link" href="anggota.php">Kembali ke dashboard</a>
</div>
</main>
<footer class="site-footer">
    <div class="page-container site-footer__inner">
        <p>Perpustakaan Digital</p>
        <a href="katalog.php">Katalog buku</a>
    </div>
</footer>
</body>
</html>
