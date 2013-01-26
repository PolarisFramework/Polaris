<?php
/**
 * Polaris Framework
 * 
 * Ligero y poderoso framework de código abierto.
 * 
 * @package     Polaris
 * @author      Ivan Molina Pavana <montemolina@live.com>
 * @copyright   Copyright (c) 2013
 * @license     http://polarisframework.com/docs/license.html
 * @link        http://polarisframework.com
 * @since       Version 1.0
 */

// ------------------------------------------------------------------------

/**
 * Funciones básicas
 * 
 * Contiene funciones que ayudan a arrancar el sistema.
 * 
 * @package     Polaris
 * @subpackage  Core
 * @category    Library
 * @author      Ivan Molina Pavana <montemolina@live.com>
 * @link        http://polarisframework.com/docs/.html
 */

// ------------------------------------------------------------------------

/**
 * Cargar una clase/librería
 * 
 * @access public
 * @param string $class
 * @param string $dir
 * @param string $prefix
 * @return object
 */
function &load_class($class, $dir = 'library', $prefix = 'Polaris_')
{
    static $_classes = array();
    
    if ( isset($_classes[$class]))
    {
        return $_classes[$class];
    }
    
    $name = false;
    
    foreach (array(APP_PATH, SYS_PATH) as $path)
    {
        if ( file_exists($path . $dir . DS . $class . '.php'))
        {
            $name = $prefix . $class;
            
            // Si no se ha cargado la clase...
            if ( class_exists($name) === false)
            {
                require $path . $dir . DS . $class . '.php';
            }
            
            break;
        }
    }
    
    // No encontramos la clase?
    if ( $name === false)
    {
        show_error('No se puede encontrar la clase especificada:' . $class );
    }
    
    is_loaded($class);
    
    $_classes[$class] = new $name();
    
    return $_classes[$class];
}

// ------------------------------------------------------------------------

/**
 * Realiza un seguimiento de los objetos cargados.
 * 
 * @access public
 * @param string $class
 * @return array
 */
function &is_loaded($class = '')
{
    static $_isLoaded = array();
    
    if ( $class != '')
    {
        $_isLoaded[strtolower($class)] = $class;
    }
    
    return $_isLoaded;
}

// ------------------------------------------------------------------------
 
/**
 * Mostrar error
 * 
 * Función temporal
 * 
 * @param string $message
 * @param integer $code
 * @return void
 */
function show_error($message, $code = 500)
{
    echo utf8_decode($message) . ' - ' . $code;
    exit;
}