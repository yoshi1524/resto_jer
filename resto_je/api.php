<?php
require 'config.php';
header('Content-Type: application/json; charset=utf-8');

$conn = dbConnect();
ensureSchema($conn);
requireLogin();
$user = currentUser();


$raw = file_get_contents('php://input');
$payload = json_decode($raw, true) ?: [];
$action = $payload['action'] ?? $_POST['action'] ?? null;

// ─────────────────────────────────────────────
// GET MENU ITEMS
// ─────────────────────────────────────────────
if ($action === 'get_menu_items') {
    try {
        $result = $conn->query("SELECT * FROM menu_items WHERE status != 'archived' ORDER BY category, name");
        $items = [];
        while ($row = $result->fetch_assoc()) {
            // Cast types so JS gets numbers, not strings
            $row['id']    = (int)$row['id'];
            $row['price'] = (float)$row['price'];
            $row['stock'] = (int)$row['stock'];
            $items[] = $row;
        }
        echo json_encode(['success' => true, 'items' => $items]);
    } catch (Exception $ex) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to load menu items: ' . $ex->getMessage()]);
    }
    exit;
}

// ─────────────────────────────────────────────
// ADD MENU ITEM 
// ─────────────────────────────────────────────
if ($action === 'add_menu_item') {
    $item = $payload['item'] ?? null;
    if (!is_array($item) || empty($item['name']) || !isset($item['price'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Item name and price are required.']);
        exit;
    }

    $name     = trim($item['name']);
    $price    = floatval($item['price']);
    $category = trim($item['category'] ?? 'Uncategorized');
    $stock    = intval($item['stock'] ?? 0);
    $emoji    = trim($item['emoji'] ?? '🍽');
    $status   = trim($item['status'] ?? 'available');

    if ($price <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Price must be greater than zero.']);
        exit;
    }

    try {
        $stmt = $conn->prepare(
            "INSERT INTO menu_items (name, price, category, stock, emoji, status) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sdisis", $name, $price, $category, $stock, $emoji, $status);
        $stmt->execute();
        $newId = (int)$conn->insert_id;
        logAction($conn, $user['id'], $user['username'], 'add_menu_item', "Added menu item: {$name}");
        echo json_encode(['success' => true, 'item_id' => $newId]);
    } catch (Exception $ex) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $ex->getMessage()]);
    }
    exit;
}

// ─────────────────────────────────────────────
// UPDATE MENU ITEM
// ─────────────────────────────────────────────
if ($action === 'update_menu_item') {
    $item = $payload['item'] ?? null;
    if (!is_array($item) || empty($item['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Item ID is required.']);
        exit;
    }

    $id       = intval($item['id']);
    $name     = trim($item['name'] ?? '');
    $price    = floatval($item['price'] ?? 0);
    $category = trim($item['category'] ?? '');
    $stock    = intval($item['stock'] ?? 0);
    $emoji    = trim($item['emoji'] ?? '🍽');
    $status   = trim($item['status'] ?? 'available');

    if (!$name || $price <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Name and valid price are required.']);
        exit;
    }

    try {
        $stmt = $conn->prepare(
            "UPDATE menu_items SET name=?, price=?, category=?, stock=?, emoji=?, status=? WHERE id=?"
        );
        $stmt->bind_param("sdisisi", $name, $price, $category, $stock, $emoji, $status, $id);
        $stmt->execute();
        logAction($conn, $user['id'], $user['username'], 'update_menu_item', "Updated menu item #{$id}: {$name}");
        echo json_encode(['success' => true]);
    } catch (Exception $ex) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $ex->getMessage()]);
    }
    exit;
}

// ─────────────────────────────────────────────
// DELETE MENU ITEM 
// ─────────────────────────────────────────────
if ($action === 'delete_menu_item') {
    $itemId = intval($payload['item_id'] ?? 0);
    if ($itemId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Valid item ID is required.']);
        exit;
    }

    try {
        
        $stmt = $conn->prepare("UPDATE menu_items SET status = 'archived' WHERE id = ?");
        $stmt->bind_param("i", $itemId);
        $stmt->execute();
        logAction($conn, $user['id'], $user['username'], 'delete_menu_item', "Archived menu item #{$itemId}");
        echo json_encode(['success' => true]);
    } catch (Exception $ex) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $ex->getMessage()]);
    }
    exit;
}

// ─────────────────────────────────────────────
// DEDUCT STOCK AFTER CHECKOUT 
// ─────────────────────────────────────────────
if ($action === 'deduct_stock') {
    $items = $payload['items'] ?? [];
    if (empty($items)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Items array is required.']);
        exit;
    }

    try {
        $stmt = $conn->prepare(
            "UPDATE menu_items SET stock = GREATEST(0, stock - ?) WHERE id = ?"
        );
        foreach ($items as $item) {
            $qty = intval($item['qty'] ?? 0);
            $id  = intval($item['id'] ?? 0);
            if ($id > 0 && $qty > 0) {
                $stmt->bind_param("ii", $qty, $id);
                $stmt->execute();
            }
        }
        echo json_encode(['success' => true]);
    } catch (Exception $ex) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Stock deduction failed: ' . $ex->getMessage()]);
    }
    exit;
}

