/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.7.2-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: domotique
-- ------------------------------------------------------
-- Server version	11.7.2-MariaDB-ubu2404

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `avatar`
--

DROP TABLE IF EXISTS `avatar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `avatar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `image_path` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `avatar`
--

LOCK TABLES `avatar` WRITE;
/*!40000 ALTER TABLE `avatar` DISABLE KEYS */;
INSERT INTO `avatar` VALUES
(1,'avatar1.jpg'),
(2,'avatar2.jpg'),
(3,'avatar3.jpg'),
(4,'avatar4.jpg'),
(5,'avatar5.jpg'),
(6,'avatar6.jpg'),
(7,'avatar7.jpg'),
(8,'avatar8.jpg'),
(9,'avatar9.jpg'),
(10,'avatar10.jpg'),
(11,'avatar11.jpg'),
(12,'avatar12.jpg'),
(13,'avatar13.jpg');
/*!40000 ALTER TABLE `avatar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `criteria`
--

DROP TABLE IF EXISTS `criteria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `criteria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mood` int(11) NOT NULL,
  `stress` int(11) NOT NULL,
  `tone` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `criteria`
--

LOCK TABLES `criteria` WRITE;
/*!40000 ALTER TABLE `criteria` DISABLE KEYS */;
INSERT INTO `criteria` VALUES
(1,80,10,70);
/*!40000 ALTER TABLE `criteria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `default_setting`
--

DROP TABLE IF EXISTS `default_setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `default_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(255) NOT NULL,
  `device_id` int(11) DEFAULT NULL,
  `feature_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_9F74B89894A4C7D5` (`device_id`),
  KEY `IDX_9F74B89860E4B880` (`feature_id`),
  CONSTRAINT `FK_9F74B89860E4B880` FOREIGN KEY (`feature_id`) REFERENCES `feature` (`id`),
  CONSTRAINT `FK_9F74B89894A4C7D5` FOREIGN KEY (`device_id`) REFERENCES `device` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `default_setting`
--

LOCK TABLES `default_setting` WRITE;
/*!40000 ALTER TABLE `default_setting` DISABLE KEYS */;
INSERT INTO `default_setting` VALUES
(1,'60',1,2),
(2,'50',2,2),
(3,'false',3,3),
(4,'40',4,5),
(5,'#ffffff',4,4),
(6,'true',1,6),
(7,'false',2,6),
(8,'true',4,7),
(9,'false',7,8),
(10,'true',7,9),
(11,'25',7,10);
/*!40000 ALTER TABLE `default_setting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `device`
--

DROP TABLE IF EXISTS `device`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `device` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(50) NOT NULL,
  `address` varchar(50) NOT NULL,
  `brand` varchar(50) NOT NULL,
  `reference` varchar(50) NOT NULL,
  `device_type_id` int(11) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_92FB68E4FFA550E` (`device_type_id`),
  KEY `IDX_92FB68E54177093` (`room_id`),
  CONSTRAINT `FK_92FB68E4FFA550E` FOREIGN KEY (`device_type_id`) REFERENCES `device_type` (`id`),
  CONSTRAINT `FK_92FB68E54177093` FOREIGN KEY (`room_id`) REFERENCES `room` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `device`
--

LOCK TABLES `device` WRITE;
/*!40000 ALTER TABLE `device` DISABLE KEYS */;
INSERT INTO `device` VALUES
(1,'Ampoule Salon','1234567','Philips','1234567',2,1),
(2,'Ampoule chambre','1234568','Philips','1234568',2,2),
(3,'Prise salon','123456','Samsung','123456',3,1),
(4,'Ampoule salon 2','123456','Samsung','123456',5,1),
(7,'Player JBL','aa:bb:cc:dd','JBL','753643',6,2);
/*!40000 ALTER TABLE `device` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `device_type`
--

DROP TABLE IF EXISTS `device_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `device_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(50) NOT NULL,
  `protocole_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_4D3F4B2A7C1A0B6` (`protocole_id`),
  CONSTRAINT `FK_4D3F4B2A7C1A0B6` FOREIGN KEY (`protocole_id`) REFERENCES `protocole` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `device_type`
--

LOCK TABLES `device_type` WRITE;
/*!40000 ALTER TABLE `device_type` DISABLE KEYS */;
INSERT INTO `device_type` VALUES
(1,'Thermostat',1),
(2,'Ampoule blanche',1),
(3,'Prise',1),
(4,'Capteur température',1),
(5,'Ampoule couleur',1),
(6,'Player',3);
/*!40000 ALTER TABLE `device_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctrine_migration_versions`
--

LOCK TABLES `doctrine_migration_versions` WRITE;
/*!40000 ALTER TABLE `doctrine_migration_versions` DISABLE KEYS */;
/*!40000 ALTER TABLE `doctrine_migration_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feature`
--

DROP TABLE IF EXISTS `feature`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `feature` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(50) NOT NULL,
  `minimum` int(11) DEFAULT NULL,
  `maximum` int(11) DEFAULT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `device_type_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_1FD77566F8BD700D` (`unit_id`),
  KEY `IDX_1FD77566F8BD701D` (`device_type_id`),
  CONSTRAINT `FK_1FD77566F8BD700D` FOREIGN KEY (`unit_id`) REFERENCES `unit` (`id`),
  CONSTRAINT `FK_1FD77566F8BD701D` FOREIGN KEY (`device_type_id`) REFERENCES `device_type` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feature`
--

LOCK TABLES `feature` WRITE;
/*!40000 ALTER TABLE `feature` DISABLE KEYS */;
INSERT INTO `feature` VALUES
(1,'Température',10,50,1,1),
(2,'Luminosité',0,100,2,2),
(3,'On/Off',NULL,NULL,NULL,3),
(4,'Couleur',NULL,NULL,NULL,5),
(5,'Luminosité',0,100,2,5),
(6,'On/Off',NULL,NULL,NULL,2),
(7,'On/Off',NULL,NULL,NULL,5),
(8,'On/Off',NULL,NULL,NULL,6),
(9,'Play',NULL,NULL,NULL,6),
(10,'Volume',0,100,4,6);
/*!40000 ALTER TABLE `feature` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `icon`
--

DROP TABLE IF EXISTS `icon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `icon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `image_path` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `icon`
--

LOCK TABLES `icon` WRITE;
/*!40000 ALTER TABLE `icon` DISABLE KEYS */;
INSERT INTO `icon` VALUES
(1,'chill.png'),
(2,'cozy.png'),
(3,'party.png'),
(4,'friend.png'),
(5,'couple.png');
/*!40000 ALTER TABLE `icon` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `planning`
--

DROP TABLE IF EXISTS `planning`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `planning` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(50) NOT NULL,
  `date_start` date NOT NULL,
  `day_creation` varchar(10) NOT NULL,
  `hour_start` varchar(5) NOT NULL,
  `hour_end` varchar(5) NOT NULL,
  `recurrence` varchar(50) NOT NULL,
  `vibe_id` int(11) DEFAULT NULL,
  `profile_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_D499BFF64B255BC3` (`vibe_id`),
  KEY `IDX_D499BFF6CCFA12B8` (`profile_id`),
  CONSTRAINT `FK_D499BFF64B255BC3` FOREIGN KEY (`vibe_id`) REFERENCES `vibe` (`id`),
  CONSTRAINT `FK_D499BFF6CCFA12B8` FOREIGN KEY (`profile_id`) REFERENCES `profile` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `planning`
--

LOCK TABLES `planning` WRITE;
/*!40000 ALTER TABLE `planning` DISABLE KEYS */;
INSERT INTO `planning` VALUES
(1,'test','2025-05-13','Mardi','22:03','23:03','daily',1,1);
/*!40000 ALTER TABLE `planning` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playlist`
--

DROP TABLE IF EXISTS `playlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `playlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `profile_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_D782112DCCFA12B8` (`profile_id`),
  CONSTRAINT `FK_D782112DCCFA12B8` FOREIGN KEY (`profile_id`) REFERENCES `profile` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playlist`
--

LOCK TABLES `playlist` WRITE;
/*!40000 ALTER TABLE `playlist` DISABLE KEYS */;
INSERT INTO `playlist` VALUES
(1,'SAO',1),
(2,'Egoist',1);
/*!40000 ALTER TABLE `playlist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profile`
--

DROP TABLE IF EXISTS `profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_8157AA0F86383B10` (`avatar_id`),
  CONSTRAINT `FK_8157AA0F86383B10` FOREIGN KEY (`avatar_id`) REFERENCES `avatar` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profile`
--

LOCK TABLES `profile` WRITE;
/*!40000 ALTER TABLE `profile` DISABLE KEYS */;
INSERT INTO `profile` VALUES
(1,'user1','user1',1),
(2,'user2','user2',2);
/*!40000 ALTER TABLE `profile` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `protocole`
--

DROP TABLE IF EXISTS `protocole`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `protocole` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `protocole`
--

LOCK TABLES `protocole` WRITE;
/*!40000 ALTER TABLE `protocole` DISABLE KEYS */;
INSERT INTO `protocole` VALUES
(1,'Zigbee'),
(2,'Z-Wave'),
(3,'WiFi');
/*!40000 ALTER TABLE `protocole` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `room`
--

DROP TABLE IF EXISTS `room`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `room` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(50) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `vibe_playing_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `room`
--

LOCK TABLES `room` WRITE;
/*!40000 ALTER TABLE `room` DISABLE KEYS */;
INSERT INTO `room` VALUES
(1,'Salon','salon.jpg',NULL),
(2,'Chambre','chambre.jpg',NULL);
/*!40000 ALTER TABLE `room` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `room_planning`
--

DROP TABLE IF EXISTS `room_planning`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `room_planning` (
  `room_id` int(11) NOT NULL,
  `planning_id` int(11) NOT NULL,
  PRIMARY KEY (`room_id`,`planning_id`),
  KEY `IDX_747F7F3254177093` (`room_id`),
  KEY `IDX_747F7F323D865311` (`planning_id`),
  CONSTRAINT `FK_747F7F323D865311` FOREIGN KEY (`planning_id`) REFERENCES `planning` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_747F7F3254177093` FOREIGN KEY (`room_id`) REFERENCES `room` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `room_planning`
--

LOCK TABLES `room_planning` WRITE;
/*!40000 ALTER TABLE `room_planning` DISABLE KEYS */;
INSERT INTO `room_planning` VALUES
(1,1);
/*!40000 ALTER TABLE `room_planning` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `setting`
--

DROP TABLE IF EXISTS `setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(255) NOT NULL,
  `feature_id` int(11) DEFAULT NULL,
  `device_id` int(11) DEFAULT NULL,
  `vibe_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_9F74B89860E4B879` (`feature_id`),
  KEY `IDX_9F74B89894A4C7D4` (`device_id`),
  KEY `IDX_9F74B8984B255BC3` (`vibe_id`),
  CONSTRAINT `FK_9F74B8984B255BC3` FOREIGN KEY (`vibe_id`) REFERENCES `vibe` (`id`),
  CONSTRAINT `FK_9F74B89860E4B879` FOREIGN KEY (`feature_id`) REFERENCES `feature` (`id`),
  CONSTRAINT `FK_9F74B89894A4C7D4` FOREIGN KEY (`device_id`) REFERENCES `device` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `setting`
--

LOCK TABLES `setting` WRITE;
/*!40000 ALTER TABLE `setting` DISABLE KEYS */;
INSERT INTO `setting` VALUES
(1,'60',2,1,1),
(2,'20',2,2,1),
(3,'false',3,3,1),
(10,'true',6,1,1),
(11,'true',6,2,1),
(12,'false',7,4,1),
(13,'#00ff00',4,4,1),
(14,'40',5,4,1);
/*!40000 ALTER TABLE `setting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `song`
--

DROP TABLE IF EXISTS `song`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `song` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `artist` varchar(50) NOT NULL,
  `duration` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `song`
--

LOCK TABLES `song` WRITE;
/*!40000 ALTER TABLE `song` DISABLE KEYS */;
INSERT INTO `song` VALUES
(1,'Unlasting','LiSA',295,'67f7932e1654c_sao-alicization-war-of-underworld-ending-full-unlasting-by-lisa.mp3','song.jpg'),
(2,'Adamas','Amalee',241,'67f7936d55918_Sword Art Online Alicization - ADAMAS Opening  ENGLISH  AmaLee.mp3','song.jpg'),
(3,'Euterpe','Egoist',225,'67f7bda2bd239_Euterpe.mp3','upload_785_phpBj7KPO.jpg'),
(4,'Fallen','Egoist',276,'67f7bdb417053_EGOIST - Fallen.mp3','upload_306_phpGpKMIr.jpg');
/*!40000 ALTER TABLE `song` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `song_playlist`
--

DROP TABLE IF EXISTS `song_playlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `song_playlist` (
  `song_id` int(11) NOT NULL,
  `playlist_id` int(11) NOT NULL,
  PRIMARY KEY (`song_id`,`playlist_id`),
  KEY `IDX_7C5E4765A0BDB2F3` (`song_id`),
  KEY `IDX_7C5E47656BBD148` (`playlist_id`),
  CONSTRAINT `FK_7C5E47656BBD148` FOREIGN KEY (`playlist_id`) REFERENCES `playlist` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_7C5E4765A0BDB2F3` FOREIGN KEY (`song_id`) REFERENCES `song` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `song_playlist`
--

LOCK TABLES `song_playlist` WRITE;
/*!40000 ALTER TABLE `song_playlist` DISABLE KEYS */;
INSERT INTO `song_playlist` VALUES
(1,1),
(2,1),
(3,2),
(4,2);
/*!40000 ALTER TABLE `song_playlist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unit`
--

DROP TABLE IF EXISTS `unit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `unit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(50) NOT NULL,
  `symbol` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unit`
--

LOCK TABLES `unit` WRITE;
/*!40000 ALTER TABLE `unit` DISABLE KEYS */;
INSERT INTO `unit` VALUES
(1,'Celsius','°C'),
(2,'Watt','W'),
(3,'Lumen','lm'),
(4,'Pourcent','%');
/*!40000 ALTER TABLE `unit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(180) NOT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`roles`)),
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_IDENTIFIER_USERNAME` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES
(1,'admin','[\"ROLE_ADMIN\"]','$2y$13$pTJZfm2ch6eOSP02BX9m7OwYtiZG9ylkFiTGeA.UrPm2a8TcarNi2');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vibe`
--

DROP TABLE IF EXISTS `vibe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vibe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(50) NOT NULL,
  `criteria_id` int(11) DEFAULT NULL,
  `playlist_id` int(11) DEFAULT NULL,
  `profile_id` int(11) DEFAULT NULL,
  `icon_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_42054C01990BEA15` (`criteria_id`),
  KEY `IDX_42054C016BBD148` (`playlist_id`),
  KEY `IDX_42054C01CCFA12B8` (`profile_id`),
  KEY `IDX_42054C0154B9D732` (`icon_id`),
  CONSTRAINT `FK_42054C0154B9D732` FOREIGN KEY (`icon_id`) REFERENCES `icon` (`id`),
  CONSTRAINT `FK_42054C016BBD148` FOREIGN KEY (`playlist_id`) REFERENCES `playlist` (`id`),
  CONSTRAINT `FK_42054C01990BEA15` FOREIGN KEY (`criteria_id`) REFERENCES `criteria` (`id`),
  CONSTRAINT `FK_42054C01CCFA12B8` FOREIGN KEY (`profile_id`) REFERENCES `profile` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vibe`
--

LOCK TABLES `vibe` WRITE;
/*!40000 ALTER TABLE `vibe` DISABLE KEYS */;
INSERT INTO `vibe` VALUES
(1,'chill',1,2,1,1);
/*!40000 ALTER TABLE `vibe` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vibe_playing`
--

DROP TABLE IF EXISTS `vibe_playing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vibe_playing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `profile_id` int(11) DEFAULT NULL,
  `vibe_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_42054C01CCFA12C9` (`profile_id`),
  KEY `IDX_42054C0154B9D743` (`vibe_id`),
  CONSTRAINT `FK_42054C0154B9D743` FOREIGN KEY (`vibe_id`) REFERENCES `vibe` (`id`),
  CONSTRAINT `FK_42054C01CCFA12C9` FOREIGN KEY (`profile_id`) REFERENCES `profile` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vibe_playing`
--

LOCK TABLES `vibe_playing` WRITE;
/*!40000 ALTER TABLE `vibe_playing` DISABLE KEYS */;
/*!40000 ALTER TABLE `vibe_playing` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2025-05-05 12:58:37
