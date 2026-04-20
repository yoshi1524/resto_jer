-- ============================================
-- Comprehensive Transaction Tracking Database
-- For Restaurant POS System
-- Records: Users, Menu, Inventory, Orders, Transactions, Audit Logs
-- ============================================

-- Drop existing database if needed (uncomment to use)
-- DROP DATABASE IF EXISTS `resto_pos`;

CREATE DATABASE IF NOT EXISTS `resto_pos` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `resto_pos`;

-- ============================================
-- USER MANAGEMENT TABLES
-- ============================================

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin','commissary','staff') NOT NULL DEFAULT 'staff',
    `status` ENUM('active','archived') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_username` (`username`),
    INDEX `idx_role` (`role`),
    INDEX `idx_status` (`status`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- MENU MANAGEMENT TABLES
-- ============================================

CREATE TABLE IF NOT EXISTS `menu_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `emoji` VARCHAR(10) NULL,
    `name` VARCHAR(100) NOT NULL,
    `category` VARCHAR(100) NOT NULL,
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `cost` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `stock` INT NOT NULL DEFAULT 0,
    `min_stock` INT NOT NULL DEFAULT 5,
    `status` ENUM('available','unavailable') NOT NULL DEFAULT 'available',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_name` (`name`),
    INDEX `idx_category` (`category`),
    INDEX `idx_status` (`status`),
    INDEX `idx_stock` (`stock`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_price` (`price`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `menu_item_history` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `menu_item_id` INT NOT NULL,
    `action` ENUM('created','updated','deleted','price_changed','stock_adjusted') NOT NULL,
    `old_values` JSON NULL,
    `new_values` JSON NULL,
    `changed_by` INT NULL,
    `changed_by_username` VARCHAR(50) NULL,
    `changed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_menu_item_id` (`menu_item_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_changed_at` (`changed_at`),
    CONSTRAINT `fk_menu_item_history_item` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_menu_item_history_user` FOREIGN KEY (`changed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INVENTORY MANAGEMENT TABLES
-- ============================================

CREATE TABLE IF NOT EXISTS `ingredients` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `unit` VARCHAR(30) NOT NULL,
    `stock` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `min_stock` DECIMAL(10,2) NOT NULL DEFAULT 5,
    `unit_price` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `status` ENUM('available','unavailable') NOT NULL DEFAULT 'available',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_name` (`name`),
    INDEX `idx_status` (`status`),
    INDEX `idx_stock` (`stock`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `inventory_movements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `type` ENUM('in','out','adjustment','return','damage','expired') NOT NULL,
    `ingredient_id` INT NULL,
    `menu_item_id` INT NULL,
    `quantity_change` DECIMAL(10,2) NOT NULL,
    `old_quantity` DECIMAL(10,2) NOT NULL,
    `new_quantity` DECIMAL(10,2) NOT NULL,
    `reason` VARCHAR(255) NULL,
    `reference_id` INT NULL,
    `reference_type` ENUM('order','restock','adjustment','sales','waste') NULL,
    `recorded_by` INT NULL,
    `recorded_by_username` VARCHAR(50) NULL,
    `recorded_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_type` (`type`),
    INDEX `idx_ingredient_id` (`ingredient_id`),
    INDEX `idx_menu_item_id` (`menu_item_id`),
    INDEX `idx_reference` (`reference_id`,`reference_type`),
    INDEX `idx_recorded_at` (`recorded_at`),
    INDEX `idx_recorded_by` (`recorded_by`),
    CONSTRAINT `fk_inventory_movements_ingredient` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_inventory_movements_menu_item` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_inventory_movements_user` FOREIGN KEY (`recorded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ORDER & SALES TRANSACTION TABLES
-- ============================================

CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_number` VARCHAR(50) NOT NULL UNIQUE,
    `user_id` INT NULL,
    `username` VARCHAR(50) NULL,
    `customer_name` VARCHAR(100) NULL,
    `table_name` VARCHAR(100) NOT NULL,
    `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `discount_percent` DECIMAL(5,2) NOT NULL DEFAULT 0,
    `tax_amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `total` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `payment_method` ENUM('cash','e_wallet','card','check','other') NOT NULL DEFAULT 'cash',
    `payment_reference` VARCHAR(200) NULL,
    `payment_details` JSON NULL,
    `cash_received` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `change_amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `status` ENUM('completed','pending','cancelled','refunded') NOT NULL DEFAULT 'completed',
    `notes` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `completed_at` DATETIME NULL,
    `modified_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_order_number` (`order_number`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_payment_method` (`payment_method`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_completed_at` (`completed_at`),
    INDEX `idx_total` (`total`),
    CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `menu_item_id` INT NULL,
    `item_name` VARCHAR(100) NOT NULL,
    `emoji` VARCHAR(10) NULL,
    `quantity` INT NOT NULL,
    `unit_price` DECIMAL(10,2) NOT NULL,
    `item_total` DECIMAL(10,2) NOT NULL,
    `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `notes` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_order_id` (`order_id`),
    INDEX `idx_menu_item_id` (`menu_item_id`),
    CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_order_items_menu` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TRANSACTION AUDIT LOG
-- Every transaction, menu change, and operation is recorded here
-- ============================================

CREATE TABLE IF NOT EXISTS `transaction_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `transaction_type` ENUM('order','menu_change','inventory_movement','user_action','payment','discount','refund','menu_item_created','menu_item_updated','menu_item_deleted','stock_adjustment','ingredient_change') NOT NULL,
    `user_id` INT NULL,
    `username` VARCHAR(50) NULL,
    `entity_type` ENUM('order','menu_item','ingredient','user','payment') NOT NULL,
    `entity_id` INT NULL,
    `entity_name` VARCHAR(255) NULL,
    `action` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `old_value` JSON NULL,
    `new_value` JSON NULL,
    `status` ENUM('success','failed','pending') NOT NULL DEFAULT 'success',
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,
    `transaction_id` VARCHAR(100) NULL,
    `related_order_id` INT NULL,
    `related_transaction_log_id` INT NULL,
    `logged_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_transaction_type` (`transaction_type`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_entity` (`entity_type`,`entity_id`),
    INDEX `idx_logged_at` (`logged_at`),
    INDEX `idx_status` (`status`),
    INDEX `idx_related_order` (`related_order_id`),
    INDEX `idx_action` (`action`),
    CONSTRAINT `fk_transaction_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_transaction_logs_order` FOREIGN KEY (`related_order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_transaction_logs_parent` FOREIGN KEY (`related_transaction_log_id`) REFERENCES `transaction_logs`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `username` VARCHAR(50) NULL,
    `action` VARCHAR(100) NOT NULL,
    `detail` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `session_id` VARCHAR(100) NULL,
    `action_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_action_time` (`action_time`),
    CONSTRAINT `fk_user_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- FINANCIAL SUMMARY TABLES
-- ============================================

CREATE TABLE IF NOT EXISTS `daily_sales_summary` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sale_date` DATE NOT NULL UNIQUE,
    `total_revenue` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `total_discount` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `total_tax` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `total_items_sold` INT NOT NULL DEFAULT 0,
    `total_orders` INT NOT NULL DEFAULT 0,
    `cash_sales` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `card_sales` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `ewallet_sales` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `other_sales` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `cash_received` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `change_given` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `average_order_value` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `generated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_sale_date` (`sale_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payment_reconciliation` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `reconciliation_date` DATE NOT NULL,
    `payment_method` ENUM('cash','e_wallet','card','check','other') NOT NULL,
    `expected_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `actual_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `variance` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `variance_percent` DECIMAL(5,2) NOT NULL DEFAULT 0,
    `status` ENUM('balanced','variance','pending_review') NOT NULL DEFAULT 'pending_review',
    `reconciled_by` INT NULL,
    `notes` TEXT NULL,
    `reconciled_at` DATETIME NULL,
    `recorded_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_date_method` (`reconciliation_date`, `payment_method`),
    INDEX `idx_reconciliation_date` (`reconciliation_date`),
    INDEX `idx_status` (`status`),
    CONSTRAINT `fk_payment_reconciliation_user` FOREIGN KEY (`reconciled_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INITIAL DATA
-- ============================================

-- Insert default admin user (password: admin123)
INSERT INTO `users` (`username`, `password`, `role`, `status`) VALUES 
('admin', '$2y$10$YourHashedPasswordHere', 'admin', 'active')
ON DUPLICATE KEY UPDATE id=id;

-- Insert sample menu items
INSERT INTO `menu_items` (`emoji`, `name`, `category`, `price`, `cost`, `stock`, `min_stock`, `status`) VALUES 
('🍗', 'Fried Chicken', 'Country Classics', 185.00, 75.00, 20, 5, 'available'),
('🍝', 'Spaghetti', 'Sizzling Favorites', 150.00, 50.00, 15, 5, 'available'),
('🍣', 'Salmon Sashimi', 'Heart Lover''s Delight', 320.00, 150.00, 8, 3, 'available'),
('🥗', 'Caesar Salad', 'Heart Lover''s Delight', 120.00, 40.00, 12, 5, 'available'),
('🍲', 'Sinigang na Baboy', 'Country Classics', 175.00, 70.00, 10, 4, 'available'),
('🍛', 'Beef Caldereta', 'Sizzling Favorites', 210.00, 85.00, 6, 3, 'available'),
('🧁', 'Chocolate Lava Cake', 'Desserts', 135.00, 45.00, 14, 5, 'available'),
('🥤', 'Iced Tea', 'Beverages', 60.00, 15.00, 30, 10, 'available'),
('☕', 'Brewed Coffee', 'Beverages', 80.00, 20.00, 25, 10, 'available'),
('🍟', 'French Fries', 'Extras', 95.00, 30.00, 3, 5, 'available'),
('🥘', 'Kare-Kare', 'Country Classics', 245.00, 100.00, 0, 3, 'available'),
('🍨', 'Halo-Halo', 'Desserts', 110.00, 35.00, 18, 5, 'available')
ON DUPLICATE KEY UPDATE stock=VALUES(stock);

-- Insert sample ingredients
INSERT INTO `ingredients` (`name`, `unit`, `stock`, `min_stock`, `unit_price`, `status`) VALUES 
('Chicken', 'kg', 50.00, 10.00, 150.00, 'available'),
('Rice', 'kg', 100.00, 20.00, 50.00, 'available'),
('Tomato', 'pcs', 30.00, 10.00, 5.00, 'available'),
('Onion', 'kg', 20.00, 5.00, 40.00, 'available'),
('Garlic', 'kg', 15.00, 3.00, 80.00, 'available'),
('Soy Sauce', 'L', 25.00, 5.00, 120.00, 'available'),
('Vinegar', 'L', 20.00, 5.00, 100.00, 'available'),
('Fish Sauce', 'L', 10.00, 3.00, 150.00, 'available')
ON DUPLICATE KEY UPDATE stock=VALUES(stock);

-- ============================================
-- SUMMARY
-- ============================================
-- This schema includes:
-- 1. User Management: users, user_logs
-- 2. Menu Management: menu_items, menu_item_history (tracks all changes)
-- 3. Inventory: ingredients, inventory_movements (records all movements with reasons)
-- 4. Orders & Sales: orders, order_items (with full transaction details)
-- 5. Comprehensive Audit Trail: transaction_logs (captures everything)
-- 6. Financial: daily_sales_summary, payment_reconciliation
-- 
-- Every action is recorded in transaction_logs:
-- - New menu items created
-- - Menu changes (price, stock)
-- - All inventory movements with reason
-- - All orders and payments
-- - User actions
-- 
-- This allows complete tracing and auditing of the entire system
-- ============================================
