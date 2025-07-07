// Validaciones avanzadas para el formulario de pago

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('paymentForm');
    if (!form) return;
    const cardNumber = form['card-number'];
    const cardExpiry = form['card-expiry'];
    const cardCvc = form['card-cvc'];
    const cardHolder = form['card-holder'];
    const postalCode = form['postal-code'];
    const errors = form.querySelectorAll('.error-message');

    // Solo números para número de tarjeta, máximo 16
    cardNumber.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0,16);
    });

    // Solo números para CVC, máximo 3
    cardCvc.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0,3);
    });

    // Solo letras para nombre del titular
    cardHolder.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
    });

    // Solo números para código postal, máximo 5
    postalCode.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0,5);
    });

    // Expira: formato MM/YY, autoinserta /
    cardExpiry.addEventListener('input', function(e) {
        let val = this.value.replace(/[^0-9]/g, '');
        if (val.length > 4) val = val.slice(0,4);
        if (val.length > 2) val = val.slice(0,2) + '/' + val.slice(2);
        this.value = val;
    });

    form.addEventListener('submit', function(e) {
        let valid = true;
        errors.forEach(el => el.textContent = '');

        // Número de tarjeta
        if (cardNumber.value.length !== 16) {
            errors[0].textContent = 'Debes ingresar 16 números.';
            valid = false;
        }
        // Expira
        if (!/^\d{2}\/\d{2}$/.test(cardExpiry.value)) {
            errors[1].textContent = 'Formato MM/AA (4 números).';
            valid = false;
        }
        // CVC
        if (cardCvc.value.length !== 3) {
            errors[2].textContent = 'CVC de 3 números.';
            valid = false;
        }
        // Nombre del titular
        if (!/^([a-zA-ZáéíóúÁÉÍÓÚñÑ]+\s?)+$/.test(cardHolder.value) || cardHolder.value.trim().length < 3) {
            errors[3].textContent = 'Solo letras, mínimo 3.';
            valid = false;
        }
        // Código postal
        if (!/^\d{5}$/.test(postalCode.value)) {
            errors[4].textContent = 'Código postal de 5 números.';
            valid = false;
        }
        if (!valid) e.preventDefault();
    });
});
