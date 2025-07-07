/* ---------- Abrir panel para crear horario ---------- */
document.querySelectorAll('.btnEditar').forEach(btn => {
    btn.addEventListener('click', e => {
        const { id, titulo } = e.target.dataset;

        document.getElementById('peliculaNombre').textContent = titulo;
        document.getElementById('inputPelId').value = id;

        // limpiar campos
        ['inputSala','inputFecha','inputHoraInicio','inputHoraFin']
            .forEach(idc => { const el = document.getElementById(idc); if (el) el.value = ''; });

        document.getElementById('formHorarioContainer').style.display = 'block';
    });
});


/* ---------- Ver horarios ---------- */
document.querySelectorAll('.btnVerHorarios').forEach(btn => {
    btn.addEventListener('click', () => {
        const { id: peliculaId, titulo } = btn.dataset;

        document.getElementById('horarioTitulo').innerText = titulo;
        document.getElementById('panelHorarios').style.display = 'block';

        fetch(`../conexion/horarios/get_horario.php?pelicula_id=${peliculaId}`)
            .then(r => r.json())
            .then(data => {
                const cont = document.getElementById('listaHorarios');
                cont.innerHTML = '';

                if (!data.length) {
                    cont.innerHTML = '<div class="sin-peliculas"><h3>No hay horarios asignados</h3><p>Agrega el primer horario para esta película</p></div>';
                    return;
                }

                /* --- meses en español para formatear --- */
                const meses = [
                    'Enero','Febrero','Marzo','Abril','Mayo','Junio',
                    'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'
                ];

                data.forEach(h => {
                    /* Formatear «07 de Julio» */
                    const [y,m,d] = h.fecha.split('-');      // yyyy‑mm‑dd
                    const fechaBonita = `${d} de ${meses[+m-1]}`;

                    const div = document.createElement('div');
                    div.className = 'horario-item';

                    /*  ⬇️  Añadimos la sala  */
                    div.innerHTML = `
                        <div class="horario-info">
                            <div class="horario-fecha">${fechaBonita}</div>
                            <div class="horario-detalles">
                                <i class="fas fa-door-open"></i> Sala ${h.numero_sala} • 
                                <i class="fas fa-clock"></i> ${h.hora_inicio} – ${h.hora_fin}
                            </div>
                        </div>
                        <div class="horario-acciones">
                            <button class="btn btn-sm btn-danger btnEliminarHorario"
                                    data-id="${h.pelicula_horario_id}">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>`;
                    cont.appendChild(div);
                });

                /* eventos eliminar */
                document.querySelectorAll('.btnEliminarHorario').forEach(b => {
                    b.addEventListener('click', () => {
                        document.getElementById('inputEliminarHorarioId').value = b.dataset.id;
                        new bootstrap.Modal(document.getElementById('modalEliminarHorario')).show();
                    });
                });
            })
            .catch(() => {
                document.getElementById('listaHorarios').innerHTML = 'Error al cargar horarios.';
            });
    });
});


/* ---------- Cerrar paneles ---------- */
document.getElementById('btnCerrarHorario')
        .addEventListener('click', () =>
            document.getElementById('formHorarioContainer').style.display = 'none');

document.getElementById('btnCerrarListaHorarios')
        .addEventListener('click', () =>
            document.getElementById('panelHorarios').style.display = 'none');


/* ---------- Ocultar alertas automáticas ---------- */
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(a => a.remove());
}, 4000);
