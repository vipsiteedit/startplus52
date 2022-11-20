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
	$r = mysql_query("SELECT id_user AS `idus`,
                             MAX(UNIX_TIMESTAMP(concat(date, time))) -
                             MIN(UNIX_TIMESTAMP(concat(date, time)))AS `dt`
                      FROM stat_log
                      WHERE (concat(stat_log.date, stat_log.time) >= '".$begdate.$begtime."') AND
                            (concat(stat_log.date, stat_log.time) <= '".$enddate.$endtime."')
                      GROUP BY id_session
                      ORDER BY id_user;");

//    while ($row = mysql_fetch_array($r, MYSQL_ASSOC)) {print_r($row);echo "<br>";}

    $a_timeview['0'] = $a_timeview['2'] = $a_timeview['2_10'] =
           $a_timeview['10_30'] = $a_timeview['30_60'] = $a_timeview['60'] = $sum = 0;

    for ($i=0; $i < mysql_num_rows($r); $i++) {
        if (mysql_result($r,$i,1) == 0) { $a_timeview['0']++;
        }else
        if ((mysql_result($r,$i,1) > 0)&&(mysql_result($r,$i,1) <= 2*60)) { $a_timeview['2']++;
        }else
        if ((mysql_result($r,$i,1) > 2*60)&&(mysql_result($r,$i,1) <= 10)) { $a_timeview['2_10']++;
        }else
        if ((mysql_result($r,$i,1) > 10*60)&&(mysql_result($r,$i,1) <= 30)) { $a_timeview['10_30']++;
        }else
        if ((mysql_result($r,$i,1) > 30*60)&&(mysql_result($r,$i,1) <= 60)) { $a_timeview['30_60']++;
        }else
        if (mysql_result($r,$i,1) > 60*60) { $a_timeview['60']++;
        }
    } /* of for */
    $sum = $a_timeview['0'] + $a_timeview['2'] + $a_timeview['2_10'] +
           $a_timeview['10_30'] + $a_timeview['30_60'] + $a_timeview['60'];

    $pr0 = $pr2 = $pr2_10 = $pr10_30 = $pr30_60 = $pr60 = "0.00%";

    if ($sum > 0) {
        $pr0 = sprintf("%01.2f", $a_timeview['0']*100/$sum)."%";
        $pr2 = sprintf("%01.2f", $a_timeview['2']*100/$sum)."%";
        $pr2_10 = sprintf("%01.2f", $a_timeview['2_10']*100/$sum)."%";
        $pr10_30 = sprintf("%01.2f", $a_timeview['10_30']*100/$sum)."%";
        $pr30_60 = sprintf("%01.2f", $a_timeview['30_60']*100/$sum)."%";
        $pr60 = sprintf("%01.2f", $a_timeview['60']*100/$sum)."%";
    }

	$html .= "<center><br><table class='tblval_report' width=100% >";

    $html .= "<tr class=trodd><td align=left width=90%>единичный просмотр";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$a_timeview['0']."' width='".@round($a_timeview['0']*500/max($a_timeview))."' height='5'></div>";
    $html .= "</td><td align=right width=10%>".$a_timeview['0'];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr0."</div>";
    $html .= "</td></tr>\n";

    $html .= "<tr class=treven><td align=left width=90%>до 2 минут";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$a_timeview['2']."' width='".@round($a_timeview['2']*500/max($a_timeview))."' height='5'></div>";
    $html .= "</td><td align=right width=10%>".$a_timeview['2'];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr2."</div>";
    $html .= "</td></tr>\n";

    $html .= "<tr class=trodd><td align=left width=90%>от 2 до 10 минут";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$a_timeview['2_10']."' width='".@round($a_timeview['2_10']*500/max($a_timeview))."' height='5'></div>";
    $html .= "</td><td align=right width=10%>".$a_timeview['2_10'];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr2_10."</div>";
    $html .= "</td></tr>\n";

    $html .= "<tr class=treven><td align=left width=90%>от 10 до 30 минут";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$a_timeview['10_30']."' width='".@round($a_timeview['10_30']*500/max($a_timeview))."' height='5'></div>";
    $html .= "</td><td align=right width=10%>".$a_timeview['10_30'];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr10_30."</div>";
    $html .= "</td></tr>\n";

    $html .= "<tr class=trodd><td align=left width=90%>от 30 минут до часа";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$a_timeview['30_60']."' width='".@round($a_timeview['30_60']*500/max($a_timeview))."' height='5'></div>";
    $html .= "</td><td align=right width=10%>".$a_timeview['30_60'];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr30_60."</div>";
    $html .= "</td></tr>\n";

    $html .= "<tr class=treven><td align=left width=90%>дольше часа";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$a_timeview['60']."' width='".@round($a_timeview['60']*500/max($a_timeview))."' height='5'></div>";
    $html .= "</td><td align=right width=10%>".$a_timeview['60'];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr60."</div>";
    $html .= "</td></tr>\n";

	$html .= "</table></center>";

print $html;
$NOFILTER = 1;
?>