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
 * Layout
 * 
 * Esta clase nos facilita el uso de las vistas.
 * 
 * @package     Polaris
 * @subpackage  Core
 * @category    Library
 * @author      Ivan Molina Pavana <montemolina@live.com>
 * @link        http://polarisframework.com/docs/.html
 */
class Polaris_Layout {
    
    /**
     * Configuración
     * 
     * @var array
     */
    private $_aConfig = array();
    
    /**
     * Layout
     * 
     * @var string
     */
    private $layout = 'default';
    
    /**
     * Título de la página.
     * 
     * @var string
     */
    private $title = array();
    
    /**
     * Meta tags
     * 
     * @var array
     */
    private $meta = array();
    
    /**
     * CSS
     * 
     * @var array
     */
    private $css = array();
    
    /**
     * Javascript
     * 
     * @var array
     */
    private $js = array();
    
    /**
     * Constructor
     */
    public function init($oController = null)
    {
        if (is_a($oController, 'App_Controller'))
        {
            // Referencia al controlador
            $this->controller = $oController;
        }
        
        $layout = array();
        
        $sLayoutPath = APP_PATH . 'config' . DS . 'layout.php';
        
        if ( ! file_exists($sLayoutPath))
        {
            show_error('No se encuentra el archivo de configuración: layout.php');
        }
        
        include $sLayoutPath;
        
        foreach ( array('layout', 'css', 'js') as $sKey)
        {
            $this->{$sKey} = $layout[$sKey];
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
        $oObject =& get_instance();
        
        return (isset($this->controller->{$sName})) ? $this->controller->{$sName} : $oObject->{$sName};
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Establecer un layout
     * 
     * @access public
     * @param string $sLayout
     * @return Layout
     */
    public function set_layout($sLayout)
    {
        $this->layout = $sLayout;
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Agregar titulo
     * 
     * @access public
     * @param string $sTitle
     * @return Layout
     */
    public function title($sTitle)
    {
        $this->title[] = $sTitle;
        
        $this->meta('og:site_name', $this->config->get('site_title'));
        $this->meta('og:title', $sTitle);
        
        return $this; 
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Metadatos
     * 
     * @access public
     * @param array $mMeta
     * @param string $sValue
     * @return Layout
     */
    public function meta($mMeta, $sValue = null)
    {
        if ( ! is_array($mMeta))
        {
            $mMeta = array($mMeta => $sValue);
        }
        
        foreach ($mMeta as $sKey => $sValue)
        {
            if ($sKey == 'description')
            {
                $this->meta['og:description'] = $sValue;
            }
            
            if ( isset($this->meta[$sKey]))
            {
                $this->meta[$sKey] .= ($sKey == 'keywords' ? ', ' : ' ') . $sValue;
            }
            else
            {
                $this->meta[$sKey] = $sValue;
            }
        }
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Agregar archivos CSS
     * 
     * @access public
     * @param array $aData Un arreglo de archivos o un solo archivo.
     * @return Layout
     */
    public function css($aData = array())
    {
        if ( ! is_array($aData))
        {
            $aData = array($aData);
        }
        
        foreach ($aData as $css)
        {
            $this->css[] = $css;
        }
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Agregar archivos JS
     * 
     * @access public
     * @param array $aData Un arreglo de archivos o un solo archivo.
     * @return Layout
     */
    public function js($aData = array())
    {
        if ( ! is_array($aData))
        {
            $aData = array($aData);
        }
        
        foreach ($aData as $js)
        {
            $this->js[] = $js;
        }
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Mostrar Layout
     * 
     * @access public
     * @param string $sView
     * @param array $aData
     * @return void
     */
    public function show($sView, $aData = array())
    {
        // Ruta del layout
        $sLayoutPath = APP_PATH . 'layout' . DS . $this->layout . '.php';
        
        // Contenido del layout
        $sOutput = file_get_contents($sLayoutPath);
        
        // Cargamos el contenido de la vista.
        $sContent = $this->load->view($sView, $aData, true);
        
        // Analizamos
        $sOutput = $this->_parse($sOutput, $sContent);
        
        // Agregamos a la salida.
        $this->output->appendOutput($sOutput);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Analizar el contenido
     * 
     * @access private
     * @param string $sLayout
     * @param string $sContent
     * @return string
     */
    private function _parse($sLayout, $sContent)
    {
        $sLayout = str_replace('{content}', $sContent, $sLayout);
        
        $sLayout = str_replace('{title}', $this->_getTitle(), $sLayout);
        
        $sLayout = str_replace('{meta}', $this->_getMeta(), $sLayout);
        
        $sLayout = str_replace('{style}', $this->_getStyles(), $sLayout);
        
        $sLayout = str_replace('{script}', $this->_getScripts(), $sLayout);
        
        return $sLayout;
    }
    
    
    // --------------------------------------------------------------------
    
    /**
     * Crear título del sitio
     * 
     * @access private
     * @return string
     */
    private function _getTitle()
    {
        $sTitle = '';        
        foreach ($this->title as $title)
        {
            $sTitle .= $title . ' ' . $this->config->get('site_title_delim') . ' ';
        }
                
        $sTitle .= $this->config->get('site_title');
                
        return $sTitle;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Generar meta tags
     * 
     * TODO: Agregar algo de limpieza a los valores...
     * 
     * @access private
     * @return string
     */
    private function _getMeta()
    {
        $sMeta = '';
        foreach ($this->meta as $meta => $value)
        {
            $sMeta .= "\n\t" . '<meta property="' . $meta . '" content="' . $value . '" />';
        }
        
        return $sMeta;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Generar estilos
     * 
     * @access private
     * @return string
     */
    private function _getStyles()
    {
        $sStyles = '';
        foreach ($this->css as $css)
        {
            $sStyles .= "\n\t" . '<link href="'. $this->config->get('url_static_css') . $css .'" rel="stylesheet">';
        }
        
        return $sStyles;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Generar scripts
     * 
     * @access private
     * @return string
     */
    private function _getScripts()
    {
        $sScripts = '';
        foreach ($this->js as $js)
        {
            $sScripts .= "\n\t" . '<script src="'. $this->config->get('url_static_css') . $js .'" type="text/javascript"></script>';
        }
        
        return $sScripts;
    }
}