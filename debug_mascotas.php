<?php
session_start();
require_once 'conexion.php';

echo "<h2>🔍 Diagnóstico de Mascotas</h2>";

// Verificar sesión
echo "<h3>1. Información de Sesión:</h3>";
echo "Usuario logueado: " . (isset($_SESSION['usuario_id']) ? 'Sí' : 'No') . "<br>";
if (isset($_SESSION['usuario_id'])) {
    echo "Usuario ID: " . $_SESSION['usuario_id'] . "<br>";
    $usuario_id = $_SESSION['usuario_id'];
} else {
    echo "<strong style='color: red;'>❌ No hay sesión activa. Redirigiendo...</strong><br>";
    echo "<a href='iniciosesion.php'>Ir a login</a>";
    exit;
}

// Verificar usuario en BD
echo "<h3>2. Verificar Usuario en BD:</h3>";
try {
    $sql = "SELECT * FROM usuarios WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario) {
        echo "✅ Usuario encontrado: " . htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) . "<br>";
        echo "Email: " . htmlspecialchars($usuario['email']) . "<br>";
    } else {
        echo "❌ Usuario no encontrado en la BD<br>";
    }
} catch(PDOException $e) {
    echo "❌ Error al consultar usuario: " . $e->getMessage() . "<br>";
}

// Verificar estructura de tabla mascotas
echo "<h3>3. Estructura de Tabla Mascotas:</h3>";
try {
    $sql = "DESCRIBE mascotas";
    $stmt = $pdo->query($sql);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . $col['Field'] . "</td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";
} catch(PDOException $e) {
    echo "❌ Error al consultar estructura: " . $e->getMessage() . "<br>";
}

// Verificar todas las mascotas
echo "<h3>4. Todas las Mascotas en la BD:</h3>";
try {
    $sql = "SELECT * FROM mascotas ORDER BY id_mascota";
    $stmt = $pdo->query($sql);
    $todas_mascotas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($todas_mascotas)) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID Mascota</th><th>Nombre</th><th>Especie</th><th>Owner ID</th><th>Fecha</th></tr>";
        foreach ($todas_mascotas as $m) {
            $highlight = ($m['id'] == $usuario_id) ? "style='background-color: yellow;'" : "";
            echo "<tr $highlight>";
            echo "<td>" . $m['id_mascota'] . "</td>";
            echo "<td>" . htmlspecialchars($m['nombre']) . "</td>";
            echo "<td>" . htmlspecialchars($m['especie']) . "</td>";
            echo "<td>" . $m['id'] . "</td>";
            echo "<td>" . $m['fecha_registro'] . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
        echo "<em>Las filas amarillas son del usuario actual.</em><br>";
    } else {
        echo "❌ No hay mascotas en la base de datos<br>";
    }
} catch(PDOException $e) {
    echo "❌ Error al consultar mascotas: " . $e->getMessage() . "<br>";
}

// Consulta específica para el usuario
echo "<h3>5. Mascotas del Usuario Actual:</h3>";
try {
    $sql = "SELECT * FROM mascotas WHERE id = ? ORDER BY fecha_creacion DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id]);
    $mascotas_usuario = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "SQL ejecutado: <code>SELECT * FROM mascotas WHERE id = $usuario_id</code><br>";
    echo "Número de resultados: " . count($mascotas_usuario) . "<br>";
    
    if (!empty($mascotas_usuario)) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Especie</th><th>Edad</th><th>Raza</th><th>Color</th><th>Foto</th></tr>";
        foreach ($mascotas_usuario as $m) {
            echo "<tr>";
            echo "<td>" . $m['id_mascota'] . "</td>";
            echo "<td>" . htmlspecialchars($m['nombre']) . "</td>";
            echo "<td>" . htmlspecialchars($m['especie']) . "</td>";
            echo "<td>" . ($m['edad'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($m['raza'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($m['color'] ?? 'N/A') . "</td>";
            echo "<td>" . ($m['foto_url'] ? 'Sí' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "❌ No se encontraron mascotas para este usuario<br>";
    }
} catch(PDOException $e) {
    echo "❌ Error en consulta específica: " . $e->getMessage() . "<br>";
}

echo "<br><hr>";
echo "<p><a href='perfil_usuario.php'>← Volver al perfil</a></p>";
echo "<p><a href='registro_mascota.php'>➕ Registrar nueva mascota</a></p>";
?>