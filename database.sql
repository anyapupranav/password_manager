CREATE DATABASE  password_manager;
USE password_manager;

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

INSERT INTO `message_templates` VALUES (1,'welcome mail','Welcome to Password Manager','<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"600\"><tr><td align=\"center\" bgcolor=\"#f0f0f0\"><img src=\"web-a.safesearch.co.in/password_manager/img/mailbanner.png\" alt=\"Your Company Logo\"  width=\"600\" height=\"165\"></td></tr><tr><td bgcolor=\"#ffffff\" style=\"padding: 40px 30px 40px 30px;\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr><td style=\"color: #333; font-family: Arial, sans-serif; font-size: 24px;\"><b>Welcome to Password Manager </b></td></tr><tr><td style=\"padding: 20px 0 30px 0; color: #666; font-family: Arial, sans-serif; font-size: 16px; line-height: 1.6;\">Thank you for signing up for an account in our password manager web app. We\'re excited to have you as a member of our community! <br><br> Your account has been successfully created in password manager web app with the following details: <br><br> <ul><li><b>First Name:</b> ','</li></ul><p> <a href=\"web-a.safesearch.co.in/password_manager/\"> Click</a> here to login into your account.</p><br><p> If you have any questions or need assistance, please refer to our <a href=\"web-a.safesearch.co.in/password_manager/documentation\">documentation</a> or check our FAQ\'s section. </p></td></tr></table></td></tr><tr><td bgcolor=\"#f0f0f0\" style=\"padding: 20px 30px 20px 30px;\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr><td style=\"color: #888; font-family: Arial, sans-serif; font-size: 12px; text-align: center;\"><?php echo date(\'Y\'); ?> Password Manager V1.0</td></tr></table></td></tr></table>',0,'2023-09-14 17:38:24'),(2,'otp mail','OTP','<div style=\"margin:0;padding:10px\"><div class=\"adM\"></div><table style=\"max-width:600px;background:#fff;margin:auto\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\"><tbody><tr> <td align=\"left\" style=\"font-family:Arial,Helvetica,sans-serif\"> <table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tbody><tr><td>&nbsp;</td> </tr><tr> <td><p style=\"font-size:16px\">Hi,<br></p><p>You are trying to reset your login password for Password Manager Website to verify this is actually you, ','</p><p>This link is valid for 1 hour, please do not share this mail with anybody.</p></td> </tr><tr><td><p style=\"font-size:12px\">This email is auto-generated so please do not reply to this email as we will be unable to respond from this email address. Please connect with us on <span> <a href=\"mailto:test.support.passwordmanager@email.com\" style=\"color:#bc0069;font-size:12px;text-decoration:none\" target=\"_blank\">test.support.passwordmanager@email.com</a></span> for any queries. </p></td> </tr> <tr><td> <h3 style=\"font-size:16px\">Thank You!<br>Mail Bot<br>Password Manager </h3></td> </tr> <tr><td>&nbsp;</td></tr></tbody></table></td></tr></tbody></table></div>',0,'2023-09-14 19:26:51');

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
);

DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER `vault_BEFORE_INSERT` BEFORE INSERT ON `vault` FOR EACH ROW SET NEW.UniqueId = UUID() */;;
DELIMITER ;

CREATE TABLE `vault_history` (
  `sno` int NOT NULL AUTO_INCREMENT,
  `UniqueId` char(38) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Password` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `PasswordVersion` int NOT NULL,
  `datecreated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `GroupName` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `AppName` text COLLATE utf8mb4_general_ci,
  `UserName` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Url` text COLLATE utf8mb4_general_ci,
  `Notes` text COLLATE utf8mb4_general_ci,
  UNIQUE KEY `sno_UNIQUE` (`sno`)
);

CREATE TABLE `version` (
  `Sno` int NOT NULL AUTO_INCREMENT,
  `AppVersion` varchar(10) NOT NULL,
  `DeleteFlag` tinyint(1) DEFAULT '0',
  `CreatedOn` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `Sno` (`Sno`),
  UNIQUE KEY `Version` (`AppVersion`)
);

INSERT INTO `version` VALUES (1,'v1.0.0',0,'2023-09-14 07:01:09'),(2,'v1.0.1',0,'2023-09-19 16:56:54');

