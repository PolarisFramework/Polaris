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
 * App
 * 
 * @package     Polaris
 * @subpackage  Core
 * @category    Library
 * @author      Ivan Molina Pavana <montemolina@live.com>
 * @link        http://polarisframework.com/docs/.html
 */
class App_Controller extends Polaris_Controller {
    
    function __construct()
    {
        // Cargamos el constructor principal
        parent::__construct();
        
        // Cargamos las librerías globales
        $this->load->_autoload(
            array(
                //'library' => array('session'),
            )
        );
        
        // Comprobamos mantenimiento              
        $this->load->model('user/auth');                
        
        if ( ! $this->userAuth->isUser())
        {
            
        }
    }
}