<?php
/* ─────────────────── Seguridad & conexión ─────────────────── */
session_start();
if (empty($_POST['funcion_id'])) {
    header("Location: ../comprar/comprar_entradas.php");
    exit();
}
$funcion_id = (int)$_POST['funcion_id'];

require_once '../conexion/conexion.php';
require_once '../resources/header/header.php';

/* ─────────────────── Datos de la función ─────────────────── */
$sql = "
    SELECT
        ph.id                       AS funcion_id,
        s.id                        AS sala_id,
        s.numero_sala,
        s.cantidad_asientos,
        p.titulo,
        p.poster,
        p.clasificacion,
        p.duracion,
        h.fecha,
        h.hora_inicio
    FROM pelicula_horario ph
    JOIN salas     s ON s.id = ph.sala_id
    JOIN horarios  h ON h.id = ph.horario_id
    JOIN peliculas p ON p.id = ph.pelicula_id
    WHERE ph.id = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$funcion_id]);
$funcion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$funcion) {
    echo "<div class='container mt-5 alert alert-danger'>Función no encontrada.</div>";
    exit();
}

/* ────────────────  NUEVO: obtener butacas ya ocupadas ──────────────── */
$stmtOcup = $pdo->prepare("
    SELECT asiento_num
    FROM butacas_ocupadas
    WHERE funcion_id = ?
");
$stmtOcup->execute([$funcion_id]);
$ocupadas = array_column($stmtOcup->fetchAll(PDO::FETCH_ASSOC), 'asiento_num');

/* ─── Cálculo de filas/columnas para la cuadrícula ─── */
$total_asientos  = (int)$funcion['cantidad_asientos'];
$por_fila        = 10;                               // 10 butacas por fila
$filas           = (int)ceil($total_asientos / $por_fila);
$libres          = $total_asientos - count($ocupadas);
?>
<!-- ─────────────────── Estilos / Bootstrap ─────────────────── -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../resources/header/css/styles.css">
<link rel="stylesheet" href="../resources/index/css/styles.css">
<link rel="stylesheet" href="css/styles.css">

<div class="container mt-4">
    <div class="row mb-4 movie-info-container">
        <div class="col-md-3 text-center">
            <?php if (!empty($funcion['poster'])): ?>
                <?php if (preg_match('/^https?:\/\//', $funcion['poster'])): ?>
                    <img src="<?= htmlspecialchars($funcion['poster']) ?>"
                         style="width:160px;border-radius:8px;">
                <?php else: ?>
                    <img src="<?= '../posters/' . htmlspecialchars($funcion['poster']) ?>"
                         style="width:160px;border-radius:8px;">
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <div class="col-md-9 d-flex flex-column justify-content-center">
            <h3 class="titulo"><?= htmlspecialchars($funcion['titulo']) ?></h3>
            <p class="mb-1" style="color: #fff;"><strong class="categorias">Sala:</strong> <?= $funcion['numero_sala'] ?></p>
            <?php
                // Nueva forma sin strftime (compatible PHP 8.1+)
                $dt = DateTime::createFromFormat('Y-m-d', $funcion['fecha']);
                $meses = [
                    'January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo',
                    'April' => 'Abril', 'May' => 'Mayo', 'June' => 'Junio',
                    'July' => 'Julio', 'August' => 'Agosto', 'September' => 'Septiembre',
                    'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre'
                ];
                $dia = $dt->format('d');
                $mes_en = $dt->format('F');
                $mes_es = $meses[$mes_en] ?? $mes_en;
                $año = $dt->format('Y');
                $fecha_completa = $dia . ' del ' . $mes_es . ' del ' . $año;

                // Convertir hora a formato 12 horas
                $hora_24 = substr($funcion['hora_inicio'], 0, 5);
                $hora_obj = DateTime::createFromFormat('H:i', $hora_24);
                $hora_12 = $hora_obj->format('g:i A');
                // Traducir AM/PM a español
                $hora_12 = str_replace(['AM', 'PM'], ['a.m.', 'p.m.'], $hora_12);
            ?>
            <p class="mb-1" style="color: #fff;"><strong class="categorias">Función:</strong> <?= $fecha_completa ?></p>
            <p class="mb-1" style="color: #fff;"><strong class="categorias">Hora:</strong> <?= $hora_12 ?></p>
            <p style="color: #fff;"><strong class="categorias">Asientos disponibles:</strong> <?= $libres ?></p>
        </div>
    </div>

    <!-- ─────────── Formulario de selección de asientos ─────────── -->
    <form method="POST" id="formAsientos" action="../pagos/pagos.php">
        <input type="hidden" name="funcion_id" value="<?= $funcion['funcion_id'] ?>">

        <div class="row">
            <!-- Leyenda de asientos -->
            <div class="col-md-4">
                <div class="seat-legend-container">
                    <div class="seat-legend">

                        <div class="legend-item">
                            <div class="legend-seat available"></div>
                            <span>Asiento disponible</span>
                        </div>

                        <div class="legend-item">
                            <div class="legend-seat occupied"></div>
                            <span>Asiento ocupado</span>
                        </div>

                        <div class="legend-item">
                            <div class="legend-seat selected"></div>
                            <span>Asiento seleccionado</span>
                        </div>

                    </div>
                </div>
                
                <!-- Información de asientos seleccionados -->
                <div class="selected-seats-info">
                    <div class="selected-info-item">
                        <span class="selected-info-label">Número de asientos seleccionados:</span>
                        <span class="selected-info-value" id="selectedCount">0</span>
                    </div>
                    <div class="selected-info-item">
                        <span class="selected-info-label">Asientos:</span>
                        <span class="selected-info-value" id="selectedSeats">Ninguno</span>
                    </div>
                </div>
                
                <!-- Información adicional de la película -->
                <div class="movie-additional-info">
                    <div class="additional-info-item">
                        <span class="additional-info-label">Clasificación:</span>
                        <?php 
                            $clasificacion = $funcion['clasificacion'] ?? 'N/A';
                            $claseColor = '';
                            switch(strtoupper($clasificacion)) {
                                case 'A':
                                case 'AA':
                                    $claseColor = 'clasificacion-verde';
                                    break;
                                case 'B':
                                case 'B15':
                                    $claseColor = 'clasificacion-amarilla';
                                    break;
                                case 'C':
                                case 'D':
                                    $claseColor = 'clasificacion-roja';
                                    break;
                                default:
                                    $claseColor = 'additional-info-value';
                                    break;
                            }
                        ?>
                        <span class="<?= $claseColor ?>"><?= htmlspecialchars($clasificacion) ?></span>
                    </div>
                    <div class="additional-info-item">
                        <span class="additional-info-label">Duración:</span>
                        <span class="additional-info-value"><?= htmlspecialchars($funcion['duracion'] ?? 'N/A') ?> min</span>
                    </div>
                    <div class="additional-info-item">
                        <span class="additional-info-label">Total:</span>
                        <span class="additional-info-value total-price" id="totalPrice">$0</span>
                    </div>
                </div>
                
                <!-- Información de puntos -->
                <div class="points-info">
                    <div class="points-info-item">
                        <span class="points-info-label">Con esta compra ganarás:</span>
                        <span class="points-info-value" id="pointsEarned">0 puntos</span>
                    </div>
                </div>
            </div>
            
            <!-- Área de selección de asientos -->
            <div class="col-md-8">
                <div class="d-flex flex-column align-items-center mb-4 seating-area-container">
            <?php
            // Cálculo del ancho dinámico de la cuadrícula (asientos por fila * ancho contenedor asiento + margen extra)
            $seat_width = 34; // Debe coincidir con el width del div de cada asiento
            $seat_margin = 8; // 4px a cada lado (ajustar si se cambia el margin en CSS)
            $grid_width = ($por_fila * $seat_width) + ($por_fila * $seat_margin);
            ?>
            <div class="d-flex justify-content-center mb-3">
                <div style="width: <?= $grid_width ?>px; min-width: 220px; max-width: 100vw; height: 32px; background: linear-gradient(180deg, #e9ecef 80%, #d1d5db 100%); border-radius: 12px 12px 18px 18px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); display: flex; align-items: center; justify-content: center; font-weight: 600; color: #555; font-size: 1.1rem; letter-spacing: 1px; border: 1px solid #ccc; transition: width .2s;">
                    Pantalla
                </div>
            </div>
            <div style="display: inline-block; width: <?= $grid_width ?>px; min-width: 220px; max-width: 100vw;">
            <?php
            $num = 1;
            for ($fila = 1; $fila <= $filas; $fila++):
                echo '<div style="display: flex;">';
                for ($col = 1; $col <= $por_fila && $num <= $total_asientos; $col++, $num++):
                    echo '<div style="width:42px;display:flex;justify-content:center;align-items:center;">';
                    if (in_array($num, $ocupadas)) {      /* ── asiento vendido ── */
                        echo "<span class='seat disabled'><span class='seat-num'>$num</span></span>";
                    } else {                              /* ── asiento libre ── */
            ?>
                        <label style="margin-bottom:0;">
                            <input type="checkbox" name="asientos[]" value="<?= $num ?>" class="visually-hidden">
                            <span class="seat"><span class="seat-num"><?= $num ?></span></span>
                        </label>
            <?php
                    }
                    echo '</div>';
                endfor;
                echo '</div>';
            endfor;
            ?>
            </div>
            <br>
            <small class="instruction-text">Selecciona tus asientos y presiona “Continuar”.</small>
        </div>

        <div class="d-flex justify-content-center gap-3 buttons-container">
            <a href="../comprar/comprar_entradas.php" class="btn btn-custom-cancel">Cancelar</a>
            <button type="submit" class="btn btn-custom-continue">Continuar</button>
        </div>
    </form>

    <!-- Modal de inicio de sesión -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content elimination-modal">
          <div class="modal-header elimination-modal-header">
            <h5 class="modal-title elimination-modal-title" id="loginModalLabel">
                
                Iniciar Sesión
            </h5>
            <button type="button" class="btn-close elimination-modal-close" data-bs-dismiss="modal" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="modal-body elimination-modal-body">
            <p class="elimination-modal-question">Debes iniciar sesión para continuar con la compra</p>
          </div>
          <div class="modal-footer elimination-modal-footer">
            <button type="button" class="btn btn-elimination-cancel" data-bs-dismiss="modal">Cancelar</button>
            <a href="../login/login.php" class="btn btn-elimination-confirm">Iniciar Sesión</a>
          </div>
        </div>
      </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formAsientos');
    const selectedCountElement = document.getElementById('selectedCount');
    const selectedSeatsElement = document.getElementById('selectedSeats');
    const totalPriceElement = document.getElementById('totalPrice');
    const pointsEarnedElement = document.getElementById('pointsEarned');
    
    // Función para actualizar la información de asientos seleccionados
    function updateSelectedSeatsInfo() {
        const checkboxes = document.querySelectorAll('input[name="asientos[]"]:checked');
        const selectedSeats = Array.from(checkboxes).map(checkbox => checkbox.value);
        const seatCount = selectedSeats.length;
        const totalPrice = seatCount * 70; // $70 por asiento
        const pointsEarned = seatCount * 5; // 5 puntos por asiento
        
        selectedCountElement.textContent = seatCount;
        selectedSeatsElement.textContent = seatCount > 0 ? selectedSeats.join(', ') : 'Ninguno';
        totalPriceElement.textContent = '$' + totalPrice;
        pointsEarnedElement.textContent = pointsEarned + (pointsEarned === 1 ? ' punto' : ' puntos');
    }
    
    // Agregar event listeners a todos los checkboxes de asientos
    document.querySelectorAll('input[name="asientos[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedSeatsInfo);
    });
    
    form.addEventListener('submit', function(e) {
        <?php if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] ?? '') !== 'normal'): ?>
            e.preventDefault();
            const modal = new bootstrap.Modal(document.getElementById('loginModal'));
            modal.show();
        <?php endif; ?>
    });
});
</script>
</body>
</html>
