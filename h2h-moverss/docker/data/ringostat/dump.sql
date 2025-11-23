-- MySQL dump 10.13  Distrib 8.0.42, for Linux (x86_64)
--
-- Host: 192.168.189.1    Database: ringostat_db
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
-- Table structure for table `event_after_call`
--

DROP TABLE IF EXISTS `event_after_call`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_after_call` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(10) unsigned DEFAULT NULL,
  `call_id` varchar(50) DEFAULT NULL,
  `type` varchar(10) DEFAULT NULL,
  `scheme_name` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `destination` varchar(20) DEFAULT NULL,
  `number_e164` varchar(100) DEFAULT NULL,
  `caller_number` varchar(20) DEFAULT NULL,
  `employee` varchar(100) DEFAULT NULL,
  `employee_estension` varchar(10) DEFAULT NULL,
  `employee_id` varchar(20) DEFAULT NULL,
  `recording_presence` tinyint(1) DEFAULT NULL,
  `recording` varchar(255) DEFAULT NULL,
  `recording_wav` varchar(255) DEFAULT NULL,
  `duration_call` int(10) unsigned DEFAULT NULL,
  `duration_conversation` int(10) unsigned DEFAULT NULL,
  `duration_waiting` int(10) unsigned DEFAULT NULL,
  `call_date` datetime DEFAULT NULL,
  `call_timestamp` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `dialogue_quality_score` decimal(10,2) DEFAULT NULL,
  `dialogue_quality_details` text DEFAULT NULL,
  `call_card_link` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`,`call_timestamp`),
  KEY `type` (`type`,`status`,`destination`,`caller_number`)
) ENGINE=InnoDB AUTO_INCREMENT=74060 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `event_before_call`
--

DROP TABLE IF EXISTS `event_before_call`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_before_call` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(10) unsigned DEFAULT NULL,
  `call_type` varchar(10) DEFAULT NULL,
  `call_date_microsecond` bigint(20) unsigned DEFAULT NULL,
  `destination` varchar(50) DEFAULT NULL,
  `number_e164` varchar(50) DEFAULT NULL,
  `callers_number` varchar(50) DEFAULT NULL,
  `employee_ringostat_id` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `client_id` int(10) unsigned DEFAULT NULL,
  `call_date` datetime DEFAULT NULL,
  `call_id` varchar(50) DEFAULT NULL,
  `extension_number` varchar(20) DEFAULT NULL,
  `responsible_employees` varchar(191) DEFAULT NULL,
  `from_event` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43173 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-07-01 14:21:42
