<?php
require_once (dirname(__FILE__).'/seDataEditor.class.php');
require_once SE_CORE . 'sitepagemenu.php';
require_once SE_CORE . 'sitemainmenu.php';

if (!defined('SE_END')) define('SE_END', '/');


class seData {
	private static $instance = null;
	private $dir;
	private $services = null;
	private $path = array();
	public $interface_lang = 'ru';
	private $ajaxId = false;
	private $ajaxType = '';
	private $urllist = array();

	public $language = 'rus';
	public $page = null;
	public $pages = null;
	public $prj = null;
	public $adminlogin = '';
	public $header = array();
	public $headercss = array();
	public $footer = array();
	public $footercss = array();
	public $footerhtml = '';
	public $adminpassw = '';
	public $skin;
	public $img;
	public $files;
	public $versionproduct;
	public $gkeywords;
	public $gdescription;
	public $sections = null;
	public $pagename;
	public $pagemenu;
	public $mainmenu;
	public $mainmeny_type = 0;
	public $req;
	public $error;
	public $modulesCss=array();
	public $editor = null;
	public $lastmodif = 0;
	public $startpage = '';
	private $linkredirect = false;


    private function __construct($namepage = '', $dir = '')
    {
        registerName('_openstat');
        registerName('merchant');
        registerName('invnum');
        $this->pagename = $namepage;	
        if ($this->pagename == 'index') $this->pagename = '';

	    // Загрузка языка проекта
	    if (file_exists(SE_ROOT . 'projects/'. SE_DIR .'cache/project_lang.dat')) {
	    	$this->language = join('', file(SE_ROOT . 'projects/'. SE_DIR .'cache/project_lang.dat'));
		}
		
		$this->openUrlList();
        // Инициализация массива запросов
        $this->req = new stdClass;
        $this->req->sub = $this->req->razdel = $this->req->object = false;
        if (str_replace('/', '', $_SERVER['REQUEST_URI']) == 'index') {
            $this->go301(seMultiDir().'/');
        }
        
        if (isRequest('lang-site')) {
           list($url) = explode('?', $_SERVER['REQUEST_URI']);
           $this->go301(seMultiDir() .  $url);
        }
		list($urlline) = explode('?', $_SERVER['REQUEST_URI']);
		if (strpos($urlline, '.') && !strpos($urlline,'.php')) {
			$this->go404();
		}
		list($urlline,) = explode('#', $urlline);
		$this->req->param = explode('/',$urlline);
		if ($this->req->param[1] == str_replace('/', '', seMultiDir())) {
			array_splice($this->req->param, 1,1);
		}

		$redirectpage = $this->redirect($this->pagename); // Редиректы
		if ($redirectpage) {
			$this->pagename = $redirectpage;
		}		
         
		if (strpos($this->pagename, '_')!==false){
		    list($this->pagename) = explode('_', $this->pagename);
		}

        if ( strpos($this->pagename, 'show') === 0 ){
            $virtualpage = $this->getVirtualPage($this->pagename);
            if ($virtualpage != ''){
                $this->pagename = $virtualpage;
            } else $this->pagename = substr($this->pagename, 4);
        }

		$this->redirectPage();
	  // Инициализация редактора
		if (strpos($this->pagename, 'show') === 0){
			if (!empty($_SESSION['SE'][$this->pagename]))
			   $this->pagename = $_SESSION['SE'][$this->pagename];
			else 
			  if (!empty($_SESSION['se']['page']))
				$this->pagename = $_SESSION['se']['page'];
			  else 
				$this->pagename = substr($this->pagename, 4);
		}

        if (isset($_GET['interfacelang'])) {
            $this->interface_lang = substr($_GET['interfacelang'], 0, 2);
            $fp = fopen(SE_ROOT.'projects/' . SE_DIR . 'cache/interface_lang.dat', "w+");
            fwrite($fp, $this->interface_lang);
            fclose($fp);
        } elseif (file_exists(SE_ROOT.'projects/' . SE_DIR . 'cache/interface_lang.dat')){
            $this->interface_lang = join('',file(SE_ROOT.'projects/' . SE_DIR . 'cache/interface_lang.dat')); 
        }


		// Подключаем on-line редактор
		$this->editor = new seDataEditor($this, $this->interface_lang);
	
		$this->error = false;
		if (!empty($dir)) $dir .= '/';
		$this->dir = $dir;
		// Инициализируем проект
		

		$this->initprj();
		if (isRequest('login-AJAX')) {
		    include dirname(__FILe__).'/../loginform.php';
		    exit;
		}

		$this->onAjax();
	
		// Загрузим файл проекта
		$this->initmenu();
	    // Инициализация списка страниц
	   	$this->initpages();
		$this->initpage(); 
		$this->rootUrl();
		$this->req->page = $this->pagename;
		list($_SESSION['SE_BACK_URL']) = explode('?', $_SERVER['REQUEST_URI']);
		
		$startpagetitle = '';
		foreach($this->pages as $item){
			if ($item['name'] == $this->startpage){
				$startpagetitle = $item->title;
				break;
			}
		}
		$this->breadcrumbs[] = array('lnk'=>seMultiDir().'/', 'name'=>$startpagetitle);
		if ($this->pagename !== $this->startpage){
			$active =  ($_SERVER['REQUEST_URI'] == seMultiDir().'/'.$this->pagename);
			$this->breadcrumbs[] = array('lnk'=>seMultiDir().'/'.$this->pagename, 'name'=>$this->page->title, 'active'=>$active);
		}		
  }

  private function objectLinkName($sect_id, $object) 
  {
		$urlname = (!empty($object->url)) ? $object->url : se_translite_url($object->title);
		$urlname = (is_numeric($urlname)) ? 'r'.$urlname : $urlname;
		if (empty($urlname)) {
		   $urlname = $sect_id .'-'. $object->id;
		}
		return $urlname;
  }

  private function objectLink($sect_id, $object) 
  {
	    if ($sect_id < 100000)
			$pagelink = $this->pagename;
		else $pagelink = 'index';
		return seMultiDir() . '/' . $pagelink . '/' . $this->objectLinkName($sect_id, $object) . SE_END;
  }

  
  private function getOldUrl($nameurl)
  {
    if (preg_match("/\b([^_]+)?\_([^_]+)\_([^_]+)/", $nameurl, $m) || (getRequest('razdel', 0) && getRequest('object', 1))){
	    if (getRequest('object', 1)){
          $this->req->razdel = getRequest('razdel', 0);
          $this->req->object = getRequest('object', 1);
		} else {
		  $this->req->razdel = $m[2];
          if (strpos($m[3],'sub')!==0){
            $this->req->object = $m[3];
            if ($this->req->object == 0) $this->go404();
          } else $this->req->sub = substr($m[3],3);
		}
        $this->req->page = $this->namepage;
		return true;
    }
	
	
	
  }

    // �������������� ������ ������� � ������� 
  private function getFromUrl($nameurl) {
    $namepage = '';
	if ($this->getOldUrl($this->req->param[1])) {
	    if($this->req->razdel > 100000) {
            $sections = $this->prj->sections;
        } else {
            $sections = $this->page->sections;
        }
        foreach($sections as $section){
		    if (strval($section->id) == $this->req->razdel){
				foreach($section->objects as $object){
				   if (strval($object->id) == $this->req->object) {
						$this->go301($this->objectLink($section->id, $object));
				   }
				}
				break;
			}
		}
	
	}
	if (!empty($this->req->param[2])){
        if ($nameurl == $this->req->param[1]){
            $sections = $this->page->sections;
        } else {
            $sections = $this->prj->sections;
        }
        if ($this->req->param[1] == 'index') $this->req->param[1] = '';
		$url = $this->req->param[2];
        foreach($sections as $section){
            foreach($section->objects as $object){
                if ($this->objectLinkName($section->id, $object) == $url) {
				    $this->req->razdel = $section->id;
				    $this->req->object = $object->id;
                    $nameurl = $this->req->param[1];
                    break;
                }
            }
        }
    }
	if ($newname = $this->getOldUrl($nameurl)){
	    return $newname;
	}
 }  
  private function rootUrl(){
	  if ($url = $this->getFromUrl($this->pagename)){
          $this->pagename = $url;
      } else {
          $this->req->sub = (!getRequest('sub')) ? $this->req->sub : getRequest('sub');
          $this->req->razdel = (!getRequest('razdel', 0)) ? $this->req->razdel : getRequest('razdel', 0);
          $this->req->object = (!getRequest('object', 1)) ? $this->req->object : getRequest('object', 1);
		  
		  
          if ($this->req->object || $this->req->sub){
            if (empty($_POST)){
                list($num_sect,) = explode('.', $this->req->razdel);
                if ($num_sect < 100000)
                    $pagelink = $this->pagename .'_';
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
				list(,$req) = explode('?', $_SERVER['REQUEST_URI']);
				$req = ($req) ? '?'.$req : ''; 
                if (!$this->req->sub){
                  //  $link = seMultiDir() . '/' . $pagelink.$this->req->razdel.'_sub'.$this->req->sub . '/' . $addlink . $req;
                //else
                    //$link = seMultiDir() . '/' . $pagelink.$this->req->razdel . '_' . $this->req->object . '/' . $addlink . $req;
					//$this->go301($link);
				}
            } 
          }
      }
      if (strpos($this->pagename, 'show') === 0){
           if (!empty($_SESSION['SE'][$this->pagename]))
               $this->pagename = $_SESSION['SE'][$this->pagename];
           else
           if (!empty($_SESSION['se']['page']))
               $this->pagename = $_SESSION['se']['page'];
           else
               $this->pagename = substr($this->pagename, 4);
      }
  }

