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
interface BaseObjectTimeableInterfaces extends BaseObjectInterfaces
{  
    
    const DEP_WATCH = 'stopwatch';
    
    public function getStopwatch($parameters=array());
    public function addStopwatch($watch, $parameters=array());    
    
}