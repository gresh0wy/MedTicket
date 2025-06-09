-- MySQL dump 10.13  Distrib 9.2.0, for Win64 (x86_64)
--
-- Host: localhost    Database: medticket_alpha
-- ------------------------------------------------------
-- Server version	9.2.0

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
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'Super Administrator'),(2,'Administrator'),(3,'Obserwator'),(4,'Sekcja Elektryczna'),(5,'Sekcja Budowlana'),(7,'Sekcja Informatyczna'),(8,'Cyberbezpieczenstwo'),(9,'Aparatura Medyczna');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_assignment_history`
--

DROP TABLE IF EXISTS `ticket_assignment_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_assignment_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ticket_id` int NOT NULL,
  `previous_employee_id` int DEFAULT NULL,
  `new_employee_id` int NOT NULL,
  `changed_by` int NOT NULL,
  `changed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `previous_employee_id` (`previous_employee_id`),
  KEY `new_employee_id` (`new_employee_id`),
  KEY `changed_by` (`changed_by`),
  CONSTRAINT `ticket_assignment_history_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_assignment_history_ibfk_2` FOREIGN KEY (`previous_employee_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ticket_assignment_history_ibfk_3` FOREIGN KEY (`new_employee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_assignment_history_ibfk_4` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_assignment_history`
--

LOCK TABLES `ticket_assignment_history` WRITE;
/*!40000 ALTER TABLE `ticket_assignment_history` DISABLE KEYS */;
INSERT INTO `ticket_assignment_history` VALUES (2,4,3,53,3,'2025-05-12 14:22:35'),(3,4,53,3,3,'2025-05-12 14:23:55'),(6,1,NULL,3,3,'2025-05-13 09:24:52'),(7,7,53,3,3,'2025-05-14 14:12:02'),(8,7,3,53,3,'2025-05-14 14:12:33'),(11,7,53,3,3,'2025-05-14 14:16:44'),(12,7,3,53,3,'2025-05-14 14:16:49'),(13,7,53,3,3,'2025-05-14 14:16:51'),(14,7,3,53,3,'2025-05-14 14:16:54'),(15,7,53,3,3,'2025-05-14 14:16:56'),(16,7,3,53,3,'2025-05-14 14:16:59'),(17,7,53,3,3,'2025-05-14 14:17:30'),(18,8,NULL,58,58,'2025-05-15 10:18:53'),(19,8,58,3,3,'2025-05-15 10:27:41'),(20,8,3,58,58,'2025-05-15 10:28:01'),(27,8,58,3,3,'2025-05-15 10:51:29'),(28,8,3,53,3,'2025-05-15 10:55:32'),(29,8,53,3,3,'2025-05-15 11:25:19'),(30,8,3,58,3,'2025-05-15 11:44:57');
/*!40000 ALTER TABLE `ticket_assignment_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_comments`
--

DROP TABLE IF EXISTS `ticket_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ticket_id` int NOT NULL,
  `user_id` int NOT NULL,
  `comment` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `ticket_comments_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_comments`
--

LOCK TABLES `ticket_comments` WRITE;
/*!40000 ALTER TABLE `ticket_comments` DISABLE KEYS */;
INSERT INTO `ticket_comments` VALUES (6,3,3,'test','2025-05-12 14:35:45',NULL),(8,2,3,'testowy komentarz','2025-05-13 12:30:14',NULL),(9,4,3,'test','2025-05-13 13:21:03',NULL),(10,5,3,'Restart systemu nic nie dał. Sprawdziłem płytki na innych stanowiskach – to samo. Ostatecznie 2 z 3 płytek otwarłem i wgrałem na dole (poziom „0”) w pracowni TK (koło SOR). 3 płytka po wielu próbach nie da się otworzyć. RTG wystąpi z prośbą o przesłanie spakowanych i za-hasłowanych plików „WeTransfer” (z Jastrzębiej Góry).  ok.','2025-05-13 13:24:26',NULL),(11,2,3,'test 22','2025-05-13 14:34:55',NULL),(12,2,3,'test 6333','2025-05-13 14:35:00',NULL),(13,2,3,'test 123!@#@QW','2025-05-13 14:35:07',NULL),(14,7,3,'test','2025-05-14 14:34:40',NULL),(15,8,58,'Umówiono termin aktualizacji na 15.5.2025 do 16.05','2025-05-15 10:19:41',NULL),(16,8,3,'Laptop przyniesiony na serwis i przekazany do michała','2025-05-15 10:20:11',NULL),(17,8,58,'Wykonano aktualizacje oraz konserwacje układu chłodzenia','2025-05-15 10:20:47',NULL);
/*!40000 ALTER TABLE `ticket_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tickets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `department` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `contact_number` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `priority` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `recurrence` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Oczekujące',
  `employee_id` int DEFAULT NULL,
  `actions` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `accepted_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `section` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `local_ip` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tickets`
--

LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
INSERT INTO `tickets` VALUES (1,' Brak dostępu do internetu przez Wi-Fi „FirmaNet” – problem występuje na wielu urządzeniach w biurze od godziny 08:45, uniemożliwia pracę zdalną i korzystanie z systemów firmowych. Proszę o pilną interwencję.','Jan Kowalski','serwis','Dyrekcja Szpitala - Sekretariat','232','Od rana nie mogę połączyć się z siecią Wi-Fi „FirmaNet”. Próbowałem na dwóch różnych urządzeniach (laptop i telefon), ale oba pokazują brak dostępu do internetu. Inne osoby z działu również zgłaszały ten sam problem. Restart routera (jeśli dostępny) nie pomógł. Proszę o pilną interwencję, ponieważ bez internetu nie mogę pracować zdalnie na systemach firmowych.','niski','jednorazowy','W trakcie',3,NULL,'2025-05-09 12:55:09',NULL,NULL,NULL,'informatyczna',NULL),(2,'Uszkodzona latarnia uliczna przy ul. Mickiewicza','Paweł Tomczyk','drukarki','Oddział Pediatryczny - Rezydenci','234','Latarnia nie działa od kilku dni. Mieszkańcy zgłosili problem, proszę o pilną interwencję.','średni','jednorazowy','Zakończone',3,NULL,'2025-05-09 13:58:28',NULL,'2025-05-12 10:35:43',NULL,'informatyczna',NULL),(3,' Zniszczony chodnik w parku miejskim','Bartosz Nowak','aktulizacja','Oddział Anestezjologii i Intensywnej Terapii - Lekarz Kierujący Oddziałem','452','Chodnik w parku miejskim jest popękany i stwarza zagrożenie dla przechodniów. Proszę o pilną','Wysoki','sporadyczny','W trakcie',53,'','2025-05-09 13:58:57',NULL,NULL,NULL,'informatyczna',NULL),(4,'Zgłoszenie w sprawie nielegalnego parkowania','Maciej Nowczak','ris/pacs','Oddział Chorób Wewnętrznych i Kardiologii z Odcinkiem Intensywnej Terapii Kardiologicznej - Lekarz Kierujący Oddziałem','234','Samochód parkuje na chodniku na ul. Wesołej, blokując przejście. Proszę o interwencję.','Krytyczny','jednorazowy','Odrzucono',3,NULL,'2025-05-09 13:59:28',NULL,'2025-05-12 10:35:33',NULL,'informatyczna',NULL),(5,'nie można odtworzyć i wczytać 3 szt. płytek ','Agnieszka Koczwara','serwis','Dział Diagnostyki Obrazowej - Pielęgniarka Oddziałowa','423','W pokoju obrazowym RTG, nie można odtworzyć i wczytać 3 szt. płytek z badaniami.','średni','sporadyczny','W trakcie',3,NULL,'2025-05-13 13:24:14',NULL,NULL,NULL,'informatyczna',NULL),(6,'www','michał stulejka','drukarki','Oddział Pediatryczny - Telefon dla pacjenta','213','qw','niski','częsty','Nowe',3,NULL,'2025-05-14 12:23:45',NULL,NULL,NULL,'informatyczna',NULL),(7,'dsa','michał t','drukarki','Oddział Anestezjologii i Intensywnej Terapii - Sala chorych nr 2','234','afds','niski','częsty','Nowe',3,NULL,'2025-05-14 12:24:32',NULL,NULL,NULL,'informatyczna',NULL),(8,'Aktualizacja laptopa - holter','Andrzej pałka','serwis','Oddział Chorób Infekcyjnych i Pediatrii - Dyżurka Lekarska','390','Jest zgoda na aktualizacje do windowsa 11 (laptop holter)','Niski','jednorazowy','Zakończone',58,'ss','2025-05-15 10:18:18',NULL,'2025-05-15 12:06:00',NULL,'informatyczna',NULL);
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_permissions`
--

DROP TABLE IF EXISTS `user_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_permissions` (
  `user_id` int NOT NULL,
  `permission_id` int NOT NULL,
  PRIMARY KEY (`user_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_permissions`
--

LOCK TABLES `user_permissions` WRITE;
/*!40000 ALTER TABLE `user_permissions` DISABLE KEYS */;
INSERT INTO `user_permissions` VALUES (3,1),(3,7),(53,7),(58,7);
/*!40000 ALTER TABLE `user_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_roles` (
  `user_id` int NOT NULL,
  `role_id` int NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_roles`
--

LOCK TABLES `user_roles` WRITE;
/*!40000 ALTER TABLE `user_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('Super Administrator','Administrator','Użytkownik','Obserwator','Informatyk','Elektryk') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Użytkownik',
  `first_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `blocked` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (3,'jprzytula','$2y$10$gQzmHfbhhOxVwl6WGB43X.SgUiZ.rdqo37bYXDofPhuvWOnaMpV5a','Super Administrator','Jakub','Przytuła','2024-06-11 18:30:08','2025-05-14 08:22:53',0),(53,'kkrupa','$2y$12$upw7/B985osX259X9KdLR./mFqLK4IyeIARayhqDns1BYF1WhjsEu','Użytkownik','Kamil','Krupa','2024-07-19 08:39:00','2025-05-12 11:06:46',0),(58,'mtuleja','$2y$10$RiLb/ZSMd14hc9imEn3Xmu/H2FGE1sp6.ph5XoC8DTT3Fp2d3sGH2','Użytkownik','Michał','Tuleja','2025-05-15 08:15:55','2025-05-15 08:15:55',0);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-05-15 13:25:53
