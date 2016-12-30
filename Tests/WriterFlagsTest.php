<?php
/**
 *
 * This file is part of the JSON Stream Project.
 *
 * @author Sergey Kolodyazhnyy <sergey.kolodyazhnyy@gmail.com>
 *
 */

namespace Bcn\Component\Json\Tests;

use Bcn\Component\Json\Writer;
use Bcn\Component\StreamWrapper\Stream;

/**
 * Class WriterFlagsTest
 * @package Bcn\Component\Json\Tests
 */
class WriterFlagsTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @param $data
     *
     * @dataProvider provideEncodeData
     */
    public function testWrite($data)
    {
        $stream = fopen("php://memory", "r+");
        $writer = new Writer($stream, JSON_PRETTY_PRINT);
        $writer->write(null, $data);

        rewind($stream);
        $encoded = stream_get_contents($stream);
        fclose($stream);

        // Assert output is equal to json_encode with JSON_PRETTY_PRINT option
        $this->assertEquals(json_encode($data, JSON_PRETTY_PRINT), $encoded);

        // Assert json_decode can properly decode the json string
        $this->assertEquals($data, json_decode($encoded, true), $encoded);
    }

    /**
     * @return array
     */
    public function provideEncodeData()
    {
        return array(
            'String'                    => array("String"),
            //'String with special chars' => array("String\"\'\\\/!@#$%^&*()!@¡™£¢"),
            'Integer'                   => array(1),
            'Decimal'                   => array(9.9991119),
            'Array'                     => array(1, 2, 3),
            'Object'                    => array('a' => 1, 'b' => 2),
            'Array of objects'          => array(array(array('a' => 1, 'b' => 2), array('a' => 1, 'b' => 2))),
            'Multilevel object'         => array(array('a' => array('a' => 1, 'b' => 2), 'b' => array('a' => 1, 'b' => 2))),
            'Object with special keys'  => array(array('¡Hola!' => 'Hello!'))
        );
    }

    /**
     * @param $items
     *
     * @dataProvider provideArrayData
     */
    public function testArrayWriting($items)
    {
        $stream = fopen("php://memory", "r+");
        $writer = new Writer($stream, JSON_PRETTY_PRINT);
        $writer->enter();
        foreach ($items as $item) {
           $writer->write(null, $item);
        }
        $writer->leave();

        rewind($stream);
        $encoded = stream_get_contents($stream);
        fclose($stream);

        $this->assertEquals(json_encode($items, JSON_PRETTY_PRINT), $encoded);

        $this->assertEquals($items, json_decode($encoded, true), $encoded);
    }

    /**
     * @return array
     */
    public function provideArrayData()
    {
        return array(
            'Array without items'       => array(array()),
            'Array with one item'       => array(array(1)),
            'Array with multiple items' => array(array(1, 2, 3)),
            'Nested array'              => array(array(array(1, 2), array(2, 3), 4))
        );
    }

    /**
     * @param $items
     *
     * @dataProvider provideObjectData
     */
    public function testObjectWriting($items)
    {
        $stream = fopen("php://memory", "r+");
        $writer = new Writer($stream, JSON_PRETTY_PRINT);
        $writer->enter(Writer::TYPE_OBJECT);
        foreach ($items as $key => $item) {
           $writer->write($key, $item);
        }
        $writer->leave();

        rewind($stream);
        $encoded = stream_get_contents($stream);
        fclose($stream);

        $this->assertEquals(json_encode($items, JSON_PRETTY_PRINT), $encoded);

        $this->assertEquals($items, json_decode($encoded, true), $encoded);
    }

    /**
     * @return array
     */
    public function provideObjectData()
    {
        return array(
            //'Empty object'               => array(array()),
            'Object with one item'       => array(array('a' => 1)),
            'Object with numeric keys'   => array(array('2' => 1, '11' => 3)),
            'Object with multiple items' => array(array('a' => 1, 'b' => 2, 'c' => 3)),
            'Nested object'              => array(array(
                    'a' => array('aa' => 1, 'ab' => 2),
                    'b' => array('ba' => 2, 'bb' => 3),
                    'c' => 4
                )
            )
        );
    }

    /**
     *
     */
    public function testBrackets()
    {
        $stream = fopen("php://memory", "r+");
        $writer = new Writer($stream, JSON_PRETTY_PRINT);
        $writer
            ->enter(Writer::TYPE_OBJECT)
                    ->enter("key", Writer::TYPE_ARRAY)
                        ->enter(Writer::TYPE_OBJECT)
                            ->enter("inner", Writer::TYPE_ARRAY)
                                ->enter()
                                ->leave()
                            ->leave()
                        ->leave()
                    ->leave()
                ->leave()
            ->leave()
        ;

        rewind($stream);
        $encoded = stream_get_contents($stream);
        fclose($stream);

        $this->assertEquals(
            array("key" => array(array('inner' => array(array())))),
            json_decode($encoded, true),
            $encoded
        );
    }

    public function testNumericCheck()
    {
        $data = [ "key" => '1' ];
        $stream = fopen("php://memory", "r+");

//print_r(JSON_PRETTY_PRINT);
//print_r(JSON_NUMERIC_CHECK);


        $writer = new Writer($stream, JSON_NUMERIC_CHECK);
        $writer->write(null, $data);

        rewind($stream);
        $encoded = stream_get_contents($stream);
        fclose($stream);

        // Assert output is equal to json_encode with JSON_PRETTY_PRINT option
        $this->assertEquals(json_encode($data, JSON_NUMERIC_CHECK), $encoded);

        // Assert json_decode can properly decode the json string
        $this->assertEquals($data, json_decode($encoded, true), $encoded);
    }


}
