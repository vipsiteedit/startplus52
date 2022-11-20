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
	$r = mysql_query("SELECT ip, COUNT(DISTINCT page) AS pages
                      FROM stat_log
                      WHERE (concat(stat_log.date, stat_log.time) >= '".$begdate.$begtime."') AND
                            (concat(stat_log.date, stat_log.time) <= '".$enddate.$endtime."')
                      GROUP BY ip;");

//    while ($row = mysql_fetch_array($r, MYSQL_ASSOC)) {print_r($row);echo "<br>";}

	$a_hosts['1'] = $a_hosts['2'] = $a_hosts['3'] = $a_hosts['4'] = $a_hosts['5'] =
    $a_hosts['6_10'] = $a_hosts['11_50'] = $a_hosts['51_100'] = $a_hosts['100'] = $a_hosts['ave'] = $sum = 0;
	for ($i=0; $i < mysql_num_rows($r); $i++) {
		$ip = mysql_result($r, $i, 0);
        if (mysql_result($r,$i,1) == 1) { $a_hosts['1']++;
        }else
        if (mysql_result($r,$i,1) == 2) { $a_hosts['2']++;
        }else
        if (mysql_result($r,$i,1) == 3) { $a_hosts['3']++;
        }else
        if (mysql_result($r,$i,1) == 4) { $a_hosts['4']++;
        }else
        if (mysql_result($r,$i,1) == 5) { $a_hosts['5']++;
        }else
        if ((mysql_result($r,$i,1) >= 6)&&(mysql_result($r,$i,1) <= 10)) { $a_hosts['6_10']++;
        }else
        if ((mysql_result($r,$i,1) >= 11)&&(mysql_result($r,$i,1) <= 50)) { $a_hosts['11_50']++;
        }else
        if ((mysql_result($r,$i,1) >= 51)&&(mysql_result($r,$i,1) <= 100)) { $a_hosts['51_100']++;
        }else
        if (mysql_result($r,$i,1) > 100) { $a_hosts['100']++;
        }
    } /* of for */
    $sum = $a_hosts['1'] + $a_hosts['2'] + $a_hosts['3'] + $a_hosts['4'] + $a_hosts['5'] +
           $a_hosts['6_10'] + $a_hosts['11_50'] + $a_hosts['51_100'] + $a_hosts['100'];
    if (mysql_num_rows($r) > 0) $a_hosts['ave'] = sprintf("%01.2f", $sum / mysql_num_rows($r));

    $pr1 = $pr2 = $pr3 = $pr4 = $pr5 = $pr6_10 = $pr11_50 = $pr51_100 = $pr100 = "0.00%";

    if ($sum > 0) {
        $pr1 = sprintf("%01.2f", $a_hosts['1']*100/$sum)."%";
        $pr2 = sprintf("%01.2f", $a_hosts['2']*100/$sum)."%";
        $pr3 = sprintf("%01.2f", $a_hosts['3']*100/$sum)."%";
        $pr4 = sprintf("%01.2f", $a_hosts['4']*100/$sum)."%";
        $pr5 = sprintf("%01.2f", $a_hosts['5']*100/$sum)."%";
        $pr6_10 = sprintf("%01.2f", $a_hosts['6_10']*100/$sum)."%";
        $pr11_50 = sprintf("%01.2f", $a_hosts['11_50']*100/$sum)."%";
        $pr51_100 = sprintf("%01.2f", $a_hosts['51_100']*100/$sum)."%";
        $pr100 = sprintf("%01.2f", $a_hosts['100']*100/$sum)."%";
    }
	$html .= "<center><br><table class='tblval_report' width=100%>";

    $html .= "<tr class=trodd><td align=left width=90%>1 страница";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$a_hosts['1']."' width='".@round($a_hosts['1']*500/max($a_hosts))."' height='5'></div>";
    $html .= "</td><td align=right width=10%>".$a_hosts['1'];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr1."</div>";
    $html .= "</td></tr>\n";

    $html .= "<tr class=treven><td align=left width=90%>2 страницы";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$a_hosts['2']."' width='".@round($a_hosts['2']*500/max($a_hosts))."' height='5'></div>";
    $html .= "</td><td align=right width=10%>".$a_hosts['2'];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr2."</div>";
    $html .= "</td></tr>\n";

    $html .= "<tr class=trodd><td align=left width=90%>3 страницы";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$a_hosts['3']."' width='".@round($a_hosts['3']*500/max($a_hosts))."' height='5'></div>";
    $html .= "</td><td align=right width=10%>".$a_hosts['3'];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr3."</div>";
    $html .= "</td></tr>\n";

    $html .= "<tr class=treven><td align=left width=90%>4 страницы";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$a_hosts['4']."' width='".@round($a_hosts['4']*500/max($a_hosts))."' height='5'></div>";
    $html .= "</td><td align=right width=10%>".$a_hosts['4'];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr4."</div>";
    $html .= "</td></tr>\n";

    $html .= "<tr class=trodd><td align=left width=90%>5 страниц";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$a_hosts['5']."' width='".@round($a_hosts['5']*500/max($a_hosts))."' height='5'></div>";
    $html .= "</td><td align=right width=10%>".$a_hosts['5'];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr5."</div>";
    $html .= "</td></tr>\n";

    $html .= "<tr class=treven><td align=left width=90%>от 6 до 10 страниц";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$a_hosts['6_10']."' width='".@round($a_hosts['6_10']*500/max($a_hosts))."' height='5'></div>";
    $html .= "</td><td align=right width=10%>".$a_hosts['6_10'];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr6_10."</div>";
    $html .= "</td></tr>\n";

    $html .= "<tr class=trodd><td align=left width=90%>от 11 до 50 страниц";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$a_hosts['11_50']."' width='".@round($a_hosts['11_50']*500/max($a_hosts))."' height='5'></div>";
    $html .= "</td><td align=right width=10%>".$a_hosts['11_50'];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr11_50."</div>";
    $html .= "</td></tr>\n";

    $html .= "<tr class=treven><td align=left width=90%>от 51 до 100 страниц";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$a_hosts['51_100']."' width='".@round($a_hosts['51_100']*500/max($a_hosts))."' height='5'></div>";
    $html .= "</td><td align=right width=10%>".$a_hosts['51_100'];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr51_100."</div>";
    $html .= "</td></tr>\n";

    $html .= "<tr class=trodd><td align=left width=90%>более 100 страниц";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$a_hosts['100']."' width='".@round($a_hosts['100']*500/max($a_hosts))."' height='5'></div>";
    $html .= "</td><td align=right width=10%>".$a_hosts['100'];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr100."</div>";
    $html .= "</td></tr>\n";

    $html .= "<tr class=treven><td align=left width=90%>Средняя глубина просмотра<br>
                <td align=right width=10%>".$a_hosts['ave']."</td></tr>\n";

	$html .= "</table></center>";

print $html;
$NOFILTER = 1;
?>