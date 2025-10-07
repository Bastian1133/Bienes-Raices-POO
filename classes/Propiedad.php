<?php

namespace App;

class Propiedad {

    // Base de Datos
    protected static $db; // static para que no se reescriba sin importar cuantas veces se cree una nueva instancia
    protected static $columnasDB = ['id', 'titulo', 'precio', 'imagen', 'descripcion', 'habitaciones', 'wc', 'estacionamiento', 'creado', 'vendedorId'];
    
    // Errores
    protected static $errores = [];

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

    //Definir la conexion a la BD
    public static function setDB($database) {
        //self en vez de this porque es static
        self::$db = $database;
    }

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? ''; // '' para placeholder en caso de que no este el valor
        $this->titulo = $args['titulo'] ?? '';
        $this->precio = $args['precio'] ?? '';
        $this->imagen = $args['imagen'] ?? '';
        $this->descripcion = $args['descripcion'] ?? '';
        $this->habitaciones = $args['habitaciones'] ?? '';
        $this->wc = $args['wc'] ?? '';
        $this->estacionamiento = $args['estacionamiento'] ?? '';
        $this->creado = date('Y/m/d');
        $this->vendedorId = $args['vendedorId'] ?? 1;
    }

    public function guardar() {
        if(isset($this->id)) { // Si existe el id...
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
        $query = " INSERT INTO propiedades ( ";
        $query .= /* .= Forma de concatenar */ join(', ', array_keys($atributos)); // Join crea un nuevo string a partir de un arreglo, se pone el espaciador y el array keys para tener un string de los nombres de los atributos  
        $query .= " ) VALUES (' ";
        $query .= join("', '", array_values($atributos)). " ') "; // se aplica join a array_values para obtener un string con todos los valores de cada atributo, separado por ,

        $resultado = self::$db->query($query);

        return $resultado;
    }

    public function actualizar() {
        // Sanitizar los datos
        $atributos = $this->sanitizarAtributos();

        $valores = [];
        foreach($atributos as $key => $value) {
            $valores[] = "{$key} = '{$value}'";
        }

        $query = "UPDATE propiedades SET ";
        $query .= join(', ', $valores);
        $query.= " WHERE id = '" . self::$db->escape_string($this->id) . "' ";
        $query.= " LIMIT 1 ";

        $resultado = self::$db->query($query);

        if($resultado) {
            //Redireccionar al usuario
            header('Location: /admin?resultado=2');
        }
    }

    //Identificar y unir los atributos de la BD
    public function atributos() {
        $atributos = [];
        foreach(self::$columnasDB as $columna) {
            if($columna === 'id') continue; // Para que ignore el atributo de columna y no lo incluya en el arreglo de atributos
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

    // Validacion
    public static function getErrores() {
        return self::$errores;
    }

    public function validar() {
        
        if(!$this->titulo) {
            self::$errores[] = "Debes de añadir un título";
        }

        if(!$this->precio) {
            self::$errores[] = "El precio es obligatorio";
        }

        if( strlen($this->descripcion) < 50) {
            self::$errores[] = "La descripcion es obligatoria y debe tener al menos 50 caracteres";
        }

        if(!$this->habitaciones) {
            self::$errores[] = "El número de habitaciones es obligatorio";
        }

        if(!$this->wc) {
            self::$errores[] = "El número de baños es obligatorio";
        }

        if(!$this->estacionamiento) {
            self::$errores[] = "El número de lugares de estacionamiento es obligatorio";
        }

        if(!$this->vendedorId) {
            self::$errores[] = "Elige un vendedor";
        }

        if(!$this->imagen) {
            self::$errores[] = "La imagen es obligatoria";
        }

        return self::$errores;
    }

    public function setImagen($imagen) {
        // Elimina la imagen previa

        if(isset($this->id)) {
            // Comrprobar si existe el archivo
            $existeArchivo = file_exists(CARPETA_IMAGENES . $this->imagen);

            if($existeArchivo) {
                unlink(CARPETA_IMAGENES . $this->imagen);
            }
        }

        // Asignar al atributo de imagen el nombre de la imagen
        if($imagen) {
            $this->imagen = $imagen;
        }
    }

    // Listar todos los registros
    public static function all() {
        $query = "SELECT * FROM propiedades";

        $resultado = self::consultarSQL($query);

        return $resultado;
    }

    // Busca un registro por su ID
    public static function find($id) {
        $query = "SELECT * FROM propiedades WHERE id = {$id}";

        $resultado = self::consultarSQL($query);

        return array_shift($resultado); // array_shift devuelve el primer elemento de un arreglo
    }

    public static function consultarSQL($query) { // Funcion reutilizable
        // Conultar la BD
        $resultado = self::$db->query($query);

        // Iterar los resultados
        $array = [];
        while($registro = $resultado->fetch_assoc()) {
            $array[] = self::crearObjeto($registro);
        }

        // Liberar la memoria
        $resultado->free();

        // Retornar los resultados
        return $array;
    }

    protected static function crearObjeto($registro) {
        $objeto = new self; // Crea nuevos objetos de la clase actual

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