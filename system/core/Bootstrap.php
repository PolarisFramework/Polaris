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
 * Bootstrap
 * 
 * Carga las clases base y ejecuta la petición. (Arranca el sistema).
 * 
 * @package     Polaris
 * @subpackage  Core
 * @category    Library
 * @author      Ivan Molina Pavana <montemolina@live.com>
 * @link        http://polarisframework.com/docs/.html
 */

/*
 * ---------------------------------------------------------------
 *  Memoria usada por PHP
 * ---------------------------------------------------------------
 */
    define('START_MEM', memory_get_usage());
    
/*
 * ---------------------------------------------------------------
 *  Límite de tiempo para el script.
 * ---------------------------------------------------------------
 */
	if (function_exists("set_time_limit") == true && @ini_get("safe_mode") == 0)
	{
		@set_time_limit(300);
	}
    
/*
 * ---------------------------------------------------------------
 *  Reporte de errores
 * ---------------------------------------------------------------
 *
 *  Puede modificar el parámetro de acuerdo a sus necesidades.
 *
 */
    error_reporting(E_ALL);
    
/*
 * ---------------------------------------------------------------
 *  Cargando constantes.
 * ---------------------------------------------------------------
 */
    require ROOT . DS . APP . DS . 'config' . DS . 'constants.php';
    
/*
 * ---------------------------------------------------------------
 *  Cargando funciones básicas.
 * ---------------------------------------------------------------
 */
    require CORE_PATH . 'Basics.php';
    
/*
 * ---------------------------------------------------------------
 *  Crea una instancia de la clase Timer
 * ---------------------------------------------------------------
 */
    $oTimer =& loadClass('Timer', 'core');
    $oTimer->mark('total_execution_time_start');
    
/*
 * ---------------------------------------------------------------
 *  Crea una instancia de la clase Config
 * ---------------------------------------------------------------
 */
    $oConfig =& loadClass('Config', 'core');
    
/*
 * ---------------------------------------------------------------
 *  Crea una instancia de la clase URI
 * ---------------------------------------------------------------
 */
    $oUri =& loadClass('URI', 'core');
    
/*
 * ---------------------------------------------------------------
 *  Crea una instancia del Router y analiza la ruta.
 * ---------------------------------------------------------------
 */
    $oRouter =& loadClass('Router', 'core');
    $oRouter->setRouting();
    
/*
 * ---------------------------------------------------------------
 *  Crea una instancia de la clase Output
 * ---------------------------------------------------------------
 */
    $oOutput =& loadClass('Output', 'core');
    
/*
 * ---------------------------------------------------------------
 *  Carga el controlador de la aplicación y el controlador local
 * ---------------------------------------------------------------
 */
    // Cargamos el controlador base
    require CORE_PATH . 'App.php';
    require CORE_PATH . 'Controller.php';
    
    function &getInstance()
    {
        return Polaris_Controller::getInstance();
    }

    // Cargar el controlador local
    // Nota: El router automáticamente valida el directorio del controlador usando Router->_validateRequest().
    // Si al incluir el archivo hay un fallo, entonces el controlador por defecto en routes.php no está resolviendo algo válido.
    if ( !file_exists($oRouter->getDirectory() . $oRouter->getClass() . '.controller.php'))
    {
        show_error('No se puede cargar el controlador predeterminado. Por favor, asegúrese de que el controlador especificado en el archivo routes.php es válido.');
    }
    
    require $oRouter->getDirectory() . $oRouter->getClass() . '.controller.php';
    
/*
 * ---------------------------------------------------------------
 *  Creamos el nombre de la clase y verificamos su existencia.
 * ---------------------------------------------------------------
 */
    // Módulo y clase solicitados.
    $sModule = $oRouter->getModule();
    $sClass = $oRouter->getClass();
    
    // Todos los métodos públicos deben llevar el prefijo action_ 
    $sMethod = 'action_' . $oRouter->getMethod();
    
    // Creamos el nombre completo de nuestro controlador.
    $sClassName = ucfirst($sModule) . '_' . ucfirst($sClass) . '_Controller';
    
    if ( !class_exists($sClassName))
    {
        show_error('Página no encontrada: ' . $sModule . '/' . $sClass, 404);
    }
    
/*
 * ---------------------------------------------------------------
 *  Crea una instancia del controlador solicitado.
 * ---------------------------------------------------------------
 */
    $oController = new $sClassName();
    
/*
 * ---------------------------------------------------------------
 *  Llamamos al método solicitado.
 * ---------------------------------------------------------------
 */
 
    // Verificamos que exista el método.
    if ( ! in_array($sMethod, array_map('strtolower', get_class_methods($oController))))
    {
        show_error('Página no encontrada: ' . $sModule . '/' . $sClass, 404);
    }
    
    // Llamamos al método solicitado.
    // Cualquier parámetro enviado después de class/method será enviado como parámetro
    call_user_func_array(array(&$oController, $sMethod), array_slice($oUri->aSegments, 2));
    
/*
 * ---------------------------------------------------------------
 *  Enviar la salida final al navegador.
 * ---------------------------------------------------------------
 */
    $oOutput->display();