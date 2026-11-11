
<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: persetujuan_pengembalian.php");
    exit;
}

$id = (int) $_GET['id'];

mysqli_begin_transaction($conn);

try {
    // Ambil data peminjaman dan kunci barisnya
    $stmt = mysqli_prepare($conn, "
        SELECT buku_id, jumlah, status
        FROM peminjaman
        WHERE id = ?
        FOR UPDATE
    ");

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($hasil);

    if (!$data || $data['status'] !== 'menunggu_pengembalian') {
        throw new Exception("Pengajuan tidak ditemukan atau sudah diproses.");
    }

    // Tambahkan kembali stok buku
    $stmt = mysqli_prepare($conn, "
        UPDATE buku
        SET stok = stok + ?
        WHERE id = ?
    ");

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $data['jumlah'],
        $data['buku_id']
    );

    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) !== 1) {
        throw new Exception("Gagal memperbarui stok buku.");
    }

    // Ubah status menjadi dikembalikan
    $stmt = mysqli_prepare($conn, "
        UPDATE peminjaman
        SET status = 'dikembalikan',
            tanggal_kembali = CURDATE()
        WHERE id = ?
    ");

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    mysqli_commit($conn);

    header("Location: persetujuan_pengembalian.php");
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    http_response_code(400);
    $errorTitle = "Pengembalian gagal diproses";
    $errorMessage = $e->getMessage();
    $returnHref = "persetujuan_pengembalian.php";
    $returnLabel = "Kembali ke persetujuan pengembalian";
    require "partials/error-page.php";
}
