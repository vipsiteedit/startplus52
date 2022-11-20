<?php
// -------- формируем строку запроса - shape query string (sqs)
// -------- если несколько переменных, то разделяем их ";"  se_stat_sqs("var1;var2;var3", "val1;val2;val3")
function se_stat_sqs($var, $val) {
    if (empty($SE_VARS['get'])) {
        // выдаем все переменные, переданные в $GET без $remove
        $link = array();
        $remove = array('page', 'razdel', 'sub', 'sheet');
        foreach($_GET as $k => $v) if (!in_array($k, $remove)) $link[$k] = $k.'='.$v;
        $SE_VARS['get'] = join('&', $link);
        if (!empty($SE_VARS['get'])) $SE_VARS['get'] = '&'.$SE_VARS['get'];
    }
    // -------------------------------

    //$squery = $_SERVER['QUERY_STRING'];
    if (!empty($var)) {
        $arrvar = explode(";", $var);
        $arrval = explode(";", $val);
        parse_str($SE_VARS['get'], $arr);
        foreach($arrvar as $k => $v) {
            $arr[$v] = $arrval[$k];
        }

        $result = "";
        foreach($arr as $k => $v) {
            $result .= '&'.$k.'='.$v;
        }
        return $result;
    }
    else
        return $SE_VARS['get'];
}

// -------- вывод группы в меню
function se_stat_title($title) {
    print '<table class="tbltitlemenu" cellspacing="0" cellpadding="5" border="0" width="100%"><tr><td><B>'.$title.'</B></td></tr></table>';
}

// -------- определение реального ip (проверка на прокси)
function se_stat_getrealip() {
    if(isset($_SERVER)) {
        if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        }elseif(!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $realip = $_SERVER["HTTP_CLIENT_IP"];
        }else{
            $realip = $_SERVER["REMOTE_ADDR"];
        }
    }else{
        if(getenv('HTTP_X_FORWARDED_FOR') ) {
            $realip = getenv('HTTP_X_FORWARDED_FOR');
        }elseif ( getenv('HTTP_CLIENT_IP') ) {
            $realip = getenv('HTTP_CLIENT_IP');
        }else {
            $realip = getenv('REMOTE_ADDR');
        }
    }
return $realip;
}
// -------- определение прокси
function se_stat_getproxy() {
    if(isset($_SERVER)) {
        if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"]) || !empty($_SERVER["HTTP_CLIENT_IP"]))
            $ipproxy = $_SERVER["REMOTE_ADDR"];
        else
            $ipproxy = 0;
    }else{
        if(getenv('HTTP_X_FORWARDED_FOR') || getenv('HTTP_CLIENT_IP') )
            $ipproxy = getenv('REMOTE_ADDR');
        else
            $ipproxy = 0;
    }
return $ipproxy;
}

// -------- вывод времени
function se_stat_getmicrotime() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

// -------- перевод секунд в часы:мин:сек
function se_stat_gethis($sec) {
    $his = "";
    $h = floor($sec/3600);
    $i = floor(($sec-(3600*$h))/60);
    $s = floor(($sec-(3600*$h+$i*60)));
    if ($h > 0) $his .= $h.' ч. ';
    if ($i > 0) $his .= $i.' мин. ';
    if ($s > 0) $his .= $s.' сек.';

    return $his;
}

