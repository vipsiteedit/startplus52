<?php

$by = htmlspecialchars($_GET["by"], ENT_QUOTES);
$one = htmlspecialchars($_GET["one"], ENT_QUOTES);
$casual = htmlspecialchars($_GET["casual"], ENT_QUOTES);

$ADMENU = "";
if ($one == 'n') {
	$ADMENU .= "<a href='index.php?".se_stat_sqs("one", "y")."'>Показывать одиночный путь</a><br>";
	$ADMENU .= "Не показывать одиночный путь<br>";
}else {
    $one = 'y';
    $ADMENU .= "Показывать одиночный путь<br>";
	$ADMENU .= "<a href='index.php?".se_stat_sqs("one", "n")."'>Не показывать одиночный путь</a><br>";
}
/*
if (!empty($nowrap)||($nowrap == 1)){
    $flusrt = "AND(stat_log.id_user = '".htmlspecialchars($_GET["flusrt"], ENT_QUOTES)."')";
    $begdate = date("Ymd");
    $begtime = "000000";
    $enddate = date("Ymd");
    $endtime = "235959";
}else{
    $flusrt = "";
}
*/
$rd = mysql_query("SELECT name FROM stat_datasuser WHERE type='dm'");
while ($row = mysql_fetch_array($rd, MYSQL_BOTH)) $notindomains[] = $row['name'];
if (!empty($notindomains)) $notind = "'".join("','", $notindomains)."'";
else $notind = "'_'";

set_time_limit(100);
if (!empty($nowrap)||($nowrap == 1)){
$r = mysql_query("SELECT stat_log.id, stat_log.id_session, stat_log.id_user, stat_log.ref_domain, stat_log.domain, stat_log.request_uri, stat_log.titlepage
                  FROM stat_log
                  INNER JOIN stat_sessions ON (stat_log.id_session = stat_sessions.id)
                  WHERE (LENGTH(stat_log.page)>0) AND
                        (stat_log.id_user = '".htmlspecialchars($_GET["flusrt"], ENT_QUOTES)."')
                  ORDER BY id_session ASC, id ASC, concat(date,time);");
}else{
$r = mysql_query("SELECT id, id_session, id_user, ref_domain, domain, request_uri, titlepage
                  FROM stat_log
                  WHERE (concat(stat_log.date, stat_log.time) >= '".$begdate.$begtime."') AND
                        (concat(stat_log.date, stat_log.time) <= '".$enddate.$endtime."') AND
                        (LENGTH(stat_log.page)>0)
                  ORDER BY id_session ASC, id ASC, concat(date,time);");

}

$pth = array(); $pathes = array(); $count = array();
for ($i=0; $i < mysql_num_rows($r); $i++) {
    $id[] = mysql_result($r, $i, 'id');
    $id_ses[] = mysql_result($r, $i, 'id_session');
    $id_us[] = mysql_result($r, $i, 'id_user');
    $ref_d[] = mysql_result($r, $i, 'ref_domain');
    $page[] = mysql_result($r, $i, 'request_uri');
}

for ($i=0; $i < count($id_ses); $i++) {
//    $pth[$id_ses[$i].$id_us[$i]] .= "<table class='tblval_report' border=0 width=100%><tr>";
    if (!empty($page[$i-1])&&(trim($page[$i-1])!=trim($page[$i])))
        $pth[$id_ses[$i].$id_us[$i]] .= "<tr><td class='tbltdpathes'>".$page[$i]."</td></tr>";
    if (empty($pth[$id_ses[$i].$id_us[$i]]))
        $pth[$id_ses[$i].$id_us[$i]] .= "<tr><td class='tbltdpathes'>".$page[$i]."</td></tr>";
//    $pth[$id_ses[$i].$id_us[$i]] .= "</tr></table>";
}

if (!empty($pth)) {
    $pathes_count = array_count_values($pth);
    arsort($pathes_count);
    foreach($pathes_count as $k => $v) {
        $tmp_pathes[] = $k;
        $tmp_count[] = $v;
    }
}

if (!empty($one)&&($one=='n')) {
    for ($i=0; $i < count($tmp_pathes); $i++) {
        if (count(split("<tr>",$tmp_pathes[$i]))>2) {
            $pathes[] = "<table class='tblpathes' border=0 width=100%>".$tmp_pathes[$i]."</table>";
            $count[] = $tmp_count[$i];
        }
    }
}else {
    for ($i=0; $i < count($tmp_pathes); $i++) {
        $pathes[] = "<table class='tblpathes' border=0 width=100%>".$tmp_pathes[$i]."</table>";
        $count[] = $tmp_count[$i];
    }
}

if (!empty($pathes)) {

$cnrow = count($pathes);

if (!empty($nowrap)||($nowrap == 1)){

    $html .= "<center><br><table class='tblval_report' border=0 width=100%>";
    if ($class != "trodd") $class = "trodd"; else $class = "treven";
	$html .= "<tr class=".$class."><td align=left width=100%>".$pathes[0]."</td></tr></table></center>";

}else{

$html .= se_stat_divpages($cnrow, $CONFIG['limitpage']);
$html .= "<center><br><table class='tblval_report' border=0 width=100%>";

if ((!empty($sheet))&&($sheet > 1)) $limitpage = ($CONFIG['limitpage']*$sheet-$CONFIG['limitpage']);
else $limitpage = 0;

for ($i=$limitpage; $i < $limitpage+$CONFIG['limitpage']; $i++) {
    if (!empty($pathes[$i])) {
    $pr = sprintf("%01.2f", $count[$i]*100/array_sum($count))."%";
    $pgim = $count[$i]*500/max($count);
    if ($class != "trodd") $class = "trodd"; else $class = "treven";
	$html .= "<tr class=".$class.">\n";
   	$html .= "\t<td align=left width=550>".$pathes[$i];

    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$count[$i]."' width='".$pgim."' height='5'></div>";
    $html .= "</td><td align=right width=50>".$count[$i];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr."</div>";
    $html .= "</td></tr>\n";
    }
}
$html .= "</table></center>";
$html .= se_stat_divpages($cnrow, $CONFIG['limitpage']);
}
//echo array_sum($count);
}else $html = "<center><table class='tblval_report' border=0 width=100%><tr class='tbltitle'><td align='center'><i>НЕТ ДАННЫХ</i></td></tr></table></center>";


print $html;

?>