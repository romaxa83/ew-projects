-- MySQL dump 10.13  Distrib 8.0.42, for Linux (x86_64)
--
-- Host: 192.168.189.1    Database: db
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
-- Table structure for table `attachments`
--

DROP TABLE IF EXISTS `attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attachments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `hash` varchar(64) NOT NULL,
  `miscs` varchar(400) DEFAULT NULL,
  `description` tinytext DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `entity_type` varchar(191) DEFAULT NULL,
  `entity_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_at` (`created_at`),
  KEY `hash` (`hash`),
  KEY `miscs` (`miscs`),
  KEY `idx_attachment_entity` (`entity_type`,`entity_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15221 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `authorize_accounts`
--

DROP TABLE IF EXISTS `authorize_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `authorize_accounts` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `division_id` tinyint(4) NOT NULL,
  `payment_account_id` tinyint(4) DEFAULT NULL COMMENT 'Add transaction to payment account',
  `title` varchar(50) NOT NULL,
  `login` varchar(32) NOT NULL,
  `transactionKey` varchar(64) NOT NULL,
  `active` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `authorize_transactions`
--

DROP TABLE IF EXISTS `authorize_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `authorize_transactions` (
  `id` bigint(20) unsigned NOT NULL,
  `account_id` tinyint(3) unsigned NOT NULL,
  `status` varchar(35) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `miscs` text DEFAULT NULL COMMENT 'JSON',
  `submitTimeUTC` datetime NOT NULL COMMENT 'Дата транзакции',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`),
  KEY `submitTimeUTC` (`submitTimeUTC`,`updated_at`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `building_types`
--

DROP TABLE IF EXISTS `building_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `building_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(80) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deleted_at` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cash_registries`
--

DROP TABLE IF EXISTS `cash_registries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cash_registries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `cash_on_hand` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cash_registry_items`
--

DROP TABLE IF EXISTS `cash_registry_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cash_registry_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cash_registry_id` int(11) NOT NULL,
  `executor_id` int(11) DEFAULT NULL,
  `type` varchar(40) NOT NULL,
  `sum` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `insert_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `lname` varchar(80) DEFAULT NULL,
  `ext_id` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deleted_at` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=79636 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clients_2_tags`
--

DROP TABLE IF EXISTS `clients_2_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients_2_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `employee_name` varchar(191) DEFAULT NULL,
  `attached_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6444 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clients_activities`
--

DROP TABLE IF EXISTS `clients_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients_activities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(10) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `type` varchar(70) NOT NULL,
  `miscs` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`client_id`),
  KEY `client_id` (`client_id`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=217207 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clients_communications_ignore`
--

DROP TABLE IF EXISTS `clients_communications_ignore`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients_communications_ignore` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(70) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`,`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clients_emails`
--

DROP TABLE IF EXISTS `clients_emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients_emails` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(10) unsigned NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `sort` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `value` varchar(60) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`client_id`),
  KEY `deleted_at` (`deleted_at`),
  KEY `client_id` (`client_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB AUTO_INCREMENT=78546 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clients_messengers`
--

DROP TABLE IF EXISTS `clients_messengers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients_messengers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(10) unsigned NOT NULL,
  `value` varchar(60) NOT NULL,
  `type_id` tinyint(3) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`client_id`),
  KEY `deleted_at` (`deleted_at`),
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clients_messengers_types`
--

DROP TABLE IF EXISTS `clients_messengers_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients_messengers_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `sort` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deleted_at` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clients_notes`
--

DROP TABLE IF EXISTS `clients_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients_notes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(10) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `value` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`client_id`),
  KEY `deleted_at` (`deleted_at`),
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=28746 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clients_phones`
--

DROP TABLE IF EXISTS `clients_phones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients_phones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(10) unsigned NOT NULL,
  `type_id` tinyint(3) unsigned NOT NULL,
  `is_primary` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `sort` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `value` varchar(25) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deleted_at` (`deleted_at`),
  KEY `client_id` (`client_id`),
  KEY `value` (`value`),
  KEY `is_primary` (`is_primary`,`sort`)
) ENGINE=InnoDB AUTO_INCREMENT=79096 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clients_tags`
--

DROP TABLE IF EXISTS `clients_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `color` varchar(7) DEFAULT NULL,
  `sort` tinyint(4) DEFAULT NULL,
  `icon` varchar(70) DEFAULT NULL,
  `title` varchar(80) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deleted_at` (`deleted_at`),
  KEY `title` (`title`),
  KEY `sort` (`sort`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `communication_call_info`
--

DROP TABLE IF EXISTS `communication_call_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `communication_call_info` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `channel_contact` varchar(191) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `score` decimal(10,2) NOT NULL,
  `details` text DEFAULT NULL,
  `call_id` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `communication_records`
--

DROP TABLE IF EXISTS `communication_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `communication_records` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entity_type` varchar(191) NOT NULL,
  `entity_id` bigint(20) unsigned NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `client_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`client_ids`)),
  `division_id` int(11) DEFAULT NULL,
  `type` varchar(30) NOT NULL,
  `is_answered` tinyint(1) NOT NULL DEFAULT 0,
  `channel_contact` varchar(191) DEFAULT NULL,
  `sort_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `communication_records_entity_type_entity_id_index` (`entity_type`,`entity_id`),
  KEY `comm_rec_index-order_id` (`order_id`),
  KEY `comm_rec_index-sort_at` (`sort_at`),
  KEY `comm_rec_index-channel_contact` (`channel_contact`),
  KEY `comm_rec_index-entity_type` (`entity_type`)
) ENGINE=InnoDB AUTO_INCREMENT=1596253 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `conversations_favorites`
--

