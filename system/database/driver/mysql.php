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
 * MySQL Driver
 * 
 * @package     Polaris
 * @subpackage  Core
 * @category    Library
 * @author      Ivan Molina Pavana <montemolina@live.com>
 * @link        http://polarisframework.com/docs/.html
 */
class Polaris_Database_Driver_Mysql extends Polaris_Database_Driver {
    
    /**
     * Conexión no persistente a la DB
     * 
     * @access public
     * @return resource
     */
    public function db_connect()
    {
        return @mysql_connect($this->hostname, $this->username, $this->password, true);   
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Conexión  persistente a la DB
     * 
     * @access public
     * @return resource
     */
    public function db_pconnect()
    {
        return @mysql_pconnect($this->hostname, $this->username, $this->password);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Seleccionar base de datos
     * 
     * @access public
     * @return resource
     */
    public function db_select()
    {
        return @mysql_select_db($this->database, $this->conn_id);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Establecer char set
     * 
     * @access public
     * @param string $charset
     * @param string $collation
     * @return resource
     */
    public function db_set_charset($charset, $collation)
    {
        return @mysql_query("SET NAMES '".$this->escape_str($charset)."' COLLATE '".$this->escape_str($collation)."'", $this->conn_id);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Escapar cadena
     * 
     * @access public
     * @param string $str
     * @param bool $like
     * @return string
     */
    public function escape_str($str)
    {
		if (is_array($str))
		{
			foreach ($str as $key => $val)
	   		{
				$str[$key] = $this->escape_str($val, $like);
	   		}

	   		return $str;
	   	}

		if (function_exists('mysql_real_escape_string') AND is_resource($this->conn_id))
		{
			$str = mysql_real_escape_string($str, $this->conn_id);
		}
		elseif (function_exists('mysql_escape_string'))
		{
			$str = mysql_escape_string($str);
		}
		else
		{
			$str = addslashes($str);
		}

		return $str;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Realiza la consulta SQL
     * 
     * @access public
     * @param string $sSql
     * @param resource $hLink
     * @return resource
     */
    public function query($sql)
    {
        $result = @mysql_query($sql, $this->conn_id);
        
        if ( ! $result)
        {
            show_error('Query error: ' . $sql);
        }
        
        return $result;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * 
     * 
     * @access public
     * @return int
     */
    public function get_last_id()
    {
        return @mysql_insert_id($this->conn_id);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Cerrar conexión
     * 
     * @access public
     * @return void
     */
    public function close()
    {
        return @mysql_close($this->conn_id);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Devuelve exactamente una fila como un array. Si existe un número de filas
     * que satisfacen la condición, entonces el primero será devuelto.
     * 
     * @access protected
     * @param string $sql
     * @param bool $assoc
     * @return array
     */
    protected function _get_row($sql, $assoc = true)
    {
        // Ejecutamos la consulta
        $result = $this->query($sql);
        
        // Obtenemos el arreglo
        $data = mysql_fetch_array($result, ($assoc ? MYSQL_ASSOC : MYSQL_NUM));
        
        return ($data ? $data : array());
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Obtiene los datos de la consulta
     * 
     * @access protected
     * @param string $sql
     * @param bool $assoc
     * @return array
     */
    protected function _get_rows($sql, $assoc = true)
    {
        $rows = array();
        $assoc = ($assoc ? MYSQL_ASSOC : MYSQL_NUM);
        
        // Ejecutamos la consulta
        $this->rquery = $this->query($sql);
        
        while($row = mysql_fetch_array($this->rquery, $assoc))
        {
            $rows[] = $row;
        }
        
        return $rows;
    }
}