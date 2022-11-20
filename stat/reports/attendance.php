<?php

$type = htmlspecialchars($_GET['type'], ENT_QUOTES);

$day = intval(htmlspecialchars($_GET['day'], ENT_QUOTES));
$month = intval(htmlspecialchars($_GET['month'], ENT_QUOTES));
$year = intval(htmlspecialchars($_GET['year'], ENT_QUOTES));
$prom = intval(htmlspecialchars($_GET['prom'], ENT_QUOTES));

$cb_views = htmlspecialchars($_GET['cb_views'], ENT_QUOTES);
$cb_hits = htmlspecialchars($_GET['cb_hits'], ENT_QUOTES);
$cb_hosts = htmlspecialchars($_GET['cb_hosts'], ENT_QUOTES);
$cb_users = htmlspecialchars($_GET['cb_users'], ENT_QUOTES);

$graph = intval(htmlspecialchars($_GET['graph'], ENT_QUOTES));
$s = htmlspecialchars($_GET['s'], ENT_QUOTES);

// Данные для меню "Дополнительно"
$resstatus = mysql_query("SHOW TABLE STATUS");
$dbstatsize = 0; $dbstatrows = 0;
while ($rowstatus = mysql_fetch_array($resstatus, MYSQL_ASSOC)) {
    if (substr($rowstatus['Name'],0,5) == "stat_") $dbstatsize += ($rowstatus['Data_length']+$rowstatus['Index_length']);
	if (($rowstatus['Name'] == "stat_log")||($rowstatus['Name'] == "stat_logrobots")) $dbstatrows += $rowstatus['Rows'];
}

