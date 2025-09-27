-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 27, 2025 at 01:01 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ride_sharing_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `aadhar` varchar(20) NOT NULL,
  `license` varchar(50) NOT NULL,
  `languages` varchar(100) DEFAULT NULL,
  `car_model` varchar(100) NOT NULL,
  `car_capacity` int(11) NOT NULL,
  `car_number` varchar(20) NOT NULL,
  `price_per_km` decimal(6,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `outstation` varchar(3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drivers`
--

INSERT INTO `drivers` (`id`, `name`, `phone`, `password`, `aadhar`, `license`, `languages`, `car_model`, `car_capacity`, `car_number`, `price_per_km`, `created_at`, `outstation`) VALUES
(23, 'Ravi Kumar', '9876543210', 'apple123', '123456789012', 'DL01AB1234', 'Hindi,English', 'Maruti Swift', 4, 'KA01AB1234', 4.00, '2025-09-09 17:23:06', 'Yes'),
(24, 'Suresh Reddy', '9876543211', 'banana456', '123456789013', 'DL01AB1235', 'Telugu,English', 'Hyundai i20', 5, 'AP09CD5678', 5.00, '2025-09-09 17:23:06', 'No'),
(25, 'Anil Sharma', '9876543212', 'cherry789', '123456789014', 'DL01AB1236', 'Hindi,Tamil', 'Honda Amaze', 4, 'MH12EF3456', 4.00, '2025-09-09 17:23:06', 'Yes'),
(26, 'Karthik Raj', '9876543213', 'grape321', '123456789015', 'DL01AB1237', 'English,Tamil', 'Toyota Etios', 6, 'TN10GH7890', 6.00, '2025-09-09 17:23:06', 'No'),
(27, 'Praveen Yadav', '9876543214', 'orange654', '123456789016', 'DL01AB1238', 'Hindi,Telugu', 'Maruti Dzire', 4, 'UP32IJ1234', 4.00, '2025-09-09 17:23:06', 'Yes'),
(28, 'Vijay Nair', '9876543215', 'peach987', '123456789017', 'DL01AB1239', 'English,Hindi', 'Honda City', 5, 'KL07KL5678', 5.00, '2025-09-09 17:23:06', 'No'),
(29, 'Arjun Mehta', '9876543216', 'mango147', '123456789018', 'DL01AB1240', 'Tamil,English', 'Ford Figo', 4, 'GJ01MN2345', 4.00, '2025-09-09 17:23:06', 'Yes'),
(30, 'Ramesh Gowda', '9876543217', 'papaya258', '123456789019', 'DL01AB1241', 'Hindi,English', 'Toyota Innova', 6, 'KA05OP6789', 6.00, '2025-09-09 17:23:06', 'Yes'),
(31, 'Sathish Kumar', '9876543218', 'kiwi369', '123456789020', 'DL01AB1242', 'Tamil,Hindi', 'Hyundai Verna', 5, 'TN22QR1234', 5.00, '2025-09-09 17:23:06', 'No'),
(32, 'Manoj Singh', '9876543219', 'berry741', '123456789021', 'DL01AB1243', 'Hindi,English', 'Maruti Baleno', 4, 'RJ14ST5678', 4.00, '2025-09-09 17:23:06', 'Yes'),
(33, 'Ajay Varma', '9876543220', 'melon852', '123456789022', 'DL01AB1244', 'Telugu,Hindi', 'Tata Nexon', 5, 'TS09UV1234', 5.00, '2025-09-09 17:23:06', 'No'),
(34, 'Rahul Desai', '9876543221', 'lemon963', '123456789023', 'DL01AB1245', 'English,Hindi', 'Hyundai Creta', 6, 'MH01WX5678', 6.00, '2025-09-09 17:23:06', 'Yes'),
(35, 'Vivek Rao', '9876543222', 'guava159', '123456789024', 'DL01AB1246', 'Telugu,Tamil', 'Honda Jazz', 4, 'AP10YZ1234', 4.00, '2025-09-09 17:23:06', 'No'),
(36, 'Naveen Kumar', '9876543223', 'plum753', '123456789025', 'DL01AB1247', 'Tamil,English', 'Toyota Corolla', 5, 'TN05AB9876', 5.00, '2025-09-09 17:23:06', 'Yes'),
(37, 'Deepak Mishra', '9876543224', 'pear852', '123456789026', 'DL01AB1248', 'Hindi,Tamil', 'Suzuki Ciaz', 4, 'UP16CD4321', 4.00, '2025-09-09 17:23:06', 'No'),
(38, 'Sanjay Pillai', '9876543225', 'fig951', '123456789027', 'DL01AB1249', 'English,Telugu', 'Maruti Ertiga', 6, 'KL11EF7654', 6.00, '2025-09-09 17:23:06', 'Yes'),
(39, 'Mahesh Patil', '9876543226', 'date357', '123456789028', 'DL01AB1250', 'Hindi,English', 'Hyundai Venue', 5, 'MH20GH6543', 5.00, '2025-09-09 17:23:06', 'No'),
(40, 'Lokesh Reddy', '9876543227', 'apricot159', '123456789029', 'DL01AB1251', 'Telugu,English', 'Tata Altroz', 4, 'AP28IJ8765', 4.00, '2025-09-09 17:23:06', 'Yes'),
(41, 'Rohit Shetty', '9876543228', 'lychee753', '123456789030', 'DL01AB1252', 'Hindi,Telugu', 'Maruti Brezza', 5, 'KA19KL5432', 5.00, '2025-09-09 17:23:06', 'No'),
(42, 'Kiran Babu', '9876543229', 'dragonfruit951', '123456789031', 'DL01AB1253', 'Tamil,Hindi', 'Toyota Camry', 6, 'TN07MN2109', 6.00, '2025-09-09 17:23:06', 'Yes'),
(43, 'Shivani R', '9876543230', 'jackfruit456', '123456789032', 'DL01AB1254', 'Hindi,English', 'Mahindra Thar', 4, 'MH12AB1234', 6.00, '2025-09-09 17:27:14', 'Yes'),
(44, 'Nikhil S', '9876543231', 'coconut789', '123456789033', 'DL01AB1255', 'Telugu,English', 'Maruti 800', 2, 'AP09CD5679', 3.00, '2025-09-09 17:27:14', 'No'),
(45, 'Ananya P', '9876543232', 'pomegranate123', '123456789034', 'DL01AB1256', 'Tamil,Hindi', 'Hyundai Santro', 3, 'TN10GH7891', 4.00, '2025-09-09 17:27:14', 'Yes'),
(46, 'Raghav M', '9876543233', 'strawberry654', '123456789035', 'DL01AB1257', 'English,Hindi', 'Ford EcoSport', 4, 'KA01EF3457', 5.00, '2025-09-09 17:27:14', 'No'),
(47, 'Priya K', '9876543234', 'blueberry321', '123456789036', 'DL01AB1258', 'Hindi,Tamil', 'Tata Tiago', 4, 'UP32IJ1235', 4.00, '2025-09-09 17:27:14', 'Yes'),
(48, 'Varun G', '9876543235', 'raspberry852', '123456789037', 'DL01AB1259', 'English,Hindi', 'Honda WR-V', 5, 'KL07KL5679', 5.00, '2025-09-09 17:27:14', 'No'),
(49, 'Meera S', '9876543236', 'blackberry963', '123456789038', 'DL01AB1260', 'Tamil,English', 'Maruti Celerio', 3, 'GJ01MN2346', 3.00, '2025-09-09 17:27:14', 'Yes'),
(50, 'Aditya T', '9876543237', 'cranberry147', '123456789039', 'DL01AB1261', 'Hindi,English', 'Toyota Glanza', 4, 'KA05OP6790', 4.00, '2025-09-09 17:27:14', 'Yes'),
(51, 'Ishaan R', '9876543238', 'mulberry258', '123456789040', 'DL01AB1262', 'Telugu,English', 'Hyundai i10', 3, 'TN22QR1235', 3.00, '2025-09-09 17:27:14', 'No'),
(52, 'Tanvi N', '9876543239', 'tamarind369', '123456789041', 'DL01AB1263', 'English,Hindi', 'Honda Amaze', 4, 'RJ14ST5679', 4.00, '2025-09-09 17:27:14', 'Yes'),
(53, 'Karthik S', '9876543240', 'custardapple741', '123456789042', 'DL01AB1264', 'Hindi,Telugu', 'Tata Nexon', 4, 'TS09UV1235', 4.00, '2025-09-09 17:27:14', 'No'),
(54, 'Sana P', '9876543241', 'starfruit159', '123456789043', 'DL01AB1265', 'English,Tamil', 'Hyundai Venue', 5, 'MH01WX5679', 5.00, '2025-09-09 17:27:14', 'Yes'),
(55, 'Dev R', '9876543242', 'durian753', '123456789044', 'DL01AB1266', 'Telugu,Hindi', 'Maruti Swift', 2, 'AP10YZ1235', 2.00, '2025-09-09 17:27:14', 'No'),
(56, 'Naina M', '9876543243', 'sapota852', '123456789045', 'DL01AB1267', 'Tamil,English', 'Toyota Corolla', 5, 'TN05AB9877', 5.00, '2025-09-09 17:27:14', 'Yes'),
(57, 'Amit V', '9876543244', 'persimmon951', '123456789046', 'DL01AB1268', 'Hindi,Tamil', 'Suzuki Celerio', 3, 'UP16CD4322', 3.00, '2025-09-09 17:27:14', 'No'),
(58, 'Ritu J', '9876543245', 'watermelon357', '123456789047', 'DL01AB1269', 'English,Telugu', 'Maruti Ertiga', 6, 'KL11EF7655', 6.00, '2025-09-09 17:27:14', 'Yes'),
(59, 'Siddharth P', '9876543246', 'pineapple159', '123456789048', 'DL01AB1270', 'Hindi,English', 'Hyundai Creta', 5, 'MH20GH6544', 5.00, '2025-09-09 17:27:14', 'No'),
(60, 'Ankit R', '9876543247', 'passionfruit753', '123456789049', 'DL01AB1271', 'Telugu,English', 'Tata Altroz', 4, 'AP28IJ8766', 4.00, '2025-09-09 17:27:14', 'Yes'),
(61, 'Pooja S', '9876543248', 'longan852', '123456789050', 'DL01AB1272', 'Hindi,Telugu', 'Maruti Brezza', 5, 'KA19KL5433', 5.00, '2025-09-09 17:27:14', 'No'),
(62, 'Rhea K', '9876543249', 'olive951', '123456789051', 'DL01AB1273', 'Tamil,Hindi', 'Toyota Camry', 6, 'TN07MN2110', 6.00, '2025-09-09 17:27:14', 'Yes'),
(63, 'Ravi varma', '9949705226', 'Ravivarma@99', '123456789123', 'DL01AB1288', 'hindi,english', 'Honda Amaze', 4, 'UP32IJ1999', 9.00, '2025-09-22 18:28:46', '1'),
(64, 'Hari rama', '9949705200', 'Harirama@99', '123456789000', 'DL01AB1000', 'hindi,english', 'Honda Amaze', 6, 'UP32IJ1000', 12.00, '2025-09-22 18:44:23', 'yes');

-- --------------------------------------------------------

--
-- Table structure for table `ride_requests`
--

CREATE TABLE `ride_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `request_time` datetime DEFAULT current_timestamp(),
  `ride_datetime` datetime NOT NULL,
  `ride_type` enum('drop','pick','roundtrip') NOT NULL,
  `pickup_location` varchar(255) NOT NULL,
  `drop_location` varchar(255) NOT NULL,
  `status` enum('waiting','accepted','rejected') DEFAULT 'waiting',
  `notification_sent` tinyint(1) DEFAULT 0,
  `post_ride` enum('yes','no') NOT NULL DEFAULT 'no',
  `available_seats` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ride_requests`
--

INSERT INTO `ride_requests` (`id`, `user_id`, `driver_id`, `request_time`, `ride_datetime`, `ride_type`, `pickup_location`, `drop_location`, `status`, `notification_sent`, `post_ride`, `available_seats`) VALUES
(16, 1, 63, '2025-09-21 23:22:46', '2025-09-23 12:30:00', 'pick', 'Amrita Vishwa Vidyapeetham (Chennai, Tamil Nadu, India)', 'VR Chennai Mall (Chennai, Tamil Nadu, India)', 'accepted', 1, 'yes', 1),
(18, 1, 64, '2025-09-23 02:16:58', '2025-09-23 12:30:00', 'roundtrip', 'Amrita Vishwa Vidyapeetham (Chennai, Tamil Nadu, India)', 'VR Chennai Mall (Chennai, Tamil Nadu, India)', 'accepted', 1, 'no', 0),
(19, 1, 55, '2025-09-23 13:26:35', '2025-09-24 13:26:00', 'roundtrip', 'Amrita Vishwa Vidyapeetham (Chennai, Tamil Nadu, India)', 'Marina Beach (Chennai, Tamil Nadu, India)', 'waiting', 1, 'no', 0),
(20, 1, 50, '2025-09-23 13:29:40', '2025-09-24 15:29:00', 'roundtrip', 'Amritsar (Punjab, India)', 'Amrita Vishwa Vidyapeetham (Ettimadai, Tamil Nadu, India)', 'waiting', 1, 'no', 0),
(21, 2, 60, '2025-09-26 16:25:24', '2025-09-29 06:00:00', 'roundtrip', 'Amrita Vishwa Vidyapeetham (Chennai, Tamil Nadu, India)', 'Phoenix Mall (Chennai, Tamil Nadu, India)', 'accepted', 1, 'yes', 2),
(22, 2, 33, '2025-09-26 16:26:20', '2025-09-29 06:00:00', 'roundtrip', 'Amrita Vishwa Vidyapeetham (Chennai, Tamil Nadu, India)', 'Phoenix Mall (Chennai, Tamil Nadu, India)', 'rejected', 1, 'no', 0),
(23, 2, 29, '2025-09-26 16:27:08', '2025-09-29 06:00:00', 'roundtrip', 'Amrita Vishwa Vidyapeetham (Chennai, Tamil Nadu, India)', 'Phoenix Mall (Chennai, Tamil Nadu, India)', 'accepted', 1, 'yes', 2),
(24, 3, 39, '2025-09-26 17:29:19', '2025-09-27 08:00:00', 'drop', 'Amrita Vishwa Vidyapeetham (Chennai, Tamil Nadu, India)', 'Chennai Central Railway Station (Chennai, Tamil Nadu, India)', 'accepted', 1, 'yes', 2),
(25, 3, 25, '2025-09-26 17:30:07', '2025-09-27 08:00:00', 'drop', 'Amrita Vishwa Vidyapeetham (Chennai, Tamil Nadu, India)', 'Chennai Central Railway Station (Chennai, Tamil Nadu, India)', 'rejected', 1, 'no', 0),
(26, 3, 45, '2025-09-26 17:46:10', '2025-09-27 08:00:00', 'drop', 'Amrita Vishwa Vidyapeetham (Chennai, Tamil Nadu, India)', 'Chennai Central Railway Station (Chennai, Tamil Nadu, India)', 'waiting', 1, 'no', 0),
(27, 3, 25, '2025-09-26 17:47:01', '2025-09-28 08:00:00', 'roundtrip', 'VR Chennai Mall (Chennai, Tamil Nadu, India)', 'Marina Beach (Chennai, Tamil Nadu, India)', 'rejected', 1, 'yes', 1),
(28, 4, 50, '2025-09-26 18:52:04', '2025-09-30 10:30:00', 'drop', 'Red Fort (Delhi, India)', 'Kanyakumari (Tamil Nadu, India)', 'waiting', 1, 'no', 0),
(29, 5, 40, '2025-09-26 19:53:54', '2025-09-30 10:00:00', 'drop', 'Periyapalayam (Tamil Nadu, India)', 'Chennai Central Railway Station (Chennai, Tamil Nadu, India)', 'accepted', 1, 'no', 0),
(30, 6, 25, '2025-09-27 15:34:42', '2025-09-27 10:00:00', 'drop', 'Amrita Vishwa Vidyapeetham (Chennai, Tamil Nadu, India)', 'Nexus Vijaya Forum Mall (Chennai, Tamil Nadu, India)', 'accepted', 1, 'no', 0),
(31, 6, 51, '2025-09-27 15:46:46', '2025-09-27 10:00:00', 'drop', 'Amrita Vishwa Vidyapeetham (Chennai, Tamil Nadu, India)', 'Nexus Vijaya Forum Mall (Chennai, Tamil Nadu, India)', 'accepted', 1, 'no', 0),
(32, 6, 56, '2025-09-27 15:47:32', '2025-09-27 10:00:00', 'drop', 'Amrita Vishwa Vidyapeetham (Chennai, Tamil Nadu, India)', 'Nexus Vijaya Forum Mall (Chennai, Tamil Nadu, India)', 'rejected', 1, 'no', 0),
(33, 6, 32, '2025-09-27 15:48:17', '2025-09-27 10:00:00', 'drop', 'Amrita Vishwa Vidyapeetham (Chennai, Tamil Nadu, India)', 'Nexus Vijaya Forum Mall (Chennai, Tamil Nadu, India)', 'accepted', 1, 'yes', 1),
(34, 6, 55, '2025-09-27 15:53:26', '2025-09-27 10:00:00', 'drop', 'Amrita Vishwa Vidyapeetham (Chennai, Tamil Nadu, India)', 'Nexus Vijaya Forum Mall (Chennai, Tamil Nadu, India)', 'waiting', 1, 'no', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `admission_no` varchar(20) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `department` varchar(50) NOT NULL,
  `year` int(11) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `admission_no`, `gender`, `department`, `year`, `password_hash`, `created_at`) VALUES
(1, 'Shivani Kanchibotla', 'CH.EN.U4AIE22022', 'Female', 'AIE', 4, '$2y$10$1GHTIOf3QQDfCVAKR8n1R.IdbNdWBpA/crCetUtDYRkjh2SniT7RK', '2025-09-05 13:28:11'),
(2, 'Harshini Kollabathula', 'CH.EN.U4AIE22073', 'Female', 'AIE', 4, '$2y$10$gJDgAagM/5Czzlprq4mmHeDFcgR8KkcvKmNeNRLX3gLiCpZcPYroG', '2025-09-25 13:42:13'),
(3, 'Anjani Vetury', 'CH.EN.U4AIE22083', 'Female', 'AIE', 4, '$2y$10$XGUQhcvMZxXov/XJeXQ3MOofgaCZ3a3.Jnkr1VqpkOPNBJKndywTO', '2025-09-26 11:56:24'),
(4, 'Chethana Kantipudi', 'CH.EN.U4AIE22026', 'Female', 'AIE', 4, '$2y$10$6gZ0EwwLUwA84eCqDHp7BudlHLDTrvIQLeeAGYr2MByQ.6hnuOEc.', '2025-09-26 13:18:47'),
(5, 'Ramkumar Kanchibotla', 'CH.EN.U4AIE22089', 'Male', 'CSE', 4, '$2y$10$gM9CxMpegYdqj0GPSQPjGeg5RUW1K6DSzrCp.ssOdU5rEmFELJ3sW', '2025-09-26 14:20:04'),
(6, 'Harshitha', 'CH.EN.U4CSE22050', 'Female', 'CSE', 4, '$2y$10$S.0yC5w5BtAA4O52m/Me8eBNBzdKQQNK9JMRY5XiuD7VIo2XN11Oe', '2025-09-27 09:45:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `aadhar` (`aadhar`),
  ADD UNIQUE KEY `license` (`license`),
  ADD UNIQUE KEY `car_number` (`car_number`);

--
-- Indexes for table `ride_requests`
--
ALTER TABLE `ride_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admission_no` (`admission_no`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `ride_requests`
--
ALTER TABLE `ride_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ride_requests`
--
ALTER TABLE `ride_requests`
  ADD CONSTRAINT `ride_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `ride_requests_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
