<?php
require_once (dirname(__FILE__).'/seMenuTree.class.php');
require_once (dirname(__FILE__).'/seModule39.class.php');



class seData {
private static $instance = null;
public $menu = null;
public $page = null;
public $pages = null;
public $prj = null;
public $adminlogin = '';
public $header = '';
public $adminpassw = '';
public $skin;
public $img;
public $files;
public $versionproduct;
public $gkeywords;
public $gdescription;
public $sections = null;
private $startpage = 'home';
private $dir;
private $urllist = array();
private $pagename;
private $path = array();
public $req;
public $error;
public $modulesCss = array();

  private function __construct($namepage = '', $dir = '')
  {
        $this->openUrlList();

	if ($namepage == 'index') $namepage = '';
	$this->req = new stdClass;
	$this->req->sub = $this->req->razdel = $this->req->object = false;
	if (str_replace('/', '', $_SERVER['REQUEST_URI']) == 'index') {
	    $this->go301(seMultiDir().'/');
	    //$namepage = 'home';
	}
	 $this->req->param = explode('/', $_SERVER['REQUEST_URI']);
	 if ($this->req->param[1] == str_replace('/', '', seMultiDir())) unset($this->req->param[1]);

	$redirectpage = $this->redirect($namepage); // Проверка на редирект
	if ($redirectpage) $namepage = $redirectpage;

	// Инициализация редактора
	if (strpos($namepage, 'show') === 0){
	    $virtualpage = $this->getVirtualPage($namepage);
	    if ($virtualpage != ''){
			$namepage = $virtualpage;
	    } else $namepage = substr($namepage, 4);
	}

	if (empty($_POST) && $namepage && ($_SERVER['REQUEST_URI'] == seMultiDir().'/'.$namepage 
	|| $_SERVER['REQUEST_URI'] == seMultiDir().'/'.$namepage.'.html')){
	    $this->go301(seMultiDir().'/'.$namepage . '/');
	}
	
	if (!preg_match("/[\?\&\=\.]/", $_SERVER['REQUEST_URI']) && substr($_SERVER['REQUEST_URI'],-1, 1)!='/'){ 
	    $this->go301($_SERVER['REQUEST_URI'] . '/');
	}
	
	
	
	
	if ($url = $this->getFromUrl($namepage)){
		$namepage = $url;
	} else {
		$this->req->sub = getRequest('sub');
		$this->req->razdel = getRequest('razdel', 0);
		$this->req->object = getRequest('object', 1);
		if ($this->req->object || $this->req->sub){
		    if (empty($_POST)){
				list($num_sect,) = explode('.', $this->req->razdel);
				if ($num_sect < 100000)
					$pagelink = $namepage.'_';
				else $pagelink = '_';
				$addlink = '';
				$offs = 0;
				foreach($this->req->param as $prm){
					if ($offs > 3 && $prm){
					    if (strpos($prm, '?')===false){
						$addlink .=$prm.'/';
					    } else {
					        $addlink .=$prm;
					    }
					}
					$offs++;
				}

				if ($this->req->sub)
					$link = seMultiDir() . '/'.$pagelink.$this->req->razdel.'_sub'.$this->req->sub.'/'.$addlink;
				else 
					$link = seMultiDir() . '/'.$pagelink.$this->req->razdel.'_'.$this->req->object.'/'.$addlink;
				$this->go301($link);
		    }
		}
	}
	$this->error = false;
	if (!empty($dir)) $dir .= '/';
	$this->dir = $dir;
	// Парсим Проект
	$this->pagename = 'home';
	//$this->getProject();
	// Парсим страницу
	
	if (file_exists(SE_ROOT.'projects/' . SE_DIR . 'project.xml'))
	{
		$this->prj = simplexml_load_file(SE_ROOT.'projects/' . SE_DIR . 'project.xml', null, LIBXML_NOCDATA);
		$this->startpage = (!empty($this->prj->vars->startpage)) ? strval($this->prj->vars->startpage) : 'home';
		
		if (strval($this->prj->vars->language) == '') {
		    $this->prj->vars->language = 'rus';
		}
		define('DEFAULT_LANG', strval($this->prj->vars->language));
	} 

	if (file_exists(SE_ROOT.'/projects/' . SE_DIR . 'pages.xml'))
	{
		$this->pages = simplexml_load_file(SE_ROOT.'projects/' . SE_DIR . 'pages.xml', null, LIBXML_NOCDATA);
        @list($namepage) = explode('.', $namepage);
        if (!empty($namepage) && $namepage != 'home'){
          $result = $this->pages->xpath('page[@name="'.$namepage.'"]');
          if (empty($result)) {
              if (file_exists(SE_ROOT.'/projects/' . SE_DIR . 'pages/' .$namepage.'.xml')){
                unlink(SE_ROOT.'/projects/' . SE_DIR . 'pages/' .$namepage.'.xml');
              }
              $this->go404();
            }
          }
    }

 	if (empty($namepage)){
	  if (!empty($this->prj->vars->startpage))
 		{
  			$namepage = $this->prj->vars->startpage;
		}
		if (!file_exists(SE_ROOT.'/projects/' . SE_DIR . 'pages/' .$namepage.'.xml'))
		{
			$namepage = 'home';	
		}
	}
    if (empty($this->prj->vars->startpage)) $this->prj->vars->startpage = 'home';
	if ($namepage && $_SERVER['REQUEST_URI'] == seMultiDir().'/'.$this->prj->vars->startpage.'/'){
	    $this->go301(seMultiDir().'/');
	}


	if (!file_exists(SE_ROOT.'/projects/' . SE_DIR . 'pages/' .$namepage.'.xml'))
	{
	    $this->error = true;
  	    $this->go404();
	}
	
	$this->pagename = $namepage;
	
	$this->path[0]=array('name'=>$this->prj->vars->startpage, 'title'=>'');
	
	if (file_exists(SE_ROOT.'/projects/' . SE_DIR . 'pages/'.$namepage.'.xml'))
	{
		$_SESSION['SE_PAGE'] = strval($namepage);
		$this->page = simplexml_load_file(SE_ROOT.'/projects/' . SE_DIR . 'pages/'.$namepage.'.xml', null, LIBXML_NOCDATA);
	}
	if ($this->page->css == '') $this->page->css = 'default';
	
	
	$this->req->page = $this->pagename;
	//$this->req->sub = getRequest('sub');
	//$this->req->razdel = getRequest('razdel', 1);
	//$this->req->object = getRequest('object', 1);
  }

  
  public function go301($url){
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: ".$url);
	exit;
  }

