<?php

/*
* Tree framework
* Version: 0.0.1 alfa.
* Author: WebDzen
*/
/*
 * Пути
 */
$application = 'application';

$modules = 'modules';

$system = 'system';


/*
 * Расширение файлов
 */

define('EXT', '.php');

/*
 * Отображение ошибок
 */

error_reporting(E_ALL | E_STRICT);
//error_reporting(E_ALL ^ E_NOTICE);
/*
 * Путь к корневому каталогу
 */
define('DOCROOT', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);

/*
 * Проверить путь относительно корневого каталога
 */
 
if (!is_dir($application) AND is_dir(DOCROOT.$application))
    $application = DOCROOT.$application;

if (!is_dir($modules) AND is_dir(DOCROOT.$modules))
    $modules = DOCROOT.$modules;

if (!is_dir($system) AND is_dir(DOCROOT.$system))
    $system = DOCROOT.$system;

/*
 * Создаем абсолютные пути
 */
define('APPPATH', realpath($application).DIRECTORY_SEPARATOR);
define('MODPATH', realpath($modules).DIRECTORY_SEPARATOR);
define('SYSPATH', realpath($system).DIRECTORY_SEPARATOR);

// Удаляем лишнее
unset($application, $modules, $system);

/*
 * Инсталяция
 */

/***************************/

/*
 * Время начала выполнения
 */
if (!defined('START_TIME'))
    define('START_TIME', microtime(TRUE));

/*
 * Затрачиваемая память
 */
if (!defined('START_MEMORY'))
    define('START_MEMORY', memory_get_usage());

/*
 * Подключаем bootstrap
 */
require SYSPATH.'bootstrap'.EXT;

/*
 * Выполнение
 */
Request::factory()->execute();
?>