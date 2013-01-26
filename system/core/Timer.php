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
 * Timer
 * 
 * Esta clase le permite marcar puntos y calcular la diferencia de tiempo
 * entre ellos. 
 * 
 * @subpackage  Core
 * @category    Library
 * @author      Ivan Molina Pavana <montemolin
 * @package     Polarisa@live.com>
 * @link        http://polarisframework.com/docs/.html
 */
class Polaris_Timer {
    
    /**
     * Puntos
     * 
     * @var array
     */
    private $points = array();
    
    /**
     * Marcar un punto
     * 
     * @access public
     * @param string $name Nombre del punto
     * @return void
     */
    public function mark($name)
    {
        $this->points[$name] = microtime();
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Calcula la diferencia de tiempo entre dos puntos marcados.
     * 
     * @access public
     * @param string $point1
     * @param string $point2
     * @param integer $decimals
     * @return mixed
     */
    public function elapsedTime($point1 = '', $point2 = '', $decimals = 3)
    {
        if ( $point1 == '')
        {
            return '{elapsed_time}';
        }
        
        if ( ! isset($this->points[$point1]))
        {
            return '';
        }
        
        if ( ! isset($this->points[$point2]))
        {
            $this->points[$point2] = microtime();
        }
        
        list($sm, $ss) = explode(' ', $this->points[$point1]);
        list($em, $es) = explode(' ', $this->points[$point2]);
        
        return number_format(($em + $es) - ($sm + $ss), $decimals);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Memoria usada
     * 
     * Retorna la cantidad de memoria consumida por el script.
     * Formatea la cantidad en KB o MB según corresponda.
     * 
     * @access public
     * @param bool $calculate Determina si se calculará el consumo o retornará la variable en la plantilla
     * @return string
     */
    public function memoryUsage($calculate = false)
    {
        if ( ! $calculate)
        {
            return '{memory_usage}';
        }
        
        $units = array('bytes', 'kb', 'mb');
   
        $base = log(memory_get_usage() - START_MEM) / log(1024);
        
        return round(pow(1024, $base - floor($base)), 2) . ' ' . $units[floor($base)];
    }
}