<?php

function simpan_sampul_buku(array $file): ?string
{
    $status = $file['error'] ?? UPLOAD_ERR_NO_FILE;

    if ($status === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($status === UPLOAD_ERR_INI_SIZE || $status === UPLOAD_ERR_FORM_SIZE) {
        throw new RuntimeException("Ukuran file sampul melebihi batas 5 MB.");
    }

    if ($status !== UPLOAD_ERR_OK) {
        throw new RuntimeException("Upload foto sampul gagal. Silakan coba lagi.");
    }

    $temporaryPath = $file['tmp_name'] ?? "";
    if (!is_uploaded_file($temporaryPath)) {
        throw new RuntimeException("File sampul yang diunggah tidak valid.");
    }

    $fileSize = filesize($temporaryPath);
    if ($fileSize === false || $fileSize === 0) {
        throw new RuntimeException("File sampul kosong atau tidak dapat dibaca.");
    }

    if ($fileSize > 5 * 1024 * 1024) {
        throw new RuntimeException("Ukuran file sampul melebihi batas 5 MB.");
    }

    if (!function_exists('finfo_open')) {
        throw new RuntimeException("Validasi tipe file tidak tersedia di server.");
    }

    $image = @getimagesize($temporaryPath);
    if ($image === false || $image[0] < 1 || $image[1] < 1) {
        throw new RuntimeException("File yang dipilih bukan gambar yang valid.");
    }

    $extensions = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp",
    ];
    $imageMime = $image['mime'] ?? "";
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($fileInfo === false) {
        throw new RuntimeException("Tipe file sampul tidak dapat divalidasi.");
    }

    $detectedMime = finfo_file($fileInfo, $temporaryPath);
    finfo_close($fileInfo);

    if (!isset($extensions[$imageMime]) || $detectedMime !== $imageMime) {
        throw new RuntimeException("Format sampul harus JPG, PNG, atau WebP.");
    }

    $uploadDirectory = __DIR__ . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "sampul";
    if (is_link($uploadDirectory)) {
        throw new RuntimeException("Folder penyimpanan sampul tidak valid.");
    }

    if (!is_dir($uploadDirectory)
        && !mkdir($uploadDirectory, 0755, true)
        && !is_dir($uploadDirectory)
    ) {
        throw new RuntimeException("Folder penyimpanan sampul tidak dapat dibuat.");
    }

    $filename = bin2hex(random_bytes(16)) . "." . $extensions[$imageMime];
    $destination = $uploadDirectory . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($temporaryPath, $destination)) {
        throw new RuntimeException("File sampul gagal disimpan.");
    }

    return "uploads/sampul/" . $filename;
}

function lokasi_sampul_buku(?string $relativePath): ?string
{
    if ($relativePath === null
        || !preg_match('~\Auploads/sampul/[a-f0-9]{32}\.(?:jpg|png|webp)\z~D', $relativePath)
    ) {
        return null;
    }

    $directory = __DIR__ . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "sampul";
    if (is_link($directory) || !is_dir($directory)) {
        return null;
    }

    $realDirectory = realpath($directory);
    if ($realDirectory === false) {
        return null;
    }

    $filename = basename($relativePath);
    $fullPath = $realDirectory . DIRECTORY_SEPARATOR . $filename;
    if (is_link($fullPath) || !is_file($fullPath)) {
        return null;
    }

    $realPath = realpath($fullPath);
    if ($realPath === false || dirname($realPath) !== $realDirectory) {
        return null;
    }

    return $realPath;
}

function ukuran_tampilan_sampul_buku(?string $relativePath, int $maxWidth, int $maxHeight): ?array
{
    $fullPath = lokasi_sampul_buku($relativePath);
    if ($fullPath === null) {
        return null;
    }

    $image = @getimagesize($fullPath);
    if ($image === false || $image[0] < 1 || $image[1] < 1) {
        return null;
    }

    $scale = min(1, $maxWidth / $image[0], $maxHeight / $image[1]);

    return [
        "src" => $relativePath,
        "width" => max(1, (int) round($image[0] * $scale)),
        "height" => max(1, (int) round($image[1] * $scale)),
    ];
}

function hapus_sampul_buku(?string $relativePath): void
{
    $fullPath = lokasi_sampul_buku($relativePath);
    if ($fullPath === null) {
        return;
    }

    if (!unlink($fullPath)) {
        throw new RuntimeException("File sampul lama tidak dapat dihapus.");
    }
}

function hapus_sampul_buku_jika_tidak_dipakai(mysqli $conn, ?string $relativePath): void
{
    if ($relativePath === null || $relativePath === "") {
        return;
    }

    $stmt = mysqli_prepare($conn, "SELECT id FROM buku WHERE sampul = ? LIMIT 1");
    if (!$stmt) {
        throw new RuntimeException("Gagal memeriksa penggunaan sampul: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "s", $relativePath);
    if (!mysqli_stmt_execute($stmt) || !mysqli_stmt_store_result($stmt)) {
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        throw new RuntimeException("Gagal memeriksa penggunaan sampul: " . $error);
    }

    $isStillUsed = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);

    if (!$isStillUsed) {
        hapus_sampul_buku($relativePath);
    }
}
