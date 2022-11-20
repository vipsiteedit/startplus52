<?php

/** -------------------------------------------------------------- //
* –абота с новост€ми
* @param string $subject	-	тема_письма
**/ 
class plugin_news {

	private $newskod = array();
	private $cache_dir;
	private $cache_sections;
	private static $instance = null;
	private static $sections = array();
	private static $groups = array();
	private $lang = 'rus';
	private static $options = array();
	private $count = 0;

	
    public function __construct($opt = array())
    {
		$this->cache_dir = SE_SAFE . 'projects/' . SE_DIR . 'cache/news/'.$opt['lang'].'/';
		$this->cache_sections = $this->cache_dir . 'news.json';
		$this->cache_count = $this->cache_dir . 'count.json';
		$this->cache_group = $this->cache_dir . 'groups.json';
		if (!is_dir($this->cache_dir)) {      
			if (!is_dir(SE_SAFE . 'projects/' . SE_DIR . 'cache/'))
				mkdir(SE_SAFE . 'projects/' . SE_DIR . 'cache/');
			if (!is_dir(SE_SAFE . 'projects/' . SE_DIR . 'cache/news/'))
				mkdir(SE_SAFE . 'projects/' . SE_DIR . 'cache/news/');
			mkdir($this->cache_dir);				
		}
		$this->checkCache();
    }

	private function checkCache() {
		$sql_cache = "SELECT
			  'news' AS type,
			  COUNT(*) AS cnt,
			  UNIX_TIMESTAMP(GREATEST(MAX(ifnull(ss.updated_at, 0)), MAX(ss.created_at))) AS time
			FROM news ss
			UNION ALL
			SELECT
			  'news_category',
			  COUNT(*),
			  UNIX_TIMESTAMP(GREATEST(MAX(ifnull(ssi.updated_at, 0)), MAX(ssi.created_at)))
			FROM news_category ssi
			UNION ALL
			SELECT
			  'news_img',
			  COUNT(*),
			  UNIX_TIMESTAMP(GREATEST(MAX(ifnull(ssp.updated_at, 0)), MAX(ssp.created_at)))
			FROM news_img ssp";
			
		$result = se_db_query($sql_cache);
		
		$cache_count = file_exists($this->cache_count) ? (int)file_get_contents($this->cache_count) : -1;
		
		$update_time = 0;


		if (!empty($result)) {
			while ($line = se_db_fetch_assoc($result)) {
				$this->count += $line['cnt'];
				$update_time = max($update_time, $line['time']);
			}
		}
		
		$update_time = max(filemtime(__FILE__), $update_time);
		
		if (!file_exists($this->cache_sections) || filemtime($this->cache_sections) < $update_time || $cache_count != $this->count) {
			$this->parseNewsFromDB();
		}
		else {
			$this->parseNewsFromCache();
		}	
	}

	public function isModerator() {
	    return (seUserGroup() > 2 || (seUserRole(self::$options['moderator_group']) && seUserGroup() > 0));
	}
	
	private function parseNewsFromDB() {
		$news = new seTable('news', 'n');
		$news->select("nc.ident, nc.lang, `n`.`id_category`, `n`.`id`, `text`, `n`.img, `n`.title, `n`.`news_date`, n.pub_date");
		$news->innerjoin("news_category nc", "`n`.id_category = `nc`.id");
		$news->Where("nc.lang = '?'", self::$options['lang']);
		$news->andWhere("n.active = 'Y'");
		$news->orderBy('news_date', 1);
		$newslist = $news->getList();
		echo se_db_error();
		
		foreach($newslist as $val) {
		    $val['news_date'] = date('Y-m-d H:i:s', $val['news_date']);
		    $val['pub_date'] = date('Y-m-d H:i:s', $val['pub_date']);
			self::$sections[] = $val;
			self::$groups[$val['ident']][] = $val['id'];
		}
		$this->saveCache();
	}	
	
	private function parseNewsFromCache() {
		self::$sections = json_decode(file_get_contents($this->cache_sections), 1);
	}

	private function saveCache() {
		file_put_contents($this->cache_sections, json_encode(self::$sections));
		file_put_contents($this->cache_group, json_encode(self::$groups));
		$this->writeLog($this->cache_sections . ' - ' . $result);
		file_put_contents($this->cache_count, $this->count);
	}	

	public static function getInstance($opt = array()) {
		if (empty($opt['size_image'])) $opt['size_image'] = '200x200';
		if (empty($opt['lang'])) $opt['lang'] = 'rus';
		self::$options = $opt;
/*
		$this->cache_dir = SE_SAFE . 'projects/' . SE_DIR . 'cache/news/'.$opt['lang'].'/';
		$this->cache_sections = $this->cache_dir . 'news.json';
		$this->cache_count = $this->cache_dir . 'count.json';
		$this->cache_group = $this->cache_dir . 'groups.json';*/

		
		if (is_null(self::$instance)) {
			self::$instance = new self($opt);
		}
		return self::$instance;
	}

	private function writeLog($text) {
		$file_log = fopen($this->cache_dir . 'news.log', 'a+');
		fwrite($file_log, date('[Y-m-d H:i:s] ') . $text . "\r\n");
		fclose($file_log);
	}
	private function getRealImgPath(){
	    return '/images/'.self::$options['lang'] .'/newsimg/';
	}
	
