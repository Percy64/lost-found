<?php
require_once 'conexion.php';
require_once 'includes/QRGenerator.php';

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

// Obtener información del código QR
$qrGenerator = new QRGenerator();
$qr_info = $qrGenerator->obtenerQRMascota($pdo, $id_mascota);

// Debug temporal
$debug_qr = [
    'id_mascota' => $id_mascota,
    'qr_info_exists' => !empty($qr_info),
    'qr_info' => $qr_info
];

// Si no existe QR, generar uno nuevo
if (!$qr_info && $mascota) {
    $qr_result = $qrGenerator->generarQRMascota($id_mascota, $mascota);
    $debug_qr['qr_generation_attempted'] = true;
    $debug_qr['qr_generation_result'] = $qr_result;
    
    if ($qr_result['success']) {
        $success = $qrGenerator->actualizarQREnBD($pdo, $id_mascota, $qr_result);
        $debug_qr['db_update_success'] = $success;
        $qr_info = $qrGenerator->obtenerQRMascota($pdo, $id_mascota);
        $debug_qr['qr_info_after_generation'] = $qr_info;
    }
}
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

            <!-- Código QR -->
            <div class="qr-section">
                <h3>Código QR de Identificación</h3>
                
                <!-- Debug temporal -->
                <?php if (isset($_GET['debug'])): ?>
                    <div style="background: #f0f0f0; padding: 15px; margin: 10px 0; border-radius: 5px; font-size: 12px; text-align: left;">
                        <strong>🔍 Debug QR Info:</strong><br>
                        <pre><?= htmlspecialchars(print_r($debug_qr, true)) ?></pre>
                        <?php if ($qr_info): ?>
                            <strong>Archivo existe:</strong> <?= file_exists($qr_info['ruta_imagen']) ? '✅ Sí' : '❌ No' ?><br>
                            <strong>Ruta imagen:</strong> <?= htmlspecialchars($qr_info['ruta_imagen'] ?? 'N/A') ?><br>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <div class="qr-container">
                    <?php if ($qr_info && file_exists($qr_info['ruta_imagen'])): ?>
                        <div class="qr-image-container">
                            <img src="<?= htmlspecialchars($qr_info['ruta_imagen']) ?>" 
                                 alt="Código QR de <?= htmlspecialchars($mascota['nombre']) ?>" 
                                 class="qr-image" />
                        </div>
                        <div class="qr-info">
                            <p class="qr-description">
                                Escanea este código QR para acceder rápidamente al perfil de <?= htmlspecialchars($mascota['nombre']) ?>
                            </p>
                            <div class="qr-actions">
                                <button type="button" onclick="descargarQR()" class="btn-qr-download">
                                    📥 Descargar QR
                                </button>
                                <button type="button" onclick="imprimirQR()" class="btn-qr-print">
                                    🖨️ Imprimir QR
                                </button>
                                <button type="button" onclick="compartirQR()" class="btn-qr-share">
                                    📤 Compartir
                                </button>
                            </div>
                            <div class="qr-url">
                                <small>URL: <a href="<?= htmlspecialchars($qr_info['url_qr']) ?>" target="_blank">
                                    <?= htmlspecialchars($qr_info['url_qr']) ?>
                                </a></small>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="qr-placeholder">
                            <div class="qr-placeholder-icon">📱</div>
                            <p>Código QR no disponible</p>
                            <?php if (isset($_GET['debug'])): ?>
                                <div style="font-size: 11px; color: #666; margin: 10px 0;">
                                    Razón: <?php 
                                    if (!$qr_info) echo "No hay info de QR en BD";
                                    elseif (!file_exists($qr_info['ruta_imagen'])) echo "Archivo QR no existe: " . $qr_info['ruta_imagen'];
                                    else echo "Razón desconocida";
                                    ?>
                                </div>
                            <?php endif; ?>
                            <button type="button" onclick="generarQR()" class="btn-generate-qr">
                                🔄 Generar QR
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="acciones">
                <button type="button" class="btn-accion btn-contactar" onclick="contactarDueño()">
                    📞 Contactar Dueño
                </button>
                <button type="button" class="btn-accion btn-volver" onclick="window.history.back()">
                    ← Volver
                </button>
            </div>

            <!-- Navegación -->
            <div class="navigation">
                <button class="nav-btn" onclick="window.location.href='home.php'">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                </button>
                <button class="nav-btn" onclick="window.location.href='busqueda.php'">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>
                <button class="nav-btn" onclick="verificarLogin()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </button>
                <button class="nav-btn" onclick="alert('Información')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                </button>
            </div>
        </div>
    </section>

    <script>
        function verificarLogin() {
            // Check if user is logged in and redirect accordingly
            window.location.href = 'perfil_usuario.php';
        }

        function contactarDueño() {
            const telefono = '<?= htmlspecialchars($mascota['telefono'] ?? '') ?>';
            if (telefono) {
                window.location.href = 'tel:' + telefono;
            } else {
                alert('No hay información de contacto disponible.');
            }
        }

        function descargarQR() {
            const qrImage = document.querySelector('.qr-image');
            if (qrImage) {
                const link = document.createElement('a');
                link.download = 'qr_<?= htmlspecialchars($mascota['nombre']) ?>_<?= $id_mascota ?>.png';
                link.href = qrImage.src;
                link.click();
            }
        }

        function imprimirQR() {
            const qrSection = document.querySelector('.qr-section');
            if (qrSection) {
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <html>
                        <head>
                            <title>QR Code - <?= htmlspecialchars($mascota['nombre']) ?></title>
                            <style>
                                body { font-family: Arial, sans-serif; text-align: center; margin: 20px; }
                                .qr-print { max-width: 300px; margin: 20px auto; }
                                .qr-print img { width: 100%; height: auto; }
                                .pet-info { margin: 20px 0; }
                                .contact-info { font-size: 14px; color: #666; }
                            </style>
                        </head>
                        <body>
                            <div class="qr-print">
                                <h2><?= htmlspecialchars($mascota['nombre']) ?></h2>
                                <p><?= htmlspecialchars($mascota['especie']) ?> - <?= htmlspecialchars($mascota['raza'] ?? '') ?></p>
                                <img src="${qrImage.src}" alt="QR Code">
                                <div class="contact-info">
                                    <p>Contacto: <?= htmlspecialchars($mascota['telefono'] ?? 'No disponible') ?></p>
                                    <p>Escanea el código para más información</p>
                                </div>
                            </div>
                        </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.print();
            }
        }

        function compartirQR() {
            const url = '<?= htmlspecialchars($qr_info['url_qr'] ?? '') ?>';
            const texto = 'Perfil de mascota: <?= htmlspecialchars($mascota['nombre']) ?>';
            
            if (navigator.share) {
                navigator.share({
                    title: texto,
                    text: 'Información de mascota perdida/encontrada',
                    url: url
                }).catch(console.error);
            } else {
                // Fallback para navegadores que no soportan Web Share API
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(url).then(() => {
                        alert('URL copiada al portapapeles: ' + url);
                    });
                } else {
                    prompt('Copia esta URL:', url);
                }
            }
        }

        function generarQR() {
            // Recargar la página para intentar generar el QR nuevamente
            window.location.reload();
        }
    </script>
</body>
</html>