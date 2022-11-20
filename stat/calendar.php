<?php

    $y = date("Y", $fdt);
    $m = date("m", $fdt);

    $mdays_noleap = Array(31,28,31,30,31,30,31,31,30,31,30,31);
    $mdays_leap = Array(31,29,31,30,31,30,31,31,30,31,30,31);
    $month = Array('янв','фев','мар','апр','май','июн','июл','авг','сен','окт','ноя','дек');
    $smonth = Array('Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь');

	$day = intval(date("d", $fdt));

	if (date("L",mktime(0,0,0,1,1,$y))) $mdays = $mdays_leap;
    else $mdays = $mdays_noleap;

	$dow = date("w", mktime(0,0,0, $m,1,$y));

	if ($dow == 0) $dow = 7;

	$m = intval($m);
	if ($m == 12) { $mn = 1; $yn = $y+1; }
    else { $mn = $m+1; $yn = $y; }
	if ($m == 1) { $mp = 12; $yp = $y-1; }
    else { $mp = $m-1; $yp = $y; }

    //if (date("Ymd", mktime(0,0,0, $mn,$day,$yn)) > date("Ymd")) { $mn = date("n"); $day = date("d"); $yn = date("Y"); }

    $d = "";
    $d .= '<table class="caltblmain" width="150px" border="0" cellpadding="1" cellspacing="0"><tr><td>';
	$d .= '<table class="caltbl" width="100%" cellpadding="1" cellspacing="1" border="0">
              <tr class="caltdtitle">';

    $d .= '<td width="25%" class="calamonth" align="right">
               <a href=\'?'.se_stat_sqs("sdt;fdt", mktime(0,0,0, $mp,$day,$yp).";".mktime(23,59,59, $mp,$day,$yp)).'\'>
                  «&nbsp;'.$month[$mp-1].'</a></td>';

	$d .= '<td width="50%" valign="top" align="center" class="caltekmonth">'.$y.'<br>'.$smonth[$m-1].'</td>';

    if (date("Ym", mktime(0,0,0, $m,1,$y)) < date("Ym"))
	    $d .= '<td width="25%" class="calamonth" align="left">
              <a href=\'?'.se_stat_sqs("sdt;fdt", mktime(0,0,0, $mn,$day,$yn).";".mktime(23,59,59, $mn,$day,$yn)).'\'>
                 '.$month[$mn-1].'&nbsp;»</a></td>';
    else
        $d .= '<td width="25%" class="calmonth" align="left">'.$month[$mn-1].'</td>';

	$d .= "</tr></table>";                      

	$d .= '<table class="caltbl" width="100%" cellpadding="1" cellspacing="1" border="0">
	<tr>
      <td align="center" class="caltdweekday">пн</td>
      <td align="center" class="caltdweekday">вт</td>
      <td align="center" class="caltdweekday">ср</td>
      <td align="center" class="caltdweekday">чт</td>
      <td align="center" class="caltdweekday">пт</td>
      <td align="center" class="caltdweekday">сб</td>
      <td align="center" class="caltdweekday">вс</td>
    </tr>
    <tr class="">';
	if ($dow != 1) $d .= '<td colspan="'.($dow-1).'" class="caltdday">&nbsp;</td>';
	$i=1;
	do {
		if ($i == $day) {
            $classtd = 'class="caltddayactive"'; $classtxt = 'class="caldayactive"';
        }else {
            $classtd = 'class="caltdday"'; $classtxt = '';
        }
        $d .= '<td width="14%" align="right" '.$classtd.'>';
        if (date("Ymd", mktime(0,0,0, $m,$i,$y)) <= date("Ymd"))
            $d .= '<a href="?'.se_stat_sqs("sdt;fdt", mktime(0,0,0, $m,$i,$y).";".mktime(23,59,59, $m,$i,$y)).'"><font '.$classtxt.'>'.$i.'</font></a>';
        else
            $d .= '<font class="caldaypas">'.$i.'</font>';
        $d .= "</td>";
		$i++;
		$dow++;
		if ($dow > 7) { $d .= '</tr><tr class="">'; $dow=1; }
	} while ($i <= $mdays[$m-1]);

	if ($dow > 1) $d .= '<td colspan='.(8-$dow).' class="caltdday">&nbsp;</td>';

	$d .= "</tr></table>";
	$d .= "</td></tr></table>";

print $d;

?>