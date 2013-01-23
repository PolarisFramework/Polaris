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
    private $_aRoutes = array();
    
    /**
     * Módulo
     * 
     * @var string
     */
    private $_sModule = '';
    
    /**
     * Clase/Controlador
     * 
     * @var string
     */
    private $_sClass = '';
    
    /**
     * Método
     * 
     * @var string
     */
    private $_sMethod = 'index';
    
    /**
     * Directorio
     * 
     * @var string
     */
    private $_sDirectory = '';
    
    /**
     * Controlador por defecto.
     * 
     * @var string
     */
    private $_sDefaultController = '';
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->config =& loadClass('Config', 'core');
        $this->uri =& loadClass('URI', 'core');
        $this->module =& loadClass('Module', 'core');
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
        
        $sRoutesPath = APP_PATH . 'config' . DS . 'routes.php';
        
        if ( ! file_exists($sRoutesPath))
        {
            show_error('No se encuentra el archivo de configuración: routes.php');
        }
        
        require $sRoutesPath;
        
        $this->_aRoutes =& $route;
        
        // Establecer el controlador por defecto, el cual será cargado cuando
        // la URI esté vacia es decir en la página principal.
        $this->_sDefaultController = ( ! isset($this->_aRoutes['default_module_controller']) || $this->_aRoutes['default_module_controller'] == '') ? false : strtolower($this->_aRoutes['default_module_controller']);
        
        $this->uri->fetchURIString();
        
        if ($this->uri->sURIString == '')
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
        if ($this->_sDefaultController === false)
        {
            show_error('No se puede determinar lo que se debe mostrar. La ruta por defecto no ha sido configurada.');
        }
        
        // Hay un método espesificado?
        $aParts = explode('/', $this->_sDefaultController);
        
        if ( is_array($aParts) && count($aParts) >= 2)
        {
            $this->setClass($aParts[1]);
            
            if ( isset($aParts[2]))
            {
                $this->setMethod($aParts[2]);
            }
            else
            {
                $this->setMethod('index');
            }
            
            $this->_setRequest($aParts);
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
        $sUri = implode('/', $this->uri->aSegments);
        
        // Existe una coincidencia total? terminamos
        if (isset($this->_aRoutes[$sUri]))
        {
            return $this->_setRequest(explode('/', $this->_aRoutes[$sUri]));
        }
        
        // Recorremos nuestras rutas en busca de coincidencias.
        foreach ( $this->_aRoutes as $sKey => $sVal)
        {
            // Reemplazamos...
            $sKey = str_replace(array(':any', ':num'), array('.+', '[0-9]+'), $sKey);
            
            // Coincide con el RegEx?
            if ( preg_match('#^'.$sKey.'$#', $sUri))
            {
                // Tenemos una variable de referencia?
                if (strpos($sVal, '$') !== false && strpos($sKey, '(') !== false)
                {
                    $sVal = preg_replace('#^'.$key.'$#', $sVal, $sUri);
                }
                
                return $this->_setRequest(explode('/', $sVal));
            }
        }
        
        // Si llegamos hasta aquí, significa que no se encontró
        // coincidencia con alguna ruta, por tanto establecemos al
        // controlador por defecto.
        $this->_setRequest($this->uri->aSegments);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Set Request
     * 
     * Esta función toma una serie de segmentos URI y establece
     * el actual Module/Controller.
     * 
     * @access private
     * @param array $aSegments
     * @return void
     */
    private function _setRequest($aSegments)
    {
        $aSegments = $this->_validateRequest($aSegments);
        
        if ( count($aSegments) == 0)
        {
            return $this->_setDefaultController();
        }
        
        $this->setClass($aSegments[0]);
        
        if ( isset($aSegments[1]))
        {
            $this->setMethod($aSegments[1]);
        }
        else
        {
            $aSegments[1] = 'index';
        }
        
        //$this->uri->aRSegments = $aSegments;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Validamos los segmentos, intentando localizar la ruta
     * del controlador solicitado.
     * 
     * @access private
     * @param array $aSegments
     * @return array
     */
    private function _validateRequest($aSegments)
    {
        if ( count($aSegments) == 0)
        {
            return $aSegments;
        }
        
        if ( $aLocated = $this->locate($aSegments))
        {
            return $aLocated;
        }
        
        show_error('Página no encotrada: ' . $aSegments[0], 404);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Localizar controlador válido
     * 
     * @access public
     * @param array $aSegments
     * @return array
     */
    public function locate($aSegments)
    {
        $sExt = '.controller.php';
        
        if (isset($aSegments[0]) && $aRoutes = $this->module->parseRoutes($aSegments[0], implode('/', $aSegments)))
        {
            $aSegments = $aRoutes;
        }
        
        // Obtener las variable desde los segmentos.
        list($sModule, $sDirectory, $sController) = array_pad($aSegments, 3, null);
        
        // Existe el directorio...
        if (is_dir($sModulePath = MOD_PATH . $sModule . DS . 'controller' . DS))
        {
            $this->_sModule = $sModule;
            $this->_sDirectory = $sModulePath;

            // Existe un sub-controlador del módulo?
            if ($sDirectory && is_file($sModulePath . $sDirectory . $sExt))
            {
                return array_slice($aSegments, 1);
            }
            
            // Existe un sub-directorio del módulo?
            if ($sDirectory && is_dir($sModulePath . $sDirectory . DS))
            {
                $sModulePath = $sModulePath . $sDirectory . DS;
                $this->_sDirectory .= $sDirectory . DS;
                
                // Existe el controlador en el sub-directorio
                if (is_file($sModulePath . $sDirectory . $sExt))
                {
                    return array_slice($aSegments, 1); 
                }
                
                // Existe un sub-controlador en el sub-directorio?
                if( $sController && is_file($sModulePath . $sController . $sExt))
                {
                    return array_slice($aSegments, 2);
                }
            }
            
            if (is_file($sModulePath . $sModule . $sExt))
            {
                return $aSegments;
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
        return $this->_sModule;
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
        return $this->_sClass;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Establecer la clase.
     * 
     * Definimos el controlador.
     * 
     * @access public
     * @param string $sClass
     * @return void
     */
    public function setClass($sClass)
    {
        $this->_sClass = str_replace(array('/', '.'), '', $sClass);
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
        if ( $this->_sMethod == $this->getClass())
        {
            return 'index';
        }
        
        return $this->_sMethod;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Establecer el método
     * 
     * @access public
     * @param string $sMethod
     * @return void
     */
    public function setMethod($sMethod)
    {
        $this->_sMethod = $sMethod;
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
        return $this->_sDirectory;
    }
}