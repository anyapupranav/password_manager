create database password_manager;
use password_manager;

CREATE TABLE `login` (
  `sno` int NOT NULL AUTO_INCREMENT,
  `EmailId` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Password` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ActiveFlag` tinyint(1) DEFAULT '1',
  `DeleteFlag` tinyint(1) DEFAULT '0',
  `CreatedOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ResetToken` text COLLATE utf8mb4_general_ci,
  `ResetTokenExpiration` text COLLATE utf8mb4_general_ci,
  UNIQUE KEY `sno_UNIQUE` (`sno`)
);

CREATE TABLE `message_templates` (
  `sno` int NOT NULL AUTO_INCREMENT,
  `TemplateName` varchar(100) NOT NULL,
  `Subject` text NOT NULL,
  `Body1` text NOT NULL,
  `Body2` text,
  `DeleteFlag` tinyint(1) DEFAULT '0',
  `createdon` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `sno` (`sno`),
  UNIQUE KEY `TemplateName` (`TemplateName`)
);

CREATE TABLE `shared_accounts` (
  `sno` int NOT NULL AUTO_INCREMENT,
  `sharedaccountuniqueid` text,
  `fromsharedemailid` text,
  `tosharedemailid` text,
  `deleteflag` tinyint(1) DEFAULT '0',
  `sharedon` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `sno` (`sno`)
);

CREATE TABLE `users` (
  `sno` int NOT NULL AUTO_INCREMENT,
  `FirstName` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `LastName` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `EmailId` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `MobileNumber` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ActiveFlag` tinyint(1) DEFAULT '1',
  `DeleteFlag` tinyint(1) DEFAULT '0',
  `CreatedOn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `sno_UNIQUE` (`sno`)
);

CREATE TABLE `vault` (
  `sno` int NOT NULL AUTO_INCREMENT,
  `UniqueId` char(38) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `GroupName` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `AppName` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `UserName` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Password` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `CurrentPasswordVersion` int NOT NULL DEFAULT '1',
  `Url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `ActiveFlag` tinyint(1) DEFAULT '1',
  `DeleteFlag` tinyint(1) DEFAULT '0',
  `UserEmailId` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  UNIQUE KEY `sno_UNIQUE` (`sno`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER `vault_BEFORE_INSERT` BEFORE INSERT ON `vault` FOR EACH ROW SET NEW.UniqueId = UUID() */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

CREATE TABLE `vault_history` (
  `sno` int NOT NULL AUTO_INCREMENT,
  `UniqueId` char(38) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Password` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `PasswordVersion` int NOT NULL,
  `datecreated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `sno_UNIQUE` (`sno`)
);