DROP TABLE IF EXISTS `conversations_favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conversations_favorites` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `starred` tinyint(1) NOT NULL DEFAULT 0,
  `client_id` int(10) unsigned DEFAULT NULL,
  `contact_type` varchar(100) DEFAULT NULL,
  `contact_value` varchar(200) DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `communication_rec_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`,`created_at`,`starred`) USING BTREE,
  KEY `contact_type` (`contact_type`,`contact_value`,`created_at`,`starred`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `conversations_marks`
--

DROP TABLE IF EXISTS `conversations_marks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conversations_marks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(100) NOT NULL,
  `client_id` int(10) unsigned DEFAULT NULL,
  `contact_type` varchar(100) DEFAULT NULL,
  `contact_value` varchar(200) DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`,`created_at`,`type`) USING BTREE,
  KEY `contact_type` (`contact_type`,`contact_value`,`created_at`,`type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=40556 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `customer_pages`
--

DROP TABLE IF EXISTS `customer_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_pages` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `division_id` tinyint(3) unsigned NOT NULL,
  `name` varchar(30) NOT NULL,
  `title` tinytext NOT NULL,
  `text` mediumtext NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPRESSED;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dispatch_employees`
--

DROP TABLE IF EXISTS `dispatch_employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dispatch_employees` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `work_id` int(10) unsigned NOT NULL,
  `employer_id` int(10) unsigned NOT NULL,
  `miscs` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `employer_id` (`employer_id`),
  KEY `work_id` (`work_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=15698 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dispatch_trucks`
--

DROP TABLE IF EXISTS `dispatch_trucks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dispatch_trucks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `work_id` int(10) unsigned NOT NULL,
  `truck_id` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `truck_id` (`truck_id`),
  KEY `work_id` (`work_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=27245 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `divisions`
--

DROP TABLE IF EXISTS `divisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `divisions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `short` varchar(8) DEFAULT NULL,
  `title` varchar(80) NOT NULL,
  `miscs` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `email_templates`
--

DROP TABLE IF EXISTS `email_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_templates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `division_id` tinyint(3) unsigned DEFAULT NULL,
  `group_id` tinyint(3) unsigned DEFAULT NULL,
  `active` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `sort` tinyint(3) unsigned DEFAULT NULL,
  `title` varchar(70) NOT NULL,
  `mailjet_tpl_id` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `active` (`active`,`mailjet_tpl_id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `email_templates_groups`
--

DROP TABLE IF EXISTS `email_templates_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_templates_groups` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `division_id` tinyint(3) unsigned DEFAULT NULL,
  `active` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `sort` tinyint(3) unsigned DEFAULT NULL,
  `title` varchar(70) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `active` (`active`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `email_templates_mandrill`
--

DROP TABLE IF EXISTS `email_templates_mandrill`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_templates_mandrill` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `template_id` int(10) unsigned DEFAULT NULL,
  `template_slug` varchar(200) DEFAULT NULL,
  `template_vars` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `template_slug` (`template_slug`),
  KEY `template_id` (`template_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `employee_efficiency_plan`
--

DROP TABLE IF EXISTS `employee_efficiency_plan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_efficiency_plan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `conversion_local_team` int(11) DEFAULT NULL,
  `conversion_long_team` int(11) DEFAULT NULL,
  `date_at` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `employee_rates`
--

DROP TABLE IF EXISTS `employee_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_rates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `employee_name` varchar(191) NOT NULL,
  `role_id` int(11) NOT NULL,
  `role_name` varchar(191) NOT NULL,
  `division_id` int(11) NOT NULL,
  `season` varchar(191) NOT NULL,
  `workday` decimal(10,2) DEFAULT NULL,
  `holiday` decimal(10,2) DEFAULT NULL,
  `peakday` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `employee_sales_plan`
--

DROP TABLE IF EXISTS `employee_sales_plan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_sales_plan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `local` int(11) DEFAULT NULL,
  `intrestate` int(11) DEFAULT NULL,
  `date_at` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `name` varchar(300) DEFAULT NULL,
  `l_name` varchar(70) DEFAULT NULL,
  `address` varchar(250) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `pay_type` varchar(15) DEFAULT NULL,
  `driver_start_of_work` date DEFAULT NULL,
  `driver_notes` text DEFAULT NULL,
  `signature` text DEFAULT NULL,
  `pbx_ext` varchar(5) DEFAULT NULL,
  `pbx_show_webrtc` tinyint(1) NOT NULL DEFAULT 0,
  `auth_user_id` bigint(20) unsigned DEFAULT NULL,
  `division_ids` varchar(15) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `partner_id` int(11) DEFAULT NULL,
  `ringostat_sip_status` tinyint(1) NOT NULL DEFAULT 0,
  `ringostat_id` int(10) unsigned DEFAULT NULL,
  `ringostat_call_rec_id` int(10) unsigned DEFAULT NULL,
  `ringostat_miscs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`ringostat_miscs`)),
  `zadarma_sip_status` tinyint(1) NOT NULL DEFAULT 0,
  `zadarma_call_rec_id` int(10) unsigned DEFAULT NULL,
  `callers_number` varchar(191) DEFAULT NULL,
  `sales_team` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=245 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `employees_busy`
--

DROP TABLE IF EXISTS `employees_busy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees_busy` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `reason` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deleted_at` (`deleted_at`),
  KEY `employee_id` (`employee_id`),
  KEY `start_date` (`start_date`),
  KEY `end_date` (`end_date`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `employees_busy_w_days`
--

DROP TABLE IF EXISTS `employees_busy_w_days`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees_busy_w_days` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `miscs` varchar(20) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `miscs` (`miscs`)
) ENGINE=InnoDB AUTO_INCREMENT=228 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `employees_emails`
--

