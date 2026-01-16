<?php include 'includes/header.php'; ?>

    <!-- Main Content -->
    <div class="container">
        <!-- Slogan -->
        <div class="site-slogan">Fedpodam Express: Bridging Campus & Commerce.</div>

        <!-- Dynamic Hero Section -->
        <div id="hero-container" class="hero-section"></div>

        <div class="main-layout">
            <!-- Sidebar (Filters) -->
            <aside class="sidebar">
                <div class="sidebar-filter">
                    <h3>Filter Products</h3>
                    <input type="text" id="search-input" placeholder="Search products...">
                    <select id="category-select">
                        <option value="All">All Categories</option>
                        <option value="Apparel">Apparel</option>
                        <option value="Electronics">Electronics</option>
                    </select>
                    <button class="btn-buy" onclick="loadProducts(true)">Apply Filters</button>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="content-area">
                <h2 style="margin-top:0;">Featured Products</h2>
                <div id="product-list" class="product-grid">
                    <!-- Products will be loaded here via JS -->
                    <p>Loading products...</p>
                </div>
                <!-- Load More Button -->
                <div style="text-align:center; margin: 2rem 0;">
                    <button id="load-more-btn" class="btn-buy" style="width:auto; display:none;" onclick="loadMore()">Load More</button>
                </div>
            </main>
        </div>


        <!-- Testimonials Section -->
        <div class="testimonials-section">
            <h2>What Our Community Says</h2>
            <div class="testimonial-grid">
                <div class="testimonial-card">
                    <p>"Fedpodam Express made getting my textbooks and snacks so much easier. Delivery to the hostel was super fast!"</p>
                    <h4>- Amina Y., CS Dept</h4>
                </div>
                <div class="testimonial-card">
                    <p>"I love the Adire collection! Great quality and it supports local student entrepreneurs."</p>
                    <h4>- Emeka O., Staff</h4>
                </div>
                <div class="testimonial-card">
                    <p>"Reliable payment options and the customer support is actually helpful. Highly recommended."</p>
                    <h4>- Zainab B., SLT Dept</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Logic -->
    <script>
        let currentPage = 1;

        // 1. Load Products on Page Load
        document.addEventListener('DOMContentLoaded', () => {
            loadProducts(true);
            loadBanners();
        });

        function loadProducts(reset = false) {
            if (reset) {
                currentPage = 1;
                document.getElementById('product-list').innerHTML = '<p>Loading products...</p>';
            }

            const search = document.getElementById('search-input').value;
            const category = document.getElementById('category-select').value;
            const btn = document.getElementById('load-more-btn');

            fetch(`api/api.php?action=get_products&search=${search}&category=${category}&page=${currentPage}`)
                .then(response => response.json())
                .then(data => {
                    const grid = document.getElementById('product-list');
                    if (reset) grid.innerHTML = '';
                    
                    if (data.length === 0 && reset) {
                        grid.innerHTML = '<p>No products found.</p>';
                        btn.style.display = 'none';
                        return;
                    }
                    
                    data.forEach(product => {
                        let priceHtml = '';
                        if (product.old_price && parseFloat(product.old_price) > parseFloat(product.price)) {
                            priceHtml += `<span class="old-price">${new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN' }).format(product.old_price)}</span>`;
                        }
                        priceHtml += new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN' }).format(product.price);

                        const card = `
                            <div class="product-card">
                                <a href="product.php?id=${product.id}">
                                    <img src="${product.image_url}" alt="${product.name}">
                                </a>
                                <div class="card-body">
                                    <h3>${product.name}</h3>
                                    <p>${product.description.substring(0, 50)}...</p>
                                    <div class="price">${priceHtml}</div>
                                    <button class="btn-buy" onclick="addToCart(${product.id})" title="Add to Cart">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        `;
                        grid.innerHTML += card;
                    });

                    // Hide button if less than limit returned (end of list)
                    if (data.length < 8) {
                        btn.style.display = 'none';
                    } else {
                        btn.style.display = 'inline-block';
                    }
                })
                .catch(err => console.error('Error loading products:', err));
        }

        function loadMore() {
            currentPage++;
            loadProducts(false);
        }

        function loadBanners() {
            fetch('api/api.php?action=get_banners')
                .then(res => res.json())
                .then(data => {
                    const container = document.getElementById('hero-container');
                    data.forEach(banner => {
                        const div = document.createElement('div');
                        div.className = 'hero-banner';
                        div.innerHTML = `
                            <img src="${banner.image_url}" style="width:100%; height:100%; object-fit:cover;">
                            <div class="hero-text">
                                <h2>${banner.title}</h2>
                                <p>${banner.subtitle}</p>
                            </div>
                        `;
                        container.appendChild(div);
                    });
                });
        }

        // 2. Cart Logic (Simple Alert)
        function addToCart(id) {
            fetch('api/api.php?action=add_to_cart', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ id: id })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) alert('Added to cart!');
                location.reload(); // Update cart count in header
            })
            .catch(err => {
                console.error(err);
                alert('Error connecting to API. Check console.');
            });
        }
    </script>

<?php include 'includes/footer.php'; ?>
