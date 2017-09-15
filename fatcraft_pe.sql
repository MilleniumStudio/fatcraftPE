/*
SQLyog Ultimate v11.11 (64 bit)
MySQL - 5.6.33 : Database - fatcraft_pe
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
  UNIQUE KEY `Username` (`Username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `StatsPE` */

insert  into `StatsPE`(`Username`,`Online`,`ClientID`,`LastIP`,`UUID`,`FirstJoin`,`LastJoin`,`JoinCount`,`KillCount`,`DeathCount`,`OnlineTime`,`BlockBreakCount`,`BlockPlaceCount`,`ChatCount`,`ItemConsumeCount`,`ItemCraftCount`,`ItemDropCount`,`Money`,`XP`,`pk_played`,`pk_XP`,`bw_played`,`bw_XP`,`hg_played`,`hg_XP`,`sw_played`,`sw_XP`) values ('BaguetteJambon','\0','-6723944035592971113','192.168.4.63','6824bada-ed61-3fc0-8ea3-a8e04465bb55',1505481945.243,1505481945.243,137,0,18,1601,24,52,0,0,0,7,410,210,1,50,0,0,2,100,0,0),('DkHeaven','','3764146711125473012','192.168.4.14','80416bf4-4ee0-3a9b-ac52-b503531f88ef',1505481582.906,1505481582.906,22,4,5,868,0,0,0,3,0,1,300,300,0,0,0,0,3,300,0,0),('Naphtale','','7173555204555630887','192.168.4.43','b9ec9ae2-3783-3f5c-95cf-8c01d459f69a',1505410301.177,1505410301.177,13,3,3,598,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,0),('Unikazz','\0','-540845177657278718','192.168.4.12','706ece23-2518-3765-9e61-d67bdaf0c020',1505482733.342,1505482733.342,3,0,1,3,0,0,0,0,0,0,250,50,1,50,0,0,0,0,0,0);

/*Table structure for table `games` */

DROP TABLE IF EXISTS `games`;

CREATE TABLE `games` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(10) NOT NULL,
  `launch` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `start` timestamp NULL DEFAULT NULL,
  `end` timestamp NULL DEFAULT NULL,
  `end_cause` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=latin1;

/*Data for the table `games` */

