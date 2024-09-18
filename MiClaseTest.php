<?php

declare(strict_types=1);

namespace Tests\Src\EjemplosDoblesPrueba;

use Hamcrest\Matchers;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use stdClass;

class MiClase
{
    public function miMetodo($arg1 = null, $arg2 = null)
    {
        // Método que será mockeado
    }
}

class MiClaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected $mock;

    public function setUp(): void
    {
        $this->mock = Mockery::mock(MiClase::class);
    }

    public function test_con_with()
    {
        $this->mock->shouldReceive('miMetodo')->once()->with(1, 2);
        // $this->mock->shouldReceive('miMetodo')->once()->withArgs([1, 2]);    // Equivalente

        // Pasaría
        $this->mock->miMetodo(1, 2);

        // Fallaría
        $this->mock->miMetodo(1, 3);

        // También pasaría porque with y withArgs no aplican validación de tipo, salvo en objetos
        $this->mock->miMetodo('1', 2);
        $this->mock->miMetodo(true, 2);

        $this->mock->shouldReceive('miMetodo')
            ->once()
            ->with(1, 2)
            ->with(Mockery::type('int'), Mockery::type('int'));
    }

    public function test_with_con_validacion_estricta_de_objeto()
    {
        $obj1 = new stdClass();
        $obj1->property = 'value1';

        $obj2 = new stdClass();
        $obj2->property = 'value1';

        // Aquí se usará validación estricta de objeto
        $this->mock->shouldReceive('miMetodo')->once()->with($obj1);

        // Esto pasará
        $this->mock->miMetodo($obj1);

        // Esto fallará, porque aunque $obj2 tenga las mismas propiedades y valores,
        // no es estrictamente el mismo objeto (referencia diferente)
        $this->mock->miMetodo($obj2);
    }

    public function test_with_con_validacion_laxa_de_objeto()
    {
        $obj1 = new stdClass();
        $obj1->property = 'value1';

        $obj2 = new stdClass();
        $obj2->property = 'value1';

        // Usaremos Hamcrest\Matchers::equalTo para una validación que no tenga en cuenta la referencia
        $this->mock->shouldReceive('miMetodo')->once()->with(Matchers::equalTo($obj1));

        // Esto pasará porque, aunque $obj2 no sea estrictamente el mismo objeto,
        // es "igual" según la comparación de Hamcrest
        $this->mock->miMetodo($obj2);
    }

    public function test_with_any()
    {
        // Se espera que 'miMetodo' sea llamado con cualquier argumento
        $this->mock->shouldReceive('miMetodo')->once()->with(Mockery::any());

        // Todo esto pasaría
        $this->mock->miMetodo(123);
        // $this->mock->miMetodo('cualquier cosa');
        // $this->mock->miMetodo(null);
    }

    public function test_withNoArgs()
    {
        // Se espera que 'miMetodo' sea llamado sin argumentos
        $this->mock->shouldReceive('miMetodo')->once()->withNoArgs();

        // Pasaría
        $this->mock->miMetodo();

        // Fallaría
        $this->mock->miMetodo(1);
    }

    public function test_with_mockery_on_para_condiciones_personalizadas_con_un_closure()
    {
        // Se espera que 'miMetodo' reciba un argumento mayor que 0
        $this->mock->shouldReceive('miMetodo')
        ->once()
        ->with(Mockery::on(function ($argumento1) {
            return $argumento1 > 0;
        }));

        // Pasaría
        $this->mock->miMetodo(5);

        // Fallaría
        $this->mock->miMetodo(0);
    }

    public function test_with_args_para_condiciones_personalizadas_con_un_closure()
    {
        // Se espera que 'miMetodo' reciba dos argumentos, el primero mayor que 0 y el segundo menor que 10
        $this->mock->shouldReceive('miMetodo')
           ->once()
           ->withArgs(function ($argumento1, $argumento2) {
               return $argumento1 > 0 && $argumento2 < 10;
           });

        // Pasaría
        $this->mock->miMetodo(1, 5);

        // Fallaría
        $this->mock->miMetodo(0, 5);
        $this->mock->miMetodo(1, 15);
    }

    public function test_with_mockery_pattern()
    {
        // Se espera que 'miMetodo' reciba un argumento que coincida con un patrón regex
        $this->mock->shouldReceive('miMetodo')
        ->once()
        ->with(Mockery::pattern('/^patron$/'));

        // Pasaría
        $this->mock->miMetodo('patron');

        // Fallaría
        $this->mock->miMetodo('otro valor');
    }

    public function test_withSomeOfArgs()
    {
        // Se espera que 'miMetodo' reciba al menos los argumentos 1, 2 y 3
        $this->mock->shouldReceive('miMetodo')->once()->withSomeOfArgs(1, 2, 3);

        // Pasarían porque los argumentos 1, 2, 3 están presentes, aunque haya otros adicionales
        $this->mock->miMetodo(1, 2, 3, 4);
        $this->mock->miMetodo(1, 2, 3);

        // Fallaría porque falta uno de los argumentos indicados (en este caso, falta 1)
        $this->mock->miMetodo(2, 3);
    }

    public function test_with_mockery_anyOf()
    {
        // Se espera que 'miMetodo' reciba un argumento que coincida con alguno de los valores 1, 2 o 3
        $this->mock->shouldReceive('miMetodo')->once()->with(Mockery::anyOf(1, 2, 3));

        // Pasaría
        $this->mock->miMetodo(1);
        $this->mock->miMetodo(3);

        // Fallaría
        $this->mock->miMetodo(4);
    }

    public function test_with_mockery_not()
    {
        // Se espera que 'miMetodo' reciba un argumento que no sea 1
        $this->mock->shouldReceive('miMetodo')->once()->with(Mockery::not(1));

        // Pasaría
        $this->mock->miMetodo(2);

        // Fallaría
        $this->mock->miMetodo(1);
    }

    public function test_with_mockery_notAnyOf()
    {
        // Se espera que 'miMetodo' reciba un argumento que no coincida con 1, 2 o 3
        $this->mock->shouldReceive('miMetodo')->once()->with(Mockery::notAnyOf(1, 2, 3));

        // Pasaría
        $this->mock->miMetodo(4);

        // Fallaría
        $this->mock->miMetodo(1);
    }

    public function test_with_mockery_ducktype()
    {
        // Valida que el objeto pasado tenga los métodos 'metodo1' y 'metodo2', sin importar su clase
        $this->mock->shouldReceive('miMetodo')->once()->with(Mockery::ducktype('metodo1', 'metodo2'));

        $objeto = new class {
            public function metodo1()
            {
            }
            public function metodo2()
            {
            }
        };

        // Pasaría
        $this->mock->miMetodo($objeto);

        // Fallaría
        $objetoInvalido = new class {
            public function metodo1()
            {
            }
        };
        $this->mock->miMetodo($objetoInvalido);
    }

    public function test_with_mockery_subset()
    {
        // Se espera que 'miMetodo' reciba un array que contenga las claves y valores indicados
        $this->mock->shouldReceive('miMetodo')->once()->with(Mockery::subset(['clave1' => 'valor1', 'clave2' => 'valor2']));

        // Pasaría
        $this->mock->miMetodo(['clave1' => 'valor1', 'clave2' => 'valor2', 'clave3' => 'valor3']);

        // Fallaría
        $this->mock->miMetodo(['clave1' => 'valor1']);
    }

    public function test_with_mockery_contains()
    {
        // Se espera que 'miMetodo' reciba un array que contenga los valores indicados
        $this->mock->shouldReceive('miMetodo')->once()->with(Mockery::contains('valor1', 'valor2'));

        // Pasaría
        $this->mock->miMetodo(['valor1', 'valor2', 'valor3']);

        // Fallaría
        $this->mock->miMetodo(['valor1', 'valor3']);
    }

    public function test_with_mockery_has_key()
    {
        // Se espera que 'miMetodo' reciba un array que contenga la clave indicada
        $this->mock->shouldReceive('miMetodo')->once()->with(Mockery::hasKey('clave'));

        // Pasaría
        $this->mock->miMetodo(['clave' => 'valor', 'otra_clave' => 'otro_valor']);

        // Fallaría
        $this->mock->miMetodo(['otra_clave' => 'otro_valor']);
    }

    public function test_with_mockery_hasValue()
    {
        // Se espera que 'miMetodo' reciba un array que contenga el valor indicado
        $this->mock->shouldReceive('miMetodo')->once()->with(Mockery::hasValue('valor'));

        // Pasaría
        $this->mock->miMetodo(['clave' => 'valor', 'otra_clave' => 'otro_valor']);

        // Fallaría
        $this->mock->miMetodo(['clave' => 'otro_valor']);
    }

    public function test_andReturn()
    {
        // Se espera que 'miMetodo' devuelva 'valor' siempre que sea llamado
        $this->mock->shouldReceive('miMetodo')->andReturn('valor');
        // $this->mock->shouldReceive('miMetodo')->andReturns('valor');     // Equivalente

        // Pasaría
        $this->assertEquals('valor', $this->mock->miMetodo());
    }

    public function test_andReturn_multiples_valores()
    {
        // Se espera que 'miMetodo' devuelva 'valor1', 'valor2' y 'valor3' en la primera,
        // segunda y tercera llamada, respectivamente
        $this->mock->shouldReceive('miMetodo')->andReturn('valor1', 'valor2', 'valor3');
        // $this->mock->shouldReceive('miMetodo')->andReturnValues(['valor1', 'valor2', 'valor3']);    // Equivalente

        // Pasarían
        $this->assertEquals('valor1', $this->mock->miMetodo());
        $this->assertEquals('valor2', $this->mock->miMetodo());
        $this->assertEquals('valor3', $this->mock->miMetodo());
    }

    public function test_andReturn_multiples_valores_siguientes_el_mismo()
    {
        // En caso de que se hagan más llamadas de las esperadas, se devolverá el último valor
        // Se espera que 'miMetodo' devuelva 'valor1', en la primera llamada, y 'valor2' en las siguientes
        $this->mock->shouldReceive('miMetodo')->andReturn('valor1', 'valor2');

        // Pasarían
        $this->assertEquals('valor1', $this->mock->miMetodo());
        $this->assertEquals('valor2', $this->mock->miMetodo());
        $this->assertEquals('valor2', $this->mock->miMetodo());
    }

    public function test_andReturnUsing()
    {
        // Se espera que el valor de retorno sea calculado con un callback
        $this->mock->shouldReceive('miMetodo')->andReturnUsing(function ($argumento) {
            return $argumento * 2;
        });

        // Pasaría
        $this->assertEquals(10, $this->mock->miMetodo(5));
    }

    public function test_andReturnArg()
    {
        // Se espera que el método devuelva el argumento en el índice 1
        $this->mock->shouldReceive('miMetodo')->andReturnArg(1);

        // Pasaría
        $this->assertEquals('valor2', $this->mock->miMetodo('valor1', 'valor2'));
    }

    public function test_andReturnSelf()
    {
        // Se espera que devuelva la instancia de la clase mockeada
        $this->mock->shouldReceive('miMetodo')->andReturnSelf();

        // Pasaría
        $this->assertSame($this->mock, $this->mock->miMetodo());
    }

    public function test_andReturnNull()
    {
        // Se espera que devuelva null
        $this->mock->shouldReceive('miMetodo')->andReturnNull();
        // $this->mock->shouldReceive('miMetodo')->andReturn(null);    // Equivalente

        // Pasaría
        $this->assertNull($this->mock->miMetodo());
    }

    public function test_andReturnTrue()
    {
        // Se espera que devuelva true
        $this->mock->shouldReceive('miMetodo')->andReturnTrue();
        // $this->mock->shouldReceive('miMetodo')->andReturn(true);    // Equivalente

        // Pasaría
        $this->assertTrue($this->mock->miMetodo());
    }

    public function test_andReturnFalse()
    {
        // Se espera que devuelva false
        $this->mock->shouldReceive('miMetodo')->andReturnFalse();
        // $this->mock->shouldReceive('miMetodo')->andReturn(false);    // Equivalente

        // Pasaría
        $this->assertFalse($this->mock->miMetodo());
    }
}
