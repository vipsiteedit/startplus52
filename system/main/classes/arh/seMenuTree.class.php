<?php

class seMenuTree {
  
  public function execute($menulist)
  {
  		$oldLevel = 1;
  		$it = 0;
  		$thismenu = array();
  		$tmpLevel = array(-1, -1, -1, -1, -1, -1);
		$tmpNode = array();

		if (!empty($menulist))
		foreach($menulist as $namevalue=>$value)
		{
			$value = explode('|',$value);
			$level = $value[0];
        		if ($level > $oldLevel)
        		{
				$tmpLevel[$level] = -1;
        		}
        	
          		// ���� ���� ������� ���� ����������
        		if ($level < $oldLevel)
        		{
				$tmpLevel[$oldLevel] = -1;
        		}

        		if ($level == 1) {
        			$tmpLevel[$level]++;
        			$tmpNode[$level] = &$thismenu[$tmpLevel[$level]];
        			$tmpNode[$level] = $this->getMenuParse($value);
        		} 
			else
			{ 
        			$tmpLevel[$level]++;
        			$tmpNode[$level] = &$tmpNode[$level-1]->item[$tmpLevel[$level]];
				$tmpNode[$level] = $this->getMenuParse($value);
			}

          		$oldLevel = $level;
		}
		
		return $thismenu;
  }

  private function getMenuParse($value)
  {
    $thismenu = new stdClass;
			$thismenu->name = (!empty($value[1])) ? $value[1] : '';
			$thismenu->title = $value[3];
			$value[2] = (!empty($value[2])) ? $value[2] : '';
			@list($url, $target) = explode(chr(8), $value[2]); 
			if (!preg_match("/\b(http|https):/", $url)) $url = '/'. $url;
			$thismenu->url = $url;
			$thismenu->target = str_replace('target=', '', $target);
			$thismenu->level = $value[0];
			$thismenu->access = $value[9];
			@list($img, $imgA) = explode(chr(8), $value[7]);
			$thismenu->image = $img;
			if (empty($imgA)) $imgA = $img;
			$thismenu->imageactive = $imgA;
			//$thismenu->node = array();
			
			return $thismenu;
  }

}
?>