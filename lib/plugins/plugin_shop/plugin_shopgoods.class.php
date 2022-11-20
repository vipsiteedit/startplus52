<?php

/**
 * @author Ponomarev Dmitry
 * @copyright 2012
 */
class plugin_shopgoods {
	private $lang;                          //язык проекта
    public $footertext = '';                //текст подвала  
    private $sortval = '';                  //напрвление и поле сортировки
    private $current_page;
    private $GroupId;
	private $string_features = null;
	private $version = ''; 

    /** Конструктор
    *   @string  page           текущая страница
    *   @string  group_name     код группы, переданное принудительно
    *   @string  base_group     стартовая страница
    **/
    public function __construct($page=false, $group_name='', $base_group='') {		
		$base_group = strval($base_group);
		$this->lang = se_getLang();
		$this->current_page = ($page!==false) ? $page : get('page',0);
		
		if (class_exists('plugin_router')) {
			$router = plugin_router::getInstance();
		
			$router->register('product', '/' . $this->current_page . '/show/$code' . URL_END);
		
			$router->register('group', '/' . $this->current_page . '/cat/$code[/sheet/$page]' . URL_END);
		}
		
		$this->plugin_groups = plugin_shopgroups::getInstance();
		$this->GroupId = (int)$this->plugin_groups->getGroupId($base_group);
		
		if ($this->GroupId) {
			if ($group = $this->plugin_groups->getGroup($this->GroupId))
				$this->footertext = $group['footertext'];
			
		}
		
		$this->setRequest();    //запросы сортировки
		$this->version = se_getVersion();
    }   
     
	/** запросы
	*   @return нечего не возвращает
	**/ 
	private function setRequest(){
        //запрос на сортировку
        if (get('orderby')) {
            $this->sortval = get('orderby',0);            
        } elseif (get('sortOrderby')) {
                $this->sortval = get('sortOrderby',0).get('sortDir',0);
        }
        if ($this->sortval) {
              $_SESSION['SHOP_VITRINE']['sortval'] = $this->sortval;
        } else {
            $this->sortval = (isset($_SESSION['SHOP_VITRINE']['sortval']))
			  	? $_SESSION['SHOP_VITRINE']['sortval']
				: null;
        }
	}

    /** Получить поле сортировки и порядок сортировки
    *   @return string 
    **/
	public function getSortVal(){
       return $this->sortval;
	}
    
