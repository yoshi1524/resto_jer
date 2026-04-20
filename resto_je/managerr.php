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
    $role = trim($_POST['role'] ?? 'staff');
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

$users = getUsers($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Countryside Manager - Dashboard  </title>
<link rel="icon" href="assets/cside.png" type="image/png">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
<style>
<?php echo \str_replace('  ', '', getSharedStyles()); ?>

.notice { padding: 14px 18px; border-radius: 14px; margin-bottom: 18px; font-size: 13px; }
.notice.success { background: rgba(91, 191, 138, 0.15); border: 1px solid rgba(91, 191, 138, 0.3); color: #b7f1cf; }
.notice.error { background: rgba(224, 92, 92, 0.15); border: 1px solid rgba(224, 92, 92, 0.3); color: #f0b0b0; }
.user-table { width: 100%; border-collapse: collapse; margin-top: 12px; }
.user-table th, .user-table td { padding: 12px 14px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.08); font-size: 13px; }
.user-table th { color: var(--text3); font-weight: 600; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
.form-group { display: flex; flex-direction: column; gap: 8px; }
.form-label { font-size: 12px; color: var(--text3); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
.form-input { background: var(--surface2); border: 1px solid var(--border); border-radius: 12px; color: var(--text); padding: 12px 14px; font-size: 14px; }
.form-input:focus { outline: none; border-color: var(--accent); }
.sidebar-section { margin: 20px 0; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 16px; }
.sidebar-section-header { display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--text3); font-weight: 600; cursor: pointer; padding: 8px 0; text-transform: uppercase; letter-spacing: 0.5px; }
.sidebar-section-header svg { width: 14px; height: 14px; }
.sidebar-section-toggle { margin-left: auto; transition: transform 0.2s; }
.sidebar-section-content { margin-top: 12px; }
</style>
</head>
<body>

<div class="app">
  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="logo">
      <div class="logo-text">Countryside</div>
      <div class="logo-sub">Manager Dashboard</div>
     <!--v style="font-size:13px;color:var(--text3);margin-top:4px;">Welcome, <?= htmlspecialchars($user['username']) ?> (<?= ucfirst($user['role']) ?>)</div>-->
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
     <!-- <div class="nav-item" onclick="showPage('users')">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zm-4 9c-4.418 0-8 1.79-8 4v1h16v-1c0-2.21-3.582-4-8-4z"/></svg>
        User Management
      </div>-->
    </nav>
    <!-- INGREDIENT INPUT SECTION -->
    <div class="sidebar-section">
      <div class="sidebar-section-header" onclick="toggleIngredientSection()">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        Quick Add Ingredient
        <svg class="sidebar-section-toggle" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </div>
      <div class="sidebar-section-content" id="ingredientSectionContent" style="display: none;">
        <form id="ingredientForm" onsubmit="addIngredient(event)">
          <div class="form-group" style="margin-bottom: 12px;">
            <input class="form-input" type="text" id="ingName" placeholder="Ingredient name" required style="font-size: 12px; padding: 8px 10px;">
          </div>
          <div class="form-group" style="margin-bottom: 12px;">
            <select class="form-input" id="ingUnit" required style="font-size: 12px; padding: 8px 10px;">
              <option value="kg">kg</option>
              <option value="pcs">pcs</option>
              <option value="liters">liters</option>
              <option value="boxes">boxes</option>
            </select>
          </div>
          <div class="form-group" style="margin-bottom: 12px;">
            <input class="form-input" type="number" id="ingStock" placeholder="Initial stock" min="0" step="0.01" required style="font-size: 12px; padding: 8px 10px;">
          </div>
          <button class="btn btn-accent btn-sm" type="submit" style="width: 100%;">Add Ingredient</button>
        </form>
      </div>
    </div>
    <div class="sidebar-footer">
      <div class="clock" id="clock">--:--</div>
      <div class="date-txt" id="dateLabel">Loading...</div>
      <div style="font-size:13px;color:var(--text3);margin-top:4px;">Signed in as <?= htmlspecialchars($user['username']) ?> (<?= ucfirst($user['role']) ?>)</div>
      <a class="btn btn-ghost btn-sm" href="login.php?logout=1" style="margin-top: 12px; display: block; text-align: center;">Logout</a>
    </div>
  </aside>

  <!-- MAIN -->
  <div class="main">

    <!-- MENU PAGE -->
    <div id="page-menu" class="page active" style="flex-direction:column;">
      <div class="section-header">
        <div class="section-title">Menu Management</div>
        <button class="btn btn-accent" onclick="openAddItemModal()">+ Add Item</button>
      </div>
      <div class="card" style="flex:1; overflow:auto;">
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
      <div class="section-header" style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:12px;">
        <div>
          <div class="section-title">Inventory Tracking</div>
          <div style="display:flex; gap:8px; margin-top:10px;">
            <button class="btn btn-ghost btn-sm active" id="inventoryModeItems" onclick="setInventoryMode('items')">Items</button>
            <button class="btn btn-ghost btn-sm" id="inventoryModeIngredients" onclick="setInventoryMode('ingredients')">Ingredients</button>
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
      <div class="card" style="flex:1; overflow:auto;">
        <table>
          <thead id="inventoryHeader"></thead>
          <tbody id="inventoryBody"></tbody>
        </table>
      </div>
    </div>

    <!-- REPORTS PAGE -->
    <div id="page-reports" class="page" style="flex-direction:column; gap:16px;">
      <div class="section-header" style="margin-bottom:0;">
        <div class="section-title">Sales Reports & Analytics</div>
        <div style="display:flex;gap:8px;">
          <button class="btn btn-ghost btn-sm" onclick="clearSalesData()">Reset Data</button>
        </div>
      </div>
      <div class="grid-4">
        <div class="stat-card accent"><div class="stat-label">Today's Revenue</div><div class="stat-value accent" id="rptRevenue">₱0.00</div><div class="stat-sub" id="rptTxCount">0 transactions</div></div>
        <div class="stat-card green"><div class="stat-label">Orders Today</div><div class="stat-value green" id="rptOrders">0</div><div class="stat-sub">Completed orders</div></div>
        <div class="stat-card blue"><div class="stat-label">Avg Order Value</div><div class="stat-value blue" id="rptAvg">₱0.00</div><div class="stat-sub">Per transaction</div></div>
        <div class="stat-card red"><div class="stat-label">Total Discounts</div><div class="stat-value" style="color:var(--red)" id="rptDiscounts">₱0.00</div><div class="stat-sub">Savings given</div></div>
      </div>
      <div class="grid-2" style="flex:1; min-height:0;">
    <div style="display: flex; flex-direction: column; gap: 16px;"> <div class="card" style="overflow:auto;">
            <div class="card-title">Sales by Hour</div>
            <div class="chart-bar-wrap" id="hourlyChart"></div>
        </div>

        <div class="card" style="flex: 1; overflow:auto;">
            <div class="card-title">Daily Sales Summary</div>
            <table class="user-table">
                <thead>
                    <tr><th>Date</th><th>Revenue</th></tr>
                </thead>
                <tbody id="dailySalesBody"></tbody>
            </table>
        </div>
    </div>

    <div class="card" style="overflow:auto;">
        <div class="card-title">Top Selling Items</div>
        <div id="topItemsChart"></div>
    </div>
</div>
      <div class="card" style="overflow:auto; max-height:260px;">
        <div class="card-title">Recent Transactions</div>
        <table>
          <thead><tr><th>#</th><th>Table</th><th>Items</th><th>Subtotal</th><th>Discount</th><th>Total</th><th>Time</th></tr></thead>
          <tbody id="txTable"></tbody>
        </table>
      </div>
    </div>

    <!-- USER MANAGEMENT PAGE -->
   <!-- <div id="page-users" class="page" style="flex-direction:column;">
      <div class="section-header">
        <div class="section-title">User Management</div>
      </div>
      <?php if ($message): ?><div class="notice success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
      <?php if ($error): ?><div class="notice error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <div class="grid-2">
        <div class="card">
          <div class="card-title">Add New User</div>
          <form method="POST">
            <input type="hidden" name="action" value="create_user">
            <div class="form-row">
              <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input class="form-input" type="text" id="username" name="username" required placeholder="Username">
              </div>
              <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input class="form-input" type="password" id="password" name="password" required placeholder="Password">
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label" for="role">Role</label>
                <select class="form-input" id="role" name="role">
                  <option value="commissary">Admin</option>
                  <option value="commissary">Commissary</option>
                  <option value="staff">Staff</option>
                </select>
              </div>
              <div class="form-group"></div>
            </div>
            <button type="submit" class="btn btn-accent">Create User</button>
          </form>
        </div>
        <div class="card">
          <div class="card-title">Existing Users</div>
          <table class="user-table">
            <thead>
              <tr><th>Username</th><th>Role</th><th>Status</th><th>Created</th></tr>
            </thead>
            <tbody>
              <?php foreach ($users as $u): ?>
                <tr>
                  <td><?= htmlspecialchars($u['username']) ?></td>
                  <td><?= htmlspecialchars(ucfirst($u['role'])) ?></td>
                  <td><?= htmlspecialchars(ucfirst($u['status'])) ?></td>
                  <td><?= htmlspecialchars($u['created_at']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>-->

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
          <option>Sizzling Favorites</option><option>Country Classics</option><option>Heart Lover's Delight</option><option>Sandwiches & Snacks</option><option>Desserts</option><option>Cream Soups</option><option>Extras</option><option>Beverages</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Price (₱) *</label>
        <input class="form-input" id="fPrice" type="number" placeholder="0.00" min="0"/>
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
          <option value="available">Available</option><option value="unavailable">Unavailable</option>
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
    <div class="form-group">
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
    <div class="receipt-row"><span>Discount Type</span><span id="rcDiscountLabel"></span></div>
    <div class="receipt-row"><span>Discount</span><span id="rcDiscount" style="color:var(--green)"></span></div>
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

<script>
let menuItems = JSON.parse(localStorage.getItem('pos_menu') || 'null') || [
  { id:1, emoji:'🍗', name:'Fried Chicken', category:'Country Classics', price:185, stock:20, status:'available' },
  { id:2, emoji:'🍝', name:'Spaghetti', category:'Sizzling Favorites', price:150, stock:15, status:'available' },
  { id:3, emoji:'🍣', name:'Salmon Sashimi', category:'Heart Lover\'s Delight', price:320, stock:8, status:'available' },
  { id:4, emoji:'🥗', name:'Caesar Salad', category:'Heart Lover\'s Delight', price:120, stock:12, status:'available' },
  { id:5, emoji:'🍲', name:'Sinigang na Baboy', category:'Country Classics', price:175, stock:10, status:'available' },
  { id:6, emoji:'🍛', name:'Beef Caldereta', category:'Sizzling Favorites', price:210, stock:6, status:'available' },
  { id:7, emoji:'🧁', name:'Chocolate Lava Cake', category:'Desserts', price:135, stock:14, status:'available' },
  { id:8, emoji:'🥤', name:'Iced Tea', category:'Beverages', price:60, stock:30, status:'available' },
  { id:9, emoji:'☕', name:'Brewed Coffee', category:'Beverages', price:80, stock:25, status:'available' },
  { id:10, emoji:'🍟', name:'French Fries', category:'Extras', price:95, stock:3, status:'available' },
  { id:11, emoji:'🥘', name:'Kare-Kare', category:'Country Classics', price:245, stock:0, status:'available' },
  { id:12, emoji:'🍨', name:'Halo-Halo', category:'Desserts', price:110, stock:18, status:'available' },
];
let ingredients = [];
let cart = [];
let transactions = JSON.parse(localStorage.getItem('pos_tx') || '[]');
let editingItemId = null;
let activeCategory = 'all';
let inventoryMode = 'items';
let nextId = Math.max(...menuItems.map(i=>i.id), 0) + 1;
const CUSTOMER_DISCOUNT_MAP = {
  regular: { percent: 0, label: 'Regular' },
  pwd: { percent: 20, label: 'PWD (20%)' },
  senior: { percent: 20, label: 'Senior Citizen (20%)' }
};

function saveMenu() { localStorage.setItem('pos_menu', JSON.stringify(menuItems)); }
function saveIngredients() { /* Ingredients are now saved to database */ }
function saveTx() { localStorage.setItem('pos_tx', JSON.stringify(transactions)); }

function getSelectedCustomerType() {
  const value = document.getElementById('customerType')?.value || 'regular';
  return CUSTOMER_DISCOUNT_MAP[value] ? value : 'regular';
}
function getDiscountDetails() {
  return CUSTOMER_DISCOUNT_MAP[getSelectedCustomerType()];
}
function getCartTotals() {
  const subtotal = cart.reduce((s,c)=>s+c.price*c.qty,0);
  const discountInfo = getDiscountDetails();
  const discount = subtotal * (discountInfo.percent / 100);
  const total = subtotal - discount;
  return {
    subtotal,
    discount,
    total,
    discount_percent: discountInfo.percent,
    discount_label: discountInfo.label,
    customer_type: getSelectedCustomerType()
  };
}

function loadIngredients() {
  return fetch('api.php', {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'get_ingredients' })
  })
  .then(async r => {
    const text = await r.text();
    try {
      return JSON.parse(text);
    } catch (err) {
      throw new Error('Invalid JSON response from server: ' + text.slice(0, 200));
    }
  })
  .then(result => {
    if (result.success) {
      ingredients = result.ingredients;
      return ingredients;
    } else {
      throw new Error(result.message || 'Failed to load ingredients');
    }
  });
}

function showPage(page) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  document.getElementById('page-' + page).classList.add('active');
  if(event && event.currentTarget) event.currentTarget.classList.add('active');
  if (page === 'menu') renderMenuTable();
  if (page === 'inventory') {
    loadIngredients().then(() => renderInventory()).catch(err => {
      toast('Failed to load ingredients: ' + err.message, 'error');
      renderInventory();
    });
  }
  if (page === 'reports') renderReports();
}
function updateClock() {
  const now = new Date();
  document.getElementById('clock').textContent = now.toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit'});
  document.getElementById('dateLabel').textContent = now.toLocaleDateString('en-PH',{weekday:'short',month:'short',day:'numeric'});
}
setInterval(updateClock, 1000); updateClock();

function getCategories() { return [...new Set(menuItems.map(i=>i.category))]; }
function renderCategoryTabs() {
  const cats = getCategories();
  const tabsEl = document.getElementById('categoryTabs');
  const posTabsEl = document.querySelector('.tabs');
  if (tabsEl) {
    tabsEl.innerHTML = `<div class="cat-tab ${activeCategory==='all'?'active':''}" onclick="filterMenuCat('all',this)">All</div>` + cats.map(c => `<div class="cat-tab ${activeCategory===c?'active':''}" onclick="filterMenuCat('${c}',this)">${c}</div>`).join('');
  }
  if (posTabsEl) {
    posTabsEl.innerHTML = `<div class="tab ${activeCategory==='all'?'active':''}" onclick="filterMenuCat('all',this)">All</div>` + cats.map(c => `<div class="tab ${activeCategory===c?'active':''}" onclick="filterMenuCat('${c}',this)">${c}</div>`).join('');
  }
}
function filterMenuCat(cat, el) {
  activeCategory = cat;
  document.querySelectorAll('.cat-tab, .tab').forEach(t => t.classList.remove('active'));
  document.querySelectorAll(`.cat-tab, .tab`).forEach(t => { if(t.textContent.trim() === (cat==='all'?'All':cat)) t.classList.add('active'); });
  renderMenuGrid();
}
function renderMenuGrid() {
  renderCategoryTabs();
  const q = document.getElementById('menuSearch')?.value.toLowerCase() || '';
  let items = menuItems.filter(i => activeCategory==='all' || i.category===activeCategory);
  if (q) items = items.filter(i => i.name.toLowerCase().includes(q) || i.category.toLowerCase().includes(q));
  const grid = document.getElementById('menuGrid');
  if (!grid) return;
  if (!items.length) { grid.innerHTML = `<div class="empty-state" style="grid-column:1/-1;">No items found</div>`; return; }
  grid.innerHTML = items.map(item => {
    const stockStatus = item.stock === 0 ? 'out' : item.stock <= 5 ? 'low' : '';
    const unavail = item.status === 'unavailable' || item.stock === 0;
    return `<div class="menu-item ${unavail?'unavailable':''}" onclick="${unavail?'':(`addToCart(${item.id})`)}">
      <span class="menu-emoji">${item.emoji||'🍽'}</span>
      <div class="menu-cat-badge">${item.category}</div>
      <div class="menu-name">${item.name}</div>
      <div class="menu-price">₱${item.price.toFixed(2)}</div>
      <div class="menu-stock ${stockStatus}">${item.stock===0?'Out of stock':item.stock<=5?`Low stock: ${item.stock}`:`Stock: ${item.stock}`}</div>
      ${unavail?`<div style="position:absolute;top:8px;right:8px;font-size:10px;background:var(--surface3);padding:2px 6px;border-radius:4px;color:var(--text3);">${item.stock===0?'OUT':'OFF'}</div>`:''}
    </div>`;
  }).join('');
}
function addToCart(id) {
  const item = menuItems.find(i=>i.id===id);
  if (!item || item.stock===0 || item.status==='unavailable') return;
  const existing = cart.find(c=>c.id===id);
  if (existing) {
    if (existing.qty >= item.stock) { toast('Max stock reached!','error'); return; }
    existing.qty++;
  } else {
    cart.push({id, name:item.name, price:item.price, qty:1, emoji:item.emoji||'🍽'});
  }
  renderCart();
  toast(`${item.emoji||'🍽'} ${item.name} added!`, 'success');
}
function removeFromCart(id) {
  const idx = cart.findIndex(c=>c.id===id);
  if (idx===-1) return;
  if (cart[idx].qty > 1) cart[idx].qty--;
  else cart.splice(idx,1);
  renderCart();
}
function clearCart() {
  cart = [];
  const customerType = document.getElementById('customerType');
  if (customerType) customerType.value = 'regular';
  const cashInput = document.getElementById('cashInput');
  if (cashInput) cashInput.value = '';
  const paymentMethod = document.getElementById('paymentMethod');
  if (paymentMethod) paymentMethod.value = 'cash';
  const paymentRef = document.getElementById('paymentRef');
  if (paymentRef) paymentRef.value = '';
  const customerName = document.getElementById('customerName');
  if (customerName) customerName.value = '';
  renderCart();
}
function renderCart() {
  const el = document.getElementById('cartItems');
  if (!el) return;
  if (!cart.length) {
    el.innerHTML = `<div class="cart-empty"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg><span>Cart is empty</span></div>`;
  } else {
    el.innerHTML = cart.map(c=>`<div class="cart-item">
      <span style="font-size:24px;">${c.emoji}</span>
      <div class="cart-item-info">
        <div class="cart-item-name">${c.name}</div>
        <div class="cart-item-price">₱${(c.price*c.qty).toFixed(2)}</div>
      </div>
      <div class="qty-ctrl">
        <button class="qty-btn" onclick="removeFromCart(${c.id})">−</button>
        <span class="qty-num">${c.qty}</span>
        <button class="qty-btn" onclick="addToCart(${c.id})">+</button>
      </div>
    </div>`).join('');
  }
  renderCartFooter();
}
function renderCartFooter() {
  const totals = getCartTotals();
  const cash = parseFloat(document.getElementById('cashInput')?.value)||0;
  const method = document.getElementById('paymentMethod')?.value || 'cash';
  const paymentRef = document.getElementById('paymentRef');
  if (paymentRef) {
    if (method !== 'cash') {
      paymentRef.style.display = 'block';
      paymentRef.placeholder = method === 'e_wallet' ? 'E-Wallet reference' : 'Online transaction ID';
    } else {
      paymentRef.style.display = 'none';
      paymentRef.value = '';
    }
  }
  const change = cash - totals.total;
  const cartSubtotal = document.getElementById('cartSubtotal');
  const cartDiscount = document.getElementById('cartDiscount');
  const cartTotal = document.getElementById('cartTotal');
  if (cartSubtotal) cartSubtotal.textContent = '₱'+totals.subtotal.toFixed(2);
  if (cartDiscount) cartDiscount.textContent = '-₱'+totals.discount.toFixed(2);
  if (cartTotal) cartTotal.textContent = '₱'+totals.total.toFixed(2);
  const changeRow = document.getElementById('changeRow');
  if (changeRow) {
    if (method === 'cash' && cash > 0) {
      changeRow.style.display = 'flex';
      const cartChange = document.getElementById('cartChange');
      if (cartChange) {
        cartChange.textContent = (change>=0?'₱':'-₱')+Math.abs(change).toFixed(2);
        cartChange.style.color = change>=0?'var(--green)':'var(--red)';
      }
    } else {
      changeRow.style.display = 'none';
    }
  }
}
function processCheckout() {
  if (!cart.length) { toast('Cart is empty!','error'); return; }
  const totals = getCartTotals();
  const cash = parseFloat(document.getElementById('cashInput')?.value)||0;
  const method = document.getElementById('paymentMethod')?.value || 'cash';
  const paymentRef = document.getElementById('paymentRef')?.value.trim() || '';
  const customerName = document.getElementById('customerName')?.value.trim() || '';
  if (method === 'cash' && cash > 0 && cash < totals.total) { toast('Insufficient cash!','error'); return; }
  if (method !== 'cash' && paymentRef === '') { toast('Payment reference is required for e-wallet/online.','error'); return; }
  const table = document.getElementById('tableSelect')?.value || 'Walk-in';
  const now = new Date();
  cart.forEach(c => {
    const item = menuItems.find(i=>i.id===c.id);
    if (item) item.stock = Math.max(0, item.stock - c.qty);
  });
  saveMenu();
  const tx = {
    table,
    items:[...cart],
    subtotal: totals.subtotal,
    discount: totals.discount,
    total: totals.total,
    customer_type: totals.customer_type,
    discount_percent: totals.discount_percent,
    discount_label: totals.discount_label,
    cash: method === 'cash' ? cash : totals.total,
    change: method === 'cash' ? (cash-totals.total) : 0,
    payment_method: method,
    payment_reference: paymentRef,
    customer_name: customerName,
    time: now.toISOString()
  };

  fetch('api.php', {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'save_order', order: tx })
  })
  .then(async r => {
    const text = await r.text();
    try {
      return JSON.parse(text);
    } catch (err) {
      throw new Error('Invalid JSON response from server: ' + text.slice(0, 200));
    }
  })
  .then(result => {
    if (!result.success) {
      toast(result.message || 'Unable to save order to database.', 'error');
      return;
    }
    tx.id = result.order_id;
    transactions.push(tx);
    saveTx();
    showReceipt(tx);
    clearCart();
    renderMenuGrid();
    toast('✅ Order completed and saved to database!', 'success');
  })
  .catch(() => {
    toast('Network error: order was not saved to database.', 'error');
  });
}
function showReceipt(tx) {
  const d = new Date(tx.time);
  const discountLabel = tx.discount_label || CUSTOMER_DISCOUNT_MAP[tx.customer_type]?.label || 'Regular';
  document.getElementById('receiptDate').textContent = d.toLocaleString('en-PH');
  document.getElementById('receiptTable').textContent = tx.table;
  document.getElementById('receiptItems').innerHTML = tx.items.map(i=>`<div class="receipt-row"><span>${i.emoji} ${i.name} x${i.qty}</span><span>₱${(i.price*i.qty).toFixed(2)}</span></div>`).join('');
  document.getElementById('rcSubtotal').textContent = '₱'+tx.subtotal.toFixed(2);
  document.getElementById('rcDiscountLabel').textContent = discountLabel;
  document.getElementById('rcDiscount').textContent = '-₱'+tx.discount.toFixed(2);
  document.getElementById('rcTotal').textContent = '₱'+tx.total.toFixed(2);
  document.getElementById('rcPaymentMethod').textContent = tx.payment_method ? tx.payment_method.replace('_', ' ').replace(/\b\w/g, ch => ch.toUpperCase()) : 'Cash';
  document.getElementById('rcPaymentReference').textContent = tx.payment_reference || '—';
  document.getElementById('rcCustomerName').textContent = tx.customer_name || '—';
  document.getElementById('rcCash').textContent = '₱'+tx.cash.toFixed(2);
  document.getElementById('rcChange').textContent = '₱'+tx.change.toFixed(2);
  openModal('receiptModal');
}
function printReceipt() { window.print(); }
function renderMenuTable() {
  const body = document.getElementById('menuTableBody');
  body.innerHTML = menuItems.map(item => `<tr><td><span style="font-size:20px;margin-right:8px;">${item.emoji||'🍽'}</span>${item.name}</td><td><span class="tag tag-yellow">${item.category}</span></td><td>₱${item.price.toFixed(2)}</td><td>${item.stock}</td><td><span class="tag ${item.status==='available'&&item.stock>0?'tag-green':item.stock===0?'tag-red':'tag-red'}">${item.stock===0?'Out of Stock':item.status==='available'?'Available':'Unavailable'}</span></td><td style="display:flex;gap:6px;"><button class="btn btn-ghost btn-sm" onclick="openEditModal(${item.id})">Edit</button><button class="btn btn-danger btn-sm" onclick="deleteItem(${item.id})">Delete</button></td></tr>`).join('');
}
function openAddItemModal() {
  editingItemId = null;
  document.getElementById('itemModalTitle').textContent = 'Add Menu Item';
  ['fEmoji','fName','fPrice','fStock'].forEach(id=>document.getElementById(id).value='');
  document.getElementById('fCategory').value='Sizzling Favorites';
  document.getElementById('fStatus').value='available';
  openModal('itemModal');
}
function openEditModal(id) {
  const item = menuItems.find(i=>i.id===id);
  if (!item) return;
  editingItemId = id;
  document.getElementById('itemModalTitle').textContent = 'Edit Menu Item';
  document.getElementById('fEmoji').value = item.emoji||'';
  document.getElementById('fName').value = item.name;
  document.getElementById('fCategory').value = item.category;
  document.getElementById('fPrice').value = item.price;
  document.getElementById('fStock').value = item.stock;
  document.getElementById('fStatus').value = item.status;
  openModal('itemModal');
}
function saveItem() {
  const name = document.getElementById('fName').value.trim();
  const price = parseFloat(document.getElementById('fPrice').value);
  if (!name) { toast('Item name required!','error'); return; }
  if (!price || price<=0) { toast('Valid price required!','error'); return; }
  const data = {
    emoji: document.getElementById('fEmoji').value||'🍽',
    name, category: document.getElementById('fCategory').value,
    price, stock: parseInt(document.getElementById('fStock').value)||0,
    status: document.getElementById('fStatus').value
  };
  if (editingItemId) {
    const idx = menuItems.findIndex(i=>i.id===editingItemId);
    menuItems[idx] = {...menuItems[idx], ...data};
    toast('Item updated!','success');
  } else {
    menuItems.push({id:nextId++, ...data});
    toast('Item added!','success');
  }
  saveMenu(); closeModal('itemModal'); renderMenuTable(); renderMenuGrid();
}
function deleteItem(id) {
  if (!confirm('Delete this menu item?')) return;
  menuItems = menuItems.filter(i=>i.id!==id);
  saveMenu(); renderMenuTable(); renderMenuGrid();
  toast('Item deleted.','info');
}
function renderInventory() {
  const source = inventoryMode === 'items' ? menuItems : ingredients;
  const inStock = source.filter(i=>i.stock>5).length;
  const low = source.filter(i=>i.stock>0&&i.stock<=5).length;
  const out = source.filter(i=>i.stock===0).length;
  document.getElementById('invInStock').textContent = inStock;
  document.getElementById('invLowStock').textContent = low;
  document.getElementById('invOutStock').textContent = out;
  document.getElementById('invTotal').textContent = source.length;
  document.getElementById('invTotalSub').textContent = inventoryMode === 'items' ? 'Menu items' : 'Ingredients';
  document.getElementById('inventoryHeader').innerHTML = inventoryMode === 'items'
    ? '<tr><th>Item</th><th>Category</th><th>Stock Level</th><th>Status</th><th>Actions</th></tr>'
    : '<tr><th>Ingredient</th><th>Unit</th><th>Stock Level</th><th>Status</th><th>Actions</th></tr>';

  const body = document.getElementById('inventoryBody');
  body.innerHTML = source.map(item=>{
    const pct = Math.min(100, (item.stock/30)*100);
    const color = item.stock===0?'var(--red)':item.stock<=5?'var(--accent)':'var(--green)';
    const status = item.stock===0?'<span class="tag tag-red">Out of Stock</span>':item.stock<=5?'<span class="tag tag-yellow">Low Stock</span>':'<span class="tag tag-green">In Stock</span>';
    return inventoryMode === 'items'
      ? `<tr><td><span style="margin-right:8px;">${item.emoji||'🍽'}</span>${item.name}</td><td>${item.category}</td><td style="width:200px;"><div style="display:flex;align-items:center;gap:10px;"><div class="progress-bar" style="flex:1;"><div class="progress-fill" style="width:${pct}%;background:${color};"></div></div><span style="font-size:13px;font-weight:600;min-width:24px;">${item.stock}</span></div></td><td>${status}</td><td><button class="btn btn-ghost btn-sm" onclick="quickRestock(${item.id})">+ Add Stock</button></td></tr>`
      : `<tr><td>${item.name}</td><td>${item.unit}</td><td style="width:200px;"><div style="display:flex;align-items:center;gap:10px;"><div class="progress-bar" style="flex:1;"><div class="progress-fill" style="width:${pct}%;background:${color};"></div></div><span style="font-size:13px;font-weight:600;min-width:24px;">${item.stock}</span></div></td><td>${status}</td><td><button class="btn btn-ghost btn-sm" onclick="quickRestock(${item.id})">+ Add Stock</button></td></tr>`;
  }).join('');
}
function setInventoryMode(mode) {
  inventoryMode = mode;
  document.getElementById('inventoryModeItems').classList.toggle('active', mode==='items');
  document.getElementById('inventoryModeIngredients').classList.toggle('active', mode==='ingredients');
  renderInventory();
}
function openRestockModal() {
  const sel = document.getElementById('restockItem');
  const source = inventoryMode === 'items' ? menuItems : ingredients;
  document.getElementById('restockModalTitle').textContent = inventoryMode === 'items' ? 'Restock Item' : 'Restock Ingredient';
  sel.innerHTML = source.map(i=>`<option value="${i.id}">${inventoryMode === 'items' ? (i.emoji||'🍽') + ' ' + i.name : i.name} (Stock: ${i.stock})</option>`).join('');
  document.getElementById('restockQty').value = '';
  openModal('restockModal');
}
function quickRestock(id) {
  const sel = document.getElementById('restockItem');
  const source = inventoryMode === 'items' ? menuItems : ingredients;
  document.getElementById('restockModalTitle').textContent = inventoryMode === 'items' ? 'Restock Item' : 'Restock Ingredient';
  sel.innerHTML = source.map(i=>`<option value="${i.id}" ${i.id===id?'selected':''}>${inventoryMode === 'items' ? (i.emoji||'🍽') + ' ' + i.name : i.name} (Stock: ${i.stock})</option>`).join('');
  document.getElementById('restockQty').value = '';
  openModal('restockModal');
}
function doRestock() {
  const id = parseInt(document.getElementById('restockItem').value);
  const qty = parseInt(document.getElementById('restockQty').value)||0;
  if (qty <= 0) { toast('Enter valid quantity!','error'); return; }

  if (inventoryMode === 'items') {
    const item = menuItems.find(i=>i.id===id);
    if (item) {
      item.stock += qty;
      saveMenu();
      renderInventory();
      toast(`✅ ${item.name} restocked +${qty}!`,'success');
    }
  } else {
    const item = ingredients.find(i=>i.id===id);
    if (item) {
      const newStock = item.stock + qty;
      fetch('api.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'update_ingredient_stock',
          ingredient_id: id,
          stock: newStock,
          reason: 'Restock'
        })
      })
      .then(async r => {
        const text = await r.text();
        try {
          return JSON.parse(text);
        } catch (err) {
          throw new Error('Invalid JSON response from server: ' + text.slice(0, 200));
        }
      })
      .then(result => {
        if (result.success) {
          item.stock = newStock;
          renderInventory();
          toast(`✅ ${item.name} restocked +${qty}!`,'success');
        } else {
          toast(result.message || 'Failed to update stock', 'error');
        }
      })
      .catch(err => {
        toast('Network error: ' + err.message, 'error');
      });
    }
  }
  closeModal('restockModal');
}
function renderReports() {
  const today = new Date().toDateString();
  const todayTx = transactions.filter(t=>new Date(t.time).toDateString()===today);
  const revenue = todayTx.reduce((s,t)=>s+t.total,0);
  const discounts = todayTx.reduce((s,t)=>s+t.discount,0);
  const avg = todayTx.length ? revenue/todayTx.length : 0;
  document.getElementById('rptRevenue').textContent = '₱'+revenue.toFixed(2);
  document.getElementById('rptOrders').textContent = todayTx.length;
  document.getElementById('rptTxCount').textContent = transactions.length + ' total transactions';
  document.getElementById('rptAvg').textContent = '₱'+avg.toFixed(2);
  document.getElementById('rptDiscounts').textContent = '₱'+discounts.toFixed(2);
  const hourData = {};
  const now = new Date();
  for (let i=7;i>=0;i--) {
    const h = new Date(now-i*3600000).getHours();
    hourData[h] = {label: h+':00', val:0};
  }
  transactions.forEach(t=>{
    const h = new Date(t.time).getHours();
    if (hourData[h] !== undefined) hourData[h].val += t.total;
  });
  const hours = Object.values(hourData);
  const maxVal = Math.max(...hours.map(h=>h.val),1);
  document.getElementById('hourlyChart').innerHTML = hours.map(h=>`<div class="chart-bar-col"><div class="chart-val">${h.val?'₱'+h.val.toFixed(0):''}</div><div class="chart-bar" style="height:${(h.val/maxVal)*100}%;" title="₱${h.val.toFixed(2)}"></div><div class="chart-label">${h.label}</div></div>`).join('');
  const itemSales = {};
  transactions.forEach(t=>t.items.forEach(i=>{
    if (!itemSales[i.name]) itemSales[i.name]={name:i.name,emoji:i.emoji,qty:0,rev:0};
    itemSales[i.name].qty+=i.qty; itemSales[i.name].rev+=i.price*i.qty;
  }));
  const top = Object.values(itemSales).sort((a,b)=>b.qty-a.qty).slice(0,5);
  const maxQty = Math.max(...top.map(i=>i.qty),1);
  document.getElementById('topItemsChart').innerHTML = top.length ? top.map((i,idx)=>`<div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;"><span style="font-size:13px;color:var(--text3);min-width:16px;">${idx+1}</span><span style="font-size:18px;">${i.emoji||'🍽'}</span><div style="flex:1;"><div style="font-size:13px;font-weight:500;margin-bottom:4px;">${i.name}</div><div class="progress-bar"><div class="progress-fill" style="width:${(i.qty/maxQty)*100}%;background:var(--accent);"></div></div></div><span style="font-size:12px;color:var(--text2);">${i.qty} sold</span></div>`).join('') : '<div class="empty-state">No sales data yet</div>';
  const recent = [...transactions].reverse().slice(0,10);
  document.getElementById('txTable').innerHTML = recent.map(t=>`<tr><td>#${t.id}</td><td>${t.table}</td><td>${t.items.map(i=>`${i.emoji} ${i.name}(${i.qty})`).join(', ')}</td><td>₱${t.subtotal.toFixed(2)}</td><td style="color:var(--green);">${t.discount>0?'-₱'+t.discount.toFixed(2):'—'}</td><td style="color:var(--accent);font-weight:600;">₱${t.total.toFixed(2)}</td><td style="color:var(--text3);">${new Date(t.time).toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit'})}</td></tr>`).join('') || '<tr><td colspan="7" class="empty-state">No transactions yet</td></tr>';
}
function clearSalesData() {
  if (!confirm('Clear all sales data?')) return;
  transactions = []; saveTx(); renderReports(); toast('Sales data cleared.','info');
}
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(m=>{ m.addEventListener('click',e=>{ if(e.target===m) m.classList.remove('open'); }); });
function toast(msg, type='info') {
  const el = document.createElement('div');
  el.className = `toast ${type}`;
  el.innerHTML = `<span>${msg}</span>`;
  document.getElementById('toastContainer').appendChild(el);
  setTimeout(()=>el.remove(),3000);
}
function toggleIngredientSection() {
  const content = document.getElementById('ingredientSectionContent');
  const toggle = document.querySelector('.sidebar-section-toggle');
  const isOpen = content.style.display !== 'none';
  content.style.display = isOpen ? 'none' : 'block';
  toggle.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
}
function addIngredient(event) {
  event.preventDefault();
  const name = document.getElementById('ingName').value.trim();
  const unit = document.getElementById('ingUnit').value;
  const stock = parseFloat(document.getElementById('ingStock').value);
  if (!name) { toast('Ingredient name required!','error'); return; }
  if (!stock || stock < 0) { toast('Valid stock quantity required!','error'); return; }

  const ingredientData = {
    name: name,
    unit: unit,
    stock: stock,
    status: 'available'
  };

  fetch('api.php', {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'save_ingredient', ingredient: ingredientData })
  })
  .then(async r => {
    const text = await r.text();
    try {
      return JSON.parse(text);
    } catch (err) {
      throw new Error('Invalid JSON response from server: ' + text.slice(0, 200));
    }
  })
  .then(result => {
    if (result.success) {
      ingredients.push({ id: result.ingredient_id, ...ingredientData });
      document.getElementById('ingredientForm').reset();
      toast('✅ Ingredient added to database!', 'success');
      if (inventoryMode === 'ingredients') renderInventory();
    } else {
      toast(result.message || 'Failed to save ingredient', 'error');
    }
  })
  .catch(err => {
    toast('Network error: ' + err.message, 'error');
  });
}

document.addEventListener('change', function(event) {
  if (event.target && event.target.id === 'customerType') {
    renderCartFooter();
  }
  if (event.target && event.target.id === 'paymentMethod') {
    renderCartFooter();
  }
});
document.addEventListener('input', function(event) {
  if (event.target && event.target.id === 'cashInput') {
    renderCartFooter();
  }
});

renderMenuGrid();
renderCartFooter();
</script>
</body>
</html>