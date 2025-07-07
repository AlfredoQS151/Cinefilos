console.log('Script.js cargado correctamente');

// Géneros disponibles para películas
const generosDisponibles = {
    "Selecione un Género": "Selecione un Género",
    "Acción": "Acción",
    "Aventura": "Aventura",
    "Animación": "Animación",
    "Comedia": "Comedia",
    "Crimen": "Crimen",
    "Documental": "Documental",
    "Drama": "Drama",
    "Familia": "Familia",
    "Fantasía": "Fantasía",
    "Historia": "Historia",
    "Terror": "Terror",
    "Música": "Música",
    "Misterio": "Misterio",
    "Romance": "Romance",
    "Ciencia ficción": "Ciencia ficción",
    "Película de TV": "Película de TV",
    "Suspenso": "Suspenso",
    "Bélica": "Bélica",
    "Western": "Western"
};

// Función para mostrar panel de información de película
function mostrarPanelPelicula(peliculaElement) {
    console.log('=== INICIANDO MOSTRAR PANEL ===');
    console.log('Elemento recibido:', peliculaElement);
    console.log('Título de película:', peliculaElement.getAttribute('data-titulo'));
    
    const panel = document.getElementById('panelPelicula');
    const overlay = document.getElementById('overlayPanelPelicula');
    
    console.log('Panel encontrado:', !!panel);
    console.log('Overlay encontrado:', !!overlay);
    
    if (!panel || !overlay) {
        console.error('ERROR: No se encontraron los elementos del panel');
        alert('Error: Panel no encontrado. Verifica la consola.');
        return;
    }
    
    // Obtener datos de la película
    const titulo = peliculaElement.getAttribute('data-titulo');
    const poster = peliculaElement.getAttribute('data-poster');
    const clasificacion = peliculaElement.getAttribute('data-clasificacion');
    const duracion = peliculaElement.getAttribute('data-duracion');
    const genero = peliculaElement.getAttribute('data-genero');
    const descripcion = peliculaElement.getAttribute('data-descripcion');
    const idioma = peliculaElement.getAttribute('data-idioma');
    const valoracion = peliculaElement.getAttribute('data-valoracion');
    const fechaEstreno = peliculaElement.getAttribute('data-fecha_estreno');
    const actores = peliculaElement.getAttribute('data-actores');
    const trailer = peliculaElement.getAttribute('data-trailer');
    
    console.log('Datos de película:', { titulo, poster, clasificacion, duracion, genero });
    
    // Llenar el panel con los datos
    document.getElementById('tituloPanel').textContent = titulo || 'Sin título';
    
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
    
    // Configurar trailer (función para obtener ID de YouTube)
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
    
    // Configurar trailer si existe
    const trailerPanel = document.getElementById('trailerPanel');
    const trailerIframe = document.getElementById('trailerIframe');
    
    if (trailerPanel && trailerIframe) {
        console.log('Trailer URL:', trailer);
        const videoId = obtenerYoutubeID(trailer);
        console.log('Video ID detectado:', videoId);
        
        if (videoId) {
            // Construir la URL del video
            const videoUrl = `https://www.youtube.com/embed/${videoId}?enablejsapi=1`;
            
            // Verificar si hay un src guardado en data-src
            const savedSrc = trailerIframe.getAttribute('data-src');
            
            // Si el trailer URL coincide con la última película vista, usar el src guardado
            // De lo contrario, usar la nueva URL basada en los datos de la película actual
            if (savedSrc && savedSrc.includes(videoId)) {
                trailerIframe.src = savedSrc;
                console.log('Restaurando URL guardada del trailer:', savedSrc);
            } else {
                trailerIframe.src = videoUrl;
                console.log('Configurando nueva URL del trailer:', videoUrl);
            }
            
            // Limpiar el data-src para evitar confusiones en futuras aperturas
            trailerIframe.removeAttribute('data-src');
            
            trailerPanel.style.display = 'block';
            console.log('Trailer configurado con URL:', trailerIframe.src);
        } else {
            trailerPanel.style.display = 'none';
            console.log('No se pudo detectar ID de YouTube');
        }
    } else {
        console.error('No se encontraron elementos del trailer');
    }
    
    console.log('Datos llenados, mostrando panel...');
    
    // Mostrar panel
    overlay.classList.add('activo');
    panel.classList.add('activo');
    
    console.log('Panel classes después de agregar activo:', panel.className);
    console.log('Overlay classes después de agregar activo:', overlay.className);
    console.log('=== PANEL MOSTRADO ===');
}

