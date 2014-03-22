<?php
/*
 * This file is part of the Tech Gardeners PHP Utility Project
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\TechGPU\Debug\Interfaces;

/**
* Rappresenta un elemento di Info Debug
*/
abstract class DbgInfoItemInterface 
{ 
    
    public $name;
    public $value;
    public $type;
    public $parameters;
   
    
    public function __toString(){
        return (string) $this->renderValue($this->parameters);
    }
   
   
    public abstract function renderValue(array $parameters = array());
        
}