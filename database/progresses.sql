-- phpMyAdmin SQL Dump
-- version 5.0.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 04, 2021 at 01:48 PM
-- Server version: 10.4.14-MariaDB
-- PHP Version: 7.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `thesis`
--

-- --------------------------------------------------------

--
-- Table structure for table `progresses`
--

CREATE TABLE `progresses` (
  `student` int(11) NOT NULL,
  `topic` int(11) NOT NULL,
  `progress` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `progresses`
--

INSERT INTO `progresses` (`student`, `topic`, `progress`) VALUES
(4, 51, 2),
(4, 52, 3),
(4, 53, 1),
(4, 54, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `progresses`
--
ALTER TABLE `progresses`
  ADD PRIMARY KEY (`student`,`topic`),
  ADD KEY `progresses_ibfk_2` (`topic`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `progresses`
--
ALTER TABLE `progresses`
  ADD CONSTRAINT `progresses_ibfk_1` FOREIGN KEY (`student`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `progresses_ibfk_2` FOREIGN KEY (`topic`) REFERENCES `topics` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
