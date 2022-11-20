<?php

$day = intval(htmlspecialchars($_GET['day'], ENT_QUOTES));
$month = intval(htmlspecialchars($_GET['month'], ENT_QUOTES));
$year = intval(htmlspecialchars($_GET['year'], ENT_QUOTES));
$prom = intval(htmlspecialchars($_GET['prom'], ENT_QUOTES));
$s = htmlspecialchars($_GET['s'], ENT_QUOTES);

/// *** Выводим таблицу *** ///
set_time_limit(100);
$r = mysql_query("SELECT page_rateload, COUNT(id) AS `cn`
                  FROM stat_log
                  WHERE (concat(stat_log.date, stat_log.time) >= '".$begdate.$begtime."') AND
                        (concat(stat_log.date, stat_log.time) <= '".$enddate.$endtime."') AND
                        (page_rateload>0)
                  GROUP BY page_rateload;");

//    while ($row = mysql_fetch_array($r, MYSQL_ASSOC)) {print_r($row);echo "<br>";}

    $a_rate['1'] = $a_rate['1_2'] = $a_rate['2_3'] = $a_rate['3_4'] = $a_rate['4_5'] = $a_rate['6_10'] =
    $a_rate['11_20'] = $a_rate['21_30'] = $a_rate['31_45'] = $a_rate['46_60'] = $a_rate['60'] = $sum = 0;
	for ($i=0; $i < mysql_num_rows($r); $i++) {
		$ip = mysql_result($r, $i, 0);
        if (mysql_result($r,$i,0) < 1) { $a_rate['1'] += mysql_result($r,$i,1);
        }else
        if ((mysql_result($r,$i,0) >= 1)&&(mysql_result($r,$i,0) <= 2)) { $a_rate['1_2'] += mysql_result($r,$i,1);
        }else
        if ((mysql_result($r,$i,0) >= 2)&&(mysql_result($r,$i,0) <= 3)) { $a_rate['2_3'] += mysql_result($r,$i,1);
        }else
        if ((mysql_result($r,$i,0) >= 3)&&(mysql_result($r,$i,0) <= 4)) { $a_rate['3_4'] += mysql_result($r,$i,1);
        }else
        if ((mysql_result($r,$i,0) >= 4)&&(mysql_result($r,$i,0) <= 5)) { $a_rate['4_5'] += mysql_result($r,$i,1);
        }else
        if ((mysql_result($r,$i,0) >= 6)&&(mysql_result($r,$i,0) <= 10)) { $a_rate['6_10'] += mysql_result($r,$i,1);
        }else
        if ((mysql_result($r,$i,0) >= 11)&&(mysql_result($r,$i,0) <= 20)) { $a_rate['11_20'] += mysql_result($r,$i,1);
        }else
        if ((mysql_result($r,$i,0) >= 21)&&(mysql_result($r,$i,0) <= 30)) { $a_rate['21_30'] += mysql_result($r,$i,1);
        }else
        if ((mysql_result($r,$i,0) >= 31)&&(mysql_result($r,$i,0) <= 45)) { $a_rate['31_45'] += mysql_result($r,$i,1);
        }else
        if ((mysql_result($r,$i,0) >= 46)&&(mysql_result($r,$i,0) <= 60)) { $a_rate['46_60'] += mysql_result($r,$i,1);
        }else
        if (mysql_result($r,$i,0) > 60) { $a_rate['60'] += mysql_result($r,$i,1);
        }
    } /* of for */
    $sum = $a_rate['1'] + $a_rate['1_2'] + $a_rate['2_3'] + $a_rate['3_4'] + $a_rate['4_5'] + $a_rate['6_10'] +
           $a_rate['11_20'] + $a_rate['21_30'] + $a_rate['31_45'] + $a_rate['46_60'] + $a_rate['60'];

    $pr1 = $pr1_2 = $pr2_3 = $pr3_4 = $pr4_5 = $pr6_10 = $pr11_20 = $pr21_30 = $pr31_45 = $pr46_60 = $pr60 = "0.00%";

    if ($sum > 0) {
        $pr1 = sprintf("%01.2f", $a_rate['1']*100/$sum)."%";
        $pr1_2 = sprintf("%01.2f", $a_rate['1_2']*100/$sum)."%";
        $pr2_3 = sprintf("%01.2f", $a_rate['2_3']*100/$sum)."%";
        $pr3_4 = sprintf("%01.2f", $a_rate['3_4']*100/$sum)."%";
        $pr4_5 = sprintf("%01.2f", $a_rate['4_5']*100/$sum)."%";
        $pr6_10 = sprintf("%01.2f", $a_rate['6_10']*100/$sum)."%";
        $pr11_20 = sprintf("%01.2f", $a_rate['11_20']*100/$sum)."%";
        $pr21_30 = sprintf("%01.2f", $a_rate['21_30']*100/$sum)."%";
        $pr31_45 = sprintf("%01.2f", $a_rate['31_45']*100/$sum)."%";
        $pr46_60 = sprintf("%01.2f", $a_rate['46_60']*100/$sum)."%";
        $pr60 = sprintf("%01.2f", $a_rate['60']*100/$sum)."%";
    }

    $html .= "<center><br><table class='tblval_report' border=0 width=100%>";

    $html .= "<tr class=trodd><td align=left width=90%>Менее 1 секунды";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$a_rate['1']."' width='".@round($a_rate['1']*500/max($a_rate))."' height='5'></div>";
    $html .= "</td><td align=right width=10%>".$a_rate['1'];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr1."</div>";
    $html .= "</td></tr>\n";

    $html .= "<tr class=treven><td align=left width=90%>1-2 секунды";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$a_rate['1_2']."' width='".@round($a_rate['1_2']*500/max($a_rate))."' height='5'></div>";
    $html .= "</td><td align=right width=10%>".$a_rate['1_2'];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr1_2."</div>";
    $html .= "</td></tr>\n";

    $html .= "<tr class=trodd><td align=left width=90%>2-3 секунды";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$a_rate['2_3']."' width='".@round($a_rate['2_3']*500/max($a_rate))."' height='5'></div>";
    $html .= "</td><td align=right width=10%>".$a_rate['2_3'];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr2_3."</div>";
    $html .= "</td></tr>\n";

    $html .= "<tr class=treven><td align=left width=90%>3-4 секунды";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$a_rate['3_4']."' width='".@round($a_rate['3_4']*500/max($a_rate))."' height='5'></div>";
    $html .= "</td><td align=right width=10%>".$a_rate['3_4'];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr3_4."</div>";
    $html .= "</td></tr>\n";

    $html .= "<tr class=trodd><td align=left width=90%>4-5 секунд";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$a_rate['4_5']."' width='".@round($a_rate['4_5']*500/max($a_rate))."' height='5'></div>";
    $html .= "</td><td align=right width=10%>".$a_rate['4_5'];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr4_5."</div>";
    $html .= "</td></tr>\n";

    $html .= "<tr class=treven><td align=left width=90%>6-10 секунд";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$a_rate['6_10']."' width='".@round($a_rate['6_10']*500/max($a_rate))."' height='5'></div>";
    $html .= "</td><td align=right width=10%>".$a_rate['6_10'];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr6_10."</div>";
    $html .= "</td></tr>\n";

    $html .= "<tr class=trodd><td align=left width=90%>11-20 секунд";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$a_rate['11_20']."' width='".@round($a_rate['11_20']*500/max($a_rate))."' height='5'></div>";
    $html .= "</td><td align=right width=10%>".$a_rate['11_20'];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr11_20."</div>";
    $html .= "</td></tr>\n";

    $html .= "<tr class=treven><td align=left width=90%>21-30 секунд";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$a_rate['21_30']."' width='".@round($a_rate['21_30']*500/max($a_rate))."' height='5'></div>";
    $html .= "</td><td align=right width=10%>".$a_rate['21_30'];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr21_30."</div>";
    $html .= "</td></tr>\n";

    $html .= "<tr class=trodd><td align=left width=90%>31-45 секунд";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$a_rate['31_45']."' width='".@round($a_rate['31_45']*500/max($a_rate))."' height='5'></div>";
    $html .= "</td><td align=right width=10%>".$a_rate['31_45'];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr31_45."</div>";
    $html .= "</td></tr>\n";

    $html .= "<tr class=treven><td align=left width=90%>46-60 секунд";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$a_rate['46_60']."' width='".@round($a_rate['46_60']*500/max($a_rate))."' height='5'></div>";
    $html .= "</td><td align=right width=10%>".$a_rate['46_60'];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr46_60."</div>";
    $html .= "</td></tr>\n";

    $html .= "<tr class=trodd><td align=left width=90%>Более 1 минуты";
    if ($CONFIG['gauge']==1) $html .= "<br><div class=primg><img src='img/color11.gif' alt='".$a_rate['60']."' width='".@round($a_rate['60']*500/max($a_rate))."' height='5'></div>";
    $html .= "</td><td align=right width=10%>".$a_rate['60'];
    if ($CONFIG['percents']==1) $html .= "<br><div class=hint>".$pr60."</div>";
    $html .= "</td></tr>\n";

	$html .= "</table></center>";

print $html;
$NOFILTER = 1;
?>