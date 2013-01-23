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
    public $aConfig = array();
    
    /**
     * Constructor
     * 
     * Carga el archivo de configuración global.
     */
    public function __construct()
    {
        $config = array();
        
        $sConfigPath = APP_PATH . 'config' . DS . 'config.php';
        
        if ( ! file_exists($sConfigPath))
        {
            show_error('No se encuentra el archivo de configuración.');
        }
        
        require $sConfigPath;
        
        $this->aConfig =& $config;
        
        if ($this->aConfig['base_url'] == '')
        {
            if (isset($_SERVER['HTTP_HOST']))
            {
                $sBaseUrl = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
                $sBaseUrl .= '://' . $_SERVER['HTTP_HOST'];
                $sBaseUrl .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
            }
            else
            {
                $sBaseUrl = 'http://localhost/';
            }
            
            $this->set('base_url', $sBaseUrl);
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Cargar un parámetro de configuración.
     * 
     * @access public
     * @param string $sName Nombre del parámetro
     * @param string $sIndex Nombre del index (Para arreglos)
     * @return mixed
     */
    public function get($sName, $sIndex = '')
    {
        if ( $sIndex == '')
        {
            if ( ! isset($this->aConfig[$sName]))
            {
                return false;
            }
            
            return $this->aConfig[$sName];
        }
        else
        {
            if ( ! isset($this->aConfig[$sIndex]))
            {
                return false;
            }
            
            if ( ! isset($this->aConfig[$sIndex][$sName]))
            {
                return false;
            }
            
            return $this->aConfig[$sIndex][$sName];
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Añadir un parámetro de configuración.
     * 
     * @access public
     * @param string $sName
     * @param string $sValue
     * @return void
     */
    public function set($sName, $sValue)
    {
        $this->aConfig[$sName] = $sValue;
    }
}