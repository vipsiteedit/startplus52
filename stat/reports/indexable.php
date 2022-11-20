<?php

$url = htmlspecialchars($_GET["url"], ENT_QUOTES);
$flrobocn = htmlspecialchars($_GET["flrobocn"], ENT_QUOTES);

$ADMENU = "";
if ($url == 'full') {
    $ADMENU .= "Полный путь<br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("url", "short")."'>Сокращенный путь</a><br>";
    $selcn = "request_uri";
}else {
    $url = 'short';
	$ADMENU .= "<a href='index.php?".se_stat_sqs("url", "full")."'>Полный путь</a><br>";
	$ADMENU .= "Сокращенный путь<br>";
    $selcn = "page";
}

if (!empty($nowrap)||($nowrap == 1)){

$flrobocn = "AND(stat_logrobots.page = '".urldecode($_GET['flrobocn'])."')";
$r = mysql_query("SELECT stat_datas.name AS `name`, COUNT(".$selcn.") AS `cn`
                  FROM stat_logrobots
                  INNER JOIN stat_datas ON stat_datas.id = stat_logrobots.id_robot
                  WHERE ((stat_logrobots.date >= '".$begdate."')AND(stat_logrobots.time >= '".$begtime."')) AND
                        ((stat_logrobots.date <= '".$enddate."')AND(stat_logrobots.time <= '".$endtime."'))
                        ".$flrobocn."
                  GROUP BY stat_logrobots.id_robot
                  ORDER BY 2 desc, 1 asc
                  LIMIT 20;");
$rs = mysql_query("SELECT COUNT(".$selcn.") AS `cn` FROM stat_logrobots
                  WHERE ((stat_logrobots.date >= '".$begdate."')AND(stat_logrobots.time >= '".$begtime."')) AND
                        ((stat_logrobots.date <= '".$enddate."')AND(stat_logrobots.time <= '".$endtime."'))
                        ".$flrobocn."
                  GROUP BY stat_logrobots.id_robot;");

$cnr = mysql_fetch_row(mysql_query("SELECT FOUND_ROWS()"));
$cnrow = $cnr[0];
$countsum = 0;
while ($rows = mysql_fetch_array($rs, MYSQL_BOTH)) $countsum += $rows['cn'];

for ($i=0; $i < mysql_num_rows($r); $i++) {
	$robot[] = mysql_result($r, $i, 0);
	$count[] = mysql_result($r, $i, 1);
}

if (!empty($robot)) {

$html .= "<center><br><table class='tblval_report' border=0 width=100%>";

for ($i=0; $i < mysql_num_rows($r); $i++) {
    $pr = sprintf("%01.2f", $count[$i]*100/$countsum)."%";
    $pgim = $count[$i]*400/max($count);
	if ($class != "trodd") $class = "trodd"; else $class = "treven";
	$html .= "<tr class=\"".$class."\">\n";
   	$html .= "\t<td align=left width=500>
                  &nbsp;".$robot[$i]."
                  <div class=primg><img src='img/color11.gif' alt='".$count[$i]."' width='".$pgim."' height='5'></div></td>
                <td align=right width=50>".$count[$i]."<br><div class=hint>".$pr."</div></td></tr>\n";
}
$html.="</table></center><br>";

}else $html = "<center><table class='tblval_report' border=0 width=100%><tr class='tbltitle'><td align='center'><i>НЕТ ДАННЫХ</i></td></tr></table></center>";


}else{

if ((!empty($sheet))&&($sheet > 1)) $limitpage = ($CONFIG['limitpage']*$sheet-$CONFIG['limitpage']).",".$CONFIG['limitpage'];
else $limitpage = $CONFIG['limitpage'];

set_time_limit(100);
if ((!empty($url))&&($url == 'short')) {
// сокращенный путь
$r = mysql_query("SELECT SQL_CALC_FOUND_ROWS page, COUNT(id) AS `cn`, domain, titlepage
                  FROM stat_logrobots
                  WHERE (concat(stat_logrobots.date, stat_logrobots.time) >= '".$begdate.$begtime."') AND
                        (concat(stat_logrobots.date, stat_logrobots.time) <= '".$enddate.$endtime."') AND
                        (LENGTH(page)>0)
                  GROUP BY page
                  ORDER BY `cn` DESC, page ASC
                  LIMIT ".$limitpage.";");
$rs = mysql_query("SELECT COUNT(page) AS `cn` FROM stat_logrobots
                   WHERE (concat(stat_logrobots.date, stat_logrobots.time) >= '".$begdate.$begtime."') AND
                         (concat(stat_logrobots.date, stat_logrobots.time) <= '".$enddate.$endtime."') AND
                         (LENGTH(page)>0)
                  GROUP BY page;");
}else {
// полный путь
$r = mysql_query("SELECT SQL_CALC_FOUND_ROWS request_uri, COUNT(id) AS `cn`, domain, titlepage
                  FROM stat_logrobots
                  WHERE (concat(stat_logrobots.date, stat_logrobots.time) >= '".$begdate.$begtime."') AND
                        (concat(stat_logrobots.date, stat_logrobots.time) <= '".$enddate.$endtime."') AND
                        (LENGTH(request_uri)>0)
                  GROUP BY request_uri
                  ORDER BY `cn` DESC, request_uri ASC
                  LIMIT ".$limitpage.";");
$rs = mysql_query("SELECT COUNT(request_uri) AS `cn` FROM stat_logrobots
                  WHERE (concat(stat_logrobots.date, stat_logrobots.time) >= '".$begdate.$begtime."') AND
                        (concat(stat_logrobots.date, stat_logrobots.time) <= '".$enddate.$endtime."') AND
                         (LENGTH(request_uri)>0)
                  GROUP BY request_uri;");
}

$cnr = mysql_fetch_row(mysql_query("SELECT FOUND_ROWS()"));
$cnrow = $cnr[0];
$countsum = 0;
while ($rows = mysql_fetch_array($rs, MYSQL_BOTH)) $countsum += $rows['cn'];

for ($i=0; $i < mysql_num_rows($r); $i++) {
    if ((!empty($url))&&($url == 'short'))
	    $robopage[] = "/".mysql_result($r, $i, 0);
    else
	    $robopage[] = mysql_result($r, $i, 0);
	$fl_robopage[] = mysql_result($r, $i, 0);
	$count[] = mysql_result($r, $i, 1);
	$domain[] = mysql_result($r, $i, 2);
	$titlepage[] = mysql_result($r, $i, 3);
}


if (!empty($robopage)) {

$html .= se_stat_divpages($cnrow, $CONFIG['limitpage']);
$html .= "<center><table class='tblval_report' border=0 width=100%>";

$html .= "<tr class='tbltitle_report'>\n";
$html .= "\t<td align='center' width=550><b>Страница</b></td>
                <td align='center' width=50><b>Количество заходов</b></td></tr>\n";
for ($i=0; $i < mysql_num_rows($r); $i++) {
    $pr = sprintf("%01.2f", $count[$i]*100/$countsum)."%";
    $pgim = $count[$i]*500/max($count);
    if ($class != "trodd") $class = "trodd"; else $class = "treven";
	$html .= "<tr class=".$class.">\n";
   	$html .= "\t<td align=left width=550>
                    <a href=\"javascript:expand('index.php?".se_stat_sqs("nowrap;flrobocn", "1;".urlencode($fl_robopage[$i]))."',".$i.")\"
                       title='20 самых популярных поисковых роботов'><img src='img/top20.gif' width='13' height='13' border=0></a>
                    <a href='http://".$domain[$i].$robopage[$i]."' target='_blank' title='перейти на сайт'>".$robopage[$i]."</a>
                    <br><font color=gray>&nbsp;".$titlepage[$i]."</font>
                    <div class='block_u' id='e".$i."'></div>";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$count[$i]."' width='".$pgim."' height='5'></div>";
    $html .= "</td><td align=right width=50>".$count[$i];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr."</div>";
    $html .= "</td></tr>\n";
}

$html .= "</table></center>";
$html .= se_stat_divpages($cnrow, $CONFIG['limitpage']);

}else $html = "<center><table class='tblval_report' border=0 width=100%><tr class='tbltitle'><td align='center'><i>НЕТ ДАННЫХ</i></td></tr></table></center>";

}

print $html;

?>