  public function go302($url){
	header("HTTP/1.1 302 Moved Permanently");
	header("Location: ".$url);
	exit;
  }


  public function go404(){
	header('HTTP/1.0 404 File not found');
  	if (!file_exists('projects/' . SE_DIR . 'pages/404.xml')){ 
	    print preg_replace("/[\"](images|skin)\//", '"http://e-stile.ru/$1/', file_get_contents("http://e-stile.ru/404.html"));
	} else {
	    print file_get_contents('http://'.$_SERVER['HTTP_HOST'].seMultiDir().'/404/');
	}
	exit;
  }

  public function execute()
  {
  	$this->sections = array();
  	$modulesArr = array();
	foreach($this->prj->sections as $value)
	{
		$id_content = strval($value['name']);
		$this->sections[$id_content] = $value;
	}
	//unset($this->prj->sections); 

	foreach($this->page->sections as $value)
	{
		$id_content = strval($value['name']);
		$this->sections[strval($id_content)] = $value;
	}
	//unset($this->page->sections); 
	
	if (!empty($this->sections)) {
	foreach($this->sections as $id_content=>$section)
	{
	  $id_content = $section->id;
	  if ($this->req->object!==false && $this->req->razdel == $id_content){
			$this->page->titlepage = strval($this->getObject($section, $this->req->object)->title);
	  }
	  $is_add_url = false;
	  if (count(array($section->objects)))
	  foreach($section->objects as $object)
	  {
	    if ($section->id < 100000)
			$pagelink = $this->pagename;
		else $pagelink = '';

		$urlname = se_translite_url($object->title);
		$urlres = strval($pagelink.'_'.$id_content.'_'.$object->id);
		if ($urlname){
		    list($is_add_url, $urlres) = $this->addUrlList($urlres, $urlname);
		}
	    $link = seMultiDir() . '/'.$urlres.'/';

	  	$object->link_detail = $link;
	  	if (!empty($object->image))
		{
		    $prev = explode('.', $object->image);
		    $object->image_prev = $prev[0] . '_prev.' . $prev[1];
		}
	  }
	  if ($is_add_url) {
	     $this->storeUrlList();
	  }
	  if (count(array($section->translates)))
	  foreach($section->translates as $language)
	  {
	    foreach($language as $name=>$value)
		$section->language->$name = $value;
	  }

	  list($nametype) = explode('.', $section->type);
	  $id_content = strval($section->id);
	  if (!function_exists('start_' . $nametype))
	  {
			$root = getcwd() . $this->getFolderModule($nametype);
   			if (file_exists($root  . '/mdl_' . $nametype . '.php')) require_once($root . '/mdl_' . $nametype . '.php');
   			if ($this->req->sub && $section->id == $this->req->razdel && !file_exists($root . '/' . $nametype .'/php/subpage_' . $this->req->sub . '.php')) {
   			    $this->go404();
   			}
	  }

	  if (function_exists('module_' . $nametype))
	  {
	        if (!in_array($nametype, $modulesArr)) {
		    $modulesArr[] = $nametype;
		    if (file_exists(getcwd() . $modulepath . '/'.$nametype . '/css/style.css') && intval($section->oncss)){
			$this->modulesCss[strval($nametype)] = $modulepath . '/'.$nametype . '/css/style.css';
		    }
		}
		$arr = array();
		$arr = call_user_func_array('module_' . $nametype, array($id_content, $section));

		$section->body = $this->getHeader($arr['content']['form'], $section);
		if (!empty($arr['content']['object']))
		{
			$section->formobject = $this->getHeader($arr['content']['object'], $section);
		}
		if (!empty($arr['content']['show']))
		{
			$section->formshow = $this->getHeader($arr['content']['show'], $section);
		}
		if (!empty($arr['content']['arhiv']))
		{
			$section->formarhiv = $this->getHeader($arr['content']['arhiv'], $section);
		}
		if (!empty($arr['subpage']))
		foreach($arr['subpage'] as $subname=>$value)
		{
			$section->subpage->$subname->form = $this->getHeader($value['form'], $section);
			$section->subpage->$subname->group = $value['group'];
		}
   	  }
	}}
	if ($this->req->razdel && empty($this->sections[strval($this->req->razdel)])) $this->go404();
  }

