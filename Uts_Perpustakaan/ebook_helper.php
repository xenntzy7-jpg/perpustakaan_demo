<?php

function simpan_file_ebook(array $file): ?string
{
    $status = $file['error'] ?? UPLOAD_ERR_NO_FILE;

    if ($status === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($status === UPLOAD_ERR_INI_SIZE || $status === UPLOAD_ERR_FORM_SIZE) {
        throw new RuntimeException("Ukuran file e-book melebihi 20 MB.");
    }

    if ($status !== UPLOAD_ERR_OK) {
        throw new RuntimeException("Unggahan file e-book gagal. Silakan coba lagi.");
    }

    $temporaryPath = $file['tmp_name'] ?? "";
    if (!is_uploaded_file($temporaryPath)) {
        throw new RuntimeException("File e-book yang diunggah tidak valid.");
    }

    $fileSize = filesize($temporaryPath);
    if ($fileSize === false || $fileSize === 0) {
        throw new RuntimeException("File e-book kosong atau tidak dapat dibaca.");
    }

    if ($fileSize > 20 * 1024 * 1024) {
        throw new RuntimeException("Ukuran file e-book melebihi 20 MB.");
    }

    $filename = $file['name'] ?? "";
    if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) !== "pdf") {
        throw new RuntimeException("Format file e-book harus PDF.");
    }

    if (!function_exists('finfo_open')) {
        throw new RuntimeException("Validasi tipe file tidak tersedia di server.");
    }

    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($fileInfo === false) {
        throw new RuntimeException("Tipe file e-book tidak dapat divalidasi.");
    }

    try {
        $detectedMime = finfo_file($fileInfo, $temporaryPath);
    } finally {
        finfo_close($fileInfo);
    }

    if ($detectedMime !== "application/pdf") {
        throw new RuntimeException("File yang diunggah bukan PDF yang valid.");
    }

    $header = file_get_contents($temporaryPath, false, null, 0, 1024);
    if (!is_string($header) || strpos($header, "%PDF-") === false) {
        throw new RuntimeException("File yang diunggah bukan PDF yang valid.");
    }

    $uploadDirectory = __DIR__ . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "ebook";
    if (is_link($uploadDirectory)) {
        throw new RuntimeException("Folder penyimpanan e-book tidak valid.");
    }

    if (!is_dir($uploadDirectory)
        && !mkdir($uploadDirectory, 0755, true)
        && !is_dir($uploadDirectory)
    ) {
        throw new RuntimeException("Folder penyimpanan e-book tidak dapat dibuat.");
    }

    $storedFilename = bin2hex(random_bytes(16)) . ".pdf";
    $destination = $uploadDirectory . DIRECTORY_SEPARATOR . $storedFilename;
    if (!move_uploaded_file($temporaryPath, $destination)) {
        throw new RuntimeException("File e-book gagal disimpan.");
    }

    return "uploads/ebook/" . $storedFilename;
}

function lokasi_file_ebook(?string $relativePath): ?string
{
    if ($relativePath === null
        || !preg_match('~\Auploads/ebook/[a-f0-9]{32}\.pdf\z~D', $relativePath)
    ) {
        return null;
    }

    $directory = __DIR__ . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "ebook";
    if (is_link($directory) || !is_dir($directory)) {
        return null;
    }

    $realDirectory = realpath($directory);
    if ($realDirectory === false) {
        return null;
    }

    $fullPath = $realDirectory . DIRECTORY_SEPARATOR . basename($relativePath);
    if (is_link($fullPath) || !is_file($fullPath)) {
        return null;
    }

    $realPath = realpath($fullPath);
    if ($realPath === false || dirname($realPath) !== $realDirectory) {
        return null;
    }

    return $realPath;
}

function hapus_file_ebook(?string $relativePath): void
{
    $fullPath = lokasi_file_ebook($relativePath);
    if ($fullPath === null) {
        return;
    }

    if (!unlink($fullPath)) {
        throw new RuntimeException("File e-book lama tidak dapat dihapus.");
    }
}

function hapus_file_ebook_jika_tidak_dipakai(mysqli $conn, ?string $relativePath): void
{
    if ($relativePath === null || $relativePath === "") {
        return;
    }

    $stmt = mysqli_prepare($conn, "SELECT id FROM buku WHERE file_ebook = ? LIMIT 1");
    if (!$stmt) {
        throw new RuntimeException("Gagal memeriksa penggunaan e-book: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "s", $relativePath);
    if (!mysqli_stmt_execute($stmt) || !mysqli_stmt_store_result($stmt)) {
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        throw new RuntimeException("Gagal memeriksa penggunaan e-book: " . $error);
    }

    $isStillUsed = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);

    if (!$isStillUsed) {
        hapus_file_ebook($relativePath);
    }
}
