<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\TechGPU\Tests\Misc;

use TechG\TechGPU\Misc\TgParameterBag;

class TgParameterBagTest extends \PHPUnit_Framework_TestCase
{
    
    protected function getTestValues()
    {        
        return  array('foo' => 
                    array('bar' => 
                        array('car' => 
                            array('dar' => 'ear')
                            )
                        ),
                    'null' => null,
                    );
    }
    

    /**
     * @covers Symfony\Component\HttpFoundation\TgParameterBag::__construct
     */
    public function testConstructor()
    {
        $data = $this->getTestValues();
        
        $bag = new TgParameterBag($data);
        $this->assertEquals($data, $bag->all());
    }

    /**
     * @covers Symfony\Component\HttpFoundation\TgParameterBag::all
     */


    public function testKeys()
    {
        
        $data = $this->getTestValues();
        $bag = new TgParameterBag($data);
                                    
        $this->assertEquals(array_keys($data), $bag->keys());
        $this->assertEquals(array_keys($data), $bag->keys('/'));
        $this->assertEquals(null, $bag->keys(''));
        $this->assertEquals(array_keys($data['foo']), $bag->keys('foo'));
        $this->assertEquals(array_keys($data['foo']['bar']), $bag->keys('foo/bar'));
        $this->assertEquals(null, $bag->keys('foo/bar/unknown'));
    }

    public function testAdd()
    {

        $bag = new TgParameterBag(array('foo' => 'bar'));
        $bag->add(array('bar' => 'bas'));
        $this->assertEquals(array('foo' => 'bar', 'bar' => 'bas'), $bag->all());
    }

    public function testRemove()
    {
        $bag = new TgParameterBag(array('foo' => 'bar'));
        $bag->add(array('bar' => 'bas'));
        $this->assertEquals(array('foo' => 'bar', 'bar' => 'bas'), $bag->all());
        $bag->remove('bar');
        $this->assertEquals(array('foo' => 'bar'), $bag->all());
        $bag->remove('unknown');
        $this->assertEquals(array('foo' => 'bar'), $bag->all());
    }

    /**
     * @covers Symfony\Component\HttpFoundation\TgParameterBag::replace
     */
    public function testReplace()
    {
        $bag = new TgParameterBag(array('foo' => 'bar'));

        $bag->replace(array('FOO' => 'BAR'));
        $this->assertEquals(array('FOO' => 'BAR'), $bag->all(), '->replace() replaces the input with the argument');
        $this->assertFalse($bag->has('foo'), '->replace() overrides previously set the input');
    }

    
    /**
     * @covers Symfony\Component\HttpFoundation\TgParameterBag::get
     */
    public function testGet()
    {
        $bag = new TgParameterBag(array('foo' => 'bar', 'null' => null));

        $this->assertEquals('bar', $bag->get('foo','default'), '->get() gets the value of a parameter');
        $this->assertEquals('default', $bag->get('unknown', 'default'), '->get() returns second argument as default if a parameter is not defined');
        $this->assertEquals('default', $bag->get('', 'default'), '->get() returns second argument as default if a parameter is blank');
        $this->assertNull($bag->get('null', 'default'), '->get() returns null if null is set');
    }

    public function testGetDeep()
    {
        $bag = new TgParameterBag(array('foo' => array('bar' => array('moo' => 'boo'))));

        $this->assertEquals(array('moo' => 'boo'), $bag->get('foo/bar', null, true));
        $this->assertEquals('boo', $bag->get('foo/bar/moo', null, true));
        $this->assertEquals('default', $bag->get('foo/bar/foo', 'default', true));
    }
    
    /**
     * @dataProvider getMalformedPaths
     */
    public function testGetWithMalformedPaths($path)
    {        
        $bag = new TgParameterBag(array('foo' => array('bar' => 'moo')));

        $this->assertEquals('default', $bag->get($path, 'default'));
    }    
    
    /**
     * @dataProvider getInvalidPaths
     * @expectedException \InvalidArgumentException
     */
    public function testGetWithInvalidPaths($path)
    {        
        $bag = new TgParameterBag(array('foo' => array('bar' => 'moo')));

        $bag->get($path, 'default');
    }    
    
