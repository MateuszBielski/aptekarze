-- MySQL dump 10.13  Distrib 5.7.25, for Linux (i686)
--
-- Host: localhost    Database: db_aptekarze_test
-- ------------------------------------------------------
-- Server version	5.7.25-0ubuntu0.16.04.2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `abstr_member`
--

DROP TABLE IF EXISTS `abstr_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `abstr_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_id` int(11) NOT NULL,
  `telephone` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `surname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_day_of_month` int(11) DEFAULT NULL,
  `nr_prawa_zawodu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nazwisko_panienskie` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `begin_date` date DEFAULT NULL,
  `initial_account` double DEFAULT NULL,
  `discriminator` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_8A6531DBBE04EA9` (`job_id`),
  CONSTRAINT `FK_8A6531DBBE04EA9` FOREIGN KEY (`job_id`) REFERENCES `job` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `abstr_member`
--

LOCK TABLES `abstr_member` WRITE;
/*!40000 ALTER TABLE `abstr_member` DISABLE KEYS */;
INSERT INTO `abstr_member` VALUES (1,2,'87654321','a@b','użytk','Register',20,NULL,NULL,'2019-08-15',0,'memberuser'),(6,4,NULL,NULL,'iDoZmianyStawki1','nDoZmianyStawki1',20,NULL,NULL,'2018-08-15',0,'memberuser'),(7,4,NULL,NULL,'iDoZmianyStawki1','nDoZmianyStawki1',20,NULL,NULL,'2018-08-15',0,'memberhistory'),(8,4,NULL,NULL,'iDoZmianyStawki2','iDoZmianyStawki2',20,NULL,NULL,'2018-04-15',0,'memberuser'),(9,4,NULL,NULL,'iDoZmianyStawki2','iDoZmianyStawki2',20,NULL,NULL,'2018-04-15',0,'memberhistory');
/*!40000 ALTER TABLE `abstr_member` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `active_job`
--

DROP TABLE IF EXISTS `active_job`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `active_job` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rate` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `active_job`
--

LOCK TABLES `active_job` WRITE;
/*!40000 ALTER TABLE `active_job` DISABLE KEYS */;
/*!40000 ALTER TABLE `active_job` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `archive_job`
--

DROP TABLE IF EXISTS `archive_job`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `archive_job` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rate` double NOT NULL,
  `date_of_change` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `archive_job`
--

LOCK TABLES `archive_job` WRITE;
/*!40000 ALTER TABLE `archive_job` DISABLE KEYS */;
/*!40000 ALTER TABLE `archive_job` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contribution`
--

DROP TABLE IF EXISTS `contribution`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contribution` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `my_user_id` int(11) NOT NULL,
  `value` double NOT NULL,
  `payment_date` date NOT NULL,
  `source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `printed` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_EA351E152D977FB9` (`my_user_id`),
  CONSTRAINT `FK_EA351E152D977FB9` FOREIGN KEY (`my_user_id`) REFERENCES `member_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contribution`
--

LOCK TABLES `contribution` WRITE;
/*!40000 ALTER TABLE `contribution` DISABLE KEYS */;
/*!40000 ALTER TABLE `contribution` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job`
--

DROP TABLE IF EXISTS `job`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `replaced_by_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rate` double NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_FBD8E0F89AC69B54` (`replaced_by_id`),
  CONSTRAINT `FK_FBD8E0F89AC69B54` FOREIGN KEY (`replaced_by_id`) REFERENCES `job` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job`
--

LOCK TABLES `job` WRITE;
/*!40000 ALTER TABLE `job` DISABLE KEYS */;
INSERT INTO `job` VALUES (1,NULL,'stanowisko1',19),(2,NULL,'stanowisko2',28),(3,NULL,'stanowisko3',25),(4,NULL,'doZmianyStawki1',54);
/*!40000 ALTER TABLE `job` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_history`
--

DROP TABLE IF EXISTS `member_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_history` (
  `id` int(11) NOT NULL,
  `my_user_id` int(11) DEFAULT NULL,
  `who_made_change_id` int(11) DEFAULT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_984EC7DF2D977FB9` (`my_user_id`),
  KEY `IDX_984EC7DFDAAD57D8` (`who_made_change_id`),
  CONSTRAINT `FK_984EC7DF2D977FB9` FOREIGN KEY (`my_user_id`) REFERENCES `member_user` (`id`),
  CONSTRAINT `FK_984EC7DFBF396750` FOREIGN KEY (`id`) REFERENCES `abstr_member` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_984EC7DFDAAD57D8` FOREIGN KEY (`who_made_change_id`) REFERENCES `member_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_history`
--

LOCK TABLES `member_history` WRITE;
/*!40000 ALTER TABLE `member_history` DISABLE KEYS */;
INSERT INTO `member_history` VALUES (7,6,NULL,'2019-08-15 21:28:26'),(9,8,NULL,'2019-08-15 21:29:06');
/*!40000 ALTER TABLE `member_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_user`
--

DROP TABLE IF EXISTS `member_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_user` (
  `id` int(11) NOT NULL,
  `username` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_711BFA15F85E0677` (`username`),
  CONSTRAINT `FK_711BFA15BF396750` FOREIGN KEY (`id`) REFERENCES `abstr_member` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_user`
--

LOCK TABLES `member_user` WRITE;
/*!40000 ALTER TABLE `member_user` DISABLE KEYS */;
INSERT INTO `member_user` VALUES (1,'użytkRegister','[\"ROLE_ADMIN\"]','$argon2id$v=19$m=65536,t=4,p=1$PHyDJHZ9F78Ouy/BvKZyfw$X/wfVOwVF8UPvjc+vzrU92O6NohD2SIC4ssL54cs2MY'),(6,'iDoZmianyStawki1nDoZmianyStawki1','[]','87654321'),(8,'iDoZmianyStawki2iDoZmianyStawki2','[]','87654321');
/*!40000 ALTER TABLE `member_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migration_versions`
--

DROP TABLE IF EXISTS `migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migration_versions` (
  `version` varchar(14) COLLATE utf8mb4_unicode_ci NOT NULL,
  `executed_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migration_versions`
--

LOCK TABLES `migration_versions` WRITE;
/*!40000 ALTER TABLE `migration_versions` DISABLE KEYS */;
INSERT INTO `migration_versions` VALUES ('20190815165349','2019-08-15 16:58:51');
/*!40000 ALTER TABLE `migration_versions` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-08-15 21:33:03
