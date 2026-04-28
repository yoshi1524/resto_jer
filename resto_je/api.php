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
// ─────────────────────────────────────────────────────────────────
// GET BRANCH SALES SUMMARY — ALL BRANCHES (Admin)
// ─────────────────────────────────────────────────────────────────
if ($action === 'get_branch_sales') {
    $dateFrom = trim($payload['date_from'] ?? date('Y-m-01'));
    $dateTo   = trim($payload['date_to']   ?? date('Y-m-d'));
    try {
        $stmt = $conn->prepare("
            SELECT COALESCE(b.id,0) AS branch_id,
                   COALESCE(b.branch_name,'Unassigned') AS branch_name,
                   COUNT(o.id) AS total_orders,
                   COALESCE(SUM(o.total),0) AS total_revenue,
                   COALESCE(SUM(o.discount_amount),0) AS total_discounts,
                   COALESCE(AVG(o.total),0) AS avg_order_value,
                   COALESCE(SUM(CASE WHEN o.payment_method='cash' THEN o.total ELSE 0 END),0) AS cash_sales,
                   COALESCE(SUM(CASE WHEN o.payment_method='e_wallet' THEN o.total ELSE 0 END),0) AS ewallet_sales,
                   COALESCE(SUM(CASE WHEN o.payment_method='online' THEN o.total ELSE 0 END),0) AS online_sales
            FROM orders o LEFT JOIN branches b ON o.branch_id=b.id
            WHERE DATE(o.created_at) BETWEEN ? AND ? AND o.status='completed'
            GROUP BY o.branch_id,b.branch_name ORDER BY total_revenue DESC");
        $stmt->bind_param('ss',$dateFrom,$dateTo);
        $stmt->execute();
        $branches=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $stmt2=$conn->prepare("
            SELECT COALESCE(o.branch_id,0) AS branch_id,
                   COALESCE(b.branch_name,'Unassigned') AS branch_name,
                   DATE(o.created_at) AS sale_date,
                   COUNT(o.id) AS orders, COALESCE(SUM(o.total),0) AS revenue
            FROM orders o LEFT JOIN branches b ON o.branch_id=b.id
            WHERE DATE(o.created_at) BETWEEN ? AND ? AND o.status='completed'
            GROUP BY o.branch_id,b.branch_name,DATE(o.created_at)
            ORDER BY sale_date DESC,revenue DESC");
        $stmt2->bind_param('ss',$dateFrom,$dateTo);
        $stmt2->execute();
        $daily=$stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt2->close();

        $stmt3=$conn->prepare("
            SELECT COALESCE(o.branch_id,0) AS branch_id,
                   oi.item_name,oi.emoji,
                   SUM(oi.quantity) AS qty_sold, SUM(oi.item_total) AS revenue
            FROM order_items oi JOIN orders o ON oi.order_id=o.id
            WHERE DATE(o.created_at) BETWEEN ? AND ? AND o.status='completed'
            GROUP BY o.branch_id,oi.item_name,oi.emoji
            ORDER BY o.branch_id,qty_sold DESC");
        $stmt3->bind_param('ss',$dateFrom,$dateTo);
        $stmt3->execute();
        $topItems=$stmt3->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt3->close();

        $stmt4=$conn->prepare("
            SELECT o.id,o.order_number,o.table_name,o.username,
                   COALESCE(b.branch_name,'Unassigned') AS branch_name,
                   o.total,o.discount_amount,o.payment_method,o.customer_name,o.created_at
            FROM orders o LEFT JOIN branches b ON o.branch_id=b.id
            WHERE DATE(o.created_at) BETWEEN ? AND ? AND o.status='completed'
            ORDER BY o.created_at DESC LIMIT 30");
        $stmt4->bind_param('ss',$dateFrom,$dateTo);
        $stmt4->execute();
        $recent=$stmt4->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt4->close();

        echo json_encode(['success'=>true,'branches'=>$branches,'daily'=>$daily,'top_items'=>$topItems,'recent'=>$recent,'date_from'=>$dateFrom,'date_to'=>$dateTo]);
    } catch(Exception $ex) { http_response_code(500); echo json_encode(['success'=>false,'message'=>$ex->getMessage()]); }
    exit;
}

// ─────────────────────────────────────────────────────────────────
// GET BRANCH SALES — SINGLE BRANCH (Manager)
// ─────────────────────────────────────────────────────────────────
if ($action === 'get_my_branch_sales') {
    $branchId = intval($payload['branch_id'] ?? 0);
    $dateFrom = trim($payload['date_from'] ?? date('Y-m-01'));
    $dateTo   = trim($payload['date_to']   ?? date('Y-m-d'));
    if ($branchId <= 0) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Branch ID required.']); exit; }
    try {
        $stmt=$conn->prepare("
            SELECT COUNT(o.id) AS total_orders,
                   COALESCE(SUM(o.total),0) AS total_revenue,
                   COALESCE(SUM(o.discount_amount),0) AS total_discounts,
                   COALESCE(AVG(o.total),0) AS avg_order_value,
                   COALESCE(SUM(CASE WHEN o.payment_method='cash' THEN o.total ELSE 0 END),0) AS cash_sales,
                   COALESCE(SUM(CASE WHEN o.payment_method='e_wallet' THEN o.total ELSE 0 END),0) AS ewallet_sales,
                   COALESCE(SUM(CASE WHEN o.payment_method='online' THEN o.total ELSE 0 END),0) AS online_sales
            FROM orders o WHERE o.branch_id=? AND DATE(o.created_at) BETWEEN ? AND ? AND o.status='completed'");
        $stmt->bind_param('iss',$branchId,$dateFrom,$dateTo);
        $stmt->execute();
        $summary=$stmt->get_result()->fetch_assoc();
        $stmt->close();

        $stmt2=$conn->prepare("
            SELECT DATE(o.created_at) AS sale_date,COUNT(o.id) AS orders,SUM(o.total) AS revenue
            FROM orders o WHERE o.branch_id=? AND DATE(o.created_at) BETWEEN ? AND ? AND o.status='completed'
            GROUP BY DATE(o.created_at) ORDER BY sale_date DESC");
        $stmt2->bind_param('iss',$branchId,$dateFrom,$dateTo);
        $stmt2->execute();
        $daily=$stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt2->close();

        $stmt3=$conn->prepare("
            SELECT oi.item_name,oi.emoji,SUM(oi.quantity) AS qty_sold,SUM(oi.item_total) AS revenue
            FROM order_items oi JOIN orders o ON oi.order_id=o.id
            WHERE o.branch_id=? AND DATE(o.created_at) BETWEEN ? AND ? AND o.status='completed'
            GROUP BY oi.item_name,oi.emoji ORDER BY qty_sold DESC LIMIT 10");
        $stmt3->bind_param('iss',$branchId,$dateFrom,$dateTo);
        $stmt3->execute();
        $topItems=$stmt3->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt3->close();

        $stmt4=$conn->prepare("
            SELECT o.id,o.order_number,o.table_name,o.username,
                   o.total,o.discount_amount,o.payment_method,o.customer_name,o.created_at
            FROM orders o WHERE o.branch_id=? AND DATE(o.created_at) BETWEEN ? AND ? AND o.status='completed'
            ORDER BY o.created_at DESC LIMIT 20");
        $stmt4->bind_param('iss',$branchId,$dateFrom,$dateTo);
        $stmt4->execute();
        $recent=$stmt4->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt4->close();

        echo json_encode(['success'=>true,'summary'=>$summary,'daily'=>$daily,'top_items'=>$topItems,'recent'=>$recent]);
    } catch(Exception $ex) { http_response_code(500); echo json_encode(['success'=>false,'message'=>$ex->getMessage()]); }
    exit;
}

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
    $category = trim($item['category'] ?? '');
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
        $stmt->bind_param("sdsiss", $name, $price, $category, $stock, $emoji, $status);
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
        $stmt->bind_param("sdsissi", $name, $price, $category, $stock, $emoji, $status, $id);
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