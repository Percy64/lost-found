

<?php
require_once 'conexion.php';

// Inicializar variables
$email = '';
$password = '';

// Inicializar mensajes de error
$msg_email = '';
$msg_password = '';
$msg_general = '';

$error = false;

if(isset($_POST['login_btn'])){
    
    // Validar email
    if(isset($_POST['email'])){
        $email = trim($_POST['email']);
        if(empty($email)){
            $msg_email = 'El campo email es obligatorio.';
            $error = true;
        } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $msg_email = 'El email no es válido.';
            $error = true;
        }
    } else {
        $msg_email = 'El campo email es obligatorio.';
        $error = true;
    }

    // Validar contraseña
    if(isset($_POST['password'])){
        $password = trim($_POST['password']);
        if(empty($password)){
            $msg_password = 'El campo contraseña es obligatorio.';
            $error = true;
        }
    } else {
        $msg_password = 'El campo contraseña es obligatorio.';
        $error = true;
    }

    // Si no hay errores, verificar credenciales
    if(!$error){
        try {
            $sql = "SELECT id, nombre, apellido, email, password FROM usuarios WHERE email = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($usuario && password_verify($password, $usuario['password'])){
                // Login exitoso - aquí podrías iniciar sesión
                session_start();
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_email'] = $usuario['email'];
                
                // Redirigir a home
                header('Location: home.php');
                exit;
            } else {
                $msg_general = 'Email o contraseña incorrectos.';
                $error = true;
            }
        } catch(PDOException $e) {
            $msg_general = 'Error al iniciar sesión. Inténtelo nuevamente.';
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
    <title>Pet Alert - Iniciar Sesión</title>
    <link rel="stylesheet" href="assets/css/mascota03.css" />
    <link rel="stylesheet" href="assets/css/iniciosesion.css">
</head>
<body>
    <section class="registro-mascota">
        <form class="formulario" action="" method="post">
            <div class="logo-container">
                <img src="assets/images/logo.png" alt="Logo" class="logo">
            </div>

            <h2>Iniciar sesión</h2>
            <p class="page-subtitle">Por favor ingrese sus datos para continuar.</p>

            <?php if(!empty($msg_general)): ?>
                <div class="error-message">
                    <?= htmlspecialchars($msg_general) ?>
                </div>
            <?php endif; ?>

            <div>
                <input type="email" name="email" placeholder="Ingrese su email" value="<?= htmlspecialchars($email) ?>" required>
                <output><?= $msg_email ?></output>
            </div>

            <div>
                <input type="password" name="password" placeholder="Ingrese su contraseña" required>
                <output><?= $msg_password ?></output>
            </div>

            <button type="submit" name="login_btn" class="btn_enviar">Iniciar sesión</button>
            
            <div class="register-link">
                <p>¿No tienes cuenta? <a href="registro_usuario.php">Regístrate aquí</a></p>
            </div>
        </form>
    </section>
</body>
</html>
