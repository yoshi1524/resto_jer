<?php
require 'config.php';
$conn = dbConnect();
ensureSchema($conn);
requireLogin();
requireRole('admin', 'manager');
$user = currentUser();
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_user') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = trim($_POST['role'] ?? 'staff');
    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } elseif (!in_array($role, ['manager', 'staff'], true)) {
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
// managerr.php
// Get the logged-in manager's branch
$manager_branch = $user['branch_id']?? 0; 

$query = "SELECT SUM(t.total_amount) as daily_total 
          FROM sales_transactions t
          JOIN users u ON t.user_id = u.id 
          WHERE u.branch_id = ? 
          AND DATE(t.created_at) = CURDATE()";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $manager_branch);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$branch_daily_sales = $row['daily_total'] ?? 0;


$users = getUsers($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Countryside Manager - Dashboard</title>
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
.sidebar-section { margin: 20px 0; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 16px; }
.sidebar-section-header { display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--text3); font-weight: 600; cursor: pointer; padding: 8px 0; text-transform: uppercase; letter-spacing: 0.5px; }
.sidebar-section-header svg { width: 14px; height: 14px; }
.sidebar-section-toggle { margin-left: auto; transition: transform 0.2s; }
</style>
</head>
<body>

<div class="app">
  <aside class="sidebar">
    <div class="logo">
      <div class="logo-text">Countryside</div>
      <div class="logo-sub">Manager Dashboard</div>
    </div>
    <nav class="nav">
      <div class="nav-item active" onclick="showPage('menu')">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        Menu Management
      </div>
      <div class="nav-item" onclick="showPage('inventory')">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        Inventory
      </div>
      <div class="nav-item" onclick="showPage('reports')">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        Sales Reports
      </div>
    </nav>

    <!-- Quick Add Ingredient -->
    <div class="sidebar-section">
      <div class="sidebar-section-header" onclick="toggleIngredientSection()">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        Quick Add Ingredient
        <svg class="sidebar-section-toggle" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </div>
      <div id="ingredientSectionContent" style="display:none;margin-top:12px;">
        <form id="ingredientForm" onsubmit="addIngredient(event)">
          <div class="form-group" style="margin-bottom:12px;">
            <input class="form-input" type="text" id="ingName" placeholder="Ingredient name" required style="font-size:12px;padding:8px 10px;">
          </div>
          <div class="form-group" style="margin-bottom:12px;">
            <select class="form-input" id="ingUnit" required style="font-size:12px;padding:8px 10px;">
              <option value="kg">kg</option>
              <option value="pcs">pcs</option>
              <option value="liters">liters</option>
              <option value="boxes">boxes</option>
            </select>
          </div>
          <div class="form-group" style="margin-bottom:12px;">
            <input class="form-input" type="number" id="ingStock" placeholder="Initial stock" min="0" step="0.01" required style="font-size:12px;padding:8px 10px;">
          </div>
          <button class="btn btn-accent btn-sm" type="submit" style="width:100%;">Add Ingredient</button>
        </form>
      </div>
    </div>

    <div class="sidebar-footer">
      <div class="clock" id="clock">--:--</div>
      <div class="date-txt" id="dateLabel">Loading...</div>
      <div style="font-size:13px;color:var(--text3);margin-top:4px;">
        Signed in as <?= htmlspecialchars($user['username']) ?> (<?= ucfirst($user['role']) ?>)
      </div>
      <a class="btn btn-ghost btn-sm" href="login.php?logout=1" style="margin-top:12px;display:block;text-align:center;">Logout</a>
    </div>
  </aside>

  <div class="main">

    <!-- MENU PAGE -->
    <div id="page-menu" class="page active" style="flex-direction:column;">
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
      <div class="section-header" style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:12px;">
        <div>
          <div class="section-title">Inventory Tracking</div>
          <div style="display:flex;gap:8px;margin-top:10px;">
            
            <button class="btn btn-ghost btn-sm active" id="inventoryModeIngredients" onclick="setInventoryMode('ingredients')">Ingredients</button>
          </div>
        </div>
        <button class="btn btn-accent" onclick="openRestockModal()">+ Restock</button>
      </div>
      <div class="grid-4" style="margin-bottom:4px;">
        <div class="stat-card green"><div class="stat-label">In Stock</div><div class="stat-value green" id="invInStock">0</div><div class="stat-sub">Items available</div></div>
        <div class="stat-card yellow"><div class="stat-label">Low Stock</div><div class="stat-value accent" id="invLowStock">0</div><div class="stat-sub">Below threshold</div></div>
        <div class="stat-card red"><div class="stat-label">Out of Stock</div><div class="stat-value" style="color:var(--red)" id="invOutStock">0</div><div class="stat-sub">Need restocking</div></div>
        <div class="stat-card blue"><div class="stat-label">Total</div><div class="stat-value blue" id="invTotal">0</div><div class="stat-sub" id="invTotalSub">Menu items</div></div>
      </div>
      <div class="card" style="flex:1;overflow:auto;">
        <table>
          <thead id="inventoryHeader"></thead>
          <tbody id="inventoryBody"></tbody>
        </table>
      </div>
    </div>

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
          <!--<div class="card" style="overflow:auto;">
            <div class="card-title">Sales by Hour</div>
            <div class="chart-bar-wrap" id="hourlyChart"></div>
          </div>
          <div class="card" style="flex:1;overflow:auto;">
            <div class="card-title">Daily Sales Summary</div>
            <div style="overflow-y:auto;max-height:260px;">
              <table class="user-table">
                <thead><tr><th>Date</th><th>Revenue</th><th>Orders</th></tr></thead>
                <tbody id="dailySalesBody"></tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="card" style="overflow:hidden;display:flex;flex-direction:column;">
          <div class="card-title">Top Selling Items</div>
          <div id="topItemsChart" style="overflow-y:auto;max-height:260px;"></div>
        </div>
      </div>
      <div class="card" style="overflow:auto;max-height:260px;">
        <div class="card-title">Recent Transactions</div>
        <table>
          <thead><tr><th>#</th><th>Table</th><th>Items</th><th>Subtotal</th><th>Discount</th><th>Total</th><th>Time</th></tr></thead>
          <tbody id="txTable"></tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<!-- MODALS -->
<div class="modal-overlay" id="itemModal">
  <div class="modal">
    <div class="modal-title" id="itemModalTitle">Add Menu Item</div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Emoji Icon</label>
        <input class="form-input" id="fEmoji" placeholder="e.g. 🍕" maxlength="4"/>
      </div>
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
          <option>Heart Lovers Delight</option>
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
    <div style="text-align:center;font-size:12px;color:var(--text3);margin-top:16px;">Thank you for dining with us!</div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('receiptModal')">Close</button>
      <button class="btn btn-accent" onclick="printReceipt()">🖨 Print</button>
    </div>
  </div>
</div>

<div class="toast-container" id="toastContainer"></div>


<script src="script/adminscript.js"></script>
<script>

document.addEventListener('DOMContentLoaded', function () {
    var origShowPage = window.showPage;
    window.showPage = function(page) {
        origShowPage(page);
        if (page === 'inventory' && typeof setInventoryMode === 'function') {
            setInventoryMode('ingredients');
        }
    };
});
</script>
</body>
</html>