// Función para cerrar el panel
function cerrarPanelPelicula() {
    console.log('Cerrando panel...');
    const panel = document.getElementById('panelPelicula');
    const overlay = document.getElementById('overlayPanelPelicula');
    const trailerIframe = document.getElementById('trailerIframe');
    
    // Detener la reproducción del video estableciendo el src a vacío
    if (trailerIframe && trailerIframe.src) {
        // Solo guardamos el src si no está vacío
        if (trailerIframe.src.trim() !== '') {
            trailerIframe.setAttribute('data-src', trailerIframe.src); // Guardar el src actual
            console.log('Trailer detenido, URL guardada:', trailerIframe.src);
        }
        trailerIframe.src = ''; // Limpiar el src para detener el video
    }
    
    if (panel) panel.classList.remove('activo');
    if (overlay) overlay.classList.remove('activo');
    
    console.log('Panel cerrado');
}

// Función para comprar entradas
function comprarEntradas() {
    window.location.href = 'comprar/comprar_entradas.php';
}

// Configuración principal cuando el DOM está cargado
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM cargado, configurando eventos de películas...');
    
    // Verificar que los elementos del panel existan
    const panel = document.getElementById('panelPelicula');
    const overlay = document.getElementById('overlayPanelPelicula');
    
    console.log('Panel encontrado en DOMContentLoaded:', !!panel);
    console.log('Overlay encontrado en DOMContentLoaded:', !!overlay);
    
    if (!panel || !overlay) {
        console.error('ERROR: Panel o overlay no encontrados en el DOM al cargar');
        return;
    }
    
    // Configurar eventos para películas después de un pequeño delay
    setTimeout(function() {
        configurarEventosPeliculas();
    }, 100);
    
    // Configurar géneros
    setupGenerosPeliculas();
});

