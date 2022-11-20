<?php

class plugin_shopamount53 {
	
	private $curr = '';
	private $count = 0;
	private $price = 0;
	private $step = 1;
	public $sum_cart = 0;
	private $discount = null;

  /**
   * @param integer $user_id	ID пользователя
   * @peram byte $typprice	Тип цены (0- Розничная, 1- корпоративная, 2- оптовая)
   * @param char $basecurr	Базовая валюта
   * @param array $goods	item good with fields (id,price,price_opt_corp,price_opt,bonus,discount,special_price,curr,presence_count,presence,measure)
   **/
    public function __construct($price_id, $goods = '', $typprice = 0, $count = 1, $modifications = '', $basecurr = '', $in_stock = true) {
		
		setlocale(LC_NUMERIC, 'C');

		$this->curr = empty($basecurr) ? se_getMoney() : $basecurr;
		
		$this->count = $count;
		if (empty($goods)) {
			$shopprice = new seShopPrice();
			$shopprice->select('id, price, id_group, price_opt_corp, price_opt, discount, max_discount, curr, presence_count, presence, step_count, measure, (SELECT 1 FROM shop_modifications sm WHERE sm.id_price=sp.id LIMIT 1) AS modifications');
			$this->goods = $shopprice->find($price_id);
			$this->price = $this->goods['price'];
        }
        else {
            $this->goods = $goods;
        }  
        $select = 'sm.value';
        $this->price = $this->goods['price'];
        if (2 == $typprice) {
            $this->price = $this->goods['price_opt_corp'];
            $select = 'sm.value_opt_corp AS value';
		} 
		elseif (1 == $typprice) {   
            $this->price = $this->goods['price_opt'];
            $select = 'sm.value_opt AS value';
		}
		$this->presence_count = 0;
		if (!empty($modifications)) {
			if (is_array($modifications))
				$modifications = join(',', $modifications);
			$shop_modifications = new seTable('shop_modifications', 'sm');
			$shop_modifications->select('sm.count, smg.vtype, ' . $select);
			$shop_modifications->innerJoin('shop_modifications_group smg', 'sm.id_mod_group = smg.id');
			$shop_modifications->where('sm.id IN (?)', $modifications);
            $shop_modifications->andWhere('sm.id_price=?', $this->goods['id']);
            $list = $shop_modifications->getList();
			if (!empty($list)) {
				$this->presence_count = -1;
				$sum_price = 0;
				foreach($list as $val) {
					if ($val['count'] === '0' || $val['count'] === '0.000') {
						$this->presence_count = 0;
					}
					elseif ($this->presence_count != 0) {
						if ($val['count'] > 0 && ($this->presence_count > $val['count'] || $this->presence_count < 0))
							$this->presence_count = $val['count'];
					}
            
					if ($val['vtype'] == '0') {
						$sum_price += ($this->price + $val['value']);  
					}
					elseif($val['vtype'] == '1') {
						$sum_price += ($this->price * $val['value']);
					}
					else {
						$sum_price += $val['value'];
					}
				}
				$this->price = $sum_price;
			}
		} 
		elseif (!empty($this->goods)) {
			$this->presence_count = ($this->goods['presence_count'] == '' || $this->goods['presence_count'] == -1) ? -1 : $this->goods['presence_count'];
		}
		if ((float)$this->goods['step_count'] > 0)
			$this->step = (float)$this->goods['step_count'];
		
		if ((float)$count > 0)
			$this->count = $this->stepRound($count, $this->step);
		else
			$this->count = $this->step;
		
		if (!$in_stock) {
			$this->presence_count = -1;
		}
		
		if ($this->presence_count >=0) {
			$this->presence_count = ($this->presence_count < $this->step) ? 0 : $this->stepRound($this->presence_count, $this->step);
			if ($this->count > $this->presence_count)
				$this->count = $this->presence_count;
		}
			
		$this->price = round(se_MoneyConvert($this->price, $this->goods['curr'], $this->curr), 2);
    }
	
	private function stepRound($count, $step) {
		return floor(round($count / $step, 4)) * $step;
	}

    public function getDiscountProc($round = true) {
        if (is_null($this->discount)) {
			$this->discount = 0;
			if ($this->goods['discount'] == 'Y') {
				$shopdiscount = plugin_shopdiscount53::getInstance();
				$discount = $shopdiscount->getDiscount($this->goods, $this->sum_cart, $this->count);
				if (!empty($discount)) {
					if ($discount['type'] == 'percent')
						$this->discount = $discount['value'];
					elseif (!empty($this->price)) {
						$value = $discount['value'] / $this->price * 100;
						$this->discount = min($value, 100);
					}
					$max_discount = (float)$this->goods['max_discount'];
					if ($max_discount > 0)
						$this->discount = min($this->discount, $this->goods['max_discount']);
				}
			}
		}
		return ($round) ? round($this->discount) : $this->discount;
    }
	
	public function getDiscountDate() {
		if ($this->goods['discount'] == 'Y') {
			$shopdiscount = plugin_shopdiscount53::getInstance();
			$discount = $shopdiscount->getDiscount($this->goods, $this->sum_cart, $this->count);
			return array('start' => $discount['date_from'], 'end' => $discount['date_to']);
		}
	}

	// Получаем скидку товара
    public function getDiscount() {
		if (!empty($this->goods['bonus']) && $this->goods['bonus'] > 0) {
			$goodsprice = se_MoneyConvert($this->goods['bonus'], $this->goods['curr'], $this->curr);
		} 
		else {
			$goodsprice = $this->price;
		}
		$discountproc = $this->getDiscountProc(false);
		
		return round($goodsprice * ($discountproc / 100), 2);
    }
	
	// Получаем комбинированную цену учитывая параметры и скидки
    public function getPrice($discounted = true) {
        return $this->price - ($discounted ? $this->getDiscount() : 0);
    }  

    public function getAmount($discounted = true) {
		return round($this->getPrice($discounted) * $this->count, 2);
    }

    // Показать стоимость $round - округлить, $space - разделитель
    public function showPrice($discounted = true, $round = false, $separator = '&nbsp;') {
		$price = $this->getPrice($discounted);
        $price = $round ? ceil($price) : $price;
		return se_formatMoney($price, $this->curr, $separator, $round);
    }

    public function showAmount($discounted = true, $round = false, $separator = '&nbsp;') {
		$price = $this->getPrice($discounted) * $this->count;
        $price = $round ? ceil($price) : $price;
		return se_formatMoney($price, $this->curr, $separator, $round);
    }

    public function showDiscount($round = false, $separator = '&nbsp;') {
		$discount = $this->getDiscount();
        $discount = $round ? floor($discount) : $discount;
		return se_formatMoney($discount, $this->curr, $separator, $round);
    }

    // Возврашает текст доступного количества
    public function showPresenceCount($not_available='', $in_stock='') {
		$pcount = $this->presence_count;
        if ($this->presence_count >= 0) {
            if ($this->presence_count == 0) {
                $pcount = !empty($this->goods['presence']) ? $this->goods['presence'] : $not_available;
            }
			else {
                $pcount .= '&nbsp;' . $this->goods['measure'];
            }
        }
		else {
            $pcount = !empty($this->goods['presence']) ? $this->goods['presence'] : $in_stock;
        }
        return $pcount;
    }

    // Получаем актуальное количество
    public function getActualCount() {
		return $this->count;
    }
   
    public function getPresenceCount() {
		return $this->presence_count;
    }
	
	public function getStepCount() {
		return $this->step;
    }
}