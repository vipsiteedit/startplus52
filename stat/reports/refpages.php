<?php

$by = htmlspecialchars($_GET["by"], ENT_QUOTES);
$pagequery = htmlspecialchars($_GET["pagequery"], ENT_QUOTES);
$www = htmlspecialchars($_GET["www"], ENT_QUOTES);

$ADMENU = "";
if ($by == "hits") {
	$ADMENU .= "Для хитов<br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "hosts")."'>Для хостов</a><br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "users")."'>Для пользователей</a><br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "views")."'>Для просмотров</a><br>";
    $selcn = "DISTINCT stat_log.id_session, stat_log.ref_page";
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
    $selcn = "DISTINCT stat_log.id_session, stat_log.ref_page";
}

$ADMENU .= "<br>";
if ($www == "ignor") {
	$ADMENU .= "<a href='index.php?".se_stat_sqs("www", "")."'>Учитывать \"www\"</a><br>";
	$ADMENU .= "Не учитывать \"www\"<br>";
    $selwww = "IF(stat_log.ref_domain REGEXP '^www.',
                 concat(SUBSTRING(stat_log.ref_domain,5),stat_log.ref_page),
                 concat(stat_log.ref_domain,stat_log.ref_page))";
}else {
    $www = "";
	$ADMENU .= "Учитывать \"www\"<br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("www", "ignor")."'>Не учитывать \"www\"</a><br>";
    $selwww = "concat(stat_log.ref_domain,stat_log.ref_page)";
}
/*
if ($pagequery == "y") {
	$ADMENU .= "Показывать строку запроса<br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("pagequery", "n")."'>Не показывать строку запроса</a><br>";
}else {
    $pagequery = "n";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("pagequery", "y")."'>Показывать строку запроса</a><br>";
	$ADMENU .= "Не показывать строку запроса<br>";
}
*/

/* Для TOOLS*/
$in_refd = htmlspecialchars($_GET["in_refd"], ENT_QUOTES);
$sel_refd = htmlspecialchars($_GET["sel_refd"], ENT_QUOTES);
$sel_refd0 = ""; $sel_refd1 = ""; $sel_refd2 = "";
if ($sel_refd==0) $sel_input_refd = "disabled";
else $sel_input_refd = "";
switch ($sel_refd) {
    case 0: $sel_refd0 = "selected"; $sel_refd_fl = ""; break;
    case 1: $sel_refd1 = "selected"; $sel_refd_fl = "AND(stat_log.ref_domain LIKE '%".$in_refd."%')"; break;
    case 2: $sel_refd2 = "selected"; $sel_refd_fl = "AND(stat_log.ref_domain NOT LIKE '%".$in_refd."%')"; break;
}

$in_refp = htmlspecialchars($_GET["in_refp"], ENT_QUOTES);
$sel_refp = htmlspecialchars($_GET["sel_refp"], ENT_QUOTES);
$sel_refp0 = ""; $sel_refp1 = ""; $sel_refp2 = "";
if ($sel_refp==0) $sel_input_refp = "disabled";
else $sel_input_refp = "";
switch ($sel_refp) {
    case 0: $sel_refp0 = "selected"; $sel_refp_fl = ""; break;
    case 1: $sel_refp1 = "selected"; $sel_refp_fl = "AND(stat_log.ref_page LIKE '%".$in_refp."%')"; break;
    case 2: $sel_refp2 = "selected"; $sel_refp_fl = "AND(stat_log.ref_page NOT LIKE '%".$in_refp."%')"; break;
}

$sel_refs = htmlspecialchars($_GET["sel_refs"], ENT_QUOTES);
$sel_refs0 = ""; $sel_refs1 = ""; $sel_refs2 = "";
switch ($sel_refs) {
    case 0: $sel_refs0 = "selected"; $sel_refs_fl = ""; break;
    case 1: $sel_refs1 = "selected"; $sel_refs_fl = "AND(stat_log.ref_search_sys > 0)"; break;
    case 2: $sel_refs2 = "selected"; $sel_refs_fl = "AND(stat_log.ref_search_sys = 0)"; break;
}

/*---*/

$TOOLS = '
<table width="100%" border="0" class="tbl_tools"><tr><td>
<table width="100%" border="0">
<tr class="tbltitle">
    <td align="center"><a href=\'JavaScript:ptable_ex();\'><img id="pimg" src="img/arr_top.gif" border="0"></a></td>
    <td width="95%">Дополнительные параметры</td></tr>
