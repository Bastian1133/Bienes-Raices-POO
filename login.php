<?php 
    // Importar conexion
    require 'includes/app.php';
    $db = conectarDB();

    //Autenticar el usuario
    $errores = [];

    $email = '';

    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        // var_dump($_POST);

        // Sanitizar datos
        $email = mysqli_real_escape_string($db, filter_var($_POST['email'], FILTER_VALIDATE_EMAIL));
        $password = mysqli_real_escape_string($db, $_POST['password']);

        if(!$email) {
            $errores[] = "El email es obligatorio o no es válido";
        }
        if(!$password) {
            $errores[] = "La contraseña es obligatoria";
        }

        if(empty($errores)) {
            // Revisar si el usuario existe
            $query = "SELECT * FROM usuarios WHERE email = '{$email}'";
            $resultado = mysqli_query($db, $query);

            if($resultado->num_rows) { // Verificar si hay resultados coincidentes en la consulta
                
                // Revisar si el password es correcto
                $usuario = mysqli_fetch_assoc($resultado);
                
                // Veificar si el password es correcto
                $auth = password_verify($password, $usuario['password']);

                if($auth) {
                    // El usuario esta autenticado
                    session_start();

                    // Llenar el arreglo de la sesión
                    $_SESSION['usuario'] = $usuario['email'];
                    $_SESSION['login'] = true;

                    header('Location: /admin');

                } else {
                    $errores[] = "La contraseña es incorrecta";
                }

            } else {
                $errores[] = "El usuario no existe";
            }
        }
    }


    // Incluye el header
    incluirTemplate('header');
?>

    <main class="contenedor seccion contenido-centrado">
        <h1>Iniciar Sesión</h1>

        <?php foreach($errores as $error): ?>
            <div class="alerta error">
                <?php echo $error; ?>
            </div>
        <?php endforeach; ?>

        <form class="formulario" method="POST" novalidate>
            <fieldset>
                <legend>Email y Contraseña</legend>

                <label for="email">E-mail</label>
                <input type="email" name="email" placeholder="Tu Email" id="email" required value="<?php echo $email; ?>">

                <label for="password">Contraseña</label>
                <input type="password" name="password" placeholder="Tu Contraseña" id="password" required>
            </fieldset>

            <input class="boton boton-verde" type="submit" value="Iniciar Sesión">
        </form>
    </main>

    <?php 
    incluirTemplate('footer');
?>