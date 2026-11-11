-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 11 Nov 2026 pada 17.00
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `perpustakaan_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `buku`
--

CREATE TABLE `buku` (
  `id` int(11) NOT NULL,
  `kode_buku` varchar(50) NOT NULL,
  `judul` varchar(50) NOT NULL,
  `penulis` varchar(50) NOT NULL,
  `penerbit` varchar(50) NOT NULL,
  `tahun_terbit` year(4) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `stok` int(60) NOT NULL DEFAULT 0,
  `sampul` varchar(255) DEFAULT NULL,
  `file_ebook` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `buku`
--

INSERT INTO `buku` (`id`, `kode_buku`, `judul`, `penulis`, `penerbit`, `tahun_terbit`, `kategori`, `stok`, `sampul`, `file_ebook`, `created_at`) VALUES
(5, '023', 'Madilog', 'Tan Malaka', 'Tan Malaka', '1951', 'Filsafat', 5, 'uploads/sampul/e725a0976599459a186c164574ea1072.jpg', 'uploads/ebook/98b6652e87dd925d0d61b17863403e4d.pdf', '2026-11-11 15:06:32'),
(6, '024', 'Aksi Masa', 'Tan Malaka', 'Tan Malaka', '1926', 'Filsafat', 6, 'uploads/sampul/6a2ece89d36de141322fedba1e0ca853.jpg', 'uploads/ebook/c3c99d56dcef643c2d0bb9cac36f2716.pdf', '2026-11-11 15:43:39'),
(8, '025', 'Gerpolek', 'Tan Malaka', 'Tan Malaka', '1946', 'Filsafat', 5, 'uploads/sampul/f6a9de490a2cc1f4df7f11dc69fec97a.jpg', 'uploads/ebook/b01f0861b285dd69c1216171548445b4.pdf', '2026-11-11 15:46:16');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_buku` (`kode_buku`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `buku`
--
ALTER TABLE `buku`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
