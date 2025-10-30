<?php
session_start();
require 'conexion.php';
require_once 'includes/QRGenerator.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: iniciosesion.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

$nombre='';
$especie='';
$edad='';
$raza='';
$color='';
////////////////////////////
$msg_nombre='';
$msg_especie='';
$msg_edad='';
$msg_raza='';
$msg_color='';
////////////////////////////

$error=false;

if(isset($_POST['btn_enviar'])){

    if(isset($_POST['nombre'])){
        $nombre=trim($_POST['nombre']);
        if(empty($nombre)){
            $msg_nombre='No puede estar vacio';
            $error=true;
        }elseif(strlen($nombre) < 2 || strlen($nombre) > 50){
            $msg_nombre='Debe tener entre 2 y 50 caracteres';
            $error=true;
        }
    }else{
        $msg_nombre='Ingrese nombre';
        $error=true;
    }

    // VALIDACIÓN DE ESPECIE
    if(isset($_POST['especie'])){
        $especie=trim($_POST['especie']);
        if(empty($especie)){
            $msg_especie='Seleccione especie';
            $error=true;
        }
    }else{
        $msg_especie='Seleccione especie';
        $error=true;
    }

    // VALIDACIÓN DE EDAD
    if(isset($_POST['edad'])){
        $edad=trim($_POST['edad']);
        if(empty($edad)){
            $msg_edad='Ingrese la edad';
            $error=true;
        }elseif(!is_numeric($edad) || $edad < 0 || $edad > 30){
            $msg_edad='La edad debe ser un número entre 0 y 30';
            $error=true;
        }
    }else{
        $msg_edad='Ingrese la edad';
        $error=true;
    }

    // VALIDACIÓN DE RAZA (opcional)
    if(isset($_POST['raza'])){
        $raza=trim($_POST['raza']);
        if(strlen($raza) > 100){
            $msg_raza='La raza no puede tener más de 100 caracteres';
            $error=true;
        }
    }

    // VALIDACIÓN DE COLOR (opcional)
    if(isset($_POST['color'])){
        $color=trim($_POST['color']);
        if(strlen($color) > 50){
            $msg_color='El color no puede tener más de 50 caracteres';
            $error=true;
        }
    }

    if (!$error) {
        try {
            // Procesar la imagen
            $foto_path = null;
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                $max_size = 5 * 1024 * 1024; // 5MB
                
                if(!in_array($_FILES['foto']['type'], $allowed_types)){
                    $msg_general = 'Formato de imagen no permitido. Use JPG, PNG, GIF o WEBP.';
                    $error = true;
                } elseif($_FILES['foto']['size'] > $max_size){
                    $msg_general = 'La imagen es demasiado grande. Máximo 5MB.';
                    $error = true;
                } else {
                    // Crear directorio si no existe
                    $upload_dir = 'assets/images/mascotas/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    // Generar nombre único
                    $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                    $filename = 'mascota_' . $usuario_id . '_' . time() . '.' . $extension;
                    $foto_path = $upload_dir . $filename;
                    
                    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $foto_path)) {
                        $msg_general = 'Error al guardar la imagen.';
                        $error = true;
                        $foto_path = null;
                    }
                }
            }

            if (!$error) {
                // Insertar en la base de datos con los campos correctos
                $sql = "INSERT INTO mascotas (nombre, especie, edad, raza, color, sexo, id, foto_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute([
                    $nombre, 
                    $especie, 
                    (int)$edad, 
                    !empty($raza) ? $raza : null, 
                    !empty($color) ? $color : null, 
                    $_POST['sexo'], 
                    $usuario_id, 
                    $foto_path
                ]);
                
                if ($result) {
                    $mascota_id = $pdo->lastInsertId();
                    
                    // 🔥 GENERAR CÓDIGO QR AUTOMÁTICAMENTE
                    $qrGenerator = new QRGenerator();
                    $qr_result = $qrGenerator->generarQRMascota($mascota_id, [
                        'nombre' => $nombre,
                        'especie' => $especie
                    ]);
                    
                    // Actualizar el QR en la base de datos
                    if ($qr_result['success']) {
                        $qrGenerator->actualizarQREnBD($pdo, $mascota_id, $qr_result);
                        $success_message = "Mascota registrada exitosamente con código QR generado.";
                    } else {
                        $success_message = "Mascota registrada exitosamente. (Error al generar QR: " . $qr_result['error'] . ")";
                    }
                    
                    // Limpiar variables
                    $nombre = $especie = $edad = $raza = $color = '';
                } else {
                    $msg_general = 'Error al registrar la mascota en la base de datos.';
                }
            }
        } catch(PDOException $e) {
            $msg_general = 'Error al registrar la mascota: ' . $e->getMessage();
            $error = true;
        }
    }
}


