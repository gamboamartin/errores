<?php

namespace gamboamartin\errores;


use config\generales;

/**
 * Class errores
 * Se define el uso general para todos los paquetes de integracion
 */
class errores{
    public static bool $error = false;
    public string $mensaje = '';
    public string $class ='';
    public int $line = -1 ;
    public string $file = '';
    public string $function = '';
    public mixed $data = '';
    public array $params = array();
    public string $fix = '';
    public static array $out = array();


    public array $upload_errores = array();

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
     * @url https://github.com/gamboamartin/errores/wiki/errores-error#funci%C3%B3n-error
     * Función de manejo de errores.
     *
     * @param string $mensaje        El mensaje de error a mostrar
     * @param mixed  $data           Los datos relacionados con el error
     * @param string $accion_header  (Opcional) Acción realizada cuando ocurrió el error
     * @param bool   $aplica_bitacora (Opcional) Si se debe registrar el error en la bitácora o no
     * @param string $class          (Opcional) La clase donde ocurrió el error
     * @param bool   $es_final       (Opcional) Si el error es crítico y se debe detener la ejecución
     * @param string $file           (Opcional) El archivo donde ocurrió el error
     * @param string $fix            (Opcional) Sugerencia para resolver el error
     * @param string $funcion        (Opcional) La función donde ocurrió el error
     * @param string $line           (Opcional) La línea donde ocurrió el error
     * @param array  $params         (Opcional) Los parámetros de la función donde ocurrió el error
     * @param int    $registro_id    (Opcional) ID del registro relacionado con el error
     * @param string $seccion_header (Opcional) Sección en la que ocurrió el error
     *
     * @return array Los detalles del error
     * @version 5.4.0
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
            $name_file = 'error_file_'.$this->file.'_line_'.$this->line.'_function_'.$this->function.'_class_'.$this->class
                .date('Y-m-d H:m:s') .'_'.time().'.log';

            $name_file = str_replace('/', '_', $name_file);
            $name_file = str_replace('\\', '_', $name_file);

            $ruta_bit_error =$ruta_archivos.$name_file;

            file_put_contents($ruta_bit_error, serialize($data_error));
        }
        return $data_error;
    }
}
