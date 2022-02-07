<?php
/**
 * Retorna errores, si en alguna parte del software se detecta algun error la estatica errores::error se vuelve true
 */
namespace base;
class errores{
    public static bool $error = false;
    public string $mensaje = '';
    public string $class ='';
    public int $line = -1 ;
    public string $file = '';
    public string $function = '';
    public mixed $data = '';


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
     * Si existe algun error se debe llamar esta funcion la cual debera funcionar de manera recursiva
     * para mostrar todos lo errores desde el origen hasta la ejecucion final
     * @param string $mensaje Mensaje a mostrar
     * @param mixed $data Complemento y/o detalle de error
     * @param string $seccion_header elemento para regresar a seccion especifica en el controlador
     * @param string $accion_header elemento para regresar a accion especifica en el controlador
     * @return array
     */
    public function error(string $mensaje, mixed $data, string $seccion_header = '', string $accion_header = ''):array{
        $mensaje = trim($mensaje);
        if($mensaje === ''){
            return $this->error("", $mensaje, $seccion_header ,  $accion_header);
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

        $data_error['error'] = 1;
        $data_error['mensaje'] = '<b><span style="color:red">' . $mensaje . '</span></b>';
        $data_error['file'] = '<b>' . $debug[0]['file'] . '</b>';
        $data_error['line'] = '<b>' . $debug[0]['line'] . '</b>';
        $data_error['class'] = '<b>' . $debug[1]['class'] . '</b>';
        $data_error['function'] = '<b>' . $debug[1]['function'] . '</b>';
        $data_error['data'] = $data;

        $_SESSION['error_resultado'][] = $data_error;

        $seccion_header = trim($seccion_header);
        $accion_header = trim($accion_header);
        if($seccion_header!=='' && $accion_header !=='') {
            $_SESSION['seccion_header'] = $seccion_header;
            $_SESSION['accion_header'] = $accion_header;
        }

        self::$error = true;
        $this->mensaje = $mensaje;
        $this->class = $debug[1]['class'];
        $this->line = $debug[0]['line'];
        $this->file = $debug[0]['file'];
        $this->function = $debug[1]['function'];
        if($data === null){
            $data = '';
        }

        $this->data = $data;

        return $data_error;
    }

}
