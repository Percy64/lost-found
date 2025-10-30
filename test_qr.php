<?php
require_once 'conexion.php';
require_once 'includes/QRGenerator.php';

echo "<h2>🧪 Test de QR Generator</h2>";

$id_mascota = 1; // Usar mascota ID 1

echo "<h3>1. Instanciar QRGenerator</h3>";
try {
    $qrGenerator = new QRGenerator();
    echo "✅ QRGenerator instanciado correctamente<br>";
} catch (Exception $e) {
    echo "❌ Error al instanciar QRGenerator: " . $e->getMessage() . "<br>";
    exit;
}

echo "<h3>2. Verificar directorio QR</h3>";
$qrDir = 'assets/images/qr/';
if (file_exists($qrDir)) {
    echo "✅ Directorio QR existe: $qrDir<br>";
    echo "Permisos: " . substr(sprintf('%o', fileperms($qrDir)), -4) . "<br>";
} else {
    echo "❌ Directorio QR no existe: $qrDir<br>";
    if (mkdir($qrDir, 0777, true)) {
        echo "✅ Directorio creado exitosamente<br>";
    } else {
        echo "❌ No se pudo crear directorio<br>";
    }
}

echo "<h3>3. Buscar QR existente para mascota ID $id_mascota</h3>";
try {
    $qr_info = $qrGenerator->obtenerQRMascota($pdo, $id_mascota);
    if ($qr_info) {
        echo "✅ QR encontrado en BD:<br>";
        echo "<pre>" . print_r($qr_info, true) . "</pre>";
        
        if (file_exists($qr_info['ruta_imagen'])) {
            echo "✅ Archivo QR existe en: " . $qr_info['ruta_imagen'] . "<br>";
        } else {
            echo "❌ Archivo QR NO existe en: " . $qr_info['ruta_imagen'] . "<br>";
        }
    } else {
        echo "⚠️ No hay QR en BD para esta mascota<br>";
    }
} catch (Exception $e) {
    echo "❌ Error al buscar QR: " . $e->getMessage() . "<br>";
}

echo "<h3>4. Intentar generar nuevo QR</h3>";
try {
    $qr_result = $qrGenerator->generarQRMascota($id_mascota);
    echo "Resultado de generación:<br>";
    echo "<pre>" . print_r($qr_result, true) . "</pre>";
    
    if ($qr_result['success']) {
        echo "✅ QR generado exitosamente<br>";
        
        if (file_exists($qr_result['qr_path'])) {
            echo "✅ Archivo creado en: " . $qr_result['qr_path'] . "<br>";
            echo "Tamaño: " . filesize($qr_result['qr_path']) . " bytes<br>";
        } else {
            echo "❌ Archivo no encontrado en: " . $qr_result['qr_path'] . "<br>";
        }
        
        // Intentar actualizar BD
        echo "<h3>5. Actualizar BD</h3>";
        $db_success = $qrGenerator->actualizarQREnBD($pdo, $id_mascota, $qr_result);
        if ($db_success) {
            echo "✅ BD actualizada exitosamente<br>";
        } else {
            echo "❌ Error al actualizar BD<br>";
        }
        
    } else {
        echo "❌ Error al generar QR: " . $qr_result['error'] . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Excepción al generar QR: " . $e->getMessage() . "<br>";
}

echo "<h3>6. Verificar mascota en BD</h3>";
try {
    $sql = "SELECT id_mascota, nombre, id_qr FROM mascotas WHERE id_mascota = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_mascota]);
    $mascota = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($mascota) {
        echo "✅ Mascota encontrada:<br>";
        echo "ID: " . $mascota['id_mascota'] . "<br>";
        echo "Nombre: " . $mascota['nombre'] . "<br>";
        echo "QR ID: " . ($mascota['id_qr'] ?: 'Sin QR') . "<br>";
    } else {
        echo "❌ Mascota no encontrada con ID: $id_mascota<br>";
    }
} catch (Exception $e) {
    echo "❌ Error al verificar mascota: " . $e->getMessage() . "<br>";
}

echo "<div style='margin: 20px 0;'>";
echo "<h3>Links de test:</h3>";
echo "<a href='perfil_mascota.php?id=1&debug=1'>Ver perfil con debug</a><br>";
echo "<a href='generar_qr_masivo.php'>Generador masivo</a><br>";
echo "</div>";
?>