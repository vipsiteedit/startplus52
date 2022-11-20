<?php

class plugin_shopgroups {
	private static $instance = null;
	
	private $cache_dir = '';
	private $cache_groups = '';
	private $cache_tree = '';
	private $cache_count = '';
	private $groups = array();
	private $tree = array();
	private $count = 0;
	

    public function __construct() {
		$this->cache_dir = SE_SAFE . 'projects/' . SE_DIR . 'cache/shop/groups/';
		$this->cache_groups = $this->cache_dir . 'groups.json';
		$this->cache_tree = $this->cache_dir . 'tree.json';
		$this->cache_count = $this->cache_dir . 'count.txt';
		$this->id_main = 1;
		
		if (!is_dir($this->cache_dir)) {      
			if (!is_dir(SE_SAFE . 'projects/' . SE_DIR . 'cache/'))
				mkdir(SE_SAFE . 'projects/' . SE_DIR . 'cache/');
			if (!is_dir(SE_SAFE . 'projects/' . SE_DIR . 'cache/shop/'))
				mkdir(SE_SAFE . 'projects/' . SE_DIR . 'cache/shop/');
			mkdir($this->cache_dir);				
		}

		if (!$this->cacheActual()/*!file_exists($this->cache_groups) || filemtime($this->cache_groups) < $this->lastTimeModify()*/) {
			$this->parseGroupsFromDB();
		}
		else {
			$this->parseGroupsFromCache();
		}
			
		//print_r($this->groups);		
    }
	
	private function parseGroupsFromDB() {
		$this->tree = $this->parseTreeGroups();
		$this->checkCount($this->tree);
		$this->saveCache();
	}
	
	private function parseGroupsFromCache() {
		$this->groups = json_decode(file_get_contents($this->cache_groups), 1);
		$this->tree = json_decode(file_get_contents($this->cache_tree), 1);
	}
	
	private function checkCount(&$tree = array()) {
		foreach ($tree as &$val) {
			$val['count'] = $this->groups[$val['id']]['count'];
			if (!empty($val['menu']))
				$this->checkCount($val['menu']);
		}
	}
	
	private function saveCache() {
		$file = fopen($this->cache_groups, "w+");
		$result = fwrite($file, json_encode($this->groups));
		fclose($file);
		
		$file_log = fopen($this->cache_dir . 'groups.log', 'a+');
		fwrite($file_log, date('[Y-m-d H:i:s] ') . $this->cache_groups . ' - ' . $result . "\r\n");
		
		$file = fopen($this->cache_tree, "w+");
		$result = fwrite($file, json_encode($this->tree));
		fclose($file);
		
		fwrite($file_log, date('[Y-m-d H:i:s] ') . $this->cache_tree . ' - ' . $result . "\r\n");
		fclose($file_log);
		
		$file = fopen($this->cache_count, "w+");
		$result = fwrite($file, $this->count);
		fclose($file);
		
	}
	
	/*
	private function lastTimeModify() {
		$result = se_db_query('SELECT UNIX_TIMESTAMP(GREATEST(MAX(sg.updated_at), MAX(sg.created_at))) AS time FROM shop_group sg');
		$time = se_db_fetch_row($result);
		return $time[0];
	} 
	*/
	
	private function cacheActual() {		
		$result = se_db_query('SELECT COUNT(*) AS count, UNIX_TIMESTAMP(GREATEST(MAX(sg.updated_at), MAX(sg.created_at))) AS time FROM shop_group sg');
		
		list($this->count, $time) = se_db_fetch_row($result);
		
		$cache_count = file_exists($this->cache_count) ? (int)file_get_contents($this->cache_count) : -1;
		
		$time = max(filemtime(__FILE__), $time);
		
		if (!file_exists($this->cache_groups) || !file_exists($this->cache_tree) || filemtime($this->cache_groups) < $time || $cache_count != $this->count) {
			return false;
		}
		else {
			return true;
		}
	}
	
	public function getTree($code = '') {
		$id = $this->getId($code);
		if (!empty($this->groups[$id])) {
			$parents = $this->getParentsId($id, true);
			if (!empty($parents)) {
				$tree = $this->tree;
				while(!empty($parents)) {
					$id = array_pop($parents);
					$tree = $tree[$id]['menu'];
				}
			}
			else {
				$tree = $this->tree[$id]['menu'];
			}
			return $tree;
		}
			
		return $this->tree;
	}
	
	
	public function getId($key = 0) {
		if (is_string($key) && !empty($key)) {
			if (!empty($this->groups['cat_' . $key]))
				$key = (int)$this->groups['cat_' . $key];
			else
				$key = 0;
		}
		else {
			$key = (int)$key;
		}
			
		return $key;
	}
	
	public function getAllGroups() {
		return $this->groups;
	}
	
	public function getGroup($id) {
		$id = $this->getId($id);
		if (empty($this->groups[$id])) return;
		return $this->groups[$id];
	}
	
	public function getGroupId($code = '') {
		$cat = isRequest('cat') ? getRequest('cat') : $code;
		$id = $this->getId((string)$cat);
		if (isset($this->groups[$id]))
			return $id;
		elseif (isRequest('cat'))
			seData::getInstance()->go404();
		return;
	}
	
