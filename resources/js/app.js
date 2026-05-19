import './bootstrap';
import 'bootstrap';
import '../css/app.css';

const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

window.bbRequest = async function bbRequest(url, options = {}) {
    const response = await fetch(url, {
        headers: {
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(options.headers || {}),
        },
        ...options,
    });

    if (!response.ok) throw new Error('Request failed');
    return response.json();
};

window.bbToast = function bbToast(message, type = 'success') {
    if (type === 'danger') type = 'error';
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: type,
            title: message,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    } else {
        alert(message);
    }
};

document.addEventListener('submit', async (event) => {
    const form = event.target.closest('[data-ajax-form]');
    if (!form) return;
    event.preventDefault();

    try {
        const payload = new FormData(form);
        const method = form.dataset.method || form.method || 'POST';
        if (['PATCH', 'DELETE'].includes(method.toUpperCase())) payload.append('_method', method.toUpperCase());
        const data = await window.bbRequest(form.action, { method: 'POST', body: payload });
        window.bbToast(data.message || data.status || 'Updated');
        if (form.matches('[action*="newsletter"]') && form.querySelector('[name="email"]')) {
            form.reset();
        }
        if (data.cart_count !== undefined) {
            document.querySelectorAll('[data-cart-count]').forEach(el => el.textContent = data.cart_count);
        }
        if (form.dataset.reload === 'true') setTimeout(() => window.location.reload(), 600);
    } catch (error) {
        window.bbToast('Something went wrong. Please try again.', 'danger');
    }
});

document.addEventListener('click', async (event) => {
    const btn = event.target.closest('[data-wishlist-toggle]');
    if (!btn) return;
    event.preventDefault();
    try {
        const data = await window.bbRequest(btn.dataset.wishlistToggle, { method: 'POST' });
        btn.classList.toggle('text-danger', data.operation === 'added');
        document.querySelectorAll('[data-wishlist-count]').forEach(el => el.textContent = data.wishlist_count);
        window.bbToast(data.operation === 'added' ? 'Added to wishlist' : 'Removed from wishlist');
    } catch (error) {
        window.bbToast('Please login first.', 'warning');
    }
});
