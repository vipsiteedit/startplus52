<?php
$by = htmlspecialchars($_GET["by"], ENT_QUOTES);
$url = htmlspecialchars($_GET["url"], ENT_QUOTES);

$ADMENU = "";
if ($by == "hits") {
	$ADMENU .= "Для хитов<br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "hosts")."'>Для хостов</a><br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "users")."'>Для пользователей</a><br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "views")."'>Для просмотров</a><br>";
    if ((!empty($url))&&($url == 'short'))
        $selcn = "DISTINCT stat_log.id_session, stat_log.page";
    else
        $selcn = "DISTINCT stat_log.id_session, stat_log.request_uri";
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
    if ((!empty($url))&&($url == 'short'))
        $selcn = "DISTINCT stat_log.id_session, stat_log.page";
    else
        $selcn = "DISTINCT stat_log.id_session, stat_log.request_uri";
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

if (!empty($nowrap)||($nowrap == 1)){

if ((!empty($url))&&($url == 'short')) {
// сокращенный путь
    $flpage = "AND(stat_log.page = '".urldecode($_GET['flpage'])."')
               AND(stat_log.ref_search_sys = '".htmlspecialchars($_GET['flss'], ENT_QUOTES)."')";
}else{
// полный путь
    $flpage = "AND(stat_log.request_uri = '".urldecode($_GET['flpage'])."')
               AND(stat_log.ref_search_sys = '".htmlspecialchars($_GET['flss'], ENT_QUOTES)."')";

}

$r = mysql_query("SELECT SQL_CALC_FOUND_ROWS
                         LCASE(ref_search_query) AS `ref_search_query`, COUNT(".$selcn.") AS `cn`,
                         concat(ref_domain,ref_page,'?',ref_pagequery) AS `refto`
                  FROM stat_log
                  WHERE (concat(stat_log.date, stat_log.time) >= '".$begdate.$begtime."') AND
                        (concat(stat_log.date, stat_log.time) <= '".$enddate.$endtime."') AND
                        (LENGTH(ref_search_query)>0)".$flpage."
                  GROUP BY LCASE(ref_search_query)
                  ORDER BY 2 desc, 1 asc
                  LIMIT 20;");
$rs = mysql_query("SELECT COUNT(".$selcn.") AS `cn` FROM stat_log
                  WHERE (concat(stat_log.date, stat_log.time) >= '".$begdate.$begtime."') AND
                        (concat(stat_log.date, stat_log.time) <= '".$enddate.$endtime."') AND
                        (LENGTH(ref_search_query)>0)".$flpage."
                  GROUP BY LCASE(ref_search_query);");

$cnr = mysql_fetch_row(mysql_query("SELECT FOUND_ROWS()"));
$cnrow = $cnr[0];
$countsum = 0;
while ($rows = mysql_fetch_array($rs, MYSQL_BOTH)) $countsum += $rows['cn'];

for ($i=0; $i < mysql_num_rows($r); $i++) {
    $phrase[] = strtolower(mysql_result($r, $i, 'ref_search_query'));
	$count[] = mysql_result($r, $i, 'cn');
	$refto[] = mysql_result($r, $i, 'refto');
}

$html .= "<center><br><table class='tblval_report' border=0 width=100%>";
$html .= $title_ss;

for ($i=0; $i < mysql_num_rows($r); $i++) {
    $pr = sprintf("%01.2f", $count[$i]*100/$countsum)."%";
    $pgim = $count[$i]*390/max($count);
	if ($class != "trodd") $class = "trodd"; else $class = "treven";
	$html .= "<tr class=\"".$class."\">\n";
   	$html .= "\t<td align=left width=80%>
                  &nbsp;<a href='http://".$refto[$i]."' target='_blank' title='Перейти на сайт'>»»»</a>&nbsp;".$phrase[$i];

    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$count[$i]."' width='".$pgim."' height='5'></div>";
    $html .= "</td><td align=right width=50>".$count[$i];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr."</div>";
    $html .= "</td></tr>\n";
}
$html.="</table></center><br>";


}else{

if ((!empty($sheet))&&($sheet > 1)) $limitpage = ($CONFIG['limitpage']*$sheet-$CONFIG['limitpage']).",".$CONFIG['limitpage'];
else $limitpage = $CONFIG['limitpage'];

if ((!empty($url))&&($url == 'short')) {
// сокращенный путь
$r = mysql_query("SELECT SQL_CALC_FOUND_ROWS
                         stat_log.page AS `page`, COUNT(".$selcn.") AS `cn`, stat_log.domain,
                         LCASE(stat_log.ref_search_query) AS `ref_search_query`,
                         stat_log.ref_search_sys AS `idrefss`, stat_datas.name AS `ref_search_sys`,
                         concat(stat_log.ref_domain,stat_log.ref_page,'?',stat_log.ref_pagequery) AS `hrefss`
                  FROM stat_log
                  INNER JOIN stat_datas ON stat_datas.id = stat_log.ref_search_sys
                  WHERE (concat(stat_log.date, stat_log.time) >= '".$begdate.$begtime."') AND
                        (concat(stat_log.date, stat_log.time) <= '".$enddate.$endtime."') AND
                        (LENGTH(stat_log.ref_search_query)>0)
                  GROUP BY stat_log.page
                  ORDER BY `cn` desc, `ref_search_sys` ASC, stat_log.page asc
                  LIMIT ".$limitpage.";");
$rs = mysql_query("SELECT COUNT(".$selcn.") AS `cn` FROM stat_log
                  WHERE (concat(stat_log.date, stat_log.time) >= '".$begdate.$begtime."') AND
                        (concat(stat_log.date, stat_log.time) <= '".$enddate.$endtime."') AND
                        (LENGTH(ref_search_query)>0)
                  GROUP BY page;");
}else {
// полный путь
$r = mysql_query("SELECT SQL_CALC_FOUND_ROWS
                         stat_log.request_uri AS `page`, COUNT(".$selcn.") AS `cn`, stat_log.domain,
                         LCASE(stat_log.ref_search_query) AS `ref_search_query`,
                         stat_log.ref_search_sys AS `idrefss`, stat_datas.name AS `ref_search_sys`,
                         concat(stat_log.ref_domain,stat_log.ref_page,'?',stat_log.ref_pagequery) AS `hrefss`
                  FROM stat_log
                  INNER JOIN stat_datas ON stat_datas.id = stat_log.ref_search_sys
                  WHERE (concat(stat_log.date, stat_log.time) >= '".$begdate.$begtime."') AND
                        (concat(stat_log.date, stat_log.time) <= '".$enddate.$endtime."') AND
                        (LENGTH(stat_log.ref_search_query)>0)
                  GROUP BY stat_log.request_uri, stat_log.ref_search_sys
                  ORDER BY `cn` desc, `ref_search_sys` ASC, stat_log.request_uri asc
                  LIMIT ".$limitpage.";");
$rs = mysql_query("SELECT COUNT(".$selcn.") AS `cn` FROM stat_log
                  WHERE (concat(stat_log.date, stat_log.time) >= '".$begdate.$begtime."') AND
                        (concat(stat_log.date, stat_log.time) <= '".$enddate.$endtime."') AND
                        (LENGTH(ref_search_query)>0)
                  GROUP BY request_uri, stat_log.ref_search_sys;");
}

$cnr = mysql_fetch_row(mysql_query("SELECT FOUND_ROWS()"));
$cnrow = $cnr[0];
$countsum = 0;
while ($rows = mysql_fetch_array($rs, MYSQL_BOTH)) $countsum += $rows['cn'];

for ($i=0; $i < mysql_num_rows($r); $i++) {
	$page[] = mysql_result($r, $i, 'page');

    if ((!empty($url))&&($url == 'short')) $linkpage[] = "/".mysql_result($r, $i, 'page');
	else $linkpage[] = mysql_result($r, $i, 'page');

	$count[] = mysql_result($r, $i, 'cn');
	$refsq[] = strtolower(mysql_result($r, $i, 'ref_search_query'));
	$domain[] = mysql_result($r, $i, 'domain');
    $idrefss[] = mysql_result($r, $i, 'idrefss');
    $refss[] = mysql_result($r, $i, 'ref_search_sys');
    $hrefss[] = mysql_result($r, $i, 'hrefss');
}

if (!empty($page)) {

$html .= se_stat_divpages($cnrow, $CONFIG['limitpage']);
$html .= "<center><table class='tblval_report' border=0 width=100%>";
$html .= "<tr class='tbltitle'>
            <td align='center' width=100><b>Поисковая<br>система</b></td>
            <td align='center' width=450><b>Найденная страница<br>Одна из поисковых фраз</b></td>
            <td align='center' width=50><b>&nbsp;</b></td></tr>";

for ($i=0; $i < count($page); $i++) {
    $pr = sprintf("%01.2f", $count[$i]*100/$countsum)."%";
    $pgim = $count[$i]*400/max($count);
	if ($class != "trodd") $class = "trodd"; else $class = "treven";
	$html .= "<tr class=\"".$class."\">\n";

    $html .= "\t<td align=left valign=top width=100><a href='http://".$hrefss[$i]."' target='_blank' title='перейти на сайт'>".$refss[$i]."</a></td>\n";
    $html .= "\t<td align=left valign=top width=450>
      <a href=\"javascript:expand('index.php?st=".$st."&amp;sdt=".$sdt."&amp;fdt=".$fdt."&filter=".$filter."&nowrap=1&flss=".$idrefss[$i]."&flpage=".urlencode($page[$i])."',".$i.");\"
                     title='20 самых популярных поисковых фраз'>
                    <img src='img/top20.gif' width='13' height='13' border=0></a>&nbsp;
                    <a href='http://".$domain[$i].$linkpage[$i]."' target='_blank' title='перейти на сайт'>".$domain[$i].$linkpage[$i]."</a>
                    <br><font color=gray></font><div class='block_u' id='e".$i."'></div>
                    ".$refsq[$i];

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