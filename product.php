<?php include 'includes/header.php'; ?>

<div class="container" id="product-detail">
    <p>Loading product details...</p>
</div>

<script>
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('id');

    if (productId) {
        fetch(`api/api.php?action=get_product&id=${productId}`)
            .then(res => res.json())
            .then(product => {
                if (!product) {
                    document.getElementById('product-detail').innerHTML = '<p>Product not found.</p>';
                    return;
                }
                
                const html = `
                    <div style="display:flex; gap:2rem; flex-wrap:wrap; background:white; padding:2rem; border-radius:8px;">
                        <div style="flex:1; min-width:300px;">
                            <img src="${product.image_url}" style="width:100%; border-radius:8px;">
                        </div>
                        <div style="flex:1;">
                            <h2 style="margin-top:0;">${product.name}</h2>
                            <div class="price" style="font-size:2rem; margin-bottom:1rem;">${new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN' }).format(product.price)}</div>
                            <div class="star-rating" style="margin-bottom:1rem;">${'★'.repeat(Math.round(product.average_rating || 0))}${'☆'.repeat(5 - Math.round(product.average_rating || 0))} <span style="font-size:0.9rem; color:#666;">(${product.review_count || 0} reviews)</span></div>
                            <p><strong>Category:</strong> ${product.category}</p>
                            <p><strong>Stock:</strong> ${product.stock_quantity} units available</p>
                            <p>${product.description}</p>
                            <br>
                            <button class="btn-buy" onclick="addToCart(${product.id})">Add to Cart</button>
                        </div>
                    </div>

                    <div class="review-section">
                        <h3>Customer Reviews</h3>
                        <?php if(isset($_SESSION['user_id'])): ?>
                        <div class="form-container" style="margin:0 0 2rem 0; max-width:100%; box-shadow:none; border:1px solid #eee;">
                            <h4>Write a Review</h4>
                            <form id="reviewForm">
                                <input type="hidden" name="product_id" value="${product.id}">
                                <div class="form-group">
                                    <label>Rating</label>
                                    <select name="rating" style="padding:5px;">
                                        <option value="5">5 Stars - Excellent</option>
                                        <option value="4">4 Stars - Good</option>
                                        <option value="3">3 Stars - Average</option>
                                        <option value="2">2 Stars - Poor</option>
                                        <option value="1">1 Star - Terrible</option>
                                    </select>
                                </div>
                                <div class="form-group"><label>Comment</label><textarea name="comment" style="width:100%; padding:8px;" required></textarea></div>
                                <button type="submit" class="btn-buy" style="width:auto;">Submit Review</button>
                            </form>
                        </div>
                        <?php endif; ?>
                        <div id="reviews-list">Loading reviews...</div>
                    </div>
                `;
                document.getElementById('product-detail').innerHTML = html;
                loadReviews(productId);
                
                // Attach event listener dynamically
                const form = document.getElementById('reviewForm');
                if(form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const data = Object.fromEntries(new FormData(this).entries());
                        fetch('api/api.php?action=add_review', { method: 'POST', body: JSON.stringify(data) })
                        .then(res => res.json()).then(d => { 
                            if(d.success) { alert('Review submitted!'); loadReviews(productId); }
                            else alert(d.message);
                        });
                    });
                }
            });
    }

    function loadReviews(id) {
        fetch(`api/api.php?action=get_reviews&id=${id}`)
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('reviews-list');
            if(data.length === 0) {
                container.innerHTML = '<p>No reviews yet. Be the first!</p>';
                return;
            }
            container.innerHTML = data.map(r => `
                <div class="review-card">
                    <div style="display:flex; justify-content:space-between;">
                        <strong>${r.full_name}</strong>
                        <span class="star-rating" style="font-size:1rem;">${'★'.repeat(r.rating)}</span>
                    </div>
                    <p style="margin:5px 0;">${r.comment}</p>
                    <small style="color:#888;">${r.created_at}</small>
                </div>
            `).join('');
        });
    }

    function addToCart(id) {
        fetch('api/api.php?action=add_to_cart', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id: id })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) { alert('Added to cart!'); location.reload(); }
        });
    }
</script>

<?php include 'includes/footer.php'; ?>