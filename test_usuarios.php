<?php
require_once 'conexion.php';

echo "<h2>🔍 Test de Login con Usuarios de Ejemplo</h2>";

// Datos de usuarios de ejemplo
$usuarios_test = [
    1 => ['email' => 'emanuelmerlo15@gmail.com', 'password' => 'password_original'],
    2 => ['email' => 'juan.perez@example.com', 'password' => 'test123'],
    3 => ['email' => 'maria.gomez@example.com', 'password' => 'test123'],
    4 => ['email' => 'carlos.lopez@example.com', 'password' => 'test123'],
];

echo "<h3>Usuarios disponibles para probar:</h3>";
echo "<ul>";
foreach ($usuarios_test as $id => $datos) {
    echo "<li><strong>ID $id:</strong> {$datos['email']} (password: {$datos['password']})</li>";
}
echo "</ul>";

// Verificar qué mascotas tiene cada usuario
echo "<h3>Mascotas por usuario:</h3>";
try {
    $sql = "SELECT u.id, u.nombre, u.apellido, u.email, 
                   COUNT(m.id_mascota) as num_mascotas,
                   GROUP_CONCAT(m.nombre SEPARATOR ', ') as nombres_mascotas
            FROM usuarios u
            LEFT JOIN mascotas m ON u.id = m.id
            GROUP BY u.id
            ORDER BY u.id";
    $stmt = $pdo->query($sql);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>ID</th><th>Usuario</th><th>Email</th><th># Mascotas</th><th>Nombres Mascotas</th></tr>";
    foreach ($resultados as $user) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['nombre'] . ' ' . $user['apellido']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . $user['num_mascotas'] . "</td>";
        echo "<td>" . htmlspecialchars($user['nombres_mascotas'] ?: 'Sin mascotas') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<h3>Links para probar:</h3>";
echo "<ul>";
echo "<li><a href='iniciosesion.php'>Ir a login</a></li>";
echo "<li><a href='perfil_usuario.php?debug=1'>Ver perfil con debug</a></li>";
echo "<li><a href='debug_relaciones.php'>Ver debug de relaciones</a></li>";
echo "</ul>";
?>