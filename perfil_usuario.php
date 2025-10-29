<?php
session_start();
require_once 'conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: iniciosesion.php');
    exit;
}

// Obtener datos del usuario
$usuario_id = $_SESSION['usuario_id'];
try {
    $sql = "SELECT * FROM usuarios WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        session_destroy();
        header('Location: iniciosesion.php');
        exit;
    }
} catch(PDOException $e) {
    $error_message = 'Error al cargar el perfil.';
}

// Obtener mascotas del usuario
$mascotas = [];
$debug_sql = '';
try {
    $sql_mascotas = "SELECT * FROM mascotas WHERE id = ? ORDER BY fecha_creacion DESC";
    $debug_sql = "SQL: " . $sql_mascotas . " con parametro: " . $usuario_id;
    $stmt_mascotas = $pdo->prepare($sql_mascotas);
    $stmt_mascotas->execute([$usuario_id]);
    $mascotas = $stmt_mascotas->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $mascotas = [];
    $debug_error = "Error al obtener mascotas: " . $e->getMessage();
    error_log($debug_error);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Pet Alert</title>
    <link rel="stylesheet" href="assets/css/mascota03.css" />
    <link rel="stylesheet" href="assets/css/perfil-usuario.css" />
</head>
<body>
    <section class="registro-mascota">
        <div class="formulario">
            <!-- Header con navegación -->
            <div class="perfil-header">
                <div class="perfil-title-header">
                    <h2>Mi Perfil</h2>
                </div>
                
                <!-- Avatar centrado debajo del título -->
                <div class="user-avatar-header">
                    <?php if (!empty($usuario['foto_url']) && file_exists($usuario['foto_url'])): ?>
                        <img src="<?= htmlspecialchars($usuario['foto_url']) ?>" 
                             alt="Foto de perfil" class="avatar-img-header">
                    <?php else: ?>
                        <div class="avatar-placeholder-header">
                            <span>👤</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if(isset($error_message)): ?>
                <div class="error-message">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <!-- Información del usuario -->
            <div class="user-info">
                <!-- Detalles del usuario -->
                <div class="user-details-center">
                    <h3><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></h3>
                    <p class="user-email"><?= htmlspecialchars($usuario['email']) ?></p>
                    <?php if (!empty($usuario['telefono'])): ?>
                        <p class="user-phone">📞 <?= htmlspecialchars($usuario['telefono']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($usuario['direccion'])): ?>
                        <p class="user-address">📍 <?= htmlspecialchars($usuario['direccion']) ?></p>
                    <?php endif; ?>
                    <p class="user-since">Miembro desde: <?= date('d/m/Y', strtotime($usuario['fecha_creacion'])) ?></p>
                    
                    <!-- Botón de editar perfil -->
                    <div class="edit-profile-section">
                        <button onclick="window.location.href='editar_perfil.php'" class="btn-edit-profile">
                            ✏️ Editar perfil
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sección de mascotas -->
            <div class="mascotas-section">
                <div class="section-header">
                    <h3>Mis Mascotas</h3>
                    <button onclick="window.location.href='registro_mascota.php'" class="btn-add-pet">➕ Agregar</button>
                </div>

                <!-- Debug info (temporal) -->
                <?php if(isset($_GET['debug'])): ?>
                    <div style="background: #f0f0f0; padding: 10px; margin: 10px 0; font-size: 12px; border-radius: 5px;">
                        <strong>🔍 Debug Info:</strong><br>
                        Usuario ID: <?= $usuario_id ?><br>
                        Usuario logueado: <?= isset($_SESSION['usuario_id']) ? 'Sí' : 'No' ?><br>
                        Sesión ID: <?= $_SESSION['usuario_id'] ?? 'No definido' ?><br>
                        <?= $debug_sql ?? '' ?><br>
                        <?php if(isset($debug_error)): ?>
                            <span style="color: red;">Error: <?= $debug_error ?></span><br>
                        <?php endif; ?>
                        Número de mascotas: <?= count($mascotas) ?><br>
                        <?php if(!empty($mascotas)): ?>
                            <strong>Mascotas encontradas:</strong><br>
                            <?php foreach($mascotas as $i => $m): ?>
                                <?= $i+1 ?>. <?= htmlspecialchars($m['nombre']) ?> 
                                (<?= htmlspecialchars($m['especie']) ?>)
                                - ID: <?= $m['id_mascota'] ?>
                                - Owner ID: <?= $m['id'] ?>
                                <?php if(!empty($m['foto_url'])): ?>
                                    - Foto: <?= htmlspecialchars($m['foto_url']) ?>
                                    <?php if(file_exists($m['foto_url'])): ?>
                                        ✅
                                    <?php else: ?>
                                        ❌ (archivo no existe)
                                    <?php endif; ?>
                                <?php else: ?>
                                    - Sin foto
                                <?php endif; ?>
                                <br>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span style="color: orange;">No se encontraron mascotas para este usuario.</span><br>
                        <?php endif; ?>
                        
                        <br><strong>📊 Verificación manual:</strong><br>
                        <em>Ejecutar en phpMyAdmin:</em><br>
                        <code style="background: #fff; padding: 2px;">SELECT * FROM mascotas WHERE id = <?= $usuario_id ?>;</code>
                    </div>
                <?php endif; ?>

                <?php if (empty($mascotas)): ?>
                    <div class="no-pets">
                        <p>No tienes mascotas registradas aún.</p>
                        <button onclick="window.location.href='registro_mascota.php'" class="btn_enviar">
                            Registrar mi primera mascota
                        </button>
                    </div>
                <?php else: ?>
                    <div class="pets-grid">
                        <?php foreach ($mascotas as $mascota): ?>
                            <div class="pet-card-profile">
                                <div class="pet-image-container">
                                    <?php if (!empty($mascota['foto_url']) && file_exists($mascota['foto_url'])): ?>
                                        <img src="<?= htmlspecialchars($mascota['foto_url']) ?>" 
                                             alt="<?= htmlspecialchars($mascota['nombre']) ?>" 
                                             class="pet-image-profile">
                                    <?php else: ?>
                                        <div class="pet-placeholder">
                                            <span><?= $mascota['especie'] === 'perro' ? '🐕' : ($mascota['especie'] === 'gato' ? '🐱' : '🐾') ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="pet-info">
                                    <h4><?= htmlspecialchars($mascota['nombre']) ?></h4>
                                    <p><?= ucfirst(htmlspecialchars($mascota['especie'])) ?></p>
                                    <?php if (!empty($mascota['raza'])): ?>
                                        <p class="pet-breed"><?= htmlspecialchars($mascota['raza']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($mascota['edad'])): ?>
                                        <p class="pet-age">🎂 <?= htmlspecialchars($mascota['edad']) ?> años</p>
                                    <?php endif; ?>
                                    <?php if (!empty($mascota['color'])): ?>
                                        <p class="pet-color">🎨 <?= htmlspecialchars($mascota['color']) ?></p>
                                    <?php endif; ?>
                                    <div class="pet-actions">
                                        <button onclick="window.location.href='perfil_mascota.php?id=<?= $mascota['id_mascota'] ?>'" 
                                                class="btn-view">Ver</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Botón de cerrar sesión al final -->
            <div class="logout-section">
                <button onclick="cerrarSesion()" class="btn-logout-bottom">
                    🚪 Cerrar sesión
                </button>
            </div>

        </div>
    </section>

    <!-- Barra de navegación inferior -->
    <div class="bottom-nav">
        <button class="nav-btn" onclick="window.location.href='home.php'">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
        </button>
        <button class="nav-btn" onclick="alert('Buscar')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
        </button>
        <button class="nav-btn" onclick="irAPerfil()">
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
        <button class="nav-btn" onclick="alert('Configuración')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="12" cy="12" r="3"></circle>
                <path d="M12 1v6m0 6v6M4.22 4.22l4.24 4.24m5.08 5.08l4.24 4.24M1 12h6m6 0h6m-15.78 7.78l4.24-4.24m5.08-5.08l4.24-4.24"></path>
            </svg>
        </button>
    </div>

    <script>
        function cerrarSesion() {
            if(confirm('¿Estás seguro que deseas cerrar sesión?')) {
                window.location.href = 'logout.php';
            }
        }

        function irAPerfil() {
            // Ya estamos en el perfil, pero podemos mostrar un mensaje o no hacer nada
            // O podríamos ir a una página de edición de perfil
            alert('Ya estás en tu perfil');
        }
    </script>
</body>
</html>
</div>