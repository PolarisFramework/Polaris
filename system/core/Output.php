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
    private $outputString = '';
    
    // --------------------------------------------------------------------
    
    /**
     * Obtener la salida actual
     * 
     * @access public
     * @return string
     */
    public function getOutput()
    {
        return $this->outputString;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Establecer una nueva salida
     * 
     * @access public
     * @param string $output
     * @return Output
     */
    public function setOutput($output)
    {
        $this->outputString = $output;
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Agregar contenido a la salida actual
     * 
     * @access public
     * @param string $output
     * @return void
     */
    public function appendOutput($output)
    {
        $this->outputString .= $output;
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Mostrar la salida
     * 
     * Esta función finaliza la salida de datos al navegador. 
     * 
     * @access public
     * @param string $output
     * @return mixed
     */
    public function display($output = '')
    {
        //
        global $timer;
        
        // Datos de salida
        if ( $output == '')
        {
            $output =& $this->outputString;
        }
        
        // Debug Time/Memory
        $elapsed = $timer->elapsedTime('total_execution_time_start', 'total_execution_time_end');
        $memory = $timer->memoryUsage(true);
        
        $output = str_replace('{elapsed_time}', $elapsed, $output);
        $output = str_replace('{memory_usage}', $memory, $output);
        
        // Mostramos la salida
        echo $output;
        
        return true;
    }
}