  private function redirect($namepage){
	if (file_exists('projects/urlredirect.dat')) {
		$redirect = file('projects/urlredirect.dat');
        @list($oldurl,) = explode('?', $_SERVER['REQUEST_URI']);
		foreach($redirect as $ur){
            $ur = explode("\t", $ur);
			$url_in = $_SERVER['HTTP_HOST'].autoencode($oldurl);
			$url_find = str_replace('http://', '', str_replace('$1', '', trim($ur[1])));
		if (strpos($url_in, $url_find) !== false && strpos($url_in, $url_find) == 0) {
			continue;
		}
	    list($url_protocol,$url_start) = explode('://', autoencode($ur[0]));
	    if (!$url_protocol) {
		$url_start = autoencode($ur[0]);
	    }
            if ($ur[0] != '' && (autoencode($oldurl) == autoencode($ur[0]) 
			|| ($_SERVER['HTTP_HOST'].autoencode($oldurl) == $url_start)
			|| (autoencode(urldecode($_SERVER['REQUEST_URI'])) == $url_start)))
			{
                if (autoencode($oldurl) == '/' && strpos($ur[1], '://') === false) {
					return trim(str_replace('/','', $ur[1]));
				} else {
					header("HTTP/1.1 301 Moved Permanently");
					header("Location: ".str_replace('$1', $oldurl, $ur[1]));
					exit;
				}
            } elseif ($ur[0] != '' && $_SERVER['HTTP_HOST'] == autoencode(str_replace(array('http://','https://'),'', $ur[0])) && strpos($ur[1], '://') !== false){
				header("HTTP/1.1 301 Moved Permanently");
				header("Location: ".str_replace('$1', $oldurl, $ur[1]));
				exit;
            }
        }
	}
    if (file_exists($namepage)) {
	echo join('', file($namepage));
        exit;
    }
  }

  
    // Преобразование строки запроса в индексы 
  private function getFromUrl($nameurl) {
	$namepage = '';
	if (strpos($nameurl, '_')===false) {
	   if ($url = $this->findUrlList($nameurl)) {
			$nameurl = $url;
		}
	} elseif ($url = $this->getAltUrlList($nameurl)) {
	   $this->go301(seMultiDir().'/'.$url.'/');
	}
	if (preg_match("/\b([^_]+)?\_([^_]+)\_([^_]+)/", $nameurl, $m)){
		if (!empty($m[1]))
		  $namepage = $m[1];
		else {
		  $namepage = $_SESSION['SE_PAGE'];
		}
		$this->req->razdel = $m[2];
		if (strpos($m[3],'sub')!==0){
		    $this->req->object = $m[3];
		    if ($this->req->object == 0) $this->go404();
		} else $this->req->sub = substr($m[3],3);
		return $namepage;
	}
	return false;
 }

 
  // Добавляем URL в индексный массив
  public function addUrlList($urlname, $altname){
	$it = -1;
	foreach($this->urllist  as  $id=>$item) {
          if ($item[0] == strval($urlname)) {
		     $it = $id;
			 break;
          }
    }
	if ($it > -1) {
		if ($this->urllist[$it][1] == strval($altname)) {
			// Ничего не делаем
			return array(false, $altname);
		}
    }
	// Проверяем альтернативную ссылку
    while ($result = $this->pages->xpath('page[@name="'.$altname.'"]')) {
       $altname .= '~';
    }
	if ($url = $this->findUrlList($altname, $urlname)) {
       $altname .= '~';
	}
	if ($it > -1) {
		// Есть изменения в альтернативном названии - исправляем
		$this->urllist[$it] = array($urlname, $altname);
		return array(true, $altname);
	}
	$this->urllist[] = array($urlname, $altname);
	return array(true, $altname);
  }



