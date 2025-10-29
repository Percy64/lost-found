<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mascota Extraviada - Nana</title>
    <link rel="stylesheet" href="assets/css/nousuario.css">
</head>
<body>
    <?php
        // Datos de la mascota
        $mascota = array(
            'nombre' => 'Nana',
            'ubicacion' => 'San Salvador',
            'descripcion' => 'Niño de 10 años busca a su conejo blanco desaparecido en el barrio. Tiene ojos azules',
            'imagenes' => 'imagenes/pexels-johndetochka-9270356 1.png',
        );
    ?>

    <div class="container">
        <div class="content">
            <!-- Imagen de la mascota -->
            <div class="image-container">
                <img src="<?php echo htmlspecialchars($mascota['imagenes']); ?>" alt="<?php echo htmlspecialchars($mascota['nombre']); ?>">
            </div>

            <!-- Encabezado con nombre y ubicación -->
            <div class="header">
                <h1 class="title"><?php echo htmlspecialchars($mascota['nombre']); ?></h1>
                <div class="location">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    <span><?php echo htmlspecialchars($mascota['ubicacion']); ?></span>
                </div>
            </div>

            <!-- Descripción -->
            <p class="description"><?php echo htmlspecialchars($mascota['descripcion']); ?></p>

            <!-- Botones de acción -->
            <div class="buttons-container">
                <button class="btn" onclick="alert('Enviar mensaje')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    Enviar mensaje
                </button>

                <button class="btn" onclick="alert('Enviar correo')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                    </svg>
                    Enviar correo
                </button>

                <button class="btn" onclick="alert('Enviar ubicación')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    Enviar ubicación
                </button>

                <!-- Footer con botones -->
                <div class="footer-container">
                    <button class="icon-btn" onclick="alert('Información')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                    </button>
                    <button class="icon-btn" onclick="alert('Perfil')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>