// -------- определение браузера
function se_stat_getbrowser($user_agent) {
    $s[3] = substr($user_agent, 0, strpos($user_agent, '('));
    $s[2] = substr($user_agent, strpos($user_agent, '(')+1, strrpos($user_agent, ')')-strpos($user_agent, '(')-1);
    $s[1] = substr($user_agent, strrpos($user_agent, ')')+1, strlen($user_agent));

    if (!strpos($user_agent, '(')) $s[3] = $user_agent;

    $result = mysql_query("SELECT id, name, d1, d2, d3 FROM stat_datas WHERE type = 'br'");
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) $datasbrowser[] = $row;

    for ($i = 0; $i < count($datasbrowser); $i++) {
        $str = trim($datasbrowser[$i]['d1']);

        if (substr($str, -1) == "|") $str = substr($datasbrowser[$i]['d1'], 0, -1);

        if (@preg_match("/\b".$str."/i", $s[1])) {
            $msg['id'] = $datasbrowser[$i]['id'];
            $msg['name'] = $datasbrowser[$i]['name'];
            break;
        }else{
            $msg['id'] = 0;
            $msg['name'] = "other";
        }
        if ($msg['id'] == 0)
            if (@preg_match("/\b".$str."/i", $s[2])) {
                $msg['id'] = $datasbrowser[$i]['id'];
                $msg['name'] = $datasbrowser[$i]['name'];
                break;
            }else{
                $msg['id'] = 0;
                $msg['name'] = "other";
            }
        if ($msg['id'] == 0)
            if (@preg_match("/\b".$str."/i", $s[3])) {
                $msg['id'] = $datasbrowser[$i]['id'];
                $msg['name'] = $datasbrowser[$i]['name'];
                break;
            }else{
                $msg['id'] = 0;
                $msg['name'] = "other";
            }
    }
    return $msg;
}

// -------- определение операционной системы
function se_stat_getsystem($user_agent) {
    $result = mysql_query("SELECT id, name, d1, d2, d3 FROM stat_datas WHERE type = 'os'");
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
        $datassystem[] = $row;

    for ($i = 0; $i < count($datassystem); $i++) {
        $str = trim($datassystem[$i]['d1']);

        if (substr($str, -1) == "|") $str = substr($datassystem[$i]['d1'], 0, -1);

        if (@preg_match("/\b".$str."/i", $user_agent)) {
            $msg['id'] = $datassystem[$i]['id'];
            $msg['name'] = $datassystem[$i]['name'];
            break;
        }else{
            $msg['id'] = 0;
            $msg['name'] = "other";
        }
    }
    return $msg;
}

// -------- определение робота
function se_stat_getrobot($user_agent) {
    $result = mysql_query("SELECT id, name, d1 FROM stat_datas WHERE type = 'rb'");
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
        $datasrobot[] = $row;

    for ($i = 0; $i < count($datasrobot); $i++) {
        $str = trim($datasrobot[$i]['d1']);

        if (substr($str, -1) == "|") $str = substr($datasrobot[$i]['d1'], 0, -1);

        if (@preg_match("/\b".$str."/i", $user_agent)) {
            $msg['id'] = $datasrobot[$i]['id'];
            $msg['name'] = $datasrobot[$i]['name'];
            break;
        }else{
            $msg['id'] = 0;
            $msg['name'] = "other";
        }
    }
    return $msg;
}

// -------- определение каталога
function se_stat_getcatalog($user_agent) {
    $result = mysql_query("SELECT id, name, d1, d2, d3 FROM stat_datas WHERE type = 'ct'");
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
        $datascatalog[] = $row;

    for ($i = 0; $i < count($datascatalog); $i++) {
        $str = trim($datascatalog[$i]['d1']);

        if (substr($str, -1) == "|") $str = substr($datascatalog[$i]['d1'], 0, -1);

        if (@preg_match("/\b".$str."/i", $user_agent)) {
            $msg['id'] = $datascatalog[$i]['id'];
            $msg['name'] = $datascatalog[$i]['name'];
            break;
        }else{
            $msg['id'] = 0;
            $msg['name'] = "other";
        }
    }
    return $msg;
}

// -------- определение поисковой системы
function se_stat_getsearchsys($user_agent) {
    $result = mysql_query("SELECT id, name, d1, d2, d3 FROM stat_datas WHERE type = 'ss'");
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
        $datasrobot[] = $row;

    for ($i = 0; $i < count($datasrobot); $i++) {
        $str = trim($datasrobot[$i]['d1']);

        if (substr($str, -1) == "|") $str = substr($datasrobot[$i]['d1'], 0, -1);

        if (@preg_match("/\b".$str."/i", $user_agent)) {
            $msg['id'] = $datasrobot[$i]['id'];
            $msg['name'] = $datasrobot[$i]['name'];
            break;
        }else{
            $msg['id'] = 0;
            $msg['name'] = "other";
        }
    }
    return $msg;
}