  private function storeUrlList(){
      if (empty($this->urllist)) return;
      $fileurl = 'projects/'.SE_DIR.'/pages.url';
      $fp = fopen($fileurl, 'w+');
      flock($fp, LOCK_EX);
      fwrite($fp, serialize($this->urllist));
      flock($fp, LOCK_UN);
      fclose($fp);
  }
  
  private function openUrlList(){
      $fileurl = 'projects/'.SE_DIR.'/pages.url';
    if (file_exists($fileurl)) {
      $this->urllist = unserialize(join('',file($fileurl)));
     // print_r($this->urllist);
    }
  }
  

  private function findUrlList($urlname, $fullpath = ''){
        foreach($this->urllist  as  $item) {
          if ($item[1] == strval($urlname) && (!$fullpath || $item[0] == strval($fullpath))) {
             return $item[0];
          }
        }
  }

  private function getAltUrlList($urlname){
        foreach($this->urllist  as  $item) {
          if ($item[0] == strval($urlname)) {
             return $item[1];
          }
        }
  }



  
  public function getVirtualPage($type){
	    if (file_exists('projects/'.SE_DIR.'types/'.$type)){
		$ftype = file('projects/'.SE_DIR.'types/'.$type);
		foreach($ftype as $item){
		    return $item;
		}
	    }
  }

  public function setVirtualPage($namepage, $type = 'text'){
	if ($namepage){
	    if (!file_exists('projects/'.SE_DIR.'types/')){
			mkdir('projects/'.SE_DIR.'types/');
	    }
	    if (!file_exists('projects/'.SE_DIR.'types/'.$type) || (time() - filemtime('projects/' . SE_DIR . 'types/'.$type)) > 3600){
			$fp = fopen('projects/'.SE_DIR.'types/'.$type, 'w+');
			fwrite($fp, $namepage);
			fclose($fp);
	    }
	}
  }
  
