<?php
namespace tests\src;

use gamboamartin\errores\errores;
use tests\test;


class erroresTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_error(){
        unset($_SESSION['error']);
        $resultado = $this->errores->error('', array());

        $this->assertArrayHasKey('error', $this->errores->error('', array()));
        $this->assertArrayHasKey('mensaje', $this->errores->error('', array()));
        $this->assertIsArray( $this->errores->error('', array()));
        $this->assertEquals(1,$this->errores->error('', array())['error']);
        $this->assertEquals('<b><span style="color:red">x</span></b>',$this->errores->error('x', array())['mensaje']);
        $this->assertEquals('x',$this->errores->mensaje);
    }

}