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
    require DB_PATH . 'Interface.php';
// ------------------------------------------------------------------------

/**
 * Clase padre para todos los SQL Drivers.
 * 
 * @package     Polaris
 * @subpackage  Core
 * @category    Library
 * @author      Ivan Molina Pavana <montemolina@live.com>
 * @link        http://polarisframework.com/docs/.html
 */
abstract class Polaris_Database_Driver implements Polaris_Database_Interface {
    
    /**
     * --------------------------------------------------------------------
     * Variables de configuración. Los nombres corresponden a los 'key' del
     * arreglo $db en el archivo database.php
     * 
     * No usamos "camelCase" en el manejo de las BD.
     * --------------------------------------------------------------------
     */
    public $hostname;
    public $username;
    public $password;
    public $database;
    public $dbdriver = 'mysql';
    public $pconnect = true;
    public $db_debug = false;
    public $char_set = 'utf8';
    public $dbcollat = 'utf8_general_ci';
    public $autoinit = true;
    
    /**
     * Recurso
     * 
     * @var resource
     */
    protected $conn_id = null;
    
    /**
     * Arreglo con las consultas que vamos a ejecutar.
     * 
     * @var array
     */
    protected $query = array();
    
    /**
     * Consulta simple
     * 
     * @var string
     */
    protected $squery = '';
    
    /**
     * Query Result
     * 
     * @var resource
     */
    protected $rquery = null;
    
