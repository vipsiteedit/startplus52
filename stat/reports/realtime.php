<?php

$rep = htmlspecialchars($_GET["rep"], ENT_QUOTES);

$ADMENU = "";
if ($rep == "full") {
	$ADMENU .= "Подробный отчет<br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("rep", "short")."'>Сокращенный отчет</a><br>";
}elseif ($rep == "short") {
    $ADMENU .= "<a href='index.php?".se_stat_sqs("rep", "full")."'>Подробный отчет</a><br>";
	$ADMENU .= "Сокращенный отчет<br>";
}else {
    $rep = "full";
	$ADMENU .= "Подробный отчет<br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("rep", "short")."'>Сокращенный отчет</a><br>";
}

if ((!empty($sheet))&&($sheet > 1)) $limitpage = ($CONFIG['limitpage']*$sheet-$CONFIG['limitpage']).",".$CONFIG['limitpage'];
else $limitpage = $CONFIG['limitpage'];

$rd = mysql_query("SELECT name FROM stat_datasuser WHERE type='dm'");
while ($row = mysql_fetch_array($rd, MYSQL_BOTH)) $notindomains[] = $row['name'];

$r = mysql_query("SELECT SQL_CALC_FOUND_ROWS distinct
                         stat_users.id AS `id`, stat_users.id_user_reg AS `idreg`, stat_users.ip_last AS `ip`,
                         stat_log.agent AS `useag`, stat_users.request_uri_last AS `page_last`,
                         stat_log.ref_domain AS `ref_d`, stat_log.ref_page AS `ref_p`,
                         stat_log.ref_pagequery AS `ref_pq`, stat_log.ref_search_query AS `ref_sq`,
                         stat_cities.name AS `city`, stat_countries.name AS `country`, stat_countries.domain AS `dcountry`,
                         concat(stat_log.domain,stat_log.request_uri) AS `input`, concat(stat_log.titlepage) AS `input_title`,
                         MIN(stat_log.date) AS `d`, MIN(stat_log.time) AS `t`,
                         stat_users.screensize AS `screen`, stat_users.colorsdepth AS `color`,
                         stat_os.name AS `os`, stat_br.name AS `browser`,
                         concat(author.a_last_name,' ',author.a_first_name) AS `fio`, stat_log.id_session
                  FROM stat_log
                  INNER JOIN stat_sessions ON stat_log.id_session = stat_sessions.id
                  INNER JOIN stat_users ON stat_log.id_user = stat_users.id
                  LEFT JOIN stat_datas AS stat_os ON stat_log.os = stat_os.id
                  LEFT JOIN stat_datas AS stat_br ON stat_log.browser = stat_br.id
                  LEFT JOIN stat_cities ON stat_cities.id = stat_log.city
                  LEFT JOIN stat_countries ON stat_countries.id = stat_log.country
                  LEFT JOIN author ON stat_users.id_user_reg = author.id
                  GROUP BY stat_sessions.id
                  ORDER BY concat(stat_log.date,stat_log.time)
                  LIMIT ".$limitpage.";");

$cnr = mysql_fetch_row(mysql_query("SELECT FOUND_ROWS()"));
$cnrow = $cnr[0];

for ($i=0; $i < mysql_num_rows($r); $i++) {
    $fio = mysql_result($r, $i, 'fio'); $idreg = mysql_result($r, $i, 'idreg');
    if ((!empty($fio)) || (!empty($idreg))) $user[] = "<font color=#008C2E>".$fio."</font>";
    else $user[] = "<font color=gray>Не зарегистрирован</font>";
	$user_id[] = mysql_result($r, $i, 'id');
	$user_idses[] = mysql_result($r, $i, 'id_session');
	$user_reg[] = mysql_result($r, $i, 'idreg');
	$user_ip[] = long2ip(mysql_result($r, $i, 'ip'));
	$page_last[] = mysql_result($r, $i, 'page_last');
    $input[] = mysql_result($r, $i, 'input');
    $input_title[] = "<br><font color=gray>&nbsp;".mysql_result($r, $i, 'input_title')."</font>";

    $sys = "";
    $os = mysql_result($r, $i, 'os');
    $br = mysql_result($r, $i, 'browser');
    $ug = mysql_result($r, $i, 'useag');
    if (($os=='')&&($br=='')) $system[] = "<font color=gray><i>Не определено</i></font>";
    $system[] = $br." ( ".$os." )";

    if (mysql_result($r, $i, 'screen')=='') $screen[] = "<font color=gray><i>Не определено</i></font>";
	else $screen[] = mysql_result($r, $i, 'screen')." (".mysql_result($r, $i, 'color').")";

    $refpage = "<font color=gray><i>Без реферера</i></font>";
    $ref_d = mysql_result($r, $i, 'ref_d');
    $ref_p = mysql_result($r, $i, 'ref_p');
    $ref_pq = mysql_result($r, $i, 'ref_pq');
    $ref_sq = mysql_result($r, $i, 'ref_sq');
    if (!empty($ref_d)&&!empty($notindomains) && (!in_array($ref_d, $notindomains))) {
        $refpage = $ref_d;
        if (!empty($ref_p)) $refpage .= $ref_p;
        if (!empty($ref_pq)) $a_refpage = "?".$ref_pq;
        else $a_refpage = "";
        $refpage = "<a href='http://".$refpage.$a_refpage."' target='_blank' title='Перейти на страницу'>".$refpage."</a>";
        if (!empty($ref_sq)) $refpage .= "<br><font color=gray>»&nbsp;Фраза поиска \"".$ref_sq."\"</font>";
    }
    $ref_page[] = $refpage;

    $d = mysql_result($r, $i, 'd'); $t = str_pad(mysql_result($r, $i, 't'), 6, "0", STR_PAD_LEFT);
    $s = time()+($COUNTER["timeoffset"]*3600)-mktime(substr($t,0,2),substr($t,2,2),substr($t,4,2),substr($d,4,2),substr($d,6,2),substr($d,0,4));
    $timeses[] = se_stat_gethis($s);

    $ctr = ""; $ct = "";
    if (!(mysql_result($r, $i, 'country')==''))
        $ctr = "<img src='img/countries/".mysql_result($r, $i, 'dcountry').".gif' width=20 height=12>&nbsp;".mysql_result($r, $i, 'country');
    if (!(mysql_result($r, $i, 'city')==''))
        $ct = "&nbsp;(".mysql_result($r, $i, 'city').")";
    if (($ctr=='')&&($ct=='')) $country_city[] = "<font color=gray><i>Не определено</i></font>";
    else $country_city[] = $ctr.$ct;

}

if (!empty($user_id)) {

$html .= se_stat_divpages($cnrow, $CONFIG['limitpage']);
$html .= "<center><br><table border=0 width=100%>";
$html .= "<tr class='tbltitle'><td colspan=2>&nbsp;Всего на сайте: <b>".$cnrow."</b></td></tr><tr><td>&nbsp;</td></tr>";
$html .= "<tr><td><table class='tblval_report' border=0 width=100% style='table-layout:fixed;'>";

if ($rep == "full") {
for ($i=0; $i < count($user_id); $i++) {
	if ($class != "trodd") $class = "trodd"; else $class = "treven";
	$html .= "<tr class=\"".$class."\"><td align=left valign=top width=130><b>Пользователь</b></td>
                  <td align=left width=470><b>".$user[$i]."</b></td></tr>
              <tr class=\"".$class."\"><td align=left valign=top >Время сессии</td><td align=left >".$timeses[$i]."</td></tr>
              <tr class=\"".$class."\"><td align=left valign=top >Точка входа</td><td align=left >
                  <a href=\"javascript:expand('index.php?st=pathes&amp;sdt=".$sdt."&amp;fdt=".$fdt."&filter=".$filter."&nowrap=1&flusrt=".$user_id[$i]."',".$i.");\"
                     title='Путь по сайту'><img src='img/top20.gif' width='13' height='13' border=0></a>&nbsp;
                     ".$input[$i]."<br><font color=gray></font><div class='block_u' id='e".$i."'></div></td></tr>
              <tr class=\"".$class."\"><td align=left valign=top >Ссылающаяся страница</td><td align=left >".$ref_page[$i]."</td></tr>
              <tr class=\"".$class."\"><td align=left valign=top >IP-адрес</td><td align=left >".$user_ip[$i]."</td></tr>
              <tr class=\"".$class."\"><td align=left valign=top >Последняя страница</td><td align=left >".$page_last[$i]."</td></tr>
              <tr class=\"".$class."\"><td align=left valign=top >Браузер (Система)</td><td align=left >".$system[$i]."</td></tr>
              <tr class=\"".$class."\"><td align=left valign=top >Разрешение экрана</td><td align=left >".$screen[$i]."</td></tr>
              <tr class=\"".$class."\"><td align=left valign=top >Страна (Город)</td><td align=left >".$country_city[$i]."</td></tr>
              <tr ><td colspan=2>&nbsp;</td></tr>";
}
}else {
$html .= "<tr class='tbltitle'><td align='left' width=100><b>IP-адрес</b></td><td align='left' width=100><b>Время сессии</b></td>
                                   <td align='left' width=100%><b>Последняя страница</b></td></tr>";
for ($i=0; $i < mysql_num_rows($r); $i++) {
	if ($class != "trodd") $class = "trodd"; else $class = "treven";
	$html .= "<tr class=\"".$class."\">
                  <td align=left >".$user_ip[$i]."</td>
                  <td align=left >".$timeses[$i]."</td>
                  <td align=left >".$page_last[$i]."</td>
              </tr>";
}
}
$html.="</table></td></tr></table></center>";
$html .= se_stat_divpages($cnrow, $CONFIG['limitpage']);

}else $html = "<center><table class='tblval_report' border=0 width=100%><tr class='tbltitle'><td align='center'><i>НЕТ ДАННЫХ</i></td></tr></table></center>";

print $html;

?>