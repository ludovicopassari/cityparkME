-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: mysql:3306
-- Creato il: Gen 23, 2025 alle 10:44
-- Versione del server: 9.1.0
-- Versione PHP: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `my_database`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `credit_cards`
--

CREATE TABLE `credit_cards` (
  `card_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `card_number` varchar(20) NOT NULL,
  `expiration_date` date DEFAULT NULL,
  `card_type` enum('VISA','MasterCard','AMEX') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `credit_cards`
--

INSERT INTO `credit_cards` (`card_id`, `user_id`, `card_number`, `expiration_date`, `card_type`) VALUES
(8, 11, 'wefwre', NULL, 'VISA');

-- --------------------------------------------------------

--
-- Struttura della tabella `otp_transactions`
--

CREATE TABLE `otp_transactions` (
  `transaction_id` varchar(12) NOT NULL,
  `user_id` int NOT NULL,
  `otp_code` varchar(10) NOT NULL,
  `expiration_time` timestamp NOT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `otp_transactions`
--

INSERT INTO `otp_transactions` (`transaction_id`, `user_id`, `otp_code`, `expiration_time`, `is_used`, `created_at`) VALUES
('0dd269933fa3', 11, 'L23PVP', '2025-01-20 11:27:37', 1, '2025-01-20 11:22:37'),
('8649ac51e8a8', 11, 'DAB1X7', '2025-01-22 17:36:27', 1, '2025-01-22 17:31:27'),
('94d9485bfff2', 11, 'YRMAOF', '2025-01-22 12:26:54', 1, '2025-01-22 12:21:54'),
('97b08b440ddf', 11, 'CO7RE6', '2025-01-22 18:00:55', 1, '2025-01-22 17:55:55'),
('97db2bc7ecf9', 11, '90I1KS', '2025-01-22 17:42:52', 1, '2025-01-22 17:37:52'),
('998f1448cba4', 11, 'NZ1GDY', '2025-01-22 14:30:10', 1, '2025-01-22 14:25:10'),
('a197f2618575', 11, '870DBJ', '2025-01-22 13:28:01', 1, '2025-01-22 13:23:01'),
('ac4b568b1a22', 11, '04ZDSQ', '2025-01-17 14:07:46', 1, '2025-01-17 14:02:46'),
('bb3ea5e12a48', 11, 'RT16IB', '2025-01-17 13:48:14', 1, '2025-01-17 13:43:14'),
('cccea2c0521d', 11, '2T27E5', '2025-01-22 12:04:19', 1, '2025-01-22 11:59:19'),
('dcb71b606da9', 11, 'ID0DWJ', '2025-01-17 15:08:38', 1, '2025-01-17 15:03:38'),
('e4217dbdac28', 11, 'TMX6BC', '2025-01-22 15:02:13', 1, '2025-01-22 14:57:13');

-- --------------------------------------------------------

--
-- Struttura della tabella `parking_slots`
--

CREATE TABLE `parking_slots` (
  `slot_id` int NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,6) DEFAULT NULL,
  `longitude` decimal(10,6) DEFAULT NULL,
  `rate_per_hour` decimal(10,2) NOT NULL,
  `is_occupied` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `parking_slots`
--

INSERT INTO `parking_slots` (`slot_id`, `location`, `latitude`, `longitude`, `rate_per_hour`, `is_occupied`, `created_at`) VALUES
(661, 'Stallo 1', 38.082551, 15.491350, 2.60, 0, '2025-01-22 18:02:38'),
(662, 'Stallo 2', 38.082551, 15.492350, 1.05, 0, '2025-01-22 18:02:38'),
(663, 'Stallo 3', 38.082551, 15.493350, 2.21, 1, '2025-01-22 18:02:38'),
(664, 'Stallo 4', 38.082551, 15.494350, 2.22, 0, '2025-01-22 18:02:38'),
(665, 'Stallo 5', 38.082551, 15.495350, 3.34, 0, '2025-01-22 18:02:38'),
(666, 'Stallo 6', 38.082551, 15.496350, 2.13, 1, '2025-01-22 18:02:38'),
(695, 'Stallo 35', 38.085551, 15.495350, 3.39, 0, '2025-01-22 18:02:38'),
(696, 'Stallo 36', 38.085551, 15.496350, 4.19, 0, '2025-01-22 18:02:38'),
(697, 'Stallo 37', 38.085551, 15.497350, 4.77, 0, '2025-01-22 18:02:38'),
(698, 'Stallo 38', 38.085551, 15.498350, 4.92, 0, '2025-01-22 18:02:38'),
(699, 'Stallo 39', 38.085551, 15.499350, 4.60, 0, '2025-01-22 18:02:38'),
(700, 'Stallo 40', 38.085551, 15.500350, 2.65, 0, '2025-01-22 18:02:38'),
(701, 'Stallo 41', 38.086551, 15.491350, 4.13, 1, '2025-01-22 18:02:38'),
(702, 'Stallo 42', 38.086551, 15.492350, 4.01, 0, '2025-01-22 18:02:38'),
(703, 'Stallo 43', 38.086551, 15.493350, 3.80, 0, '2025-01-22 18:02:38'),
(704, 'Stallo 44', 38.086551, 15.494350, 4.45, 1, '2025-01-22 18:02:38'),
(705, 'Stallo 45', 38.086551, 15.495350, 3.39, 0, '2025-01-22 18:02:38'),
(706, 'Stallo 46', 38.086551, 15.496350, 4.95, 1, '2025-01-22 18:02:38'),
(707, 'Stallo 47', 38.086551, 15.497350, 3.52, 0, '2025-01-22 18:02:38'),
(708, 'Stallo 48', 38.086551, 15.498350, 4.55, 0, '2025-01-22 18:02:38'),
(709, 'Stallo 49', 38.086551, 15.499350, 1.10, 0, '2025-01-22 18:02:38'),
(710, 'Stallo 50', 38.086551, 15.500350, 2.14, 1, '2025-01-22 18:02:38'),
(711, 'Stallo 51', 38.087551, 15.491350, 1.39, 0, '2025-01-22 18:02:38'),
(712, 'Stallo 52', 38.087551, 15.492350, 4.19, 0, '2025-01-22 18:02:38'),
(713, 'Stallo 53', 38.087551, 15.493350, 2.82, 0, '2025-01-22 18:02:38'),
(714, 'Stallo 54', 38.087551, 15.494350, 3.43, 0, '2025-01-22 18:02:38'),
(715, 'Stallo 55', 38.087551, 15.495350, 4.15, 0, '2025-01-22 18:02:38'),
(716, 'Stallo 56', 38.087551, 15.496350, 4.19, 0, '2025-01-22 18:02:38'),
(717, 'Stallo 57', 38.087551, 15.497350, 1.97, 0, '2025-01-22 18:02:38'),
(718, 'Stallo 58', 38.087551, 15.498350, 4.62, 0, '2025-01-22 18:02:38'),
(719, 'Stallo 59', 38.087551, 15.499350, 2.47, 0, '2025-01-22 18:02:38'),
(720, 'Stallo 60', 38.087551, 15.500350, 1.90, 0, '2025-01-22 18:02:38'),
(721, 'Stallo 61', 38.088551, 15.491350, 2.87, 0, '2025-01-22 18:02:38'),
(722, 'Stallo 62', 38.088551, 15.492350, 2.80, 0, '2025-01-22 18:02:38'),
(723, 'Stallo 63', 38.088551, 15.493350, 4.63, 0, '2025-01-22 18:02:38'),
(724, 'Stallo 64', 38.088551, 15.494350, 3.50, 0, '2025-01-22 18:02:38'),
(725, 'Stallo 65', 38.088551, 15.495350, 2.29, 0, '2025-01-22 18:02:38'),
(726, 'Stallo 66', 38.088551, 15.496350, 3.73, 0, '2025-01-22 18:02:38'),
(727, 'Stallo 67', 38.088551, 15.497350, 2.85, 0, '2025-01-22 18:02:38'),
(728, 'Stallo 68', 38.088551, 15.498350, 3.61, 0, '2025-01-22 18:02:38'),
(729, 'Stallo 69', 38.088551, 15.499350, 2.99, 0, '2025-01-22 18:02:38'),
(730, 'Stallo 70', 38.088551, 15.500350, 3.16, 0, '2025-01-22 18:02:38'),
(731, 'Stallo 71', 38.089551, 15.491350, 1.86, 0, '2025-01-22 18:02:38'),
(732, 'Stallo 72', 38.089551, 15.492350, 3.98, 0, '2025-01-22 18:02:38'),
(733, 'Stallo 73', 38.089551, 15.493350, 4.82, 0, '2025-01-22 18:02:38'),
(734, 'Stallo 74', 38.089551, 15.494350, 2.26, 0, '2025-01-22 18:02:38'),
(735, 'Stallo 75', 38.089551, 15.495350, 2.64, 0, '2025-01-22 18:02:38'),
(736, 'Stallo 76', 38.089551, 15.496350, 4.17, 0, '2025-01-22 18:02:38'),
(737, 'Stallo 77', 38.089551, 15.497350, 3.86, 0, '2025-01-22 18:02:38'),
(738, 'Stallo 78', 38.089551, 15.498350, 4.32, 0, '2025-01-22 18:02:38'),
(739, 'Stallo 79', 38.089551, 15.499350, 1.76, 0, '2025-01-22 18:02:38'),
(740, 'Stallo 80', 38.089551, 15.500350, 1.31, 1, '2025-01-22 18:02:38'),
(741, 'Stallo 81', 38.090551, 15.491350, 2.86, 1, '2025-01-22 18:02:38'),
(742, 'Stallo 82', 38.090551, 15.492350, 3.07, 0, '2025-01-22 18:02:38'),
(743, 'Stallo 83', 38.090551, 15.493350, 1.38, 0, '2025-01-22 18:02:38'),
(744, 'Stallo 84', 38.090551, 15.494350, 4.29, 0, '2025-01-22 18:02:38'),
(745, 'Stallo 85', 38.090551, 15.495350, 3.96, 0, '2025-01-22 18:02:38'),
(746, 'Stallo 86', 38.090551, 15.496350, 4.36, 1, '2025-01-22 18:02:38'),
(747, 'Stallo 87', 38.090551, 15.497350, 3.69, 0, '2025-01-22 18:02:38'),
(748, 'Stallo 88', 38.090551, 15.498350, 1.15, 0, '2025-01-22 18:02:38'),
(749, 'Stallo 89', 38.090551, 15.499350, 4.49, 0, '2025-01-22 18:02:38'),
(750, 'Stallo 90', 38.090551, 15.500350, 3.70, 0, '2025-01-22 18:02:38'),
(751, 'Stallo 91', 38.091551, 15.491350, 1.55, 0, '2025-01-22 18:02:38'),
(752, 'Stallo 92', 38.091551, 15.492350, 3.42, 1, '2025-01-22 18:02:38'),
(753, 'Stallo 93', 38.091551, 15.493350, 4.55, 0, '2025-01-22 18:02:38'),
(754, 'Stallo 94', 38.091551, 15.494350, 2.93, 1, '2025-01-22 18:02:38'),
(755, 'Stallo 95', 38.091551, 15.495350, 4.24, 0, '2025-01-22 18:02:38'),
(756, 'Stallo 96', 38.091551, 15.496350, 4.12, 1, '2025-01-22 18:02:38'),
(757, 'Stallo 97', 38.091551, 15.497350, 3.37, 0, '2025-01-22 18:02:38'),
(758, 'Stallo 98', 38.091551, 15.498350, 1.10, 0, '2025-01-22 18:02:38'),
(759, 'Stallo 99', 38.091551, 15.499350, 1.63, 1, '2025-01-22 18:02:38'),
(760, 'Stallo 100', 38.091551, 15.500350, 1.84, 0, '2025-01-22 18:02:38');

-- --------------------------------------------------------

--
-- Struttura della tabella `payments`
--

CREATE TABLE `payments` (
  `payment_id` int NOT NULL,
  `reservation_id` int NOT NULL,
  `payment_method` enum('credit_card','paypal','bank_transfer') NOT NULL,
  `payment_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `amount` decimal(10,2) NOT NULL,
  `payment_status` enum('successful','failed') DEFAULT 'successful'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `payments`
--

INSERT INTO `payments` (`payment_id`, `reservation_id`, `payment_method`, `payment_date`, `amount`, `payment_status`) VALUES
(16, 26, 'paypal', '2025-01-22 18:10:23', 3.52, 'successful'),
(17, 27, 'credit_card', '2025-01-22 18:14:01', 3.52, 'successful'),
(18, 28, 'credit_card', '2025-01-22 18:21:25', 18.20, 'successful'),
(19, 29, 'credit_card', '2025-01-22 18:25:39', 0.06, 'successful'),
(20, 30, 'credit_card', '2025-01-22 18:37:57', 3.39, 'successful'),
(21, 31, 'bank_transfer', '2025-01-22 18:38:07', 7.04, 'successful');

-- --------------------------------------------------------

--
-- Struttura della tabella `reservations`
--

CREATE TABLE `reservations` (
  `reservation_id` int NOT NULL,
  `user_id` int NOT NULL,
  `slot_id` int NOT NULL,
  `start_time` timestamp NOT NULL,
  `end_time` timestamp NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `status` enum('confirmed','cancelled','completed') DEFAULT 'confirmed',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `plates_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `reservations`
--

INSERT INTO `reservations` (`reservation_id`, `user_id`, `slot_id`, `start_time`, `end_time`, `total_cost`, `payment_status`, `status`, `created_at`, `updated_at`, `plates_id`) VALUES
(26, 11, 707, '2025-01-22 14:04:00', '2025-01-22 15:04:00', 3.52, 'paid', 'completed', '2025-01-22 18:05:12', '2025-01-22 18:10:23', 53),
(27, 11, 707, '2025-01-22 16:11:00', '2025-01-22 17:11:00', 3.52, 'paid', 'completed', '2025-01-22 18:11:22', '2025-01-22 18:14:01', 53),
(28, 11, 708, '2025-01-22 13:11:00', '2025-01-22 17:11:00', 18.20, 'paid', 'completed', '2025-01-22 18:12:01', '2025-01-22 18:21:25', 56),
(29, 11, 707, '2025-01-22 15:12:00', '2025-01-22 15:13:00', 0.06, 'paid', 'completed', '2025-01-22 18:13:09', '2025-01-22 18:25:39', 56),
(30, 11, 705, '2025-01-22 14:22:00', '2025-01-22 15:22:00', 3.39, 'paid', 'completed', '2025-01-22 18:22:25', '2025-01-22 18:37:57', 53),
(31, 11, 707, '2025-01-22 13:31:00', '2025-01-22 15:31:00', 7.04, 'paid', 'completed', '2025-01-22 18:31:34', '2025-01-22 18:38:07', 56),
(32, 11, 708, '2025-01-22 11:38:00', '2025-01-22 18:38:00', 31.85, 'pending', 'completed', '2025-01-22 18:38:28', '2025-01-22 18:39:01', 53),
(33, 11, 707, '2025-01-22 06:59:00', '2025-01-22 07:59:00', 3.52, 'pending', 'completed', '2025-01-22 18:44:40', '2025-01-22 18:45:01', 53);

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `two_factor_enabled` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `password_hash`, `created_at`, `updated_at`, `two_factor_enabled`) VALUES
(11, 'Ludovico', 'Passari', 'ludovico.passari', 'ludovicopassari9@gmail.com', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', '2025-01-17 11:17:59', '2025-01-17 11:17:59', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `veichle`
--

CREATE TABLE `veichle` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `targa` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `veichle`
--

INSERT INTO `veichle` (`id`, `user_id`, `targa`) VALUES
(53, 11, 'EF123PD'),
(56, 11, 'AB1234CD');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `credit_cards`
--
ALTER TABLE `credit_cards`
  ADD PRIMARY KEY (`card_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indici per le tabelle `otp_transactions`
--
ALTER TABLE `otp_transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indici per le tabelle `parking_slots`
--
ALTER TABLE `parking_slots`
  ADD PRIMARY KEY (`slot_id`);

--
-- Indici per le tabelle `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `reservation_id` (`reservation_id`);

--
-- Indici per le tabelle `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `slot_id` (`slot_id`),
  ADD KEY `plates_id` (`plates_id`);

--
-- Indici per le tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indici per le tabelle `veichle`
--
ALTER TABLE `veichle`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `targa` (`targa`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `credit_cards`
--
ALTER TABLE `credit_cards`
  MODIFY `card_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT per la tabella `parking_slots`
--
ALTER TABLE `parking_slots`
  MODIFY `slot_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=761;

--
-- AUTO_INCREMENT per la tabella `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT per la tabella `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT per la tabella `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT per la tabella `veichle`
--
ALTER TABLE `veichle`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `credit_cards`
--
ALTER TABLE `credit_cards`
  ADD CONSTRAINT `credit_cards_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Limiti per la tabella `otp_transactions`
--
ALTER TABLE `otp_transactions`
  ADD CONSTRAINT `otp_transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`reservation_id`);

--
-- Limiti per la tabella `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`slot_id`) REFERENCES `parking_slots` (`slot_id`),
  ADD CONSTRAINT `reservations_ibfk_3` FOREIGN KEY (`plates_id`) REFERENCES `veichle` (`id`);

--
-- Limiti per la tabella `veichle`
--
ALTER TABLE `veichle`
  ADD CONSTRAINT `veichle_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
