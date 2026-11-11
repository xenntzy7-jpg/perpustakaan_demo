<?php
session_start();

if (!isset($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'anggota') {
    header("Location: login.php");
    exit;
}

require_once "koneksi.php";
require_once "ebook_helper.php";

function tampilkan_error_baca_buku(int $status, string $judul, string $pesan): void
{
    http_response_code($status);
    $errorTitle = $judul;
    $errorMessage = $pesan;
    $returnHref = "katalog.php";
    $returnLabel = "Kembali ke katalog";
    require "partials/error-page.php";
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id < 1) {
    tampilkan_error_baca_buku(400, "Permintaan tidak valid", "Identitas buku tidak valid.");
}

$statement = mysqli_prepare($conn, "SELECT file_ebook FROM buku WHERE id = ?");
if (!$statement) {
    error_log("Gagal menyiapkan permintaan baca e-book: " . mysqli_error($conn));
    tampilkan_error_baca_buku(500, "E-book belum dapat dibaca", "Terjadi kesalahan saat memeriksa file. Silakan coba lagi.");
}

mysqli_stmt_bind_param($statement, "i", $id);
if (!mysqli_stmt_execute($statement)) {
    error_log("Gagal menjalankan permintaan baca e-book: " . mysqli_stmt_error($statement));
    mysqli_stmt_close($statement);
    tampilkan_error_baca_buku(500, "E-book belum dapat dibaca", "Terjadi kesalahan saat memeriksa file. Silakan coba lagi.");
}

mysqli_stmt_bind_result($statement, $fileEbook);
$bookFound = mysqli_stmt_fetch($statement);
mysqli_stmt_close($statement);

if ($bookFound === null) {
    tampilkan_error_baca_buku(404, "E-book tidak ditemukan", "File buku yang diminta tidak tersedia.");
}

if ($bookFound === false) {
    tampilkan_error_baca_buku(500, "E-book belum dapat dibaca", "Terjadi kesalahan saat membaca data file. Silakan coba lagi.");
}

$fullPath = lokasi_file_ebook($fileEbook);
if ($fullPath === null) {
    tampilkan_error_baca_buku(404, "E-book tidak ditemukan", "File buku yang diminta tidak tersedia.");
}

$fileSize = filesize($fullPath);
if ($fileSize === false || !is_readable($fullPath)) {
    error_log("File e-book tidak dapat dibaca untuk buku ID " . $id);
    tampilkan_error_baca_buku(500, "E-book belum dapat dibaca", "File tidak dapat dibaca. Silakan hubungi pengelola perpustakaan.");
}

header("Content-Type: application/pdf");
header('Content-Disposition: inline; filename="ebook-' . $id . '.pdf"');
header("Content-Length: " . $fileSize);
header("Cache-Control: private, no-store, max-age=0");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");

if (readfile($fullPath) === false) {
    error_log("Gagal menampilkan file e-book untuk buku ID " . $id);
    http_response_code(500);
}
exit;
