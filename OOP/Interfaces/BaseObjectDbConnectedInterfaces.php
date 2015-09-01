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
interface BaseObjectDbConnectedInterfaces extends BaseObjectInterfaces
{  
    
    public function getConnection();
    public function setConnection($connection);
 
}