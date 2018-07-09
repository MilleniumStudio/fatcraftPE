/*
SQLyog Ultimate v12.09 (64 bit)
MySQL - 5.7.20-0ubuntu0.16.04.1 : Database - fatcraft_pe
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`fatcraft_pe` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `fatcraft_pe`;

/*Table structure for table `StatsPE` */

DROP TABLE IF EXISTS `StatsPE`;

CREATE TABLE `StatsPE` (
  `Username` varchar(16) NOT NULL,
  `Online` bit(1) NOT NULL DEFAULT b'0',
  `ClientID` varchar(255) NOT NULL DEFAULT 'undefined',
  `LastIP` varchar(255) NOT NULL DEFAULT 'undefined',
  `UUID` varchar(255) NOT NULL DEFAULT 'undefined',
  `FirstJoin` decimal(65,3) NOT NULL DEFAULT '0.000',
  `LastJoin` decimal(65,3) NOT NULL DEFAULT '0.000',
  `JoinCount` bigint(255) NOT NULL DEFAULT '1',
  `KillCount` bigint(255) NOT NULL DEFAULT '0',
  `DeathCount` bigint(255) NOT NULL DEFAULT '0',
  `OnlineTime` bigint(255) NOT NULL DEFAULT '0',
  `BlockBreakCount` bigint(255) NOT NULL DEFAULT '0',
  `BlockPlaceCount` bigint(255) NOT NULL DEFAULT '0',
  `ChatCount` bigint(255) NOT NULL DEFAULT '0',
  `ItemConsumeCount` bigint(255) NOT NULL DEFAULT '0',
  `ItemCraftCount` bigint(255) NOT NULL DEFAULT '0',
  `ItemDropCount` bigint(255) NOT NULL DEFAULT '0',
  `Money` bigint(255) NOT NULL DEFAULT '0',
  `XP` bigint(255) NOT NULL DEFAULT '0',
  `pk_played` bigint(255) NOT NULL DEFAULT '0',
  `pk_XP` bigint(255) NOT NULL DEFAULT '0',
  `bw_played` bigint(255) NOT NULL DEFAULT '0',
  `bw_XP` bigint(255) NOT NULL DEFAULT '0',
  `hg_played` bigint(255) NOT NULL DEFAULT '0',
  `hg_XP` bigint(255) NOT NULL DEFAULT '0',
  `sw_played` bigint(255) NOT NULL DEFAULT '0',
  `sw_XP` bigint(255) NOT NULL DEFAULT '0',
  `br_played` bigint(255) NOT NULL DEFAULT '0',
  `br_XP` bigint(255) NOT NULL DEFAULT '0',
  `md_played` bigint(255) NOT NULL DEFAULT '0',
  `md_XP` bigint(255) NOT NULL DEFAULT '0',
  UNIQUE KEY `Username` (`Username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `bans` */

DROP TABLE IF EXISTS `bans`;

CREATE TABLE `bans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_uuid` varchar(36) DEFAULT NULL,
  `player_ip` varchar(15) DEFAULT NULL,
  `expiration_date` timestamp NULL DEFAULT NULL,
  `creation_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `reason` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_expiration_date` (`expiration_date`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `chrono_scores` */

DROP TABLE IF EXISTS `chrono_scores`;

CREATE TABLE `chrono_scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `xuid` varchar(255) NOT NULL,
  `player_name` varchar(255) NOT NULL,
  `map_name` varchar(255) NOT NULL,
  `time` float NOT NULL DEFAULT '0',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

/*Table structure for table `games` */

DROP TABLE IF EXISTS `games`;

CREATE TABLE `games` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `launch` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `start` timestamp NULL DEFAULT NULL,
  `end` timestamp NULL DEFAULT NULL,
  `end_cause` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9975 DEFAULT CHARSET=latin1;

/*Table structure for table `games_data` */

DROP TABLE IF EXISTS `games_data`;

CREATE TABLE `games_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `game_id` int(11) NOT NULL,
  `event` varchar(20) NOT NULL,
  `player` varchar(36) DEFAULT NULL,
  `data` text,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8579 DEFAULT CHARSET=latin1;

/*Table structure for table `player_connected` */

DROP TABLE IF EXISTS `player_connected`;

CREATE TABLE `player_connected` (
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `player_number` int(11) NOT NULL,
  PRIMARY KEY (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `player_gift` */

DROP TABLE IF EXISTS `player_gift`;

CREATE TABLE `player_gift` (
  `name` char(50) NOT NULL,
  `gift_type` varchar(100) NOT NULL,
  PRIMARY KEY (`name`,`gift_type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `players` */

DROP TABLE IF EXISTS `players`;

CREATE TABLE `players` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) NOT NULL,
  `xuid` varchar(36) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `fsaccount` varchar(50) DEFAULT NULL,
  `lang` int(3) NOT NULL DEFAULT '0' COMMENT '0 en, 1 fr, 2 es',
  `permission_group` varchar(50) DEFAULT NULL,
  `join_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fatsilver` int(11) DEFAULT '0',
  `fatgold` int(11) DEFAULT '0',
  `muted` int(11) DEFAULT NULL,
  `shop_possessed` text,
  `shop_equipped` text,
  `kit_items` varchar(1000) NOT NULL DEFAULT '[]',
  `level` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

/*Table structure for table `players_connection_log` */

DROP TABLE IF EXISTS `players_connection_log`;

CREATE TABLE `players_connection_log` (
  `player_uuid` varchar(255) DEFAULT NULL,
  `player_name` varchar(255) DEFAULT NULL,
  `server_type` varchar(255) DEFAULT NULL,
  `time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `players_on_servers` */

DROP TABLE IF EXISTS `players_on_servers`;

CREATE TABLE `players_on_servers` (
  `name` char(50) DEFAULT NULL,
  `uuid` char(36) NOT NULL,
  `sid` char(36) DEFAULT NULL,
  `ip` varchar(63) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `pocketvote_checks` */

DROP TABLE IF EXISTS `pocketvote_checks`;

CREATE TABLE `pocketvote_checks` (
  `server_hash` varchar(255) NOT NULL,
  `vote_id` int(10) unsigned NOT NULL DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `server_hash` (`server_hash`),
  KEY `FK_vote_id_pocketvote_votes` (`vote_id`),
  CONSTRAINT `FK_vote_id_pocketvote_votes` FOREIGN KEY (`vote_id`) REFERENCES `pocketvote_votes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `pocketvote_votes` */

DROP TABLE IF EXISTS `pocketvote_votes`;

CREATE TABLE `pocketvote_votes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `player` varchar(50) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `site` varchar(255) DEFAULT NULL,
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `scores` */

DROP TABLE IF EXISTS `scores`;

CREATE TABLE `scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `game` int(11) NOT NULL DEFAULT '0',
  `player` varchar(36) NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  `data` text,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `map` varchar(255) DEFAULT '""',
  `time` float DEFAULT '0',
  `serverType` varchar(255) NOT NULL DEFAULT '""',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=989 DEFAULT CHARSET=latin1;

/*Table structure for table `servers` */

DROP TABLE IF EXISTS `servers`;

CREATE TABLE `servers` (
  `sid` char(36) NOT NULL,
  `type` varchar(63) DEFAULT NULL,
  `id` int(11) DEFAULT NULL,
  `name` varchar(25) DEFAULT NULL,
  `ip` varchar(63) DEFAULT NULL,
  `port` smallint(6) DEFAULT NULL,
  `status` varchar(63) DEFAULT NULL,
  `online` smallint(6) DEFAULT NULL,
  `max` smallint(6) DEFAULT NULL,
  `laston` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`sid`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `shop_history` */

DROP TABLE IF EXISTS `shop_history`;

CREATE TABLE `shop_history` (
  `uuid` varchar(36) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `item` varchar(255) DEFAULT NULL,
  `spentFS` int(11) DEFAULT '0',
  `spentFG` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `simpleauth_players` */

DROP TABLE IF EXISTS `simpleauth_players`;

CREATE TABLE `simpleauth_players` (
  `email` varchar(16) NOT NULL,
  `password` char(128) DEFAULT NULL,
  `registerdate` int(11) DEFAULT NULL,
  `logindate` int(11) DEFAULT NULL,
  `lastip` varchar(50) DEFAULT NULL,
  `ip` varchar(50) DEFAULT NULL,
  `cid` bigint(20) DEFAULT NULL,
  `skinhash` varchar(50) DEFAULT NULL,
  `pin` int(11) DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
