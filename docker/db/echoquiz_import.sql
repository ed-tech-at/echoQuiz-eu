-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: mariadb
-- Erstellungszeit: 03. Jul 2025 um 11:53
-- Server-Version: 11.7.2-MariaDB-ubu2404
-- PHP-Version: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `echoquiz`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `answerAlerts`
--

CREATE TABLE `answerAlerts` (
  `alertId` int(11) NOT NULL,
  `f_answerId` int(11) NOT NULL DEFAULT 0,
  `alert_user_text` text NOT NULL DEFAULT '',
  `alert_sender_user_email` text NOT NULL DEFAULT '',
  `alert_from_admin` tinyint(2) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `answers`
--

CREATE TABLE `answers` (
  `answerId` int(11) NOT NULL,
  `f_roomId` int(11) NOT NULL DEFAULT 0,
  `f_userId` int(11) NOT NULL DEFAULT 0,
  `f_questionId` int(11) NOT NULL DEFAULT 0,
  `answer_text` varchar(255) NOT NULL DEFAULT '',
  `replaced_by_answerId` int(11) NOT NULL DEFAULT 0,
  `answer_grade` int(3) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `feedback`
--

CREATE TABLE `feedback` (
  `feedbackId` int(11) NOT NULL,
  `stars` int(11) NOT NULL DEFAULT 0,
  `f_feedbackKey` varchar(255) NOT NULL DEFAULT '',
  `f_tnId` int(11) NOT NULL DEFAULT 0,
  `f_roomId` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `feedbackBank`
--

CREATE TABLE `feedbackBank` (
  `feedbackBankId` int(11) NOT NULL,
  `feedbackGroup` int(11) NOT NULL DEFAULT 0,
  `feedbackQ` varchar(255) NOT NULL DEFAULT '',
  `feedbackKey` varchar(255) NOT NULL DEFAULT '',
  `feedbackOrder` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `feedbackBank`
--

INSERT INTO `feedbackBank` (`feedbackBankId`, `feedbackGroup`, `feedbackQ`, `feedbackKey`, `feedbackOrder`) VALUES
(1, 1, 'Das Quiz hat mein Interesse an den Inhalten geweckt:', 'interesse1', 1),
(2, 1, 'Das interaktive Format hat Spaß gemacht und motiviert:', 'motiviert1', 2),
(3, 1, 'Ich konnte mein Wissen durch das Quiz effektiv überprüfen:', 'wissencheck1', 3),
(4, 1, 'Das Feedback hat mir geholfen, meine Fehler zu verstehen:', 'fehlerhilfe1', 4),
(5, 1, 'Ich habe das Gefühl, durch das Quiz etwas Neues gelernt zu haben:', 'neues1', 5),
(8, 3, 'The quiz sparked my interest in the content:', 'interesse1e', 1),
(9, 3, 'The interactive format was fun and motivating:', 'motiviert1e', 2),
(10, 3, 'I was able to effectively test my knowledge with the quiz:', 'wissencheck1e', 3),
(11, 3, 'The feedback helped me understand my mistakes:', 'fehlerhilfe1e', 4),
(12, 3, 'I feel like I\'ve learned something new from taking this quiz:', 'neues1e', 5);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `openAiShortcode`
--

CREATE TABLE `openAiShortcode` (
  `openAiShortcodeId` int(11) NOT NULL,
  `apiShortcode` varchar(255) NOT NULL DEFAULT '',
  `apiKey` varchar(255) NOT NULL DEFAULT '',
  `mailRegex` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `questions`
--

CREATE TABLE `questions` (
  `questionId` int(11) NOT NULL,
  `f_roomId` int(11) NOT NULL DEFAULT 0,
  `questionText` text NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ratings`
--

CREATE TABLE `ratings` (
  `ratingId` int(11) NOT NULL,
  `f_questionId` int(11) NOT NULL DEFAULT 0,
  `f_answerId` int(11) NOT NULL DEFAULT 0,
  `f_tnId` int(11) NOT NULL DEFAULT 0,
  `rating` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `rooms`
--

CREATE TABLE `rooms` (
  `roomId` int(11) NOT NULL,
  `roomPw` varchar(32) NOT NULL,
  `roomPhase` varchar(10) NOT NULL DEFAULT 'b',
  `roomLang` varchar(10) NOT NULL DEFAULT 'de',
  `roomApiKey` varchar(255) NOT NULL DEFAULT '',
  `roomEmail` varchar(255) NOT NULL DEFAULT '',
  `f_feedbackGroup` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tn`
--

CREATE TABLE `tn` (
  `tnId` int(11) NOT NULL,
  `f_roomId` int(11) NOT NULL DEFAULT 0,
  `tnName` varchar(255) NOT NULL DEFAULT '',
  `phpSessionId` varchar(255) NOT NULL DEFAULT '',
  `logoutTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `loginTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `answerAlerts`
--
ALTER TABLE `answerAlerts`
  ADD PRIMARY KEY (`alertId`);

--
-- Indizes für die Tabelle `answers`
--
ALTER TABLE `answers`
  ADD PRIMARY KEY (`answerId`),
  ADD KEY `f_userId` (`f_userId`),
  ADD KEY `f_questionId` (`f_questionId`);

--
-- Indizes für die Tabelle `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedbackId`),
  ADD UNIQUE KEY `f_tnId` (`f_tnId`,`f_feedbackKey`);

--
-- Indizes für die Tabelle `feedbackBank`
--
ALTER TABLE `feedbackBank`
  ADD PRIMARY KEY (`feedbackBankId`);

--
-- Indizes für die Tabelle `openAiShortcode`
--
ALTER TABLE `openAiShortcode`
  ADD PRIMARY KEY (`openAiShortcodeId`);

--
-- Indizes für die Tabelle `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`questionId`),
  ADD KEY `f_roomId` (`f_roomId` DESC);

--
-- Indizes für die Tabelle `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`ratingId`),
  ADD KEY `f_questionId` (`f_questionId`),
  ADD KEY `f_tnId` (`f_tnId`),
  ADD KEY `f_answerId` (`f_answerId`);

--
-- Indizes für die Tabelle `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`roomId`);

--
-- Indizes für die Tabelle `tn`
--
ALTER TABLE `tn`
  ADD PRIMARY KEY (`tnId`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `answerAlerts`
--
ALTER TABLE `answerAlerts`
  MODIFY `alertId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `answers`
--
ALTER TABLE `answers`
  MODIFY `answerId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedbackId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `feedbackBank`
--
ALTER TABLE `feedbackBank`
  MODIFY `feedbackBankId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT für Tabelle `openAiShortcode`
--
ALTER TABLE `openAiShortcode`
  MODIFY `openAiShortcodeId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `questions`
--
ALTER TABLE `questions`
  MODIFY `questionId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `ratings`
--
ALTER TABLE `ratings`
  MODIFY `ratingId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `rooms`
--
ALTER TABLE `rooms`
  MODIFY `roomId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `tn`
--
ALTER TABLE `tn`
  MODIFY `tnId` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
