<?php
class plugin_shopmodifications {
	private $lang; 
	private $id_price;
	private $in_stock_only = false;
	
	public function __construct($id_price,  $in_stock_only = false) {
		$this->lang = se_getLang();
		$this->id_price = $id_price;
		$this->in_stock_only = $in_stock_only;
	}
	
	public static function getName($modifications) {
		$name = '';
		if (!empty($modifications)) {
			if (is_array($modifications)) 
				$modifications = join(',', $modifications);
			
			$shop_modifications = new seTable('shop_modifications', 'sm');
			$shop_modifications->select('GROUP_CONCAT(CONCAT(sf.name,": ",sfvl.value) SEPARATOR ", ") AS paramsname');
			$shop_modifications->innerjoin('shop_modifications_feature smf', 'sm.id=smf.id_modification');
			$shop_modifications->innerjoin('shop_feature sf', 'smf.id_feature=sf.id');
			$shop_modifications->innerjoin('shop_feature_value_list sfvl', 'smf.id_value=sfvl.id');
			$shop_modifications->where('sm.id IN (?)', $modifications);
			$shop_modifications->fetchOne();
			
			if ($shop_modifications->isFind())
				$name = $shop_modifications->paramsname;
		}
		return $name;
	}
	
	public static function getArticle($modifications) {
		$article = '';
		if (!empty($modifications)) {
			if (!is_array($modifications)) { 
				$modifications = explode(',', $modifications);
			}

			$shop_modifications = new seTable('shop_modifications', 'sm');
			$shop_modifications->select('sm.code');
			$shop_modifications->where('sm.id=?', trim(array_pop($modifications)));
			$shop_modifications->fetchOne();
			if ($shop_modifications->isFind())
				$article = $shop_modifications->code;
		}
		
		return $article;
	}
	
