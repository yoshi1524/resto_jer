<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>POS Dashboard — Country Side</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php'); exit;
}
require 'db_connect.php';

// Today's stats
$today = date('Y-m-d');
$statsRes = $conn->query("SELECT COUNT(*) AS orders, COALESCE(SUM(total),0) AS revenue FROM orders WHERE DATE(created_at)='$today' AND status='completed'");
$stats = $statsRes->fetch_assoc();

$itemsRes = $conn->query("SELECT COALESCE(SUM(oi.quantity),0) AS sold FROM order_items oi JOIN orders o ON oi.order_id=o.id WHERE DATE(o.created_at)='$today' AND o.status='completed'");
$itemsSold = $itemsRes->fetch_assoc()['sold'];

// Inventory status
$invRes = $conn->query("SELECT name, stock, (stock/30*100) AS pct FROM menu_items ORDER BY pct ASC LIMIT 5");
$invItems = [];
while ($row = $invRes->fetch_assoc()) $invItems[] = $row;

// Expiring soon count
$expRes = $conn->query("SELECT COUNT(*) AS cnt FROM menu_items WHERE expiry_date IS NOT NULL AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
$expiringCount = $expRes->fetch_assoc()['cnt'];

$firstname = $_SESSION['firstname'];
$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
?>

<div class="background"></div>

<div class="dashboard-container">
  <aside class="sidebar">
    <div class="sidebar-logo">
      <img src="icon.jpg" alt="Logo">
    </div>
    <nav>
      <div class="nav-item active">
        <i class="fa fa-chart-line"></i>
        <p>SALES</p>
      </div>
      <div class="nav-item" onclick="location.href='inventory.php'">
        <i class="fa fa-box"></i>
        <p>INVENTORY</p>
      </div>
      <div class="nav-item" onclick="location.href='report.php'">
        <i class="fa fa-file-alt"></i>
        <p>REPORTS</p>
      </div>
      <div class="nav-item" onclick="location.href='notification.php'" style="position:relative;">
        <i class="fa fa-bell"></i>
        <?php if($expiringCount > 0): ?>
          <span class="notif-badge"><?= $expiringCount ?></span>
        <?php endif; ?>
        <p>ALERTS</p>
      </div>
      <div class="nav-item" onclick="location.href='signup.html'">
        <i class="fa fa-user-plus"></i>
        <p>ADD CASHIER</p>
      </div>
    </nav>
    <div class="nav-item logout" onclick="confirmLogout()">
      <i class="fa fa-sign-out-alt"></i>
      <p>LOGOUT</p>
    </div>
  </aside>

  <main class="main-content">
    <header class="top-bar">
      <div style="flex:1;">
        <h2 style="font-size:18px;font-weight:700;">Welcome back, <?= htmlspecialchars($firstname) ?>! 👋</h2>
        <p style="font-size:12px;color:rgba(255,255,255,0.5);margin-top:4px;"><?= date('l, F j, Y') ?></p>
      </div>
      <div class="settings-btn" onclick="location.href='cashier.php'" style="background:linear-gradient(135deg,rgba(102,126,234,0.3),rgba(118,75,162,0.3));border-color:#667eea;">
        <i class="fa fa-cash-register"></i>
        <span>Open POS</span>
      </div>
    </header>

    <?php if($success): ?>
      <div class="alert alert-success" style="margin-bottom:20px;"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <section class="stats-grid">
      <div class="stat-card">
        <div class="stat-info">
          <h3>TODAY'S ORDERS</h3>
          <p class="value" id="totalOrders"><?= $stats['orders'] ?></p>
          <p style="font-size:11px;opacity:0.5;margin-top:4px;">Completed today</p>
        </div>
        <i class="fa fa-shopping-cart stat-icon"></i>
      </div>
      <div class="stat-card">
        <div class="stat-info">
          <h3>TODAY'S REVENUE</h3>
          <p class="value" id="totalRevenue">₱<?= number_format($stats['revenue'], 2) ?></p>
          <p style="font-size:11px;opacity:0.5;margin-top:4px;"><?= $itemsSold ?> items sold</p>
        </div>
        <i class="fa fa-peso-sign stat-icon"></i>
      </div>
    </section>

    <section class="main-grid">
      <div class="glass-card inventory-section">
        <h3><i class="fa fa-warehouse"></i> INVENTORY STATUS</h3>
        <?php foreach($invItems as $item):
          $pct = min(100, round($item['pct']));
          $cls = $pct >= 40 ? 'green' : ($pct >= 15 ? 'yellow' : 'red');
        ?>
        <div class="progress-item">
          <label><?= htmlspecialchars($item['name']) ?> <span><?= $pct ?>%</span></label>
          <div class="progress-bar">
            <div class="fill <?= $cls ?>" style="width:<?= $pct ?>%;"></div>
          </div>
        </div>
        <?php endforeach; ?>
        <div style="margin-top:16px;">
          <a href="inventory.php" style="color:#667eea;font-size:12px;">View full inventory →</a>
        </div>
      </div>

      <div class="glass-card chart-section">
        <h3><i class="fa fa-chart-area"></i> HOURLY SALES TODAY</h3>
        <div id="hourlyChart" style="display:flex;align-items:flex-end;gap:6px;height:180px;padding-top:8px;"></div>
      </div>
    </section>
  </main>
</div>

<div class="toast-wrap" id="toastWrap"></div>

<script>
function confirmLogout() {
  if (confirm('Are you sure you want to logout?')) window.location.href = 'logout.php';
}

// Load hourly chart
fetch('api/orders.php?action=today')
  .then(r => r.json())
  .then(d => {
    if (!d.success) return;
    const hourly = d.hourly || {};
    const now = new Date();
    const chart = document.getElementById('hourlyChart');
    let html = '';
    const hours = [];
    for (let i = 7; i >= 0; i--) {
      const h = new Date(now - i * 3600000).getHours();
      hours.push({ label: h + ':00', val: hourly[h] || 0 });
    }
    const max = Math.max(...hours.map(h => h.val), 1);
    hours.forEach(h => {
      const pct = (h.val / max) * 100;
      html += `<div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;height:100%;justify-content:flex-end;">
        <div style="font-size:9px;color:rgba(255,255,255,0.4);">${h.val ? '₱' + h.val.toFixed(0) : ''}</div>
        <div style="width:100%;border-radius:4px 4px 0 0;background:#667eea;opacity:0.8;min-height:4px;height:${pct}%;transition:height 0.5s;"></div>
        <div style="font-size:9px;color:rgba(255,255,255,0.4);">${h.label}</div>
      </div>`;
    });
    chart.innerHTML = html;
  });

function toast(msg, type = 'info') {
  const el = document.createElement('div');
  el.className = `toast ${type}`;
  el.innerHTML = `<span>${msg}</span>`;
  document.getElementById('toastWrap').appendChild(el);
  setTimeout(() => el.remove(), 3000);
}
</script>

</body>
</html>
