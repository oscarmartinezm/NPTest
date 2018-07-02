-- Creates Database
DROP DATABASE IF EXISTS `netpay`;
CREATE DATABASE `netpay`;

-- Creates tables
USE `netpay`;
DROP TABLE IF EXISTS `filesystem`;
CREATE TABLE `filesystem` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `parent` int(11) unsigned DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `complete_path` text,
  `insert_on` datetime DEFAULT NULL,
  `update_on` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `filesystem_id_parent_idx` (`parent`),
  KEY `parent_id_idx` (`parent`),
  CONSTRAINT `parent_id` FOREIGN KEY (`parent`) REFERENCES `filesystem` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
