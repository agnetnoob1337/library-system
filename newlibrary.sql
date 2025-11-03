-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Värd: 127.0.0.1
-- Tid vid skapande: 03 nov 2025 kl 13:39
-- Serverversion: 10.4.32-MariaDB
-- PHP-version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databas: `library`
--

-- --------------------------------------------------------

--
-- Tabellstruktur `checked_out`
--

CREATE TABLE `checked_out` (
  `id` int(11) NOT NULL,
  `return_date` date NOT NULL,
  `c_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `checkout_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumpning av Data i tabell `checked_out`
--

INSERT INTO `checked_out` (`id`, `return_date`, `c_id`, `user_id`, `checkout_date`) VALUES
(8, '2025-11-24', 27, 9, '2025-11-03');

-- --------------------------------------------------------

--
-- Tabellstruktur `copy`
--

CREATE TABLE `copy` (
  `id` int(11) NOT NULL,
  `media_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellstruktur `late_returns`
--

CREATE TABLE `late_returns` (
  `id` int(11) NOT NULL,
  `media_title` varchar(255) NOT NULL,
  `fee` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date_of_return` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumpning av Data i tabell `late_returns`
--

INSERT INTO `late_returns` (`id`, `media_title`, `fee`, `user_id`, `date_of_return`) VALUES
(5, 'Berserk Deluxe Volume 2', 750, 9, '2025-11-03');

-- --------------------------------------------------------

--
-- Tabellstruktur `media`
--

CREATE TABLE `media` (
  `id` int(11) NOT NULL,
  `title` varchar(1000) NOT NULL,
  `author` varchar(255) NOT NULL,
  `SAB_signum` varchar(100) NOT NULL,
  `price` int(11) NOT NULL DEFAULT 0,
  `ISBN` varchar(20) NOT NULL,
  `IMDB` varchar(100) NOT NULL,
  `mediatype` varchar(10) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumpning av Data i tabell `media`
--

INSERT INTO `media` (`id`, `title`, `author`, `SAB_signum`, `price`, `ISBN`, `IMDB`, `mediatype`, `description`) VALUES
(25, 'The return to Silent Hill', 'Christophe Gans', 'I', 250, '', '22868010', 'film', ''),
(27, 'Rygga inte undan', 'Stephen King', 'H', 250, '9789100810801', '', 'ljudbok', ''),
(28, 'Berserk Deluxe Volume 2', 'Kentaro Miura', 'H', 500, '9789100810801', '', 'bok', '');

-- --------------------------------------------------------

--
-- Tabellstruktur `sab_categories`
--

CREATE TABLE `sab_categories` (
  `signum` varchar(5) NOT NULL,
  `category` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumpning av Data i tabell `sab_categories`
--

INSERT INTO `sab_categories` (`signum`, `category`) VALUES
('A', 'Bok- och bibliotekväsen'),
('B', 'Allmänt och blandat'),
('C', 'Religion'),
('D', 'Filosofi och psykologi'),
('E', 'Uppfostran och undervisning'),
('F', 'Språkvetenskap'),
('G', 'Litteraturvetenskap'),
('H', 'Skönlitteratur'),
('I', 'Konst, musik, teater, film, fotografi'),
('J', 'Arkeologi'),
('K', 'Historia'),
('L', 'Biografi med genealogi'),
('M', 'Etnografi, socialantropologi och enologi'),
('N', 'Geografi och lokalhistoria'),
('O', 'Samhäls- och rättsventenskap'),
('P', 'Teknik, industri och kommunikationer'),
('Q', 'Ekonomi och näringsväsen'),
('R', 'Idrott, leg och spel'),
('S', 'Militärväsen'),
('T', 'Matematik'),
('U', 'Naturvetenskap'),
('V', 'Medicin'),
('X', 'Musikalier'),
('Y', 'Musikinspelningar');

-- --------------------------------------------------------

--
-- Tabellstruktur `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumpning av Data i tabell `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `is_admin`) VALUES
(0, 'admin', 'admin', 1),
(9, 'emil', '$2y$10$hJ6ULPQzyOVzyFLIMy5a4.sbZCi74cjdZFR48Uk.lifefDrX2117W', 0),
(10, 'test3453', '$2y$10$u3XvJXS3b9Pxjdi0aX2WFulKCMDuoBlRC7jm8c/JsJeiey1e.jU6W', 0);

--
-- Index för dumpade tabeller
--

--
-- Index för tabell `checked_out`
--
ALTER TABLE `checked_out`
  ADD PRIMARY KEY (`id`);

--
-- Index för tabell `copy`
--
ALTER TABLE `copy`
  ADD PRIMARY KEY (`id`);

--
-- Index för tabell `late_returns`
--
ALTER TABLE `late_returns`
  ADD PRIMARY KEY (`id`);

--
-- Index för tabell `media`
--
ALTER TABLE `media`
  ADD UNIQUE KEY `barcode` (`id`);

--
-- Index för tabell `sab_categories`
--
ALTER TABLE `sab_categories`
  ADD UNIQUE KEY `signum` (`signum`);

--
-- Index för tabell `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_username` (`username`);

--
-- AUTO_INCREMENT för dumpade tabeller
--

--
-- AUTO_INCREMENT för tabell `checked_out`
--
ALTER TABLE `checked_out`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT för tabell `copy`
--
ALTER TABLE `copy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT för tabell `late_returns`
--
ALTER TABLE `late_returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT för tabell `media`
--
ALTER TABLE `media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT för tabell `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
