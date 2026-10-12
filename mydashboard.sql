-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 12, 2026 at 05:28 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mydashboard`
--

-- --------------------------------------------------------

--
-- Table structure for table `keuangan`
--

CREATE TABLE `keuangan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `jenis` enum('Pemasukan','Pengeluaran') NOT NULL,
  `nominal` bigint(20) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `tanggal` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `keuangan`
--

INSERT INTO `keuangan` (`id`, `user_id`, `jenis`, `nominal`, `kategori`, `keterangan`, `tanggal`, `created_at`) VALUES
(8, 19, 'Pemasukan', 9000, 'kiriman', 'kiriman kucing', '2026-10-11', '2026-10-11 13:11:07'),
(9, 19, 'Pemasukan', 10000, 'Jualan', 'Jual Barang Bekas', '2026-10-11', '2026-10-11 14:54:25');

-- --------------------------------------------------------

--
-- Table structure for table `lecturers`
--

CREATE TABLE `lecturers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_dosen` varchar(100) NOT NULL,
  `matkul` varchar(100) NOT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lecturers`
--

INSERT INTO `lecturers` (`id`, `user_id`, `nama_dosen`, `matkul`, `no_telp`, `created_at`) VALUES
(3, 19, 'Dr. Ir. Oddy Virgantara Putra, S.Kom., M.T.', 'Metode Numerik', '+62 856-3667-286', '2026-10-09 03:36:24'),
(4, 19, 'Moch. Nasheh Annafii, S.Kom., M.Kom.', 'Algoritma struktur data', '+62 852-1937-0971', '2026-10-09 03:37:30'),
(5, 19, 'Eko Prasetio Widhi, M.Kom.', 'Organisasi komputer', '+62 812-5221-5252', '2026-10-09 03:38:46');

-- --------------------------------------------------------

--
-- Table structure for table `profiles`
--

CREATE TABLE `profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `universitas` varchar(100) DEFAULT NULL,
  `jurusan` varchar(100) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_matkul` varchar(100) NOT NULL,
  `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Jum''at','Sabtu','Minggu') NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `ruangan` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `user_id`, `nama_matkul`, `hari`, `jam_mulai`, `jam_selesai`, `ruangan`) VALUES
(11, 19, 'Kalkulus 2', 'Sabtu', '07:25:00', '09:10:00', '316'),
(12, 19, 'Bahasa Arab ', 'Minggu', '07:45:00', '09:15:00', '316'),
(13, 19, 'Matematika Diskrit', 'Minggu', '09:25:00', '11:00:00', '316'),
(14, 19, 'Sistem Digital', 'Senin', '07:30:00', '09:15:00', '316'),
(15, 19, ' Algoritma dan Struktur data', 'Senin', '09:30:00', '11:00:00', '316'),
(16, 19, 'English for Writing and Speaking', 'Selasa', '07:30:00', '09:10:00', '428'),
(17, 19, ' Pendidikan Kewarganegaraan', 'Selasa', '09:30:00', '11:00:00', '316'),
(18, 19, ' Metode Numerik', 'Rabu', '07:20:00', '09:15:00', '428'),
(19, 19, 'Worldview Islam Syariah', 'Rabu', '09:20:00', '11:00:00', '428'),
(20, 19, 'Organisasi Komputer', 'Kamis', '13:20:00', '14:30:00', 'Lab Ti lantai 1'),
(21, 19, 'LIBUR', 'Jumat', '00:00:00', '00:00:00', '-'),
(22, 19, 'Libur', 'Jumat', '00:00:00', '00:00:00', 'Di kamar Masing Masing');

-- --------------------------------------------------------

--
-- Table structure for table `todolist`
--

CREATE TABLE `todolist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tugas` varchar(255) NOT NULL,
  `status` enum('Belum','Selesai') DEFAULT 'Belum',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `tanggal` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `todolist`
--

INSERT INTO `todolist` (`id`, `user_id`, `tugas`, `status`, `created_at`, `tanggal`) VALUES
(7, 20, 'olahraga ', 'Belum', '2026-10-08 02:41:32', '2026-10-11'),
(8, 20, 'ngerjain tugas / kalo ada ', 'Belum', '2026-10-08 02:42:53', '2026-10-11'),
(9, 20, 'main game ', 'Belum', '2026-10-08 02:57:41', '2026-10-11'),
(10, 20, 'main game', 'Belum', '2026-10-08 02:57:46', '2026-10-11'),
(11, 20, 'main game ', 'Belum', '2026-10-08 02:57:52', '2026-10-11'),
(12, 20, 'main game', 'Belum', '2026-10-08 02:57:57', '2026-10-11'),
(13, 20, 'main game', 'Belum', '2026-10-08 02:58:02', '2026-10-11'),
(14, 20, 'main game', 'Belum', '2026-10-08 02:58:10', '2026-10-11'),
(15, 20, 'main game', 'Belum', '2026-10-08 02:58:28', '2026-10-11'),
(16, 20, 'main game', 'Belum', '2026-10-08 02:58:38', '2026-10-11'),
(18, 22, 'makan ', 'Selesai', '2026-10-08 11:04:10', '2026-10-11'),
(19, 22, 'pergi ke battam ', 'Belum', '2026-10-08 12:03:51', '2026-10-11');

