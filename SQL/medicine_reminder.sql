-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 21, 2025 at 05:24 PM
-- Server version: 10.1.38-MariaDB
-- PHP Version: 5.6.40

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `medicine_reminder`
--

-- --------------------------------------------------------

--
-- Table structure for table `medicine_history`
--

CREATE TABLE `medicine_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `medicine_name` varchar(255) NOT NULL,
  `dosage` varchar(100) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `taken_time` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `medicine_history`
--

INSERT INTO `medicine_history` (`id`, `user_id`, `medicine_name`, `dosage`, `start_time`, `end_time`, `taken_time`, `created_at`) VALUES
(55, 1, 'Paroxetine', '1 tablet - Consult dosage with doctor', '2025-11-21 17:13:00', '2025-11-21 18:13:00', '2025-11-21 04:25:45', '2025-11-21 09:14:14'),
(57, 1, 'Diazepam', '1 tablet - Consult dosage with doctor', '2025-11-21 17:25:00', '2025-11-21 18:25:00', '2025-11-21 04:30:36', '2025-11-21 09:30:33'),
(58, 1, 'Atorvastatin', '1 tablet - Consult dosage with doctor', '2025-11-21 17:34:00', '2025-11-21 18:34:00', '2025-11-21 04:35:06', '2025-11-21 09:35:01'),
(59, 1, 'Ibuprofen 200mg', '1-2 tablets (200mg) - Pain and inflammation', '2025-11-21 18:36:00', '2025-11-21 19:36:00', '2025-11-21 05:37:22', '2025-11-21 10:37:13');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, '123', '$2y$10$fdqF5YjulWgC0UklqLhVH.LVzZSk.53qim0pdSjzHnM88yVkfJmZS'),
(2, '1234', '$2y$10$wtcH7RvEHcKf9BQBY2EVCuKHsuDKTraaJlGRJ.qyqEGKmAc.obW32'),
(3, '12345qwe', '$2y$10$uDyBT.guEesFLUrgGML5YOyiYSJEu5zrQeFJb2Ohw1uS7A0QAYNzG'),
(4, '90041805', '$2y$10$Mf4G47/BAh6KD.7AN/oRgeGdwW6qoWiuBrqWxE0G.AfiQ2PchocRm'),
(5, '123', '$2y$10$fdqF5YjulWgC0UklqLhVH.LVzZSk.53qim0pdSjzHnM88yVkfJmZS'),
(6, '1234', '$2y$10$wtcH7RvEHcKf9BQBY2EVCuKHsuDKTraaJlGRJ.qyqEGKmAc.obW32'),
(7, 'Stanley', '$2y$10$1YzTRbXn8dP2zojkrnpPK.prWqbG94S0PDeym/M/0sFkwDcLFcWJy');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `medicine_history`
--
ALTER TABLE `medicine_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `medicine_history`
--
ALTER TABLE `medicine_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `medicine_history`
--
ALTER TABLE `medicine_history`
  ADD CONSTRAINT `medicine_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
