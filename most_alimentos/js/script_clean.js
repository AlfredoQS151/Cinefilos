// JavaScript para la página de mostrar alimentos
let carrito = [];
let totalCarrito = 0;

// Función para inicializar el carrito y la UI
function inicializarCarrito() {
    carrito = [];
    totalCarrito = 0;
    actualizarCarrito();
    actualizarPuntos();
}

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar el carrito y la UI
    inicializarCarrito();
    
    // Manejar botones de agregar al carrito
    const botonesAgregar = document.querySelectorAll('.btn-agregar-carrito');
    
    botonesAgregar.forEach(boton => {
        boton.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nombre = this.getAttribute('data-nombre');
            const precio = parseFloat(this.getAttribute('data-precio'));
            
            agregarAlCarrito(id, nombre, precio);
        });
    });
    
    // Manejar carga de imágenes
    const imagenes = document.querySelectorAll('.alimento-imagen img');
    imagenes.forEach(img => {
        img.addEventListener('load', function() {
            this.style.opacity = '1';
        });
        
        img.addEventListener('error', function() {
            this.style.display = 'none';
            // Verificar si ya hay un placeholder
            if (!this.parentElement.querySelector('.no-image-placeholder')) {
                const placeholder = document.createElement('div');
                placeholder.className = 'no-image-placeholder';
                placeholder.innerHTML = '🍽️';
                this.parentElement.appendChild(placeholder);
            }
        });
        
        // Si la imagen no tiene src o está vacía, mostrar placeholder inmediatamente
        if (!img.src || img.src === '' || img.src === window.location.href) {
            img.style.display = 'none';
            if (!img.parentElement.querySelector('.no-image-placeholder')) {
                const placeholder = document.createElement('div');
                placeholder.className = 'no-image-placeholder';
                placeholder.innerHTML = '🍽️';
                img.parentElement.appendChild(placeholder);
            }
        }
    });
});

function agregarAlCarrito(id, nombre, precio) {
    // Verificar si el item ya existe en el carrito
    const itemExistente = carrito.find(item => item.id === id);
    
    if (itemExistente) {
        itemExistente.cantidad += 1;
    } else {
        carrito.push({
            id: id,
            nombre: nombre,
            precio: precio,
            cantidad: 1
        });
    }
    
    actualizarCarrito();
    mostrarNotificacion(`${nombre} agregado al carrito`);
}

