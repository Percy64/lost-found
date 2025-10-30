<?php
require_once 'conexion.php';

echo "<h2>🔍 Debug: Verificar relación Usuario-Mascotas</h2>";

try {
    // 1. Mostrar todos los usuarios
    echo "<h3>1. Usuarios en la base de datos:</h3>";
    $sql = "SELECT id, nombre, apellido, email FROM usuarios";
    $stmt = $pdo->query($sql);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>Email</th></tr>";
    foreach ($usuarios as $usuario) {
        echo "<tr>";
        echo "<td>" . $usuario['id'] . "</td>";
        echo "<td>" . htmlspecialchars($usuario['nombre']) . "</td>";
        echo "<td>" . htmlspecialchars($usuario['apellido']) . "</td>";
        echo "<td>" . htmlspecialchars($usuario['email']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // 2. Mostrar todas las mascotas
    echo "<h3>2. Mascotas en la base de datos:</h3>";
    $sql = "SELECT id_mascota, nombre, especie, id as usuario_id FROM mascotas";
    $stmt = $pdo->query($sql);
    $mascotas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>ID Mascota</th><th>Nombre</th><th>Especie</th><th>Usuario ID (FK)</th></tr>";
    foreach ($mascotas as $mascota) {
        echo "<tr>";
        echo "<td>" . $mascota['id_mascota'] . "</td>";
        echo "<td>" . htmlspecialchars($mascota['nombre']) . "</td>";
        echo "<td>" . htmlspecialchars($mascota['especie']) . "</td>";
        echo "<td>" . $mascota['usuario_id'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // 3. Mostrar relación JOIN
    echo "<h3>3. Relación completa (Usuario + Mascotas):</h3>";
    $sql = "SELECT u.id as usuario_id, u.nombre as usuario_nombre, u.apellido, 
                   m.id_mascota, m.nombre as mascota_nombre, m.especie 
            FROM usuarios u 
            LEFT JOIN mascotas m ON u.id = m.id 
            ORDER BY u.id";
    $stmt = $pdo->query($sql);
    $relaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Usuario ID</th><th>Usuario</th><th>Mascota ID</th><th>Mascota</th><th>Especie</th></tr>";
    foreach ($relaciones as $rel) {
        echo "<tr>";
        echo "<td>" . $rel['usuario_id'] . "</td>";
        echo "<td>" . htmlspecialchars($rel['usuario_nombre'] . ' ' . $rel['apellido']) . "</td>";
        echo "<td>" . ($rel['id_mascota'] ?: 'Sin mascotas') . "</td>";
        echo "<td>" . htmlspecialchars($rel['mascota_nombre'] ?: 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($rel['especie'] ?: 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // 4. Verificar sesión actual
    session_start();
    echo "<h3>4. Información de sesión:</h3>";
    if (isset($_SESSION['usuario_id'])) {
        $usuario_actual = $_SESSION['usuario_id'];
        echo "<p><strong>Usuario logueado ID:</strong> $usuario_actual</p>";
        
        // Buscar mascotas del usuario actual
        $sql = "SELECT * FROM mascotas WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_actual]);
        $mascotas_usuario = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p><strong>Mascotas del usuario actual:</strong> " . count($mascotas_usuario) . "</p>";
        if (!empty($mascotas_usuario)) {
            echo "<ul>";
            foreach ($mascotas_usuario as $mascota) {
                echo "<li>" . htmlspecialchars($mascota['nombre']) . " (" . htmlspecialchars($mascota['especie']) . ")</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<p style='color: red;'>No hay sesión activa</p>";
    }

} catch(PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>