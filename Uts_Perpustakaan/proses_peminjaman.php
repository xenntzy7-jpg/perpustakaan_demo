<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id']) || !isset($_GET['aksi'])) {
    header("Location: persetujuan_peminjaman.php");
    exit;
}

$id = (int) $_GET['id'];
$aksi = $_GET['aksi'];

mysqli_begin_transaction($conn);

try {
    $stmt = mysqli_prepare($conn, "
        SELECT buku_id, jumlah, status
        FROM peminjaman
        WHERE id = ?
        FOR UPDATE
    ");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $hasil = mysqli_stmt_get_result($stmt);
    $pinjam = mysqli_fetch_assoc($hasil);

    if (!$pinjam || $pinjam['status'] !== 'menunggu') {
        throw new Exception("Permintaan tidak ditemukan atau sudah diproses.");
    }

    if ($aksi === 'setujui') {
        $stmt = mysqli_prepare($conn, "
            UPDATE buku
            SET stok = stok - ?
            WHERE id = ? AND stok >= ?
        ");
        mysqli_stmt_bind_param(
            $stmt,
            "iii",
            $pinjam['jumlah'],
            $pinjam['buku_id'],
            $pinjam['jumlah']
        );
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) !== 1) {
            throw new Exception("Stok buku tidak mencukupi.");
        }

        $status = 'dipinjam';
    } elseif ($aksi === 'tolak') {
        $status = 'ditolak';
    } else {
        throw new Exception("Aksi tidak valid.");
    }

    $stmt = mysqli_prepare($conn, "
        UPDATE peminjaman SET status = ? WHERE id = ?
    ");
    mysqli_stmt_bind_param($stmt, "si", $status, $id);
    mysqli_stmt_execute($stmt);

    mysqli_commit($conn);
    header("Location: persetujuan_peminjaman.php");
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    http_response_code(400);
    $errorTitle = "Peminjaman gagal diproses";
    $errorMessage = $e->getMessage();
    $returnHref = "persetujuan_peminjaman.php";
    $returnLabel = "Kembali ke persetujuan peminjaman";
    require "partials/error-page.php";
}