// Función para configurar eventos de películas
function configurarEventosPeliculas() {
    const peliculas = document.querySelectorAll('.pelicula');
    console.log('=== CONFIGURANDO EVENTOS ===');
    console.log('Total películas encontradas:', peliculas.length);
    
    peliculas.forEach(function(pelicula, index) {
        const tieneOverlay = pelicula.querySelector('.pelicula-overlay');
        const titulo = pelicula.getAttribute('data-titulo');
        const peliculaId = pelicula.getAttribute('data-id');
        const esTipoProximo = pelicula.getAttribute('data-tipo') === 'proximo';
        
        console.log(`Película ${index + 1}: "${titulo}" - Admin: ${!!tieneOverlay} - ID: ${peliculaId} - Próximo: ${esTipoProximo}`);
        
        if (tieneOverlay) {
            // Configurar botón editar para administradores
            const btnEditar = pelicula.querySelector('.editar-btn');
            if (btnEditar) {
                btnEditar.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    console.log('Botón Editar clickeado para:', titulo, 'ID:', peliculaId);
                    
                    // Llenar formulario según si es película normal o próximo estreno
                    const esProximo = pelicula.getAttribute('data-tipo') === 'proximo';
                    const modalId = esProximo ? 'modalFormAgregarProximo' : 'modalFormAgregar';
                    const modal = document.getElementById(modalId);
                    
                    if (!modal) {
                        console.error('Modal no encontrado:', modalId);
                        return;
                    }
                    
                    // Cambiar título del modal
                    const modalTitleElement = modal.querySelector('.modal-title');
                    if (modalTitleElement) {
                        modalTitleElement.textContent = esProximo ? 'Editar Próximo Estreno' : 'Editar Película';
                    }
                    
                    // Llenar campos del formulario
                    fillFormWithMovieData(pelicula, esProximo);
                    
                    // Mostrar modal usando Bootstrap
                    const bsModal = new bootstrap.Modal(modal);
                    bsModal.show();
                });
            }
            
            // Configurar botón eliminar para modal
            const btnEliminar = pelicula.querySelector('.eliminar-btn');
            if (btnEliminar) {
                btnEliminar.addEventListener('click', function(e) {
                    e.stopPropagation(); // Evitar que el click se propague
                    const peliculaId = this.getAttribute('data-id');
                    const esTipoProximo = pelicula.getAttribute('data-tipo') === 'proximo';
                    
                    console.log('Configurando eliminación para ID:', peliculaId, 'Tipo:', esTipoProximo ? 'proximo' : 'pelicula');
                    
                    // Configurar el modal de eliminación
                    document.getElementById('inputEliminarPeliculaId').value = peliculaId;
                    document.getElementById('inputEliminarTipo').value = esTipoProximo ? 'proximo' : 'pelicula';
                });
            }
        } else {
            // Es una película clickeable para usuarios normales
            pelicula.style.cursor = 'pointer';
            
            // Obtener la imagen del poster dentro de la película
            const posterImg = pelicula.querySelector('img');
            
            pelicula.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('=== CLICK DETECTADO ===');
                console.log('Película clickeada:', titulo);
                
                // Aplicar animación al poster si existe
                if (posterImg) {
                    // Remover clase si ya está aplicada para poder reaplicarla
                    posterImg.classList.remove('rotate-scale-up-diagonal-left');
                    
                    // Agregar animación después de un pequeño delay
                    setTimeout(() => {
                        posterImg.classList.add('rotate-scale-up-diagonal-left');
                        console.log('✓ Animación aplicada al poster');
                    }, 10);
                    
                    // Remover la clase después de que termine la animación
                    setTimeout(() => {
                        posterImg.classList.remove('rotate-scale-up-diagonal-left');
                    }, 400);
                }
                
                // Mantener funcionalidad original de abrir panel
                mostrarPanelPelicula(this);
            });
            
            // Agregar efecto visual
            pelicula.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.05)';
                this.style.transition = 'transform 0.2s ease';
            });
            
            pelicula.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
            
            console.log(`✓ Configurado click para: "${titulo}"`);
        }
    });
    
    // Configurar cierre del panel al hacer click en overlay
    const overlayElement = document.getElementById('overlayPanelPelicula');
    if (overlayElement) {
        overlayElement.addEventListener('click', function(e) {
            e.preventDefault();
            cerrarPanelPelicula();
        });
        console.log('✓ Configurado click en overlay para cerrar');
    }
    
    console.log('=== CONFIGURACIÓN COMPLETA ===');
}

// Función para configurar géneros de películas normales
function setupGenerosPeliculas() {
    const generosSeleccionadosDiv = document.getElementById('generosSeleccionados');
    const btnAgregarGenero = document.getElementById('btnAgregarGenero');
    const selectGeneros = document.getElementById('selectGeneros');
    
    if (!generosSeleccionadosDiv || !btnAgregarGenero || !selectGeneros) {
        console.log('Elementos de géneros no encontrados, saltando configuración');
        return;
    }
    
    function actualizarSelect() {
        selectGeneros.innerHTML = '';
        const seleccionados = Array.from(generosSeleccionadosDiv.querySelectorAll('input[name="genero[]"]')).map(i => i.value);
        
        for (const nombre of Object.values(generosDisponibles)) {
            if (!seleccionados.includes(nombre)) {
                const option = document.createElement('option');
                option.value = nombre;
                option.textContent = nombre;
                selectGeneros.appendChild(option);
            }
        }
        
        selectGeneros.style.display = selectGeneros.options.length > 0 ? 'block' : 'none';
    }
    
    btnAgregarGenero.addEventListener('click', () => {
        actualizarSelect();
        selectGeneros.style.display = 'block';
        selectGeneros.focus();
    });
    
    selectGeneros.addEventListener('change', () => {
        const value = selectGeneros.value;
        if (value && value !== 'Selecione un Género') {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'genero[]';
            input.value = value;
            generosSeleccionadosDiv.appendChild(input);
            
            const badge = document.createElement('span');
            badge.className = 'badge bg-primary me-1 mb-1';
            badge.textContent = value;
            badge.style.cursor = 'pointer';
            badge.onclick = function() {
                generosSeleccionadosDiv.removeChild(input);
                generosSeleccionadosDiv.removeChild(badge);
                actualizarSelect();
            };
            generosSeleccionadosDiv.appendChild(badge);
            actualizarSelect();
        }
    });
    
    actualizarSelect();
}

