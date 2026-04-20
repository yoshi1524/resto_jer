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
let ingredients = JSON.parse(localStorage.getItem('pos_ingredients') || 'null') || [
  { id:101, name:'Rice', unit:'kg', stock:50, status:'available' },
  { id:102, name:'Chicken', unit:'kg', stock:30, status:'available' },
  { id:103, name:'Pork', unit:'kg', stock:25, status:'available' },
  { id:104, name:'Vegetables', unit:'kg', stock:40, status:'available' },
  { id:105, name:'Cheese', unit:'kg', stock:15, status:'available' },
  { id:106, name:'Flour', unit:'kg', stock:18, status:'available' },
  { id:107, name:'Sugar', unit:'kg', stock:20, status:'available' },
  { id:108, name:'Ice', unit:'kg', stock:60, status:'available' },
];
let cart = [];
let transactions = JSON.parse(localStorage.getItem('pos_tx') || '[]');
let editingItemId = null;
let activeCategory = 'all';
let inventoryMode = 'items';
let nextId = Math.max(...menuItems.map(i=>i.id), 0) + 1;

function showPage(page) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  document.getElementById('page-' + page).classList.add('active');
  event.currentTarget.classList.add('active');
  if (page === 'menu') renderMenuTable();
  if (page === 'inventory') renderInventory();
  if (page === 'reports') renderReports();
  if (page === 'orders') renderOrderHistory();
  if (page === 'pos') renderMenuGrid();
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
  tabsEl.innerHTML = `<div class="cat-tab ${activeCategory==='all'?'active':''}" onclick="filterMenuCat('all',this)">All</div>` + cats.map(c => `<div class="cat-tab ${activeCategory===c?'active':''}" onclick="filterMenuCat('${c}',this)">${c}</div>`).join('');
  posTabsEl.innerHTML = `<div class="tab ${activeCategory==='all'?'active':''}" onclick="filterMenuCat('all',this)">All</div>` + cats.map(c => `<div class="tab ${activeCategory===c?'active':''}" onclick="filterMenuCat('${c}',this)">${c}</div>`).join('');
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
function clearCart() { cart = []; document.getElementById('discountInput').value=''; document.getElementById('cashInput').value=''; document.getElementById('paymentMethod').value='cash'; document.getElementById('paymentRef').value=''; document.getElementById('customerName').value=''; renderCart(); }
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
  const discPct = parseFloat(document.getElementById('discountInput').value)||0;
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
  const discPct = parseFloat(document.getElementById('discountInput').value)||0;
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
    time: now.toISOString()
  };

  fetch('api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'save_order', order: tx })
  })
  .then(r => r.json())
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
  document.getElementById('receiptDate').textContent = d.toLocaleString('en-PH');
  document.getElementById('receiptTable').textContent = tx.table;
  document.getElementById('receiptItems').innerHTML = tx.items.map(i=>`<div class="receipt-row"><span>${i.emoji} ${i.name} x${i.qty}</span><span>₱${(i.price*i.qty).toFixed(2)}</span></div>`).join('');
  document.getElementById('rcSubtotal').textContent = '₱'+tx.subtotal.toFixed(2);
  document.getElementById('rcDiscount').textContent = '-₱'+tx.discount.toFixed(2);
  document.getElementById('rcTotal').textContent = '₱'+tx.total.toFixed(2);
  document.getElementById('rcPaymentMethod').textContent = tx.payment_method === 'cash' ? 'Cash' : tx.payment_method === 'e_wallet' ? 'E-Wallet' : 'Online';
  document.getElementById('rcPaymentReference').textContent = tx.payment_reference || '—';
  document.getElementById('rcCustomerName').textContent = tx.customer_name || 'Walk-in';
  document.getElementById('rcCash').textContent = '₱'+tx.cash.toFixed(2);
  document.getElementById('rcChange').textContent = '₱'+tx.change.toFixed(2);
  openModal('receiptModal');
}
function printReceipt() { window.print(); }
// Update this function in your <script> section
function renderMenuTable() {
  const body = document.getElementById('menuTableBody');
  // Filter out archived items so they don't clutter the active management list
  const activeItems = menuItems.filter(item => item.status !== 'archived');
  
  body.innerHTML = activeItems.map(item => `
    <tr>
      <td><span style="font-size:20px;margin-right:8px;">${item.emoji||'🍽'}</span>${item.name}</td>
      <td><span class="tag tag-yellow">${item.category}</span></td>
      <td>₱${item.price.toFixed(2)}</td>
      <td>${item.stock}</td>
      <td>
        <span class="tag ${item.status==='available'&&item.stock>0?'tag-green':'tag-red'}">
          ${item.stock===0?'Out of Stock':item.status==='available'?'Available':'Unavailable'}
        </span>
      </td>
      <td style="display:flex;gap:6px;">
        <button class="btn btn-ghost btn-sm" onclick="openEditModal(${item.id})">Edit</button>
  <form method="POST" style="display:inline;" onsubmit="return confirm('Archive this Item? )">
    <input type="hidden" name="action" value="archive_record">
    <input type="hidden" name="id" value="<?= $u['id'] ?>">
    <input type="hidden" name="type" value="user">
    <button type="submit" class="btn btn-danger btn-sm">Archive</button>
  </form>
</td>
    </tr>`).join('');
}