function actualizarCarrito() {
    const contenidoCarrito = document.getElementById('carritoContenido');
    const totalElement = document.getElementById('carritoTotal');
    const contadorElement = document.getElementById('carritoContador');
    const btnCarrito = document.getElementById('btnCarrito');
    
    // Limpiar contenido
    contenidoCarrito.innerHTML = '';
    
    if (carrito.length === 0) {
        contenidoCarrito.innerHTML = '<p style="text-align: center; color: #ccc; padding: 20px;">El carrito está vacío</p>';
        if (btnCarrito) btnCarrito.style.display = 'none';
        actualizarPuntos();
        return;
    }
    
    // Mostrar items del carrito
    let total = 0;
    let cantidadTotal = 0;
    
    carrito.forEach(item => {
        const itemElement = document.createElement('div');
        itemElement.className = 'carrito-item';
        itemElement.innerHTML = `
            <div class="item-info">
                <div class="item-nombre">${item.nombre}</div>
                <div class="item-precio">$${item.precio.toFixed(2)} c/u</div>
            </div>
            <div class="item-cantidad">
                <button class="btn-cantidad" onclick="event.stopPropagation(); cambiarCantidad('${item.id}', -1)">-</button>
                <span style="color: #eaf822; font-weight: 600; min-width: 30px; text-align: center;">${item.cantidad}</span>
                <button class="btn-cantidad" onclick="event.stopPropagation(); cambiarCantidad('${item.id}', 1)">+</button>
                <button class="btn-cantidad" onclick="event.stopPropagation(); eliminarDelCarrito('${item.id}')" style="margin-left: 10px; background-color: #dc3545;">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        contenidoCarrito.appendChild(itemElement);
        
        total += item.precio * item.cantidad;
        cantidadTotal += item.cantidad;
    });
    
    // Actualizar total
    if (totalElement) {
        totalElement.textContent = total.toFixed(2);
    }
    
    // Actualizar puntos
    actualizarPuntos();
    
    // Actualizar contador
    if (contadorElement) {
        contadorElement.textContent = cantidadTotal;
        if (cantidadTotal > 0) {
            contadorElement.classList.add('activo');
        } else {
            contadorElement.classList.remove('activo');
        }
    }
    
    // Mostrar/ocultar botón del carrito
    if (btnCarrito) {
        if (cantidadTotal > 0) {
            btnCarrito.style.display = 'flex';
        } else {
            btnCarrito.style.display = 'none';
        }
    }
    
    totalCarrito = total;
}

function actualizarPuntos() {
    // Calcular puntos (5 puntos por producto único, sin importar la cantidad)
    const productosUnicos = carrito.length;
    const puntosGanados = productosUnicos * 5;
    
    const puntosElement = document.getElementById('puntosGanados');
    if (puntosElement) {
        puntosElement.textContent = puntosGanados;
    }
}

function cambiarCantidad(id, cambio) {
    const item = carrito.find(item => item.id === id);
    if (item) {
        item.cantidad += cambio;
        if (item.cantidad <= 0) {
            eliminarDelCarrito(id);
        } else {
            actualizarCarrito();
        }
    }
}

function eliminarDelCarrito(id) {
    carrito = carrito.filter(item => item.id !== id);
    actualizarCarrito();
}

function mostrarCarrito() {
    const carritoFlotante = document.getElementById('carritoFlotante');
    if (carritoFlotante) {
        carritoFlotante.style.display = 'block';
        
        // Animación de entrada
        carritoFlotante.style.transform = 'translateY(-50%) translateX(100%)';
        setTimeout(() => {
            carritoFlotante.style.transition = 'transform 0.3s ease';
            carritoFlotante.style.transform = 'translateY(-50%) translateX(0)';
        }, 10);
    }
}

function cerrarCarrito() {
    const carritoFlotante = document.getElementById('carritoFlotante');
    if (carritoFlotante) {
        carritoFlotante.style.transform = 'translateY(-50%) translateX(100%)';
        setTimeout(() => {
            carritoFlotante.style.display = 'none';
            carritoFlotante.style.transition = 'none';
        }, 300);
    }
}

function procederCompra() {
    if (carrito.length === 0) {
        mostrarNotificacion('El carrito está vacío', 'warning');
        return;
    }
    
    // Verificar si el usuario está autenticado
    fetch('../conexion/verificar_sesion.php')
        .then(response => response.json())
        .then(data => {
            if (!data.loggedIn || data.rol !== 'normal') {
                // Mostrar modal de login
                const modal = new bootstrap.Modal(document.getElementById('loginModal'));
                modal.show();
                return;
            }
            
            // Si está autenticado, proceder con la compra
            procederConCompra();
        })
        .catch(error => {
            console.error('Error al verificar sesión:', error);
            // En caso de error, mostrar modal de login por seguridad
            const modal = new bootstrap.Modal(document.getElementById('loginModal'));
            modal.show();
        });
}

function procederConCompra() {
    // Crear formulario para enviar a pagos.php
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '../pagos/pagos.php';
    
    // Agregar campo para indicar que es compra de alimentos
    const tipoCompraInput = document.createElement('input');
    tipoCompraInput.type = 'hidden';
    tipoCompraInput.name = 'tipo_compra';
    tipoCompraInput.value = 'alimentos';
    form.appendChild(tipoCompraInput);
    
    // Agregar datos del carrito
    const carritoInput = document.createElement('input');
    carritoInput.type = 'hidden';
    carritoInput.name = 'carrito_alimentos';
    carritoInput.value = JSON.stringify(carrito);
    form.appendChild(carritoInput);
    
    // Agregar total
    const totalInput = document.createElement('input');
    totalInput.type = 'hidden';
    totalInput.name = 'total_alimentos';
    totalInput.value = totalCarrito.toFixed(2);
    form.appendChild(totalInput);
    
    // Agregar formulario al DOM y enviarlo
    document.body.appendChild(form);
    form.submit();
}

function mostrarNotificacion(mensaje, tipo = 'success') {
    // Crear notificación
    const notificacion = document.createElement('div');
    
    let colorFondo = '';
    let icono = '';
    
    switch(tipo) {
        case 'success':
            colorFondo = 'linear-gradient(45deg, #4cd137, #44bd32)';
            icono = 'fas fa-check-circle';
            break;
        case 'warning':
            colorFondo = 'linear-gradient(45deg, #f39c12, #e67e22)';
            icono = 'fas fa-exclamation-triangle';
            break;
        case 'error':
            colorFondo = 'linear-gradient(45deg, #e74c3c, #c0392b)';
            icono = 'fas fa-times-circle';
            break;
        default:
            colorFondo = 'linear-gradient(45deg, #4cd137, #44bd32)';
            icono = 'fas fa-check-circle';
    }
    
    notificacion.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${colorFondo};
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        z-index: 9999;
        font-weight: 600;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        max-width: 300px;
    `;
    notificacion.innerHTML = `
        <i class="${icono}"></i> ${mensaje}
    `;
    
    document.body.appendChild(notificacion);
    
    // Mostrar notificación
    setTimeout(() => {
        notificacion.style.transform = 'translateX(0)';
    }, 10);
    
    // Ocultar notificación después de 3 segundos
    setTimeout(() => {
        notificacion.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (document.body.contains(notificacion)) {
                document.body.removeChild(notificacion);
            }
        }, 300);
    }, 3000);
}

// Cerrar carrito al hacer clic fuera de él
document.addEventListener('click', function(event) {
    const carritoFlotante = document.getElementById('carritoFlotante');
    const btnCarrito = document.getElementById('btnCarrito');
    
    if (carritoFlotante && btnCarrito && carritoFlotante.style.display === 'block' && 
        !carritoFlotante.contains(event.target) && 
        !btnCarrito.contains(event.target)) {
        cerrarCarrito();
    }
});

// Cerrar carrito con tecla Escape
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const carritoFlotante = document.getElementById('carritoFlotante');
        if (carritoFlotante && carritoFlotante.style.display === 'block') {
            cerrarCarrito();
        }
    }
});
