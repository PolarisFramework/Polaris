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
 * Polaris_Router
 * 
 * Se encarga del enrutar la solicitud del usuario enviada
 * mediante URI. Establece el módulo/controlador/método que
 * será ejecutado.
 * 
 * @package     Polaris
 * @subpackage  Core
 * @category    Library
 * @author      Ivan Molina Pavana <montemolina@live.com>
 * @link        http://polarisframework.com/docs/general/routing.html
 */

class Polaris_Router {
    
    /**
     * Rutas
     * 
     * @var array
     */
    private $_routes = array();
    
    /**
     * Módulo
     * 
     * @var string
     */
    private $_module = '';
    
    /**
     * Clase/Controlador
     * 
     * @var string
     */
    private $_class = '';
    
    /**
     * Método
     * 
     * @var string
     */
    private $_method = 'index';
    
    /**
     * Directorio
     * 
     * @var string
     */
    private $_directory = '';
    
    /**
     * Controlador por defecto.
     * 
     * @var string
     */
    private $_defaultController = '';
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->config =& load_class('Config', 'core');
        $this->uri =& load_class('URI', 'core');
        $this->module =& load_class('Module', 'core');
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Establecer el mapeo de rutas.
     * 
     * Esta función determina lo que se debe ejecutar, basándose en la
     * solicitud URI y las rutas del archivo de configuración routes.php
     * 
     * @access public
     * @return void
     */
    public function setRouting()
    {
        $route = array();
        
        $filePath = APP_PATH . 'config' . DS . 'routes.php';
        
        if ( ! file_exists($filePath))
        {
            show_error('No se encuentra el archivo de configuración: routes.php');
        }
        
        include $filePath;
        
        $this->_routes =& $route;
        
        // Establecer el controlador por defecto, el cual será cargado cuando
        // la URI esté vacia es decir en la página principal.
        $this->_defaultController = ( ! isset($this->_routes['default_module_controller']) || $this->_routes['default_module_controller'] == '') ? false : strtolower($this->_routes['default_module_controller']);
        
        $this->uri->fetchURIString();
        
        if ($this->uri->uriString == '')
        {
            $this->_setDefaultController();
        }
        
        // Crear arreglo con los segmentos de la URI
        $this->uri->explodeSegments();
        
        // Filtramos las rutas establecidas en routes.php
        $this->_parseRoutes();
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Establecer el controlador por defecto.
     * 
     * @access private
     * @return void
     */
    private function _setDefaultController()
    {
        if ($this->_defaultController === false)
        {
            show_error('No se puede determinar lo que se debe mostrar. La ruta por defecto no ha sido configurada.');
        }
        
        // Hay un método espesificado?
        $parts = explode('/', $this->_defaultController);
        
        if ( is_array($parts) && count($parts) >= 2)
        {
            $this->setClass($parts[1]);
            
            if ( isset($parts[2]))
            {
                $this->setMethod($parts[2]);
            }
            else
            {
                $this->setMethod('index');
            }
            
            $this->_setRequest($parts);
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Analizar Rutas
     * 
     * Esta función busca coincidencias entre las rutas
     * establecidas en routes.php y las recibidas en URI
     * para así determinar que clase/método cargar.
     * 
     * @access private
     * @return void
     */
    private function _parseRoutes()
    {
        // Convertir el arreglo de segmentos en una cadena URI
        $uri = implode('/', $this->uri->segments);
        
        // Existe una coincidencia total? terminamos
        if (isset($this->_routes[$uri]))
        {
            return $this->_setRequest(explode('/', $this->_routes[$uri]));
        }
        
        // Recorremos nuestras rutas en busca de coincidencias.
        foreach ( $this->_routes as $key => $val)
        {
            // Reemplazamos...
            $key = str_replace(array(':any', ':num'), array('.+', '[0-9]+'), $key);
            
            // Coincide con el RegEx?
            if ( preg_match('#^'.$key.'$#', $uri))
            {
                // Tenemos una variable de referencia?
                if (strpos($val, '$') !== false && strpos($key, '(') !== false)
                {
                    $val = preg_replace('#^'.$key.'$#', $val, $uri);
                }
                
                return $this->_setRequest(explode('/', $val));
            }
        }
        
        // Si llegamos hasta aquí, significa que no se encontró
        // coincidencia con alguna ruta, por tanto establecemos al
        // controlador por defecto.
        $this->_setRequest($this->uri->segments);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Set Request
     * 
     * Esta función toma una serie de segmentos URI y establece
     * el actual Module/Controller.
     * 
     * @access private
     * @param array $segments
     * @return void
     */
    private function _setRequest($segments)
    {
        $segments = $this->_validateRequest($segments);
        
        if ( count($segments) == 0)
        {
            return $this->_setDefaultController();
        }
        
        $this->setClass($segments[0]);
        
        if ( isset($segments[1]))
        {
            $this->setMethod($segments[1]);
        }
        else
        {
            $segments[1] = 'index';
        }
        
        //$this->uri->aRSegments = $segments;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Validamos los segmentos, intentando localizar la ruta
     * del controlador solicitado.
     * 
     * @access private
     * @param array $segments
     * @return array
     */
    private function _validateRequest($segments)
    {
        if ( count($segments) == 0)
        {
            return $segments;
        }
        
        if ( $aLocated = $this->locate($segments))
        {
            return $aLocated;
        }
        
        show_error('Página no encotrada: ' . $segments[0], 404);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Localizar controlador válido
     * 
     * @access public
     * @param array $segments
     * @return array
     */
    public function locate($segments)
    {
        $sExt = '.controller.php';
        
        if (isset($segments[0]) && $routes = $this->module->parseRoutes($segments[0], implode('/', $segments)))
        {
            $segments = $routes;
        }
        
        // Obtener las variable desde los segmentos.
        list($module, $directory, $controller) = array_pad($segments, 3, null);
        
        // Existe el directorio...
        if (is_dir($modulePath = MOD_PATH . $module . DS . 'controller' . DS))
        {
            $this->_module = $module;
            $this->_directory = $modulePath;

            // Existe un sub-controlador del módulo?
            if ($directory && is_file($modulePath . $directory . $sExt))
            {
                return array_slice($segments, 1);
            }
            
            // Existe un sub-directorio del módulo?
            if ($directory && is_dir($modulePath . $directory . DS))
            {
                $modulePath = $modulePath . $directory . DS;
                $this->_directory .= $directory . DS;
                
                // Existe el controlador en el sub-directorio
                if (is_file($modulePath . $directory . $sExt))
                {
                    return array_slice($segments, 1); 
                }
                
                // Existe un sub-controlador en el sub-directorio?
                if( $controller && is_file($modulePath . $controller . $sExt))
                {
                    return array_slice($segments, 2);
                }
            }
            
            if (is_file($modulePath . $module . $sExt))
            {
                return $segments;
            }
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * getModule
     * 
     * @access public
     * @return string
     */
    public function getModule()
    {
        return $this->_module;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * getClass
     * 
     * @access public
     * @return string
     */
    public function getClass()
    {
        return $this->_class;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Establecer la clase.
     * 
     * Definimos el controlador.
     * 
     * @access public
     * @param string $class
     * @return void
     */
    public function setClass($class)
    {
        $this->_class = str_replace(array('/', '.'), '', $class);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Obtener el método
     * 
     * @access public
     * @return string
     */
    public function getMethod()
    {
        if ( $this->_method == $this->getClass())
        {
            return 'index';
        }
        
        return $this->_method;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Establecer el método
     * 
     * @access public
     * @param string $method
     * @return void
     */
    public function setMethod($method)
    {
        $this->_method = $method;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * getDirectory
     * 
     * @access public
     * @return string
     */
    public function getDirectory()
    {
        return $this->_directory;
    }
}