?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registro de Mascota</title>
  <link rel="stylesheet" href="assets/css/mascota03.css" />
  <link rel="stylesheet" href="assets/css/registro-mascota-addon.css" />
</head>
<body>
  <section class="registro-mascota">
    <form class="formulario" method="post" action="" enctype="multipart/form-data">
      <!-- Header con flecha de regreso -->
      <div class="form-header">
        <button type="button" onclick="window.location.href='perfil_usuario.php'" class="btn-back-arrow">
          ← 
        </button>
        <h2>INGRESAR MASCOTA</h2>
        <div></div> <!-- Spacer -->
      </div>

      <?php if(isset($success_message)): ?>
        <div class="success-message">
          <?= htmlspecialchars($success_message) ?>
        </div>
      <?php endif; ?>

      <?php if(isset($msg_general) && !empty($msg_general)): ?>
        <div class="error-message">
          <?= htmlspecialchars($msg_general) ?>
        </div>
      <?php endif; ?>

      <div class="foto-mascota">
      <!-- Input oculto para cargar imagen -->
      <input type="file" id="input-foto" name="foto" accept="image/*" class="input-foto-hidden" />

      <!-- Botón que abre el selector -->
      <button type="button" class="btn-foto" id="btn-foto" onclick="document.getElementById('input-foto').click()">
        📷 Seleccionar foto
      </button>

      <!-- Vista previa -->
      <img id="preview-foto" src="" alt="Vista previa" class="preview-foto" />
      </div>

      <input type="text" name="nombre" placeholder="Nombre" value="<?=$nombre?>" />
      <output class="msg_nombre"><?=$msg_nombre?></output>

      <select name="especie" required>
      <option value="" disabled selected>Especie</option>
      <option value="perro" <?= $especie == 'perro' ? 'selected' : '' ?>>Perro</option>
      <option value="gato" <?= $especie == 'gato' ? 'selected' : '' ?>>Gato</option>
      </select>
      <output class="msg_especie"><?=$msg_especie?></output>

      <input type="number" name="edad" placeholder="Edad (años)" value="<?=$edad?>" min="0" max="30" />
      <output class="msg_edad"><?=$msg_edad?></output>

      <input type="text" name="raza" placeholder="Raza (opcional)" value="<?=$raza?>" />
      <output class="msg_raza"><?=$msg_raza?></output>

      <input type="text" name="color" placeholder="Color (opcional)" value="<?=$color?>" />
      <output class="msg_color"><?=$msg_color?></output>
      
      <select name="sexo" required>
        <option value="" disabled selected>Sexo</option>
        <option value="macho">Macho</option>
        <option value="hembra">Hembra</option>
      </select>

      <button type="submit" name="btn_enviar" class="btn_enviar">Registrar mascota</button>
    </form>
  </section>

  <script>
    const inputFoto = document.getElementById('input-foto');
    const previewFoto = document.getElementById('preview-foto');
    const btnFoto = document.getElementById('btn-foto');

    inputFoto.addEventListener('change', () => {
      const file = inputFoto.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = e => {
          previewFoto.src = e.target.result;
          previewFoto.style.display = 'block';
          btnFoto.style.display = 'none'; // 🔹 Ocultar el botón "+"
        };
        reader.readAsDataURL(file);
      }
    });
  </script>

</body>
</html>
