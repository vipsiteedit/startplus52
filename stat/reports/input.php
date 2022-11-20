<?php

$by = htmlspecialchars($_GET["by"], ENT_QUOTES);
$url = htmlspecialchars($_GET["url"], ENT_QUOTES);
$casual = htmlspecialchars($_GET["casual"], ENT_QUOTES);

$ADMENU = "";
if ($url == 'short') {
	$ADMENU .= "<a href='index.php?".se_stat_sqs("url", "full")."'>Полный путь</a><br>";
	$ADMENU .= "Сокращенный путь<br>";
}else {
    $url = 'full';
    $ADMENU .= "Полный путь<br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("url", "short")."'>Сокращенный путь</a><br>";
}

/*
$ADMENU .= "<br>";
if ($casual == 'n') {
	$ADMENU .= "<a href='index.php?".se_stat_sqs("casual", "y")."'>Учитывать всех</a><br>";
	$ADMENU .= "Исключить случайных<br>";
}else {
    $casual = 'y';
    $ADMENU .= "Учитывать всех<br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("casual", "n")."'>Исключить случайных</a><br>";
}
*/

if ((!empty($sheet))&&($sheet > 1)) $limitpage = ($CONFIG['limitpage']*$sheet-$CONFIG['limitpage']).",".$CONFIG['limitpage'];
else $limitpage = $CONFIG['limitpage'];

$rd = mysql_query("SELECT name FROM stat_datasuser WHERE type='dm'");
while ($row = mysql_fetch_array($rd, MYSQL_BOTH)) $notindomains[] = $row['name'];
if (!empty($notindomains)) $notind = "'".join("','", $notindomains)."'";
else $notind = "'_'";

$rdt = mysql_query("SELECT MIN(id) AS `id`, concat(id_session, id_user) AS `ses`
                  FROM stat_log
                  WHERE (concat(stat_log.date, stat_log.time) >= '".$begdate.$begtime."') AND
                        (concat(stat_log.date, stat_log.time) <= '".$enddate.$endtime."') AND
                        (LENGTH(page)>0) AND (ref_domain NOT IN (".$notind."))
                  GROUP BY concat(id_session, id_user)
                  ;");
while ($row = mysql_fetch_array($rdt, MYSQL_BOTH)) $tmp_indt[] = $row['id'];
if (!empty($tmp_indt)) $indt = "'".join("','", $tmp_indt)."'";
else $indt = "'_'";

if ((!empty($url))&&($url == 'short')) {
// сокращенный путь
$r = mysql_query("SELECT SQL_CALC_FOUND_ROWS COUNT(page) AS `cn`, page AS `page`,
                         domain, titlepage
                  FROM stat_log
                  WHERE (stat_log.id IN (".$indt."))
                  GROUP BY page
                  ORDER BY `cn` DESC
                  LIMIT ".$limitpage.";");
$rs = mysql_query("SELECT COUNT(page) AS `cn` FROM stat_log
                  WHERE (stat_log.id IN (".$indt."))
                  GROUP BY page;");
}else {
// полный путь
$r = mysql_query("SELECT SQL_CALC_FOUND_ROWS COUNT(request_uri) AS `cn`, request_uri AS `page`,
                         domain, titlepage
                  FROM stat_log
                  WHERE (stat_log.id IN (".$indt."))
                  GROUP BY request_uri
                  ORDER BY `cn` DESC
                  LIMIT ".$limitpage.";");
$rs = mysql_query("SELECT COUNT(request_uri) AS `cn` FROM stat_log
                  WHERE (stat_log.id IN (".$indt."))
                  GROUP BY request_uri;");
}

$cnr = mysql_fetch_row(mysql_query("SELECT FOUND_ROWS()"));
$cnrow = $cnr[0];
$countsum = 0;
while ($rows = mysql_fetch_array($rs, MYSQL_BOTH)) $countsum += $rows['cn'];

for ($i=0; $i < mysql_num_rows($r); $i++) {
	$inputpage[] = mysql_result($r, $i, 'page');
    $count[] = mysql_result($r, $i, 'cn');
    $domain[] = mysql_result($r, $i, 'domain');
	$titlepage[] = mysql_result($r, $i, 'titlepage');
}

if (!empty($inputpage)) {

$html .= se_stat_divpages($cnrow, $CONFIG['limitpage']);
$html .= "<center><br><table class='tblval_report' border=0 width=100%>";

for ($i=0; $i < mysql_num_rows($r); $i++)
if (!empty($inputpage[$i])) {
    $pr = sprintf("%01.2f", $count[$i]*100/$countsum)."%";
    $pgim = $count[$i]*500/max($count);
    if ($class != "trodd") $class = "trodd"; else $class = "treven";
	$html .= "<tr class=".$class.">\n";
   	$html .= "\t<td align=left width=550>
                  <a href='http://".$domain[$i].$inputpage[$i]."' target='_blank' title='перейти на сайт'>".$inputpage[$i]."</a>
                  <br><font color=gray>&nbsp;".$titlepage[$i]."</font>";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$count[$i]."' width='".$pgim."' height='5'></div>";
    $html .= "</td><td align=right width=50>".$count[$i];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr."</div>";
    $html .= "</td></tr>\n";
}

$html.="</table></center>";
$html .= se_stat_divpages($cnrow, $CONFIG['limitpage']);

//echo $countsum;

}else $html = "<center><table class='tblval_report' border=0 width=100%><tr class='tbltitle'><td align='center'><i>НЕТ ДАННЫХ</i></td></tr></table></center>";

print $html;

?>