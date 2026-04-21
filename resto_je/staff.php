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

/* ── Payment Confirmation Modal ─────────────────────────────── */
.pay-confirm-modal { width: 420px; }
.pay-confirm-header { text-align: center; padding-bottom: 20px; border-bottom: 1px solid var(--border); margin-bottom: 20px; }
.pay-confirm-method-icon { font-size: 48px; margin-bottom: 8px; }
.pay-confirm-method-label { font-size: 12px; color: var(--text3); text-transform: uppercase; letter-spacing: 1.5px; font-weight: 600; }
.pay-confirm-total { font-family: 'Syne', sans-serif; font-size: 36px; font-weight: 800; color: var(--accent); margin: 4px 0; }
.pay-confirm-table-tag { display: inline-block; background: var(--surface3); color: var(--text2); font-size: 12px; padding: 4px 12px; border-radius: 20px; margin-top: 4px; }

.pay-detail-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--border); font-size: 13px; }
.pay-detail-row:last-child { border-bottom: none; }
.pay-detail-label { color: var(--text3); }
.pay-detail-value { color: var(--text); font-weight: 500; }
.pay-detail-value.accent { color: var(--accent); font-weight: 700; }
.pay-detail-value.green  { color: var(--green);  font-weight: 700; }
.pay-detail-value.red    { color: var(--red);    font-weight: 700; }

.pay-items-list { background: var(--surface2); border-radius: 10px; padding: 12px 14px; margin: 14px 0; max-height: 140px; overflow-y: auto; }
.pay-item-row { display: flex; justify-content: space-between; font-size: 12px; padding: 3px 0; color: var(--text2); }
.pay-item-row span:first-child { flex: 1; }
.pay-item-row span:last-child  { color: var(--text); font-weight: 500; }

.pay-ref-display { background: var(--surface2); border: 1px solid var(--border); border-radius: 10px; padding: 12px 14px; margin: 14px 0; }
.pay-ref-label { font-size: 11px; color: var(--text3); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; }
.pay-ref-value { font-size: 14px; font-weight: 600; color: var(--accent); word-break: break-all; }

.pay-cashless-info { background: rgba(91,191,138,0.08); border: 1px solid rgba(91,191,138,0.2); border-radius: 10px; padding: 12px 14px; margin: 14px 0; font-size: 12px; color: var(--green); display: flex; align-items: flex-start; gap: 8px; }
.pay-cashless-info svg { width: 16px; height: 16px; flex-shrink: 0; margin-top: 1px; }

.pay-confirm-actions { display: flex; gap: 10px; margin-top: 20px; }
.pay-confirm-actions .btn { flex: 1; justify-content: center; padding: 13px; font-size: 14px; font-weight: 600; }

