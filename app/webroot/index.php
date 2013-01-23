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
* index
*
* Controlador frontal, se encargará de recibir todas las solicitudes.
*
* @package     Polaris
* @subpackage  Core
* @category    Library
* @author      Ivan Molina Pavana <montemolina@live.com>
* @link        http://polarisframework.com/docs/.html
*/
 
/*
 * ---------------------------------------------------------------
 *  Constantes generales.
 * ---------------------------------------------------------------
 */
 
    // Se usa para separar los directorios.
    define('DS', DIRECTORY_SEPARATOR);

    // Ruta principal del framework.
    define('ROOT', dirname(dirname(dirname(__FILE__))));
    
    // Directorio donde se encuentra la aplicación.
    define('APP', basename(dirname(dirname(__FILE__))));
    
/*
 * --------------------------------------------------------------------
 * Cargar Bootstrap.
 * --------------------------------------------------------------------
 *
 * Let's go...
 *
 */

    require ROOT . DS . 'system' . DS . 'core' . DS . 'Bootstrap.php';