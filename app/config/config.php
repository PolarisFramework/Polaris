<?php
/*
|--------------------------------------------------------------------------
| Título del sitio web.
|--------------------------------------------------------------------------
|
| Titulo que tendrá su sitio web.
|
| http://polarisframework.com/docs/general/title.html
*/
$config['site_title'] = 'Polaris';
$config['site_title_delim'] = '&bull;';

/*
|--------------------------------------------------------------------------
| URL Base
|--------------------------------------------------------------------------
|
| URL a donde se encuentra su sitio, agregando una diagonal al final.
|
|	http://example.com/
|
| Si no se espesificada el sistema la detectará automáticamente.
|
*/
$config['base_url']	= 'http://polaris.com/';

/*
|--------------------------------------------------------------------------
| Archivos estáticos
|--------------------------------------------------------------------------
|
| URL´s de sus archivos estáticos.
|
*/
$config['url_static'] = $config['base_url'];
$config['url_static_css'] = $config['url_static'] . 'css/';
$config['url_static_img'] = $config['url_static'] . 'img/';
$config['url_static_js'] = $config['url_static'] . 'js/';

/*
|--------------------------------------------------------------------------
| Sufijo de la URL
|--------------------------------------------------------------------------
|
| Esta opción le permite añadir un sufijo a todas las URL generadas por Polaris.
| Para obtener más información, consulte la guía del usuario:
|
| http://polarisframework.com/docs/general/urls.html
*/

$config['url_suffix'] = '';

/*
|--------------------------------------------------------------------------
| Caracteres permitidos en la URL
|--------------------------------------------------------------------------
|
| Esto le permite espesificar con una expresión regular qué caracteres
| están permitidos en las URL. Cuando alguien trata de ingresar una URL
| con caracteres no permitidos se enviará un mensaje de error.
|
| Como medida de seguridad se recomienda restringir caracteres que puedan
| representar una amenza.
|
| Si se siente con suerte, deje en blanco para permitir que todos los 
| caracteres.
|
| NO CAMBIE ESTO A MENOS QUE ENTIENDA LAS REPERCUSIONES!!!
|
*/
$config['permitted_uri_chars'] = 'a-z 0-9~%.:_\-';