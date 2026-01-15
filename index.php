<?php include 'includes/header.php'; ?>

    <!-- Main Content -->
    <div class="container">
        <!-- Dynamic Hero Section -->
        <div id="hero-container" class="hero-section"></div>

        <div class="search-container">
            <input type="text" id="search-input" placeholder="Search products...">
            <select id="category-select">
                <option value="All">All Categories</option>
                <option value="Apparel">Apparel</option>
                <option value="Electronics">Electronics</option>
            </select>
            <button class="btn-buy" style="width:auto;" onclick="loadProducts()">Search</button>
        </div>

        <h2>Featured Products</h2>
        <div id="product-list" class="product-grid">
            <!-- Products will be loaded here via JS -->
            <p>Loading products...</p>
        </div>
    </div>

    <!-- JavaScript Logic -->
    <script>
        // 1. Load Products on Page Load
        document.addEventListener('DOMContentLoaded', () => {
            loadProducts();
            loadBanners();
        });

        function loadProducts() {
            const search = document.getElementById('search-input').value;
            const category = document.getElementById('category-select').value;

            fetch(`api.php?action=get_products&search=${search}&category=${category}`)
                .then(response => response.json())
                .then(data => {
                    const grid = document.getElementById('product-list');
                    grid.innerHTML = ''; // Clear loading text
                    
                    data.forEach(product => {
                        const card = `
                            <div class="product-card">
                                <a href="product.php?id=${product.id}">
                                    <img src="${product.image_url}" alt="${product.name}">
                                </a>
                                <div class="card-body">
                                    <h3>${product.name}</h3>
                                    <p>${product.description.substring(0, 50)}...</p>
                                    <div class="price">$${product.price}</div>
                                    <button class="btn-buy" onclick="addToCart(${product.id})">Add to Cart</button>
                                </div>
                            </div>
                        `;
                        grid.innerHTML += card;
                    });
                })
                .catch(err => console.error('Error loading products:', err));
        }

        function loadBanners() {
            fetch('api.php?action=get_banners')
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
            fetch('api.php?action=add_to_cart', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ id: id })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) alert('Added to cart!');
                location.reload(); // Update cart count in header
            });
        }
    </script>

<?php include 'includes/footer.php'; ?>
