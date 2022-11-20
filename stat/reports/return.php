<?php

$day = intval(htmlspecialchars($_GET['day'], ENT_QUOTES));
$month = intval(htmlspecialchars($_GET['month'], ENT_QUOTES));
$year = intval(htmlspecialchars($_GET['year'], ENT_QUOTES));
$prom = intval(htmlspecialchars($_GET['prom'], ENT_QUOTES));
$s = htmlspecialchars($_GET['s'], ENT_QUOTES);


/// *** Выводим таблицу *** ///
/*SELECT a.id_user AS `idus`,
           UNIX_TIMESTAMP(concat(a.date,a.time)) AS `dt1`,
           (SELECT UNIX_TIMESTAMP(concat(date,time)) as `dt` FROM stat_log
            WHERE (id_user=`idus`)AND
            (UNIX_TIMESTAMP(concat(date,time))>`dt1`) LIMIT 1) AS `dt2`
FROM stat_log as a
WHERE (a.date >= '20070101')AND(a.date <= '20070105')
HAVING (`dt2`<>'NULL')AND((`dt2`-`dt1`)>=15*60)
ORDER BY a.id*/
    $r = mysql_query("SELECT id_user AS `idus`, UNIX_TIMESTAMP(concat(date,time)) AS `dt1`, UNIX_TIMESTAMP(concat(date,time)) AS `dt2`
                      FROM stat_log
                      WHERE (concat(stat_log.date, stat_log.time) >= '".$begdate.$begtime."') AND
                            (concat(stat_log.date, stat_log.time) <= '".$enddate.$endtime."')
                      HAVING `dt2` > `dt1`
                      ORDER BY 2;");

//    while ($row = mysql_fetch_array($r, MYSQL_ASSOC)) {print_r($row);echo "<br>";}

	$a_return['15m_30m'] = $a_return['30m_1h'] = $a_return['1h_3h'] = $a_return['3h_12h'] = $a_return['12h_1d'] =
    $a_return['1d_3d'] = $a_return['3d_7d'] = $a_return['7d_14d'] = $a_return['14d_31d'] = $a_return['unic'] = $a_return['ret'] = $sum = 0;
	for ($i=0; $i < mysql_num_rows($r); $i++) {
		$ip = mysql_result($r, $i, 0);
        if ((mysql_result($r,$i,1) >= 6)&&(mysql_result($r,$i,1) <= 10)) { $a_return['15m_30m']++;
        }else
        if ((mysql_result($r,$i,1) >= 6)&&(mysql_result($r,$i,1) <= 10)) { $a_return['30m_1h']++;
        }else
        if ((mysql_result($r,$i,1) >= 6)&&(mysql_result($r,$i,1) <= 10)) { $a_return['1h_3h']++;
        }else
        if ((mysql_result($r,$i,1) >= 6)&&(mysql_result($r,$i,1) <= 10)) { $a_return['3h_12h']++;
        }else
        if ((mysql_result($r,$i,1) >= 6)&&(mysql_result($r,$i,1) <= 10)) { $a_return['12h_1d']++;
        }else
        if ((mysql_result($r,$i,1) >= 6)&&(mysql_result($r,$i,1) <= 10)) { $a_return['1d_3d']++;
        }else
        if ((mysql_result($r,$i,1) >= 11)&&(mysql_result($r,$i,1) <= 50)) { $a_return['3d_7d']++;
        }else
        if ((mysql_result($r,$i,1) >= 51)&&(mysql_result($r,$i,1) <= 100)) { $a_return['7d_14d']++;
        }else
        if ((mysql_result($r,$i,1) >= 51)&&(mysql_result($r,$i,1) <= 100)) { $a_return['14d_31d']++;
        }
    } /* of for */
    $sum = $a_return['15m_30m'] + $a_return['30m_1h'] + $a_return['1h_3h'] + $a_return['3h_12h'] + $a_return['12h_1d'] +
           $a_return['1d_3d'] + $a_return['3d_7d'] + $a_return['7d_14d'] + $a_return['14d_31d'];

    $pr15m_30m = $pr30m_1h = $pr1h_3h = $pr3h_12h = $pr12h_1d = $pr1d_3d = $pr3d_7d = $pr7d_14d = $pr14d_31d = 0;

    if ($sum > 0) {
        $pr15m_30m = sprintf("%01.2f", $a_return['15m_30m']*100/$sum)."%";
        $pr30m_1h = sprintf("%01.2f", $a_return['30m_1h']*100/$sum)."%";
        $pr1h_3h = sprintf("%01.2f", $a_return['1h_3h']*100/$sum)."%";
        $pr3h_12h = sprintf("%01.2f", $a_return['3h_12h']*100/$sum)."%";
        $pr12h_1d = sprintf("%01.2f", $a_return['12h_1d']*100/$sum)."%";
        $pr1d_3d = sprintf("%01.2f", $a_return['1d_3d']*100/$sum)."%";
        $pr3d_7d = sprintf("%01.2f", $a_return['3d_7d']*100/$sum)."%";
        $pr7d_14d = sprintf("%01.2f", $a_return['7d_14d']*100/$sum)."%";
        $pr14d_31d = sprintf("%01.2f", $a_return['14d_31d']*100/$sum)."%";
    }

	$html .= "<center><br>ОТЧЕТ&nbsp;НЕ&nbsp;РАБОТАЕТ<table class='tblval_report' border=0 width=100% >";

    $html .= "<tr class=trodd><td align=left width=90%>от 15 до 30 минут</td>
                <td align=right width=10%>".$a_return['15m_30m']."<br><div class=hint>".$pr15m_30m."</div></td></tr>\n";

    $html .= "<tr class=treven><td align=left width=90%>от 30 минут до 1 часа</td>
                <td align=right width=10%>".$a_return['30m_1h']."<br><div class=hint>".$pr30m_1h."</div></td></tr>\n";

    $html .= "<tr class=trodd><td align=left width=90%>от 1 до 3 часов</td>
                <td align=right width=10%>".$a_return['1h_3h']."<br><div class=hint>".$pr1h_3h."</div></td></tr>\n";

    $html .= "<tr class=treven><td align=left width=90%>от 3 до 12 часов</td>
                <td align=right width=10%>".$a_return['3h_12h']."<br><div class=hint>".$pr3h_12h."</div></td></tr>\n";

    $html .= "<tr class=trodd><td align=left width=90%>от 12 часов до суток</td>
                <td align=right width=10%>".$a_return['12h_1d']."<br><div class=hint>".$pr12h_1d."</div></td></tr>\n";

    $html .= "<tr class=treven><td align=left width=90%>от 1 до 3 дней</td>
                <td align=right width=10%>".$a_return['1d_3d']."<br><div class=hint>".$pr1d_3d."</div></td></tr>\n";

    $html .= "<tr class=trodd><td align=left width=90%>от 3 до 7 дней</td>
                <td align=right width=10%>".$a_return['3d_7d']."<br><div class=hint>".$pr3d_7d."</div></td></tr>\n";

    $html .= "<tr class=treven><td align=left width=90%>от 7 до 14 дней</td>
                <td align=right width=10%>".$a_return['7d_14d']."<br><div class=hint>".$pr7d_14d."</div></td></tr>\n";

    $html .= "<tr class=trodd><td align=left width=90%>от 14 до 31 дня</td>
                <td align=right width=10%>".$a_return['14d_31d']."<br><div class=hint>".$pr14d_31d."</div></td></tr>\n";

    $html .= "<tr><td colspan=2 align=left><b>Всего:</b></td></tr>\n";

    $html .= "<tr class=treven><td align=left width=90%>Всего уникальных пользователей за выбранный период</td>
                <td align=right width=10%>".$a_return['unic']."</td></tr>\n";

    $html .= "<tr class=trodd><td align=left width=90%>Из них вернувшихся</td>
                <td align=right width=10%>".$a_return['ret']."</td></tr>\n";

	$html .= "</table></center>";

print $html;
$NOFILTER = 1;
?>