// Función de test simple para verificar que el panel funciona
function testPanelSimple() {
    console.log('=== EJECUTANDO TEST ===');
    const panel = document.getElementById('panelPelicula');
    const overlay = document.getElementById('overlayPanelPelicula');
    
    if (panel && overlay) {
        console.log('Panel y overlay encontrados para test');
        overlay.classList.add('activo');
        panel.classList.add('activo');
        
        // Datos de prueba
        document.getElementById('tituloPanel').textContent = 'Película de Prueba';
        document.getElementById('clasificacionPanel').textContent = 'B';
        document.getElementById('duracionPanel').textContent = '120 min';
        document.getElementById('generoPanel').textContent = 'Acción';
        document.getElementById('descripcionPanel').textContent = 'Esta es una película de prueba para verificar el panel lateral.';
        // Aplicar fuente Roboto directamente
        document.getElementById('descripcionPanel').style.fontFamily = '"Roboto", Arial, sans-serif';
        document.getElementById('idiomaPanel').textContent = 'Español';
        document.getElementById('valoracionPanel').textContent = '85';
        document.getElementById('fechaEstrenoPanel').textContent = '2024-01-15';
        document.getElementById('actoresPanel').textContent = 'Actor Principal, Actor Secundario';
        
        console.log('Panel de test abierto');
    } else {
        console.error('Panel o overlay no encontrados para test');
        alert('Error en test: Panel no encontrado');
    }
}

