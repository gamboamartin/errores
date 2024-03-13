<?php
namespace tests\src;

use gamboamartin\errores\errores;

use PHPUnit\Framework\TestCase;


class erroresTest extends TestCase{
    public errores $errores;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
    }

    public function test_error(){
        unset($_SESSION['error']);

        $this->assertArrayHasKey('error', $this->errores->error(mensaje:'',data:  array()));
        $this->assertArrayHasKey('mensaje', $this->errores->error('', array()));
        $this->assertIsArray( $this->errores->error('', array()));
        $this->assertEquals(1,$this->errores->error('', array())['error']);
        $this->assertEquals('<b><span style="color:red">x</span></b>',$this->errores->error('x', array())['mensaje']);
        $this->assertEquals('x',$this->errores->mensaje);

       $this->errores->error('test',data: array('test'),aplica_bitacora: true);

    }

}