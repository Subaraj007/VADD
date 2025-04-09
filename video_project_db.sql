-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 09, 2025 at 06:39 PM
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
-- Database: `video_project_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `approvedevices`
--

CREATE TABLE `approvedevices` (
  `Id` int(11) NOT NULL,
  `UniqueId` varchar(255) NOT NULL,
  `StoreId` varchar(10) NOT NULL,
  `DeviceName` varchar(20) NOT NULL,
  `IsActive` tinyint(1) DEFAULT 1,
  `MediaPath` varchar(255) DEFAULT NULL,
  `EnterBy` varchar(50) NOT NULL,
  `ActionTime` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `approvedevices`
--

INSERT INTO `approvedevices` (`Id`, `UniqueId`, `StoreId`, `DeviceName`, `IsActive`, `MediaPath`, `EnterBy`, `ActionTime`) VALUES
(55, 'bfdb668525e5ee9744137e0004570f5c', 'S0100', 'TV1', 1, '/Base_VADD/videolar/2.mp4', 'system', '2025-04-09 14:41:28'),
(59, '551e014a7f5262d0566eef1b933d9963', 'S0100', 'TV3', 1, '/Base_VADD/videolar/2.mp4', 'system', '2025-04-09 16:32:33'),
(60, '5a0b8fd9f754b1c7a7edab3593155f51', 'S0100', 'TV2', 1, '/Base_VADD/videolar/2.mp4', 'system', '2025-04-09 16:38:37');

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `Id` int(11) NOT NULL,
  `DeviceName` varchar(20) NOT NULL,
  `StoreId` varchar(10) NOT NULL,
  `UniqueId` varchar(255) NOT NULL,
  `IsRegister` tinyint(1) DEFAULT 0,
  `EnterBy` varchar(50) NOT NULL,
  `ActionDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `devices`
--

INSERT INTO `devices` (`Id`, `DeviceName`, `StoreId`, `UniqueId`, `IsRegister`, `EnterBy`, `ActionDate`) VALUES
(1, 'TV1', 'S0100', 'bfdb668525e5ee9744137e0004570f5c', 1, 'admin', '2025-04-01 09:21:40'),
(2, 'TV2', 'S0100', '5a0b8fd9f754b1c7a7edab3593155f51', 1, 'admin', '2025-04-01 09:21:51'),
(3, 'TV3', 'S0100', '551e014a7f5262d0566eef1b933d9963', 1, 'admin', '2025-04-01 09:21:59'),
(4, 'TV4', 'S0100', '', 0, 'admin', '2025-04-01 09:22:08'),
(5, 'TV1', 'S0101', '', 0, 'admin', '2025-04-01 09:21:40'),
(6, 'TV2', 'S0101', '', 0, 'admin', '2025-04-01 09:21:51');

-- --------------------------------------------------------

--
-- Table structure for table `noseries`
--

CREATE TABLE `noseries` (
  `StoreId` int(11) NOT NULL,
  `PayId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `PayId` varchar(10) NOT NULL,
  `StoreId` varchar(10) NOT NULL,
  `Plans` enum('Basic','Standard','Premium') NOT NULL,
  `Amount` decimal(10,2) NOT NULL,
  `StartDate` date NOT NULL,
  `EndDate` date NOT NULL,
  `IsPaid` tinyint(1) DEFAULT 0,
  `EnterBy` varchar(50) NOT NULL,
  `ActionTime` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`PayId`, `StoreId`, `Plans`, `Amount`, `StartDate`, `EndDate`, `IsPaid`, `EnterBy`, `ActionTime`) VALUES
('P1001', 'S0100', 'Basic', 10.00, '2025-04-01', '2025-04-30', 1, 'admin', '2025-04-01 14:53:46'),
('P1002', 'S0101', 'Basic', 8.00, '2025-03-01', '2025-03-30', 1, 'admin', '2025-04-01 14:54:23');

-- --------------------------------------------------------

--
-- Table structure for table `stores`
--

CREATE TABLE `stores` (
  `StoreId` varchar(10) NOT NULL,
  `StoreName` varchar(200) NOT NULL,
  `Address` text NOT NULL,
  `PhoneNum` varchar(20) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `NoOfHost` int(11) DEFAULT 0,
  `IsBlock` tinyint(1) DEFAULT 0,
  `EnterBy` varchar(50) NOT NULL,
  `ActionDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stores`
--

INSERT INTO `stores` (`StoreId`, `StoreName`, `Address`, `PhoneNum`, `Email`, `NoOfHost`, `IsBlock`, `EnterBy`, `ActionDate`) VALUES
('S0100', 'BIRLING FRIED CHICKEN', 'BIRLING', '0123 456789', 'bfc@gmail.com', 4, 0, 'admin', '2025-04-01 14:50:41'),
('S0101', 'AKSHNA FRIED CHICKEN', 'KENT', '0123 456789', 'akshna@gmail.com', 2, 0, 'admin', '2025-04-01 14:51:15');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `Id` int(11) DEFAULT NULL,
  `UserName` varchar(50) NOT NULL,
  `Role` varchar(20) NOT NULL DEFAULT 'user',
  `Password` varchar(255) NOT NULL,
  `Email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `approvedevices`
--
ALTER TABLE `approvedevices`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `UniqueId` (`UniqueId`);

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`PayId`);

--
-- Indexes for table `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`StoreId`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserName`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `approvedevices`
--
ALTER TABLE `approvedevices`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
