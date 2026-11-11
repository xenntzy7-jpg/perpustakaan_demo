
<?php
session_start();
require_once "koneksi.php";
require_once "sampul_helper.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$query = mysqli_query($conn, "SELECT * FROM buku ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Buku | Perpustakaan Digital</title>
    <link rel="stylesheet" href="assets/css/perpustakaan.css">
</head>
<body class="legacy-document">
<?php require "partials/site-header.php"; ?>
<main class="legacy-page" id="konten" tabindex="-1">
    <div class="page-intro">
        <p class="eyebrow eyebrow--ink">Koleksi perpustakaan</p>
        <h1>Kelola data buku</h1>
        <p class="page-intro__lead">Periksa metadata, sampul, dan stok. Gunakan aksi pada baris untuk mengubah data buku.</p>
    </div>
    <div class="page-actions">
        <a class="button button--primary" href="tambah_buku.php">Tambah buku</a>
        <a class="text-link" href="admin.php">Kembali ke dashboard</a>
    </div>
    <div class="table-wrap">
<table class="data-table">
    <tr>
        <th>No</th>
        <th>Sampul</th>
        <th>Kode Buku</th>
        <th>Judul</th>
        <th>Penulis</th>
        <th>Penerbit</th>
        <th>Tahun</th>
        <th>Kategori</th>
        <th>Stok</th>
        <th>Aksi</th>
    </tr>

    <?php if (mysqli_num_rows($query) > 0) { ?>
        <?php
        $no = 1;
        while ($buku = mysqli_fetch_assoc($query)) {
            $cover = ukuran_tampilan_sampul_buku($buku['sampul'] ?? null, 52, 72);
        ?>
        <tr>
            <td><?php echo $no++; ?></td>
            <td>
                <?php if ($cover !== null) { ?>
                    <img
                        class="data-table__cover"
                        src="<?php echo htmlspecialchars($cover['src'], ENT_QUOTES, 'UTF-8'); ?>"
                        width="<?php echo $cover['width']; ?>"
                        height="<?php echo $cover['height']; ?>"
                        alt="Sampul <?php echo htmlspecialchars($buku['judul'], ENT_QUOTES, 'UTF-8'); ?>"
                    >
                <?php } else { ?>
                    <span class="data-table__placeholder">Sampul tidak tersedia</span>
                <?php } ?>
            </td>
            <td><?php echo htmlspecialchars($buku['kode_buku']); ?></td>
            <td><?php echo htmlspecialchars($buku['judul']); ?></td>
            <td><?php echo htmlspecialchars($buku['penulis']); ?></td>
            <td><?php echo htmlspecialchars($buku['penerbit']); ?></td>
            <td><?php echo htmlspecialchars($buku['tahun_terbit']); ?></td>
            <td><?php echo htmlspecialchars($buku['kategori']); ?></td>
            <td><?php echo htmlspecialchars($buku['stok']); ?></td>
            <td>
                <a href="edit_buku.php?id=<?php echo (int) $buku['id']; ?>">Edit</a>
                <a href="hapus_buku.php?id=<?php echo (int) $buku['id']; ?>"
                   onclick="return confirm('Yakin ingin menghapus buku ini?')">Hapus</a>
            </td>
        </tr>
        <?php } ?>
    <?php } else { ?>
        <tr><td colspan="10">Belum ada buku. Tambahkan buku untuk memulai koleksi.</td></tr>
    <?php } ?>

</table>
</div>
</main>
<footer class="site-footer">
    <div class="page-container site-footer__inner">
        <p>Perpustakaan Digital</p>
        <a href="logout.php">Keluar dari akun</a>
    </div>
</footer>
</body>
</html>
