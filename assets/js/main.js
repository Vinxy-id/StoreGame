document.addEventListener('DOMContentLoaded', () => {
    // Handling selection for products
    const productItems = document.querySelectorAll('.product-item');
    productItems.forEach(item => {
        item.addEventListener('click', () => {
            productItems.forEach(i => i.classList.remove('active'));
            item.classList.add('active');
            const radio = item.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
            }
        });
    });

    // Handling selection for payment methods
    const paymentItems = document.querySelectorAll('.payment-item');
    paymentItems.forEach(item => {
        item.addEventListener('click', () => {
            paymentItems.forEach(i => i.classList.remove('active'));
            item.classList.add('active');
            const radio = item.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
            }
        });
    });

    // Simple alert fade out if exists
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease';
            setTimeout(() => alert.remove(), 500);
        }, 3000);
    });
});
