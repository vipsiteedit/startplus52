<?php

if (!empty($_GET["by"])) $by = htmlspecialchars($_GET["by"], ENT_QUOTES);
else $by = "";
if (!empty($_GET["vw"])) $vw = htmlspecialchars($_GET["vw"], ENT_QUOTES);
else $vw = "";

$ADMENU = "";
/*
if ($vw == "list") {
	$ADMENU .= "<a href='index.php?".se_stat_sqs("", "")."'>Отчет</a><br>";
	$ADMENU .= "Список партнеров<br><br>";
}else {
	$ADMENU .= "Отчет<br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("vw", "list")."'>Список партнеров</a><br><br>";
}
*/
if ($by == "hits") {
	$ADMENU .= "Для хитов<br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "hosts")."'>Для хостов</a><br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "users")."'>Для пользователей</a><br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("by", "views")."'>Для просмотров</a><br>";
    $selcn = "DISTINCT stat_log.id_session, stat_log.ref_domain";
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
    $selcn = "DISTINCT stat_log.id_session, stat_log.ref_domain";
}

$rd = mysql_query("SELECT name FROM stat_datasuser WHERE type='dm'");
while ($row = mysql_fetch_array($rd, MYSQL_BOTH)) $notindomains[] = $row['name'];
if (!empty($notindomains)) $notind = "'".join("','", $notindomains)."'";
else $notind = "'_'";

$r = mysql_query("SELECT ref_domain, COUNT(".$selcn.") AS `cn`
                  FROM stat_log
                  WHERE (concat(stat_log.date, stat_log.time) >= '".$begdate.$begtime."') AND
                        (concat(stat_log.date, stat_log.time) <= '".$enddate.$endtime."') AND
                        (ref_domain NOT IN (".$notind."))
                  GROUP BY 1
                  HAVING (LENGTH(ref_domain)>0)
                  ORDER BY 2 desc;");
while ($rowr = mysql_fetch_array($r, MYSQL_BOTH)) {
    $refdomain[] = $rowr["ref_domain"];
    $refdomaincn[] = $rowr["cn"];
}

// партнеры
$rd = mysql_query("SELECT name, d1 FROM stat_datasuser WHERE type='pr'");
while ($row = mysql_fetch_array($rd, MYSQL_BOTH)) {
    $indomains[] = explode("|", $row["d1"]);
    $indomainsname[] = $row["name"];
}

$cnoth = 0; $count = array();
for ($i=0; $i < count($refdomain); $i++) {
    for ($j=0; $j < count($indomains); $j++) {
        if (in_array($refdomain[$i], $indomains[$j])) {
            $partner[] = $indomainsname[$j];
            $partnerurl[] = $refdomain[$i];
        	$count[] = $refdomaincn[$i];
        }else {
            $cnoth += $refdomaincn[$i];
        }
    }
}

if (!empty($partner)) {

$html .= "<center><br><table class='tblval_report' border=0 width=100%>";

for ($i=0; $i < COUNT($partner); $i++) {
    $pr = sprintf("%01.2f", $count[$i]*100/array_sum($count))."%";
    $pgim = $count[$i]*500/max($count);
	if ($class != "trodd") $class = "trodd"; else $class = "treven";

	$html .= "<tr class=\"".$class."\">\n";
   	$html .= "\t<td align=left width=550><a href='http://".$partnerurl[$i]."' target='_blank' title='перейти на сайт'>".$partner[$i]."</a>";

    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$count[$i]."' width='".$pgim."' height='5'></div>";
    $html .= "</td><td align=right width=50>".$count[$i];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr."</div>";
    $html .= "</td></tr>\n";
}

	$html .= "<tr class='tbltitle'><td align='left' colspan=2><b>Всего:</b></td></tr>
              <tr class='trodd'><td align='left' width=550>Партнеры</td>
                  <td align=right width=50>".array_sum($count)."<br><div class=hint>".@sprintf("%01.2f", array_sum($count)*100/(array_sum($count)+$cnoth))."%</div></td></tr>
              <tr class='treven'><td align='left' width=550>Остальные ссылки</td>
                  <td align=right width=50>".$cnoth."<br><div class=hint>".@sprintf("%01.2f", $cnoth*100/(array_sum($count)+$cnoth))."%</div></td></tr>";

$html.="</table></center>";

}else $html = "<center><table class='tblval_report' border=0 width=100%><tr class='tbltitle'><td align='center'><i>НЕТ ДАННЫХ</i></td></tr></table></center>";

print $html;

?>