  private function getHeader($text, $section)
  {
    while (preg_match("/<header:js>(.+?)<\/header:js>/usim", $text, $m))
    {
        preg_match_all("/<script.+?<\/script>/usim", $this->header, $arrheaderjs);
        preg_match_all("/<link.+?>/usim", $this->header, $arrheaderlink);


	$m[1] = preg_replace("/\[js:([\w\d\.\/\-]+)\]/", "<script type=\"text/javascript\" src=\"/lib/js/$1\"></script>", $m[1]);
	$m[1] = str_replace(array("[this_url_modul]", "[module_url]"), $this->getFolderModule(strval($section->type)) . '/' . strval($section->type) . '/', $m[1]);



	if (preg_match_all("/<link.+?>/usim", $m[1], $ma)) {
	    foreach($ma[0] as $head){
	        $linkname = '';
	        if (preg_match("/ href=\"(.+?)\"/", $head, $linkname)){
	         $linkname = basename($linkname[1]);
		}
		
		if (in_array($head, $arrheaderlink[0])){
		    if (!empty($linkname)){
	    		foreach($arrheaderlink[0] as $phead){
	    	    	    if (strpos($phead, '/'.$linkname.'"')!== false) 
	    	    		$m[1] = str_replace($head, '', $m[1]);
			}
		    } else {
	    	    	$m[1] = str_replace($head, '', $m[1]);
		    }
		}
	    }
	 } 

	if (preg_match_all("/<script.+?<\/script>/usim", $m[1], $ma)) {
	    foreach($ma[0] as $head){
	        $jsname = '';
	        if (preg_match("/src=\"(.+?)\"/", $head, $jsname)){
	         $jsname = basename($jsname[1]);
		}
		if (in_array($head, $arrheaderjs[0])){
		    if (!empty($jsname)){
	    		foreach($arrheaderjs[0] as $phead){
	    	          if (strpos($phead, '/'.$jsname.'"')!== false) 
	    	            $m[1] = str_replace($head, '', $m[1]);
			}
		    } else {
	    	    	$m[1] = str_replace($head, '', $m[1]);
		    }
		}
	    }
	}
	$headlist = explode("\r\n", $m[1]);
	foreach($headlist as $h){
	  if (trim($h) == '') continue;
	  $this->header .= $h . "\r\n";
	}
	
	$text = str_replace($m[0], '', $text);
    }
    return $text;
  }
  
  
  private function getFolderModule($type)
  {
  	$pathalt = '/lib';
  	$path = '/modules';
  	
 	if (file_exists(getcwd() . $pathalt . $path. '/module_' . $type . '.class.php') 
	 || file_exists(getcwd() . $pathalt . $path. '/mdl_' . $type . '.php'))
	 {
 		return $pathalt . $path;
	 }
 	else
 	if (file_exists(getcwd(). $path.'/module_' . $type . '.class.php')
	 || file_exists(getcwd(). $path. '/mdl_' . $type . '.php'))
	 {
 		return $path;
	 }
	 return;
  }
  
  public function getPathArray()
  {
 	$level_arr = array();
	if ($this->startpage != $this->pagename)
 	{
		foreach($this->pages as $page)
  		{
			$level = $page->level;
			if ($level < 1) $level = 1;
			$name = strval($page['name']);
			if (!empty($name))
			{
				$level_arr[$level - 1]['name'] = $name;
				$level_arr[$level - 1]['title'] = strval($page->title);
				if ($name == $this->pagename) 
				{
					$endlevel = $level - 1;
					break;
				}
			}
		} 
  	}
  	$tmparr = array();	
  	
  	foreach($level_arr as $level=>$data)
  	{
  		if ($level <= $endlevel)
  		$tmparr[$level] = $data;
  	}
  	return $tmparr;
  }


  // Хлебные крошки
  public function getPathLinks($space = '/', $endtitle = 'Home')
  {
	// Главная страница
	$link = '';
  	$level_arr = $this->getPathArray();
  	//print_r($level_arr);

	foreach($this->pages as $page)
	{
	    if (strval($page['name']) == $this->startpage) {
		$link = ' <a href="' . seMultiDir().'/'.$this->startpage.'/">' . $page->title . '</a> ';
		break;
	    }
	}
	
	foreach($level_arr as $line)
	{
		if ($line['name'] == $this->startpage) continue;
	
	
		if (empty($line['name'])) break;
		    $link .= '<span class="space">'.$space . '</span> <a href="'. seMultiDir().'/'.$line['name'] .'/">'.$line['title'].'</a> ';
	}
	if ($this->req->razdel && $this->req->object)
	{
		
		$objects = $this->sections[strval($this->req->razdel)]->objects;
		foreach($objects as $object)
		{
			if ($object->id == intval($this->req->object))
			{
				$link .= '<span class="space">'. $space . '</span> '.'<span class="endtitle">'.$object->title.'</span> ';
				break;
			}
		}
		$title = $this->section->title;
	}
	if ($endtitle != ''){
	    $link .= '<span class="space">'.$space . '</span> <span class="endtitle">' . $endtitle .'</span>';
	}
	return $link;
  }
  
