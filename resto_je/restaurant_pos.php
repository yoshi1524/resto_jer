<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: rbac.php?redirect=restaurant_pos.php');
    exit;
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>CountrysidePOS — Restaurant Point of Sale</title>
<link rel="icon" href="assets/cside.png" type="image/png">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
<style>
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

  /* LAYOUT */
  .app { display: flex; height: 100vh; overflow: hidden; }
  .sidebar { width: 220px; min-width: 220px; background: var(--surface); border-right: 1px solid var(--border); display: flex; flex-direction: column; padding: 0; }
  .main { flex: 1; overflow: hidden; display: flex; flex-direction: column; }

  /* SIDEBAR */
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

  /* TOPBAR */
  .topbar { padding: 16px 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; background: var(--surface); flex-shrink: 0; }
  .topbar-title { font-family: 'Syne', sans-serif; font-size: 20px; font-weight: 700; }
  .topbar-actions { display: flex; gap: 10px; align-items: center; }
  .badge { background: var(--accent); color: #000; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 20px; }

  /* BUTTONS */
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

  /* PAGES */
  .page { display: none; flex: 1; overflow: auto; padding: 24px; flex-direction: column; gap: 20px; }
  .page.active { display: flex; }

  /* CARDS */
  .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 20px; }
  .card-title { font-family: 'Syne', sans-serif; font-size: 14px; font-weight: 700; color: var(--text2); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 16px; }

  /* GRID */
  .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
  .grid-4 { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; }

  /* STAT CARDS */
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

  /* POS PAGE */
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

  /* CART */
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

  /* TABLE */
  table { width: 100%; border-collapse: collapse; font-size: 13px; }
  thead th { padding: 10px 12px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: var(--text3); border-bottom: 1px solid var(--border); }
  tbody td { padding: 12px 12px; border-bottom: 1px solid var(--border); color: var(--text2); vertical-align: middle; }
  tbody tr:last-child td { border-bottom: none; }
  tbody tr:hover td { background: var(--surface2); color: var(--text); }
  .tag { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 500; }
  .tag-green { background: rgba(91,191,138,0.15); color: var(--green); }
  .tag-yellow { background: rgba(232,160,69,0.15); color: var(--accent); }
  .tag-red { background: rgba(224,92,92,0.15); color: var(--red); }

  /* MODALS */
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

  /* RECEIPT */
  .receipt-modal { width: 360px; }
  .receipt-header { text-align: center; margin-bottom: 16px; }
  .receipt-logo { font-family: 'Syne', sans-serif; font-size: 22px; font-weight: 800; color: var(--accent); }
  .receipt-divider { border: none; border-top: 1px dashed var(--border); margin: 12px 0; }
  .receipt-row { display: flex; justify-content: space-between; font-size: 13px; padding: 3px 0; }
  .receipt-total { font-family: 'Syne', sans-serif; font-size: 18px; font-weight: 800; display: flex; justify-content: space-between; padding: 4px 0; }
  .receipt-total span:last-child { color: var(--accent); }
  .receipt-payment { text-align: center; margin-top: 14px; padding: 12px; background: var(--surface2); border-radius: var(--radius-sm); }
  .receipt-change { font-family: 'Syne', sans-serif; font-size: 20px; font-weight: 800; color: var(--green); }

  /* CHART */
  .chart-bar-wrap { display: flex; align-items: flex-end; gap: 6px; height: 120px; padding-top: 8px; }
  .chart-bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px; height: 100%; justify-content: flex-end; }
  .chart-bar { width: 100%; border-radius: 4px 4px 0 0; background: var(--accent); opacity: 0.8; min-height: 4px; transition: height .5s ease; }
  .chart-bar:hover { opacity: 1; }
  .chart-label { font-size: 10px; color: var(--text3); }
  .chart-val { font-size: 10px; color: var(--text2); }

  /* SEARCH */
  .search-wrap { position: relative; }
  .search-wrap svg { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: var(--text3); pointer-events: none; }
  .search-input { width: 100%; background: var(--surface2); border: 1px solid var(--border); color: var(--text); padding: 9px 12px 9px 34px; border-radius: var(--radius-sm); font-size: 13px; font-family: 'DM Sans', sans-serif; }
  .search-input:focus { outline: none; border-color: var(--accent); }

  /* SCROLLBAR */
  ::-webkit-scrollbar { width: 5px; height: 5px; }
  ::-webkit-scrollbar-track { background: transparent; }
  ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

  /* TOAST */
  .toast-container { position: fixed; bottom: 24px; right: 24px; display: flex; flex-direction: column; gap: 8px; z-index: 200; }
  .toast { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 12px 16px; font-size: 13px; color: var(--text); box-shadow: var(--shadow); animation: slideIn .3s ease; display: flex; align-items: center; gap: 8px; min-width: 200px; }
  .toast.success { border-left: 3px solid var(--green); }
  .toast.error { border-left: 3px solid var(--red); }
  .toast.info { border-left: 3px solid var(--accent); }
  @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

  /* TABS */
  .tabs { display: flex; gap: 2px; background: var(--surface2); border-radius: var(--radius-sm); padding: 3px; width: fit-content; }
  .tab { padding: 7px 16px; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; color: var(--text2); transition: all .2s; }
  .tab.active { background: var(--accent); color: #000; }

  /* MISC */
  .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
  .section-title { font-family: 'Syne', sans-serif; font-size: 18px; font-weight: 700; }
  .empty-state { text-align: center; padding: 40px; color: var(--text3); }
  .color-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
  select.form-input { cursor: pointer; }
  .progress-bar { background: var(--surface3); border-radius: 4px; height: 6px; overflow: hidden; }
  .progress-fill { height: 100%; border-radius: 4px; transition: width .5s ease; }
</style>
</head>
<body>

<div class="app">
  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="logo">
      <div class="logo-text">Countryside</div>
      <div class="logo-sub">v1.0 Capstone</div>
    </div>
    <nav class="nav">
      <div class="nav-item active" onclick="showPage('pos')">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        Order & Checkout
      </div>
      <div class="nav-item" onclick="showPage('menu')">
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
      <div class="nav-item" onclick="showPage('orders')">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Order History
      </div>
    </nav>
    <div class="sidebar-footer">
      <div class="clock" id="clock">--:--</div>
      <div class="date-txt" id="dateLabel">Loading...</div>
    </div>
  </aside>

  <!-- MAIN -->
  <div class="main">

    <!-- POS PAGE -->
    <div id="page-pos" class="page active" style="padding:0; flex-direction:column;">
      <div class="topbar">
        <div>
          <div class="topbar-title">Order & Checkout</div>
          <div style="font-size:13px;color:var(--text3);margin-top:4px;">Signed in as <?= htmlspecialchars($user['username']) ?></div>
        </div>
        <div class="topbar-actions">
          <div class="tabs">
            <div class="tab active" onclick="filterMenuCat('all', this)">All</div>
          </div>
          <div class="search-wrap" style="width:200px;">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input class="search-input" id="menuSearch" placeholder="Search item..." oninput="renderMenuGrid()"/>
          </div>
          <a class="btn btn-ghost btn-sm" href="rbac.php" target="_blank">Manage Users</a>
        </div>
      </div>
      <div style="flex:1; display:flex; overflow:hidden; padding:20px; gap:20px;">
        <div class="menu-panel">
          <div class="category-tabs" id="categoryTabs"></div>
          <div class="menu-grid" id="menuGrid"></div>
        </div>
        <!-- CART -->
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
                <option value="pwd">PWD</option>
                <option value="senior">Senior</option>
              </select>
              <input class="input-sm" id="cashInput" type="number" placeholder="Cash received" oninput="renderCartFooter()"/>
            </div>
            <div class="discount-row">
              <select class="input-sm" id="paymentMethod" onchange="renderCartFooter()">
                <option value="cash">Cash</option>
                <option value="e_wallet">E-Wallet</option>
                <option value="online">Online</option>
              </select>
              <input class="input-sm" id="paymentRef" type="text" placeholder="Payment reference (for e-wallet/online)" oninput="renderCartFooter()" style="display:none;"/>
            </div>
            <div class="discount-row">
              <input class="input-sm" id="customerName" type="text" placeholder="Customer name (optional)" oninput="renderCartFooter()"/>
            </div>
            <div class="cart-line"><span>Subtotal</span><span id="cartSubtotal">₱0.00</span></div>
            <div class="cart-line"><span>Discount</span><span id="cartDiscount" style="color:var(--green)">-₱0.00</span></div>
            <div class="cart-total"><span>TOTAL</span><span id="cartTotal">₱0.00</span></div>
            <div class="cart-line" id="changeRow" style="display:none;"><span>Change</span><span id="cartChange" style="color:var(--green);">₱0.00</span></div>
            <button class="btn btn-accent" style="width:100%; justify-content:center; padding:12px;" onclick="processCheckout()">
              <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              Process Payment
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MENU MANAGEMENT PAGE -->
    <div id="page-menu" class="page" style="flex-direction:column;">
      <div class="section-header">
        <div class="section-title">Menu Management</div>
        <button class="btn btn-accent" onclick="openAddItemModal()">+ Add Item</button>
      </div>
      <div class="card" style="flex:1; overflow:auto;">
        <table id="menuTable">
          <thead>
            <tr>
              <th>Item</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th>
            </tr>
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
        <div class="card" style="overflow:auto;">
          <div class="card-title">Sales by Hour</div>
          <div class="chart-bar-wrap" id="hourlyChart"></div>
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

    <!-- ORDER HISTORY PAGE -->
    <div id="page-orders" class="page" style="flex-direction:column;">
      <div class="section-header">
        <div class="section-title">Order History</div>
        <span class="badge" id="orderCount">0 orders</span>
      </div>
      <div class="card" style="flex:1; overflow:auto;">
        <table>
          <thead><tr><th>#</th><th>Table</th><th>Items</th><th>Total</th><th>Discount</th><th>Change</th><th>Time</th></tr></thead>
          <tbody id="orderHistoryBody"></tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<!-- MODALS -->
<!-- Add/Edit Item Modal -->
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

<!-- Restock Modal -->
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

<!-- Toast container -->
<div class="toast-container" id="toastContainer"></div>

<script>
// ============ STATE ============
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
const CUSTOMER_DISCOUNTS = {
  regular: { percent: 0, label: 'Regular Discount' },
  pwd: { percent: 20, label: 'PWD Discount (20%)' },
  senior: { percent: 20, label: 'Senior Citizen Discount (20%)' }
};
let editingItemId = null;
let activeCategory = 'all';
let inventoryMode = 'items';
let nextId = Math.max(...menuItems.map(i=>i.id), 0) + 1;

function saveMenu() { localStorage.setItem('pos_menu', JSON.stringify(menuItems)); }
function saveIngredients() { /* Ingredients are now saved to database */ }
function saveTx() { localStorage.setItem('pos_tx', JSON.stringify(transactions)); }

function loadIngredients() {
  return fetch('api.php', {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'get_ingredients' })
  })
  .then(async r => {
    const text = await r.text();
    try { return JSON.parse(text); } catch (err) {
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

// ============ NAV ============
function showPage(page) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  document.getElementById('page-' + page).classList.add('active');
  event.currentTarget.classList.add('active');
  if (page === 'menu') renderMenuTable();
  if (page === 'inventory') {
    loadIngredients().then(() => renderInventory()).catch(err => {
      toast('Failed to load ingredients: ' + err.message, 'error');
      renderInventory();
    });
  }
  if (page === 'reports') renderReports();
  if (page === 'orders') renderOrderHistory();
  if (page === 'pos') renderMenuGrid();
}

// ============ CLOCK ============
function updateClock() {
  const now = new Date();
  document.getElementById('clock').textContent = now.toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit'});
  document.getElementById('dateLabel').textContent = now.toLocaleDateString('en-PH',{weekday:'short',month:'short',day:'numeric'});
}
setInterval(updateClock, 1000); updateClock();

// ============ MENU GRID ============
function getCategories() {
  const cats = [...new Set(menuItems.map(i=>i.category))];
  return cats;
}

function renderCategoryTabs() {
  const cats = getCategories();
  const tabsEl = document.getElementById('categoryTabs');
  const posTabsEl = document.querySelector('.tabs');
  tabsEl.innerHTML = `<div class="cat-tab ${activeCategory==='all'?'active':''}" onclick="filterMenuCat('all',this)">All</div>` +
    cats.map(c => `<div class="cat-tab ${activeCategory===c?'active':''}" onclick="filterMenuCat('${c}',this)">${c}</div>`).join('');
  posTabsEl.innerHTML = `<div class="tab ${activeCategory==='all'?'active':''}" onclick="filterMenuCat('all',this)">All</div>` +
    cats.map(c => `<div class="tab ${activeCategory===c?'active':''}" onclick="filterMenuCat('${c}',this)">${c}</div>`).join('');
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

// ============ CART ============
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

function getCustomerDiscountDetails() {
  const customerType = document.getElementById('customerType').value || 'regular';
  return {
    customer_type: customerType,
    ...(CUSTOMER_DISCOUNTS[customerType] || CUSTOMER_DISCOUNTS.regular)
  };
}

function clearCart() {
  cart = [];
  document.getElementById('customerType').value='regular';
  document.getElementById('cashInput').value='';
  document.getElementById('paymentMethod').value='cash';
  document.getElementById('paymentRef').value='';
  document.getElementById('customerName').value='';
  renderCart();
}

function renderCart() {
  const el = document.getElementById('cartItems');
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
  const subtotal = cart.reduce((s,c)=>s+c.price*c.qty,0);
  const discountDetails = getCustomerDiscountDetails();
  const discPct = discountDetails.percent;
  const disc = subtotal * (discPct/100);
  const total = subtotal - disc;
  const cash = parseFloat(document.getElementById('cashInput').value)||0;
  const method = document.getElementById('paymentMethod').value;
  const paymentRef = document.getElementById('paymentRef');
  if (method !== 'cash') {
    paymentRef.style.display = 'block';
    paymentRef.placeholder = method === 'e_wallet' ? 'E-Wallet reference' : 'Online transaction ID';
  } else {
    paymentRef.style.display = 'none';
    paymentRef.value = '';
  }
  const change = cash - total;
  document.getElementById('cartSubtotal').textContent = '₱'+subtotal.toFixed(2);
  document.getElementById('cartDiscount').textContent = '-₱'+disc.toFixed(2);
  document.getElementById('cartTotal').textContent = '₱'+total.toFixed(2);
  const changeRow = document.getElementById('changeRow');
  if (method === 'cash' && cash > 0) {
    changeRow.style.display = 'flex';
    document.getElementById('cartChange').textContent = (change>=0?'₱':'-₱')+Math.abs(change).toFixed(2);
    document.getElementById('cartChange').style.color = change>=0?'var(--green)':'var(--red)';
  } else {
    changeRow.style.display = 'none';
  }
}

function processCheckout() {
  if (!cart.length) { toast('Cart is empty!','error'); return; }
  const subtotal = cart.reduce((s,c)=>s+c.price*c.qty,0);
  const discountDetails = getCustomerDiscountDetails();
  const discPct = discountDetails.percent;
  const disc = subtotal*(discPct/100);
  const total = subtotal - disc;
  const cash = parseFloat(document.getElementById('cashInput').value)||0;
  const method = document.getElementById('paymentMethod').value;
  const paymentRef = document.getElementById('paymentRef').value.trim();
  const customerName = document.getElementById('customerName').value.trim();
  if (method === 'cash' && cash > 0 && cash < total) { toast('Insufficient cash!','error'); return; }
  if (method !== 'cash' && paymentRef === '') { toast('Payment reference is required for e-wallet/online.','error'); return; }
  const table = document.getElementById('tableSelect').value;
  const now = new Date();
  // Deduct stock
  cart.forEach(c => {
    const item = menuItems.find(i=>i.id===c.id);
    if (item) item.stock = Math.max(0, item.stock - c.qty);
  });
  saveMenu();
  const tx = {
    table, items:[...cart], subtotal, discount:disc, total,
    cash: method === 'cash' ? cash : total,
    change: method === 'cash' ? (cash-total) : 0,
    payment_method: method,
    payment_reference: paymentRef,
    customer_name: customerName,
    customer_type: discountDetails.customer_type,
    discount_percent: discPct,
    discount_label: discountDetails.label,
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
    try { return JSON.parse(text); } catch (err) {
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

// ============ RECEIPT ============
function showReceipt(tx) {
  const d = new Date(tx.time);
  document.getElementById('receiptDate').textContent = d.toLocaleString('en-PH');
  document.getElementById('receiptTable').textContent = tx.table;
  document.getElementById('receiptItems').innerHTML = tx.items.map(i=>
    `<div class="receipt-row"><span>${i.emoji} ${i.name} x${i.qty}</span><span>₱${(i.price*i.qty).toFixed(2)}</span></div>`
  ).join('');
  document.getElementById('rcSubtotal').textContent = '₱'+tx.subtotal.toFixed(2);
  document.getElementById('rcDiscountLabel').textContent = tx.discount_label || `Discount (${(tx.discount_percent || 0)}%)`;
  document.getElementById('rcDiscount').textContent = '-₱'+tx.discount.toFixed(2);
  document.getElementById('rcTotal').textContent = '₱'+tx.total.toFixed(2);
  document.getElementById('rcPaymentMethod').textContent = tx.payment_method === 'cash' ? 'Cash' : tx.payment_method === 'e_wallet' ? 'E-Wallet' : 'Online';
  document.getElementById('rcPaymentReference').textContent = tx.payment_reference || '—';
  document.getElementById('rcCustomerName').textContent = tx.customer_name || 'Walk-in';
  document.getElementById('rcCash').textContent = '₱'+tx.cash.toFixed(2);
  document.getElementById('rcChange').textContent = '₱'+tx.change.toFixed(2);
  openModal('receiptModal');
}

function printReceipt() {
  window.print();
}

// ============ MENU MANAGEMENT ============
function renderMenuTable() {
  const body = document.getElementById('menuTableBody');
  body.innerHTML = menuItems.map(item => `
    <tr>
      <td><span style="font-size:20px;margin-right:8px;">${item.emoji||'🍽'}</span>${item.name}</td>
      <td><span class="tag tag-yellow">${item.category}</span></td>
      <td>₱${item.price.toFixed(2)}</td>
      <td>${item.stock}</td>
      <td><span class="tag ${item.status==='available'&&item.stock>0?'tag-green':item.stock===0?'tag-red':'tag-red'}">${item.stock===0?'Out of Stock':item.status==='available'?'Available':'Unavailable'}</span></td>
      <td style="display:flex;gap:6px;">
        <button class="btn btn-ghost btn-sm" onclick="openEditModal(${item.id})">Edit</button>
        <button class="btn btn-danger btn-sm" onclick="deleteItem(${item.id})">Delete</button>
      </td>
    </tr>`).join('');
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

// ============ INVENTORY ============
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
      ? `<tr>
          <td><span style="margin-right:8px;">${item.emoji||'🍽'}</span>${item.name}</td>
          <td>${item.category}</td>
          <td style="width:200px;"><div style="display:flex;align-items:center;gap:10px;"><div class="progress-bar" style="flex:1;"><div class="progress-fill" style="width:${pct}%;background:${color};"></div></div><span style="font-size:13px;font-weight:600;min-width:24px;">${item.stock}</span></div></td>
          <td>${status}</td>
          <td><button class="btn btn-ghost btn-sm" onclick="quickRestock(${item.id})">+ Add Stock</button></td>
        </tr>`
      : `<tr>
          <td>${item.name}</td>
          <td>${item.unit}</td>
          <td style="width:200px;"><div style="display:flex;align-items:center;gap:10px;"><div class="progress-bar" style="flex:1;"><div class="progress-fill" style="width:${pct}%;background:${color};"></div></div><span style="font-size:13px;font-weight:600;min-width:24px;">${item.stock}</span></div></td>
          <td>${status}</td>
          <td><button class="btn btn-ghost btn-sm" onclick="quickRestock(${item.id})">+ Add Stock</button></td>
        </tr>`;
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
    // Menu items still use localStorage
    const item = menuItems.find(i=>i.id===id);
    if (item) {
      item.stock += qty;
      saveMenu();
      renderInventory();
      toast(`✅ ${item.name} restocked +${qty}!`,'success');
    }
  } else {
    // Ingredients use database
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
        try { return JSON.parse(text); } catch (err) {
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

// ============ REPORTS ============
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

  // Hourly chart (last 8 hours)
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
  document.getElementById('hourlyChart').innerHTML = hours.map(h=>`
    <div class="chart-bar-col">
      <div class="chart-val">${h.val?'₱'+h.val.toFixed(0):''}</div>
      <div class="chart-bar" style="height:${(h.val/maxVal)*100}%;" title="₱${h.val.toFixed(2)}"></div>
      <div class="chart-label">${h.label}</div>
    </div>`).join('');

  // Top items
  const itemSales = {};
  transactions.forEach(t=>t.items.forEach(i=>{
    if (!itemSales[i.name]) itemSales[i.name]={name:i.name,emoji:i.emoji,qty:0,rev:0};
    itemSales[i.name].qty+=i.qty; itemSales[i.name].rev+=i.price*i.qty;
  }));
  const top = Object.values(itemSales).sort((a,b)=>b.qty-a.qty).slice(0,5);
  const maxQty = Math.max(...top.map(i=>i.qty),1);
  document.getElementById('topItemsChart').innerHTML = top.length ? top.map((i,idx)=>`
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
      <span style="font-size:13px;color:var(--text3);min-width:16px;">${idx+1}</span>
      <span style="font-size:18px;">${i.emoji||'🍽'}</span>
      <div style="flex:1;">
        <div style="font-size:13px;font-weight:500;margin-bottom:4px;">${i.name}</div>
        <div class="progress-bar"><div class="progress-fill" style="width:${(i.qty/maxQty)*100}%;background:var(--accent);"></div></div>
      </div>
      <span style="font-size:12px;color:var(--text2);">${i.qty} sold</span>
    </div>`).join('') : '<div class="empty-state">No sales data yet</div>';

  // Recent transactions
  const recent = [...transactions].reverse().slice(0,10);
  document.getElementById('txTable').innerHTML = recent.map(t=>`
    <tr>
      <td>#${t.id}</td>
      <td>${t.table}</td>
      <td>${t.items.map(i=>`${i.emoji} ${i.name}(${i.qty})`).join(', ')}</td>
      <td>₱${t.subtotal.toFixed(2)}</td>
      <td style="color:var(--green);">${t.discount>0?'-₱'+t.discount.toFixed(2):'—'}</td>
      <td style="color:var(--accent);font-weight:600;">₱${t.total.toFixed(2)}</td>
      <td style="color:var(--text3);">${new Date(t.time).toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit'})}</td>
    </tr>`).join('') || '<tr><td colspan="7" class="empty-state">No transactions yet</td></tr>';
}

function clearSalesData() {
  if (!confirm('Clear all sales data?')) return;
  transactions = []; saveTx(); renderReports(); toast('Sales data cleared.','info');
}

// ============ ORDER HISTORY ============
function renderOrderHistory() {
  document.getElementById('orderCount').textContent = transactions.length + ' orders';
  const body = document.getElementById('orderHistoryBody');
  body.innerHTML = [...transactions].reverse().map(t=>`
    <tr>
      <td>#${t.id}</td>
      <td>${t.table}</td>
      <td>${t.items.map(i=>`${i.emoji} ${i.name} x${i.qty}`).join(', ')}</td>
      <td style="color:var(--accent);font-weight:600;">₱${t.total.toFixed(2)}</td>
      <td style="color:var(--green);">${t.discount>0?'-₱'+t.discount.toFixed(2):'—'}</td>
      <td>₱${t.change.toFixed(2)}</td>
      <td style="color:var(--text3);">${new Date(t.time).toLocaleString('en-PH',{month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'})}</td>
    </tr>`).join('') || '<tr><td colspan="7" class="empty-state">No orders yet</td></tr>';
}

// ============ MODAL ============
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(m=>{
  m.addEventListener('click',e=>{ if(e.target===m) m.classList.remove('open'); });
});

// ============ TOAST ============
function toast(msg, type='info') {
  const el = document.createElement('div');
  el.className = `toast ${type}`;
  el.innerHTML = `<span>${msg}</span>`;
  document.getElementById('toastContainer').appendChild(el);
  setTimeout(()=>el.remove(),3000);
}

// ============ INIT ============
renderMenuGrid();
</script>
</body>
</html>
