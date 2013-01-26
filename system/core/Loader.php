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
 * Loader
 * 
 * Se encargará de cargar los modelos, vistas y librerías que se requieran
 * dentro del controlador.
 * 
 * @package     Polaris
 * @subpackage  Core
 * @category    Library
 * @author      Ivan Molina Pavana <montemolina@live.com>
 * @link        http://polarisframework.com/docs/.html
 */
class Polaris_Loader {
    
    /**
     * Nivel del mecanismo de bufer.
     * 
     * @var int
     */
    private $_obLevel;
    
    /**
     * Módulo cargado actualmente.
     * 
     * @var string
     */
    private $_module;
    
    /**
     * Lista de archivos cargados
     * 
     * @var array
     */
    private $_loadedFiles = array();
    
    /**
     * Lista de clases cargadas.
     * 
     * @var array
     */
    private $_classes = array();
    
    /**
     * Listado de modelos cargados.
     * 
     * @var array
     */
    private $_models = array();
    
    /**
     * Cache de variables usadas por las vistas.
     * 
     * @var array
     */
    private $_cachedVars = array();
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_obLevel = ob_get_level();
    }
    
    /**
     * Inicializar...
     * 
     * @access public
     * @param object $object Controlador desde fue cargado..
     * @return void
     */
    public function init($object = null)
    {   
        $this->object = $object;
        
        // Módulo actual
        $this->_module = $this->router->getModule();
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
     * Cargar una librería
     * 
     * @access public
     * @param string $library
     * @param array $params
     * @param string $name
     * @return void
     */
    public function library($library, $params = array(), $name = null)
    {
        // Podemos enviar un arreglo con el nombre de las librerías y cargarlas
        if (is_array($library))
        {
            foreach($library as $_library)
            {
                $this->library($_library, $params);
            }
            
            return;
        }
        // Clase
        $class = strtolower(basename($library));
        
        if ( isset($this->_classes[$class]) && $alias = $this->_classes[$class])
        {
            return $this->object->{$alias};
        }
        
        // Alias
        $alias = ($name !== null) ? strtolower($name) : $class;
        
        // Existe un recurso con el nombre de la librería?
        if( isset($this->object->{$alias}))
        {
            show_error('El alias de la librería que está cargando, es nombre de un recurso que está en uso: ' . $alias);
        }
        
        // Buscamos...
        list($path, $_library) = $this->module->find(strtolower($library), $this->_module, 'library/', '.lib');
        
        if ($path === false)
        {
            $this->_loadClass($library, $params, $name);
            $alias = $this->_classes[$class];
        }
        else
        {
            $this->module->loadFile($_library . '.lib', $path);
            $library = ucfirst($this->_module) . '_' . ucfirst($_library) . '_Lib';
            
            $this->object->{$alias} = new $library($params);
            
            $this->_classes[$class] = $alias;
        }
        
        return $this->object->{$alias};
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Cargar un modelo.
     * 
     * @access public
     * @param string $model Nombre del modelo.
     * @param string $name Nombre alternativo para el modelo.
     * @param string $module Módulo en el cual buscar el modelo.
     * @return void
     */
    public function model($model, $name = null)
    {
        // Podemos enviar un arreglo con el nombre de los modelos y cargarlos.
        if (is_array($model))
        {
            foreach($model as $_model)
            {
                $this->model($_model);
            }
            
            return;
        }
        
        // Alias del modelo
        $parts = explode('/', $model);
        $alias = ($name !== null) ? $name : (count($parts) == 2 ? $parts[0] . ucfirst($parts[1]) : $model);
        
        // Ya hemos cargado este modelo?
        if (isset($this->object->{$alias}) && in_array($alias, $this->_models))
        {
            return $this->object->{$alias};
        }
        
        // Existe un recurso con el nombre del modelo?
        if( isset($this->object->{$alias}))
        {
            show_error('El alias del modelo que está cargando, es nombre de un recurso que está en uso: ' . $alias);
        }
        // Buscamos...
        list($path, $_model) = $this->module->find(strtolower($model), $this->_module, 'model/', '.model');
        
        if ($path !== false)
        {
            if ( !class_exists('Polaris_Model'))
            {
                load_class('Model', 'core');
            }
            // Cargamos el archivo..
            $this->module->loadFile($_model . '.model', $path);
            
            // Nombre del modelo
            list($module, $model) = array_pad(explode('/', $model), 2, null);
            
            $model = ($model != null) ? ucfirst($module) . '_' . ucfirst($model) : ucfirst($module) . '_' . ucfirst($module);
            $model .= '_Model';
            
            $this->object->{$alias} = new $model();
            
            $this->_models[] = $alias;
            
            return $this->object->{$alias};
        }
        
        show_error('No se localizó el modelo espesificado: ' . $model);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Cargar base de datos
     * 
     * @access public
     * @param string $dbGroup Cargarémos los datos de conexión desde $db[$dbGroup];
     * @param bool $return Retorna el objecto
     * @return mixed
     */
    public function database($dbGroup = '', $return = false)
    {
        // Necesitamos cargar la DB?
        if (class_exists('Polaris_Database') && $return == false && isset($this->object->db) && is_object($this->object->db))
        {
            return false;
        }
        
        if ( ! file_exists($filePath = APP_PATH . 'config' . DS . 'database.php'))
        {
            show_error('No existe el archivo de configuración: database.php');
        }
        
        include $filePath;
        
        if ( ! isset($db) || count($db) == 0)
        {
            show_error('El archivo de configuración database.php no contiene parámetros válidos.');
        }
        
        if ($dbGroup != '')
        {
            $activeGroup = $dbGroup;
        }
        
        if ( ! isset($activeGroup) || ! isset($db[$activeGroup]))
        {
            show_error('Se ha espesificado un grupo inválido para la Base de Datos.');
        }
        
        $params = $db[$activeGroup];
        
        // Espesificó el driver a utilizar?
        if ( ! isset($params['dbdriver']) || $params['dbdriver'] == '')
        {
            show_error('No se ha espesificado un Driver para la Base de Datos');
        }
        
        // Cargamos la base de datos.
        require DB_PATH . 'Database.php';
        $db = new Polaris_Database($params);
        
        // Retornar objeto?
        if ( $return === true)
        {
            return $db; 
        }
        
        // Inicializamos la variable, esto para prevenir errores.
        $this->object->db = '';
        
        // Cargamos la Base de Datos
        $this->object->db =& $db->get_instance();
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Cargar variables
     * 
     * Carga las variables que estarán disponibles para la vista
     * llamda desde un controlador.
     * 
     * @access public
     * @param array $vars Nombre de la variable ó arreglo de variables.
     * @param string $value Valor de la variable
     * @return Loader
     */
    public function setVar($vars, $value = null)
    {
        if ( ! is_array($vars))
        {
            $vars = array($vars => $value);
        }
        
        foreach( $vars as $key => $value)
        {
            $this->_cachedVars[$key] = $value;
        }
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Solicitar variable
     * 
     * Busca si se ha cargado una variable a la vista.
     * 
     * @access public
     * @param string $sVar
     * @return mixed
     */
    public function getVar($sVar)
    {
        return isset($this->_cachedVars[$sVar]) ? $this->_cachedVars[$sVar] : null;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Cargar una vista
     * 
     * @access public
     * @param string $view Nombre de la vista que será cargada.
     * @param array $vars Arreglo con las variables que serán asignadas a la vista.
     * @param bool $return En algunos casos es necesario regresar el contenido de la vista.
     * @return void
     */
    public function view($view, $vars = array(), $return = false)
    {
        return $this->_loadView($view, $vars, $return);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Cargar el controlador de un módulo.
     * 
     * @access public
     * @param string $module Módulo/Controlador a cargar.
     * @param array $params Parámetros enviados al controlador
     * @return object
     */
    public function module($module, $params = array())
    {
        $alias = strtolower(basename($module));
        
        $this->object->{$alias} = $this->module->load(array($module => $params));
        
        return $this->object->{$alias};
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Cargar un bloque
     * 
     * Esta función facilita la carga de módulos en una platilla.
     * 
     * @access public
     * @param string $module
     * @return void
     */
    public function block($module)
    {
        $module =& $this->module;
        $args = func_get_args();
        
        $result = call_user_func_array(array($module, 'run'), $args);
        
        echo $result;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Cargar vista
     * 
     * @access protected
     * @param string $_view
     * @param array $_vars
     * @param bool $_return
     * @return void
     */
    protected function _loadView($_view, $_vars = array(), $_return = false)
    {
        // Buscamos la vista...
        list($_path, $_view) = $this->module->find($_view, $this->_module, 'view/', '.view');
        
        if ($_path != false)
        {
            // Archivo de la vista
            $_viewPath = $_path . $_view . '.view.php';
            
            // Extraer las variables
            if ( is_array($_vars))
            {
                $this->_cachedVars = array_merge($this->_cachedVars, $_vars);
            }
            
            extract($this->_cachedVars);
            
            // Render...
            ob_start();
            
            // Incluimos la vista
            include $_viewPath;
            
            // Queremos retornar el resultado?
            if ( $_return === true)
            {
                $_buffer = ob_get_contents();
                @ob_end_clean();
                return $_buffer;
            }
            
            // Con el fin de permitir vistas anidadas, es necesario vaciar
            // el contenido cada vez que el nivel de buffer se inremente para así
            // incluir la plantilla adecuadamente.
            if (ob_get_level() > $this->_obLevel + 1)
            {
                ob_end_flush();
            }
            else
            {
                $this->output->appendOutput(ob_get_clean());
            }
            
            return;
        }
        
        show_error('No se pudo cargar la vista: ' . $_view);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Cargar librería
     * 
     * @access protected
     * @param string $class
     * @param array $params
     * @param string $name
     * @return void
     */
    protected function _loadClass($class, $params = null, $name = null)
    {
        // Aseguramos un nombre válido
        $class = str_replace('.php', '', trim($class, '/'));
        
        // La clase se encuentra en un subdirectorio?
        $subDir = '';
        
        if ( ($lastSlash = strpos($class, '/')) !== false)
        {
            $subDir = substr($class, 0, $lastSlash + 1);
            $subDir = preg_replace('/\//', DS, $subDir);
            $class = substr($class, $lastSlash + 1);
        }
        
        $path = SYS_PATH;
        $filePath = $path . 'library' . DS . $subDir . $class . '.php';
        
        if (file_exists($filePath))
        {
            // Verifico si esta clase ya ha sido llamada antes...
            if ( in_array($filePath, $this->_loadedFiles))
            {
                if ( ! is_null($name))
                {
                    $this->object =& getInstance();
                    if( !isset($this->object->{$name}))
                    {
                        return $this->_initClass($class, $params, $name);
                    }
                }
                
                return;
            }
            
            include $filePath;
            $this->_loadedFiles[] = $filePath;
            return $this->_initClass($class, $params, $name);
        }
        
        // Último intento. Tal vez la clase esté en un subdirectorio pero no se espesificó.
        if ( $subDir == '')
        {
            $path = strtolower($class) . '/' . $class;
            return $this->_loadClass($path, $params, $name);
        }
        
        show_error('La clase solicitada no se localizó: ' . $class);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Inicializar una clase
     * 
     * @access private
     * @param string $class
     * @param mixed $params
     * @param string $name
     */
    private function _initClass($class, $params = false, $name = null)
    {
        if ( $params === null)
        {
            // TODO: Buscamos archivo de configuración.
        }
        
        if ( ! class_exists($className = 'Polaris_' . ucfirst($class)))
        {
            show_error('No existe la clase: ' . $className);
        }
        
        $class = strtolower($class);
        
        $alias = ($name !== null) ? $name : $class;
        
        // Guardamos el nombre de la clase y objeto.
        $this->_classes[$class] = $alias;
        
        if( $params !== null)
        {
            $this->object->{$alias} = new $className($params);
        }
        else
        {
            $this->object->{$alias} = new $className;
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Carga automática
     * 
     * Esta función se encarga de la carga automática de librerías.
     * La carga está determinada por el archivo de configuración autoload.php
     * 
     * @access private
     * @return void
     */
    public function _autoload($autoload = array())
    {   
        if (count($autoload) == 0)
        {
            return false;
        }
        
        // Cargar Helpers
        if (isset($autoload['helper']) && count($autoload['helper']) > 0)
        {
            $this->helper($autoload['helper']);
        }
        
        // Cargar librerías
        if (isset($autoload['library']) && count($autoload['library']) > 0)
        {
            if (in_array('database', $autoload['library']))
            {
                $this->database();
                $autoload['library'] = array_diff($autoload['library'], array('database'));
            }
            
            foreach ($autoload['library'] as $library)
            {
                $this->library($library);
            }
        }
        
        // Cargar modelos
        if (isset($autoload['model']) && count($autoload['model']) > 0)
        {
            $this->model($autoload['model']);
        }
        
        // Cargar Módulos
        if (isset($autoload['module']) && count($autoload['module']) > 0)
        {
            foreach ($autoload['module'] as $module)
            {
                if ($module != $this->_module)
                {
                    $this->module($module);
                }
            }
        }
    }
}