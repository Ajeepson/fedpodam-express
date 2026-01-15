<?php include 'includes/header.php'; ?>

<div class="container" id="product-detail">
    <p>Loading product details...</p>
</div>

<script>
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('id');

    if (productId) {
        fetch(`api.php?action=get_product&id=${productId}`)
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
                            <div class="price" style="font-size:2rem; margin-bottom:1rem;">$${product.price}</div>
                            <p><strong>Category:</strong> ${product.category}</p>
                            <p><strong>Stock:</strong> ${product.stock_quantity} units available</p>
                            <p>${product.description}</p>
                            <br>
                            <button class="btn-buy" onclick="addToCart(${product.id})">Add to Cart</button>
                        </div>
                    </div>
                `;
                document.getElementById('product-detail').innerHTML = html;
            });
    }

    function addToCart(id) {
        fetch('api.php?action=add_to_cart', {
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