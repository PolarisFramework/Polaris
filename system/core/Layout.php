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
     * Layout
     * 
     * @var string
     */
    private $_layout = 'default';
    
    /**
     * Título de la página.
     * 
     * @var string
     */
    private $_title = array();
    
    /**
     * Meta tags
     * 
     * @var array
     */
    private $_meta = array();
    
    /**
     * CSS
     * 
     * @var array
     */
    private $_css = array();
    
    /**
     * Javascript
     * 
     * @var array
     */
    private $_js = array();
    
    /**
     * Constructor
     */
    public function init($object = null)
    {
        // Referencia al Super Controlador
        $this->object = $object;
        
        // Configuraciones
        $layout = array();
        $layoutPath = APP_PATH . 'config' . DS . 'layout.php';
        
        if ( ! file_exists($layoutPath))
        {
            show_error('No se encuentra el archivo de configuración: layout.php');
        }
        
        include $layoutPath;
        
        // Asignamos config a variables de la clase.
        foreach ( array('_layout', '_css', '_js') as $key)
        {
            $this->{$key} = $layout[substr($key, 1)];
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
     * @param string $name
     * @return object
     */
    public function __get($name)
    {
        return (isset($this->object->{$name})) ? $this->object->{$name} : null;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Establecer un layout
     * 
     * @access public
     * @param string $layout
     * @return Layout
     */
    public function set_layout($layout)
    {
        $this->_layout = $layout;
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Agregar titulo
     * 
     * @access public
     * @param string $title
     * @return Layout
     */
    public function title($title)
    {
        $this->_title[] = $title;
        
        $this->meta('og:site_name', $this->config->get('site_title'));
        $this->meta('og:title', $title);
        
        return $this; 
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Metadatos
     * 
     * @access public
     * @param array $meta
     * @param string $value
     * @return Layout
     */
    public function meta($meta, $value = null)
    {
        if ( ! is_array($meta))
        {
            $meta = array($meta => $value);
        }
        
        foreach ($meta as $key => $value)
        {
            if ($key == 'description')
            {
                $this->_meta['og:description'] = $value;
            }
            
            if ( isset($this->_meta[$key]))
            {
                $this->_meta[$key] .= ($key == 'keywords' ? ', ' : ' ') . $value;
            }
            else
            {
                $this->_meta[$key] = $value;
            }
        }
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Agregar archivos CSS
     * 
     * @access public
     * @param array $data Un arreglo de archivos o un solo archivo.
     * @return Layout
     */
    public function css($data = array())
    {
        if ( ! is_array($data))
        {
            $data = array($data);
        }
        
        foreach ($data as $css)
        {
            $this->_css[] = $css;
        }
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Agregar archivos JS
     * 
     * @access public
     * @param array $data Un arreglo de archivos o un solo archivo.
     * @return Layout
     */
    public function js($data = array())
    {
        if ( ! is_array($data))
        {
            $data = array($data);
        }
        
        foreach ($data as $js)
        {
            $this->_js[] = $js;
        }
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Mostrar Layout
     * 
     * @access public
     * @param string $view
     * @param array $data
     * @return void
     */
    public function show($view, $data = array())
    {
        // Ruta del layout
        $layoutPath = APP_PATH . 'layout' . DS . $this->_layout . '.php';
        
        // Contenido del layout
        $output = file_get_contents($layoutPath);
        
        // Cargamos el contenido de la vista.
        $content = $this->load->view($view, $data, true);
        
        // Analizamos
        $output = $this->_parse($output, $content);
        
        // Agregamos a la salida.
        $this->output->appendOutput($output);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Analizar el contenido
     * 
     * @access private
     * @param string $layout
     * @param string $content
     * @return string
     */
    private function _parse($layout, $content)
    {
        $layout = str_replace('{content}', $content, $layout);
        
        $layout = str_replace('{title}', $this->_getTitle(), $layout);
        
        $layout = str_replace('{meta}', $this->_getMeta(), $layout);
        
        $layout = str_replace('{style}', $this->_getStyles(), $layout);
        
        $layout = str_replace('{script}', $this->_getScripts(), $layout);
        
        return $layout;
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
        $titles = '';        
        foreach ($this->_title as $title)
        {
            $titles .= $title . ' ' . $this->config->get('site_title_delim') . ' ';
        }
                
        $titles .= $this->config->get('site_title');
                
        return $titles;
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
        $metas = '';
        foreach ($this->_meta as $name => $value)
        {
            $metas .= "\n\t" . '<meta property="' . $name . '" content="' . $value . '" />';
        }
        
        return $metas;
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
        $styles = '';
        foreach ($this->_css as $css)
        {
            $styles .= "\n\t" . '<link href="'. $this->config->get('url_static_css') . $css .'" rel="stylesheet">';
        }
        
        return $styles;
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
        $scripts = '';
        foreach ($this->_js as $js)
        {
            $scripts .= "\n\t" . '<script src="'. $this->config->get('url_static_js') . $js .'" type="text/javascript"></script>';
        }
        
        return $scripts;
    }
}