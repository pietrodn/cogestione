-- phpMyAdmin SQL Dump
-- version 4.2.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Lug 21, 2014 alle 17:43
-- Versione del server: 5.6.15
-- PHP Version: 5.4.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `cogestione`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `activity`
--

CREATE TABLE IF NOT EXISTS `activity` (
`activity_id` int(11) NOT NULL,
  `activity_time` int(11) NOT NULL,
  `activity_size` int(11) NOT NULL,
  `activity_title` text NOT NULL,
  `activity_vm` tinyint(1) NOT NULL DEFAULT '0',
  `activity_description` text
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `block`
--

CREATE TABLE IF NOT EXISTS `block` (
`block_id` int(11) NOT NULL,
  `block_title` tinytext NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `class`
--

CREATE TABLE IF NOT EXISTS `class` (
  `class_name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `config_key` varchar(255) NOT NULL,
  `config_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `prenotazioni`
--

CREATE TABLE IF NOT EXISTS `prenotazioni` (
`pren_id` int(11) NOT NULL,
  `pren_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pren_user` int(11) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `prenotazioni_attivita`
--

CREATE TABLE IF NOT EXISTS `prenotazioni_attivita` (
`prenact_id` int(11) NOT NULL,
  `prenact_prenotation` int(11) NOT NULL,
  `prenact_activity` int(11) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `user`
--

CREATE TABLE IF NOT EXISTS `user` (
`user_id` int(11) NOT NULL,
  `user_name` tinytext NOT NULL,
  `user_surname` tinytext NOT NULL,
  `user_class` varchar(20) NOT NULL,
  `user_pren_latest` int(11) DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity`
--
ALTER TABLE `activity`
 ADD UNIQUE KEY `id` (`activity_id`), ADD KEY `activity_time` (`activity_time`);

--
-- Indexes for table `block`
--
ALTER TABLE `block`
 ADD PRIMARY KEY (`block_id`);

--
-- Indexes for table `class`
--
ALTER TABLE `class`
 ADD PRIMARY KEY (`class_name`);

--
-- Indexes for table `config`
--
ALTER TABLE `config`
 ADD PRIMARY KEY (`config_key`);

--
-- Indexes for table `prenotazioni`
--
ALTER TABLE `prenotazioni`
 ADD PRIMARY KEY (`pren_id`), ADD KEY `pren_user` (`pren_user`);

--
-- Indexes for table `prenotazioni_attivita`
--
ALTER TABLE `prenotazioni_attivita`
 ADD PRIMARY KEY (`prenact_id`), ADD KEY `prenact_prenotation` (`prenact_prenotation`), ADD KEY `prenact_activity` (`prenact_activity`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
 ADD PRIMARY KEY (`user_id`), ADD KEY `user_class` (`user_class`), ADD KEY `user_pren_latest` (`user_pren_latest`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity`
--
ALTER TABLE `activity`
MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=121;
--
-- AUTO_INCREMENT for table `block`
--
ALTER TABLE `block`
MODIFY `block_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `prenotazioni`
--
ALTER TABLE `prenotazioni`
MODIFY `pren_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=37;
--
-- AUTO_INCREMENT for table `prenotazioni_attivita`
--
ALTER TABLE `prenotazioni_attivita`
MODIFY `prenact_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=88;
--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=37;
--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `activity`
--
ALTER TABLE `activity`
ADD CONSTRAINT `activity_block` FOREIGN KEY (`activity_time`) REFERENCES `block` (`block_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `prenotazioni`
--
ALTER TABLE `prenotazioni`
ADD CONSTRAINT `pren_user` FOREIGN KEY (`pren_user`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `prenotazioni_attivita`
--
ALTER TABLE `prenotazioni_attivita`
ADD CONSTRAINT `prenact_activity` FOREIGN KEY (`prenact_activity`) REFERENCES `activity` (`activity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `prenact_pren` FOREIGN KEY (`prenact_prenotation`) REFERENCES `prenotazioni` (`pren_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `user`
--
ALTER TABLE `user`
ADD CONSTRAINT `user_class` FOREIGN KEY (`user_class`) REFERENCES `class` (`class_name`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `user_pren_latest` FOREIGN KEY (`user_pren_latest`) REFERENCES `prenotazioni` (`pren_id`) ON DELETE SET NULL ON UPDATE SET NULL;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