</table>
<table width="100%" id="ptable" class="vis1" border="0">
<form action="index.php" method="get">
    <input type="hidden" name="st" value="'.$st.'">
    <input type="hidden" name="sdt" value="'.$sdt.'">
    <input type="hidden" name="fdt" value="'.$fdt.'">
    <input type="hidden" name="filter" value="'.$filter.'">
    <input type="hidden" name="www" value="'.$www.'">
    <tr class="trodd">
        <td width="30%">Ссылающийся домен</td>
        <td width="20%">
            <SELECT width="100%" OnChange="javascript:eSelect(this.value,\'in_refd\')" name="sel_refd" id="sel_refd">
                <OPTION value="0" '.$sel_refd0.'>не важно
                <OPTION value="1" '.$sel_refd1.'>содержит
                <OPTION value="2" '.$sel_refd2.'>не содержит
            </SELECT>
        </td>
        <td width="50%"><input '.$sel_input_refd.' type="text" style="width:100%" id="in_refd" name="in_refd" value="'.$in_refd.'"></td>
    </tr>
    <tr class="treven">
        <td width="30%">Ссылающаяся страница</td>
        <td width="20%">
            <SELECT width="100%" OnChange="javascript:eSelect(this.value,\'in_refp\')" name="sel_refp" id="sel_refp">
                <OPTION value="0" '.$sel_refp0.'>не важно
                <OPTION value="1" '.$sel_refp1.'>содержит
                <OPTION value="2" '.$sel_refp2.'>не содержит
            </SELECT>
        </td>
        <td width="50%"><input '.$sel_input_refp.' type="text" style="width:100%" id="in_refp" name="in_refp" value="'.$in_refp.'"></td>
    </tr>
    <tr class="trodd">
        <td width="100%" colspan="3">
            <SELECT width="100%" OnChange="javascript:eSelect(this.value,\'in_refs\')" name="sel_refs" id="sel_refs">
                <OPTION value="0" '.$sel_refs0.'>показывать всех
                <OPTION value="1" '.$sel_refs1.'>только поисковики
                <OPTION value="2" '.$sel_refs2.'>исключить поисковики
            </SELECT>
        </td>
    </tr>
    <tr class="treven">
        <td colspan="3" align="center">
            <input type="submit" value="Показать">
        </td>
    </tr>
</form>
</table>
</td></tr></table><br>';

if ((!empty($sheet))&&($sheet > 1)) $limitpage = ($CONFIG['limitpage']*$sheet-$CONFIG['limitpage']).",".$CONFIG['limitpage'];
else $limitpage = $CONFIG['limitpage'];

$rd = mysql_query("SELECT name FROM stat_datasuser WHERE type='dm'");
while ($row = mysql_fetch_array($rd, MYSQL_BOTH)) $notindomains[] = $row['name'];
if (!empty($notindomains)) $notind = "'".join("','", $notindomains)."'";
else $notind = "'_'";

$r = mysql_query("SELECT ".$selwww." AS `refpage`, COUNT(".$selcn.") AS `cn`,
                         stat_log.ref_pagequery, stat_log.ref_search_query, stat_datas.name AS `ref_search_sys`
                  FROM stat_log
                  LEFT JOIN stat_datas ON stat_datas.id = stat_log.ref_search_sys
                  WHERE (concat(stat_log.date, stat_log.time) >= '".$begdate.$begtime."') AND
                        (concat(stat_log.date, stat_log.time) <= '".$enddate.$endtime."') AND
                        (ref_domain NOT IN (".$notind."))AND(LENGTH(concat(ref_domain,ref_page))>0)
                        ".$sel_refd_fl."".$sel_refp_fl."".$sel_refs_fl."
                  GROUP BY `refpage`
                  ORDER BY 2 desc
                  LIMIT ".$limitpage.";");
$rs = mysql_query("SELECT COUNT(".$selcn.") AS `cn` FROM stat_log
                  WHERE (concat(stat_log.date, stat_log.time) >= '".$begdate.$begtime."') AND
                        (concat(stat_log.date, stat_log.time) <= '".$enddate.$endtime."') AND
                        (ref_domain NOT IN (".$notind."))AND(LENGTH(concat(ref_domain,ref_page))>0)
                        ".$sel_refd_fl."".$sel_refp_fl."".$sel_refs_fl."
                  GROUP BY concat(ref_domain,ref_page);");

$cnr = mysql_fetch_row(mysql_query("SELECT FOUND_ROWS()"));
$cnrow = $cnr[0];
$countsum = 0;
while ($rows = mysql_fetch_array($rs, MYSQL_BOTH)) $countsum += $rows['cn'];

for ($i=0; $i < mysql_num_rows($r); $i++) {
    $tmp_qp = mysql_result($r, $i, 'ref_pagequery');
    if (!empty($tmp_qp)) $qp[] = "?".$tmp_qp;
    else $qp[] = "";

    $tmp_qsp = mysql_result($r, $i, 'ref_search_query');
    $tmp_ss = mysql_result($r, $i, 'ref_search_sys');
    if (!empty($tmp_qsp)) $qsp[] = "<br><font color=gray>Поиск на ".$tmp_ss." по запросу «".$tmp_qsp."»</font>";
    else $qsp[] = "";

    $ref_page[] = mysql_result($r, $i, 'refpage');
    $count[] = mysql_result($r, $i, 'cn');
}

if (!empty($ref_page)) {

$html .= se_stat_divpages($cnrow, $CONFIG['limitpage']);
$html .= "<center><table class='tblval_report' border=0 width=100% style='table-layout:fixed;'>";

for ($i=0; $i < mysql_num_rows($r); $i++) {
    $pr = sprintf("%01.2f", $count[$i]*100/$countsum)."%";
    $pgim = $count[$i]*500/max($count);
	if ($class != "trodd") $class = "trodd"; else $class = "treven";
	$html .= "<tr class=\"".$class."\">\n";
   	$html .= "\t<td align=left width=550><a href='http://".$ref_page[$i].$qp[$i]."' target='_blank' title='перейти на сайт'>".$ref_page[$i]."</a>".$qsp[$i];

    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$count[$i]."' width='".$pgim."' height='5'></div>";
    $html .= "</td><td align=right width=50>".$count[$i];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr."</div>";
    $html .= "</td></tr>\n";
}
$html.="</table></center>";
$html .= se_stat_divpages($cnrow, $CONFIG['limitpage']);

}else $html = "<center><table class='tblval_report' border=0 width=100%><tr class='tbltitle'><td align='center'><i>НЕТ ДАННЫХ</i></td></tr></table></center>";

print $TOOLS.$html;

?>