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

    if (isset($_POST['email'])){
        $email=trim($_POST['email']);
        if (empty($email)) {
            $msg_email = 'El campo email es obligatorio.';
            $error = true;
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $msg_email = 'El email no es válido.';
            $error = true;
        }
    }
    if (isset($_POST['telefono'])){
        $telefono=trim($_POST['telefono']);
        if (empty($telefono)) {
            $msg_telefono = 'El campo teléfono es obligatorio.';
            $error = true;
        } elseif (!preg_match('/^[0-9]{10}$/', $telefono)) {
            //El primer parámetro es la expresión regular. El segundo parámetro es la cadena a evaluar.
            //Devuelve 1 si hay coincidencia, 0 si no, y FALSE si ocurre un error.
            $msg_telefono = 'El teléfono debe tener 10 dígitos.';
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
        $sql = "INSERT INTO usuarios (nombre, telefono, contraseña) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $telefono, $contraseña]);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="mascota03.css" />
</head>
<body>
    <section class="registro-mascota">
        <form class="formulario" action="" method="post">
            <div class="logo-container">
                <img src="img/logo.png" alt="Logo" class="logo" />
            </div>
            <h2>Registro de Usuario</h2>
            
            <div>
                <input type="text" name="nombre" placeholder="Ingresar nombre" value="<?=$nombre?>">
                <output><?=$msg_nombre?></output>
            </div>
            <div>
                <input type="email" name="email" placeholder="Ingresar email" value="<?=$email?>">
                <output><?=$msg_email?></output>
            </div>
            <div>
                <input type="text" name="telefono" placeholder="Ingresar teléfono" value="<?=$telefono?>">
                <output><?=$msg_telefono?></output>
            </div>
            <div>
                <input type="password" name="contraseña" placeholder="Ingresar contraseña" value="<?=$contraseña?>">
                <output><?=$msg_contraseña?></output>
            </div>

            <button type="submit" name="env_btn" class="btn_enviar">Registrarse</button>
            
            <div class="login-link">
                <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
            </div>
        </form>
    </section>
</body>
</html>