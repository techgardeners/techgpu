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
interface BaseObjectDependenciesInterfaces extends BaseObjectInterfaces
{  
    
    const DEP_REQUEST = 'request';
    const DEP_CONN = 'conn';
    
    const DP_INFO_PROPERTY = 'property';
    const DP_INFO_REQUIRED = 'required';
    const DP_INFO_CLASS = 'class';
    const DP_INFO_INIT_FUNC = 'init_f';
    
    public function getDependenciesInfo($name = false, $property=false);
    public function getDependencyInfo($name, $property=false);
    public function addDependencyInfo($name, $dependencyInfo = array());
    public function replaceDependencyInfo($name, $dependencyInfo = array());
    public function removeDependencyInfo($name);
    
    public function getDependenciesKey();
    public function getRequiredDependenciesKey();
    
    public function getDependencies($names = null);
    public function getDependency($name);
    public function addDependency($name, $dependency, $parameters=array());
 
}