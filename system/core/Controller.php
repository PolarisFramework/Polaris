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
 * Controlador Base
 * 
 * Este es la columna vertebral del sistema.
 * 
 * @package     Polaris
 * @subpackage  Core
 * @category    Library
 * @author      Ivan Molina Pavana <montemolina@live.com>
 * @link        http://polarisframework.com/docs/.html
 */
class Polaris_Controller {
    
    private static $_oInstance;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        self::$_oInstance =& $this;
        
        // Asigna todos los objetos que fueron instanciados por el bootstrap
        // a objetos locales para que este se convierta en un super objeto.
        foreach ( is_loaded() as $sKey => $sClass)
        {
            $this->{$sKey} =& load_class($sClass);
        }
        
        // Cargador
        $this->load =& load_class('Loader', 'core');
        
        $this->load->init();
    }
    
    public static function &getInstance()
    {
        return self::$_oInstance;
    }
}

new Polaris_Controller;