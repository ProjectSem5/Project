-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 10, 2026 at 01:57 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `scholarin`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_report_seen`
--

CREATE TABLE `admin_report_seen` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `review_count` int(11) NOT NULL DEFAULT 0,
  `seen_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `payment` varchar(20) DEFAULT NULL,
  `checkin_date` date DEFAULT NULL,
  `checkout_date` date DEFAULT NULL,
  `full_payment` varchar(50) DEFAULT 'Unpaid',
  `receipt` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `review` text DEFAULT NULL,
  `cleanliness` int(11) DEFAULT 5,
  `services` int(11) DEFAULT 5,
  `staff` int(11) DEFAULT 5
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `availability` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `location` varchar(2) DEFAULT 'JB',
  `max_units` int(11) NOT NULL DEFAULT 3
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `title`, `price`, `availability`, `description`, `image`, `location`, `max_units`) VALUES
(3, 'Double bedroom', 231.00, 'Available', 'A cozy room featuring one comfortable double bed, perfect for couples or solo travelers. Designed for a relaxing stay with essential amenities and a comfortable atmosphere. Ideal for both short getaways and longer stays.', 'double bedroom.jpg', 'JB', 3),
(4, 'Twin Bedroom', 150.00, 'Available', 'Comfortable twin bedroom featuring two single beds, air conditioning, free Wi-Fi, a private bathroom, and a relaxing atmosphere suitable for friends, family members, or business travelers. The room also includes basic amenities to ensure a pleasant and convenient stay.', 'KL DOUBLE.jpg', 'JB', 3),
(5, 'Single', 125.00, 'Available', 'Cozy single bedroom featuring one single bed, air conditioning, free Wi-Fi, and a private bathroom. The room is designed to provide a comfortable and relaxing stay, making it suitable for solo travelers or short business trips. Basic amenities are also provided for convenience and comfort.', 'JB SINGLE.jpeg', 'KL', 3),
(6, 'Master Bedroom', 300.00, 'Available', 'Cozy master bedroom available for rent featuring a comfortable bed, air conditioning, and free Wi-Fi, offering a clean and quiet environment ideal for working professionals or couples seeking a relaxing and spacious place to stay.', 'JB master.jfif', 'KL', 3),
(7, 'Double Bedroom', 220.00, 'Available', 'Bright double bedroom available for rent, fitted with a double bed, air conditioning, and reliable Wi-Fi, offering a comfortable shared-style space ideal for couples or two occupants looking for a practical and cozy stay.', 'JB DOUBLE.jfif', 'JB', 3),
(8, 'Twin Bedroom', 160.00, 'Unavailable', 'Spacious twin room for rent comes with two separate single beds, air-conditioning, and internet access, offering a well-maintained and peaceful setting suitable for students or professionals looking for affordable shared accommodation with comfort and convenience.', 'KL TWIN.jfif', 'KL', 3);

-- --------------------------------------------------------

--
-- Table structure for table `room_reports`
--

CREATE TABLE `room_reports` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `generated_at` datetime NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `report_json` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_reports`
--

INSERT INTO `room_reports` (`id`, `room_id`, `generated_at`, `created_by`, `report_json`) VALUES
(26, 8, '2026-05-28 09:18:55', 7, '{\"room_id\":8,\"room_title\":\"Twin Bedroom\",\"generated_at\":\"2026-05-28 09:18:55\",\"generated_by\":\"7\",\"bookings\":[]}');

-- --------------------------------------------------------

--
-- Table structure for table `system_activities`
--

CREATE TABLE `system_activities` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_activities`
--

INSERT INTO `system_activities` (`id`, `user_id`, `user_name`, `role`, `action`, `details`, `created_at`) VALUES
(5, 7, 'Admin', 'admin', 'Changed own password', 'Password reset', '2026-05-28 15:24:52'),
(6, 7, 'Admin', 'admin', 'Changed own password', 'Password reset', '2026-05-28 15:25:00'),
(7, 7, 'Admin', 'admin', 'Changed own password', 'Password reset', '2026-05-28 15:25:45');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL,
  `ic` varchar(50) NOT NULL,
  `location` varchar(2) DEFAULT 'JB'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `ic`, `location`) VALUES
(7, 'Admin', 'admin@gmail.com', '123', 'admin', '', 'JB'),
(8, 'Staff', 'staff@gmail.com', '1234', 'staff', '', 'KL'),
(11, 'Chong', 'user@gmail.com', '1234', 'customer', '123', 'KL'),
(12, 'Bla', '12@gmail.com', '234', 'customer', '123', 'JB');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_report_seen`
--
ALTER TABLE `admin_report_seen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_admin_room` (`admin_id`,`room_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `room_reports`
--
ALTER TABLE `room_reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_activities`
--
ALTER TABLE `system_activities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_report_seen`
--
ALTER TABLE `admin_report_seen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `room_reports`
--
ALTER TABLE `room_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `system_activities`
--
ALTER TABLE `system_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
