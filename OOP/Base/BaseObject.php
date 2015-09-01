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
use TechG\TechGPU\OOP\Interfaces\BaseObjectTimeableInterfaces;

use TechG\TechGPU\Misc\TgParameterBag;

use Symfony\Component\Stopwatch\Stopwatch;

/**
* 
*/
class BaseObject implements BaseObjectConfInterfaces, BaseObjectTimeableInterfaces 
{      
    /**
     * Parameters storage.
     *
     * @var array
     */
    protected $objParams;
    
    protected $watch;
    
    function __construct($parameters=array())
    {        
        // Set Paramns
        $this->setObjParams($this->getDefObjParams());
        $this->addObjParams($parameters);
        
        $this->init(); 
    }

// ****************************************************************************************************    
// Init functions   
// ****************************************************************************************************    
    
            
// ****************************************************************************************************    
// Methods for childrens overrides
// ****************************************************************************************************    

    protected function getDefObjParams($parameters=array())
    {
        $defParams = array();
                 
        return array_replace_recursive($defParams, $parameters);
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
    
    public function setObjParams($parameters=array())
    {
        $this->objParams = new TgParameterBag($parameters);
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
 
    public function setStopwatch($watch, $parameters=array())
    {
        $this->watch = $watch;
    }
    
}