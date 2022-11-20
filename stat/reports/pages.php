<?php

$by = htmlspecialchars($_GET["by"], ENT_QUOTES);
$url = htmlspecialchars($_GET["url"], ENT_QUOTES);

$ADMENU = "";
if ($by == "hits") {
	$ADMENU .= "Для хитов<br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "hosts")."'>Для хостов</a><br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "users")."'>Для пользователей</a><br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "views")."'>Для просмотров</a><br>";
    $selcn = "DISTINCT id_session, request_uri";
}elseif ($by == "hosts") {
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "hits")."'>Для хитов</a><br>";
	$ADMENU .= "Для хостов<br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "users")."'>Для пользователей</a><br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "views")."'>Для просмотров</a><br>";
    $selcn = "DISTINCT ip";
}elseif ($by == "users") {
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "hits")."'>Для хитов</a><br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "hosts")."'>Для хостов</a><br>";
	$ADMENU .= "Для пользователей<br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "views")."'>Для просмотров</a><br>";
    $selcn = "DISTINCT id_user";
}elseif ($by == "views") {
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "hits")."'>Для хитов</a><br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "hosts")."'>Для хостов</a><br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "users")."'>Для пользователей</a><br>";
	$ADMENU .= "Для просмотров<br>";
    $selcn = "id";
}else {
    $by = "hits";
	$ADMENU .= "Для хитов<br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "hosts")."'>Для хостов</a><br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "users")."'>Для пользователей</a><br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "views")."'>Для просмотров</a><br>";
    $selcn = "DISTINCT id_session, request_uri";
}
$ADMENU .= "<br>";
if ($url == 'short') {
	$ADMENU .= "<a href='index.php?".se_stat_sqs("url", "full")."'>Полный путь</a><br>";
	$ADMENU .= "Сокращенный путь<br>";
}else {
    $url = 'full';
    $ADMENU .= "Полный путь<br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("url", "short")."'>Сокращенный путь</a><br>";
}

if ((!empty($sheet))&&($sheet > 1)) $limitpage = ($CONFIG['limitpage']*$sheet-$CONFIG['limitpage']).",".$CONFIG['limitpage'];
else $limitpage = $CONFIG['limitpage'];

set_time_limit(100);
if ((!empty($url))&&($url == 'short')) {
// сокращенный путь
$r = mysql_query("SELECT SQL_CALC_FOUND_ROWS concat('/',page), COUNT(".$selcn.") AS `cn`, domain, titlepage
                  FROM stat_log
                  WHERE (concat(stat_log.date, stat_log.time) >= '".$begdate.$begtime."') AND
                        (concat(stat_log.date, stat_log.time) <= '".$enddate.$endtime."') AND
                        (LENGTH(page)>0)
                  GROUP BY page
                  ORDER BY `cn` DESC, page ASC
                  LIMIT ".$limitpage.";");
$rs = mysql_query("SELECT COUNT(".$selcn.") AS `cn` FROM stat_log
                   WHERE (concat(stat_log.date, stat_log.time) >= '".$begdate.$begtime."') AND
                         (concat(stat_log.date, stat_log.time) <= '".$enddate.$endtime."') AND
                         (LENGTH(page)>0)
                  GROUP BY page;");
}else {
// полный путь
$r = mysql_query("SELECT SQL_CALC_FOUND_ROWS request_uri, COUNT(".$selcn.") AS `cn`, domain, titlepage
                  FROM stat_log
                  WHERE (concat(stat_log.date, stat_log.time) >= '".$begdate.$begtime."') AND
                        (concat(stat_log.date, stat_log.time) <= '".$enddate.$endtime."') AND
                        (LENGTH(request_uri)>0)
                  GROUP BY request_uri
                  ORDER BY `cn` DESC, request_uri ASC
                  LIMIT ".$limitpage.";");
$rs = mysql_query("SELECT COUNT(".$selcn.") AS `cn` FROM stat_log
                  WHERE (concat(stat_log.date, stat_log.time) >= '".$begdate.$begtime."') AND
                        (concat(stat_log.date, stat_log.time) <= '".$enddate.$endtime."') AND
                         (LENGTH(request_uri)>0)
                  GROUP BY request_uri;");
}

$cnr = mysql_fetch_row(mysql_query("SELECT FOUND_ROWS()"));
$cnrow = $cnr[0];
$countsum = 0;
while ($rows = mysql_fetch_array($rs, MYSQL_BOTH)) $countsum += $rows['cn'];

for ($i=0; $i < mysql_num_rows($r); $i++) {
	$page[] = mysql_result($r, $i, 0);
	$count[] = mysql_result($r, $i, 1);
	$domain[] = mysql_result($r, $i, 2);
	$titlepage[] = mysql_result($r, $i, 3);
}

if (!empty($page)) {

$html .= se_stat_divpages($cnrow, $CONFIG['limitpage']);
$html .= "<center><table class='tblval_report' border=0 width=100%>";

//$countsum = array_sum($count);

for ($i=0; $i < mysql_num_rows($r); $i++) {
    $pr = sprintf("%01.2f", $count[$i]*100/$countsum)."%";
    $pgim = $count[$i]*500/max($count);
    if ($class != "trodd") $class = "trodd"; else $class = "treven";
	$html .= "<tr class=".$class.">\n";
   	$html .= "\t<td align=left width=550><a href='http://".$domain[$i].$page[$i]."' target='_blank' title='перейти на сайт'>".$page[$i]."</a>
                                         <br><font color=gray>&nbsp;".$titlepage[$i]."</font>";

    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$count[$i]."' width='".$pgim."' height='5'></div>";
    $html .= "</td><td align=right width=50>".$count[$i];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr."</div>";
    $html .= "</td></tr>\n";
}

$html .= "</table></center>";
$html .= se_stat_divpages($cnrow, $CONFIG['limitpage']);

}else $html = "<center><table class='tblval_report' border=0 width=100%><tr class='tbltitle'><td align='center'><i>НЕТ ДАННЫХ</i></td></tr></table></center>";

print $html;

?>