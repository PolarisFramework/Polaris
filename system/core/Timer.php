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
    private $_aPoints = array();
    
    /**
     * Marcar un punto
     * 
     * @access public
     * @param string $sName Nombre del punto
     * @return void
     */
    public function mark($sName)
    {
        $this->_aPoints[$sName] = microtime();
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Calcula la diferencia de tiempo entre dos puntos marcados.
     * 
     * @access public
     * @param string $sPoint1
     * @param string $sPoint2
     * @param integer $iDecimals
     * @return mixed
     */
    public function elapsed_time($sPoint1 = '', $sPoint2 = '', $iDecimals = 3)
    {
        if ( $sPoint1 == '')
        {
            return '{elapsed_time}';
        }
        
        if ( ! isset($this->_aPoints[$sPoint1]))
        {
            return '';
        }
        
        if ( ! isset($this->_aPoints[$sPoint2]))
        {
            $this->_aPoints[$sPoint2] = microtime();
        }
        
        list($sm, $ss) = explode(' ', $this->_aPoints[$sPoint1]);
        list($em, $es) = explode(' ', $this->_aPoints[$sPoint2]);
        
        return number_format(($em + $es) - ($sm + $ss), $iDecimals);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Memoria usada
     * 
     * Retorna la cantidad de memoria consumida por el script.
     * Formatea la cantidad en KB o MB según corresponda.
     * 
     * @access public
     * @param bool $bCalculate Determina si se calculará el consumo o retornará la variable en la plantilla
     * @return string
     */
    public function memory_usage($bCalculate = false)
    {
        if ( ! $bCalculate)
        {
            return '{memory_usage}';
        }
        
        $aUnits = array('bytes', 'kb', 'mb');
   
        $fBase = log(memory_get_usage() - START_MEM) / log(1024);
        
        return round(pow(1024, $fBase - floor($fBase)), 2) . ' ' . $aUnits[floor($fBase)];
    }
}