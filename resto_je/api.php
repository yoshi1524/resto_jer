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
        exit;
    } catch (mysqli_sql_exception $ex) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error while saving order: ' . $ex->getMessage(), 'error' => $ex->getMessage()]);
        exit;
    } catch (Exception $ex) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Unexpected error while saving order: ' . $ex->getMessage(), 'error' => $ex->getMessage()]);
        exit;
    }
}

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
        exit;
    } catch (Exception $ex) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $ex->getMessage()]);
        exit;
    }
}

if ($action === 'get_ingredients') {
    try {
        $ingredients = getIngredients($conn);
        echo json_encode(['success' => true, 'ingredients' => $ingredients]);
        exit;
    } catch (Exception $ex) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to load ingredients.']);
        exit;
    }
}

if ($action === 'update_ingredient_stock') {
    $ingredientId = intval($payload['ingredient_id'] ?? 0);
    $newStock = floatval($payload['stock'] ?? 0);
    $reason = trim($payload['reason'] ?? '');

    if ($ingredientId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Valid ingredient ID is required.']);
        exit;
    }

    try {
        updateIngredientStock($conn, $ingredientId, $newStock, $user['id'] ?? null, $user['username'] ?? null, $reason);
        echo json_encode(['success' => true]);
        exit;
    } catch (Exception $ex) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $ex->getMessage()]);
        exit;
    }
}

if ($action === 'perform_reconciliation') {
    try {
        $rows = performDailyReconciliation($conn, $user['id'] ?? null, $user['username'] ?? 'system');
        echo json_encode(['success' => true, 'rows' => $rows, 'message' => 'Daily reconciliation completed.']);
        exit;
    } catch (Exception $ex) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to perform reconciliation: ' . $ex->getMessage()]);
        exit;
    }
}
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';    

if ($action === 'archive_menu_item') {
    $itemId = (int)$data['item_id'];
    
    // Update the status to 'archived' instead of deleting the row
    $stmt = $conn->prepare("UPDATE menu_items SET status = 'archived' WHERE id = ?");
    $stmt->bind_param("i", $itemId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
    exit;
}
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Invalid API action.']);
exit;
