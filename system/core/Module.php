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
 * Clase para los módulos
 * 
 * Esta clase hace posible el uso de módulos.
 * 
 * @package     Polaris
 * @subpackage  Core
 * @category    Library
 * @author      Ivan Molina Pavana <montemolina@live.com>
 * @link        http://polarisframework.com/docs/.html
 */
class Polaris_Module {
    
    /**
     * Rutas de los módulos
     * 
     * @var array
     */
    private $routes = array();
    
    /**
     * Registro de módulos
     * 
     * @var array
     */
    private $registry = array();
    
    // --------------------------------------------------------------------
    
    /**
     * Correr un módulo
     * 
     * Esta función nos permite mandar a llamar al controlador/método 
     * de un módulo, el resultado lo almacenará en buffer y lo devolverá
     * 
     * @access public
     * @param string $module
     * @return string
     */
    public function run($module)
    {
        $method = 'index';
        
        if (($lastSlash = strrpos($module, '/')) !== false)
        {
            $method = substr($module, $lastSlash + 1);
            $module = substr($module, 0, $lastSlash);
        }
        
        if ($class = $this->load($module))
        {
            if (method_exists($class, 'action_' . $method))
            {
                // Buffer
                ob_start();
                
                // Argumentos que serán enviados.
                $args = func_get_args();
                
                // Procedemos a ejecutar el módulo.
                $result = call_user_func_array(array($class, 'action_' . $method), array_slice($args, 1));
                $buffer = ob_get_clean();
                
                // Retornamos
                return ($result !== null) ? $result : $buffer;
            }
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Cargar un controlador de un módulo
     * 
     * @access public
     * @param string $module Nombre del modelo. Podemos enviar un arreglo donde el primer parámetro será el nombre y el segundo los parámetros para el controlador. 
     * @return object
     */
    public function load($module)
    {
        $params = null;
        if (is_array($module))
        {
            list($module, $params) = each($module);
        }
        
        //
        list($module, $class) = array_pad(explode('/', $module), 2, null);
        $alias = ($class != null) ? $module . '_' . $class : $module . '_' . $module;
        
        // Crear y retornar el controlador solicitado
        if ( ! isset($this->registry[$alias]))
        {
            // Buscamos el controlador
            list($class) = load_class('Router', 'core')->locate(array($module, $class));
                        
            // No existe el controlador...
            if (empty($class))
                return;
                
            // Nombre del controlador
            $controller = ucfirst($module) . '_' . ucfirst($class) . '_Controller';
            
            // Si la clase existe es porque el objeto tambien...
            if ( class_exists($controller, false))
                return;   
            
            $path = load_class('Router', 'core')->getDirectory();
            $this->loadFile($class. '.controller', $path);
                                          
            // Crear y registrar el módulo
            $this->registry[$alias] = new $controller($params);
        }
        
        return $this->registry[$alias];
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Analizar las rutas de un módulo.
     * 
     * @access public
     * @param string $module
     * @param string $uri
     * @return array
     */
    public function parseRoutes($module, $uri = '')
    {
        // Cargamos el archivo de las rutas.
        if ( ! isset($this->routes[$module]))
        {
            // Existe?
            if((list($path) = $this->find('routes', $module, 'config/')) && $path)
            {
                $this->routes[$module] = $this->loadFile('routes', $path, 'route');
            }
        }
        
        if ( ! isset($this->routes[$module]))
        {
            return;
        }
        
        // Analizamos...
        foreach ($this->routes[$module] as $key => $val)
        {
            $key = str_replace(array(':any', ':num'), array('.+', '[0-9]+'), $key);
            
            if ( preg_match('#^'.$key.'$#', $uri))
            {
                // Tenemos una variable de referencia?
                if (strpos($val, '$') !== false && strpos($key, '(') !== false)
                {
                    $val = preg_replace('#^'.$key.'$#', $val, $uri);
                }
                
                return explode('/', $module . '/' . $val);
            }
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Buscar un archivo
     * 
     * Busca archivos en el directorio de un módulo.
     * 
     * @access public
     * @param string $file Nombre del archivo a buscar.
     * @param string $module Módulo en el cual buscarémos.
     * @param string $base Carpeta donde buscarémos.
     * @return array
     */
    public function find($file, $module, $base, $suffix = '')
    {
        $segments = explode('/', $file);
        $base = str_replace('/', DS, $base);
        
        $file = array_pop($segments);
        $fileExt = (pathinfo($file, PATHINFO_EXTENSION)) ? $file : $file . $suffix . '.php';
        
        $path = ltrim(implode('/', $segments).'/', '/');
        
        $modules = array();
        
        $module ? $modules[$module] = $path : array();
        
        if ( ! empty($segments))
        {
            $modules[array_shift($segments)] = ltrim(implode('/', $segments).'/', '/');
        }
        
        foreach ($modules as $module => $subPath)
        {
            $modulePath = MOD_PATH . $module . DS . $base . $subPath;
            
            if (is_file($modulePath.$fileExt))
            {
                return array($modulePath, $file);
            }
        }
        
        return array(false, $file);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Cargar archivo de un módulo.
     * 
     * @access public
     * @param string $file Nombre del archivo.
     * @param string $path Ruta completa del archivo.
     * @param string $type Si no cargamos un clase entonces estamos solicitando una variable, el tipo se convierte en el nombre de esa variable.
     * @param bool $sResult
     */
    public function loadFile($file, $path, $type = 'class', $result = true)
    {
        $filePath = $path . $file . '.php';
        
        if ($type === 'class')
        {
            if (class_exists($file, false))
            {
                return $result;
            }
            
            require $filePath;
        }
        else
        {
            // Cargamos el archivo
            require $filePath;
            
            // Comprobamos 
            if ( ! isset($$type) || ! is_array($$type))
            {
                show_error(str_replace(MOD_PATH, '', $filePath) . ' no contiene el arreglo $' . $type);
            }
            
            $result = $$type;
        }
        
        return $result;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Registrar un controlador
     * 
     * @access public
     * @param string $class
     * @param object $object
     * @return void
     */
    public function addClass($class, $object)
    {
        $this->registry[strtolower($class)] = $object;
    }
}