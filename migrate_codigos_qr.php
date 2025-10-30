<?php
require_once 'conexion.php';

echo "<h2>Migration: Agregar columnas a codigos_qr si faltan</h2>";

try {
    // Obtener columnas existentes
    $stmt = $pdo->query("DESCRIBE codigos_qr");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $needed = [];
    if (!in_array('url_qr', $cols)) $needed[] = "ADD COLUMN url_qr VARCHAR(255) DEFAULT NULL";
    if (!in_array('ruta_imagen', $cols)) $needed[] = "ADD COLUMN ruta_imagen VARCHAR(255) DEFAULT NULL";

    if (empty($needed)) {
        echo "<p>✅ La tabla <strong>codigos_qr</strong> ya tiene las columnas necesarias.</p>";
    } else {
        $sql = "ALTER TABLE codigos_qr " . implode(', ', $needed) . ";";
        echo "<p>Ejecutando: <code>" . htmlspecialchars($sql) . "</code></p>";
        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ Columnas agregadas correctamente.</p>";
    }

    echo "<h3>Estado actual de la tabla:</h3>";
    $stmt2 = $pdo->query("DESCRIBE codigos_qr");
    $cols2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    foreach ($cols2 as $c) {
        echo "<tr><td>" . htmlspecialchars($c['Field']) . "</td><td>" . htmlspecialchars($c['Type']) . "</td><td>" . htmlspecialchars($c['Null']) . "</td><td>" . htmlspecialchars($c['Key']) . "</td></tr>";
    }
    echo "</table>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>Error en la migración: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<p><a href='perfil_mascota.php?id=1&debug=1'>Ir a perfil de prueba (con debug)</a></p>";
?>