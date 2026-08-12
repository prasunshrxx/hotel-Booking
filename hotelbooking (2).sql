-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 26, 2026 at 04:16 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hotelbooking`
--

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `booking_id` int(11) NOT NULL,
  `room_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` varchar(255) NOT NULL,
  `tprice` bigint(20) NOT NULL,
  `created_At` timestamp NOT NULL DEFAULT current_timestamp(),
  `phone_number` varchar(20) DEFAULT NULL,
  `no_of_guests` int(11) DEFAULT NULL,
  `checkin_date` date DEFAULT NULL,
  `checkout_date` date DEFAULT NULL,
  `special_request` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`booking_id`, `room_id`, `user_id`, `status`, `tprice`, `created_At`, `phone_number`, `no_of_guests`, `checkin_date`, `checkout_date`, `special_request`) VALUES
(1, 1, 2, 'cancelled', 40500, '2026-03-26 01:27:53', '8364586387453', 1, '2026-03-26', '2026-04-04', 'no chicken near room'),
(2, 5, 3, 'cancelled', 14000, '2026-03-26 02:53:28', '928349234', 1, '2026-03-26', '2026-03-30', 'must be black'),
(3, 5, 3, 'checked out', 10500, '2026-03-26 02:54:23', '21342342', 1, '2026-04-04', '2026-04-07', 'sdfsdfs'),
(5, 3, 3, 'cancelled', 112500, '2026-03-26 14:57:52', '234234234', 4, '2026-03-26', '2026-04-10', 'asdfasdfaf');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int(11) NOT NULL,
  `label` varchar(100) DEFAULT NULL,
  `price` int(11) NOT NULL,
  `no_of_guests` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `image` text DEFAULT NULL,
  `features` text DEFAULT NULL,
  `available` int(11) DEFAULT 1,
  `created_At` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `label`, `price`, `no_of_guests`, `description`, `image`, `features`, `available`, `created_At`) VALUES
(1, 'Deluxe Double Room', 45, 2, 'Spacious room with stunning garden views, perfect for couples seeking comfort and tranquility.', 'https://images.unsplash.com/photo-1590490360182-c33d57733427', 'Free WiFi, Air Conditioning, Private Bathroom, Garden View', 4, '2026-03-22 14:29:27'),
(2, 'Superior Twin Room', 50, 2, 'Comfortable twin beds in a bright, airy room with modern amenities for friends traveling together.', 'https://images.unsplash.com/photo-1566665797739-1674de7a421a', 'Free WiFi, Air Conditioning, Private Bathroom, Terrace Access', 4, '2026-03-22 14:29:27'),
(3, 'Family Suite', 75, 4, 'Generous suite with separate living area, ideal for families or groups seeking extra space.', 'https://images.unsplash.com/photo-1596394516093-501ba68a0ba6', 'Free WiFi, Air Conditioning, Private Bathroom, Living Area', 2, '2026-03-22 14:29:27'),
(4, 'Budget Single Room', 25, 1, 'Cozy single room perfect for solo travelers looking for comfort at an affordable price.', 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2', 'Free WiFi, Fan, Shared Bathroom, Garden Access', 5, '2026-03-22 14:29:27'),
(5, 'Long Stay Studio', 35, 2, 'Self-contained studio with kitchenette, designed for extended stays of a week or more.', 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267', 'Free WiFi, Air Conditioning, Private Bathroom, Kitchenette', 8, '2026-03-22 14:29:27'),
(6, 'Garden View Suite', 85, 2, 'Premium suite overlooking our beautiful garden, featuring a private balcony and luxury amenities.', 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b', 'Free WiFi, Air Conditioning, Private Bathroom, Private Balcony', 4, '2026-03-22 14:29:27');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(255) NOT NULL,
  `created_At` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_At`) VALUES
(1, 'admin', 'admin@gmail.com', '$2y$10$kOfXBgIorjqHaENrWw8i6e0AE/XYuQFsRw7IIFzMtX8xrhL.StTUy', 'admin', '2026-03-22 11:02:53'),
(2, 'Prasun Shrestha', 'ahenstha@gmail.com', '$2y$10$cnZyg9bEfn9DO3VjxmCnYeVQWD99BgPOOlOU6/2tiThiKRIzHHVNu', 'user', '2026-03-22 11:50:32'),
(3, 'Sayujya Maharjan', 'sayujya@gmail.com', '$2y$10$gT86caX0XaBnw99sX4Ad3uYpuQAOvrJRk6O5h7dDC7cFFYZAamfx2', 'user', '2026-03-26 02:52:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`),
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