.pay-warn { background: rgba(224,92,92,0.1); border: 1px solid rgba(224,92,92,0.3); border-radius: 10px; padding: 10px 14px; font-size: 12px; color: var(--red); margin-top: 6px; display: none; }
.pay-warn.visible { display: block; }
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
    <div id="page-pos" class="page active" style="padding:0;flex-direction:column;">
      <div class="topbar">
        <div><div class="topbar-title">Order &amp; Checkout</div></div>
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
              <div class="cart-title">Current Order</div>
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
            <!-- Customer type / discount -->
            <div class="discount-row">
              <select class="input-sm" id="customerType" onchange="renderCartFooter()">
                <option value="regular">Regular</option>
                <option value="pwd">PWD (20% off)</option>
                <option value="senior">Senior Citizen (20% off)</option>
              </select>
            </div>

            <!-- Payment method selector -->
            <div class="discount-row">
              <select class="input-sm" id="paymentMethod" onchange="onPaymentMethodChange()">
                <option value="cash">Cash</option>
                <option value="e_wallet">E-Wallet (GCash / Maya)</option>
                <option value="online">Online Bank Transfer</option>
              </select>
            </div>

            <!-- Cash amount input — shown only for cash -->
            <div id="cashFields" style="margin-top:4px;">
              <div class="discount-row">
                <input class="input-sm" id="cashInput" type="number" placeholder="Cash received *" min="0" oninput="renderCartFooter()"/>
              </div>
              <div id="payWarnCash" class="pay-warn"></div>
            </div>

            <!-- Reference number input — shown only for cashless -->
            <div id="cashlessFields" style="display:none;margin-top:4px;">
              <div class="discount-row">
                <input class="input-sm" id="paymentRef" type="text" placeholder="Reference / transaction no. *"/>
              </div>
              <div id="payWarnRef" class="pay-warn"></div>
            </div>

            <div class="discount-row" style="margin-top:4px;">
              <input class="input-sm" id="customerName" type="text" placeholder="Customer name (optional)"/>
            </div>

            <div class="cart-line"><span>Subtotal</span><span id="cartSubtotal">₱0.00</span></div>
            <div class="cart-line"><span>Discount</span><span id="cartDiscount" style="color:var(--green)">-₱0.00</span></div>
            <div class="cart-total"><span>TOTAL</span><span id="cartTotal">₱0.00</span></div>
            <div class="cart-line" id="changeRow" style="display:none;">
              <span>Change</span><span id="cartChange" style="color:var(--green);">₱0.00</span>
            </div>

            <!-- Opens confirmation modal — does NOT directly complete the order -->
            <button class="btn btn-accent" style="width:100%;justify-content:center;padding:12px;" onclick="openPaymentConfirm()">
              <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              Review &amp; Confirm Payment
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ── PAYMENT CONFIRMATION MODAL ──────────────────────────────── -->
<div class="modal-overlay" id="payConfirmModal">
  <div class="modal pay-confirm-modal">
    <div class="pay-confirm-header">
      <div class="pay-confirm-method-icon" id="pcMethodIcon">💵</div>
      <div class="pay-confirm-method-label" id="pcMethodLabel">Cash Payment</div>
      <div class="pay-confirm-total" id="pcTotal">₱0.00</div>
      <div class="pay-confirm-table-tag" id="pcTable">Takeout</div>
    </div>

    <div class="pay-items-list" id="pcItemsList"></div>

    <!-- Cash details panel -->
    <div id="pcCashDetails">
      <div class="pay-detail-row"><span class="pay-detail-label">Subtotal</span><span class="pay-detail-value" id="pcSubtotal">₱0.00</span></div>
      <div class="pay-detail-row"><span class="pay-detail-label" id="pcDiscountLabel">Discount</span><span class="pay-detail-value green" id="pcDiscount">-₱0.00</span></div>
      <div class="pay-detail-row" style="font-size:15px;"><span class="pay-detail-label"><b>Total</b></span><span class="pay-detail-value accent" id="pcTotalRow">₱0.00</span></div>
      <div class="pay-detail-row"><span class="pay-detail-label">Cash Received</span><span class="pay-detail-value" id="pcCashReceived">₱0.00</span></div>
      <div class="pay-detail-row"><span class="pay-detail-label">Change</span><span class="pay-detail-value green" id="pcChange">₱0.00</span></div>
      <div class="pay-detail-row"><span class="pay-detail-label">Customer</span><span class="pay-detail-value" id="pcCustomer">Walk-in</span></div>
    </div>

    <!-- Cashless details panel -->
    <div id="pcCashlessDetails" style="display:none;">
      <div class="pay-detail-row"><span class="pay-detail-label">Subtotal</span><span class="pay-detail-value" id="pcSubtotalCL">₱0.00</span></div>
      <div class="pay-detail-row"><span class="pay-detail-label" id="pcDiscountLabelCL">Discount</span><span class="pay-detail-value green" id="pcDiscountCL">-₱0.00</span></div>
      <div class="pay-detail-row" style="font-size:15px;"><span class="pay-detail-label"><b>Total</b></span><span class="pay-detail-value accent" id="pcTotalCL">₱0.00</span></div>
      <div class="pay-ref-display">
        <div class="pay-ref-label">Reference / Transaction No.</div>
        <div class="pay-ref-value" id="pcRefNumber">—</div>
      </div>
      <div class="pay-cashless-info">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span id="pcCashlessNote">Verify that payment has been received before confirming.</span>
      </div>
      <div class="pay-detail-row"><span class="pay-detail-label">Customer</span><span class="pay-detail-value" id="pcCustomerCL">Walk-in</span></div>
    </div>

    <div class="pay-confirm-actions">
      <button class="btn btn-ghost" onclick="closeModal('payConfirmModal')">← Back</button>
      <button class="btn btn-accent" onclick="confirmAndPay()">✓ Confirm &amp; Complete</button>
    </div>
  </div>
</div>

<!-- ── RECEIPT MODAL ──────────────────────────────────────────── -->
<div class="modal-overlay" id="receiptModal">
  <div class="modal receipt-modal">
    <div class="receipt-header">
      <div class="receipt-logo">🍽 Countryside</div>
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
<script src="script/adminscript.js"></script>

<script>
// ── Payment method toggle ────────────────────────────────────────
function onPaymentMethodChange() {
  const method = document.getElementById('paymentMethod').value;
  document.getElementById('cashFields').style.display     = method === 'cash' ? 'block' : 'none';
  document.getElementById('cashlessFields').style.display = method !== 'cash' ? 'block' : 'none';
  document.getElementById('payWarnCash').classList.remove('visible');
  document.getElementById('payWarnRef').classList.remove('visible');
  renderCartFooter();
}

