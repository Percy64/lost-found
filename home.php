<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mascotas Extraviadas</title>
    <link rel="stylesheet" href="assets/css/home.css">
</head>
<body>
    <?php
        // Array de mascotas extraviadas
        $mascotas = array(
            array(
                'descripcion' => 'Familia desesperada busca a su perrita perdida en San Salvador',
                'ubicacion' => 'San Salvador',
                'imagen' => 'imagen/PERRITA GOLDEN RETRIVER 1.png',
            ),
            array(
                'descripcion' => 'Gato desaparecido en Santa Tecla: Familia ruega por su regreso seguro',
                'ubicacion' => 'Santa Tecla',
                'imagen' => 'imagen/pexels-fernanda-gomez-de-la-torre-197095072-11539729 1.png',
            ),
            array(
                'descripcion' => 'Conejo doméstico extraviado en La Libertad: Propietarios piden ayuda para encontrarlo',
                'ubicacion' => 'La Libertad',
                'imagen' => 'imagen/pexels-wildshots-19824946 1.png',
            )
        );
    ?>

    <div class="content-wrapper">
        <div class="container">
            <?php foreach ($mascotas as $mascota): ?>
                <div class="pet-card">
                    <div class="pet-header">
                        <p class="pet-description"><?php echo htmlspecialchars($mascota['descripcion']); ?></p>
                        <div class="pet-location">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <span><?php echo htmlspecialchars($mascota['ubicacion']); ?></span>
                        </div>
                    </div>
                    <img src="<?php echo htmlspecialchars($mascota['imagen']); ?>" alt="Mascota" class="pet-image">
                    <div class="pet-footer"></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Barra de navegación inferior -->
    <div class="bottom-nav">
        <button class="nav-btn" onclick="alert('Inicio')">
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
        <button class="nav-btn" onclick="alert('Perfil')">
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
</body>
</html>