-- phpMyAdmin SQL Dump
-- version 5.0.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 10, 2021 at 12:40 PM
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
-- Table structure for table `topics`
--

CREATE TABLE `topics` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `topics`
--

INSERT INTO `topics` (`id`, `name`, `description`) VALUES
(51, 'Basic JavaScript', 'Basic coding for JavaScript'),
(52, 'Vanilla JavaScript', 'Plain JavaScript without external libraries'),
(53, 'ReactJS', 'React is a library for building user interfaces.\r\nIt allows you to build isolated UI components in a simple, declarative way. When the state of your application changes, affected components will react accordingly by re-rendering to reflect the new state.'),
(54, 'Infrastructure', 'The infrastructure for front-end developers'),
(59, 'Single Page Application', NULL),
(60, 'useContext', NULL),
(61, 'NodeJS', NULL),
(62, 'Class Components', NULL),
(69, 'SQL schemas', 'Database schemas'),
(70, 'SQL', 'queries'),
(71, 'PLpgSQL', 'functions'),
(72, 'join', 'join'),
(73, 'select', 'projection'),
(74, 'where', 'where'),
(75, 'Data modelling', 'basic'),
(76, 'testt', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `topics`
--
ALTER TABLE `topics`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `topics`
--
ALTER TABLE `topics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