DROP TABLE IF EXISTS `employees_emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees_emails` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `sort` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `value` varchar(60) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`employee_id`),
  KEY `deleted_at` (`deleted_at`),
  KEY `client_id` (`employee_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB AUTO_INCREMENT=250 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `employees_messengers`
--

DROP TABLE IF EXISTS `employees_messengers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees_messengers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `value` varchar(60) NOT NULL,
  `type_id` tinyint(3) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`employee_id`),
  KEY `deleted_at` (`deleted_at`),
  KEY `client_id` (`employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `employees_notes`
--

DROP TABLE IF EXISTS `employees_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees_notes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `value` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`employee_id`),
  KEY `deleted_at` (`deleted_at`),
  KEY `client_id` (`employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `employees_pbx_data`
--

DROP TABLE IF EXISTS `employees_pbx_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees_pbx_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `pbx_id` int(10) unsigned DEFAULT NULL,
  `pbx_ext` int(10) unsigned DEFAULT NULL,
  `pbx_password` varchar(100) DEFAULT NULL,
  `pbx_show_webrtc` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `call_rec_id` int(10) unsigned DEFAULT NULL,
  `sip_status` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pbx_id` (`pbx_id`,`pbx_ext`),
  KEY `employee_id` (`employee_id`),
  KEY `employee_id_2` (`employee_id`,`pbx_id`)
) ENGINE=InnoDB AUTO_INCREMENT=429 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `employees_phones`
--

DROP TABLE IF EXISTS `employees_phones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees_phones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `type_id` tinyint(3) unsigned NOT NULL,
  `is_primary` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `sort` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `value` varchar(25) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deleted_at` (`deleted_at`),
  KEY `client_id` (`employee_id`),
  KEY `value` (`value`),
  KEY `is_primary` (`is_primary`,`sort`)
) ENGINE=InnoDB AUTO_INCREMENT=171 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `event_after_calls`
--

DROP TABLE IF EXISTS `event_after_calls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_after_calls` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(10) unsigned DEFAULT NULL,
  `call_id` varchar(50) DEFAULT NULL,
  `type` varchar(10) DEFAULT NULL,
  `scheme_name` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `destination` varchar(20) DEFAULT NULL,
  `number_e164` varchar(20) DEFAULT NULL,
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `extra_materials`
--

DROP TABLE IF EXISTS `extra_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `extra_materials` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sort` int(10) unsigned DEFAULT NULL,
  `division_id` int(10) unsigned DEFAULT NULL,
  `group_id` int(10) unsigned NOT NULL,
  `title` varchar(50) NOT NULL,
  `notes` varchar(150) DEFAULT NULL,
  `price` decimal(6,2) unsigned NOT NULL,
  `need_packing` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `need_unpacking` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `packing_price` decimal(5,2) unsigned DEFAULT NULL,
  `unpacking_price` decimal(5,2) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `division_id` (`division_id`)
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `extra_materials_types`
--

DROP TABLE IF EXISTS `extra_materials_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `extra_materials_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(80) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deleted_at` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gmail_accounts`
--

DROP TABLE IF EXISTS `gmail_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gmail_accounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `division_id` int(11) DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL COMMENT 'Юзер ID',
  `active` tinyint(4) NOT NULL COMMENT 'Активность',
  `is_archived` tinyint(4) NOT NULL DEFAULT 0,
  `miscs` varchar(400) DEFAULT NULL COMMENT 'JSON',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `active` (`active`,`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gmail_accounts_2_users`
--

DROP TABLE IF EXISTS `gmail_accounts_2_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gmail_accounts_2_users` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` smallint(5) unsigned NOT NULL,
  `user_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=178 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `incoming_calls`
--

DROP TABLE IF EXISTS `incoming_calls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `incoming_calls` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `provider` varchar(191) NOT NULL,
  `call_id` varchar(191) NOT NULL,
  `phone` varchar(191) NOT NULL,
  `client_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11990 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `interstate_shuttle_prices`
--

DROP TABLE IF EXISTS `interstate_shuttle_prices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `interstate_shuttle_prices` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `division_id` tinyint(4) DEFAULT NULL,
  `min` int(10) unsigned DEFAULT 0,
  `max` int(10) unsigned NOT NULL DEFAULT 0,
  `price` decimal(8,2) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `division_id` (`division_id`,`min`,`max`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `interstate_state_coefficients`
--

DROP TABLE IF EXISTS `interstate_state_coefficients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `interstate_state_coefficients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `division_id` smallint(5) unsigned DEFAULT NULL,
  `range_id` int(10) unsigned NOT NULL,
  `from_state` varchar(2) NOT NULL,
  `to_state` varchar(2) NOT NULL,
  `price` decimal(5,2) unsigned DEFAULT NULL,
  `price_2` decimal(5,2) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `range_id` (`range_id`,`from_state`,`to_state`,`division_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4287 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `interstate_state_ranges`
--

DROP TABLE IF EXISTS `interstate_state_ranges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `interstate_state_ranges` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `division_id` tinyint(4) DEFAULT NULL,
  `lb_from` double DEFAULT NULL,
  `lb_to` double DEFAULT NULL,
  `cbft_from` double DEFAULT NULL,
  `cbft_to` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_from` (`lb_from`,`lb_to`),
  KEY `cbft_from` (`cbft_from`,`cbft_to`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `intrastate_rates`
--

DROP TABLE IF EXISTS `intrastate_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intrastate_rates` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `division_id` tinyint(4) DEFAULT NULL,
  `rate_weight_id` smallint(5) unsigned NOT NULL,
  `rate_distance_id` smallint(5) unsigned NOT NULL,
  `coefficient` float unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `division_id` (`division_id`,`rate_weight_id`,`rate_distance_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=338 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `intrastate_rates_distances`
--

DROP TABLE IF EXISTS `intrastate_rates_distances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intrastate_rates_distances` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `division_id` tinyint(4) DEFAULT NULL,
  `from` int(10) unsigned NOT NULL DEFAULT 0,
  `to` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `division_id` (`division_id`,`from`,`to`)
) ENGINE=InnoDB AUTO_INCREMENT=138 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `intrastate_rates_weights`
--

DROP TABLE IF EXISTS `intrastate_rates_weights`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intrastate_rates_weights` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `division_id` tinyint(4) DEFAULT NULL,
  `from` int(10) unsigned NOT NULL DEFAULT 0,
  `to` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `division_id` (`division_id`,`from`,`to`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `division_ids` varchar(15) DEFAULT NULL,
  `group_id` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `weight` decimal(8,2) unsigned DEFAULT NULL,
  `cuft` decimal(7,2) unsigned DEFAULT NULL,
  `price` double DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deleted_at` (`deleted_at`),
  KEY `title` (`title`(191))
) ENGINE=InnoDB AUTO_INCREMENT=1546 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `items_groups`
--

DROP TABLE IF EXISTS `items_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `items_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deleted_at` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=159965 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `local_hourly_rates`
--

DROP TABLE IF EXISTS `local_hourly_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `local_hourly_rates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `division_id` tinyint(4) DEFAULT NULL,
  `season` enum('summer','winter') DEFAULT NULL,
  `workday` mediumint(8) unsigned NOT NULL,
  `holiday` mediumint(8) unsigned NOT NULL,
  `peakday` mediumint(8) unsigned NOT NULL,
  `crew_qty` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `season` (`season`,`division_id`,`crew_qty`),
  KEY `crew_qty` (`crew_qty`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `move_estimate_types`
--

DROP TABLE IF EXISTS `move_estimate_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `move_estimate_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deleted_at` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `move_sizes`
--

DROP TABLE IF EXISTS `move_sizes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `move_sizes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(80) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order_foreman_notes`
--

DROP TABLE IF EXISTS `order_foreman_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_foreman_notes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `foreman_id` int(11) NOT NULL,
  `text` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order_payroll_items`
--

DROP TABLE IF EXISTS `order_payroll_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_payroll_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payroll_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `hourly_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `hours` decimal(10,2) NOT NULL DEFAULT 0.00,
  `extras` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_cc_due` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order_payrolls`
--

DROP TABLE IF EXISTS `order_payrolls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_payrolls` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `processed_employee_id` int(11) DEFAULT NULL,
  `paid_form_bol` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`paid_form_bol`)),
  `hours` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_processed` tinyint(1) NOT NULL,
  `start_at` timestamp NULL DEFAULT NULL,
  `end_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `creator_id` int(11) DEFAULT NULL,
  `cash_on_hand` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(10) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `division_id` int(10) unsigned NOT NULL,
  `status_id` int(10) unsigned NOT NULL,
  `source_id` int(10) unsigned DEFAULT NULL,
  `move_size_id` int(10) unsigned DEFAULT NULL,
  `sizing_is_auto` tinyint(3) unsigned DEFAULT NULL,
  `sizing_volume` decimal(12,2) unsigned DEFAULT NULL,
  `sizing_weight` decimal(12,2) unsigned DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `hash` varchar(32) DEFAULT NULL COMMENT 'md5: created_at+id',
  `base_id` int(10) unsigned DEFAULT NULL,
  `first_calc_as_client` tinyint(1) NOT NULL DEFAULT 0,
  `reject_reason` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `user_id` (`user_id`),
  KEY `division_id` (`division_id`),
  KEY `hash` (`hash`),
  KEY `created_at` (`created_at`),
  KEY `status_id` (`status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=156509 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_2_tags`
--

DROP TABLE IF EXISTS `orders_2_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders_2_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tag_id` (`tag_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6599 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_activities`
--

DROP TABLE IF EXISTS `orders_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders_activities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `type` varchar(70) NOT NULL,
  `miscs` text DEFAULT NULL,
  `ext_id` varchar(32) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `ext_id` (`ext_id`),
  KEY `user_id` (`user_id`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=721549 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_customs_extras`
--

DROP TABLE IF EXISTS `orders_customs_extras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders_customs_extras` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `price` decimal(7,2) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9595 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_defaults`
--

DROP TABLE IF EXISTS `orders_defaults`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders_defaults` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `value` varchar(100) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_estimates`
--

DROP TABLE IF EXISTS `orders_estimates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders_estimates` (
  `order_id` int(10) unsigned NOT NULL,
  `type` enum('local','interstate','intrastate') NOT NULL,
  `is_locked` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `trucks` tinyint(3) unsigned DEFAULT NULL,
  `crews` tinyint(3) unsigned DEFAULT NULL,
  `discount_type` enum('percent','sum') NOT NULL DEFAULT 'sum',
  `discount_value` decimal(7,2) unsigned DEFAULT NULL,
  `fee_type` enum('percent','sum') NOT NULL DEFAULT 'sum',
  `travel_fee` decimal(7,2) DEFAULT NULL,
  `calculated_moving_min_value` decimal(9,2) unsigned DEFAULT NULL,
  `calculated_moving_max_value` decimal(9,2) unsigned DEFAULT NULL,
  `calculated_extra_services` decimal(8,2) unsigned DEFAULT NULL,
  `calculated_extra_materials` decimal(8,2) unsigned DEFAULT NULL,
  `calculated_moving_time` int(10) unsigned DEFAULT NULL COMMENT 'Секунды',
  `calculated_moving_distance` decimal(8,2) unsigned DEFAULT NULL COMMENT 'Miles',
  `calculated_moving_distance_auto` decimal(8,2) unsigned DEFAULT NULL COMMENT 'Miles',
  `calculated_moving_distance_is_auto` tinyint(4) NOT NULL DEFAULT 1,
  `dispatch_allowed` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`order_id`),
  KEY `dispatch_allowed` (`dispatch_allowed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Расчеты по заказам';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_estimates_calculated`
--

DROP TABLE IF EXISTS `orders_estimates_calculated`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders_estimates_calculated` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `estimate_type` varchar(100) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `value` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`,`estimate_type`)
) ENGINE=InnoDB AUTO_INCREMENT=419667 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Расчеты по заказам';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_estimates_interstate`
--

DROP TABLE IF EXISTS `orders_estimates_interstate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders_estimates_interstate` (
  `order_id` int(10) unsigned NOT NULL,
  `estimate_rate` enum('expedited','consolidated') NOT NULL DEFAULT 'expedited',
  `rate` decimal(6,2) unsigned DEFAULT NULL,
  `rate_auto` decimal(8,2) unsigned DEFAULT NULL,
  `is_auto` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `packing` decimal(8,2) DEFAULT NULL,
  `unpacking` decimal(8,2) DEFAULT NULL,
  `shuttle_pickup` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `shuttle_delivery` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `delivery_days` varchar(60) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Расчеты по заказам';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_estimates_intrastate`
--

DROP TABLE IF EXISTS `orders_estimates_intrastate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders_estimates_intrastate` (
  `order_id` int(10) unsigned NOT NULL,
  `rate` decimal(8,2) unsigned DEFAULT NULL,
  `rate_auto` decimal(8,2) unsigned DEFAULT NULL,
  `is_auto` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Внутри штата';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_estimates_local`
--

DROP TABLE IF EXISTS `orders_estimates_local`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders_estimates_local` (
  `order_id` int(10) unsigned NOT NULL,
  `hours_min` decimal(5,2) NOT NULL,
  `hours_max` decimal(5,2) NOT NULL,
  `rate` decimal(5,2) unsigned DEFAULT NULL,
  `rate_auto` int(10) unsigned DEFAULT NULL,
  `is_auto` tinyint(1) NOT NULL DEFAULT 0,
  `mileage` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Локальная перевозка';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_estimates_mobile`
--

DROP TABLE IF EXISTS `orders_estimates_mobile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders_estimates_mobile` (
  `order_id` bigint(20) unsigned NOT NULL,
  `estimate` mediumtext DEFAULT NULL COMMENT 'JSON',
  `bol` mediumtext DEFAULT NULL COMMENT 'json',
  `estimate_signed_at` datetime DEFAULT NULL,
  `bol_signed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `bol_signed_employee_id` int(10) unsigned DEFAULT NULL,
  `estimate_signed_employee_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`order_id`) USING BTREE,
  KEY `orders_estimates_mobile_bol_signed_employee_id_foreign` (`bol_signed_employee_id`),
  KEY `orders_estimates_mobile_estimate_signed_employee_id_foreign` (`estimate_signed_employee_id`),
  CONSTRAINT `orders_estimates_mobile_bol_signed_employee_id_foreign` FOREIGN KEY (`bol_signed_employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `orders_estimates_mobile_estimate_signed_employee_id_foreign` FOREIGN KEY (`estimate_signed_employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPRESSED;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_extended`
--

DROP TABLE IF EXISTS `orders_extended`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders_extended` (
  `order_id` int(10) unsigned NOT NULL,
  `ext_id` int(11) DEFAULT NULL,
  `import` tinytext DEFAULT NULL COMMENT 'JSON',
  `miscs` tinytext DEFAULT NULL,
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_inventories`
--

DROP TABLE IF EXISTS `orders_inventories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders_inventories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `section_id` int(10) unsigned NOT NULL,
  `is_section` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `item_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `price` decimal(6,2) unsigned DEFAULT NULL,
  `qty` int(10) unsigned DEFAULT NULL,
  `weight` decimal(8,2) unsigned DEFAULT NULL,
  `volume` decimal(7,2) DEFAULT NULL,
  `sort` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `section_id` (`section_id`),
  KEY `is_section` (`is_section`)
) ENGINE=InnoDB AUTO_INCREMENT=1414275 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_inventory_activities`
--

DROP TABLE IF EXISTS `orders_inventory_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders_inventory_activities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(191) NOT NULL,
  `is_client_action` tinyint(1) NOT NULL,
  `miscs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`miscs`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_materials`
--

DROP TABLE IF EXISTS `orders_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders_materials` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type_id` int(10) unsigned NOT NULL,
  `order_id` int(10) unsigned NOT NULL,
  `material_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(50) NOT NULL,
  `price` decimal(6,2) unsigned NOT NULL,
  `qty` int(10) unsigned NOT NULL,
  `need_packing` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `need_unpacking` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `packing_price` decimal(5,2) unsigned DEFAULT NULL,
  `unpacking_price` decimal(5,2) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `material_id` (`material_id`)
) ENGINE=InnoDB AUTO_INCREMENT=86813 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_notes`
--

DROP TABLE IF EXISTS `orders_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders_notes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `visibility` varchar(250) DEFAULT NULL,
  `text` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `is_pinned` (`is_pinned`),
  KEY `created_at` (`created_at`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=310837 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_payments`
--

DROP TABLE IF EXISTS `orders_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders_payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `order_id` int(10) unsigned DEFAULT NULL,
  `payment_account_id` int(10) unsigned NOT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(10,4) NOT NULL,
  `in_total_sum` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `created_at` (`created_at`,`user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=40190 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_services`
--

DROP TABLE IF EXISTS `orders_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders_services` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `service_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(50) NOT NULL,
  `price` double NOT NULL,
  `qty` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_sources`
--

DROP TABLE IF EXISTS `orders_sources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders_sources` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `color` varchar(7) DEFAULT NULL,
  `division_ids` varchar(15) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `division_ids` (`division_ids`)
) ENGINE=InnoDB AUTO_INCREMENT=119 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_status_change_history`
--

DROP TABLE IF EXISTS `orders_status_change_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders_status_change_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `prev_status` int(10) unsigned NOT NULL,
  `new_status` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  `is_deleted` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `created_at` (`created_at`),
  KEY `new_status` (`new_status`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=153725 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_statuses`
--

DROP TABLE IF EXISTS `orders_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders_statuses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `color` varchar(7) DEFAULT NULL,
  `group_id` int(10) unsigned NOT NULL,
  `sort` int(10) unsigned DEFAULT NULL,
  `actions` varchar(150) DEFAULT NULL,
  `in_calendar` tinyint(4) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deleted_at` (`deleted_at`),
  KEY `group_id` (`group_id`,`sort`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_statuses_groups`
--

DROP TABLE IF EXISTS `orders_statuses_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders_statuses_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `sort` int(10) unsigned NOT NULL,
  `in_report` tinyint(4) DEFAULT NULL,
  `in_funel_report` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_statuses_routes`
--

DROP TABLE IF EXISTS `orders_statuses_routes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders_statuses_routes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status_id` int(10) unsigned DEFAULT NULL,
  `route_to_status_id` int(10) unsigned DEFAULT NULL,
  `sort` tinyint(3) unsigned NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_tags`
--

DROP TABLE IF EXISTS `orders_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `color` varchar(7) DEFAULT NULL,
  `sort` tinyint(4) DEFAULT NULL,
  `icon` varchar(70) DEFAULT NULL,
  `title` varchar(80) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deleted_at` (`deleted_at`),
  KEY `title` (`title`),
  KEY `sort` (`sort`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_trucks`
--

DROP TABLE IF EXISTS `orders_trucks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders_trucks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `truck_id` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `user_id` (`truck_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_waypoints`
--

DROP TABLE IF EXISTS `orders_waypoints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders_waypoints` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `type` enum('pickup','destination') NOT NULL,
  `state` varchar(2) NOT NULL,
  `zip` varchar(5) NOT NULL,
  `city` varchar(50) DEFAULT NULL,
  `address` varchar(150) DEFAULT NULL,
  `ap` varchar(20) DEFAULT NULL,
  `parking_type_id` int(10) unsigned NOT NULL,
  `has_elevator` tinyint(1) NOT NULL DEFAULT 0,
  `building_type_id` int(10) unsigned NOT NULL,
  `flights_id` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `sort` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `miscs` tinytext DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `deleted_at` (`deleted_at`),
  KEY `sort` (`sort`)
) ENGINE=InnoDB AUTO_INCREMENT=139783 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_waypoints_notes`
--

DROP TABLE IF EXISTS `orders_waypoints_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders_waypoints_notes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `waypoint_id` int(10) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `value` varchar(250) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`waypoint_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21294 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_workers`
--

DROP TABLE IF EXISTS `orders_workers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders_workers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_works`
--

DROP TABLE IF EXISTS `orders_works`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders_works` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `start_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `start_time_to` time DEFAULT NULL,
  `duration` decimal(4,1) DEFAULT NULL,
  `trucks` tinyint(1) DEFAULT NULL,
  `employees` tinyint(3) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `notes_by` bigint(20) unsigned DEFAULT NULL,
  `notes_created_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `in_dispatch` tinyint(4) NOT NULL DEFAULT 0,
  `dispatch_updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `deleted_at` (`deleted_at`),
  KEY `start_date` (`start_date`),
  KEY `dispatch_updated_at` (`dispatch_updated_at`)
) ENGINE=InnoDB AUTO_INCREMENT=72670 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_works_2_work`
--

DROP TABLE IF EXISTS `orders_works_2_work`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders_works_2_work` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `work_id` int(10) unsigned NOT NULL,
  `work_type_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `work_id` (`work_id`)
) ENGINE=InnoDB AUTO_INCREMENT=84337 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `parking_types`
--

DROP TABLE IF EXISTS `parking_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `parking_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `partners`
--

DROP TABLE IF EXISTS `partners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `partners` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `division_id` int(11) DEFAULT NULL,
  `contact_person` varchar(191) DEFAULT NULL,
  `phone` varchar(191) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payments_accounts`
--

DROP TABLE IF EXISTS `payments_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments_accounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `title` varchar(50) NOT NULL,
  `division_id` int(10) unsigned DEFAULT NULL,
  `sort` tinyint(4) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sort` (`sort`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `peaks_dates`
--

DROP TABLE IF EXISTS `peaks_dates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `peaks_dates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type_id` int(10) unsigned NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=533 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `peaks_dates_bkp`
--

DROP TABLE IF EXISTS `peaks_dates_bkp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `peaks_dates_bkp` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type_id` int(10) unsigned NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=383 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `peaks_types`
--

DROP TABLE IF EXISTS `peaks_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `peaks_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `color` varchar(7) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(191) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=678 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `services` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(250) NOT NULL,
  `price` decimal(7,2) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `division_id` tinyint(4) DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `value` varchar(30) DEFAULT NULL,
  `miscs` varchar(500) DEFAULT NULL,
  `description` varchar(250) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `settings_closing_statuses`
--

DROP TABLE IF EXISTS `settings_closing_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings_closing_statuses` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `group_id` int(11) DEFAULT NULL,
  `sort` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `settings_closing_statuses_groups`
--

DROP TABLE IF EXISTS `settings_closing_statuses_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings_closing_statuses_groups` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `sort` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `settings_estimate_parameters`
--

DROP TABLE IF EXISTS `settings_estimate_parameters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings_estimate_parameters` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `division_id` varchar(10) DEFAULT NULL,
  `estimate_type` enum('local','interstate','intrastate','any') NOT NULL,
  `name` varchar(50) NOT NULL,
  `value` varchar(50) NOT NULL,
  `description` varchar(250) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `settings_waypoints_flights`
--

DROP TABLE IF EXISTS `settings_waypoints_flights`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings_waypoints_flights` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `value` decimal(10,1) unsigned NOT NULL,
  `sort` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type_id` int(11) DEFAULT NULL COMMENT 'Группа',
  `status_id` int(11) NOT NULL COMMENT 'Статус',
  `division_id` int(11) DEFAULT NULL,
  `title` varchar(80) DEFAULT NULL COMMENT 'Название',
  `description` text DEFAULT NULL COMMENT 'Описание',
  `result` tinytext DEFAULT NULL,
  `priority` tinyint(3) unsigned NOT NULL COMMENT 'Приоритет',
  `due_date` datetime DEFAULT NULL COMMENT 'Выполнить до',
  `user_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Автор',
  `executor_id` int(10) unsigned DEFAULT NULL COMMENT 'Исполнитель',
  `order_id` int(10) unsigned DEFAULT NULL,
  `notify_holder` datetime DEFAULT NULL COMMENT 'Уведомить исполнителя',
  `notify_subscribers` datetime DEFAULT NULL COMMENT 'Уведомить наблюдателей',
  `miscs` text DEFAULT NULL COMMENT 'JSON',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `result_at` datetime DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `due_date` (`due_date`),
  KEY `status_id` (`status_id`),
  KEY `executor_id` (`executor_id`),
  KEY `created_at` (`created_at`),
  KEY `order_id` (`order_id`),
  KEY `division_id` (`division_id`)
) ENGINE=InnoDB AUTO_INCREMENT=139181 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tasks_status_history`
--

DROP TABLE IF EXISTS `tasks_status_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks_status_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(10) unsigned NOT NULL,
  `user_id` smallint(5) unsigned NOT NULL,
  `prev_status` tinyint(3) unsigned NOT NULL,
  `new_status` tinyint(3) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`)
) ENGINE=InnoDB AUTO_INCREMENT=241970 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tasks_statuses`
--

DROP TABLE IF EXISTS `tasks_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks_statuses` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `active` tinyint(3) unsigned NOT NULL COMMENT 'Активность',
  `sort` tinyint(3) unsigned NOT NULL COMMENT 'Сортировка',
  `title` varchar(50) NOT NULL COMMENT 'Название',
  `class` varchar(30) DEFAULT NULL COMMENT 'CSS Класс',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tasks_subscribers`
--

DROP TABLE IF EXISTS `tasks_subscribers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks_subscribers` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `task_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tasks_types`
--

DROP TABLE IF EXISTS `tasks_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks_types` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `active` tinyint(3) unsigned NOT NULL,
  `sort` tinyint(3) unsigned NOT NULL,
  `title` varchar(50) NOT NULL,
  `icon` varchar(30) DEFAULT NULL,
  `color` varchar(7) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `trucks`
--

DROP TABLE IF EXISTS `trucks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trucks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `title` varchar(50) DEFAULT NULL,
  `nickname` varchar(50) DEFAULT NULL,
  `division_ids` varchar(15) DEFAULT NULL,
  `vendor` varchar(25) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `year` year(4) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `l_plate` varchar(20) DEFAULT NULL,
  `vin` varchar(50) DEFAULT NULL,
  `length` double DEFAULT NULL,
  `cuft` double DEFAULT NULL,
  `lbs` double DEFAULT NULL,
  `start_mi` int(10) unsigned DEFAULT NULL,
  `cur_mi` int(10) unsigned DEFAULT NULL,
  `p_color` varchar(7) DEFAULT NULL,
  `avi_date` date DEFAULT NULL,
  `reg_date` date DEFAULT NULL,
  `tech_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `partner_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`active`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `trucks_busy`
--

DROP TABLE IF EXISTS `trucks_busy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trucks_busy` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `truck_id` int(10) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `reason` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deleted_at` (`deleted_at`),
  KEY `truck_id` (`truck_id`),
  KEY `start_date` (`start_date`,`end_date`)
) ENGINE=InnoDB AUTO_INCREMENT=423 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `trucks_busy_w_days`
--

DROP TABLE IF EXISTS `trucks_busy_w_days`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trucks_busy_w_days` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `truck_id` int(10) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `miscs` varchar(20) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `truck_id` (`truck_id`),
  KEY `miscs` (`miscs`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `trucks_notes`
--

DROP TABLE IF EXISTS `trucks_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trucks_notes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `truck_id` int(10) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `value` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`truck_id`),
  KEY `deleted_at` (`deleted_at`),
  KEY `client_id` (`truck_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `twilio_sms`
--

DROP TABLE IF EXISTS `twilio_sms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `twilio_sms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `division` smallint(6) DEFAULT NULL,
  `sid` varchar(50) NOT NULL,
  `direction` varchar(50) DEFAULT NULL,
  `from` varchar(20) DEFAULT NULL,
  `to` varchar(20) DEFAULT NULL,
  `body` text DEFAULT NULL,
  `misc` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `division` (`division`),
  KEY `direction` (`direction`,`from`),
  KEY `direction_2` (`direction`,`to`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=170665 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `twilio_sms_statuses`
--

DROP TABLE IF EXISTS `twilio_sms_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `twilio_sms_statuses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `status` varchar(100) DEFAULT NULL,
  `from` varchar(20) DEFAULT NULL,
  `to` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sid` (`sid`)
) ENGINE=InnoDB AUTO_INCREMENT=276789 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tmp_is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `name` varchar(191) NOT NULL,
  `division_ids` varchar(15) DEFAULT NULL,
  `email` varchar(191) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `miscs` text DEFAULT NULL COMMENT 'JSON',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=257 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_roles`
--

DROP TABLE IF EXISTS `users_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users_roles` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `for_crew` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_roles_2_routes`
--

DROP TABLE IF EXISTS `users_roles_2_routes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users_roles_2_routes` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` tinyint(3) unsigned NOT NULL,
  `route_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`),
  KEY `route_id` (`route_id`)
) ENGINE=InnoDB AUTO_INCREMENT=188 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_roles_2_users`
--

DROP TABLE IF EXISTS `users_roles_2_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users_roles_2_users` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` tinyint(3) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=366 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_routes_list`
--

DROP TABLE IF EXISTS `users_routes_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users_routes_list` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `is_group` tinyint(4) NOT NULL DEFAULT 0,
  `method` varchar(10) DEFAULT NULL,
  `uri` varchar(200) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=360 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `websockets_statistics_entries`
--

DROP TABLE IF EXISTS `websockets_statistics_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `websockets_statistics_entries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` varchar(191) NOT NULL,
  `peak_connection_count` int(11) NOT NULL,
  `websocket_message_count` int(11) NOT NULL,
  `api_message_count` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `works_types`
--

DROP TABLE IF EXISTS `works_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `works_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sort` int(10) unsigned NOT NULL,
  `title` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deleted_at` (`deleted_at`),
  KEY `sort` (`sort`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `zadarma_calls_end`
--

DROP TABLE IF EXISTS `zadarma_calls_end`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `zadarma_calls_end` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event` varchar(50) DEFAULT NULL,
  `pbx_id` varchar(10) DEFAULT NULL,
  `call_start` datetime DEFAULT NULL COMMENT 'pbx timezone',
  `pbx_call_id` varchar(100) DEFAULT NULL,
  `caller_id` varchar(20) DEFAULT NULL,
  `destination` varchar(50) DEFAULT NULL,
  `called_did` varchar(20) DEFAULT NULL,
  `internal` int(10) unsigned DEFAULT NULL,
  `duration` int(10) unsigned DEFAULT 0,
  `disposition` varchar(30) DEFAULT NULL,
  `status_code` tinyint(4) DEFAULT 0,
  `is_recorded` tinyint(4) DEFAULT NULL,
  `call_id_with_rec` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `client_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `caller_id` (`caller_id`),
  KEY `destination` (`destination`),
  KEY `called_did` (`called_did`),
  KEY `event` (`event`),
  KEY `call_start` (`call_start`,`pbx_id`) USING BTREE,
  KEY `pbx_call_id` (`pbx_call_id`)
) ENGINE=InnoDB AUTO_INCREMENT=965597 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `zadarma_calls_end_copy`
--

DROP TABLE IF EXISTS `zadarma_calls_end_copy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `zadarma_calls_end_copy` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event` varchar(50) DEFAULT NULL,
  `pbx_id` varchar(10) DEFAULT NULL,
  `call_start` datetime DEFAULT NULL COMMENT 'pbx timezone',
  `pbx_call_id` varchar(100) DEFAULT NULL,
  `caller_id` varchar(20) DEFAULT NULL,
  `destination` varchar(50) DEFAULT NULL,
  `called_did` varchar(20) DEFAULT NULL,
  `internal` int(10) unsigned DEFAULT NULL,
  `duration` int(10) unsigned DEFAULT 0,
  `disposition` varchar(30) DEFAULT NULL,
  `status_code` tinyint(4) DEFAULT 0,
  `is_recorded` tinyint(4) DEFAULT NULL,
  `call_id_with_rec` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `caller_id` (`caller_id`),
  KEY `destination` (`destination`),
  KEY `called_did` (`called_did`),
  KEY `event` (`event`),
  KEY `call_start` (`call_start`,`pbx_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=896151 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `zadarma_calls_records`
--

DROP TABLE IF EXISTS `zadarma_calls_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `zadarma_calls_records` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event` varchar(50) DEFAULT NULL,
  `pbx_id` varchar(10) DEFAULT NULL,
  `pbx_call_id` varchar(100) DEFAULT NULL,
  `call_id_with_rec` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `event` (`event`),
  KEY `pbx_id` (`pbx_id`),
  KEY `pbx_call_id` (`pbx_call_id`)
) ENGINE=InnoDB AUTO_INCREMENT=169228 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `zadarma_sms`
--

DROP TABLE IF EXISTS `zadarma_sms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `zadarma_sms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pbx_id` varchar(10) DEFAULT NULL,
  `inbound` tinyint(1) DEFAULT 0,
  `caller_id` varchar(20) DEFAULT NULL,
  `caller_did` varchar(20) DEFAULT NULL,
  `text` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `caller_id` (`caller_id`),
  KEY `caller_did` (`caller_did`)
) ENGINE=InnoDB AUTO_INCREMENT=1679 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-05-22 17:14:38
