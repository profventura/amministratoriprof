-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: am_professionisti
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cash_categories`
--

DROP TABLE IF EXISTS `cash_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cash_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `type` enum('income','expense') NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cash_categories_name_type` (`name`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cash_flows`
--

DROP TABLE IF EXISTS `cash_flows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cash_flows` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `flow_date` date NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  `description` varchar(190) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` enum('income','expense') NOT NULL,
  `related_payment_id` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cash_flows_category` (`category_id`),
  KEY `idx_cash_flows_type_date` (`type`,`flow_date`),
  KEY `fk_cash_flows_payment` (`related_payment_id`),
  CONSTRAINT `fk_cash_flows_category` FOREIGN KEY (`category_id`) REFERENCES `cash_categories` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cash_flows_payment` FOREIGN KEY (`related_payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `course_participants`
--

DROP TABLE IF EXISTS `course_participants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_participants` (
  `course_id` int(10) unsigned NOT NULL,
  `member_id` int(10) unsigned NOT NULL,
  `certificate_document_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`course_id`,`member_id`),
  KEY `idx_course_participants_member` (`member_id`),
  KEY `fk_course_participants_certificate` (`certificate_document_id`),
  CONSTRAINT `fk_course_participants_certificate` FOREIGN KEY (`certificate_document_id`) REFERENCES `documents` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_course_participants_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_course_participants_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `courses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(190) NOT NULL,
  `description` text DEFAULT NULL,
  `course_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `year` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(10) unsigned DEFAULT NULL,
  `type` enum('membership_certificate','receipt','dm_certificate') NOT NULL,
  `year` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_documents_member` (`member_id`),
  KEY `idx_documents_type_year` (`type`,`year`),
  CONSTRAINT `fk_documents_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `email_logs`
--

DROP TABLE IF EXISTS `email_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sent_at` datetime NOT NULL DEFAULT current_timestamp(),
  `to_email` varchar(190) NOT NULL,
  `cc` varchar(255) DEFAULT NULL,
  `bcc` varchar(255) DEFAULT NULL,
  `subject` varchar(190) NOT NULL,
  `body` text NOT NULL,
  `status` enum('sent','failed') NOT NULL,
  `error_message` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `email_settings`
--

DROP TABLE IF EXISTS `email_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `smtp_host` varchar(190) NOT NULL,
  `smtp_port` int(11) NOT NULL,
  `smtp_secure` enum('none','tls','ssl') NOT NULL DEFAULT 'none',
  `username` varchar(190) DEFAULT NULL,
  `password` varchar(190) DEFAULT NULL,
  `from_email` varchar(190) NOT NULL,
  `from_name` varchar(190) NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `email_certificate_subject` varchar(190) DEFAULT 'Invio Certificato Iscrizione',
  `email_certificate_body` text DEFAULT NULL,
  `email_dm_certificate_subject` varchar(190) DEFAULT 'Invio Attestato Partecipazione',
  `email_dm_certificate_body` text DEFAULT NULL,
  `smtp_from_email` varchar(190) NOT NULL DEFAULT 'noreply@example.com',
  `smtp_from_name` varchar(190) NOT NULL DEFAULT 'Associazione',
  `smtp_cc` varchar(255) DEFAULT NULL,
  `smtp_bcc` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `members`
--

DROP TABLE IF EXISTS `members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `members` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_number` varchar(50) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `studio_name` varchar(150) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `mobile_phone` varchar(50) DEFAULT NULL,
  `address` varchar(190) DEFAULT NULL,
  `city` varchar(120) DEFAULT NULL,
  `province` varchar(10) DEFAULT NULL,
  `zip_code` varchar(10) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `tax_code` varchar(32) DEFAULT NULL,
  `billing_piva` varchar(32) DEFAULT NULL,
  `billing_cf` varchar(32) DEFAULT NULL,
  `is_revisor` tinyint(1) NOT NULL DEFAULT 0,
  `revision_number` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `registration_date` date DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_members_email` (`email`),
  UNIQUE KEY `uq_members_username` (`username`),
  KEY `idx_members_member_number` (`member_number`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `memberships`
--

DROP TABLE IF EXISTS `memberships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `memberships` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(10) unsigned NOT NULL,
  `year` int(11) NOT NULL,
  `status` enum('pending','regular','overdue') NOT NULL DEFAULT 'pending',
  `renewal_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_memberships_member_year` (`member_id`,`year`),
  CONSTRAINT `fk_memberships_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(10) unsigned NOT NULL,
  `membership_id` int(10) unsigned DEFAULT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` enum('cash','bank','card') NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `receipt_number` varchar(50) DEFAULT NULL,
  `receipt_year` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_receipt_year_number` (`receipt_year`,`receipt_number`),
  KEY `idx_payments_member` (`member_id`),
  KEY `idx_payments_membership` (`membership_id`),
  CONSTRAINT `fk_payments_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payments_membership` FOREIGN KEY (`membership_id`) REFERENCES `memberships` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `association_name` varchar(190) NOT NULL,
  `address` varchar(190) DEFAULT NULL,
  `city` varchar(120) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `receipt_sequence_current` int(11) NOT NULL DEFAULT 0,
  `receipt_sequence_year` int(11) NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `membership_certificate_template_path` varchar(255) DEFAULT NULL,
  `dm_certificate_template_docx_path` varchar(255) DEFAULT NULL,
  `membership_certificate_template_docx_path` varchar(255) DEFAULT NULL,
  `certificate_stamp_name_x` int(11) DEFAULT 100,
  `certificate_stamp_name_y` int(11) DEFAULT 120,
  `certificate_stamp_number_x` int(11) DEFAULT 100,
  `certificate_stamp_number_y` int(11) DEFAULT 140,
  `certificate_stamp_font_size` int(11) DEFAULT 16,
  `certificate_stamp_name_font_size` int(11) DEFAULT 12,
  `certificate_stamp_name_color` varchar(7) DEFAULT '#000000',
  `certificate_stamp_name_font_family` varchar(20) DEFAULT 'Helvetica',
  `certificate_stamp_number_font_size` int(11) DEFAULT 12,
  `certificate_stamp_number_color` varchar(7) DEFAULT '#000000',
  `certificate_stamp_number_font_family` varchar(20) DEFAULT 'Helvetica',
  `certificate_stamp_date_x` int(11) DEFAULT 0,
  `certificate_stamp_date_y` int(11) DEFAULT 0,
  `certificate_stamp_date_font_size` int(11) DEFAULT 12,
  `certificate_stamp_date_color` varchar(7) DEFAULT '#000000',
  `certificate_stamp_date_font_family` varchar(20) DEFAULT 'Helvetica',
  `certificate_stamp_year_x` int(11) DEFAULT 0,
  `certificate_stamp_year_y` int(11) DEFAULT 0,
  `certificate_stamp_year_font_size` int(11) DEFAULT 12,
  `certificate_stamp_year_color` varchar(7) DEFAULT '#000000',
  `certificate_stamp_year_font_family` varchar(20) DEFAULT 'Helvetica',
  `certificate_stamp_name_bold` tinyint(1) DEFAULT 1,
  `certificate_stamp_number_bold` tinyint(1) DEFAULT 1,
  `certificate_stamp_date_bold` tinyint(1) DEFAULT 0,
  `certificate_stamp_year_bold` tinyint(1) DEFAULT 0,
  `dm_certificate_stamp_name_x` int(11) DEFAULT 100,
  `dm_certificate_stamp_name_y` int(11) DEFAULT 120,
  `dm_certificate_stamp_name_font_size` int(11) DEFAULT 16,
  `dm_certificate_stamp_name_color` varchar(10) DEFAULT '#000000',
  `dm_certificate_stamp_name_font_family` varchar(50) DEFAULT 'Arial',
  `dm_certificate_stamp_name_bold` tinyint(4) DEFAULT 1,
  `dm_certificate_stamp_course_title_x` int(11) DEFAULT 100,
  `dm_certificate_stamp_course_title_y` int(11) DEFAULT 140,
  `dm_certificate_stamp_course_title_font_size` int(11) DEFAULT 16,
  `dm_certificate_stamp_course_title_color` varchar(10) DEFAULT '#000000',
  `dm_certificate_stamp_course_title_font_family` varchar(50) DEFAULT 'Arial',
  `dm_certificate_stamp_course_title_bold` tinyint(4) DEFAULT 1,
  `dm_certificate_stamp_date_x` int(11) DEFAULT 0,
  `dm_certificate_stamp_date_y` int(11) DEFAULT 0,
  `dm_certificate_stamp_date_font_size` int(11) DEFAULT 12,
  `dm_certificate_stamp_date_color` varchar(10) DEFAULT '#000000',
  `dm_certificate_stamp_date_font_family` varchar(50) DEFAULT 'Arial',
  `dm_certificate_stamp_date_bold` tinyint(4) DEFAULT 0,
  `dm_certificate_stamp_year_x` int(11) DEFAULT 0,
  `dm_certificate_stamp_year_y` int(11) DEFAULT 0,
  `dm_certificate_stamp_year_font_size` int(11) DEFAULT 12,
  `dm_certificate_stamp_year_color` varchar(10) DEFAULT '#000000',
  `dm_certificate_stamp_year_font_family` varchar(50) DEFAULT 'Arial',
  `dm_certificate_stamp_year_bold` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin') NOT NULL DEFAULT 'admin',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-13 10:52:50
