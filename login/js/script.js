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
        
        // Validación en tiempo real de contraseñas
        const contrasenaInput = document.querySelector('input[name="contrasena"]');
        const repiteContrasenaInput = document.querySelector('input[name="repite_contrasena"]');
        
        function validarCoincidenciaContrasenas() {
            if (contrasenaInput && repiteContrasenaInput) {
                if (repiteContrasenaInput.value && contrasenaInput.value !== repiteContrasenaInput.value) {
                    repiteContrasenaInput.style.borderColor = '#ff0000';
                    repiteContrasenaInput.style.boxShadow = '0 0 5px rgba(255,0,0,0.3)';
                } else if (repiteContrasenaInput.value && contrasenaInput.value === repiteContrasenaInput.value) {
                    repiteContrasenaInput.style.borderColor = '#00ff00';
                    repiteContrasenaInput.style.boxShadow = '0 0 5px rgba(0,255,0,0.3)';
                } else {
                    repiteContrasenaInput.style.borderColor = '';
                    repiteContrasenaInput.style.boxShadow = '';
                }
            }
        }
        
        if (contrasenaInput) {
            contrasenaInput.addEventListener('input', validarCoincidenciaContrasenas);
        }
        if (repiteContrasenaInput) {
            repiteContrasenaInput.addEventListener('input', validarCoincidenciaContrasenas);
        }
        
        // Validación adicional al enviar el formulario
        registroForm.addEventListener('submit', function(e) {
            let errores = [];
            
            // Obtener campos de contraseña
            const contrasenaInput = document.querySelector('input[name="contrasena"]');
            const repiteContrasenaInput = document.querySelector('input[name="repite_contrasena"]');
            
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
            
            // Validar contraseñas
            if (contrasenaInput && contrasenaInput.value.trim() === '') {
                errores.push('La contraseña es requerida');
            }
            
            if (repiteContrasenaInput && repiteContrasenaInput.value.trim() === '') {
                errores.push('Debe repetir la contraseña');
            }
            
            // Validar que las contraseñas coincidan
            if (contrasenaInput && repiteContrasenaInput && 
                contrasenaInput.value.trim() !== '' && repiteContrasenaInput.value.trim() !== '') {
                if (contrasenaInput.value !== repiteContrasenaInput.value) {
                    errores.push('Las contraseñas no coinciden');
                    repiteContrasenaInput.style.borderColor = '#ff0000';
                    repiteContrasenaInput.style.boxShadow = '0 0 5px rgba(255,0,0,0.3)';
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