$datemonth = array('', 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');
$mindatelog = mysql_fetch_array(mysql_query("SELECT MIN(date) AS `date`, MIN(time) AS `time` FROM stat_log GROUP BY id LIMIT 1"), MYSQL_BOTH);

$ADMENU = "Занято байт:&nbsp;<B><span dir=\"ltr\">".se_stat_formatNumber($dbstatsize)."</span></B><br>
           Записей:&nbsp;<B><span dir=\"ltr\">".se_stat_formatNumber($dbstatrows)."</span></B><br>
           Лог ведется с:<br><B><span dir=\"ltr\">".substr($mindatelog['date'],6,2)." ".
                                                    $datemonth[intval(substr($mindatelog['date'],4,2))]." ".
                                                    substr($mindatelog['date'],0,4)." г.&nbsp;
                                                    (".substr(sprintf("%06u",$mindatelog['time']),0,2).":".
                                                    substr(sprintf("%06u",$mindatelog['time']),2,2).":".
                                                    substr(sprintf("%06u",$mindatelog['time']),4,2).")</span></B>";
/*
$ntime = strtotime($CONFIG["dataupdate"]);
if (time()>$ntime+86400*30) // если необходимо обновить словари данных
    $ADMENU .="<br>Словари данных:<br><a href=\"index.php?st=datafiles&amp;stm=".$stm."&amp;ftp=".$ftm.$flt."\">
               <font color=red><b>необходимо обновить</b></font>";
*/
/*
function stable($color, $text) {
	return("<table cellspacing=0 cellpadding=0 border=0><tr><td>
              <table style='width:12px;height:12px;' cellspacing=1 cellpadding=1 border=0 bgcolor='black'><tr><td bgcolor='".$color."'></td></tr>
              </table></td><td>&nbsp;<B>".$text."</B></td></tr>
            </table>");
}
*/
// Для вывода даты
$dateday = array('воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота');

// Входные параметры
if ($_GET['second'] != 1) {
	$cb_hosts = $cb_hits = $cb_users = $table = "on";
	$type = "ondays";
}

// Начальные значения
$mini = $minh = $minu = $minv = 99999999;
$maxi = $maxh = $maxu = $maxv = 1;
$limit = 30;

// Расчитываем значения для графиков
$SESTATDATA = array();

/// *** Выводим таблицу *** ///
$html .= "<center><br><table class='tblval_report' border=0 width=100%>";

if ($type == "onhours") { /* По часам */

	$tm = $sdt;//time() + $COUNTER["timeoffset"];
	$rdate = date("Ymd", mktime(0,0,0,date("m",$tm), date("d",$tm), date("Y",$tm)));
	$rtime = date("His", mktime(0,0,0,date("m",$tm), date("d",$tm), date("Y",$tm)));

	$r = mysql_query("SELECT concat(date, LEFT(time,2)) AS dateh,
                             COUNT(DISTINCT id) AS views,
                             COUNT(DISTINCT id_session, page) AS hits,
                             COUNT(DISTINCT ip) AS hosts,
                             COUNT(DISTINCT id_user) AS users
                      FROM stat_log
                      WHERE (date = '".$rdate."')
                      GROUP BY dateh
                      ORDER BY dateh desc;");

//    while ($row = mysql_fetch_array($r, MYSQL_ASSOC)) {print_r($row);echo "<br>";}

	if (mysql_num_rows($r) > 0) $date = substr(mysql_result($r, 0, 0), 8);
	else $date = date("Ymd");

	for ($i=0; $i < 24; $i++) {
		$a_views[$i] = "0";
		$a_hits[$i] = "0";
		$a_hosts[$i] = "0";
		$a_users[$i] = "0";
	} /* of for */

	for ($i=0; $i < mysql_num_rows($r); $i++) {
		$h = intval(substr(mysql_result($r, $i, 0), 8, 2));
		$a_views[$h] = mysql_result($r, $i, 1);
		$a_hits[$h] = mysql_result($r, $i, 2);
		$a_hosts[$h] = mysql_result($r, $i, 3);
		$a_users[$h] = mysql_result($r, $i, 4);

		if (($minv > $a_views[$h])&&($a_views[$h] > 0)) $minv = $a_views[$h];
		if ($maxv < $a_views[$h]) $maxv = $a_views[$h];

		if (($mini > $a_hits[$h])&&($a_hits[$h] > 0)) $mini = $a_hits[$h];
		if ($maxi < $a_hits[$h]) $maxi = $a_hits[$h];

		if (($minh > $a_hosts[$h])&&($a_hosts[$h] > 0)) $minh = $a_hosts[$h];
		if ($maxh < $a_hosts[$h]) $maxh = $a_hosts[$h];

		if (($minu > $a_users[$h])&&($a_users[$h] > 0)) $minu = $a_users[$h];
		if ($maxu < $a_users[$h]) $maxu = $a_users[$h];
	} /* of for */

	$html .= "<tr class='tbltitle'>
                <td align='center'><b>Дата</b></td>
                <td align='center'><b>Часы</b></td>
                <td align='center' width=100><b>Просмотры</b></td>
                <td align='center' width=100><b>Хиты</b></td>
                <td align='center' width=100><b>Хосты</b></td>
                <td align='center' width=100><b>Пользователи</b></td></tr>";

	$tvi = $thi = $tho = $tus=0;
	for ($i=23; $i >= 0; $i--) {
		if ($class != "trodd") $class = "trodd"; else $class = "treven";
		$html .= "<tr class=\"".$class."\">\n";

		$html .= "<td align=\"center\">".date($CONFIG["date_format"], strtotime($rdate))."</td>\n";
		$html .= "<td align=\"center\">".sprintf("%02d:00", $i)." - ".sprintf("%02d:59", $i)."</td>\n";

		$t1 = $t2 = "";
		if ($a_views[$i] == $minv) {$t1 = "<font color=red><B>"; $t2 = "</font></B>";}
		if ($a_views[$i] == $maxv) {$t1 = "<font color=blue><B>"; $t2 = "</font></B>";}
		$html .= "<td align=\"right\">".$t1.se_stat_formatNumber($a_views[$i]).$t2."</td>\n";

		$t1 = $t2 = "";
		if ($a_hits[$i] == $mini) {$t1 = "<font color=red><B>"; $t2 = "</font></B>";}
		if ($a_hits[$i] == $maxi) {$t1 = "<font color=blue><B>"; $t2 = "</font></B>";}
		$html .= "<td align=\"right\">".$t1.se_stat_formatNumber($a_hits[$i]).$t2."</td>\n";

		$t1 = $t2 = "";
		if ($a_hosts[$i] == $minh) {$t1 = "<font color=red><B>"; $t2 = "</font></B>";}
		if ($a_hosts[$i] == $maxh) {$t1 = "<font color=blue><B>"; $t2 = "</font></B>";}
		$html .= "<td align=\"right\">".$t1.se_stat_formatNumber($a_hosts[$i]).$t2."</td>\n";

		$t1 = $t2 = "";
		if ($a_users[$i] == $minu) {$t1 = "<font color=red><B>"; $t2 = "</font></B>";}
		if ($a_users[$i] == $maxu) {$t1 = "<font color=blue><B>"; $t2 = "</font></B>";}
		$html .= "<td align=\"right\">".$t1.se_stat_formatNumber($a_users[$i]).$t2."</td>\n";

        // для графика
		if ($cb_views == "on") $SESTATDATA[3][] = intval($a_views[$i]);
		if ($cb_hits == "on") $SESTATDATA[0][] = intval($a_hits[$i]);
		if ($cb_users == "on") $SESTATDATA[1][] = intval($a_users[$i]);
		if ($cb_hosts == "on") $SESTATDATA[2][] = intval($a_hosts[$i]);
		$SESTATDATA["x"][] = str_pad($i,2,"0", STR_PAD_LEFT);


        if ($a_views[$i] != "0") $tvi += $a_views[$i];
		if ($a_hits[$i] != "0") $thi += $a_hits[$i];
		if ($a_hosts[$i] != "0") $tho += $a_hosts[$i];
		if ($a_users[$i] != "0") $tus += $a_users[$i];
		$html .= "</tr>\n";
	} /* of for */
	$html .= "<tr class='tbltitle'>
                  <td align=left colspan=2><b>Всего</b></td>
                  <td align=right width=100><b>".se_stat_formatNumber($tvi)."</b></td>
                  <td align=right width=100><b>".se_stat_formatNumber($thi)."</b></td>
                  <td align=right width=100><b>".se_stat_formatNumber($tho)."</b></td>
                  <td align=right width=100><b>".se_stat_formatNumber($tus)."</b></td>
              </tr>";
} /* of if ($type=="onhours") */
else
if ($type == "ondays") { /* По дням */
/*	$r = mysql_query("SELECT date,
                             COUNT(DISTINCT id) AS views,
                             COUNT(DISTINCT id_session, page) AS hits,
                             COUNT(DISTINCT ip) AS hosts,
                             COUNT(DISTINCT id_user) AS users
                      FROM stat_log
                      GROUP BY date
                      ORDER BY date desc
                      LIMIT ".($limit+1).";");
*/
	$r = mysql_query("SELECT date, views, hits, hosts, users
                      FROM stat_total
                      ORDER BY date desc
                      LIMIT ".($limit+1).";");

//    while ($row = mysql_fetch_array($r, MYSQL_ASSOC)) {print_r($row);echo "<br>";}

	$tvi = $thi = $tho = $tus = 0;

	for ($i=0; $i <= $limit; $i++) {
	    $date = @mysql_result($r, $i, 0);
        $a_views[$date] = @mysql_result($r, $i, 1);
		$a_hits[$date] = @mysql_result($r, $i, 2);
		$a_hosts[$date] = @mysql_result($r, $i, 3);
		$a_users[$date] = @mysql_result($r, $i, 4);

        if ($date != date("Ymd")) {
        if (($minv > $a_views[$date])&&($a_views[$date] > 0)) $minv = $a_views[$date];
		if ($maxv < $a_views[$date]) $maxv = $a_views[$date];

		if (($mini > $a_hits[$date])&&($a_hits[$date] > 0)) $mini = $a_hits[$date];
		if ($maxi < $a_hits[$date]) $maxi = $a_hits[$date];

		if (($minh > $a_hosts[$date])&&($a_hosts[$date] > 0)) $minh = $a_hosts[$date];
		if ($maxh < $a_hosts[$date]) $maxh = $a_hosts[$date];

		if (($minu > $a_users[$date])&&($a_users[$date] > 0)) $minu = $a_users[$date];
		if ($maxu < $a_users[$date]) $maxu = $a_users[$date];
        }
	} /* of for */

	$html .= "<tr class=tbltitle>
                <td align=center><b>Дата</b></td>
                <td align=center width=100><B>Просмотры</b></td>
                <td align=center width=100><B>Хиты</b></td>
                <td align=center width=100><b>Хосты</b></td>
                <td align=center width=100><b>Пользователи</b></td></tr>";

	for ($i=0; $i <= $limit; $i++) {
		$time = time() - (86400*($i));
		$date = date("Ymd", $time);
		$pdate = date("Y-m-d", $time);
        $weekday = date("w", strtotime($pdate));
        if ($class != "trodd") $class = "trodd"; else $class = "treven";
		$html .= "<tr class=$class>\n";

        if (($weekday == 0)||($weekday == 6))
            $html .= "<td align=left width=35%><font color=red>".date($CONFIG["date_format"], strtotime($pdate)).", ".$dateday[$weekday]."</font></td>\n";
        else
            $html .= "<td align=left width=35%>".date($CONFIG["date_format"], strtotime($pdate)).", ".$dateday[$weekday]."</td>\n";

		if (empty($a_views[$date])) $a_views[$date] = 0; else $tvi += $a_views[$date];
		if (empty($a_hits[$date])) $a_hits[$date] = 0; else $thi += $a_hits[$date];
		if (empty($a_hosts[$date])) $a_hosts[$date] = 0; else $tho += $a_hosts[$date];
		if (empty($a_users[$date])) $a_users[$date] = 0; else $tus += $a_users[$date];

		$t1 = $t2 = "";
		if ($a_views[$date] == $minv) { $t1 = "<font color=red><B>"; $t2 = "</font></B>"; }
		if ($a_views[$date] == $maxv) { $t1 = "<font color=blue><B>"; $t2 = "</font></B>"; }
		$html .= "<td align=right>".$t1.se_stat_formatNumber($a_views[$date]).$t2."</td>\n";

		$t1 = $t2 = "";
		if ($a_hits[$date] == $mini) { $t1 = "<font color=red><B>"; $t2 = "</font></B>"; }
		if ($a_hits[$date] == $maxi) { $t1 = "<font color=blue><B>"; $t2 = "</font></B>"; }
		$html .= "<td align=right>".$t1.se_stat_formatNumber($a_hits[$date]).$t2."</td>\n";

		$t1 = $t2 = "";
		if ($a_hosts[$date] == $minh) { $t1 = "<font color=red><B>"; $t2 = "</font></B>"; }
		if ($a_hosts[$date] == $maxh) { $t1 = "<font color=blue><B>"; $t2 = "</font></B>"; }
		$html .= "<td align=right>".$t1.se_stat_formatNumber($a_hosts[$date]).$t2."</td>\n";

		$t1 = $t2 = "";
		if ($a_users[$date] == $minu) { $t1 = "<font color=red><B>"; $t2 = "</font></B>"; }
		if ($a_users[$date] == $maxu) { $t1 = "<font color=blue><B>"; $t2 = "</font></B>"; }
		$html .= "<td align=right>".$t1.se_stat_formatNumber($a_users[$date]).$t2."</td>\n";

        if ($date != date("Ymd")) {
        // для графика
		if ($cb_views == "on") $SESTATDATA[3][] = intval($a_views[$date]);
		if ($cb_hits == "on") $SESTATDATA[0][] = intval($a_hits[$date]);
		if ($cb_hosts == "on") $SESTATDATA[2][] = intval($a_hosts[$date]);
		if ($cb_users == "on") $SESTATDATA[1][] = intval($a_users[$date]);
		$SESTATDATA["x"][] = date($CONFIG["shortdm_format"], $time);
        }

		$html .= "</tr>\n";
	} /* of for */

    $html .= "<tr class='tbltitle'>
                  <td align=left><b>В среднем за день</b></td>
                  <td align=right width=100><b>".se_stat_formatNumber(ceil($tvi/($limit)))."</b></td>
                  <td align=right width=100><b>".se_stat_formatNumber(ceil($thi/($limit)))."</b></td>
                  <td align=right width=100><b>".se_stat_formatNumber(ceil($tho/($limit)))."</b></td>
                  <td align=right width=100><b>".se_stat_formatNumber(ceil($tus/($limit)))."</b></td>
              </tr>";
    $html .= "<tr class='tbltitle'>
                  <td align=left><b>Всего</b></td>
                  <td align=right width=100><b>".se_stat_formatNumber($tvi)."</b></td>
                  <td align=right width=100><b>".se_stat_formatNumber($thi)."</b></td>
                  <td align=right width=100><b>".se_stat_formatNumber($tho)."</b></td>
                  <td align=right width=100><b>".se_stat_formatNumber($tus)."</b></td>
              </tr>";
} /* of if ($type=="ondays") */
else
if ($type == "onweeks") { /* По неделям */
	$r = mysql_query("SELECT date, views, hits, hosts, users
                      FROM stat_total
                      ORDER BY date desc
                      LIMIT ".(7*($limit)).";");

//    while ($row = mysql_fetch_array($r, MYSQL_ASSOC)) {print_r($row);echo "<br>";}

	$tvi = $thi = $tho = $tus = 0;

	for ($i=0; $i < mysql_num_rows($r); $i++) {
		$date = mysql_result($r, $i, 0);
		$a_views[$date] = mysql_result($r, $i, 1);
		$a_hits[$date] = mysql_result($r, $i, 2);
		$a_hosts[$date] = mysql_result($r, $i, 3);
		$a_users[$date] = mysql_result($r, $i, 4);
	} /* of for */

    $w = 0;
	$day_of_w = 1;
	$w_views = Array();
	$w_hits = Array();
	$w_hosts = Array();
	$w_users = Array();

    for ($i=0; $i < (7*($limit)); $i++) {
		$time = time() - (86400*($i+1));
		$date = date("Ymd", $time);

		$w_views[$w] += $a_views[$date];
		$w_hits[$w] += $a_hits[$date];
		$w_hosts[$w] += $a_hosts[$date];
		$w_users[$w] += $a_users[$date];
		$day_of_w++;
		if ($day_of_w > 7) { $day_of_w = 1; $w++; }
	} /* of for */

	for ($i=0; $i < $limit; $i++) {
		if (($minv > $w_views[$i]) && ($w_views[$i] > 0)) $minv = $w_views[$i];
		if ($maxv < $w_views[$i]) $maxv = $w_views[$i];

		if (($mini > $w_hits[$i]) && ($w_hits[$i] > 0)) $mini = $w_hits[$i];
		if ($maxi < $w_hits[$i]) $maxi = $w_hits[$i];

		if (($minh > $w_hosts[$i]) && ($w_hosts[$i] > 0)) $minh = $w_hosts[$i];
		if ($maxh < $w_hosts[$i]) $maxh = $w_hosts[$i];

		if (($minu > $w_users[$i]) && ($w_users[$i] > 0)) $minu = $w_users[$i];
		if ($maxu < $w_users[$i]) $maxu = $w_users[$i];
	}

	$html .= "<tr class=tbltitle>
                  <td align=center width=30%><b>Дата</b></td>
                  <td align=center width=15%><b>Просмотры</b></td>
                  <td align=center width=15%><b>Хиты</b></td>
                  <td align=center width=15%><b>Хосты</b></td>
                  <td align=center width=15%><b>Пользователи</b></td>
              </tr>";
	for ($i=0; $i < $limit; $i++) {
		if ($class != "trodd") $class = "trodd"; else $class = "treven";
		$html .= "<tr class=\"".$class."\">\n";
		$date1 = date($CONFIG["date_format"], time()-((7*($i+1))*86400));
		$date2 = date($CONFIG["date_format"], time()-((7*$i+1)*86400));
		$html .= "<td align=\"center\">".$date1." - ".$date2."</td>\n";

        // для графика
		if ($cb_views == "on") $SESTATDATA[3][] = intval($w_views[$i]);
		if ($cb_hits == "on") $SESTATDATA[0][] = intval($w_hits[$i]);
		if ($cb_users == "on") $SESTATDATA[1][] = intval($w_users[$i]);
		if ($cb_hosts == "on") $SESTATDATA[2][] = intval($w_hosts[$i]);
		$SESTATDATA["x"][] = date($CONFIG["shortdm_format"], time()-((7*$i+1)*86400));


		if (empty($w_views[$i])) $w_views[$i] = "0"; else $tvi += $w_views[$i];
		if (empty($w_hits[$i])) $w_hits[$i] = "0"; else $thi += $w_hits[$i];
		if (empty($w_hosts[$i])) $w_hosts[$i] = "0"; else $tho += $w_hosts[$i];
		if (empty($w_users[$i])) $w_users[$i] = "0"; else $tus += $w_users[$i];

		$t1 = $t2 = "";
		if ($w_views[$i] == $minv) {$t1 = "<font color=red><B>"; $t2 = "</font></B>";}
		if ($w_views[$i] == $maxv) {$t1 = "<font color=blue><B>"; $t2 = "</font></B>";}
		$html .= "<td align=right>".$t1.se_stat_formatNumber($w_views[$i]).$t2."</td>\n";

		$t1 = $t2 = "";
		if ($w_hits[$i] == $mini) {$t1 = "<font color=red><B>"; $t2 = "</font></B>";}
		if ($w_hits[$i] == $maxi) {$t1 = "<font color=blue><B>"; $t2 = "</font></B>";}
		$html .= "<td align=right>".$t1.se_stat_formatNumber($w_hits[$i]).$t2."</td>\n";

		$t1 = $t2 = "";
		if ($w_hosts[$i] == $minh) {$t1 = "<font color=red><B>"; $t2 = "</font></B>";}
		if ($w_hosts[$i] == $maxh) {$t1 = "<font color=blue><B>"; $t2 = "</font></B>";}
		$html .= "<td align=right>".$t1.se_stat_formatNumber($w_hosts[$i]).$t2."</td>\n";

		$t1 = $t2 = "";
		if ($w_users[$i] == $minu) {$t1 = "<font color=red><B>"; $t2 = "</font></B>";}
		if ($w_users[$i] == $maxu) {$t1 = "<font color=blue><B>"; $t2 = "</font></B>";}
		$html .= "<td align=right>".$t1.se_stat_formatNumber($w_users[$i]).$t2."</td>\n";

		$html .= "</tr>\n";
	} /* of for */
	$html .= "<tr class='tbltitle'>
                  <td align=left width=30%><b>Всего</b></td>
                  <td align=right width=15%><b>".se_stat_formatNumber($tvi)."</b></td>
                  <td align=right width=15%><b>".se_stat_formatNumber($thi)."</b></td>
                  <td align=right width=15%><b>".se_stat_formatNumber($tho)."</b></td>
                  <td align=right width=15%><b>".se_stat_formatNumber($tus)."</b></td>
              </tr>";
} /* of if ($type=="onweeks") */
else
if ($type == "onmonths") { /* По месяцам */
	$r = mysql_query("SELECT LEFT(date,6) AS datem, views, hits, hosts, users
                      FROM stat_total
                      ORDER BY datem desc
                      LIMIT ".(($limit+1)*30).";");
//    while ($row = mysql_fetch_array($r, MYSQL_ASSOC)) {print_r($row);echo "<br>";}

	$tvi = $thi = $tho = $tus = 0;
    for ($i=0; $i < mysql_num_rows($r); $i++) {
		$date = @mysql_result($r,$i,0);
		$a_views[$date] += @mysql_result($r,$i,1);
		$a_hits[$date] += @mysql_result($r,$i,2);
		$a_hosts[$date] += @mysql_result($r,$i,3);
		$a_users[$date] += @mysql_result($r,$i,4);

	} /* of for */

    $minv = min($a_views);
    $mini = min($a_hits);
    $minh = min($a_hosts);
    $minu = min($a_users);

    $maxv = max($a_views);
    $maxi = max($a_hits);
    $maxh = max($a_hosts);
    $maxu = max($a_users);

	$html .= "<tr class=tbltitle>
                  <td align=center width=20%><b>Дата</b></td>
                  <td align=center width=20%><b>Просмотры</b></td>
                  <td align=center width=20%><b>Хиты</b></td>
                  <td align=center width=20%><b>Хосты</b></td>
                  <td align=center width=20%><b>Пользователи</b></td></tr>";

	for ($i=-1; $i <= $limit; $i++) {
		$date = date("Ym", mktime(0,0,0,date("m")-$i,0,date("Y")));
		$pdate = date($CONFIG["shortdate_format"], mktime(0,0,0,date("m")-$i,0,date("Y")));
		if ($class != "trodd") $class = "trodd"; else $class = "treven";
		$html .= "<tr class=".$class.">\n";
		$html .= "<td align=center>".$pdate."</td>\n";

        if (date("Ym")!=$date) {
            // для графика
    		if ($cb_views == "on") $SESTATDATA[3][] = intval($a_views[$date]);
	    	if ($cb_hits == "on") $SESTATDATA[0][] = intval($a_hits[$date]);
    		if ($cb_hosts == "on") $SESTATDATA[2][] = intval($a_hosts[$date]);
    		if ($cb_users == "on") $SESTATDATA[1][] = intval($a_users[$date]);
    		$SESTATDATA["x"][] = $pdate;
        }

		if (empty($a_views[$date])) $a_views[$date] = "0"; else $tvi += $a_views[$date];
		if (empty($a_hits[$date])) $a_hits[$date] = "0"; else $thi += $a_hits[$date];
		if (empty($a_hosts[$date])) $a_hosts[$date] = "0"; else $tho += $a_hosts[$date];
		if (empty($a_users[$date])) $a_users[$date] = "0"; else $tus += $a_users[$date];

		$t1 = $t2 = "";
		if ($a_views[$date] == $minv) {$t1 = "<font color=red><B>"; $t2 = "</font></B>";}
		if ($a_views[$date] == $maxv) {$t1 = "<font color=blue><B>"; $t2 = "</font></B>";}
		$html .= "<td align=right>".$t1.se_stat_formatNumber($a_views[$date]).$t2."</td>\n";

		$t1 = $t2 = "";
		if ($a_hits[$date] == $mini) {$t1 = "<font color=red><B>"; $t2 = "</font></B>";}
		if ($a_hits[$date] == $maxi) {$t1 = "<font color=blue><B>"; $t2 = "</font></B>";}
		$html .= "<td align=right>".$t1.se_stat_formatNumber($a_hits[$date]).$t2."</td>\n";

		$t1 = $t2 = "";
		if ($a_hosts[$date] == $minh) {$t1 = "<font color=red><B>"; $t2 = "</font></B>";}
		if ($a_hosts[$date] == $maxh) {$t1 = "<font color=blue><B>"; $t2 = "</font></B>";}
		$html .= "<td align=right>".$t1.se_stat_formatNumber($a_hosts[$date]).$t2."</td>\n";

		$t1 = $t2 = "";
		if ($a_users[$date] == $minu) {$t1 = "<font color=red><B>"; $t2 = "</font></B>";}
		if ($a_users[$date] == $maxu) {$t1 = "<font color=blue><B>"; $t2 = "</font></B>";}
		$html .= "<td align=right>".$t1.se_stat_formatNumber($a_users[$date]).$t2."</td>\n";

		$html .= "</tr>\n";
	} /* of for */

	$html .= "<tr class='tbltitle'>
                  <td align=left width=20%><b>Всего</b></td>
                  <td align=right width=20%><b>".se_stat_formatNumber($tvi)."</b></td>
                  <td align=right width=20%><b>".se_stat_formatNumber($thi)."</b></td>
                  <td align=right width=20%><b>".se_stat_formatNumber($tho)."</b></td>
                  <td align=right width=20%><b>".se_stat_formatNumber($tus)."</b></td>
              </tr>";
} /* of if ($type=="onmonths") */

$html .= "</table></center>";



foreach($SESTATDATA as $k => $v)
    $SESTATDATA[$k] = array_reverse($v);

$_SESSION['SESTATDATA'] = $SESTATDATA;

// Если выбран график в ручную, то игнорируем настройки
if (intval(htmlspecialchars($_GET['graph'], ENT_QUOTES))) $CONFIG["graphic"] = intval(htmlspecialchars($_GET['graph'], ENT_QUOTES));
else $graph = intval($CONFIG['graphic']);

// Определяем тип графика
$GDVERSION = se_stat_gdVersion();

// Определяем размеры графика
$IMGW = 600;
$IMGH = round($IMGW/3);


// Если GD 2.0, но анти-алиасинг отключен, то делаем вид, что GD 1.0 и тогда графики сглаживаться не будут
if ($GDVERSION == 2 && $CONFIG['antialias'] == 0) $GDVERSION = 1;
// Если нет GD, то в любом случае включаем HTML график
if ($GDVERSION == 0) $CONFIG['graphic'] = 0;

if ($CONFIG['graphic'] > 0 && $CONFIG['graphic'] <= 4) {
	switch ($CONFIG['graphic']) {
		case  1: $g = "3d"; break;
		case  2: $g = "lines"; break;
		case  3: $g = "bar"; break;
		case  4: $g = "pie"; break;
		default: $g = "3d";
	}
	$img_smooth = "s=".($s == "on"?1:0);
	$img_antialias = "antialias=".($GDVERSION == 1?0:1);
    print "<table width=100% cellspacing=1 cellpadding=0 border=0><tr><td align='center'>
           <img src='graph/".$g.".php?".$img_smooth."&".$img_antialias."&width=".$IMGW."&height=".$IMGH."&rnd=".time()."' width='".$IMGW."' height='".$IMGH."' border=0><br>\n
           <img src='#' width=1 height=10 border=0>
           </td></tr></table>";
}else {
    print "<table width=100% cellspacing=1 cellpadding=0 border=0><tr><td>";
    include "graph/html.php";
    print "</td></tr></table>";
}

?>
<script language="JavaScript" type="text/javascript">
<!--
function redraw(i) {
	var ge=document.getElementById('ge');ge.value=i;
	var gf=document.getElementById('gf');gf.submit();
}
//-->
</script>

<center>
<table cellspacing="0" cellpadding="2" border="0" width="100%" class="tbl_tools">
<form action="index.php" method="get" class="m0" id="gf">

<input type=hidden name="st" value="<?=$st;?>">
<input type=hidden name="sdt" value="<?=$sdt;?>">
<input type=hidden name="fdt" value="<?=$fdt;?>">
<!--
<input type=hidden name="filter" value="<?=$filter;?>">
<input type=hidden name="day" value="<?=$day;?>">
<input type=hidden name="month" value="<?=$month;?>">
<input type=hidden name="year" value="<?=$year;?>">
<input type=hidden name="prom" value="<?=$prom;?>">
-->
<input type=hidden name="second" value="1">
<input type=hidden name="graph" value="<?=$graph;?>" id="ge">

<tr>
<?php
if ($GDVERSION > 0) {
	if ($graph == 1) print "<td><img src='img/graph_1_s.gif' hspace=6 width=18 height=18 border=0></td>";
	else print "<td><a href=\"javascript:redraw(1);\"><img src='img/graph_1.gif' hspace=6 width=18 height=18 border=0></a></td>";
}
?>
<td>
<table cellspacing="0" cellpadding="0" border="0">
  <tr>
    <td><input type="checkbox" name="cb_views" <?=($cb_views=="on"?"checked":"");?>></td>
    <td>&nbsp;</td>
    <td style="color:fuchsia;"><B>Просмотры</B></td>
  </tr>
</table>
</td>
<td>&nbsp;</td>
</tr>
<tr>
<?php
if ($GDVERSION > 0) {
	if ($graph == 2) print "<td><img src=\"img/graph_2_s.gif\" hspace=\"6\" width=\"18\" height=\"18\" border=\"0\"></td>";
	else print "<td><a href=\"javascript:redraw(2);\"><img src=\"img/graph_2.gif\" hspace=\"6\" width=\"18\" height=\"18\" border=\"0\"></a></td>";
}
?>
<td>
<table cellspacing="0" cellpadding="0" border="0">
  <tr>
    <td><input type="checkbox" name="cb_hits" <?=($cb_hits=="on"?"checked":"");?>></td>
    <td>&nbsp;</td>
    <td style="color:red;"><B>Хиты</B></td>
  </tr>
</table>
</td>
<td>&nbsp;</td>
</tr>
<tr>
<?php
if ($GDVERSION > 0) {
	if ($graph == 3) print "<td><img src=\"img/graph_3_s.gif\" hspace=\"6\" width=\"18\" height=\"18\" border=\"0\"></td>";
	else print "<td><a href=\"javascript:redraw(3);\"><img src=\"img/graph_3.gif\" hspace=\"6\" width=\"18\" height=\"18\" border=\"0\"></a></td>";
}
?>
<td>
<table cellspacing="0" cellpadding="0" border="0">
  <tr>
    <td><input type="checkbox" name="cb_hosts" <?=($cb_hosts=="on"?"checked":"");?>></td>
    <td>&nbsp;</td>
    <td style="color:blue;"><B>Хосты</B></td>
  </tr>
</table>
</td>
<td>&nbsp;</td>
</tr>
<tr>
<?php
/*
if ($GDVERSION > 0) {
	if ($graph == 4) print "<td><img src=\"img/graph_4_s.gif\" hspace=\"6\" width=\"18\" height=\"18\" border=\"0\"></td>";
	else print "<td><a href=\"javascript:redraw(4);\"><img src=\"img/graph_4.gif\" hspace=\"6\" width=\"18\" height=\"18\" border=\"0\"></a></td>";
}
*/
    print "<td>&nbsp;</td>";

?>
<td>
<table cellspacing="0" cellpadding="0" border="0">
  <tr>
    <td><input type="checkbox" name="cb_users" <?=($cb_users=="on"?"checked":"");?>></td>
    <td>&nbsp;</td>
    <td style="color:green;"><B>Пользователи</B></td>
  </tr>
</table>
</td>
<td align="right">
<table width="100%" cellspacing="0" cellpadding="0" border="0">
  <tr>
    <td align="right">&nbsp;
	  <select name="type">
	    <option value="onhours" <?=($type=="onhours"?"selected":"");?>>По часам
	    <option value="ondays" <?=($type=="ondays"?"selected":"");?>>По дням
	    <option value="onweeks" <?=($type=="onweeks"?"selected":"");?>>По неделям
	    <option value="onmonths" <?=($type=="onmonths"?"selected":"");?>>По месяцам
	  </select>
    </td>
    <td>&nbsp;</td>
    <td align="left" width="100px"><input type="submit" value="Обновить"></td>
  </tr>
</table>
</td>
</tr>
</form>
</table>

<?php
print $html;
$NOFILTER = 1;
?>
