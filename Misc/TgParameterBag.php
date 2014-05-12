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

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Extension of Symfony HttpFoundation ParameterBag
 * For manipulate multidimension array
 *
 * @author Roberto Beccaceci <roberto@beccaceci.it>
 *
 * @api
 */
class TgParameterBag extends ParameterBag
{
    
    const KEYSEPARATOR = '/';
    const DEEP_LEAF = 0;
    const DEEP_PARENT = 1;


    // TODO: Convert to protected (need to manage tests issue)
    public function convertPathToArrayStyle($path = '', $sep = self::KEYSEPARATOR)
    {
        if (!is_string($path))
            throw new \InvalidArgumentException(sprintf('Malformed argument path: %s', $path));
        
        $arrKeysStr = '';
        $keys = explode($sep, $path);
        foreach ($keys as $ii=>$key) {
            $arrKeysStr .= ($key) ? "['$key']" : '';
        }            

        return $arrKeysStr;                    
    }
    
    
    /**
     * Returns the parameter keys.
     *
     * @return array An array of parameter keys
     *
     * @api
     */
    public function keys($path = self::KEYSEPARATOR, $default = null, $sep = self::KEYSEPARATOR)
    {
 
        $bag = $this->get($path, null, true, $sep);
        
        return (is_null($bag) || !is_array($bag)) ? $default : array_keys($bag);
    }

    /**
     * Replaces the current parameters by a new set.
     *
     * @param array $parameters An array of parameters
     *
     * @api
     */
    public function replace(array $parameters = array(), $path = self::KEYSEPARATOR, $sep = self::KEYSEPARATOR)
    {
        return $this->set($path, $parameters, $sep);       
    }

    /**
     * Adds parameters.
     *
     * @param array $parameters An array of parameters
     *
     * @api
     */
    public function add(array $parameters = array(), $path = self::KEYSEPARATOR, $sep = self::KEYSEPARATOR)
    {
        
        if ($this->has($path)) {
            
            $arrKeys = $this->convertPathToArrayStyle($path, $sep);
            eval('$parameters = array_replace($this->parameters'.$arrKeys.', $parameters);');    
        } 
        
        $this->set($path, $parameters, $sep);        
    }

    /**
     * Merges parameters.
     *
     * @param array $parameters An array of parameters
     *
     * @api
     */
    public function merge(array $parameters = array(), $path = self::KEYSEPARATOR, $sep = self::KEYSEPARATOR)
    {
        
        if ($this->has($path)) {
            
            $arrKeys = $this->convertPathToArrayStyle($path, $sep);
            eval('$parameters = array_merge_recursive($this->parameters'.$arrKeys.', $parameters);');    
        } 

        $this->set($path, $parameters, $sep);
                
    }

    /**
     * Returns a parameter by name.
     *
     * @param string  $path    The key
     * @param mixed   $default The default value if the parameter key does not exist
     * @param bool    $deep    If true, a path like foo[bar] will find deeper items
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     *
     * @api
     */
    public function getParent($path, $default = null, $sep = self::KEYSEPARATOR)
    {
        return $this->get($path, $default, true, $sep, self::DEEP_PARENT);   
    }
    
    public function get($path, $default = null, $deep = true, $sep = self::KEYSEPARATOR, $deepLevel = self::DEEP_LEAF)
    {
        if (!is_string($path))
            throw new \InvalidArgumentException(sprintf('Malformed argument path: %s', $path));

        if (!is_string($sep) || '' == $sep)
            throw new \InvalidArgumentException(sprintf('Malformed argument sep: %s', $sep));

        if (!is_numeric($deepLevel) || $deepLevel < 0)
            throw new \InvalidArgumentException(sprintf('Malformed argument deepLevel: %s', $deepLevel));

        $bag = $this->parameters;
        
        if ($path == self::KEYSEPARATOR) { return $bag; }            
            
        if (!$deep) {
            return ( !is_array($bag) || !array_key_exists($path, $bag) ) ? $default : $bag[$path];
        }        

        $keys = explode($sep, $path);

        if (!is_array($keys) || count($keys) < 1 || count($keys) < $deepLevel) { return $default; }
        if (count($keys) == $deepLevel) { return $bag; }
                
        $keys = array_slice($keys,0,count($keys)-$deepLevel);
        
        foreach ($keys as $ii=>$key) {
            
            // If last key
            if ($ii >= count($keys)-1) {
                
                return (!is_array($bag) || !array_key_exists($key, $bag)) ? $default : $bag[$key];
            }

            // If next bag is not array the key finded not exist
            if (!is_array($bag) || !array_key_exists($key, $bag))
                return $default;                    
            
            $bag = $bag[$key];
        }

        // Never user, only for fallback
        return $default;
    }

