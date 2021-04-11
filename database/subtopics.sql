-- phpMyAdmin SQL Dump
-- version 5.0.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 11, 2021 at 11:24 AM
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
-- Table structure for table `subtopics`
--

CREATE TABLE `subtopics` (
  `id` int(11) NOT NULL,
  `topic` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `sort` int(11) NOT NULL,
  `video` varchar(25) DEFAULT NULL,
  `link` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `subtopics`
--

INSERT INTO `subtopics` (`id`, `topic`, `name`, `sort`, `video`, `link`) VALUES
(1, 51, 'Syntax', 1, 'tSvsPFpNHKs', 'https://developer.mozilla.org/en-US/docs/Web/API/HTMLElement?fbclid=IwAR1EC4z8_HuCLffw0xnVleZRnIZYtFzM11bMr73sr_lk4bljksAGFNR8jZc'),
(2, 51, 'Ecosystem', 2, NULL, NULL),
(3, 52, 'Intro', 1, NULL, NULL),
(4, 52, 'DOM', 2, NULL, NULL),
(5, 52, 'Loading order', 3, NULL, NULL),
(6, 53, 'Intro', 1, NULL, NULL),
(7, 53, 'Basic CSS usage', 2, NULL, NULL),
(8, 54, 'NPM Intro', 1, NULL, NULL),
(9, 59, 'testt', 1, NULL, NULL),
(10, 69, 'CREATE TABLE', 1, NULL, NULL),
(11, 69, 'PRIMARY KEY', 2, NULL, NULL),
(13, 54, 'testt', 2, NULL, NULL),
(14, 54, 'test2', 3, NULL, NULL),
(15, 61, 'Intro', 1, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `subtopics`
--
ALTER TABLE `subtopics`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `topic` (`topic`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `subtopics`
--
ALTER TABLE `subtopics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `subtopics`
--
ALTER TABLE `subtopics`
  ADD CONSTRAINT `subtopics_ibfk_1` FOREIGN KEY (`topic`) REFERENCES `topics` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
