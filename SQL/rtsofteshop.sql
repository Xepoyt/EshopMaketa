-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 19, 2025 at 10:24 PM
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
-- Database: `rtsofteshop`
--

-- --------------------------------------------------------

--
-- Table structure for table `kombinace`
--

CREATE TABLE `kombinace` (
  `id` int(11) NOT NULL,
  `kusy` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Dumping data for table `kombinace`
--

INSERT INTO `kombinace` (`id`, `kusy`) VALUES
(33, 0),
(34, 1),
(35, 2),
(36, 3),
(37, 4),
(38, 5),
(39, 6),
(40, 7),
(41, 8),
(42, 9),
(43, 0),
(44, 1),
(45, 0),
(46, 2),
(47, 4),
(48, 5),
(49, 6),
(50, 7),
(51, 8),
(52, 9),
(53, 0),
(54, 1),
(55, 2),
(56, 3),
(57, 4),
(58, 5),
(59, 6),
(60, 7),
(61, 8),
(62, 9),
(63, 0),
(64, 1),
(65, 2),
(66, 3),
(67, 4),
(68, 5),
(69, 6),
(70, 7),
(71, 3),
(72, 9),
(73, 0),
(74, 0),
(75, 2),
(76, 3),
(77, 4),
(78, 5),
(79, 6),
(80, 7),
(81, 7),
(82, 9),
(83, 0),
(84, 0),
(85, 2),
(86, 3),
(87, 4),
(88, 5),
(89, 6),
(90, 7),
(91, 8),
(92, 9),
(93, 0),
(94, 1),
(95, 2),
(96, 3),
(97, 4),
(98, 5),
(99, 6),
(100, 7),
(101, 8),
(102, 9),
(103, 0),
(104, 1),
(105, 2),
(106, 3),
(107, 4),
(108, 5),
(109, 6),
(110, 7),
(111, 2);

-- --------------------------------------------------------

--
-- Table structure for table `objednavka`
--

CREATE TABLE `objednavka` (
  `id` int(11) NOT NULL,
  `email` text NOT NULL,
  `jmeno` text NOT NULL,
  `telefon` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Dumping data for table `objednavka`
--

INSERT INTO `objednavka` (`id`, `email`, `jmeno`, `telefon`) VALUES
(1, 'majitel@penize.com', 'Majitel Peněz', '123456789'),
(4, 'majitel@penize.com', 'Majitel Peněz', '111222333'),
(5, 'majitel@penize.com', 'Majitel Peněz', '111222333'),
(6, 'majitel@penize.com', 'Majitel Peněz', '333666999'),
(7, 'penize@majitelstvi.cz', 'Peníze Majitele', '111444777'),
(8, 'test@alert.cz', 'Pan Upozornění', '123456654'),
(9, 'udelej@alert.pls', 'Halo Pane', '123456788');

-- --------------------------------------------------------

--
-- Table structure for table `objednavka_kombinace`
--

CREATE TABLE `objednavka_kombinace` (
  `objednavka_id` int(11) NOT NULL,
  `kombinace_id` int(11) NOT NULL,
  `kusy` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Dumping data for table `objednavka_kombinace`
--

INSERT INTO `objednavka_kombinace` (`objednavka_id`, `kombinace_id`, `kusy`) VALUES
(1, 111, 3),
(6, 46, 1),
(6, 71, 3),
(6, 111, 2),
(7, 71, 2),
(7, 111, 1),
(8, 74, 1),
(9, 84, 1);

-- --------------------------------------------------------

--
-- Table structure for table `produkt`
--

CREATE TABLE `produkt` (
  `id` int(11) NOT NULL,
  `nazev` text NOT NULL,
  `cena100` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Dumping data for table `produkt`
--

INSERT INTO `produkt` (`id`, `nazev`, `cena100`) VALUES
(1, 'Tričko', 39990),
(2, 'Tílko', 32990),
(3, 'Kšiltovka', 31990),
(4, 'Džíny', 68990),
(5, 'Tepláky', 64990),
(6, 'Rohlík', 290);

-- --------------------------------------------------------

--
-- Table structure for table `produkt_stitek`
--

CREATE TABLE `produkt_stitek` (
  `produkt_id` int(11) NOT NULL,
  `stitek_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Dumping data for table `produkt_stitek`
--

INSERT INTO `produkt_stitek` (`produkt_id`, `stitek_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(5, 1),
(5, 2),
(6, 3);

-- --------------------------------------------------------

--
-- Table structure for table `produkt_varianta`
--

CREATE TABLE `produkt_varianta` (
  `id` int(11) NOT NULL,
  `produkt_id` int(11) NOT NULL,
  `varianta_id` int(11) DEFAULT NULL,
  `varianta_hodnota` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Dumping data for table `produkt_varianta`
--

INSERT INTO `produkt_varianta` (`id`, `produkt_id`, `varianta_id`, `varianta_hodnota`) VALUES
(66, 1, 1, 'Černá'),
(67, 1, 1, 'Bílá'),
(68, 1, 1, 'Modrá'),
(69, 1, 1, 'Zelená'),
(70, 1, 2, 'S'),
(71, 1, 2, 'M'),
(72, 1, 2, 'L'),
(73, 1, 3, 'Regular'),
(74, 1, 3, 'Baggy'),
(75, 1, 3, 'Slim'),
(76, 2, 1, 'Bílá'),
(77, 2, 1, 'Černá'),
(78, 2, 1, 'Zelená'),
(79, 2, 2, 'S'),
(80, 2, 2, 'M'),
(81, 2, 2, 'L'),
(82, 2, 2, 'XL'),
(83, 3, 1, 'Bílá'),
(84, 3, 1, 'Černá'),
(85, 4, 3, 'Baggy'),
(86, 4, 3, 'Skinny'),
(87, 4, 4, '30'),
(88, 4, 4, '32'),
(89, 4, 4, '34'),
(90, 4, 5, '32'),
(91, 4, 5, '34'),
(92, 4, 5, '36'),
(93, 4, 5, '38'),
(94, 5, 1, 'Černá'),
(95, 5, 1, 'Šedá'),
(96, 5, 2, 'S'),
(97, 5, 2, 'M'),
(98, 6, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `produkt_varianta_kombinace`
--

CREATE TABLE `produkt_varianta_kombinace` (
  `produkt_varianta_id` int(11) NOT NULL,
  `kombinace_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Dumping data for table `produkt_varianta_kombinace`
--

INSERT INTO `produkt_varianta_kombinace` (`produkt_varianta_id`, `kombinace_id`) VALUES
(66, 33),
(66, 34),
(66, 35),
(66, 36),
(66, 37),
(66, 38),
(66, 39),
(66, 40),
(66, 41),
(67, 42),
(67, 43),
(67, 44),
(67, 45),
(67, 46),
(67, 47),
(67, 48),
(67, 49),
(67, 50),
(68, 51),
(68, 52),
(68, 53),
(68, 54),
(68, 55),
(68, 56),
(68, 57),
(68, 58),
(68, 59),
(69, 60),
(69, 61),
(69, 62),
(69, 63),
(69, 64),
(69, 65),
(69, 66),
(69, 67),
(69, 68),
(70, 33),
(70, 34),
(70, 35),
(70, 42),
(70, 43),
(70, 44),
(70, 51),
(70, 52),
(70, 53),
(70, 60),
(70, 61),
(70, 62),
(71, 36),
(71, 37),
(71, 38),
(71, 45),
(71, 46),
(71, 47),
(71, 54),
(71, 55),
(71, 56),
(71, 63),
(71, 64),
(71, 65),
(72, 39),
(72, 40),
(72, 41),
(72, 48),
(72, 49),
(72, 50),
(72, 57),
(72, 58),
(72, 59),
(72, 66),
(72, 67),
(72, 68),
(73, 33),
(73, 36),
(73, 39),
(73, 42),
(73, 45),
(73, 48),
(73, 51),
(73, 54),
(73, 57),
(73, 60),
(73, 63),
(73, 66),
(74, 34),
(74, 37),
(74, 40),
(74, 43),
(74, 46),
(74, 49),
(74, 52),
(74, 55),
(74, 58),
(74, 61),
(74, 64),
(74, 67),
(75, 35),
(75, 38),
(75, 41),
(75, 44),
(75, 47),
(75, 50),
(75, 53),
(75, 56),
(75, 59),
(75, 62),
(75, 65),
(75, 68),
(76, 69),
(76, 70),
(76, 71),
(76, 72),
(77, 73),
(77, 74),
(77, 75),
(77, 76),
(78, 77),
(78, 78),
(78, 79),
(78, 80),
(79, 69),
(79, 73),
(79, 77),
(80, 70),
(80, 74),
(80, 78),
(81, 71),
(81, 75),
(81, 79),
(82, 72),
(82, 76),
(82, 80),
(83, 81),
(84, 82),
(85, 83),
(85, 84),
(85, 85),
(85, 86),
(85, 87),
(85, 88),
(85, 89),
(85, 90),
(85, 91),
(85, 92),
(85, 93),
(85, 94),
(86, 95),
(86, 96),
(86, 97),
(86, 98),
(86, 99),
(86, 100),
(86, 101),
(86, 102),
(86, 103),
(86, 104),
(86, 105),
(86, 106),
(87, 83),
(87, 84),
(87, 85),
(87, 86),
(87, 95),
(87, 96),
(87, 97),
(87, 98),
(88, 87),
(88, 88),
(88, 89),
(88, 90),
(88, 99),
(88, 100),
(88, 101),
(88, 102),
(89, 91),
(89, 92),
(89, 93),
(89, 94),
(89, 103),
(89, 104),
(89, 105),
(89, 106),
(90, 83),
(90, 87),
(90, 91),
(90, 95),
(90, 99),
(90, 103),
(91, 84),
(91, 88),
(91, 92),
(91, 96),
(91, 100),
(91, 104),
(92, 85),
(92, 89),
(92, 93),
(92, 97),
(92, 101),
(92, 105),
(93, 86),
(93, 90),
(93, 94),
(93, 98),
(93, 102),
(93, 106),
(94, 107),
(94, 108),
(95, 109),
(95, 110),
(96, 107),
(96, 109),
(97, 108),
(97, 110),
(98, 111);

-- --------------------------------------------------------

--
-- Table structure for table `stitek`
--

CREATE TABLE `stitek` (
  `id` int(11) NOT NULL,
  `text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Dumping data for table `stitek`
--

INSERT INTO `stitek` (`id`, `text`) VALUES
(1, 'Sleva'),
(2, 'Nové'),
(3, 'Top');

-- --------------------------------------------------------

--
-- Table structure for table `varianta`
--

CREATE TABLE `varianta` (
  `id` int(11) NOT NULL,
  `nazev` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Dumping data for table `varianta`
--

INSERT INTO `varianta` (`id`, `nazev`) VALUES
(1, 'Barva'),
(2, 'Velikost'),
(3, 'Střih'),
(4, 'Délka'),
(5, 'Obvod v pase');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `kombinace`
--
ALTER TABLE `kombinace`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `objednavka`
--
ALTER TABLE `objednavka`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `objednavka_kombinace`
--
ALTER TABLE `objednavka_kombinace`
  ADD PRIMARY KEY (`objednavka_id`,`kombinace_id`),
  ADD KEY `kombinace_id` (`kombinace_id`);

--
-- Indexes for table `produkt`
--
ALTER TABLE `produkt`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `produkt_stitek`
--
ALTER TABLE `produkt_stitek`
  ADD PRIMARY KEY (`produkt_id`,`stitek_id`),
  ADD KEY `stitek_id` (`stitek_id`);

--
-- Indexes for table `produkt_varianta`
--
ALTER TABLE `produkt_varianta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produkt_id` (`produkt_id`),
  ADD KEY `varianta_id` (`varianta_id`);

--
-- Indexes for table `produkt_varianta_kombinace`
--
ALTER TABLE `produkt_varianta_kombinace`
  ADD PRIMARY KEY (`produkt_varianta_id`,`kombinace_id`),
  ADD KEY `kombinace_id` (`kombinace_id`),
  ADD KEY `produkt_varianta_id` (`produkt_varianta_id`) USING BTREE;

--
-- Indexes for table `stitek`
--
ALTER TABLE `stitek`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `varianta`
--
ALTER TABLE `varianta`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `kombinace`
--
ALTER TABLE `kombinace`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=160;

--
-- AUTO_INCREMENT for table `objednavka`
--
ALTER TABLE `objednavka`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `produkt`
--
ALTER TABLE `produkt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `produkt_varianta`
--
ALTER TABLE `produkt_varianta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `stitek`
--
ALTER TABLE `stitek`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `varianta`
--
ALTER TABLE `varianta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `objednavka_kombinace`
--
ALTER TABLE `objednavka_kombinace`
  ADD CONSTRAINT `objednavka_kombinace_ibfk_1` FOREIGN KEY (`kombinace_id`) REFERENCES `kombinace` (`id`),
  ADD CONSTRAINT `objednavka_kombinace_ibfk_2` FOREIGN KEY (`objednavka_id`) REFERENCES `objednavka` (`id`);

--
-- Constraints for table `produkt_stitek`
--
ALTER TABLE `produkt_stitek`
  ADD CONSTRAINT `produkt_stitek_ibfk_1` FOREIGN KEY (`stitek_id`) REFERENCES `stitek` (`id`),
  ADD CONSTRAINT `produkt_stitek_ibfk_2` FOREIGN KEY (`produkt_id`) REFERENCES `produkt` (`id`);

--
-- Constraints for table `produkt_varianta`
--
ALTER TABLE `produkt_varianta`
  ADD CONSTRAINT `produkt_varianta_ibfk_1` FOREIGN KEY (`produkt_id`) REFERENCES `produkt` (`id`),
  ADD CONSTRAINT `produkt_varianta_ibfk_2` FOREIGN KEY (`varianta_id`) REFERENCES `varianta` (`id`);

--
-- Constraints for table `produkt_varianta_kombinace`
--
ALTER TABLE `produkt_varianta_kombinace`
  ADD CONSTRAINT `produkt_varianta_kombinace_ibfk_1` FOREIGN KEY (`kombinace_id`) REFERENCES `kombinace` (`id`),
  ADD CONSTRAINT `produkt_varianta_kombinace_ibfk_2` FOREIGN KEY (`produkt_varianta_id`) REFERENCES `produkt_varianta` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
