<?php

use App\Propiedad;
use App\Vendedor;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager as Image;

    require '../../includes/app.php';

    estaAutenticado();

    // Validar la URL por id valido
    $id = $_GET['id'];
    $id = filter_var($id, FILTER_VALIDATE_INT);

    // Obtener los datos de la propiedad
    $propiedad = Propiedad::find($id);

    // Consulta para obtener todos los vendedores
    $vendedores = Vendedor::all();

    //Arreglo con mensajes de errores
    $errores = Propiedad::getErrores();

    // Ejecutar el codigo despues de que el usuario envia el formulario
    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        // mysqli_real_escape_string() sirve para evitar inyecciones sql
        
        // Asignar los atributos
        $args = $_POST['propiedad'];
        
        $propiedad->sincronizar($args);
        
        // Validacion
        $errores =$propiedad->validar();

        // var_dump($imagen['name']); se puede acceder a los atributos de files para hacer validaciones
        // exit;

        /**  subida de archivos **/

        // Generar un nombre unico para la imagen
        $nombreImagen = md5(uniqid(rand(), true)) . ".jpg"; // genera un hash variable
        
        // Leer la imagen con intervention/image
        if($_FILES['propiedad']['tmp_name']['imagen']) {
            $manager = new Image(Driver::class);
            $imagen = $manager->read($_FILES['propiedad']['tmp_name']['imagen'])->cover(800, 600); // cover para cortar pixeles excedentes y ajustar tamaño 800px ancho x 600px alto
            $propiedad->setImagen($nombreImagen);
        }

        // Revisar que el arreglo de errores este vacío
        if(empty($errores)) {
            if($_FILES['propiedad']['tmp_name']['imagen']) {
                // Almacenar la imagen
                $imagen->save(CARPETA_IMAGENES . $nombreImagen);
            }
            
            $propiedad->guardar();
        }
        
        
    }    

    incluirTemplate('header');
?>

    <main class="contenedor seccion">
        <h1>Actualizar Propiedad</h1>

        <a class="boton boton-verde" href="/admin">Volver</a>

        <?php foreach($errores as $error): ?>
        <div class="alerta error">
            <?php echo $error; ?>
        </div>
        
        <?php endforeach; ?>

        <form class="formulario" method="POST" enctype="multipart/form-data"> <!-- POST para enviar datos de forma segura y 
            privada, GET para exponerlos en una URL y enviar datos a un servidor. multipart/form-data para habilitar la subida de archivos.
            Sin action para redirigir a la misma url con el id en la misma en caso de que no se complete el registro -->
            <?php include '../../includes/templates/formulario_propiedades.php'; ?>

            <input class="boton boton-verde" type="submit" value="Actualizar Propiedad">
        </form>
    </main>

<?php 
    mysqli_close($db);

    incluirTemplate('footer');
?>