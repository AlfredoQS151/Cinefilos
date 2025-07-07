// Validaciones para el formulario de registro
document.addEventListener('DOMContentLoaded', function() {
    // Animación paneles login/registro
    const signUpButton = document.getElementById('signUp');
    const signInButton = document.getElementById('signIn');
    const container = document.getElementById('container');
    if(signUpButton && signInButton && container) {
        signUpButton.addEventListener('click', () => {
            container.classList.add('right-panel-active');
        });
        signInButton.addEventListener('click', () => {
            container.classList.remove('right-panel-active');
        });
    }

    // Hacer desaparecer el mensaje de registro exitoso después de 4 segundos
    const mensajeRegistroExitoso = document.querySelector('[style*="background:#d4edda"]');
    if (mensajeRegistroExitoso) {
        setTimeout(() => {
            mensajeRegistroExitoso.style.opacity = '0';
            mensajeRegistroExitoso.style.transition = 'opacity 0.5s ease-out';
            setTimeout(() => {
                mensajeRegistroExitoso.style.display = 'none';
            }, 500);
        }, 4000);
    }

    // Validaciones del formulario
    const registroForm = document.getElementById('registroForm');
    
    if (registroForm) {
        // Validación para nombre y apellido (solo letras)
        const nombreInput = document.querySelector('input[name="nombre"]');
        const apellidoInput = document.querySelector('input[name="apellido"]');
        const telefonoInput = document.querySelector('input[name="telefono"]');
        
        // Función para validar solo letras
        function validarSoloLetras(input) {
            input.addEventListener('input', function(e) {
                // Permitir solo letras, espacios y caracteres especiales del español
                const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]*$/;
                if (!regex.test(e.target.value)) {
                    e.target.value = e.target.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
                }
            });
        }
        
        // Función para validar solo números y máximo 10 dígitos
        function validarTelefono(input) {
            input.addEventListener('input', function(e) {
                // Permitir solo números
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
                
                // Limitar a 10 dígitos
                if (e.target.value.length > 10) {
                    e.target.value = e.target.value.slice(0, 10);
                }
            });
        }
        
        // Aplicar validaciones
        if (nombreInput) validarSoloLetras(nombreInput);
        if (apellidoInput) validarSoloLetras(apellidoInput);
        if (telefonoInput) validarTelefono(telefonoInput);
        
        // Validación adicional al enviar el formulario
        registroForm.addEventListener('submit', function(e) {
            let errores = [];

            // Validar nombre
            if (nombreInput && nombreInput.value.trim() === '') {
                errores.push('El nombre es requerido');
            } else if (nombreInput && !/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(nombreInput.value)) {
                errores.push('El nombre solo puede contener letras');
            }

            // Validar apellido
            if (apellidoInput && apellidoInput.value.trim() === '') {
                errores.push('El apellido es requerido');
            } else if (apellidoInput && !/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(apellidoInput.value)) {
                errores.push('El apellido solo puede contener letras');
            }

            // Validar teléfono
            if (telefonoInput && telefonoInput.value.trim() === '') {
                errores.push('El teléfono es requerido');
            } else if (telefonoInput && telefonoInput.value.length !== 10) {
                errores.push('El teléfono debe tener exactamente 10 dígitos');
            }

            // Validar que las contraseñas coincidan (si existen los campos)
            const passwordInput = document.querySelector('input[name="contrasena"]');
            const repetirPasswordInput = document.querySelector('input[name="repetir_contrasena"]');
            if (passwordInput && repetirPasswordInput) {
                if (passwordInput.value !== repetirPasswordInput.value) {
                    errores.push('Las contraseñas no coinciden');
                }
            }

            // Si hay errores, prevenir envío y mostrar mensajes
            if (errores.length > 0) {
                e.preventDefault();
                alert('Por favor corrige los siguientes errores:\n• ' + errores.join('\n• '));
            }
        });
    }
});
