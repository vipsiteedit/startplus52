<?php

$by = htmlspecialchars($_GET["by"], ENT_QUOTES);
$www = htmlspecialchars($_GET["www"], ENT_QUOTES);

$ADMENU = "";
if ($by == "hits") {
	$ADMENU .= "Для хитов<br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "hosts")."'>Для хостов</a><br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "users")."'>Для пользователей</a><br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "views")."'>Для просмотров</a><br>";
    $selcn = "DISTINCT id_session, ref_domain";
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
    $selcn = "DISTINCT id_session, ref_domain";
}

$ADMENU .= "<br>";
if ($www == "ignor") {
	$ADMENU .= "<a href='index.php?".se_stat_sqs("www", "")."'>Учитывать \"www\"</a><br>";
	$ADMENU .= "Не учитывать \"www\"<br>";
    $selwww = "IF(stat_log.ref_domain REGEXP '^www.', TRIM(SUBSTRING(stat_log.ref_domain,5)), TRIM(stat_log.ref_domain)) AS `ref_domain`";
}else {
    $www = "";
	$ADMENU .= "Учитывать \"www\"<br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("www", "ignor")."'>Не учитывать \"www\"</a><br>";
    $selwww = "stat_log.ref_domain";
}

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
        <td width="100%" colspan="3">
            <SELECT width="100%" OnChange="javascript:eSelect(this.value,\'in_refs\')" name="sel_refs" id="sel_refs">
                <OPTION value="0" '.$sel_refs0.'>показывать всех
                <OPTION value="1" '.$sel_refs1.'>только поисковики
                <OPTION value="2" '.$sel_refs2.'>исключить поисковики
            </SELECT>
        </td>
    </tr>
    <tr class="trodd">
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

$r = mysql_query("SELECT ".$selwww.", COUNT(".$selcn.")
                  FROM stat_log
                  WHERE (concat(stat_log.date, stat_log.time) >= '".$begdate.$begtime."') AND
                        (concat(stat_log.date, stat_log.time) <= '".$enddate.$endtime."') AND
                        (ref_domain NOT IN ('',".$notind."))AND(LENGTH(ref_domain)>0)
                        ".$sel_refd_fl."".$sel_refs_fl."
                  GROUP BY ref_domain
                  ORDER BY 2 desc
                  LIMIT ".$limitpage.";");
$rs = mysql_query("SELECT COUNT(".$selcn.") AS `cn` FROM stat_log
                  WHERE (concat(stat_log.date, stat_log.time) >= '".$begdate.$begtime."') AND
                        (concat(stat_log.date, stat_log.time) <= '".$enddate.$endtime."') AND
                        (ref_domain NOT IN ('',".$notind."))AND(LENGTH(ref_domain)>0)
                        ".$sel_refd_fl."".$sel_refs_fl."
                  GROUP BY ref_domain;");

$cnr = mysql_fetch_row(mysql_query("SELECT FOUND_ROWS()"));
$cnrow = $cnr[0];
$countsum = 0;
while ($rows = mysql_fetch_array($rs, MYSQL_BOTH)) $countsum += $rows['cn'];

for ($i=0; $i < mysql_num_rows($r); $i++) {
	$ref_domain[] = trim(mysql_result($r, $i, 0));
	$count[] = mysql_result($r, $i, 1);
}

if (!empty($ref_domain)) {

$html .= se_stat_divpages($cnrow, $CONFIG['limitpage']);
$html .= "<center><table class='tblval_report' border=0 width=100%>";

for ($i=0; $i < mysql_num_rows($r); $i++) {
    $pr = sprintf("%01.2f", $count[$i]*100/$countsum)."%";
    $pgim = $count[$i]*500/max($count);
	if ($class != "trodd") $class = "trodd"; else $class = "treven";
	$html .= "<tr class=\"".$class."\">\n";
   	$html .= "\t<td align=left width=550><a href='http://".$ref_domain[$i]."' target='_blank' title='перейти на сайт'>".$ref_domain[$i]."</a>";

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