<?php
session_start();
require_once "koneksi.php";
require_once "sampul_helper.php";
require_once "ebook_helper.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $sampul = null;
    $fileEbook = null;

    $stmtSampul = mysqli_prepare($conn, "SELECT sampul, file_ebook FROM buku WHERE id = ?");
    if (!$stmtSampul) {
        die("SQL Error: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmtSampul, "i", $id);
    if (!mysqli_stmt_execute($stmtSampul)) {
        die("Database Error: " . mysqli_stmt_error($stmtSampul));
    }

    mysqli_stmt_bind_result($stmtSampul, $sampul, $fileEbook);
    mysqli_stmt_fetch($stmtSampul);
    mysqli_stmt_close($stmtSampul);

    $stmt = mysqli_prepare($conn, "DELETE FROM buku WHERE id = ?");
    if (!$stmt) {
        die("SQL Error: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);

        try {
            hapus_sampul_buku_jika_tidak_dipakai($conn, $sampul);
            hapus_file_ebook_jika_tidak_dipakai($conn, $fileEbook);
        } catch (RuntimeException $error) {
            http_response_code(500);
            $errorTitle = "Buku terhapus, file belum dibersihkan";
            $errorMessage = "Data buku berhasil dihapus, tetapi file sampul atau e-book gagal dibersihkan. "
                . $error->getMessage();
            $returnHref = "buku.php";
            $returnLabel = "Kembali ke daftar buku";
            require "partials/error-page.php";
            exit;
        }

        header("Location: buku.php");
        exit;
    } else {
        mysqli_stmt_close($stmt);
        http_response_code(409);
        $errorTitle = "Buku tidak dapat dihapus";
        $errorMessage = "Buku masih memiliki data peminjaman, sehingga penghapusan dibatalkan.";
        $returnHref = "buku.php";
        $returnLabel = "Kembali ke daftar buku";
        require "partials/error-page.php";
        exit;
    }
} else {
    header("Location: buku.php");
    exit;
}
?>
