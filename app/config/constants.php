<?php
/*
 * ---------------------------------------------------------------
 *  Rutas básicas
 * ---------------------------------------------------------------
 *
 * Definimos las rutas principales, sin ellas el sistema no funciona,
 * es por eso que debe evitar modificarlas.
 *
 */
 
    // Ruta de la aplicación
    define('APP_PATH', ROOT . DS . APP . DS);
    
    // Ruta de los módulos
    define('MOD_PATH', ROOT . DS . 'module' . DS);
    
    // Ruta del sistema.
    define('SYS_PATH', ROOT . DS . 'system' . DS);
    
    // Ruta del núcleo.
    define('CORE_PATH', SYS_PATH . 'core' . DS);
    
    // Ruta a las clases de bases de datos
    define('DB_PATH', SYS_PATH . 'database' . DS);