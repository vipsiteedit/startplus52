<?php
session_start();
session_register("SESTATDATA");
$SESTATDATA = $_SESSION["SESTATDATA"];


include "../functions.php";

function imagebar($im, $x, $y, $w, $h, $dx, $dy, $c1, $c2, $c3) {
    imagefilledpolygon($im,	Array($x, $y-$h,  $x+$w, $y-$h,  $x+$w+$dx, $y-$h-$dy,  $x+$dx, $y-$dy-$h), 4, $c1);
	imagefilledpolygon($im, Array( $x+$w, $y-$h,  $x+$w, $y,  $x+$w+$dx, $y-$dy,  $x+$w+$dx, $y-$dy-$h), 4, $c3);
	imagefilledrectangle($im, $x, $y-$h, $x+$w, $y, $c2);
}

$GDVERSION = se_stat_gdVersion();
if (!empty($_GET["antialias"])&&($_GET["antialias"]==0)) $GDVERSION = 1;

// Разрешение
$W = $_GET["width"];
$H = $_GET["height"];

// Псевдоглубина
$DX = 30;
$DY = 20;

// Отступы
$MB = 20; // bottom
$ML = 8; // left
$M = 5; // остальные

// Ширина одного символа
$LW = imagefontwidth(2);

// Если версия GD больше чем 2.0, то все в два раза больше (для сглаживания)
if ($GDVERSION>=2) {
	$W *= 2; $H *= 2;
	$DX *= 2; $DY *= 2;
	$LW *= 2; $MB *= 2; $M *= 2; $ML *= 2;
}

// Количество элементов
$count = 1;
foreach($SESTATDATA as $k => $v)
    if (strcasecmp($k, 'x')!=0) if (count($v)>$count) $count = count($v);
if ($count==0) $count = 1;


// Сглаживаем графики ##########################################################
if (!empty($_GET["s"])&&($_GET["s"]==1)) {
	for ($i=2; $i<$count-2; $i++) {
        foreach($SESTATDATA as $k => $v) {
            if (strcasecmp($k, 'x')!=0)
    			$SESTATDATA[$k][$i] = ($v[$i-1]+$v[$i-2]+$v[$i]+$v[$i+1]+$v[$i+2])/5;
		}
	}
}

// Максимальное значение
$max = 0;
for ($i=0; $i<$count; $i++) {
    foreach($SESTATDATA as $k => $v)
        if (strcasecmp($k, 'x')!=0) $max = $max<$v[$i]?$v[$i]:$max;
}

// ################# Настройки графика #################
$im = imagecreate($W,$H);

include "shared.php";

$county = 10;
$max = $nmax;

// Подравниваем левую границу
$text_width = strlen(se_stat_formatNumber($max))*$LW;
$ML += $text_width;

// Вывод фона графика
imageline($im, $ML, $M+$DY, $ML, $H-$MB, $c);
imageline($im, $ML, $M+$DY, $ML+$DX, $M, $c);
imageline($im, $ML, $H-$MB, $ML+$DX, $H-$MB-$DY, $c);
imageline($im, $ML, $H-$MB, $W-$M-$DX, $H-$MB, $c);
imageline($im, $W-$M-$DX, $H-$MB, $W-$M, $H-$MB-$DY, $c);

imagefilledrectangle($im, $ML+$DX, $M, $W-$M, $H-$MB-$DY, $bg[1]);
imagerectangle($im, $ML+$DX, $M, $W-$M, $H-$MB-$DY, $c);

imagefill($im, $ML+1, $H/2, $bg[2]);

$cnval = count($SESTATDATA)-1;

// Вывод неизменяемой сетки
for ($i=1; $i<$cnval; $i++) {
	imageline($im, $ML+$i*intval($DX/$cnval), $M+$DY-$i*intval($DY/$cnval), $ML+$i*intval($DX/$cnval), $H-$MB-$i*intval($DY/$cnval), $c);
	imageline($im, $ML+$i*intval($DX/$cnval), $H-$MB-$i*intval($DY/$cnval), $W-$M-$DX+$i*intval($DX/$cnval), $H-$MB-$i*intval($DY/$cnval), $c);
}

