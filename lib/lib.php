<?php

function __autoload($psClassName) {
	if (file_exists(dirname(__file__) . '/classes/' . $psClassName . '.class.php')) {
		require_once dirname(__file__) . '/classes/' . $psClassName . '.class.php';
	}

	if (strpos($psClassName, 'plugin_')!==false) {
		if ($handle = opendir(dirname(__file__).'/plugins/')) {
			while (false !== ($file = readdir($handle))) {
				if (is_dir(dirname(__file__) . '/plugins/' .$file) 
				&& strpos($file,"plugin_")!== false && strpos(strtolower($psClassName), $file)!==false) {
					if (file_exists(dirname(__file__) . '/plugins/' . $file. '/' . strtolower($psClassName) . '.class.php'))
						require_once dirname(__file__) . '/plugins/' . $file. '/' . strtolower($psClassName) . '.class.php';
				} 
			}
		}
		closedir($handle);
	}
}

setlocale(LC_ALL, 'ru_RU.CP1251');

require_once dirname(__FILE__) . "/yaml/seYaml.class.php";
require_once dirname(__FILE__) . "/lib_utf8.php";
require_once dirname(__FILE__) . "/lib_nav.php";
require_once dirname(__FILE__) . "/lib_function.php";
require_once dirname(__FILE__) . "/lib_se_function.php";
require_once dirname(__FILE__) . "/lib_safe_function.php";
require_once dirname(__FILE__) . "/lib_images.php";
require_once dirname(__FILE__) . "/lib_widgets.php";
require_once dirname(__FILE__) . "/lib_simpleXml.php";
//if (file_exists(dirname(__FILE__) . "/rss.php"))
//  require_once dirname(__FILE__) . "/rss.php";
require_once dirname(__FILE__) . "/lib_voting.php";
require_once dirname(__FILE__) . "/lib_rating.php";
//if (file_exists(dirname(__FILE__) . "/lib_macrocomands.php"))
//  require_once dirname(__FILE__) . "/lib_macrocomands.php";
