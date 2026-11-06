-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 01, 2026 at 08:03 AM
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
-- Database: `softecoms`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_changelog`
--

CREATE TABLE `admin_changelog` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action_type` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `firstName` varchar(100) NOT NULL,
  `lastName` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `userId`, `firstName`, `lastName`, `email`, `subject`, `message`, `created_at`) VALUES
(1, 12, 'samsfasfasfsaf', 'roque', 'sammy12@gmail.com', 'fa', 'fa', '2026-05-20 13:59:27'),
(2, 12, 'samsfasfasfsaf', 'roque', 'sammy12@gmail.com', 'fa', 'fa', '2026-05-20 13:59:30'),
(3, 12, 'samsfasfasfsaf', 'roque', 'sammy12@gmail.com', 'fa', 'fa', '2026-05-20 15:13:40'),
(4, 12, 'samsfasfasfsaf', 'roque', 'sammy12@gmail.com', '', 'fa', '2026-05-20 15:13:42'),
(5, 12, 'samsfasfasfsaf', 'roque', 'sammy12@gmail.com', 'fa', 'faff', '2026-05-20 15:13:44'),
(6, 12, 'samsfasfasfsaf', 'roque', 'sammy12@gmail.com', 'fa', 'fa', '2026-05-20 15:14:45'),
(7, 12, 'samsfasfasfsaf', 'roque', 'sammy12@gmail.com', 'ga', 'ga', '2026-05-20 15:15:04'),
(8, 12, 'samsfasfasfsaf', 'roque', 'sammy12@gmail.com', 'hahahah', 'ahaha', '2026-05-20 15:15:07'),
(9, 12, 'samsfasfasfsaf', 'roque', 'sammy12@gmail.com', 'fa', 'fa', '2026-05-20 15:23:17'),
(10, 12, 'samsfasfasfsaf', 'roque', 'sammy12@gmail.com', 'fa', 'fa', '2026-05-20 15:23:20'),
(11, 12, 'samsfasfasfsaf', 'roque', 'sammy12@gmail.com', 'fa', 'fa', '2026-05-20 15:24:27'),
(12, 12, 'samsfasfasfsaf', 'roque', 'sammy12@gmail.com', 'goodwow', 'wow', '2026-05-21 18:03:00'),
(13, 12, 'samsfasfasfsaf', 'roque', 'sammy12@gmail.com', 'fafa', 'fafafafa', '2026-05-28 04:54:37'),
(14, 12, 'samsfasfasfsaf', 'roque', 'sammy12@gmail.com', 'wow', 'product good', '2026-05-28 04:55:13'),
(15, 22, 'sam', 'roque', 'marjons@gmail.com', 'Nice!', 'chada kag product woah', '2026-05-29 04:46:08');

-- --------------------------------------------------------

--
-- Table structure for table `orderitems`
--

