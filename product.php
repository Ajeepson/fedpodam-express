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
                
                let priceHtml = '';
                if (product.old_price && parseFloat(product.old_price) > parseFloat(product.price)) {
                    priceHtml += `<span class="old-price" style="font-size:1.2rem;">${new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN' }).format(product.old_price)}</span> `;
                }
                priceHtml += new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN' }).format(product.price);

                const html = `
                    <div style="display:flex; gap:2rem; flex-wrap:wrap; background:white; padding:2rem; border-radius:8px;">
                        <div style="flex:1; min-width:300px;">
                            <img src="${product.image_url}" style="width:100%; border-radius:8px;">
                        </div>
                        <div style="flex:1;">
                            <h2 style="margin-top:0;">${product.name}</h2>
                            <div class="price" style="font-size:2rem; margin-bottom:1rem;">${priceHtml}</div>
                            <div class="star-rating" style="margin-bottom:1rem;">${'★'.repeat(Math.round(product.average_rating || 0))}${'☆'.repeat(5 - Math.round(product.average_rating || 0))} <span style="font-size:0.9rem; color:#666;">(${product.review_count || 0} reviews)</span></div>
                            <p><strong>Category:</strong> ${product.category}</p>
                            <p><strong>Stock:</strong> ${product.stock_quantity} units available</p>
                            <p>${product.description}</p>
                            <br>
                            <button class="btn-buy" onclick="addToCart(${product.id})" style="width:auto; padding: 10px 20px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16" style="margin-right:8px;">
                                    <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                                </svg> Add to Cart
                            </button>
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