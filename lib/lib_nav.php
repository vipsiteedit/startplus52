<?php

//Адоптивная вестрстка
function se_Navigator_ad($count, $limit=30, $section_id = false, $sel = '')
{
    if (0 == $limit) return;
    $r = '';
	$cnpage = ceil($count / $limit);
    if ($cnpage > 1) {
        //формирование строки запроса $L_VARS['get']
        if (!$section_id) {
            $get_query = '';//. $this->table_alias;
        } else {
            $get_query = '/' . $section_id;
        }
        list($listurl, $params)  = explode('?', $_SERVER['REQUEST_URI']);
        $params = (!empty($params)) ? '?'. htmlentities($params) : '';
		if ($sel != '')
            $sel = '/sel/' . $sel;

        $listurl = from_Url($listurl);
        $startpage = 'home';
        if (class_exists('seData')){
            $se = seData::getInstance();
            $startpage = $se->startpage;
			unset($se);
        }
        if (empty($listurl['page'])) 
			$listurl['page'] = $startpage;
        $urlpath = '';
        foreach($listurl as $name=>$value) {
            if (!in_array($name, array('','db', 'sheet', 'razdel', 'sel'))) {
                $urlpath .= '&' . $name . '=' . $value;
            }
        }
        $urlpath  = urlToLine($urlpath);

        if (isRequest('sheet'))
            $sheet = getRequest('sheet', VAR_INT);
        else
           $sheet = 1;

      $r .= '<nav><ul class="seNavigator pagination pagination-sm" id="nav">';
      if ($sheet == 1) {
		  $r .= '<li  class="pagen disabled"><span aria-hidden="true">&laquo;</span></li>';
	  }
	  else {
		if ($sheet == 2)
			$r .= '<li class="pagen"><a href="' . $urlpath . $get_query . $sel. URL_END . $params . '"><span aria-hidden="true">&laquo;</span></a></li>';
		else
			$r .= '<li class="pagen"><a href="' . $urlpath . $get_query . $sel. '/sheet/' . ($sheet-1) . URL_END . $params . '"><span aria-hidden="true">&laquo;</span></a></li>';
	  }
	  
      $r_left = '';
      $r_right = '';
      $cnpw =8;
      $in = 1;
      $ik = $cnpage;
      if ($cnpage > $cnpw){
        $in = $sheet - floor($cnpw / 2);
        $ik = $sheet + floor($cnpw / 2);
        if ($in <= 1)
        {
          $in = 1;
          $ik = $sheet + ($cnpw - $sheet);
        }

        if ($ik > $cnpage)
        {
          $in = $sheet - (($cnpw - 1) - ($cnpage - $sheet));
          $ik = $cnpage;
        }
        if ($in > 1)
        {
          $in = $in + 2;
          $urlpathitem = $urlpath;
          if ($urlpathitem == '/' . $startpage) $urlpathitem = '';

          $r_left .= '<li class="pagen hidden-xs"><a href="' . $urlpathitem . URL_END . $params . '">1</a></li>';
                        //    <li class="pagen"><a href="' . $urlpath . $get_query . $sel .'/sheet/2' . URL_END . $params . '#nav">2</a></li>';

          $r_left .= '<li class="pagen disabled"><span>...</span></li>';
        }
        if ($ik < $cnpage)
        {
          $ik = $ik - 2;
          $r_right = '<li class="pagen disabled"><span>...</span></li>';

         // $r_right .= '<li class="pagen"><a href="' . $urlpath . $get_query . $sel. '/sheet/' .  ($cnpage - 1) . URL_END . $params . '#nav">' . ($cnpage - 1) . '</a></li>';
          $r_right .= '<li class="pagen hidden-xs"><a href="' . $urlpath . $get_query . $sel .'/sheet/' . $cnpage . URL_END . $params . '">' . $cnpage . '</a></li>';
        }
      }
      $r .= $r_left;
	  
      for ($i = $in; $i <= $ik; $i++)
      {
        if ($i == $sheet)
          $r .= '<li class="pagen active">
                       <span class="pagenactive">' . $i . '</span></li>';
        else {
            if ($i != 1) {
                $urlpathitem = $urlpath . $get_query . $sel .'/sheet/' . $i;
                $r .= '<li class="pagen"><a href="' . $urlpathitem . URL_END . $params . '">' . $i . '</a></li>';
            } else {
                $urlpathitem = $urlpath;
                if ($urlpathitem == '/home') $urlpathitem = '';
                $r .= '<li class="pagen"><a href="' . $urlpathitem . URL_END . $params .'">' . $i . '</a></li>';
            }
        }
      }
      $r .= $r_right;
	  if ($sheet == $cnpage) {
		$r .= '<li  class="pagen disabled"><span aria-hidden="true">&raquo;</span></li>';
	  }
	  else {
		$r .= '<li  class="pagen"><a href="' . $urlpath . $get_query . $sel .'/sheet/' . ($sheet+1) . URL_END . $params . '"><span aria-hidden="true">&raquo;</span></a></li>';
	  }
      $r .= "</ul></nav>";
    }
    return $r;
}

