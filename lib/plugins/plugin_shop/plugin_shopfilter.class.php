<?php

class plugin_shopfilter {
    
    private $group;
    private $selected_filter = array();
    
    public function __construct($basegroup = '', $id_group = 0) {
        if (empty($id_group)) {
			$group = plugin_shopgroup::getInstance($basegroup);
			$id_group = $group->getId();
		}	
		$this->group = $id_group;
        if (isRequest('f')) {
            $get = $_GET['f'];
			if (is_array($get))
				$this->selected_filter = $_GET['f'];   
		}
    }
    
    public function existFilters() {
        $filters = array();
		if (!empty($this->group)) {
			$shop_filter = new seTable('shop_group_filter');
			$shop_filter->select('id_feature, default_filter, expanded');
			$shop_filter->where('id_group=?', $this->group);
			$shop_filter->andwhere('(id_feature IS NOT NULL OR default_filter IS NOT NULL)');
			$shop_filter->orderBy('sort', 0);
			$list = $shop_filter->getList();
			if (!empty($list)) {
				foreach($list as $val) {
					$key = (!empty($val['id_feature'])) ? $val['id_feature'] : $val['default_filter'];
					if ($key == 'flag_hit')
						$key = 'hit';
					elseif ($key == 'flag_new')
						$key = 'new';
					$filters[$key] = array('expanded'=>$val['expanded']);
				}
			}
        }
		return $filters; 
    }

    public function getCountFiltered() {
		$goods = new plugin_shopgoods();
        return $goods->getGoodsCount();
    }

    public function getSQLFiltered() {
        $join = $where = array();   
		if (!empty($this->selected_filter)) {
            $default_filter = array('price', 'brand', 'hit', 'new', 'discount', 'special');
            $base_curr = se_baseCurrency();
            $current_curr = se_getMoney(); 
            $i=0;

            //$join[] = array('type'=>'left', 'table'=>'shop_modifications sm', 'on'=>'sp.id = sm.id_price');
            foreach($this->selected_filter as $key => $val) {
                $value = $val;
                if (in_array($key, $default_filter)) {
                    switch ($key) {
                        case 'price': 
                            $price_from = se_MoneyConvert((float)$value['from'], $current_curr, $base_curr);
                            $price_to = se_MoneyConvert((float)$value['to'], $current_curr, $base_curr);
                            $where[] ='(sp.price * (SELECT m.kurs FROM `money` `m` WHERE m.name = sp.curr ORDER BY m.date_replace DESC LIMIT 1) BETWEEN "' . $price_from . '" AND "' . $price_to . '")';
                            break;
                        case 'brand':
                            $join[] = array('type'=>'inner', 'table'=>'shop_brand sb', 'on'=>'sp.id_brand=sb.id');
                            $value = join(',', array_map('intval', $value));
                            $where[] = "(sb.id IN ($value))"; 
                            break;
                        case 'hit':
                            if ($value === '1') {
                                $where[]  = "sp.flag_hit = 'Y'";    
                            }
                            elseif ($value === '0') {
                                $where[] = "sp.flag_hit = 'N'"; 
                            }
                            break;
                        case 'new':
                            if ($value === '1') {
                                $where[] = "sp.flag_new = 'Y'";    
                            }
                            elseif ($value === '0') {
                                $where[] = "sp.flag_new = 'N'"; 
                            } 
                            break;
                        case 'discount': 
                            if ($value === '1') {
                                //$where[] = '(SELECT DISTINCT 1 FROM shop_discount_links sdl WHERE sdl.id_price = sp.id OR sdl.id_group = sp.id_group LIMIT 1) IS NOT NULL';
								$join[] = array('type'=>'inner', 'table'=>'shop_discount_links sdl', 'on'=>'sp.id = sdl.id_price OR sp.id_group = sdl.id_group');
                            }
                            elseif ($value === '0') {
                                $where[] = '(SELECT DISTINCT 1 FROM shop_discount_links sdl WHERE sdl.id_price = sp.id OR sdl.id_group = sp.id_group LIMIT 1) IS NULL';
                            } 
							break;
						case 'special': 
							if ($value === '1') {
								$join[] = array('type'=>'inner', 'table'=>'shop_leader sl', 'on'=>'sp.id = sl.id_price');
                            }
                            elseif ($value === '0') {
                                $join[] = array('type'=>'left', 'table'=>'shop_leader sl', 'on'=>'sp.id = sl.id_price');
								$where[] = 'sl.id IS NULL'; 
                            } 
							break;     
                    }
                }
                else {
                    $spf = 'spf' . $i++;
                    (int)$key;
                    if (is_array($value)) {
                        if (isset($value['from']) && isset($value['to'])) {
                            (float)$value['from'];
                            (float)$value['to'];
                            if (!empty($value['from']) && !empty($value['to'])) {
                                $join[] = array('type'=>'inner', 'table'=>'shop_modifications_feature ' . $spf, 'on'=>$spf .'.id_price=sp.id');
                                $where[] = '('.$spf.'.id_feature='.$key.' AND '.$spf.'.value_number BETWEEN "' . (float)$value['from']. '" AND "' . (float)$value['to'] . '")';
                            }
                        } 
                        else {
                            $value = join(',', array_map('intval', $value));
							if (!empty($value)){
								$join[] = array('type'=>'inner', 'table'=>'shop_modifications_feature ' . $spf, 'on'=>"$spf.id_price=sp.id");
								//$join[] = array('type'=>'inner', 'table'=>'shop_modifications_feature ' . $spf, 'on'=>"$spf.id_price=sp.id OR sm.id=$spf.id_modification");
								$where[] = "({$spf}.id_feature={$key} AND {$spf}.id_value IN ({$value}))";
							}
                        }   
                    }  
                    elseif ($val === '0' || $val === '1') {
                        $join[] = array('type'=>'inner', 'table'=>'shop_modifications_feature ' . $spf, 'on'=>$spf .'.id_price=sp.id');
                        $where[] = '('.$spf.'.id_feature='.$key.' AND '.$spf.'.value_bool = '. (int)$value . ')';
                    } 
                }      
            } 
        }
        return array($join, $where);
    }
    