    //получить характеристики товара
    public function getGoodsFeatures($id_goods) {
        $price_feature_list = array();
        if (!empty($id_goods)) {
            $shop_feature = new seTable('shop_feature', 'sf');
            $shop_feature->select("DISTINCT sf.id,
                sf.id AS fid,
                sf.name AS fname,
                sf.type,
                sf.measure,
                sf.image AS fimage,
                sf.description AS fdescription,
                sfg.id AS gid,
                sfg.name AS gname,
                sfg.image AS gimage,
                sfg.description AS gdescription,
                GROUP_CONCAT(CASE    
                    WHEN (sf.type = 'list' OR sf.type = 'colorlist') THEN (SELECT CONCAT_WS('', sfvl.value, '##', sfvl.color, '##', sfvl.image) FROM shop_feature_value_list sfvl WHERE sfvl.id = smf.id_value)
                    WHEN (sf.type = 'number') THEN smf.value_number
                    WHEN (sf.type = 'bool') THEN smf.value_bool
                    WHEN (sf.type = 'string') THEN smf.value_string 
                    ELSE NULL
                END SEPARATOR '~~') AS value
			");
            $shop_feature->innerJoin('shop_modifications_feature smf', 'sf.id=smf.id_feature');
            $shop_feature->leftJoin('shop_feature_group sfg', 'sf.id_feature_group=sfg.id');
            $shop_feature->where('smf.id_price=?', $id_goods);
            $shop_feature->andWhere('smf.id_modification IS NULL');
			$shop_feature->groupBy('sf.id');
			$shop_feature->orderBy('sfg.sort IS NULL', 0);
            $shop_feature->addOrderBy('sfg.sort', 0);
			$shop_feature->addOrderBy('sf.sort', 0);
            $featurelist = $shop_feature->getList();

            if (!empty($featurelist)) {
                $images_dir = '/images/' . $this->lang . '/shopfeature/';
				foreach($featurelist as $val) {
                    $gid = (int)$val['gid'];
                    if (!isset($price_feature_list[$gid])) {
                        $price_feature_list[$gid] = array();
                    }
                    $price_feature_list[$gid]['name'] = $val['gname'];        
                    if (!empty($val['gimage'])) {
						$val['gimage'] = $images_dir . $val['gimage'];
						if (!file_exists(SE_ROOT . $val['gimage']))
							$val['gimage'] = '';
					}
					$price_feature_list[$gid]['image'] = $val['gimage'] ;
                    $price_feature_list[$gid]['description'] = $val['gdescription'];
        
                    if (!isset($price_feature_list[$gid]['features'])) {
                        $price_feature_list[$gid]['features'] = array();
                    }
					if (!empty($val['fimage'])) {
						$val['fimage'] = $images_dir . $val['fimage'];
						if (!file_exists(SE_ROOT . $val['fimage']))
							$val['fimage'] = '';
					}
					$val['background'] = '';
					if ($val['type'] == 'colorlist' || $val['type'] == 'list') {
						$list_values = explode('~~', $val['value']);
						$val['value'] = array();
						foreach($list_values as $value){
							list($val['val'], $color, $image) = explode('##', $value);
							$val['value'][] = $val['val'];
						}
						$val['value'] = join(', ', $val['value']);
					}
					else {
						$val['value'] = str_replace('~~', ', ', $val['value']);
					}
                    $feature = array(
                        'id' => $val['fid'],
                        'type' => $val['type'],
                        'name' => $val['fname'],
                        'image' => trim($val['fimage']),
                        'description' => trim($val['fdescription']),
                        'value' => $val['value'],
                        'measure' => $val['measure'],
						'background' => $val['background'],
                    );
                    $price_feature_list[$gid]['features'][] = $feature;
                }
            }
        }
        return $price_feature_list;
    }
	
	public function getStringFeatures($id_goods) {
		$feature_sting = '';
		if (!empty($id_goods)) {
            $shop_feature = new seTable('shop_feature', 'sf');
            $shop_feature->select('
				GROUP_CONCAT(CASE 
					WHEN (sf.type = "list" OR sf.type = "colorlist") THEN CONCAT_WS(": ", sf.name, CONCAT_WS(" ", (SELECT sfvl.value FROM shop_feature_value_list sfvl WHERE sfvl.id = smf.id_value), sf.measure))
					WHEN (sf.type = "number") THEN CONCAT_WS(": ", sf.name, CONCAT_WS(" ", smf.value_number, sf.measure))
					WHEN (sf.type = "bool") THEN IF (smf.value_bool > 0, sf.name, NULL)
					WHEN (sf.type = "string") THEN CONCAT_WS(": ", sf.name, smf.value_string)
					ELSE NULL 
				END SEPARATOR ", ") AS features
			');
            $shop_feature->innerJoin('shop_modifications_feature smf', 'sf.id=smf.id_feature');
            $shop_feature->leftJoin('shop_feature_group sfg', 'sf.id_feature_group=sfg.id');
            $shop_feature->where('smf.id_price=?', $id_goods);
            $shop_feature->andWhere('smf.id_modification IS NULL');
			$shop_feature->andWhere('sf.seo<>0');
			$shop_feature->orderBy('sfg.sort IS NULL', 0);
            $shop_feature->addOrderBy('sfg.sort', 0);
			$shop_feature->addOrderBy('sf.sort', 0);
			$featurelist = $shop_feature->fetchOne();
			if ($shop_feature->isFind())
				$feature_sting = $shop_feature->features;

        }
        return $feature_sting;
	}

	//возвращает количество товаров удовлетворяющих условиям поиска и фильтра
    public function getGoodsCount($options = array(), $goods_name = '') {
		return $this->getGoods($options, $goods_name, true);
	}

	/** Возвращает список товаров и постраничная навигация
    *   @array option             массив входных параметров
    *   @array goods_name         список товаров, которые надо отобразить
	*   @return array()     возвращает список товаров и постраничную навигацию
    **/
    public function getGoods($options=array(), $goods_name = '', $on_count = false) {
        if (!$this->GroupId && !$goods_name && !$on_count  && empty($options['is_under_group'])) {
            return false;
        }
		$where = $order = array();
		if (is_array($goods_name)) $goods_name = implode(',', $goods_name);
		if (!empty($options['is_base_group']) && $options['is_base_group']) $this->Group_id = $this->baseGroupId;

		$defoptions = array(
    		'interface' => array(
	    		'sp.id',
	    	    'sp.id_group',
	    	    'sp.code',
	    	    'sp.article',
	    	    'sp.name',
	    	    'sp.img_alt',
	    	    'sp.note',
	    	    'sp.text',
	    	    'sp.presence_count',
	    	    'sp.presence',
				'sp.step_count',
	    	    'sp.measure', 
	    	    'sp.enabled',
	    	    'sp.unsold', 
	    	    'sp.flag_hit', 
	    	    'sp.flag_new', 
	    	    'sp.price',
	    	    'sp.price_opt_corp',
	    	    'sp.price_opt',
	    	    'sp.bonus',
	    	    'sp.discount',
				'sp.max_discount',
	    	    'sp.curr',
				'sp.title',
				'sp.keywords', 
				'sp.description'
			),
			'is_under_group'=>true,
			'sort'=>false,
			'in_stock' => false,
			'limit'=>20,
		);
		$option = array_merge($defoptions, $options);	
        $price = new seShopPrice();
        if (!$on_count) {
			if (!is_array($option['interface'])) {
				$selectfields = explode(',',$option['interface']);
			}
			else $selectfields = $option['interface'];
			
			$selectfields[] = $this->getModificationsSql();
			$selectfields[] = $this->getRatingSql();
			$selectfields[] = $this->getBrandSql();
			$selectfields[] = 'IF ((sp.presence_count=-1 OR sp.presence_count IS NULL), 1000000000, sp.presence_count) presence_count_adopt';
			//$selectfields[] = 'sg.name AS group_name';
			//$selectfields[] = 'sg.code_gr';
			if ($this->version == '5.2')
				$selectfields[] = '(SELECT CONCAT_WS("", si.picture, "##", si.picture_alt) FROM shop_img si WHERE si.id_price = sp.id ORDER BY si.`default` DESC, si.sort ASC LIMIT 1) AS img';
			else {
				$selectfields[] = $this->getDiscountSql();
				$selectfields[] = $this->getSpecialSql();
				$selectfields[] = 'sp.img';
			}

			$price->select(implode(',',$selectfields));
		} else {
			$price->select('COUNT(*)');
		}
		$price->where('sp.enabled="Y"');

		//$price->innerjoin('shop_group sg','sp.id_group = sg.id');
		if (empty($goods_name)) {
			// отображать ли подгруппы?
			/*
			if ($option['is_under_group'] && $this->GroupId) {                         
				$groups = implode(',', $this->getTreeShopGroup($this->GroupId,true));
				$groups = str_replace(',,', ',', $groups );
			}
			else
				$groups = $this->GroupId;
			*/

			$where = array();
			$wheregroup = 'active = "Y"';
		
			if ($this->GroupId) {
				$groups = $this->GroupId;
				if ($option['is_under_group'])
					$groups = join(',', $this->plugin_groups->getChildrensId($groups));
				$wheregroup .= " AND id IN ($groups)";
			}

			// Создаем запросы для групп
			$nwhere = "((SELECT id FROM shop_group WHERE id=sp.id_group AND $wheregroup) IS NOT NULL";
			if (!empty($groups)) {
				$nwhere .=	" OR ((SELECT price_id FROM shop_group_price WHERE price_id=sp.id AND group_id IN ($groups) LIMIT 1) IS NOT NULL)".
							" OR ((SELECT group_id FROM shop_crossgroup WHERE group_id=sp.id_group AND id IN ($groups) LIMIT 1) IS NOT NULL)";
			}
			$nwhere .= ')';

			$where[] = $nwhere;
			
			// Использование фильтра
			if (isRequest('f', 1)) {
				$filter = new plugin_shopfilter();
				list($fjoin, $fwhere) = $filter->getSqlFiltered();
				if (!empty($fwhere)) {
					$where = array_merge($where, $fwhere);
				}
				if (!empty($fjoin)) {
					foreach($fjoin as $fj) {
						if ($fj['type'] == 'left') {
							$price->leftjoin($fj['table'], $fj['on']);
						} 
						else {
							$price->innerjoin($fj['table'], $fj['on']);
						}
					}
				}
			}
			
			//передана строка поиска
			if (isRequest('q', 1)) {
				$query = trim(urldecode($_GET['q']));
				$query = htmlspecialchars_decode($query, ENT_QUOTES);
				$words = preg_split('/[\s,]+/', $query); 
				foreach($words as $word){
					$price->andWhere('(sp.name LIKE "%?%" OR sp.article LIKE "%?%")', $word);
				}
			}
			
			if (getRequest('brand', 3)){
				$price->innerjoin('shop_brand sb', 'sb.id=sp.id_brand');
				$price->andWhere('sb.code="?"', getRequest('brand', 3));
			}
			
			if (!empty($where)) {
				foreach ($where as $w) {
					$price->andWhere($w);
				}
			}
		} 
		else {
			$price->andWhere('sp.id IN (?)', $goods_name);
		}
		
		if ($option['in_stock'])
			$price->andWhere($this->checkSqlCount());
		
        if (!$on_count) {
        //сортировка по полям
			if (!empty($this->sortval)) {
				$sortval = $this->sortval;
				list($field,$asc) = $this->getSortByField();
			} else {
				list($field,$asc) = $option['sort'];              //первичная сортировка
				if(!$option['sort']){
					$field = 'name'; 
					$asc = '0';
				};
				switch ($field) {
					case 'name': { $tsort = 'n'; } break;
					case 'count': { $tsort = 'c'; } break;
					case 'id': { $tsort = 'r';} break;
					case 'price': { $tsort = 'p';} break;
					case 'vizits': { $tsort = 'l';} break;
					case 'R': { $tsort = 'a';} break;
					case 'article': { $tsort = 'a';} break;
					default: $tsort = 'n';
				}
				switch ($asc) {
					case '0': { $tasc = 'a'; } break;
					case '1': { $tasc = 'd';} break;
					default: $tasc = 'a';
				}
				$this->sortval =  $tsort.$tasc;
			}
			if ($option['sort'][0] != 'R') { 
				$order[] = array($field, $asc);            
				$price->groupby('sp.id');
			}

			/*
			foreach ($order as $o) {
				list($field,$asc) = $o;
				$price->addorderby($field,$asc,false);
			}
			*/
			foreach ($order as $o) {
				list($field, $asc) = $o;
				if ($field == 'price')
					$price->addorderby('(sp.price * (SELECT m.kurs FROM `money` `m` WHERE m.name = sp.curr ORDER BY m.date_replace DESC LIMIT 1))', $asc); 
				else if ($field == 'count')
					$price->addorderby('presence_count_adopt', $asc);
				else
					$price->addorderby("`$field`", $asc);
			}
			
			if (!$option['limit']) {
				$option['limit'] = 30;
			}
		}
		
		if ($on_count) {
		    return $price->getListCount();
		}
		
		$SE_NAVIGATOR = $price->pageNavigator($option['limit']);
		$pricelist = $price->getList();
		
		if (!empty($pricelist)) {
			foreach ($pricelist as $key => $val) {
				list($pricelist[$key]['img'], $pricelist[$key]['img_alt']) = explode('##', $val['img']);
			}
		}
		
		//echo se_db_error();
		//if (isRequest('getsql'))
		//	echo $price->getSql();
        return  array($pricelist, $SE_NAVIGATOR);	
	}
	
	private function checkSqlCount(){
		return '(((sp.presence_count<>0 OR sp.presence_count IS NULL) AND (SELECT 1 FROM shop_modifications WHERE id_price = sp.id LIMIT 1) IS NULL) OR 
		(SELECT 1 FROM shop_modifications WHERE id_price=sp.id AND (`count` IS NULL OR `count`<>0) LIMIT 1)>0)';
	}
	
	private function getDiscountSql(){
        return '(SELECT GROUP_CONCAT(sd.id) FROM shop_discount sd
        WHERE sd.id_price=sp.id OR sd.id_group=sp.id_group OR sd.id_user='.seUserId().') as discounts'; 
	}
	
