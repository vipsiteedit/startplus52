<?php

$by = htmlspecialchars($_GET["by"], ENT_QUOTES);

$ADMENU = "";
if ($by == "hits") {
	$ADMENU .= "Для хитов<br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "hosts")."'>Для хостов</a><br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "users")."'>Для пользователей</a><br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "views")."'>Для просмотров</a><br>";
    $selcn = "DISTINCT stat_log.id_session, stat_log.city";
}elseif ($by == "hosts") {
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "hits")."'>Для хитов</a><br>";
	$ADMENU .= "Для хостов<br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "users")."'>Для пользователей</a><br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "views")."'>Для просмотров</a><br>";
    $selcn = "DISTINCT stat_log.ip";
}elseif ($by == "users") {
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "hits")."'>Для хитов</a><br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "hosts")."'>Для хостов</a><br>";
	$ADMENU .= "Для пользователей<br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "views")."'>Для просмотров</a><br>";
    $selcn = "DISTINCT stat_log.id_user";
}elseif ($by == "views") {
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "hits")."'>Для хитов</a><br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "hosts")."'>Для хостов</a><br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "users")."'>Для пользователей</a><br>";
	$ADMENU .= "Для просмотров<br>";
    $selcn = "stat_log.id";
}else {
    $by = "hits";
	$ADMENU .= "Для хитов<br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "hosts")."'>Для хостов</a><br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "users")."'>Для пользователей</a><br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "views")."'>Для просмотров</a><br>";
    $selcn = "DISTINCT stat_log.id_session, stat_log.city";
}

if ((!empty($sheet))&&($sheet > 1)) $limitpage = ($CONFIG['limitpage']*$sheet-$CONFIG['limitpage']).",".$CONFIG['limitpage'];
else $limitpage = $CONFIG['limitpage'];

$r = mysql_query("SELECT stat_cities.name AS city, COUNT(".$selcn.")
                  FROM stat_log
                  LEFT JOIN stat_cities ON stat_cities.id = stat_log.city
                  WHERE (concat(stat_log.date, stat_log.time) >= '".$begdate.$begtime."') AND
                        (concat(stat_log.date, stat_log.time) <= '".$enddate.$endtime."')
                  GROUP BY stat_cities.id
                  ORDER BY 2 desc
                  LIMIT ".$limitpage.";");
$rs = mysql_query("SELECT COUNT(".$selcn.") AS `cn` FROM stat_log
                  LEFT JOIN stat_cities ON stat_cities.id = stat_log.city
                  WHERE (concat(stat_log.date, stat_log.time) >= '".$begdate.$begtime."') AND
                        (concat(stat_log.date, stat_log.time) <= '".$enddate.$endtime."')
                  GROUP BY stat_cities.id;");

$cnr = mysql_fetch_row(mysql_query("SELECT FOUND_ROWS()"));
$cnrow = $cnr[0];
$countsum = 0;
while ($rows = mysql_fetch_array($rs, MYSQL_BOTH)) $countsum += $rows['cn'];

for ($i=0; $i < mysql_num_rows($r); $i++) {
    if (mysql_result($r, $i, 0)=='') $city[] = '<i class=na>Не определено</i>';
    else $city[] = mysql_result($r, $i, 0);
	$count[] = mysql_result($r, $i, 1);
}

if (!empty($city)) {

$html .= se_stat_divpages($cnrow, $CONFIG['limitpage']);
$html .= "<center><br><table class='tblval_report' border=0 width=100%>";

for ($i=0; $i < mysql_num_rows($r); $i++) {
    $pr = sprintf("%01.2f", $count[$i]*100/$countsum)."%";
    $pgim = $count[$i]*500/max($count);
	if ($class != "trodd") $class = "trodd"; else $class = "treven";
	$html .= "<tr class=\"".$class."\">\n";
   	$html .= "\t<td align=left width=550>".$city[$i];
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$count[$i]."' width='".$pgim."' height='5'></div>";
    $html .= "</td><td align=right width=50>".$count[$i];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr."</div>";
    $html .= "</td></tr>\n";
}
$html.="</table></center>";
$html .= se_stat_divpages($cnrow, $CONFIG['limitpage']);

}else $html = "<center><table class='tblval_report' border=0 width=100%><tr class='tbltitle'><td align='center'><i>НЕТ ДАННЫХ</i></td></tr></table></center>";

print $html;

?>