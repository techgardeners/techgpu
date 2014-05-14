<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\TechGPU\Tests\OOP\Base;

use TechG\TechGPU\OOP\Base\BaseObject;

class BaseObjectTest extends \PHPUnit_Framework_TestCase
{
    
    protected function getDefArrayParams()
    {        
        $getDefObjParams = self::getPrivateMethod('getDefObjParams');
        $baseObj = new BaseObject();

        return $getDefObjParams->invokeArgs($baseObj, array());
    }
    
    protected function getDependenciesInfo()
    {
        $retArr = array();
        
        $dependencyInfo= array(
                    'property' => 'request',
                    'required' => true,        
                    'default' => null,        
                    'getdef_f' => null,        
                    'init_f' => '_initRequest',        
                    );

       $retArr[BaseObject::DEP_REQUEST] = $dependencyInfo;
       
       return $retArr;
        
    }
    protected function getPersonalizedArrayParams()
    {        
            return array('config' => array('debug' => array(
                                                        'enable' => false,
                                                        'level' => 8,
                                                         ),
                                       ),
                         'parameter' => array('param1' => array(
                                                            'subparam3' => 45,
                                                             ),
                                           'param2' => 'value param2',                                                        
                                           'param3' => 'value param3',                                                        
                                           'param4' => 'value param4',                                                        
                                           ),    
                 );
    }
    

    /**
     * @covers TechG\TechGPU\OOP\Base\BaseObject::__construct
     */
    public function testConstructor()
    {
        $defaultParameters = $this->getDefArrayParams();
        
        $baseObj = new BaseObject($this->getPersonalizedArrayParams());
        $this->assertNotEquals($this->getDefArrayParams(), $baseObj->getObjParams());
        $this->assertEquals(array_replace_recursive($this->getDefArrayParams(),$this->getPersonalizedArrayParams()), $baseObj->getObjParams());        
        
    }    

    /**
     * @covers TechG\TechGPU\OOP\Base\BaseObject::getObjParams
     */    
    public function testGetObjParams()
    {
        $defaultParameters = $this->getDefArrayParams();
        
        $baseObj = new BaseObject();
        
        // Check that return default parameter if not modify on constructor
        $this->assertEquals($defaultParameters, $baseObj->getObjParams());

        $defWithPersParams = $baseObj->getObjParams($this->getPersonalizedArrayParams());
        
        $this->assertNotEquals($defaultParameters, $defWithPersParams);

        $this->assertEquals(array_replace_recursive($defaultParameters, $this->getPersonalizedArrayParams()), $defWithPersParams);                
    }    
       
    /**
     * @covers TechG\TechGPU\OOP\Base\BaseObject::addDependencyInfo
     */    
    public function testAddDependencyInfo()
    {
        $depReq = $this->getDependenciesInfo();
        
        $obj = new BaseObject();
        $obj->addDependencyInfo('request', $depReq);       
        
        $this->assertEquals($depReq, $obj->getDependenciesInfo('request'));
    } 
        
    /**
     * @covers TechG\TechGPU\OOP\Base\BaseObject::getDependenciesInfo
     */    
    public function testGetDependenciesInfo()
    {

        $this->testAddDependencyInfo();          
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
      $class = new \ReflectionClass('TechG\TechGPU\OOP\Base\BaseObject');
      $method = $class->getMethod($name);
      $method->setAccessible(true);
      return $method;
    }        
    
    
}
