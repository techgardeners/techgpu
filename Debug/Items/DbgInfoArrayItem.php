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

use TechG\TechGPU\Misc\TgArrayUtil;

use TechG\TechGPU\Debug\Interfaces\DbgInfoItemInterface;
use TechG\TechGPU\Debug\Items\DbgInfoItem;

/**
* Rappresenta un elemento di Info Debug di tipo Array
*/
class DbgInfoArrayItem extends DbgInfoItem implements \IteratorAggregate, \ArrayAccess
{ 
    
    public function __construct($name = null, array $value = array(), array $parameters = array())
    {
        parent::__construct($name, $value, self::TYPE_ARRAY, $parameters);
    }   
    
    public function add($name, $value)
    {
        
        $this->value[$name] = $value;
        
        return $this;
    } 

    public function addGeneric($name, $value, $type, array $parameters = array())
    {
        
        $this->value[$name] = new DbgInfoItem($name, $value, $type, $parameters);
        
        return $this;
    } 
    
    
// *********************************************************************************    
// RENDERING FUNCTIONS
// *********************************************************************************    
    
    // Renderizza l'oggetto sotto forma di tabella html    
    public function renderValue(array $parameters = array()) { 
        
        $param = array(
            'render_type' => 'table',
        );
        
        $p = array_replace_recursive($param, $this->parameters, $parameters);
        
        switch($p['render_type']) {
            
            case 'table':
                  return TgArrayUtil::makeTable($this, 0);
                              
            case 'list':
                  return TgArrayUtil::makeList($this, 0);            
                  
            default:
                  return TgArrayUtil::makeTable($this, 0);
        }
    }   
    
// *********************************************************************************    
// *********************************************************************************    
// *********************************************************************************    
    
    public function getIterator() 
    {
        
        $objArray = array();
        foreach ($this->value as $item) {
            $objArray[$item->name] = $item;    
        }
        
        return new \ArrayIterator($objArray);
    }    
     
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->value[] = $value;
        } else {
            $this->value[$offset] = $value;
        }
    }
    public function offsetExists($offset) 
    {
        return isset($this->value[$offset]);
    }
    public function offsetUnset($offset) 
    {
        unset($this->value[$offset]);
    }
    public function offsetGet($offset) 
    {
        return isset($this->value[$offset]) ? $this->value[$offset] : null;
    }    
    
}