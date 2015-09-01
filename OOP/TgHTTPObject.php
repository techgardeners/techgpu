<?php
/*
 * This file is part of the Tech Gardeners PHP Utility Project
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\TechGPU\OOP;

use TechG\TechGPU\OOP\Base\BaseObject;
use TechG\TechGPU\OOP\Interfaces\BaseObjectRequestedInterfaces;
use TechG\TechGPU\OOP\Interfaces\BaseObjectDbConnectedInterfaces;

use Symfony\Component\HttpFoundation\Request;

class TgHTTPObject extends BaseObject implements BaseObjectRequestedInterfaces, BaseObjectDbConnectedInterfaces
{  
        
    protected $request;
    protected $conn;        
   
    function __construct(Request $request = null, $conn = null, $parameters = array())
    {                
        $this->request = $request;
        $this->conn = $conn;

        parent::__construct($parameters);
    }    
    
// ****************************************************************************************************    
// Overrides of parent methods    
// ****************************************************************************************************
    
    /**
    * Get Default Parameters of Object
    * 
    * @param mixed $parameters
    */
    protected function getDefObjParams($parameters=array())
    {
        
        $defParams = array('config' => array('debug' => array(
                                                        'enable' => true,
                                                        'level' => 3,
                                                         )),
                         );
                                   
        return array_replace_recursive(parent::getDefObjParams(), $defParams, $parameters);            
    }            
    
    protected function init()
    {
        parent::init();    
        
        $this->_initRequest();
        $this->_initConnection();            
    }  
    
// ****************************************************************************************************    
// Methods for childrens overrides
// ****************************************************************************************************       

    public function getObjParamsRequestMap()
    {
        $paramnsMap = array(
            '__dbg' => 'config/debug/enable',
        );
        
        return $paramnsMap;
    }

// ****************************************************************************************************    
// Init functions   
// ****************************************************************************************************
            
    protected function _initRequest()
    {
        if (!$this->request) { $this->request = Request::createFromGlobals(); }
        
        $this->hydrateObjParamsFromRequest();
    }                

    protected function _initConnection()
    {
        
    }        
    
// ****************************************************************************************************    
// Implementation of BaseObjectRequestedInterfaces    
// ****************************************************************************************************    
 
    public function getRequest()
    {
        return $this->request;
    }
 
    public function setRequest(Request $request, $hydrateParams=true)
    {
        $this->request = $request;
        
        if ($hydrateParams) {
            $this->hydrateObjParamsFromRequest();            
        }
            
    }
 
    protected function hydrateObjParamsFromRequest()
    {        
        
        $request = $this->getRequest();
        
        if (!$request) 
            return false;
        
        // Hydrate config parameters
        $paramToSync = $this->getObjParamsRequestMap();
        
        foreach ($paramToSync as $param => $paramPath) {
            
            if (($value = $request->query->get($param)) != NULL) {
                
                $this->objParams->set($paramPath, $value);
            } 
            
            if (($value = $request->request->get($param)) != NULL) {
                $this->objParams->set($paramPath, $value);    
            }
        }
        
    } 
 
// ****************************************************************************************************    
// Implementation of BaseObjectDbConnectedInterfaces    
// ****************************************************************************************************    
 
    public function getConnection()
    {
        return $this->conn;
    }
 
    public function setConnection($connection)
    {
        $this->conn = $connection;
    }
 
 
}