<?php

namespace gamboamartin\errores;

use config\generales;

/**
 * Clase principal para la gestión y registro de errores en la aplicación.
 *
 * Esta clase provee un mecanismo centralizado para:
 *  - Registrar errores en una sesión y/o bitácora (archivos .log).
 *  - Almacenar detalles relevantes del error (archivo, línea, clase, función, etc.).
 *  - Generar mensajes de error más descriptivos, incluyendo sugerencias de corrección (fix).
 *  - Exponer propiedades y métodos que permiten identificar claramente dónde y cómo ocurrió el error.
 *
 * Adicionalmente, en el constructor se establecen los mensajes de error estándar relacionados con la
 * subida de archivos (a través de `$upload_errores`), lo cual facilita la interpretación de los códigos
 * de error que genera PHP en dichos procesos.
 *
 * @package gamboamartin\errores
 * @author
 * @version 6.3
 * @since   2025-20-01
 *
 * @property bool   $error         Indica si se ha registrado al menos un error. Es estático, pues se mantiene
 *                                 a lo largo del ciclo de vida de la aplicación para indicar estados de error.
 * @property string $mensaje       Mensaje de error principal.
 * @property string $class         Clase en la que se registró el último error.
 * @property int    $line          Línea en la que se registró el último error.
 * @property string $file          Archivo en el que se registró el último error.
 * @property string $function      Función/método en el que se registró el último error.
 * @property mixed  $data          Datos adicionales asociados al último error (puede ser array, objeto, etc.).
 * @property array  $params        Parámetros relevantes al contexto del último error.
 * @property string $fix           Sugerencia para corregir el error.
 * @property array  $out           Colección de mensajes de salida generados por la clase, típicamente en HTML.
 * @property array  $upload_errores Mensajes de error estándar que corresponden a los códigos de subida de archivos en PHP.
 *
 * @example
 *  // Ejemplo de uso básico:
 *  use gamboamartin\errores\errores;
 *
 *  $errores = new errores();
 *  if(!$conexion) {
 *      $errores->error(
 *          mensaje: "No se pudo establecer la conexión a la base de datos",
 *          data: array("host"=>"localhost","user"=>"root"),
 *          aplica_bitacora: true
 *      );
 *  }
 *
 *  // Posteriormente, se puede verificar si existe un error global:
 *  if(errores::$error) {
 *      // Manejar la lógica de error, por ejemplo, redirigir o mostrar un mensaje al usuario
 *  }
 *
 * @see errores::error() Método principal que registra y maneja los detalles del error.
 */
class errores {
    /**
     * Indica si hay un error registrado en el sistema.
     *
     * @var bool
     */
    public static bool $error = false;

    /**
     * Mensaje de error principal.
     *
     * @var string
     */
    public string $mensaje = '';

    /**
     * Nombre de la clase donde ocurrió el error.
     *
     * @var string
     */
    public string $class ='';

    /**
     * Número de línea donde ocurrió el error.
     *
     * @var int
     */
    public int $line = -1;

    /**
     * Ruta o nombre del archivo donde ocurrió el error.
     *
     * @var string
     */
    public string $file = '';

    /**
     * Nombre de la función o método donde ocurrió el error.
     *
     * @var string
     */
    public string $function = '';

    /**
     * Datos adicionales relacionados con el error (puede ser array, objeto, etc.).
     *
     * @var mixed
     */
    public mixed $data = '';

    /**
     * Parámetros relevantes que se quieran registrar en el momento del error (por ejemplo, valores de variables).
     *
     * @var array
     */
    public array $params = array();

    /**
     * Sugerencia para corregir o prevenir el error.
     *
     * @var string
     */
    public string $fix = '';

    /**
     * Colección de mensajes de salida (en formato HTML) generados por los errores detectados.
     *
     * @var array
     */
    public static array $out = array();

    /**
     * Mensajes de error correspondientes a los códigos de error en la subida de archivos (UPLOAD_ERR_*).
     *
     * @var array
     */
    public array $upload_errores = array();

    /**
     * Constructor de la clase.
     *
     * Se encarga de inicializar los mensajes de error estándar para procesos de subida de archivos.
     * Estos mensajes corresponden a los posibles códigos de error que emite PHP al subir un archivo.
     *
     * @return void
     */

    public function __construct(){
        $this->upload_errores = array(
            0 => 'There is no error, the file uploaded with success',
            1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            3 => 'The uploaded file was only partially uploaded',
            4 => 'No file was uploaded',
            6 => 'Missing a temporary folder',
            7 => 'Failed to write file to disk.',
            8 => 'A PHP extension stopped the file upload.',
        );

    }

