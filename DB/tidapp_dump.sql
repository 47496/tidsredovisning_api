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
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

/*Data for the table `categories` */

insert  into `categories`(`ID`,`Category`) values 
(1,'PHP'),
(2,'HTML'),
(3,'JAVASCRIPT');

/*Table structure for table `tasks` */

CREATE TABLE `tasks` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Time` time NOT NULL,
  `Date` date NOT NULL,
  `Description` varchar(255) COLLATE utf8_swedish_ci DEFAULT NULL,
  `categoryID` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `tasks_ibfk_1` (`categoryID`),
  CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`categoryID`) REFERENCES `categories` (`ID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

/*Data for the table `tasks` */

insert  into `tasks`(`ID`,`Time`,`Date`,`Description`,`categoryID`) values 
(1,'04:24:00','2005-06-05','hejsan',3),
(2,'05:20:00','2023-01-04','123dasd',2);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
