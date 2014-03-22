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


use TechG\TechGPU\Debug\Items\DbgInfoItem;

/**
* Rappresenta un elemento di Info Debug di tipo sql
*/
class DbgInfoSqlItem extends DbgInfoItem
{ 
    
    public function __construct($name, $sql)
    {
        parent::__construct($name, $sql, self::TYPE_SQL);
        
    }    
    
}