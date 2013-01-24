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
* URI
*
* Obtiene el URI desde el navegador y lo parte en segmentos que serán
* analizados por el Router.
*
* @package     Polaris
* @subpackage  Core
* @category    Library
* @author      Ivan Molina Pavana <montemolina@live.com>
* @link        http://polarisframework.com/docs/.html
*/
class Polaris_URI {
    
    /**
     * URI String
     * 
     * @var string
     */
    public $sURIString = '';
    
    /**
     * Segmentos
     * 
     * @var array
     */
    public $aSegments = array();
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->config =& load_class('Config', 'core');
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Buscamos la URI
     * 
     * @access public
     * @return void
     */
    public function fetchURIString()
    {
        if ($sURI = $this->_detectURI())
        {
            $this->setURIString($sURI);
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Detectar la URI del navegador.
     * 
     * @access private
     * @return string
     */
    private function _detectURI()
    {
        $sUri = $_SERVER['REQUEST_URI'];
        
        // Solo queremos lo que está antes del '?'
        $aParts = preg_split('#\?#i', $sUri, 2);
        $sUri = $aParts[0];
        
        // Página principal?
        if ($sUri == '/' || empty($sUri))
        {
            return '/';
        }
        
        // Limpiamos...
        return str_replace(array('//', '../'), '/', trim($sUri, '/'));
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Re-indexar los Segmentos
     * 
     * Esta función reordena el arreglo de segmentos para
     * que el index inicie en 1 y no en 0, esta para un fácil
     * manejo de la URI.
     * 
     * @access public
     * @return void
     */
    public function reindexSegments()
    {
        array_unshift($this->aSegments, NULL);
        unset($this->aSegments);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Creamos un arreglo con los segmentos de la URI
     * 
     * @access public
     * @return void
     */
    public function explodeSegments()
    {
        foreach ( explode('/', preg_replace('|/*(.+?)/*$|', '\\1', $this->sURIString)) as $sVal)
        {
            $sVal = trim($this->_filterURI($sVal));
            
            if ( $sVal != '')
            {
                $this->aSegments[] = $sVal;
            }
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Filtrar un segmento recibido en URI.
     * 
     * @access private
     * @param string $sVal
     * @return string
     */
    private function _filterUri($sVal)
    {
        if ( $sVal != '' && $this->config->get('permitted_uri_chars') != '')
        {
            if ( ! preg_match('|^['.str_replace(array('\\-', '\-'), '-', preg_quote($this->config->get('permitted_uri_chars'), '-')).']+$|i', $sVal))
            {
                show_error('El URI enviado contiene caracteres no permitidos.', 400);
            }
        }
        
        return $sVal;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Set URI String
     * 
     * @access private
     * @param string $sUri
     * @return void
     */
    public function setUriString($sUri)
    {
        $this->sURIString = ($sUri == '/') ? '' : $sUri;
    }
}