CREATE TABLE `orderitems` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `uuid` varchar(255) DEFAULT NULL,
  `COnumber` varchar(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `totalPrice` decimal(10,2) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contactNumber` varchar(20) DEFAULT NULL,
  `paymentMethod` varchar(50) DEFAULT NULL,
  `status` enum('pending','cancelled','completed','') DEFAULT 'pending',
  `DateCreated` timestamp NOT NULL DEFAULT current_timestamp(),
  `subtotal` decimal(10,2) DEFAULT NULL,
  `tax` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_item`
--

CREATE TABLE `product_item` (
  `id` int(11) NOT NULL,
  `uuid` varchar(255) DEFAULT NULL,
  `productName` varchar(255) DEFAULT NULL,
  `productDescription` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `img` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_item`
--

INSERT INTO `product_item` (`id`, `uuid`, `productName`, `productDescription`, `price`, `img`) VALUES
(1, '4a4bf0e7-51bc-11f1-b3aa-d843aec840f7', 'Regular Choco', 'Classic éclair filled with smooth chocolate cream and topped with a rich chocolate glaze.', 120.00, 'chocoReg.jpg'),
(2, '4a4c00b4-51bc-11f1-b3aa-d843aec840f7', 'Choco Lucent pair', 'Light and airy éclairs filled with sweet strawberry cream, finished with a delicate chocolate drizzle.', 130.00, 'chocoLucent.jpg'),
(3, '4a4c0127-51bc-11f1-b3aa-d843aec840f7', 'Dark Choco crumbs', 'A rich chocolate éclair coated with crunchy dark chocolate crumbs for added texture and flavor.', 140.00, 'chocoCrumbs.jpg'),
(4, '4a4c0161-51bc-11f1-b3aa-d843aec840f7', 'Heavy Yema Glaze', 'Soft éclair filled with creamy yema custard and topped with a thick caramelized glaze.', 135.00, 'heavyYema.jpg'),
(5, '4a4c0192-51bc-11f1-b3aa-d843aec840f7', 'Yema Peanut pair ', 'A delightful éclair filled with sweet yema and topped with roasted peanuts for a nutty crunch.', 145.00, 'yema.jpg'),
(6, '4a4c01c4-51bc-11f1-b3aa-d843aec840f7', 'Special Tops with Mellow Frosting ', 'Filipino-inspired éclair filled with creamy ube and topped with smooth, mellow frosting.', 150.00, 'STops.jpg'),
(7, '4a4c01f4-51bc-11f1-b3aa-d843aec840f7', 'Choco Nutty Toppings', 'Chocolate éclair topped with roasted nuts and coated in a silky chocolate finish.', 140.00, 'chocoNut.jpg'),
(8, '4a4c0221-51bc-11f1-b3aa-d843aec840f7', 'Vanilla Filled Choco', 'Soft éclair filled with rich vanilla cream and covered in a smooth chocolate glaze.', 150.00, 'vanillaField.jpg'),
(9, '4a4c0251-51bc-11f1-b3aa-d843aec840f7', 'Fluffy Matcha', 'A light and fluffy éclair infused with matcha cream, offering a subtle earthy sweetness.', 145.00, 'fluffyMatcha.jpg'),
(10, '4a4c0281-51bc-11f1-b3aa-d843aec840f7', 'Frosty Berry Tops', 'Éclair topped with mixed berry frosting and a refr', 120.00, 'frostBerry.jpg'),
(11, '4a4c02b2-51bc-11f1-b3aa-d843aec840f7', 'Hazel Nut Glazzed ', 'Creamy hazelnut-filled éclair coated with a glossy chocolate-hazelnut glaze.', 170.00, 'hazelNut.jpg'),
(12, '4a4c02f2-51bc-11f1-b3aa-d843aec840f7', 'Hazzy Choco Twins', 'Twin éclairs filled with hazelnut and chocolate cream for a rich, indulgent bite.', 165.00, 'hazzyChoco.jpg'),
(13, '4a4c031e-51bc-11f1-b3aa-d843aec840f7', 'Velet Toffee Eclair', 'A delicate éclair filled with rich cream and topped with a smooth caramel glaze for a perfectly indulgent treat', 150.00, 'Velvet.jpg'),
(14, '4a4c0353-51bc-11f1-b3aa-d843aec840f7', 'Red Vel Powder puff', 'Éclair inspired by red velvet, dusted with powder and filled with cream cheese filling.', 137.00, 'redVel.jpg'),
(15, '4a4c037e-51bc-11f1-b3aa-d843aec840f7', 'Pinky Meya', 'Sweet pink-colored éclair filled with yema cream and a light sugary topping.', 135.00, 'pinkyMeya.jpg'),
(16, '4a4c03b0-51bc-11f1-b3aa-d843aec840f7', 'Pink Glazed duo', 'Pair of éclairs coated in pink glaze with a smooth vanilla cream filling.', 120.00, 'pinkGlazed.jpg'),
(17, '4a4c03de-51bc-11f1-b3aa-d843aec840f7', 'Strawberry Glaze Special', 'Éclair filled with cream and topped with a fresh strawberry glaze.', 110.00, 'strawberryGlaze.jpg'),
(18, '4a4c040c-51bc-11f1-b3aa-d843aec840f7', 'Vanilla Soft Flakes', 'Vanilla-filled éclair topped with soft flaky crumbs for a delicate crunch.', 125.00, 'vanillaSoft.jpg'),
(19, '4a4c043b-51bc-11f1-b3aa-d843aec840f7', 'Frosty Luscent Frop', 'Chilled éclair with a frosty finish and light cream filling for a refreshing bite.', 140.00, 'frostyLuscent.jpg'),
(20, '4a4c0466-51bc-11f1-b3aa-d843aec840f7', 'Yin and Yang', 'Éclair featuring a mix of vanilla and chocolate cream, symbolizing balance in flavor.', 155.00, 'yinyang.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `firstName` varchar(255) NOT NULL,
  `lastName` varchar(255) NOT NULL,
  `middleName` varchar(255) DEFAULT NULL,
  `emailAddress` varchar(255) DEFAULT NULL,
  `contactNumber` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `barangay` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `profile` varchar(255) NOT NULL,
  `role` enum('customer','admin') NOT NULL,
  `dateCreated` timestamp NOT NULL DEFAULT current_timestamp(),
  `accountStatus` varchar(20) NOT NULL DEFAULT 'unverified',
  `emailVerificationToken` varchar(64) DEFAULT NULL,
  `emailVerificationExpires` datetime DEFAULT NULL,
  `reset_token_hash` char(64) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `uuid`, `firstName`, `lastName`, `middleName`, `emailAddress`, `contactNumber`, `username`, `password`, `street`, `barangay`, `city`, `profile`, `role`, `dateCreated`, `accountStatus`, `emailVerificationToken`, `emailVerificationExpires`, `reset_token_hash`, `reset_token_expires`) VALUES
(1, '16ddf72a-b54d-47d2-8ef1-c82db25c56c2', 'sam', 'roque', 'lagcao', 'roque.sam05@gmail.com', '04120941294', 'sammy', '$2b$12$xIuEMLVj3iL/8Yi8iTL8GesKmVvI8bcfJN.UvRDv2HQY8MERpeD6O', 'manok', 'manok', 'manok', '', 'customer', '2026-10-19 19:09:48', 'active', NULL, NULL, '69302de056b9a0f3993b355380717f277cc7eabe448075d4874f7d08398bc3cb', '2026-10-30 23:16:05'),
(3, '8e256248-04f6-4b04-ac19-548cdc935c9e', 'sam', 'roque', NULL, 'samroque87@gmail.com', NULL, 'samroque87_d2cff004', '$2b$12$Qnx/M0CPN5K7AmI6dOqxmujZB0cUvFup0fo3Pz5tUMsm5qh066FhK', NULL, NULL, NULL, '', 'customer', '2026-10-27 19:01:24', 'active', NULL, NULL, NULL, NULL),
(5, 'c36e74a3-8f15-47c2-bd73-ce70aa1be418', 'sam', 'roque', NULL, 'samroque959@gmail.com', '094219041', 'samroque959_18fba6e3', '$2b$12$9hqmxvBzAeJEbam4/JFHaO1/e/7kDXrM72FvbygRR/JydxK4CnDx.', 'pad', 'nazareth', 'cdo', '', 'customer', '2026-10-30 06:32:49', 'active', NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_changelog`
--
ALTER TABLE `admin_changelog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_id` (`user_id`),
  ADD KEY `fk_product_id` (`product_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userId` (`userId`);

--
-- Indexes for table `orderitems`
--
ALTER TABLE `orderitems`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`);

--
-- Indexes for table `product_item`
--
ALTER TABLE `product_item`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_changelog`
--
ALTER TABLE `admin_changelog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `orderitems`
--
ALTER TABLE `orderitems`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_item`
--
ALTER TABLE `product_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_changelog`
--
ALTER TABLE `admin_changelog`
  ADD CONSTRAINT `admin_changelog_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `fk_product_id` FOREIGN KEY (`product_id`) REFERENCES `product_item` (`id`),
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD CONSTRAINT `contact_messages_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`);

--
-- Constraints for table `orderitems`
--
ALTER TABLE `orderitems`
  ADD CONSTRAINT `orderitems_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `orderitems_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product_item` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
