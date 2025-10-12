<fieldset>
    <legend>Información General</legend>

    <label for="titulo">Nombre: </label>
    <input type="text" id="nombre" name="vendedor[nombre]" value="<?php echo s($vendedor->nombre) ?>" placeholder="Nombre Vendedor(a)">

    <label for="apellido">Apellido: </label>
    <input type="text" id="apellido" name="vendedor[apellido]" value="<?php echo s($vendedor->apellido) ?>" placeholder="Apellido Vendedor(a)">

</fieldset>

<fieldset>
    <legend>Información Extra</legend>

    <label for="telefono">Teléfono: </label>
    <input type="text" id="telefono" name="vendedor[telefono]" value="<?php echo s($vendedor->telefono) ?>" placeholder="Telefono Vendedor(a)">
    
</fieldset>