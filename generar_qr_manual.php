<?php
require_once 'conexion.php';
require_once 'includes/QRGenerator.php';

echo "<h2>🔧 Generación Manual de QR</h2>";

$id_mascota = 1;

// Instanciar generador
$qrGenerator = new QRGenerator();

echo "<h3>Información del sistema:</h3>";
echo "Base URL detectada: " . $qrGenerator->baseUrl ?? 'No definida' . "<br>";
echo "Directorio QR: assets/images/qr/<br>";
echo "Directorio existe: " . (file_exists('assets/images/qr/') ? '✅ Sí' : '❌ No') . "<br>";

// Generar QR forzadamente
echo "<h3>Generando QR para mascota ID $id_mascota...</h3>";

$qr_result = $qrGenerator->generarQRMascota($id_mascota);

echo "<strong>Resultado:</strong><br>";
echo "<pre>" . print_r($qr_result, true) . "</pre>";

if ($qr_result['success']) {
    echo "<h3>Verificando archivo generado:</h3>";
    $file_exists = file_exists($qr_result['qr_path']);
    echo "Archivo existe: " . ($file_exists ? '✅ Sí' : '❌ No') . "<br>";
    echo "Ruta: " . $qr_result['qr_path'] . "<br>";
    
    if ($file_exists) {
        echo "Tamaño: " . filesize($qr_result['qr_path']) . " bytes<br>";
        echo "<img src='" . $qr_result['qr_path'] . "' alt='QR Code' style='max-width: 200px; border: 1px solid #ccc;'><br>";
    }
    
    // Actualizar BD
    echo "<h3>Actualizando base de datos...</h3>";
    $db_success = $qrGenerator->actualizarQREnBD($pdo, $id_mascota, $qr_result);
    echo "BD actualizada: " . ($db_success ? '✅ Sí' : '❌ No') . "<br>";
    
    if ($db_success) {
        // Verificar que se puede recuperar
        echo "<h3>Verificando recuperación...</h3>";
        $qr_info = $qrGenerator->obtenerQRMascota($pdo, $id_mascota);
        if ($qr_info) {
            echo "✅ QR recuperado de BD:<br>";
            echo "<pre>" . print_r($qr_info, true) . "</pre>";
        } else {
            echo "❌ No se pudo recuperar QR de BD<br>";
        }
    }
}

echo "<div style='margin: 20px 0; padding: 15px; background: #e7f3ff; border-radius: 5px;'>";
echo "<h3>🧪 Pruebas manuales:</h3>";
echo "<a href='perfil_mascota.php?id=1&debug=1' target='_blank' style='display: inline-block; margin: 5px; padding: 10px; background: #007bff; color: white; text-decoration: none; border-radius: 3px;'>Ver perfil con debug</a><br>";
echo "<a href='test_qr.php' target='_blank' style='display: inline-block; margin: 5px; padding: 10px; background: #28a745; color: white; text-decoration: none; border-radius: 3px;'>Test completo QR</a><br>";
echo "<a href='generar_qr_masivo.php' target='_blank' style='display: inline-block; margin: 5px; padding: 10px; background: #ffc107; color: black; text-decoration: none; border-radius: 3px;'>Generador masivo</a>";
echo "</div>";

// Crear QR de prueba simple
echo "<h3>🔄 Regenerar QR (sin BD):</h3>";
echo "<form method='post'>";
echo "<button type='submit' name='regenerar' style='padding: 10px; background: #dc3545; color: white; border: none; border-radius: 3px; cursor: pointer;'>🔄 Regenerar QR ahora</button>";
echo "</form>";

if (isset($_POST['regenerar'])) {
    echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>🔄 Regenerando...</strong><br>";
    
    $new_qr = $qrGenerator->generarQRMascota($id_mascota);
    if ($new_qr['success']) {
        $qrGenerator->actualizarQREnBD($pdo, $id_mascota, $new_qr);
        echo "✅ QR regenerado exitosamente<br>";
        echo "<meta http-equiv='refresh' content='2'>";
    } else {
        echo "❌ Error: " . $new_qr['error'] . "<br>";
    }
    echo "</div>";
}
?>