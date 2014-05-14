<?php
/*
 * This file is part of the Tech Gardeners PHP Utility Project
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\TechGPU\OOP\Base;

use TechG\TechGPU\OOP\Interfaces\BaseObjectConfInterfaces;
use TechG\TechGPU\OOP\Interfaces\BaseObjectDependenciesInterfaces;
use TechG\TechGPU\OOP\Interfaces\BaseObjectTimeableInterfaces;

use TechG\TechGPU\Misc\TgParameterBag;

use Symfony\Component\Stopwatch\Stopwatch;

/**
* 
*/
class BaseObject implements BaseObjectConfInterfaces, BaseObjectDependenciesInterfaces, BaseObjectTimeableInterfaces 
{      
    /**
     * Parameters storage.
     *
     * @var array
     */
    protected $objParams;
    protected $dependenciesInfo;
    
    protected $watch;
    
    function __construct($parameters=array(), $dependencies=array())
    {        
        
        // Set Paramns
        $this->objParams = new TgParameterBag($this->getDefObjParams());
        $this->addObjParams($parameters);
        
        // Set dependencies  
        $this->buildDependenciesInfo();        
        $this->initDependencies($dependencies);

        $this->watch->start('init'.get_class($this) , 'core');

        $this->init(); 
        
        $this->watch->stop('init'.get_class($this));                           
    }

// ****************************************************************************************************    
// Init functions   
// ****************************************************************************************************    
    
    protected function initDependencies($dependencies = array())
    { 
        $depInfos = $this->getDependenciesInfo();
        
        if (!$depInfos || !is_array($depInfos) || !count($depInfos) > 0) 
            return false;
        
        foreach ($depInfos as $depName => $depInfo) {
            
            $dependency = (array_key_exists($depName,$dependencies)) ? $dependencies[$depName] : null;
            $this->addDependency($depName, $dependency, array());
            
        }               
    }

        protected function _initWatch($watch=null, $parameters=array())
        {
            
            if ($watch) { $this->watch = $watch; }
            if (!$this->watch) { $this->watch = new Stopwatch(); }
         
        }    
            
// ****************************************************************************************************    
// Methods for childrens overrides
// ****************************************************************************************************    

    protected function getDefObjParams($parameters=array())
    {
        $defParams = array();
                 
        return array_replace_recursive($defParams, $parameters);
    }   

    protected function buildDependenciesInfo() 
    {  
        $this->addDependencyInfo(self::DEP_WATCH, array(
                                                        self::DP_INFO_PROPERTY => 'watch',
                                                        self::DP_INFO_REQUIRED => false,        
                                                        self::DP_INFO_INIT_FUNC => '_initWatch',
                                                        ));    
             
    } 
        
    protected function init()
    {   
    }
    
// ****************************************************************************************************    
// Implementation of BaseObjectConfInterfaces    
// ****************************************************************************************************    

    public function getObjParams($parameters=array())
    {
        
        return array_replace_recursive($this->objParams->all(), $parameters);
    }

    public function getObjParam($path, $default = null, $deep = true, $sep = '/', $deepLevel = 0)
    {
        
        return $this->objParams->get($path, $default, $deep, $sep, $deepLevel);
    }
    
    public function addObjParams($parameters=array())
    {
        // TODO: Implementare meccanismo che idrati SOLO le configurazioni presenti (con flag)
        $this->objParams->add($parameters);
    }    

// ****************************************************************************************************    
// Implementation of BaseObjectTimeableInterfaces    
// ****************************************************************************************************    

    public function getStopwatch($parameters=array())
    {
        return $this->watch;
    }
 
    public function addStopwatch($watch, $parameters=array())
    {
        $this->addDependency(self::DEP_WATCH, $watch, $parameters);
    }
    
// ****************************************************************************************************    
// Implementation of BaseObjectDependenciesInterfaces    
// ****************************************************************************************************    
    
    public function getDependenciesInfo($name = null, $property=false)
    {
        return ($name) ? $this->getDependencyInfo($name, $property) : $this->dependenciesInfo;    
    }

    public function getDependencyInfo($name, $property=false)
    {
        if (!array_key_exists($name, $this->dependenciesInfo)) return false;
        
        $info = $this->dependenciesInfo[$name];
        
        if (!$property) {
            return $this->dependenciesInfo[$name];   
        } else {
            return (array_key_exists($property, $this->dependenciesInfo[$name])) ? $this->dependenciesInfo[$name][$property] : false;
        }
        
        return null;    
    }

    public function addDependencyInfo($name, $dependencyInfo = array())
    {
        $this->dependenciesInfo[$name] = (isset($this->dependenciesInfo[$name])) ? array_replace_recursive($this->dependenciesInfo[$name], $dependencyInfo) : $dependencyInfo;
    }       
    
    public function replaceDependencyInfo($name, $dependencyInfo = array())
    {
        $this->dependenciesInfo[$name] = $dependencyInfo;
    }       
        
    public function removeDependencyInfo($name)
    {
        unset($this->dependenciesInfo[$name]);
    }    
    
    
    public function getDependenciesKey()
    {
        return array_keys($this->dependenciesInfo);
    }
    
    public function getRequiredDependenciesKey()
    {
        $depsKeys = $this->getDependenciesKey();
        
        $dependeciesKey = array();
        foreach($depsKeys as $dName) {
            if ($this->getDependencyInfo($dName, self::DP_INFO_REQUIRED) === true)
                $dependeciesKey[] = $dName;    
        }

        return $dependeciesKey;
    }
    
    
    public function getDependencies($names = null)
    {
        $dependecies = array();
        
        if (!$names)
            $names = array_keys($this->getDependenciesInfo());
        
        foreach($names as $depKey) {
            if($dependency = $this->getDependency($depKey)) {
                $dependecies[$depKey] = $dependency;
            }
        }
        
        return $dependecies;        
    }
    
    public function getDependency($name)
    {
        $property = $this->getDependencyInfo($name, self::DP_INFO_PROPERTY);
        
        return ($property) ? $this->$property : null; 
    }
    
    public function addDependency($name, $dependency, $parameters=array())
    {
        $property = $this->getDependencyInfo($name, self::DP_INFO_PROPERTY);
        $initFunc = $this->getDependencyInfo($name, self::DP_INFO_INIT_FUNC);
        
        if($property) { $this->$property = $dependency; }
        if($initFunc) { $this->$initFunc($dependency, $parameters); }
        
        //TODO: Inject new dependency to other componedn linked
                 
    }
   
    
}