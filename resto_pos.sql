-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 23, 2026 at 03:52 PM
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
-- Database: `resto_pos`
--

-- --------------------------------------------------------

--
-- Table structure for table `archived_records`
--

CREATE TABLE `archived_records` (
  `id` int(11) NOT NULL,
  `original_id` int(11) NOT NULL,
  `record_type` enum('user','menu_item','ingredient') NOT NULL,
  `record_name` varchar(255) DEFAULT NULL,
  `data_snapshot` longtext NOT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `archived_records`
--

INSERT INTO `archived_records` (`id`, `original_id`, `record_type`, `record_name`, `data_snapshot`, `archived_at`, `reason`) VALUES
(1, 11, 'user', 'admin', '{\"id\":\"11\",\"username\":\"admin\",\"password\":\"$2y$10$wPE4QW61bWjeG2kcV8wbB.5g8Ux4.4OZMvw0Z4QDC1nAOYgcPlxCy\",\"role\":\"admin\",\"branch_id\":null,\"status\":\"active\",\"created_at\":\"2026-04-17 00:44:56\",\"updated_at\":\"2026-04-17 00:44:56\"}', '2026-04-16 20:27:33', NULL),
(2, 13, 'user', 'Jerald', '{\"id\":\"13\",\"username\":\"Jerald\",\"password\":\"$2y$10$M32B2go6wxU2nvZuvcLkZ.3zaoY.fvp4EwH0G1bWwaV4yscixj4eO\",\"role\":\"staff\",\"branch_id\":null,\"status\":\"active\",\"created_at\":\"2026-04-17 01:12:14\",\"updated_at\":\"2026-04-17 01:12:14\"}', '2026-04-16 20:30:38', NULL),
(3, 14, 'user', 'Rim', '{\"id\":\"14\",\"username\":\"Rim\",\"password\":\"$2y$10$X4viRgPfYFFUfl0sw6XLVez3OMWgjSLybMaWkTaJa\\/JmMl4ucGLci\",\"role\":\"manager\",\"branch_id\":null,\"status\":\"active\",\"created_at\":\"2026-04-17 02:08:31\",\"updated_at\":\"2026-04-17 02:08:31\"}', '2026-04-16 20:30:44', NULL),
(4, 15, 'user', 'admin', '{\"id\":\"15\",\"username\":\"admin\",\"password\":\"$2y$10$AUt23T9zVuMUhZ34eFDfleHhBAyb79xOEdSaNUfi3wsHm5dNjzow2\",\"role\":\"admin\",\"branch_id\":null,\"status\":\"active\",\"created_at\":\"2026-04-17 04:30:47\",\"updated_at\":\"2026-04-17 04:30:47\"}', '2026-04-16 20:31:45', NULL),
(5, 16, 'user', 'admin', '{\"id\":\"16\",\"username\":\"admin\",\"password\":\"$2y$10$RvYVJ9oMA0yuK15djNxzMehMfYyiwGCxOeEvVlh0kMj0Ux5LQyWMW\",\"role\":\"admin\",\"branch_id\":null,\"status\":\"active\",\"created_at\":\"2026-04-17 04:31:50\",\"updated_at\":\"2026-04-17 04:31:50\"}', '2026-04-16 20:33:14', NULL),
(6, 23, 'user', 'Rim', '{\"id\":\"23\",\"username\":\"Rim\",\"password\":\"$2y$10$w0WNeNkLFL70yrvyAo5gSOzdeo4JLOBxZrcGf2Deki7Iq.XC77IIu\",\"role\":\"staff\",\"branch_id\":null,\"status\":\"active\",\"created_at\":\"2026-04-21 10:04:30\",\"updated_at\":\"2026-04-21 10:04:30\"}', '2026-04-21 02:05:33', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(11) NOT NULL,
  `branch_name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `branch_name`, `address`, `status`, `created_at`) VALUES
(1, 'Countryside Steakhouse Burol Main', 'Congressional Rd, Dasmariñas, Cavite', 'active', '2026-04-16 16:03:43'),
(2, 'Countryside Steakhouse Malihan', 'Malihan St., Dasmariñas, Cavite', 'active', '2026-04-16 16:03:43'),
(3, 'Countryside Steakhouse Poblacion Imus', 'Castaneda St., Carsadang Bago, Imus, Cavite', 'active', '2026-04-16 16:03:43'),
(4, 'Countryside Steakhouse Noveleta', 'Dr. J. M. Salud Rd., Noveleta, Cavite', 'active', '2026-04-16 16:03:43'),
(5, 'Countryside Steakhouse Silang', 'Silang, Cavite', 'active', '2026-04-16 16:03:43'),
(6, 'Countryside Steakhouse Tanza', 'Tanza, Cavite', 'active', '2026-04-16 16:03:43'),
(7, 'Country Side Pasig', 'Metro Manila', 'active', '2026-04-16 16:03:43'),
(8, 'Main Branch', '', 'active', '2026-04-21 15:23:48'),
(9, 'Branch 2', '', 'active', '2026-04-21 15:23:48');

-- --------------------------------------------------------

--
-- Table structure for table `daily_sales_summary`
--

CREATE TABLE `daily_sales_summary` (
  `id` int(11) NOT NULL,
  `sale_date` date NOT NULL,
  `total_revenue` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_discount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_tax` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_items_sold` int(11) NOT NULL DEFAULT 0,
  `total_orders` int(11) NOT NULL DEFAULT 0,
  `cash_sales` decimal(12,2) NOT NULL DEFAULT 0.00,
  `card_sales` decimal(12,2) NOT NULL DEFAULT 0.00,
  `ewallet_sales` decimal(12,2) NOT NULL DEFAULT 0.00,
  `other_sales` decimal(12,2) NOT NULL DEFAULT 0.00,
  `cash_received` decimal(12,2) NOT NULL DEFAULT 0.00,
  `change_given` decimal(12,2) NOT NULL DEFAULT 0.00,
  `average_order_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `generated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `unit` varchar(30) NOT NULL,
  `stock` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_stock` decimal(10,2) NOT NULL DEFAULT 5.00,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('available','unavailable') NOT NULL DEFAULT 'available',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`id`, `name`, `unit`, `stock`, `min_stock`, `unit_price`, `status`, `created_at`, `updated_at`) VALUES
(10, 'paminta', 'kg', 12.00, 5.00, 0.00, 'available', '2026-04-13 16:12:20', '2026-04-13 16:12:20'),
(11, 'suka', 'liters', 3.00, 5.00, 0.00, 'available', '2026-04-16 17:30:47', '2026-04-16 17:30:47'),
(12, 'Pasta', 'kg', 5.00, 5.00, 0.00, 'available', '2026-04-17 02:10:08', '2026-04-17 02:10:08'),
(13, 'Ketchup', 'boxes', 12.00, 5.00, 0.00, 'available', '2026-04-20 23:59:41', '2026-04-21 15:40:38'),
(15, 'Mayonnaise', 'boxes', 15.00, 5.00, 0.00, 'available', '2026-04-21 00:00:28', '2026-04-22 00:09:13');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_movements`
--

CREATE TABLE `inventory_movements` (
  `id` int(11) NOT NULL,
  `type` enum('in','out','adjustment','return','damage','expired') NOT NULL,
  `ingredient_id` int(11) DEFAULT NULL,
  `menu_item_id` int(11) DEFAULT NULL,
  `quantity_change` decimal(10,2) NOT NULL,
  `old_quantity` decimal(10,2) NOT NULL,
  `new_quantity` decimal(10,2) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `reference_type` enum('order','restock','adjustment','sales','waste') DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `recorded_by_username` varchar(50) DEFAULT NULL,
  `recorded_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_movements`
--

INSERT INTO `inventory_movements` (`id`, `type`, `ingredient_id`, `menu_item_id`, `quantity_change`, `old_quantity`, `new_quantity`, `reason`, `reference_id`, `reference_type`, `recorded_by`, `recorded_by_username`, `recorded_at`) VALUES
(1, 'in', NULL, NULL, 12.00, 0.00, 12.00, 'Initial stock', NULL, '', NULL, '0', '2026-04-13 15:58:45'),
(2, 'in', 10, NULL, 12.00, 0.00, 12.00, 'Initial stock', NULL, '', NULL, '0', '2026-04-13 16:12:20'),
(3, 'in', 11, NULL, 3.00, 0.00, 3.00, 'Initial stock', NULL, '', NULL, '0', '2026-04-16 17:30:47'),
(4, 'in', 12, NULL, 5.00, 0.00, 5.00, 'Initial stock', NULL, '', NULL, '0', '2026-04-17 02:10:08'),
(6, 'in', 15, NULL, 2.00, 0.00, 2.00, 'Initial stock', NULL, '', 18, '0', '2026-04-21 00:00:28'),
(7, 'in', 13, NULL, 10.00, 2.00, 12.00, 'Restock', NULL, 'adjustment', 17, '0', '2026-04-21 15:40:38'),
(8, 'in', 15, NULL, 3.00, 2.00, 5.00, 'Restock', NULL, 'adjustment', 18, '0', '2026-04-21 15:43:51'),
(9, 'in', 15, NULL, 10.00, 5.00, 15.00, 'Restock', NULL, 'adjustment', 17, '0', '2026-04-22 00:09:13');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `emoji` varchar(10) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `min_stock` int(11) NOT NULL DEFAULT 5,
  `status` enum('available','unavailable','archived') NOT NULL DEFAULT 'available',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `emoji`, `name`, `category`, `price`, `cost`, `stock`, `min_stock`, `status`, `created_at`, `updated_at`) VALUES
(2, '🍝', 'Spaghetti', 'Sizzling Favorites', 150.00, 50.00, 10, 5, 'available', '2026-04-13 14:57:41', '2026-04-22 01:33:05'),
(3, '🍣', 'Salmon Sashimi', 'Heart Lover\'s Delight', 320.00, 150.00, 3, 3, 'available', '2026-04-13 14:57:41', '2026-04-22 01:33:05'),
(4, '🥗', 'Caesar Salad', 'Heart Lover\'s Delight', 120.00, 40.00, 9, 5, 'available', '2026-04-13 14:57:41', '2026-04-21 23:57:26'),
(5, '🍲', 'Sinigang na Baboy', 'Country Classics', 175.00, 70.00, 50, 4, 'available', '2026-04-13 14:57:41', '2026-04-22 01:33:05'),
(6, '🍛', 'Beef Caldereta', 'Sizzling Favorites', 210.00, 85.00, 17, 3, 'available', '2026-04-13 14:57:41', '2026-04-22 01:33:05'),
(7, '🥗', 'Fruit Salad', 'Desserts', 75.00, 45.00, 20, 5, 'available', '2026-04-13 14:57:41', '2026-04-22 01:52:57'),
(8, '🥤', 'Iced Tea', 'Beverages', 65.00, 15.00, 28, 10, 'available', '2026-04-13 14:57:41', '2026-04-22 01:46:43'),
(9, '☕', 'Brewed Coffee', 'Beverages', 95.00, 20.00, 12, 10, 'available', '2026-04-13 14:57:41', '2026-04-22 01:46:32'),
(10, '🍟', 'French Fries', 'Extras', 95.00, 30.00, 9, 5, 'available', '2026-04-13 14:57:41', '2026-04-21 23:57:26'),
(11, '0', 'Kare-Kare', 'Uncategorized', 245.00, 100.00, 17, 3, 'archived', '2026-04-13 14:57:41', '2026-04-21 15:31:37'),
(13, '0', 'chicken ko', '0', 123.00, 0.00, 20, 5, 'archived', '2026-04-21 00:38:27', '2026-04-21 15:31:32'),
(15, '0', 'Caldereta', '0', 205.00, 0.00, 24, 5, 'archived', '2026-04-21 10:25:05', '2026-04-21 15:23:24'),
(16, '0', 'Chili', '0', 122.00, 0.00, 23, 5, 'archived', '2026-04-21 12:59:52', '2026-04-21 14:16:53'),
(17, '0', 'sili', '0', 300.00, 0.00, 20, 5, 'archived', '2026-04-21 13:01:04', '2026-04-21 13:12:29'),
(18, '0', 'Fried Chicken Wings', '0', 195.00, 0.00, 20, 5, 'archived', '2026-04-21 15:28:12', '2026-04-21 15:31:34'),
(19, '🍗', 'Fried Chicken Leg', 'Country Classics', 195.00, 0.00, 17, 5, 'available', '2026-04-21 15:31:10', '2026-04-22 01:39:19'),
(20, '🐔', 'Fried Chicken Wings', 'Country Classics', 200.00, 0.00, 20, 5, 'available', '2026-04-22 01:39:05', '2026-04-22 01:39:05'),
(21, '🐄', 'Tapsilog', 'Country Classics', 195.00, 0.00, 20, 5, 'available', '2026-04-22 01:39:46', '2026-04-22 01:39:46'),
(22, '🥩', 'Pan-Grilled Liempo', 'Country Classics', 190.00, 0.00, 20, 5, 'available', '2026-04-22 01:42:37', '2026-04-22 01:42:37'),
(23, '🥓', 'Tocilog', 'Country Classics', 170.00, 0.00, 20, 5, 'available', '2026-04-22 01:43:10', '2026-04-22 01:43:10'),
(24, '🥩', 'Porksilog', 'Country Classics', 170.00, 0.00, 20, 5, 'available', '2026-04-22 01:43:44', '2026-04-22 01:43:44'),
(25, '🍥', 'Shanghaisilog', 'Country Classics', 150.00, 0.00, 20, 5, 'available', '2026-04-22 01:44:24', '2026-04-22 01:44:24'),
(26, '🥩', 'Bisteksilog', 'Country Classics', 150.00, 0.00, 20, 5, 'available', '2026-04-22 01:44:44', '2026-04-22 01:44:44'),
(27, '🥓', 'Bacsilog', 'Country Classics', 150.00, 0.00, 20, 5, 'available', '2026-04-22 01:45:08', '2026-04-22 01:45:08'),
(28, '🥫', 'Spamsilog', 'Country Classics', 140.00, 0.00, 20, 5, 'available', '2026-04-22 01:45:37', '2026-04-22 01:45:37'),
(29, '🥤', 'Soda', 'Beverages', 75.00, 0.00, 20, 5, 'available', '2026-04-22 01:47:20', '2026-04-22 01:47:20'),
(30, '🥭', 'Mango Juice', 'Beverages', 85.00, 0.00, 20, 5, 'available', '2026-04-22 01:47:50', '2026-04-22 01:47:50'),
(31, '🍍', 'Pineapple Juice', 'Beverages', 85.00, 0.00, 20, 5, 'available', '2026-04-22 01:48:15', '2026-04-22 01:49:34'),
(32, '🍋', 'Lemonade', 'Beverages', 65.00, 0.00, 20, 5, 'available', '2026-04-22 01:48:41', '2026-04-22 01:48:41'),
(33, '💦', 'Bottled Water', 'Beverages', 40.00, 0.00, 20, 5, 'available', '2026-04-22 01:49:04', '2026-04-22 01:49:04'),
(34, '4️⃣', 'Four Seasons', 'Beverages', 85.00, 0.00, 20, 5, 'available', '2026-04-22 01:50:17', '2026-04-22 01:50:17'),
(35, '🍒', 'Cherry Lemonade', 'Beverages', 80.00, 0.00, 20, 5, 'available', '2026-04-22 01:50:41', '2026-04-22 01:50:41'),
(36, '🔵', 'Blue Lemonade', 'Beverages', 65.00, 0.00, 20, 5, 'available', '2026-04-22 01:51:09', '2026-04-22 01:51:09'),
(37, '🥒', 'Cucumber Lemonade', 'Beverages', 75.00, 0.00, 20, 5, 'available', '2026-04-22 01:51:33', '2026-04-22 01:51:33'),
(38, '🌿', 'Pandan Jelly', 'Desserts', 75.00, 0.00, 20, 5, 'available', '2026-04-22 01:53:29', '2026-04-22 01:53:29'),
(39, '🌽', 'Cream of Corn', 'Cream Soups', 85.00, 0.00, 20, 5, 'available', '2026-04-22 01:54:08', '2026-04-22 01:54:08'),
(40, '🍄‍', 'Cream of Mushroom', 'Cream Soups', 85.00, 0.00, 20, 5, 'available', '2026-04-22 01:54:32', '2026-04-22 01:54:32'),
(41, '🍚', 'Rice', 'Extras', 45.00, 0.00, 20, 5, 'available', '2026-04-22 01:55:07', '2026-04-22 01:55:07'),
(42, '🫙', 'Gravy', 'Extras', 25.00, 0.00, 20, 5, 'available', '2026-04-22 01:55:39', '2026-04-22 01:55:39'),
(43, '🥚', 'Egg', 'Extras', 35.00, 0.00, 20, 5, 'available', '2026-04-22 01:56:01', '2026-04-22 01:56:01'),
(44, '🫛', 'Vegetables', 'Extras', 30.00, 0.00, 20, 5, 'available', '2026-04-22 01:56:30', '2026-04-22 01:56:30'),
(45, '🍽', 'Dressing', 'Extras', 35.00, 0.00, 20, 5, 'available', '2026-04-22 01:56:52', '2026-04-22 01:56:52'),
(46, '🍞', 'Toasted Bread', 'Extras', 35.00, 0.00, 20, 5, 'available', '2026-04-22 01:57:32', '2026-04-22 01:57:50');

-- --------------------------------------------------------

--
-- Table structure for table `menu_item_history`
--

CREATE TABLE `menu_item_history` (
  `id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `action` enum('created','updated','deleted','price_changed','stock_adjusted') NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `changed_by` int(11) DEFAULT NULL,
  `changed_by_username` varchar(50) DEFAULT NULL,
  `changed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `table_name` varchar(100) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` enum('cash','e_wallet','card','check','online','other') NOT NULL DEFAULT 'cash',
  `payment_reference` varchar(200) DEFAULT NULL,
  `payment_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_details`)),
  `cash_received` decimal(10,2) NOT NULL DEFAULT 0.00,
  `change_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('completed','pending','cancelled','refunded') NOT NULL DEFAULT 'completed',
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  `modified_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `discount_type` enum('regular','pwd','senior') NOT NULL DEFAULT 'regular',
  `discount_label` varchar(100) NOT NULL DEFAULT 'Regular Discount',
  `branch_id` int(11) DEFAULT NULL,
  `branch_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `user_id`, `username`, `customer_name`, `table_name`, `subtotal`, `discount_amount`, `discount_percent`, `tax_amount`, `total`, `payment_method`, `payment_reference`, `payment_details`, `cash_received`, `change_amount`, `status`, `notes`, `created_at`, `completed_at`, `modified_at`, `discount_type`, `discount_label`, `branch_id`, `branch_name`) VALUES
(1, 'ORD-20260413-69dc98c904c94', NULL, 'Nel', '', 'Takeout', 60.00, 0.00, 0.00, 0.00, 60.00, 'cash', '', NULL, 40.00, 40.00, 'completed', NULL, '2026-04-13 15:18:33', '2026-04-13 09:18:33', '2026-04-13 15:18:33', 'regular', 'Regular Discount', NULL, NULL),
(2, 'ORD-20260413-69dc9cc88f384', NULL, 'Nel', 'Pogi', 'Takeout', 295.00, 0.00, 0.00, 0.00, 295.00, 'cash', '', NULL, 5.00, 5.00, 'completed', NULL, '2026-04-13 15:35:36', '2026-04-13 09:35:36', '2026-04-13 15:35:36', 'regular', 'Regular Discount', NULL, NULL),
(3, 'ORD-20260413-69dc9ccb29bbd', NULL, 'Nel', 'Pogi', 'Takeout', 295.00, 0.00, 0.00, 0.00, 295.00, 'cash', '', NULL, 5.00, 5.00, 'completed', NULL, '2026-04-13 15:35:39', '2026-04-13 09:35:39', '2026-04-13 15:35:39', 'regular', 'Regular Discount', NULL, NULL),
(4, 'ORD-20260413-69dc9cd82603f', NULL, 'Nel', 'Pogi', 'Takeout', 295.00, 0.00, 0.00, 0.00, 295.00, 'cash', '', NULL, 5.00, 5.00, 'completed', NULL, '2026-04-13 15:35:52', '2026-04-13 09:35:52', '2026-04-13 15:35:52', 'regular', 'Regular Discount', NULL, NULL),
(5, 'ORD-20260413-69dc9cec8c3cd', NULL, 'Nel', 'Pogi', 'Takeout', 295.00, 0.00, 0.00, 0.00, 295.00, 'cash', '', NULL, 5.00, 5.00, 'completed', NULL, '2026-04-13 15:36:12', '2026-04-13 09:36:12', '2026-04-13 15:36:12', 'regular', 'Regular Discount', NULL, NULL),
(6, 'ORD-20260413-69dc9d9de59cf', NULL, 'Nel', 'Pogi', 'Takeout', 295.00, 0.00, 0.00, 0.00, 295.00, 'cash', '', NULL, 5.00, 5.00, 'completed', NULL, '2026-04-13 15:39:09', '2026-04-13 09:39:09', '2026-04-13 15:39:09', 'regular', 'Regular Discount', NULL, NULL),
(7, 'ORD-20260413-69dc9da222db4', NULL, 'Nel', 'Pogi', 'Takeout', 295.00, 0.00, 0.00, 0.00, 295.00, 'cash', '', NULL, 5.00, 5.00, 'completed', NULL, '2026-04-13 15:39:14', '2026-04-13 09:39:14', '2026-04-13 15:39:14', 'regular', 'Regular Discount', NULL, NULL),
(8, 'ORD-20260413-69dc9e225723c', NULL, 'Nel', '', 'Takeout', 215.00, 0.00, 0.00, 0.00, 215.00, 'cash', '', NULL, 5.00, 5.00, 'completed', NULL, '2026-04-13 15:41:22', '2026-04-13 09:41:22', '2026-04-13 15:41:22', 'regular', 'Regular Discount', NULL, NULL),
(9, 'ORD-20260413-69dca6990eeb6', NULL, 'Nel', 'nel', 'Takeout', 850.00, 85.00, 0.00, 0.00, 765.00, 'e_wallet', '120763070018', NULL, 0.00, 0.00, 'completed', NULL, '2026-04-13 16:17:29', '2026-04-13 10:17:29', '2026-04-13 16:17:29', 'regular', 'Regular Discount', NULL, NULL),
(10, 'ORD-20260416-69e0a1e8ceef9', NULL, 'Nel', '', 'Takeout', 215.00, 43.00, 20.00, 0.00, 172.00, 'cash', '', NULL, 220.00, 48.00, 'completed', NULL, '2026-04-16 16:46:32', '2026-04-16 10:46:32', '2026-04-16 16:46:32', 'pwd', 'PWD (20%)', NULL, NULL),
(11, 'ORD-20260416-69e0b03e9787a', NULL, 'Nel', '', 'Takeout', 1815.00, 363.00, 20.00, 0.00, 1452.00, 'cash', '', NULL, 1500.00, 48.00, 'completed', NULL, '2026-04-16 17:47:42', '2026-04-16 11:47:42', '2026-04-16 17:47:42', 'senior', 'Senior Citizen (20%)', NULL, NULL),
(12, 'ORD-20260416-69e0f05643527', NULL, 'admin', '', 'Takeout', 340.00, 0.00, 0.00, 0.00, 340.00, 'cash', '', NULL, 350.00, 10.00, 'completed', NULL, '2026-04-16 22:21:10', '2026-04-16 16:21:10', '2026-04-16 22:21:10', 'regular', 'Regular', NULL, NULL),
(13, 'ORD-20260420-69e64cb3af98d', NULL, 'Nel', '', 'Takeout', 185.00, 0.00, 0.00, 0.00, 185.00, 'cash', '', NULL, 190.00, 5.00, 'completed', NULL, '2026-04-20 23:56:35', '2026-04-20 17:56:35', '2026-04-20 23:56:35', 'regular', 'Regular', NULL, NULL),
(14, 'ORD-20260421-69e6dbfa8870c', 20, 'Nel', '', 'Takeout', 245.00, 49.00, 20.00, 0.00, 196.00, 'cash', '', NULL, 0.00, -196.00, 'completed', NULL, '2026-04-21 10:07:54', '2026-04-21 04:07:54', '2026-04-21 10:07:54', 'pwd', 'PWD (20%)', NULL, NULL),
(15, 'ORD-20260421-69e6dc133435e', 20, 'Nel', '', 'Takeout', 120.00, 0.00, 0.00, 0.00, 120.00, 'cash', '', NULL, 0.00, -120.00, 'completed', NULL, '2026-04-21 10:08:19', '2026-04-21 04:08:19', '2026-04-21 10:08:19', 'regular', 'Regular', NULL, NULL),
(16, 'ORD-20260421-69e6dc2724724', 20, 'Nel', '', 'Takeout', 320.00, 0.00, 0.00, 0.00, 320.00, 'cash', '', NULL, 350.00, 30.00, 'completed', NULL, '2026-04-21 10:08:39', '2026-04-21 04:08:39', '2026-04-21 10:08:39', 'regular', 'Regular', NULL, NULL),
(17, 'ORD-20260421-69e6e3627cce1', 20, 'Nel', 'Baddet', 'Table 1', 350.00, 0.00, 0.00, 0.00, 350.00, 'cash', '', NULL, 360.00, 10.00, 'completed', NULL, '2026-04-21 10:39:30', '2026-04-21 04:39:30', '2026-04-21 10:39:30', 'regular', 'Regular', NULL, NULL),
(18, 'ORD-20260421-69e70034e3684', 20, 'Nel', '', 'Takeout', 120.00, 0.00, 0.00, 0.00, 120.00, 'cash', '', NULL, 120.00, 0.00, 'completed', NULL, '2026-04-21 12:42:28', '2026-04-21 06:42:28', '2026-04-21 12:42:28', 'regular', 'Regular', NULL, NULL),
(19, 'ORD-20260421-69e700e72d8af', 20, 'Nel', '', 'Takeout', 245.00, 49.00, 20.00, 0.00, 196.00, 'cash', '', NULL, 250.00, 54.00, 'completed', NULL, '2026-04-21 12:45:27', '2026-04-21 06:45:27', '2026-04-21 12:45:27', 'senior', 'Senior Citizen (20%)', NULL, NULL),
(20, 'ORD-20260421-69e7113d24d78', 24, 'John', '', 'Takeout', 842.00, 0.00, 0.00, 0.00, 842.00, 'cash', '', NULL, 850.00, 8.00, 'completed', NULL, '2026-04-21 13:55:09', '2026-04-21 07:55:09', '2026-04-21 13:55:09', 'regular', 'Regular', NULL, NULL),
(21, 'ORD-20260421-69e7116079156', 25, 'Jeri', '', 'Takeout', 875.00, 175.00, 20.00, 0.00, 700.00, 'cash', '', NULL, 700.00, 0.00, 'completed', NULL, '2026-04-21 13:55:44', '2026-04-21 07:55:44', '2026-04-21 13:55:44', 'pwd', 'PWD (20%)', NULL, NULL),
(22, 'ORD-20260421-69e71181f215a', 26, 'rest', '', 'Takeout', 1303.00, 260.60, 20.00, 0.00, 1042.40, 'cash', '', NULL, 1050.00, 7.60, 'completed', NULL, '2026-04-21 13:56:17', '2026-04-21 07:56:17', '2026-04-21 13:56:17', 'senior', 'Senior Citizen (20%)', NULL, NULL),
(23, 'ORD-20260421-69e7176712abc', 24, 'John', '', 'Takeout', 205.00, 0.00, 0.00, 0.00, 205.00, 'cash', '', NULL, 205.00, 0.00, 'completed', NULL, '2026-04-21 14:21:27', '2026-04-21 08:21:27', '2026-04-21 14:21:27', 'regular', 'Regular', NULL, NULL),
(24, 'ORD-20260421-69e72b24487c6', 24, 'John', '', 'Takeout', 1140.00, 228.00, 20.00, 0.00, 912.00, 'cash', '', NULL, 1000.00, 88.00, 'completed', NULL, '2026-04-21 15:45:40', '2026-04-21 09:45:40', '2026-04-21 15:45:40', 'pwd', 'PWD (20%)', NULL, NULL),
(25, 'ORD-20260421-69e72fc87c949', 24, 'John', '', 'Takeout', 150.00, 30.00, 20.00, 0.00, 120.00, 'cash', '', NULL, 120.00, 0.00, 'completed', NULL, '2026-04-21 16:05:28', '2026-04-21 10:05:28', '2026-04-21 16:05:28', 'pwd', 'PWD (20%)', NULL, NULL),
(26, 'ORD-20260421-69e79e6657d6d', 20, 'Nel', '', 'Table 5', 1545.00, 309.00, 20.00, 0.00, 1236.00, 'cash', '', NULL, 1240.00, 4.00, 'completed', NULL, '2026-04-21 23:57:26', '2026-04-21 17:57:26', '2026-04-21 23:57:26', 'pwd', 'PWD (20%)', 1, 'Countryside Steakhouse Burol Main'),
(27, 'ORD-20260421-69e7a1dbe2e98', 25, 'Jeri', '', 'Takeout', 230.00, 0.00, 0.00, 0.00, 230.00, 'e_wallet', '12009801356', NULL, 230.00, 0.00, 'completed', NULL, '2026-04-22 00:12:11', '2026-04-21 18:12:11', '2026-04-22 00:12:11', 'regular', 'Regular', 3, 'Countryside Steakhouse Poblacion Imus'),
(28, 'ORD-20260421-69e7ad011c474', 26, 'rest', '', 'Table 3', 1020.00, 0.00, 0.00, 0.00, 1020.00, 'cash', '', NULL, 1030.00, 10.00, 'completed', NULL, '2026-04-22 00:59:45', '2026-04-21 18:59:45', '2026-04-22 00:59:45', 'regular', 'Regular', 4, 'Countryside Steakhouse Noveleta'),
(29, 'ORD-20260421-69e7b4d11b14e', 28, 'jiri', '', 'Takeout', 1270.00, 254.00, 20.00, 0.00, 1016.00, 'online', '9654720811', NULL, 1016.00, 0.00, 'completed', NULL, '2026-04-22 01:33:05', '2026-04-21 19:33:05', '2026-04-22 01:33:05', 'senior', 'Senior Citizen (20%)', 3, 'Countryside Steakhouse Poblacion Imus');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `menu_item_id` int(11) DEFAULT NULL,
  `item_name` varchar(100) NOT NULL,
  `emoji` varchar(10) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `item_total` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `menu_item_id`, `item_name`, `emoji`, `quantity`, `unit_price`, `item_total`, `discount_amount`, `notes`, `created_at`) VALUES
(1, 1, 8, 'Iced Tea', '🥤', 1, 60.00, 60.00, 0.00, NULL, '2026-04-13 15:18:33'),
(8, 8, 7, 'Sizzling Chicken Wings', '🧁', 1, 215.00, 215.00, 0.00, NULL, '2026-04-13 15:41:22'),
(9, 9, 4, 'Porterhouse Steak', '🥗', 1, 120.00, 120.00, 0.00, NULL, '2026-04-13 16:17:29'),
(10, 9, 7, 'Sizzling Chicken Wings', '🧁', 1, 215.00, 215.00, 0.00, NULL, '2026-04-13 16:17:29'),
(11, 9, 8, 'Iced Tea', '🥤', 1, 60.00, 60.00, 0.00, NULL, '2026-04-13 16:17:29'),
(12, 9, 9, 'Brewed Coffee', '☕', 1, 80.00, 80.00, 0.00, NULL, '2026-04-13 16:17:29'),
(13, 9, 10, 'Burger Steak', '🍟', 1, 95.00, 95.00, 0.00, NULL, '2026-04-13 16:17:29'),
(14, 9, NULL, 'T-Bone Steak', '🍽', 1, 280.00, 280.00, 0.00, NULL, '2026-04-13 16:17:29'),
(15, 10, 7, 'Sizzling Chicken Wings', '🧁', 1, 215.00, 215.00, 0.00, NULL, '2026-04-16 16:46:32'),
(16, 11, NULL, 'T-Bone Steak', '🍽', 1, 280.00, 280.00, 0.00, NULL, '2026-04-16 17:47:42'),
(17, 11, NULL, 'Pork Sisig', '🍨', 1, 205.00, 205.00, 0.00, NULL, '2026-04-16 17:47:42'),
(18, 11, 10, 'Burger Steak', '🍟', 1, 95.00, 95.00, 0.00, NULL, '2026-04-16 17:47:42'),
(19, 11, 9, 'Brewed Coffee', '☕', 1, 80.00, 80.00, 0.00, NULL, '2026-04-16 17:47:42'),
(20, 11, 7, 'Sizzling Chicken Wings', '🧁', 2, 215.00, 430.00, 0.00, NULL, '2026-04-16 17:47:42'),
(21, 11, 4, 'Porterhouse Steak', '🥗', 1, 120.00, 120.00, 0.00, NULL, '2026-04-16 17:47:42'),
(22, 11, NULL, 'Pork Steak', '🍽', 1, 195.00, 195.00, 0.00, NULL, '2026-04-16 17:47:42'),
(23, 11, NULL, 'Tenderloin Steak', '🍽', 1, 290.00, 290.00, 0.00, NULL, '2026-04-16 17:47:42'),
(24, 11, NULL, 'Spamsilog', '🍽', 1, 120.00, 120.00, 0.00, NULL, '2026-04-16 17:47:42'),
(25, 12, 8, 'Iced Tea', '🥤', 1, 60.00, 60.00, 0.00, NULL, '2026-04-16 22:21:10'),
(26, 12, NULL, 'T-Bone Steak', '🍽', 1, 280.00, 280.00, 0.00, NULL, '2026-04-16 22:21:10'),
(27, 13, NULL, 'Fried Chicken', '🍗', 1, 185.00, 185.00, 0.00, NULL, '2026-04-20 23:56:35'),
(28, 14, 11, 'Kare-Kare', '0', 1, 245.00, 245.00, 0.00, NULL, '2026-04-21 10:07:54'),
(29, 15, 4, 'Caesar Salad', '🥗', 1, 120.00, 120.00, 0.00, NULL, '2026-04-21 10:08:19'),
(30, 16, 3, 'Salmon Sashimi', '🍣', 1, 320.00, 320.00, 0.00, NULL, '2026-04-21 10:08:39'),
(31, 17, 5, 'Sinigang na Baboy', '🍲', 2, 175.00, 350.00, 0.00, NULL, '2026-04-21 10:39:30'),
(32, 18, 4, 'Caesar Salad', '🥗', 1, 120.00, 120.00, 0.00, NULL, '2026-04-21 12:42:28'),
(33, 19, 11, 'Kare-Kare', '0', 1, 245.00, 245.00, 0.00, NULL, '2026-04-21 12:45:27'),
(34, 20, 16, 'Chili', '0', 1, 122.00, 122.00, 0.00, NULL, '2026-04-21 13:55:09'),
(35, 20, 9, 'Brewed Coffee', '☕', 9, 80.00, 720.00, 0.00, NULL, '2026-04-21 13:55:09'),
(36, 21, 5, 'Sinigang na Baboy', '🍲', 5, 175.00, 875.00, 0.00, NULL, '2026-04-21 13:55:44'),
(37, 22, 3, 'Salmon Sashimi', '🍣', 1, 320.00, 320.00, 0.00, NULL, '2026-04-21 13:56:17'),
(38, 22, 6, 'Beef Caldereta', '🍛', 1, 210.00, 210.00, 0.00, NULL, '2026-04-21 13:56:17'),
(39, 22, 13, 'chicken ko', '0', 1, 123.00, 123.00, 0.00, NULL, '2026-04-21 13:56:17'),
(40, 22, 11, 'Kare-Kare', '0', 1, 245.00, 245.00, 0.00, NULL, '2026-04-21 13:56:18'),
(41, 22, 7, 'Chocolate Lava Cake', '🧁', 1, 135.00, 135.00, 0.00, NULL, '2026-04-21 13:56:18'),
(42, 22, 10, 'French Fries', '🍟', 1, 95.00, 95.00, 0.00, NULL, '2026-04-21 13:56:18'),
(43, 22, 5, 'Sinigang na Baboy', '🍲', 1, 175.00, 175.00, 0.00, NULL, '2026-04-21 13:56:18'),
(44, 23, 15, 'Caldereta', '0', 1, 205.00, 205.00, 0.00, NULL, '2026-04-21 14:21:27'),
(45, 24, 10, 'French Fries', '🍟', 12, 95.00, 1140.00, 0.00, NULL, '2026-04-21 15:45:40'),
(46, 25, 2, 'Spaghetti', '🍝', 1, 150.00, 150.00, 0.00, NULL, '2026-04-21 16:05:28'),
(47, 26, 10, 'French Fries', '🍟', 1, 95.00, 95.00, 0.00, NULL, '2026-04-21 23:57:26'),
(48, 26, 9, 'Brewed Coffee', '☕', 1, 80.00, 80.00, 0.00, NULL, '2026-04-21 23:57:26'),
(49, 26, 8, 'Iced Tea', '🥤', 1, 60.00, 60.00, 0.00, NULL, '2026-04-21 23:57:26'),
(50, 26, 19, 'Fried Chicken Leg', '🍗', 1, 200.00, 200.00, 0.00, NULL, '2026-04-21 23:57:26'),
(51, 26, 5, 'Sinigang na Baboy', '🍲', 1, 175.00, 175.00, 0.00, NULL, '2026-04-21 23:57:26'),
(52, 26, 7, 'Chocolate Lava Cake', '🧁', 1, 135.00, 135.00, 0.00, NULL, '2026-04-21 23:57:26'),
(53, 26, 4, 'Caesar Salad', '🥗', 1, 120.00, 120.00, 0.00, NULL, '2026-04-21 23:57:26'),
(54, 26, 6, 'Beef Caldereta', '🍛', 1, 210.00, 210.00, 0.00, NULL, '2026-04-21 23:57:26'),
(55, 26, 3, 'Salmon Sashimi', '🍣', 1, 320.00, 320.00, 0.00, NULL, '2026-04-21 23:57:26'),
(56, 26, 2, 'Spaghetti', '🍝', 1, 150.00, 150.00, 0.00, NULL, '2026-04-21 23:57:26'),
(57, 27, 9, 'Brewed Coffee', '☕', 1, 80.00, 80.00, 0.00, NULL, '2026-04-22 00:12:11'),
(58, 27, 2, 'Spaghetti', '🍝', 1, 150.00, 150.00, 0.00, NULL, '2026-04-22 00:12:11'),
(59, 28, 9, 'Brewed Coffee', '☕', 1, 80.00, 80.00, 0.00, NULL, '2026-04-22 00:59:45'),
(60, 28, 6, 'Beef Caldereta', '🍛', 1, 210.00, 210.00, 0.00, NULL, '2026-04-22 00:59:45'),
(61, 28, 3, 'Salmon Sashimi', '🍣', 1, 320.00, 320.00, 0.00, NULL, '2026-04-22 00:59:45'),
(62, 28, 2, 'Spaghetti', '🍝', 1, 150.00, 150.00, 0.00, NULL, '2026-04-22 00:59:45'),
(63, 28, 19, 'Fried Chicken Leg', '🍗', 1, 200.00, 200.00, 0.00, NULL, '2026-04-22 00:59:45'),
(64, 28, 8, 'Iced Tea', '🥤', 1, 60.00, 60.00, 0.00, NULL, '2026-04-22 00:59:45'),
(65, 29, 9, 'Brewed Coffee', '☕', 1, 80.00, 80.00, 0.00, NULL, '2026-04-22 01:33:05'),
(66, 29, 7, 'Chocolate Lava Cake', '🧁', 1, 135.00, 135.00, 0.00, NULL, '2026-04-22 01:33:05'),
(67, 29, 5, 'Sinigang na Baboy', '🍲', 1, 175.00, 175.00, 0.00, NULL, '2026-04-22 01:33:05'),
(68, 29, 19, 'Fried Chicken Leg', '🍗', 1, 200.00, 200.00, 0.00, NULL, '2026-04-22 01:33:05'),
(69, 29, 2, 'Spaghetti', '🍝', 1, 150.00, 150.00, 0.00, NULL, '2026-04-22 01:33:05'),
(70, 29, 6, 'Beef Caldereta', '🍛', 1, 210.00, 210.00, 0.00, NULL, '2026-04-22 01:33:05'),
(71, 29, 3, 'Salmon Sashimi', '🍣', 1, 320.00, 320.00, 0.00, NULL, '2026-04-22 01:33:05');

-- --------------------------------------------------------

--
-- Table structure for table `payment_reconciliation`
--

CREATE TABLE `payment_reconciliation` (
  `id` int(11) NOT NULL,
  `reconciliation_date` date NOT NULL,
  `payment_method` enum('cash','e_wallet','card','check','other') NOT NULL,
  `expected_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `actual_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `variance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `variance_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `status` enum('balanced','variance','pending_review') NOT NULL DEFAULT 'pending_review',
  `reconciled_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `reconciled_at` datetime DEFAULT NULL,
  `recorded_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_transactions`
