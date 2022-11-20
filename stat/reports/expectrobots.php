<?php

$by = htmlspecialchars($_GET["by"], ENT_QUOTES);

$ADMENU = "";

$r = mysql_query("SELECT agent, COUNT(id) AS `cn`,
                         MIN(date) AS `d1`, MIN(time) AS `t1`, MAX(date) AS `d2`, MAX(time) AS `t2`
                  FROM stat_log
                  WHERE (concat(stat_log.date, stat_log.time) >= '".$begdate.$begtime."') AND
                        (concat(stat_log.date, stat_log.time) <= '".$enddate.$endtime."') AND
                        ((browser=0)AND(os=0)AND(LENGTH(agent)>0))
                  GROUP BY 1
                  ORDER BY 2 desc;");

for ($i=0; $i < mysql_num_rows($r); $i++) {
	$robot_agent[] = mysql_result($r, $i, 0);

    $df = mysql_result($r, $i, 'd1'); $tf = str_pad(mysql_result($r, $i, 't1'), 6, "0", STR_PAD_LEFT);
    $robot_first_date[] = date($CONFIG["datetime_format"],
                          mktime(substr($tf,0,2),substr($tf,2,2),substr($tf,4,2),substr($df,4,2),substr($df,6,2),substr($df,0,4)));

    $dl = mysql_result($r, $i, 'd2'); $tl = str_pad(mysql_result($r, $i, 't2'), 6, "0", STR_PAD_LEFT);
    $robot_last_date[] = date($CONFIG["datetime_format"],
                          mktime(substr($tl,0,2),substr($tl,2,2),substr($tl,4,2),substr($dl,4,2),substr($dl,6,2),substr($dl,0,4)));

	$count[] = mysql_result($r, $i, 'cn');
}

if (!empty($robot_agent)) {

$html .= "<center><br><table class='tblval_report' border=0 width=100%>";

for ($i=0; $i < mysql_num_rows($r); $i++) {
    $pr = sprintf("%01.2f", $count[$i]*100/array_sum($count))."%";
    $pgim = $count[$i]*550/max($count);
	if ($class != "trodd") $class = "trodd"; else $class = "treven";
	$html .= "<tr class=\"".$class."\">
              <tr class=\"".$class."\"><td align=left width=100>User-Agent</td><td align=left width=500>".$robot_agent[$i]."</td></tr>
              <tr class=\"".$class."\"><td align=left width=100>Количество</td><td align=left width=500><b>".$count[$i]."</b></td></tr>
              <tr class=\"".$class."\"><td align=left width=100>Первый визит</td><td align=left width=500>".$robot_first_date[$i]."</td></tr>
              <tr class=\"".$class."\"><td align=left width=100>Последний визит</td><td align=left width=500>".$robot_last_date[$i]."</td></tr>
              <tr class=\"".$class."\"><td colspan=2><spin class=hint>".$pr."</spin><br><div class=primg>
                <img src='img/color11.gif' alt='".$count[$i]."' width='".$pgim."' height='5'></div></td></tr>
              <tr ><td colspan=2>&nbsp;</td></tr>
              ";
}
$html.="</table></center>";

}else $html = "<center><table class='tblval_report' border=0 width=100%><tr class='tbltitle'><td align='center'><i>НЕТ ДАННЫХ</i></td></tr></table></center>";

print $html;

?>