	public function getChildrensId($id, $add_current = true) {
		$id = $this->getId($id);
		if (empty($this->groups[$id])) return;
		$childrens = $this->groups[$id]['childrens'];
		if ($add_current)
			array_push($childrens, $id);
		return $childrens;
	}
	
	public function getChildrens($id, $add_current = false) {
		$childrens_id = $this->getChildrensId($id, $add_current);
		if (empty($childrens_id)) return;
		$groups = array();
		foreach ($childrens_id as $val) {
			$groups[] = $this->groups[$val];
		}
		return $groups;
	}
	
	public function getChildsId($id, $add_current = false) {
		$id = $this->getId($id);
		if (empty($this->groups[$id])) return;
		$childs = $this->groups[$id]['children'];
		if ($add_current)
			array_push($childs, $id);
		return $childs;
	}
	
	public function getChilds($id, $add_current = false) {
		$childs_id = $this->getChildsId($id, $add_current);
		if (empty($childs_id)) return;
		$groups = array();
		foreach ($childs_id as $val) {
			$groups[] = $this->groups[$val];
		}
		return $groups;
	}
	
	public function getParentsId($id, $add_current = false) {
		$id = $this->getId($id);
		if (empty($this->groups[$id])) return;
		$parents = $this->groups[$id]['parents'];
		if ($add_current)
			array_unshift($parents, $id);
		return $parents;
	}
	
	public function getParents($id, $add_current = false) {
		$parents_id = $this->getParentsId($id, $add_current);
		if (empty($parents_id)) return;
		$groups = array();
		foreach ($parents_id as $val) {
			$groups[] = $this->groups[$val];
		}
		return $groups;
	}
	
	public function getSiblingsId($id) {
		$id = $this->getId($id);
		if (!empty($this->groups[$id]['parent'])) {
			$parent_id = $this->groups[$id]['parent'];
			$id_list = $this->groups[$parent_id]['children'];
		}
		elseif (!empty($this->groups[$id])) {
			$id_list = $this->getMainGroupsId();
		}
		else
			return;
		return $id_list;
	}
	
	public function getSiblings($id) {
		$siblings_id = $this->getSiblingsId($id);
		if (empty($siblings_id)) return;
		$groups = array();
		foreach ($siblings_id as $val) {
			$groups[] = $this->groups[$val];
		}
		return $groups;
	}
	
	public function getMainGroupsId() {
		$list = array();
		foreach ($this->tree as $val) {
			$list[] = $val['id'];
		}
		return $list;
	}
	
	public function getMainGroups() {
		$groups_id = $this->getMainGroupsId();
		if (empty($groups_id)) return;
		$groups = array();
		foreach ($groups_id as $val) {
			$groups[] = $this->groups[$val];
		}
		return $groups;
	}
	
	private function parseTreeGroups($upid = 0) {
		$groups = array();
		$tbl = new seTable('shop_group', 'sg');
		$tbl->select('sg.id, sg.name, sg.code_gr, sg.picture, sg.picture_alt, sg.scount, sg.commentary, sg.footertext, sg.title, sg.keywords, sg.description');
		if ($upid) {
			$tbl->where('sg.upid = ?', $upid);
		} 
		else {                       
			$tbl->where('sg.upid = 0 OR sg.upid IS NULL');
		}
		$tbl->andWhere('sg.lang = "?"', se_getlang());
		$tbl->andWhere('sg.active = "Y"');
		//$tbl->andWhere('sg.id_main = ?', $this->id_main);
		$tbl->orderby('sg.position');
		$list = $tbl->getList();
		
		if (!empty($list)) {
			foreach ($list as $val) {
				$this->groups['cat_' . $val['code_gr']] = $val['id'];
				$this->groups[$val['id']] = array(
					'id' => $val['id'],
					'name' => $val['name'],
					'image' => trim($val['picture']),
					'image_alt' => trim($val['picture_alt']),
					'code' => $val['code_gr'],
					'title' => $val['title'],
					'keywords' => $val['keywords'],
					'description' => $val['description'],
					'footertext' => $val['footertext'],
					'commentary' => $val['commentary'],
					'children' => array(),
					'childrens' => array(),
					'parent' => $upid,
					'parents' => array(),
					'count' => $val['scount']
				);
				if (!empty($upid)) {
					$this->groups[$upid]['children'][] = $val['id'];
					$this->groups[$val['id']]['parents'] = $this->groups[$upid]['parents'];
					array_unshift($this->groups[$val['id']]['parents'], $upid);
					$this->recursiveChildren($upid, $val['id']);
				}
				$groups[$val['id']] = array(
					'id' => $val['id'],
					'name' => $val['name'],
					'link' => '/cat/' . urlencode($val['code_gr']) . URL_END,
					'code' => $val['code_gr'],
					'menu' => $this->parseTreeGroups($val['id']),
					'image' => trim($val['picture']),
					'image_alt' => trim($val['picture_alt']),
					'count' => $val['scount']
				);
				
			}
		}
		
		return $groups;
	}
	
	private function recursiveChildren($parent, $child) {
		$this->groups[$parent]['childrens'][] = $child;
		$this->groups[$parent]['count'] += $this->groups[$child]['count'];
		if (!empty($this->groups[$parent]['parent']) && $this->groups[$parent]['parent'] != $parent) {
			$this->recursiveChildren($this->groups[$parent]['parent'], $child);
		}
	}
	
	public static function getInstance() 
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}	
}