// -------- определение фразы поиска
function se_stat_getsearchquery($url) {
    $arr = parse_url($url);
    @$_host = $arr['host'];
    @$_query = $arr['query'];

    $result = mysql_query("SELECT id, name, d1, d2, d3 FROM stat_datas WHERE type = 'ss'");
    while ($srow = mysql_fetch_array($result, MYSQL_ASSOC))
        $datasquery[] = $srow;

    for ($i = 0; $i < count($datasquery); $i++) {
        $str = trim($datasquery[$i]['d1']);
        $perq = trim($datasquery[$i]['d2']);
        $codq = trim($datasquery[$i]['d3']);

        if (substr($str, -1) == "|") $str = substr($datasquery[$i]['d1'], 0, -1);

        if (@preg_match("/\b".$str."/i", $_host)) {
            if (!empty($perq)) {
                parse_str($_query);
                if (!empty($codq))
                    @$msg = iconv($codq, "WINDOWS-1251", $$perq);
                else
                    @$msg = $$perq;
            }else $msg = "";
        }
    }
  return @$msg;
}

// -------- определение системы рейтингов
function se_stat_getratingsys($user_agent) {
    $result = mysql_query("SELECT id, name, d1, d2, d3 FROM stat_datas WHERE type = 'tp'");
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
        $dataratingsys[] = $row;

    for ($i = 0; $i < count($dataratingsys); $i++) {
        $str = trim($dataratingsys[$i]['d1']);

        if (substr($str, -1) == "|") $str = substr($dataratingsys[$i]['d1'], 0, -1);

        if (@preg_match("/\b".$str."/i", $user_agent)) {
            $msg['id'] = $dataratingsys[$i]['id'];
            $msg['name'] = $dataratingsys[$i]['name'];
            break;
        }else{
            $msg['id'] = 0;
            $msg['name'] = "other";
        }
    }
    return $msg;
}

/////////////////////////////////////////////////////////////////////////

