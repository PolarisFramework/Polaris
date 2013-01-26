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
    
    /**
     * Instancia de la clase.
     * 
     * @var object
     */
    private static $instance;
    
    /**
     * Carga automática de librerías.
     * 
     * @var array
     */
    public $autoload = array();
    
    /**
     * Constructor
     * 
     * @access public
     */
    public function __construct()
    {
        self::$instance =& $this;
        
        // Asigna todos los objetos que fueron instanciados por el bootstrap
        // a objetos locales para que este se convierta en un super objeto.
        foreach ( is_loaded() as $key => $class)
        {
            $this->{$key} =& load_class($class);
        }
        
        // Registramos el módulo actual.
        $class = str_replace('_Controller', '', get_class($this));
        load_class('Module', 'core')->addClass($class, $this);
        
        // Copiamos una instancia de Layout
        $this->layout = clone load_class('Layout', 'core');
        $this->layout->init($this);
        
        // Copiamos una instancia de Loader e inicializamos();
        $this->load = clone load_class('Loader', 'core');
        $this->load->init($this);
        
        $this->load->_autoload($this->autoload);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Retornar Instancia del super controlador
     */
    public static function &get_instance()
    {
        return self::$instance;
    }
}