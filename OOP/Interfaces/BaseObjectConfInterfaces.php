<?php
/*
 * This file is part of the Tech Gardeners PHP Utility Project
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\TechGPU\OOP\Interfaces;

use TechG\TechGPU\OOP\Interfaces\BaseObjectInterfaces;

/**
* 
*/
interface BaseObjectConfInterfaces extends BaseObjectInterfaces
{  
    
    
    public function getObjParams($parameters=array());
    public function getObjParam($path, $default = null, $deep = true, $sep = '/', $deepLevel = 0); 
    public function addObjParams($parameters=array());    
    
}