        public function getMalformedPaths()
        {
            return array(
                array('foo[bar'),
                array('foo/bar/'),
                array(''),
                array('!"£$%&/()=?^|+èé{}{}@#ù§,;.:-_ì'),
            );
        }
    
        public function getInvalidPaths()
        {
            return array(
                array(null),
                array(array()),
                array(23),
                array(false),
            );
        }
    
    public function testGetUseDeepByDefault()
    {
        $bag = new TgParameterBag(array('foo' => array('bar' => 'moo')));

        $this->assertEquals('moo', $bag->get('foo/bar'));
    }

    public function testGetDisablingDeepByDefault()
    {
        $bag = new TgParameterBag(array('foo' => array('bar' => 'moo')));

        $this->assertNotEquals('moo', $bag->get('foo/bar', null, false));
    }

    public function testGetWithOtherSep()
    {
        $bag = new TgParameterBag(array('foo' => array('bar' => 'moo')));

        $this->assertEquals('moo', $bag->get('foo%bar', null, true, '%'));
        $this->assertNotEquals('moo', $bag->get('foo/bar', null, true, '%'));
    }

    public function testGetDeepLevel()
    {
        $bag = new TgParameterBag(array('foo' => 
                                    array('bar' => 
                                        array('car' => 
                                            array('dar' => 'ear')))
                                    ));

        $this->assertEquals('ear', $bag->get('foo/bar/car/dar'));
        $this->assertEquals('ear', $bag->get('foo/bar/car/dar', 'default', true, '/', TgParameterBag::DEEP_LEAF));
        $this->assertEquals(array('dar' => 'ear'), $bag->get('foo/bar/car/dar', 'default', true, '/', TgParameterBag::DEEP_PARENT));
        $this->assertEquals(array('car' => array('dar' => 'ear')), $bag->get('foo/bar/car/dar', 'default', true, '/', 2));
        $this->assertEquals(array('bar' => array('car' => array('dar' => 'ear'))), $bag->get('foo/bar/car/dar', 'default', true, '/', 3));
        $this->assertEquals(array('foo' => array('bar' => array('car' => array('dar' => 'ear')))), $bag->get('foo/bar/car/dar', 'default', true, '/', 4));
        $this->assertEquals('default', $bag->get('foo/bar/car/dar', 'default', true, '/', 5));
    }



    /**
     * @covers Symfony\Component\HttpFoundation\TgParameterBag::set
     */
    public function testSet()
    {
        $bag = new TgParameterBag(array());

        $bag->set('foo', 'bar');
        $this->assertEquals('bar', $bag->get('foo'), '->set() sets the value of parameter');

        $bag->set('foo', 'baz');
        $this->assertEquals('baz', $bag->get('foo'), '->set() overrides previously set parameter');
    }

    /**
     * @covers Symfony\Component\HttpFoundation\TgParameterBag::has
     */
    public function testHas()
    {
        $bag = new TgParameterBag(array( 
                                    'foo' => array('bar' => array('car' => array('dar' => 'ear'))),
                                    ));

        $this->assertTrue($bag->has('/'), '->has() returns true if a parameter is defined');
        $this->assertTrue($bag->has('foo'), '->has() returns true if a parameter is defined');
        $this->assertTrue($bag->has('foo/bar'), '->has() returns true if a parameter is defined');
        $this->assertTrue($bag->has('foo/bar/car'), '->has() returns true if a parameter is defined');
        $this->assertTrue($bag->has('foo/bar/car/dar'), '->has() returns true if a parameter is defined');
        $this->assertFalse($bag->has('foo/bar/car/dar/ear'), '->has() returns false if a parameter is not defined');
        $this->assertFalse($bag->has('unknown'), '->has() return false if a parameter is not defined');
        $this->assertFalse($bag->has(''), '->has() return false if a parameter is not defined');
    }

    /**
     * @covers Symfony\Component\HttpFoundation\TgParameterBag::getAlpha
     */
    public function testGetAlpha()
    {
        $bag = new TgParameterBag(array('word' => 'foo_BAR_012'));

        $this->assertEquals('fooBAR', $bag->getAlpha('word'), '->getAlpha() gets only alphabetic characters');
        $this->assertEquals('', $bag->getAlpha('unknown'), '->getAlpha() returns empty string if a parameter is not defined');
    }

