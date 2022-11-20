<?php
session_start();
session_register("SESTATDATA");
$TMPDATA = $SESTATDATA;
$SESTATDATA = $_SESSION["SESTATDATA"];

if ($s=="on") $need_s = true;
else $need_s = false;

// Количество элементов
$count = 1;
foreach($SESTATDATA as $k => $v)
    if (strcasecmp($k, 'x')!=0) if (count($v)>$count) $count = count($v);
if ($count==0) $count = 1;

$dcount = count($SESTATDATA);
$max = array();

// Сглаживаем графики ##########################################################
if (!empty($_GET["s"])&&($_GET["s"]==1)) {
	for ($i=2; $i<$count-2; $i++) {
        foreach($SESTATDATA as $k => $v) {
            if (strcasecmp($k, 'x')!=0)
    			$SESTATDATA[$k][$i] = ($v[$i-1]+$v[$i-2]+$v[$i]+$v[$i+1]+$v[$i+2])/5;
		}
	}
}

// Вычисляем максимум, и строим горизонтальные полосы ###########################
foreach($SESTATDATA as $k => $v)
    if (strcasecmp($k, 'x')!=0)
        for ($i=0; $i<$count; $i++)
            if ($max[$k] < $v[$i]) $max[$k]=$v[$i];


$maximum = 0;
foreach($SESTATDATA as $k => $v)
    if (strcasecmp($k, 'x')!=0)
        if ($maximum < $max[$k]) $maximum = $max[$k];

$k = $h/($maximum+10);
$wk = $w/($count-1);

$step = 500000;
if ($maximum<5000000) $step=500000;
if ($maximum<1000000) $step=100000;
if ($maximum<100000) $step=10000;
if ($maximum<50000) $step=5000;
if ($maximum<10000) $step=1000;
if ($maximum<5000) $step=500;
if ($maximum<1000) $step=100;
if ($maximum<500) $step=50;
if ($maximum<100) $step=10;
?> 
<table width='<?php echo $IMGW;?>' border=0 cellspacing=1 cellpadding=3 bgcolor='#FEF4E9'><tr><td class=''>
<table width='100%' style='height:<?php echo $IMGH; ?>px' border=0 cellspacing=0 cellpadding=0><tr class=''>
<?php

if ($maximum==0) {
	print "<td align=center><br>Нет данных<br><br></td>";
}
else {
print '
<td>
<table cellspacing="0" cellpadding="5" border="0" style="height:'.$IMGH.'px">
<tr><td valign="top" align="right">'.se_stat_formatNumber($maximum).'</td></tr>
<tr><td align="right">'.se_stat_formatNumber(intval($maximum/2)).'</td></tr>
<tr><td valign="bottom" align="right">0</td></tr>
</table>
</td>';

	$w = 3;
	if ($type==0) $w = 5;
	else if ($maximum > 99999) $start = 1;
    else $start = 0;

	for	($i=$start; $i<$count; $i++)
        foreach($SESTATDATA as $k => $v) if (strcasecmp($k, 'x')!=0) {
            print "<td valign=\"bottom\" >";
            print "<img src='img/color".$k.".gif' width='".$w."' height='".(intval($v[$i]*$IMGH/$maximum)+1)."' title='".intval($v[$i])."'>";
            print "</td>\n";
	}
}
?>
</tr></table>
</td></tr></table>
<?php

$SESTATDATA = $TMPDATA;

?>