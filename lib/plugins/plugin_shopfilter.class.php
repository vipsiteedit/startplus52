<?php

class plugin_shopfilter {
    
    private $group;
    private $selected_filter = array();
    
    public function __construct($id_group = 0) {
        if (empty($id_group)) {
			$group = plugin_shopgroup::getInstance('');
			$id_group = $group->getId();
		}	
		$this->group = $id_group;
        if (isset($_GET['f']))
            $this->selected_filter = $_GET['f'];   
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
        if (!empty($this->selected_filter)) {
            $lang = se_getLang(); 
            $default_filter = array('price', 'brand', 'hit', 'new', 'discount');
            $base_curr = se_baseCurrency();
            $current_curr = se_getMoney(); 
            $i=0;
            $pr=0;
          $andwhere = '';
            $join = array();
            $where = array();
            //$join[] = array('type'=>'left', 'table'=>'shop_modifications sm', 'on'=>'sp.id = sm.id_price');
            foreach($this->selected_filter as $key => $val) {
                $value = $val;
                if (in_array($key, $default_filter)) {
                    switch ($key) {
                        case 'price': 
                            $pr++;
                            $price_from = se_MoneyConvert((float)$value['from'], $current_curr, $base_curr);
                            $price_to = se_MoneyConvert((float)$value['to'], $current_curr, $base_curr);
                            $where[] ='(sp.price * (SELECT m.kurs FROM `money` `m` WHERE m.name = sp.curr ORDER BY m.date_replace DESC LIMIT 1) BETWEEN "' . $price_from . '" AND "' . $price_to . '")';
                            break;
                        case 'brand':
                            $pr++;
                            $join[] = array('type'=>'inner', 'table'=>'shop_brand sb', 'on'=>'sp.id_brand=sb.id');
                            $value = join(',', array_map('intval', $value));
                            $where[] = "(sb.id IN ($value))"; 
                            break;
                        case 'hit': 
                            $pr++;
                            if ($value === '1') {
                                $where[]  = "sp.flag_hit = 'Y'";    
                            }
                            elseif ($value === '0') {
                                $where[] = "sp.flag_hit = 'N'"; 
                            }
                            break;
                        case 'new':
                            $pr++;
                            if ($value === '1') {
                                $where[] = "sp.flag_new = 'Y'";    
                            }
                            elseif ($value === '0') {
                                $where[] = "sp.flag_new = 'N'"; 
                            } 
                            break;
                        case 'discount': 
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
                                $pr++;
                                $join[] = array('type'=>'inner', 'table'=>'shop_modifications_feature ' . $spf, 'on'=>$spf .'.id_price=sp.id');
                                $where[] = '('.$spf.'.id_feature='.$key.' AND '.$spf.'.value_number BETWEEN "' . (float)$value['from']. '" AND "' . (float)$value['to'] . '")';
                            }
                        } 
                        else {
                            $pr++;
                            $join[] = array('type'=>'inner', 'table'=>'shop_modifications_feature ' . $spf, 'on'=>"$spf.id_price=sp.id");
                            //$join[] = array('type'=>'inner', 'table'=>'shop_modifications_feature ' . $spf, 'on'=>"$spf.id_price=sp.id OR sm.id=$spf.id_modification");
                            $value = join(',', array_map('intval', $value));
                            $where[] = "({$spf}.id_feature={$key} AND {$spf}.id_value IN ({$value}))";
                        }   
                    }  
                    elseif ($val === '0' || $val === '1') {
                        $pr++;
                        $join[] = array('type'=>'inner', 'table'=>'shop_modifications_feature ' . $spf, 'on'=>$spf .'.id_price=sp.id');
                        $where[] = '('.$spf.'.id_feature='.$key.' AND '.$spf.'.value_bool = '. (int)$value . ')';
                    } 
                }      
            } 
            if ($pr) {
                return array($join, $where);
            }
        }
        return array();
    }
    
    public function getFilterValues($tree_group = null) {
        $filter_list = $this->existFilters();      
        $join = array();
        $where = array();
        list($join, $where) = $this->getSQLFiltered();
        
        //echo '<!--';
        //print_r($where);
        //print_r($join);
        //echo '-->';
        if (empty($filter_list)) return;
        
        if (empty($tree_group)) {
            $group = plugin_shopgroup::getInstance('');
            $tree_group = $group->getGroups();
        }		
        
        $base_curr = se_baseCurrency();
        $current_curr = se_getMoney(); 
        $price_feature = new seTable('shop_feature', 'sf');
        $price_feature->select("
            sf.id,
            sf.type,
            sf.name,
            sf.measure,
            GROUP_CONCAT(DISTINCT CASE
                WHEN (sf.type = 'list' OR sf.type = 'colorlist') THEN (SELECT CONCAT(sfv.value, '##', sfv.id) FROM shop_feature_value_list sfv WHERE sfv.id = spf.id_value LIMIT 1)
                WHEN (sf.type = 'number') THEN spf.value_number  
                WHEN (sf.type = 'bool') THEN spf.value_bool
            END SEPARATOR '~~') AS value
        ");

        $price_feature->innerJoin('shop_group_filter sgf', 'sgf.id_feature = sf.id');
        $price_feature->innerJoin('shop_modifications_feature spf', 'spf.id_feature = sgf.id_feature');
        $price_feature->innerJoin('shop_price sp', 'spf.id_price = sp.id');
        $price_feature->where("sp.enabled='Y'");
        $price_feature->andwhere('(sp.id_group IN (?) OR sp.id IN (SELECT price_id FROM shop_group_price WHERE group_id IN (?)))', $tree_group);
		/*
		foreach($join as $jn) {
           $price_feature->innerJoin($jn['table'], $jn['on']);
        }
        foreach($where as $wh) {
            $price_feature->andwhere($wh);
        }
		*/

        $price_feature->andWhere("sf.type IN ('colorlist', 'list', 'number', 'bool')");
        $price_feature->andWhere('sgf.id_group = ?', $this->group);
        $price_feature->groupBy('sf.id');
        $price_feature->having('COUNT(DISTINCT spf.id_value) + COUNT(DISTINCT spf.value_number) + COUNT(DISTINCT spf.value_bool) > 0');
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
                if (isset($this->selected_filter['price'])) {
                    if (isset($this->selected_filter['price']['from']))
                        $filter_list['price']['from'] = (float)$this->selected_filter['price']['from'];
                    if (isset($this->selected_filter['price']['to']))
                        $filter_list['price']['to'] = (float)$this->selected_filter['price']['to']; 
                }
            }
			else
				unset($filter_list['price']);
        }
        
        if (isset($filter_list['brand'])) {
            $shop_price = new seTable('shop_price', 'sp');
            $shop_price->select('DISTINCT sb.id, sb.name');
            $shop_price->innerjoin('shop_brand sb', 'sb.id=sp.id_brand');
            foreach($join as $jn) {
               $shop_price->innerJoin($jn['table'], $jn['on']);
            }
            $shop_price->where('sp.enabled = "Y"');
            foreach($where as $wh) {
               $shop_price->andwhere($wh);
            }
            $shop_price->andwhere('(sp.id_group IN (?) OR sp.id IN (SELECT price_id FROM shop_group_price WHERE group_id IN (?)))', $tree_group);
            //$shop_price->andwhere('sp.id_group IN (?)', $tree_group);
            $list = $shop_price->getList();
            if (!empty($list) && count($list) > 0) {
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
        
        if (isset($filter_list['flag_hit'])) {
            $expanded = $filter_list['flag_hit']['expanded'];
			if (!$expanded && isset($this->selected_filter['hit']) && ($this->selected_filter['hit'] === '1' || $this->selected_filter['hit'] === '0'))
				$expanded = 1;
			unset($filter_list['flag_hit']);
            $check = '';
            if (isset($this->selected_filter['hit'])) {
                if ($this->selected_filter['hit'] === '1' || $this->selected_filter['hit'] === '0') {
                    $check = $this->selected_filter['hit'];
                }
            } 
            $filter_list['hit'] = array('type' => 'bool', 'check' => $check, 'expanded' => $expanded);        
        }
        
        if (isset($filter_list['flag_new'])) {
			$expanded = $filter_list['flag_new']['expanded'];
			if (!$expanded && isset($this->selected_filter['new']) && ($this->selected_filter['new'] === '1' || $this->selected_filter['new'] === '0'))
				$expanded = 1;
			unset($filter_list['flag_new']);
            $check = '';
            if (isset($this->selected_filter['new'])) {
                if ($this->selected_filter['new'] === '1' || $this->selected_filter['new'] === '0') {
                    $check = $this->selected_filter['new'];
                }
            } 
            $filter_list['new'] = array('type' => 'bool', 'check' => $check, 'expanded' => $expanded);        
        }
        
        if (!empty($feature_list)) {
            foreach ($feature_list as $val) {
				$id = $val['id'];
				if (!$filter_list[$id]['expanded'] && isset($this->selected_filter[$id])) {
					if ($val['type'] == 'bool') {
						if (($this->selected_filter[$id] === '1' || $this->selected_filter[$id] === '0'))
							$filter_list[$id]['expanded'] = 1;
					}
					else
						$filter_list[$id]['expanded'] = 1;
				}
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
                    $filter['type'] = 'list';
                    if (!empty($val['value'])) {
                        $value_list = explode('~~', $val['value']);
                        sort($value_list);
                        foreach($value_list as $line) {
                            list($value, $id_value) = explode('##', $line);
                            $check = (bool)isset($this->selected_filter[$id]) && is_array($this->selected_filter[$id]) && in_array($id_value, $this->selected_filter[$id]);
                            $filter['values'][$id_value] = array('value' => $value, 'check' => $check);    
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
                }
                
                $filter_list[$id] = $filter;        
            }
        }
        foreach($filter_list as $id=>$val){
            if (empty($filter_list[$id]['type'])) unset($filter_list[$id]);
        }
        return $filter_list;       
    }
}