// ── Validate → populate confirm modal → open it ──────────────────
function openPaymentConfirm() {
  if (!cart.length) { toast('Cart is empty!', 'error'); return; }

  const method   = document.getElementById('paymentMethod').value;
  const cash     = parseFloat(document.getElementById('cashInput').value) || 0;
  const payRef   = document.getElementById('paymentRef').value.trim();
  const disc     = getDiscountDetails();
  const subtotal = cart.reduce((s, c) => s + c.price * c.qty, 0);
  const discount = subtotal * (disc.percent / 100);
  const total    = subtotal - discount;
  const warnCash = document.getElementById('payWarnCash');
  const warnRef  = document.getElementById('payWarnRef');

  // ── Validation ─────────────────────────────────────────────────
  if (method === 'cash') {
    if (cash <= 0) {
      warnCash.textContent = '⚠️ Please enter the cash amount received.';
      warnCash.classList.add('visible');
      document.getElementById('cashInput').focus();
      return;
    }
    if (cash < total) {
      warnCash.textContent = `⚠️ ₱${cash.toFixed(2)} received is less than the total of ₱${total.toFixed(2)}.`;
      warnCash.classList.add('visible');
      document.getElementById('cashInput').focus();
      return;
    }
    warnCash.classList.remove('visible');
  } else {
    if (!payRef) {
      warnRef.textContent = '⚠️ A reference / transaction number is required for cashless payment.';
      warnRef.classList.add('visible');
      document.getElementById('paymentRef').focus();
      return;
    }
    warnRef.classList.remove('visible');
  }

  // ── Populate modal ─────────────────────────────────────────────
  const table    = document.getElementById('tableSelect').value;
  const custName = document.getElementById('customerName').value.trim() || 'Walk-in';
  const change   = method === 'cash' ? (cash - total) : 0;
  const discLabel = disc.percent > 0 ? `Discount — ${disc.label}` : 'Discount';

  const ICONS = { cash: '💵', e_wallet: '📱', online: '🏦' };
  const LABELS = { cash: 'Cash Payment', e_wallet: 'E-Wallet · GCash / Maya', online: 'Online Bank Transfer' };

  document.getElementById('pcMethodIcon').textContent  = ICONS[method]  || '💵';
  document.getElementById('pcMethodLabel').textContent = LABELS[method] || 'Payment';
  document.getElementById('pcTotal').textContent       = '₱' + total.toFixed(2);
  document.getElementById('pcTable').textContent       = table;

  document.getElementById('pcItemsList').innerHTML = cart.map(c =>
    `<div class="pay-item-row">
      <span>${c.emoji || '🍽'} ${c.name} ×${c.qty}</span>
      <span>₱${(c.price * c.qty).toFixed(2)}</span>
    </div>`
  ).join('');

  if (method === 'cash') {
    document.getElementById('pcCashDetails').style.display    = 'block';
    document.getElementById('pcCashlessDetails').style.display = 'none';
    document.getElementById('pcSubtotal').textContent      = '₱' + subtotal.toFixed(2);
    document.getElementById('pcDiscountLabel').textContent = discLabel;
    document.getElementById('pcDiscount').textContent      = '-₱' + discount.toFixed(2);
    document.getElementById('pcTotalRow').textContent      = '₱' + total.toFixed(2);
    document.getElementById('pcCashReceived').textContent  = '₱' + cash.toFixed(2);
    document.getElementById('pcChange').textContent        = '₱' + change.toFixed(2);
    document.getElementById('pcCustomer').textContent      = custName;
  } else {
    document.getElementById('pcCashDetails').style.display    = 'none';
    document.getElementById('pcCashlessDetails').style.display = 'block';
    document.getElementById('pcSubtotalCL').textContent       = '₱' + subtotal.toFixed(2);
    document.getElementById('pcDiscountLabelCL').textContent  = discLabel;
    document.getElementById('pcDiscountCL').textContent       = '-₱' + discount.toFixed(2);
    document.getElementById('pcTotalCL').textContent          = '₱' + total.toFixed(2);
    document.getElementById('pcRefNumber').textContent        = payRef;
    document.getElementById('pcCustomerCL').textContent       = custName;
    document.getElementById('pcCashlessNote').textContent     = method === 'e_wallet'
      ? `Confirm that ₱${total.toFixed(2)} has been received in your GCash or Maya account before completing this order.`
      : `Confirm that ₱${total.toFixed(2)} has been credited to your bank account before completing this order.`;
  }

  openModal('payConfirmModal');
}


function confirmAndPay() {
  closeModal('payConfirmModal');
  processCheckout();   
}
</script>
</body>
</html>