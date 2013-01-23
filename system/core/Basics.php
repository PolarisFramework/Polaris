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
 * @param string $sClass
 * @param string $sDir
 * @param string $sPrefix
 * @return object
 */
function &loadClass($sClass, $sDir = 'library', $sPrefix = 'Polaris_')
{
    static $_aClasses = array();
    
    if ( isset($_aClasses[$sClass]))
    {
        return $_aClasses[$sClass];
    }
    
    $sClassName = false;
    
    foreach (array(APP_PATH, SYS_PATH) as $sPath)
    {
        if ( file_exists($sPath . $sDir . DS . $sClass . '.php'))
        {
            $sClassName = $sPrefix . $sClass;
            
            // Si no se ha cargado la clase...
            if ( class_exists($sClassName) === false)
            {
                require $sPath . $sDir . DS . $sClass . '.php';
            }
            
            break;
        }
    }
    
    // No encontramos la clase?
    if ( $sClassName === false)
    {
        show_error('No se puede encontrar la clase especificada:' . $sClass );
    }
    
    isLoaded($sClass);
    
    $_aClasses[$sClass] = new $sClassName();
    
    return $_aClasses[$sClass];
}

// ------------------------------------------------------------------------

/**
 * Realiza un seguimiento de los objetos cargados.
 * 
 * @access public
 * @param string $sClass
 * @return array
 */
function &isLoaded($sClass = '')
{
    static $_aLoaded = array();
    
    if ( $sClass != '')
    {
        $_aLoaded[strtolower($sClass)] = $sClass;
    }
    
    return $_aLoaded;
}

// ------------------------------------------------------------------------
 
/**
 * Mostrar error
 * 
 * Función temporal
 * 
 * @param string $sMessage
 * @param integer $nCode
 * @return void
 */
function show_error($sMessage, $nCode = 500)
{
    echo utf8_decode($sMessage) . ' - ' . $nCode;
    exit;
}

// ------------------------------------------------------------------------

/**
 * Mostrar consumo de recursos
 * 
 * @return string
 */
function show_debug()
{
    $iMem = memory_get_usage() - START_MEM;
    $sContent = round($iMem / 1024, 2) . ' kb &bull; ';
    $sContent .= number_format(array_sum(explode(' ', microtime())) - START_TIME, 3) . 's';
    
    return $sContent;
}