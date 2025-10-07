<?php 

    require '../../includes/app.php';

    use App\Propiedad;
    use Intervention\Image\Drivers\Gd\Driver;
    use Intervention\Image\ImageManager as Image;

    estaAutenticado();


    //Base de datos
    $db = conectarDB();

    $propiedad = new Propiedad;

    // Consultar para obtener los vendedores
    $consulta = "SELECT * FROM vendedores";
    $resultado = mysqli_query($db, $consulta);

    //Arreglo con mensajes de errores
    $errores = Propiedad::getErrores();

    // Ejecutar el codigo despues de que el usuario envia el formulario
    if($_SERVER['REQUEST_METHOD'] === 'POST') {

        $propiedad = new Propiedad($_POST['propiedad']);

        // Generar un nombre unico para la imagen
        $nombreImagen = md5(uniqid(rand(), true)) . ".jpg"; // genera un hash variable
        
        // Leer la imagen con intervention/image
        if($_FILES['propiedad']['tmp_name']['imagen']) {
            $manager = new Image(Driver::class);
            $imagen = $manager->read($_FILES['propiedad']['tmp_name']['imagen'])->cover(800, 600); // cover para cortar pixeles excedentes y ajustar tamaño 800px ancho x 600px alto
            $propiedad->setImagen($nombreImagen);
        }

        $errores = $propiedad->validar();

        // Revisar que el arreglo de errores este vacío
        if(empty($errores)) {

            // SUBIDA DE ARCHIVOS
            // Crear carpeta en la raiz
            
            

            if(!is_dir(CARPETA_IMAGENES)) {
                mkdir(CARPETA_IMAGENES);
            }

            // Guarda la imagen en el servidor
            $imagen->save(CARPETA_IMAGENES . $nombreImagen);

            $resultado = $propiedad->guardar();
            if($resultado) {

                //Redireccionar al usuario
                header('Location: /admin?resultado=1');

            }
        }
        
        
    }    

    incluirTemplate('header');
?>

    <main class="contenedor seccion">
        <h1>Crear</h1>

        <a class="boton boton-verde" href="/admin">Volver</a>

        <?php foreach($errores as $error): ?>
        <div class="alerta error">
            <?php echo $error; ?>
        </div>
        
        <?php endforeach; ?>

        <form class="formulario" method="POST" action="/admin/propiedades/crear.php" enctype="multipart/form-data"> <!-- POST para enviar datos de forma segura y 
            privada, GET para exponerlos en una URL y enviar datos a un servidor. multipart/form-data para habilitar la subida de archivos -->
            
            <?php include '../../includes/templates/formulario_propiedades.php'; ?>

            <input class="boton boton-verde" type="submit" value="Crear Propiedad">
        </form>
    </main>

<?php 
    incluirTemplate('footer');
?>