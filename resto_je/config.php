<?php
session_start();

// ============ CONSTANTS ============
const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'resto_pos';

// ============ DATABASE CONNECTION ============
function dbConnect() {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    $conn->set_charset('utf8mb4');
    $conn->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->select_db(DB_NAME);
    return $conn;
}

function ensureSchema(mysqli $conn) {
    // Users table
    $conn->query("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin','manager','staff') NOT NULL DEFAULT 'staff',
        status ENUM('active','archived') NOT NULL DEFAULT 'active',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX(username), INDEX(role), INDEX(status), INDEX(created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Menu items table
    $conn->query("CREATE TABLE IF NOT EXISTS menu_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        emoji VARCHAR(10) NULL,
        name VARCHAR(100) NOT NULL,
        category VARCHAR(100) NOT NULL,
        price DECIMAL(10,2) NOT NULL DEFAULT 0,
        cost DECIMAL(10,2) NOT NULL DEFAULT 0,
        stock INT NOT NULL DEFAULT 0,
        min_stock INT NOT NULL DEFAULT 5,
        status ENUM('available','unavailable') NOT NULL DEFAULT 'available',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX(name), INDEX(category), INDEX(status), INDEX(stock), INDEX(created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Menu item history for audit trail
    $conn->query("CREATE TABLE IF NOT EXISTS menu_item_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        menu_item_id INT NOT NULL,
        action ENUM('created','updated','deleted','price_changed','stock_adjusted') NOT NULL,
        old_values JSON NULL,
        new_values JSON NULL,
        changed_by INT NULL,
        changed_by_username VARCHAR(50) NULL,
        changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX(menu_item_id), INDEX(action), INDEX(changed_at),
        CONSTRAINT fk_menu_item_history_item FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE,
        CONSTRAINT fk_menu_item_history_user FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Ingredients table
    $conn->query("CREATE TABLE IF NOT EXISTS ingredients (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        unit VARCHAR(30) NOT NULL,
        stock DECIMAL(10,2) NOT NULL DEFAULT 0,
        min_stock DECIMAL(10,2) NOT NULL DEFAULT 5,
        unit_price DECIMAL(10,2) NOT NULL DEFAULT 0,
        status ENUM('available','unavailable') NOT NULL DEFAULT 'available',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX(name), INDEX(status), INDEX(stock), INDEX(created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Inventory movements table - records EVERY stock change
    $conn->query("CREATE TABLE IF NOT EXISTS inventory_movements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type ENUM('in','out','adjustment','return','damage','expired') NOT NULL,
        ingredient_id INT NULL,
        menu_item_id INT NULL,
        quantity_change DECIMAL(10,2) NOT NULL,
        old_quantity DECIMAL(10,2) NOT NULL,
        new_quantity DECIMAL(10,2) NOT NULL,
        reason VARCHAR(255) NULL,
        reference_id INT NULL,
        reference_type ENUM('order','restock','adjustment','sales','waste') NULL,
        recorded_by INT NULL,
        recorded_by_username VARCHAR(50) NULL,
        recorded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX(type), INDEX(ingredient_id), INDEX(menu_item_id), INDEX(reference_id), INDEX(recorded_at), INDEX(recorded_by),
        CONSTRAINT fk_inventory_movements_ingredient FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE SET NULL,
        CONSTRAINT fk_inventory_movements_menu_item FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE SET NULL,
        CONSTRAINT fk_inventory_movements_user FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Orders table
    $conn->query("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_number VARCHAR(50) NOT NULL UNIQUE,
        user_id INT NULL,
        username VARCHAR(50) NULL,
        customer_name VARCHAR(100) NULL,
        table_name VARCHAR(100) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
        discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
        discount_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
        discount_type ENUM('regular','pwd','senior') NOT NULL DEFAULT 'regular',
        discount_label VARCHAR(100) NOT NULL DEFAULT 'Regular Discount',
        tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
        total DECIMAL(10,2) NOT NULL DEFAULT 0,
        payment_method ENUM('cash','e_wallet','card','check','online','other') NOT NULL DEFAULT 'cash',
        payment_reference VARCHAR(200) NULL,
        payment_details JSON NULL,
        cash_received DECIMAL(10,2) NOT NULL DEFAULT 0,
        change_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
        status ENUM('completed','pending','cancelled','refunded') NOT NULL DEFAULT 'completed',
        notes TEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        completed_at DATETIME NULL,
        modified_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX(order_number), INDEX(user_id), INDEX(status), INDEX(payment_method), INDEX(created_at), INDEX(completed_at), INDEX(total),
        CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $conn->query("ALTER TABLE orders
        MODIFY COLUMN payment_method ENUM('cash','e_wallet','card','check','online','other') NOT NULL DEFAULT 'cash',
        ADD COLUMN IF NOT EXISTS order_number VARCHAR(50) NOT NULL UNIQUE,
        ADD COLUMN IF NOT EXISTS discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
        ADD COLUMN IF NOT EXISTS discount_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
        ADD COLUMN IF NOT EXISTS discount_type ENUM('regular','pwd','senior') NOT NULL DEFAULT 'regular',
        ADD COLUMN IF NOT EXISTS discount_label VARCHAR(100) NOT NULL DEFAULT 'Regular Discount',
        ADD COLUMN IF NOT EXISTS payment_details JSON NULL,
        ADD COLUMN IF NOT EXISTS cash_received DECIMAL(10,2) NOT NULL DEFAULT 0,
        ADD COLUMN IF NOT EXISTS change_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
        ADD COLUMN IF NOT EXISTS completed_at DATETIME NULL");

    // Order items table
    $conn->query("CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        menu_item_id INT NULL,
        item_name VARCHAR(100) NOT NULL,
        emoji VARCHAR(10) NULL,
        quantity INT NOT NULL,
        unit_price DECIMAL(10,2) NOT NULL,
        item_total DECIMAL(10,2) NOT NULL,
        discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
        notes TEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX(order_id), INDEX(menu_item_id),
        CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        CONSTRAINT fk_order_items_menu FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $conn->query("ALTER TABLE order_items
        ADD COLUMN IF NOT EXISTS item_total DECIMAL(10,2) NOT NULL,
        ADD COLUMN IF NOT EXISTS discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0");

    // User logs table
    $conn->query("CREATE TABLE IF NOT EXISTS user_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        username VARCHAR(50) NULL,
        action VARCHAR(100) NOT NULL,
        detail TEXT NULL,
        ip_address VARCHAR(45) NULL,
        session_id VARCHAR(100) NULL,
        action_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX(user_id), INDEX(action), INDEX(action_time),
        CONSTRAINT fk_user_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Comprehensive transaction logs table - records EVERYTHING
    $conn->query("CREATE TABLE IF NOT EXISTS transaction_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        transaction_type ENUM('order','menu_change','inventory_movement','user_action','payment','discount','refund','menu_item_created','menu_item_updated','menu_item_deleted','stock_adjustment','ingredient_change') NOT NULL,
        user_id INT NULL,
        username VARCHAR(50) NULL,
        entity_type ENUM('order','menu_item','ingredient','user','payment') NOT NULL,
        entity_id INT NULL,
        entity_name VARCHAR(255) NULL,
        action VARCHAR(100) NOT NULL,
        description TEXT NULL,
        old_value JSON NULL,
        new_value JSON NULL,
        status ENUM('success','failed','pending') NOT NULL DEFAULT 'success',
        ip_address VARCHAR(45) NULL,
        user_agent VARCHAR(255) NULL,
        transaction_id VARCHAR(100) NULL,
        related_order_id INT NULL,
        related_transaction_log_id INT NULL,
        logged_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX(transaction_type), INDEX(user_id), INDEX(entity_type), INDEX(entity_id), INDEX(logged_at), INDEX(status), INDEX(related_order_id), INDEX(action),
        CONSTRAINT fk_transaction_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        CONSTRAINT fk_transaction_logs_order FOREIGN KEY (related_order_id) REFERENCES orders(id) ON DELETE SET NULL,
        CONSTRAINT fk_transaction_logs_parent FOREIGN KEY (related_transaction_log_id) REFERENCES transaction_logs(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Daily sales summary table
    $conn->query("CREATE TABLE IF NOT EXISTS daily_sales_summary (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sale_date DATE NOT NULL UNIQUE,
        total_revenue DECIMAL(12,2) NOT NULL DEFAULT 0,
        total_discount DECIMAL(12,2) NOT NULL DEFAULT 0,
        total_tax DECIMAL(12,2) NOT NULL DEFAULT 0,
        total_items_sold INT NOT NULL DEFAULT 0,
        total_orders INT NOT NULL DEFAULT 0,
        cash_sales DECIMAL(12,2) NOT NULL DEFAULT 0,
        card_sales DECIMAL(12,2) NOT NULL DEFAULT 0,
        ewallet_sales DECIMAL(12,2) NOT NULL DEFAULT 0,
        other_sales DECIMAL(12,2) NOT NULL DEFAULT 0,
        cash_received DECIMAL(12,2) NOT NULL DEFAULT 0,
        change_given DECIMAL(12,2) NOT NULL DEFAULT 0,
        average_order_value DECIMAL(10,2) NOT NULL DEFAULT 0,
        generated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX(sale_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Payment reconciliation table
    $conn->query("CREATE TABLE IF NOT EXISTS payment_reconciliation (
        id INT AUTO_INCREMENT PRIMARY KEY,
        reconciliation_date DATE NOT NULL,
        payment_method ENUM('cash','e_wallet','card','check','other') NOT NULL,
        expected_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
        actual_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
        variance DECIMAL(12,2) NOT NULL DEFAULT 0,
        variance_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
        status ENUM('balanced','variance','pending_review') NOT NULL DEFAULT 'pending_review',
        reconciled_by INT NULL,
        notes TEXT NULL,
        reconciled_at DATETIME NULL,
        recorded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_date_method (reconciliation_date, payment_method),
        INDEX(reconciliation_date), INDEX(status),
        CONSTRAINT fk_payment_reconciliation_user FOREIGN KEY (reconciled_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Check if admin user exists, create if not
    $result = $conn->query("SELECT COUNT(*) AS count FROM users");
    $row = $result->fetch_assoc();
    if (isset($row['count']) && (int)$row['count'] === 0) {
        $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->query("INSERT INTO users (username, password, role, status) VALUES ('admin', '{$conn->real_escape_string($passwordHash)}', 'admin', 'active')");
        logAction($conn, null, 'system', 'default_admin_created', 'Created default admin user');
        logTransaction($conn, null, 'system', 'user', null, 'admin', 'create', 'Default admin user created', null, array('username' => 'admin', 'role' => 'admin'));
    }
}

// ============ SESSION & AUTH FUNCTIONS ============
function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

function requireLogin() {
    if (!currentUser()) {
        header('Location: login.php');
        exit;
    }
}

function requireRole(string ...$allowedRoles) {
    $user = currentUser();
    if (!$user || !in_array($user['role'], $allowedRoles, true)) {
        header('Location: login.php');
        exit;
    }
}

function canManageUsers(): bool {
    $user = currentUser();
    return $user && in_array($user['role'], ['admin', 'manager'], true);
}

// ============ USER MANAGEMENT ============
function getUsers(mysqli $conn): array {
    $result = $conn->query("SELECT id, username, role, status, created_at FROM users ORDER BY created_at DESC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function createUser(mysqli $conn, string $username, string $password, string $role): bool {
    $username = trim($username);
    if ($username === '' || $password === '') {
        return false;
    }
    if (!in_array($role, ['admin', 'manager', 'staff'], true)) {
        $role = 'staff';
    }
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password, role, status) VALUES (?, ?, ?, 'active')");
    $stmt->bind_param('sss', $username, $passwordHash, $role);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

function getLogs(mysqli $conn, int $limit = 50): array {
    $stmt = $conn->prepare("SELECT id, user_id, username, action, detail, action_time FROM user_logs ORDER BY action_time DESC LIMIT ?");
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function logAction(mysqli $conn, ?int $userId, ?string $username, string $action, ?string $detail = null) {
    // Verify user_id exists before inserting, to avoid foreign key constraint errors
    if ($userId !== null) {
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
        $checkStmt->bind_param('i', $userId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        if ($checkResult->fetch_assoc() === null) {
            // User doesn't exist, set user_id to NULL to avoid constraint violation
            $userId = null;
        }
        $checkStmt->close();
    }
    
    $stmt = $conn->prepare("INSERT INTO user_logs (user_id, username, action, detail) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('isss', $userId, $username, $action, $detail);
    $stmt->execute();
    $stmt->close();
}

function logTransaction(mysqli $conn, ?int $userId, ?string $username, string $entityType, ?int $entityId, ?string $entityName, string $action, ?string $description = null, ?array $oldValue = null, ?array $newValue = null, string $status = 'success', ?string $transactionType = null, ?int $relatedOrderId = null) {
    $transactionType = $transactionType ?? 'user_action';
    $oldVal = $oldValue ? json_encode($oldValue) : null;
    $newVal = $newValue ? json_encode($newValue) : null;
    
    // Verify user_id and related_order_id exist before inserting
    if ($userId !== null) {
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
        $checkStmt->bind_param('i', $userId);
        $checkStmt->execute();
        if ($checkStmt->get_result()->fetch_assoc() === null) {
            $userId = null;
        }
        $checkStmt->close();
    }
    
    if ($relatedOrderId !== null) {
        $checkStmt = $conn->prepare("SELECT id FROM orders WHERE id = ? LIMIT 1");
        $checkStmt->bind_param('i', $relatedOrderId);
        $checkStmt->execute();
        if ($checkStmt->get_result()->fetch_assoc() === null) {
            $relatedOrderId = null;
        }
        $checkStmt->close();
    }
    
    $stmt = $conn->prepare(
        "INSERT INTO transaction_logs (transaction_type, user_id, username, entity_type, entity_id, entity_name, action, description, old_value, new_value, status, related_order_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        'sississssssi',
        $transactionType,
        $userId,
        $username,
        $entityType,
        $entityId,
        $entityName,
        $action,
        $description,
        $oldVal,
        $newVal,
        $status,
        $relatedOrderId
    );
    $stmt->execute();
    $stmt->close();
}

function logInventoryMovement(mysqli $conn, string $type, ?int $ingredientId, ?int $menuItemId, float $quantityChange, float $oldQuantity, float $newQuantity, ?string $reason, ?int $referencelId, ?string $referenceType, ?int $userId, ?string $username) {
    // Verify user_id exists before inserting
    if ($userId !== null) {
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
        $checkStmt->bind_param('i', $userId);
        $checkStmt->execute();
        if ($checkStmt->get_result()->fetch_assoc() === null) {
            $userId = null;
        }
        $checkStmt->close();
    }
    
    $stmt = $conn->prepare(
        "INSERT INTO inventory_movements (type, ingredient_id, menu_item_id, quantity_change, old_quantity, new_quantity, reason, reference_id, reference_type, recorded_by, recorded_by_username) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        'siidddsissi',
        $type,
        $ingredientId,
        $menuItemId,
        $quantityChange,
        $oldQuantity,
        $newQuantity,
        $reason,
        $referencelId,
        $referenceType,
        $userId,
        $username
    );
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

function saveOrder(mysqli $conn, ?int $userId, ?string $username, array $orderData): int {
    $tableName = trim($orderData['table'] ?? 'Takeout');
    $customerName = trim($orderData['customer_name'] ?? '');
    $paymentMethod = in_array($orderData['payment_method'] ?? 'cash', ['cash', 'e_wallet', 'card', 'check', 'online', 'other'], true) ? $orderData['payment_method'] : 'cash';
    $paymentReference = trim($orderData['payment_reference'] ?? '');
    $paymentDetailsRaw = trim($orderData['payment_details'] ?? '');
    $paymentDetails = $paymentDetailsRaw !== '' ? json_encode(['details' => $paymentDetailsRaw]) : null;
    $subtotal = max(0.0, floatval($orderData['subtotal'] ?? 0));
    $discountType = strtolower(trim($orderData['customer_type'] ?? 'regular'));
    if (!in_array($discountType, ['regular', 'pwd', 'senior'], true)) {
        $discountType = 'regular';
    }
    $discountPercent = floatval($orderData['discount_percent'] ?? 0);
    $legalDiscountPercent = in_array($discountType, ['pwd', 'senior'], true) ? 20.0 : 0.0;
    $discountPercent = $legalDiscountPercent;
    $discountLabel = trim($orderData['discount_label'] ?? '');
    if ($discountLabel === '') {
        $discountLabel = $discountType === 'pwd'
            ? 'PWD Discount (20%)'
            : ($discountType === 'senior' ? 'Senior Citizen Discount (20%)' : 'Regular Discount');
    }
    $discount = round($subtotal * ($discountPercent / 100), 2);
    $total = max(0.0, round($subtotal - $discount, 2));
    $cashReceived = max(0.0, floatval($orderData['cash'] ?? $orderData['cash_received'] ?? 0));
    $changeAmount = $paymentMethod === 'cash' ? round($cashReceived - $total, 2) : 0.0;
    $orderNumber = 'ORD-' . date('Ymd') . '-' . uniqid();
    $now = date('Y-m-d H:i:s');

    $stmt = $conn->prepare(
        'INSERT INTO orders (order_number, user_id, username, customer_name, table_name, subtotal, discount_amount, discount_percent, discount_type, discount_label, total, payment_method, payment_reference, payment_details, cash_received, change_amount, completed_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param(
        'sisssdddssssssdds',
        $orderNumber,
        $userId,
        $username,
        $customerName,
        $tableName,
        $subtotal,
        $discount,
        $discountPercent,
        $discountType,
        $discountLabel,
        $total,
        $paymentMethod,
        $paymentReference,
        $paymentDetails,
        $cashReceived,
        $changeAmount,
        $now
    );
    $stmt->execute();
    $orderId = $conn->insert_id;
    $stmt->close();

    $itemStmt = $conn->prepare(
        'INSERT INTO order_items (order_id, menu_item_id, item_name, emoji, quantity, unit_price, item_total) VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    foreach ($orderData['items'] as $item) {
        $itemId = null;
        if (isset($item['id']) && is_numeric($item['id'])) {
            $candidateId = intval($item['id']);
            $checkStmt = $conn->prepare('SELECT id FROM menu_items WHERE id = ? LIMIT 1');
            $checkStmt->bind_param('i', $candidateId);
            $checkStmt->execute();
            if ($checkStmt->get_result()->fetch_assoc() !== null) {
                $itemId = $candidateId;
            }
            $checkStmt->close();
        }
        $itemName = trim($item['name'] ?? 'Unknown');
        $itemEmoji = trim($item['emoji'] ?? '');
        $quantity = max(1, intval($item['qty'] ?? $item['quantity'] ?? 1));
        $unitPrice = floatval($item['price'] ?? $item['unit_price'] ?? 0);
        $totalPrice = floatval($item['total'] ?? ($unitPrice * $quantity));
        $itemStmt->bind_param('iissidd', $orderId, $itemId, $itemName, $itemEmoji, $quantity, $unitPrice, $totalPrice);
        $itemStmt->execute();
    }
    $itemStmt->close();

    // Log the transaction in the comprehensive audit trail
    $orderData_json = array(
        'order_number' => $orderNumber,
        'table' => $tableName,
        'customer' => $customerName,
        'subtotal' => $subtotal,
        'discount' => $discount,
        'discount_percent' => $discountPercent,
        'discount_type' => $discountType,
        'discount_label' => $discountLabel,
        'total' => $total,
        'items_count' => count($orderData['items'] ?? []),
        'payment_method' => $paymentMethod
    );
    logTransaction($conn, $userId, $username, 'order', $orderId, $orderNumber, 'create', 'Order created and completed', null, $orderData_json, 'success', 'order', $orderId);

    return $orderId;
}

function saveIngredient(mysqli $conn, ?int $userId, ?string $username, array $ingredientData): int {
    $name = trim($ingredientData['name'] ?? '');
    $unit = trim($ingredientData['unit'] ?? 'kg');
    $stock = max(0.0, floatval($ingredientData['stock'] ?? 0));
    $minStock = max(0.0, floatval($ingredientData['min_stock'] ?? 5));
    $unitPrice = max(0.0, floatval($ingredientData['unit_price'] ?? 0));
    $status = in_array($ingredientData['status'] ?? 'available', ['available', 'unavailable'], true) ? $ingredientData['status'] : 'available';

    if (empty($name)) {
        throw new Exception('Ingredient name is required');
    }

    try {
        $stmt = $conn->prepare(
            'INSERT INTO ingredients (name, unit, stock, min_stock, unit_price, status) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('ssddds', $name, $unit, $stock, $minStock, $unitPrice, $status);
        $stmt->execute();
        $ingredientId = $conn->insert_id;
        $stmt->close();

        // Log inventory movement for initial stock
        if ($stock > 0) {
            logInventoryMovement($conn, 'in', $ingredientId, null, $stock, 0, $stock, 'Initial stock', null, 'ingredient_created', $userId, $username);
        }

        // Log the transaction
        logTransaction($conn, $userId, $username, 'ingredient', $ingredientId, $name, 'create', 'Ingredient created', null, array('name' => $name, 'unit' => $unit, 'stock' => $stock), 'success');

        return $ingredientId;
    } catch (mysqli_sql_exception $ex) {
        if ($ex->getCode() === 1062) { // Duplicate entry
            throw new Exception('Ingredient name already exists');
        }
        throw $ex;
    }
}

function getIngredients(mysqli $conn): array {
    $result = $conn->query('SELECT * FROM ingredients ORDER BY name ASC');
    $ingredients = [];
    while ($row = $result->fetch_assoc()) {
        $ingredients[] = $row;
    }
    return $ingredients;
}

function updateIngredientStock(mysqli $conn, int $ingredientId, float $newStock, ?int $userId, ?string $username, ?string $reason = null): bool {
    // Get current stock
    $stmt = $conn->prepare('SELECT stock FROM ingredients WHERE id = ?');
    $stmt->bind_param('i', $ingredientId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row) {
        throw new Exception('Ingredient not found');
    }

    $oldStock = floatval($row['stock']);
    $quantityChange = $newStock - $oldStock;

    // Update stock
    $stmt = $conn->prepare('UPDATE ingredients SET stock = ? WHERE id = ?');
    $stmt->bind_param('di', $newStock, $ingredientId);
    $stmt->execute();
    $stmt->close();

    // Log inventory movement
    $type = $quantityChange > 0 ? 'in' : 'out';
    logInventoryMovement($conn, $type, $ingredientId, null, abs($quantityChange), $oldStock, $newStock, $reason ?: 'Stock adjustment', null, 'stock_adjustment', $userId, $username);

    return true;
}

function performDailyReconciliation(mysqli $conn, ?int $userId, ?string $username): int {
    $reconciliationDate = date('Y-m-d');
    $stmt = $conn->prepare(
        'SELECT payment_method, SUM(total) AS expected_amount FROM orders WHERE DATE(created_at) = ? GROUP BY payment_method'
    );
    $stmt->bind_param('s', $reconciliationDate);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    $expectedAmounts = [];
    while ($row = $result->fetch_assoc()) {
        $expectedAmounts[$row['payment_method']] = floatval($row['expected_amount']);
    }

    $paymentMethods = ['cash', 'e_wallet', 'card', 'check', 'online', 'other'];
    $upsert = $conn->prepare(
        'INSERT INTO payment_reconciliation (reconciliation_date, payment_method, expected_amount, actual_amount, variance, variance_percent, status, reconciled_by, notes, reconciled_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE expected_amount = VALUES(expected_amount), actual_amount = VALUES(actual_amount), variance = VALUES(variance), variance_percent = VALUES(variance_percent), status = VALUES(status), reconciled_by = VALUES(reconciled_by), notes = VALUES(notes), reconciled_at = NOW()'
    );

    $rowsCreated = 0;
    foreach ($paymentMethods as $method) {
        $expected = $expectedAmounts[$method] ?? 0.0;
        $actual = $expected;
        $variance = 0.0;
        $variancePercent = 0.0;
        $status = 'success';
        $notes = 'Auto reconciliation generated at 22:00';
        $upsert->bind_param('ssdddssis', $reconciliationDate, $method, $expected, $actual, $variance, $variancePercent, $status, $userId, $notes);
        $upsert->execute();
        $rowsCreated += 1;
    }
    $upsert->close();

    return $rowsCreated;
}

// ============ UTILITY FUNCTIONS ============
function sanitize(string $value): string {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function isSafeRedirect(string $url): bool {
    $parsed = parse_url($url);
    if ($parsed === false) {
        return false;
    }
    if (isset($parsed['scheme']) || isset($parsed['host'])) {
        return false;
    }
    if (!isset($parsed['path']) || strpos($parsed['path'], '..') !== false) {
        return false;
    }
    return preg_match('#^[a-zA-Z0-9_\/\.\-]+$#', $parsed['path']);
}

// ============ SHARED CSS ============
function getSharedStyles(): string {
    return <<<'CSS'
    :root {
        --bg: #0f0e0c;
        --surface: #1a1917;
        --surface2: #242320;
        --surface3: #2e2c29;
        --border: #3a3835;
        --accent: #e8a045;
        --accent2: #d4691e;
        --green: #5bbf8a;
        --red: #e05c5c;
        --blue: #5b9fe0;
        --text: #f0ede8;
        --text2: #a09890;
        --text3: #6a6560;
        --radius: 12px;
        --radius-sm: 8px;
        --shadow: 0 4px 24px rgba(0,0,0,0.4);
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; overflow-x: hidden; }
    h1,h2,h3,h4 { font-family: 'Syne', sans-serif; }

    .app { display: flex; height: 100vh; overflow: hidden; }
    .sidebar { width: 220px; min-width: 220px; background: var(--surface); border-right: 1px solid var(--border); display: flex; flex-direction: column; padding: 0; }
    .main { flex: 1; overflow: hidden; display: flex; flex-direction: column; }

    .logo { padding: 24px 20px 20px; border-bottom: 1px solid var(--border); }
    .logo-text { font-family: 'Syne', sans-serif; font-size: 20px; font-weight: 800; color: var(--accent); letter-spacing: -0.5px; }
    .logo-sub { font-size: 11px; color: var(--text3); letter-spacing: 1.5px; text-transform: uppercase; margin-top: 2px; }
    .nav { flex: 1; padding: 12px 0; }
    .nav-item { display: flex; align-items: center; gap: 12px; padding: 12px 20px; cursor: pointer; color: var(--text2); font-size: 14px; font-weight: 500; transition: all .2s; border-left: 3px solid transparent; }
    .nav-item:hover { color: var(--text); background: var(--surface2); }
    .nav-item.active { color: var(--accent); background: rgba(232,160,69,0.08); border-left-color: var(--accent); }
    .nav-item svg { width: 18px; height: 18px; flex-shrink: 0; }
    .sidebar-footer { padding: 16px 20px; border-top: 1px solid var(--border); }
    .clock { font-size: 22px; font-family: 'Syne', sans-serif; font-weight: 700; color: var(--text); }
    .date-txt { font-size: 12px; color: var(--text3); margin-top: 2px; }

    .topbar { padding: 16px 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; background: var(--surface); flex-shrink: 0; }
    .topbar-title { font-family: 'Syne', sans-serif; font-size: 20px; font-weight: 700; }
    .topbar-actions { display: flex; gap: 10px; align-items: center; }
    .badge { background: var(--accent); color: #000; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 20px; }

    .btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; border-radius: var(--radius-sm); font-size: 13px; font-weight: 500; cursor: pointer; border: none; transition: all .2s; font-family: 'DM Sans', sans-serif; }
    .btn-accent { background: var(--accent); color: #000; }
    .btn-accent:hover { background: #f0b055; transform: translateY(-1px); }
    .btn-ghost { background: transparent; color: var(--text2); border: 1px solid var(--border); }
    .btn-ghost:hover { color: var(--text); border-color: var(--text3); }
    .btn-danger { background: rgba(224,92,92,0.15); color: var(--red); border: 1px solid rgba(224,92,92,0.3); }
    .btn-danger:hover { background: rgba(224,92,92,0.25); }
    .btn-green { background: rgba(91,191,138,0.15); color: var(--green); border: 1px solid rgba(91,191,138,0.3); }
    .btn-green:hover { background: rgba(91,191,138,0.25); }
    .btn-sm { padding: 6px 12px; font-size: 12px; }
    .btn-icon { padding: 8px; border-radius: var(--radius-sm); background: var(--surface2); color: var(--text2); border: 1px solid var(--border); cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: all .2s; }
    .btn-icon:hover { color: var(--text); border-color: var(--text3); }

    .page { display: none; flex: 1; overflow: auto; padding: 24px; flex-direction: column; gap: 20px; }
    .page.active { display: flex; }

    .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 20px; }
    .card-title { font-family: 'Syne', sans-serif; font-size: 14px; font-weight: 700; color: var(--text2); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 16px; }

    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
    .grid-4 { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; }

    .stat-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 20px; position: relative; overflow: hidden; }
    .stat-card::before { content:''; position:absolute; top:0; right:0; width:80px; height:80px; border-radius: 0 0 0 80px; opacity: 0.07; }
    .stat-card.green::before { background: var(--green); }
    .stat-card.accent::before { background: var(--accent); }
    .stat-card.blue::before { background: var(--blue); }
    .stat-card.red::before { background: var(--red); }
    .stat-label { font-size: 12px; color: var(--text3); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
    .stat-value { font-family: 'Syne', sans-serif; font-size: 28px; font-weight: 800; }
    .stat-value.green { color: var(--green); }
    .stat-value.accent { color: var(--accent); }
    .stat-value.blue { color: var(--blue); }
    .stat-sub { font-size: 12px; color: var(--text3); margin-top: 4px; }

    .pos-layout { display: flex; gap: 20px; flex: 1; overflow: hidden; }
    .menu-panel { flex: 1; display: flex; flex-direction: column; gap: 16px; overflow: hidden; }
    .cart-panel { width: 340px; min-width: 340px; display: flex; flex-direction: column; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; }

    .category-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
    .cat-tab { padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 500; cursor: pointer; background: var(--surface2); color: var(--text2); border: 1px solid var(--border); transition: all .2s; }
    .cat-tab.active, .cat-tab:hover { background: var(--accent); color: #000; border-color: var(--accent); }

    .menu-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px,1fr)); gap: 12px; overflow-y: auto; flex: 1; padding-bottom: 4px; }
    .menu-item { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 16px; cursor: pointer; transition: all .2s; position: relative; }
    .menu-item:hover { border-color: var(--accent); transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.3); }
    .menu-item.unavailable { opacity: 0.4; cursor: not-allowed; }
    .menu-item.unavailable:hover { transform: none; border-color: var(--border); }
    .menu-emoji { font-size: 32px; margin-bottom: 8px; display: block; }
    .menu-name { font-family: 'Syne', sans-serif; font-size: 14px; font-weight: 600; margin-bottom: 4px; }
    .menu-price { font-size: 15px; font-weight: 500; color: var(--accent); }
    .menu-cat-badge { font-size: 10px; color: var(--text3); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
    .menu-stock { font-size: 11px; color: var(--text3); margin-top: 4px; }
    .menu-stock.low { color: #e09a45; }
    .menu-stock.out { color: var(--red); }

    .cart-header { padding: 16px 20px; border-bottom: 1px solid var(--border); }
    .cart-header-row { display: flex; align-items: center; justify-content: space-between; }
    .cart-title { font-family: 'Syne', sans-serif; font-size: 16px; font-weight: 700; }
    .table-selector { display: flex; align-items: center; gap: 8px; margin-top: 10px; }
    .table-selector label { font-size: 12px; color: var(--text3); }
    .table-selector select { background: var(--surface2); border: 1px solid var(--border); color: var(--text); padding: 5px 10px; border-radius: var(--radius-sm); font-size: 13px; font-family: 'DM Sans', sans-serif; }
    .cart-items { flex: 1; overflow-y: auto; padding: 12px; display: flex; flex-direction: column; gap: 8px; }
    .cart-empty { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--text3); gap: 8px; }
    .cart-empty svg { width: 48px; height: 48px; opacity: 0.3; }
    .cart-item { background: var(--surface2); border-radius: var(--radius-sm); padding: 12px; display: flex; align-items: center; gap: 10px; }
    .cart-item-info { flex: 1; }
    .cart-item-name { font-size: 13px; font-weight: 500; }
    .cart-item-price { font-size: 12px; color: var(--accent); margin-top: 2px; }
    .qty-ctrl { display: flex; align-items: center; gap: 6px; }
    .qty-btn { width: 26px; height: 26px; border-radius: 6px; border: 1px solid var(--border); background: var(--surface3); color: var(--text); cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center; transition: all .15s; }
    .qty-btn:hover { background: var(--accent); color: #000; border-color: var(--accent); }
    .qty-num { font-size: 14px; font-weight: 600; min-width: 20px; text-align: center; }
    .cart-footer { border-top: 1px solid var(--border); padding: 16px 20px; display: flex; flex-direction: column; gap: 10px; }
    .cart-line { display: flex; justify-content: space-between; font-size: 13px; color: var(--text2); }
    .cart-total { display: flex; justify-content: space-between; font-family: 'Syne', sans-serif; font-size: 18px; font-weight: 800; }
    .cart-total span:last-child { color: var(--accent); }
    .discount-row { display: flex; gap: 8px; }
    .input-sm { flex: 1; background: var(--surface2); border: 1px solid var(--border); color: var(--text); padding: 7px 12px; border-radius: var(--radius-sm); font-size: 13px; font-family: 'DM Sans', sans-serif; }
    .input-sm:focus { outline: none; border-color: var(--accent); }

    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    thead th { padding: 10px 12px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: var(--text3); border-bottom: 1px solid var(--border); }
    tbody td { padding: 12px 12px; border-bottom: 1px solid var(--border); color: var(--text2); vertical-align: middle; }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover td { background: var(--surface2); color: var(--text); }
    .tag { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 500; }
    .tag-green { background: rgba(91,191,138,0.15); color: var(--green); }
    .tag-yellow { background: rgba(232,160,69,0.15); color: var(--accent); }
    .tag-red { background: rgba(224,92,92,0.15); color: var(--red); }

    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 100; backdrop-filter: blur(4px); opacity: 0; pointer-events: none; transition: opacity .2s; }
    .modal-overlay.open { opacity: 1; pointer-events: all; }
    .modal { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 28px; width: 480px; max-width: 95vw; box-shadow: var(--shadow); transform: translateY(20px); transition: transform .2s; }
    .modal-overlay.open .modal { transform: translateY(0); }
    .modal-title { font-family: 'Syne', sans-serif; font-size: 18px; font-weight: 800; margin-bottom: 20px; }
    .form-group { margin-bottom: 14px; }
    .form-label { font-size: 12px; color: var(--text3); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; display: block; }
    .form-input { width: 100%; background: var(--surface2); border: 1px solid var(--border); color: var(--text); padding: 10px 14px; border-radius: var(--radius-sm); font-size: 14px; font-family: 'DM Sans', sans-serif; transition: border-color .2s; }
    .form-input:focus { outline: none; border-color: var(--accent); }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .modal-footer { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }

    .receipt-modal { width: 360px; }
    .receipt-header { text-align: center; margin-bottom: 16px; }
    .receipt-logo { font-family: 'Syne', sans-serif; font-size: 22px; font-weight: 800; color: var(--accent); }
    .receipt-divider { border: none; border-top: 1px dashed var(--border); margin: 12px 0; }
    .receipt-row { display: flex; justify-content: space-between; font-size: 13px; padding: 3px 0; }
    .receipt-total { font-family: 'Syne', sans-serif; font-size: 18px; font-weight: 800; display: flex; justify-content: space-between; padding: 4px 0; }
    .receipt-total span:last-child { color: var(--accent); }
    .receipt-payment { text-align: center; margin-top: 14px; padding: 12px; background: var(--surface2); border-radius: var(--radius-sm); }
    .receipt-change { font-family: 'Syne', sans-serif; font-size: 20px; font-weight: 800; color: var(--green); }

    .chart-bar-wrap { display: flex; align-items: flex-end; gap: 6px; height: 120px; padding-top: 8px; }
    .chart-bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px; height: 100%; justify-content: flex-end; }
    .chart-bar { width: 100%; border-radius: 4px 4px 0 0; background: var(--accent); opacity: 0.8; min-height: 4px; transition: height .5s ease; }
    .chart-bar:hover { opacity: 1; }
    .chart-label { font-size: 10px; color: var(--text3); }
    .chart-val { font-size: 10px; color: var(--text2); }

    .search-wrap { position: relative; }
    .search-wrap svg { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: var(--text3); pointer-events: none; }
    .search-input { width: 100%; background: var(--surface2); border: 1px solid var(--border); color: var(--text); padding: 9px 12px 9px 34px; border-radius: var(--radius-sm); font-size: 13px; font-family: 'DM Sans', sans-serif; }
    .search-input:focus { outline: none; border-color: var(--accent); }

    ::-webkit-scrollbar { width: 5px; height: 5px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

    .toast-container { position: fixed; bottom: 24px; right: 24px; display: flex; flex-direction: column; gap: 8px; z-index: 200; }
    .toast { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 12px 16px; font-size: 13px; color: var(--text); box-shadow: var(--shadow); animation: slideIn .3s ease; display: flex; align-items: center; gap: 8px; min-width: 200px; }
    .toast.success { border-left: 3px solid var(--green); }
    .toast.error { border-left: 3px solid var(--red); }
    .toast.info { border-left: 3px solid var(--accent); }
    @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

    .tabs { display: flex; gap: 2px; background: var(--surface2); border-radius: var(--radius-sm); padding: 3px; width: fit-content; }
    .tab { padding: 7px 16px; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; color: var(--text2); transition: all .2s; }
    .tab.active { background: var(--accent); color: #000; }

    .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
    .section-title { font-family: 'Syne', sans-serif; font-size: 18px; font-weight: 700; }
    .empty-state { text-align: center; padding: 40px; color: var(--text3); }
    .color-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
    select.form-input { cursor: pointer; }
    .progress-bar { background: var(--surface3); border-radius: 4px; height: 6px; overflow: hidden; }
    .progress-fill { height: 100%; border-radius: 4px; transition: width .5s ease; }
CSS;
}
