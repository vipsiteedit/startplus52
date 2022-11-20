<?php

/**
 * Виджет для работы с объектами в разделе страницы
 * @param	$razdel int	Номер текущего раздела
 * @param	$dataarr array 	двумерный массив данных
 * @param	$styles string - имена слассов для чередования (разделитель запятая)
 * @return string возвращатся форматированный текст списков объеков согласно структуре {OBJECT}
 */
 
 
if (!function_exists('se_show_fields')){
function se_show_fields($section, $dataarr, $styles = '', $recordcount = 0)
{

  $mstyles = array();
  if (!empty($styles)) 
  {
  	$mstyles = explode(',', $styles);	
  }
  // Максимальное число слассов оформления
  $cnstyle = count($mstyles);


  $cn = 0;
  unset($section->objects);
  //$section->objects = array();

  $nn = 0;
  if (!empty($dataarr))
  {
  	foreach ($dataarr as $linearr) 
    {
     		if ($cnstyle>0)
     		{
				if ($cn >= $cnstyle) $cn = 0;
      			if (isset($mstyles[$cn]))
	  			$getstyle = trim($mstyles[$cn]);
				$linearr['style'] = $getstyle;
      			$cn++;
	 		} else {
	    		$dataarr['style'] = '';
			}
			
			foreach($linearr as $nameval=>$value)
			{	
				if (!is_int($nameval))
				{
					$section->objects[$nn]->$nameval = $value;
				}
			}
			$nn++;
   }
  }
}}

function se_parse_object($object, $str)
{

	/*  preg_match_all("/<if (.*)>(.*)<\/if>/m", $str, $m);
	  if (!empty($m))
	  {
	  	foreach($m[0] as $id=>$mline)
	  	{
	  		$if = $m[1][$id];
	  		$value = $m[2][$id];
	  		eval("\$res = ($if)");
	  		if (!$res) $value = '';
	  		
			$str = str_replace($mline, $value, $str);
	  	}
	  	//return $str;
	  }*/

	  preg_match_all("/<if:record\.([\*\d\w]+)(==|!=)\"([\*\d\w]+)\">(.*)<\/if>/", $str, $m);
	  if (!empty($m))
	  {
	  	foreach($m[0] as $id=>$mline)
	  	{
		    $field = $m[1][$id];
	  		list($data1,) = explode('|', $object->text1);
	  		$data1 = str_replace(array("\"", "'"), '',$data1);
	  		$f = $m[2][$id];
	  		$data2 = str_replace(array("\"", "'"), '', $m[3][$id]);
	  		$value = $m[4][$id];
	  		if (($f == '==' && $data1 != $data2) ||($f == '!=' && $data1 == $data2)) $value = '';
			$str = str_replace($mline, $value, $str);
	  	}
	  }
	
	  while (preg_match("/\[textline\.(.+)\/textline\]/ims", $str, $m))
	  {
	  		//echo $object->note;
	  		$list = explode("\n", $object->note);
	  	    $value = '';
	  	    $i = 0;
	  		foreach($list as $line)
	  		{
	  			$maska = $m[1];
      			$line = str_replace('<br>', '', $line);
				if (substr($line, 0, 1) == '*')
      			{
      				$line = substr($line, 1);
        			$maska = str_replace(array('%SELECTED%','%CHECKED%'), array('SELECTED', 'CHECKED'), $maska);
      			}
      			else
      			{
        			$maska = str_replace(array('%SELECTED%','%CHECKED%'), '', $maska);
      			}

      			$textvar = explode("%%", $line);
      			@$maska = str_replace(array('@textline','@textlineval','@textline_num'),
				   array($textvar[0], $textvar[1],$i), $maska);
	  			$value .= $maska;
	  			$i++;
	  		}
			$str = str_replace($m[0], $value, $str);
	  } 
	  	return $str;
}

/**
 * Виджет для работы со списками данных
 * @param	$fieldarray array 	двумерный массив данных 
 * @param	$maska 	string	макет строки объекта
 * @param	$itemtext string значение для выбора активной позиции в списке
 * @param	$itemfield int	номер позиции в поле select для выбора активной позиции в списке
 * @param	$fieldreplace string - строка замены одного значения на другое в указанной колонке массива [Колонка, Найти, Заменить]
 * @param	$styles string - имена слассов для чередования (разделитель запятая)
 * @return string возвращатся форматированный текст списков объеков согласно структуре $mask
 */