insert  into `games`(`id`,`type`,`launch`,`start`,`end`,`end_cause`) values (1,'hg','2017-09-15 12:15:39',NULL,NULL,NULL),(2,'hg','2017-09-15 12:17:21',NULL,NULL,NULL),(3,'lobby','2017-09-15 12:17:53',NULL,NULL,NULL),(4,'hg','2017-09-15 12:27:01',NULL,NULL,NULL),(5,'lobby','2017-09-15 12:27:22',NULL,NULL,NULL),(6,'hg','2017-09-15 12:30:59',NULL,NULL,NULL),(7,'lobby','2017-09-15 12:31:12',NULL,NULL,NULL),(8,'hg','2017-09-15 13:40:34',NULL,NULL,NULL),(9,'lobby','2017-09-15 13:41:24',NULL,NULL,NULL),(10,'hg','2017-09-15 13:55:17',NULL,NULL,NULL),(11,'lobby','2017-09-15 13:55:25',NULL,NULL,NULL),(12,'hg','2017-09-15 13:56:52',NULL,NULL,NULL),(13,'lobby','2017-09-15 13:57:12',NULL,NULL,NULL),(14,'hg','2017-09-15 13:59:50',NULL,NULL,NULL),(15,'lobby','2017-09-15 14:00:03',NULL,NULL,NULL),(16,'hg','2017-09-15 14:09:21',NULL,NULL,NULL),(17,'lobby','2017-09-15 14:09:36',NULL,NULL,NULL),(18,'hg','2017-09-15 14:12:39',NULL,NULL,NULL),(19,'hg','2017-09-15 14:17:50',NULL,NULL,NULL),(20,'hg','2017-09-15 14:21:35',NULL,NULL,NULL),(21,'lobby','2017-09-15 14:21:45',NULL,NULL,NULL),(22,'hg','2017-09-15 14:24:41',NULL,NULL,NULL),(23,'hg','2017-09-15 14:26:34','2017-09-15 14:28:22',NULL,NULL),(24,'lobby','2017-09-15 14:27:25',NULL,NULL,NULL),(25,'hg','2017-09-15 14:29:56',NULL,NULL,NULL),(26,'hg','2017-09-15 14:46:54','2017-09-15 14:48:37','2017-09-15 14:50:06','eng_game'),(27,'lobby','2017-09-15 14:47:08',NULL,NULL,NULL),(28,'hg','2017-09-15 14:50:24',NULL,NULL,NULL),(29,'hg','2017-09-15 14:57:37','2017-09-15 14:58:35','2017-09-15 14:59:46','eng_game'),(30,'lobby','2017-09-15 14:58:04',NULL,NULL,NULL),(31,'hg','2017-09-15 15:00:06',NULL,NULL,NULL),(32,'hg','2017-09-15 15:02:13','2017-09-15 15:03:26','2017-09-15 15:04:39','eng_game'),(33,'lobby','2017-09-15 15:02:37',NULL,NULL,NULL),(34,'hg','2017-09-15 15:04:57',NULL,NULL,NULL),(35,'hg','2017-09-15 15:14:11','2017-09-15 15:15:18','2017-09-15 15:15:58','eng_game'),(36,'lobby','2017-09-15 15:14:40',NULL,NULL,NULL),(37,'hg','2017-09-15 15:16:17',NULL,NULL,NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=latin1;

/*Data for the table `games_data` */

insert  into `games_data`(`id`,`game_id`,`event`,`player`,`data`,`date`) values (1,11,'join','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"ip\":\"192.168.4.63\"}','2017-09-15 13:55:25'),(2,13,'join','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"ip\":\"172.17.0.1\"}','2017-09-15 13:57:12'),(3,15,'join','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"ip\":\"172.17.0.1\"}','2017-09-15 14:00:03'),(4,15,'leave','6824bada-ed61-3fc0-8ea3-a8e04465bb55','','2017-09-15 14:06:53'),(5,14,'join','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"ip\":\"192.168.4.63\"}','2017-09-15 14:06:58'),(6,17,'join','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"ip\":\"172.17.0.1\"}','2017-09-15 14:09:37'),(7,17,'join','80416bf4-4ee0-3a9b-ac52-b503531f88ef','{\"ip\":\"192.168.4.14\"}','2017-09-15 14:11:10'),(8,17,'leave','6824bada-ed61-3fc0-8ea3-a8e04465bb55','','2017-09-15 14:11:18'),(9,16,'join','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"ip\":\"192.168.4.63\"}','2017-09-15 14:11:23'),(10,17,'leave','80416bf4-4ee0-3a9b-ac52-b503531f88ef','','2017-09-15 14:11:24'),(11,16,'join','80416bf4-4ee0-3a9b-ac52-b503531f88ef','{\"ip\":\"192.168.4.14\"}','2017-09-15 14:11:28'),(12,16,'join','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"by\":\"Player(1)\"}','2017-09-15 14:12:20'),(13,16,'join','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"target\":\"BaguetteJambon\"}','2017-09-15 14:12:20'),(14,17,'join','80416bf4-4ee0-3a9b-ac52-b503531f88ef','{\"ip\":\"192.168.4.14\"}','2017-09-15 14:12:33'),(15,17,'join','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"ip\":\"172.17.0.1\"}','2017-09-15 14:12:33'),(16,17,'leave','80416bf4-4ee0-3a9b-ac52-b503531f88ef','','2017-09-15 14:13:53'),(17,18,'join','80416bf4-4ee0-3a9b-ac52-b503531f88ef','{\"ip\":\"192.168.4.14\"}','2017-09-15 14:13:58'),(18,18,'join','80416bf4-4ee0-3a9b-ac52-b503531f88ef','{\"by\":\"Player(1)\"}','2017-09-15 14:17:29'),(19,18,'join','80416bf4-4ee0-3a9b-ac52-b503531f88ef','{\"target\":\"DkHeaven\"}','2017-09-15 14:17:29'),(20,17,'join','80416bf4-4ee0-3a9b-ac52-b503531f88ef','{\"ip\":\"192.168.4.14\"}','2017-09-15 14:17:41'),(21,17,'leave','80416bf4-4ee0-3a9b-ac52-b503531f88ef','','2017-09-15 14:19:56'),(22,21,'join','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"ip\":\"172.17.0.1\"}','2017-09-15 14:21:45'),(23,21,'leave','6824bada-ed61-3fc0-8ea3-a8e04465bb55','','2017-09-15 14:22:00'),(24,20,'join','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"ip\":\"192.168.4.63\"}','2017-09-15 14:22:04'),(25,21,'join','80416bf4-4ee0-3a9b-ac52-b503531f88ef','{\"ip\":\"192.168.4.14\"}','2017-09-15 14:22:42'),(26,21,'leave','80416bf4-4ee0-3a9b-ac52-b503531f88ef','','2017-09-15 14:22:54'),(27,20,'join','80416bf4-4ee0-3a9b-ac52-b503531f88ef','{\"ip\":\"192.168.4.14\"}','2017-09-15 14:22:58'),(28,20,'kill','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"target\":\"BaguetteJambon\"}','2017-09-15 14:24:23'),(29,20,'death','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"by\":\"Player(1)\"}','2017-09-15 14:24:23'),(30,21,'join','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"ip\":\"172.17.0.1\"}','2017-09-15 14:24:36'),(31,21,'join','80416bf4-4ee0-3a9b-ac52-b503531f88ef','{\"ip\":\"192.168.4.14\"}','2017-09-15 14:24:36'),(32,24,'join','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"ip\":\"172.17.0.1\"}','2017-09-15 14:27:25'),(33,24,'leave','6824bada-ed61-3fc0-8ea3-a8e04465bb55','','2017-09-15 14:27:35'),(34,23,'join','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"ip\":\"192.168.4.63\"}','2017-09-15 14:27:40'),(35,24,'join','80416bf4-4ee0-3a9b-ac52-b503531f88ef','{\"ip\":\"192.168.4.14\"}','2017-09-15 14:27:48'),(36,24,'leave','80416bf4-4ee0-3a9b-ac52-b503531f88ef','','2017-09-15 14:28:03'),(37,23,'join','80416bf4-4ee0-3a9b-ac52-b503531f88ef','{\"ip\":\"192.168.4.14\"}','2017-09-15 14:28:07'),(38,23,'death','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"by\":\"Player(1)\"}','2017-09-15 14:29:36'),(39,23,'kill','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"target\":\"BaguetteJambon\"}','2017-09-15 14:29:36'),(40,23,'board',NULL,'[]','2017-09-15 14:29:36'),(41,24,'join','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"ip\":\"172.17.0.1\"}','2017-09-15 14:29:48'),(42,24,'join','80416bf4-4ee0-3a9b-ac52-b503531f88ef','{\"ip\":\"192.168.4.14\"}','2017-09-15 14:29:49'),(43,27,'join','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"ip\":\"192.168.4.63\"}','2017-09-15 14:47:08'),(44,27,'leave','6824bada-ed61-3fc0-8ea3-a8e04465bb55','','2017-09-15 14:47:15'),(45,26,'join','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"ip\":\"192.168.4.63\"}','2017-09-15 14:47:20'),(46,27,'join','80416bf4-4ee0-3a9b-ac52-b503531f88ef','{\"ip\":\"192.168.4.14\"}','2017-09-15 14:48:08'),(47,27,'leave','80416bf4-4ee0-3a9b-ac52-b503531f88ef','','2017-09-15 14:48:17'),(48,26,'join','80416bf4-4ee0-3a9b-ac52-b503531f88ef','{\"ip\":\"192.168.4.14\"}','2017-09-15 14:48:22'),(49,26,'death','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"by\":\"Player(1)\"}','2017-09-15 14:50:06'),(50,26,'kill','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"target\":\"BaguetteJambon\"}','2017-09-15 14:50:06'),(51,26,'board',NULL,'[]','2017-09-15 14:50:06'),(52,27,'join','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"ip\":\"192.168.4.63\"}','2017-09-15 14:50:18'),(53,27,'join','80416bf4-4ee0-3a9b-ac52-b503531f88ef','{\"ip\":\"192.168.4.14\"}','2017-09-15 14:50:18'),(54,30,'join','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"ip\":\"192.168.4.63\"}','2017-09-15 14:58:04'),(55,30,'join','80416bf4-4ee0-3a9b-ac52-b503531f88ef','{\"ip\":\"192.168.4.14\"}','2017-09-15 14:58:10'),(56,30,'leave','6824bada-ed61-3fc0-8ea3-a8e04465bb55','','2017-09-15 14:58:10'),(57,29,'join','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"ip\":\"192.168.4.63\"}','2017-09-15 14:58:15'),(58,30,'leave','80416bf4-4ee0-3a9b-ac52-b503531f88ef','','2017-09-15 14:58:15'),(59,29,'join','80416bf4-4ee0-3a9b-ac52-b503531f88ef','{\"ip\":\"192.168.4.14\"}','2017-09-15 14:58:20'),(60,29,'kill','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"target\":\"BaguetteJambon\"}','2017-09-15 14:59:46'),(61,29,'death','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"by\":\"Player(1)\"}','2017-09-15 14:59:46'),(62,29,'board',NULL,'[\"DkHeaven\"]','2017-09-15 14:59:46'),(63,30,'join','80416bf4-4ee0-3a9b-ac52-b503531f88ef','{\"ip\":\"192.168.4.14\"}','2017-09-15 14:59:59'),(64,30,'join','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"ip\":\"192.168.4.63\"}','2017-09-15 14:59:59'),(65,33,'join','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"ip\":\"192.168.4.63\"}','2017-09-15 15:02:37'),(66,33,'join','80416bf4-4ee0-3a9b-ac52-b503531f88ef','{\"ip\":\"192.168.4.14\"}','2017-09-15 15:02:55'),(67,33,'leave','80416bf4-4ee0-3a9b-ac52-b503531f88ef','','2017-09-15 15:03:05'),(68,33,'leave','6824bada-ed61-3fc0-8ea3-a8e04465bb55','','2017-09-15 15:03:07'),(69,32,'join','80416bf4-4ee0-3a9b-ac52-b503531f88ef','{\"ip\":\"192.168.4.14\"}','2017-09-15 15:03:09'),(70,32,'join','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"ip\":\"192.168.4.63\"}','2017-09-15 15:03:11'),(71,32,'death','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"by\":\"Player(2)\"}','2017-09-15 15:04:39'),(72,32,'kill','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"target\":\"BaguetteJambon\"}','2017-09-15 15:04:39'),(73,32,'board',NULL,'[\"DkHeaven\",\"BaguetteJambon\"]','2017-09-15 15:04:39'),(74,33,'join','80416bf4-4ee0-3a9b-ac52-b503531f88ef','{\"ip\":\"192.168.4.14\"}','2017-09-15 15:04:51'),(75,33,'join','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"ip\":\"192.168.4.63\"}','2017-09-15 15:04:52'),(76,36,'join','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"ip\":\"192.168.4.63\"}','2017-09-15 15:14:40'),(77,36,'join','80416bf4-4ee0-3a9b-ac52-b503531f88ef','{\"ip\":\"192.168.4.14\"}','2017-09-15 15:14:45'),(78,36,'leave','6824bada-ed61-3fc0-8ea3-a8e04465bb55','','2017-09-15 15:14:59'),(79,36,'leave','80416bf4-4ee0-3a9b-ac52-b503531f88ef','','2017-09-15 15:15:00'),(80,35,'join','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"ip\":\"192.168.4.63\"}','2017-09-15 15:15:03'),(81,35,'join','80416bf4-4ee0-3a9b-ac52-b503531f88ef','{\"ip\":\"192.168.4.14\"}','2017-09-15 15:15:04'),(82,35,'death','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"by\":\"Player(1)\"}','2017-09-15 15:15:58'),(83,35,'kill','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"target\":\"BaguetteJambon\"}','2017-09-15 15:15:58'),(84,35,'board',NULL,'[\"DkHeaven\",\"BaguetteJambon\"]','2017-09-15 15:15:58'),(85,36,'join','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"ip\":\"192.168.4.63\"}','2017-09-15 15:16:11'),(86,36,'leave','6824bada-ed61-3fc0-8ea3-a8e04465bb55','','2017-09-15 15:17:21'),(87,36,'join','80416bf4-4ee0-3a9b-ac52-b503531f88ef','{\"ip\":\"192.168.4.14\"}','2017-09-15 15:19:37'),(88,36,'leave','80416bf4-4ee0-3a9b-ac52-b503531f88ef','','2017-09-15 15:19:41'),(89,36,'join','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"ip\":\"192.168.4.63\"}','2017-09-15 15:19:41'),(90,36,'leave','6824bada-ed61-3fc0-8ea3-a8e04465bb55','','2017-09-15 15:20:00'),(91,36,'join','706ece23-2518-3765-9e61-d67bdaf0c020','{\"ip\":\"192.168.4.12\"}','2017-09-15 15:20:52'),(92,36,'leave','706ece23-2518-3765-9e61-d67bdaf0c020','','2017-09-15 15:20:58'),(93,36,'join','6824bada-ed61-3fc0-8ea3-a8e04465bb55','{\"ip\":\"192.168.4.63\"}','2017-09-15 15:25:47'),(94,36,'join','706ece23-2518-3765-9e61-d67bdaf0c020','{\"ip\":\"192.168.4.12\"}','2017-09-15 15:38:55'),(95,36,'leave','706ece23-2518-3765-9e61-d67bdaf0c020','','2017-09-15 15:56:06');

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

/*Data for the table `players_on_servers` */

insert  into `players_on_servers`(`name`,`uuid`,`sid`,`ip`,`updated`) values ('BaguetteJambon','6824bada-ed61-3fc0-8ea3-a8e04465bb55','89999018-2b55-3a8f-7757-2561c43d3e30','192.168.4.63','2017-09-15 15:25:47'),('DkHeaven','80416bf4-4ee0-3a9b-ac52-b503531f88ef','73b93c1c-5476-de2c-8246-5dd29129b177','192.168.4.14','2017-09-15 15:19:45');

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

/*Data for the table `pocketvote_checks` */

insert  into `pocketvote_checks`(`server_hash`,`vote_id`,`timestamp`) values ('8b9810689c7f8421e4298439000c8ff1',1,1505309885),('76d82d08239e3bfa8be355bd62681546',1,1505309886);

/*Table structure for table `pocketvote_votes` */

DROP TABLE IF EXISTS `pocketvote_votes`;

CREATE TABLE `pocketvote_votes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `player` varchar(50) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `site` varchar(255) DEFAULT NULL,
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

/*Data for the table `pocketvote_votes` */

insert  into `pocketvote_votes`(`id`,`player`,`ip`,`site`,`timestamp`) values (1,NULL,NULL,NULL,1505138922);

/*Table structure for table `servers` */

DROP TABLE IF EXISTS `servers`;

CREATE TABLE `servers` (
  `sid` char(36) NOT NULL,
  `type` varchar(63) DEFAULT NULL,
  `id` int(11) DEFAULT NULL,
  `ip` varchar(63) DEFAULT NULL,
  `port` smallint(6) DEFAULT NULL,
  `status` varchar(63) DEFAULT NULL,
  `online` smallint(6) DEFAULT NULL,
  `max` smallint(6) DEFAULT NULL,
  `laston` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `servers` */

insert  into `servers`(`sid`,`type`,`id`,`ip`,`port`,`status`,`online`,`max`,`laston`) values ('0cc2f87c-c761-d69c-7189-17b38b389d52','lb',1,'192.168.4.10',19132,'open',0,1,'2017-09-15 16:01:05'),('73b93c1c-5476-de2c-8246-5dd29129b177','pk',1,'192.168.4.10',19135,'open',1,20,'2017-09-15 16:01:04'),('89999018-2b55-3a8f-7757-2561c43d3e30','lobby',1,'192.168.4.10',19133,'open',1,20,'2017-09-15 16:01:05'),('a24cef45-60fe-eb85-2149-79d87caad289','hg',1,'192.168.4.10',19134,'open',0,20,'2017-09-15 16:01:05');

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

/*Data for the table `simpleauth_players` */

insert  into `simpleauth_players`(`email`,`password`,`registerdate`,`logindate`,`lastip`,`ip`,`cid`,`skinhash`,`pin`) values ('baguettejambon','0597cd20887ae9c4fdad74b5ef36472e7f5dce533734fd23f01999cafe732afc92977bbaff09a71625e409773e28f0fbaabad3da0f368a9c26d3d7b6752447fe',1504883712,1504883712,'6824bada-ed61-3fc0-8ea3-a8e04465bb55','192.168.4.32',6408947806708983738,'6a75f9703cd9d50461eb6f50356b0d99',0),('dkheaven','a9246ce0858dca91a1e8b07990a9d0b078c77494edf14b2c21a98f97188bc0a5b3fdf73420c118a04b3ad9e354d849950a8ec35602e9f1c4ac13b2464126cd69',1505405572,1505405572,'80416bf4-4ee0-3a9b-ac52-b503531f88ef','192.168.4.14',3764146711125473012,'1c3aad4973b59964f42b885c0f24f2b3',0),('naphtale','af34b1868bae15c0bac8e25fac44f0e0f21af78910a9a10d355efb592c8433201be64659ca0d7d3be42527adc9ce173d3c5561f8bc2c74cd4dcd220006f80b45',1505407808,1505407808,'b9ec9ae2-3783-3f5c-95cf-8c01d459f69a','192.168.4.43',7173555204555630887,'76d5e40f55bae256e710f355a4a1f6a9',0),('nyhven','89a57aed9430cfc34525f7f06fbe154a1c967d966b75f76fb5a206b5bd71594afac29651ac4cc173ee5bb97531191eaa9f0314af622701a9968873523e57285c',1504878972,1504878972,'2837c000-0f8d-301f-9890-595a5ed065b4','192.168.4.11',-1641603473279785692,'2ac814dd40c4fb37acab3f49e3623967',0),('unikazz','abeacb21a54bdd3b4192d157ea6abf1e0d07a7cdf82994b4b820d562656fcf2d2295ba088416bb682d406da500caf73b5bb8b89c3a8062c96b703cf479ae382c',1505140244,1505140244,'706ece23-2518-3765-9e61-d67bdaf0c020','192.168.4.12',-540845177657278718,'5214c72cbe3e22e5f45dad447b4980d4',0);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
