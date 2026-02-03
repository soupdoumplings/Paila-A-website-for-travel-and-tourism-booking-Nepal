/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.15-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: np03cs4a240006
-- ------------------------------------------------------
-- Server version	10.11.15-MariaDB

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
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tour_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `contact_email` varchar(100) NOT NULL,
  `travel_date` date NOT NULL,
  `travelers` int(11) NOT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `tour_guide_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `tour_id` (`tour_id`),
  KEY `user_id` (`user_id`),
  KEY `tour_guide_id` (`tour_guide_id`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`tour_id`) REFERENCES `tours` (`id`),
  CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`tour_guide_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
INSERT INTO `bookings` VALUES
(1,1,NULL,'John Doe','john@example.com','2026-04-15',2,'confirmed',NULL,'2026-02-02 14:38:26'),
(2,2,NULL,'Jane Smith','jane@example.com','2026-05-10',4,'cancelled',NULL,'2026-02-02 14:38:26'),
(3,3,NULL,'Alex Johnson','alex@example.com','2026-10-20',1,'confirmed',NULL,'2026-02-02 14:38:26'),
(4,1,2,'Rajesh hamal','rajeshhamaltest@paila.user','2026-03-12',1,'confirmed',3,'2026-02-02 15:52:23'),
(5,11,6,'Person','Person@gmail.com','2000-01-01',1,'confirmed',3,'2026-02-03 12:29:49'),
(6,7,3,'fdm','fat@gmail.com','9999-01-01',1,'cancelled',NULL,'2026-02-03 13:07:03');
/*!40000 ALTER TABLE `bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inquiries`
--

DROP TABLE IF EXISTS `inquiries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inquiries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','replied') DEFAULT 'new',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inquiries`
--

LOCK TABLES `inquiries` WRITE;
/*!40000 ALTER TABLE `inquiries` DISABLE KEYS */;
INSERT INTO `inquiries` VALUES
(3,'Phone','Phone@gmail.com','833213321','Wsp i know domain expansion','new','2026-02-03 12:44:20');
/*!40000 ALTER TABLE `inquiries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `context_type` enum('booking','private_request') NOT NULL,
  `context_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
INSERT INTO `messages` VALUES
(1,6,1,'booking',5,'UwU',0,'2026-02-03 12:30:39'),
(2,1,6,'booking',5,'gay',0,'2026-02-03 12:31:01'),
(3,6,1,'booking',5,'*snuggles*',0,'2026-02-03 12:31:02'),
(4,6,1,'booking',5,'&#039; OR &#039;1&#039;=&#039;1',0,'2026-02-03 12:31:32'),
(5,6,1,'booking',5,'&#039;',0,'2026-02-03 12:31:45'),
(6,1,6,'booking',5,'gay',0,'2026-02-03 12:32:07'),
(7,6,1,'booking',5,'hello',0,'2026-02-03 12:32:24'),
(8,1,6,'booking',5,'hi',0,'2026-02-03 12:32:26'),
(9,6,1,'booking',5,'hello',0,'2026-02-03 12:32:33'),
(10,3,1,'booking',6,'hi',0,'2026-02-03 13:08:20'),
(11,1,3,'booking',6,'hello',0,'2026-02-03 13:09:15'),
(12,3,1,'booking',6,'noob',0,'2026-02-03 13:09:27'),
(13,3,1,'booking',6,'noob',0,'2026-02-03 13:09:32'),
(14,1,3,'booking',6,'hello',0,'2026-02-03 13:09:34'),
(15,3,1,'booking',6,'noob',0,'2026-02-03 13:09:35'),
(16,3,1,'booking',6,'noob',0,'2026-02-03 13:09:40'),
(17,3,1,'booking',6,'noob',0,'2026-02-03 13:09:44'),
(18,3,1,'booking',6,'noob',0,'2026-02-03 13:09:54');
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recipient_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `recipient_id` (`recipient_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES
(1,1,'New Booking #4','New booking from Rajesh hamal (rajeshhamaltest@paila.user).','admin/manage_bookings.php?id=4',1,'2026-02-02 15:52:23'),
(2,2,'Booking Received','Your booking #4 has been successfully submitted.','my_bookings.php',0,'2026-02-02 15:52:23'),
(3,1,'New Inquiry Received','You have a new message from Ujwal Shrestha (np03cs4a240006@heraldcollege.edu.np).','admin/manage_inquiries.php',1,'2026-02-03 12:08:23'),
(4,4,'New Inquiry Received','You have a new message from Ujwal Shrestha (np03cs4a240006@heraldcollege.edu.np).','admin/manage_inquiries.php',0,'2026-02-03 12:08:23'),
(5,1,'New Booking #ORD-41B51A','New booking from Puri (PuriPuriPRISONER@gmail.com).','admin/manage_bookings.php?id=ORD-41B51A',1,'2026-02-03 12:28:16'),
(6,4,'New Booking #ORD-41B51A','New booking from Puri (PuriPuriPRISONER@gmail.com).','admin/manage_bookings.php?id=ORD-41B51A',0,'2026-02-03 12:28:16'),
(7,6,'Booking Received','Your booking #ORD-41B51A has been successfully submitted.','my_bookings.php',1,'2026-02-03 12:28:16'),
(8,1,'New Booking #5','New booking from Person (Person@gmail.com).','admin/manage_bookings.php?id=5',1,'2026-02-03 12:29:49'),
(9,4,'New Booking #5','New booking from Person (Person@gmail.com).','admin/manage_bookings.php?id=5',0,'2026-02-03 12:29:49'),
(10,6,'Booking Received','Your booking #5 has been successfully submitted.','my_bookings.php',1,'2026-02-03 12:29:49'),
(11,1,'New Message: Booking #5','User sent a message regarding booking #5','admin/booking_detail.php?id=5',1,'2026-02-03 12:30:39'),
(12,4,'New Message: Booking #5','User sent a message regarding booking #5','admin/booking_detail.php?id=5',0,'2026-02-03 12:30:39'),
(13,6,'New Message regarding Booking #5','You have a new message from the admin.','user_booking_detail.php?id=5',1,'2026-02-03 12:31:01'),
(14,1,'New Message: Booking #5','User sent a message regarding booking #5','admin/booking_detail.php?id=5',1,'2026-02-03 12:31:02'),
(15,4,'New Message: Booking #5','User sent a message regarding booking #5','admin/booking_detail.php?id=5',0,'2026-02-03 12:31:02'),
(16,1,'New Message: Booking #5','User sent a message regarding booking #5','admin/booking_detail.php?id=5',1,'2026-02-03 12:31:32'),
(17,4,'New Message: Booking #5','User sent a message regarding booking #5','admin/booking_detail.php?id=5',0,'2026-02-03 12:31:32'),
(18,1,'New Message: Booking #5','User sent a message regarding booking #5','admin/booking_detail.php?id=5',1,'2026-02-03 12:31:45'),
(19,4,'New Message: Booking #5','User sent a message regarding booking #5','admin/booking_detail.php?id=5',0,'2026-02-03 12:31:45'),
(20,6,'New Message regarding Booking #5','You have a new message from the admin.','user_booking_detail.php?id=5',1,'2026-02-03 12:32:07'),
(21,1,'New Message: Booking #5','User sent a message regarding booking #5','admin/booking_detail.php?id=5',1,'2026-02-03 12:32:24'),
(22,4,'New Message: Booking #5','User sent a message regarding booking #5','admin/booking_detail.php?id=5',0,'2026-02-03 12:32:24'),
(23,6,'New Message regarding Booking #5','You have a new message from the admin.','user_booking_detail.php?id=5',1,'2026-02-03 12:32:26'),
(24,1,'New Message: Booking #5','User sent a message regarding booking #5','admin/booking_detail.php?id=5',1,'2026-02-03 12:32:33'),
(25,4,'New Message: Booking #5','User sent a message regarding booking #5','admin/booking_detail.php?id=5',0,'2026-02-03 12:32:33'),
(26,1,'New Inquiry Received','You have a new message from Phone.','admin/manage_inquiries.php',1,'2026-02-03 12:44:20'),
(27,4,'New Inquiry Received','You have a new message from Phone.','admin/manage_inquiries.php',0,'2026-02-03 12:44:20'),
(28,1,'New Booking #6','New booking from fdm (fat@gmail.com).','admin/manage_bookings.php?id=6',0,'2026-02-03 13:07:03'),
(29,4,'New Booking #6','New booking from fdm (fat@gmail.com).','admin/manage_bookings.php?id=6',0,'2026-02-03 13:07:03'),
(30,3,'Booking Received','Your booking #6 has been successfully submitted.','my_bookings.php',0,'2026-02-03 13:07:03'),
(31,1,'New Message: Booking #6','User sent a message regarding booking #6','admin/booking_detail.php?id=6',0,'2026-02-03 13:08:20'),
(32,4,'New Message: Booking #6','User sent a message regarding booking #6','admin/booking_detail.php?id=6',0,'2026-02-03 13:08:20'),
(33,3,'New Message regarding Booking #6','You have a new message from the admin.','user_booking_detail.php?id=6',0,'2026-02-03 13:09:15'),
(34,1,'New Message: Booking #6','User sent a message regarding booking #6','admin/booking_detail.php?id=6',0,'2026-02-03 13:09:27'),
(35,4,'New Message: Booking #6','User sent a message regarding booking #6','admin/booking_detail.php?id=6',0,'2026-02-03 13:09:27'),
(36,1,'New Message: Booking #6','User sent a message regarding booking #6','admin/booking_detail.php?id=6',0,'2026-02-03 13:09:32'),
(37,4,'New Message: Booking #6','User sent a message regarding booking #6','admin/booking_detail.php?id=6',0,'2026-02-03 13:09:32'),
(38,3,'New Message regarding Booking #6','You have a new message from the admin.','user_booking_detail.php?id=6',0,'2026-02-03 13:09:34'),
(39,1,'New Message: Booking #6','User sent a message regarding booking #6','admin/booking_detail.php?id=6',0,'2026-02-03 13:09:35'),
(40,4,'New Message: Booking #6','User sent a message regarding booking #6','admin/booking_detail.php?id=6',0,'2026-02-03 13:09:35'),
(41,1,'New Message: Booking #6','User sent a message regarding booking #6','admin/booking_detail.php?id=6',0,'2026-02-03 13:09:40'),
(42,4,'New Message: Booking #6','User sent a message regarding booking #6','admin/booking_detail.php?id=6',0,'2026-02-03 13:09:40'),
(43,1,'New Message: Booking #6','User sent a message regarding booking #6','admin/booking_detail.php?id=6',0,'2026-02-03 13:09:44'),
(44,4,'New Message: Booking #6','User sent a message regarding booking #6','admin/booking_detail.php?id=6',0,'2026-02-03 13:09:44'),
(45,1,'New Message: Booking #6','User sent a message regarding booking #6','admin/booking_detail.php?id=6',0,'2026-02-03 13:09:54'),
(46,4,'New Message: Booking #6','User sent a message regarding booking #6','admin/booking_detail.php?id=6',0,'2026-02-03 13:09:54');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `private_requests`
--

DROP TABLE IF EXISTS `private_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `private_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `access_code` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_access_code` (`access_code`),
  CONSTRAINT `private_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `private_requests`
--

LOCK TABLES `private_requests` WRITE;
/*!40000 ALTER TABLE `private_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `private_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES
(1,'super_admin','Has full access to all settings and can manage other admins'),
(2,'admin','Can manage tours and bookings'),
(3,'user','Regular customer account'),
(4,'tour_guide','Assigned to tours, no admin access');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` varchar(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_id` (`student_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES
(1,'2461787','Ujwal Shrestha','$2y$10$HtYje0IA5GYRCxa5Jgsoh.hyD/lo6zlWUOg1TORnZplL/DKgI.csa'),
(2,'111111','Rajesh hamal','$2y$10$c1vg0NnoWzMCuc29to9ugu33cyhM0WEr4hwdulUpGcmnMpeEOSEdK');
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(100) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `department` varchar(50) NOT NULL,
  `priority` int(11) NOT NULL,
  `issue_description` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tickets`
--

LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
INSERT INTO `tickets` VALUES
(1,'Ujwal Shrestha','ujwalshrestha@gmail.com','IT',3,'Wifi issue\r\nWifi issue\r\nWifi issue\r\nWifi issue\r\nWifi issueWifi issue\r\nWifi issuev\r\nWifi issue\r\nWifi issue\r\nWifi issue\r\nWifi issue\r\nWifi issue\r\nWifi issue\r\nWifi issue\r\nWifi issue','2026-01-13 10:11:53'),
(2,'Rajesh Hamal','nepatop@gmail.com','Finance',4,'Mariovdasjjnajvbkhersvkjr3nvkj kjk jknnvjkserkber jkbsjbes','2026-01-13 10:17:01'),
(3,'gayatri','fbsnet37@gmail.com','IT',1,'f ewgrt hrth tgreh rtgewg rehregr ger','2026-01-20 11:17:21');
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tours`
--

DROP TABLE IF EXISTS `tours`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `difficulty` varchar(50) DEFAULT NULL,
  `max_group` int(11) DEFAULT NULL,
  `highlights` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `best_season` varchar(50) DEFAULT NULL,
  `altitude_max` int(11) DEFAULT NULL,
  `permit_requirements` text DEFAULT NULL,
  `itinerary` text DEFAULT NULL,
  `inclusions` text DEFAULT NULL,
  `exclusions` text DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tours`
--

LOCK TABLES `tours` WRITE;
/*!40000 ALTER TABLE `tours` DISABLE KEYS */;
INSERT INTO `tours` VALUES
(1,'Everest Base Camp Trek','Everest',165000.00,'14 Days','Experience the world\'s most iconic trek to the base of Mt. Everest. Journey through Sherpa villages, ancient monasteries, and breathtaking alpine landscapes with stunning views of the world\'s highest peaks.','trekking','Hard',12,'Scenic flight to Lukla\r\nNamche Bazaar market\r\nTengboche Monastery\r\nKala Patthar sunrise viewpoint\r\nEverest Base Camp at 5364m\r\nSherpa culture immersion','1770063851_photo-1755621089875-946937e8b609..jpg','Spring, Autumn',5545,'TIMS Card, Sagarmatha National Park Entry Permit','Arrival in Kathmandu, hotel transfer and briefing\nFly to Lukla (2840m), trek to Phakding (2610m)\nTrek to Namche Bazaar (3440m)\nAcclimatization day in Namche, optional hike to Everest View Hotel\nTrek to Tengboche (3860m)\nTrek to Dingboche (4410m)\nAcclimatization day in Dingboche\nTrek to Lobuche (4910m)\nTrek to Gorak Shep (5140m), hike to EBC (5364m)\nHike to Kala Patthar (5545m) for sunrise, descend to Pheriche\nTrek back to Namche Bazaar\nTrek to Lukla\nFly back to Kathmandu\nDeparture day','Airport transfers\nDomestic flights (Kathmandu-Lukla-Kathmandu)\nTeahouse accommodation during trek\nExperienced English-speaking guide\nPorter service (1 porter for 2 trekkers)\nAll permit fees (TIMS, National Park)\nFirst aid kit\nGovernment taxes','International flights\nNepal visa fees\nLunch and dinner in Kathmandu\nPersonal trekking equipment\nTravel insurance\nDrinks and beverages\nTips for guide and porter\nPersonal expenses',1,NULL,'2026-02-02 14:38:26'),
(2,'Annapurna Base Camp Trek','Annapurna',95000.00,'10 Days','Trek to the heart of the Annapurna Sanctuary, surrounded by towering peaks. Experience diverse landscapes from lush rhododendron forests to high alpine meadows with spectacular mountain views.','trekking','Moderate',14,'Annapurna Base Camp at 4130m\nMachhapuchhre Base Camp\nNatural hot springs at Jhinu Danda\nGurung and Magar villages\nDiverse ecosystems\nClose-up mountain views','annapurna.jpg','Spring, Autumn',4130,'TIMS Card, ACAP Entry Permit','Drive from Pokhara to Nayapul, trek to Tikhedhunga\nTrek to Ghorepani (2850m)\nSunrise at Poon Hill (3210m), trek to Tadapani\nTrek to Chhomrong (2170m)\nTrek to Bamboo (2310m)\nTrek to Deurali (3230m)\nTrek to Annapurna Base Camp (4130m) via MBC\nDescend to Bamboo\nTrek to Jhinu Danda, relax in hot springs\nTrek to Nayapul, drive back to Pokhara','Pokhara-Nayapul-Pokhara transportation\nTeahouse accommodation\nExperienced trekking guide\nPorter service\nACP and TIMS permits\nFirst aid kit\nAll government taxes','Kathmandu-Pokhara transportation\nMeals in Pokhara\nPersonal trekking gear\nTravel insurance\nBeverages\nTips\nPersonal expenses',0,NULL,'2026-02-02 14:38:26'),
(3,'Langtang Valley Trek','Langtang',75000.00,'9 Days','Discover the stunning Langtang Valley, known as the \"Valley of Glaciers\". Trek through beautiful Tamang villages, lush forests, and alongside the Langtang River with views of Langtang Lirung.','trekking','Moderate',12,'Kyanjin Gompa monastery (3870m)\nTserko Ri viewpoint (4984m)\nLangtang Glacier\nTamang heritage and culture\nCheese factories\nPanoramic mountain views','kathmandu_heritage.jpg','Spring, Autumn',4984,'TIMS Card, Langtang National Park Entry Permit','Drive from Kathmandu to Syabrubesi (1460m)\nTrek to Lama Hotel (2380m)\nTrek to Langtang Village (3430m)\nTrek to Kyanjin Gompa (3870m)\nAcclimatization day, optional hike to Tserko Ri (4984m)\nDescend to Lama Hotel\nTrek to Syabrubesi\nDrive back to Kathmandu\nReserve day for contingency','Kathmandu-Syabrubesi-Kathmandu transport\nTeahouse accommodation\nEnglish-speaking guide\nPorter service\nNational Park and TIMS fees\nFirst aid supplies\nGovernment taxes','Meals in Kathmandu\nPersonal equipment\nTravel insurance\nDrinks\nTips for staff\nEmergency evacuation\nPersonal expenses',0,NULL,'2026-02-02 14:38:26'),
(4,'Manaslu Circuit Trek','Manaslu',185000.00,'16 Days','Trek around the eighth highest mountain in the world. Experience remote villages, ancient monasteries, and cross the challenging Larkya La Pass with stunning views of Manaslu, Himlung Himal, and more.','trekking','Hard',10,'Larkya La Pass (5160m)\nManaslu Conservation Area\nRemote Tibetan Buddhist villages\nPungen Glacier\nBirendra Lake\nLess crowded alternative to Annapurna','annapurna.jpg','Spring, Autumn',5160,'Restricted Area Permit, ACAP, MCAP, TIMS','Drive Kathmandu to Soti Khola (700m)\nTrek to Machhakhola (930m)\nTrek to Jagat (1410m)\nTrek to Deng (1860m)\nTrek to Namrung (2660m)\nTrek to Lho (3180m)\nTrek to Samagaun (3530m)\nAcclimatization day in Samagaun\nTrek to Samdo (3860m)\nTrek to Dharamsala/Larkya Phedi (4460m)\nCross Larkya La Pass (5160m), descend to Bimthang (3720m)\nTrek to Tilije (2300m)\nTrek to Dharapani, drive to Besisahar\nDrive to Kathmandu\nBuffer day','All transportation (Kathmandu-trek-Kathmandu)\nBasic lodge accommodation\nExperienced guide and porter\nAll permits (RAP, MCAP, ACAP, TIMS)\nThree meals a day during trek\nFirst aid kit\nGovernment taxes and fees','Kathmandu hotel\nInternational flights\nTravel and rescue insurance\nExtra nights due to delays\nDrinks and snacks\nPersonal gear\nTips\nPersonal expenses',0,NULL,'2026-02-02 14:38:26'),
(5,'Kathmandu Valley Cultural Tour','Kathmandu',25000.00,'4 Days','Explore the rich cultural heritage of Kathmandu Valley. Visit UNESCO World Heritage Sites including ancient temples, palaces, and stupas that showcase Nepal\'s artistic and architectural brilliance.','culture','Beginner',20,'Swayambhunath Stupa (Monkey Temple)\nPashupatinath Temple\nBoudhanath Stupa\nKathmandu Durbar Square\nPatan Durbar Square\nBhaktapur Durbar Square\nTraditional Newari architecture','kathmandu_heritage.jpg','Year-round',1400,'Monument Entry Fees','Arrival, hotel check-in, evening orientation\nFull day Kathmandu sightseeing (Swayambhunath, Kathmandu Durbar Square, Patan)\nFull day Bhaktapur and Pashupatinath tour\nBoudhanath visit, departure preparation','Airport transfers\n3-star hotel accommodation with breakfast\nPrivate vehicle with driver\nEnglish-speaking guide\nAll monument entry fees\nGovernment taxes','Lunch and dinner\nInternational flights\nVisa fees\nTravel insurance\nPersonal expenses\nDrinks\nTips for guide and driver',0,NULL,'2026-02-02 14:38:26'),
(6,'Chitwan Jungle Safari','Chitwan',42000.00,'3 Days','Immerse yourself in the wilderness of Chitwan National Park. Spot endangered one-horned rhinos, Bengal tigers, and over 500 species of birds in this UNESCO World Heritage Site.','adventure','Beginner',16,'Jeep safari through jungle\nCanoe ride on Rapti River\nElephant breeding center visit\nJungle walk with naturalist\nTharu cultural dance\nBird watching\nWildlife spotting (rhino, deer, crocodiles)','chitwan.jpg','Year-round',150,'Chitwan National Park Entry Permit','Drive/fly from Kathmandu to Chitwan, evening Tharu cultural program\nFull day jungle activities (canoe ride, jungle walk, jeep safari)\nMorning bird watching, departure to Kathmandu','Kathmandu-Chitwan-Kathmandu transfers\nFull board accommodation in jungle resort\nAll jungle activities with guide\nNational Park fees\nTharu cultural show\nNaturalist guide','Meals in Kathmandu\nPersonal expenses\nDrinks and beverages\nTips for guides\nTravel insurance',0,NULL,'2026-02-02 14:38:26'),
(7,'Pokhara Adventure Package','Pokhara',38000.00,'5 Days','Experience the adventure capital of Nepal. Enjoy paragliding, boating on Phewa Lake, visiting caves, waterfalls, and stunning mountain views of the Annapurna range.','adventure','Beginner',15,'Paragliding with mountain views\nBoating on Phewa Lake\nSarangkot sunrise viewpoint\nDavis Falls and Gupteshwor Cave\nInternational Mountain Museum\nWorld Peace Pagoda\nLakeside strolls','kathmandu_heritage.jpg','Year-round',1600,'None','Drive/fly Kathmandu to Pokhara, lakeside exploration\nEarly morning Sarangkot sunrise, paragliding experience\nFull day sightseeing (Davis Falls, caves, museum, Peace Pagoda)\nLeisure day for optional activities (zip-line, bungee, ultra-light flight)\nReturn to Kathmandu','Kathmandu-Pokhara-Kathmandu transport\nHotel accommodation with breakfast\nParagliding with photos and video\nSightseeing with private vehicle\nEntry fees for monuments\nExperienced guide','Lunch and dinner\nOptional activities (ultra-light, bungee, zip-line)\nDrinks\nTravel insurance\nTips\nPersonal expenses',0,NULL,'2026-02-02 14:38:26'),
(8,'Upper Mustang Trek','Mustang',195000.00,'12 Days','Journey to the forbidden kingdom of Upper Mustang, a remote Tibetan Buddhist region with ancient walled cities, mysterious caves, and barren landscapes. Perfect for monsoon trekking in the rain shadow area.','trekking','Moderate',10,'Lo Manthang walled city\nAncient monasteries and caves\nTibetan Buddhist culture\nBarren Himalayan desert landscapes\nCho ser cave monastery\nRain shadow trek (good in monsoon)','annapurna.jpg','Monsoon, Autumn',3840,'Restricted Area Permit, ACAP, TIMS','Fly Kathmandu to Pokhara\nDrive Pokhara to Jomsom, trek to Kagbeni\nTrek to Chele (3050m)\nTrek to Syanbochen (3800m)\nTrek to Ghami (3520m)\nTrek to Tsarang (3560m)\nTrek to Lo Manthang (3840m)\nExploration day in Lo Manthang\nTrek to Drakmar (3810m)\nTrek to Ghiling (3806m)\nTrek to Chhusang (2980m)\nTrek to Jomsom, fly to Pokhara\nFly to Kathmandu','Domestic flights (Kathmandu-Pokhara, Jomsom-Pokhara-Kathmandu)\nJeep transfer Pokhara-Jomsom\nBasic lodge accommodation\nThree meals daily during trek\nExperienced guide and porter\nAll permits (RAP, ACAP, TIMS)\nFirst aid\nGovernment taxes','Kathmandu and Pokhara hotels\nMeals in cities\nInternational flights\nTravel insurance\nDrinks and snacks\nPersonal gear\nTips\nPersonal expenses\nEmergency evacuation',0,NULL,'2026-02-02 14:38:26'),
(9,'Tilicho Lake Trek','Annapurna',115000.00,'11 Days','Trek to one of the highest lakes in the world at 4919m. Combine stunning turquoise lake views with the classic Annapurna Circuit experience through diverse landscapes and cultures.','trekking','Hard',12,'Tilicho Lake (4919m) - one of world\'s highest lakes\nThorong La Pass optional (5416m)\nDiverse landscapes\nManang Valley\nTilcho Base Camp\nGangapurna glacier','annapurna.jpg','Monsoon, Autumn',4919,'TIMS, ACAP','Drive Kathmandu to Besisahar, to Chame (2710m)\nTrek to Pisang (3200m)\nTrek to Manang (3540m)\nAcclimatization in Manang\nTrek to Tilicho Base Camp (4150m)\nTrek to Tilicho Lake (4919m) and back to base camp\nTrek to Yak Kharka (4018m)\nTrek to Thorong Phedi (4450m)\nCross Thorong La (5416m), descend to Muktinath (3800m)\nDrive to Jomsom, fly to Pokhara\nDrive/fly to Kathmandu','Kathmandu-Besisahar-Kathmandu transport\nJomsom-Pokhara flight\nLodge accommodation\nAll meals during trek\nGuide and porter\nACWAP and TIMS permits\nOxygen meter and first aid\nGovernment taxes','Kathmandu/Pokhara hotels\nLunch/dinner in cities\nInternational flights\nTravel insurance\nPersonal equipment\nDrinks\nTips\nPersonal expenses',0,NULL,'2026-02-02 14:38:26'),
(10,'Rara Lake Trek','Rara Lake',145000.00,'10 Days','Discover Nepal\'s largest and deepest lake in the remote far-western region. Trek through pristine forests, encounter rare wildlife, and experience the tranquility of this hidden gem.','trekking','Moderate',12,'Rara Lake (2990m) - largest lake in Nepal\nRara National Park\nPristine alpine scenery\nJuniper and pine forests\nRare flora and fauna\nRemote Malla and Thakuri villages\nFew tourists - off the beaten path','kathmandu_heritage.jpg','Monsoon, Spring',3710,'Rara National Park Entry, TIMS','Fly Kathmandu to Nepalgunj\nFly to Talcha (Mugu) airstrip, trek to Rara Lake (2 hrs)\nFull day exploring Rara Lake and surroundings\nTrek to Chhapre (2800m)\nTrek to Jumla (2540m)\nBuffer day for flight delays\nFly Jumla to Nepalgunj\nFly to Kathmandu\nReserve days (2 days)','All domestic flights (Kathmandu-Nepalgunj-Talcha/Jumla-Kathmandu)\nLodge/camping accommodation\nAll meals during trek\nCamping equipment if needed\nExperienced guide and porter\nNational Park and TIMS fees\nFirst aid kit','Kathmandu hotel\nMeals in cities\nInternational flights\nTravel insurance\nExtra costs due to flight delays\nDrinks\nPersonal gear\nTips\nPersonal expenses',0,NULL,'2026-02-02 14:38:26'),
(11,'Dai ko chiya','Kathmandu',50.00,'1 Days','Bhatti ko chiya khana jaum','family','Easy',12,'Mitho dudh chiya','1770098744_Tea-3-1-576x1024.png',NULL,NULL,NULL,NULL,NULL,NULL,1,4,'2026-02-03 11:50:44');
/*!40000 ALTER TABLE `tours` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL DEFAULT 3,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'UjShresthaAdmin','2461787@paila.admin','$2y$10$IAt.ObChDnyTj1cpASLUkePEb3xxxCm57dCODZC/mzAvFdmQY59M6',1,'2026-02-02 14:38:26'),
(2,'Rajesh Hamal','rajeshhamaltest@paila.user','$2y$10$YNDyYzpPmhL25admCkG3JOSguCGhmcVdI0VnWrA0Aq1WmLfvwPzsi',3,'2026-02-02 15:50:51'),
(3,'guideNo1','falanogharkochora@paila.guide','$2y$10$eYFr65Ra05xU6rCX5LZVO.v1B7XZQArbqFLAruPgXDMaUHadia.x6',4,'2026-02-02 16:32:53'),
(4,'testadmin@paila.admin','testadmin@paila.admin','$2y$10$oA46LEmqV80NMmclIurPgeNk.QQ1Dcu/W6XJLUb4sf0W4R8nCZhn2',2,'2026-02-03 11:45:09'),
(5,'rohan','rohan@gmail.com','$2y$10$lnTRVi/RFq/fwbSXpdk79eqMq4/cgZ2LMTE18l6zdbc8Zs4XAMl5i',3,'2026-02-03 12:10:09'),
(6,'John Pork','Pork@gmail.com','$2y$10$IXMPP/eMfx/o.4E0s4aPA.E9oM969qw3bVgV3EzxtU1Fx61ybN5aq',3,'2026-02-03 12:22:51'),
(7,'testguide','testguide1@paila.guide','$2y$10$ZXaazBEwRkws4DeUU0eAJOF8fb4XqNLuyooowfWtcmT49.VI98mBO',4,'2026-02-03 12:46:23');
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

-- Dump completed on 2026-02-03 16:17:10
