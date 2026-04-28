// ─────────────────────────────────────────────────────────────────
// STATE
// FIX: menuItems now comes from DB, not localStorage.
// localStorage is only kept for transactions (pos_tx) as a
// local session cache — orders are also saved to DB via api.php.
// ─────────────────────────────────────────────────────────────────
let menuItems = [];
let ingredients = [];
let cart = [];
let transactions = JSON.parse(localStorage.getItem('pos_tx') || '[]');
let editingItemId = null;
let activeCategory = 'all';
let inventoryMode = 'items';
let nextId = 1;

// ─────────────────────────────────────────────────────────────────
// DB HELPERS
// ─────────────────────────────────────────────────────────────────
function apiPost(payload) {
  return fetch('api.php', {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  })
  .then(async r => {
    const text = await r.text();
    try { return JSON.parse(text); }
    catch (e) { throw new Error('Server returned non-JSON: ' + text.slice(0, 200)); }
  });
}

// FIX: Load menu from DB instead of localStorage
function loadMenuFromDB() {
  return apiPost({ action: 'get_menu_items' })
    .then(result => {
      if (result.success) {
        // FIX: Normalize numeric fields so id comparisons with === always work
        menuItems = result.items.map(i => ({
          ...i,
          id:    parseInt(i.id)      || 0,
          price: parseFloat(i.price) || 0,
          stock: parseInt(i.stock)   || 0
        }));
        nextId = Math.max(...menuItems.map(i => i.id), 0) + 1;
      } else {
        toast('Failed to load menu: ' + (result.message || 'Unknown error'), 'error');
      }
    })
    .catch(err => toast('Network error loading menu: ' + err.message, 'error'));
}

function loadIngredients() {
  return apiPost({ action: 'get_ingredients' })
    .then(result => {
      if (result.success) {
        
        ingredients = result.ingredients.map(i => ({
          ...i,
          id:    parseInt(i.id)    || 0,
          stock: parseFloat(i.stock) || 0
        }));
        return ingredients;
      } else {
        throw new Error(result.message || 'Failed to load ingredients');
      }
    });
}


function saveMenu() { }
function saveIngredients() { }
function saveTx() { localStorage.setItem('pos_tx', JSON.stringify(transactions)); }

// ─────────────────────────────────────────────────────────────────
// NAVIGATION
// ─────────────────────────────────────────────────────────────────
function showPage(page) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  const pageEl = document.getElementById('page-' + page);
  if (pageEl) pageEl.classList.add('active');
  if (event && event.currentTarget) event.currentTarget.classList.add('active');

  if (page === 'menu') {
   
    loadMenuFromDB().then(() => renderMenuTable());
  }
  if (page === 'inventory') {
   
    inventoryMode = 'items';
    document.getElementById('inventoryModeItems')?.classList.add('active');
    document.getElementById('inventoryModeIngredients')?.classList.remove('active');
   
    Promise.all([loadMenuFromDB(), loadIngredients().catch(() => [])])
      .then(() => renderInventory())
      .catch(() => renderInventory());
  }
  if (page === 'reports') { initReportDates(); if (typeof loadBranchSales === 'function') loadBranchSales(); }
  if (page === 'orders') renderOrderHistory();
  if (page === 'pos') {
    loadMenuFromDB().then(() => renderMenuGrid());
  }
}

// ─────────────────────────────────────────────────────────────────
// CLOCK
// ─────────────────────────────────────────────────────────────────
function updateClock() {
  const now = new Date();
  const clockEl = document.getElementById('clock');
  const dateEl  = document.getElementById('dateLabel');
  if (clockEl) clockEl.textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
  if (dateEl)  dateEl.textContent  = now.toLocaleDateString('en-PH', { weekday: 'short', month: 'short', day: 'numeric' });
}
setInterval(updateClock, 1000);
updateClock();

// ─────────────────────────────────────────────────────────────────
// MENU GRID (POS view)
// ─────────────────────────────────────────────────────────────────
function getCategories() { return [...new Set(menuItems.map(i => i.category))]; }

function renderCategoryTabs() {
  const cats = getCategories();
  const tabsEl    = document.getElementById('categoryTabs');
  const posTabsEl = document.querySelector('.tabs');
  const makeTab   = (label, cat, cls) =>
    `<div class="${cls} ${activeCategory === cat ? 'active' : ''}" onclick="filterMenuCat('${cat}',this)">${label}</div>`;
  const inner = makeTab('All', 'all', 'cat-tab') + cats.map(c => makeTab(c, c, 'cat-tab')).join('');
  const posInner  = makeTab('All', 'all', 'tab')  + cats.map(c => makeTab(c, c, 'tab')).join('');
  if (tabsEl)    tabsEl.innerHTML    = inner;
  if (posTabsEl) posTabsEl.innerHTML = posInner;
}

function filterMenuCat(cat) {
  activeCategory = cat;
  document.querySelectorAll('.cat-tab, .tab').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.cat-tab, .tab').forEach(t => {
    if (t.textContent.trim() === (cat === 'all' ? 'All' : cat)) t.classList.add('active');
  });
  renderMenuGrid();
}

