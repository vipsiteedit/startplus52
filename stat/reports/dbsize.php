<?php

// Данные для меню "Дополнительно"
$resstatus = mysql_query("SHOW TABLE STATUS");
$dbstatsize = 0; $dbstatrows = 0;
while ($rowstatus = mysql_fetch_array($resstatus, MYSQL_ASSOC)) {
    if (substr($rowstatus['Name'],0,5) == "stat_") $dbstatsize += $rowstatus['Data_length']+$rowstatus['Index_length'];
	if (($rowstatus['Name'] == "stat_log")||($rowstatus['Name'] == "stat_logrobots")) $dbstatrows += $rowstatus['Rows'];
}

$datemonth = array('', 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');
$mindatelog = mysql_fetch_array(mysql_query("SELECT MIN(date) AS `date`, MIN(time) AS `time` FROM stat_log GROUP BY id LIMIT 1;"), MYSQL_BOTH);

$ADMENU = "Занято байт:&nbsp;<B><span dir=\"ltr\">".se_stat_formatNumber($dbstatsize)."</span></B><br>
           Записей:&nbsp;<B><span dir=\"ltr\">".se_stat_formatNumber($dbstatrows)."</span></B><br>
           Лог ведется с:<br><B><span dir=\"ltr\">".substr($mindatelog['date'],6,2)." ".
                                                    $datemonth[intval(substr($mindatelog['date'],4,2))]." ".
                                                    substr($mindatelog['date'],0,4)." г.&nbsp;
                                                    (".substr($mindatelog['time'],0,2).":".
                                                    substr($mindatelog['time'],2,2).":".
                                                    substr($mindatelog['time'],4,2).")</span></B>";

$s_l = 0; $s_r = 0; $s_u = 0;
$rl = mysql_query("SELECT date, COUNT(id) AS `size`
                   FROM stat_log GROUP BY date ORDER BY date DESC LIMIT 30");
while ($rowl = mysql_fetch_array($rl, MYSQL_BOTH)) {
	$date[] = $rowl[0];
	$sum_l[$rowl[0]] = $rowl[1];
	$count_l[$rowl[0]] = $rowl[1];
    $s_l += $rowl[1];
}
$rr = mysql_query("SELECT date, COUNT(id) AS `size` FROM stat_logrobots GROUP BY date ORDER BY date DESC LIMIT 30");
while ($rowr = mysql_fetch_array($rr, MYSQL_BOTH)) {
	$sum_r[$rowr[0]] = $rowr[1];
	$count_r[$rowr[0]] = $rowr[1];
    $s_r += $rowr[1];
}
$ru = mysql_query("SELECT date, COUNT(DISTINCT stat_users.id) AS `size` FROM stat_log
                   INNER JOIN stat_users ON (stat_users.date_first=stat_log.date)
                   GROUP BY date_first ORDER BY date_first DESC LIMIT 30");
while ($rowu = mysql_fetch_array($ru, MYSQL_BOTH)) {
	$sum_u[$rowu[0]] = $rowu[1];
	$count_u[$rowu[0]] = $rowu[1];
    $s_u += $rowu[1];
}

if (!empty($sum_l)) {

    $html .= "<center><br><table class='tblval_report' border=0 width=100%>";
	$html .= "<tr class='tbltitle_report'>";
	$html .= "<td align='center' rowspan=2><B>Дата</B></td>";
	$html .= "<td align='center' colspan=3><B>Кол-во записей в таблицах&nbsp;&nbsp;&nbsp;/всего (за день)/</B></td>";
	$html .= "<td align='center' rowspan=2><B>Всего</B></td></tr>";
	$html .= "<tr class='tbltitle_report'>
                  <td align='center'><B>stat_log</B></td>
                  <td align='center'><B>stat_logrobots</B></td>
                  <td align='center'><B>stat_users</B></td></tr>";

    rsort($date);
    for ($i=0; $i < mysql_num_rows($rl); $i++) {

        $d = date($CONFIG["date_format"],
                  mktime(0,0,0,substr($date[$i],4,2),substr($date[$i],6,2),substr($date[$i],0,4)));

        if ($class != "trodd") $class = "trodd"; else $class="treven";
    	$html .= "<tr class='".$class."'>\n";
   	    $html .= "\t<td align=center width=100>".$d."</td>
                    <td align=right width=100>".se_stat_formatNumber($sum_l[$date[$i]])." (".trim(se_stat_formatNumber($count_l[$date[$i]])).")</td>
                    <td align=right width=100>".se_stat_formatNumber($sum_r[$date[$i]])." (".trim(se_stat_formatNumber($count_r[$date[$i]])).")</td>
                    <td align=right width=100>".se_stat_formatNumber($sum_u[$date[$i]])." (".trim(se_stat_formatNumber($count_u[$date[$i]])).")</td>
                    <td align=right width=100>".se_stat_formatNumber($sum_l[$date[$i]]+$sum_r[$date[$i]]+$sum_u[$date[$i]])
                                               ." (".trim(se_stat_formatNumber($count_l[$date[$i]]+$count_r[$date[$i]]+$count_u[$date[$i]])).")</td></tr>\n";
    }
	$html .= "</table></center>\n";

}else $html = "<center><table class='tblval_report' border=0 width=100%><tr class='tbltitle'><td align='center'><i>НЕТ ДАННЫХ</i></td></tr></table></center>";

print $html;

?>