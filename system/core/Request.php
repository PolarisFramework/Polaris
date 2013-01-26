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
 * Request
 * 
 * Esta clase se utiliza para la interacción con las peticiones enviadas
 * por el usuario ya sean por ($_GET, $_POST, $_FILES, $_COOKIE, $_SERVER).
 * 
 * @package     Polaris
 * @subpackage  Core
 * @category    Library
 * @author      Ivan Molina Pavana <montemolina@live.com>
 * @link        http://polarisframework.com/docs/.html
 */
class Polaris_Request {
    
    /**
     * Datos enviados por el usuario.
     * 
     * ($_GET, $_POST, $_FILES)
     * 
     * @var array
     */
    private $_args = array();
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_args = $this->_trimData(array_merge($_GET, $_POST, $_FILES));
        $this->config =& load_class('Config', 'core');
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Verifica si existe un request.
     * 
     * @access public
     * @param string $name
     * @return bool
     */
    public function is($name)
    {
        return (isset($this->_args[$name])) ? true : false;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Obtener un parámetro.
     * 
     * @access public
     * @param string $name
     * @param string $default Valor por defecto si el parámetro no fue enviado.
     * @param bool $xss
     * @return mixed
     */
    public function get($name, $default = null, $xss = false)
    {
        return (isset($this->_args[$name]) ? ($xss ? $this->xssClean($this->_args[$name]) : $this->_args[$name]) : $default);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Obtener un parámetro y convertirlo a entero.
     * 
     * @access public
     * @param string $name
     * @param string $default
     * @return int
     */
    public function getInt($name, $default = null)
    {
        return (int) $this->get($name, $default);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Obtener todos los valores recibidos.
     * 
     * @access public
     * @return array
     */
    public function getRequest()
    {
        return (array) $this->_args;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Obtener un valor desde $_SERVER
     * 
     * @access public
     * @param string $name
     * @return mixed
     */
    public function server($name)
    {
        return (isset($_SERVER[$name]) ? $_SERVER[$name] : '');
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Obtener un valor desde $_COOKIE
     * 
     * @access public
     * @param string $name
     * @param bool $xss
     * @return string
     */
    public function cookie($name, $xss = false)
    {
        return (isset($_COOKIE[$name]) ? ($xss ? $this->xssClean($_COOKIE[$name]) : $_COOKIE[$name]) : '');
    }
    
    // -------------------------------------------------------------
    
    /**
     * Obtener dirección IP
     * 
     * @access public
     * @return string
     */
    public function ip()
    {
        $ipAddress = $this->getServer('REMOTE_ADDR');
        
        if ( ! filter_var($ipAddress, FILTER_VALIDATE_IP))
        {
            $ipAddress = '0.0.0.0';
        }
        
        return $ipAddress;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Agregar un parámetro.
     * 
     * @access public
     * @param mixed $name
     * @param string $value
     * @return void
     */
    public function set($name, $value = null)
    {
        if ( ! is_array($name))
        {
            $name = array($name => $value);
        }
        
        foreach($name as $key => $value)
        {
            $this->_args[$key] = $value;
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Crear una cookie
     * 
     * @access public
     * @param string $name
     * @param string $value
     * @param string $expire
     * @param string $domain
     * @param string $path
     * @param string $prefix
     * @param bool $secure
     * @return void
     */
    public function setCookie($name, $value = '', $expire = '', $domain = '', $path = '/', $prefix = '', $secure = false)
    {
        if ( is_array($name))
        {
            foreach(array('value', 'expire', 'domain', 'path', 'prefix', 'secure', 'name') as $item)
            {
                $$item = $name[$item];
            }
        }
        
        if ( ! is_numeric($expire))
        {
            $expire = time() - 86500;
        }
        else
        {
            $expire = ($expire > 0) ? time() + $expire : 0;
        }
        
        if ($domain == '' && $this->config->get('cookie_domain') != '')
        {
            $domain = $this->config->get('cookie_domain');
        }
        
        if ($path == '/' && $this->config->get('cookie_path') != '/')
        {
            $path = $this->config->get('cookie_path');
        }
        
        if ($prefix == '' && $this->config->get('cookie_prefix') != '')
        {
            $prefix = $this->config->get('cookie_prefix');
        }
        
        if ($secure == false && $this->config->get('cookie_secure') != false)
        {
            $secure = $this->config->get('cookie_secure');
        }
        
        setcookie($prefix.$name, $value, $expire, $path, $domain, $secure);
    }
    
    
    // --------------------------------------------------------------------
    
    /**
     * XSS Clean
     * 
     * TODO: Función provicional....
     * 
     * @access private
     * @param string $str
     * @return string
     */
    public function xssClean($str)
    {
        return str_replace(array("'", '"', '<', '>'), array("&#39;", "&quot;", '&lt;', '&gt;'), stripslashes($str));
    }

    // --------------------------------------------------------------------
    
    /**
     * Limpiar datos
     * 
     * @access private
     * @param mixed $param
     * @return mixed
     */
    private function _trimData($param)
    {
        if (is_array($param))
        {
            return array_map(array(&$this, '_trimData'), $param);
        }
        
        if ( get_magic_quotes_gpc())
        {
            $param = stripcslashes($param);
        }
        
        return trim($param);
    }
}