    /**
     * Sets a parameter by name.
     *
     * @param string $key   The key
     * @param mixed  $value The value
     *
     * @api
     */
    public function set($path, $value, $sep = self::KEYSEPARATOR)
    {
        $arrKeys = $this->convertPathToArrayStyle($path,$sep);

        eval('$this->parameters'.$arrKeys.' = $value;');
    }

    /**
     * Returns true if the parameter is defined.
     *
     * @param string $key The key
     *
     * @return bool    true if the parameter exists, false otherwise
     *
     * @api
     */
    public function has($path, $deep = true, $sep = self::KEYSEPARATOR)
    {

        if (!is_string($path))
            throw new \InvalidArgumentException(sprintf('Malformed argument path %s', $path));        
        
        if ($path == self::KEYSEPARATOR) { return true; }
        if (!$deep) return array_key_exists($path, $this->parameters); 
        
        $parent = $this->getParent($path, null, $sep);
        if ($parent && is_array($parent)) {
            $keyArr = explode($sep, $path);            
            return array_key_exists(array_pop($keyArr), $parent);
        }
                
        return false;        
    }

    /**
     * Removes a parameter.
     *
     * @param string $key The key
     *
     * @api
     */
    public function remove($path, $deep = true, $sep = self::KEYSEPARATOR)
    {
        $arrKeys = $this->convertPathToArrayStyle($path,$sep);

        eval('unset($this->parameters'.$arrKeys.');');
    }

    /**
     * Returns the alphabetic characters of the parameter value.
     *
     * @param string  $key     The parameter key
     * @param mixed   $default The default value if the parameter key does not exist
     * @param bool    $deep    If true, a path like foo[bar] will find deeper items
     *
     * @return string The filtered value
     *
     * @api
     */
    public function getAlpha($path, $default = '', $deep = true, $sep = self::KEYSEPARATOR)
    {
        return preg_replace('/[^[:alpha:]]/', '', $this->get($path, $default, $deep, $sep));
    }

    /**
     * Returns the alphabetic characters and digits of the parameter value.
     *
     * @param string  $key     The parameter key
     * @param mixed   $default The default value if the parameter key does not exist
     * @param bool    $deep    If true, a path like foo[bar] will find deeper items
     *
     * @return string The filtered value
     *
     * @api
     */
    public function getAlnum($path, $default = '', $deep = true, $sep = self::KEYSEPARATOR)
    {
        return preg_replace('/[^[:alnum:]]/', '', $this->get($path, $default, $deep, $sep));
    }

    /**
     * Returns the digits of the parameter value.
     *
     * @param string  $key     The parameter key
     * @param mixed   $default The default value if the parameter key does not exist
     * @param bool    $deep    If true, a path like foo[bar] will find deeper items
     *
     * @return string The filtered value
     *
     * @api
     */
    public function getDigits($path, $default = '', $deep = true, $sep = self::KEYSEPARATOR)
    {
        // we need to remove - and + because they're allowed in the filter
        return str_replace(array('-', '+'), '', $this->filter($path, $default, $deep, FILTER_SANITIZE_NUMBER_INT, array(), $sep));
    }

    /**
     * Returns the parameter value converted to integer.
     *
     * @param string  $key     The parameter key
     * @param mixed   $default The default value if the parameter key does not exist
     * @param bool    $deep    If true, a path like foo[bar] will find deeper items
     *
     * @return int     The filtered value
     *
     * @api
     */
    public function getInt($path, $default = 0, $deep = true, $sep = self::KEYSEPARATOR)
    {
        return (int) $this->get($path, $default, $deep, $sep);
    }

    /**
     * Filter key.
     *
     * @param string  $key     Key.
     * @param mixed   $default Default = null.
     * @param bool    $deep    Default = false.
     * @param int     $filter  FILTER_* constant.
     * @param mixed   $options Filter options.
     *
     * @see http://php.net/manual/en/function.filter-var.php
     *
     * @return mixed
     */
    public function filter($path, $default = null, $deep = true, $filter = FILTER_DEFAULT, $options = array(), $sep = self::KEYSEPARATOR)
    {
        $value = $this->get($path, $default, $deep, $sep);

        // Always turn $options into an array - this allows filter_var option shortcuts.
        if (!is_array($options) && $options) {
            $options = array('flags' => $options);
        }

        // Add a convenience check for arrays.
        if (is_array($value) && !isset($options['flags'])) {
            $options['flags'] = FILTER_REQUIRE_ARRAY;
        }

        return filter_var($value, $filter, $options);
    }

}