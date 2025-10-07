<?php

// Importar conexion
require 'includes/config/app.php';
$db = conectarDB();

// Crear un email y password
$email = "correo@correo.com";
$password = "123456";

// Hash a la contraseña
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Query para crear el usuario
$query = "INSERT INTO usuarios (email, password) VALUES ('{$email}', '{$passwordHash}');";

//echo $query;

// Agregarlo a la base de datos
mysqli_query($db, $query);