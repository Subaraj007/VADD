-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 30, 2025 at 06:19 PM
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
-- Database: `devicedb`
--

-- --------------------------------------------------------

--
-- Table structure for table `approved_ids`
--

CREATE TABLE `approved_ids` (
  `id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `notes` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `approved_ids`
--

INSERT INTO `approved_ids` (`id`, `device_id`, `notes`) VALUES
(16, 21, 'FFN Legacy User21'),
(17, 20, 'Gchrome Legacy User20'),
(18, 15, 'Explorer Legacy User19'),
(19, 18, 'Legacy User18'),
(20, 23, 'Legacy User23'),
(21, 25, 'Legacy User'),
(22, 29, 'Legacy User29');

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `id` int(11) NOT NULL,
  `fingerprint` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_approved` tinyint(1) DEFAULT 0,
  `shop_name` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `device_number` varchar(50) DEFAULT NULL,
  `registration_complete` tinyint(1) DEFAULT 0,
  `videos` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `devices`
--

INSERT INTO `devices` (`id`, `fingerprint`, `created_at`, `is_approved`, `shop_name`, `postal_code`, `device_number`, `registration_complete`, `videos`) VALUES
(15, 'd06f8b52f3cc80f59405ab2332212325', '2025-03-29 15:46:22', 0, 'Explorer', 'Explo123', 'D1', 1, '[\"videolar/1.mp4\",https://mediumpurple-pony-682107.hostingersite.com/]'),
(22, '5c4bb24277a242290f2e919d7d20e1e8', '2025-03-30 14:25:59', 0, NULL, NULL, NULL, 0, NULL),
(24, '546cf41ee55fdb3bb8716c1c7dac7ea7', '2025-03-30 14:35:44', 0, NULL, NULL, NULL, 0, NULL),
(25, 'fbae8a76ecb04525cb81a5cc77d69d0c', '2025-03-30 14:36:10', 0, 'Admin', '4', '455', 1, NULL),
(26, 'bc3138da3019ea4abdbc99607810ce29', '2025-03-30 14:53:47', 0, NULL, NULL, NULL, 0, NULL),
(28, 'bfdb668525e5ee9744137e0004570f5c', '2025-03-30 15:51:55', 0, NULL, NULL, NULL, 0, NULL),
(29, '551e014a7f5262d0566eef1b933d9963', '2025-03-30 16:05:57', 0, 'tv1', 'tv123', '1', 1, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `approved_ids`
--
ALTER TABLE `approved_ids`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_id` (`device_id`);

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fingerprint` (`fingerprint`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `approved_ids`
--
ALTER TABLE `approved_ids`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
