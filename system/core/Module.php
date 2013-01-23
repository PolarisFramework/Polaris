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
 * Clase para los módulos
 * 
 * Esta clase hace posible el uso de módulos.
 * 
 * @package     Polaris
 * @subpackage  Core
 * @category    Library
 * @author      Ivan Molina Pavana <montemolina@live.com>
 * @link        http://polarisframework.com/docs/.html
 */
class Polaris_Module {
    
    /**
     * Rutas de los módulos
     * 
     * @var array
     */
    private $_aRoutes = array();
    
    /**
     * Registro de módulos
     * 
     * @var array
     */
    private $_aRegistry = array();
    
    // --------------------------------------------------------------------
    
    /**
     * Correr un módulo
     * 
     * Esta función nos permite mandar a llamar al controlador/método 
     * de un módulo, el resultado lo almacenará en buffer y lo devolverá
     * 
     * @access public
     * @param string $sModule
     * @return string
     */
    public function run($sModule)
    {
        $sMethod = 'index';
        
        if (($nLastSlash = strrpos($sModule, '/')) !== false)
        {
            $sMethod = substr($sModule, $nLastSlash + 1);
            $sModule = substr($sModule, 0, $nLastSlash);
        }
        
        if ($oClass = $this->load($sModule))
        {
            if (method_exists($oClass, 'action_' . $sMethod))
            {
                // Buffer
                ob_start();
                
                // Argumentos que serán enviados.
                $aArgs = func_get_args();
                
                // Procedemos a ejecutar el módulo.
                $mResult = call_user_func_array(array($oClass, 'action_' . $sMethod), array_slice($aArgs, 1));
                $sBuffer = ob_get_clean();
                
                // Retornamos
                return ($mResult !== null) ? $mResult : $sBuffer;
            }
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Cargar un controlador de un módulo
     * 
     * @access public
     * @param string $sModule Nombre del modelo. Podemos enviar un arreglo donde el primer parámetro será el nombre y el segundo los parámetros para el controlador. 
     * @return object
     */
    public function load($sModule)
    {
        $aParams = null;
        
        if (is_array($sModule))
        {
            list($sModule, $aParams) = each($sModule);
        }
        
        //
        list($sModule, $sClass) = array_pad(explode('/', $sModule), 2, null);
        $sAlias = ($sClass != null) ? $sModule . '_' . $sClass : $sModule . '_' . $sModule;
        
        // Crear y retornar el controlador solicitado
        if ( ! isset($this->_aRegistry[$sAlias]))
        {
            // Buscamos el controlador
            list($sClass) = loadClass('Router', 'core')->locate(array($sModule, $sClass));
            
            // No existe el controlador...
            if (empty($sClass))
                return;
                
            // Nombre del controlador
            $sController = ucfirst($sModule) . '_' . ucfirst($sClass) . '_Controller';
            
            // Si la clase existe es porque el objeto tambien...
            if ( class_exists($sController, false))
                return;   
            
            $sPath = loadClass('Router')->getDirectory();
            $this->loadFile($sClass. '.controller', $sPath);
                                          
            // Crear y registrar el módulo
            $this->_aRegistry[$sAlias] = new $sController($aParams);
        }
        
        return $this->_aRegistry[$sAlias];
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Analizar las rutas de un módulo.
     * 
     * @access public
     * @param string $sModule
     * @param string $sURI
     * @return array
     */
    public function parseRoutes($sModule, $sURI = '')
    {
        // Cargamos el archivo de las rutas.
        if ( ! isset($this->_aRoutes[$sModule]))
        {
            // Existe?
            if((list($sPath) = $this->find('routes', $sModule, 'config/')) && $sPath)
            {
                $this->_aRoutes[$sModule] = $this->loadFile('routes', $sPath, 'route');
            }
        }
        
        if ( ! isset($this->_aRoutes[$sModule]))
        {
            return;
        }
        
        // Analizamos...
        foreach ($this->_aRoutes[$sModule] as $sKey => $sVal)
        {
            $sKey = str_replace(array(':any', ':num'), array('.+', '[0-9]+'), $sKey);
            
            if ( preg_match('#^'.$sKey.'$#', $sURI))
            {
                // Tenemos una variable de referencia?
                if (strpos($sVal, '$') !== false && strpos($sKey, '(') !== false)
                {
                    $sVal = preg_replace('#^'.$sKey.'$#', $sVal, $sURI);
                }
                
                return explode('/', $sModule . '/' . $sVal);
            }
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Buscar un archivo
     * 
     * Busca archivos en el directorio de un módulo.
     * 
     * @access public
     * @param string $sFile Nombre del archivo a buscar.
     * @param string $sModule Módulo en el cual buscarémos.
     * @param string $sBase Carpeta donde buscarémos.
     * @return array
     */
    public function find($sFile, $sModule, $sBase, $sSuffix = '')
    {
        $aSegments = explode('/', $sFile);
        $sBase = str_replace('/', DS, $sBase);
        
        $sFile = array_pop($aSegments);
        $sFileExt = (pathinfo($sFile, PATHINFO_EXTENSION)) ? $sFile : $sFile . $sSuffix . '.php';
        
        $sPath = ltrim(implode('/', $aSegments).'/', '/');
        
        $aModules = array();
        
        $sModule ? $aModules[$sModule] = $sPath : array();
        
        if ( ! empty($aSegments))
        {
            $aModules[array_shift($aSegments)] = ltrim(implode('/', $aSegments).'/', '/');
        }
        
        foreach ($aModules as $sModule => $sSubPath)
        {
            $sModulePath = MOD_PATH . $sModule . DS . $sBase . $sSubPath;
            
            if (is_file($sModulePath.$sFileExt))
            {
                return array($sModulePath, $sFile);
            }
        }
        
        return array(false, $sFile);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Cargar archivo de un módulo.
     * 
     * @access public
     * @param string $sFile Nombre del archivo.
     * @param string $sPath Ruta completa del archivo.
     * @param string $sType Si no cargamos un clase entonces estamos solicitando una variable, el tipo se convierte en el nombre de esa variable.
     * @param bool $sResult
     */
    public function loadFile($sFile, $sPath, $sType = 'class', $bResult = true)
    {
        $sFilePath = $sPath . $sFile . '.php';
        
        if ($sType === 'class')
        {
            if (class_exists($sFile, false))
            {
                return $bResult;
            }
            
            require $sFilePath;
        }
        else
        {
            // Cargamos el archivo
            require $sFilePath;
            
            // Comprobamos 
            if ( ! isset($$sType) || ! is_array($$sType))
            {
                show_error(str_replace(MOD_PATH, '', $sFilePath) . ' no contiene el arreglo $' . $sType);
            }
            
            $bResult = $$sType;
        }
        
        return $bResult;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Registrar un controlador
     * 
     * @access public
     * @param string $sClass
     * @param object $oObject
     * @return void
     */
    public function addClass($sClass, $oObject)
    {
        $this->_aRegistry[strtolower($sClass)] = $oObject;
    }
}