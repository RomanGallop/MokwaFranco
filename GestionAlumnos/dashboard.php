<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

if (!user_is_logged()) {
    header("Location: index.php");
    exit;
}

$user   = get_logged_user();
$userId = $user['id'];
$role   = $user['role'];

$messages = [];

// =========================
// PROCESAR FORMULARIOS (Profesor)
// =========================

// Cargar Nota
if (isset($_POST['add_nota']) && $role === 'Profesor') {
    $id_ac  = (int)$_POST['alumno_clase'];
    $tipo   = $_POST['tipo'];
    $valor  = floatval($_POST['valor']);
    $fecha  = date('Y-m-d');

    if (can_edit_class($pdo, $userId, $_POST['id_clase'])) {
        $st = $pdo->prepare("INSERT INTO nota (ID_Alumno_Clase, Tipo, Valor, Fecha) VALUES (?, ?, ?, ?)");
        $st->execute([$id_ac, $tipo, $valor, $fecha]);
        $messages[] = "✅ Nota cargada.";
    } else {
        $messages[] = "❌ No tenés permiso para esta clase.";
    }
}

// Registrar Resumen de Asistencias
if (isset($_POST['add_asistencia']) && $role === 'Profesor') {
    $id_ac    = (int)$_POST['alumno_clase_att'];
    $periodo  = $_POST['periodo'];
    $total    = (int)$_POST['total'];
    $asis     = (int)$_POST['asistencias'];
    $inasis   = (int)$_POST['inasistencias'];

    if (can_edit_class($pdo, $userId, $_POST['id_clase'])) {
        $st = $pdo->prepare("
            INSERT INTO alumno_asistencia (ID_Alumno_Clase, Periodo, Total_Clases, Asistencias, Inasistencias)
            VALUES (?, ?, ?, ?, ?)
        ");
        $st->execute([$id_ac, $periodo, $total, $asis, $inasis]);
        $messages[] = "✅ Resumen de asistencia registrado.";
    } else {
        $messages[] = "❌ No tenés permiso para esta clase.";
    }
}

// =========================
// DATOS PARA PROFESORES
// =========================
$profesorClases = [];
$alumnoInscripciones = [];

if ($role === 'Profesor') {
    $profesorClases = get_user_classes($pdo, $userId, 'Profesor');

    foreach ($profesorClases as $c) {
        $st = $pdo->prepare("
            SELECT ac.ID_Alumno_Clase, al.Nombre, al.Apellido
            FROM alumno_clase ac
            JOIN alumno al ON ac.ID_Alumno = al.ID_Alumno
            WHERE ac.ID_Clase = ?
        ");
        $st->execute([$c['ID_Clase']]);
        $alumnoInscripciones[$c['ID_Clase']] = $st->fetchAll(PDO::FETCH_ASSOC);
    }
}

// =========================
// DATOS PARA ALUMNOS
// =========================
$alumnoClases = [];
$detalleClase = null;

if ($role === 'Alumno') {
    $alumnoClases = get_user_classes($pdo, $userId, 'Alumno');

    if (isset($_GET['clase'])) {
        $claseId = (int)$_GET['clase'];

        $st = $pdo->prepare("
            SELECT c.Nombre AS ClaseNombre, ac.ID_Alumno_Clase
            FROM alumno_clase ac
            JOIN clase c ON c.ID_Clase = ac.ID_Clase
            JOIN usuario u ON u.ID_Alumno = ac.ID_Alumno
            WHERE u.ID_Usuario = ? AND c.ID_Clase = ?
        ");
        $st->execute([$userId, $claseId]);
        $detalleClase = $st->fetch(PDO::FETCH_ASSOC);

        if ($detalleClase) {
            $id_ac = $detalleClase['ID_Alumno_Clase'];

            // Notas
            $stN = $pdo->prepare("SELECT Tipo, Valor, Fecha FROM nota WHERE ID_Alumno_Clase = ?");
            $stN->execute([$id_ac]);
            $detalleClase['Notas'] = $stN->fetchAll(PDO::FETCH_ASSOC);

            // Asistencias (resumen)
            $stA = $pdo->prepare("
                SELECT Periodo, Total_Clases, Asistencias, Inasistencias 
                FROM alumno_asistencia 
                WHERE ID_Alumno_Clase = ?
            ");
            $stA->execute([$id_ac]);
            $detalleClase['Asistencias'] = $stA->fetchAll(PDO::FETCH_ASSOC);

            // Calcular estado
            $notas = array_column($detalleClase['Notas'], 'Valor', 'Tipo');
            $estado = "Libre";

            if (isset($notas['1C']) && isset($notas['2C'])) {
                if ($notas['1C'] >= 7 && $notas['2C'] >= 7) {
                    $estado = "Promocionado";
                } elseif ($notas['1C'] >= 6 && $notas['2C'] >= 6) {
                    $estado = "Regular";
                }
            }

            foreach ($detalleClase['Asistencias'] as $a) {
                if ($a['Total_Clases'] > 0 && ($a['Inasistencias'] / $a['Total_Clases']) > 0.5) {
                    $estado = "Libre";
                }
            }

            $detalleClase['EstadoFinal'] = $estado;

            // Totales de asistencia
            $totalAsistencias = 0;
            $totalInasistencias = 0;
            $totalClases = 0;
            foreach ($detalleClase['Asistencias'] as $a) {
                $totalAsistencias += $a['Asistencias'];
                $totalInasistencias += $a['Inasistencias'];
                $totalClases += $a['Total_Clases'];
            }
            $detalleClase['TotalAsistencias'] = $totalAsistencias;
            $detalleClase['TotalInasistencias'] = $totalInasistencias;
            $detalleClase['TotalClases'] = $totalClases;
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="dashboard-header">
        <h1>Bienvenido, <?= htmlspecialchars($user['username']) ?> (<?= $role ?>)</h1>
        <a href="logout.php">Cerrar sesión</a>
    </div>

    <?php foreach ($messages as $m): ?>
        <p class="success"><?= $m ?></p>
    <?php endforeach; ?>

    <?php if ($role === 'Profesor'): ?>
        <h2>Mis Clases</h2>
        <?php foreach ($profesorClases as $c): ?>
            <div class="card">
                <h3><?= htmlspecialchars($c['Nombre']) ?></h3>
                <h4>Alumnos</h4>
                <?php foreach ($alumnoInscripciones[$c['ID_Clase']] as $al): ?>
                    <p><?= $al['Nombre'] . " " . $al['Apellido'] ?></p>

                    <!-- Formulario Nota -->
                    <form method="post">
                        <input type="hidden" name="id_clase" value="<?= $c['ID_Clase'] ?>">
                        <input type="hidden" name="alumno_clase" value="<?= $al['ID_Alumno_Clase'] ?>">
                        <label>Tipo:
                            <select name="tipo">
                                <option value="1C">1C</option>
                                <option value="2C">2C</option>
                                <option value="Final">Final</option>
                                <option value="Recuperatorio">Recuperatorio</option>
                            </select>
                        </label>
                        <label>Nota: <input type="number" name="valor" step="0.01" required></label>
                        <button type="submit" name="add_nota">Cargar Nota</button>
                    </form>

                    <!-- Formulario Resumen de Asistencia -->
                    <form method="post">
                        <input type="hidden" name="id_clase" value="<?= $c['ID_Clase'] ?>">
                        <input type="hidden" name="alumno_clase_att" value="<?= $al['ID_Alumno_Clase'] ?>">
                        <label>Periodo:
                            <select name="periodo">
                                <option value="1C">1C</option>
                                <option value="2C">2C</option>
                                <option value="Anual">Anual</option>
                            </select>
                        </label>
                        <label>Total Clases: <input type="number" name="total" required></label>
                        <label>Asistencias: <input type="number" name="asistencias" required></label>
                        <label>Inasistencias: <input type="number" name="inasistencias" required></label>
                        <button type="submit" name="add_asistencia">Registrar Resumen</button>
                    </form>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($role === 'Alumno'): ?>
        <h2>Mis Clases</h2>
        <ul>
            <?php foreach ($alumnoClases as $c): ?>
                <li>
                    <a href="?clase=<?= $c['ID_Clase'] ?>">
                        <?= htmlspecialchars($c['Nombre']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if ($detalleClase): ?>
            <div class="card">
                <h3><?= $detalleClase['ClaseNombre'] ?></h3>

                <h4>Notas</h4>
                <ul>
                    <?php foreach ($detalleClase['Notas'] as $n): ?>
                        <li><?= $n['Tipo'] ?>: <?= $n['Valor'] ?> (<?= $n['Fecha'] ?>)</li>
                    <?php endforeach; ?>
                </ul>

                <h4>Asistencias (Resumen)</h4>
                <ul>
                    <?php foreach ($detalleClase['Asistencias'] as $a): ?>
                        <li><?= $a['Periodo'] ?> →
                            Asistencias: <?= $a['Asistencias'] ?>/<?= $a['Total_Clases'] ?>
                            (Inasistencias: <?= $a['Inasistencias'] ?>)
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="totales">
                    <h4>Totales del Año</h4>
                    <p>
                        Total Clases: <?= $detalleClase['TotalClases'] ?><br>
                        Total Asistencias: <?= $detalleClase['TotalAsistencias'] ?><br>
                        Total Inasistencias: <?= $detalleClase['TotalInasistencias'] ?>
                    </p>
                </div>

                <h4>Estado Final: <?= $detalleClase['EstadoFinal'] ?></h4>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</body>

</html>