// Función para llenar el formulario con los datos de la película
function fillFormWithMovieData(peliculaElement, esProximo) {
    console.log('Llenando formulario para:', esProximo ? 'próximo estreno' : 'película');
    
    // Obtener los datos de los atributos data
    const id = peliculaElement.getAttribute('data-id');
    const titulo = peliculaElement.getAttribute('data-titulo');
    const poster = peliculaElement.getAttribute('data-poster');
    const idioma = peliculaElement.getAttribute('data-idioma');
    const clasificacion = peliculaElement.getAttribute('data-clasificacion');
    const genero = peliculaElement.getAttribute('data-genero');
    const valoracion = peliculaElement.getAttribute('data-valoracion');
    const duracion = peliculaElement.getAttribute('data-duracion');
    const descripcion = peliculaElement.getAttribute('data-descripcion');
    const fechaEstreno = peliculaElement.getAttribute('data-fecha_estreno');
    const trailer = peliculaElement.getAttribute('data-trailer');
    const actores = peliculaElement.getAttribute('data-actores');
    
    console.log('Datos a cargar:', { id, titulo, poster, idioma, clasificacion, genero, valoracion });
    
    // Determinar el prefijo del ID del formulario según el tipo
    const formPrefix = esProximo ? 'Proximo' : '';
    const suffix = esProximo ? '_proximo' : '';
    
    // Llenar los campos del formulario
    const form = esProximo ? 
        document.querySelector('form[action="conexion/insertar.php"][id="formAgregarProximo"]') : 
        document.querySelector('form[action="conexion/insertar.php"][id="formAgregarPelicula"]');
        
    if (!form) {
        console.error('No se encontró el formulario');
        return;
    }
    
    // Cambiar acción del formulario para editar en lugar de insertar
    form.action = `conexion/editar.php`;
    
    // Agregar campo oculto con ID para la edición
    let inputId = form.querySelector('input[name="id_editar"]');
    if (!inputId) {
        inputId = document.createElement('input');
        inputId.type = 'hidden';
        inputId.name = 'id_editar';
        form.appendChild(inputId);
    }
    inputId.value = id;
    
    // Agregar campo oculto para tipo si es próximo estreno
    if (esProximo) {
        let inputTipo = form.querySelector('input[name="tipo_editar"]');
        if (!inputTipo) {
            inputTipo = document.createElement('input');
            inputTipo.type = 'hidden';
            inputTipo.name = 'tipo_editar';
            form.appendChild(inputTipo);
        }
        inputTipo.value = 'proximo';
    }
    
    // Campos comunes
    const inputTitulo = form.querySelector('input[name="titulo"]');
    if (inputTitulo) inputTitulo.value = titulo || '';
    
    const inputIdioma = form.querySelector('input[name="idioma"]');
    if (inputIdioma) inputIdioma.value = idioma || '';
    
    const selectClasificacion = form.querySelector('select[name="clasificacion"]');
    if (selectClasificacion) selectClasificacion.value = clasificacion || '';
    
    const inputValoracion = form.querySelector('input[name="valoracion"]');
    if (inputValoracion) inputValoracion.value = valoracion || '';
    
    const textareaDescripcion = form.querySelector('textarea[name="descripcion"]');
    if (textareaDescripcion) textareaDescripcion.value = descripcion || '';
    
    const inputFechaEstreno = form.querySelector(`input[name="fecha_estreno"]`);
    if (inputFechaEstreno) inputFechaEstreno.value = fechaEstreno || '';
    
    const inputTrailer = form.querySelector('input[name="trailer"]');
    if (inputTrailer) inputTrailer.value = trailer || '';
    
    // Configurar poster
    const inputPosterUrl = form.querySelector('input[name="poster_url"]');
    if (inputPosterUrl) inputPosterUrl.value = poster || '';
    
    const inputPosterActual = form.querySelector('input[name="poster_actual"]');
    if (inputPosterActual) inputPosterActual.value = poster || '';
    
    // Mostrar previsualización del poster
    const previewContainerId = esProximo ? 'posterPreviewContainerProximo' : 'posterPreviewContainer';
    const previewContainer = document.getElementById(previewContainerId);
    if (previewContainer) {
        previewContainer.innerHTML = '';
        if (poster) {
            const img = document.createElement('img');
            img.src = poster.startsWith('http') ? poster : `posters/${poster}`;
            img.style.maxWidth = '100%';
            img.style.maxHeight = '200px';
            img.style.borderRadius = '4px';
            previewContainer.appendChild(img);
        }
    }
    
    // Configurar duración (horas y minutos)
    if (duracion) {
        const duracionMinutos = parseInt(duracion);
        const horas = Math.floor(duracionMinutos / 60);
        const minutos = duracionMinutos % 60;
        
        const selectHoras = form.querySelector(`select[name="horas${suffix}"]`);
        if (selectHoras) selectHoras.value = horas.toString();
        
        const selectMinutos = form.querySelector(`select[name="minutos${suffix}"]`);
        if (selectMinutos) selectMinutos.value = minutos.toString();
    }
    
    // Configurar géneros
    if (genero) {
        const generosArray = genero.split(',').map(g => g.trim());
        const generosSeleccionadosDiv = document.getElementById(`generosSeleccionados${formPrefix}`);
        
        if (generosSeleccionadosDiv) {
            generosSeleccionadosDiv.innerHTML = ''; // Limpiar géneros actuales
            
            generosArray.forEach(g => {
                if (g) {
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-warning text-dark me-2 mb-2';
                    badge.innerHTML = `${g} <span style="cursor: pointer;" onclick="this.parentElement.remove()">×</span>`;
                    
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = esProximo ? 'generos_proximo[]' : 'generos[]';
                    input.value = g;
                    badge.appendChild(input);
                    
                    generosSeleccionadosDiv.appendChild(badge);
                }
            });
        }
    }
    
    // Configurar actores
    if (actores) {
        const actoresArray = actores.split(',').map(a => a.trim());
        const actoresContainer = document.getElementById(`actoresContainer${formPrefix}`);
        
        if (actoresContainer) {
            actoresContainer.innerHTML = ''; // Limpiar actores actuales
            
            actoresArray.forEach((actor, index) => {
                if (actor) {
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.className = 'form-control mb-1';
                    input.name = `actores${suffix}[]`;
                    input.placeholder = 'Nombre del actor';
                    input.value = actor;
                    input.required = true;
                    actoresContainer.appendChild(input);
                }
            });
            
            // Asegurarnos de que haya al menos un campo
            if (actoresArray.length === 0) {
                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'form-control mb-1';
                input.name = `actores${suffix}[]`;
                input.placeholder = 'Nombre del actor';
                input.required = true;
                actoresContainer.appendChild(input);
            }
        }
    }
    
    // Cambiar el botón principal y agregar un campo para identificar que es una edición
    if (esProximo) {
        const btnAgregarProximo = document.getElementById('btnAgregarProximo');
        const btnGuardarCambiosProximo = document.getElementById('btnGuardarCambiosProximo');
        
        if (btnAgregarProximo) btnAgregarProximo.style.display = 'none';
        if (btnGuardarCambiosProximo) btnGuardarCambiosProximo.style.display = 'block';
    } else {
        const btnAgregar = document.getElementById('btnAgregar');
        const btnGuardarCambios = document.getElementById('btnGuardarCambios');
        
        if (btnAgregar) btnAgregar.style.display = 'none';
        if (btnGuardarCambios) btnGuardarCambios.style.display = 'block';
    }
    
    // Agregar un campo oculto para el ID (diferente del anterior para evitar conflictos)
    let inputIdElement = form.querySelector('input[name="id"]');
    if (!inputIdElement) {
        inputIdElement = document.createElement('input');
        inputIdElement.type = 'hidden';
        inputIdElement.name = 'id';
        form.appendChild(inputIdElement);
    }
    inputIdElement.value = id;
    
    console.log('Formulario llenado correctamente');
}

