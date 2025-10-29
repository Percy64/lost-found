<?php
session_start();
require_once 'conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: iniciosesion.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$error = false;
$success_message = '';
$msg_nombre = '';
$msg_apellido = '';
$msg_email = '';
$msg_telefono = '';
$msg_direccion = '';
$msg_foto = '';

// Obtener datos actuales del usuario
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
    $error_message = 'Error al cargar los datos del usuario.';
}

// Procesar el formulario si se envía
if (isset($_POST['btn_actualizar'])) {
    
    // Validar nombre
    if (isset($_POST['nombre'])) {
        $nombre = trim($_POST['nombre']);
        if (empty($nombre)) {
            $msg_nombre = 'El nombre es obligatorio.';
            $error = true;
        } elseif (strlen($nombre) < 2) {
            $msg_nombre = 'El nombre debe tener al menos 2 caracteres.';
            $error = true;
        }
    } else {
        $msg_nombre = 'El nombre es obligatorio.';
        $error = true;
    }

    // Validar apellido
    if (isset($_POST['apellido'])) {
        $apellido = trim($_POST['apellido']);
        if (empty($apellido)) {
            $msg_apellido = 'El apellido es obligatorio.';
            $error = true;
        } elseif (strlen($apellido) < 2) {
            $msg_apellido = 'El apellido debe tener al menos 2 caracteres.';
            $error = true;
        }
    } else {
        $msg_apellido = 'El apellido es obligatorio.';
        $error = true;
    }

    // Validar email
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
        if (empty($email)) {
            $msg_email = 'El email es obligatorio.';
            $error = true;
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $msg_email = 'El formato del email no es válido.';
            $error = true;
        } else {
            // Verificar si el email ya existe (excluyendo el usuario actual)
            try {
                $sql_check = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
                $stmt_check = $pdo->prepare($sql_check);
                $stmt_check->execute([$email, $usuario_id]);
                if ($stmt_check->fetch()) {
                    $msg_email = 'Este email ya está registrado por otro usuario.';
                    $error = true;
                }
            } catch(PDOException $e) {
                $msg_email = 'Error al verificar el email.';
                $error = true;
            }
        }
    } else {
        $msg_email = 'El email es obligatorio.';
        $error = true;
    }

    // Validar teléfono (opcional)
    $telefono = '';
    if (isset($_POST['telefono']) && !empty(trim($_POST['telefono']))) {
        $telefono = trim($_POST['telefono']);
        if (strlen($telefono) < 10) {
            $msg_telefono = 'El teléfono debe tener al menos 10 dígitos.';
            $error = true;
        }
    }

    // Validar dirección (opcional)
    $direccion = '';
    if (isset($_POST['direccion']) && !empty(trim($_POST['direccion']))) {
        $direccion = trim($_POST['direccion']);
    }

    // Procesar foto (opcional)
    $foto_path = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = $_FILES['foto']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $msg_foto = 'Solo se permiten archivos JPG, PNG o GIF.';
            $error = true;
        } elseif ($_FILES['foto']['size'] > 5000000) { // 5MB
            $msg_foto = 'El archivo es demasiado grande. Máximo 5MB.';
            $error = true;
        } else {
            // Crear directorio de uploads si no existe
            $upload_dir = 'assets/images/usuarios/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generar nombre único para la imagen
            $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $filename = 'usuario_' . $usuario_id . '_' . time() . '.' . $extension;
            $foto_path = $upload_dir . $filename;
            
            // Mover el archivo subido
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $foto_path)) {
                // Eliminar la foto anterior si existe
                if (!empty($usuario['foto_url']) && file_exists($usuario['foto_url'])) {
                    unlink($usuario['foto_url']);
                }
            } else {
                $msg_foto = 'Error al guardar la imagen.';
                $error = true;
                $foto_path = null;
            }
        }
    }

    // Si no hay errores, actualizar en la base de datos
    if (!$error) {
        try {
            if ($foto_path !== null) {
                // Actualizar con nueva foto
                $sql_update = "UPDATE usuarios SET nombre = ?, apellido = ?, email = ?, telefono = ?, direccion = ?, foto_url = ? WHERE id = ?";
                $stmt_update = $pdo->prepare($sql_update);
                $result = $stmt_update->execute([$nombre, $apellido, $email, $telefono, $direccion, $foto_path, $usuario_id]);
            } else {
                // Actualizar sin cambiar la foto
                $sql_update = "UPDATE usuarios SET nombre = ?, apellido = ?, email = ?, telefono = ?, direccion = ? WHERE id = ?";
                $stmt_update = $pdo->prepare($sql_update);
                $result = $stmt_update->execute([$nombre, $apellido, $email, $telefono, $direccion, $usuario_id]);
            }

            if ($result) {
                $success_message = 'Perfil actualizado correctamente.';
                // Recargar los datos del usuario
                $sql = "SELECT * FROM usuarios WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$usuario_id]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error_message = 'Error al actualizar el perfil.';
            }
        } catch(PDOException $e) {
            $error_message = 'Error en la base de datos: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - Pet Alert</title>
    <link rel="stylesheet" href="assets/css/mascota03.css" />
    <link rel="stylesheet" href="assets/css/perfil-usuario.css" />
</head>
<body>
    <section class="registro-mascota">
        <div class="formulario">
            <!-- Header -->
            <div class="perfil-header">
                <div class="perfil-title-header">
                    <button onclick="window.location.href='perfil_usuario.php'" class="btn-back-arrow">
                        ← 
                    </button>
                    <h2>Editar Perfil</h2>
                    <div></div> <!-- Spacer para centrar el título -->
                </div>
                
                <!-- Avatar actual -->
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

            <!-- Mensajes -->
            <?php if(!empty($success_message)): ?>
                <div class="success-message">
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>

            <?php if(isset($error_message)): ?>
                <div class="error-message">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <!-- Formulario de edición -->
            <form method="POST" enctype="multipart/form-data" class="edit-form">
                
                <!-- Nombre -->
                <div class="campo">
                    <label for="nombre">Nombre *</label>
                    <input type="text" 
                           id="nombre" 
                           name="nombre" 
                           value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>" 
                           required>
                    <?php if(!empty($msg_nombre)): ?>
                        <div class="error-text"><?= htmlspecialchars($msg_nombre) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Apellido -->
                <div class="campo">
                    <label for="apellido">Apellido *</label>
                    <input type="text" 
                           id="apellido" 
                           name="apellido" 
                           value="<?= htmlspecialchars($usuario['apellido'] ?? '') ?>" 
                           required>
                    <?php if(!empty($msg_apellido)): ?>
                        <div class="error-text"><?= htmlspecialchars($msg_apellido) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Email -->
                <div class="campo">
                    <label for="email">Email *</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="<?= htmlspecialchars($usuario['email'] ?? '') ?>" 
                           required>
                    <?php if(!empty($msg_email)): ?>
                        <div class="error-text"><?= htmlspecialchars($msg_email) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Teléfono -->
                <div class="campo">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" 
                           id="telefono" 
                           name="telefono" 
                           value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>" 
                           placeholder="Opcional">
                    <?php if(!empty($msg_telefono)): ?>
                        <div class="error-text"><?= htmlspecialchars($msg_telefono) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Dirección -->
                <div class="campo">
                    <label for="direccion">Dirección</label>
                    <input type="text" 
                           id="direccion" 
                           name="direccion" 
                           value="<?= htmlspecialchars($usuario['direccion'] ?? '') ?>" 
                           placeholder="Opcional">
                </div>

                <!-- Foto -->
                <div class="campo">
                    <label for="foto">Cambiar foto de perfil</label>
                    <input type="file" 
                           id="foto" 
                           name="foto" 
                           accept="image/*"
                           onchange="previewImage(this)">
                    <div class="file-info">Solo JPG, PNG o GIF. Máximo 5MB.</div>
                    <?php if(!empty($msg_foto)): ?>
                        <div class="error-text"><?= htmlspecialchars($msg_foto) ?></div>
                    <?php endif; ?>
                    
                    <!-- Preview de la nueva imagen -->
                    <div id="imagePreview" style="display: none;">
                        <img id="preview" alt="Vista previa" style="max-width: 100px; max-height: 100px; border-radius: 15px; margin-top: 10px; border: 2px solid #c9a7f5; object-fit: cover;">
                    </div>
                </div>

                <!-- Botones -->
                <div class="form-buttons">
                    <button type="submit" name="btn_actualizar" class="btn_enviar">
                        Actualizar perfil
                    </button>
                    
                    <button type="button" onclick="window.location.href='perfil_usuario.php'" class="btn-cancel">
                        Cancelar
                    </button>
                </div>
            </form>

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
        <button class="nav-btn" onclick="window.location.href='perfil_usuario.php'">
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
        function previewImage(input) {
            const preview = document.getElementById('preview');
            const previewContainer = document.getElementById('imagePreview');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewContainer.style.display = 'block';
                }
                
                reader.readAsDataURL(input.files[0]);
            } else {
                previewContainer.style.display = 'none';
            }
        }
    </script>
</body>
</html>