const KEY = 'qpay_cart';

function persist(cart) {
    try {
        localStorage.setItem(KEY, JSON.stringify(cart));
        window.dispatchEvent(new Event('qpay-cart-update'));
    } catch {
        // localStorage unavailable (SSR / private mode)
    }
}

export function getCart() {
    try {
        return JSON.parse(localStorage.getItem(KEY) ?? '[]');
    } catch {
        return [];
    }
}

export function addToCart({ id, name, price, image, stock }, qty = 1) {
    const cart = getCart();
    const max = stock ?? 99;
    const idx = cart.findIndex((i) => i.id === id);
    if (idx >= 0) {
        cart[idx].qty = Math.min(cart[idx].qty + qty, max);
        cart[idx].stock = max; // keep stock in sync
    } else {
        cart.push({ id, name, price, image: image ?? null, stock: max, qty: Math.min(qty, max) });
    }
    persist(cart);
    return cart;
}

export function removeFromCart(productId) {
    const cart = getCart().filter((i) => i.id !== productId);
    persist(cart);
    return cart;
}

export function updateCartQty(productId, qty) {
    if (qty <= 0) return removeFromCart(productId);
    const cart = getCart().map((i) => {
        if (i.id !== productId) return i;
        const max = i.stock ?? 99;
        return { ...i, qty: Math.min(qty, max) };
    });
    persist(cart);
    return cart;
}

export function clearCart() {
    try {
        localStorage.removeItem(KEY);
        window.dispatchEvent(new Event('qpay-cart-update'));
    } catch {}
}

export function getCartCount() {
    return getCart().reduce((s, i) => s + i.qty, 0);
}