--

CREATE TABLE `sales_transactions` (
  `id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `user_id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaction_logs`
--

CREATE TABLE `transaction_logs` (
  `id` int(11) NOT NULL,
  `transaction_type` enum('order','menu_change','inventory_movement','user_action','payment','discount','refund','menu_item_created','menu_item_updated','menu_item_deleted','stock_adjustment','ingredient_change') NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `entity_type` enum('order','menu_item','ingredient','user','payment') NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `entity_name` varchar(255) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `old_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_value`)),
  `new_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_value`)),
  `status` enum('success','failed','pending') NOT NULL DEFAULT 'success',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `related_order_id` int(11) DEFAULT NULL,
  `related_transaction_log_id` int(11) DEFAULT NULL,
  `logged_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transaction_logs`
--

INSERT INTO `transaction_logs` (`id`, `transaction_type`, `user_id`, `username`, `entity_type`, `entity_id`, `entity_name`, `action`, `description`, `old_value`, `new_value`, `status`, `ip_address`, `user_agent`, `transaction_id`, `related_order_id`, `related_transaction_log_id`, `logged_at`) VALUES
(1, 'user_action', NULL, 'system', '', NULL, '0', '0', 'Default admin user created', NULL, '{\"username\":\"admin\",\"role\":\"admin\"}', 'success', NULL, NULL, NULL, NULL, NULL, '2026-04-13 15:01:24'),
(2, 'order', NULL, 'Nel', '', 1, '0', '0', 'Order created and completed', NULL, '{\"order_number\":\"ORD-20260413-69dc98c904c94\",\"table\":\"Takeout\",\"customer\":\"\",\"subtotal\":60,\"discount\":0,\"total\":60,\"items_count\":1,\"payment_method\":\"cash\"}', 'success', NULL, NULL, NULL, 1, NULL, '2026-04-13 15:18:33'),
(3, 'order', NULL, 'Nel', 'order', 8, 'ORD-20260413-69dc9e225723c', 'create', 'Order created and completed', NULL, '{\"order_number\":\"ORD-20260413-69dc9e225723c\",\"table\":\"Takeout\",\"customer\":\"\",\"subtotal\":215,\"discount\":0,\"total\":215,\"items_count\":1,\"payment_method\":\"cash\"}', 'success', NULL, NULL, NULL, 8, NULL, '2026-04-13 15:41:22'),
(4, 'user_action', NULL, 'Jerald', 'ingredient', 9, 'paminta', 'create', 'Ingredient created', NULL, '{\"name\":\"paminta\",\"unit\":\"kg\",\"stock\":12}', 'success', NULL, NULL, NULL, NULL, NULL, '2026-04-13 15:58:45'),
(5, 'user_action', NULL, 'Jerald', 'ingredient', 10, 'paminta', 'create', 'Ingredient created', NULL, '{\"name\":\"paminta\",\"unit\":\"kg\",\"stock\":12}', 'success', NULL, NULL, NULL, NULL, NULL, '2026-04-13 16:12:20'),
(6, 'order', NULL, 'Nel', 'order', 9, 'ORD-20260413-69dca6990eeb6', 'create', 'Order created and completed', NULL, '{\"order_number\":\"ORD-20260413-69dca6990eeb6\",\"table\":\"Takeout\",\"customer\":\"nel\",\"subtotal\":850,\"discount\":85,\"total\":765,\"items_count\":6,\"payment_method\":\"e_wallet\"}', 'success', NULL, NULL, NULL, 9, NULL, '2026-04-13 16:17:29'),
(7, 'order', NULL, 'Nel', 'order', 10, 'ORD-20260416-69e0a1e8ceef9', 'create', 'Order created and completed', NULL, '{\"order_number\":\"ORD-20260416-69e0a1e8ceef9\",\"table\":\"Takeout\",\"customer\":\"\",\"subtotal\":215,\"discount\":43,\"discount_percent\":20,\"discount_type\":\"pwd\",\"discount_label\":\"PWD (20%)\",\"total\":172,\"items_count\":1,\"payment_method\":\"cash\"}', 'success', NULL, NULL, NULL, 10, NULL, '2026-04-16 16:46:32'),
(8, 'user_action', NULL, 'admin', 'ingredient', 11, 'suka', 'create', 'Ingredient created', NULL, '{\"name\":\"suka\",\"unit\":\"liters\",\"stock\":3}', 'success', NULL, NULL, NULL, NULL, NULL, '2026-04-16 17:30:47'),
(9, 'order', NULL, 'Nel', 'order', 11, 'ORD-20260416-69e0b03e9787a', 'create', 'Order created and completed', NULL, '{\"order_number\":\"ORD-20260416-69e0b03e9787a\",\"table\":\"Takeout\",\"customer\":\"\",\"subtotal\":1815,\"discount\":363,\"discount_percent\":20,\"discount_type\":\"senior\",\"discount_label\":\"Senior Citizen (20%)\",\"total\":1452,\"items_count\":9,\"payment_method\":\"cash\"}', 'success', NULL, NULL, NULL, 11, NULL, '2026-04-16 17:47:42'),
(10, 'order', NULL, 'admin', 'order', 12, 'ORD-20260416-69e0f05643527', 'create', 'Order created and completed', NULL, '{\"order_number\":\"ORD-20260416-69e0f05643527\",\"table\":\"Takeout\",\"customer\":\"\",\"subtotal\":340,\"discount\":0,\"discount_percent\":0,\"discount_type\":\"regular\",\"discount_label\":\"Regular\",\"total\":340,\"items_count\":2,\"payment_method\":\"cash\"}', 'success', NULL, NULL, NULL, 12, NULL, '2026-04-16 22:21:10'),
(11, 'user_action', NULL, 'system', 'user', NULL, 'admin', 'create', 'Default admin user created', NULL, '{\"username\":\"admin\",\"role\":\"admin\"}', 'success', NULL, NULL, NULL, NULL, NULL, '2026-04-16 22:51:06'),
(12, 'user_action', NULL, 'system', 'user', NULL, 'admin', 'create', 'Default admin user created', NULL, '{\"username\":\"admin\",\"role\":\"admin\"}', 'success', NULL, NULL, NULL, NULL, NULL, '2026-04-17 00:44:56'),
(13, 'user_action', NULL, 'Rim', 'ingredient', 12, 'Pasta', 'create', 'Ingredient created', NULL, '{\"name\":\"Pasta\",\"unit\":\"kg\",\"stock\":5}', 'success', NULL, NULL, NULL, NULL, NULL, '2026-04-17 02:10:08'),
(14, 'user_action', NULL, 'system', 'user', NULL, 'admin', 'create', 'Default admin user created', NULL, '{\"username\":\"admin\",\"role\":\"admin\"}', 'success', NULL, NULL, NULL, NULL, NULL, '2026-04-17 04:30:47'),
(15, 'user_action', NULL, 'system', 'user', NULL, 'admin', 'create', 'Default admin user created', NULL, '{\"username\":\"admin\",\"role\":\"admin\"}', 'success', NULL, NULL, NULL, NULL, NULL, '2026-04-17 04:31:50'),
(16, 'user_action', NULL, 'system', 'user', NULL, 'admin', 'create', 'Default admin user created', NULL, '{\"username\":\"admin\",\"role\":\"admin\"}', 'success', NULL, NULL, NULL, NULL, NULL, '2026-04-17 21:47:49'),
(17, 'order', NULL, 'Nel', 'order', 13, 'ORD-20260420-69e64cb3af98d', 'create', 'Order created and completed', NULL, '{\"order_number\":\"ORD-20260420-69e64cb3af98d\",\"table\":\"Takeout\",\"customer\":\"\",\"subtotal\":185,\"discount\":0,\"discount_percent\":0,\"discount_type\":\"regular\",\"discount_label\":\"Regular\",\"total\":185,\"items_count\":1,\"payment_method\":\"cash\"}', 'success', NULL, NULL, NULL, 13, NULL, '2026-04-20 23:56:35'),
(18, 'user_action', 18, 'Jerald', 'ingredient', 15, 'Mayonnaise', 'create', 'Ingredient created', NULL, '{\"name\":\"Mayonnaise\",\"unit\":\"boxes\",\"stock\":2}', 'success', NULL, NULL, NULL, NULL, NULL, '2026-04-21 00:00:28'),
(19, 'order', 20, 'Nel', 'order', 14, 'ORD-20260421-69e6dbfa8870c', 'create', 'Order created and completed', NULL, '{\"order_number\":\"ORD-20260421-69e6dbfa8870c\",\"table\":\"Takeout\",\"customer\":\"\",\"subtotal\":245,\"discount\":49,\"discount_percent\":20,\"discount_type\":\"pwd\",\"discount_label\":\"PWD (20%)\",\"total\":196,\"items_count\":1,\"payment_method\":\"cash\"}', 'success', NULL, NULL, NULL, 14, NULL, '2026-04-21 10:07:54'),
(20, 'order', 20, 'Nel', 'order', 15, 'ORD-20260421-69e6dc133435e', 'create', 'Order created and completed', NULL, '{\"order_number\":\"ORD-20260421-69e6dc133435e\",\"table\":\"Takeout\",\"customer\":\"\",\"subtotal\":120,\"discount\":0,\"discount_percent\":0,\"discount_type\":\"regular\",\"discount_label\":\"Regular\",\"total\":120,\"items_count\":1,\"payment_method\":\"cash\"}', 'success', NULL, NULL, NULL, 15, NULL, '2026-04-21 10:08:19'),
(21, 'order', 20, 'Nel', 'order', 16, 'ORD-20260421-69e6dc2724724', 'create', 'Order created and completed', NULL, '{\"order_number\":\"ORD-20260421-69e6dc2724724\",\"table\":\"Takeout\",\"customer\":\"\",\"subtotal\":320,\"discount\":0,\"discount_percent\":0,\"discount_type\":\"regular\",\"discount_label\":\"Regular\",\"total\":320,\"items_count\":1,\"payment_method\":\"cash\"}', 'success', NULL, NULL, NULL, 16, NULL, '2026-04-21 10:08:39'),
(22, 'order', 20, 'Nel', 'order', 17, 'ORD-20260421-69e6e3627cce1', 'create', 'Order created and completed', NULL, '{\"order_number\":\"ORD-20260421-69e6e3627cce1\",\"table\":\"Table 1\",\"customer\":\"Baddet\",\"subtotal\":350,\"discount\":0,\"discount_percent\":0,\"discount_type\":\"regular\",\"discount_label\":\"Regular\",\"total\":350,\"items_count\":1,\"payment_method\":\"cash\"}', 'success', NULL, NULL, NULL, 17, NULL, '2026-04-21 10:39:30'),
(23, 'order', 20, 'Nel', 'order', 18, 'ORD-20260421-69e70034e3684', 'create', 'Order created and completed', NULL, '{\"order_number\":\"ORD-20260421-69e70034e3684\",\"table\":\"Takeout\",\"customer\":\"\",\"subtotal\":120,\"discount\":0,\"discount_percent\":0,\"discount_type\":\"regular\",\"discount_label\":\"Regular\",\"total\":120,\"items_count\":1,\"payment_method\":\"cash\"}', 'success', NULL, NULL, NULL, 18, NULL, '2026-04-21 12:42:28'),
(24, 'order', 20, 'Nel', 'order', 19, 'ORD-20260421-69e700e72d8af', 'create', 'Order created and completed', NULL, '{\"order_number\":\"ORD-20260421-69e700e72d8af\",\"table\":\"Takeout\",\"customer\":\"\",\"subtotal\":245,\"discount\":49,\"discount_percent\":20,\"discount_type\":\"senior\",\"discount_label\":\"Senior Citizen (20%)\",\"total\":196,\"items_count\":1,\"payment_method\":\"cash\"}', 'success', NULL, NULL, NULL, 19, NULL, '2026-04-21 12:45:27'),
(25, 'order', 24, 'John', 'order', 20, 'ORD-20260421-69e7113d24d78', 'create', 'Order created and completed', NULL, '{\"order_number\":\"ORD-20260421-69e7113d24d78\",\"table\":\"Takeout\",\"customer\":\"\",\"subtotal\":842,\"discount\":0,\"discount_percent\":0,\"discount_type\":\"regular\",\"discount_label\":\"Regular\",\"total\":842,\"items_count\":2,\"payment_method\":\"cash\"}', 'success', NULL, NULL, NULL, 20, NULL, '2026-04-21 13:55:09'),
(26, 'order', 25, 'Jeri', 'order', 21, 'ORD-20260421-69e7116079156', 'create', 'Order created and completed', NULL, '{\"order_number\":\"ORD-20260421-69e7116079156\",\"table\":\"Takeout\",\"customer\":\"\",\"subtotal\":875,\"discount\":175,\"discount_percent\":20,\"discount_type\":\"pwd\",\"discount_label\":\"PWD (20%)\",\"total\":700,\"items_count\":1,\"payment_method\":\"cash\"}', 'success', NULL, NULL, NULL, 21, NULL, '2026-04-21 13:55:44'),
(27, 'order', 26, 'rest', 'order', 22, 'ORD-20260421-69e71181f215a', 'create', 'Order created and completed', NULL, '{\"order_number\":\"ORD-20260421-69e71181f215a\",\"table\":\"Takeout\",\"customer\":\"\",\"subtotal\":1303,\"discount\":260.6,\"discount_percent\":20,\"discount_type\":\"senior\",\"discount_label\":\"Senior Citizen (20%)\",\"total\":1042.4,\"items_count\":7,\"payment_method\":\"cash\"}', 'success', NULL, NULL, NULL, 22, NULL, '2026-04-21 13:56:18'),
(28, 'order', 24, 'John', 'order', 23, 'ORD-20260421-69e7176712abc', 'create', 'Order created and completed', NULL, '{\"order_number\":\"ORD-20260421-69e7176712abc\",\"table\":\"Takeout\",\"customer\":\"\",\"subtotal\":205,\"discount\":0,\"discount_percent\":0,\"discount_type\":\"regular\",\"discount_label\":\"Regular\",\"total\":205,\"items_count\":1,\"payment_method\":\"cash\"}', 'success', NULL, NULL, NULL, 23, NULL, '2026-04-21 14:21:27'),
(29, 'order', 24, 'John', 'order', 24, 'ORD-20260421-69e72b24487c6', 'create', 'Order created and completed', NULL, '{\"order_number\":\"ORD-20260421-69e72b24487c6\",\"table\":\"Takeout\",\"customer\":\"\",\"subtotal\":1140,\"discount\":228,\"discount_percent\":20,\"discount_type\":\"pwd\",\"discount_label\":\"PWD (20%)\",\"total\":912,\"items_count\":1,\"payment_method\":\"cash\"}', 'success', NULL, NULL, NULL, 24, NULL, '2026-04-21 15:45:40'),
(30, 'order', 24, 'John', 'order', 25, 'ORD-20260421-69e72fc87c949', 'create', 'Order created and completed', NULL, '{\"order_number\":\"ORD-20260421-69e72fc87c949\",\"table\":\"Takeout\",\"customer\":\"\",\"subtotal\":150,\"discount\":30,\"discount_percent\":20,\"discount_type\":\"pwd\",\"discount_label\":\"PWD (20%)\",\"total\":120,\"items_count\":1,\"payment_method\":\"cash\"}', 'success', NULL, NULL, NULL, 25, NULL, '2026-04-21 16:05:28'),
(31, 'order', 20, 'Nel', 'order', 26, 'ORD-20260421-69e79e6657d6d', 'create', 'Order created and completed', NULL, '{\"order_number\":\"ORD-20260421-69e79e6657d6d\",\"table\":\"Table 5\",\"customer\":\"\",\"subtotal\":1545,\"discount\":309,\"discount_percent\":20,\"discount_type\":\"pwd\",\"discount_label\":\"PWD (20%)\",\"total\":1236,\"items_count\":10,\"payment_method\":\"cash\"}', 'success', NULL, NULL, NULL, 26, NULL, '2026-04-21 23:57:26'),
(32, 'order', 25, 'Jeri', 'order', 27, 'ORD-20260421-69e7a1dbe2e98', 'create', 'Order created and completed', NULL, '{\"order_number\":\"ORD-20260421-69e7a1dbe2e98\",\"table\":\"Takeout\",\"customer\":\"\",\"subtotal\":230,\"discount\":0,\"discount_percent\":0,\"discount_type\":\"regular\",\"discount_label\":\"Regular\",\"total\":230,\"items_count\":2,\"payment_method\":\"e_wallet\"}', 'success', NULL, NULL, NULL, 27, NULL, '2026-04-22 00:12:11'),
(33, 'order', 26, 'rest', 'order', 28, 'ORD-20260421-69e7ad011c474', 'create', 'Order created and completed', NULL, '{\"order_number\":\"ORD-20260421-69e7ad011c474\",\"table\":\"Table 3\",\"customer\":\"\",\"subtotal\":1020,\"discount\":0,\"discount_percent\":0,\"discount_type\":\"regular\",\"discount_label\":\"Regular\",\"total\":1020,\"items_count\":6,\"payment_method\":\"cash\"}', 'success', NULL, NULL, NULL, 28, NULL, '2026-04-22 00:59:45'),
(34, 'order', 28, 'jiri', 'order', 29, 'ORD-20260421-69e7b4d11b14e', 'create', 'Order created and completed', NULL, '{\"order_number\":\"ORD-20260421-69e7b4d11b14e\",\"table\":\"Takeout\",\"customer\":\"\",\"subtotal\":1270,\"discount\":254,\"discount_percent\":20,\"discount_type\":\"senior\",\"discount_label\":\"Senior Citizen (20%)\",\"total\":1016,\"items_count\":7,\"payment_method\":\"online\"}', 'success', NULL, NULL, NULL, 29, NULL, '2026-04-22 01:33:05');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','manager','staff') NOT NULL DEFAULT 'staff',
  `branch_id` int(11) DEFAULT NULL,
  `status` enum('active','archived') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `branch_id`, `status`, `created_at`, `updated_at`) VALUES
(17, 'admin', '$2y$10$xqh9ORLWP4JiRC0TcVdt6O7gAQV/2gSd6X/cKO1pThLJAN460xc9W', 'admin', NULL, 'active', '2026-04-17 21:47:49', '2026-04-17 21:47:49'),
(18, 'Jerald', '$2y$10$djRY2B65c/RPumzwmPBbue2XUoLLhLFahr0YnljBouuLtdn1ICZmu', 'manager', 1, 'active', '2026-04-17 22:11:05', '2026-04-21 12:44:40'),
(20, 'Nel', '$2y$10$m7IUx4EgLfPcsm2cu3Il6e4V/eLoUhEKqS0nW3ab8rGDyCRsbZMgO', 'staff', 1, 'active', '2026-04-21 09:53:57', '2026-04-21 12:44:32'),
(24, 'John', '$2y$10$m5.fz4PR5JpsTzLeWGR0ieBSjXMdmLVXGxo7561HMESADXLCd7zWS', 'staff', 2, 'active', '2026-04-21 13:53:56', '2026-04-21 23:59:06'),
(25, 'Jeri', '$2y$10$ckT2fI6/uMxOUZaCbmovZ.jDZOQutf4BGpsbyoC5jSMLiIbbjofLi', 'staff', 3, 'active', '2026-04-21 13:54:13', '2026-04-21 23:59:12'),
(26, 'rest', '$2y$10$8FQx4KQ9v2H4iObfei2kEOU.Gw6RoCQJVHV2f9SediOuMjzyEU.nu', 'staff', 4, 'active', '2026-04-21 13:54:34', '2026-04-21 23:59:19'),
(27, 'jiji', '$2y$10$jiXIk80okbycxc4An543ZO74y3QxOnu/bidRJi1yXVRdN5zOFt2Si', 'manager', 4, 'active', '2026-04-22 01:29:56', '2026-04-22 01:29:56'),
(28, 'jiri', '$2y$10$03YVM9pfoDvWjy1XFFQUye6GyXZlIlwxItjh7bnuH32WmPi0gtopy', 'staff', 3, 'active', '2026-04-22 01:32:34', '2026-04-22 01:32:34'),
(29, 'jirimi', '$2y$10$b9ZngtwyoTndzw8wilkgTOBcr9S.M2s6pZKT8V8grWbtRGZKXamlm', 'manager', 3, 'active', '2026-04-22 01:33:55', '2026-04-22 01:33:55');

-- --------------------------------------------------------

--
-- Table structure for table `user_logs`
--

CREATE TABLE `user_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `detail` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `action_time` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_logs`
--

INSERT INTO `user_logs` (`id`, `user_id`, `username`, `action`, `detail`, `ip_address`, `session_id`, `action_time`) VALUES
(54, NULL, 'Jerald', 'create_user', 'Created POS user admin with role commissary', NULL, NULL, '2026-04-16 17:09:39'),
(55, NULL, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-16 17:09:48'),
(56, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-16 17:09:52'),
(57, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-16 17:14:38'),
(58, NULL, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-16 17:14:41'),
(59, NULL, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-16 17:17:32'),
(60, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-16 17:17:37'),
(61, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-16 17:26:34'),
(62, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-16 17:26:37'),
(63, NULL, 'admin', 'create_ingredient', 'Created ingredient #11', NULL, NULL, '2026-04-16 17:30:47'),
(64, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-16 17:34:32'),
(65, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-16 17:34:35'),
(66, NULL, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-16 17:38:09'),
(67, NULL, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-16 17:38:11'),
(68, NULL, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-16 17:43:35'),
(69, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-16 17:43:40'),
(70, NULL, 'Nel', 'create_order', 'Created order #11', NULL, NULL, '2026-04-16 17:47:42'),
(71, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-16 21:51:40'),
(72, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-16 22:13:48'),
(73, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-16 22:13:51'),
(74, NULL, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-16 22:19:27'),
(75, NULL, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-16 22:19:32'),
(76, NULL, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-16 22:20:32'),
(77, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-16 22:20:36'),
(78, NULL, 'admin', 'create_order', 'Created order #12', NULL, NULL, '2026-04-16 22:21:10'),
(79, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-16 22:30:40'),
(80, NULL, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-16 22:30:43'),
(81, NULL, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-16 22:32:36'),
(82, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-16 22:32:40'),
(83, NULL, 'system', 'default_admin_created', 'Created default admin user', NULL, NULL, '2026-04-16 22:51:06'),
(84, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-16 22:51:17'),
(85, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-16 22:52:17'),
(86, NULL, 'admin', 'create_user', 'Created POS user Manager with role commissary', NULL, NULL, '2026-04-17 00:12:30'),
(87, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:12:33'),
(88, NULL, 'Manager', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:12:35'),
(89, NULL, 'Manager', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:13:46'),
(90, NULL, 'Manager', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:13:48'),
(91, NULL, 'Manager', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:16:20'),
(92, NULL, 'Manager', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:16:21'),
(93, NULL, 'Manager', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:18:11'),
(94, NULL, 'Manager', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:18:13'),
(95, NULL, 'Manager', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:19:48'),
(96, NULL, 'Manager', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:19:49'),
(97, NULL, 'Manager', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:20:00'),
(98, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:20:07'),
(99, NULL, 'admin', 'create_user', 'Created POS user Manager with role manager', NULL, NULL, '2026-04-17 00:20:17'),
(100, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:20:21'),
(101, NULL, 'Manager', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:20:23'),
(102, NULL, 'Manager', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:20:25'),
(103, NULL, 'Manager', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:20:27'),
(104, NULL, 'Manager', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:21:20'),
(105, NULL, 'Manager', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:21:22'),
(106, NULL, 'Manager', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:21:25'),
(107, NULL, 'Manager', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:24:28'),
(108, NULL, 'Manager', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:24:29'),
(109, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:24:33'),
(110, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:24:49'),
(111, NULL, 'Manager', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:24:52'),
(112, NULL, 'Manager', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:24:53'),
(113, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:25:47'),
(114, NULL, 'admin', 'create_user', 'Created POS user Nel with role manager', NULL, NULL, '2026-04-17 00:25:59'),
(115, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:26:04'),
(116, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:26:07'),
(117, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:27:54'),
(118, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:28:11'),
(119, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:28:47'),
(120, NULL, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:28:49'),
(121, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:29:03'),
(122, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:29:57'),
(123, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:32:53'),
(124, NULL, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:37:40'),
(125, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:37:42'),
(126, NULL, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:38:34'),
(127, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:38:35'),
(128, NULL, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:41:07'),
(129, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:41:08'),
(130, NULL, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:41:09'),
(131, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:42:23'),
(132, NULL, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:42:29'),
(133, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:43:44'),
(134, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:43:46'),
(135, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:43:48'),
(136, NULL, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:43:49'),
(137, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:43:56'),
(138, NULL, 'system', 'default_admin_created', 'Created default admin user', NULL, NULL, '2026-04-17 00:44:56'),
(139, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:44:57'),
(140, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:45:03'),
(141, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:45:04'),
(142, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:45:47'),
(143, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:46:51'),
(144, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:46:53'),
(145, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:46:54'),
(146, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:53:14'),
(147, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:53:15'),
(148, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:55:53'),
(149, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:56:32'),
(150, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:56:36'),
(151, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:57:18'),
(152, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:58:01'),
(153, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:58:02'),
(154, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:58:10'),
(155, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 00:59:53'),
(156, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 00:59:58'),
(157, NULL, 'admin', 'create_user', 'Created POS user Nel with role commissary', NULL, NULL, '2026-04-17 01:02:32'),
(158, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 01:02:41'),
(159, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:02:45'),
(160, NULL, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-17 01:04:09'),
(161, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:04:20'),
(162, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 01:08:08'),
(163, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:08:23'),
(164, NULL, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-17 01:08:32'),
(165, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:08:45'),
(166, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 01:08:52'),
(167, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:12:05'),
(168, NULL, 'admin', 'create_user', 'Created POS user Jerald with role commissary', NULL, NULL, '2026-04-17 01:12:14'),
(169, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 01:12:17'),
(170, NULL, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:12:21'),
(171, NULL, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-17 01:15:10'),
(172, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:15:32'),
(173, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:15:55'),
(174, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:16:46'),
(175, NULL, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-17 01:16:50'),
(176, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:16:59'),
(177, NULL, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-17 01:19:58'),
(178, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:20:02'),
(179, NULL, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-17 01:20:53'),
(180, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:20:56'),
(181, NULL, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-17 01:20:57'),
(182, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:21:24'),
(183, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 01:21:51'),
(184, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:21:57'),
(185, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 01:23:53'),
(186, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:23:57'),
(187, NULL, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-17 01:23:58'),
(188, NULL, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:24:07'),
(189, NULL, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-17 01:24:08'),
(190, NULL, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:24:22'),
(191, NULL, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-17 01:24:22'),
(192, NULL, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:24:59'),
(193, NULL, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-17 01:25:00'),
(194, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:27:14'),
(195, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:27:40'),
(196, NULL, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-17 01:27:41'),
(197, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:27:46'),
(198, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 01:27:47'),
(199, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:27:59'),
(200, NULL, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-17 01:28:00'),
(201, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:29:01'),
(202, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 01:29:03'),
(203, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:31:08'),
(204, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 01:44:47'),
(205, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:44:52'),
(206, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 01:44:54'),
(207, NULL, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:45:02'),
(208, NULL, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-17 01:45:10'),
(209, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:45:14'),
(210, NULL, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-17 01:46:19'),
(211, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:46:22'),
(212, NULL, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-17 01:46:23'),
(213, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:49:19'),
(214, NULL, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-17 01:49:20'),
(215, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:49:26'),
(216, NULL, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-17 01:49:56'),
(217, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 01:50:36'),
(218, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 01:58:33'),
(219, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 02:00:04'),
(220, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 02:00:15'),
(221, NULL, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-17 02:00:28'),
(222, NULL, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-17 02:00:46'),
(223, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 02:00:54'),
(224, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 02:01:04'),
(225, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 02:01:20'),
(226, NULL, 'admin', 'create_user', 'Created POS user Rim with role manager', NULL, NULL, '2026-04-17 02:08:31'),
(227, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 02:08:43'),
(228, NULL, 'Rim', 'login', 'User logged in', NULL, NULL, '2026-04-17 02:08:48'),
(229, NULL, 'Rim', 'create_ingredient', 'Created ingredient #12', NULL, NULL, '2026-04-17 02:10:08'),
(230, NULL, 'Rim', 'logout', 'User logged out', NULL, NULL, '2026-04-17 02:26:19'),
(231, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 02:26:25'),
(232, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 02:27:45'),
(233, NULL, 'Rim', 'login', 'User logged in', NULL, NULL, '2026-04-17 02:27:59'),
(234, NULL, 'Rim', 'logout', 'User logged out', NULL, NULL, '2026-04-17 02:35:54'),
(235, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 02:35:59'),
(236, NULL, 'system', 'default_admin_created', 'Created default admin user', NULL, NULL, '2026-04-17 04:30:47'),
(237, NULL, 'system', 'default_admin_created', 'Created default admin user', NULL, NULL, '2026-04-17 04:31:50'),
(238, NULL, 'system', 'default_admin_created', 'Created default admin user', NULL, NULL, '2026-04-17 21:47:49'),
(239, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 21:48:01'),
(240, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 22:10:44'),
(241, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-17 22:10:55'),
(242, 17, 'admin', 'create_user', 'Created POS user Jerald with role manager', NULL, NULL, '2026-04-17 22:11:05'),
(243, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-17 22:11:14'),
(244, 18, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-17 22:11:18'),
(245, 18, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-17 22:11:24'),
(246, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-18 06:21:27'),
(247, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-20 22:34:24'),
(248, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-20 23:06:39'),
(249, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-20 23:06:44'),
(250, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-20 23:06:46'),
(251, 18, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-20 23:07:10'),
(252, 18, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-20 23:13:02'),
(253, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-20 23:13:20'),
(254, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-20 23:35:07'),
(255, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-20 23:35:13'),
(256, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-20 23:53:42'),
(257, 18, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-20 23:53:48'),
(258, 18, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-20 23:55:35'),
(259, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-20 23:55:40'),
(260, 17, 'admin', 'create_user', 'Created POS user Nel with role staff', NULL, NULL, '2026-04-20 23:55:50'),
(261, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-20 23:56:03'),
(262, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-20 23:56:16'),
(263, NULL, 'Nel', 'create_order', 'Created order #13', NULL, NULL, '2026-04-20 23:56:35'),
(264, NULL, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-20 23:58:19'),
(265, 18, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-20 23:58:28'),
(266, 18, 'Jerald', 'create_ingredient', 'Created ingredient #15', NULL, NULL, '2026-04-21 00:00:28'),
(267, 18, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-21 00:23:00'),
(268, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-21 00:23:05'),
(269, NULL, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-21 00:24:46'),
(270, 18, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-21 00:24:52'),
(271, 18, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-21 00:26:30'),
(272, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-21 00:26:39'),
(273, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-21 00:30:44'),
(274, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-21 00:30:48'),
(275, NULL, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-21 00:38:04'),
(276, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-21 00:38:09'),
(277, 17, 'admin', 'add_menu_item', 'Added menu item: chicken ko', NULL, NULL, '2026-04-21 00:38:27'),
(278, 17, 'admin', 'update_menu_item', 'Updated menu item #1: Fried Chicken Wings', NULL, NULL, '2026-04-21 00:39:10'),
(279, 17, 'admin', 'add_menu_item', 'Added menu item: Fried Chicken Leg', NULL, NULL, '2026-04-21 00:41:39'),
(280, 17, 'admin', 'update_menu_item', 'Updated menu item #14: Fried Chicken Leg', NULL, NULL, '2026-04-21 00:41:52'),
(281, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-21 00:45:14'),
(282, NULL, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-21 00:45:23'),
(283, NULL, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-21 00:49:23'),
(284, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-21 00:49:32'),
(285, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-21 00:49:57'),
(286, 18, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-21 00:50:06'),
(287, 18, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-21 00:50:27'),
(288, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-21 00:50:32'),
(289, 17, 'admin', 'update_menu_item', 'Updated menu item #14: Fried Chicken Leg', NULL, NULL, '2026-04-21 00:55:04'),
(290, 17, 'admin', 'update_menu_item', 'Updated menu item #11: Kare-Kare', NULL, NULL, '2026-04-21 00:57:46'),
(291, 17, 'admin', 'update_menu_item', 'Updated menu item #14: Fried Chicken Leg', NULL, NULL, '2026-04-21 01:08:38'),
(292, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-21 04:13:39'),
(293, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-21 04:13:45'),
(294, 17, 'admin', 'delete_menu_item', 'Archived menu item #13', NULL, NULL, '2026-04-21 04:14:17'),
(295, 17, 'admin', 'delete_menu_item', 'Archived menu item #11', NULL, NULL, '2026-04-21 04:14:20'),
(296, 17, 'admin', 'delete_menu_item', 'Archived menu item #1', NULL, NULL, '2026-04-21 04:14:36'),
(297, 17, 'admin', 'delete_menu_item', 'Archived menu item #1', NULL, NULL, '2026-04-21 04:14:42'),
(298, 17, 'admin', 'delete_menu_item', 'Archived menu item #14', NULL, NULL, '2026-04-21 04:15:09'),
(299, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-21 04:34:34'),
(300, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-21 04:34:57'),
(301, 17, 'admin', 'archive_menu_item', 'Archived menu item #', NULL, NULL, '2026-04-21 09:53:57'),
(302, 17, 'admin', 'create_user', 'Created POS user Rim with role staff', NULL, NULL, '2026-04-21 10:04:30'),
(303, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-21 10:07:20'),
(304, 20, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-21 10:07:31'),
(305, 20, 'Nel', 'create_order', 'Created order #14', NULL, NULL, '2026-04-21 10:07:54'),
(306, 20, 'Nel', 'create_order', 'Created order #15', NULL, NULL, '2026-04-21 10:08:19'),
(307, 20, 'Nel', 'create_order', 'Created order #16', NULL, NULL, '2026-04-21 10:08:39'),
(308, 20, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-21 10:08:52'),
(309, 18, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-21 10:09:01'),
(310, 18, 'Jerald', 'add_menu_item', 'Added menu item: Caldereta', NULL, NULL, '2026-04-21 10:25:05'),
(311, 18, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-21 10:30:22'),
(312, 20, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-21 10:30:27'),
(313, 20, 'Nel', 'create_order', 'Created order #17', NULL, NULL, '2026-04-21 10:39:30'),
(314, 20, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-21 10:43:16'),
(315, 18, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-21 10:43:21'),
(316, 18, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-21 10:44:27'),
(317, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-21 10:44:33'),
(318, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-21 10:56:29'),
(319, 18, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-21 10:56:33'),
(320, 18, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-21 12:42:13'),
(321, 20, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-21 12:42:18'),
(322, 20, 'Nel', 'create_order', 'Created order #18', NULL, NULL, '2026-04-21 12:42:28'),
(323, 20, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-21 12:43:48'),
(324, 18, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-21 12:43:52'),
(325, 18, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-21 12:45:08'),
(326, 20, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-21 12:45:11'),
(327, 20, 'Nel', 'create_order', 'Created order #19', NULL, NULL, '2026-04-21 12:45:27'),
(328, 20, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-21 12:45:32'),
(329, 18, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-21 12:45:37'),
(330, 18, 'Jerald', 'add_menu_item', 'Added menu item: Chili', NULL, NULL, '2026-04-21 12:59:52'),
(331, 18, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-21 13:00:37'),
(332, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-21 13:00:43'),
(333, 17, 'admin', 'add_menu_item', 'Added menu item: sili', NULL, NULL, '2026-04-21 13:01:04'),
(334, 17, 'admin', 'update_menu_item', 'Updated menu item #15: Caldereta', NULL, NULL, '2026-04-21 13:11:54'),
(335, 17, 'admin', 'delete_menu_item', 'Archived menu item #17', NULL, NULL, '2026-04-21 13:12:29'),
(336, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-21 13:16:37'),
(337, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-21 13:16:44'),
(338, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-21 13:25:46'),
(339, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-21 13:25:50'),
(340, 17, 'admin', 'create_user', 'Created POS user John with role staff', NULL, NULL, '2026-04-21 13:53:56'),
(341, 17, 'admin', 'create_user', 'Created POS user Jeri with role staff', NULL, NULL, '2026-04-21 13:54:13'),
(342, 17, 'admin', 'create_user', 'Created POS user rest with role staff', NULL, NULL, '2026-04-21 13:54:34'),
(343, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-21 13:54:37'),
(344, 24, 'John', 'login', 'User logged in', NULL, NULL, '2026-04-21 13:54:44'),
(345, 24, 'John', 'create_order', 'Created order #20', NULL, NULL, '2026-04-21 13:55:09'),
(346, 24, 'John', 'logout', 'User logged out', NULL, NULL, '2026-04-21 13:55:17'),
(347, 25, 'Jeri', 'login', 'User logged in', NULL, NULL, '2026-04-21 13:55:22'),
(348, 25, 'Jeri', 'create_order', 'Created order #21', NULL, NULL, '2026-04-21 13:55:44'),
(349, 25, 'Jeri', 'logout', 'User logged out', NULL, NULL, '2026-04-21 13:55:47'),
(350, 26, 'rest', 'login', 'User logged in', NULL, NULL, '2026-04-21 13:56:02'),
(351, 26, 'rest', 'create_order', 'Created order #22', NULL, NULL, '2026-04-21 13:56:18'),
(352, 26, 'rest', 'logout', 'User logged out', NULL, NULL, '2026-04-21 13:56:21'),
(353, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-21 13:56:25'),
(354, 17, 'admin', 'delete_menu_item', 'Archived menu item #16', NULL, NULL, '2026-04-21 14:16:53'),
(355, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-21 14:20:55'),
(356, 24, 'John', 'login', 'User logged in', NULL, NULL, '2026-04-21 14:21:14'),
(357, 24, 'John', 'create_order', 'Created order #23', NULL, NULL, '2026-04-21 14:21:27'),
(358, 24, 'John', 'logout', 'User logged out', NULL, NULL, '2026-04-21 14:22:07'),
(359, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-21 14:22:14'),
(360, 17, 'admin', 'restock_menu_item', 'Restocked item #6 +10', NULL, NULL, '2026-04-21 15:22:19'),
(361, 17, 'admin', 'restock_menu_item', 'Restocked item #6 +5', NULL, NULL, '2026-04-21 15:22:56'),
(362, 17, 'admin', 'delete_menu_item', 'Archived menu item #15', NULL, NULL, '2026-04-21 15:23:24'),
(363, 17, 'admin', 'update_menu_item', 'Updated menu item #13: chicken ko', NULL, NULL, '2026-04-21 15:27:01'),
(364, 17, 'admin', 'add_menu_item', 'Added menu item: Fried Chicken Wings', NULL, NULL, '2026-04-21 15:28:12'),
(365, 17, 'admin', 'add_menu_item', 'Added menu item: Fried Chicken Leg', NULL, NULL, '2026-04-21 15:31:10'),
(366, 17, 'admin', 'delete_menu_item', 'Archived menu item #13', NULL, NULL, '2026-04-21 15:31:32'),
(367, 17, 'admin', 'delete_menu_item', 'Archived menu item #18', NULL, NULL, '2026-04-21 15:31:34'),
(368, 17, 'admin', 'delete_menu_item', 'Archived menu item #11', NULL, NULL, '2026-04-21 15:31:37'),
(369, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-21 15:31:53'),
(370, 18, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-21 15:31:56'),
(371, 18, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-21 15:32:46'),
(372, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-21 15:32:51'),
(373, 17, 'admin', 'restock_menu_item', 'Restocked item #5 +50', NULL, NULL, '2026-04-21 15:36:26'),
(374, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-21 15:43:28'),
(375, 18, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-21 15:43:42'),
(376, 18, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-21 15:44:05'),
(377, 18, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-21 15:44:11'),
(378, 18, 'Jerald', 'restock_menu_item', 'Restocked item #10 +10', NULL, NULL, '2026-04-21 15:44:40'),
(379, 18, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-21 15:44:52'),
(380, 24, 'John', 'login', 'User logged in', NULL, NULL, '2026-04-21 15:45:03'),
(381, 24, 'John', 'create_order', 'Created order #24', NULL, NULL, '2026-04-21 15:45:40'),
(382, 24, 'John', 'logout', 'User logged out', NULL, NULL, '2026-04-21 15:56:39'),
(383, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-21 15:59:49'),
(384, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-21 16:01:51'),
(385, 18, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-21 16:02:05'),
(386, 18, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-21 16:04:02'),
(387, 24, 'John', 'login', 'User logged in', NULL, NULL, '2026-04-21 16:04:10'),
(388, 24, 'John', 'create_order', 'Created order #25', NULL, NULL, '2026-04-21 16:05:28'),
(389, 24, 'John', 'logout', 'User logged out', NULL, NULL, '2026-04-21 16:08:22'),
(390, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-21 16:18:48'),
(391, 24, 'John', 'login', 'User logged in', NULL, NULL, '2026-04-21 23:21:45'),
(392, 24, 'John', 'logout', 'User logged out', NULL, NULL, '2026-04-21 23:21:49'),
(393, 18, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-21 23:21:53'),
(394, 18, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-21 23:22:50'),
(395, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-21 23:22:56'),
(396, 17, 'admin', 'restock_menu_item', 'Restocked item #10 +10', NULL, NULL, '2026-04-21 23:54:43'),
(397, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-21 23:56:50'),
(398, 20, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-21 23:56:54'),
(399, 20, 'Nel', 'create_order', 'Created order #26', NULL, NULL, '2026-04-21 23:57:26'),
(400, 20, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-21 23:57:31'),
(401, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-21 23:57:35'),
(402, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-22 00:11:25'),
(403, 25, 'Jeri', 'login', 'User logged in', NULL, NULL, '2026-04-22 00:11:44'),
(404, 25, 'Jeri', 'create_order', 'Created order #27', NULL, NULL, '2026-04-22 00:12:11'),
(405, 25, 'Jeri', 'logout', 'User logged out', NULL, NULL, '2026-04-22 00:12:15'),
(406, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-22 00:12:20'),
(407, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-22 00:59:00'),
(408, 26, 'rest', 'login', 'User logged in', NULL, NULL, '2026-04-22 00:59:07'),
(409, 26, 'rest', 'create_order', 'Created order #28', NULL, NULL, '2026-04-22 00:59:45'),
(410, 26, 'rest', 'logout', 'User logged out', NULL, NULL, '2026-04-22 00:59:49'),
(411, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-22 01:00:11'),
(412, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-22 01:26:34'),
(413, 18, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-22 01:26:38'),
(414, 18, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-22 01:29:13'),
(415, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-22 01:29:30'),
(416, 17, 'admin', 'create_user', 'Created POS user jiji with role manager', NULL, NULL, '2026-04-22 01:29:56'),
(417, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-22 01:30:00'),
(418, 27, 'jiji', 'login', 'User logged in', NULL, NULL, '2026-04-22 01:30:08'),
(419, 27, 'jiji', 'logout', 'User logged out', NULL, NULL, '2026-04-22 01:31:54'),
(420, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-22 01:32:00'),
(421, 17, 'admin', 'create_user', 'Created POS user jiri with role staff', NULL, NULL, '2026-04-22 01:32:34'),
(422, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-22 01:32:35'),
(423, 28, 'jiri', 'login', 'User logged in', NULL, NULL, '2026-04-22 01:32:41'),
(424, 28, 'jiri', 'create_order', 'Created order #29', NULL, NULL, '2026-04-22 01:33:05'),
(425, 28, 'jiri', 'logout', 'User logged out', NULL, NULL, '2026-04-22 01:33:37'),
(426, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-22 01:33:42'),
(427, 17, 'admin', 'create_user', 'Created POS user jirimi with role manager', NULL, NULL, '2026-04-22 01:33:55'),
(428, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-22 01:33:56'),
(429, 29, 'jirimi', 'login', 'User logged in', NULL, NULL, '2026-04-22 01:34:03'),
(430, 29, 'jirimi', 'logout', 'User logged out', NULL, NULL, '2026-04-22 01:34:49'),
(431, 18, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-22 01:36:31'),
(432, 18, 'Jerald', 'add_menu_item', 'Added menu item: Fried Chicken Wings', NULL, NULL, '2026-04-22 01:39:05'),
(433, 18, 'Jerald', 'update_menu_item', 'Updated menu item #19: Fried Chicken Leg', NULL, NULL, '2026-04-22 01:39:19'),
(434, 18, 'Jerald', 'add_menu_item', 'Added menu item: Tapsilog', NULL, NULL, '2026-04-22 01:39:46'),
(435, 18, 'Jerald', 'add_menu_item', 'Added menu item: Pan-Grilled Liempo', NULL, NULL, '2026-04-22 01:42:37'),
(436, 18, 'Jerald', 'add_menu_item', 'Added menu item: Tocilog', NULL, NULL, '2026-04-22 01:43:10'),
(437, 18, 'Jerald', 'add_menu_item', 'Added menu item: Porksilog', NULL, NULL, '2026-04-22 01:43:44'),
(438, 18, 'Jerald', 'add_menu_item', 'Added menu item: Shanghaisilog', NULL, NULL, '2026-04-22 01:44:24'),
(439, 18, 'Jerald', 'add_menu_item', 'Added menu item: Bisteksilog', NULL, NULL, '2026-04-22 01:44:44'),
(440, 18, 'Jerald', 'add_menu_item', 'Added menu item: Bacsilog', NULL, NULL, '2026-04-22 01:45:08'),
(441, 18, 'Jerald', 'add_menu_item', 'Added menu item: Spamsilog', NULL, NULL, '2026-04-22 01:45:37'),
(442, 18, 'Jerald', 'update_menu_item', 'Updated menu item #9: Brewed Coffee', NULL, NULL, '2026-04-22 01:46:32'),
(443, 18, 'Jerald', 'update_menu_item', 'Updated menu item #8: Iced Tea', NULL, NULL, '2026-04-22 01:46:43'),
(444, 18, 'Jerald', 'add_menu_item', 'Added menu item: Soda', NULL, NULL, '2026-04-22 01:47:20'),
(445, 18, 'Jerald', 'add_menu_item', 'Added menu item: Mango Juice', NULL, NULL, '2026-04-22 01:47:50'),
(446, 18, 'Jerald', 'add_menu_item', 'Added menu item: Pineapple Juice', NULL, NULL, '2026-04-22 01:48:15'),
(447, 18, 'Jerald', 'add_menu_item', 'Added menu item: Lemonade', NULL, NULL, '2026-04-22 01:48:41'),
(448, 18, 'Jerald', 'add_menu_item', 'Added menu item: Bottled Water', NULL, NULL, '2026-04-22 01:49:04'),
(449, 18, 'Jerald', 'update_menu_item', 'Updated menu item #31: Pineapple Juice', NULL, NULL, '2026-04-22 01:49:34'),
(450, 18, 'Jerald', 'add_menu_item', 'Added menu item: Four Seasons', NULL, NULL, '2026-04-22 01:50:17'),
(451, 18, 'Jerald', 'add_menu_item', 'Added menu item: Cherry Lemonade', NULL, NULL, '2026-04-22 01:50:41'),
(452, 18, 'Jerald', 'add_menu_item', 'Added menu item: Blue Lemonade', NULL, NULL, '2026-04-22 01:51:09'),
(453, 18, 'Jerald', 'add_menu_item', 'Added menu item: Cucumber Lemonade', NULL, NULL, '2026-04-22 01:51:33'),
(454, 18, 'Jerald', 'update_menu_item', 'Updated menu item #7: Fruit Salad', NULL, NULL, '2026-04-22 01:52:57'),
(455, 18, 'Jerald', 'add_menu_item', 'Added menu item: Pandan Jelly', NULL, NULL, '2026-04-22 01:53:29'),
(456, 18, 'Jerald', 'add_menu_item', 'Added menu item: Cream of Corn', NULL, NULL, '2026-04-22 01:54:08'),
(457, 18, 'Jerald', 'add_menu_item', 'Added menu item: Cream of Mushroom', NULL, NULL, '2026-04-22 01:54:32'),
(458, 18, 'Jerald', 'add_menu_item', 'Added menu item: Rice', NULL, NULL, '2026-04-22 01:55:07'),
(459, 18, 'Jerald', 'add_menu_item', 'Added menu item: Gravy', NULL, NULL, '2026-04-22 01:55:39'),
(460, 18, 'Jerald', 'add_menu_item', 'Added menu item: Egg', NULL, NULL, '2026-04-22 01:56:01'),
(461, 18, 'Jerald', 'add_menu_item', 'Added menu item: Vegetables', NULL, NULL, '2026-04-22 01:56:30'),
(462, 18, 'Jerald', 'add_menu_item', 'Added menu item: Dressing', NULL, NULL, '2026-04-22 01:56:52'),
(463, 18, 'Jerald', 'add_menu_item', 'Added menu item: Toasted Bread', NULL, NULL, '2026-04-22 01:57:32'),
(464, 18, 'Jerald', 'update_menu_item', 'Updated menu item #46: Toasted Bread', NULL, NULL, '2026-04-22 01:57:50'),
(465, 18, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-22 01:59:10'),
(466, 20, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-22 01:59:15'),
(467, 20, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-22 02:01:23'),
(468, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-23 01:01:29'),
(469, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-23 01:02:53'),
(470, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-23 01:03:41'),
(471, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-23 01:06:02'),
(472, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-23 01:11:45'),
(473, 17, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-23 01:14:56'),
(474, 20, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-23 01:15:03'),
(475, 20, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-23 01:30:58'),
(476, 17, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-23 01:31:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `archived_records`
--
ALTER TABLE `archived_records`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `daily_sales_summary`
--
ALTER TABLE `daily_sales_summary`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sale_date` (`sale_date`),
  ADD KEY `idx_sale_date` (`sale_date`);

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_stock` (`stock`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_ingredient_id` (`ingredient_id`),
  ADD KEY `idx_menu_item_id` (`menu_item_id`),
  ADD KEY `idx_reference` (`reference_id`,`reference_type`),
  ADD KEY `idx_recorded_at` (`recorded_at`),
  ADD KEY `idx_recorded_by` (`recorded_by`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_stock` (`stock`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_price` (`price`);

--
-- Indexes for table `menu_item_history`
--
ALTER TABLE `menu_item_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_menu_item_id` (`menu_item_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_changed_at` (`changed_at`),
  ADD KEY `fk_menu_item_history_user` (`changed_by`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_order_number` (`order_number`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_payment_method` (`payment_method`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_completed_at` (`completed_at`),
  ADD KEY `idx_total` (`total`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_menu_item_id` (`menu_item_id`);

--
-- Indexes for table `payment_reconciliation`
--
ALTER TABLE `payment_reconciliation`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_date_method` (`reconciliation_date`,`payment_method`),
  ADD KEY `idx_reconciliation_date` (`reconciliation_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `fk_payment_reconciliation_user` (`reconciled_by`);

--
-- Indexes for table `sales_transactions`
--
ALTER TABLE `sales_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `transaction_logs`
--
ALTER TABLE `transaction_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_transaction_type` (`transaction_type`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_logged_at` (`logged_at`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_related_order` (`related_order_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `fk_transaction_logs_parent` (`related_transaction_log_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_users_branch` (`branch_id`);

--
-- Indexes for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_action_time` (`action_time`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `archived_records`
--
ALTER TABLE `archived_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `daily_sales_summary`
--
ALTER TABLE `daily_sales_summary`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `menu_item_history`
--
ALTER TABLE `menu_item_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `payment_reconciliation`
--
ALTER TABLE `payment_reconciliation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_transactions`
--
ALTER TABLE `sales_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transaction_logs`
--
ALTER TABLE `transaction_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=477;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD CONSTRAINT `fk_inventory_movements_ingredient` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_inventory_movements_menu_item` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_inventory_movements_user` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `menu_item_history`
--
ALTER TABLE `menu_item_history`
  ADD CONSTRAINT `fk_menu_item_history_item` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_menu_item_history_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_menu` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_reconciliation`
--
ALTER TABLE `payment_reconciliation`
  ADD CONSTRAINT `fk_payment_reconciliation_user` FOREIGN KEY (`reconciled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sales_transactions`
--
ALTER TABLE `sales_transactions`
  ADD CONSTRAINT `sales_transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `transaction_logs`
--
ALTER TABLE `transaction_logs`
  ADD CONSTRAINT `fk_transaction_logs_order` FOREIGN KEY (`related_order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_transaction_logs_parent` FOREIGN KEY (`related_transaction_log_id`) REFERENCES `transaction_logs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_transaction_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD CONSTRAINT `fk_user_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
