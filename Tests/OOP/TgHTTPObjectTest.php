<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\TechGPU\Tests\OOP;

use TechG\TechGPU\OOP\TgHTTPObject;

use Symfony\Component\HttpFoundation\Request;

class TgHTTPObjectTest extends \PHPUnit_Framework_TestCase
{
    
    protected function getDefArrayParams()
    {        
        $function = self::getPrivateMethod('getDefObjParams');
        $obj = new TgHTTPObject();

        return $function->invokeArgs($obj, array());
    }
    
    protected function getRequestParamsMap()
    {        
        $function = self::getPrivateMethod('getObjParamsRequestMap');
        $obj = new TgHTTPObject();

        return $function->invokeArgs($obj, array());
    }
    
    /**
     * @covers TechG\TechGPU\OOP\TgHTTPObject::__construct
     */
    public function testConstructor()
    {

        $request = Request::createFromGlobals();        
        
        // Test default construction
        $tgHttpObj = new TgHTTPObject();
        $this->assertEquals($request, $tgHttpObj->getRequest());
        
        // Test inject request dependency on construction
        $modifiedRequest = Request::createFromGlobals();
        $modifiedRequest->query->set('testparam','testvalue');
        
        $tgHttpObj = new TgHTTPObject($modifiedRequest);
        $this->assertNotEquals($request, $tgHttpObj->getRequest());
        $this->assertEquals($modifiedRequest, $tgHttpObj->getRequest());
                
    }    
    
    /**
     * @covers TechG\TechGPU\OOP\TgHTTPObject::setRequest
     */
    public function testSetRequest()
    {
        $request = Request::createFromGlobals();
        $modifiedRequest = Request::createFromGlobals();
        $modifiedRequest->query->set('testparam','testvalue');
        
        $tgHttpObj = new TgHTTPObject();
        $tgHttpObj->setRequest($modifiedRequest);
        $this->assertNotEquals($request, $tgHttpObj->getRequest());
        $this->assertEquals($modifiedRequest, $tgHttpObj->getRequest());
                
    }    
    
    /**
     * @covers TechG\TechGPU\OOP\TgHTTPObject::hydrateObjParamsFromRequest
     */
    public function testHydrateObjParamsFromRequest()
    {
        $request = Request::createFromGlobals();
        $paramsMap = $this->getRequestParamsMap();
        
        foreach ($paramsMap as $reqParam => $paramPath) {
            $request->query->set($reqParam,'testvalue');    
        }
        
        $tgHttpObj = new TgHTTPObject($request);
        
        foreach ($paramsMap as $reqParam => $paramPath) {
            $this->assertEquals('testvalue', $tgHttpObj->getObjParam($paramPath));       
        }    
                    
    }    
    
// *********************************************************************************************************************************************************    
// *********************************************************************************************************************************************************    
// *********************************************************************************************************************************************************
    
    /**
    * For Unit Test purpose
    * 
    * @param mixed $name
    * @return \ReflectionMethod
    */
    protected static function getPrivateMethod($name) {
      $class = new \ReflectionClass('TechG\TechGPU\OOP\TgHTTPObject');
      $method = $class->getMethod($name);
      $method->setAccessible(true);
      return $method;
    }        
    
    
}
