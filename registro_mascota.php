<?php
require 'conexion.php';
$nombre='';
$especie='';
$fecha_nacimiento='';
////////////////////////////
$msg_nombre='';
$msg_especie='';
$msg_fecha_nacimiento='';
////////////////////////////

$error=false;

if(isset($_POST['btn_enviar'])){

    if(isset($_POST['nombre'])){
        $nombre=trim($_POST['nombre']);
        if(empty($nombre)){
            $msg_nombre='No puede estar vacio';
            $error=true;
        }elseif(strlen($nombre) < 3 || strlen($nombre) > 12){
            $msg_nombre='Debe tener entre 3 y 12 caracteres';
            $error=true;
        }
    }else{
        $msg_nombre='Ingrese nombre';
    }

    // AGREGAR VALIDACIÓN Y ASIGNACIÓN DE ESPECIE
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

    if(isset($_POST['fecha_nacimiento'])){
        $fecha_nacimiento=trim($_POST['fecha_nacimiento']);
        if(empty($fecha_nacimiento)){
            $msg_fecha_nacimiento='No puede estar vacio';
            $error=true;
        }
    }else{
        $msg_fecha_nacimiento='Ingrese fecha de nacimiento';
    }

    if (!$error) {
        try {
            // Procesar la imagen
            $foto = null;
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
                    $foto = file_get_contents($_FILES['foto']['tmp_name']);
                }
            }

            if (!$error) {
                $sql = "INSERT INTO mascotas (nombre, especie, fecha_nacimiento, sexo, foto) VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre, $especie, $fecha_nacimiento, $_POST['sexo'], $foto]);
                
                $success_message = "Mascota registrada exitosamente.";
                // Limpiar variables
                $nombre = $especie = $fecha_nacimiento = '';
            }
        } catch(PDOException $e) {
            $msg_general = 'Error al registrar la mascota. Inténtelo nuevamente.';
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
      <h2>INGRESAR MASCOTA</h2>

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

      <input type="date" name="fecha_nacimiento" placeholder="Fecha de nacimiento" value="<?=$fecha_nacimiento?>" />
      <output class="msg_fecha_nacimiento"><?=$msg_fecha_nacimiento?></output>
      
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
