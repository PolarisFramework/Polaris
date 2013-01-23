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
 * Loader
 * 
 * Se encargará de cargar los modelos, vistas y librerías que se requieran
 * dentro del controlador.
 * 
 * @package     Polaris
 * @subpackage  Core
 * @category    Library
 * @author      Ivan Molina Pavana <montemolina@live.com>
 * @link        http://polarisframework.com/docs/.html
 */
class Polaris_Loader {
    
    /**
     * Nivel del mecanismo de bufer.
     * 
     * @var int
     */
    private $_nObLevel;
    
    /**
     * Módulo cargado actualmente.
     * 
     * @var string
     */
    private $_sModule;
    
    /**
     * Listado de modelos cargados.
     * 
     * @var array
     */
    private $_aModels = array();
    
    /**
     * Cache de variables usadas por las vistas.
     * 
     * @var array
     */
    private $_aCachedVars = array();
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_nObLevel = ob_get_level();
    }
    
    /**
     * Inicializar...
     * 
     * @access public
     * @param object $oController Controlador desde fue cargado..
     * @return void
     */
    public function init($oController = null)
    {
        $oObject =& getInstance();
        
        $this->_sModule = $oObject->router->getModule();
        
        if (is_a($oController, 'App_Controller'))
        {
            // Referencia al controlador
            $this->controller = $oController;
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * __get
     * 
     * Esta función nos permitirá acceder a los recursos
     * del controlador base.
     * 
     * @access public
     * @param string $sName
     * @return object
     */
    public function __get($sName)
    {
        $oObject =& getInstance();
        
        return (isset($this->controller->{$sName})) ? $this->controller->{$sName} : $oObject->{$sName};
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Cargar un modelo.
     * 
     * @access public
     * @param string $sModel Nombre del modelo.
     * @param string $sObjectName Nombre alternativo para el modelo.
     * @param string $sModule Módulo en el cual buscar el modelo.
     * @return void
     */
    public function model($sModel, $sObjectName = null)
    {
        // Podemos enviar un arreglo con el nombre de los modelos y cargarlos.
        if (is_array($sModel))
        {
            foreach($sModel as $iModel)
            {
                $this->model($iModel);
            }
            
            return;
        }
        
        // Alias del modelo
        $sAlias = ($sObjectName !== null) ? $sObjectName : basename($sModel);
        
        // Controlador Base
        $oObject =& getInstance();
        
        // Ya hemos cargado este modelo?
        if (isset($oObject->{$sAlias}) && in_array($sAlias, $this->_aModels))
        {
            return $oObject->{$sAlias};
        }
        
        // Existe un recurso con el nombre del modelo?
        if( isset($oObject->{$sAlias}))
        {
            show_error('El alias del modelo que está cargando, es nombre de un recurso que está en uso: ' . $sAlias);
        }
        // Buscamos...
        list($sPath, $_sModel) = $this->module->find(strtolower($sModel), $this->_sModule, 'model/', '.model');
        
        if ($sPath !== false)
        {
            if ( !class_exists('Polaris_Model'))
            {
                loadClass('Model', 'core');
            }
            // Cargamos el archivo..
            $this->module->loadFile($_sModel . '.model', $sPath);
            
            // Nombre del modelo
            list($sModule, $sModel) = array_pad(explode('/', $sModel), 2, null);
            
            $sModel = ($sModel != null) ? ucfirst($sModule) . '_' . ucfirst($sModel) : ucfirst($sModule) . '_' . ucfirst($sModule);
            $sModel .= '_Model';
            
            $oObject->{$sAlias} = new $sModel();
            
            $this->_aModels[] = $sAlias;
            
            return $oObject->{$sAlias};
        }
        
        show_error('No se localizó el modelo espesificado: ' . $sModel);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Cargar variables
     * 
     * Carga las variables que estarán disponibles para la vista
     * llamda desde un controlador.
     * 
     * @access public
     * @param array $mVars Nombre de la variable ó arreglo de variables.
     * @param string $sValue Valor de la variable
     * @return Loader
     */
    public function set_var($mVars, $sValue = null)
    {
        if ( ! is_array($mVars))
        {
            $mVars = array($mVars => $sValue);
        }
        
        foreach( $mVars as $sKey => $sValue)
        {
            $this->_aCachedVars[$sKey] = $sValue;
        }
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Solicitar variable
     * 
     * Busca si se ha cargado una variable a la vista.
     * 
     * @access public
     * @param string $sVar
     * @return mixed
     */
    public function get_var($sVar)
    {
        return isset($this->_aCachedVars[$sVar]) ? $this->_aCachedVars[$sVar] : null;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Cargar una vista
     * 
     * @access public
     * @param string $_sView Nombre de la vista que será cargada.
     * @param array $_aVars Arreglo con las variables que serán asignadas a la vista.
     * @param bool $_bReturn En algunos casos es necesario regresar el contenido de la vista.
     * @return void
     */
    public function view($_sView, $_aVars = array(), $_bReturn = false)
    {
        // Buscamos la vista...
        list($_sPath, $_sView) = $this->module->find($_sView, $this->_sModule, 'view/', '.view');
        
        if ($_sPath != false)
        {
            // Archivo de la vista
            $_sViewPath = $_sPath . $_sView . '.view.php';
            
            // Extraer las variables
            if ( is_array($_aVars))
            {
                $this->_aCachedVars = array_merge($this->_aCachedVars, $_aVars);
            }
            
            extract($this->_aCachedVars);
            
            // Render...
            ob_start();
            
            // Incluimos la vista
            include $_sViewPath;
            
            // Queremos retornar el resultado?
            if ( $_bReturn === true)
            {
                $_sBuffer = ob_get_contents();
                @ob_end_clean();
                return $_sBuffer;
            }
            
            // Con el fin de permitir vistas anidadas, es necesario vaciar
            // el contenido cada vez que el nivel de buffer se inremente para así
            // incluir la plantilla adecuadamente.
            if (ob_get_level() > $this->_nObLevel + 1)
            {
                ob_end_flush();
            }
            else
            {
                $this->output->appendOutput(ob_get_clean());
            }
            
            return;
        }
        
        show_error('No se pudo cargar la vista: ' . $_sView);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Cargar el controlador de un módulo.
     * 
     * @access public
     * @param string $sModule Módulo/Controlador a cargar.
     * @param array $aParams Parámetros enviados al controlador
     * @return object
     */
    public function module($sModule, $aParams = array())
    {
        $sAlias = strtolower(basename($sModule));
        
        $oObject =& getInstance();
        
        $oObject->{$sAlias} = $this->module->load(array($sModule => $aParams));
        
        return $oObject->{$sAlias};
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Cargar un bloque
     * 
     * Esta función facilita la carga de módulos en una platilla.
     * 
     * @access public
     * @param string $sModule
     * @return void
     */
    public function block($sModule)
    {
        $oModule =& $this->module;
        $aArgs = func_get_args();
        
        $sResult = call_user_func_array(array($oModule, 'run'), $aArgs);
        
        echo $sResult;
    }
}