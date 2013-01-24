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
    require DB_PATH . 'Driver.php';
// ------------------------------------------------------------------------

/**
 * Database
 * 
 * Capa para la base de datos. Todas las interacciones con una base de datos
 * se realiza a través de esta clase. Se conecta a un driver específico como
 * MySQL, MySQLi, Oracle, etc.
 * 
 * @package     Polaris
 * @subpackage  Core
 * @category    Library
 * @author      Ivan Molina Pavana <montemolina@live.com>
 * @link        http://polarisframework.com/docs/.html
 */
class Polaris_Database {
    
    /**
     * Driver Object
     * 
     * @var object
     */
    private $object = null;
    
    /**
     * Constructor
     * 
     * Carga he inicializa el driver que necesitamos.
     * 
     * @access public
     * @param array $params
     * @return void
     */
    public function __construct($params)
    {
        if ( ! $this->object)
        {   
            if ( ! file_exists($file_path = DB_PATH . 'driver' . DS . strtolower($params['dbdriver']) . '.php'))
            {
                show_error('No se pudo cargar el driver solicitado: ' . $params['dbdriver']);
            }
            
            include $file_path;
            
            // Creamos el objecto
            $driver = 'Polaris_Database_Driver_' . ucfirst($params['dbdriver']);
            $this->object = new $driver($params);
            
            if ($this->object->autoinit)
            {
                $this->object->init();
            }
        }
    }
    
    // --------------------------------------------------------------------
    
    public function &get_instance()
    {
        return $this->object;
    }
}