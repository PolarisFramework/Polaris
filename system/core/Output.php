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
 * Output
 * 
 * Responsable de enviar la salida final al navegador.
 * Recolecta el contenido de las vistas para mostrarlo al final de todo.
 * 
 * @package     Polaris
 * @subpackage  Core
 * @category    Library
 * @author      Ivan Molina Pavana <montemolina@live.com>
 * @link        http://polarisframework.com/docs/.html
 */
class Polaris_Output {
    
    /**
     * Contenido actual para mostrar.
     * 
     * @var string
     */
    private $_sOutputString = '';
    
    // --------------------------------------------------------------------
    
    /**
     * Obtener la salida actual
     * 
     * @access public
     * @return string
     */
    public function getOutput()
    {
        return $this->_sOutputString;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Establecer una nueva salida
     * 
     * @access public
     * @param string $sOutput
     * @return Output
     */
    public function setOutput($sOutput)
    {
        $this->_sOutputString = $sOutput;
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Agregar contenido a la salida actual
     * 
     * @access public
     * @param string $sOutput
     * @return void
     */
    public function appendOutput($sOutput)
    {
        $this->_sOutputString .= $sOutput;
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Mostrar la salida
     * 
     * Esta función finaliza la salida de datos al navegador. 
     * 
     * @access public
     * @param string $sOutput
     * @return mixed
     */
    public function display($sOutput = '')
    {
        //
        global $oTimer;
        
        // Datos de salida
        if ( $sOutput == '')
        {
            $sOutput =& $this->_sOutputString;
        }
        
        // Debug Time/Memory
        $sElapsed = $oTimer->elapsed_time('total_execution_time_start', 'total_execution_time_end');
        $sMemory = $oTimer->memory_usage(true);
        
        $sOutput = str_replace('{elapsed_time}', $sElapsed, $sOutput);
        $sOutput = str_replace('{memory_usage}', $sMemory, $sOutput);
        
        // Mostramos la salida
        echo $sOutput;
        
        return true;
    }
}