    /**
     * Constructor
     * 
     * @access public
     * @param array $params
     */
    public function __construct($params)
    {
        if ( is_array($params))
        {
            foreach ($params as $key => $value)
            {
                $this->{$key} = $value;
            }
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Inicializa la base de datos con los parámetros provistos.
     * 
     * @access public
     * @return void
     */
    public function init()
    {
        if ( is_resource($this->conn_id) || is_object($this->conn_id))
        {
            return true;
        }
        
        $this->conn_id = ($this->pconnect == false) ? $this->db_connect() : $this->db_pconnect();
        
        if ( ! $this->conn_id)
        {
            show_error('No se puede conectar al servidor de base de datos mediante la configuración suministrada.');
            
            return false;
        }
        
        // Seleccionar la BD
        if ($this->database != '')
        {
            if ( ! $this->db_select())
            {
                show_error('No se puede seleccionar la base de datos especificada: ' . $this->database);
            }
            else
            {
                if ( ! $this->db_set_charset($this->char_set, $this->dbcollat))
                {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Devuelve una fila.
     * 
     * @access public
     * @param string $sql
     * @param bool $assoc
     * @return array
     */
    public function get_row($sql, $assoc = true)
    {
        return $this->_get_row($sql, $assoc);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Devuelve varias filas.
     * 
     * @access public
     * @param string $sql
     * @param bool $assoc
     * @return array
     */
    public function get_rows($sql, $assoc = true)
    {
        return $this->_get_rows($sql, $assoc);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Devuelve un campo de una fila
     * 
     * @access public
     * @param string $sql
     * @return mixed
     */
    public function get_field($sql)
    {
        $result = '';
        $row = $this->_get_row($sql, false);
        
        if ( $row)
        {
            $result = $row[0];
        }
        
        return $result;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Consulta simple
     * 
     * @access public
     * @param string $sql
     * @return mixed
     */
    public function simple_query($sql)
    {
        // Reset
        $this->query = array();
        
        // Asignamos
        $this->squery = $sql;
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Almacena la parte SELECT de una consulta.
     * 
     * @access public
     * @param string $select
     * @return object
     */
    public function select($select)
    {
        if ( ! isset($this->query['select']))
        {
            $this->query['select'] = 'SELECT ';
        }
        
        $this->query['select'] .= $select;
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Almacena la parte FROM de una consulta.
     * 
     * @access public
     * @param string $table
     * @param string $alias
     * @return object
     */
    public function from($table, $alias = '')
    {
        $this->query['table'] = 'FROM ' . $table . ($alias ? ' AS ' . $alias : '');
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Almacena la parte WHERE de una consulta.
     * 
     * @access public
     * @param mixed $conds
     * @return object
     */
    public function where($conds)
    {
        $this->query['where'] = '';
        if ( is_array($conds) && count($conds) > 0)
        {
            foreach ($conds as $value)
            {
                $this->query['where'] .= $value . ' ';
            }
            
            $this->query['where'] = 'WHERE ' . trim(preg_replace('/^(AND|OR)(.*?)/i', '', trim($this->query['where'])));
        }
        else
        {
            if ( ! empty($conds))
            {
                $this->query['where'] = 'WHERE ' . $conds;
            }
        }
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Almacena la parte ORDER de una consulta.
     * 
     * @access public
     * @param string $order
     * @return object
     */
    public function order($order)
    {
        if ( ! empty($order))
        {
            $this->query['order'] = 'ORDER BY ' . $order;
        }
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Almacena la parte GROUP BY de una consulta.
     * 
     * @access public
     * @param string $group
     * @return object
     */
    public function group($group)
    {
        $this->query['group'] = 'GROUP BY ' . $group;
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Almacena la parte HAVING de una consulta.
     * 
     * @access public
     * @param string $having
     * @return object
     */
    public function having($having)
    {
        $this->query['having'] = 'HAVING ' . $having;
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Creamos un LEFT JOIN para la consulta.
     * 
     * @see self::_join()
     * @access public
     * @param string $table
     * @param string $alias
     * @param mixed $param
     * @return object
     */
    public function left_join($table, $alias, $param = null)
    {
        $this->_join('LEFT JOIN', $table, $alias, $param);
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Creamos un INNER JOIN para la consulta.
     * 
     * @see self::_join()
     * @access public
     * @param string $table
     * @param string $alias
     * @param mixed $param
     * @return object
     */
    public function inner_join($table, $alias, $param = null)
    {
        $this->_join('INNER JOIN', $table, $alias, $param);
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Creamos un JOIN para la consulta.
     * 
     * @see self::_join()
     * @access public
     * @param string $table
     * @param string $alias
     * @param mixed $param
     * @return object
     */
    public function join($table, $alias, $param = null)
    {
        $this->_join('JOIN', $table, $alias, $param);
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Almacena la parte LIMIT / OFFSET de una consulta.
     * 
     * También se puede utilizar para crear una paginación si $limit y $total son
     * enviados, de lo contrario se comporta como un LIMIT en la consulta.
     * 
     * @access public
     * @param integer $page
     * @param integer $limit
     * @param integer $total
     * @param bool $return
     */
    public function limit($page, $limit = null, $total = null, $return = false)
    {
        if ( $limit === null && $total === null && $page !== null)
        {
            $this->query['limit'] = 'LIMIT ' . $page;
            
            return $this;
        }
        
        $offset = ($total === null ? $page : load_class('Pager')->getOffset($page, $limit, $total));
        
        $this->query['limit'] = ($limit ? 'LIMIT ' . $limit : '') . ($offset ? ' OFFSET ' . $offset : '');
        
        if ($return == true)
        {
            return $this->query['limit'];
        }
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Lleva a cabo la última consulta SQL con toda la información que hemos
     * recogido de otros métodos de esta clase. A través de este método se
     * pueden realizar todas las tareas de conseguir un único campo de una fila,
     * sólo una fila o una lista de filas.
     * 
     * @see self::get_row()
     * @see self::get_rows()
     * @see self::get_field()
     * @param string $type El comando que vamos a ejecutar. Puede ser null para devolver simplementa la consulta SQL.
     * @return mixed
     */
    public function execute($type = null)
    {
        $sql = '';
        
        if ( empty($this->query))
        {
            $sql = $this->squery;
        }
        else
        {
            if ( isset($this->query['select']))
            {
                $sql .= $this->query['select'] . "\n";
            }
            
            if ( isset($this->query['table']))
            {
                $sql .= $this->query['table'] . "\n";
            }
            
            $sql .= (isset($this->query['join']) ? $this->query['join'] . "\n" : '');
            $sql .= (isset($this->query['where']) ? $this->query['where'] . "\n" : '');
            $sql .= (isset($this->query['group']) ? $this->query['group'] . "\n" : '');
            $sql .= (isset($this->query['having']) ? $this->query['having'] . "\n" : '');
            $sql .= (isset($this->query['order']) ? $this->query['order'] . "\n" : '');
            $sql .= (isset($this->query['limit']) ? $this->query['limit'] . "\n" : '');
            $sql .= '/* OO Query */';
            
            $this->query = array();
            
        }
        
        switch($type)
        {
            case 'row':
                $rows = $this->get_row($sql);
                break;
            case 'rows':
                $rows = $this->get_rows($sql);
                break;
            case 'field':
                $rows = $this->get_field($sql);
                break;
            default:
                return $sql;
                break;
        }
        
        return $rows;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Insertar una fila. Acepta datos enviados en un array.
     * 
     * @access public
     * @param string $table
     * @param array $data
     * @param bool $escape
     * @return int last_insert_id
     */
    public function insert($table, $data = array(), $escape = true)
    {
        $values = '';
        foreach($data as $val)
        {
            if (is_null($val))
            {
                $values .= 'NULL, ';
            }
            else
            {
                $values .= "'". ($escape ? $this->escape($val) : $val) . "', ";
            }
        }
        $values = rtrim(trim($values), ',');
        
        $sql = $this->_insert($table, implode(', ', array_keys($data)), $values);
        
        if ($result = $this->query($sql))
        {
            return $this->get_last_id();
        }
        
        return 0;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Actualizar datos.
     * 
     * @access public
     * @param string $table
     * @param array $values
     * @param string $cond
     * @param bool $escape
     * @return bool
     */
    public function update($table, $data, $cond = null, $escape = true)
    {
        $sets = '';
        foreach($data as $col => $val)
        {
            $cmd = '=';
            if (is_array($val))
            {
                $cmd = $val[0];
                $val = $val[1];
            }
            
            $sets = "{$col} {$cmd} " . (is_null($val) ? 'NULL' : ($escape ? "'" . $this->escape($val) . "'" : $val)) . ', ';
        }
        $sets[strlen($sets) - 2] = ' ';
        
        return $this->query($this->_update($table, $sets, $cond));
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Eliminar registro de la base de datos.
     * 
     * @access public
     * @param $table
     * @param $query
     * @param $limit
     * @return  bool
     */
    public function delete($table, $query, $limit = null)
    {
        if ($limit !== null)
        {
            $query .= 'LIMIT ' . (int) $limit;
        }
        
        return $this->query("DELETE FROM {$table} WHERE " . $query);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Actualizar un contador.
     * 
     * Esta función nos facilita realizar consultas del tipo:
     * 
     * UPDATE table SET counter = counter (+/-) 1 WHERE field = 1;
     * 
     * @access public
     * @param string $table
     * @param string $counter Campo que vamos a actualizar.
     * @param string $field Campo que debe coincidir para actualizar.
     * @param int $id Valor para el campo de coincidencia.
     * @param bool $minus Por defecto la variable se incrementa, cuando queramos disminuir debemos colocarla como true.
     * @return void
     */
    public function update_counter($table, $counter, $field, $id, $minus = false)
    {
        $count = $this->select($counter)->from($table)->where($field . ' = ' . (int) $id)->execute('field');
        
        $this->update($table, array($counter => ($minus === true ? (($count <= 0 ? 0 : $count - 1)) : ($count + 1))), $field . ' = ' . (int) $id);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Filtrar variables
     * 
     * @access public
     * @param string $str
     * @return mixed
     */
    public function escape($str)
    {
		if (is_string($str))
		{
			$str = "'".$this->escape_str($str)."'";
		}
		elseif (is_bool($str))
		{
			$str = ($str === false) ? 0 : 1;
		}
		elseif (is_null($str))
		{
			$str = 'NULL';
		}

		return $str;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Creamos un LEFT JOIN para la consulta.
     * 
     * @access protected
     * @param string $type
     * @param string $table
     * @param string $alias
     * @param mixed $param
     */
    protected function _join($type, $table, $alias, $param = null)
    {
        if ( ! isset($this->query['join']))
        {
            $this->query['join'] = '';
        }
        
        $this->query['join'] = $type . ' ' . $table . ' AS ' . $alias;
        
        if (is_array($param))
        {
            $this->query['join'] .= "\n\tON(";
            foreach ($param as $value)
            {
                $this->query['join'] .= $value . ' ';
            }
        }
        else
        {
            if (preg_match('/(AND|OR|=|LIKE)/', $param))
            {
                $this->query['join'] .= "\n\tON({$param}";
            }
            else
            {
                show_error('No es permitido el uso de "USING()" en las consultas SQL nunca más.');
            }
        }
        
        $this->query['join'] = preg_replace('/^(AND|OR)(.*?)/i', '', trim($this->query['join'])) . ")\n";
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Crear consulta para INSERT
     * 
     * @access protected
     * @param $table
     * @param $fields
     * @param $values
     * @return string SQL
     */
    protected function _insert($table, $fields, $values)
    {
		return 'INSERT INTO ' . $table . ' '.
        	'        (' . $fields . ')'.
            ' VALUES (' . $values . ')';
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Crear consulta UPDATE
     * 
     * @access protected
     * @param $table
     * @param $sets
     * @param $cond
     * @return string SQL
     */
	protected function _update($table, $sets, $cond)
	{
		return 'UPDATE ' . $table . ' SET ' . $sets . ' WHERE ' . $cond;
	}
}