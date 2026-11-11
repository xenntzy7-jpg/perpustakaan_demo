<?php
$errorTitle = $errorTitle ?? "Permintaan tidak dapat diproses";
$errorMessage = $errorMessage ?? "Terjadi kesalahan. Silakan coba lagi.";
$returnHref = $returnHref ?? "index.php";
$returnLabel = $returnLabel ?? "Kembali ke beranda";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($errorTitle, ENT_QUOTES, 'UTF-8'); ?> | Perpustakaan Digital</title>
    <link rel="stylesheet" href="assets/css/perpustakaan.css">
</head>
<body class="legacy-document">
<?php require __DIR__ . DIRECTORY_SEPARATOR . "site-header.php"; ?>
<main class="legacy-page" id="konten" tabindex="-1">
    <section class="empty-state empty-state--page" aria-labelledby="error-title">
        <p class="eyebrow eyebrow--ink">Perpustakaan Digital</p>
        <h1 id="error-title"><?php echo htmlspecialchars($errorTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
        <p><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
        <a class="button button--primary" href="<?php echo htmlspecialchars($returnHref, ENT_QUOTES, 'UTF-8'); ?>">
            <?php echo htmlspecialchars($returnLabel, ENT_QUOTES, 'UTF-8'); ?>
        </a>
    </section>
</main>
<footer class="site-footer">
    <div class="page-container site-footer__inner">
        <p>Perpustakaan Digital</p>
        <a href="logout.php">Keluar dari akun</a>
    </div>
</footer>
</body>
</html>