  public function getBreadCrumbs(){
      ob_start();
      include dirname(__FILE__).'/tpl/breadcrumbs.php';
      $content = ob_get_contents();
      ob_end_clean();
      return $content;
  }

  
  private function redirectPage(){
        if (empty($_POST) && $this->pagename && ($_SERVER['REQUEST_URI'] == seMultiDir(). '/' . $this->pagename 
        || $_SERVER['REQUEST_URI'] == seMultiDir(). '/' . $this->pagename . '.html')){
                $this->go301(seMultiDir().'/'. $this->pagename . '/');
        }
        if (!preg_match("/[\?\&\=\.]/", $_SERVER['REQUEST_URI']) && substr($_SERVER['REQUEST_URI'], -1, 1)!='/'){ 
            $this->go301($_SERVER['REQUEST_URI'] . '/');
        }

        if (strpos($_SERVER['REQUEST_URI'], seMultiDir().'/'.$this->pagename.'/')===0) {
            $alias = explode('?',substr($_SERVER['REQUEST_URI'], strlen(seMultiDir().'/'. $this->pagename.'/')),2);
        }
  }


 
  // Получение ID раздела
  private function getAjaxId()
  {
        $reqajax = array_keys($_REQUEST);
        if (!empty($reqajax))
        foreach($reqajax as $res) {
            if (strpos($res, 'ajax')!==false) {
                if (preg_match("/ajax([\d\.]+)/", $res, $reqajax)) {
                   return strval($reqajax[1]);
                }
            }
        }
        return false;
  }


  // Получение ID раздела
  private function getAjaxType()
  {
        $reqajax = array_keys($_REQUEST);
        if (!empty($reqajax))
        foreach($reqajax as $res) {
            if (strpos($res, 'ajax')!==false) {
                if (preg_match("/ajax_([\w]+)/", $res, $reqajax)) {
                   return strval($reqajax[1]);
                }
            }
        }
        return false;
  }

  // Обработка Ajax запросов
  private function onAjax()
  {
        // Проверка на Ajax запросы
        $this->ajaxId = $this->getAjaxId();
		if ($this->ajaxId) {
			list($part_id) = explode('.', $this->ajaxId);
			if ($part_id < 100000) {
				$this->initpage();
			}
		} else {
			$this->ajaxType = $this->getAjaxType();
			if ($this->ajaxType) {
				$this->initpage();
			}
		}
  }

  /* Получить имя рабочей папки */
  private function getWorkFolder($namefile)
  {
  	  return ($this->editor->editorAccess() && file_exists(SE_ROOT.'projects/' . SE_DIR . 'edit/' . $namefile)
	         && filemtime(SE_ROOT.'projects/' . SE_DIR . 'edit/' . $namefile) > filemtime(SE_ROOT.'projects/' . SE_DIR . $namefile)
	         ) ? 'edit/' : ''; 
  }

  // Загрузка файла проекта
  private function initprj() 
  {
        $folder = $this->getWorkFolder('project.xml');
        if (file_exists(SE_ROOT.'projects/' . SE_DIR . $folder . 'project.xml')) {
            $this->prj = simplexml_load_file(SE_ROOT.'projects/' . SE_DIR . $folder . 'project.xml');
            $this->startpage = (!empty($this->prj->vars->startpage)) ? strval($this->prj->vars->startpage) : 'home';
            define('SE_STARTPAGE', $this->startpage);
            if (strval($this->prj->vars->language) == '') {
                $this->prj->vars->language = 'rus';
            }
            if (strval($this->prj->sitedomain) && $this->prj->siteredirect == '1') {
                //$urlsite = strtolower(str_replace(array('http://','https://'),'', $this->prj->sitedomain));
                if (strpos($this->prj->sitedomain, '://')===false) $this->prj->sitedomain = _HTTP_ . $this->prj->sitedomain;
                if (_HTTP_ . $_SERVER['HTTP_HOST'] != $this->prj->sitedomain) {
                    $this->go301($this->prj->sitedomain . $_SERVER['REQUEST_URI']);
                }
            }
            if (strval($this->language) != strval($this->prj->vars->language)) {
                $this->language = strval($this->prj->vars->language);
                $fp = fopen(SE_ROOT . 'projects/'. SE_DIR .'cache/project_lang.dat', "w+");
                fwrite($fp, $this->language);
                fclose($fp);
            }
            if (empty($this->pagename)) {
                $this->pagename = 'home';
                if (!empty($this->startpage)) {
                    $this->pagename = $this->startpage;
                    $folder = $this->getWorkFolder('pages/' . $this->pagename . '.xml');
                    if (!file_exists(SE_ROOT.'projects/' . SE_DIR . $folder . 'pages/' . $this->pagename  .'.xml')) {
                        $this->pagename = 'home';
                    }
                }
            }
            if (!$this->startpage  || !file_exists(SE_ROOT.'projects/' . SE_DIR . $folder . 'pages/' . $this->startpage  .'.xml')) {
              $this->startpage = 'home';
            }
            
            $uri = $_SERVER['REQUEST_URI'];
            if (str_replace('/', '', $uri) == $this->startpage
                || ($uri == '/' . SE_DIR . $this->startpage . URL_END && seMultiDir() == '')
            ) {
                 $this->go301(seMultiDir() . '/');
            }
            if (SE_DIR != '' && substr($uri, 1, strlen(SE_DIR)) == SE_DIR && seMultiDir() == ''){
                 $uri = substr($uri, strlen(SE_DIR), strlen($uri));
                 $this->go301($uri);
            }
        }
        $this->prj->wmgoogle = trim($this->prj->wmgoogle);
        $this->prj->wmyandex = trim($this->prj->wmyandex);
        if (!empty($this->prj->wmgoogle) && !file_exists(SE_ROOT . SE_DIR . $this->prj->wmgoogle) && strpos($this->prj->wmgoogle, '.html')!==false) {
            $fp = fopen(SE_ROOT . SE_DIR . $this->prj->wmgoogle, "w+");
            fwrite($fp, 'google-site-verification: ' . strval($this->prj->wmgoogle));
            fclose($fp);
        }
        if (!empty($this->prj->wmyandex)){
            $this->headercss[] = '<meta name="yandex-verification" content="'. $this->prj->wmyandex . '" />';
        }
        if (!empty($this->prj->bootstraptools) || !empty($this->prj->adaptive)) {
            $this->footer[] = "<script type=\"text/javascript\" src=\"/lib/js/jquery/jquery.min.js\"></script>";
        }
        if ((!empty($this->prj->adaptive) && !isset($this->prj->bootstraptools)) || $this->prj->bootstraptools == 1) {
            $this->headercss[] = '<link href="/lib/js/bootstrap/css/bootstrap.min.css" id="pageCSS" rel="stylesheet" type="text/css">';
            $this->footer[] = "<script type=\"text/javascript\" src=\"/lib/js/bootstrap/bootstrap.min.js\"></script>";
            $this->footer[] = "<script type=\"text/javascript\" src=\"/lib/js/bootstrap/bootstrap.init.js\"></script>";
        }
        if (!empty($this->prj->setools)) {
            $this->footer[] = "<script type=\"text/javascript\" src=\"/lib/js/fancybox2/jquery.fancybox.pack.js\"></script>";
            $this->footercss[] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"/lib/js/fancybox2/jquery.fancybox.css\">";
            $this->footer[] = "<script type=\"text/javascript\" src=\"/lib/js/fancybox2/helpers/jquery.fancybox-thumbs.js\"></script>";
            $this->footer[] = "<script type=\"text/javascript\" src=\"/lib/loginza/modallogin.js\"></script>";
        }
        $_SESSION['editor_page'] = strval($this->pagename);
        define('DEFAULT_LANG', strval($this->language));
        $this->path[0] = array('name' => $this->startpage, 'title'=>'');
  }


  private function initpage() {
		$folder = $this->getWorkFolder('pages/' . $this->pagename . '.xml');
		
		if (!file_exists(SE_ROOT.'projects/' . SE_DIR . $folder . 'pages/' . $this->pagename  .'.xml')) {
		    $this->error = true;

	        if (!file_exists('projects/' . SE_DIR . 'pages/404.xml')) 
			{
	  			header("Location: http://e-stile.ru/404.php");
	  			exit();
	  		} 
			else 
			{
				$this->pagename  = '404';
	  		}
		} else {
		    $this->lastmodif = filemtime(SE_SAFE.'projects/' . SE_DIR . $folder . 'pages/' . $this->pagename  .'.xml');
		}
		if ($this->pagename  == '404') {
			 header('HTTP/1.0 404 File not found');
		}
		if (file_exists(SE_ROOT.'projects/' . SE_DIR . $folder . 'pages/' . $this->pagename  .'.xml')) {
			$this->page = simplexml_load_file(SE_ROOT.'projects/' . SE_DIR . $folder . 'pages/' . $this->pagename . '.xml');
			$_SESSION['se']['page'] = strval($this->pagename);
		} else {
			$this->page = new SimpleXMLElement('<page></page>');	
		}
		if ($this->page->css == '') $this->page->css = 'default';
  }

  private function initpages() {
  	$folder = $this->getWorkFolder('pages.xml');
	if (file_exists(SE_ROOT.'projects/' . SE_DIR . $folder . 'pages.xml')) {
		$this->pages = simplexml_load_file(SE_ROOT.'projects/' . SE_DIR . $folder . 'pages.xml');
	} else {
		$this->pages = new stdClass;
	}
	 if (!$this->editor->editorAccess()) {
        $result = $this->pages->xpath('page[@name="' . $this->pagename . '"]');
        if (empty($result)) {
			if (file_exists(SE_ROOT.'projects/' . SE_DIR . 'pages/' . $this->pagename . '.xml')){
				unlink(SE_ROOT.'projects/' . SE_DIR . 'pages/' . $this->pagename . '.xml');
			}
			//echo $url = $this->getAltUrlList($this->pagename);
			
			if ($url = $this->findUrlList($this->pagename)) {
				$this->go301($url);
			}
	    //}
		
		//elseif ($url = $this->getAltUrlList($nameurl)) {
	   //echo $url;
	   //exit;
	   //$this->go301(seMultiDir().'/'.$url.'/');
	//}
          //$this->go404();
        }
	 }
  }

