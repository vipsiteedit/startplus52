<?php
    ini_set('zend.ze1_compatibility_mode', 0);
    if (!defined('SE_INDEX_INCLUDED'))
    {
        Header('404 Not Found', true, 404);
        exit();
    }

    //if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    //    define('_HTTP_', 'https://');
    //else 
    if (!empty($_SERVER['REQUEST_SCHEME'])) {
        define('_HTTP_', $_SERVER['REQUEST_SCHEME'].'://');
    } else {
        define('_HTTP_', ((!$_SERVER['HTTPS'] || $_SERVER['HTTPS'] == 'off') ? 'http://' : 'https://'));
    }

    define('_HOST_', _HTTP_ . $_SERVER['HTTP_HOST']);
    
    if (empty($_SERVER['DOCUMENT_ROOT'])) {
       define('SE_ROOT', '');
    } else {
       $se_root = (substr($_SERVER['DOCUMENT_ROOT'], -1) == '/') ? $_SERVER['DOCUMENT_ROOT'] : $_SERVER['DOCUMENT_ROOT'] . '/';
       define('SE_ROOT', $se_root);
    }
    define('SE_LIBS', SE_ROOT . 'lib/');
    define('SE_MODULES', SE_LIBS . 'modules/');
    define('SE_CORE', SE_ROOT . 'system/main/');
    define('SE_JS_LIBS', SE_LIBS . 'js/');
    define('SE_PRJ_FOLDER', '');
    define('SE_SAFE', '');
    define('SE_ALL_SERVICES', true);
    define('URL_END', '/');


    // Использовать базу данных {use database}
    if (file_exists("system/config_db.php")) {
        define('SE_DB_ENABLE', true);
    	require "system/config_db.php";
    	require SE_LIBS . 'lib_database.php';
       // Тип базы данных {database type}
    	se_db_dsn('mysql');
       // Запустить базу данных {start database}
	//Update version db
      se_db_connect($CONFIG);
    } else {
        define('SE_DB_ENABLE', false);
    }


    // Обработчик внешних запросов {external request handler}
    require SE_CORE . 'serequests.php';    // Служебные функции ядра {service core functions}


    // Переключатель языков и проектов {languages and projects switcher}
    require SE_CORE . 'manager.php';

    // Авторизация {authorization}
    require SE_CORE . 'auth.php';

    require SE_CORE . 'function.php';
    // Библиотеки {librarys}
    if (file_exists(SE_LIBS . 'lib.php')){
    	require SE_LIBS . 'lib.php';
    }

    // Обработчик статических данных {static data handler}
    require SE_CORE . 'classes/seData.class.php';

    if (file_exists(SE_ROOT . "modules/modules.php")){
	require_once SE_ROOT . "modules/modules.php";
    }
    if (SE_DB_ENABLE && file_exists(SE_LIBS . "rss.php"))
    require_once SE_LIBS . "rss.php";	