// Реальные размеры графика
$RW = $W-$ML-$M-$DX;
$RH = $H-$MB-$M-$DY;

// Координаты нуля
$X0 = $ML+$DX;
$Y0 = $H-$MB-$DY;

// Вывод изменяемой сетки
for ($i=0; $i<$count; $i++) {
	imageline($im, $X0+$i*($RW/$count), $Y0, $X0+$i*($RW/$count)-$DX, $Y0+$DY, $c);
	imageline($im, $X0+$i*($RW/$count), $Y0, $X0+$i*($RW/$count), $Y0-$RH, $c);
}

$step = $RH/$county;
for ($i=0; $i<=$county; $i++) {
	imageline($im, $X0, $Y0-$step*$i, $X0+$RW, $Y0-$step*$i, $c);
	imageline($im, $X0, $Y0-$step*$i, $X0-$DX, $Y0-$step*$i+$DY, $c);
	imageline($im, $X0-$DX, $Y0-$step*$i+$DY, $X0-$DX-($ML-$text_width)/4, $Y0-$step*$i+$DY, $text_color);
}

// Вывод баров
$cn = 1;
foreach($SESTATDATA as $k => $v)
    if (strcasecmp($k, 'x')!=0) {
        for ($i=0; $i<count($v); $i++)
        imagebar($im, $X0+$i*($RW/$count)+4-($cn)*intval($DX/$cnval), $Y0+($cn)*intval($DY/$cnval),
                      intval($RW/$count)-4, $RH/$max*$v[$i],
                      intval($DX/$cnval)-5, intval($DY/$cnval)-3,
                      $bar[$k][0], $bar[$k][1], $bar[$k][2]);
        $cn++;
    }

// Уменьшение и пересчет коррдинат
$ML -= $text_width;
if ($GDVERSION >= 2) {
	$im1 = imagecreatetruecolor($W/2, $H/2);
	imagecopyresampled($im1, $im, 0, 0, 0, 0, $W/2, $H/2, $W, $H);
	imagedestroy($im);
	$im = $im1;

	$W/=2; $H/=2;
	$DX/=2; $DY/=2;
	$LW/=2; $MB/=2; $M/=2; $ML/=2;
	$X0/=2; $Y0/=2; $step/=2;
	$RW/=2; $RH/=2;
}

// Цвет текста
$text_color = imagecolorallocate($im,0,78,155);

// Вывод подписей по оси Y
for ($i=1; $i<=$county; $i++) {
	$str = se_stat_formatNumber(($max/$county)*$i);
	imagestring($im, 2, $X0-$DX-strlen($str)*$LW-$ML/4-2, $Y0+$DY-$step*$i-imagefontheight(2)/2, $str, $text_color);
}

// Вывод подписей по оси X
$prev = 100000;
$twidth = $LW*strlen($SESTATDATA['x'][0])+6;
$i = $X0+$RW-$DX;

while ($i > $X0-$DX) {
	if ($prev-$twidth > $i) {
		$drawx = $i+1-($RW/$count)/2;
		if ($drawx > $X0-$DX) {
            $k = ceil(ceil($i-$X0+$DX)/ceil($RW/$count))-1;
			$str = @$SESTATDATA['x'][$k];
			imageline($im, $drawx, $Y0+$DY, $i+1-($RW/$count)/2, $Y0+$DY+5, $text_color);
			imagestring($im, 2, $drawx+1-(strlen($str)*$LW)/2, $Y0+$DY+7, $str, $text_color);
		}
	    $prev = $i;
	}
	$i -= $RW/$count;
}

header("Content-Type: image/png");
imagepng($im);
imagedestroy($im);
?>