// Funciones para la sección destacada
document.addEventListener('DOMContentLoaded', function() {
    // Configurar desplazamiento suave para la flecha de "Ver cartelera"
    const flechaLink = document.querySelector('.flecha-link');
    if (flechaLink) {
        flechaLink.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    }
    
    // Configurar el botón "Ver trailer" para abrir el trailer en un modal
    const btnTrailer = document.querySelector('.btn-trailer');
    if (btnTrailer) {
        btnTrailer.addEventListener('click', function(e) {
            e.preventDefault();
            
            const trailerUrl = this.getAttribute('href');
            if (!trailerUrl) return;
            
            // Extraer ID de YouTube
            let videoId = '';
            const match = trailerUrl.match(/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
            if (match && match[1]) {
                videoId = match[1];
            } else {
                // Si no podemos extraer el ID, abrir en nueva pestaña
                window.open(trailerUrl, '_blank');
                return;
            }
            
            // Crear un modal para mostrar el trailer
            const modalHTML = `
                <div class="modal fade" id="trailerModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content bg-dark">
                            <div class="modal-header border-0">
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-0">
                                <div class="ratio ratio-16x9">
                                    <iframe src="https://www.youtube.com/embed/${videoId}?autoplay=1&modestbranding=1" 
                                            title="YouTube video" allowfullscreen 
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                                    </iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Añadir el modal al body
            const modalElement = document.createElement('div');
            modalElement.innerHTML = modalHTML;
            document.body.appendChild(modalElement);
            
            // Mostrar el modal
            const trailerModal = new bootstrap.Modal(document.getElementById('trailerModal'));
            trailerModal.show();
            
            // Eliminar el modal del DOM cuando se cierre
            document.getElementById('trailerModal').addEventListener('hidden.bs.modal', function() {
                document.body.removeChild(modalElement);
            });
        });
    }
});

// Hacer función disponible en consola
window.testPanelSimple = testPanelSimple;
window.cerrarPanelPelicula = cerrarPanelPelicula;
window.mostrarPanelPelicula = mostrarPanelPelicula;