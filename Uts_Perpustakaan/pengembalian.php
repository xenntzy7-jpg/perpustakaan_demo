<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'anggota') {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id'];
$pesanPengembalian = "";
$pengembalianBerhasil = false;

if (isset($_POST['kembalikan'])) {
    $id_peminjaman = (int) $_POST['id_peminjaman'];

    $stmt = mysqli_prepare($conn, "
        SELECT id FROM peminjaman
        WHERE id = ? AND user_id = ? AND status = 'dipinjam'
    ");

    mysqli_stmt_bind_param($stmt, "ii", $id_peminjaman, $id_user);
    mysqli_stmt_execute($stmt);
    $hasil = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($hasil) === 1) {
        $stmt = mysqli_prepare($conn, "
            UPDATE peminjaman
            SET status = 'menunggu_pengembalian'
            WHERE id = ? AND user_id = ? AND status = 'dipinjam'
        ");

        mysqli_stmt_bind_param($stmt, "ii", $id_peminjaman, $id_user);
        if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) === 1) {
            $pesanPengembalian = "Pengajuan pengembalian berhasil dikirim.";
            $pengembalianBerhasil = true;
        } else {
            $pesanPengembalian = "Pengajuan pengembalian gagal dikirim. Silakan coba lagi.";
        }
    } else {
        $pesanPengembalian = "Data peminjaman tidak ditemukan atau sudah diajukan.";
    }
}

$stmt = mysqli_prepare($conn, "
    SELECT peminjaman.id, buku.judul, peminjaman.tanggal_pinjam,
           peminjaman.tanggal_jatuh_tempo
    FROM peminjaman
    JOIN buku ON peminjaman.buku_id = buku.id
    WHERE peminjaman.user_id = ?
    AND peminjaman.status = 'dipinjam'
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
    <title>Pengembalian Buku | Perpustakaan Digital</title>
    <link rel="stylesheet" href="assets/css/perpustakaan.css">
</head>
<body class="legacy-document">
<?php require "partials/site-header.php"; ?>
<main class="legacy-page" id="konten" tabindex="-1">
    <div class="page-intro">
        <p class="eyebrow eyebrow--ink">Ruang anggota</p>
        <h1>Pengajuan pengembalian</h1>
        <p class="page-intro__lead">Ajukan pengembalian untuk buku yang sedang dipinjam.</p>
    </div>
    <?php if ($pesanPengembalian !== "") { ?>
        <p class="message <?php echo $pengembalianBerhasil ? 'message--success' : 'message--error'; ?>" role="status">
            <?php echo htmlspecialchars($pesanPengembalian, ENT_QUOTES, 'UTF-8'); ?>
        </p>
    <?php } ?>
    <div class="table-wrap">
<table class="data-table">
    <tr>
        <th>No</th>
        <th>Judul Buku</th>
        <th>Tanggal Pinjam</th>
        <th>Jatuh Tempo</th>
        <th>Aksi</th>
    </tr>

    <?php
    $no = 1;

    if (mysqli_num_rows($result) > 0) {
        while ($data = mysqli_fetch_assoc($result)) {
    ?>
    <tr>
        <td><?php echo $no++; ?></td>
        <td><?php echo htmlspecialchars($data['judul']); ?></td>
        <td><?php echo $data['tanggal_pinjam']; ?></td>
        <td><?php echo $data['tanggal_jatuh_tempo']; ?></td>
        <td>
            <form method="POST" onsubmit="return confirm('Ajukan pengembalian buku ini?')">
                <input type="hidden" name="id_peminjaman"
                       value="<?php echo $data['id']; ?>">
                <button class="button button--primary" type="submit" name="kembalikan">
                    Ajukan Pengembalian
                </button>
            </form>
        </td>
    </tr>
    <?php
        }
    } else {
        echo "<tr><td colspan='5'>Tidak ada buku yang sedang dipinjam.</td></tr>";
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
        <a href="riwayat_peminjaman.php">Riwayat peminjaman</a>
    </div>
</footer>
</body>
</html>
