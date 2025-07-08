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

    // Función para detectar patrones sospechosos de inyección SQL
    function detectarInyeccionSQL(valor) {
        const patronesPeligrosos = [
            /('|(\\)|(;)|(--)|(\s(or|and)\s)|(union\s+select)|(drop\s+table)|(insert\s+into)|(delete\s+from)|(update\s+set))/i,
            /(script\s*>)|(javascript:)|(vbscript:)|(onload\s*=)|(onerror\s*=)/i,
            /(<\s*script)|(alert\s*\()|(eval\s*\()|(document\.)/i,
            /(select\s+.*\s+from)|(union\s+all)|(exec\s*\()|(xp_)/i
        ];
        
        return patronesPeligrosos.some(patron => patron.test(valor));
    }
    
    // Función para limpiar entrada de caracteres peligrosos
    function limpiarEntrada(valor) {
        return valor
            .replace(/[<>\"'\\]/g, '') // Remover caracteres HTML y comillas
            .replace(/(\s(or|and|union|select|drop|insert|delete|update|script)\s)/gi, '') // Remover palabras SQL peligrosas
            .trim();
    }
    
    // Aplicar validación de seguridad a todos los campos de texto
    const camposTexto = [cardHolder];
    
    camposTexto.forEach(campo => {
        if (campo) {
            campo.addEventListener('input', function(e) {
                let valor = e.target.value;
                
                if (detectarInyeccionSQL(valor)) {
                    e.target.value = limpiarEntrada(valor);
                    e.target.style.borderColor = '#ff6b6b';
                    e.target.style.boxShadow = '0 0 5px rgba(255,107,107,0.3)';
                    
                    // Mostrar mensaje de advertencia
                    const errorDiv = e.target.nextElementSibling;
                    if (errorDiv && errorDiv.classList.contains('error-message')) {
                        errorDiv.textContent = 'Caracteres no permitidos detectados';
                        errorDiv.style.color = '#ff6b6b';
                    }
                } else {
                    e.target.style.borderColor = '';
                    e.target.style.boxShadow = '';
                }
            });
        }
    });
    
    // Validación adicional en el envío del formulario
    const formularioOriginal = form.addEventListener;
    form.addEventListener('submit', function(e) {
        let erroresSeguridad = [];
        
        // Verificar todos los campos de texto
        [cardHolder].forEach(campo => {
            if (campo && detectarInyeccionSQL(campo.value)) {
                erroresSeguridad.push('El campo "' + campo.placeholder + '" contiene caracteres no permitidos');
            }
        });
        
        // Si hay errores de seguridad, prevenir envío
        if (erroresSeguridad.length > 0) {
            e.preventDefault();
            alert('Error de seguridad detectado:\n• ' + erroresSeguridad.join('\n• '));
            return false;
        }
    });
});
