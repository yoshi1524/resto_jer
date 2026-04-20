<?php
require 'config.php';
$conn = dbConnect();
ensureSchema($conn);
requireLogin();
requireRole('admin');
$user = currentUser();
$message = null;
$error = null;

// ── Create User ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_user') {
    $username  = trim($_POST['username'] ?? '');
    $password  = trim($_POST['password'] ?? '');
    $role      = trim($_POST['role'] ?? 'staff');
    $branch_id = intval($_POST['branch_id'] ?? 0) ?: null;

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } elseif (!in_array($role, ['admin', 'manager', 'staff'], true)) {
        $error = 'Please select a valid role.';
    } else {
        try {
            if (createUser($conn, $username, $password, $role)) {
                $message = "User '{$username}' created successfully.";
                logAction($conn, $user['id'], $user['username'], 'create_user', "Created POS user {$username} with role {$role}");
            } else {
                $error = 'Unable to create user. The username may already exist.';
            }
        } catch (mysqli_sql_exception $ex) {
            $error = $conn->errno === 1062 ? 'Username already exists.' : 'Database error: ' . $ex->getMessage();
        }
    }
}

// ── Archive Record ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'archive_record') {
    $id   = intval($_POST['id']);
    $type = $_POST['type']; // 'user', 'menu_item', or 'ingredient'
    $table = ($type === 'user') ? 'users' : (($type === 'menu_item') ? 'menu_items' : 'ingredients');

    $res  = $conn->query("SELECT * FROM `$table` WHERE id = $id");
    $data = $res ? $res->fetch_assoc() : null;

    if ($data) {
        $name      = $data['username'] ?? $data['name'];
        $json_data = json_encode($data);
        $stmt = $conn->prepare("INSERT INTO archived_records (original_id, record_type, record_name, data_snapshot) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $id, $type, $name, $json_data);
        if ($stmt->execute()) {
            $conn->query("DELETE FROM `$table` WHERE id = $id");
            $message = "Record archived successfully.";
        } else {
            $error = "Failed to archive record.";
        }
    } else {
        $error = "Record not found.";
    }
    header("Location: admin.php");
    exit;
}

// ── Data for page ────────────────────────────────────────────────
$users = getUsers($conn);
$branch_list = [];
$b_res = $conn->query("SELECT id, branch_name FROM branches ORDER BY branch_name ASC");
if ($b_res) {
    while ($row = $b_res->fetch_assoc()) $branch_list[] = $row;
}

