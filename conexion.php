<?php
// Establecer la conexión a la base de datos
$dsn = "mysql:host=localhost;dbname=mascotas_db;charset=utf8";
$usuario = "root";
$contraseña = "";
$opciones = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
$pdo = new PDO($dsn, $usuario, $contraseña, $opciones);