    /**
     * @covers Symfony\Component\HttpFoundation\TgParameterBag::getAlnum
     */
    public function testGetAlnum()
    {
        $bag = new TgParameterBag(array('word' => 'foo_BAR_012'));

        $this->assertEquals('fooBAR012', $bag->getAlnum('word'), '->getAlnum() gets only alphanumeric characters');
        $this->assertEquals('', $bag->getAlnum('unknown'), '->getAlnum() returns empty string if a parameter is not defined');
    }

    /**
     * @covers Symfony\Component\HttpFoundation\TgParameterBag::getDigits
     */
    public function testGetDigits()
    {
        $bag = new TgParameterBag(array('word' => 'foo_BAR_012'));

        $this->assertEquals('012', $bag->getDigits('word'), '->getDigits() gets only digits as string');
        $this->assertEquals('', $bag->getDigits('unknown'), '->getDigits() returns empty string if a parameter is not defined');
    }

    /**
     * @covers Symfony\Component\HttpFoundation\TgParameterBag::getInt
     */
    public function testGetInt()
    {
        $bag = new TgParameterBag(array('digits' => '0123'));

        $this->assertEquals(123, $bag->getInt('digits'), '->getInt() gets a value of parameter as integer');
        $this->assertEquals(0, $bag->getInt('unknown'), '->getInt() returns zero if a parameter is not defined');
    }

    /**
     * @covers Symfony\Component\HttpFoundation\TgParameterBag::filter
     */
    public function testFilter()
    {
        $bag = new TgParameterBag(array(
            'digits' => '0123ab',
            'email' => 'example@example.com',
            'url' => 'http://example.com/foo',
            'dec' => '256',
            'hex' => '0x100',
            'array' => array('bang'),
            ));

        $this->assertEmpty($bag->filter('nokey'), '->filter() should return empty by default if no key is found');

        $this->assertEquals('0123', $bag->filter('digits', '', false, FILTER_SANITIZE_NUMBER_INT), '->filter() gets a value of parameter as integer filtering out invalid characters');

        $this->assertEquals('example@example.com', $bag->filter('email', '', false, FILTER_VALIDATE_EMAIL), '->filter() gets a value of parameter as email');

        $this->assertEquals('http://example.com/foo', $bag->filter('url', '', false, FILTER_VALIDATE_URL, array('flags' => FILTER_FLAG_PATH_REQUIRED)), '->filter() gets a value of parameter as URL with a path');

        // This test is repeated for code-coverage
        $this->assertEquals('http://example.com/foo', $bag->filter('url', '', false, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED), '->filter() gets a value of parameter as URL with a path');

        $this->assertFalse($bag->filter('dec', '', false, FILTER_VALIDATE_INT, array(
            'flags'   => FILTER_FLAG_ALLOW_HEX,
            'options' => array('min_range' => 1, 'max_range' => 0xff))
                ), '->filter() gets a value of parameter as integer between boundaries');

        $this->assertFalse($bag->filter('hex', '', false, FILTER_VALIDATE_INT, array(
            'flags'   => FILTER_FLAG_ALLOW_HEX,
            'options' => array('min_range' => 1, 'max_range' => 0xff))
                ), '->filter() gets a value of parameter as integer between boundaries');

        $this->assertEquals(array('bang'), $bag->filter('array', '', false), '->filter() gets a value of parameter as an array');

    }


    /**
     * @covers Symfony\Component\HttpFoundation\TgParameterBag::convertPathToArrayStyle
     */
    public function testConvertPathToArrayStyle()
    {

        $bag = new TgParameterBag(array());

        $this->assertEquals("", $bag->convertPathToArrayStyle());
        $this->assertEquals("", $bag->convertPathToArrayStyle(''));
        $this->assertEquals("['foo']", $bag->convertPathToArrayStyle('foo'));
        $this->assertEquals("['foo']['baa']", $bag->convertPathToArrayStyle('foo/baa'));
    }
}
