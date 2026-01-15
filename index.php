<?php include 'includes/header.php'; ?>

    <!-- Main Content -->
    <div class="container">
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
            fetch('api.php?action=get_products')
                .then(response => response.json())
                .then(data => {
                    const grid = document.getElementById('product-list');
                    grid.innerHTML = ''; // Clear loading text
                    
                    data.forEach(product => {
                        const card = `
                            <div class="product-card">
                                <img src="${product.image_url}" alt="${product.name}">
                                <div class="card-body">
                                    <h3>${product.name}</h3>
                                    <p>${product.description}</p>
                                    <div class="price">$${product.price}</div>
                                    <button class="btn-buy" onclick="addToCart(${product.id})">Add to Cart</button>
                                </div>
                            </div>
                        `;
                        grid.innerHTML += card;
                    });
                })
                .catch(err => console.error('Error loading products:', err));
        });

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
