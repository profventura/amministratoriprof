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
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `cash_categories`
--

LOCK TABLES `cash_categories` WRITE;
/*!40000 ALTER TABLE `cash_categories` DISABLE KEYS */;
INSERT INTO `cash_categories` VALUES (1,'Quote associative','income',1),(2,'Donazioni','income',1),(3,'Spese generiche','expense',1);
/*!40000 ALTER TABLE `cash_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `cash_flows`
--

LOCK TABLES `cash_flows` WRITE;
/*!40000 ALTER TABLE `cash_flows` DISABLE KEYS */;
INSERT INTO `cash_flows` VALUES (1,'2026-01-15',1,'Quota annuale Mario Rossi',50.00,'income',1,'2026-01-15 17:04:02'),(2,'2026-02-09',1,'Quota annuale zaza zaza Arancio',180.00,'income',3,'2026-02-09 12:32:12'),(3,'2026-02-09',1,'Quota annuale Giulia Bianchi',150.00,'income',4,'2026-02-09 12:32:39');
/*!40000 ALTER TABLE `cash_flows` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `course_participants`
--

LOCK TABLES `course_participants` WRITE;
/*!40000 ALTER TABLE `course_participants` DISABLE KEYS */;
INSERT INTO `course_participants` VALUES (1,4,39),(1,2,40);
/*!40000 ALTER TABLE `course_participants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `courses`
--

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
INSERT INTO `courses` VALUES (1,'ciccio','cxccxcxc','2026-02-08','16:15:00','19:16:00',2026,'2026-02-08 11:16:12');
/*!40000 ALTER TABLE `courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `documents`
--

LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
INSERT INTO `documents` VALUES (13,2,'membership_certificate',2026,'storage/documents/membership_certificate/2026/certificate_Giulia_Bianchi.pdf','2026-01-15 21:26:25'),(14,2,'membership_certificate',2026,'storage/documents/membership_certificate/2026/certificate_Giulia_Bianchi.pdf','2026-01-25 16:28:34'),(15,2,'membership_certificate',2026,'storage/documents/membership_certificate/2026/certificate_Giulia_Bianchi.pdf','2026-01-25 16:29:08'),(16,2,'membership_certificate',2026,'storage/documents/membership_certificate/2026/certificate_Giulia_Bianchi.pdf','2026-01-25 16:48:31'),(17,1,'membership_certificate',2026,'storage/documents/membership_certificate/2026/certificate_Mario_Rossi.pdf','2026-01-25 17:01:42'),(18,2,'membership_certificate',2026,'storage/documents/membership_certificate/2026/certificate_Giulia_Bianchi.pdf','2026-01-25 17:06:31'),(19,2,'membership_certificate',2026,'storage/documents/membership_certificate/2026/certificate_Giulia_Bianchi_1769357364.pdf','2026-01-25 17:09:24'),(20,2,'membership_certificate',2026,'storage/documents/membership_certificate/2026/certificate_Giulia_Bianchi_25012026171201.pdf','2026-01-25 17:12:01'),(21,2,'membership_certificate',2026,'storage/documents/membership_certificate/2026/certificate_Giulia_Bianchi_25012026171459.pdf','2026-01-25 17:15:17'),(22,2,'membership_certificate',2026,'storage/documents/membership_certificate/2026/certificate_Giulia_Bianchi_25012026185021.pdf','2026-01-25 18:50:36'),(23,2,'membership_certificate',2026,'storage/documents/membership_certificate/2026/certificate_Giulia_Bianchi_25012026215626.pdf','2026-01-25 21:56:29'),(24,2,'membership_certificate',2026,'storage/documents/membership_certificate/2026/certificate_Giulia_Bianchi_25012026220657.pdf','2026-01-25 22:06:57'),(25,2,'membership_certificate',2026,'storage/documents/membership_certificate/2026/certificate_Giulia_Bianchi_25012026220901.pdf','2026-01-25 22:09:01'),(26,2,'membership_certificate',2026,'storage/documents/membership_certificate/2026/certificate_Giulia_Bianchi_26012026195812.pdf','2026-01-26 19:58:28'),(27,1,'membership_certificate',2026,'storage/documents/membership_certificate/2026/certificate_Mario_Rossi_26012026201208.pdf','2026-01-26 20:12:23'),(28,2,'membership_certificate',2026,'storage/documents/membership_certificate/2026/certificate_Giulia_Bianchi_26012026201749.pdf','2026-01-26 20:18:04'),(29,2,'membership_certificate',2026,'storage/documents/membership_certificate/2026/certificate_Giulia_Bianchi_08022026110646.pdf','2026-02-08 11:06:46'),(30,1,'membership_certificate',2026,'storage/documents/membership_certificate/2026/certificate_Rossi_Mario_08022026110748.pdf','2026-02-08 11:07:48'),(31,2,'membership_certificate',2026,'storage/documents/membership_certificate/2026/certificate_Giulia_Bianchi_08022026111341.pdf','2026-02-08 11:13:41'),(32,1,'membership_certificate',2026,'storage/documents/membership_certificate/2026/certificate_Rossi_Mario_08022026111625.pdf','2026-02-08 11:16:25'),(33,1,'membership_certificate',2026,'storage/documents/membership_certificate/2026/certificate_Rossi_Mario_09022026122850.pdf','2026-02-09 12:28:50'),(34,4,'receipt',2026,'storage/documents/receipts/2026/receipt_0002.pdf','2026-02-09 12:32:12'),(35,2,'receipt',2026,'storage/documents/receipts/2026/receipt_0003.pdf','2026-02-09 12:32:39'),(36,4,'membership_certificate',2026,'storage/documents/membership_certificate/2026/certificate_Arancio_zaza_zaza_09022026123302.pdf','2026-02-09 12:33:02'),(37,2,'membership_certificate',2026,'storage/documents/membership_certificate/2026/certificate_Bianchi_Giulia_09022026123302.pdf','2026-02-09 12:33:02'),(38,1,'membership_certificate',2026,'storage/documents/membership_certificate/2026/certificate_Rossi_Mario_09022026123302.pdf','2026-02-09 12:33:02'),(39,4,'dm_certificate',2026,'storage/documents/dm_certificate/2026/dm_Arancio_zaza_zaza_1_09022026124442.pdf','2026-02-09 12:44:42'),(40,2,'dm_certificate',2026,'storage/documents/dm_certificate/2026/dm_Bianchi_Giulia_1_09022026124443.pdf','2026-02-09 12:44:43'),(41,4,'membership_certificate',2026,'storage/documents/membership_certificate/2026/certificate_Arancio_zaza_zaza_13022026070246.pdf','2026-02-13 07:02:46'),(42,2,'membership_certificate',2026,'storage/documents/membership_certificate/2026/certificate_Bianchi_Giulia_13022026070246.pdf','2026-02-13 07:02:46'),(43,1,'membership_certificate',2026,'storage/documents/membership_certificate/2026/certificate_Rossi_Mario_13022026070246.pdf','2026-02-13 07:02:46'),(44,4,'membership_certificate',2026,'storage/documents/membership_certificate/2026/certificate_Arancio_zaza_zaza_13022026100850.pdf','2026-02-13 10:08:50');
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `email_logs`
--

LOCK TABLES `email_logs` WRITE;
/*!40000 ALTER TABLE `email_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `email_settings`
--

LOCK TABLES `email_settings` WRITE;
/*!40000 ALTER TABLE `email_settings` DISABLE KEYS */;
INSERT INTO `email_settings` VALUES (1,'smtp.example.com',587,'tls',NULL,NULL,'info@example.com','Associazione',NULL,'Invio Certificato Iscrizione',NULL,'Invio Attestato Partecipazione',NULL,'noreply@example.com','Associazione',NULL,NULL);
/*!40000 ALTER TABLE `email_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `members`
--

LOCK TABLES `members` WRITE;
/*!40000 ALTER TABLE `members` DISABLE KEYS */;
INSERT INTO `members` VALUES (1,'1','Mario','Rossi',NULL,'mario.rossi@example.com','mario.rossi','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','+39 333111222',NULL,NULL,'Milano',NULL,NULL,'1985-04-12','RSSMRA85D12F205X',NULL,NULL,0,NULL,'active',NULL,'2026-01-15 17:04:02',NULL,NULL),(2,'2','Giulia','Bianchi',NULL,'giulia.bianchi@example.com','giulia.bianchi','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','+39 333222333',NULL,NULL,'Torino',NULL,NULL,'1990-11-02','BNCGLL90S42L219Z',NULL,NULL,0,NULL,'active',NULL,'2026-01-15 17:04:02',NULL,NULL),(3,'3','Luca','Verdi',NULL,'luca.verdi@example.com','luca.verdi','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','+39 333333444',NULL,NULL,'Roma',NULL,NULL,'1982-07-20','VRDLCU82L20H501Z',NULL,NULL,0,NULL,'inactive',NULL,'2026-01-15 17:04:02',NULL,NULL),(4,'4','zaza zaza','Arancio','zaza zaza','hmfv5ydp.cza@20email.eu','zaza.zaza','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','','3233333333','via po','ROMA','RM','00100',NULL,'',NULL,'dffgdfggfdgfd',1,'','active',NULL,'2026-02-09 12:28:39',NULL,NULL);
/*!40000 ALTER TABLE `members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `memberships`
--

LOCK TABLES `memberships` WRITE;
/*!40000 ALTER TABLE `memberships` DISABLE KEYS */;
INSERT INTO `memberships` VALUES (1,1,2026,'regular',NULL,'2026-01-15 17:04:02'),(2,2,2026,'regular',NULL,'2026-01-15 17:04:02'),(3,3,2025,'overdue',NULL,'2026-01-15 17:04:02'),(4,3,2026,'pending',NULL,'2026-01-15 17:16:47'),(5,4,2026,'regular',NULL,'2026-02-09 12:29:27');
/*!40000 ALTER TABLE `memberships` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,1,1,'2026-01-15',50.00,'bank','Quota annuale','0001',2026,'2026-01-15 17:04:02'),(3,4,5,'2026-02-09',180.00,'bank','','0002',2026,'2026-02-09 12:32:12'),(4,2,2,'2026-02-09',150.00,'bank','','0003',2026,'2026-02-09 12:32:39');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'Associazione AP','Via Roma 1','Milano','info@associazione-ap.it','+39 02 123456',3,2026,'2026-02-09 14:12:09',NULL,'app/templates/attestato.pdf','app/templates/certificato.pdf',148,112,188,123,20,16,'#ff0000','Gill Sans MT',14,'#ff0000','Helvetica',73,161,12,'#000000','Helvetica',149,177,12,'#000000','Helvetica',1,1,0,0,145,83,16,'#000000','Arial',1,146,127,16,'#000000','Arial',1,43,186,12,'#000000','Arial',0,149,193,12,'#000000','Arial',0);
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-13 10:53:04
