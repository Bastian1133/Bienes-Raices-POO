<?php

    require '../../includes/app.php';
    use App\Vendedor;
    estaAutenticado();

    // Validar que sea un ID valido

    $id = $_GET['id'];
    $id = filter_var($id, FILTER_VALIDATE_INT);

    if(!$id) {
        header('Location: /admin');
    }

    // Obtener el arreglo del vendedor
    $vendedor = Vendedor::find($id);

    //Arreglo con mensajes de errores
    $errores = Vendedor::getErrores();

    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Asignar los valores
        $args = $_POST['vendedor'];

        // Sincronizar objeto en memoria con lo que el usuario escribio
        $vendedor->sincronizar($args);

        // validacion
        $errores = $vendedor->validar();

        if(empty($errores)) {
            $vendedor->guardar();
        }
    }

    incluirTemplate('header');
?>

<main class="contenedor seccion">
        <h1>Actualizar Vendedor(a)</h1>

        <a class="boton boton-verde" href="/admin">Volver</a>

        <?php foreach($errores as $error): ?>
        <div class="alerta error">
            <?php echo $error; ?>
        </div>
        
        <?php endforeach; ?>

        <form class="formulario" method="POST"> <!-- POST para enviar datos de forma segura y 
            privada, GET para exponerlos en una URL y enviar datos a un servidor. multipart/form-data para habilitar la subida de archivos -->
            
            <?php include '../../includes/templates/formulario_vendedores.php'; ?>

            <input class="boton boton-verde" type="submit" value="Guardar Cambios">
        </form>
    </main>

<?php 
    incluirTemplate('footer');
?>