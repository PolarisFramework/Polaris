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
 * Form Validator
 * 
 * Validar formularios
 * 
 * @package     Polaris
 * @subpackage  Core
 * @category    Library
 * @author      Ivan Molina Pavana <montemolina@live.com>
 * @link        http://polarisframework.com/docs/.html
 */
class Polaris_Form {
    
    /**
     * Datos de los campos
     * 
     * @var array
     */
    private $_fieldData = array();
    
    /**
     * Lista de errores.
     * 
     * @var array
     */
    private $_errors = array();
    
    /**
     * Mensajes de error.
     * 
     * @var array
     */
    private $_errorMessage = array(
        'required' => 'El campo es requerido.',
        'matches' => 'Las contraseñas no son iguales.',
        'is_valid' => array(
            'user_name' => 'El nombre de usuario no es válido.',
            'email' => 'El correo enviado no es válido.'
        )
    );
    
    /**
     * Validar campos con Expresiones Regulares.
     * 
     * @var array
     */
    private $_regex = array(
        'user_name' => '/^[a-zA-Z0-9_\- ]{5,16}$/',
        'email' => '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix',
    );
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->object =& get_instance();
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Establecer reglas
     * 
     * @access public
     * @param array $field Nombre del input[] o un arreglo de reglas.
     * @param string $label Nombre del campo.
     * @param string $rules Reglas que debemos aplicar.
     * @param array $default Valor por defecto en caso de no encontrar uno.  
     * @return void
     */
    public function setRules($field, $label = '', $rules = '', $default = null)
    {
        if (count($this->object->request->getRequest()) == 0)
        {
            //return $this;
        }
        
        // Si enviamos un arreglo lo agregamos
        if (is_array($field))
        {
            foreach ($field as $row)
            {
                // El nombre del campo y las reglas son requeridas.
                if ( ! isset($row['field']) || (! isset($row['rules']) && ! isset($row['default'])))
                {
                    continue;
                }
                
                // Solucionamos datos no enviados.
                $label = ( ! isset($row['label'])) ? $row['field'] : $row['label'];
                $default = ( ! isset($row['default'])) ? null : $row['default'];
                $rules = ( ! isset($row['rules'])) ? '' : $row['rules'];
                
                $this->setRules($row['field'], $label, $rules, $default); 
            }
            
            return $this;
        }
        
        // Nada que hacer...
		if ( ! is_string($field) ||  ! is_string($rules) || $field == '')
		{
			return $this;
		}
        
        $label = ($label == '') ? $field : $label;
        
        // TODO: Agregar soporte para arreglos de campos.
        
        
        // Agregar a nuestro recolector
        $this->_fieldData[$field] = array(
            'field'     => $field,
            'label'     => $label,
            'rules'     => $rules,
            'default'   => $default,
            'value'     => '',
            'error'     => ''
        );
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Validar formulario
     * 
     * @access public
     * @return bool 
     */
    public function validate()
    {
        if (count($this->object->request->getRequest()) == 0)
        {
            //return $this;
        }
        
        // No hay reglas?
        if (count($this->_fieldData) == 0)
        {
            return false;
        }
        
        // Recorremos el arreglo
        foreach ($this->_fieldData as $field => $row)
        {
            // Tenemos un valor por defecto?
            $this->_fieldData[$field]['value'] = $this->object->request->get($field);
            
            $this->_execute($row, explode('|', $row['rules']), $this->_fieldData[$field]['value']);
        }
        
        if (count($this->_errors) == 0)
        {
            return true;
        }
        
        return false;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Ejecutar la validación
     * 
     * @access protected
     * @param array $row
     * @param array $rules
     * @param mixed $value
     * @return mixed
     */
    protected function _execute($row, $rules, $value = null)
    {
        // Si el valor es vacío y no es requerido no hay porq validar...
        $callback = false;
        if ( ! in_array('required', $rules) && is_null($value))
        {
            // Si hay una llamda entonces no podemos salir...
            if (preg_match("/(callback_\w+(\[.*?\])?)/", implode(' ', $rules), $match))
            {
                $callback = true;
                $rules = (array('1' => $match[1]));
            }
            else
            {
                return;
            }
        }
        
        // --------------------------------------------------------------------
        
        // Vamos a validar el formulario...
        foreach ($rules as $rule)
        {
            // Debemos llamar a una función?
			$callback = false;
			if (substr($rule, 0, 9) == 'callback_')
			{
				$rule = substr($rule, 9);
				$callback = true;
			}
            
            // Buscamos parámetros, las reglas pueden contener parámetros: min_length[3]
			$param = false;
			if (preg_match("/(.*?)\[(.*)\]/", $rule, $match))
			{
				$rule	= $match[1];
				$param	= $match[2];
			}
            
            // Llamar a la función que corresponde
            if ($callback == true)
            {
                if ( ! method_exists($this->object, $rule))
                {
                    continue;
                }
                
                // Corremos la función
                $result = $this->object->$rule($value, $param);
                
                // Reasignamos valor
                $this->_fieldData[$row['field']]['value'] = (is_bool($result)) ? $value : $result;
                
                // Si el campo no es requerido
				if ( ! in_array('required', $rules, true) && $result !== false)
				{
					continue;
				}
            }
            else
            {
                if ( ! method_exists($this, $rule))
                {
                    // Si no existe la regla en nuestros métodos tal vez sea una función nativa de PHP
                    if ( function_exists($rule))
                    {
                        $result = $rule($value);
                        $this->_fieldData[$row['field']]['value'] = (is_bool($result)) ? $value : $result;
                    }
                    
                    continue;
                }
                
                $result = $this->$rule($value, $param);
                $this->_fieldData[$row['field']]['value'] = (is_bool($result)) ? $value : $result;
            }
            
            // Si la validación es negativa... agregamos el error.
            if ($result === false)
            {
                // Buscamos el mensaje de error
                if ( ! isset($this->_errorMessage[$rule]))
                {
                    $line = 'No podemos determinar el error.';
                }
                else
                {
                    if ( $rule == 'is_valid')
                    {
                        $line = isset($this->_errorMessage['is_valid'][$param]) ? $this->_errorMessage['is_valid'][$param] : 'Error desconocido.';
                    }
                    else
                    {
                        $line = $this->_errorMessage[$rule];
                    }
                    
                }
                
                // Formateamos el mensaje
                $message = sprintf($line, $row['label'], $param);
                
                // Guardamos el error
                $this->_fieldData[$row['field']]['error'] = $message;
                
                if ( ! isset($this->_errors[$row['field']]))
                {
                    $this->_errors[$row['field']] = $message;
                }
                
                return;
            }
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Agregar mensaje de error.
     * 
     * @access public
     * @param string $rule
     * @param string $message
     * @return void
     */
    public function message($rule, $message = '')
    {
        $rule = array($rule => $message);
        
        $this->_errorMessage = array_merge($this->_errorMessage, $rule);
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Obtener un mensaje de error
     * 
     * @access public
     * @param string $field
     * @return mixed
     */
    public function error($field = null)
    {
        if ( ! $field)
        {
            return $this->_errors;
        }
        
        return $this->_errors[$field];
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Obtener los datos procesados por el validador.
     * 
     * @access public
     * @return array
     */
    public function getFields()
    {
        $fields = array();
        foreach ( $this->_fieldData as $field => $row)
        {
            $fields[$field] = ($row['value'] == '' && isset($row['default'])) ? $row['default'] : $row['value'];
        }
        
        return $fields;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * required
     * 
     * @access public
     * @param string $str
     * @return bool
     */
    public function required($str)
    {
        return (trim($str) == '') ? false : true;
    }
    
	// --------------------------------------------------------------------

	/**
	 * Coincidir un campo con otro
	 *
	 * @access	public
	 * @param string $str
	 * @param string $field
	 * @return bool
	 */
	public function matches($str, $field)
	{
		$field = $this->object->request->get($field);
        
        if (is_null($field))
        {
            return false;
        }

		return ($str !== $field) ? false : true;
	}
    
	// --------------------------------------------------------------------

	/**
	 * Buscar que el valor seá único
	 *
	 * @access	public
	 * @param string $str
	 * @param string $field
	 * @return bool
	 */
	public function is_unique($str, $field)
	{
		list($table, $field)=explode('.', $field);
        
        $exists = $this->object->db->select('COUNT(*)')->from($table)->where(array($field => $str))->execute('field');
		
		return $exists === 0;
    }

	// --------------------------------------------------------------------

	/**
	 * Longitud mínima
	 *
	 * @access	public
	 * @param string $str
	 * @param string $val
	 * @return bool
	 */
	public function min_length($str, $val)
	{
		if (preg_match("/[^0-9]/", $val))
		{
			return false;
		}

		if (function_exists('mb_strlen'))
		{
			return (mb_strlen($str) < $val) ? false : false;
		}

		return (strlen($str) < $val) ? false : true;
	}

	// --------------------------------------------------------------------

	/**
	 * Longitud máxima
	 *
	 * @access	public
	 * @param string $str
	 * @param string $val
	 * @return bool
	 */
	public function max_length($str, $val)
	{
		if (preg_match("/[^0-9]/", $val))
		{
			return FALSE;
		}

		if (function_exists('mb_strlen'))
		{
			return (mb_strlen($str) > $val) ? FALSE : TRUE;
		}

		return (strlen($str) > $val) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Longitud exacta
	 *
	 * @access	public
	 * @param string $str
	 * @param string $val
	 * @return bool
	 */
	public function exact_length($str, $val)
	{
		if (preg_match("/[^0-9]/", $val))
		{
			return false;
		}

		if (function_exists('mb_strlen'))
		{
			return (mb_strlen($str) != $val) ? false : true;
		}

		return (strlen($str) != $val) ? false : true;
	}
    
    // --------------------------------------------------------------------
    
    /**
     * Validar campo especial.
     * 
     * @access public
     * @param string $str
     * @param string $type
     * @return bool
     */
    public function is_valid($str, $type)
    {
        if ( ! isset($this->_regex[$type]))
        {
            return false;
        }
        
        return (bool) preg_match($this->_regex[$type], $str);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * XSS Clean
     * 
     * @access public
     * @param string $str
     * @return bool
     */
    public function xss_clean($str)
    {
        return $this->object->request->xssClean($str);
    }
}