<?php

class plugin_shopdiscount53 {
	//v5.2
	private static $instance = null;
	private $discounts = null;
	private $id_user;
	private $user_discounts = array();
	private $product_discounts = array();
	private $group_discounts = array();
	private $order_summ = null;
	
	public function __construct() {
		$this->id_user = seUserId();
		
		$this->getDiscounts();
    }
	
	public static function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	private function getSumUserOrders() {
		if (is_null($this->order_summ) && !empty($this->id_user)) {
			$shop_order = new seTable('shop_order', 'so');
			$shop_order->select('SUM((SELECT SUM((st.price - st.discount) * st.count) FROM shop_tovarorder st WHERE st.id_order = so.id) - so.discount) as order_summ');
			$shop_order->where('so.id_author=?', $this->id_user);
			$shop_order->andWhere('so.status="Y"');
			$shop_order->fetchOne();
			
			$this->order_summ = (float)$shop_order->order_summ;
		}
		return $this->order_summ;
	}
	
	private function getDiscounts() {
		
		$date = date('Y-m-d H:i:s');
		$day_week = date('w');
		
		if ($day_week == 0)
			$day_week = 7;

		se_db_query('SET group_concat_max_len = 16096;');
		
		$shop_discounts = new seTable('shop_discounts', 'sd');
		$shop_discounts->select('
			sd.id, 
			sd.title, 
			sd.step_time, 
			sd.step_discount, 
			sd.date_from, 
			sd.date_to, 
			sd.summ_from, 
			sd.summ_to, 
			sd.count_from, 
			sd.count_to,
			sd.discount,
			sd.type_discount,
			sd.summ_type,
			GROUP_CONCAT(DISTINCT sdl.id_price) AS products,
			GROUP_CONCAT(DISTINCT sdl.id_group) AS groups,
			GROUP_CONCAT(DISTINCT sdl.id_user) AS users
		');
		$shop_discounts->where("((sd.date_from <= '?' OR sd.date_from IS NULL) AND (sd.date_to >= '?' OR sd.date_to IS NULL))", $date);
		$shop_discounts->andWhere('(SUBSTRING(sd.week, ?, 1) > 0 OR sd.week IS NULL)', (int)$day_week);
		
		$shop_discounts->innerJoin('shop_discount_links sdl', 'sdl.discount_id=sd.id');
		$shop_discounts->andWhere('(sdl.id_price IS NOT NULL OR sdl.id_group IS NOT NULL OR sdl.id_user = ?)', (int)$this->id_user);
		
		$shop_discounts->groupBy('sd.id');
		
		if ($shop_discounts->isFindField('sort'))
			$shop_discounts->orderBy('sd.sort');
		
		$all_discounts = $shop_discounts->getList();
		//echo $shop_discounts->getSql();
		$discounts = array();
		
		if (!empty($all_discounts)) {
			$time = time();
			foreach($all_discounts as $val) {
				if (!empty($val['step_time']) && !empty($val['step_discount']) && !empty($val['date_from'])) {
					$count_step =  floor(($time - strtotime($val['date_from'])) / ($val['step_time'] * 3600));
					$val['discount'] = $val['discount'] + $count_step * $val['step_discount'];
				}
				if ($val['type_discount'] == 'percent' && $val['discount'] > 100) {
					$val['discount'] = 100;
				}
				if ($val['discount'] > 0) {
					$this->saveDiscount($val);				
				}
			}
		}
		if (!empty($discounts)) {
			//print_r($discounts);

			$list_d = join(',',array_keys($discounts));
		}
		
		return $discounts;
	}
	
	private function saveDiscount($discount) {
		$id_discount = $discount['id'];
		$this->discounts[$id_discount] = array(
			'title' => $discount['title'],
			'summ_from' => $discount['summ_from'],
			'summ_to' => $discount['summ_to'],
			'count_from' => $discount['count_from'],
			'count_to' => $discount['count_to'],
			'value' => $discount['discount'],
			'type' => $discount['type_discount'],
			'summ_type' => $discount['summ_type'],
			'date_from' => $discount['date_from'],
			'date_to' => $discount['date_to']
		);
		
		if (!empty($this->id_user) && !empty($discount['users']) && in_array($this->id_user, explode(',', $discount['users']))) {
			$this->user_discounts[] = $id_discount;
		}
		
		if (!empty($discount['products'])) {
			$links = explode(',', $discount['products']);
			foreach($links as $val) {
				$this->product_discounts[$val][] = $id_discount;
			}
		}
		
		if (!empty($discount['groups'])) {
			$links = explode(',', $discount['groups']);
			foreach($links as $val) {
				$this->group_discounts[$val][] = $id_discount;
			}
		}
	}
	
	public function getDiscount($product, $summ = 0, $count = 0) {
		if (empty($this->discounts))
			return;
		
		if (!empty($this->id_user) && !empty($this->user_discounts)) {
			$id_discount = $this->eachDiscount($this->user_discounts, $summ, $count);
		}
		if (empty($id_discount) && !empty($this->product_discounts[$product['id']])) {
			$id_discount = $this->eachDiscount($this->product_discounts[$product['id']], $summ, $count);
		}
		if (empty($id_discount) && !empty($this->group_discounts[$product['id_group']])) {
			$id_discount = $this->eachDiscount($this->group_discounts[$product['id_group']], $summ, $count);
		}
		
		if (!empty($id_discount))
			return $this->discounts[$id_discount];
		else
			return;
	}
	
	private function eachDiscount($discount_links = array(), $summ = 0, $count = 0) {
		$id_discount = 0;
		foreach($discount_links as $val) {
			$discount = $this->discounts[$val];

			if ($discount['summ_type'] == 1){
				$summ = $this->getSumUserOrders();
			}
			elseif ($discount['summ_type'] == 2) {
				$summ += $this->getSumUserOrders();
			}
			
			$check = ($discount['summ_from'] == 0 || $discount['summ_from'] <= $summ) && ($discount['summ_to'] == 0 || $discount['summ_to'] >= $summ);

			$check &= ($discount['count_from'] == 0 || $discount['count_from'] <= $count) && ($discount['count_to'] == 0  || $discount['count_to'] >= $count);
			
			if ($check) {
				$id_discount = $val;
				break;
			}
		}
		return $id_discount;
	}
	
}