// Inventory counts
$inStockCount    = (int)$conn->query("SELECT COUNT(*) FROM menu_items WHERE stock > 5 AND status != 'archived'")->fetch_row()[0];
$lowStockCount   = (int)$conn->query("SELECT COUNT(*) FROM menu_items WHERE stock <= 5 AND stock > 0 AND status != 'archived'")->fetch_row()[0];
$outOfStockCount = (int)$conn->query("SELECT COUNT(*) FROM menu_items WHERE stock = 0 AND status != 'archived'")->fetch_row()[0];
$totalCount      = (int)$conn->query("SELECT COUNT(*) FROM menu_items WHERE status != 'archived'")->fetch_row()[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Countryside Admin - Full Dashboard</title>
<link rel="icon" href="assets/cside.png" type="image/png">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
<style>
<?php echo str_replace('  ', '', getSharedStyles()); ?>

.notice { padding: 14px 18px; border-radius: 14px; margin-bottom: 18px; font-size: 13px; }
.notice.success { background: rgba(91, 191, 138, 0.15); border: 1px solid rgba(91, 191, 138, 0.3); color: #b7f1cf; }
.notice.error   { background: rgba(224, 92, 92, 0.15);  border: 1px solid rgba(224, 92, 92, 0.3);  color: #f0b0b0; }
.user-table { width: 100%; border-collapse: collapse; margin-top: 12px; }
.user-table th, .user-table td { padding: 12px 14px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.08); font-size: 13px; }
.user-table th { color: var(--text3); font-weight: 600; }
.form-row   { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
.form-group { display: flex; flex-direction: column; gap: 8px; }
.form-label { font-size: 12px; color: var(--text3); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
.form-input { background: var(--surface2); border: 1px solid var(--border); border-radius: 12px; color: var(--text); padding: 12px 14px; font-size: 14px; }
.form-input:focus { outline: none; border-color: var(--accent); }
</style>
</head>
<body>

<div class="app">
  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="logo">
      <div class="logo-text">Countryside</div>
      <div class="logo-sub">Admin Dashboard</div>
    </div>
    <nav class="nav">
      <div class="nav-item" onclick="showPage('reports')">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        Sales Reports
      </div>
      <div class="nav-item" onclick="showPage('menu')">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        Menu Management
      </div>
      <div class="nav-item" onclick="showPage('inventory')">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        Inventory
      </div>
      <div class="nav-item" onclick="showPage('users')">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zm-4 9c-4.418 0-8 1.79-8 4v1h16v-1c0-2.21-3.582-4-8-4z"/></svg>
        User Management
      </div>
    </nav>
    <div class="sidebar-footer">
      <div class="clock" id="clock">--:--</div>
      <div class="date-txt" id="dateLabel">Loading...</div>
      <div style="font-size:13px;color:var(--text3);margin-top:4px;">
        Signed in as <?= htmlspecialchars($user['username']) ?> (Admin)
      </div>
    </div>
    <div class="sidebar-footer">
      <a class="btn btn-ghost btn-sm" href="login.php?logout=1" style="margin-top:12px;display:block;text-align:center;">Logout</a>
    </div>
  </aside>

  <!-- MAIN -->
  <div class="main">

    <!-- REPORTS PAGE -->
    <div id="page-reports" class="page" style="flex-direction:column;gap:16px;">
      <div class="section-header" style="margin-bottom:0;">
        <div class="section-title">Sales Reports &amp; Analytics</div>
        <button class="btn btn-ghost btn-sm" onclick="clearSalesData()">Reset Data</button>
      </div>
      <div class="grid-4">
        <div class="stat-card accent"><div class="stat-label">Today's Revenue</div><div class="stat-value accent" id="rptRevenue">₱0.00</div><div class="stat-sub" id="rptTxCount">0 transactions</div></div>
        <div class="stat-card green"><div class="stat-label">Orders Today</div><div class="stat-value green" id="rptOrders">0</div><div class="stat-sub">Completed orders</div></div>
        <div class="stat-card blue"><div class="stat-label">Avg Order Value</div><div class="stat-value blue" id="rptAvg">₱0.00</div><div class="stat-sub">Per transaction</div></div>
        <div class="stat-card red"><div class="stat-label">Total Discounts</div><div class="stat-value" style="color:var(--red)" id="rptDiscounts">₱0.00</div><div class="stat-sub">Savings given</div></div>
      </div>
      <div class="grid-2" style="flex:1;min-height:0;">
        <div style="display:flex;flex-direction:column;gap:16px;">
          <div class="card" style="overflow:auto;">
            <div class="card-title">Sales by Hour</div>
            <div class="chart-bar-wrap" id="hourlyChart"></div>
          </div>
          <div class="card" style="flex:1;overflow:auto;">
            <div class="card-title">Daily Sales Summary</div>
            <table class="user-table">
              <thead><tr><th>Date</th><th>Revenue</th></tr></thead>
              <tbody id="dailySalesBody"></tbody>
            </table>
          </div>
        </div>
        <div class="card" style="overflow:auto;">
          <div class="card-title">Top Selling Items</div>
          <div id="topItemsChart"></div>
        </div>
      </div>
    </div>

    <!-- MENU PAGE -->
    <div id="page-menu" class="page" style="flex-direction:column;">
      <div class="section-header">
        <div class="section-title">Menu Management</div>
        <button class="btn btn-accent" onclick="openAddItemModal()">+ Add Item</button>
      </div>
      <div class="card" style="flex:1;overflow:auto;">
        <table id="menuTable">
          <thead>
            <tr><th>Item</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr>
          </thead>
          <tbody id="menuTableBody"></tbody>
        </table>
      </div>
    </div>

    <!-- INVENTORY PAGE -->
    <div id="page-inventory" class="page" style="flex-direction:column;">
      <div class="section-header">
        <div>
          <div class="section-title">Inventory Tracking</div>
          <div style="display:flex;gap:8px;margin-top:10px;">
            <button class="btn btn-ghost btn-sm active" id="inventoryModeItems" onclick="setInventoryMode('items')">Items</button>
            <button class="btn btn-ghost btn-sm" id="inventoryModeIngredients" onclick="setInventoryMode('ingredients')">Ingredients</button>
          </div>
        </div>
        <button class="btn btn-accent" onclick="openRestockModal()">+ Restock</button>
      </div>
      <!-- FIX: Stat cards now have JS-updatable IDs (invInStock etc.)
           PHP values are used as initial server-rendered values; JS updates them on navigation -->
      <div class="grid-4" style="margin-bottom:4px;">
        <div class="stat-card green">
          <div class="stat-label">In Stock</div>
          <div class="stat-value green" id="invInStock"><?= $inStockCount ?></div>
          <div class="stat-sub">Items available</div>
        </div>
        <div class="stat-card yellow">
          <div class="stat-label">Low Stock</div>
          <div class="stat-value accent" id="invLowStock"><?= $lowStockCount ?></div>
          <div class="stat-sub">Below threshold</div>
        </div>
        <div class="stat-card red">
          <div class="stat-label">Out of Stock</div>
          <div class="stat-value" style="color:var(--red)" id="invOutStock"><?= $outOfStockCount ?></div>
          <div class="stat-sub">Need restocking</div>
        </div>
        <div class="stat-card blue">
          <div class="stat-label">Total</div>
          <div class="stat-value blue" id="invTotal"><?= $totalCount ?></div>
          <div class="stat-sub" id="invTotalSub">Menu items</div>
        </div>
      </div>
      <!-- FIX: Added the inventory table that was completely missing from admin.php -->
      <div class="card" style="flex:1;overflow:auto;">
        <table>
          <thead id="inventoryHeader"></thead>
          <tbody id="inventoryBody"></tbody>
        </table>
      </div>
    </div>

    <!-- USER MANAGEMENT PAGE -->
    <div id="page-users" class="page" style="flex-direction:column;">
      <div class="section-header">
        <div class="section-title">User Management</div>
      </div>
      <?php if ($message): ?><div class="notice success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
      <?php if ($error):   ?><div class="notice error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <div class="grid-2">
        <div class="card">
          <div class="card-title">Add New User</div>
          <form method="POST">
            <input type="hidden" name="action" value="create_user">
            <div class="form-row">
              <div class="form-group">
                <label class="form-label" for="new_username">Username</label>
                <input class="form-input" type="text" id="new_username" name="username" required placeholder="Username">
              </div>
              <div class="form-group">
                <label class="form-label" for="new_password">Password</label>
                <input class="form-input" type="password" id="new_password" name="password" required placeholder="Password">
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label" for="new_role">Role</label>
                <select class="form-input" id="new_role" name="role">
                  <option value="admin">Admin</option>
                  <option value="manager">Manager</option>
                  <option value="staff" selected>Staff</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label" for="branch_id">Branch</label>
                <select class="form-input" id="branch_id" name="branch_id">
                  <option value="">-- Select Branch --</option>
                  <?php foreach ($branch_list as $b): ?>
                    <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['branch_name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <button type="submit" class="btn btn-accent">Create User</button>
          </form>
        </div>
        <div class="card">
          <div class="card-title">Existing Users</div>
          <table class="user-table">
            <thead>
              <tr><th>Username</th><th>Role</th><th>Status</th><th>Created</th><th>Action</th></tr>
            </thead>
            <tbody>
              <?php foreach ($users as $u): ?>
                <tr>
                  <td><?= htmlspecialchars($u['username']) ?></td>
                  <td><?= htmlspecialchars(ucfirst($u['role'])) ?></td>
                  <td><?= htmlspecialchars(ucfirst($u['status'])) ?></td>
                  <td><?= htmlspecialchars($u['created_at']) ?></td>
                  <td>
                    <!-- FIX: Archive form now uses correct PHP variable $u['id'] in PHP context,
                         not inside a JS string where it would render blank -->
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Archive this user?')">
                      <input type="hidden" name="action" value="archive_record">
                      <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                      <input type="hidden" name="type" value="user">
                      <button type="submit" class="btn btn-danger btn-sm">Archive</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ORDER HISTORY PAGE -->
    <div id="page-orders" class="page" style="flex-direction:column;">
      <div class="section-header">
        <div class="section-title">Order History</div>
        <span class="badge" id="orderCount">0 orders</span>
      </div>
      <div class="card" style="flex:1;overflow:auto;">
        <table>
          <thead><tr><th>#</th><th>Table</th><th>Items</th><th>Total</th><th>Discount</th><th>Change</th><th>Time</th></tr></thead>
          <tbody id="orderHistoryBody"></tbody>
        </table>
      </div>
    </div>

  </div><!-- /.main -->
</div><!-- /.app -->

<!-- MODALS -->
<div class="modal-overlay" id="itemModal">
  <div class="modal">
    <div class="modal-title" id="itemModalTitle">Add Menu Item</div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Item Name *</label>
        <input class="form-input" id="fName" placeholder="Item name"/>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Category *</label>
        <select class="form-input" id="fCategory">
          <option>Sizzling Favorites</option>
          <option>Country Classics</option>
          <option>Heart Lover's Delight</option>
          <option>Sandwiches &amp; Snacks</option>
          <option>Desserts</option>
          <option>Cream Soups</option>
          <option>Extras</option>
          <option>Beverages</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Price (₱) *</label>
        <input class="form-input" id="fPrice" type="number" placeholder="0.00" min="0" step="0.01"/>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Stock Quantity</label>
        <input class="form-input" id="fStock" type="number" placeholder="0" min="0"/>
      </div>
      <div class="form-group">
        <label class="form-label">Status</label>
        <select class="form-input" id="fStatus">
          <option value="available">Available</option>
          <option value="unavailable">Unavailable</option>
        </select>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('itemModal')">Cancel</button>
      <button class="btn btn-accent" onclick="saveItem()">Save Item</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="restockModal">
  <div class="modal" style="width:400px;">
    <div class="modal-title" id="restockModalTitle">Restock Item</div>
    <div class="form-group">
      <label class="form-label">Select Item</label>
      <select class="form-input" id="restockItem"></select>
    </div>
    <div class="form-group" style="margin-top:12px;">
      <label class="form-label">Add Quantity</label>
      <input class="form-input" id="restockQty" type="number" placeholder="0" min="1"/>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('restockModal')">Cancel</button>
      <button class="btn btn-green" onclick="doRestock()">Restock</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="receiptModal">
  <div class="modal receipt-modal">
    <div class="receipt-header">
      <div class="receipt-logo">🍽 RestoPOS</div>
      <div style="font-size:12px;color:var(--text3);margin-top:4px;" id="receiptDate"></div>
      <div style="font-size:13px;color:var(--text2);margin-top:2px;" id="receiptTable"></div>
    </div>
    <hr class="receipt-divider"/>
    <div id="receiptItems"></div>
    <hr class="receipt-divider"/>
    <div class="receipt-row"><span>Subtotal</span><span id="rcSubtotal"></span></div>
    <div class="receipt-row"><span id="rcDiscountLabel">Discount</span><span id="rcDiscount" style="color:var(--green)"></span></div>
    <div class="receipt-total"><span>TOTAL</span><span id="rcTotal"></span></div>
    <hr class="receipt-divider"/>
    <div class="receipt-row"><span>Payment</span><span id="rcPaymentMethod"></span></div>
    <div class="receipt-row"><span>Reference</span><span id="rcPaymentReference"></span></div>
    <div class="receipt-row"><span>Customer</span><span id="rcCustomerName"></span></div>
    <div class="receipt-payment">
      <div style="font-size:12px;color:var(--text3);">Amount Received</div>
      <div style="font-size:18px;font-weight:700;" id="rcCash"></div>
      <div style="font-size:12px;color:var(--text3);margin-top:8px;">Change</div>
      <div class="receipt-change" id="rcChange"></div>
    </div>
    <div style="text-align:center;font-size:12px;color:var(--text3);margin-top:16px;">Thank you for dining with us! 🙏</div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('receiptModal')">Close</button>
      <button class="btn btn-accent" onclick="printReceipt()">🖨 Print</button>
    </div>
  </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<!-- FIX: Was <link rel="script"> which does nothing. Now a proper script tag. -->
<script src="script/adminscript.js"></script>
</body>
</html>