<?php
/*
| -------------------------------------------------------------------------
| URI Routing
| -------------------------------------------------------------------------
| Este archivo le permite re-direccionar las peticiones URI a un
| módulo/controlador/método en específico.
|
| Normalmente hay una relación uno a uno entre la URL y su correspondiente
| módulo/controlador/método/. Los segmentos de una URL normalmente siguen
| este patrón.
|
|	example.com/module/class/method/id/
|
| En algunos casos, es posible que desee reasignar esta relación para que
| otro módulo/clase/método/ sea llamado a la URL correspondiente.
|
| Por favor, consulte la guía de usuario para obtener información detallada:
|
|	http://polarisframework.com/docs/general/routing.html
|
| -------------------------------------------------------------------------
| Rutas reservadas
| -------------------------------------------------------------------------
|
| Hay dos rutas reservadas:
|
|	$route['default_module_controller'] = 'blog/home';
|
| Esta ruta es la que será cargada cuando la URI no contenga datos, es decir
| será el módulo/controlador cargado por defecto en su página principal. Se
| requiere el nombre del módulo y controlador.
|
|	$route['404_override'] = 'errors/page_missing';
|
| Esta ruta indica qué controlador debe cargarse si el controlador solicitado 
| no se encuentra.
|
*/

$route['default_module_controller'] = 'blog/home';
$route['404_override'] = '';