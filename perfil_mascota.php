<?php
require_once 'conexion.php';

// Obtener ID de la mascota desde GET
$id_mascota = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// Consulta para obtener datos de la mascota y su dueño
$sql = "SELECT m.*, u.nombre, u.apellido, u.telefono, u.email, u.direccion
        FROM mascotas m 
        LEFT JOIN usuarios u ON m.id = u.id 
        WHERE m.id_mascota = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_mascota]);
$mascota = $stmt->fetch(PDO::FETCH_ASSOC);

// Si no se encuentra la mascota, usar datos de ejemplo
if (!$mascota) {
    $mascota = [
        'id_mascota' => 1,
        'nombre' => 'Firulais',
        'especie' => 'Perro',
        'raza' => 'Labrador',
        'edad' => 3,
        'sexo' => 'Macho',
        'color' => 'Marrón',
        'foto_url' => null,
        'nombre_dueño' => 'Juan',
        'apellido' => 'Pérez',
        'telefono' => '341-5551234',
        'email' => 'juan.perez@example.com',
        'direccion' => 'Calle Falsa 123, Rosario'
    ];
} else {
    // Combinar nombre y apellido del dueño
    $mascota['nombre_dueño'] = trim($mascota['nombre'] . ' ' . $mascota['apellido']);
}

// Obtener historial médico
$sql_historial = "SELECT * FROM historial_medico WHERE id_mascota = ? ORDER BY fecha DESC LIMIT 3";
$stmt_historial = $pdo->prepare($sql_historial);
$stmt_historial->execute([$id_mascota]);
$historial = $stmt_historial->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?= htmlspecialchars($mascota['nombre']) ?></title>
    <link rel="stylesheet" href="assets/css/mascota03.css" />
</head>
<body>
    <section class="registro-mascota">
        <div class="perfil-mascota">
            <h1 class="titulo-perfil">Perfil Mascota</h1>
            
            <!-- Foto de la mascota -->
            <div class="foto-perfil">
                <?php if ($mascota['foto_url']): ?>
                    <img src="<?= htmlspecialchars($mascota['foto_url']) ?>" 
                         alt="Foto de <?= htmlspecialchars($mascota['nombre']) ?>" 
                         class="imagen-mascota" />
                <?php else: ?>
                    <div class="placeholder-foto">
                        <span>📷</span>
                        <p>Sin foto</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Nombre de la mascota -->
            <h2 class="nombre-mascota"><?= htmlspecialchars($mascota['nombre']) ?></h2>

            <!-- Información básica de la mascota -->
            <div class="info-mascota">
                <h3>Información de la Mascota</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Especie:</span>
                        <span class="info-value"><?= ucfirst(htmlspecialchars($mascota['especie'])) ?></span>
                    </div>
                    <?php if ($mascota['raza']): ?>
                    <div class="info-item">
                        <span class="info-label">Raza:</span>
                        <span class="info-value"><?= htmlspecialchars($mascota['raza']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="info-item">
                        <span class="info-label">Sexo:</span>
                        <span class="info-value"><?= ucfirst(htmlspecialchars($mascota['sexo'])) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Edad:</span>
                        <span class="info-value"><?= htmlspecialchars($mascota['edad']) ?> año<?= $mascota['edad'] != 1 ? 's' : '' ?></span>
                    </div>
                    <?php if ($mascota['color']): ?>
                    <div class="info-item">
                        <span class="info-label">Color:</span>
                        <span class="info-value"><?= htmlspecialchars($mascota['color']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Información del dueño -->
            <div class="info-dueño">
                <h3>Información del Dueño</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Nombre:</span>
                        <span class="info-value"><?= htmlspecialchars($mascota['nombre_dueño'] ?? 'No disponible') ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Teléfono:</span>
                        <span class="info-value">
                            <?php if ($mascota['telefono']): ?>
                                <a href="tel:<?= htmlspecialchars($mascota['telefono']) ?>">
                                    <?= htmlspecialchars($mascota['telefono']) ?>
                                </a>
                            <?php else: ?>
                                No disponible
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php if ($mascota['email']): ?>
                    <div class="info-item">
                        <span class="info-label">Email:</span>
                        <span class="info-value">
                            <a href="mailto:<?= htmlspecialchars($mascota['email']) ?>">
                                <?= htmlspecialchars($mascota['email']) ?>
                            </a>
                        </span>
                    </div>
                    <?php endif; ?>
                    <?php if ($mascota['direccion']): ?>
                    <div class="info-item">
                        <span class="info-label">Dirección:</span>
                        <span class="info-value"><?= htmlspecialchars($mascota['direccion']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Historial médico -->
            <?php if (!empty($historial)): ?>
            <div class="historial-medico">
                <h3>Historial Médico Reciente</h3>
                <div class="historial-grid">
                    <?php foreach ($historial as $registro): ?>
                    <div class="historial-item">
                        <div class="historial-fecha"><?= date('d/m/Y', strtotime($registro['fecha'])) ?></div>
                        <div class="historial-descripcion"><?= htmlspecialchars($registro['descripcion']) ?></div>
                        <?php if ($registro['veterinario']): ?>
                        <div class="historial-veterinario">Dr/a: <?= htmlspecialchars($registro['veterinario']) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Botones de acción -->
            <div class="acciones">
                <button type="button" class="btn-accion btn-contactar" onclick="contactarDueño()">
                    📞 Contactar Dueño
                </button>
                <button type="button" class="btn-accion btn-volver" onclick="window.history.back()">
                    ← Volver
                </button>
            </div>
        </div>
    </section>

    <script>
        function contactarDueño() {
            const telefono = '<?= htmlspecialchars($mascota['telefono'] ?? '') ?>';
            if (telefono) {
                window.location.href = 'tel:' + telefono;
            } else {
                alert('No hay información de contacto disponible.');
            }
        }
    </script>
</body>
</html>