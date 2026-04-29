// ============================================
// ÉTAT DE LA COMMANDE
// ============================================
const order = {
    coffee_type : 'espresso',
    coffee_name : 'Espresso',
    size        : 'S',
    size_name   : 'Small',
    milk_type   : 'whole_milk',
    milk_name   : 'Whole Milk',
    extras      : [],
    base_price  : 5.00,
    size_price  : 0.00,
    milk_price  : 0.00,
    extras_price: 0.00
};

// ============================================
// SÉLECTION SINGLE (Coffee, Size, Milk)
// ============================================
function setupSingleSelect(containerId, orderKey, nameKey, priceKey, summaryId) {
    const container = document.getElementById(containerId);
    container.querySelectorAll('.option-card').forEach(card => {
        card.addEventListener('click', function() {
            // Retirer selected de tous
            container.querySelectorAll('.option-card')
                     .forEach(c => c.classList.remove('selected'));
            // Ajouter au cliqué
            this.classList.add('selected');

            // Mettre à jour l'ordre
            order[orderKey]  = this.dataset.value;
            order[nameKey]   = this.querySelector('.option-name').textContent;
            order[priceKey]  = parseFloat(this.dataset.price);

            // Mettre à jour le summary
            document.getElementById(summaryId).textContent = order[nameKey];

            updatePrice();
        });
    });
}

// ============================================
// SÉLECTION MULTIPLE (Extras)
// ============================================
function setupExtras() {
    const container = document.getElementById('extras-options');
    container.querySelectorAll('.extra-card').forEach(card => {
        card.addEventListener('click', function() {
            this.classList.toggle('selected');

            const value = this.dataset.value;
            const price = parseFloat(this.dataset.price);
            const name  = this.querySelector('.option-name').textContent;

            if (this.classList.contains('selected')) {
                order.extras.push({ value, name, price });
            } else {
                order.extras = order.extras.filter(e => e.value !== value);
            }

            // Mettre à jour summary extras
            const sumExtras = document.getElementById('sum-extras');
            if (order.extras.length === 0) {
                sumExtras.textContent = 'None';
            } else {
                sumExtras.textContent = order.extras.map(e => e.name).join(', ');
            }

            updatePrice();
        });
    });
}

// ============================================
// CALCULER ET AFFICHER LE PRIX
// ============================================
function updatePrice() {
    order.extras_price = order.extras.reduce((sum, e) => sum + e.price, 0);

    const total = order.base_price
                + order.size_price
                + order.milk_price
                + order.extras_price;

    // Mettre à jour price breakdown
    document.getElementById('price-base').textContent   =
        order.base_price.toFixed(2) + ' DT';
    document.getElementById('price-size').textContent   =
        '+' + order.size_price.toFixed(2) + ' DT';
    document.getElementById('price-milk').textContent   =
        '+' + order.milk_price.toFixed(2) + ' DT';
    document.getElementById('price-extras').textContent =
        '+' + order.extras_price.toFixed(2) + ' DT';

    // Total avec animation
    const totalEl = document.getElementById('total-price');
    totalEl.style.transform  = 'scale(1.1)';
    totalEl.style.color      = '#d4a574';
    totalEl.textContent      = total.toFixed(2) + ' DT';

    setTimeout(() => {
        totalEl.style.transform = 'scale(1)';
        totalEl.style.color     = '#9ba36a';
    }, 300);
}

// ============================================
// PASSER LA COMMANDE
// ============================================
async function placeOrder() {
    // Vérifier session
    try {
        const sessionRes = await fetch('auth/check_session.php');
        const sessionData = await sessionRes.json();

        if (!sessionData.logged_in) {
            showToast('⚠️ Please sign in to place your order.', 'warning');
            setTimeout(() => window.location.href = 'signin.html', 2000);
            return;
        }
    } catch (err) {
        showToast('❌ Session check failed. Please sign in.', 'error');
        setTimeout(() => window.location.href = 'signin.html', 2000);
        return;
    }

    const btn = document.getElementById('btn-order');
    btn.textContent = 'Processing...';
    btn.disabled    = true;

    const total = order.base_price
                + order.size_price
                + order.milk_price
                + order.extras_price;

    const formData = new FormData();
    formData.append('coffee_type', order.coffee_type);
    formData.append('size',        order.size);
    formData.append('milk_type',   order.milk_type);
    formData.append('extras',      JSON.stringify(order.extras));
    formData.append('base_price',  order.base_price);
    formData.append('total_price', total.toFixed(2));

    try {
        const response = await fetch('api/customize.php', {
            method : 'POST',
            body   : formData
        });
        const data = await response.json();

        if (data.success) {
            // Ajouter au panier localStorage
            addToCart({
                id: 'custom_' + Date.now(),
                name: `Custom ${order.coffee_name}`,
                price: total,
                quantity: 1,
                type: 'custom',
                details: {
                    coffee_type: order.coffee_type,
                    size: order.size,
                    milk_type: order.milk_type,
                    extras: order.extras
                }
            });

            showToast('✅ Your custom coffee is in your cart! ☕', 'success');
            //setTimeout(() => window.location.href = 'cart.php', 2500);
        } else {
            showToast('❌ ' + data.message, 'error');
            btn.textContent = 'Order Now';
            btn.disabled    = false;
        }

    } catch (err) {
        showToast('❌ Server error. Please try again.', 'error');
        btn.textContent = 'Order Now';
        btn.disabled    = false;
    }
}

// ============================================
// AJOUTER AU PANIER
// ============================================
function addToCart(item) {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];

    // Créer la description de customisation
    let customization = `Size: ${order.size_name}`;
    if (order.milk_type !== 'no_milk') {
        customization += `, Milk: ${order.milk_name}`;
    }
    if (order.extras.length > 0) {
        customization += `, Extras: ${order.extras.map(e => e.name).join(', ')}`;
    }

    item.customization = customization;
    cart.push(item);
    localStorage.setItem('cart', JSON.stringify(cart));

    // Mettre à jour le compteur du panier
    updateCartCount();
}

// ============================================
// METTRE À JOUR COMPTEUR PANIER
// ============================================
function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const total = cart.reduce((sum, item) => sum + item.quantity, 0);
    document.querySelectorAll('.cart-count')
            .forEach(el => el.textContent = total);
}

// ============================================
// TOAST
// ============================================
function showToast(message, type) {
    const old = document.querySelector('.toast-notif');
    if (old) old.remove();

    const colors = {
        success : '#9ba36a',
        error   : '#e74c3c',
        warning : '#d4a574'
    };

    const toast = document.createElement('div');
    toast.className   = 'toast-notif';
    toast.textContent = message;
    toast.style.cssText = `
        position     : fixed;
        bottom       : 30px;
        right        : 30px;
        background   : ${colors[type]};
        color        : white;
        padding      : 14px 25px;
        border-radius: 30px;
        font-weight  : 600;
        font-size    : 0.9rem;
        z-index      : 9999;
        box-shadow   : 0 10px 30px rgba(0,0,0,0.2);
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}

// ============================================
// INITIALISATION
// ============================================
document.addEventListener('DOMContentLoaded', () => {
    // Setup selections
    setupSingleSelect(
        'coffee-options', 'coffee_type', 'coffee_name',
        'base_price', 'sum-coffee'
    );
    setupSingleSelect(
        'size-options', 'size', 'size_name',
        'size_price', 'sum-size'
    );
    setupSingleSelect(
        'milk-options', 'milk_type', 'milk_name',
        'milk_price', 'sum-milk'
    );
    setupExtras();

    // Mettre à jour compteur panier
    updateCartCount();
});