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
 * App Controller
 * 
 * @package     Polaris
 * @subpackage  Core
 * @category    Library
 * @author      Ivan Molina Pavana <montemolina@live.com>
 * @link        http://polarisframework.com/docs/.html
 */
class App_Controller {
    
    /**
     * Constructor
     * 
     * @access public
     */
    public function __construct()
    {
        $sClass = str_replace('_Controller', '', get_class($this));
        load_class('Module', 'core')->addClass($sClass, $this);
        
        // Copiamos una instancia de Layout
        $this->layout = clone load_class('Layout', 'core');
        $this->layout->init($this);
        
        // Copiamos una instancia de Loader e inicializamos();
        $this->load = clone load_class('Loader', 'core');
        $this->load->init($this);
    }
    
    /**
     * __get
     * 
     * @access public
     * @param string $sName
     * @return mixed
     */
    public function __get($sName)
    {
        $oObject =& get_instance();
        
        return $oObject->{$sName};
    }
}