	private function getRatingSql(){
        return '(SELECT AVG(sr.mark) FROM shop_reviews sr WHERE sr.id_price=sp.id) AS rating, 
		(SELECT COUNT(sr.mark) FROM shop_reviews sr WHERE sr.id_price=sp.id) AS marks'; 
	}
	
	private function getBrandSql(){
        return '(SELECT CONCAT_WS("||", sb.name, sb.code, sb.image) FROM shop_brand sb WHERE sp.id_brand=sb.id LIMIT 1) AS brand';
	}
	
	private function getModificationsSql(){
        return '(SELECT 1 FROM shop_modifications sm WHERE sm.id_price=sp.id LIMIT 1) AS modifications';
	}

    private function getSpecialSql(){
        return '(SELECT ss.`newproc` FROM shop_special ss
        WHERE (ss.id_price=sp.id OR ss.id_group=sp.id_group) AND ss.date_added<=CURDATE() AND ss.expires_date>CURDATE() LIMIT 1) as spec_proc'; 
	}

    /** 
    *   @boolean old_format             какой формат используется(новый или старый)
    *   @string  group_name             код группы
    *   @return                      
    **/
    public function getRequestGroup($group_name = ''){
        $shopcatgr = '';
		$old_format=false;
		$md = SE_MULTI_DIR;
        if (get('shopcatgr')) {
            $shopcatgr = get('shopcatgr', 1);
            if ($old_format) {
                if ($shopcatgr) {
                    $tbl = new seTable("shop_group", "sg");
                    $tbl->select('code_gr, footertext');
                    $tbl->find($shopcatgr);
                    $catgr = $tbl->code_gr;
                    header('HTTP/1.1 301 Moved Permanently');
                    header("Location: $md/{$this->current_page}/cat/$catgr/");
                    exit;
                } else {
                    header('HTTP/1.1 301 Moved Permanently');
                    header("Location: $md/{$this->current_page}/");
                    exit;
                }
            }
        } else {
            if (get('cat'))
                $group_name = get('cat',3); 
            if ($group_name)
                $shopcatgr = $this->getGroupId($group_name, true);
        }     
        return $shopcatgr;
    }
   
  /** Получить id стартовой группы 
    *   @string  group_name         код стартовой группы
    *   @boolean ifFooter           надо ли получить текст подвала
    *   @return int                 код группы
    **/
    public function getGroupId($group_name, $ifFooter = false){
        if ($group_name) {
            $tbl = new seTable('shop_group', 'sg');
            if ($ifFooter)
                $tbl->select('id, footertext');
            else 
                $tbl->select('id');
            $tbl->where("sg.code_gr = '?'", $group_name);
            $tbl->fetchOne();
            if ($ifFooter) 
                $this->footertext = $tbl->footertext;        
            return $tbl->id;
        }
    }
   
    /** Выбирает id всех вложенных подгрупп группы $shopcatgr
    *   @int     shopcatgr          значение id группы, из которой выбираем подгруппы
    *   @boolean true               автоматически создается первый элемент
    *   @return string              список групп
    **/   
    public function getTreeShopGroup($shopcatgr,$first=false,$startline=0,$endline=0){
        $list = array();
        if($first && is_numeric($shopcatgr) && intval($shopcatgr)) $list[] = $shopcatgr;
        $shgroup = new seShopGroup();
        $shgroup->select('id');
    //    $shgroup->select('GROUP_CONCAT(id) as idlist');
        $shgroup->where('upid IN (?)', $shopcatgr);
        $shgroup->andwhere("active='Y'");
        $grlist = $shgroup->getList();
        $idlist = array();
        foreach($grlist as $item){
           $idlist[] = $item['id'];
        }
        if (!empty($idlist))
           $list = array_merge($list, $this->getTreeShopGroup(implode(',', $idlist)), $idlist);
        $list = array_filter($list);
        return $list;
    }

    /** Сортировка по полям в таблице витрины
    *   @return array           возвращается поле сортировки и направление сортировки
    **/
    public function getSortByField(){
        $sortval = $this->sortval;
        if($sortval=='R'){
            return array('name',0);
        }
        if(($sortval=='name')||($sortval=='id')||($sortval=='price')||($sortval=='article')){
            if(substr($sortval, 1, 1) == 'a'){
                return array($sortval,0);
            } else {
                return array($sortval,1);
            }
        }
        $asc = (substr($sortval, 1, 1) == 'a') ? '0' : '1';
        switch ($sortval) {
            case 'ga': $field = 'group_name'; break;
            case 'gd': $field = 'group_name'; break;
            case 'aa': $field = 'article'; break;
            case 'ad': $field = 'article'; break;
            case 'na': $field = 'name'; break;
            case 'nd': $field = 'name'; break;
            case 'ma': $field = 'manufacturer'; break;
            case 'md': $field = 'manufacturer'; break;
            case 'pa': $field = 'price'; break;
            case 'pd': $field = 'price'; break;
            case 'ca': $field = 'presence_count_adopt'; break;
            case 'cd': $field = 'presence_count_adopt'; break;
            case 'ra': $field = 'id'; break;
            case 'rd': $field = 'id'; break;
            case 'la': $field = 'vizits'; break;
            case 'ld': $field = 'vizits'; break;
        } 
        return array($field,$asc);   
    }
    
    /** Получить все комментарии
    *   @viewgoods int          id товара
    *   @return array           комментарии
    **/
    public function getGoodsComment($viewgoods=''){
        if($viewgoods=='') return;
        $comms = new seTable('shop_comm');
        $comms->where('id_price=?',  $viewgoods);
        $comms->orderby('id', 1);
        $commlist = $comms->getList();
        $flstyle = false;
        $a = 0;
        foreach ($commlist as $comm) {         
            $flstyle = !$flstyle;
            $commlist[$a]['style'] = ($flstyle) ? 'tableRowOdd' : 'tableRowEven';
            $commlist[$a]['date'] = date('d.m.Y', strtotime($comm['date']));
            @list($comments, $response) = explode('<%comment%>', $comm['commentary']); 
            if (empty($response))
                $response = $comm['response'];
            unset($comm['commentary']);
    
            $commlist[$a]['comment'] = str_replace("\r\n", '<br>', $comments);
            if (!empty($response)) {
                $commlist[$a]['adminnote'] = str_replace("\r\n", '<br>', $response);
            }
            $a++;
        }          
        return $commlist;
    }
	
    /** сохранить отзыв(комметарии)
    *   @viewgoods  int             id товара
    *   @comm_note  string          текст сообщения
    *   @admin      string          имя администратора
    **/
    public function saveGoodsComment($viewgoods = 0, $comm_note = '', $user_name) {           
        if (empty($viewgoods) || empty($comm_note)) return;
		$comments = new seTable('shop_comm');
        $comments->id_price = $viewgoods;
        if (seUserGroup()) {
            $person = new seTable('person');
            $person->select('email, last_name, first_name, sec_name');
            $person->find(seUserId());
            $comments->name = trim("{$person->last_name} {$person->first_name} {$person->sec_name}");
            $comments->email = $person->email;
            unset($person);
        } 
		else {
            $comments->name = $user_name;
        }
        $comments->commentary = $comm_note;
        $comments->date = date('Y-m-d', time());    
        return $comments->save();
    }
	
	public function getGoodsReviews($id_price, $offset = 0, $count = 10, $sort = 'date', $asc = false) {
		if (empty($id_price)) return;
		se_db_query('SET sql_mode = "NO_UNSIGNED_SUBTRACTION"');
		$shop_reviews = new seTable('shop_reviews', 'sr');
		$select = 'sr.id, 
			sr.likes, 
			sr.dislikes, 
			sr.use_time, 
			sr.id, 
			sr.mark, 
			sr.merits, 
			sr.demerits, 
			sr.comment, 
			sr.date,
			sr.id_user,
			concat_ws(" ", p.first_name, p.last_name) AS user_fullname,
			(SELECT su.username FROM se_user AS su WHERE su.id = sr.id_user) as user_name,
			(SELECT su.id FROM se_user AS su WHERE su.id = sr.id_user) as user_id';
		if ($user_id = (int)seUserId())
			$select .= ", (SELECT srv.vote FROM shop_reviews_votes AS srv WHERE srv.id_review = sr.id AND srv.id_user = $user_id) as user_vote";
		if ($sort == 'rating')
			$select .= ', CONVERT(likes - dislikes, SIGNED) as rating';
		$shop_reviews->select($select);
		$shop_reviews->innerjoin('person p', 'p.id=sr.id_user');
		$shop_reviews->where('sr.id_price = ?', $id_price);
		$shop_reviews->andWhere('sr.active <> 0');
		if ($sort == 'mark')
			$shop_reviews->orderBy('sr.mark', !$asc);
		elseif ($sort == 'helpful')
			$shop_reviews->orderBy('sr.likes', !$asc);
		elseif ($sort == 'rating')
			$shop_reviews->orderBy('rating', !$asc);
		else
			$shop_reviews->orderBy('sr.date', !$asc);
		$shop_reviews->addOrderBy('sr.id', 0);
		$list = $shop_reviews->getList($offset, $count);
		return $list;
	}
	
	public function getCountReviews($id_price) {
		$shop_reviews = new seTable('shop_reviews');
		$shop_reviews->select('COUNT(*) as count');
		$shop_reviews->where('id_price = ?', $id_price);
		$shop_reviews->fetchOne();
		return $shop_reviews->count;
	}
	
	public function saveReview($review, $id_price, $id_user) {
		if (!empty($id_price) && !empty($id_user)) {
			if (!empty($review['mark']) && !empty($review['comment']) && !empty($review['usetime'])) {
				if (!($review['mark'] >= 1 && $review['mark'] <= 5 && $review['usetime'] >= 1 && $review['usetime'] <= 3))
					return;
				$shop_reviews = new seTable('shop_reviews');
				$shop_reviews->insert();
				$shop_reviews->id_price = $id_price;
				$shop_reviews->id_user = $id_user;
				$shop_reviews->mark = $review['mark'];
				if (!empty($review['merits']))
					$shop_reviews->merits = $review['merits'];
				if (!empty($review['demerits']))	
					$shop_reviews->demerits = $review['demerits'];
				$shop_reviews->comment = $review['comment'];
				$shop_reviews->use_time = $review['usetime'];
				$shop_reviews->date = date('Y-m-d H:i:s');
				$shop_reviews->active = 1;
				return $shop_reviews->save();
			}
		}
		return;
	}
	
	public function voteReview($id_review, $id_user, $vote) {
		if ($id_review && $id_user) {
			$shop_reviews = new seTable('shop_reviews', 'sr');
			$shop_reviews->select('sr.likes, sr.dislikes');
			$shop_reviews->where('id=?', $id_review);
			$shop_reviews->andWhere('id_user <> ?', $id_user);
			$shop_reviews->fetchOne();
			if ($shop_reviews->isFind()) {
				$vote = ((int)$vote > 0) ? 1 : -1;
				$shop_reviews_votes = new seTable('shop_reviews_votes');
				$shop_reviews_votes->insert();
				$shop_reviews_votes->id_user = $id_user;
				$shop_reviews_votes->id_review = $id_review;
				$shop_reviews_votes->vote = (int)$vote;
				if ($shop_reviews_votes->save()) {
					$likes = $shop_reviews->likes;
					$dislikes = $shop_reviews->dislikes;
					if ($vote == 1) {
						$shop_reviews->update('likes', 'likes+1');
						$likes++;
					}
					else {
						$shop_reviews->update('dislikes', 'dislikes+1');
						$dislikes++;
					}
					$shop_reviews->where('id=?', $id_review);
					if ($shop_reviews->save())
						return array('likes' => (int)$likes, 'dislikes' => (int)$dislikes);
				}
			}
			
		}
	}
	
	public function isUserReview($id_price, $id_user) {
		if (!empty($id_price) && !empty($id_user)) {
			$shop_reviews = new seTable('shop_reviews');
			$shop_reviews->select('id');
			$shop_reviews->where('id_price = ?', $id_price);
			$shop_reviews->andWhere('id_user = ?', $id_user);
			$shop_reviews->fetchOne();
			return $shop_reviews->id;
		}
	}
	
	public function getGoodsId($code = '') {
		if (empty($code))
			$code = getRequest('show', 3);
		if (empty($code)) return;
		$shop_price = new seTable('shop_price', 'sp');
		$shop_price->select('id');
		$shop_price->where("code = '?'", $code);
		$shop_price->fetchOne();
		return $shop_price->id;
	}
    
    /** получить информацию о товаре
    *   @viewgoods  string              id товара
    *   @return  array                  информацию о товаре
    **/
    public function showGoodsDescription($goods_id='') {
        if($goods_id=='') return;
        $shop_price = new seTable('shop_price', 'sp');
		
		if ($this->version == '5.2')
			$img = '(SELECT si.picture FROM shop_img si WHERE si.id_price = sp.id ORDER BY si.`default` DESC, si.sort ASC LIMIT 1) AS img, ';
		else
			$img  = 'sp.img, ';
		
		$shop_price->select('sg.name AS group_name, sp.id, sp.id_group, sp.title, sp.code, sp.name, sp.keywords, sp.description, sp.img_alt, sp.article, sp.note, sp.text, sp.measure, sp.price, sp.price_opt_corp, sp.price_opt, sp.curr, sp.discount, sg.code_gr, sp.presence_count,sp.presence, sg.name AS group_name, ' . $img . $this->getBrandSql() . ',' . $this->getRatingSql() . ',' . $this->getModificationsSql());

		$shop_price->innerjoin('shop_group sg','sp.id_group = sg.id');
		$shop_price->where('sp.id=?', $goods_id);
		$goods = $shop_price->fetchOne();

		$name = htmlspecialchars($goods['name']);
        $goods['title'] = (trim($goods['title'])) ? htmlspecialchars($goods['title']) : $name;
		$goods['keywords'] = (trim($goods['keywords'])) ? htmlspecialchars($goods['keywords']) : $name;
		$goods['description'] = (trim($goods['description'])) ? htmlspecialchars($goods['description']) : htmlspecialchars($goods['note']);
		$goods['img_alt'] = (trim($goods['img_alt'])) ? htmlspecialchars($goods['img_alt']) : $name;
        return $goods;
    }
    
    /** Подсчет кол-ва посещении группы
    *   @id_visit_group string              id группы
    *   @return                             сохранение данных БД
    **/
    public function countVizit($id_visit_group='') {
        /*
		if($id_visit_group=='') return;
        $visits = new seShopGroup();
        $visits->update('visits', '`visits`+1');
        $visits->where('id=?', $id_visit_group);
        return $visits->save();    
		*/
    }
    
    /** Подсчет кол-ва просмотра товара
    *   @id_goods   string                  id товара
    *   @return                             сохранение данных БД
    **/
    public function countGoodsVizit($id_goods='') {
        if($id_goods=='') return;
        /*if(!se_db_is_field('shop_price', 'vizits')){
            se_db_add_field('shop_price', 'vizits', 'INT( 10 ) UNSIGNED NOT NULL AFTER `unsold`');        
        }*/ //Роман Кинякин: включить в апдейт - лишний запрос постоянный
		$this->setNowLooks($id_goods);
		$this->setUserLooks($id_goods);		
        $visits = new seTable('shop_price');
        $visits->update('vizits', '`vizits`+1');
        $visits->where('id=?', $id_goods);
        return $visits->save();
    }

    /** Увеличение кол-ва голосов и сохранение голоса за товар
    *   @viewgoods int                       id товара
    *   @vote string                         кол-во голосов
    *   @return int                          возвращает кол-во голосов за товар
    **/
    public function GoodsVotes($viewgoods='', $vote='') {
        if($viewgoods=='') return;
        $prc = new seTable('shop_price');
        $prc->select('votes');
		$prc->find($viewgoods);
        if($vote=='') return $prc->votes;   //получить голос
        $_SESSION['VOTED'][$viewgoods] = 1; // Признак того, что пользователь уже проголосовал
        $votes = $prc->votes + $vote;
        $prc->update('votes', "'{$votes}'");
        $prc->where('id=?', $viewgoods);
        $prc->save();
        return $votes;
    }  
    
    /** Выбор похожих и сопутствующих товаров
    *   @option array                           начальные параметры
    *   @viewgoods int                          кол-во голосов
    *   @types string                           название таблицы shop_sameprice или shop_accomp
    *   @return array                           возвращает массив товаров
    **/
	public function sameGoods($option, $viewgoods, $types) {
		$same = new seTable('shop_price', 'sp');
		$same->select('sp.id');
		$same->where("`id` IN (SELECT id_acc from $types WHERE `id_price`='$viewgoods')");
		if ($types == 'shop_sameprice') {
			$same->orWhere("`id` IN (SELECT id_price from $types WHERE `id_acc`='$viewgoods')");
		}
		$samegoods = $same->getList();    
		$rez = '';
		if (!empty($samegoods)) {
			foreach($samegoods as $item){
				if($rez == ''){
					$rez .= $item['id'];
				} 
				else {
					$rez .= ',' . $item['id'];
				}
			}
		}
		if($rez == '') return;      
		$samegoods = $this->getGoods($option, $rez);
		return $samegoods;
	}	

    public function viewgalleryImages($price_id, $size_prev, $size_mid, $size_full, $res = 's', $watermark = '', $position = 'center', $waterquality = 75) {
        $images = array();
        $shop_img = new seTable('shop_img');
        $shop_img->select('`id`, `picture`, `title`, `picture_alt`, `default`'); 
        $shop_img->where('id_price=?', $price_id);
        $shop_img->orderBy('sort', 0);
		$imglist = $shop_img->getList(); 
		if(!empty($imglist)) {
			foreach($imglist as $val) {
				$image = array();
				if($val['picture']!='') {
					list($image['image'], $nofoto) = $this->getGoodsImage($val['picture'], $size_full, $res, $watermark, $position, $waterquality);
					if ($nofoto) continue;
					$image['title'] = $val['title'];
					$image['alt'] = $val['picture_alt'];
					$image['id'] = $val['id'];
					list($image['image_prev'],) = $this->getGoodsImage($val['picture'], $size_prev, $res);
					list($image['image_mid'],) = $this->getGoodsImage($val['picture'], $size_mid, $res, $watermark, $position, $waterquality);
					$images[] = $image;
				}
			}  
        }
		return $images;
    }		
	
    public function getGoodsImage($imagename='', $size = 0, $res = 's', $watermark = '', $position = 'center', $waterquality = 75) {
		$im = new plugin_ShopImages();
        $imagename = $im->getPictFromImage($imagename, $size, $res, $watermark, $position, $waterquality);
        $nofoto = (bool)(end(explode('/', $imagename))=='no_foto.gif');
        return array($imagename, $nofoto);
    }
    
    /** Проверка есть ли аналогичные товары у данного товара
    *   @option     array                   начальные параметры 
    *   @viewgoods  string(int)             id товара
    *   return      boolean
    **/
    public function isSetGoodsAnalog($option, $viewgoods='') {
        if (empty($viewgoods)) return;
        $analog = new seTable('shop_sameprice');
        $analog->select('id, id_price');
        $analog->where('id_price=?', $viewgoods);
        $good = $analog->getList();
        $rez = '';
        foreach($good as $item){
            if($rez==''){
                $rez .= $item['id'];
            } else {
                $rez .= ",".$item['id'];
            }
        }
        $isAnalog = false;                                       
        if($rez=='') return $isAnalog;
        $e = array();      
        $e = $this->getGoods($option, $rez);
        if(($e!='')&&(!empty($e))) $isAnalog = true;
        return $isAnalog;
    }
	
	/**
	 * Добавление/обновление таблицы 'Сейчас смотрят'
	 *
	 * @param integer $id_price	 id товара
	 * @param integer $period    время(сек) в течение которого товар считается просматриваемым
	 */
	public function setNowLooks($id_price, $period = 600) {
        if (!$id_price) return;
        /*
        $sql = "CREATE TABLE IF NOT EXISTS `shop_nowlooks` (
            `id` int(10) unsigned NOT NULL,
            `time_expire` int(11) NOT NULL,
            `count_looks` int(10) unsigned default NULL,
            `updated_at` timestamp NOT NULL default '0000-00-00 00:00:00' on update CURRENT_TIMESTAMP,
            `created_at` timestamp NOT NULL default '0000-00-00 00:00:00',
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        se_db_query($sql);
        */
        $time = time();
        
        $tbl = new seTable('shop_nowlooks');
        $tbl->where("time_expire < $time");
        $tbl->deletelist();
        
        if($tbl->find($id_price)){
            $tbl->time_expire = $time + $period;
            $tbl->count_looks++;
            $tbl->save(); 
        }
        else{
            $tbl->insert();
            $tbl->id = $id_price;
            $tbl->time_expire = $time + $period;
            $tbl->count_looks = 1;
            $tbl->save();
        }
    }
    
	/**
	 * Установить товар который просмотрел пользователь ("Вы смотрели")
	 * @param integer $id_price	           id товара
	 * @param unix time integer $expire    время до которого товар остается просмотренным пользователем
	 */
    public function setUserLooks($id_price, $expire = '-1') {
        if (!$id_price) return;
        if ($expire == -1){
            $expire = time() + 604800;//
        }
        if (!empty($_SESSION["look_goods"][$id_price])) {
            $looks = $_SESSION["look_goods"][$id_price];
            $arr = explode('#', $looks);
            (int)$arr[1]++;
            $looks = time().'#'.$arr[1];
            $_SESSION["look_goods"][$id_price] = $looks;
        } 
        else {
            $_SESSION["look_goods"][$id_price] = time().'#1';
        } 
        if (isset($_COOKIE["look_goods"][$id_price])){
            $looks = $_COOKIE["look_goods"][$id_price];
            $arr = explode('#', $looks);
            (int)$arr[1]++;
            $looks = time().'#'.$arr[1];
        } else {
                $looks = time().'#1';
        }
        setcookie("look_goods[$id_price]", $looks, $expire, "/");
    }

	public function getUserLooks($price_id = 0, $lastcount = 0) {
        if (isset($_SESSION['look_goods'])){
            $list = array();
            $spec = $_SESSION['look_goods'];
            foreach ($spec as $key => $val){
				if ($price_id == $key) continue;
                $arr = explode('#', $val);
				$list[$key] = (int)$arr[0] + (int)$arr[1] * 60;    
            }
            arsort($list);
			$list = array_keys(array_slice($list, 0, $lastcount, true));
			if ($lastcount > 0) {
				array_splice($list, 0, -$lastcount);
			}
            return join(',', $list);
		}
	}
	
	private function replaceSufix($name) {
		$name = preg_replace("/\s+/ui", ' ', trim($name));
		$names = explode(' ', $name);
		$result = '';
		foreach($names as $name) {
			if (strpos($name, '«') === false && strpos($name, '"') === false && utf8_strlen($name) > 3) {
				$name = preg_replace("/(ая)$/ui", 'ую', $name);
				$name = preg_replace("/(яя)$/ui", 'юю', $name);
				$name = preg_replace("/(а)$/ui", 'у', $name);
				$name = preg_replace("/(я)$/ui", 'ю', $name);
			}
			$result .= $name . ' ';
		}
		return trim($result);
	}

	//** Парсим пользовательскую маску
	public function parseUserMask($mask, $goods) {
		$in = array(
			0 => '{name}',
			1 => '{название товара}',
			2 => '{asname}',
			3 => '{brand}',
			4 => '{производитель}',
			5 => '{price}',
			6 => '{цена}',
			7 => '{new price}',
			8 => '{новая цена}',
			9 => '{old price}',
			10 => '{старая цена}',
			11 => '{discount}',
			12 => '{скидка}',
			13 => '{description}',
			14 => '{описание товара}',
			15 => '{features}',
			16 => '{характеристики}',
			17 => '{article}',
			18 => '{артикул}',
			19 => '{note}',
			20 => '{краткое описание}',
			21 => '{title}',
			22 => '{заголовок}',
			23 => '{keywords}',
			24 => '{ключевые слова}'
		);
		
		if (strpos($mask, '{features}') !== false || strpos($mask, '{характеристики}') !== false) {
			if ($this->string_features === null)
				$this->string_features = $this->getStringFeatures($goods['id']);
		}
		$goods['features'] = $this->string_features;
		
		if (!isset($goods['old price']))
			$goods['old price'] = ''; 
		
		if (!(strpos($mask, '{name}') === 0 || strpos($mask, '{название товара}') === 0 || strpos($mask, '{asname}') === 0))
			$goods['name'] = utf8_strtolower($goods['name']);
		
		$out = array(
			0 => $this->replaceSufix($goods['name']),
			1 => $this->replaceSufix($goods['name']),
			2 => $goods['name'],
			3 => $goods['brand'], 
			4 => $goods['brand'],
			5 => $goods['price'], 
			6 => $goods['price'],
			7 => $goods['new price'], 
			8 => $goods['new price'],
			9 => $goods['old price'], 
			10 => $goods['old price'],
			11 => $goods['discount'], 
			12 => $goods['discount'],
			13 => $goods['description'], 
			14 => $goods['description'],
			15 => $goods['features'], 
			16 => $goods['features'],
			17 => $goods['article'],
			18 => $goods['article'],
			19 => $goods['note'], 
			20 => $goods['note'],
			21 => $goods['title'], 
			22 => $goods['title'],
			23 => $goods['keywords'], 
			24 => $goods['keywords']
		);
		while (preg_match("/\[(.+)\]/", $mask, $m)) {
			if (preg_match("/(\{[^\}]+\})/", $m[1], $mm)) {
				$mm[1] = str_replace($in, $out, $mm[1]);
				if (!$mm[1]) $m[1] = '';
			}
			$mask = str_replace($m[0], $m[1], $mask);
		}
		
		$result = str_replace($in, $out, $mask);
		return htmlspecialchars(trim(str_replace('&nbsp;',' ', strip_tags(htmlspecialchars_decode($result)))));
	}
	
	public function getBrandImage($image) {
		$path_image = '/images/' . $this->lang . '/shopbrand/' . $image;
		if (!file_exists(SE_ROOT . $path_image))
			$path_image = '';
		return $path_image;
	}

	// хлебные крошки
	public function getPathGroup($id_group) {
        $dt = array();	
		
		$parents = $this->plugin_groups->getParents((int)$id_group, true);
		
		if (!empty($parents)) {
			foreach ($parents as $val) {
				$dt[] = array(
					'cat' => $val['code'],
					'cat_nm' => $val['name']
				);
			}
		}
		
        return $dt;
	} 
	
}