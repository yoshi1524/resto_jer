-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 13, 2026 at 10:44 AM
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
(10, 'paminta', 'kg', 12.00, 5.00, 0.00, 'available', '2026-04-13 16:12:20', '2026-04-13 16:12:20');

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
(1, 'in', NULL, NULL, 12.00, 0.00, 12.00, 'Initial stock', NULL, '', 3, '0', '2026-04-13 15:58:45'),
(2, 'in', 10, NULL, 12.00, 0.00, 12.00, 'Initial stock', NULL, '', 3, '0', '2026-04-13 16:12:20');

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
  `status` enum('available','unavailable') NOT NULL DEFAULT 'available',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `emoji`, `name`, `category`, `price`, `cost`, `stock`, `min_stock`, `status`, `created_at`, `updated_at`) VALUES
(1, '🍗', 'Fried Chicken', 'Country Classics', 185.00, 75.00, 20, 5, 'available', '2026-04-13 14:57:41', '2026-04-13 14:57:41'),
(2, '🍝', 'Spaghetti', 'Sizzling Favorites', 150.00, 50.00, 15, 5, 'available', '2026-04-13 14:57:41', '2026-04-13 14:57:41'),
(3, '🍣', 'Salmon Sashimi', 'Heart Lover\'s Delight', 320.00, 150.00, 8, 3, 'available', '2026-04-13 14:57:41', '2026-04-13 14:57:41'),
(4, '🥗', 'Caesar Salad', 'Heart Lover\'s Delight', 120.00, 40.00, 12, 5, 'available', '2026-04-13 14:57:41', '2026-04-13 14:57:41'),
(5, '🍲', 'Sinigang na Baboy', 'Country Classics', 175.00, 70.00, 10, 4, 'available', '2026-04-13 14:57:41', '2026-04-13 14:57:41'),
(6, '🍛', 'Beef Caldereta', 'Sizzling Favorites', 210.00, 85.00, 6, 3, 'available', '2026-04-13 14:57:41', '2026-04-13 14:57:41'),
(7, '🧁', 'Chocolate Lava Cake', 'Desserts', 135.00, 45.00, 14, 5, 'available', '2026-04-13 14:57:41', '2026-04-13 14:57:41'),
(8, '🥤', 'Iced Tea', 'Beverages', 60.00, 15.00, 30, 10, 'available', '2026-04-13 14:57:41', '2026-04-13 14:57:41'),
(9, '☕', 'Brewed Coffee', 'Beverages', 80.00, 20.00, 25, 10, 'available', '2026-04-13 14:57:41', '2026-04-13 14:57:41'),
(10, '🍟', 'French Fries', 'Extras', 95.00, 30.00, 3, 5, 'available', '2026-04-13 14:57:41', '2026-04-13 14:57:41'),
(11, '🥘', 'Kare-Kare', 'Country Classics', 245.00, 100.00, 0, 3, 'available', '2026-04-13 14:57:41', '2026-04-13 14:57:41'),
(12, '🍨', 'Halo-Halo', 'Desserts', 110.00, 35.00, 18, 5, 'available', '2026-04-13 14:57:41', '2026-04-13 14:57:41');

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
  `modified_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `user_id`, `username`, `customer_name`, `table_name`, `subtotal`, `discount_amount`, `discount_percent`, `tax_amount`, `total`, `payment_method`, `payment_reference`, `payment_details`, `cash_received`, `change_amount`, `status`, `notes`, `created_at`, `completed_at`, `modified_at`) VALUES
(1, 'ORD-20260413-69dc98c904c94', 4, 'Nel', '', 'Takeout', 60.00, 0.00, 0.00, 0.00, 60.00, 'cash', '', NULL, 40.00, 40.00, 'completed', NULL, '2026-04-13 15:18:33', '2026-04-13 09:18:33', '2026-04-13 15:18:33'),
(2, 'ORD-20260413-69dc9cc88f384', 4, 'Nel', 'Pogi', 'Takeout', 295.00, 0.00, 0.00, 0.00, 295.00, 'cash', '', NULL, 5.00, 5.00, 'completed', NULL, '2026-04-13 15:35:36', '2026-04-13 09:35:36', '2026-04-13 15:35:36'),
(3, 'ORD-20260413-69dc9ccb29bbd', 4, 'Nel', 'Pogi', 'Takeout', 295.00, 0.00, 0.00, 0.00, 295.00, 'cash', '', NULL, 5.00, 5.00, 'completed', NULL, '2026-04-13 15:35:39', '2026-04-13 09:35:39', '2026-04-13 15:35:39'),
(4, 'ORD-20260413-69dc9cd82603f', 4, 'Nel', 'Pogi', 'Takeout', 295.00, 0.00, 0.00, 0.00, 295.00, 'cash', '', NULL, 5.00, 5.00, 'completed', NULL, '2026-04-13 15:35:52', '2026-04-13 09:35:52', '2026-04-13 15:35:52'),
(5, 'ORD-20260413-69dc9cec8c3cd', 4, 'Nel', 'Pogi', 'Takeout', 295.00, 0.00, 0.00, 0.00, 295.00, 'cash', '', NULL, 5.00, 5.00, 'completed', NULL, '2026-04-13 15:36:12', '2026-04-13 09:36:12', '2026-04-13 15:36:12'),
(6, 'ORD-20260413-69dc9d9de59cf', 4, 'Nel', 'Pogi', 'Takeout', 295.00, 0.00, 0.00, 0.00, 295.00, 'cash', '', NULL, 5.00, 5.00, 'completed', NULL, '2026-04-13 15:39:09', '2026-04-13 09:39:09', '2026-04-13 15:39:09'),
(7, 'ORD-20260413-69dc9da222db4', 4, 'Nel', 'Pogi', 'Takeout', 295.00, 0.00, 0.00, 0.00, 295.00, 'cash', '', NULL, 5.00, 5.00, 'completed', NULL, '2026-04-13 15:39:14', '2026-04-13 09:39:14', '2026-04-13 15:39:14'),
(8, 'ORD-20260413-69dc9e225723c', 4, 'Nel', '', 'Takeout', 215.00, 0.00, 0.00, 0.00, 215.00, 'cash', '', NULL, 5.00, 5.00, 'completed', NULL, '2026-04-13 15:41:22', '2026-04-13 09:41:22', '2026-04-13 15:41:22'),
(9, 'ORD-20260413-69dca6990eeb6', 4, 'Nel', 'nel', 'Takeout', 850.00, 85.00, 0.00, 0.00, 765.00, 'e_wallet', '120763070018', NULL, 0.00, 0.00, 'completed', NULL, '2026-04-13 16:17:29', '2026-04-13 10:17:29', '2026-04-13 16:17:29');

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
(14, 9, NULL, 'T-Bone Steak', '🍽', 1, 280.00, 280.00, 0.00, NULL, '2026-04-13 16:17:29');

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
(2, 'order', 4, 'Nel', '', 1, '0', '0', 'Order created and completed', NULL, '{\"order_number\":\"ORD-20260413-69dc98c904c94\",\"table\":\"Takeout\",\"customer\":\"\",\"subtotal\":60,\"discount\":0,\"total\":60,\"items_count\":1,\"payment_method\":\"cash\"}', 'success', NULL, NULL, NULL, 1, NULL, '2026-04-13 15:18:33'),
(3, 'order', 4, 'Nel', 'order', 8, 'ORD-20260413-69dc9e225723c', 'create', 'Order created and completed', NULL, '{\"order_number\":\"ORD-20260413-69dc9e225723c\",\"table\":\"Takeout\",\"customer\":\"\",\"subtotal\":215,\"discount\":0,\"total\":215,\"items_count\":1,\"payment_method\":\"cash\"}', 'success', NULL, NULL, NULL, 8, NULL, '2026-04-13 15:41:22'),
(4, 'user_action', 3, 'Jerald', 'ingredient', 9, 'paminta', 'create', 'Ingredient created', NULL, '{\"name\":\"paminta\",\"unit\":\"kg\",\"stock\":12}', 'success', NULL, NULL, NULL, NULL, NULL, '2026-04-13 15:58:45'),
(5, 'user_action', 3, 'Jerald', 'ingredient', 10, 'paminta', 'create', 'Ingredient created', NULL, '{\"name\":\"paminta\",\"unit\":\"kg\",\"stock\":12}', 'success', NULL, NULL, NULL, NULL, NULL, '2026-04-13 16:12:20'),
(6, 'order', 4, 'Nel', 'order', 9, 'ORD-20260413-69dca6990eeb6', 'create', 'Order created and completed', NULL, '{\"order_number\":\"ORD-20260413-69dca6990eeb6\",\"table\":\"Takeout\",\"customer\":\"nel\",\"subtotal\":850,\"discount\":85,\"total\":765,\"items_count\":6,\"payment_method\":\"e_wallet\"}', 'success', NULL, NULL, NULL, 9, NULL, '2026-04-13 16:17:29');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','commissary','staff') NOT NULL DEFAULT 'staff',
  `status` enum('active','archived') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES
(3, 'Jerald', '$2y$10$x25iNHwD4JfHYra7rviSDONqSe.5t9YSIyCN73/pi6Q1qbGJ3iOme', 'commissary', 'active', '2026-04-13 15:02:20', '2026-04-13 15:02:20'),
(4, 'Nel', '$2y$10$40IFj/vgrezYGu248DTM0eVjl3ygJuMKxQKnv9Nj74to2FLwrXXXG', 'staff', 'active', '2026-04-13 15:02:54', '2026-04-13 15:02:54');

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
(4, NULL, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-13 14:59:39'),
(5, NULL, 'system', 'default_admin_created', 'Created default admin user', NULL, NULL, '2026-04-13 15:01:24'),
(6, NULL, 'admin', 'login', 'User logged in', NULL, NULL, '2026-04-13 15:01:24'),
(7, NULL, 'admin', 'create_user', 'Created POS user Jerald with role commissary', NULL, NULL, '2026-04-13 15:02:20'),
(8, NULL, 'admin', 'logout', 'User logged out', NULL, NULL, '2026-04-13 15:02:24'),
(9, 3, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-13 15:02:26'),
(10, 3, 'Jerald', 'create_user', 'Created POS user Nel with role staff', NULL, NULL, '2026-04-13 15:02:54'),
(11, 4, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-13 15:03:43'),
(12, 4, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-13 15:04:21'),
(13, 3, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-13 15:04:23'),
(14, 3, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-13 15:04:32'),
(15, 4, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-13 15:04:34'),
(16, 4, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-13 15:07:29'),
(17, 4, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-13 15:07:32'),
(18, 4, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-13 15:07:37'),
(19, 3, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-13 15:07:39'),
(20, 3, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-13 15:09:47'),
(21, 4, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-13 15:09:50'),
(22, 4, 'Nel', 'create_order', 'Created order #1', NULL, NULL, '2026-04-13 15:18:33'),
(23, 4, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-13 15:30:31'),
(24, 3, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-13 15:30:33'),
(25, 3, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-13 15:35:01'),
(26, 4, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-13 15:35:03'),
(27, 4, 'Nel', 'create_order', 'Created order #8', NULL, NULL, '2026-04-13 15:41:22'),
(28, 4, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-13 15:46:21'),
(29, 3, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-13 15:46:23'),
(30, 3, 'Jerald', 'create_ingredient', 'Created ingredient #9', NULL, NULL, '2026-04-13 15:58:45'),
(31, 3, 'Jerald', 'create_ingredient', 'Created ingredient #10', NULL, NULL, '2026-04-13 16:12:20'),
(32, 3, 'Jerald', 'logout', 'User logged out', NULL, NULL, '2026-04-13 16:16:55'),
(33, 4, 'Nel', 'login', 'User logged in', NULL, NULL, '2026-04-13 16:16:57'),
(34, 4, 'Nel', 'create_order', 'Created order #9', NULL, NULL, '2026-04-13 16:17:29'),
(35, 4, 'Nel', 'logout', 'User logged out', NULL, NULL, '2026-04-13 16:43:06'),
(36, 3, 'Jerald', 'login', 'User logged in', NULL, NULL, '2026-04-13 16:43:08');

--
-- Indexes for dumped tables
--

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
  ADD KEY `idx_created_at` (`created_at`);

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
-- AUTO_INCREMENT for table `daily_sales_summary`
--
ALTER TABLE `daily_sales_summary`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `menu_item_history`
--
ALTER TABLE `menu_item_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `payment_reconciliation`
--
ALTER TABLE `payment_reconciliation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transaction_logs`
--
ALTER TABLE `transaction_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

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
-- Constraints for table `transaction_logs`
--
ALTER TABLE `transaction_logs`
  ADD CONSTRAINT `fk_transaction_logs_order` FOREIGN KEY (`related_order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_transaction_logs_parent` FOREIGN KEY (`related_transaction_log_id`) REFERENCES `transaction_logs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_transaction_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD CONSTRAINT `fk_user_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
