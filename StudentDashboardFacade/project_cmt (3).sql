-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: 26 مايو 2025 الساعة 16:03
-- إصدار الخادم: 10.4.10-MariaDB
-- PHP Version: 7.3.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
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
-- بنية الجدول `files`
--

DROP TABLE IF EXISTS `files`;
CREATE TABLE IF NOT EXISTS `files` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `file_name` varchar(20) NOT NULL,
  `file_type` varchar(20) NOT NULL,
  PRIMARY KEY (`file_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4;

--
-- إرجاع أو استيراد بيانات الجدول `files`
--

INSERT INTO `files` (`file_id`, `file_name`, `file_type`) VALUES
(1, 'cs431Finalexam.pdf', 'application/pdf'),
(2, 'Lesson-8_ Sequence D', 'application/pdf'),
(3, 'Lesson-6_Software De', 'application/pdf'),
(4, 'Lesson-8_ Sequence D', 'application/pdf'),
(5, 'Lesson-8_ Sequence D', 'application/pdf'),
(6, 'Lesson-8_ Sequence D', ''),
(7, 'Lesson-8_ Sequence D', ''),
(8, 'wallpaperflare.com_w', ''),
(9, 'Lesson-6_Software De', ''),
(10, 'Lesson-5_Scrum (4).p', ''),
(11, 'tripoli.webp', ''),
(12, 'delete.drawio.png', 'image/png'),
(13, 'delete.drawio.png', 'image/png'),
(14, 'Lecture06.pdf', 'application/pdf'),
(15, 'Lecture04 (2).pdf', 'application/pdf');

-- --------------------------------------------------------

--
-- بنية الجدول `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `sender` varchar(255) NOT NULL,
  `receiver` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message_content` text NOT NULL,
  `send_date` datetime DEFAULT current_timestamp(),
  `is_deleted_by_receiver` tinyint(1) DEFAULT 0,
  `is_deleted_by_sender` tinyint(1) DEFAULT 0,
  `is_read` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`message_id`),
  KEY `sender` (`sender`),
  KEY `receiver` (`receiver`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

--
-- إرجاع أو استيراد بيانات الجدول `messages`
--

INSERT INTO `messages` (`message_id`, `sender`, `receiver`, `subject`, `message_content`, `send_date`, `is_deleted_by_receiver`, `is_deleted_by_sender`, `is_read`) VALUES
(1, 'rawase', 'lara', 'SDSJKDH', 'RGJHEBFD FUTIRUGHFJGTKGHKJFH', '2025-05-09 23:36:18', 0, 1, 0),
(2, 'rawase', 'lara', 'Ø§Ù‡Ù„Ø§', 'ØµØ¹Ø§ØªØ³Ø§Ø¤Ø¹ØºÙ‡ØµØºÙŠÙ‡Ø¹Ø§', '2025-05-09 23:47:02', 0, 1, 0),
(3, 'rawase', 'loly', 'Ø§Ù‡Ù„Ø§', 'JFBDNB3URTHJFNJ3HFUI3HE', '2025-05-09 23:58:19', 0, 1, 0),
(4, 'rawase', 'test1', 'Ø§Ù‡Ù„Ø§', 'HJXJKSNCWEUHFCKJNFKURG', '2025-05-10 00:00:07', 1, 1, 0),
(5, 'test1', 'mona', 'Ù„Ø§Ø§ØªØ«Ù„Ø§Ø§Ø¨Ù„Ø§Ø§Ø«ÙÙ„', 'ØºÙØªØ§Ù‰Ù†Øº', '2025-05-10 00:31:18', 0, 1, 0),
(6, 'test1', 'ALiPh', 'Ø§Ù„Ù…Ø´Ø±ÙˆØ¹ ', 'Ø§Ù‡Ù„Ø§ Ø¯ÙƒØªÙˆØ± Ø£Ù†Ø§ Ø§Ù„Ø·Ø§Ù„Ø¨Ù‡', '2025-05-10 00:37:01', 0, 0, 0),
(7, 'test1', 'admin', 'Ø§Ù‡Ù„Ø§', 'Ù…Ø³ØªØ®Ø¯Ù… 1', '2025-05-18 18:31:32', 0, 0, 0),
(8, 'test1', 'rawase', 'Ø§Ù‡Ù„Ø§', 'Ø§Ù‡Ù„Ø§ Ø¨Ùƒ ÙÙ‰ Ù‰Ù…Ø´Ø±ÙˆØ¹', '2025-05-26 13:13:16', 0, 0, 0);

-- --------------------------------------------------------

--
-- بنية الجدول `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `recipient` varchar(100) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- بنية الجدول `projects`
--

DROP TABLE IF EXISTS `projects`;
CREATE TABLE IF NOT EXISTS `projects` (
  `project_id` int(11) NOT NULL AUTO_INCREMENT,
  `project_code` varchar(50) NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` varchar(20) NOT NULL,
  `supervisor` varchar(100) DEFAULT NULL,
  `student` varchar(100) NOT NULL,
  `team_leader` varchar(20) NOT NULL,
  PRIMARY KEY (`project_id`),
  UNIQUE KEY `project_code` (`project_code`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;

--
-- إرجاع أو استيراد بيانات الجدول `projects`
--

INSERT INTO `projects` (`project_id`, `project_code`, `project_name`, `start_date`, `end_date`, `status`, `supervisor`, `student`, `team_leader`) VALUES
(1, '1', 'project', '2025-04-25', '2025-04-18', 'Pending', 'test3', '0', '0'),
(2, '2', 'CMT', '2025-04-22', '2025-05-08', 'Pending', 'retaj', '0', '0'),
(3, '44', 'pro', '2025-05-06', '2025-05-07', 'Pending', 'marwa18', '0', '0'),
(4, '13', 'clean home', '2025-05-05', '2025-05-07', 'Pending', 'marwa18', '0', '0'),
(5, '12', 'driver', '2025-05-16', '2025-05-30', 'Pending', 'marwa18', '0', '0'),
(6, '33', 'AI', '2025-05-14', '2025-05-07', 'Pending', 'marwa18', '0', '0'),
(7, '455', 'AI new', '2025-05-14', '2025-05-07', 'Pending', 'marwa18', '0', '0'),
(8, '4552', 'AI new 3', '2025-05-14', '2025-05-07', 'Pending', 'marwa18', 'gida', '0'),
(9, '0000', 'projectManagemantCMT', '2025-05-01', '2025-06-03', 'Pending', 'ALiPh', 'test2, maram02, test1, mmmm, rawase', 'loly'),
(10, '90', 'machin learninig', '2025-05-02', '2025-06-07', 'Pending', 'jeje', 'test, test1, hhh, rawase', 'mona'),
(13, '12345', 'Ø¥Ø®ØªØ¨Ø§Ø±', '2025-05-25', '2025-06-01', 'Pending', 'test6', 'test1, rawase, tweri78', 'test3'),
(14, '768', 'game', '2025-03-01', '2025-07-10', 'Pending', 'test6', 'test2, test1, rawase', 'mona'),
(15, '099089', 'creat project', '2025-05-03', '2025-05-30', 'Completed', 'test6', 'test2, maram02, test1, rawase', 'mona');

-- --------------------------------------------------------

--
-- بنية الجدول `project_members`
--

DROP TABLE IF EXISTS `project_members`;
CREATE TABLE IF NOT EXISTS `project_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `member_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=latin1;

--
-- إرجاع أو استيراد بيانات الجدول `project_members`
--

INSERT INTO `project_members` (`id`, `project_id`, `member_name`) VALUES
(1, 1, 'test1'),
(2, 3, 'maram02'),
(3, 5, 'maram02'),
(4, 6, 'lara'),
(5, 7, 'lara'),
(6, 8, 'gida'),
(7, 9, 'test2'),
(8, 9, 'maram02'),
(9, 9, 'test1'),
(10, 9, 'mmmm'),
(11, 9, 'rawase'),
(12, 10, 'test'),
(13, 10, 'test1'),
(14, 10, 'hhh'),
(15, 10, 'rawase'),
(21, 13, 'test1'),
(22, 13, 'rawase'),
(23, 13, 'tweri78'),
(24, 14, 'test2'),
(25, 14, 'test1'),
(26, 14, 'rawase'),
(27, 15, 'test2'),
(28, 15, 'maram02'),
(29, 15, 'test1'),
(30, 15, 'rawase');

-- --------------------------------------------------------

--
-- بنية الجدول `report`
--

DROP TABLE IF EXISTS `report`;
CREATE TABLE IF NOT EXISTS `report` (
  `reportID` int(11) NOT NULL AUTO_INCREMENT,
  `reportDate` date NOT NULL,
  `numComplet` int(11) NOT NULL,
  `numLate` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `taskID` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  PRIMARY KEY (`reportID`),
  KEY `ro2` (`file_id`),
  KEY `ro3` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- بنية الجدول `task`
--

DROP TABLE IF EXISTS `task`;
CREATE TABLE IF NOT EXISTS `task` (
  `taskID` int(11) NOT NULL AUTO_INCREMENT,
  `taskName` varchar(40) NOT NULL,
  `status` varchar(20) NOT NULL,
  `deadline` date NOT NULL,
  `project_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `assigned_to` varchar(20) NOT NULL,
  PRIMARY KEY (`taskID`),
  KEY `fo1` (`file_id`),
  KEY `fo2` (`project_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4;

--
-- إرجاع أو استيراد بيانات الجدول `task`
--

INSERT INTO `task` (`taskID`, `taskName`, `status`, `deadline`, `project_id`, `file_id`, `assigned_to`) VALUES
(1, 'Assignment 4', 'Ù…ÙƒØªÙ…Ù„Ø©', '2025-04-29', 1, 0, 'test1'),
(2, 'Assignment 5', 'Ù…ÙƒØªÙ…Ù„Ø©', '2025-04-28', 1, 2, 'test1'),
(3, 'Assignment 6', 'قيد التنفيذ', '2025-04-30', 1, 3, ''),
(4, 'Assignment 66', 'Ù…ÙƒØªÙ…Ù„Ø©', '2025-04-30', 1, 13, 'test1'),
(5, 'Assignment 66', 'Ù…ÙƒØªÙ…Ù„Ø©', '2025-04-30', 1, 15, 'test1'),
(6, 'Assignment 13', 'Ù…ÙƒØªÙ…Ù„Ø©', '2025-04-16', 1, 12, 'test1'),
(7, 'Assignment 14', 'Ù…ÙƒØªÙ…Ù„Ø©', '2025-04-30', 1, 14, 'test1'),
(8, 'Assignment 15', 'غير مكتملة', '2025-04-30', 1, 8, ''),
(9, 'Assignment 8', 'غير مكتملة', '2025-05-10', 4, 9, ''),
(10, 'Assignment 9', 'غير مكتملة', '2025-05-10', 5, 10, ''),
(11, 'Assignment 55', 'غير مكتملة', '2025-05-10', 4, 11, '');

-- --------------------------------------------------------

--
-- بنية الجدول `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=latin1;

--
-- إرجاع أو استيراد بيانات الجدول `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `email`) VALUES
(2, 'test', '$2y$10$5qbQPwfxx11OScF139n7l.GM5h6hX6xecnV3ziuRaiUNzdmLL9nBm', 'student', ''),
(3, 'test2', '$2y$10$cQyLYO92ltuOVd37j5fAeeqlxEBlOugc0DCopaQ1zept94luVp6e2', 'student', 'nada@gmail.com'),
(4, 'test3', '$2y$10$BpYP70EsiKvJCGIgt.eMaeeEZYSys0HqQeLrVYQr2b.nbnbl8p/86', 'team_leader', 'retaf@gmail.com'),
(5, 'maram02', '$2y$10$WQSCwMuVXfdy6xuHrQGly.R75m49g.NkEJBQY16/pWHOO1bt1K0V6', 'Student', 'marammaram@gmail.com'),
(6, 'marwa18', '$2y$10$qe8Tg/5MNgV1YkWR.fD55emIxLuu20IMop2ZZdpOusPv3.sSRA/2a', 'Supervisor', 'marwa@gmail.com'),
(7, 'motaz', '$2y$10$I8hWuZ0JgIiBJcgetkCvgObXSoQEcXR2qL8fyxWi0Eu8i8ocPRDHG', 'Supervisor', 'motazezoo@gmail.com'),
(8, 'motaz98', '$2y$10$Bp1YNn2wc/PyUx35DB.JW.cRCyzm9aqJX/ctLMM8Zw00tcezmc/66', 'Supervisor', 'motazezo@gmail.com'),
(9, 'loly', '$2y$10$NYZoy7wvEWtI36d3byUHZeFT7g3qHLROMUaEFSwBUI2va5i5EqOfa', 'Supervisor', 'loly@gmail.com'),
(10, 'lara', '$2y$10$ZeNd7aQUitt/VwtfHCzdteYoAXJxGq1rEZDHaki/ZbV.sDDiTnli.', 'Student', 'lara@gmail.com'),
(11, 'gida', '$2y$10$j7LNA72vgkOu.6WMBHIl8OWyO4hrKBDc/9TlM1aJ5zj3DV5J2biBq', 'Student', 'gida@gmail.com'),
(12, 'sadig', '$2y$10$v1RvO.vK.SbYhIpIgxoLt./OJydBuokFwwXHMACfHghyUK0yoSr1a', 'Admin', 'sadig@gmail.com'),
(13, 'test1', '$2y$10$peoTJW6y60Pr86Lfd15CX.ndIOP/lcX0m97aUTeNDsfOGNMLBNoYy', 'Student', 'test1@gmail.com'),
(14, 'mmmm', '$2y$10$onoLyCegehsfY1Qf59YwCe7LuhjVT29vUOC370tsaXbJZ6WInirb.', 'Student', 'nmkk@gmail.com'),
(15, 'hhh', '$2y$10$FtW46DiJvCDL.I/nuaC2LuUYjIblkdrGuDVs.iaasAmtsuOUQ9alC', 'Student', 'retaj@gmail.com'),
(16, 'ALiPh', '12300000', 'supervisor', 'ALiPh@gmail.com'),
(17, 'mona', '00000000', 'team_leader', 'mona@gmail.com'),
(18, 're', '', '', ''),
(22, 'rawase', '$2y$10$L.Emch4EnsabPX/rMbODuuDKfqcbI26.ftPH5dm.zYLklTv.sMsUe', 'Student', 'rawase@gmail.com'),
(23, 'admin', '$2y$10$FnT.cle2gKQdUUQApuiYT.ClsHIXnc6lOuLXE01uLlZT0CLF0Hvdu', 'Supervisor', 'admin@gmail.com'),
(24, 'tweri78', '$2y$10$7s000nyWm1cbYloCTnChy.sluNCP3vM9ZcElfJmUA6Rg6JOgpWLW6', 'Student', 'tweri78@gmail.com'),
(25, 'jeje', '$2y$10$6ve49iAjWPinyklXWowKnes5dcZJV1n3.Md1QKHwSOeliy2ER3MJO', 'Supervisor', 'jeje@gmail.com'),
(26, 'test6', '$2y$10$/qOLWRVK8X1UvdiGFZJPmu24YuNTI/6Opj59UENcVd7Ubv4OZtEkG', 'Supervisor', 'test6@gmail.com');

--
-- قيود الجداول المحفوظة
--

--
-- القيود للجدول `project_members`
--
ALTER TABLE `project_members`
  ADD CONSTRAINT `fk_project_members_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- القيود للجدول `report`
--
ALTER TABLE `report`
  ADD CONSTRAINT `fk_report_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ro1` FOREIGN KEY (`file_id`) REFERENCES `files` (`file_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ro2` FOREIGN KEY (`file_id`) REFERENCES `files` (`file_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- القيود للجدول `task`
--
ALTER TABLE `task`
  ADD CONSTRAINT `fk_task_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
