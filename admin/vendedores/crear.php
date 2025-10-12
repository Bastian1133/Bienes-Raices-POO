<?php

    require '../../includes/app.php';

    use App\Vendedor;

    estaAutenticado();

    $vendedor = new Vendedor();

    //Arreglo con mensajes de errores
    $errores = Vendedor::getErrores();

    if($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Crear una nueva instancia
        $vendedor = new Vendedor($_POST['vendedor']);

        // Validar que no haya campos vacios
        $errores = $vendedor->validar();

        // No hay errores
        if(empty($errores)) {
            $vendedor->guardar();
        }
    }

    incluirTemplate('header');
?>

<main class="contenedor seccion">
        <h1>Registrar Vendedor(a)</h1>

        <a class="boton boton-verde" href="/admin">Volver</a>

        <?php foreach($errores as $error): ?>
        <div class="alerta error">
            <?php echo $error; ?>
        </div>
        
        <?php endforeach; ?>

        <form class="formulario" method="POST" action="/admin/vendedores/crear.php"> <!-- POST para enviar datos de forma segura y 
            privada, GET para exponerlos en una URL y enviar datos a un servidor. multipart/form-data para habilitar la subida de archivos -->
            
            <?php include '../../includes/templates/formulario_vendedores.php'; ?>

            <input class="boton boton-verde" type="submit" value="Registrar Vendedor(a)">
        </form>
    </main>

<?php 
    incluirTemplate('footer');
?>