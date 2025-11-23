-- MySQL dump 10.13  Distrib 8.0.39, for Linux (x86_64)
--
-- Host: 192.168.189.1    Database: mailbox_db
-- ------------------------------------------------------
-- Server version	5.5.5-10.3.38-MariaDB-1:10.3.38+maria~ubu2004

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `gmail_messages`
--

DROP TABLE IF EXISTS `gmail_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gmail_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int(10) unsigned NOT NULL,
  `msg_id` varchar(64) NOT NULL,
  `thread_id` varchar(64) DEFAULT NULL,
  `history_id` varchar(64) DEFAULT NULL,
  `tags` varchar(150) NOT NULL,
  `tag` enum('inbox','draft','sent','spam','trash') NOT NULL COMMENT 'Main Folder',
  `subject` varchar(1598) DEFAULT NULL,
  `miscs` text NOT NULL,
  `created_at` datetime NOT NULL COMMENT 'GTM',
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `thread_id` (`thread_id`),
  KEY `tag` (`tag`),
  KEY `account_id` (`account_id`),
  KEY `updated_at` (`updated_at`),
  KEY `created_at` (`created_at`),
  KEY `msg_id` (`msg_id`)
) ENGINE=InnoDB AUTO_INCREMENT=273687 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Meta писем';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gmail_messages_data`
--

DROP TABLE IF EXISTS `gmail_messages_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gmail_messages_data` (
  `message_id` bigint(20) unsigned NOT NULL,
  `text` mediumtext NOT NULL,
  PRIMARY KEY (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPRESSED;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-08-30 10:54:31