  // Загрузка файлов меню
  private function initmenu() 
  {
		$folder = $this->getWorkFolder('pagemenu.xml');
	    if (file_exists(SE_ROOT.'projects/' . SE_DIR . $folder . 'pagemenu.xml')) {
			$this->pagemenu = simplexml_load_file(SE_ROOT.'projects/' . SE_DIR . $folder . 'pagemenu.xml');
		} else {
			$this->pagemenu = new stdClass;
		}
		
		$folder = $this->getWorkFolder('mainmenu.xml');
	    if (file_exists(SE_ROOT.'projects/' . SE_DIR . $folder . 'mainmenu.xml')){
			$this->mainmenu = simplexml_load_file(SE_ROOT.'projects/' . SE_DIR . $folder . 'mainmenu.xml');
		} else {
			$this->mainmenu = new stdClass;
		}
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

  public function getHTTP($url){
      $c = curl_init($url);
      curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
      $content = curl_exec($c);
      curl_close($c);
      return $content;
  }

  public function go404(){
    if (!file_exists('projects/' . SE_DIR . 'pages/404.xml')){
        header("Location: http://e-stile.ru/404.php");
        //print preg_replace("/[\"](images|skin)\//", '"http://e-stile.ru/$1/', $this->getHTTP("http://e-stile.ru/404.php"));
    } else {
        header('HTTP/1.0 404 File not found');
        print $this->getHTTP('http://'.$_SERVER['HTTP_HOST'].seMultiDir().'/404/');
    }
    exit;
  }

  public function include_tpl($section, $name)
  {
      $pagename = ($section->id > 100000) ? '_' : $this->pagename;
      $path_cache = SE_ROOT . "projects/" . SE_DIR . 'cache/' . $pagename . '/';
      $filename = $section->type . '_' . $name . '_' .$section->id . '.tpl';
      $objname = str_replace('subpage_','', $name);
      $subname = str_replace('subpage_','sub', $name);
      $is_source = (!empty($section->sources->$subname) || !empty($section->sources->$objname));
      if (file_exists($path_cache . $filename ) && $is_source) {
               return $path_cache . $filename;
      } else {
               if (file_exists($path_cache . $filename)) unlink($path_cache . $filename);
               $MDL_ROOT = getcwd() . $this->getFolderModule($section->type) . '/' . strval($section->type);
               return $MDL_ROOT . '/tpl/' . $name . '.tpl';
      }
  }

  public function link_tpl($section, $name){
      $pagename = ($section->id > 100000) ? '_' : $this->pagename;
      $path_cache = "projects/" . SE_DIR . 'cache/' . $pagename . '/';
      $filename = $section->type . '_' . $name . '_' .$section->id . '.tpl';
      $objname = str_replace('subpage_','', $name);
      $subname = str_replace('subpage_','sub', $name);
      $is_source = (!empty($section->sources->$subname) || !empty($section->sources->$objname));
      if (file_exists(SE_ROOT . $path_cache . $filename ) && $is_source) {
               return '/' . $path_cache . $filename;
      } else {
               if (file_exists(SE_ROOT . $path_cache . $filename)) unlink(SE_ROOT . $path_cache . $filename);
               $MDL_ROOT = $this->getFolderModule($section->type) . '/' . strval($section->type);
               return $MDL_ROOT . '/tpl/' . $name . '.tpl';
      }
  }

  public function execute()
  {
        global $SE_REQUEST_NAME;
	if (empty($this->page->title_tag)) $this->page->title_tag = 'h1';
	$this->sections = array();
	foreach($this->prj->sections as $value)
	{
		$id_content = strval($value['name']);
	  	if ($this->ajaxId && $this->ajaxId != $id_content) continue;
	  	//if ($this->ajaxType && $this->ajaxType != strval($value->type)) continue;
		if (empty($value->title_tag)) $value->title_tag = 'h3';
		$this->sections[$id_content] = $value;
	}

	if (count($this->page->sections))
	foreach($this->page->sections as $value)
	{
		$id_content = strval($value['name']);
	  	if ($this->ajaxId && $this->ajaxId != $id_content) continue;
	  	//if ($this->ajaxType && $this->ajaxType != strval($value->type)) continue;
		if (empty($value->title_tag)) $value->title_tag = 'h3';
		$this->sections[strval($id_content)] = $value;
	}
	  // Save content
	 $this->editor->editContent();
	$modulesArr = array();
	if (!empty($this->sections))
	foreach($this->sections as $id_content=>$section)
	{
	  $id_content = strval($section->id);
	  if ($this->req->object && $this->req->razdel == $id_content){
		$obj = $this->getObject($section, $this->req->object);
		$this->page->titlepage = (!empty($obj->meta_title)) ? strip_tags($obj->meta_title) : strip_tags($obj->title);
		$this->page->keywords = (!empty($obj->meta_keywords)) ? strip_tags($obj->meta_keywords) : strip_tags($obj->title);
		$this->page->description = (!empty($obj->meta_descr)) ? strip_tags($obj->meta_descr) : strip_tags($obj->note);
	  }

      $is_add_url = false;
      $first = 0;
      $row = 1;
	  if (count(array($section->objects)))
	  foreach($section->objects as $object)
	  {
	    if ($object->showrecord == 'off') {
	        //continue;
	    }
	    if ($first == 0) $first = intval($object->id);
	    if ($section->showrecord != 'off') {
	  	$object->link_detail = $this->objectLink($section->id, $object);
	    }
		$object->end = count($section->objects);
	        $object->first = $first;
	        $object->row = $row;
	        $object->num = $row - 1;
	  	if (!empty($object->image) && empty($object->image_prev))
		{
			if (strpos($object->image, '://')===false){
				$prev = explode('.', $object->image);
				$object->image_prev = $prev[0] . '_prev.' . $prev[1];
			} else {
			    $object->image_prev = $object->image;
			}
		}
		if (!strval($object->image_alt)) {
		    $object->image_alt = htmlspecialchars($object->title);
		}
		if (empty($object->title_tag)) $object->title_tag = 'h4';
		$row++;
	  }
	  if (strval($section->showrecord) == 'off' && strval($section->id) == strval($this->req->razdel) && intval($this->req->object)) {
	      $this->go404();
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


	  $nametype = $section->type;
	  $id_content = strval($section->id);
	  $modulepath = '';
	  if (!function_exists('start_' . $nametype))
	  {
			$modulepath = $this->getFolderModule($nametype);
			$root =  getcwd() . $modulepath . '/mdl_' . $nametype . '.php';
   			if (file_exists($root)) require_once($root);
	  }

	  if (function_exists('module_' . $nametype) && $this->getStatusService($nametype))
	  {
                $fl_find_source = false;
		if (!empty($section->sources)){
                    $fl_find_source = true;
                    $pagename = ($section->id > 100000) ? '_' : $this->pagename;
		    $path_cache = SE_ROOT . "projects/" . SE_DIR . 'cache/' . $pagename . '/';
		    
		    $filename_page = SE_ROOT . "projects/" . SE_DIR;
		    $filename_page .=  ($pagename == '_') ? 'project.xml' : 'pages/' . $this->pagename . '.xml';
		    if (!file_exists($path_cache)) mkdir($path_cache);
		    foreach($section->sources[0] as $name=>$value){
                       if ($value == '') continue;
                       if (strpos($name, 'sub')===0) $name = substr($name, 3);
		       if (file_exists(getcwd() . $modulepath . '/' . $nametype . '/tpl/' . 'subpage_' . $name . '.tpl')){
		           $name = 'subpage_' . $name;
		       }
		       $name_cache = $path_cache .  $nametype . '_' . $name . '_' .$section->id . '.tpl';
		       if (!file_exists($name_cache) || filemtime($name_cache) <  filemtime($filename_page) || filemtime($name_cache) <  filemtime(__FILE__)){
                           $fp = fopen($name_cache, "w+");
		           fwrite($fp, $this->parseModule($value, $section));
		           fclose($fp);
		       }
		    }
		}


		if (!in_array($nametype, $modulesArr)) {
		    $modulesArr[] = $nametype;
		    if (file_exists(getcwd() . $modulepath . '/'.$nametype . '/style.css') && intval($section->oncss)){
		        //$mstyle = '/' . SE_SAFE . SE_WWWDATA . SE_DIR . 'skin/' . strval($nametype) . '/style.css';
		        
		        $mstyle = $modulepath . '/'.$nametype . '/style.css';
		        $link = '<link href="' . $mstyle .'" rel="stylesheet" type="text/css">';
		        if (!in_array($link, $this->headercss)){
					$this->headercss[] = $link;
				}
		    }
		}
		$arr = array();
		$arr = call_user_func_array('module_' . $nametype, array($id_content, $section));
		if (!SE_ALL_SERVICES && !$this->getStatusService($nametype, false)) {
			$arr['content']['form'] = '<div style="color: #FF0000;">&nbsp;'.$this->editor->getTextLanguage('close_service').'</div>' . $arr['content']['form'];
		}
		$arr['content']['form'] = $this->editor->setEditorLinks($section, $arr['content']['form']);
		
		$section->body = replace_link($this->getHeader($arr['content']['form'], $section));
		if (!empty($arr['content']['object']))
		{
			$section->formobject = replace_link($this->getHeader($arr['content']['object'], $section));
		}
		if (!empty($arr['content']['show']))
		{
			$section->formshow = $this->editor->addEditSection($section, replace_link($this->getHeader($arr['content']['show'], $section)));
			
		}
		if (!empty($arr['content']['arhiv']))
		{
			$section->formarhiv = $this->editor->addEditSection($section, replace_link($this->getHeader($arr['content']['arhiv'], $section)));
		}
		if (!empty($arr['subpage']))
		foreach($arr['subpage'] as $subname=>$value)
		{
			$section->subpage->$subname->form = $this->getHeader($value['form'], $section);
			$section->subpage->$subname->group = $value['group'];
		}
   	  }
	}
	if ($this->ajaxId || $this->ajaxType) exit;
	$urllist = from_Url();
	foreach($urllist as $uname=>$arr) {
	  $find = false;
	  @list($uname) = explode('[', urldecode($uname));
	  if (empty($uname)) continue;
          if (is_numeric($uname) &&  empty($_GET[$uname])) {
             $url = explode('?', $_SERVER['REQUEST_URI']);
             $this->go301($url[0]);
          }
	  foreach($SE_REQUEST_NAME as $qname=>$name) {
	     if (strval($uname) == strval($qname) || empty($arr) || isset($_GET[$uname])) {
	       $find = true; 
	       break;
	     }
	  }
	  if (!$find) {
	      $this->go404();
	  }
	}
	if (isset($_GET['page'])) {
	      $this->go404();
	}
	$footer = array();
	foreach($this->footer as $key => $line){
	    $line= trim($line);
	    if (in_array($line, $this->header, true)){
	        unset($this->footer[$key]);
	        //$footer[] = $line;
	    }
	}
	//print_r($this->footer);
  }
  
  public function getStatusService($servicename, $fl = true)
  {
	if ($fl && (SE_ALL_SERVICES || $this->editor->editorAccess())) return true;
	if ($this->services != null)
	{
		if (!empty($this->services->module))
		foreach($this->services->module as $serv)
		{
			if (strval($serv['name']) == strval($servicename) && $serv[0] == 1)
			{
				return true;
			} 
		}
		
		// Если модуль пользователя
		if (!empty($this->services->packet) && preg_match("/\bmain_/", $servicename) && $this->services->packet == 'usermodule')
		{
			return true;
		}
	    return false;
	} else return true;
  }

  public function getSkinService()
  {
	  return SE_DIR.'skin';
/*	if ($this->services != null)
	{
		if (!empty($this->services->skin))
		  return 'skin/templates/'.strval($this->services->skin);
		else 
	    	  return SE_DIR.'skin';
	} else {
	  return SE_DIR.'skin';
	}*/
  }

  public function getThisService($serv)
  {
	if ($this->services != null)
	{
		if (!empty($this->services->$serv))
		  return $this->services->$serv;
	}
	return false;
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
	    @list($url_protocol, $url_start) = explode('://', autoencode($ur[0]));
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
    if (file_exists($namepage) && !is_dir($namepage)) {
	    echo join('', file($namepage));
        exit;
    }
  }

  private function openUrlList(){
    $fileurl = 'projects/'.SE_DIR;
    if (file_exists($fileurl . '/roots.url')) {
        $this->urllist = json_decode(join('',file($fileurl . '/roots.url')), true);
    }
  }
  

  private function findUrlList($urlname, $fullpath = ''){
    if (!empty($this->urllist[$urlname])){
       return $this->urllist[$urlname];
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

  public function setVirtualPage($namepage, $type = 'text', $rewrite = true){
        if ($namepage){
            if (!file_exists('projects/'.SE_DIR.'types/')){
                mkdir('projects/'.SE_DIR.'types/');
            }
            if (!file_exists('projects/'.SE_DIR.'types/'.$type)
            || filemtime('projects/' . SE_DIR . 'types/'.$type) < filemtime('projects/' . SE_DIR . 'project.xml')
            || ($rewrite && $this->getVirtualPage($type)!=$namepage)){
                $fp = fopen('projects/'.SE_DIR.'types/'.$type, 'w+');
                fwrite($fp, $namepage);
                fclose($fp);
            }
        }
  }

  
  public function showSection($section, $add = true){
         $nametype = $section->type;
         $id_content = strval($section->id);
         $root = getcwd() . $this->getFolderModule($nametype) . '/mdl_' . $nametype . '.php';
         if (file_exists($root)) require_once($root);
         if (function_exists('module_' . $nametype)){
            if (count(array($section->objects)))
                foreach($section->objects as $object){
                    if (empty($object->title_tag)) $object->title_tag = 'h4';
                    $link = seMultiDir() . '/'.$this->pagename.'/'.$id_content.'/'.$object->id.'/';
                    $object->link_detail = $link;
                    if (!empty($object->image)) {
                        $prev = explode('.', $object->image);
                        $object->image_prev = $prev[0] . '_prev.' . $prev[1];
                    }
                }
            $arr = call_user_func_array('module_' . $nametype, array($id_content, $section));
            if ($section->type == '') $section->type = 'mtext';
            return $this->editor->addClassSection($section, $this->getHeader(replace_values($arr['content']['form']), $section), $add);
        }
  }

  private function getSrcNameScript($tag)
  {
      $jsname = '';
      if (preg_match("/src=\"(.+?)\"/", $tag, $jsname)){
         $jsname = basename($jsname[1]);
      }
      return $jsname;
  }

  private function getUrlNameLink($tag)
  {
      $jsname = '';
      if (preg_match("/href=\"(.+?)\"/", $tag, $jsname)){
         $jsname = basename($jsname[1]);
      }
      return $jsname;
  }


  private function parseHeader($header, $in)
  {
        $in = preg_replace("/\[js:([\w\d\.\/\-]+)\]/", "<script type=\"text/javascript\" src=\"/lib/js/$1\"></script>", $in);
        $in = trim(preg_replace("/\[lnk:([\w\d\.\/\-]+)\]/", "<link rel=\"stylesheet\" type=\"text/css\" href=\"/lib/js/$1\">", $in));
        preg_match_all("/<style.+?<\/style>/usim", $in, $arrheaderstyle);
        preg_match_all("/<script.+?<\/script>/usim", $in, $arrheaderjs);
        preg_match_all("/<link.+?>/usim", $in, $arrheaderlink);
        foreach($arrheaderjs[0] as $link){
            $link = trim($link);
            if ($link && !in_array($link, $header, true)) {
                $header[] = $link;
            }
        }

        foreach($arrheaderstyle[0] as $link){
            $link = trim($link);
            if ($link && !in_array($link, $header, true)) {
                $header[] = $link;
            }
        }
		
		
        foreach($arrheaderlink[0] as $link){
            $link = trim($link);
            if ($link && !in_array($link, $header, true)) {
                $header[] = $link;
            }
        }
        return $header;
  }


  public function getHeader($text, $section = '')
  {
    $modulefolder = '';
	if (!empty($section->type)) {
	     $modulefolder = $this->getFolderModule(strval($section->type)) . '/' . strval($section->type);
	}
	while ($modulefolder && preg_match("/\[include_js(\(.*?\))?\]/isum", $text, $m)){
        $jsfile = $modulefolder . '/' . $section->type.'.js';
        if (file_exists(getcwd() . $jsfile)){
            $s1 = "\r\n<script type=\"text/javascript\" src=\"{$jsfile}\"></script>";
            $s1 .= "\r\n<script type=\"text/javascript\"> {$section->type}_execute(";
            if (!empty($m[1])) $s1 .= utf8_substr($m[1], 1, -1);
            $s1 .= ');</script>';
        } else {
            $s1 = "\r\n<script type=\"text/javascript\" src=\"{$modulefolder}/engine.js\"></script>";
        }
        $text = str_replace($m[0], $s1, $text);
    }
    while ($modulefolder && preg_match("/\[include_css\]/imu", $text, $m)){
        $text = str_replace($m[0], '<link href="'.$modulefolder.'/style.css" rel="stylesheet" type="text/css">', $text);
    }

    while ($modulefolder && preg_match("/\[module_js:([^\]]*)\]/imu", $text, $m)){
        $s1 = "\r\n<script type=\"text/javascript\" src=\"{$modulefolder}/{$m[1]}\"></script>";
        $text = str_replace($m[0], $s1, $text);
    }

    while ($modulefolder && preg_match("/\[module_css:([^\]]*)\]/imu", $text, $m)){
        $s1 = "\r\n<link href=\"{$modulefolder}/{$m[1]}\" rel=\"stylesheet\" type=\"text/css\">";
        $text = str_replace($m[0], $s1, $text);
    }

    while (preg_match("/<header:js>(.+?)<\/header:js>/usim", $text, $m))
    {
        if (!empty($modulefolder)) {
            $m[1] = str_replace(array("[this_url_modul]", "[module_url]"), $modulefolder . '/', $m[1]);
        }
        $this->header = $this->parseHeader($this->header, $m[1]);
        $text = str_replace($m[0], '', $text);
    }
    // header css
    while (preg_match("/<header:css>(.+?)<\/header:css>/usim", $text, $m))
    {
        if (!empty($modulefolder)) {
            $m[1] = str_replace(array("[this_url_modul]", "[module_url]"), $modulefolder . '/', $m[1]);
        }
        $this->headercss = $this->parseHeader($this->headercss, $m[1]);
        $text = str_replace($m[0], '', $text);
    }
    while (preg_match("/<footer:html>(.+?)<\/footer:html>/usim", $text, $m))
    {
        if (!empty($modulefolder)) {
            $m[1] = str_replace(array("[this_url_modul]", "[module_url]"), $modulefolder . '/', $m[1]);
        }
        $this->footerhtml .= $m[1];
        $text = str_replace($m[0], '', $text);
    }

    // footer js
    while (preg_match("/<footer:js>(.+?)<\/footer:js>/usim", $text, $m))
    {
        if (!empty($modulefolder)) {
            $m[1] = str_replace(array("[this_url_modul]", "[module_url]"), $modulefolder . '/', $m[1]);
        }
        $this->footer = $this->parseHeader($this->footer, $m[1]);
        $text = str_replace($m[0], '', $text);
    }
    while (preg_match("/<footer:css>(.+?)<\/footer:css>/usim", $text, $m))
    {
        if (!empty($modulefolder)) {
            $m[1] = str_replace(array("[this_url_modul]", "[module_url]"), $modulefolder . '/', $m[1]);
        }
        $this->footercss = $this->parseHeader($this->footercss, $m[1]);
        $text = str_replace($m[0], '', $text);
    }
    return $text;
  }


  public function getFolderModule($type)
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
			if (!empty($name)){
				$level_arr[$level - 1]['name'] = $name;
				$level_arr[$level - 1]['title'] = strval($page->title);
				if ($name == $this->pagename) {
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
	$startpage = $this->startpage;
	if (empty($startpage)) $startpage = 'home';
	    $link = '';
	$level_arr = $this->getPathArray();
	//print_r($level_arr);

	foreach($this->pages as $page)
	{
	    if (strval($page['name']) == $startpage) {
		$link = ' <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a href="/'. $startpage. SE_END . '" itemprop="url"><span itemprop="title">' . $page->title . '</span></a></span> ';
		break;
	    }
	}

	$linkTemplate = '';     //  если НЕ пустой, то значит мы находимся на субстранице
	if ($this->req->razdel && $this->req->object)
	{

	    $objects = $this->sections[strval($this->req->razdel)]->objects;
	    foreach($objects as $object)
	    {
		if ($object->id == intval($this->req->object))
		{
		    $linkTemplate = '<span class="space">'. $space . '</span> '.'<span itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><span class="endtitle" itemprop="title">'.$object->title.'</span></span> ';
		    break;
		}
	    }
	    $title = $this->section->title;
	}

	$getLastElement = end($level_arr);  //  вытаскиваем последний элемент для того, чтобы он далее не стал ссылкой
	foreach($level_arr as $line)
	{
	    if ($line['name'] == $startpage) continue;
	    if (empty($line['name'])) break;
	    if (($linkTemplate == '') && ($getLastElement['title'] == $line['title'])) {
		$link .= '<span class="space">'.$space . '</span> <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><span itemprop="title">'.$line['title'].'</span></span> ';
	    } else {
		$link .= '<span class="space">'.$space . '</span> <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a href="/'. $line['name'] . SE_END . '" itemprop="url"><span itemprop="title">'.$line['title'].'</span></a></span> ';
	    }
	}
	$link .= $linkTemplate;
	if ($endtitle != ''){
	    $link .= '<span class="space">'.$space . '</span> <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><span class="endtitle" itemprop="title">'. $endtitle .'</span></span>';
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
  
  public function getLinkPageName()
  {
      return ($this->getPageName() == strval($this->startpage)) ? seMultiDir() . URL_END : seMultiDir() . '/' . $this->getPageName() . URL_END;
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
    		$this->go404(); //301(seMultiDir().'/'.$this->getPageName().'/');
  	    }
  	}
  }

	public function getSectionContent($id_content, $scope = null) {
		//var_dump($id_content);
		if (is_null($scope)) {
			$scope = $this->sections;
		}
		$section_array = array();
		foreach ($scope as $section) {
			$id_section = strval($section->id);
			list($id_section,) = explode('.', $id_section);
                        if (floor($id_section/1000)==$id_content) {
				$section_array[] = $section;
			}
		}
		return $section_array;
	}

    public function getSection($id_section)
    {
        if ($id_section){
            foreach($this->sections as $section){
                if (strval($section['name']) == strval($id_section)){
                    return $section;
                }
            }
            if (!$this->editor->editorAccess()) 
                $this->go301(seMultiDir().'/'.$this->getPageName().'/');
        }
    }

    public function deleteSection($id_section)
    {
        $i = 0;
        if (!$id_section) return;
        if ($id_section < 10000){
            foreach($this->page->sections as $section) {
                if (strval($section['name']) === $id_section) {
                    unset($this->page->sections[$i]);
                    break;
                }
                $i++;
            }
        } else {
            foreach($this->prj->sections as $section) {
                if (strval($section['name']) === $id_section) {
                    unset($this->prj->sections[$i]);
                    break;
                }
                $i++;
            }
        }
    }

  
  public function getMaxSection($id_content, $sections = null)
  {
	if ($sections == null) $sections = $this->sections;
	$max = $id_content * 1000;
    	foreach($sections as $section)
    	{
    	    if(intval($section->id) < $id_content * 1000 || intval($section->id) > $id_content * 1000 + 1000 ){ 
    		continue;
    	    }
    	    if (intval($section->id) > $max)
    	    {
    		$max = intval($section->id);
    	    }
    	}
	return $max;
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
    $this->req->razdel = $section->id;
    $this->req->sub = $subname;
  }

  public function limitObjects($section, $limit = -1, $sort = 0)
  {
    if ($limit < 0) $limit = intval($section->objectcount);
    if($limit < 1) $limit = 30;
    $_item = getRequest('item',1);
    $page = $this->getPageName();
    if (!empty($_SESSION['SE'][$page.'_'.$section.'_item'])){
	$_item = $_SESSION['SE'][$page.'_'.$section.'_item'];
    } else { 
	$_item = getRequest('item',1);
    }
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
    if (!$sort) {
        $objects = $section->objects;
    } else {
        $objects = array();
        $k = count($section->objects);
        foreach($section->objects as $it) {
            $objects[] = $section->objects[$k - 1];
            $k--;
        }
    }
    
    unset($section->records);
    foreach($objects as $record)
    {
        if(empty($record) || $record->visible == 'off') continue;
        $i++;
        if($i <= $startitem)continue;
        if($i > $enditem)break;
        if ($record->text1!='') list($record->text1) = explode('|', $record->text1);
        $record->row = $i;
        $this->setItemList($section, 'records', $record);
    }
    return $section->records;
  }
  
  public function savePage(){
	//file_put_contents(SE_ROOT.'/projects/' . SE_DIR . 'pages/' .$this->pagename.'.xml',  preg_replace("/[\s]{1,}[\r\n]/","", $this->page->saveXML()));
	if (!file_exists(SE_ROOT.'projects/' . SE_DIR . 'edit/')) mkdir(SE_ROOT.'projects/' . SE_DIR . 'edit/');
	if (!file_exists(SE_ROOT.'projects/' . SE_DIR . 'edit/pages/')) mkdir(SE_ROOT.'projects/' . SE_DIR . 'edit/pages/');
	if (!empty($this->page))
	file_put_contents(SE_ROOT.'projects/' . SE_DIR . 'edit/pages/' .$this->pagename.'.xml',  preg_replace("/[\s]{1,}[\r\n]/","", $this->page->saveXML()));	
  }

  public function savePrj(){
	if (!file_exists(SE_ROOT.'projects/' . SE_DIR . 'edit/')) mkdir(SE_ROOT.'projects/' . SE_DIR . 'edit/');
	file_put_contents(SE_ROOT.'projects/' . SE_DIR . 'edit/project.xml', preg_replace("/[\s]{1,}[\r\n]/","", $this->prj->saveXML()));

	if (file_exists(SE_ROOT.'projects/' . SE_DIR . 'edit/pages/' .$this->pagename.'.xml.log'))
	  unlink(SE_ROOT.'projects/' . SE_DIR . 'edit/pages/' .$this->pagename.'.xml.log');
  }

  public function savePages(){
	if (!file_exists(SE_ROOT.'projects/' . SE_DIR . 'edit/')) mkdir(SE_ROOT.'projects/' . SE_DIR . 'edit/');
	file_put_contents(SE_ROOT.'projects/' . SE_DIR . 'edit/pages.xml', $this->pages->saveXML());
  }

  // Публикуем проект и сохраняем старые данные в архиве
  public function storeProject(){
	if (!file_exists(SE_ROOT.'projects/' . SE_DIR . 'arhiv/')) mkdir(SE_ROOT.'projects/' . SE_DIR . 'arhiv/');	
	if (!file_exists(SE_ROOT.'projects/' . SE_DIR . 'arhiv/pages/')) mkdir(SE_ROOT.'projects/' . SE_DIR . 'arhiv/pages/');	
	$d=opendir(SE_ROOT.'projects/' . SE_DIR . 'edit/');
	if (!empty($d))
	while(($f=readdir($d))!==false) {
		if ($f=='.'||$f=='..' || is_dir($f)) continue;
		if (strpos($f,'.xml') !== false) {
			//echo $f.'<br>';
			if (file_exists(SE_ROOT.'projects/' . SE_DIR . 'arhiv/'.$f)){
				unlink(SE_ROOT.'projects/' . SE_DIR . 'arhiv/'.$f);
			}
			rename(SE_ROOT.'projects/' . SE_DIR . $f, SE_ROOT.'projects/' . SE_DIR . 'arhiv/'.$f);
			rename(SE_ROOT.'projects/' . SE_DIR . 'edit/'.$f, SE_ROOT.'projects/' . SE_DIR .$f);
			if (file_exists(SE_ROOT.'projects/' . SE_DIR .$f.'.log'))
			    unlink(SE_ROOT.'projects/' . SE_DIR .$f.'.log');
		}
	}
	closedir($d);

	$d=opendir(SE_ROOT.'projects/' . SE_DIR . 'edit/pages/');
	if (!empty($d))
	while(($f=readdir($d))!==false) {
		if ($f=='.'||$f=='..' || is_dir($f)) continue;
		if (strpos($f,'.del') !== false) {
			if (file_exists(SE_ROOT.'projects/' . SE_DIR . 'pages/'.delExtFile($f).'.xml')) {
				unlink(SE_ROOT.'projects/' . SE_DIR . 'arhiv/pages/'.delExtFile($f).'.xml');
				rename(SE_ROOT.'projects/' . SE_DIR . 'pages/'.delExtFile($f).'.xml', 
					SE_ROOT.'projects/' . SE_DIR . 'arhiv/pages/'.delExtFile($f).'.xml');
			}
			unlink(SE_ROOT.'projects/' . SE_DIR . 'edit/pages/'.$f);
		}
		if (strpos($f,'.xml') !== false) {
			if (file_exists(SE_ROOT.'projects/' . SE_DIR . 'arhiv/pages/'.$f)){
				unlink(SE_ROOT.'projects/' . SE_DIR . 'arhiv/pages/'.$f);
			}
			if (file_exists(SE_ROOT.'projects/' . SE_DIR . 'edit/pages/'.$f)) {
				rename(SE_ROOT.'projects/' . SE_DIR . 'pages/'.$f, SE_ROOT.'projects/' . SE_DIR . 'arhiv/pages/'.$f);
				rename(SE_ROOT.'projects/' . SE_DIR . 'edit/pages/'.$f, SE_ROOT.'projects/' . SE_DIR .'pages/'.$f);
				if (file_exists(SE_ROOT.'projects/' . SE_DIR .'pages/'.$f.'.log'))
				    unlink(SE_ROOT.'projects/' . SE_DIR .'pages/'.$f.'.log');
			}
		}
	}
	closedir($d);
  }


  // Восстановление проекта из архива
  public function restoreProject(){
	$d=opendir(SE_ROOT.'projects/' . SE_DIR . 'edit/');
	if (!empty($d))
	while(($f=readdir($d))!==false) {
		if ($f=='.'||$f=='..' || is_dir($f)) continue;
		if (strpos($f,'.xml') !== false) {
			if (file_exists(SE_ROOT.'projects/' . SE_DIR . 'edit/'.$f)){
				unlink(SE_ROOT.'projects/' . SE_DIR . 'edit/'.$f);
			}
		}
	}
	closedir($d);

	$d=opendir(SE_ROOT.'projects/' . SE_DIR . 'arhiv/');
	if (!empty($d))
	while(($f=readdir($d))!==false) {
		if ($f=='.'||$f=='..' || is_dir($f)) continue;
		if (strpos($f,'.xml') !== false) {
			copy(SE_ROOT.'projects/' . SE_DIR . 'arhiv/'.$f, SE_ROOT.'projects/' . SE_DIR . 'edit/'.$f);
		}
	}
	closedir($d);
	
	
	$d=opendir(SE_ROOT.'projects/' . SE_DIR . 'edit/pages/');
	if (!empty($d))
	while(($f=readdir($d))!==false) {
		if ($f=='.'||$f=='..' || is_dir($f)) continue;
		if (strpos($f,'.xml') !== false) {
			if (file_exists(SE_ROOT.'projects/' . SE_DIR . 'edit/pages/'.$f)){
				unlink(SE_ROOT.'projects/' . SE_DIR . 'edit/pages/'.$f);
			}
		}
	}
	closedir($d);
	$d=opendir(SE_ROOT.'projects/' . SE_DIR . 'arhiv/pages/');
	if (!empty($d))
	while(($f=readdir($d))!==false) {
		if ($f=='.'||$f=='..' || is_dir($f)) continue;
		if (strpos($f,'.xml') !== false) {
			copy(SE_ROOT.'projects/' . SE_DIR . 'arhiv/pages/'.$f, SE_ROOT.'projects/' . SE_DIR . 'edit/pages/'.$f);
		}
	}
	closedir($d);
  }

  // Сбросить редактирование
  public function returnProject(){
	$d=opendir(SE_ROOT.'projects/' . SE_DIR . 'edit/');
	if (!empty($d))
	while(($f=readdir($d))!==false) {
		if ($f=='.'||$f=='..' || is_dir($f)) continue;
		if (strpos($f,'.xml') !== false) {
			//echo $f.'<br>';
			if (file_exists(SE_ROOT.'projects/' . SE_DIR . 'edit/'.$f)){
				unlink(SE_ROOT.'projects/' . SE_DIR . 'edit/'.$f);
			}
		}
	}
	closedir($d);

	$d=opendir(SE_ROOT.'projects/' . SE_DIR . 'edit/pages/');
	if (!empty($d))
	while(($f=readdir($d))!==false) {
		if ($f=='.'||$f=='..' || is_dir($f)) continue;
		if (strpos($f,'.xml') !== false) {
			echo $f.'<br>';
			if (file_exists(SE_ROOT.'projects/' . SE_DIR . 'edit/pages/'.$f)){
				unlink(SE_ROOT.'projects/' . SE_DIR . 'edit/pages/'.$f);
			}
		}
	}
	closedir($d);
  }


  
  public function getVars($type, $name){
	$result = '';
	if ($name == 'enteringtext' || $name == 'closingtext' || $name == 'title')
	{
		$result = $this->page->$name;
	} else {
	        if ($name == 'reklamform' && $this->getThisService('reclam') && file_exists($this->getSkinService().'/reclam.dat')){
		    $result = join('', file($this->getSkinService().'/reclam.dat'));
	        } else
				$result = $this->$type->vars->$name;

                if ($name == 'newsform'){
                    $result = skin_news($result);
                }
	}

	if (utf8_strpos($result, '[')!==false)
	{
		$result = replace_values($result);
	} else {
		$result = replace_link($result);
	}

  	if ($this->editor->editorAccess() && !$_SESSION['siteediteditor']) {
  		//var_dump($type, $name);
		$button = $this->editor->getBtn('edit_var','vars',$type.'_'.$name,'/admin/assets/icons/edit_text.gif','change',' data-toolbar="top" style="display:none;"', false, 'xs', 'info');
		$result = "<span data-editvar=\"{$type}_{$name}\">{$button}{$result}</span>";
  		/*if ($name=='sitetitle') {
			if ($result != '') {
				$result = str_replace('siteTitle"','siteTitle" data-dblclick="sitetitle" data-subject="vars"', $result);
			}
			else {
				$result = '<span data-dblclick="sitetitle" data-subject="vars">'.$this->editor->getTextLanguage($name)."</span>";
			}
		}
		elseif ($name='sitesubtitle') {
			if ($result != '') {
				$result = str_replace('siteSubTitle"','siteSubTitle" data-dblclick="sitesubtitle" data-subject="vars"', $result);
			}
			else {
				$result = '<span data-dblclick="sitesubtitle" data-subject="vars">'.$this->editor->getTextLanguage($name)."</span>";
			}
		}*/
  		/*if ($name == 'sitetitle' || $name == 'sitesubtitle' || $name=='title'){
  		//ob_start();
		//include include_once dirname(__FILE__)."/../editor/site_editor_".$name.".tpl";
		//$result = ob_get_contents() . $result;
		//ob_end_clean();
		  if ($result != '')
		  $result = '<span data-dblclick="">'.$result."</span>";
		  else $result = '<span id="'.$type.'_'.$name."\">".$this->editor->getTextLanguage($name)."</span>";
		} else {*/
		/*else {
			$result .= '<img style="cursor: pointer;" data-dblclick="'.$type.'" data-subject="vars" data-subject="'.$name.' src="/admin/assets/icons/edit_text.gif" title="'.$this->editor->getTextLanguage($name).'">';
		}*/
	} 
	return $result;
  } 

 // Методы для работы с редактором 
  public function editor(){

  }

  public function editorHeader(){
    if ($this->editor->editorAccess()){
	//include SE_CORE .'editor/header_editor.tpl';
	echo '<!-- EDITORMODE:'.$_SESSION['siteediteditor'].' -->';
    }
	else {
		echo '<!-- EDITORMODE:disabled -->';
	}
  }
  
  public function editorAccess(){
	return $this->editor->editorAccess($this->getPageName());
  }
  
  public function editItemRecord($section_id, $record_id){
      return $this->editor->editItemRecord($section_id, $record_id);
  }

  public function editorAddPhotos($section){
	return $this->editor->editorAddPhotos($section);
  }
  
  public function linkEditRecord($section_id, $record_id, $type){
      return $this->editor->linkEditRecord($section_id, $record_id, $type);
  }
  
  public function recordsWrapperStart($id){
    $this->editor->recordsWrapperStart($id);
  }

  public function recordsWrapperEnd(){
    $this->editor->recordsWrapperEnd();
  }

  public function linkAddRecord($section_id){
       return $this->editor->linkAddRecord($section_id);
  }

  public function groupWrapper($content_id, $text){
       return $this->editor->groupWrapper($content_id, $text);
  }
/*
  private function getParseMenu($text, $section) {
      while (preg_match("/<createmenu:item\-([\d]+)>(.+?)<\/createmenu>/umis", $text, $m)) {
        $text = str_replace($m[0], '<? list($templatemenu) = getItemMenu(\''.$m[1].'\'); echo parseTemplateMenu(\'base64:'.str_replace("'", "\'", base64_encode($m[2])).'\', $templatemenu); ?>', $text);
      }
	  return $text;
  }
*/
  private function getParseMenu($text, $section) {
    while (preg_match("/<createmenu:item\-([^>]+)>(.+?)<\/createmenu>/umis", $text, $m)) { 
        if (preg_match("/[\'\"]?\[param([^\]]+)\][\'\"]?/im", $m[1], $m1)) {
            $m[1] = str_replace($m1[0], '$section->parametrs->param' . $m1[1], $m[1]); 
        } else {
            $m[1] = '"'.$m[1].'"';
        }
        $text = str_replace($m[0], '<? if(function_exists(\'getItemMenu\')){ list($menuitems) = getItemMenu('.$m[1].'); $__data->setList($section,\'menuitems\', $menuitems);} ?>'.$m[2], $text);
    }
    return $text;
  }

  private function conditions($text) {
       while (preg_match("/\<if:\(\s?(.+?)\s?\)\>/im", $text, $m)
       || preg_match("/\<if:\s?([^\>]+)\s?\>/im", $text, $m)) {
           $m[1] = str_replace('[thispage.link]', '$__data->getLinkPageName()', $m[1]);
           $m[1] = str_replace('[arhiv.link]', '<?php echo seMultiDir()."/".$__data->getPageName()."/".$section->id."/arhiv/" ?>', $m[1]);
           $m[1] = str_replace('[thispage.name]', '$__data->getPageName()', $m[1]);

           while (preg_match("/\[sys\.isrequest.([^\]]+)\]/im", $m[1], $m1)) {
              $m[1] = str_replace($m1[0], "isRequest('".$m1[1]."')", $m[1]);
           }

           while (preg_match("/\[sys\.request\.([^\]]+)\]/im", $m[1], $m1)) {
              $m[1] = str_replace($m1[0], "getRequest('".$m1[1]."', 3)", $m[1]);
           }
           while (preg_match("/[\'\"]?\[params\.param([^\]]+)\][\'\"]?/im", $m[1], $m1)) {
              $m[1] = str_replace($m1[0], 'trim($section->parametrs->param' . $m1[1].')', $m[1]);
           }
           while (preg_match("/[\'\"]?\[param([^\]]+)\][\'\"]?/im", $m[1], $m1)) {
              $m[1] = str_replace($m1[0], 'trim($section->parametrs->param' . $m1[1].')', $m[1]);
           }
		   while (preg_match("/[\'\"]?\[%(site[\d\w]+)%\][\'\"]?/im", $m[1], $m1)) {
              $m[1] = str_replace($m1[0], 'strval($__data->prj->vars->' . $m1[1].')', $m[1]);
           }
           while (preg_match("/\[([^\.]+)\.([^\]]+)\](\.html)/im", $m[1], $mm)) {
                $m[1] = str_replace($mm[0], '$' .$mm[1].'->'.$mm[2].'.\''.$mm[3].'\'', $m[1]);
            }
           while (preg_match("/\[([^\.]+)\.([^\]]+)\]/im", $m[1], $mm)) {
               $m[1] = str_replace($mm[0], '$' .$mm[1].'->'.$mm[2], $m[1]);
           }



           $arr = array('{','}');
           $m[1] = str_replace($arr, '', $m[1]);
           $text = str_replace($m[0], "<?php if({$m[1]}): ?>", $text);
       }
       return $text;	   
  }  
  
  public function parseModule($tpl, $section) {
       $tpl = str_replace(array('<serv>','</serv>', '<SERV>', '</SERV>'), '', $tpl);
       $tpl = preg_replace("/(=[\"\'])([\w\d\-_\[\]\.]+)\.html/u", "$1".seMultiDir()."/$2/", $tpl);
       $tpl = preg_replace("/<se>(.+?)<\/se>/imus", "", $tpl);
       $tpl = preg_replace("/\[#\"(.+?)\"\]/imus", "$1", $tpl);
       $tpl = preg_replace("/\[se\.\"(.+?)\"\]/imus", "", $tpl);
       $tpl = str_replace(array('[contedit]'), '', $tpl);
       $tpl = str_replace('[menu.mainmenu]', '<?php echo fmainmenu(0) ?>', $tpl);
       $tpl = str_replace('[menu.mainhoriz]', '<?php echo fmainmenu(1) ?>', $tpl);
       $tpl = str_replace('[menu.mainvert]', '<?php echo fmainmenu(2) ?>', $tpl);
       $tpl = str_replace('[menu.pagemenu]', '<?php echo pageMenu() ?>', $tpl);
       while (preg_match("/\[menu.item-(\d{1,})\]/i", $tpl, $mm)){
           $tpl = str_replace("[menu.item-" . $mm[1] . "]", '<?php echo ItemsMenu(\''.$mm[1].'\') ?>', $tpl);
       }

       $tpl = preg_replace("/<wrapper>(.+?)<\/wrapper>/imus", 
         "<?php \$__data->recordsWrapperStart(\$section->id) ?>$1<?php \$__data->recordsWrapperEnd() ?>", $tpl);
       $tpl = preg_replace("/<arhiv:item>(.+?)<\/arhiv:item>/imus", 
         "<?php foreach(\$__data->limitObjects(\$section, \$section->objectcount) as \$record): ?>$1<?php endforeach; ?>", $tpl);


       $tpl = str_replace('[site.authorizeform]', '<?php echo replace_link(seAuthorize($__data->prj->vars->authorizeform)) ?>', $tpl);

       $tpl = str_replace(array('<SE>', '</SE>'), array('<se>','</se>'), $tpl);
       $tpl = str_replace(array('</noempty>','</empty>'), '<?php endif; ?>', $tpl);
       $tpl = str_replace('</if>', '<?php endif; ?>', $tpl);
       $tpl = str_replace(array('</else>', '<else>'), '<?php else: ?>', $tpl);


        while(preg_match("/\[\@subpage_?([\d\w]+)\]/",$tpl,$m)  || preg_match("/\[link\.subpage=([\d\w]+)\]/",$tpl,$m)) {
            $tpl = str_replace($m[0],'<?php echo seMultiDir().\'/\' . $__data->getPageName() . \'/\' . $section->id . \'/sub' . $m[1] . '/\' ?>',$tpl);
        }

		while(preg_match("/\[\%([\d\w]+)\%\]/",$tpl,$m)) {
            $tpl = str_replace($m[0],'<?php echo $__data->prj->vars->'.$m[1].' ?>',$tpl);
        }
	   $tpl = $this->getParseMenu($tpl, $section);
		$tpl = $this->conditions($tpl);

       $tpl = preg_replace("/<noempty:\[sys\.request\.([\w\d_]+)\]>/i", "<?php if(getRequest('$1', 3)): ?>", $tpl);
       $tpl = preg_replace("/<noempty:part\.([\w\d_]+)>/i", "<?php if(!empty(\$section->$1)): ?>", $tpl);
       $tpl = preg_replace("/<noempty:\[([\w\d_]+)\.([\w\d_]+)\]>/i", "<?php if(!empty(\$$1->$2)): ?>", $tpl);
       $tpl = preg_replace("/<noempty:\(\[([\w\d_]+)\.([\w\d_]+)\]\)>/i", "<?php if(!empty(\$$1->$2)): ?>", $tpl);
       $tpl = preg_replace("/<noempty:([\w\d_]+)\.([\w\d_]+)>/i", "<?php if(!empty(\$$1->$2)): ?>", $tpl);
       $tpl = preg_replace("/<noempty:[\(]?\{(.+?)\}[\)]?>/i", "<?php if(!empty($1)): ?>", $tpl);
       $tpl = preg_replace("/<noempty:\[lang([\d]+)\]>/i", "<?php if(!empty(\$section->language->lang$1)): ?>", $tpl);
       $tpl = preg_replace("/<noempty:\[param([\d]+)\]>/i", "<?php if(!empty(\$section->parametrs->param$1)): ?>", $tpl);
       $tpl = preg_replace("/<noempty:\[%site([\w\d]+)%\]>/i", "<?php if(!empty(\$__data->prj->vars->$1)): ?>", $tpl);

       $tpl = preg_replace("/<empty:\[sys\.request\.([\w\d_]+)\]>/i", "<?php if(!getRequest('$1', 3)): ?>", $tpl);
       $tpl = preg_replace("/<empty:part\.([\w\d_]+)>/i", "<?php if(empty(\$section->$1)): ?>", $tpl);
       $tpl = preg_replace("/<empty:\[([\w\d_]+)\.([\w\d_]+)\]>/i", "<?php if(empty(\$$1->$2)): ?>", $tpl);
       $tpl = preg_replace("/<empty:\(\[([\w\d_]+)\.([\w\d_]+)\]\)>/i", "<?php if(empty(\$$1->$2)): ?>", $tpl);
       $tpl = preg_replace("/<empty:([\w\d_]+)\.([\w\d_]+)>/i", "<?php if(empty(\$$1->$2)): ?>", $tpl);
       $tpl = preg_replace("/<empty:\[%site([\w\d]+)%\]>/i", "<?php if(empty(\$__data->prj->vars->$1)): ?>", $tpl);
       $tpl = preg_replace("/<empty:[\(]?\{(.+?)\}[\)]?>/i", "<?php if(empty($1)): ?>", $tpl);
       $tpl = preg_replace("/<empty:\[lang([\d]+)\]>/i", "<?php if(empty(\$section->language->lang$1)): ?>", $tpl);
       $tpl = preg_replace("/<empty:\[param([\d]+)\]>/i", "<?php if(empty(\$section->parametrs->param$1)): ?>", $tpl);


       while (preg_match("/<repeat:pages>(.+?)<\/repeat:pages>/imus", $tpl, $m))
       {

            $s1 = '<?php foreach($__data->getPages() as $map): ?><?php if(intval($map->indexes)): ?>';
            $m[1] = str_replace('[map.name]', '<?php echo $map["name"] ?>', $m[1]);
            $m[1] = str_replace('[map.title]', '<?php echo $map->title ?>', $m[1]);
            $m[1] = str_replace('[map.link]', '<?php echo seMultiDir()."/".$map["name"]."/" ?>', $m[1]);
            $m[1] = str_replace('[map.mapid]', '<?php echo "maplinks" . $map->level ?>', $m[1]);
            $s1 = $s1 . $m[1] . "\r\n" . '<?php endif; ?><?php endforeach; ?>';
            $tpl = str_replace($m[0], $s1, $tpl);
       }
       /*while (preg_match("/\[include_js(\([^\)]*\))?\]/imu", $tpl, $m))
       {
            $jsfile = 'modules/'.$section->type.'/'.$section->type.'.js';
            if (file_exists($jsfile) || file_exists('lib/'.$jsfile)){
                $s1 = "\r\n<script type=\"text/javascript\" src=\"[module_url]{$section->type}.js\"></script>";
                $s1 .= "\r\n<script type=\"text/javascript\"> {$section->type}_execute(";
                if (!empty($m[1])) $s1 .= utf8_substr($m[1], 1, -1);
                $s1 .= ');</script>';
            } else {
                $s1 = "\r\n<script type=\"text/javascript\" src=\"[module_url]engine.js\"></script>";
            }
            $tpl = str_replace($m[0], $s1, $tpl);
       }
	   $tpl = str_replace('[include_css]', '<link href="[module_url]css/style.css" rel="stylesheet" type="text/css">', $tpl);*/
       //$tpl = str_replace('[include_js]', '<script type="text/javascript" src="[module_url]engine.js"></script>', $tpl);
       $tpl = preg_replace("/<repeat:records>(.+?)<\/repeat:records>/imus", 
         "<?php foreach(\$__data->limitObjects(\$section, \$section->objectcount) as \$record): ?>\n$1\n<?php endforeach; ?>", $tpl);
       $tpl = preg_replace("/<repeat:records\|desc>(.+?)<\/repeat:records>/imus", 
         "<?php foreach(\$__data->limitObjects(\$section, \$section->objectcount, 1) as \$record): ?>\n$1\n<?php endforeach; ?>", $tpl);

       while (preg_match("/<repeat:\[([\w\d]+)\.([\w\d]+)\]([^\>]+)?>/imus", $tpl, $m)){
           $s1 = 'record';
           if (count($m == 4) && trim($m[3])) list(,$s1) = explode('=', $m[3]);
           $tpl = str_replace($m[0], '<?php foreach($'.$m[1].'->'.$m[2].' as $'.$s1.'): ?>', $tpl);
       }


       while (preg_match("/<repeat:([\d\w]+)\[([\w\d]+)\.([\w\d]+)\]([^\>]+)?>/imus", $tpl, $m)){
          $s1 = 'record';
          if (count($m == 5) && trim($m[4])) list(,$s1) = explode('=', $m[4]);
          $tpl = str_replace($m[0], '<?php $__list = \''.$m[1].'\'.$'.$m[2].'->'.$m[3].';
          foreach($section->$__list as $'.$s1.'): ?>', $tpl);
       }

       while (preg_match("/\<repeat:([^\>]+)\>(.+?)\<\/repeat:([^\>]+)\>/umis", $tpl, $m)){
           list($s1,) = explode(' ', $m[1]);
           if (strpos($m[1], ' name=') !== false){
                list(,$s2) = explode(' name=', $m[1]);
                $s2 = trim($s2);
           } else {
                $s2 = 'record';
           }
           if (empty($s2)) $s2 = 'record';
           if ($s1 == 'records') $s1 = 'objects';

           if (strpos($m[2], '<if:record.text1')!==false){
                $s3 = '<?php list($record->text1)=explode("|",$record->text1) ?>'.$m[2]."\n<?php endforeach; ?>";
           } else $s3 = $m[2] . "\n<?php endforeach; ?>";
           $tpl = str_replace($m[0], '<?php foreach($section->'.$s1.' as $'.$s2.'): ?>'.$s3, $tpl);
       }
       $tpl = preg_replace("/\<\/repeat:(.+?)\>/m", '<?php endforeach; ?>', $tpl);


       $tpl = preg_replace("/\[subpage\sname=([\w\d]+)\]/m", 
            "<?php if(file_exists(\$__MDL_ROOT.\"/php/subpage_$1.php\")) 
            include \$__MDL_ROOT.\"/php/subpage_$1.php\"; 
            if(file_exists(\$__MDL_ROOT.\"/tpl/subpage_$1.tpl\")) include \$__data->include_tpl(\$section, \"subpage_$1\"); ?>", 
            $tpl);

       $tpl = preg_replace("/\[subpage\slink=([\w\d]+)\]/m", 
            "<?php echo \$__data->link_tpl(\$section, \"subpage_$1\"); ?>", 
            $tpl);

       while (preg_match("/\[textline\.(.+?)\/textline\]/m", $tpl, $m)) {
          $s1 = '<?php $noteitem = explode("\n", str_replace("\n\n","\n", 
            trim(str_replace(array("<br>","<br />","<p>","</p>"),array("\n","\n","","\n"),str_replace("\r", "", $record->note))))); ?>'."\r\n";
          $s1 .= '<?php foreach($noteitem as $num=>$noteline): ?>' . "\r\n";
          $m[1] = str_replace('%SELECTED%', '<?php if(strpos($noteline, "*")!==false) echo "selected"; ?>', $m[1]);
          $m[1] = str_replace('%CHECKED%', '<?php if(strpos($noteline, "*")!==false) echo "checked"; ?>', $m[1]);
          $m[1] = str_replace('@textlineval', '<?php list(,$noteline_) = explode("%%", trim($noteline)); 
           if (empty($noteline_)) $noteline_ =  (strip_tags($noteline)); echo str_replace("*", "", htmlspecialchars($noteline_)) ?>', $m[1]);
          $m[1] = str_replace('@textline_num', '<?php echo str_replace("*", "", $num+1) ?>', $m[1]);
          $m[1] = str_replace('@textline', '<?php list($noteline_) = explode("%%", $noteline); echo str_replace("*", "", $noteline_) ?>', $m[1]);
          $s1 .= $m[1] . "\r\n<?php endforeach; ?>";
          $tpl = str_replace($m[0], $s1, $tpl);
       }

       $tpl = str_replace('[thispage.link]', '<?php echo $__data->getLinkPageName() ?>', $tpl);
       $tpl = str_replace('[thispage.name]', '<?php echo $__data->getPageName() ?>', $tpl);
       $tpl = str_replace('[arhiv.link]', '<?php echo seMultiDir()."/".$__data->getPageName()."/".$section->id."/arhiv/" ?>', $tpl);
       $tpl = preg_replace("/\[part\.([^\]]*)\]/m", "<?php echo \$section->$1 ?>", $tpl);
       $tpl = preg_replace("/\[sys\.request\.([\w\d_]+)\]/i", "<?php echo getRequest('$1', 3) ?>", $tpl);
       $tpl = preg_replace("/\[params\.param([\d]+)\]/i", "<?php echo \$section->parametrs->param$1 ?>", $tpl);
       $tpl = preg_replace("/\[param([\d]+)\]/i", "<?php echo \$section->parametrs->param$1 ?>", $tpl);
       $tpl = preg_replace("/\[site\.(copyright|sitetitle|sitesubtitle|sitephone|siteemail|siteaddr|sitepostcode|siteregion|sitelocality)\]/im", "<?php echo \$__data->prj->vars->$1 ?>", $tpl);
       $tpl = preg_replace("/\[([\w\d_]+)\.([\w\d_]+)\]/im", "<?php echo \$$1->$2 ?>", $tpl);
       $tpl = str_replace(array('<serv>','</serv>','[*addobj]', '[*edobj]'), '', $tpl);
       $tpl = str_replace('[SE_PARTSELECTOR]',
              '<?php echo SE_PARTSELECTOR($section->id,count($section->objects),
               $section->objectcount, getRequest("item",1), getRequest("sel",1)) ?>', $tpl);


       $tpl = str_replace('[objedit]', '<?php echo $__data->editItemRecord($section->id, $record->id) ?>', $tpl);
       $tpl = str_replace('[editrecord]', '<?php echo $__data->linkEditRecord($section->id, $record->id, "") ?>', $tpl);
       $tpl = str_replace('[addrecord]', '<?php echo $__data->linkAddRecord($section->id) ?>', $tpl);
       $tpl = str_replace('[addphotos]', '<?php if(method_exists($__data, "editorAddPhotos")) echo $__data->editorAddPhotos($section); ?>', $tpl);
       $tpl = str_replace('[editrecord_title]', '<?php echo $__data->linkEditRecord($section->id, $record->id, "Title") ?>', $tpl);
       $tpl = str_replace('[editrecord_image_prev]', '<?php echo $__data->linkEditRecord($section->id, $record->id, "PImage") ?>', $tpl);
       $tpl = str_replace('[editrecord_image]', '<?php echo $__data->linkEditRecord($section->id, $record->id, "Image") ?>', $tpl);
       $tpl = str_replace('[editrecord_note]', '<?php echo $__data->linkEditRecord($section->id, $record->id, "Note") ?>', $tpl);
       $tpl = str_replace('[editrecord_text]', '<?php echo $__data->linkEditRecord($section->id, $record->id, "Text") ?>', $tpl);

       $tpl = preg_replace("/\[lang([\d]+)\]/m", "<?php echo \$section->language->lang$1 ?>", $tpl);
       $tpl = preg_replace("/\[([\$][^\]]+)\]/m", "<?php echo $1 ?>", $tpl);
       $tpl = preg_replace("/\{([\$][^\}]+)\}/m", "<?php echo $1 ?>", $tpl);
       $tpl = preg_replace("/\[\$([^\}]+)\]/m", "<?php echo $1 ?>", $tpl);

       return $tpl;
  }
  
}
?>