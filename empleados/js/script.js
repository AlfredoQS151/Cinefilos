document.addEventListener('DOMContentLoaded', () => {
    const btnAgregarUsuario = document.getElementById('btnAgregarUsuario');
    const formContainer = document.getElementById('formUsuarioContainer');
    const formUsuario = document.getElementById('formUsuario');
    const btnCancelar = document.getElementById('btnCancelar');
    const btnCerrar = document.getElementById('btnCerrar');

    const inputId = document.getElementById('inputId');
    const inputNombre = document.getElementById('inputNombre');
    const inputApellido = document.getElementById('inputApellido');
    const inputFechaNacimiento = document.getElementById('inputFechaNacimiento');
    const inputCorreo = document.getElementById('inputCorreo');
    const inputPassword = document.getElementById('inputPassword');

    // Funciones de validación
    function validarNombreApellido(texto) {
        const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
        return regex.test(texto);
    }

    function validarEdad(fechaNacimiento) {
        const hoy = new Date();
        const fechaNac = new Date(fechaNacimiento);
        const edad = hoy.getFullYear() - fechaNac.getFullYear();
        const mesActual = hoy.getMonth();
        const diaActual = hoy.getDate();
        const mesNacimiento = fechaNac.getMonth();
        const diaNacimiento = fechaNac.getDate();

        // Ajustar si no ha llegado el cumpleaños este año
        if (mesActual < mesNacimiento || (mesActual === mesNacimiento && diaActual < diaNacimiento)) {
            return edad - 1 >= 18;
        }
        return edad >= 18;
    }

    function validarPassword(password) {
        return password.length >= 8;
    }

    function mostrarError(input, mensaje) {
        // Remover errores anteriores
        const errorAnterior = input.parentNode.querySelector('.error-message');
        if (errorAnterior) {
            errorAnterior.remove();
        }

        // Agregar nuevo error
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.style.color = '#dc3545';
        errorDiv.style.fontSize = '0.875rem';
        errorDiv.style.marginTop = '0.25rem';
        errorDiv.textContent = mensaje;
        input.parentNode.appendChild(errorDiv);
        input.style.borderColor = '#dc3545';
    }

    function limpiarError(input) {
        const errorAnterior = input.parentNode.querySelector('.error-message');
        if (errorAnterior) {
            errorAnterior.remove();
        }
        input.style.borderColor = '#555';
    }

    function validarFormulario() {
        let esValido = true;

        // Limpiar errores anteriores
        limpiarError(inputNombre);
        limpiarError(inputApellido);
        limpiarError(inputFechaNacimiento);
        limpiarError(inputPassword);

        // Validar nombre
        if (!validarNombreApellido(inputNombre.value.trim())) {
            mostrarError(inputNombre, 'El nombre no puede contener números ni caracteres especiales');
            esValido = false;
        }

        // Validar apellido
        if (!validarNombreApellido(inputApellido.value.trim())) {
            mostrarError(inputApellido, 'El apellido no puede contener números ni caracteres especiales');
            esValido = false;
        }

        // Validar edad
        if (!validarEdad(inputFechaNacimiento.value)) {
            mostrarError(inputFechaNacimiento, 'El empleado debe ser mayor de 18 años');
            esValido = false;
        }

        // Validar contraseña solo si es requerida (agregar) o si se escribió algo (editar)
        if (inputPassword.required || inputPassword.value.trim() !== '') {
            if (!validarPassword(inputPassword.value)) {
                mostrarError(inputPassword, 'La contraseña debe tener al menos 8 caracteres');
                esValido = false;
            }
        }

        return esValido;
    }

    btnAgregarUsuario.addEventListener('click', () => {
        formUsuario.reset();
        inputId.value = '';
        inputPassword.required = true;
        inputPassword.placeholder = '';
        formUsuario.action = '../conexion/empleados/insertar.php';
        formContainer.style.display = 'block';
        window.scrollTo({top: 0, behavior: 'smooth'});
    });

    btnCancelar.addEventListener('click', () => {
        formContainer.style.display = 'none';
        formUsuario.reset();
        // Limpiar errores
        limpiarError(inputNombre);
        limpiarError(inputApellido);
        limpiarError(inputFechaNacimiento);
        limpiarError(inputPassword);
    });

    btnCerrar.addEventListener('click', () => {
        formContainer.style.display = 'none';
        formUsuario.reset();
        // Limpiar errores
        limpiarError(inputNombre);
        limpiarError(inputApellido);
        limpiarError(inputFechaNacimiento);
        limpiarError(inputPassword);
    });

    // Validación en tiempo real
    inputNombre.addEventListener('input', () => {
        if (inputNombre.value.trim() !== '') {
            if (!validarNombreApellido(inputNombre.value.trim())) {
                mostrarError(inputNombre, 'El nombre no puede contener números ni caracteres especiales');
            } else {
                limpiarError(inputNombre);
            }
        }
    });

    inputApellido.addEventListener('input', () => {
        if (inputApellido.value.trim() !== '') {
            if (!validarNombreApellido(inputApellido.value.trim())) {
                mostrarError(inputApellido, 'El apellido no puede contener números ni caracteres especiales');
            } else {
                limpiarError(inputApellido);
            }
        }
    });

    inputFechaNacimiento.addEventListener('change', () => {
        if (inputFechaNacimiento.value !== '') {
            if (!validarEdad(inputFechaNacimiento.value)) {
                mostrarError(inputFechaNacimiento, 'El empleado debe ser mayor de 18 años');
            } else {
                limpiarError(inputFechaNacimiento);
            }
        }
    });

    inputPassword.addEventListener('input', () => {
        if (inputPassword.value.trim() !== '') {
            if (!validarPassword(inputPassword.value)) {
                mostrarError(inputPassword, 'La contraseña debe tener al menos 8 caracteres');
            } else {
                limpiarError(inputPassword);
            }
        }
    });

    // Validación al enviar el formulario
    formUsuario.addEventListener('submit', (e) => {
        e.preventDefault();
        if (validarFormulario()) {
            formUsuario.submit();
        }
    });

    document.querySelectorAll('.btnEditar').forEach(btn => {
        btn.addEventListener('click', e => {
            const tr = e.target.closest('tr');
            inputId.value = tr.dataset.id;
            inputNombre.value = tr.dataset.nombre;
            inputApellido.value = tr.dataset.apellido;
            inputFechaNacimiento.value = tr.dataset.fecha_nacimiento;
            inputCorreo.value = tr.dataset.correo;
            inputPassword.required = false;
            inputPassword.placeholder = 'Dejar vacío para no cambiar contraseña';
            inputPassword.value = '';
            formUsuario.action = '../conexion/empleados/editar.php';
            formContainer.style.display = 'block';
            window.scrollTo({top: 0, behavior: 'smooth'});
        });
    });

    const modalEliminar = new bootstrap.Modal(document.getElementById('modalEliminar'));
    const formEliminar = document.getElementById('formEliminar');
    const inputEliminarId = document.getElementById('inputEliminarId');

    document.querySelectorAll('.btnEliminar').forEach(btn => {
        btn.addEventListener('click', e => {
            const id = e.target.dataset.id;
            inputEliminarId.value = id;
            formEliminar.action = '../conexion/empleados/eliminar.php';
            modalEliminar.show();
        });
    });

    document.getElementById('modalEliminar').addEventListener('hidden.bs.modal', () => {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(bd => bd.remove());
    });
});