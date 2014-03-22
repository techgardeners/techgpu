<?php
/*
 * This file is part of the Tech Gardeners PHP Utility Project
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\TechGPU\Debug\Items;


use TechG\TechGPU\Debug\Interfaces\DbgInfoItemInterface;

/**
* Rappresenta un elemento di Info Debug generico
*/
class DbgInfoItem extends DbgInfoItemInterface
{ 
    
    const TYPE_GENERIC = 'generic';
    const TYPE_STRING = 'string';
    const TYPE_NUMERIC = 'numeric';
    const TYPE_MONEY = 'money';
    const TYPE_MEMSIZE = 'memsize';
    const TYPE_ARRAY = 'array';
    const TYPE_SQL = 'sql';
       
    public function __construct($name, $value, $type, array $parameters = array())
    {
        $this->name = $name;
        $this->value = $value;
        $this->type = $type;
        $this->parameters = $parameters;
    }    
    
    public function renderValue(array $parameters = array()) { 
        
        // TODO: to implements
        switch ($this->type) {
            
            case self::TYPE_NUMERIC:
            case self::TYPE_MONEY:
            case self::TYPE_SQL:
            
            default: 
                return $this->value;
        }    
        
        return $this->value; 
    
    }
    
}