-- --------------------------------------------------------

--
-- Table structure for table `tugas_kuliah`
--

CREATE TABLE `tugas_kuliah` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `mata_kuliah` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `deadline` date NOT NULL,
  `status` enum('Belum Selesai','Selesai') DEFAULT 'Belum Selesai'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tugas_kuliah`
--

INSERT INTO `tugas_kuliah` (`id`, `user_id`, `mata_kuliah`, `deskripsi`, `deadline`, `status`) VALUES
(6, 19, 'Organisasi Komputer', 'Tugas Setiap Pertemuan', '2026-11-07', 'Belum Selesai'),
(7, 19, 'Algoritma Struktur Data', 'Tugas Di nuun', '2026-10-15', 'Belum Selesai');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `nim` varchar(20) DEFAULT NULL,
  `jurusan` varchar(100) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL,
  `target_ipk` varchar(10) DEFAULT NULL,
  `status_mhs` varchar(20) DEFAULT 'Aktif',
  `no_hp` varchar(20) DEFAULT NULL,
  `instagram` varchar(100) DEFAULT NULL,
  `github` varchar(200) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `created_at`, `nim`, `jurusan`, `semester`, `target_ipk`, `status_mhs`, `no_hp`, `instagram`, `github`, `bio`, `foto`) VALUES
(18, 'nohen', 'nohen@gmail.com', '$2y$10$8xDN1xet2ABxH/uKYHtdX.M1dsGXQwGjd967pspy9nrXnHvOXDM6q', '2026-10-06 21:52:14', '', '', 0, '', 'Aktif', '', '', '', '', NULL),
(19, 'Naufal Mubaroq', 'naufalmubaroq29@gmail.com', '$2y$10$u1OD9rKBIOjK3k2eRjPHIOlg7qX4DjtKSuEnlbOxtkv8CUYGpqUG6', '2026-10-07 11:24:45', '4720261611026', 'Tehnik Informatika', 2, '3.9', 'Aktif', '08132740842', 'n_baroq', 'Universitas Darusallam Gontor', 'This side i made it with Ai and my fundamental knowladge\r\n', 'user_19_1791373271.png'),
(20, 'Steve', 'steve@gmail.com', '$2y$10$yqmlVjROcU/6cVsequWL2u7EAu769YJn9lDJx7sPiPb5V/p2O62wO', '2026-10-08 02:38:47', '4720261611068', 'Tehnik Informasi', 2, '3.7', 'Aktif', '08135678889', 'S_t@ve', 'guanih', 'i newbie in programming ', 'user_20_1791427275.jpg'),
(21, 'GHOFRAN', 'gof@gmail.com', '$2y$10$7z/e0ALXpNpimdSLR/MTq.slD2YvLuzD2J0dyX5djXFt5DKNlxHJC', '2026-10-08 10:11:51', NULL, NULL, NULL, NULL, 'Aktif', NULL, NULL, NULL, NULL, NULL),
(22, 'Hfizh', 'Hfizh@gmail.com', '$2y$10$1J1q6/KM7EmxN2EGH2/64uPTxOZ3Fk2Hck9VUHBa6CqgbmkojyPH.', '2026-10-08 10:58:35', '4720261611028', 'Tehnik Informatika', 4, '3.9', 'Aktif', '08132740842', 'Hafizd alfaa bin Khamid', '', '', 'user_22_1791457227.jpg'),
(23, 'koko', 'k@gmail.com', '$2y$10$ZSBQjpwgiFlrUbVTP860bea0/jiazKJHUeThID90TmsslVHH/eixi', '2026-10-09 13:27:28', NULL, NULL, NULL, NULL, 'Aktif', NULL, NULL, NULL, NULL, NULL),
(24, 'alif', 'kkk@gmail.com', '$2y$10$2.BqIPGUi3rpIu9s5HFj2eGsK7k/dzFrQLApIDJDeeohAoalp.TDW', '2026-10-11 10:30:55', NULL, NULL, NULL, NULL, 'Aktif', NULL, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `keuangan`
--
ALTER TABLE `keuangan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lecturers`
--
ALTER TABLE `lecturers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `profiles`
--
ALTER TABLE `profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `todolist`
--
ALTER TABLE `todolist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tugas_kuliah`
--
ALTER TABLE `tugas_kuliah`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `keuangan`
--
ALTER TABLE `keuangan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `lecturers`
--
ALTER TABLE `lecturers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `profiles`
--
ALTER TABLE `profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `todolist`
--
ALTER TABLE `todolist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `tugas_kuliah`
--
ALTER TABLE `tugas_kuliah`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `lecturers`
--
ALTER TABLE `lecturers`
  ADD CONSTRAINT `lecturers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `profiles`
--
ALTER TABLE `profiles`
  ADD CONSTRAINT `profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `todolist`
--
ALTER TABLE `todolist`
  ADD CONSTRAINT `todolist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