    /**
     * REG
     * Registra un error, construye un arreglo de datos con información relevante (archivo, línea, clase, función, etc.),
     * y en caso de ser necesario, guarda un registro de bitácora (archivo .log) con el detalle del error.
     *
     * Este método se encarga de:
     *  - Validar que el mensaje de error no sea vacío.
     *  - Obtener la información del backtrace (archivo, línea, clase y función donde ocurrió el error).
     *  - Construir un array `$data_error` con toda la información relevante del error.
     *  - Almacenar la información del error en la sesión (bajo la clave `$_SESSION['error_resultado']`).
     *  - Opcionalmente, redirigir a una sección/acción en específico si se proporcionan `$seccion_header` y `$accion_header`.
     *  - Si `$aplica_bitacora` es `true`, crea un archivo de bitácora en la carpeta `archivos/errores/`.
     *  - Establecer la propiedad estática `self::$error` en `true`, e incluir en `self::$out` un mensaje
     *    con el detalle del error en formato HTML.
     *
     * @param string $mensaje         Mensaje de error principal que se desea registrar. No debe ser vacío.
     * @param mixed  $data            Datos adicionales relacionados con el error (puede ser un array, objeto, string, etc.).
     * @param string $accion_header   Acción a la cual se redirige en caso de ser necesario (opcional).
     * @param bool   $aplica_bitacora Indica si se debe generar un archivo de bitácora con los detalles del error.
     * @param string $class           Nombre de la clase en la que se produjo el error (opcional, se detecta automáticamente).
     * @param bool   $es_final        Si es `true`, se incluye el contenido de `$data` en el mensaje de salida final.
     * @param string $file            Ruta del archivo en el que se produjo el error (opcional, se detecta automáticamente).
     * @param string $fix             Sugerencia para corregir el error. Se incluye en la salida final si no está vacío.
     * @param string $funcion         Nombre de la función/método en la que se produjo el error (opcional, se detecta automáticamente).
     * @param string $line            Línea de código donde se produjo el error (opcional, se detecta automáticamente).
     * @param array  $params          Parámetros que se deseen registrar (por ejemplo, los valores de variables en el contexto).
     * @param int    $registro_id     Identificador de un registro específico que desencadenó el error (opcional).
     * @param string $seccion_header  Sección a la que se redirige en caso de ser necesario (opcional).
     *
     * @return array                  Devuelve un arreglo que contiene la siguiente información:
     *  - error           (int)    Indica si es un error (1 = sí).
     *  - mensaje         (string) Mensaje de error en HTML con formato de resaltado.
     *  - mensaje_limpio  (string) Mensaje de error sin formato.
     *  - file            (string) Archivo donde ocurrió el error.
     *  - line            (string) Línea donde ocurrió el error.
     *  - class           (string) Clase donde ocurrió el error.
     *  - function        (string) Función/método donde ocurrió el error.
     *  - data            (mixed)  Datos adicionales relacionados con el error.
     *  - params          (array)  Conjunto de parámetros que se pasaron al método.
     *  - fix             (string) Sugerencia para corregir el error (si se proporcionó).
     *
     * @example
     *  Ejemplo 1: Uso básico registrando un error genérico.
     *  --------------------------------------------------------------------------
     *  $errores = new errores(); // Instanciación de la clase que contiene este método
     *  $resultado_error = $errores->error(
     *      mensaje: "No se pudo conectar a la base de datos",
     *      data: array("host" => "localhost", "user" => "root"),
     *      aplica_bitacora: true,
     *  );
     *
     *  // $resultado_error contendrá un arreglo con la información del error.
     *  // Además, se creará un archivo .log dentro de la carpeta archivos/errores/
     *  // con la información serializada del error.
     *
     * @example
     *  Ejemplo 2: Proporcionando parámetros opcionales (sección y acción para redirigir).
     *  --------------------------------------------------------------------------
     *  $errores = new errores();
     *  $resultado_error = $errores->error(
     *      mensaje: "Error en proceso de facturación",
     *      data: null,
     *      accion_header: "editar",
     *      seccion_header: "facturas",
     *      registro_id: 123,
     *      aplica_bitacora: true
     *  );
     *
     *  // En este caso, además de registrar el error, se asignan valores a
     *  // $_SESSION['seccion_header'], $_SESSION['accion_header'] y $_SESSION['registro_id_header']
     *  // que podrían usarse para redirigir al usuario o mostrarle un mensaje en otra pantalla.
     *
     * @example
     *  Ejemplo 3: Incluyendo un mensaje fix y contenido final.
     *  --------------------------------------------------------------------------
     *  $errores = new errores();
     *  $resultado_error = $errores->error(
     *      mensaje: "Error al validar datos de usuario",
     *      data: ['email' => 'usuario@example.com', 'error_info' => 'Formato no válido'],
     *      fix: "Verifique que el email tenga un formato adecuado y vuelva a intentarlo",
     *      es_final: true
     *  );
     *
     *  // El arreglo de respuesta contendrá la sugerencia (fix) y los datos completos.
     *  // Además, en la salida HTML final se incluirán dichos datos.
     */
    final public function error(string $mensaje, mixed $data, string $accion_header = '', bool $aplica_bitacora = false,
                                string $class = '', bool $es_final = false, string $file = '', string $fix = '',
                                string $funcion = '', string $line = '', array $params = array(),
                                int $registro_id = -1, string $seccion_header = ''):array{

        $mensaje = trim($mensaje);
        if($mensaje === ''){
            $fix = 'Debes mandar llamar la funcion con un mensaje valido en forma de texto ej ';
            $fix .= ' $error = new errores()';
            $fix .= '$error->error(mensaje: "Mensaje de error descriptivo",data: "datos con el error");';
            return $this->error(mensaje: "Error el mensaje esta vacio", data: $mensaje, accion_header: $accion_header,
                fix: $fix, params: get_defined_vars(), registro_id: $registro_id, seccion_header: $seccion_header);
        }
        $debug = debug_backtrace(2);

        if(!isset($debug[0]['line'])){
            $debug[0]['line'] = -1;
        }
        if(!isset($debug[0]['line'])){
            $debug[0]['file'] = '';
        }
        if(!isset($debug[1]['class'])){
            $debug[1]['class'] = '';
        }
        if(!isset($debug[1]['function'])){
            $debug[1]['function'] = '';
        }

        $file_error = $debug[0]['file'];
        $file = trim($file);
        if($file !== ''){
            $file_error = $file;
        }

        $class_error = $debug[1]['class'];
        $class = trim($class);
        if($class !== ''){
            $class_error = $class;
        }

        $funcion_error = $debug[1]['function'];
        $funcion = trim($funcion);
        if($funcion !== ''){
            $funcion_error = $funcion;
        }

        $line_error = $debug[0]['line'];
        $line = trim($line);
        if($line !== ''){
            $line_error = $line;
        }


        $data_error['error'] = 1;
        $data_error['mensaje'] = '<b><span style="color:red">' . $mensaje . '</span></b>';
        $data_error['mensaje_limpio'] = $mensaje;
        $data_error['file'] = '<b>' . $file_error . '</b>';
        $data_error['line'] = '<b>' . $line_error . '</b>';
        $data_error['class'] = '<b>' . $class_error . '</b>';
        $data_error['function'] = '<b>' . $funcion_error . '</b>';
        $data_error['data'] = $data;
        $data_error['params'] = $params;
        $data_error['fix'] = $fix;

        $datos_error = '';
        if($es_final){
            $datos_error = $data;
        }
        if(is_array($datos_error)){
            $datos_error = serialize($datos_error);
        }
        if(is_object($datos_error)){
            $datos_error = serialize($datos_error);
        }

        $out = "Mensaje: <b>".$data_error['mensaje']."</b><br>";
        $out .= "File: <b>".$data_error['file']."</b><br>";
        $out .= "Line: <b>".$data_error['line']."</b><br>";
        $out .= "Class: <b>".$data_error['class']."</b><br>";
        $out .= "Funcion: <b>".$data_error['function']."</b><br>";
        if($es_final) {
            $out .= "Datos: <b>" . $datos_error . "</b><br>";
        }
        $fix = trim($fix);
        if($fix!== ''){
            $out .= "Fix: <b>" . $fix . "</b><br>";
        }
        self::$out[] = $out;

        $_SESSION['error_resultado'][] = $data_error;

        $seccion_header = trim($seccion_header);
        $accion_header = trim($accion_header);
        if($seccion_header!=='' && $accion_header !=='') {
            $_SESSION['seccion_header'] = $seccion_header;
            $_SESSION['accion_header'] = $accion_header;
            $_SESSION['registro_id_header'] = $registro_id;
        }

        self::$error = true;
        $this->mensaje = $mensaje;
        $this->class = $debug[1]['class'];
        $this->line = $debug[0]['line'];
        $this->file = $debug[0]['file'];
        $this->function = $debug[1]['function'];
        $this->fix = $fix;
        $this->params = $params;
        if($data === null){
            $data = '';
        }

        $this->data = $data;
        if($aplica_bitacora){

            $ruta_archivos = (new generales())->path_base.'archivos/';
            if(!file_exists($ruta_archivos)){
                mkdir($ruta_archivos);
            }
            $ruta_archivos = $ruta_archivos.'errores/';
            if(!file_exists($ruta_archivos)){
                mkdir($ruta_archivos);
            }
            $name_file = 'error_file_'.$this->file.'_line_'.$this->line.'_function_'.$this->function.'_class_'.
                $this->class .date('Y-m-d H:m:s') .'_'.time().'.log';

            $name_file = str_replace('/', '_', $name_file);
            $name_file = str_replace('\\', '_', $name_file);

            $ruta_bit_error =$ruta_archivos.$name_file;

            file_put_contents($ruta_bit_error, serialize($data_error));
        }
        return $data_error;
    }
}