    public function getFilterValues($tree_group = null, $flLive = false) {
        $filter_list = $this->existFilters();      
        $join = array();
        $where = array();
        list($join, $where) = $this->getSQLFiltered();
        
        if (empty($filter_list)) return;
        
        if (empty($tree_group)) {
            $group = plugin_shopgroup::getInstance('');
            $tree_group = $group->getGroups();
        }		
        
        $base_curr = se_baseCurrency();
        $current_curr = se_getMoney(); 
		
		se_db_query('SET group_concat_max_len = 4096;');
		
        $price_feature = new seTable('shop_feature', 'sf');
        $price_feature->select("
            sf.id,
            sf.type,
            sf.name,
            sf.measure,
            GROUP_CONCAT(DISTINCT CASE
                WHEN (sf.type = 'list' OR sf.type = 'colorlist') THEN (SELECT CONCAT_WS('##', RIGHT(CONCAT('0000', sfv.sort), 5), sfv.value, sfv.id, sfv.color, sfv.image) FROM shop_feature_value_list sfv WHERE sfv.id = spf.id_value LIMIT 1)
                WHEN (sf.type = 'number') THEN spf.value_number  
                WHEN (sf.type = 'bool') THEN spf.value_bool
            END SEPARATOR '~~') AS value
        ");

        $price_feature->innerJoin('shop_group_filter sgf', 'sgf.id_feature = sf.id');
        $price_feature->innerJoin('shop_modifications_feature spf', 'spf.id_feature = sgf.id_feature');
        $price_feature->innerJoin('shop_price sp', 'spf.id_price = sp.id');
        $price_feature->where("(sp.enabled='Y')");
        $price_feature->andwhere('(sp.id_group IN (?) OR sp.id IN (SELECT price_id FROM shop_group_price WHERE group_id IN (?)))', $tree_group);
        if ($flLive) {
			foreach($join as $jn) {
				$price_feature->innerJoin($jn['table'], $jn['on']);
			}
			foreach($where as $wh) {
				$price_feature->andwhere($wh);
			}
        }

        $price_feature->andWhere("sf.type IN ('colorlist', 'list', 'number', 'bool')");
        $price_feature->andWhere('sgf.id_group = ?', $this->group);
        $price_feature->groupBy('sf.id');
        $min_count = ($flLive) ? 0 : 1;
        $price_feature->having('COUNT(DISTINCT spf.id_value) + COUNT(DISTINCT spf.value_number) + COUNT(DISTINCT spf.value_bool) > '. $min_count);
        //$price_feature->orderBy('sf.name');
        $feature_list = $price_feature->getList();
		
        if (isset($filter_list['price'])) {
            $shop_price = new seTable('shop_price', 'sp');
            $shop_price->select('
                MIN(sp.price * (SELECT m.kurs FROM `money` `m` WHERE m.name = sp.curr ORDER BY m.date_replace DESC LIMIT 1)) AS minprice,
                MAX(sp.price * (SELECT m.kurs FROM `money` `m` WHERE m.name = sp.curr ORDER BY m.date_replace DESC LIMIT 1)) AS maxprice 
            ');
            $shop_price->where('sp.enabled = "Y"');
            $shop_price->andwhere('(sp.id_group IN (?) OR sp.id IN (SELECT price_id FROM shop_group_price WHERE group_id IN (?)))', $tree_group);
            //$shop_price->andwhere('sp.id_group IN (?)', $tree_group);
            $shop_price->fetchOne();

            if ($shop_price->isFind()) {
                $expanded = $filter_list['price']['expanded'];
				if (!$expanded && isset($this->selected_filter['price']))
					$expanded = 1;
				$filter_list['price'] = array(
                    'type' => 'range', 
                    'measure' => 'р.',
					'expanded' => $expanded,
                    'min' => floor(se_MoneyConvert($shop_price->minprice, $base_curr, $current_curr)), 
                    'max' => ceil(se_MoneyConvert($shop_price->maxprice, $base_curr, $current_curr)) 
                );
				
				$currency = seCurrency::getInstance(se_getlang());
				$res_setcurr = $currency->getCurrData($current_curr);
				if (!empty($res_setcurr['name_front']))
					$filter_list['price']['measure'] = '<span class="fMoneyFront">' . $res_setcurr['name_front'].'</span>';
				elseif  (!empty($res_setcurr['name_flang'])) {
					$rubl = (in_array($current_curr,array('RUR','RUB'))) ? ' rubl' : '';
					$nameflang = (in_array($current_curr,array('RUR','RUB'))) ? 'руб.' : $res_setcurr['name_flang'];
					$filter_list['price']['measure'] = '&nbsp;<span class="fMoneyFlang'.$rubl.'">'.$nameflang.'</span>';
				}
				
                if (isset($this->selected_filter['price'])) {
                    if (isset($this->selected_filter['price']['from']))
                        $filter_list['price']['from'] = (float)$this->selected_filter['price']['from'];
                    if (isset($this->selected_filter['price']['to']))
                        $filter_list['price']['to'] = (float)$this->selected_filter['price']['to']; 
                }
                if ($filter_list['price']['min'] == $filter_list['price']['max'])
                    unset($filter_list['price']);
            }
			else
				unset($filter_list['price']);
        }
        
        if (isset($filter_list['brand'])) {
            $shop_price = new seTable('shop_price', 'sp');
            $shop_price->select('DISTINCT sb.id, sb.name');
            $shop_price->innerjoin('shop_brand sb', 'sb.id=sp.id_brand');
            $shop_price->where('sp.enabled = "Y"');
			if ($flLive) {
				foreach($join as $jn) {
				   $shop_price->innerJoin($jn['table'], $jn['on']);
				}
				foreach($where as $wh) {
				   $shop_price->andwhere($wh);
				}
			}
            //$shop_price->andwhere('sp.id_group IN (?)', $tree_group);
			$shop_price->andWhere('(sp.id_group IN (?) OR sp.id IN (SELECT price_id FROM shop_group_price WHERE group_id IN (?)))', $tree_group);
			$shop_price->orderBy('sb.name', 0);
            $list = $shop_price->getList();
            if (!empty($list) && count($list) > 0) {
                if (isRequest('brand')) {
					$brand = getRequest('brand');
					$shop_brand = new seTable('shop_brand');
					$shop_brand->select('id');
					$shop_brand->where("code='?'", urldecode($brand));
					$shop_brand->fetchOne();
					if ($shop_brand->isFind())
						$this->selected_filter['brand'][] = $shop_brand->id;
				}
				$expanded = $filter_list['brand']['expanded'];
				if (!$expanded && isset($this->selected_filter['brand']))
					$expanded = 1;
				$filter_list['brand'] = array('type' => 'list', 'values' => array(), 'expanded' => $expanded);
				foreach($list as $val) {
                    $check = (bool)isset($this->selected_filter['brand']) && is_array($this->selected_filter['brand']) && in_array($val['id'], $this->selected_filter['brand']);
                    $filter_list['brand']['values'][$val['id']] = array('value' => $val['name'], 'check' => $check);
                }  
            }
        }
        
        if (isset($filter_list['hit'])) {
            $expanded = $filter_list['hit']['expanded'];
			if (!$expanded && isset($this->selected_filter['hit']) && ($this->selected_filter['hit'] === '1' || $this->selected_filter['hit'] === '0'))
				$expanded = 1;
			//unset($filter_list['flag_hit']);
            $check = '';
            if (isset($this->selected_filter['hit'])) {
                if ($this->selected_filter['hit'] === '1' || $this->selected_filter['hit'] === '0') {
                    $check = $this->selected_filter['hit'];
                }
            } 
            $filter_list['hit'] = array('type' => 'bool', 'check' => $check, 'expanded' => $expanded);        
        }
        
        if (isset($filter_list['new'])) {
			$expanded = $filter_list['new']['expanded'];
			if (!$expanded && isset($this->selected_filter['new']) && ($this->selected_filter['new'] === '1' || $this->selected_filter['new'] === '0'))
				$expanded = 1;
			//unset($filter_list['flag_new']);
            $check = '';
            if (isset($this->selected_filter['new'])) {
                if ($this->selected_filter['new'] === '1' || $this->selected_filter['new'] === '0') {
                    $check = $this->selected_filter['new'];
                }
            } 
            $filter_list['new'] = array('type' => 'bool', 'check' => $check, 'expanded' => $expanded);        
        }
        
        if (!empty($feature_list)) {
            $feature_image_dir = '/images/' . se_getLang() . '/shopfeature/'; 
			foreach ($feature_list as $val) {
				$id = $val['id'];
                $filter = array(
                    'name' => $val['name'],
                    'type' => $val['type'],
                    'measure' => $val['measure'],
					'expanded' => $filter_list[$id]['expanded']
                );
                
                if ($val['type'] == 'bool') {
                    $filter_list[$id]['check'] = false;  
                    if (isset($this->selected_filter[$id])) {
                        if ($this->selected_filter[$id] === '1' || $this->selected_filter[$id] === '0') {
                            $filter['check'] = $this->selected_filter[$id];
                        }
                    }    
                }
                elseif ($val['type'] == 'list' || $val['type'] == 'colorlist') {
                    $filter['values'] = array();
                    $filter['type'] = $val['type'];
                    if (!empty($val['value'])) {
                        $value_list = explode('~~', $val['value']);
                        sort($value_list);
                        foreach($value_list as $line) {
                            @list(, $value, $id_value, $color, $image) = explode('##', $line);
                            $check = (bool)isset($this->selected_filter[$id]) && is_array($this->selected_filter[$id]) && in_array($id_value, $this->selected_filter[$id]);
                            $filter['values'][$id_value] = array('value' => $value, 'check' => $check); 

							if ($val['type'] == 'colorlist') {
								if (!empty($image) && file_exists(SE_ROOT . $feature_image_dir . $image))
									$filter['values'][$id_value]['image'] = $feature_image_dir . $image;
								else
									$filter['values'][$id_value]['color'] = $color;
							}
                        }
                    }   
                }
                elseif ($val['type'] == 'number') {
                    $filter['type'] = 'range';
                    $value_list = explode('~~', $val['value']);
                    $filter['min'] = min($value_list);
                    $filter['max'] = max($value_list); 
                    if (isset($this->selected_filter[$id])) {
                        if (isset($this->selected_filter[$id]['from']))
                            $filter['from'] = (float)$this->selected_filter[$id]['from'];
                        if (isset($this->selected_filter[$id]['to']))
                            $filter['to'] = (float)$this->selected_filter[$id]['to']; 
                    }
                    if ($filter['min'] == $filter['max']) unset($filter_list[$id]);
                }
				if (!$filter['expanded'] && isset($this->selected_filter[$id])) {
					if ($val['type'] == 'bool') {
						if (($this->selected_filter[$id] === '1' || $this->selected_filter[$id] === '0'))
							$filter['expanded'] = 1;
					}
					elseif ($val['type'] == 'number') {
						if ($filter['from'] > $filter['min'] || $filter['to'] < $filter['max'])
							$filter['expanded'] = 1;
					}
					else
						$filter['expanded'] = 1;
				}
                if (isset($filter_list[$id]))
					$filter_list[$id] = $filter;        
            }
        }
		foreach ($filter_list as $key => $val) {
			if (empty($val['type']))
				unset($filter_list[$key]);
		}
        return $filter_list;       
    }
}
