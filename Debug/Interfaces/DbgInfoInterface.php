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

use TechG\TechGPU\Debug\Items\DbgInfoArrayItem;

/**
* Rappresenta un Aggregato di di Info Debug Item
*/
abstract class DbgInfoInterface
{ 
    
    public $name;
    public $value;

    public function __construct()
    {
        
        $this->value = new DbgInfoArrayItem();
    }    
    
    public function __toString(){
        return (string) $this->render();
    }    
    
    public abstract function render();
    
    public function renderRaw( ) { return var_dump($this); }
    
}