  public static function getInstance($namepage = '', $dir = '') 
  {
    //if (empty($namepage)) return;
    if (self::$instance === null) {
      self::$instance = new self($namepage, $dir);
    }
    return self::$instance;
  }

  public function getPageName()
  {
   		return strval($this->pagename);
  }
  
  public function setHead($head)
  {
    self::$instance->page->head = $head;
  }

  public function getPages()
  {
    return self::$instance->pages;
  }

  public function setPageTitle($titlepage)
  {
    self::$instance->page->titlepage = $titlepage;
  }
  
  public function getObject($section, $id_object)
  {
  	if ($id_object)
  	{
    	    foreach($section->objects as $object)
    	    {
    		if (intval($object->id) == $id_object)
    		{
    			return $object;
    		}
    	    }
	    if (strval($section->id) == strval($this->req->razdel)) {
    		$this->go404();
  	    }
  	}
  }

  public function getSection($id_section)
  {
  	if ($id_section){
    	  foreach($this->sections as $section)
    	  {
    		if (strval($section->id) == $id_section)
    		{
    			return $section;
    		}
    	  }
    	  $this->go404();
    	}
  }

  // дПВБЧЙФШ ЪБРЙУШ 
  public function setList($section, $nameobject, $array)
  {
        unset($section->$nameobject);
    	foreach($array as $line)
    	add_simplexml_from_array($section, $nameobject, $line);
  }

  public function setItemList($section, $nameobject, $itemarray)
  {
    	add_simplexml_from_array($section, $nameobject, $itemarray);
  }

  public function setObjectList($dataarr, $objects_id = 0)
  {
  	if (!$objects_id)
  	{
  		  unset($section->objects);
		  $objects = $section->objects;
	}
	else 
	{
 		unset($section->objects[$objects_id]);
		$objects = $section->objects[$objects_id];
	}
  	if ($dataarr){
  	foreach ($dataarr as $linearr) 
    {
     		if ($cnstyle>0)
     		{
				if ($cn >= $cnstyle) $cn = 0;
      			if (isset($mstyles[$cn]))
	  			$getstyle = trim($mstyles[$cn]);
				$linearr['style'] = $getstyle;
      			$cn++;
	 		} else {
	    		$dataarr['style'] = '';
			}
			
			foreach($linearr as $nameval=>$value)
			{	
				if (!is_int($nameval))
				{
					$objects[$nn]->$nameval = $value;
				}
			}
			$nn++;
   }}
  } 

  public function goSubName($section, $subname)
  {
    $this->req->razdel = strval($section->id);
    $this->req->sub = $subname;
  }

  public function limitObjects($section, $limit = -1)
  {
    if ($limit <= 0) $limit = intval($section->objectcount);
    //if($limit < 1) $limit = 30;
    list($sect_id, $_item ) = explode('-', getRequest('item'));
    //if ($sect_id != strval($section->id)) return;
	if($_item < 1) {
        $_item = 1;
    }
    if($limit) {
        if($_item * intval($limit)  >= count($section->objects))
    	    $_item = ceil(count($section->objects) / $limit);

        if($_item < 1) {
            $_item = 1;
        }

        $startitem = ($_item - 1) * $limit;
        $enditem = ($_item * $limit);
    } else {
        $startitem = 0;
        $enditem = count($section->objects);
    }
    $i = 0;
    unset($section->records);
    foreach($section->objects as $record)
    {
        if (empty($record)) continue;
        if ($record->visible == 'off') continue;
        $i++;
        if($i <= $startitem)continue;
        if($i > $enditem)break;
        if ($record->text1!='') list($record->text1) = explode('|', $record->text1);
	$record->row = $i;
        $this->setItemList($section, 'records', $record);
    }
    return $section->records;
  }
  
  public function linkAddRecord($section_id){
    return '';
  }

  public function linkEditRecord($section_id, $record_id, $type){
     return '';
  }
  
  public function editItemRecord($section_id, $record_id){
    return '';
  }
  
  public function recordsWrapperStart(){
    return '';
  }

  public function recordsWrapperEnd(){
    return '';
  }

  public function groupWrapper($content_id, $text){
    return $text;
  }

  public function editorAccess(){
    return false;
  }

  public function getVars(){
    return '';
  }
  
  public function  getSkinService(){
    return SE_DIR . 'skin';
  }

}
?>