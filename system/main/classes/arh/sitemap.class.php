<?php

class siteMap {
//function 

    private $countpos = 0;
    private $maxcount = 30000;
    private $arr = array();
    private $name = 'sitemap';

    private function getShopGroups($shopcatgr){
         $shgroup = new seShopGroup();
         $shgroup->select('id');
         $shgroup->where("code_gr='?'", $shopcatgr);
	 $shgroup->fetchOne();
	 return $this->getTreeShopGroup($shgroup->id);
    }

    private function getTreeShopGroup($shopcatgr){
         $list = array();
         $shgroup = new seShopGroup();
         $shgroup->select('id');
         $shgroup->where('upid=?', $shopcatgr);
         $glist = $shgroup->getList();    
         foreach($glist as $item){
            if ($item['id'])
        	$list = array_merge($list, $this->getTreeShopGroup($item['id']));
            $list[] = $item['id'];     
         }
         return $list;
    }

    private function auto_priority($page)
    {
	if ($page->priority_man != 1){
	    if ($page['name'] == 'home') return 10;
	    if ($page['name'] == 'maps') return 9.9;
	    if ($page->level == 1) return 9;
	    if ($page->level == 2) return 8.5;
	    if ($page->level > 2) return 8;
	    return 6;
	
	} else return $page->priority;

    }

    private function changefreq($time)
    {
	$changefreq = array('always','hourly','daily','weekly','monthly','yearly','never');
	$delta = ((time() - $time)/(3600 * 24));
	if ($delta * 24 < 1) $ch = 0;
	elseif ($delta < 1) $ch = 1;
	elseif ($delta <= 3) $ch = 2;
	elseif ($delta <= 7) $ch = 3;
	elseif ($delta <= 180) $ch = 4;
	elseif ($delta <= 360) $ch = 5;
	else $ch = 6;
	return $changefreq[$ch];
    }

    private function map_page($dir, $name, $folder, $lang = 'rus', $domain, $prior){
    	    $new = '';
    	    $page_data = simplexml_load_file($dir .  "pages/{$name}.xml");
    	    $date_mod = filemtime($dir . "pages/". $name . ".xml");
    	    if ($name == 'home') return;

	    if ($prior > 1) $prior --; 
	    $prior = str_replace(',','.',($prior / 10)); 

	    foreach($page_data->sections as $section){
	        // Обработка новостей
		    if (!empty($section->objects)){
			foreach($section->objects as $object){
			  if (strval($object->text) == '') continue;
			    $loc = $domain . '/'. $folder.$name."/{$section->id}/" . $object['name'];
	    		    $lastmod = date ("c", $date_mod);       
	    		    $changefreq = $this->changefreq($date_mod);
	    		    $priority = $prior;
			    $this->arr[] = array('loc'=>$loc, 'lastmod'=>$lastmod, 'changefreq'=>$changefreq,'priority'=>$priority);
	    		    if (count($this->arr) >= $this->maxcount){
	    			$this->countpos++;
	    			$this->setMap(SE_DIR.$this->name.$this->countpos, $this->arr);
	    			unset($this->arr);
	    			$this->arr = array();
	    		    }
			}
		    }

	    	    if ($section->type == 'monlinenews'){

	    	    }
	        if (SE_DB_ENABLE){
	        // Обработка групп товаров
	    	    if (strpos($section->type,'mshop_groups')!==false){
	    		$shopprice = new seShopGroup();
	    		$shopprice->select('code_gr, updated_at, created_at');
	    		$shopprice->where("active='Y'");
	    		$shopprice->andwhere("lang='?'", $lang);
	    		$ident = $section->parametrs->param27;
	    		if ($ident){
			    $groups = $this->getShopGroups($ident);
	    		    $shopprice->andwhere("id IN (?)", join(',', $groups));
	    		}
	    		$shoplist = $shopprice->getList();
	    		//echo $shopprice->getSql();
	    		foreach($shoplist as $code){    
			    $loc = $domain . '/'. $folder.$name."/cat/" . $code['code_gr'];
			    if (strtotime($code['updated_at']) < 1000) $code['updated_at'] = $code['created_at'];
			    if (strtotime($code['updated_at']) < 1000) $code['updated_at'] = date('Y-m-d H:i:s');
	    		    $lastmod = date ("c", strtotime($code['updated_at']));
	    		    $changefreq = $this->changefreq(strtotime($code['updated_at']));
	    		    $priority = $prior;
			    $this->arr[] = array('loc'=>$loc, 'lastmod'=>$lastmod, 'changefreq'=>$changefreq,'priority'=>$priority);
	    		    if (count($this->arr) >= $this->maxcount){
	    			$this->countpos++;
	    			$this->setMap(SE_DIR.$this->name.$this->countpos, $this->arr);
	    			unset($this->arr);
	    			$this->arr = array();
	    		    }
	    		}
	    	    }



	        // Обработка каталога магазина
	    	    if (strpos($section->type,'mshop_vitrine')!==false){
	    		$shopprice = new seShopPrice();
	    		$shopprice->select('code, updated_at, created_at');
	    		$shopprice->where("enabled='Y'");
	    		$shopprice->andwhere("lang='?'", $lang);
	    		$ident = $section->parametrs->param273;
	    		if ($ident){
			    $groups = $this->getShopGroups($ident);
	    		    $shopprice->andwhere("id_group IN (?)", join(',', $groups));
	    		}
	    		$shoplist = $shopprice->getList();
	    		//echo $shopprice->getSql();
	    		foreach($shoplist as $code){
			    $loc = $domain . '/'.$folder.$name."/show/" . $code['code'];
			    if (strtotime($code['updated_at']) < 1000) $code['updated_at'] = $code['created_at'];
			    if (strtotime($code['updated_at']) < 1000) $code['updated_at'] = date('Y-m-d H:i:s');
	    		    $lastmod = date ("c", strtotime($code['updated_at']));
	    		    $changefreq = $this->changefreq(strtotime($code['updated_at']));
	    		    $priority = $prior;
			    $this->arr[] = array('loc'=>$loc, 'lastmod'=>$lastmod, 'changefreq'=>$changefreq,'priority'=>$priority);
	    		    if (count($this->arr) >= $this->maxcount){
	    			$this->countpos++;
	    			$this->setMap(SE_DIR.$this->name.$this->countpos, $this->arr);
	    			unset($this->arr);
	    			$this->arr = array();
	    		    }
	    		}
	    	    }
	    	}
	}

    }

