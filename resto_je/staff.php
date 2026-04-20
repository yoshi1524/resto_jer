<?php
require 'config.php';
$conn = dbConnect();
ensureSchema($conn);
requireLogin();

requireRole('staff', 'manager', 'admin');
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Countryside POS - Staff Dashboard</title>
<link rel="icon" href="assets/cside.png" type="image/png">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
<style>
<?php echo str_replace('  ', '', getSharedStyles()); ?>
</style>
</head>
<body>

<div class="app">
  <aside class="sidebar">
    <div class="logo">
      <div class="logo-text">Countryside</div>
      <div class="logo-sub">POS Station</div>
    </div>
    <nav class="nav">
      <div class="nav-item active" onclick="showPage('pos')">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        Order &amp; Checkout
      </div>
    </nav>
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

    <!-- POS PAGE -->
    <div id="page-pos" class="page active" style="padding:0;flex-direction:column;">
      <div class="topbar">
        <div>
          <div class="topbar-title">Order &amp; Checkout</div>
        </div>
        <div class="topbar-actions">
          <div class="tabs">
            <div class="tab active" onclick="filterMenuCat('all',this)">All</div>
          </div>
          <div class="search-wrap" style="width:200px;">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input class="search-input" id="menuSearch" placeholder="Search item..." oninput="renderMenuGrid()"/>
          </div>
        </div>
      </div>

      <div style="flex:1;display:flex;overflow:hidden;padding:20px;gap:20px;">
        <div class="menu-panel">
          <div class="category-tabs" id="categoryTabs"></div>
          <div class="menu-grid" id="menuGrid"></div>
        </div>

        <div class="cart-panel">
          <div class="cart-header">
            <div class="cart-header-row">
              <div class="cart-title">🧾 Current Order</div>
              <button class="btn btn-ghost btn-sm" onclick="clearCart()">Clear</button>
            </div>
            <div class="table-selector">
              <label>Table:</label>
              <select id="tableSelect">
                <option value="Takeout">Takeout</option>
                <option value="Table 1">Table 1</option>
                <option value="Table 2">Table 2</option>
                <option value="Table 3">Table 3</option>
                <option value="Table 4">Table 4</option>
                <option value="Table 5">Table 5</option>
              </select>
            </div>
          </div>

          <div class="cart-items" id="cartItems"></div>

          <div class="cart-footer">
            <div class="discount-row">
              <select class="input-sm" id="customerType" onchange="renderCartFooter()">
                <option value="regular">Regular</option>
                <option value="pwd">PWD (20% off)</option>
                <option value="senior">Senior Citizen (20% off)</option>
              </select>
              <input class="input-sm" id="cashInput" type="number" placeholder="Cash received" oninput="renderCartFooter()"/>
            </div>
            <div class="discount-row">
              <select class="input-sm" id="paymentMethod" onchange="renderCartFooter()">
                <option value="cash">Cash</option>
                <option value="e_wallet">E-Wallet</option>
                <option value="online">Online</option>
              </select>
              <input class="input-sm" id="paymentRef" type="text" placeholder="Reference #" oninput="renderCartFooter()" style="display:none;"/>
            </div>
            <div class="discount-row">
              <input class="input-sm" id="customerName" type="text" placeholder="Customer name (optional)"/>
            </div>
            <div class="cart-line"><span>Subtotal</span><span id="cartSubtotal">₱0.00</span></div>
            <div class="cart-line"><span>Discount</span><span id="cartDiscount" style="color:var(--green)">-₱0.00</span></div>
            <div class="cart-total"><span>TOTAL</span><span id="cartTotal">₱0.00</span></div>
            <div class="cart-line" id="changeRow" style="display:none;">
              <span>Change</span><span id="cartChange" style="color:var(--green);">₱0.00</span>
            </div>
            <button class="btn btn-accent" style="width:100%;justify-content:center;padding:12px;" onclick="processCheckout()">
              <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              Process Payment
            </button>
          </div>
        </div>
      </div>
    </div>

  </div><!-- /.main -->
</div><!-- /.app -->

<!-- Receipt Modal -->
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

<!-- FIX: Removed the huge inline <script> block that loaded menu from localStorage.
     adminscript.js handles everything, fetching menu from DB on load. -->
<script src="script/adminscript.js"></script>
</body>
</html>