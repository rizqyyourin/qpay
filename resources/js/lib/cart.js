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

export function addToCart({ id, name, price, image }, qty = 1) {
    const cart = getCart();
    const idx = cart.findIndex((i) => i.id === id);
    if (idx >= 0) {
        cart[idx].qty = Math.min(cart[idx].qty + qty, 99);
    } else {
        cart.push({ id, name, price, image: image ?? null, qty });
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
    const cart = getCart().map((i) => (i.id === productId ? { ...i, qty } : i));
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
