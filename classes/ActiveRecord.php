<?php

namespace App;

class ActiveRecord {
    
    // Base de Datos
    protected static $db; // static para que no se reescriba sin importar cuantas veces se cree una nueva instancia
    protected static $columnasDB = [];
    protected static $tabla = '';

    // Errores
    protected static $errores = [];

    //Definir la conexion a la BD
    public static function setDB($database) {
        //self en vez de this porque es static
        self::$db = $database;
    }

    // Forma antes de php 8
    public $id;
    public $titulo;
    public $precio;
    public $imagen;
    public $descripcion;
    public $habitaciones;
    public $wc;
    public $estacionamiento;
    public $creado;
    public $vendedorId;

    public $nombre;
    public $apellido;
    public $telefono;

    public function guardar() {
        if(!is_null($this->id)) { // Si existe el campo id...
            // Actualizar
            $this->actualizar();
        } else {
            // Crear nuevo registro
            $this->crear();
        }
    }

    public function crear() {

        // Sanitizar los datos
        $atributos = $this->sanitizarAtributos();
        

        // Insertar en la base de datos
        $query = " INSERT INTO " . static::$tabla . " ( ";
        $query .= /* .= Forma de concatenar */ join(', ', array_keys($atributos)); // Join crea un nuevo string a partir de un arreglo, se pone el espaciador y el array keys para tener un string de los nombres de los atributos  
        $query .= " ) VALUES (' ";
        $query .= join("', '", array_values($atributos)). " ') "; // se aplica join a array_values para obtener un string con todos los valores de cada atributo, separado por ,
        
        $resultado = self::$db->query($query);
        
        if($resultado) {
            //Redireccionar al usuario
            header('Location: /admin?resultado=1');
        }
    }

    public function actualizar() {
        // Sanitizar los datos
        $atributos = $this->sanitizarAtributos();

        $valores = [];
        foreach($atributos as $key => $value) {
            $valores[] = "{$key} = '{$value}'";
        }

        $query = "UPDATE " . static::$tabla . " SET ";
        $query .= join(', ', $valores);
        $query .= " WHERE id = '" . self::$db->escape_string($this->id) . "' ";
        $query .= " LIMIT 1 ";

        $resultado = self::$db->query($query);

        if($resultado) {
            //Redireccionar al usuario
            header('Location: /admin?resultado=2');
        }
    }

    // Eliminar un registro
    public function eliminar() {
        // Borrar fila de la base de datos
        $query = "DELETE FROM " . static::$tabla . " WHERE id = " . self::$db->escape_string($this->id) . " LIMIT 1";
        $resultado = self::$db->query($query);

        if($resultado) {
            $this->borrarImagen();
            //Redireccionar al usuario
            header('Location: /admin?resultado=3');
        }
    }

    //Identificar y unir los atributos de la BD
    public function atributos() {
        $atributos = [];
        foreach(static::$columnasDB as $columna) {
            if($columna === 'id') continue; // Para que ignore el atributo de id y no lo incluya en el arreglo de atributos
            $atributos[$columna] = $this->$columna;
        }
        return $atributos;
    }

    public function sanitizarAtributos() {
        $atributos = $this->atributos();
        $sanitizado = [];
        

        foreach($atributos as $key => $value) { // Para guardar tanto el nombre del dato (titulo, precio, etc) como el valor de cada uno
            $sanitizado[$key] = self::$db->escape_string($value);
        }
        
        return $sanitizado;
    }

    public function setImagen($imagen) {
        // Elimina la imagen previa
        if(!is_null($this->id)) {
            $this->borrarImagen();
        }

        // Asignar al atributo de imagen el nombre de la imagen
        if($imagen) {
            $this->imagen = $imagen;
        }
    }

    // Eliminar el archivo
    public function borrarImagen() {

        // Comrprobar si existe el archivo
        $existeArchivo = file_exists(CARPETA_IMAGENES . $this->imagen);

        if($existeArchivo) {
            unlink(CARPETA_IMAGENES . $this->imagen);
        }
    }

    // Validacion
    public static function getErrores() {
        return static::$errores;
    }

    public function validar() {

        static::$errores = [];
        return static::$errores;
    }

    // Listar todos los registros
    public static function all() {
        $query = "SELECT * FROM " . static::$tabla; 

        $resultado = self::consultarSQL($query);

        return $resultado;
    }

    // Busca un registro por su ID
    public static function find($id) {
        $query = "SELECT * FROM " . static::$tabla . " WHERE id = {$id}";

        $resultado = self::consultarSQL($query);

        return array_shift($resultado); // array_shift devuelve el primer elemento de un arreglo
    }

    public static function consultarSQL($query) { // Funcion reutilizable
        // Conultar la BD
        $resultado = self::$db->query($query);

        // Iterar los resultados
        $array = [];
        while($registro = $resultado->fetch_assoc()) {
            $array[] = static::crearObjeto($registro);
        }

        // Liberar la memoria
        $resultado->free();

        // Retornar los resultados
        return $array;
    }

    protected static function crearObjeto($registro) {
        $objeto = new static; // Crea nuevos objetos de la clase hija que se instancie

        foreach($registro as $key => $value) {
            if(property_exists($objeto, $key)) { // Revisa que exista el identificador $key en el objeto creado, el cual hereda los identificadores de la clase
                $objeto->$key = $value;
            }
        }

        return $objeto;
    }

    // Sincroniza el objeto en memoria con los cambios realizados por el usuario
    public function sincronizar($args = []) {
        foreach($args as $key => $value) {
            if(property_exists($this, $key) && !is_null($value)) {
                $this->$key = $value;
            }
        }
    }
}