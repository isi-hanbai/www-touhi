<?php
//エラー表示の設定
ini_set( 'display_errors', "On" );
error_reporting(1);
error_reporting(E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_ERROR | E_CORE_ERROR);
//error_reporting(E_ALL　&~E_STRICT & ~E_NOTICE);
//定数の設定
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/sys/application'));
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
defined('APPLICATION_UPLOADS_DIR') || define('APPLICATION_UPLOADS_DIR', realpath(dirname(__FILE__) . '/uploads/'));
define('BASEURL', "https://www.touhi-ishikai.jp/search");
define('HOMEDIR', "/home/touhi-ishikai.jp/htdocs/search/");
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));
// Public path
define('PUBLIC_PATH', realpath(dirname(__FILE__) . '/public'));
// Template path
define('LAYOUT_PATH', APPLICATION_PATH . '/layouts/scripts');
// Block path
define('BLOCK_PATH', APPLICATION_PATH . '/blocks');
//2015.08.12
date_default_timezone_set('Asia/Tokyo');
define('YYMMDD', date('Ymd')); //for log filename
defined('ROOT_PATH')  || define('ROOT_PATH',  realpath(dirname(__FILE__)));
define('OBJECT_TYPE_PRODUCT', 1);
define('OBJECT_TYPE_USER', 2);
define('OBJECT_TYPE_CUSTOMER', 3);
//application.iniの参照
require_once 'Zend/Application.php';
$application = new Zend_Application(
    APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini'
);
//bootstrapの使用
$application->bootstrap()->run();
?>