function se_fields_list($fieldarray, $maska, $itemtext='', $itemfield=-1, $fieldreplace='', $styles='')
{
  global $itemselect;
  $tabvalue = '';
  $fieldrepl = explode(';', $fieldreplace);
  $itemselect = -1;
  $mstyles = array();
  $getstyle = '';

  if (!empty($styles)) 
  {
  	$mstyles = explode(",", $styles);	
  }
  // Максимальное число слассов оформления
  $cnstyle = count($mstyles);

    $nn = $cn = 0;

    if (!empty($fieldarray))
    foreach($fieldarray as $tabl)
	{
		 if (!empty($fieldreplace))
	      foreach($fieldrepl as $linerepl)
		  {
           	list($rItem, $rSearh, $rRepl) = explode(',', $linerepl);
           	$tabl[$rItem]= str_replace($rSearh, $rRepl, $tabl[$rItem]);
   	      }

      $fmas = $maska;
      $fmas = str_replace("\r\n\r\n", "", se_parse_object($tabl, $fmas));
      
      if ($cn >= $cnstyle) $cn = 0;
      if (isset($mstyles[$cn]))
	  	$getstyle = trim($mstyles[$cn]);
      $cn++;
 
	  $selected = $checked = ''; 
	  if (isset($tabl[$itemfield]) && $itemfield > -1 && $itemtext == $tabl[$itemfield]) 
	  {
       	$selected = "SELECTED"; 
       	$checked = "CHECKED"; 
	   	$itemselect=@$tabl[$itemfield];
      }

      unset($m);
	  preg_match_all("/\[\@col_?([\w\d\.\-_]+)\]/m", $fmas, $m);
	  if (!empty($m))
	  {
	  	foreach($m[0] as $id=>$mline)
	  	{
	  		//echo $id.'!';
			$field = $m[1][$id];
			$data = $tabl->$field;
			$fmas = str_replace($mline, $data, $fmas);
	  	}
	  }
/*
      if (!empty($tabl))
	  foreach($tabl as $field=>$dline)
	  {
		if (preg_match("/\[\@col_?$field\]/m", $fmas, $m))
		{
		  	$fmas = str_replace($m[0], $dline, $fmas);
 		}
      }
*/      
      unset($m);
	  preg_match_all("/<noempty:record\.([\w\d\.\-_]+)>(.*)<\/noempty>/m", $fmas, $m);
	  if (!empty($m))
	  {
	  	foreach($m[0] as $id=>$mline)
	  	{
	  		$field = $m[1][$id];
	  		$value = $m[2][$id];
	  		if ($tabl->$field == '') $value = '';
	  		
			$fmas = str_replace($mline, $value, $fmas);
	  	}
	  }
      
      unset($m);
	  preg_match_all("/\[record\.([\w\d\.\-_]+)\]/m", $fmas, $m);
	  //print_r($m[0]);
	  if (!empty($m))
	  {
		foreach($m[0] as $id=>$mline)
	  	{
			$field = $m[1][$id];
	  		$data = '';
			if ($field == 'row')
	  		{
	  			$data = $nn;
	  		} 
			else
	  		{
	  			$value = '';
			  	if ($tabl->$field!='')
					$value = $tabl->$field;
				
				if ($field == 'field' && strpos($value, '.html'))
				{
					list($value,) = explode('.html', $value);	
				}	
		  		$data = str_replace('\n', "\n", $value);
	  		}
			$fmas = str_replace($mline, $data, $fmas);
	  	}
	  }
      
      
 
	  $fmas = str_replace(array('[@row]','[@select]','[@selected]','[@checked]','[@style]'),
	  			array($nn+1, $selected, $selected, $checked, $getstyle), $fmas);


	  $tabvalue .= preg_replace("/(\[@col)[_\d\w]{1,}\]/m", '', $fmas) ."\r";	

      unset($fmas);
      $nn++;
     }

   return $tabvalue;
}

