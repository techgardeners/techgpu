<?php
/*
 * This file is part of the Tech Gardeners PHP Utility Project
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\TechGPU\Misc;


class TgArrayUtil
{
    
    /**
    * Converte un array in una lista html
    * 
    * @param mixed $array
    */
    public static function makeList($array, $deep = -1, $level = 0, array $parameters = array())
    { 

        //Base case: an empty array produces no list 
        if ($array === null) return false; 
        if ((!is_array($array) && !($array instanceof \Traversable)) || 
           (($deep != -1 && $level > $deep)) ) return " => ".$array; 

        //Recursive Step: make a list with child lists 
        $output = '<ul>'; 
        foreach ($array as $key => $subArray) { 
            $output .= '<li>' . $key . self::makeList($subArray, $deep, $level++) . '</li>'; 
        } 
        $output .= '</ul>'; 
         
        return $output; 
    } 
    
    /**
    * Converte un array in una tabella html
    * 
    * @param mixed $array
    */
    public static function makeTable($array, $deep = -1, $level = 0, array $parameters = array())
    { 

        //Base case: an empty array produces no list 
        if ($array === null) return false; 
        if ((!is_array($array) && !($array instanceof \Traversable)) || 
           (($deep != -1 && $level > $deep)) ) return $array; 

        //Recursive Step: make a list with child lists 
        $output = "<table border='1'>"; 
        //$output .= "<tr><th>Name</th><th>Value</th>"; 
        foreach ($array as $key => $subArray) { 
            $output .= '<tr><td>' . $key . "</td><td>" .  self::makeTable($subArray, $deep, $level++) . '</td></tr>'; 
        } 
        $output .= '</table>';
         
        return $output; 
    }     
    
}