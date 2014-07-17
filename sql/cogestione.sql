-- phpMyAdmin SQL Dump
-- version 4.1.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Lug 17, 2014 alle 17:03
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
  `activity_id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_time` int(11) NOT NULL,
  `activity_size` int(11) NOT NULL,
  `activity_title` text NOT NULL,
  `activity_vm` tinyint(1) NOT NULL DEFAULT '0',
  `activity_description` text,
  UNIQUE KEY `id` (`activity_id`),
  KEY `activity_time` (`activity_time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `block`
--

CREATE TABLE IF NOT EXISTS `block` (
  `block_id` int(11) NOT NULL AUTO_INCREMENT,
  `block_title` tinytext NOT NULL,
  PRIMARY KEY (`block_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `class`
--

CREATE TABLE IF NOT EXISTS `class` (
  `class_name` varchar(20) NOT NULL,
  PRIMARY KEY (`class_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `config_key` varchar(255) NOT NULL,
  `config_value` text NOT NULL,
  PRIMARY KEY (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `prenotazioni`
--

CREATE TABLE IF NOT EXISTS `prenotazioni` (
  `pren_id` int(11) NOT NULL AUTO_INCREMENT,
  `pren_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pren_user` int(11) NOT NULL,
  PRIMARY KEY (`pren_id`),
  KEY `pren_user` (`pren_user`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `prenotazioni_attivita`
--

CREATE TABLE IF NOT EXISTS `prenotazioni_attivita` (
  `prenact_id` int(11) NOT NULL AUTO_INCREMENT,
  `prenact_prenotation` int(11) NOT NULL,
  `prenact_activity` int(11) NOT NULL,
  PRIMARY KEY (`prenact_id`),
  KEY `prenact_prenotation` (`prenact_prenotation`),
  KEY `prenact_activity` (`prenact_activity`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` tinytext NOT NULL,
  `user_surname` tinytext NOT NULL,
  `user_class` varchar(20) NOT NULL,
  `user_pren_latest` int(11) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  KEY `user_class` (`user_class`),
  KEY `user_pren_latest` (`user_pren_latest`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

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
  ADD CONSTRAINT `user_pren_latest` FOREIGN KEY (`user_pren_latest`) REFERENCES `prenotazioni` (`pren_id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `user_class` FOREIGN KEY (`user_class`) REFERENCES `class` (`class_name`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
