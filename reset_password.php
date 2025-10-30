<?php
require_once 'conexion.php';

echo "<h2>🔧 Resetear Contraseña de Usuario de Prueba</h2>";

// Nueva contraseña para el usuario de prueba
$nueva_password = 'test123';
$password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);

try {
    // Actualizar la contraseña del usuario ID 1 (Emanuel)
    $sql = "UPDATE usuarios SET password = ? WHERE id = 1";
    $stmt = $pdo->prepare($sql);
    $resultado = $stmt->execute([$password_hash]);
    
    if ($resultado) {
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>✅ Contraseña actualizada exitosamente!</strong><br>";
        echo "Usuario: emanuelmerlo15@gmail.com<br>";
        echo "Nueva contraseña: <strong>test123</strong>";
        echo "</div>";
        
        // Verificar las mascotas de este usuario
        $sql_mascotas = "SELECT * FROM mascotas WHERE id = 1";
        $stmt_mascotas = $pdo->prepare($sql_mascotas);
        $stmt_mascotas->execute();
        $mascotas = $stmt_mascotas->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Mascotas asociadas a este usuario:</h3>";
        if (!empty($mascotas)) {
            echo "<ul>";
            foreach ($mascotas as $mascota) {
                echo "<li><strong>" . htmlspecialchars($mascota['nombre']) . "</strong> - " . 
                     htmlspecialchars($mascota['especie']) . 
                     (isset($mascota['raza']) ? " (" . htmlspecialchars($mascota['raza']) . ")" : "") . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: orange;'>⚠️ No se encontraron mascotas para este usuario.</p>";
        }
        
        echo "<div style='margin: 20px 0;'>";
        echo "<a href='iniciosesion.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔑 Ir a Login</a>";
        echo "<a href='perfil_usuario.php?debug=1' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>👤 Ver Perfil (con debug)</a>";
        echo "</div>";
        
    } else {
        echo "<p style='color: red;'>❌ Error al actualizar la contraseña</p>";
    }
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<h3>📋 Instrucciones:</h3>
<ol>
    <li>Haz clic en "Ir a Login"</li>
    <li>Usa las credenciales:
        <ul>
            <li><strong>Email:</strong> emanuelmerlo15@gmail.com</li>
            <li><strong>Contraseña:</strong> test123</li>
        </ul>
    </li>
    <li>Una vez logueado, ve a tu perfil para ver las mascotas</li>
</ol>