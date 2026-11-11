<?php
$currentRole = $_SESSION['role'] ?? '';
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$navigationLinks = [
    ['label' => 'Beranda', 'href' => 'index.php'],
];

if ($currentRole === 'admin') {
    $navigationLinks[] = ['label' => 'Dashboard', 'href' => 'admin.php'];
    $navigationLinks[] = ['label' => 'Kelola buku', 'href' => 'buku.php'];
    $navigationLinks[] = ['label' => 'Peminjaman', 'href' => 'persetujuan_peminjaman.php'];
    $navigationLinks[] = ['label' => 'Pengembalian', 'href' => 'persetujuan_pengembalian.php'];
    $navigationLinks[] = ['label' => 'Keluar', 'href' => 'logout.php'];
} elseif ($currentRole === 'anggota') {
    $navigationLinks[] = ['label' => 'Katalog', 'href' => 'katalog.php'];
    $navigationLinks[] = ['label' => 'Dashboard', 'href' => 'anggota.php'];
    $navigationLinks[] = ['label' => 'Riwayat', 'href' => 'riwayat_peminjaman.php'];
    $navigationLinks[] = ['label' => 'Pengembalian', 'href' => 'pengembalian.php'];
    $navigationLinks[] = ['label' => 'Keluar', 'href' => 'logout.php'];
} else {
    $navigationLinks[] = ['label' => 'Masuk', 'href' => 'login.php'];
    $navigationLinks[] = ['label' => 'Daftar anggota', 'href' => 'register.php'];
}
?>
<div class="navigation-progress" aria-hidden="true"></div>
<p class="navigation-progress__status" role="status" aria-live="polite" aria-atomic="true"></p>
<script src="assets/js/page-transitions.js?v=2" defer></script>
<a class="skip-link" href="#konten">Lewati ke konten</a>
<header class="site-header">
    <div class="site-header__inner">
        <a class="wordmark" href="index.php" aria-label="Perpustakaan Digital, beranda">
            <span class="wordmark__title">Perpustakaan</span>
            <span class="wordmark__detail">Digital</span>
        </a>
        <nav class="site-nav" aria-label="Navigasi utama">
            <?php foreach ($navigationLinks as $link) { ?>
                <a
                    href="<?php echo htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8'); ?>"
                    <?php echo $currentPage === $link['href'] ? 'aria-current="page"' : ''; ?>
                >
                    <?php echo htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
            <?php } ?>
        </nav>
    </div>
</header>