// Табличная верстка
function se_Navigator_def($count, $limit=30, $section_id = false, $sel = '')
{
    if (0 == $limit) return;
    $r = '';
	$cnpage = ceil($count / $limit);
    if ($cnpage > 1) {
        //формирование строки запроса $L_VARS['get']
        if (!$section_id) {
            $get_query = '';//. $this->table_alias;
        } else {
            $get_query = '/' . $section_id;
        }
        list($listurl, $params)  = explode('?', $_SERVER['REQUEST_URI']);
        $params = (!empty($params)) ? '?'. htmlentities($params) : '';
		if ($sel != '')
            $sel = '/sel/' . $sel;

        $listurl = from_Url($listurl);
        $startpage = 'home';
        if (class_exists('seData')){
            $se = seData::getInstance();
            $startpage = $se->startpage;
			unset($se);
        }
        if (empty($listurl['page'])) 
			$listurl['page'] = $startpage;
        $urlpath = '';
        foreach($listurl as $name=>$value) {
            if (!in_array($name, array('','db', 'sheet', 'razdel', 'sel'))) {
                $urlpath .= '&' . $name . '=' . $value;
            }
        }
        $urlpath  = urlToLine($urlpath);

        if (isRequest('sheet'))
            $sheet = getRequest('sheet', VAR_INT);
        else
           $sheet = 1;
		   
	  $r .= '<table border="0" class="seNavigator" cellspacing="0" cellpadding="0">';
	  $r .= "<tr>";
      $r_left = '';
      $r_right = '';
      $cnpw =11;
      $in = 1;
      $ik = $cnpage;
      if ($cnpage > $cnpw){
        $in = $sheet - floor($cnpw / 2);
        $ik = $sheet + floor($cnpw / 2);
        if ($in <= 1)
        {
          $in = 1;
          $ik = $sheet + ($cnpw - $sheet);
        }

        if ($ik > $cnpage)
        {
          $in = $sheet - (($cnpw - 1) - ($cnpage - $sheet));
          $ik = $cnpage;
        }
        if ($in > 1)
        {
          $in = $in + 3;
          $urlpathitem = $urlpath;
          if ($urlpathitem == '/' . $startpage) $urlpathitem = '';
          $r_left .= '<td width="20px" align="center" class="pagen"><a href="' . $urlpathitem . URL_END . $params . '">1</a></td>
                            <td width="20px" align="center" class="pagen"><a href="' . $urlpath . $get_query . $sel .'/sheet/2' . URL_END . $params . '#nav">2</a></td>';
          $r_left .= '<td width="20px" align="center" class="pagen">...</td>';
        }
        if ($ik < $cnpage)
        {
          $ik = $ik - 3;
		  $r_right = '<td width="20px" align="center" class="pagen">...</td>';
          $r_right .= '<td width="20px" align="center" class="pagen"><a href="' . $urlpath . $get_query . $sel. '/sheet/' .  ($cnpage - 1) . URL_END . $params . '#nav">' . ($cnpage - 1) . '</a></td>';
          $r_right .= '<td width="20px" align="center" class="pagen"><a href="' . $urlpath . $get_query . $sel .'/sheet/' . $cnpage . URL_END . $params . '#nav">' . $cnpage . '</a></td>';
        }
      }
      $r .= $r_left;
	  
      for ($i = $in; $i <= $ik; $i++)
      {
        if ($i == $sheet)
          $r .= '<td width="20px" align="center">
                       <span class="pagenactive">' . $i . '</span></td>';
        else {
            if ($i != 1) {
                $urlpathitem = $urlpath . $get_query . $sel .'/sheet/' . $i;
				$r .= '<td width="20px" align="center" class="pagen"><a href="' . $urlpathitem . URL_END . $params .'#nav">' . $i . '</a></td>';
            } else {
                $urlpathitem = $urlpath;
                if ($urlpathitem == '/home') $urlpathitem = '';
				$r .= '<td width="20px" align="center" class="pagen"><a href="' . $urlpathitem . URL_END . $params .'">' . $i . '</a></td>';
            }
        }
      }
      $r .= $r_right;
      $r .= "</tr></table>";
    }
    return $r;
}

function se_Navigator($count, $limit=30, $section_id = false, $sel = '')
{
   if (intval(seData::getInstance()->prj->adaptive)){
       return se_Navigator_ad($count, $limit, $section_id, $sel);
   } else {
       return se_Navigator_def($count, $limit, $section_id, $sel);
   }

}
