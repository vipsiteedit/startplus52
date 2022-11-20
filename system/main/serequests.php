<?php
        require_once "lib/lib_xss.php";
	define('VAR_WORD',0);
	define('VAR_INT',1);
	define('VAR_FLOAT',2);
	define('VAR_STRING',3);
	define('VAR_NOTAGS',4);  // �������� ���� �� 
	define('VAR_BIN',5);
	define('VAR_NOTNULL',6);
	define('VAR_EMAIL',7); //валидность e-mail
	define('VAR_URL',8); //валидность url
	

	define('M_ALL',0); // ��� �������
	define('M_GET',1); // ������ GET
	define('M_POST',2);// ������ POST
	$SE_REQUEST_NAME = array();


/**
 * ������� ��� ��������� ������� ��������
 * @param $name_var string ��� ����������
 * @param $flag integer ���� ������� (VAR_WORD - VAR_BIN)
 * @param $method integer ����� ������� (METHOD_ALL - METHOD_POST)
 * @param $allowable_tags  string ����, ������� ����� ���������� � ������� (��������: '<a><b><i><u>')
 */

        function validateXSS($value) {
            $xss = new XSS();
            $xss->set_source($value);
            return  strip_tags($xss->filter());
        }

        function filterXSS(&$arr = array()){
            foreach($arr as $name => $val) {
               $arr[$name] = validateXSS($val);
            }
        }

	function registerName($name_var)
	{
            global $SE_REQUEST_NAME;
	    $SE_REQUEST_NAME[$name_var] = true;
	}

	function getRequest($name_var, $flag = VAR_WORD, $method = M_ALL, $option = '')
	{
		global $SE_REQUEST_NAME;

		$SE_REQUEST_NAME[$name_var] = true;
		$result = null;
		if ($method==M_ALL)
		{
			$resUrl = from_Url();
			if (!empty($resUrl['site-lang']) && $name_var == 'page' && empty($resUrl['page'])) return '';
			if ($flag == 6 && isset($resUrl[$name_var])) $result = urldecode($resUrl[$name_var]);
			elseif (!empty($resUrl[$name_var])) $result = $resUrl[$name_var];
			elseif (isset($_GET[$name_var])) $result = $_GET[$name_var];
			elseif (isset($_POST[$name_var])) $result = $_POST[$name_var];
		}
		elseif($method==M_GET)
		{
			$resUrl = from_Url();
			if (!empty($resUrl[$name_var])) $result = urldecode($resUrl[$name_var]);
			else
			if (isset($_GET[$name_var])) $result = $_GET[$name_var];
		}
		elseif($method==M_POST)
		{
			//$resUrl = from_Url();
			//if (!empty($resUrl[$name_var])) $result = $resUrl[$name_var];
			/*
			 * Роман Кинякин: изначально строка запроса URI преобразовывалась в запрос GET посредством .htaccess
			 * т. о. функционально она таковым и является.
			 * фильтрация M_POST подразумевает использование только массива $_POST
			*/
			if (isset($_POST[$name_var])) $result = $_POST[$name_var];
		}
		if (isset($result))
		{
			return filterRequest($result, $flag, $option);
		}
		return null;
	}

	function filterRequest($value, $flag = VAR_WORD, $option = '')
	{
		$fv = function_exists('filter_var'); //наличие функции filter_var, на случай отсутствия таковой по каким-либо причинам
		if (get_magic_quotes_gpc()){
			if (is_array($value)) {
			   foreach($value as $id=>$val)
			     $value[$id] = stripslashes($val);
			} else
			    $value = stripslashes($value);
		}
		if ($flag == VAR_WORD)
		{
			if (is_array($value)) {
			   foreach($value as $id=>$val){
                               $value[$id] = htmlspecialchars(preg_replace("/[^\w\d\-@\._\s\"\']/u", "", $val));
			   }
			   return $value;
			} else {
			    return htmlspecialchars(preg_replace("/[^\w\d\-@\._\s\"\']/u", "", $value));
			}
		}
		elseif ($flag == VAR_INT)
		{
			if (is_array($value)) {
			   foreach($value as $id=>$val)
			     $value[$id] = intval($val);
			   return $value;
			} else
			return intval($value);
		}
		elseif ($flag == VAR_FLOAT)
		{
			if (is_array($value)) {
			   foreach($value as $id=>$val)
			     $value[$id] = floatval($val);
			   return $value;
			} else
			return floatval($value);
		}
		elseif ($flag == VAR_STRING)
		{
			if (is_array($value)) {
			   foreach($value as $id=>$val)
			     $value[$id] = htmlspecialchars($val, ENT_QUOTES);
			   return $value;
			} else
			return htmlspecialchars($value, ENT_QUOTES);
		} 
		elseif ($flag == VAR_NOTAGS)
		{
			if (is_array($value)) {
			   foreach($value as $id=>$val)
			     $value[$id] = strip_tags($val, $option);
			   return $value;
			} else
			return strip_tags($value, $option);
		}
		elseif ($flag == VAR_EMAIL) {
			$value = trim($value);
			if ($fv) return (filter_var($value, FILTER_VALIDATE_EMAIL)===$value) ? $value : false;
			else return (!preg_match("/^([a-z0-9_\.-]+)@([a-z0-9_\.-]+)\.([a-z\.]{2,6})$/", $value)) ? false : $value;
		}
		elseif ($flag == VAR_URL) {
			$value = trim($value);
			if ($fv && $option===true) return (filter_var($value, FILTER_VALIDATE_URL,FILTER_FLAG_PATH_REQUIRED)===$value) ? $value : false;
			elseif ($fv) return (filter_var($value, FILTER_VALIDATE_URL)===$value) ? $value : false;
		}
		elseif ($flag == VAR_NOTNULL)
		{
			return isset($value);
		}
		else
		{
			return $value;
		}
	}

	function isRequest($name_var, $method = M_ALL)
	{
		return getRequest($name_var, VAR_NOTNULL, $method);
	}


	// ������� ������ ���������� � ���� ������������� �������
	function getRequestList($request = array(), $fields = '', $flag = VAR_WORD, $method = M_ALL, $option = '')
	{
			$fieldarr =  explode(',', str_replace(' ','', $fields));
			$resUrl = array();
			if (!empty($_POST) && $method!=M_GET) $resUrl = array_merge($_POST, $resUrl);
			if (!empty($_GET) && $method!=M_POST) $resUrl = array_merge($_GET, $resUrl);
			if ($method!=M_POST) $resUrl = array_merge(from_Url(), $resUrl);
			//Роман Кинякин: фильтрация по методу встроена в функцию для прямого использования filterRequest вместо getRequest 

			if (!empty($resUrl))
			foreach ($resUrl as $name_var => $value)
			{
				if (in_array($name_var, $fieldarr) || empty($fields))
				{
					$request[$name_var] = filterRequest($value, $flag, $method, $option);
				}
			}
			return $request;
	}
	


	/**
	 * �������������� URL ������ � ������ ����������
	 */
	$se_fromurl = null; //Роман Кинякин: глобальная переменная с содержимым разбора текущего URI
	function from_Url($REQUEST = '')
	{
		global $se_fromurl;
		$global = empty($REQUEST); //Параметр функции пустой - используем текущий URI
		if (is_array($se_fromurl) && $global) { //URI уже был разобран, выдаем глобальную переменную
			return $se_fromurl;
		}
		else { //Разбираем запрос
		$REQUEST = str_replace('/&', '/?', $REQUEST);
		$langarr = array();

		if (file_exists('hostname.dat')) $langarr = @file('hostname.dat');
		if (!empty($langarr)){
		    $folder = array();
		    foreach($langarr as $arr){
			$folder[] = trim($arr);
		    }
		    $langarr = $folder;
		}
		
		$i = 1;
		if ($global)
		{
			$REQUEST = $_SERVER['REQUEST_URI'];
		}
		$URLPARH = '';

		if (!empty($REQUEST))
		{
			@list($URLPATH, $REQUEST) = explode('?', $REQUEST);
			$res = explode('/',$URLPATH);
			if (!empty($res[1]) && in_array($res[1], $langarr)){
			    $resarr['site-lang'] = $res[1];
			    if (!defined('SE_PROJECT_DIR') && !empty($res[1]))
				define('SE_PROJECT_DIR', '/'.$res[1]);
				$resarr['page'] = '';
			    $i = 2;
			} 
			else {
			    $i = 1;
			}
		}
		if (!empty($res[$i]))
		{
			list($resarr['page']) = explode('.', $res[$i]);
			$i++;
		}
		else
			$resarr['page'] = '';

		if (isset($_GET['site-lang'])){
		    $page = '';
		    $resarr['site-lang'] = '';
		    if ($resarr['page'] != '') {
			$page = $resarr['page'].'/';
		    }
		    $lng = htmlspecialchars($_GET['site-lang']);
		    if (in_array($lng, $langarr)){
			header('Location: /'.$lng. '/'. $page);
			exit;
		    }
		}



		if (!empty($res[$i]) && preg_match("/^([\d]{1,})$/im",$res[$i]))
		{
			$resarr['razdel'] = $res[$i];
			$i++;
		}

		if (!empty($resarr['razdel']) && intval($resarr['razdel']) && !empty($res[$i]) 
		&& preg_match("/^([\d]{1,})$/im",$res[$i]))
		{
			$resarr['object'] = $res[$i];
			$i++;
		}


		if (!empty($resarr['razdel']) && intval($resarr['razdel']) && !empty($res[$i]) 
		&& preg_match("/sub([\d\w]{1,})/im",$res[$i],$m))
		{
			$resarr['sub'] = $m[1];
			$i++;
		}

		if (!empty($res) && count($res) > 3)
		while(isset($res[$i]) && isset($res[$i + 1]))
		{
			$resarr[$res[$i]] = $res[$i + 1];
			$i = $i + 2;
		}

		$lineUrl = explode('&',$REQUEST);

		if (!empty($lineUrl))
		foreach($lineUrl as $line)
		{
			@list($name, $value) = explode('=',$line);
			if (empty($value)) $value = strval($value);
			$resarr[$name] = $value;
		}
		if ($global) $se_fromurl = $resarr; //Сохраняем переменную для дальнейшего использования
		return $resarr;
		}
	}

	/**
	 * �������������� ������ URL ���������� � ������
	 */
	function UrlToLine($URL_Line)
	{
		if (preg_match("/^(http:|https:)/",$URL_Line)) return $URL_Line;

			$result = '';
			if (strpos($URL_Line,'?')!==false)
			    list($firsUrl, $URL_Line) = explode('?', $URL_Line);


			$URL_ARR = explode('&', $URL_Line);
			if (!empty($URL_ARR))

			foreach($URL_ARR as $line)
			{
				list($valname, $value) = explode('=', $line);
				if ($valname == 'page')
				{
					if (!empty($value))	$result .= '/'.$value;
				}
				elseif ($valname == 'razdel')
				{
					if (!empty($value))	$result .= '/'.$value;
				}
				elseif ($valname == 'sub')
				{
					if (!empty($value))	$result .= '/sub'.$value;
				}
			}

			foreach($URL_ARR as $line)
			{
				list($valname, $value) = explode('=', $line);
				if (!preg_match("/^(page|razdel|sub)$/", $valname))
				{
					if (!empty($value))	$result .= '/'.$valname.'/'.$value;
				}
			}

			if (strpos($firsUrl, '/')!==false) $firsUrl .= $result;
			return $result;
	}

	function locationUrl()
	{
            $link = array();
            foreach($_GET as $k => $v)
        	    $link[$k] = $k.'='.$v;
            return UrlToLine(join('&', $link));
	}
	
	/* содержимое функции seMultiDir() перенесено в файл serequests.php
	 * это позволяет избежать постоянного чтения файла sitelang.dat
	 * т. к. файл manager.php подключается после serequests.php, функция перенесена в него для обеспечания совместимости
	 */
	
	/*
	 * Роман Кинякин: укороченные алиасы функций getRequest и getRequestList
	 * в getRL упразднена переменная массива
	 */
	
	function get($name_var, $flag = VAR_NOTNULL, $method = M_ALL, $option = '')
	{
		return getRequest($name_var, $flag, $method, $option);
	}
	function getlist($fields = '', $flag = VAR_NOTNULL, $method = M_ALL, $option = '')
	{
		return getRequestList(array(), $fields, $flag, $method, $option);
	}
	//filterXSS($_GET);
?>