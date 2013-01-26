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
 * Configuración
 * 
 * Maneja la configuración del sistema.
 * 
 * @package     Polaris
 * @subpackage  Core
 * @category    Library
 * @author      Ivan Molina Pavana <montemolina@live.com>
 * @link        http://polarisframework.com/docs/.html
 */
class Polaris_Config {
    
    /**
     * Lista de configuraciones cargadas.
     * 
     * @var array
     */
    public $config = array();
    
    /**
     * Constructor
     * 
     * Carga el archivo de configuración global.
     */
    public function __construct()
    {
        $config = array();
        
        $filePath = APP_PATH . 'config' . DS . 'config.php';
        
        if ( ! file_exists($filePath))
        {
            show_error('No se encuentra el archivo de configuración.');
        }
        
        include $filePath;
        
        $this->config =& $config;
        
        if ($this->config['base_url'] == '')
        {
            if (isset($_SERVER['HTTP_HOST']))
            {
                $baseUrl = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
                $baseUrl .= '://' . $_SERVER['HTTP_HOST'];
                $baseUrl .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
            }
            else
            {
                $baseUrl = 'http://localhost/';
            }
            
            $this->set('base_url', $baseUrl);
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Cargar un parámetro de configuración.
     * 
     * @access public
     * @param string $name Nombre del parámetro
     * @param string $index Nombre del index (Para arreglos)
     * @return mixed
     */
    public function get($name, $index = '')
    {
        if ( $index == '')
        {
            if ( ! isset($this->config[$name]))
            {
                return false;
            }
            
            return $this->config[$name];
        }
        else
        {
            if ( ! isset($this->config[$index]))
            {
                return false;
            }
            
            if ( ! isset($this->config[$index][$name]))
            {
                return false;
            }
            
            return $this->config[$index][$name];
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Añadir un parámetro de configuración.
     * 
     * @access public
     * @param string $name
     * @param string $value
     * @return void
     */
    public function set($name, $value)
    {
        $this->config[$name] = $value;
    }
}