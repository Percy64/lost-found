<?php
require_once 'conexion.php';
$nombre = '';
$apellido = '';
$telefono = '';
$email = '';
$contraseña = '';
$contraseña2 = '';

$msg_nombre = '';
$msg_apellido = '';
$msg_telefono = '';
$msg_email = '';    
$msg_contraseña = '';
$msg_contraseña2 = '';

$error = false;


if(isset($_POST['env_btn'])){

    if (isset($_POST['nombre'])){
        $nombre=trim($_POST['nombre']);
        if (empty($nombre)) {
            $msg_nombre = 'El campo nombre es obligatorio.';
            $error = true;
        } elseif (strlen($nombre) < 3) {
            $msg_nombre = 'El nombre debe tener al menos 3 caracteres.';
            $error = true;    
        }
    }

    if (isset($_POST['apellido'])){
        $apellido=trim($_POST['apellido']);
        if (empty($apellido)) {
            $msg_apellido = 'El campo apellido es obligatorio.';
            $error = true;
        } elseif (strlen($apellido) < 2) {
            $msg_apellido = 'El apellido debe tener al menos 2 caracteres.';
            $error = true;    
        }
    }

    if (isset($_POST['email'])){
        $email=trim($_POST['email']);
        if (empty($email)) {
            $msg_email = 'El campo email es obligatorio.';
            $error = true;
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $msg_email = 'El email no es válido.';
            $error = true;
        } else {
            // Verificar si el email ya existe
            $sql_check = "SELECT id FROM usuarios WHERE email = ?";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute([$email]);
            if($stmt_check->fetch()){
                $msg_email = 'Este email ya está registrado.';
                $error = true;
            }
        }
    }
    if (isset($_POST['telefono'])){
        $telefono=trim($_POST['telefono']);
        if (empty($telefono)) {
            $msg_telefono = 'El campo teléfono es obligatorio.';
            $error = true;
        } elseif (!preg_match('/^[0-9+\-\s]{8,15}$/', $telefono)) {
            $msg_telefono = 'El teléfono debe tener entre 8 y 15 dígitos.';
            $error = true;
        }
    }

    if (isset($_POST['contraseña'])){
        $contraseña=trim($_POST['contraseña']);
        if (empty($contraseña)) {
            $msg_contraseña = 'El campo contraseña es obligatorio.';
            $error = true;
        } elseif (strlen($contraseña) < 6) {
            $msg_contraseña = 'La contraseña debe tener al menos 6 caracteres.';
            $error = true;
        }

}

    if (!$error) {
        try {
            $contraseña_hashed = password_hash($contraseña, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (nombre, apellido, telefono, email, password) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $apellido, $telefono, $email, $contraseña_hashed]);
            
            // Mensaje de éxito
            $success_message = "Usuario registrado exitosamente.";
            // Limpiar variables
            $nombre = $apellido = $telefono = $email = '';
        } catch(PDOException $e) {
            $msg_general = 'Error al registrar el usuario. Inténtelo nuevamente.';
            $error = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="assets/css/mascota03.css" />
    <link rel="stylesheet" href="assets/css/registro-usuario.css" />
</head>
<body>
    <section class="registro-mascota">
        <form class="formulario" action="" method="post">
            <div class="logo-container">
                <img src="assets/images/logo.png" alt="Logo" class="logo" />
            </div>
            <h2>Registro de Usuario</h2>
            
            <?php if(isset($success_message)): ?>
                <div class="success-message">
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>
            
            <div>
                <input type="text" name="nombre" placeholder="Ingresar nombre" value="<?= htmlspecialchars($nombre) ?>">
                <output><?=$msg_nombre?></output>
            </div>
            <div>
                <input type="text" name="apellido" placeholder="Ingresar apellido" value="<?= htmlspecialchars($apellido) ?>">
                <output><?=$msg_apellido?></output>
            </div>
            <div>
                <input type="email" name="email" placeholder="Ingresar email" value="<?= htmlspecialchars($email) ?>">
                <output><?=$msg_email?></output>
            </div>
            <div>
                <input type="text" name="telefono" placeholder="Ingresar teléfono" value="<?= htmlspecialchars($telefono) ?>">
                <output><?=$msg_telefono?></output>
            </div>
            <div>
                <input type="password" name="contraseña" placeholder="Ingresar contraseña">
                <output><?=$msg_contraseña?></output>
            </div>

            <button type="submit" name="env_btn" class="btn_enviar">Registrarse</button>
            
            <div class="login-link">
                <p>¿Ya tienes cuenta? <a href="iniciosesion.php">Inicia sesión</a></p>
            </div>
        </form>
    </section>
</body>
</html>