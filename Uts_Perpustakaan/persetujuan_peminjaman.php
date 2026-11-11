
<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$query = mysqli_query($conn, "
    SELECT 
        peminjaman.id,
        users.nama,
        buku.judul,
        peminjaman.tanggal_pinjam,
        peminjaman.tanggal_jatuh_tempo,
        peminjaman.jumlah,
        peminjaman.status
    FROM peminjaman
    JOIN users ON peminjaman.user_id = users.id
    JOIN buku ON peminjaman.buku_id = buku.id
    WHERE peminjaman.status = 'menunggu'
    ORDER BY peminjaman.id DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Persetujuan Peminjaman | Perpustakaan Digital</title>
    <link rel="stylesheet" href="assets/css/perpustakaan.css">
</head>
<body class="legacy-document">
<?php require "partials/site-header.php"; ?>
<main class="legacy-page" id="konten" tabindex="-1">
    <div class="page-intro">
        <p class="eyebrow eyebrow--ink">Ruang pengelola</p>
        <h1>Persetujuan peminjaman</h1>
        <p class="page-intro__lead">Tinjau permintaan anggota dan stok sebelum menyetujui peminjaman.</p>
    </div>
    <div class="table-wrap">
<table class="data-table">
    <tr>
        <th>No</th>
        <th>Nama Anggota</th>
        <th>Judul Buku</th>
        <th>Tanggal Pinjam</th>
        <th>Jatuh Tempo</th>
        <th>Jumlah</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>

    <?php if (mysqli_num_rows($query) > 0) { ?>
        <?php
        $no = 1;
        while ($data = mysqli_fetch_assoc($query)) {
        ?>
        <tr>
            <td><?php echo $no++; ?></td>
            <td><?php echo htmlspecialchars($data['nama']); ?></td>
            <td><?php echo htmlspecialchars($data['judul']); ?></td>
            <td><?php echo $data['tanggal_pinjam']; ?></td>
            <td><?php echo $data['tanggal_jatuh_tempo']; ?></td>
            <td><?php echo $data['jumlah']; ?></td>
            <td><span class="status-copy"><?php echo htmlspecialchars($data['status'], ENT_QUOTES, 'UTF-8'); ?></span></td>
            <td>
                <a href="proses_peminjaman.php?id=<?php echo (int) $data['id']; ?>&aksi=setujui">Setujui</a>
                <a href="proses_peminjaman.php?id=<?php echo (int) $data['id']; ?>&aksi=tolak"
                   onclick="return confirm('Yakin ingin menolak peminjaman ini?')">Tolak</a>
            </td>
        </tr>
        <?php } ?>
    <?php } else { ?>
        <tr><td colspan="8">Belum ada permintaan peminjaman yang menunggu persetujuan.</td></tr>
    <?php } ?>
</table>
</div>
<div class="page-actions">
    <a class="text-link" href="admin.php">Kembali ke dashboard admin</a>
</div>
</main>
<footer class="site-footer">
    <div class="page-container site-footer__inner">
        <p>Perpustakaan Digital</p>
        <a href="persetujuan_pengembalian.php">Persetujuan pengembalian</a>
    </div>
</footer>
</body>
</html>