    private function setMap($name, $maplist){
	$new  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	$new .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"\n";
	$new .= "xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"\n";
	$new .= "xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9\n";
	$new .= "http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\">\n";
	foreach($maplist as $item){
		$new .= "\t<url>\n";
		$new .= "\t\t<loc>". $item['loc']."/</loc>\n";
		$new .= "\t\t<lastmod>". $item['lastmod']."</lastmod>\n";       
	    	$new .= "\t\t<changefreq>".$item['changefreq']."</changefreq>\n";
		$new .= "\t\t<priority>" . $item['priority'] . "</priority>\n";
		$new .= "\t</url>\n";
	}
	$new .= "</urlset>\n";
	$file = fopen($name.'.xml',"w+");
	fwrite($file, $new);
	fclose($file);
    }

    public function execute(){
	$lang = se_getlang();
	$name = "sitemap.xml";
	$up = 0;
	$count = 0;
	$dir = getcwd() . "/projects/" . SE_DIR;
	$prj = simplexml_load_file($dir .  "project.xml");
	$domain = strval($prj->sitedomain);
	if (empty($domain)) {
	    $langs = @file("hostname.dat");
	    $defsite = trim(join('', file("sitelang.dat")));

    	    $lng = array();
    	    foreach($langs as $lang){
		$ll = explode("\t", $lang);
    		if ($ll[1] == str_replace('/','',SE_DIR)){
    		    $domain = $ll[0];
		    $extdomain = true;
    		    break;
		}
	    }
	    if (empty($domain)) {
		$domain = $_SERVER['HTTP_HOST'];
		$extdomain = false;
		if (strpos($domain,'www.')!==false) list(,$domain) = explode('www.', $domain);
		$domain = 'http://www.'. $domain;
	    }
	}
	if (strpos($domain,'://')===false)
	    $domain = 'http://'. $domain;
	if (strval($prj->language) == '') $prj->language = 'rus';
	$pages = simplexml_load_file($dir .  "pages.xml");

	$folder = seMultiDir();
	if ($folder == $defsite . '/' || $extdomain) $folder = '';
	$arr = array();
	$new = '';
	foreach($pages as $page){
		if ($page->indexes == 1){
			$new .= "\t<url>\n";
			$loc = $domain . "/" . $folder . $page["name"];
			$lastmod = date ("c",filemtime($dir . "pages/". $page["name"] . ".xml"));
	    		$changefreq = $this->changefreq(filemtime($dir . "pages/". $page["name"] . ".xml"));
			$priority = str_replace(",", ".", $this->auto_priority($page)/10);
			$this->arr[] = array('loc'=>$loc, 'lastmod'=>$lastmod, 'changefreq'=>$changefreq,'priority'=>$priority);
			$this->map_page($dir, $page['name'], $folder, $prj->language, $domain, $this->auto_priority($page));

	    		if (count($this->arr) >= $this->maxcount){
	    		    $this->countpos++;
	    		    $this->setMap(SE_DIR .$this->name.$this->countpos, $this->arr);
	    		    unset($this->arr);
	    		    $this->arr = array();
	    		}
		    }
	}
	if ($this->countpos){
	    $new  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	    $new .= "<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
	    for ($i = 1; $i <= $this->countpos; $i++){
		$new .= "<sitemap>";
		$new .= "\t<loc>".$domain."/" . $folder . $this->name .$i .".xml</loc>\n'";
		$new .= "\t\t<lastmod>". date ("c", filemtime(SE_DIR .$name.$i.'.xml'))."</lastmod>\n";
		$new .= "</sitemap>\n";
	    }
	    $new .= "</sitemapindex>\n";
	    $file = fopen(SE_DIR . $this->name.'.xml',"w+");
	    fwrite($file, $new);
	    fclose($file);
	} elseif (count($this->arr)) {
	//echo SE_DIR .$this->name;
	//exit;
	    $this->setMap(SE_DIR .$this->name, $this->arr);
	}
	
    }
}
?>