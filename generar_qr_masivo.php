<?php
require_once 'conexion.php';
require_once 'includes/QRGenerator.php';

echo "<h2>🔗 Generador de QR para Mascotas Existentes</h2>";

$qrGenerator = new QRGenerator();

try {
    // Obtener todas las mascotas que no tienen QR
    $sql = "SELECT m.id_mascota, m.nombre, m.especie, m.raza, u.nombre as usuario_nombre 
            FROM mascotas m 
            LEFT JOIN usuarios u ON m.id = u.id 
            LEFT JOIN codigos_qr cq ON m.id_qr = cq.id_qr 
            WHERE m.id_qr IS NULL OR cq.id_qr IS NULL
            ORDER BY m.id_mascota";
    
    $stmt = $pdo->query($sql);
    $mascotas_sin_qr = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Mascotas sin código QR: " . count($mascotas_sin_qr) . "</h3>";
    
    if (empty($mascotas_sin_qr)) {
        echo "<p style='color: green;'>✅ Todas las mascotas ya tienen códigos QR asignados.</p>";
    } else {
        echo "<div style='margin: 20px 0;'>";
        echo "<button onclick='generarTodosQR()' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>🔄 Generar QR para todas</button>";
        echo "</div>";
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Especie</th><th>Dueño</th><th>Acción</th></tr>";
        
        foreach ($mascotas_sin_qr as $mascota) {
            echo "<tr>";
            echo "<td>" . $mascota['id_mascota'] . "</td>";
            echo "<td>" . htmlspecialchars($mascota['nombre']) . "</td>";
            echo "<td>" . htmlspecialchars($mascota['especie']) . "</td>";
            echo "<td>" . htmlspecialchars($mascota['usuario_nombre'] ?? 'Sin dueño') . "</td>";
            echo "<td><button onclick='generarQRIndividual(" . $mascota['id_mascota'] . ")' style='background: #28a745; color: white; padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer;'>Generar QR</button></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Mostrar mascotas que YA tienen QR
    $sql_con_qr = "SELECT m.id_mascota, m.nombre, m.especie, cq.codigo, cq.ruta_imagen, u.nombre as usuario_nombre 
                   FROM mascotas m 
                   LEFT JOIN usuarios u ON m.id = u.id 
                   JOIN codigos_qr cq ON m.id_qr = cq.id_qr 
                   ORDER BY m.id_mascota";
    
    $stmt_con_qr = $pdo->query($sql_con_qr);
    $mascotas_con_qr = $stmt_con_qr->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Mascotas con código QR: " . count($mascotas_con_qr) . "</h3>";
    
    if (!empty($mascotas_con_qr)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Especie</th><th>Dueño</th><th>Código QR</th><th>Ver</th></tr>";
        
        foreach ($mascotas_con_qr as $mascota) {
            echo "<tr>";
            echo "<td>" . $mascota['id_mascota'] . "</td>";
            echo "<td>" . htmlspecialchars($mascota['nombre']) . "</td>";
            echo "<td>" . htmlspecialchars($mascota['especie']) . "</td>";
            echo "<td>" . htmlspecialchars($mascota['usuario_nombre'] ?? 'Sin dueño') . "</td>";
            echo "<td>" . htmlspecialchars($mascota['codigo']) . "</td>";
            echo "<td><a href='perfil_mascota.php?id=" . $mascota['id_mascota'] . "' target='_blank' style='color: #007bff; text-decoration: none;'>👁️ Ver perfil</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// Procesar generación individual si se solicita
if (isset($_POST['generar_individual'])) {
    $mascota_id = (int)$_POST['mascota_id'];
    
    $qr_result = $qrGenerator->generarQRMascota($mascota_id);
    
    if ($qr_result['success']) {
        $success = $qrGenerator->actualizarQREnBD($pdo, $mascota_id, $qr_result);
        if ($success) {
            echo "<div style='background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "✅ QR generado exitosamente para mascota ID: $mascota_id";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "❌ Error al guardar QR en base de datos para mascota ID: $mascota_id";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "❌ Error al generar QR para mascota ID: $mascota_id - " . $qr_result['error'];
        echo "</div>";
    }
    
    echo "<script>setTimeout(() => window.location.reload(), 2000);</script>";
}

// Procesar generación masiva si se solicita
if (isset($_POST['generar_todos'])) {
    $sql = "SELECT id_mascota FROM mascotas WHERE id_qr IS NULL";
    $stmt = $pdo->query($sql);
    $mascotas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $exitosos = 0;
    $errores = 0;
    
    foreach ($mascotas as $mascota_id) {
        $qr_result = $qrGenerator->generarQRMascota($mascota_id);
        if ($qr_result['success'] && $qrGenerator->actualizarQREnBD($pdo, $mascota_id, $qr_result)) {
            $exitosos++;
        } else {
            $errores++;
        }
    }
    
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "🎉 Proceso completado:<br>";
    echo "✅ QR generados exitosamente: $exitosos<br>";
    echo "❌ Errores: $errores";
    echo "</div>";
    
    echo "<script>setTimeout(() => window.location.reload(), 3000);</script>";
}
?>

<div style="margin: 20px 0;">
    <h3>Links útiles:</h3>
    <ul>
        <li><a href="perfil_usuario.php">👤 Mi Perfil</a></li>
        <li><a href="registro_mascota.php">➕ Registrar Nueva Mascota</a></li>
        <li><a href="busqueda.php">🔍 Buscar Mascotas</a></li>
    </ul>
</div>

<script>
function generarQRIndividual(mascotaId) {
    if (confirm('¿Generar código QR para esta mascota?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        
        const inputAction = document.createElement('input');
        inputAction.name = 'generar_individual';
        inputAction.value = '1';
        
        const inputId = document.createElement('input');
        inputId.name = 'mascota_id';
        inputId.value = mascotaId;
        
        form.appendChild(inputAction);
        form.appendChild(inputId);
        document.body.appendChild(form);
        form.submit();
    }
}

function generarTodosQR() {
    if (confirm('¿Generar códigos QR para TODAS las mascotas sin QR? Esto puede tardar unos momentos.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        
        const input = document.createElement('input');
        input.name = 'generar_todos';
        input.value = '1';
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>