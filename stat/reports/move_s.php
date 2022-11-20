<?php

$by = htmlspecialchars($_GET["by"], ENT_QUOTES);
$phr = htmlspecialchars(urldecode($_GET["phr"]), ENT_QUOTES);

$ADMENU = "";
if ($by == "hits") {
	$ADMENU .= "Для хитов<br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "hosts")."'>Для хостов</a><br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "users")."'>Для пользователей</a><br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "views")."'>Для просмотров</a><br>";
    $selcn = "DISTINCT stat_log.id_session, stat_log.ref_search_sys";
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
    $selcn = "DISTINCT stat_log.id_session, stat_log.ref_search_sys";
}
/*
if (!empty($phr)) {
    $title_phr = "<tr class='tbltitle'><td colspan=2>&nbsp;По фразе <b>\"".$phr."\"</b></td></tr><tr><td>&nbsp;</td></tr>";
    $filter_phr = "AND(stat_log.ref_search_query='".$phr."')";
}else {
    $title_phr = "";
    $filter_phr = "";
}
*/

if (!empty($nowrap)&&($nowrap == 1)){

$flmove_s = "AND(stat_log.ref_search_sys = '".urldecode($_GET['flmove_s'])."')";
$r = mysql_query("SELECT LCASE(ref_search_query) AS `ref_search_query`, COUNT(".$selcn."), ref_domain
                  FROM stat_log
                  WHERE (concat(stat_log.date, stat_log.time) >= '".$begdate.$begtime."') AND
                        (concat(stat_log.date, stat_log.time) <= '".$enddate.$endtime."') AND
                        (LENGTH(ref_search_query)>0)".$flmove_s."
                  GROUP BY LCASE(ref_search_query)
                  ORDER BY 2 desc, 1 asc
                  LIMIT 20;");
$rs = mysql_query("SELECT COUNT(".$selcn.") AS `cn` FROM stat_log
                  WHERE (concat(stat_log.date, stat_log.time) >= '".$begdate.$begtime."') AND
                        (concat(stat_log.date, stat_log.time) <= '".$enddate.$endtime."') AND
                        (LENGTH(ref_search_query)>0)".$flmove_s."
                  GROUP BY LCASE(ref_search_query);");
$cnr = mysql_fetch_row(mysql_query("SELECT FOUND_ROWS()"));
$cnrow = $cnr[0];
$countsum = 0;
while ($rows = mysql_fetch_array($rs, MYSQL_BOTH)) $countsum += $rows['cn'];

for ($i=0; $i < mysql_num_rows($r); $i++) {
	$phrase[] = strtolower(mysql_result($r, $i, 0));
	$domain[] = mysql_result($r, $i, 2);
	$count[] = mysql_result($r, $i, 1);
}

if (!empty($phrase)) {
$html .= "<center><br><table class='tblval_report' border=0 width=100%>";

for ($i=0; $i < mysql_num_rows($r); $i++) {
    $pr = sprintf("%01.2f", $count[$i]*100/$countsum)."%";
    $pgim = $count[$i]*400/max($count);
	if ($class != "trodd") $class = "trodd"; else $class = "treven";
	$html .= "<tr class=\"".$class."\">\n";
   	$html .= "\t<td align=left width=500>
                  &nbsp;".$phrase[$i];

    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$count[$i]."' width='".$pgim."' height='5'></div>";
    $html .= "</td><td align=right width=50>".$count[$i];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr."</div>";
    $html .= "</td></tr>\n";
}
$html.="</table></center><br>";
}else $html = "<center><table class='tblval_report' border=0 width=100%><tr class='tbltitle'><td align='center'><i>НЕТ ДАННЫХ</i></td></tr></table></center>";

}else{

if ((!empty($sheet))&&($sheet > 1)) $limitpage = ($CONFIG['limitpage']*$sheet-$CONFIG['limitpage']).",".$CONFIG['limitpage'];
else $limitpage = $CONFIG['limitpage'];

$rd = mysql_query("SELECT name FROM stat_datasuser WHERE type='dm'");
while ($row = mysql_fetch_array($rd, MYSQL_BOTH)) $notindomains[] = $row['name'];
if (!empty($notindomains)) $notind = "'".join("','", $notindomains)."'";
else $notind = "'_'";

$r = mysql_query("SELECT stat_datas.name AS ref_s, COUNT(".$selcn."), stat_log.ref_search_sys AS `id`, ref_domain AS `ref_d`
                  FROM stat_log
                  INNER JOIN stat_datas ON stat_datas.id = stat_log.ref_search_sys
                  WHERE (concat(stat_log.date, stat_log.time) >= '".$begdate.$begtime."') AND
                        (concat(stat_log.date, stat_log.time) <= '".$enddate.$endtime."') AND
                        (stat_log.ref_search_sys>0)
                  GROUP BY stat_datas.name
                  ORDER BY 2 desc
                  LIMIT ".$limitpage.";");
$rs = mysql_query("SELECT COUNT(".$selcn.") AS `cn` FROM stat_log
                  INNER JOIN stat_datas ON stat_datas.id = stat_log.ref_search_sys
                  WHERE (concat(stat_log.date, stat_log.time) >= '".$begdate.$begtime."') AND
                        (concat(stat_log.date, stat_log.time) <= '".$enddate.$endtime."') AND
                        (stat_log.ref_search_sys>0)
                  GROUP BY stat_datas.name;");

$cnr = mysql_fetch_row(mysql_query("SELECT FOUND_ROWS()"));
$cnrow = $cnr[0];
$countsum = 0;
while ($rows = mysql_fetch_array($rs, MYSQL_BOTH)) $countsum += $rows['cn'];

for ($i=0; $i < mysql_num_rows($r); $i++) {
	$move_s[] = mysql_result($r, $i, 0);
	$move_s_d[] = mysql_result($r, $i, 'ref_d');
	$move_s_id[] = mysql_result($r, $i, 'id');
	$count[] = mysql_result($r, $i, 1);
}

if (!empty($move_s)) {

$html .= se_stat_divpages($cnrow, $CONFIG['limitpage']);
$html .= "<center><br><table class='tblval_report' border=0 width=100%>";

for ($i=0; $i < count($move_s); $i++) {
    $pr = sprintf("%01.2f", $count[$i]*100/$countsum)."%";
    $pgim = $count[$i]*500/max($count);
	if ($class != "trodd") $class = "trodd"; else $class = "treven";
	$html .= "<tr class=\"".$class."\">\n";
   	$html .= "\t<td align=left valign=top width=550>
                  <a href=\"javascript:expand('index.php?st=".$st."&amp;sdt=".$sdt."&amp;fdt=".$fdt."&filter=".$filter."&nowrap=1&flmove_s=".urlencode($move_s_id[$i])."',".$i.");\"
                     title='20 самых популярных поисковых фраз'>
                    <img src='img/top20.gif' width='13' height='13' border=0></a>&nbsp;
                    <a href='http://".$move_s_d[$i]."' target='_blank' title='Перейти на сайт'>".$move_s[$i]."</a>
                    <br><font color=gray></font><div class='block_u' id='e".$i."'></div>";

    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$count[$i]."' width='".$pgim."' height='5'></div>";
    $html .= "</td><td align=right width=50>".$count[$i];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr."</div>";
    $html .= "</td></tr>\n";
}
$html.="</table></center>";
$html .= se_stat_divpages($cnrow, $CONFIG['limitpage']);

}else $html = "<center><table class='tblval_report' border=0 width=100%><tr class='tbltitle'><td align='center'><i>НЕТ ДАННЫХ</i></td></tr></table></center>";
}

print $html;

?>