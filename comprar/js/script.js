// Función para mostrar panel de información de película
function mostrarPanelPelicula(peliculaData) {
    const panel = document.getElementById('panelPelicula');
    const overlay = document.getElementById('overlayPanelPelicula');
    
    if (!panel || !overlay) {
        console.error('ERROR: No se encontraron los elementos del panel');
        return;
    }
    
    // Obtener datos de la película
    const titulo = peliculaData.titulo;
    const poster = peliculaData.poster;
    const clasificacion = peliculaData.clasificacion;
    const duracion = peliculaData.duracion;
    const genero = peliculaData.genero;
    const descripcion = peliculaData.descripcion;
    const idioma = peliculaData.idioma;
    const valoracion = peliculaData.valoracion;
    const fechaEstreno = peliculaData.fecha_estreno;
    const actores = peliculaData.actores;
    const trailer = peliculaData.trailer;
    
    // Aplicar fuente Roboto a todos los elementos del panel
    document.querySelectorAll('#panelPelicula, #panelPelicula *').forEach(function(element) {
        element.style.fontFamily = '"Roboto", Arial, sans-serif';
    });
    
    // Llenar el panel con los datos
    document.getElementById('tituloPanel').textContent = titulo || 'Sin título';
    
    // Siempre mostrar la etiqueta "En Cartelera"
    const enCarteleraPanel = document.getElementById('enCarteleraPanel');
    enCarteleraPanel.style.display = 'inline-flex';
    
    // Aplicar color según la clasificación
    const clasificacionElement = document.getElementById('clasificacionPanel');
    clasificacionElement.textContent = clasificacion || '';
    
    // Remover clases de color anteriores
    clasificacionElement.classList.remove('clasificacion-verde', 'clasificacion-amarilla', 'clasificacion-roja');
    
    // Asignar clase de color según la clasificación
    if (clasificacion) {
        // Limpiar el contenedor primero para evitar anidamiento de elementos
        clasificacionElement.innerHTML = '';
        
        // Crear un span para la clasificación
        const span = document.createElement('span');
        span.textContent = clasificacion;
        
        if (clasificacion === 'AA' || clasificacion === 'A') {
            // Verde para clasificaciones para todos (AA, A)
            span.className = 'clasificacion-verde';
        } else if (clasificacion === 'B' || clasificacion === 'B15') {
            // Amarillo para clasificaciones intermedias (B, B15)
            span.className = 'clasificacion-amarilla';
        } else if (clasificacion === 'C' || clasificacion === 'D') {
            // Rojo para clasificaciones para adultos (C, D)
            span.className = 'clasificacion-roja';
        }
        
        // Añadir el span al elemento clasificación
        clasificacionElement.appendChild(span);
    } else {
        clasificacionElement.textContent = '';
    }
    
    // Formatear duración como en previa.php
    if (duracion) {
        const duracionNum = parseInt(duracion);
        const horas = Math.floor(duracionNum / 60);
        const minutos = duracionNum % 60;
        document.getElementById('duracionPanel').textContent = `${horas} h ${minutos} min`;
    } else {
        document.getElementById('duracionPanel').textContent = '';
    }
    
    document.getElementById('descripcionPanel').textContent = descripcion || '';
    // Aplicar fuente Roboto directamente
    document.getElementById('descripcionPanel').style.fontFamily = '"Roboto", Arial, sans-serif';
    document.getElementById('idiomaPanel').textContent = idioma || '';
    
    // Formatear valoración sin decimales (77.0 -> 77)
    if (valoracion) {
        const valoracionFormateada = parseInt(valoracion).toString();
        document.getElementById('valoracionPanel').textContent = valoracionFormateada;
    } else {
        document.getElementById('valoracionPanel').textContent = '';
    }
    
    // Formatear fecha de estreno al formato "DD de Mes del YYYY"
    if (fechaEstreno) {
        const fecha = new Date(fechaEstreno);
        const dia = fecha.getDate();
        const mes = fecha.getMonth();
        const anio = fecha.getFullYear();
        
        // Array con nombres de meses en español
        const nombresMeses = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];
        
        // Formatear la fecha como "DD de Mes del YYYY"
        const fechaFormateada = `${dia} de ${nombresMeses[mes]} del ${anio}`;
        document.getElementById('fechaEstrenoPanel').textContent = fechaFormateada;
    } else {
        document.getElementById('fechaEstrenoPanel').textContent = '';
    }
    
    document.getElementById('actoresPanel').textContent = actores || '';
    
    // Configurar géneros como en previa.php
    const generosPanel = document.getElementById('generosPanel');
    if (generosPanel && genero) {
        generosPanel.innerHTML = '';
        const generos = genero.split(',');
        generos.forEach(function(g) {
            const span = document.createElement('span');
            span.textContent = g.trim();
            generosPanel.appendChild(span);
        });
    }
    
    // Configurar trailer si existe
    const trailerPanel = document.getElementById('trailerPanel');
    const trailerIframe = document.getElementById('trailerIframe');
    
    if (trailerPanel && trailerIframe) {
        const videoId = obtenerYoutubeID(trailer);
        
        if (videoId) {
            // Construir la URL del video
            const videoUrl = `https://www.youtube.com/embed/${videoId}?enablejsapi=1`;
            trailerIframe.src = videoUrl;
            trailerPanel.style.display = 'block';
        } else {
            trailerPanel.style.display = 'none';
        }
    }
    
    // Mostrar el panel y el overlay
    panel.classList.add('abierto');
    overlay.classList.add('activo');
}

// Función para obtener ID de YouTube
function obtenerYoutubeID(url) {
    if (url && url.includes('youtube.com')) {
        const match = url.match(/(?:youtube\.com\/.*v=|youtu\.be\/)([^&]+)/);
        return match ? match[1] : null;
    } else if (url && url.includes('youtu.be')) {
        const match = url.match(/(?:youtu\.be\/)([^?&]+)/);
        return match ? match[1] : null;
    }
    return null;
}

// Función para cerrar el panel
function cerrarPanelPelicula() {
    const panel = document.getElementById('panelPelicula');
    const overlay = document.getElementById('overlayPanelPelicula');
    const trailerIframe = document.getElementById('trailerIframe');
    
    // Si hay un iframe de trailer, guardar su src para detener la reproducción
    if (trailerIframe) {
        // Guardar la URL actual antes de limpiarla
        trailerIframe.setAttribute('data-src', trailerIframe.src);
        // Limpiar el src para detener el video
        trailerIframe.src = '';
    }
    
    // Cerrar el panel
    panel.classList.remove('abierto');
    overlay.classList.remove('activo');
}

// Función para comprar entradas
function comprarEntradas() {
    window.location.href = 'comprar_entradas.php';
}

// Agregar listeners para los enlaces "Ver detalle"
document.addEventListener('DOMContentLoaded', function() {
    const verDetalleLinks = document.querySelectorAll('.ver-detalle');
    verDetalleLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const peliculaData = JSON.parse(this.getAttribute('data-pelicula'));
            mostrarPanelPelicula(peliculaData);
        });
    });
    
    // También permitir cerrar el panel al hacer clic en el overlay
    const overlay = document.getElementById('overlayPanelPelicula');
    if (overlay) {
        overlay.addEventListener('click', cerrarPanelPelicula);
    }
});