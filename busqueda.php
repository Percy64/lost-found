<?php
session_start();
require_once 'conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: iniciosesion.php');
    exit;
}

// Obtener parámetros de búsqueda
$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
$mascotas = [];

// Si hay búsqueda, realizar consulta a la base de datos
if (!empty($busqueda)) {
    try {
        $sql = "SELECT m.*, u.nombre as nombre_usuario, u.apellido as apellido_usuario 
                FROM mascotas m 
                JOIN usuarios u ON m.id = u.id 
                WHERE m.nombre LIKE ? OR m.especie LIKE ? OR m.raza LIKE ? OR m.color LIKE ?
                ORDER BY m.fecha_registro DESC";
        $stmt = $pdo->prepare($sql);
        $search_term = "%$busqueda%";
        $stmt->execute([$search_term, $search_term, $search_term, $search_term]);
        $mascotas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $error_message = 'Error al realizar la búsqueda.';
    }
} else {
    // Si no hay búsqueda, mostrar las mascotas más recientes
    try {
        $sql = "SELECT m.*, u.nombre as nombre_usuario, u.apellido as apellido_usuario 
                FROM mascotas m 
                JOIN usuarios u ON m.id = u.id 
                ORDER BY m.fecha_registro DESC LIMIT 10";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $mascotas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $error_message = 'Error al cargar las mascotas.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Búsqueda de Mascotas - Pet Alert</title>
  <link rel="stylesheet" href="assets/css/busqueda.css">
</head>
<body>
  <section class="contenedor-principal">
    <div class="busqueda-mascota">
      <h2>Búsqueda de Mascotas</h2>

      <form method="GET" action="" class="barra-busqueda">
        <input type="text" name="q" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Buscar por nombre, especie, raza, color..." />
        <button type="submit" class="btn-buscar">🔍</button>
        <button type="button" class="btn-camara" onclick="alert('Función de cámara próximamente')">📷</button>
      </form>

      <div id="mapa">
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3282.853604936277!2d-89.2182!3d13.7942!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8f633a5e8b8a4b15%3A0x7f3b4c5d6e7f8a9b!2sSan%20Salvador%2C%20El%20Salvador!5e0!3m2!1ses!2sar!4v1698000000000!5m2!1ses!2sar"
          width="100%" height="100%" style="border:0; border-radius:12px;" allowfullscreen="" loading="lazy">
        </iframe>
      </div>

      <?php if (isset($error_message)): ?>
        <div class="error-message">
          <?= htmlspecialchars($error_message) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($busqueda)): ?>
        <h3>Resultados para: "<?= htmlspecialchars($busqueda) ?>" (<?= count($mascotas) ?> encontrados)</h3>
      <?php else: ?>
        <h3>Mascotas Reportadas Recientemente</h3>
      <?php endif; ?>

      <!-- Carrusel de mascotas desde la base de datos -->
      <div class="carrusel-con-flechas">
        <button class="flecha izquierda">‹</button>

        <div class="carrusel">
          <?php if (empty($mascotas)): ?>
            <div class="no-results">
              <p>No se encontraron mascotas<?= !empty($busqueda) ? " para la búsqueda \"$busqueda\"" : "" ?>.</p>
            </div>
          <?php else: ?>
            <?php foreach ($mascotas as $mascota): ?>
              <div class="card-mascota" onclick="window.location.href='perfil_mascota.php?id=<?= $mascota['id_mascota'] ?>'">
                <?php 
                $imagen_src = 'assets/images/dog-placeholder.svg'; // Imagen por defecto
                if (!empty($mascota['foto_url'])) {
                  if (file_exists($mascota['foto_url'])) {
                    $imagen_src = $mascota['foto_url'];
                  }
                }
                
                // Determinar icono según especie
                if (stripos($mascota['especie'], 'gato') !== false) {
                  $imagen_src = empty($mascota['foto_url']) || !file_exists($mascota['foto_url']) ? 'assets/images/cat-placeholder.svg' : $mascota['foto_url'];
                } elseif (stripos($mascota['especie'], 'conejo') !== false) {
                  $imagen_src = empty($mascota['foto_url']) || !file_exists($mascota['foto_url']) ? 'assets/images/rabbit-placeholder.svg' : $mascota['foto_url'];
                }
                ?>
                <img src="<?= htmlspecialchars($imagen_src) ?>" alt="<?= htmlspecialchars($mascota['nombre']) ?>">
                <p><strong><?= htmlspecialchars($mascota['nombre']) ?></strong></p>
                <p><?= htmlspecialchars($mascota['especie']) ?> - <?= htmlspecialchars($mascota['raza']) ?></p>
                <span>📍 Reportado por: <?= htmlspecialchars($mascota['nombre_usuario'] . ' ' . $mascota['apellido_usuario']) ?></span>
                <span>🕒 <?= date('d/m/Y H:i', strtotime($mascota['fecha_registro'])) ?></span>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <button class="flecha derecha">›</button>
      </div>
      <!-- Fin carrusel -->

      <nav class="barra-navegacion">
        <button onclick="window.location.href='home.php'" title="Inicio">🏠</button>
        <button onclick="window.location.href='busqueda.php'" title="Búsqueda" class="active">🔍</button>
        <button onclick="alert('Información próximamente')" title="Información">ℹ️</button>
        <button onclick="window.location.href='perfil_usuario.php'" title="Perfil">👤</button>
      </nav>
    </div>
  </section>

  <!-- Script para navegación del carrusel -->
  <script>
    const carrusel = document.querySelector('.carrusel');
    const btnIzq = document.querySelector('.flecha.izquierda');
    const btnDer = document.querySelector('.flecha.derecha');

    if (btnIzq && btnDer && carrusel) {
      btnIzq.addEventListener('click', () => {
        carrusel.scrollBy({ left: -250, behavior: 'smooth' });
      });

      btnDer.addEventListener('click', () => {
        carrusel.scrollBy({ left: 250, behavior: 'smooth' });
      });
    }
  </script>
</body>
</html>