function se_record_list($section, $maska)
{
	$_item = 1;
	$recordcount = intval($section->objectcount);
	if ((!isRequest('arhiv') && !getRequest('object', 1)) || $section->id > 1000)
	{
  // Разделитель страниц при большом числе объектов
     if ($recordcount)
     {
     	if (isRequest('item') && intval($section->id) == getRequest('razdel', 1))
     	{
			$_item = getRequest('item', 1);
			$itemArr[getRequest('page')][intval($section->id)] = $_item;
			$_SESSION['itemincontent'] = $itemArr;
     	}
 		else 
 		if (isset($_SESSION['itemincontent']))
  		{
			$itemArr = $_SESSION['itemincontent'];  
			if (isset($itemArr[getRequest('page')][intval($section->id)]))
			{ 
				$_item = $itemArr[getRequest('page')][intval($section->id)];
			}
  		}
  		
	    if ($_item < 1)
    	{
      			$_item = 1;
    	}

		if ($_item * $recordcount > count($section->objects))
    	{
      		$_item = floor(count($section->objects) / $recordcount) + 1;
   		}

    	$i = (($_item - 1) * $recordcount);
    	$itm = $_item;

  		$startlimit = $recordcount * ($itm - 1);
  		$endlimit = $startlimit + $recordcount;
  		if ($endlimit > count($section->objects)) $endlimit = count($section->objects);
  	}
  	else
  	{
  		$startlimit = 0;
  		$endlimit = count($section->objects);
  	}
  	//echo $startlimit.'='.$endlimit;

  // конец кода разделителя страниц

    	$arrlist = array();
		for($i = $startlimit; $i < $endlimit; $i++)
		{	
			$arrlist[] = $section->objects[intval($i)];
		} 
	//	print_r($arrlist); 
		return se_fields_list($arrlist, $maska); 
	}
}

function se_record_show($section, $maska)
{
	if (getRequest('object', 1) && $section->id == getRequest('razdel', 1))
	{
		$object = array();
		$obj_id = getRequest('object', 1);
		foreach($section->objects as $obj)
		{
			if ($obj->id == $obj_id)
			{
				
				$object[$obj_id] = $obj;
				return se_fields_list($object, $maska); 
			}	
		}	
	}
	return;	
}

function se_record_arhiv($section, $maska)
{
	if (isRequest('arhiv', 1) && $section->id == getRequest('razdel', 1))
	{
	$recordcount = 30;
	$section->arhivcount = $recordcount;
  // Разделитель страниц при большом числе объектов
     if ($recordcount)
     {
     	if (isRequest('item') && $section->id == getRequest('razdel', 1))
     	{
			$itemArr = array();
			$_item = getRequest('item', 1);
			$itemArr[getRequest('page')][intval($section->id)] = $_item;
			$_SESSION['iteminarhiv'] = $itemArr;
     	}
 		else 
  		{
			$itemArr = $_SESSION['iteminarhiv'];  
			if (!empty($itemArr[getRequest('page')][intval($section->id)]['arhiv']))
			{ 
				$_item = $itemArr[getRequest('page')][intval($section->id)]['arhiv'];
			}
  		}
  		
	    if ($_item < 1)
    	{
      			$_item = 1;
    	}

		if ($_item * $recordcount > count($section->objects))
    	{
      		$_item = floor(count($section->objects) / $recordcount) + 1;
   		}

    	$i = (($_item - 1) * $recordcount);
    	$itm = $_item;

  		$startlimit = $recordcount * ($itm - 1);
  		$endlimit = $startlimit + $recordcount;
  		if ($endlimit > count($section->objects)) $endlimit = count($section->objects);
  	}
  	else
  	{
  		$startlimit = 0;
  		$endlimit = count($section->objects);
  	}
  	//echo $startlimit.'='.$endlimit;

  // конец кода разделителя страниц

    	$arrlist = array();
		for($i = $startlimit; $i < $endlimit; $i++)
		{	
			$arrlist[] = $section->objects[intval($i)];
		} 
		if (preg_match("/<arhiv:item>(.+)<\/arhiv:item>/i", $maska, $m))
		{
			$maska = str_replace($m[0] ,se_fields_list($arrlist, $m[1], 1), $maska); 
		}
		return $maska;
	}
}