-- phpMyAdmin SQL Dump
-- version 4.7.9
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 21, 2021 at 06:51 PM
-- Server version: 10.5.10-MariaDB
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `documentsadmin_main`
--

-- --------------------------------------------------------

--
-- Table structure for table `administrators`
--

CREATE TABLE `administrators` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_bin NOT NULL,
  `password` varchar(255) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `administrators`
--

INSERT INTO `administrators` (`id`, `name`, `password`) VALUES
(1, 'ivana', '$2y$10$gpNpEIZVdKinHD/otoiPg.M294cMXazT4Le.Y4r1l7QPF8FeY5xiK'),
(2, 'gosho', '$2y$10$gpNpEIZVdKinHD/otoiPg.M294cMXazT4Le.Y4r1l7QPF8FeY5xiK');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_bin NOT NULL,
  `email` varchar(255) COLLATE utf8_bin NOT NULL,
  `password` varchar(255) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `email`, `password`) VALUES
(1, 'Студенти', 'doc.system.mail@mail.bg', '$2y$10$gpNpEIZVdKinHD/otoiPg.M294cMXazT4Le.Y4r1l7QPF8FeY5xiK'),
(2, 'Магистри', 'masters@fmi.com', '$2y$10$gpNpEIZVdKinHD/otoiPg.M294cMXazT4Le.Y4r1l7QPF8FeY5xiK'),
(3, 'Кандидатстуденти', 'candidate@fmi.com', '$2y$10$gpNpEIZVdKinHD/otoiPg.M294cMXazT4Le.Y4r1l7QPF8FeY5xiK'),
(4, 'Сесия', 'session@fmi.com', '$2y$10$gpNpEIZVdKinHD/otoiPg.M294cMXazT4Le.Y4r1l7QPF8FeY5xiK'),
(5, 'Друг', 'kirilgolov1@gmail.com', '$2y$10$gpNpEIZVdKinHD/otoiPg.M294cMXazT4Le.Y4r1l7QPF8FeY5xiK');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_bin NOT NULL,
  `description` text COLLATE utf8_bin NOT NULL,
  `code` char(15) COLLATE utf8_bin NOT NULL,
  `hash` char(40) COLLATE utf8_bin NOT NULL,
  `path` varchar(255) COLLATE utf8_bin NOT NULL,
  `department_id` int(11) NOT NULL,
  `timesOpened` int(11) NOT NULL DEFAULT 0,
  `status_id` int(11) NOT NULL DEFAULT 2,
  `uploaded` bigint(20) NOT NULL,
  `firstOpened` bigint(20) DEFAULT NULL,
  `lastOpened` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `name`, `description`, `code`, `hash`, `path`, `department_id`, `timesOpened`, `status_id`, `uploaded`, `firstOpened`, `lastOpened`) VALUES
(19, 'Ивана', 'Такса', '9d85e4aab8959c2', '69d85c8fa10938ef70ee25cbad7a461458d921d9', 'files/file19.pdf', 3, 5, 6, 1624127486, 1624139449, 1624301042),
(20, 'Ivana', 'Такса', 'd2994e44c933bcf', 'b0baccaa6e31cdbb9df486d18b762abeaabbdcff', 'files/file20.pdf', 3, 5, 4, 1624127824, 1624139434, 1624301037),
(23, 'Реферат - ZIP', 'Реферат от w16ed - пробен ZIP архив.', 'fe53ec4782bc3d1', 'a4af7f40208c298c83d7b2b27e032c6dd00430d0', 'files/file23/index.html', 5, 7, 7, 1624140248, 1624140268, 1624212236),
(31, 'PDF пример за документ', 'Документ за отдел Студенти.', 'cd56774b40e3214', '2f80123389cba1d751ebfed899a7d19c45398ec4', 'files/file31.pdf', 1, 12, 2, 1624213742, 1624214036, 1624301031);

-- --------------------------------------------------------

--
-- Table structure for table `statusChanges`
--

