<?php

$filter = htmlspecialchars($_GET['filter'], ENT_QUOTES);
$day = intval(htmlspecialchars($_GET['day'], ENT_QUOTES));
$month = intval(htmlspecialchars($_GET['month'], ENT_QUOTES));
$year = intval(htmlspecialchars($_GET['year'], ENT_QUOTES));
$prom = intval(htmlspecialchars($_GET['prom'], ENT_QUOTES));
$s = htmlspecialchars($_GET['s'], ENT_QUOTES);
/*
$begdate = date("Ymd", $sdt);
$enddate = date("Ymd", $fdt);
$begtime = date("His", $sdt);
$endtime = date("His", $fdt);
*/

/// *** Выводим таблицу *** ///
    $a_users1 = array(); $a_users2 = array();

    $usr1 = mysql_query("SELECT DISTINCT id_user AS users FROM stat_log WHERE (concat(date, time) < '".$begdate.$begtime."');");
    while ($usrow1 = mysql_fetch_array($usr1, MYSQL_ASSOC)) $a_users1[] = $usrow1['users'];

    $usr2 = mysql_query("SELECT DISTINCT id_user AS users FROM stat_log
                       WHERE (concat(stat_log.date, stat_log.time) >= '".$begdate.$begtime."') AND
                             (concat(stat_log.date, stat_log.time) <= '".$enddate.$endtime."');");
    while ($usrow2 = mysql_fetch_array($usr2, MYSQL_ASSOC)) $a_users2[] = $usrow2['users'];

    $newusers = count(array_diff($a_users2, $a_users1));
    $oldusers = count(array_intersect($a_users2, $a_users1));

    $opr = $npr = "0.00%";
    if (($oldusers > 0)||($newusers > 0)) {
        $opr = sprintf("%01.2f", $oldusers*100/($oldusers+$newusers))."%";
        $npr = sprintf("%01.2f", $newusers*100/($oldusers+$newusers))."%";
    }

    $html .= "<center><br><table class='tblval_report' border=0 width=100%>";

    $html .= "<tr class=trodd><td align=left width=90%>Старые пользователи";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$oldusers."' width='".@round($oldusers*500/max($oldusers,$newusers))."' height='5'></div>";
    $html .= "</td><td align=right width=50>".$oldusers;
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$opr."</div>";
    $html .= "</td></tr>\n";

    $html .= "<tr class=treven><td align=left width=90%>Новые пользователи";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$newusers."' width='".@round($newusers*500/max($oldusers,$newusers))."' height='5'></div>";
    $html .= "</td><td align=right width=50>".$newusers;
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$npr."</div>";
    $html .= "</td></tr>\n";

	$html .= "</table></center>";

print $html;

?>