// -------- многостраничность
function se_stat_divpages($cnrowfull, $cnrowpage) {
    $r = "";
    $cnpage = ceil($cnrowfull/$cnrowpage);
    if ($cnpage > 1) {
        //$squery = $_SERVER['QUERY_STRING'];

        if (empty($SE_VARS['get'])) {
            // выдаем все переменные, переданные в $GET без $remove
            $link = array();
            $remove = array('page', 'razdel', 'sub', 'sheet');
            foreach($_GET as $k => $v) if (!in_array($k, $remove)) $link[$k] = $k.'='.$v;
            $SE_VARS['get'] = join('&', $link);
            if (!empty($SE_VARS['get'])) $SE_VARS['get'] = '&'.$SE_VARS['get'];
        }
        // -------------------------------

        if (!empty($_GET['sheet']))
            $sheet = htmlspecialchars($_GET['sheet'], ENT_QUOTES);
        else $sheet = 1;

        $r .= '<center>
        <form style="margin:0px"
        onSubmit="if ((this.elements[0].value)>'.$cnpage.' || this.elements[0].value < 1) {
                       alert(\'Страницы с таким номером не существует\'); return false; }
                   location.href=\'?'.$SE_VARS['get'].'&sheet=\'+(this.elements[0].value);
                   return false;" method="get">';
        $r .= '<table class="tblpage" border="0" cellspacing="0" cellpadding="0">';
        //$r .= '<tr><td colspan="9" align="center">Записей: <b>'.$cnrowfull.'</b>; Страниц: <b>'.$cnpage.'</b></td></tr>';
        $r .= "<tr>";
        $r_left = "";
        $r_right = "";
        $cnpw = 11;
        $in = 1; $ik = $cnpage;
        if ($cnpage > $cnpw) {
            $in = $sheet-floor($cnpw/2); $ik = $sheet+floor($cnpw/2);
            if ($in <= 1) { $in = 1; $ik = $sheet+($cnpw-$sheet); }
            if ($ik > $cnpage) { $in = $sheet-(($cnpw-1)-($cnpage-$sheet)); $ik = $cnpage; }
            if ($in > 1) {
                $in = $in + 3;

                $r_left .= '<td width="20" class="pagen"><a href=\'?'.$SE_VARS['get'].'&sheet=1\'>1</a></td>
                            <td width="20" class="pagen"><a href=\'?'.$SE_VARS['get'].'&sheet=2\'>2</a></td>';

               $r_left .= '<td width="20" class="pagen">...</td>';
            }
            if ($ik < $cnpage) {
                $ik = $ik - 3;
                $r_right = '<td width="20" class="pagen">...</td>';

                $r_right .= '<td width="20" class="pagen"><a href=\'?'.$SE_VARS['get'].'&sheet='.($cnpage - 1).'\'>'.($cnpage - 1).'</a></td>';
                $r_right .= '<td width="20" class="pagen"><a href=\'?'.$SE_VARS['get'].'&sheet='.$cnpage.'\'>'.$cnpage.'</a></td>';
            }
        }
        $r .= $r_left;
        for ($i = $in; $i <= $ik; $i++) {
            if ($i == $sheet)
                $r .= '<td width="20" class="pagen"><script language="Javascript">function EnsureNumeric() { if ((window.event.keyCode < 48 || window.event.keyCode > 57) && window.event.keyCode != 13) window.event.returnValue = false; }</script>
                       <input class="pagenactive" name="sheet" type="text" size="2" maxlength="'.strlen($cnpage).'" value="'.$i.'" OnKeyPress="EnsureNumeric()"></td>';
            else
                $r .= '<td width="20" class="pagen"><a href=\'?'.$SE_VARS['get'].'&sheet='.$i.'\'>'.$i.'</a></td>';
        }
        $r .= $r_right;
        $r .= "</tr>";
        $r .= "</table></form></center>";
    }
    return $r;
}

// -------- формат вывода цыфр
function se_stat_formatNumber($num) {
    $num = strval(intval($num));
	$res = "";
	$l = strlen($num)-1;
	$c = 0;
	for ($i = $l; $i >= 0; $i--) {
		$c++;
		$res = $num[$i].$res;
		if ($c>=3) {
            $res = " ".$res;
            $c = 0;
        }
	}
	return($res);
}

function se_stat_gdVersion() {
	if (!function_exists("imagepng")) return(0);

	ob_start();
	phpinfo(8);
	$phpinfo=ob_get_contents();
	ob_end_clean();
	$phpinfo=stristr($phpinfo,"gd version");
	$phpinfo=stristr($phpinfo,"version");

	$end=strpos($phpinfo,"</tr>");
	if ($end) $phpinfo=substr($phpinfo,0,$end);
	$phpinfo=strip_tags($phpinfo);

	if (ereg(".*([0-9]+)\.([0-9]+)\.([0-9]+).*", $phpinfo, $r)) {
		$phpinfo=$r[1].".".$r[2].".".$r[3];
		}

	if (version_compare("2.0", $phpinfo)>=1) return(1);
	else return(2);
}

function se_stat_checkmail($email) {
// Проверка синтаксиса имени почтового ящика
    if (@preg_match("/[a-z,A-Z,0-9,\-,\_,\.]+\@[a-z,0-9,\-,\_]+\.[a-z]{2,6}/", $email)) return true;
    else return false;
}

function se_stat_checklogin($login) {
// Проверка синтаксиса введенного логина
    if (@preg_match("/[\w\d-]+/", $login)) return true;
    else return false;
}

function se_stat_checkpassword($pass) {
// Проверка синтаксиса введенного пароля
    if (@preg_match("/[a-z,A-Z,0-9]+/", $email)) return true;
    else return false;
}

?>