CREATE TABLE `statusChanges` (
  `id` int(11) NOT NULL,
  `status` varchar(255) COLLATE utf8_bin NOT NULL,
  `document_id` int(11) NOT NULL,
  `role` enum('ADMIN','DEPT') COLLATE utf8_bin NOT NULL,
  `changed` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `statusChanges`
--

INSERT INTO `statusChanges` (`id`, `status`, `document_id`, `role`, `changed`) VALUES
(57, 'view', 1, 'ADMIN', 1624122115),
(115, 'view', 20, 'ADMIN', 1624139434),
(116, 'view', 19, 'ADMIN', 1624139449),
(117, 'view', 23, 'ADMIN', 1624140268),
(118, 'view', 23, 'ADMIN', 1624140300),
(119, '1', 23, 'ADMIN', 1624140316),
(120, 'download', 23, 'ADMIN', 1624140317),
(122, 'download', 23, 'ADMIN', 1624141431),
(147, 'view', 23, 'ADMIN', 1624187795),
(148, 'view', 19, 'ADMIN', 1624188978),
(149, 'download', 20, 'ADMIN', 1624188985),
(150, 'view', 23, 'ADMIN', 1624188992),
(151, 'view', 23, 'ADMIN', 1624208340),
(152, 'view', 23, 'ADMIN', 1624212074),
(153, 'view', 23, 'ADMIN', 1624212236),
(154, '4', 23, 'ADMIN', 1624212241),
(155, '6', 23, 'ADMIN', 1624212306),
(156, 'download', 23, 'ADMIN', 1624212319),
(157, '7', 23, 'ADMIN', 1624212329),
(158, 'view', 31, 'ADMIN', 1624214036),
(159, '3', 31, 'ADMIN', 1624214039),
(160, 'view', 20, 'ADMIN', 1624214042),
(161, '4', 20, 'ADMIN', 1624214046),
(162, 'view', 19, 'ADMIN', 1624214048),
(163, '6', 19, 'ADMIN', 1624214050),
(164, 'view', 31, 'ADMIN', 1624214063),
(165, '2', 31, 'ADMIN', 1624214065),
(166, 'view', 31, 'ADMIN', 1624214075),
(167, 'view', 31, 'ADMIN', 1624214374),
(168, 'view', 31, 'ADMIN', 1624222282),
(169, 'download', 31, 'ADMIN', 1624222285),
(170, 'view', 31, 'ADMIN', 1624277495),
(171, 'view', 31, 'ADMIN', 1624277506),
(172, 'download', 31, 'ADMIN', 1624277766),
(173, 'download', 20, 'ADMIN', 1624277774),
(174, 'download', 19, 'ADMIN', 1624277778),
(175, 'download', 31, 'ADMIN', 1624277789),
(176, '6', 23, 'ADMIN', 1624277795),
(177, '7', 23, 'ADMIN', 1624277799),
(178, '6', 23, 'ADMIN', 1624277801),
(179, '7', 23, 'ADMIN', 1624277808),
(180, 'download', 31, 'ADMIN', 1624280758),
(181, 'view', 20, 'ADMIN', 1624280771),
(182, 'view', 31, 'ADMIN', 1624282600),
(183, 'download', 31, 'ADMIN', 1624282604),
(184, '6', 23, 'ADMIN', 1624283097),
(185, '7', 23, 'ADMIN', 1624283101),
(186, 'view', 31, 'ADMIN', 1624283752),
(187, 'view', 20, 'ADMIN', 1624283761),
(188, 'view', 19, 'ADMIN', 1624283763),
(189, 'view', 31, 'ADMIN', 1624283766),
(190, 'view', 31, 'ADMIN', 1624290224),
(191, 'view', 31, 'ADMIN', 1624301031),
(192, 'view', 20, 'ADMIN', 1624301037),
(193, 'view', 19, 'ADMIN', 1624301042);

-- --------------------------------------------------------

--
-- Table structure for table `statuses`
--

CREATE TABLE `statuses` (
  `id` int(11) NOT NULL,
  `color` char(6) COLLATE utf8_bin NOT NULL,
  `status` varchar(255) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `statuses`
--

INSERT INTO `statuses` (`id`, `color`, `status`) VALUES
(1, 'B6977D', 'Приоритетен'),
(2, 'DD80A0', 'Очаква обработка'),
(3, 'C6E1C6', 'Обработва се'),
(4, 'F8DDA7', 'Паузиран'),
(5, 'EBA3A3', 'Изисква корекция'),
(6, 'C8D7E1', 'Приключен'),
(7, 'E5E5E5', 'Архивиран');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `administrators`
--
ALTER TABLE `administrators`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `IDX_CODE` (`code`),
  ADD KEY `FK_STATUS` (`status_id`),
  ADD KEY `FK_DEPARTMENT` (`department_id`);

--
-- Indexes for table `statusChanges`
--
ALTER TABLE `statusChanges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_DOCUMENT` (`document_id`);

--
-- Indexes for table `statuses`
--
ALTER TABLE `statuses`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `administrators`
--
ALTER TABLE `administrators`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `statusChanges`
--
ALTER TABLE `statusChanges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=194;

--
-- AUTO_INCREMENT for table `statuses`
--
ALTER TABLE `statuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `FK_DEPARTMENT` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_STATUS` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `statusChanges`
--
ALTER TABLE `statusChanges`
  ADD CONSTRAINT `FK_DOCUMENT` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
