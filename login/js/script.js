// Validaciones para el formulario de registro
document.addEventListener('DOMContentLoaded', function() {
    console.log('JavaScript cargado correctamente');
    
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
        console.log('Formulario de registro encontrado');
        
        // Obtener todos los campos
        const nombreInput = document.querySelector('input[name="nombre"]');
        const apellidoInput = document.querySelector('input[name="apellido"]');
        const telefonoInput = document.querySelector('input[name="telefono"]');
        const contrasenaInput = document.querySelector('input[name="contrasena"]');
        const repiteContrasenaInput = document.querySelector('input[name="repite_contrasena"]');
        
        console.log('Campos encontrados:', {
            nombre: nombreInput,
            apellido: apellidoInput,
            telefono: telefonoInput,
            contrasena: contrasenaInput,
            repiteContrasena: repiteContrasenaInput
        });
        
        // Función para validar solo letras
        function validarSoloLetras(input) {
            if (input) {
                input.addEventListener('input', function(e) {
                    const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]*$/;
                    if (!regex.test(e.target.value)) {
                        e.target.value = e.target.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
                    }
                });
            }
        }
        
        // Función para validar teléfono
        function validarTelefono(input) {
            if (input) {
                input.addEventListener('input', function(e) {
                    e.target.value = e.target.value.replace(/[^0-9]/g, '');
                    if (e.target.value.length > 10) {
                        e.target.value = e.target.value.slice(0, 10);
                    }
                });
            }
        }
        
        // Función para validar contraseñas en tiempo real
        function validarContrasenas() {
            if (contrasenaInput && repiteContrasenaInput) {
                const password1 = contrasenaInput.value;
                const password2 = repiteContrasenaInput.value;
                
                if (password2 === '') {
                    // Campo vacío, resetear estilo
                    repiteContrasenaInput.style.borderColor = '';
                    repiteContrasenaInput.style.boxShadow = '';
                } else if (password1 === password2) {
                    // Contraseñas coinciden
                    repiteContrasenaInput.style.borderColor = '#28a745';
                    repiteContrasenaInput.style.boxShadow = '0 0 5px rgba(40, 167, 69, 0.3)';
                } else {
                    // Contraseñas no coinciden
                    repiteContrasenaInput.style.borderColor = '#dc3545';
                    repiteContrasenaInput.style.boxShadow = '0 0 5px rgba(220, 53, 69, 0.3)';
                }
            }
        }
        
        // Aplicar validaciones
        validarSoloLetras(nombreInput);
        validarSoloLetras(apellidoInput);
        validarTelefono(telefonoInput);
        
        // Eventos para validar contraseñas
        if (contrasenaInput) {
            contrasenaInput.addEventListener('input', validarContrasenas);
        }
        if (repiteContrasenaInput) {
            repiteContrasenaInput.addEventListener('input', validarContrasenas);
        }
        
        // Validación al enviar el formulario
        registroForm.addEventListener('submit', function(e) {
            console.log('Enviando formulario...');
            let errores = [];
            
            // Validar nombre
            if (!nombreInput || nombreInput.value.trim() === '') {
                errores.push('El nombre es requerido');
            } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(nombreInput.value)) {
                errores.push('El nombre solo puede contener letras');
            }
            
            // Validar apellido
            if (!apellidoInput || apellidoInput.value.trim() === '') {
                errores.push('El apellido es requerido');
            } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(apellidoInput.value)) {
                errores.push('El apellido solo puede contener letras');
            }
            
            // Validar teléfono
            if (!telefonoInput || telefonoInput.value.trim() === '') {
                errores.push('El teléfono es requerido');
            } else if (telefonoInput.value.length !== 10) {
                errores.push('El teléfono debe tener exactamente 10 dígitos');
            }
            
            // Validar contraseñas
            if (!contrasenaInput || contrasenaInput.value.trim() === '') {
                errores.push('La contraseña es requerida');
            }
            
            if (!repiteContrasenaInput || repiteContrasenaInput.value.trim() === '') {
                errores.push('Debe repetir la contraseña');
            }
            
            // Validar que las contraseñas coincidan
            if (contrasenaInput && repiteContrasenaInput && 
                contrasenaInput.value.trim() !== '' && repiteContrasenaInput.value.trim() !== '') {
                
                if (contrasenaInput.value !== repiteContrasenaInput.value) {
                    errores.push('Las contraseñas no coinciden');
                    repiteContrasenaInput.style.borderColor = '#dc3545';
                    repiteContrasenaInput.style.boxShadow = '0 0 5px rgba(220, 53, 69, 0.3)';
                    console.log('Error: Las contraseñas no coinciden');
                    console.log('Contraseña 1:', contrasenaInput.value);
                    console.log('Contraseña 2:', repiteContrasenaInput.value);
                }
            }
            
            // Si hay errores, prevenir envío y mostrar mensajes
            if (errores.length > 0) {
                e.preventDefault();
                console.log('Errores encontrados:', errores);
                alert('Por favor corrige los siguientes errores:\n\n• ' + errores.join('\n• '));
                return false;
            }
            
            console.log('Formulario válido, enviando...');
        });
    } else {
        console.error('No se encontró el formulario de registro');
    }
});
