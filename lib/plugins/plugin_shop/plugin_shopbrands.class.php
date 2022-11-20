<?php

class plugin_shopbrands {
	private static $instance = null;
	
	private $cache_dir = '';
	private $brands = array();
	private $count = 0;

    public function __construct() {
		$this->cache_dir = SE_SAFE . 'projects/' . SE_DIR . 'cache/shop/brands/';
		$this->cache_brands = $this->cache_dir . 'brands.json';
		$this->cache_count = $this->cache_dir . 'count.txt';
		$this->id_main = 1;
		
		if (!is_dir($this->cache_dir)) {      
			if (!is_dir(SE_SAFE . 'projects/' . SE_DIR . 'cache/'))
				mkdir(SE_SAFE . 'projects/' . SE_DIR . 'cache/');
			if (!is_dir(SE_SAFE . 'projects/' . SE_DIR . 'cache/shop/'))
				mkdir(SE_SAFE . 'projects/' . SE_DIR . 'cache/shop/');
			mkdir($this->cache_dir);				
		}
		
		$this->checkCache();
    }
	
	private function checkCache() {
		$result = se_db_query('SELECT COUNT(*) AS cnt, UNIX_TIMESTAMP(GREATEST(MAX(sg.updated_at), MAX(sg.created_at))) AS time FROM shop_brand sg');
		
		list($this->count, $time) = se_db_fetch_row($result);
		
		$cache_count = file_exists($this->cache_count) ? (int)file_get_contents($this->cache_count) : -1;
		
		$time = max(filemtime(__FILE__), $time);
		
		if (!file_exists($this->cache_brands) || filemtime($this->cache_brands) < $time || $cache_count != $this->count) {
			$shop_brand = new seTable('shop_brand');
			$shop_brand->select('id, name, code, image, text, title, keywords, description');
			$list = $shop_brand->getList();
			
			if (!empty($list)) {
				foreach ($list as $val) {
					$this->brands[$val['id']] = $val;
				}
			}
			
			$this->saveCache();
		}
		else {
			$this->brands = json_decode(file_get_contents($this->cache_brands), 1);
		}
	}
	
	private function saveCache() {
		$file = fopen($this->cache_brands, "w+");
		$result = fwrite($file, json_encode($this->brands));
		fclose($file);
		
		$file = fopen($this->cache_count, "w+");
		$result = fwrite($file, $this->count);
		fclose($file);	
	}
	
	public function getBrand($id) {
		if (empty($this->brands[$id])) return;
		$brand = $this->brands[$id];
		return $brand;
	}
	
	public function getListBrands() {
		return $this->brands;
	}
	
	public static function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}	
}