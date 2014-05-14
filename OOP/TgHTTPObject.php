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
    public $conn;        
   
    function __construct($parameters=array(), $dependencies=array())
    {        
        parent::__construct($parameters, $dependencies);
    }    
    
// ****************************************************************************************************    
// Overrides of parent methods    
// ****************************************************************************************************
    
    /**
    * Get Dafault Parameters of Object
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
    
    protected function buildDependenciesInfo()
    {        
        parent::buildDependenciesInfo();
        

        $this->addDependencyInfo(self::DEP_REQUEST, array(
                                                        self::DP_INFO_PROPERTY => 'request',
                                                        self::DP_INFO_REQUIRED => true,  
                                                        self::DP_INFO_CLASS => 'Symfony\Component\HttpFoundation\Request',
                                                        self::DP_INFO_INIT_FUNC => '_initRequest',
                                                        ));

        $this->addDependencyInfo(self::DEP_CONN, array(
                                                        self::DP_INFO_PROPERTY => 'conn',
                                                        self::DP_INFO_REQUIRED => false,        
                                                        self::DP_INFO_INIT_FUNC => '_initConnection',
                                                        ));


    }      
    
    protected function init()
    {
        $this->hydrateObjParamsFromRequest();
        
        parent::init();        
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
            
    protected function _initRequest($request=null, $parameters=array())
    {
        
        if ($request) { $this->request = $request; }
        if (!$this->request) { $this->request = Request::createFromGlobals(); }

        // From here request is set
        /*        
        if(array_key_exists('request_var', $dependencies)) {
            $request_var = $dependencies['request_var'];
            
            if(array_key_exists('_get', $request_var)) { $this->request->query->replace($request_var['_get']); }                
            if(array_key_exists('_post', $request_var)) { $this->request->request->replace($request_var['_get']); }                
            if(array_key_exists('_files', $request_var)) { $this->request->files->replace($request_var['_files']); }                
            if(array_key_exists('_cookie', $request_var)) { $this->request->cookies->replace($request_var['_cookie']); }                
            if(array_key_exists('_server', $request_var)) { $this->request->server->replace($request_var['_server']); }                
        
        } 
        */       
    }                

    protected function _initConnection($conn=null, $parameters=array())
    {
        
        if ($conn) { $this->conn = $conn; }
        
    }        
    
// ****************************************************************************************************    
// Implementation of BaseObjectRequestedInterfaces    
// ****************************************************************************************************    
 
    public function getRequest()
    {
        return $this->request;
    }
 
    public function addRequest(Request $request)
    {
        $this->addDependency(self::DEP_REQUEST, $request);
    }
 
    protected function hydrateObjParamsFromRequest()
    {        
        
        $request = $this->getRequest();
        
        if (!$request) 
            return false;
        
        // Hydrate config parameters
        $paramToSync = $this->getObjParamsRequestMap();
        
        foreach ($paramToSync as $param => $paramPath) {
            
            if ($value = $request->query->get($param)) {
                
                $this->objParams->set($paramPath, $value);
            } 
            
            if ($value = $request->request->get($param)) {
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
 
    public function addConnection($connection)
    {
        $this->addDependency(self::DEP_CONN, $connection);
    }
 
 
}