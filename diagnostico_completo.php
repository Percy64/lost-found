<?php
session_start();
require_once 'conexion.php';

echo "<h2>🔍 Diagnóstico Completo del Problema de Mascotas</h2>";

echo "<h3>1. Información de Sesión:</h3>";
if (isset($_SESSION['usuario_id'])) {
    $usuario_id = $_SESSION['usuario_id'];
    echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
    echo "<strong>✅ Sesión activa</strong><br>";
    echo "Usuario ID en sesión: <strong>$usuario_id</strong><br>";
    echo "Nombre en sesión: " . ($_SESSION['usuario_nombre'] ?? 'No definido') . "<br>";
    echo "Email en sesión: " . ($_SESSION['usuario_email'] ?? 'No definido');
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; color: #721c24;'>";
    echo "❌ No hay sesión activa";
    echo "</div>";
    exit;
}

echo "<h3>2. Verificar Usuario en Base de Datos:</h3>";
try {
    $sql = "SELECT * FROM usuarios WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Usuario encontrado en BD</strong><br>";
        echo "ID: " . $usuario['id'] . "<br>";
        echo "Nombre: " . htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) . "<br>";
        echo "Email: " . htmlspecialchars($usuario['email']);
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; color: #721c24;'>";
        echo "❌ Usuario NO encontrado en BD con ID: $usuario_id";
        echo "</div>";
    }
} catch(PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; color: #721c24;'>";
    echo "❌ Error al consultar usuario: " . $e->getMessage();
    echo "</div>";
}

echo "<h3>3. Buscar Mascotas del Usuario:</h3>";
try {
    // Consulta exacta que usa perfil_usuario.php
    $sql_mascotas = "SELECT * FROM mascotas WHERE id = ? ORDER BY fecha_registro DESC";
    echo "<strong>SQL ejecutado:</strong> <code>$sql_mascotas</code><br>";
    echo "<strong>Parámetro:</strong> $usuario_id<br><br>";
    
    $stmt_mascotas = $pdo->prepare($sql_mascotas);
    $stmt_mascotas->execute([$usuario_id]);
    $mascotas = $stmt_mascotas->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<strong>Resultado:</strong> " . count($mascotas) . " mascotas encontradas<br><br>";
    
    if (!empty($mascotas)) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Mascotas encontradas:</strong><br>";
        foreach ($mascotas as $mascota) {
            echo "- ID: " . $mascota['id_mascota'] . " | Nombre: " . htmlspecialchars($mascota['nombre']) . 
                 " | Especie: " . htmlspecialchars($mascota['especie']) . 
                 " | Usuario FK: " . $mascota['id'] . "<br>";
        }
        echo "</div>";
    } else {
        echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px; color: #856404;'>";
        echo "⚠️ No se encontraron mascotas para el usuario ID: $usuario_id";
        echo "</div>";
    }
    
} catch(PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; color: #721c24;'>";
    echo "❌ Error al consultar mascotas: " . $e->getMessage();
    echo "</div>";
}

echo "<h3>4. Verificar TODAS las mascotas en la tabla:</h3>";
try {
    $sql_todas = "SELECT id_mascota, nombre, especie, id as usuario_fk FROM mascotas ORDER BY id";
    $stmt_todas = $pdo->query($sql_todas);
    $todas_mascotas = $stmt_todas->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID Mascota</th><th>Nombre</th><th>Especie</th><th>Usuario FK</th><th>¿Es tuya?</th></tr>";
    foreach ($todas_mascotas as $mascota) {
        $es_tuya = ($mascota['usuario_fk'] == $usuario_id) ? "✅ SÍ" : "❌ No";
        $color = ($mascota['usuario_fk'] == $usuario_id) ? "background: #d4edda;" : "";
        echo "<tr style='$color'>";
        echo "<td>" . $mascota['id_mascota'] . "</td>";
        echo "<td>" . htmlspecialchars($mascota['nombre']) . "</td>";
        echo "<td>" . htmlspecialchars($mascota['especie']) . "</td>";
        echo "<td>" . $mascota['usuario_fk'] . "</td>";
        echo "<td>" . $es_tuya . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch(PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; color: #721c24;'>";
    echo "❌ Error al consultar todas las mascotas: " . $e->getMessage();
    echo "</div>";
}

echo "<h3>5. Soluciones Posibles:</h3>";
echo "<ul>";
echo "<li><strong>Si no tienes mascotas:</strong> <a href='registro_mascota.php' style='color: #007bff;'>Registrar una mascota</a></li>";
echo "<li><strong>Si eres Emanuel (ID 1):</strong> Deberías ver la mascota 'Firulais'</li>";
echo "<li><strong>Para probar con otro usuario:</strong> <a href='reset_password.php' style='color: #007bff;'>Resetear contraseñas</a></li>";
echo "<li><strong>Volver al perfil:</strong> <a href='perfil_usuario.php' style='color: #007bff;'>Mi Perfil</a></li>";
echo "</ul>";
?>