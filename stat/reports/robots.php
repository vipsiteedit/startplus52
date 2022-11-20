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

$r = mysql_query("SELECT SQL_CALC_FOUND_ROWS
                         stat_datas.name, COUNT(stat_logrobots.id), stat_logrobots.agent,
                         MIN(stat_logrobots.date), MIN(stat_logrobots.time), MAX(stat_logrobots.date), MAX(stat_logrobots.time)
                  FROM stat_logrobots
                  INNER JOIN stat_datas ON stat_logrobots.id_robot = stat_datas.id
                  WHERE (concat(stat_logrobots.date, stat_logrobots.time) >= '".$begdate.$begtime."') AND
                        (concat(stat_logrobots.date, stat_logrobots.time) <= '".$enddate.$endtime."')
                  GROUP BY stat_datas.id
                  ORDER BY 2 desc
                  LIMIT ".$limitpage.";");
$rs = mysql_query("SELECT SQL_CALC_FOUND_ROWS COUNT(stat_logrobots.id) AS `cn` FROM stat_logrobots
                  INNER JOIN stat_datas ON stat_logrobots.id_robot = stat_datas.id
                  WHERE (concat(stat_logrobots.date, stat_logrobots.time) >= '".$begdate.$begtime."') AND
                        (concat(stat_logrobots.date, stat_logrobots.time) <= '".$enddate.$endtime."')
                  GROUP BY stat_datas.id;");

$cnr = mysql_fetch_row(mysql_query("SELECT FOUND_ROWS()"));
$cnrow = $cnr[0];
$countsum = 0;
while ($rows = mysql_fetch_array($rs, MYSQL_BOTH)) $countsum += $rows['cn'];

for ($i=0; $i < mysql_num_rows($r); $i++) {
	$robot[] = mysql_result($r, $i, 0);
	$robot_agent[] = mysql_result($r, $i, 2);

    $df = mysql_result($r, $i, 3); $tf = str_pad(mysql_result($r, $i, 4), 6, "0", STR_PAD_LEFT);
    $robot_first_date[] = date($CONFIG["datetime_format"],
                          mktime(substr($tf,0,2),substr($tf,2,2),substr($tf,4,2),substr($df,4,2),substr($df,6,2),substr($df,0,4)));

    $dl = mysql_result($r, $i, 5); $tl = str_pad(mysql_result($r, $i, 6), 6, "0", STR_PAD_LEFT);
    $robot_last_date[] = date($CONFIG["datetime_format"],
                          mktime(substr($tl,0,2),substr($tl,2,2),substr($tl,4,2),substr($dl,4,2),substr($dl,6,2),substr($dl,0,4)));

	$count[] = mysql_result($r, $i, 1);
}

if (!empty($robot)) {

$html .= se_stat_divpages($cnrow, $CONFIG['limitpage']);
$html .= "<center><br><table class='tblval_report' border=0 width=100%>";

if ($rep == "full") {
for ($i=0; $i < mysql_num_rows($r); $i++) {
    $pr = sprintf("%01.2f", $count[$i]*100/$countsum)."%";
    $pgim = $count[$i]*550/max($count);
	if ($class != "trodd") $class = "trodd"; else $class = "treven";
	$html .= "<tr class=\"".$class."\"><td align=left width=100>Робот</td>
                <td align=left width=500><b>".$robot[$i]."</b></td></tr>
              <tr class=\"".$class."\"><td align=left width=100>User-Agent</td><td align=left width=500>".$robot_agent[$i]."</td></tr>
              <tr class=\"".$class."\"><td align=left width=100>Количество</td><td align=left width=500><b>".$count[$i]."</b></td></tr>
              <tr class=\"".$class."\"><td align=left width=100>Первый визит</td><td align=left width=500>".$robot_first_date[$i]."</td></tr>
              <tr class=\"".$class."\"><td align=left width=100>Последний визит</td><td align=left width=500>".$robot_last_date[$i]."</td></tr>";

    if (($CONFIG['percents']==1) || ($CONFIG['gauge']==1)) $html .= "<tr class=\"".$class."\"><td colspan=2>";
    if ($CONFIG['percents']==1) $html .= "<spin class=hint>".$pr."</spin>";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$count[$i]."' width='".$pgim."' height='5'></div>";
    if (($CONFIG['percents']==1) || ($CONFIG['gauge']==1)) $html .= "</td></tr>";

    $html .= "<tr ><td colspan=2>&nbsp;</td></tr>";
}
}else {
for ($i=0; $i < mysql_num_rows($r); $i++) {
    $pr = sprintf("%01.2f", $count[$i]*100/$countsum)."%";
    $pgim = $count[$i]*500/max($count);
	if ($class != "trodd") $class = "trodd"; else $class = "treven";
	$html .= "<tr class=\"".$class."\">\n";
    $html .= "\t<td align=left width=550>".$robot[$i]."
                    <br><div class=primg><img src='img/color11.gif' alt='".$count[$i]."' width='".$pgim."' height='5'></div></td>
                <td align=right width=50>".$count[$i]."<br><div class=hint>".$pr."</div></td></tr>\n";
}
}
$html.="</table></center>";
$html .= se_stat_divpages($cnrow, $CONFIG['limitpage']);

}else $html = "<center><table class='tblval_report' border=0 width=100%><tr class='tbltitle'><td align='center'><i>НЕТ ДАННЫХ</i></td></tr></table></center>";

print $html;

?>