	public function getAllList($in_sql = null) {
		if (!$this->id_price) return;
		$shop_modifications = new seTable('shop_modifications', 'sm');
		$shop_modifications->select('
			sm.id,
			smf.id_feature,
			smf.id_value,
			sm.id_mod_group,
			sm.`default`,
			sf.name,
			sf.type,
			sf.description,
			sfvl.value,
			sfvl.color,
			sfvl.image
		');
		$shop_modifications->innerJoin('shop_modifications_feature smf', 'sm.id = smf.id_modification');
		$shop_modifications->innerJoin('shop_feature sf', 'smf.id_feature = sf.id');
		$shop_modifications->innerJoin('shop_feature_value_list sfvl', 'smf.id_value = sfvl.id');
		$shop_modifications->innerJoin('shop_modifications_group smg', 'sm.id_mod_group = smg.id');
		$shop_modifications->where('sm.id_price = ?', $this->id_price);
		if (!empty($in_sql))
			$shop_modifications->andwhere('sm.id IN (?)', $in_sql);   
		if ($this->in_stock_only)
			$shop_modifications->andWhere('(sm.count <> 0 OR sm.count IS NULL)');  
		$shop_modifications->orderBy('smg.sort', 0);
		$shop_modifications->addOrderBy('sf.sort', 0);
		$shop_modifications->addOrderBy('sfvl.sort', 0);
		$shop_modifications->addOrderBy('sfvl.value', 0);
		return $shop_modifications->getList();  
	}

	public function getModifications($selected_only = false) {
		$images_dir = '/images/' . $this->lang . '/shopfeature/';
		$params = array();
		$modifications_list = $this->getAllList();
		if (!empty($modifications_list)) { 
			$selected = array();
			$get_selected = isRequest('m') ? explode(',', getRequest('m', 3)) : '';
			foreach($modifications_list as $val) {
				$id_group = $val['id_mod_group'];
				$id_feature = $val['id_feature'];
				$id_modification = $val['id'];
				$id_value = $val['id_value'];
				
				if (!isset($selected[$id_group])) {
					$selected[$id_group] = array('first' => $id_modification, 'selected' => null, 'default' => null); 
				}
				if (!$selected[$id_group]['default'] && $val['default'])
					$selected[$id_group]['default'] = $id_modification;
				
				if (!$selected[$id_group]['selected'] && isset($_SESSION['modifications'][$this->id_price][$id_group])) {
					if ($id_modification == $_SESSION['modifications'][$this->id_price][$id_group])
						$selected[$id_group]['selected'] = $id_modification;
				}       
				if (!empty($get_selected) && in_array($id_modification, $get_selected)) {
					$selected[$id_group]['selected'] = $id_modification;
				}
				if (!$selected_only) {
					if (!isset($params[$id_group])) {
						$params[$id_group] = array('name' => 'gr_' . $id_group, 'params' => array());      
					}
					
					if (!isset($params[$id_group]['params'][$id_feature])) {
						$params[$id_group]['params'][$id_feature] = array(
							'name' => $val['name'],
							'type' => $val['type'],
							'description' => $val['description'],
							'values' => array()
						);                
					} 
					if (!isset($params[$id_group]['params'][$id_feature]['values'][$id_value])) {
						$params[$id_group]['params'][$id_feature]['values'][$id_value] = array(
							'value' => $val['value'],
							'color' => $val['color'],
							'image' => (!empty($val['image']) && file_exists(SE_ROOT . $images_dir . $val['image'])) ? $images_dir . $val['image'] : '',
							'modification' => array($val['id'])
						);
					}
					elseif (!in_array($id_modification, $params[$id_group]['params'][$id_feature]['values'][$id_value]['modification'])) {
						$params[$id_group]['params'][$id_feature]['values'][$id_value]['modification'][] = $id_modification;    
					}
				}
			}
		}
		unset($_SESSION['modifications'][$this->id_price]);
		if (!empty($selected)) {
			//print_r($selected);
			foreach ($selected as $key => $val) {
				$select = !empty($val['selected']) ? $val['selected'] : (!empty($val['default']) ? $val['default'] : $val['first']);
				$_SESSION['modifications'][$this->id_price][$key] = $select;
			}
		}
		return $params;
	}
	
	public function changeModifications($group, $param, $value) {
		$images_dir = '/images/' . $this->lang . '/shopfeature/';
		if (!empty($group)) {
			$shop_modifications = new seTable('shop_modifications', 'sm');
			$shop_modifications->select('sm.id');
			$shop_modifications->where('sm.id_price=?', $this->id_price);
			$shop_modifications->andWhere('sm.id_mod_group=?', $group);
			$list = $this->getAllList($shop_modifications->getSql());
			$params = array();
			$selected = $_SESSION['modifications'][$this->id_price][$group];
			
			$shop_modifications->select('smf.id_feature, smf.id_value');
			$shop_modifications->innerjoin('shop_modifications_feature smf', 'sm.id=smf.id_modification');
			$shop_modifications->where('sm.id = ?', $selected);
			$selected_list = $shop_modifications->getList();
			$selected_feature = array();
			foreach($selected_list as $val) {
				$selected_feature[$val['id_feature']] = $val['id_value'];
			}
			$last_m = $new_m = array();
			foreach ($list as $val) {
				$id_feature = $val['id_feature'];
				$id_modification = $val['id'];
				$id_value = $val['id_value'];
				
				if (in_array($id_feature, array_keys($selected_feature))) {
					//if ($selected_feature[$id_feature] == $id_value && !in_array($id_modification, $last_m))
					//	$last_m[] = $id_modification;
					if ($selected_feature[$id_feature] == $id_value) {
						if (isset($last_m[$id_modification]))
							$last_m[$id_modification]++;
						else
							$last_m[$id_modification] = 1;
					}
				}
				
				if ($param == $id_feature && $value == $id_value) {
					$new_m[] = $id_modification;
				}
					
				if (!isset($params[$id_feature])) {
					$params[$id_feature] = array(
						'type' => $val['type'],
						'selected' => 0,
						'values' => array()
					);                
				}
				if (!isset($params[$id_feature]['values'][$id_value])) {
					$params[$id_feature]['values'][$id_value] = array(
						'value' => $val['value'],
						'color' => $val['color']
					);
					if (!empty($val['image']) && file_exists(SE_ROOT . $images_dir . $val['image']))
						$params[$id_feature]['values'][$id_value]['image'] = $images_dir . $val['image'];
				}
				
				if (!isset($params[$id_feature]['values'][$id_value]['mod']) || !in_array($id_modification, $params[$id_feature]['values'][$id_value]['mod']))
					$params[$id_feature]['values'][$id_value]['mod'][] = $id_modification;
			}
			arsort($last_m);
			$result = array_intersect(array_keys($last_m), $new_m);
			//$result = array_intersect($last_m, $new_m);
			if (!empty($result))
				$selected = array_shift($result);
			else
				$selected = array_shift($new_m);
			
			$available_features1 = null;
			$available_features2 = null;
			foreach ($params as $key => $val) {
				if (!empty($available_features2)) {
					if ($available_features1 === null)
						$available_features1 = $available_features2;    
					else
						$available_features1 = array_intersect($available_features1, $available_features2);
				}
            
				$available_features2 = null;
				foreach ($val['values'] as $key2 => $val2) {
					if (!empty($available_features1)) {
						$result = array_intersect($available_features1, $val2['mod']); 
						if (empty($result)) {
							unset($params[$key]['values'][$key2]);
						}      
					}
					if (in_array($selected, $val2['mod'])) {
						$params[$key]['selected'] = $key2;
						if (empty($available_features2)){
							$available_features2 = $val2['mod'];
						}
					}
					unset($params[$key]['values'][$key2]['mod']);
				}
			}
			$_SESSION['modifications'][$this->id_price][$group] = $selected;
			return $params;
		}		
	}
	
	public function getImages($modifications) {
		$images = array();
		if (!empty($modifications)) {
			if (is_array($modifications)) 
				$modifications = join(',', $modifications);
			$shop_img = new seTable('shop_img', 'si');
			$shop_img->select('si.id');
			$shop_img->innerJoin('shop_modifications_img smi', 'smi.id_img=si.id'); 
			$shop_img->where('si.id_price=?', $this->id_price);
			$shop_img->andwhere('smi.id_modification IN (?)', $modifications);
			$images = $shop_img->getList();  
		}
		return $images;
	}
}