// Frontend helper to fetch and render products
async function fetchProducts(q = '', category = ''){
  const params = new URLSearchParams();
  if (q) params.set('q', q);
  if (category) params.set('category', category);
  const resp = await fetch('/api/products?' + params.toString());
  return resp.json();
}

// Render on index page
async function renderFeatured(){
  const container = document.getElementById('featured');
  if (!container) return;
  const list = await fetchProducts();
  container.innerHTML = '';
  list.slice(0,4).forEach(p => {
    const el = document.createElement('div'); el.className='card';
    el.innerHTML = `<img src="${p.img}" alt="${p.name}" onerror="this.style.background='#ddd'">`+
      `<h3>${p.name}</h3><p>₦${p.price}</p><a href="product.html?id=${p.id}" class="btn">View</a>`;
    container.appendChild(el);
  });
}

// Shop page render
async function renderShop(){
  const container = document.getElementById('products');
  if (!container) return;
  const category = new URLSearchParams(location.search).get('category') || document.getElementById('categorySelect')?.value || '';
  const q = new URLSearchParams(location.search).get('q') || '';
  document.getElementById('categorySelect')?.addEventListener('change', (e)=>{
    location.search = 'category='+e.target.value;
  });
  const list = await fetchProducts(q, category);
  container.innerHTML = '';
  list.forEach(p => {
    const el = document.createElement('div'); el.className='card';
    el.innerHTML = `<img src="${p.img}" alt="${p.name}">`+
      `<h3>${p.name}</h3><p>₦${p.price}</p><a href="product.html?id=${p.id}" class="btn">View</a>`;
    container.appendChild(el);
  });
}

// Product page
async function renderProduct(){
  const id = new URLSearchParams(location.search).get('id');
  if (!id) return;
  const resp = await fetch('/api/products/'+id);
  if (!resp.ok) return;
  const p = await resp.json();
  const container = document.getElementById('productDetail');
  container.innerHTML = `<div class="card"><img src="${p.img}" alt="${p.name}"><h2>${p.name}</h2><p>₦${p.price}</p><p>${p.desc}</p><button class="btn" onclick="addToCart(${p.id})">Add to cart</button></div>`;
}

// Cart (localStorage simple)
function addToCart(id){
  const cart = JSON.parse(localStorage.getItem('cart') || '[]');
  cart.push(Number(id));
  localStorage.setItem('cart', JSON.stringify(cart));
  alert('Added to cart');
}

function renderCart(){
  const container = document.getElementById('cartItems');
  if (!container) return;
  const cart = JSON.parse(localStorage.getItem('cart') || '[]');
  if (cart.length===0){ container.innerHTML='<p>Your cart is empty</p>'; return; }
  fetch('/api/products').then(r=>r.json()).then(all=>{
    const items = cart.map(id=>all.find(x=>x.id===id)).filter(Boolean);
    container.innerHTML = items.map(it=>`<div class="card"><h4>${it.name}</h4><p>₦${it.price}</p></div>`).join('');
  });
}

// Checkout form
if (document.getElementById('checkoutForm')){
  document.getElementById('checkoutForm').addEventListener('submit', (e)=>{
    e.preventDefault();
    localStorage.removeItem('cart');
    alert('Order placed. We will contact you.');
    location.href='index.html';
  });
}

// Init depending on page
window.addEventListener('load', ()=>{
  renderFeatured();
  renderShop();
  renderProduct();
  renderCart();

  // Search
  document.getElementById('searchBtn')?.addEventListener('click', ()=>{
    const q = document.getElementById('searchInput').value || '';
    location.href = 'shop.html?q='+encodeURIComponent(q);
  });
});