function renderMenuGrid() {
  renderCategoryTabs();
  const q = document.getElementById('menuSearch')?.value.toLowerCase() || '';
  let items = menuItems.filter(i => activeCategory === 'all' || i.category === activeCategory);
  if (q) items = items.filter(i => i.name.toLowerCase().includes(q) || i.category.toLowerCase().includes(q));
  const grid = document.getElementById('menuGrid');
  if (!grid) return;
  if (!items.length) { grid.innerHTML = `<div class="empty-state" style="grid-column:1/-1;">No items found</div>`; return; }
  grid.innerHTML = items.map(item => {
    const unavail = item.status === 'unavailable' || item.stock === 0;
    const stockStatus = item.stock === 0 ? 'out' : item.stock <= 5 ? 'low' : '';
    return `<div class="menu-item ${unavail ? 'unavailable' : ''}" onclick="${unavail ? '' : `addToCart(${item.id})`}">
      <span class="menu-emoji">${item.emoji || '🍽'}</span>
      <div class="menu-cat-badge">${item.category}</div>
      <div class="menu-name">${item.name}</div>
      <div class="menu-price">₱${parseFloat(item.price).toFixed(2)}</div>
      <div class="menu-stock ${stockStatus}">${item.stock === 0 ? 'Out of stock' : item.stock <= 5 ? `Low stock: ${item.stock}` : `Stock: ${item.stock}`}</div>
      ${unavail ? `<div style="position:absolute;top:8px;right:8px;font-size:10px;background:var(--surface3);padding:2px 6px;border-radius:4px;color:var(--text3);">${item.stock === 0 ? 'OUT' : 'OFF'}</div>` : ''}
    </div>`;
  }).join('');
}

// ─────────────────────────────────────────────────────────────────
// MENU TABLE (Admin/Manager management view)
// ─────────────────────────────────────────────────────────────────
function renderMenuTable() {
  const body = document.getElementById('menuTableBody');
  if (!body) return;
  if (!menuItems.length) {
    body.innerHTML = `<tr><td colspan="6" class="empty-state">No menu items found.</td></tr>`;
    return;
  }
  body.innerHTML = menuItems.map(item => `
    <tr>
      <td><span style="font-size:20px;margin-right:8px;">${item.emoji || '🍽'}</span>${item.name}</td>
      <td><span class="tag tag-yellow">${item.category}</span></td>
      <td>₱${parseFloat(item.price).toFixed(2)}</td>
      <td>${item.stock}</td>
      <td><span class="tag ${item.status === 'available' && item.stock > 0 ? 'tag-green' : 'tag-red'}">
        ${item.stock === 0 ? 'Out of Stock' : item.status === 'available' ? 'Available' : 'Unavailable'}
      </span></td>
      <td style="display:flex;gap:6px;">
        <button class="btn btn-ghost btn-sm" onclick="openEditModal(${item.id})">Edit</button>
        <button class="btn btn-danger btn-sm" onclick="deleteItem(${item.id})">Archive</button>
      </td>
    </tr>`).join('');
}

// ─────────────────────────────────────────────────────────────────
// ADD / EDIT ITEM MODAL
// ─────────────────────────────────────────────────────────────────
function openAddItemModal() {
  editingItemId = null;
  const titleEl = document.getElementById('itemModalTitle');
  if (titleEl) titleEl.textContent = 'Add Menu Item';
  ['fEmoji', 'fName', 'fPrice', 'fStock'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });
  const cat = document.getElementById('fCategory');
  if (cat) cat.value = 'Sizzling Favorites';
  const stat = document.getElementById('fStatus');
  if (stat) stat.value = 'available';
  openModal('itemModal');
}

function openEditModal(id) {
  const item = menuItems.find(i => i.id === id);
  if (!item) return;
  editingItemId = id;
  const titleEl = document.getElementById('itemModalTitle');
  if (titleEl) titleEl.textContent = 'Edit Menu Item';
  document.getElementById('fEmoji').value    = item.emoji || '';
  document.getElementById('fName').value     = item.name;
  document.getElementById('fCategory').value = item.category;
  document.getElementById('fPrice').value    = item.price;
  document.getElementById('fStock').value    = item.stock;
  document.getElementById('fStatus').value   = item.status;
  openModal('itemModal');
}


function saveItem() {
  const name  = document.getElementById('fName').value.trim();
  const price = parseFloat(document.getElementById('fPrice').value);
  if (!name)          { toast('Item name required!', 'error'); return; }
  if (!price || price <= 0) { toast('Valid price required!', 'error'); return; }

  const data = {
    id:       editingItemId,
    emoji:    document.getElementById('fEmoji').value || '🍽',
    name,
    category: document.getElementById('fCategory').value,
    price,
    stock:    parseInt(document.getElementById('fStock').value) || 0,
    status:   document.getElementById('fStatus').value
  };

  const action = editingItemId ? 'update_menu_item' : 'add_menu_item';

  apiPost({ action, item: data })
    .then(result => {
      if (result.success) {
        toast(editingItemId ? 'Item updated!' : 'Item added!', 'success');
        closeModal('itemModal');
        
        loadMenuFromDB().then(() => { renderMenuTable(); renderMenuGrid(); });
      } else {
        toast(result.message || 'Failed to save item.', 'error');
      }
    })
    .catch(err => toast('Network error: ' + err.message, 'error'));
}


function deleteItem(id) {
  if (!confirm('This menu item will be archived.')) return;
  apiPost({ action: 'delete_menu_item', item_id: id })
    .then(result => {
      if (result.success) {
        toast('Item archived.', 'info');
        loadMenuFromDB().then(() => { renderMenuTable(); renderMenuGrid(); });
      } else {
        toast(result.message || 'Failed to archive item.', 'error');
      }
    })
    .catch(err => toast('Network error: ' + err.message, 'error'));
}