function archiveItem(id) {
  if (!confirm('Are you sure you want to archive this item? It will no longer appear in the menu.')) return;

  const item = menuItems.find(i => i.id === id);
  if (!item) return;

  // 1. Update local state
  item.status = 'archived';
  saveMenu();
  renderMenuTable();
  renderMenuGrid();

  // 2. Reflect in database via API call
  fetch('api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ 
      action: 'archive_menu_item', 
      item_id: id 
    })
  })
  .then(r => r.json())
  .then(result => {
    if (result.success) {
      toast('✅ Item archived successfully.', 'success');
    } else {
      toast('Error: ' + (result.message || 'Database sync failed.'), 'error');
    }
  })
  .catch(() => {
    toast('Network error: Database was not updated.', 'error');
  });
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
  const source = inventoryMode === 'items' ? menuItems : ingredients;
  const item = source.find(i=>i.id===id);
  if (item) {
    item.stock += qty;
    if (inventoryMode === 'items') saveMenu(); else saveIngredients();
    renderInventory();
    toast(`✅ ${item.name} restocked +${qty}!`,'success');
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
  
  // --- NEW: DAILY SALES LOGIC ---
  const dailyTotals = {};
  transactions.forEach(t => {
    const dateKey = new Date(t.time).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
    dailyTotals[dateKey] = (dailyTotals[dateKey] || 0) + t.total;
  });

  const dailyBody = document.getElementById('dailySalesBody');
  const sortedDates = Object.keys(dailyTotals).sort((a, b) => new Date(b) - new Date(a)); // Newest first

  dailyBody.innerHTML = sortedDates.map(date => `
    <tr>
        <td style="font-weight:500;">${date}</td>
        <td style="color:var(--accent); font-weight:700;">₱${dailyTotals[date].toFixed(2)}</td>
    </tr>
  `).join('') || '<tr><td colspan="2" class="empty-state">No daily data</td></tr>';
  
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
function renderOrderHistory() {
  document.getElementById('orderCount').textContent = transactions.length + ' orders';
  const body = document.getElementById('orderHistoryBody');
  body.innerHTML = [...transactions].reverse().map(t=>`<tr><td>#${t.id}</td><td>${t.table}</td><td>${t.items.map(i=>`${i.emoji} ${i.name} x${i.qty}`).join(', ')}</td><td style="color:var(--accent);font-weight:600;">₱${t.total.toFixed(2)}</td><td style="color:var(--green);">${t.discount>0?'-₱'+t.discount.toFixed(2):'—'}</td><td>₱${t.change.toFixed(2)}</td><td style="color:var(--text3);">${new Date(t.time).toLocaleString('en-PH',{month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'})}</td></tr>`).join('') || '<tr><td colspan="7" class="empty-state">No orders yet</td></tr>';
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
renderMenuGrid();