	private function getPictimage($image, $size, $res = 'm') {
	    if (strpos($image, 'http://') !== false || strpos($image, 'https://') !== false) {
		    return $image;
		}
		if (!empty($CONFIG['DBLink']) && !empty($image)){
		    return 'http://'.$CONFIG['DBLink'].$img_name;
		}
	    $img_name = $this->getRealImgPath() . $image;
		if ($size == 0) {
		    return $img_name;
		}
		if (!empty($image) && file_exists(getcwd() . $img_name)){
		    return se_getDImage($img_name, $size, $res);
		} else {
		    return '';
		}
	}
	
	public function getItem($id) {
		$news = new seTable('news', 'n');
		$news->select("id, title, short_txt, text, img, active, pub_date, news_date");
		$val = $news->find($id);
		$val['news_date'] = date('Y-m-d H:i:s', $val['news_date']);
		$val['pub_date'] = date('Y-m-d H:i:s', $val['pub_date']);
		$val['image'] = $this->getPictimage($val['img'], self::$options['size_fullimage'], 'm');
		$newsimg = new seTable('news_img', 'ni');
		$newsimg->select();
		$newsimg->where('id_news=?', $id);
		$imglist = $newsimg->getList();
		$val['imagelist'] = $imglist;
		return $val;
	}
	
	public function edit($id){
		$result = array();
	    if (!$this->isModerator()) {
            $result['status'] = 'error';
			$result['errorcode'] = 'No access';
			return $result;
		}
	if (is_uploaded_file($_FILES['userfile']['tmp_name'])){                     
        $userfile = $_FILES['userfile']['tmp_name'];
        $userfile_size = $_FILES['userfile']['size'];
        $user = mb_strtolower(htmlspecialchars($_FILES['userfile']['name'], ENT_QUOTES), 'UTF-8');
//ѕровер€ем, что загруженный файл - картинка
        $sz = GetImageSize($userfile);
        if (preg_match("/([^.]+)\.(gif|jpeg|jpg|png)$/u", $user, $m) && (($sz[2] == 1) || ($sz[2] == 2) || ($sz[2] == 3))) {
            $extendfile = $m[2];
        } else {
            $result['status'] = 'error';
			$result['errorcode'] = 'It is not an image';
			return $result;
        }                  
//≈сли размер файла больше заданного
        if ($userfile_size > 10240000) {
            $result['status'] = 'error';
			$result['errorcode'] = 'Large file size';
			return $result;
        }
        $file = true;  
    } //конец обработки картинки
    //  если была ошибка - нет одного из полей

        $date = getRequest('date');
        $time = strtotime($date);
        $title = getRequest('title', 4);
        $newstext = trim($text);//getRequest('text', 3); 
//$imgname  = 'news'.time();
        $imgname = 'news' . $time;
// если картинка есть
        if ($file) {  
            $uploadfile = getcwd() . $IMAGE_DIR . $imgname . ".".$extendfile;
            move_uploaded_file($userfile, $uploadfile);
            ThumbCreate($uploadfile, $uploadfile, '', $width);
        }
		// опубликовать сегодн€
        $checkbox = ((getRequest('publics', 3)=='on')? 0:$time); 
        $news = new seTable('news', 'n');
		if (empty($id)) {
			//при добавлении
            $cat_name = "[param20]";
            $newscat = new seTable('news_category', 'nc');
            $newscat->where("nc.ident = '$cat_name'");
            $newscat->andWhere("nc.lang = '$lang'");
            $newscat->fetchOne();
            $id_cat = $newscat->id;
            if (!$id_cat) {
                $newscat->ident = $cat_name;
                $newscat->title = $cat_name;
                $newscat->lang = $lang;
                $id_cat = $newscat->save();
            }                
            $news->insert();
            $news->id_category = $id_cat;
        } else {
            $news->find($id);  //при редактировании
        }
        $news->news_date = $time;
        $news->pub_date = $checkbox;
        $news->title = $title;
        $news->text = $newstext;
        $news->img = $filename;
        if ($id_save =  $news->save()) {
		    $id = (!$id) ? $id_save : $id;
			$result['status'] = 'success';
			$result['result'] = $id;
		} else {
			$result['status'] = 'error';
			$result['errorcode'] = 'An error in the base record';
		}
		return $result;
	}

	public function delete($id){
		$result = array();
	    if (!$this->isModerator()) {
            $result['status'] = 'error';
			$result['errorcode'] = 'No access';
			return $result;
		}
        $news = new seTable('news', 'n');
		$news->find($id); 
		$filename = $news->img;
		if (!empty($filename)) { 
			$temp = explode(".", $filename);
			$filename = getcwd() . $IMAGE_DIR . $filename;
			if (file_exists($filename)) {
				@unlink($filename); 
			}
		}
        if ($news->delete($id)) {
			$result['status'] = 'success';
			$result['result'] = $id;
		} else {
			$result['status'] = 'error';
			$result['errorcode'] = 'An error in the base record';
		}
		return $result;
	}
	
	public function getItems($code = '', $offset = 0, $limit = 30) {
	    
		$items = array();
		$codearr = false;
		if (strpos($code, ',')===false) {
		    $codearr = array($code);
		} elseif(!empty($code)) {
			$codearr = explode(',', $code);
		}
		$ff_id = 0;
		foreach(self::$sections as $item) {
		    if (empty($codearr) || in_array($item['ident'], $codearr)){
			if (strtotime($item['pub_date']) < time()) {
					$ff_id ++;
				    if ($offset > $ff_id - 1) continue;
					$item['image_prev'] = $this->getPictimage($item['img'], self::$options['size_image'], 'm');
					//$item['image'] = ($item['img']) ? $this->getRealImgPath() . $item['img'] : '';
					unset($item['img']);
					$items[] = $item;
					if (count($items) >= $limit) break;
				}
			}
		}
		return $items;
	}
}

?>