// ─────────────────────────────────────────────
// RESTOCK MENU ITEM 
// ─────────────────────────────────────────────
if ($action === 'restock_menu_item') {
    $itemId   = intval($payload['item_id'] ?? 0);
    $quantity = intval($payload['quantity'] ?? 0);

    if ($itemId <= 0 || $quantity <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Valid item ID and quantity are required.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("UPDATE menu_items SET stock = stock + ? WHERE id = ?");
        $stmt->bind_param("ii", $quantity, $itemId);
        $stmt->execute();
        // Get updated stock for confirmation
        $res = $conn->query("SELECT stock FROM menu_items WHERE id = $itemId");
        $row = $res->fetch_assoc();
        logAction($conn, $user['id'], $user['username'], 'restock_menu_item', "Restocked item #{$itemId} +{$quantity}");
        echo json_encode(['success' => true, 'new_stock' => (int)$row['stock']]);
    } catch (Exception $ex) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Restock failed: ' . $ex->getMessage()]);
    }
    exit;
}

// ─────────────────────────────────────────────
// SAVE ORDER 
// ─────────────────────────────────────────────
if ($action === 'save_order') {
    $order = $payload['order'] ?? null;
    if (!is_array($order) || empty($order['items'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Order payload is required.']);
        exit;
    }

    try {
        $orderId = saveOrder($conn, $user['id'] ?? null, $user['username'] ?? null, $order);
        logAction($conn, $user['id'], $user['username'], 'create_order', "Created order #{$orderId}");
        echo json_encode(['success' => true, 'order_id' => $orderId]);
    } catch (mysqli_sql_exception $ex) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error while saving order: ' . $ex->getMessage()]);
    } catch (Exception $ex) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Unexpected error while saving order: ' . $ex->getMessage()]);
    }
    exit;
}

// ─────────────────────────────────────────────
// SAVE INGREDIENT
// ─────────────────────────────────────────────
if ($action === 'save_ingredient') {
    $ingredient = $payload['ingredient'] ?? null;
    if (!is_array($ingredient) || empty($ingredient['name'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ingredient data is required.']);
        exit;
    }

    try {
        $ingredientId = saveIngredient($conn, $user['id'] ?? null, $user['username'] ?? null, $ingredient);
        logAction($conn, $user['id'], $user['username'], 'create_ingredient', "Created ingredient #{$ingredientId}");
        echo json_encode(['success' => true, 'ingredient_id' => $ingredientId]);
    } catch (Exception $ex) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $ex->getMessage()]);
    }
    exit;
}

// ─────────────────────────────────────────────
// GET INGREDIENTS 
// ─────────────────────────────────────────────
if ($action === 'get_ingredients') {
    try {
        $ingredients = getIngredients($conn);
        echo json_encode(['success' => true, 'ingredients' => $ingredients]);
    } catch (Exception $ex) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to load ingredients.']);
    }
    exit;
}

// ─────────────────────────────────────────────
// UPDATE INGREDIENT STOCK 
// ─────────────────────────────────────────────
if ($action === 'update_ingredient_stock') {
    $ingredientId = intval($payload['ingredient_id'] ?? 0);
    $newStock     = floatval($payload['stock'] ?? 0);
    $reason       = trim($payload['reason'] ?? '');

    if ($ingredientId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Valid ingredient ID is required.']);
        exit;
    }

    try {
        updateIngredientStock($conn, $ingredientId, $newStock, $user['id'] ?? null, $user['username'] ?? null, $reason);
        echo json_encode(['success' => true]);
    } catch (Exception $ex) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $ex->getMessage()]);
    }
    exit;
}

// ─────────────────────────────────────────────
// PERFORM RECONCILIATION 
// ─────────────────────────────────────────────
if ($action === 'perform_reconciliation') {
    try {
        $rows = performDailyReconciliation($conn, $user['id'] ?? null, $user['username'] ?? 'system');
        echo json_encode(['success' => true, 'rows' => $rows, 'message' => 'Daily reconciliation completed.']);
    } catch (Exception $ex) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to perform reconciliation: ' . $ex->getMessage()]);
    }
    exit;
}

// ─────────────────────────────────────────────
// ARCHIVE MENU ITEM 
// ─────────────────────────────────────────────
if ($action === 'archive_menu_item') {
    $itemId = intval($payload['item_id'] ?? 0);
    if ($itemId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Valid item ID is required.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("UPDATE menu_items SET status = 'archived' WHERE id = ?");
        $stmt->bind_param("i", $itemId);
        $stmt->execute();
        logAction($conn, $user['id'], $user['username'], 'archive_menu_item', "Archived menu item #{$itemId}");
        echo json_encode(['success' => true]);
    } catch (Exception $ex) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
    exit;
}

// ─────────────────────────────────────────────
// FALLBACK
// ─────────────────────────────────────────────
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Invalid API action: ' . htmlspecialchars($action ?? '')]);
exit;