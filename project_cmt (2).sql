-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 23, 2025 at 12:45 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project_cmt`
--

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;
CREATE TABLE IF NOT EXISTS `files` (
  `file_id` int NOT NULL AUTO_INCREMENT,
  `file_name` varchar(20) NOT NULL,
  `file_type` varchar(20) NOT NULL,
  PRIMARY KEY (`file_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `files`
--

INSERT INTO `files` (`file_id`, `file_name`, `file_type`) VALUES
(1, 'cs431Finalexam.pdf', 'application/pdf'),
(2, 'Lesson-8_ Sequence D', 'application/pdf'),
(3, 'Lesson-6_Software De', 'application/pdf');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
CREATE TABLE IF NOT EXISTS `projects` (
  `id` int NOT NULL AUTO_INCREMENT,
  `project_id` varchar(50) NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` varchar(20) NOT NULL,
  `team_leader` varchar(100) DEFAULT NULL,
  `supervisor` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_id` (`project_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `project_id`, `project_name`, `start_date`, `end_date`, `status`, `team_leader`, `supervisor`) VALUES
(1, '1', 'project', '2025-04-25', '2025-04-18', 'Pending', 'test1', 'test3');

-- --------------------------------------------------------

--
-- Table structure for table `project_members`
--

DROP TABLE IF EXISTS `project_members`;
CREATE TABLE IF NOT EXISTS `project_members` (
  `id` int NOT NULL AUTO_INCREMENT,
  `project_id` varchar(50) NOT NULL,
  `member_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `project_members`
--

INSERT INTO `project_members` (`id`, `project_id`, `member_name`) VALUES
(1, '1', 'test1');

-- --------------------------------------------------------

--
-- Table structure for table `task`
--

DROP TABLE IF EXISTS `task`;
CREATE TABLE IF NOT EXISTS `task` (
  `taskID` int DEFAULT NULL,
  `taskName` varchar(40) NOT NULL,
  `status` varchar(20) NOT NULL,
  `deadline` date NOT NULL,
  `project_id` int NOT NULL,
  `file_id` int NOT NULL,
  KEY `fo1` (`file_id`),
  KEY `fo2` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `task`
--

INSERT INTO `task` (`taskID`, `taskName`, `status`, `deadline`, `project_id`, `file_id`) VALUES
(NULL, 'Assignment 4', 'قيد التنفيذ', '2025-04-29', 1, 0),
(NULL, 'Assignment 5', 'قيد التنفيذ', '2025-04-28', 1, 2),
(NULL, 'Assignment 6', 'قيد التنفيذ', '2025-04-30', 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `email`) VALUES
(1, 'retaj', '$2y$10$0BedSZDxF0NPzxLBBKmWSOQGKbTV4BpUqMJ7Nrq07PuOTUG4.xUqi', 'supervisor', ''),
(2, 'test', '$2y$10$5qbQPwfxx11OScF139n7l.GM5h6hX6xecnV3ziuRaiUNzdmLL9nBm', 'student', ''),
(3, 'test2', '$2y$10$cQyLYO92ltuOVd37j5fAeeqlxEBlOugc0DCopaQ1zept94luVp6e2', 'student', 'nada@gmail.com'),
(4, 'test3', '$2y$10$BpYP70EsiKvJCGIgt.eMaeeEZYSys0HqQeLrVYQr2b.nbnbl8p/86', 'leader', 'retaf@gmail.com');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `task`
--
ALTER TABLE `task`
  ADD CONSTRAINT `fo2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