// ─────────────────────────────────────────────────────────────────
// CART
// ─────────────────────────────────────────────────────────────────
function addToCart(id) {
  const item = menuItems.find(i => i.id === id);
  if (!item || item.stock === 0 || item.status === 'unavailable') return;
  const existing = cart.find(c => c.id === id);
  if (existing) {
    if (existing.qty >= item.stock) { toast('Max stock reached!', 'error'); return; }
    existing.qty++;
  } else {
    cart.push({ id, name: item.name, price: parseFloat(item.price), qty: 1, emoji: item.emoji || '🍽' });
  }
  renderCart();
  toast(`${item.emoji || '🍽'} ${item.name} added!`, 'success');
}

function removeFromCart(id) {
  const idx = cart.findIndex(c => c.id === id);
  if (idx === -1) return;
  if (cart[idx].qty > 1) cart[idx].qty--;
  else cart.splice(idx, 1);
  renderCart();
}

function clearCart() {
  cart = [];
  ['customerType', 'cashInput', 'paymentMethod', 'paymentRef', 'customerName', 'discountInput'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = id === 'customerType' ? 'regular' : id === 'paymentMethod' ? 'cash' : '';
  });
  renderCart();
}

function renderCart() {
  const el = document.getElementById('cartItems');
  if (!el) return;
  if (!cart.length) {
    el.innerHTML = `<div class="cart-empty"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg><span>Cart is empty</span></div>`;
  } else {
    el.innerHTML = cart.map(c => `<div class="cart-item">
      <span style="font-size:24px;">${c.emoji}</span>
      <div class="cart-item-info">
        <div class="cart-item-name">${c.name}</div>
        <div class="cart-item-price">₱${(c.price * c.qty).toFixed(2)}</div>
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

// Unified discount logic — works for both staff (customerType dropdown) and
// admin/manager (discountInput percent field)
function getDiscountDetails() {
  const CUSTOMER_DISCOUNT_MAP = {
    regular: { percent: 0,  label: 'Regular' },
    pwd:     { percent: 20, label: 'PWD (20%)' },
    senior:  { percent: 20, label: 'Senior Citizen (20%)' }
  };
  const typeEl = document.getElementById('customerType');
  if (typeEl) {
    const meta = CUSTOMER_DISCOUNT_MAP[typeEl.value] || CUSTOMER_DISCOUNT_MAP.regular;
    return { percent: meta.percent, label: meta.label, customer_type: typeEl.value };
  }
  // Fallback: manual percent field (admin/manager)
  const pct = parseFloat(document.getElementById('discountInput')?.value) || 0;
  return { percent: pct, label: pct > 0 ? `${pct}%` : 'None', customer_type: 'regular' };
}

function renderCartFooter() {
  const disc      = getDiscountDetails();
  const subtotal  = cart.reduce((s, c) => s + c.price * c.qty, 0);
  const discount  = subtotal * (disc.percent / 100);
  const total     = subtotal - discount;
  const cash      = parseFloat(document.getElementById('cashInput')?.value) || 0;
  const method    = document.getElementById('paymentMethod')?.value || 'cash';
  const payRef    = document.getElementById('paymentRef');

  if (payRef) {
    payRef.style.display = method !== 'cash' ? 'block' : 'none';
    if (method === 'cash') payRef.value = '';
    else payRef.placeholder = method === 'e_wallet' ? 'E-Wallet reference' : 'Online transaction ID';
  }

  const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
  set('cartSubtotal', '₱' + subtotal.toFixed(2));
  set('cartDiscount', '-₱' + discount.toFixed(2));
  set('cartTotal',    '₱' + total.toFixed(2));

  const changeRow = document.getElementById('changeRow');
  if (changeRow) {
    if (method === 'cash' && cash > 0) {
      changeRow.style.display = 'flex';
      const change = cash - total;
      const changeEl = document.getElementById('cartChange');
      if (changeEl) {
        changeEl.textContent = (change >= 0 ? '₱' : '-₱') + Math.abs(change).toFixed(2);
        changeEl.style.color = change >= 0 ? 'var(--green)' : 'var(--red)';
      }
    } else {
      changeRow.style.display = 'none';
    }
  }
}

// ─────────────────────────────────────────────────────────────────
// CHECKOUT
// FIX: Now also calls deduct_stock so DB stock is updated after sale
// ─────────────────────────────────────────────────────────────────
function processCheckout() {
  if (!cart.length) { toast('Cart is empty!', 'error'); return; }

  const disc      = getDiscountDetails();
  const subtotal  = cart.reduce((s, c) => s + c.price * c.qty, 0);
  const discount  = subtotal * (disc.percent / 100);
  const total     = subtotal - discount;
  const cash      = parseFloat(document.getElementById('cashInput')?.value) || 0;
  const method    = document.getElementById('paymentMethod')?.value || 'cash';
  const payRef    = document.getElementById('paymentRef')?.value.trim() || '';
  const custName  = document.getElementById('customerName')?.value.trim() || '';
  const table     = document.getElementById('tableSelect')?.value || 'Walk-in';

  if (method === 'cash' && cash > 0 && cash < total) { toast('Insufficient cash!', 'error'); return; }
  if (method !== 'cash' && !payRef) { toast('Payment reference is required for e-wallet/online.', 'error'); return; }

  const tx = {
    table,
    items: [...cart],
    subtotal, discount, total,
    customer_type:    disc.customer_type,
    discount_percent: disc.percent,
    discount_label:   disc.label,
    cash:   method === 'cash' ? cash : total,
    change: method === 'cash' ? (cash - total) : 0,
    payment_method:    method,
    payment_reference: payRef,
    customer_name:     custName,
    time: new Date().toISOString()
  };

  // Step 1: Save order to DB
  apiPost({ action: 'save_order', order: tx })
    .then(result => {
      if (!result.success) {
        toast(result.message || 'Unable to save order.', 'error');
        return;
      }
      tx.id = result.order_id;

      // FIX Step 2: Deduct stock in DB (previously only done in localStorage)
      return apiPost({
        action: 'deduct_stock',
        items: cart.map(c => ({ id: c.id, qty: c.qty }))
      }).then(stockResult => {
        if (!stockResult.success) {
          // Order saved but stock deduction failed — warn but don't block
          toast('⚠️ Order saved but stock not updated: ' + (stockResult.message || ''), 'error');
        }
        transactions.push(tx);
        saveTx();
        showReceipt(tx);
        clearCart();
        // Reload menu so stock counts are fresh
        loadMenuFromDB().then(() => renderMenuGrid());
        toast('✅ Order completed and saved!', 'success');
      });
    })
    .catch(err => toast('Network error: ' + err.message, 'error'));
}

function showReceipt(tx) {
  document.getElementById('receiptDate').textContent  = new Date(tx.time).toLocaleString('en-PH');
  document.getElementById('receiptTable').textContent = tx.table;
  document.getElementById('receiptItems').innerHTML   = tx.items.map(i =>
    `<div class="receipt-row"><span>${i.emoji} ${i.name} x${i.qty}</span><span>₱${(i.price * i.qty).toFixed(2)}</span></div>`
  ).join('');

  const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
  set('rcSubtotal', '₱' + tx.subtotal.toFixed(2));
  set('rcDiscount', '-₱' + tx.discount.toFixed(2));
  set('rcTotal',    '₱' + tx.total.toFixed(2));
  set('rcPaymentMethod',    tx.payment_method === 'cash' ? 'Cash' : tx.payment_method === 'e_wallet' ? 'E-Wallet' : 'Online');
  set('rcPaymentReference', tx.payment_reference || '—');
  set('rcCustomerName',     tx.customer_name || 'Walk-in');
  set('rcCash',   '₱' + tx.cash.toFixed(2));
  set('rcChange', '₱' + tx.change.toFixed(2));

  // Some pages have rcDiscountLabel, handle gracefully
  const rcLabel = document.getElementById('rcDiscountLabel');
  if (rcLabel) rcLabel.textContent = tx.discount_label || 'Discount';

  openModal('receiptModal');
}

function printReceipt() { window.print(); }

// ─────────────────────────────────────────────────────────────────
// INVENTORY
// ─────────────────────────────────────────────────────────────────
function renderInventory() {
  const source = inventoryMode === 'items' ? menuItems : ingredients;
  const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
  set('invInStock',  source.filter(i => i.stock > 5).length);
  set('invLowStock', source.filter(i => i.stock > 0 && i.stock <= 5).length);
  set('invOutStock', source.filter(i => i.stock === 0).length);
  set('invTotal',    source.length);
  set('invTotalSub', inventoryMode === 'items' ? 'Menu items' : 'Ingredients');

  const header = document.getElementById('inventoryHeader');
  if (header) {
    header.innerHTML = inventoryMode === 'items'
      ? '<tr><th>Item</th><th>Category</th><th>Stock Level</th><th>Status</th><th>Actions</th></tr>'
      : '<tr><th>Ingredient</th><th>Unit</th><th>Stock Level</th><th>Status</th><th>Actions</th></tr>';
  }

  const body = document.getElementById('inventoryBody');
  if (!body) return;
  body.innerHTML = source.map(item => {
    const pct    = Math.min(100, (item.stock / 30) * 100);
    const color  = item.stock === 0 ? 'var(--red)' : item.stock <= 5 ? 'var(--accent)' : 'var(--green)';
    const status = item.stock === 0
      ? '<span class="tag tag-red">Out of Stock</span>'
      : item.stock <= 5
        ? '<span class="tag tag-yellow">Low Stock</span>'
        : '<span class="tag tag-green">In Stock</span>';
    const bar = `<div style="display:flex;align-items:center;gap:10px;"><div class="progress-bar" style="flex:1;"><div class="progress-fill" style="width:${pct}%;background:${color};"></div></div><span style="font-size:13px;font-weight:600;min-width:24px;">${item.stock}</span></div>`;
    return inventoryMode === 'items'
      ? `<tr><td><span style="margin-right:8px;">${item.emoji || '🍽'}</span>${item.name}</td><td>${item.category}</td><td style="width:200px;">${bar}</td><td>${status}</td><td><button class="btn btn-ghost btn-sm" onclick="quickRestock(${item.id})">+ Add Stock</button></td></tr>`
      : `<tr><td>${item.name}</td><td>${item.unit}</td><td style="width:200px;">${bar}</td><td>${status}</td><td><button class="btn btn-ghost btn-sm" onclick="quickRestock(${item.id})">+ Add Stock</button></td></tr>`;
  }).join('');
}

function setInventoryMode(mode) {
  inventoryMode = mode;
  //document.getElementById('inventoryModeItems')?.classList.toggle('active', mode === 'items');
  document.getElementById('inventoryModeIngredients')?.classList.toggle('active', mode === 'ingredients');

  
  if (mode === 'ingredients') {
    loadIngredients()
      .then(() => renderInventory())
      .catch(err => { toast('Failed to load ingredients: ' + err.message, 'error'); renderInventory(); });
  } else {
    loadMenuFromDB()
      .then(() => renderInventory())
      .catch(() => renderInventory());
  }
}

function openRestockModal() {
  // FIX: Always fetch fresh data before opening so the dropdown is never empty
  const prepare = inventoryMode === 'ingredients' ? loadIngredients() : loadMenuFromDB();
  prepare
    .then(() => { populateRestockSelect(null); document.getElementById('restockQty').value = ''; openModal('restockModal'); })
    .catch(() => { populateRestockSelect(null); document.getElementById('restockQty').value = ''; openModal('restockModal'); });
}

function quickRestock(id) {
  // FIX: Same — ensure data is loaded before populating the select
  const prepare = inventoryMode === 'ingredients' ? loadIngredients() : loadMenuFromDB();
  prepare
    .then(() => { populateRestockSelect(id); document.getElementById('restockQty').value = ''; openModal('restockModal'); })
    .catch(() => { populateRestockSelect(id); document.getElementById('restockQty').value = ''; openModal('restockModal'); });
}

function populateRestockSelect(selectedId) {
  const source  = inventoryMode === 'items' ? menuItems : ingredients;
  const titleEl = document.getElementById('restockModalTitle');
  if (titleEl) titleEl.textContent = inventoryMode === 'items' ? 'Restock Item' : 'Restock Ingredient';
  const sel = document.getElementById('restockItem');
  if (!sel) return;
  if (!source.length) {
    sel.innerHTML = `<option value="">— No items found —</option>`;
    return;
  }
  sel.innerHTML = source.map(i =>
    `<option value="${i.id}" ${parseInt(i.id) === parseInt(selectedId) ? 'selected' : ''}>${inventoryMode === 'items' ? (i.emoji || '🍽') + ' ' + i.name : i.name} (Stock: ${i.stock})</option>`
  ).join('');
}

// FIX: doRestock() now calls api.php for menu items too, not just ingredients
function doRestock() {
  const id  = parseInt(document.getElementById('restockItem').value);
  const qty = parseInt(document.getElementById('restockQty').value) || 0;
  if (qty <= 0) { toast('Enter valid quantity!', 'error'); return; }
  closeModal('restockModal');

  if (inventoryMode === 'items') {
    apiPost({ action: 'restock_menu_item', item_id: id, quantity: qty })
      .then(result => {
        if (result.success) {
          const item = menuItems.find(i => parseInt(i.id) === parseInt(id));
          if (item) item.stock = parseInt(result.new_stock) || item.stock;
          renderInventory();
          toast(`✅ ${item?.name || 'Item'} restocked +${qty}!`, 'success');
        } else {
          toast(result.message || 'Restock failed.', 'error');
        }
      })
      .catch(err => toast('Network error: ' + err.message, 'error'));
  } else {
    // FIX: parseInt on both sides so string/number mismatch never causes false miss
    const item = ingredients.find(i => parseInt(i.id) === parseInt(id));
    if (!item) {
      toast('Could not find ingredient — reloading data.', 'info');
      loadIngredients().then(() => renderInventory());
      return;
    }
    const newStock = item.stock + qty;
    apiPost({ action: 'update_ingredient_stock', ingredient_id: id, stock: newStock, reason: 'Restock' })
      .then(result => {
        if (result.success) {
          item.stock = newStock;
          renderInventory();
          toast(`✅ ${item.name} restocked +${qty}!`, 'success');
        } else {
          toast(result.message || 'Failed to update stock.', 'error');
        }
      })
      .catch(err => toast('Network error: ' + err.message, 'error'));
  }
}

// ─────────────────────────────────────────────────────────────────
// REPORTS — Admin: all branches | Manager: own branch only
// ─────────────────────────────────────────────────────────────────

function initReportDates() {
  const today    = new Date().toISOString().slice(0, 10);
  const firstDay = today.slice(0, 8) + '01';
  const fromEl   = document.getElementById('rptDateFrom');
  const toEl     = document.getElementById('rptDateTo');
  if (fromEl && !fromEl.value) fromEl.value = firstDay;
  if (toEl   && !toEl.value)   toEl.value   = today;
}

// ── ADMIN: all-branch sales ──────────────────────────────────────
function loadBranchSales() {
  const dateFrom = document.getElementById('rptDateFrom')?.value || new Date().toISOString().slice(0,8)+'01';
  const dateTo   = document.getElementById('rptDateTo')?.value   || new Date().toISOString().slice(0,10);

  apiPost({ action: 'get_branch_sales', date_from: dateFrom, date_to: dateTo })
    .then(result => {
      if (!result.success) { toast(result.message || 'Failed to load sales.', 'error'); return; }
      renderAdminReports(result);
    })
    .catch(err => toast('Network error: ' + err.message, 'error'));
}

function renderAdminReports(data) {
  const branches = data.branches || [];
  const daily    = data.daily    || [];
  const items    = data.top_items|| [];
  const recent   = data.recent   || [];
  const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };

  // ── Totals across all branches ───────────────────────────────
  const totalRev  = branches.reduce((s,b) => s + parseFloat(b.total_revenue  || 0), 0);
  const totalOrd  = branches.reduce((s,b) => s + parseInt(b.total_orders     || 0), 0);
  const totalDisc = branches.reduce((s,b) => s + parseFloat(b.total_discounts|| 0), 0);
  const avgVal    = totalOrd ? totalRev / totalOrd : 0;

  set('rptRevenue',   '₱' + totalRev.toFixed(2));
  set('rptOrders',    totalOrd);
  set('rptTxCount',   totalOrd + ' orders across ' + branches.length + ' branch(es)');
  set('rptAvg',       '₱' + avgVal.toFixed(2));
  set('rptDiscounts', '₱' + totalDisc.toFixed(2));

  // ── Per-branch cards ─────────────────────────────────────────
  const cardsEl = document.getElementById('branchCards');
  if (cardsEl) {
    if (!branches.length) {
      cardsEl.innerHTML = `<div class="card" style="grid-column:1/-1;text-align:center;color:var(--text3);padding:32px;">No sales data for this period.</div>`;
    } else {
      const COLORS = ['var(--accent)', 'var(--green)', 'var(--blue)', '#b07ff0', '#e05c9a'];
      cardsEl.innerHTML = branches.map((b, idx) => {
        const color   = COLORS[idx % COLORS.length];
        const pct     = totalRev > 0 ? ((parseFloat(b.total_revenue) / totalRev) * 100).toFixed(1) : '0.0';
        const cashPct = parseFloat(b.total_revenue) > 0
          ? ((parseFloat(b.cash_sales) / parseFloat(b.total_revenue)) * 100).toFixed(0) : 0;
        return `<div class="card" style="border-left:3px solid ${color};">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
            <div>
              <div style="font-family:'Syne',sans-serif;font-size:15px;font-weight:700;">${b.branch_name}</div>
              <div style="font-size:11px;color:var(--text3);margin-top:2px;">${pct}% of total revenue</div>
            </div>
            <div style="font-family:'Syne',sans-serif;font-size:20px;font-weight:800;color:${color};">₱${parseFloat(b.total_revenue).toFixed(2)}</div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:12px;">
            <div style="background:var(--surface2);border-radius:8px;padding:8px;">
              <div style="color:var(--text3);">Orders</div>
              <div style="font-weight:700;margin-top:2px;">${b.total_orders}</div>
            </div>
            <div style="background:var(--surface2);border-radius:8px;padding:8px;">
              <div style="color:var(--text3);">Avg Order</div>
              <div style="font-weight:700;margin-top:2px;">₱${parseFloat(b.avg_order_value).toFixed(2)}</div>
            </div>
            <div style="background:var(--surface2);border-radius:8px;padding:8px;">
              <div style="color:var(--text3);">Discounts</div>
              <div style="font-weight:700;margin-top:2px;color:var(--green);">₱${parseFloat(b.total_discounts).toFixed(2)}</div>
            </div>
            <div style="background:var(--surface2);border-radius:8px;padding:8px;">
              <div style="color:var(--text3);">Cash %</div>
              <div style="font-weight:700;margin-top:2px;">${cashPct}%</div>
            </div>
          </div>
          <div style="margin-top:10px;">
            <div style="font-size:11px;color:var(--text3);margin-bottom:4px;">Payment split</div>
            <div style="display:flex;gap:4px;height:6px;border-radius:4px;overflow:hidden;">
              <div style="background:var(--accent);flex:${parseFloat(b.cash_sales)};"></div>
              <div style="background:var(--green);flex:${parseFloat(b.ewallet_sales)};"></div>
              <div style="background:var(--blue);flex:${parseFloat(b.online_sales)};"></div>
            </div>
            <div style="display:flex;gap:12px;margin-top:4px;font-size:10px;color:var(--text3);">
              <span style="color:var(--accent);">■ Cash</span>
              <span style="color:var(--green);">■ E-Wallet</span>
              <span style="color:var(--blue);">■ Online</span>
            </div>
          </div>
        </div>`;
      }).join('');
    }
  }

  // ── Daily breakdown table ────────────────────────────────────
  const dailyEl = document.getElementById('dailySalesBody');
  if (dailyEl) {
    dailyEl.innerHTML = daily.map(d =>
      `<tr>
        <td style="font-weight:500;">${d.sale_date}</td>
        <td>${d.branch_name}</td>
        <td>${d.orders}</td>
        <td style="color:var(--accent);font-weight:700;">₱${parseFloat(d.revenue).toFixed(2)}</td>
      </tr>`
    ).join('') || '<tr><td colspan="4" class="empty-state">No data for this period.</td></tr>';
  }

  // ── Top items (combine across branches) ──────────────────────
  const itemTotals = {};
  items.forEach(i => {
    const key = i.item_name;
    if (!itemTotals[key]) itemTotals[key] = { name: i.item_name, emoji: i.emoji, qty: 0, rev: 0 };
    itemTotals[key].qty += parseInt(i.qty_sold  || 0);
    itemTotals[key].rev += parseFloat(i.revenue || 0);
  });
  const top    = Object.values(itemTotals).sort((a,b) => b.qty - a.qty).slice(0, 8);
  const maxQty = Math.max(...top.map(i => i.qty), 1);
  const topEl  = document.getElementById('topItemsChart');
  if (topEl) {
    topEl.innerHTML = top.length ? top.map((i, idx) =>
      `<div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
        <span style="font-size:12px;color:var(--text3);min-width:16px;">${idx+1}</span>
        <span style="font-size:18px;">${i.emoji || '🍽'}</span>
        <div style="flex:1;">
          <div style="font-size:13px;font-weight:500;margin-bottom:4px;">${i.name}</div>
          <div class="progress-bar"><div class="progress-fill" style="width:${(i.qty/maxQty)*100}%;background:var(--accent);"></div></div>
        </div>
        <span style="font-size:12px;color:var(--text2);">${i.qty} sold</span>
      </div>`
    ).join('') : '<div class="empty-state">No sales data yet.</div>';
  }

  // ── Payment breakdown ────────────────────────────────────────
  const cashTotal    = branches.reduce((s,b) => s + parseFloat(b.cash_sales    || 0), 0);
  const ewalletTotal = branches.reduce((s,b) => s + parseFloat(b.ewallet_sales || 0), 0);
  const onlineTotal  = branches.reduce((s,b) => s + parseFloat(b.online_sales  || 0), 0);
  const payEl = document.getElementById('paymentBreakdown');
  if (payEl) {
    const rows = [
      { label: '💵 Cash',         val: cashTotal,    color: 'var(--accent)' },
      { label: '📱 E-Wallet',     val: ewalletTotal, color: 'var(--green)'  },
      { label: '🏦 Online Bank',  val: onlineTotal,  color: 'var(--blue)'   },
    ];
    const maxPay = Math.max(...rows.map(r => r.val), 1);
    payEl.innerHTML = rows.map(r => `
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
        <span style="font-size:13px;min-width:110px;color:var(--text2);">${r.label}</span>
        <div class="progress-bar" style="flex:1;"><div class="progress-fill" style="width:${(r.val/maxPay)*100}%;background:${r.color};"></div></div>
        <span style="font-size:13px;font-weight:600;min-width:80px;text-align:right;">₱${r.val.toFixed(2)}</span>
      </div>`).join('');
  }

  // ── Recent transactions table ────────────────────────────────
  const txEl = document.getElementById('txTable');
  if (txEl) {
    txEl.innerHTML = recent.map(t =>
      `<tr>
        <td style="color:var(--text3);font-size:11px;">${t.order_number}</td>
        <td><span class="tag tag-yellow" style="font-size:10px;">${t.branch_name}</span></td>
        <td>${t.table_name}</td>
        <td style="color:var(--text3);">${t.username || '—'}</td>
        <td style="color:var(--accent);font-weight:700;">₱${parseFloat(t.total).toFixed(2)}</td>
        <td>${t.payment_method === 'cash' ? '💵' : t.payment_method === 'e_wallet' ? '📱' : '🏦'} ${t.payment_method}</td>
        <td style="color:var(--text3);font-size:12px;">${new Date(t.created_at).toLocaleString('en-PH',{month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'})}</td>
      </tr>`
    ).join('') || '<tr><td colspan="7" class="empty-state">No transactions in this period.</td></tr>';
  }
}

// ── MANAGER: single-branch sales ─────────────────────────────────
function loadManagerBranchSales(branchId) {
  if (!branchId) {
    toast('Your account has no branch assigned. Please contact an admin.', 'error');
    return;
  }
  const dateFrom = document.getElementById('rptDateFrom')?.value || new Date().toISOString().slice(0,8)+'01';
  const dateTo   = document.getElementById('rptDateTo')?.value   || new Date().toISOString().slice(0,10);

  apiPost({ action: 'get_my_branch_sales', branch_id: branchId, date_from: dateFrom, date_to: dateTo })
    .then(result => {
      if (!result.success) { toast(result.message || 'Failed to load sales.', 'error'); return; }
      renderManagerReports(result);
    })
    .catch(err => toast('Network error: ' + err.message, 'error'));
}

function renderManagerReports(data) {
  const summary  = data.summary   || {};
  const daily    = data.daily     || [];
  const items    = data.top_items || [];
  const recent   = data.recent    || [];
  const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };

  set('rptRevenue',   '₱' + parseFloat(summary.total_revenue   || 0).toFixed(2));
  set('rptOrders',    summary.total_orders || 0);
  set('rptTxCount',   (summary.total_orders||0) + ' orders this period');
  set('rptAvg',       '₱' + parseFloat(summary.avg_order_value || 0).toFixed(2));
  set('rptDiscounts', '₱' + parseFloat(summary.total_discounts || 0).toFixed(2));

  // Payment breakdown
  const payEl = document.getElementById('paymentBreakdown');
  if (payEl) {
    const cashTotal    = parseFloat(summary.cash_sales    || 0);
    const ewalletTotal = parseFloat(summary.ewallet_sales || 0);
    const onlineTotal  = parseFloat(summary.online_sales  || 0);
    const maxPay = Math.max(cashTotal, ewalletTotal, onlineTotal, 1);
    const rows = [
      { label: '💵 Cash',        val: cashTotal,    color: 'var(--accent)' },
      { label: '📱 E-Wallet',    val: ewalletTotal, color: 'var(--green)'  },
      { label: '🏦 Online Bank', val: onlineTotal,  color: 'var(--blue)'   },
    ];
    payEl.innerHTML = rows.map(r => `
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
        <span style="font-size:13px;min-width:110px;color:var(--text2);">${r.label}</span>
        <div class="progress-bar" style="flex:1;"><div class="progress-fill" style="width:${(r.val/maxPay)*100}%;background:${r.color};"></div></div>
        <span style="font-size:13px;font-weight:600;min-width:80px;text-align:right;">₱${r.val.toFixed(2)}</span>
      </div>`).join('');
  }

  const dailyEl = document.getElementById('dailySalesBody');
  if (dailyEl) {
    dailyEl.innerHTML = daily.map(d =>
      `<tr>
        <td style="font-weight:500;">${d.sale_date}</td>
        <td style="color:var(--accent);font-weight:700;">₱${parseFloat(d.revenue).toFixed(2)}</td>
        <td>${d.orders}</td>
      </tr>`
    ).join('') || '<tr><td colspan="3" class="empty-state">No data for this period.</td></tr>';
  }

  const maxQty = Math.max(...items.map(i => parseInt(i.qty_sold||0)), 1);
  const topEl  = document.getElementById('topItemsChart');
  if (topEl) {
    topEl.innerHTML = items.length ? items.map((i, idx) =>
      `<div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
        <span style="font-size:12px;color:var(--text3);min-width:16px;">${idx+1}</span>
        <span style="font-size:18px;">${i.emoji||'🍽'}</span>
        <div style="flex:1;">
          <div style="font-size:13px;font-weight:500;margin-bottom:4px;">${i.item_name}</div>
          <div class="progress-bar"><div class="progress-fill" style="width:${(parseInt(i.qty_sold)/maxQty)*100}%;background:var(--accent);"></div></div>
        </div>
        <span style="font-size:12px;color:var(--text2);">${i.qty_sold} sold</span>
      </div>`
    ).join('') : '<div class="empty-state">No sales data yet.</div>';
  }

  const txEl = document.getElementById('txTable');
  if (txEl) {
    txEl.innerHTML = recent.map(t =>
      `<tr>
        <td style="color:var(--text3);font-size:11px;">${t.order_number}</td>
        <td>${t.table_name}</td>
        <td style="color:var(--text3);">${t.username||'—'}</td>
        <td style="color:var(--accent);font-weight:700;">₱${parseFloat(t.total).toFixed(2)}</td>
        <td>${t.payment_method==='cash'?'💵':t.payment_method==='e_wallet'?'📱':'🏦'} ${t.payment_method}</td>
        <td style="color:var(--text3);font-size:12px;">${new Date(t.created_at).toLocaleString('en-PH',{month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'})}</td>
      </tr>`
    ).join('') || '<tr><td colspan="6" class="empty-state">No transactions in this period.</td></tr>';
  }
}

function clearSalesData() {
  toast('Sales data is stored in the database and cannot be cleared from here.', 'info');
}

function renderOrderHistory() {
  const countEl = document.getElementById('orderCount');
  if (countEl) countEl.textContent = transactions.length + ' orders';
  const body = document.getElementById('orderHistoryBody');
  if (!body) return;
  body.innerHTML = [...transactions].reverse().map(t =>
    `<tr><td>#${t.id}</td><td>${t.table}</td><td>${t.items.map(i => `${i.emoji} ${i.name} x${i.qty}`).join(', ')}</td><td style="color:var(--accent);font-weight:600;">₱${t.total.toFixed(2)}</td><td style="color:var(--green);">${t.discount > 0 ? '-₱' + t.discount.toFixed(2) : '—'}</td><td>₱${t.change.toFixed(2)}</td><td style="color:var(--text3);">${new Date(t.time).toLocaleString('en-PH', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</td></tr>`
  ).join('') || '<tr><td colspan="7" class="empty-state">No orders yet</td></tr>';
}

// ─────────────────────────────────────────────────────────────────
// INGREDIENT FORM (manager sidebar)
// ─────────────────────────────────────────────────────────────────
function toggleIngredientSection() {
  const content = document.getElementById('ingredientSectionContent');
  const toggle  = document.querySelector('.sidebar-section-toggle');
  if (!content) return;
  const isOpen = content.style.display !== 'none';
  content.style.display = isOpen ? 'none' : 'block';
  if (toggle) toggle.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
}

function addIngredient(event) {
  event.preventDefault();
  const name  = document.getElementById('ingName').value.trim();
  const unit  = document.getElementById('ingUnit').value;
  const stock = parseFloat(document.getElementById('ingStock').value);
  if (!name)              { toast('Ingredient name required!', 'error'); return; }
  if (!stock || stock < 0) { toast('Valid stock quantity required!', 'error'); return; }

  apiPost({ action: 'save_ingredient', ingredient: { name, unit, stock, status: 'available' } })
    .then(result => {
      if (result.success) {
        ingredients.push({ id: result.ingredient_id, name, unit, stock, status: 'available' });
        document.getElementById('ingredientForm')?.reset();
        toast('✅ Ingredient added!', 'success');
        if (inventoryMode === 'ingredients') renderInventory();
      } else {
        toast(result.message || 'Failed to save ingredient.', 'error');
      }
    })
    .catch(err => toast('Network error: ' + err.message, 'error'));
}

// ─────────────────────────────────────────────────────────────────
// MODAL HELPERS
// ─────────────────────────────────────────────────────────────────
function openModal(id)  { document.getElementById(id)?.classList.add('open'); }
function closeModal(id) { document.getElementById(id)?.classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); });
});

// ─────────────────────────────────────────────────────────────────
// TOAST
// ─────────────────────────────────────────────────────────────────
function toast(msg, type = 'info') {
  const container = document.getElementById('toastContainer');
  if (!container) return;
  const el = document.createElement('div');
  el.className = `toast ${type}`;
  el.innerHTML = `<span>${msg}</span>`;
  container.appendChild(el);
  setTimeout(() => el.remove(), 3000);
}

// ─────────────────────────────────────────────────────────────────
// INIT — load menu from DB on first load
// ─────────────────────────────────────────────────────────────────
loadMenuFromDB().then(() => {
  renderMenuGrid();
  renderCartFooter();
});