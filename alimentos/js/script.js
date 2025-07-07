// JavaScript para la gestión de alimentos
document.addEventListener('DOMContentLoaded', function() {
    // Manejar el modal de editar alimento
    const modalEditarAlimento = document.getElementById('modalEditarAlimento');
    modalEditarAlimento.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        
        // Obtener los datos del botón
        const id = button.getAttribute('data-id');
        const nombre = button.getAttribute('data-nombre');
        const foto = button.getAttribute('data-foto');
        const descripcion = button.getAttribute('data-descripcion');
        const precio = button.getAttribute('data-precio');
        const categoria = button.getAttribute('data-categoria');
        
        // Llenar los campos del modal
        document.getElementById('editar_id').value = id;
        document.getElementById('editar_nombre').value = nombre;
        document.getElementById('editar_foto').value = foto;
        document.getElementById('editar_descripcion').value = descripcion;
        document.getElementById('editar_precio').value = precio;
        document.getElementById('editar_categoria').value = categoria;
    });

    // Manejar el modal de eliminar alimento
    const modalEliminarAlimento = document.getElementById('modalEliminarAlimento');
    modalEliminarAlimento.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        
        // Obtener los datos del botón
        const id = button.getAttribute('data-id');
        const nombre = button.getAttribute('data-nombre');
        
        // Llenar los campos del modal
        document.getElementById('eliminar_id').value = id;
        document.getElementById('eliminar_nombre_mostrar').textContent = nombre;
    });

    // Validación de formularios
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            const precio = form.querySelector('input[name="precio"]');
            if (precio && parseFloat(precio.value) < 0) {
                event.preventDefault();
                alert('El precio no puede ser negativo');
                return false;
            }
        });
    });

    // Previsualización de imágenes
    const inputs = document.querySelectorAll('input[type="url"]');
    inputs.forEach(input => {
        if (input.name === 'foto') {
            input.addEventListener('blur', function() {
                previewImage(this);
            });
        }
    });
});

function previewImage(input) {
    const url = input.value;
    if (url && isValidUrl(url)) {
        // Crear elemento de previsualización si no existe
        let preview = input.parentNode.querySelector('.image-preview');
        if (!preview) {
            preview = document.createElement('div');
            preview.className = 'image-preview mt-2';
            input.parentNode.appendChild(preview);
        }
        
        preview.innerHTML = `
            <img src="${url}" alt="Preview" style="max-width: 200px; max-height: 150px; border-radius: 8px; border: 1px solid #333;">
        `;
        
        // Manejar error de carga
        const img = preview.querySelector('img');
        img.onerror = function() {
            preview.innerHTML = '<p class="text-danger">Error al cargar la imagen</p>';
        };
    }
}

function isValidUrl(string) {
    try {
        new URL(string);
        return true;
    } catch (_) {
        return false;
    }
}
