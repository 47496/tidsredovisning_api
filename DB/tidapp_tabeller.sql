/*
SQLyog Community
MySQL - 5.7.36 : Database - timerbyte
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `categories` */

CREATE TABLE `categories` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Category` varchar(30) COLLATE utf8_swedish_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UIX` (`Category`)
) ENGINE=InnoDB AUTO_INCREMENT=2617 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

/*Table structure for table `tasks` */

CREATE TABLE `tasks` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `categoryID` int(11) NOT NULL,
  `Time` time NOT NULL,
  `Date` date NOT NULL,
  `Description` varchar(255) COLLATE utf8_swedish_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `tasks_ibfk_1` (`categoryID`),
  CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`categoryID`) REFERENCES `categories` (`ID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=421 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
