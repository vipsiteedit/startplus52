<?php
session_start();
session_register("SESTATDATA");
$SESTATDATA = $_SESSION["SESTATDATA"];


include "../functions.php";

function imagelinethick($image, $x1, $y1, $x2, $y2, $color, $thick = 1) {

    /* // this way it works well only for orthogonal lines
   // эта прорисовка хорошо только для orthogonal линий
   imagesetthickness($image, $thick);
   return imageline($image, $x1, $y1, $x2, $y2, $color);
   */
   if ($thick == 1) {
       return imageline($image, $x1, $y1, $x2, $y2, $color);
   }
   $t = $thick / 2 - 0.5;
   if ($x1 == $x2 || $y1 == $y2) {
       return imagefilledrectangle($image, round(min($x1, $x2) - $t), round(min($y1, $y2) - $t),
                                   round(max($x1, $x2) + $t), round(max($y1, $y2) + $t), $color);
   }
   $k = ($y2 - $y1) / ($x2 - $x1); //y = kx + q
   $a = $t / sqrt(1 + pow($k, 2));
   $points = array(
       round($x1 - (1+$k)*$a), round($y1 + (1-$k)*$a),
       round($x1 - (1-$k)*$a), round($y1 - (1+$k)*$a),
       round($x2 + (1+$k)*$a), round($y2 - (1-$k)*$a),
       round($x2 + (1-$k)*$a), round($y2 + (1+$k)*$a),
   );    
   imagefilledpolygon($image, $points, 4, $color);
   return imagepolygon($image, $points, 4, $color);
}

$GDVERSION = se_stat_gdVersion();
if (!empty($_GET["antialias"])&&($_GET["antialias"]==0)) $GDVERSION = 1;

// Разрешение
$W = $_GET["width"];
$H = $_GET["height"];

// Отступы
$MB = 20; // bottom
$ML = 8; // left
$M = 5; // остальные

// Ширина одного символа
$LW = imagefontwidth(2);

$THICK = 1;
$DX = 1;
$DY = 1;

// Если версия GD больше чем 2.0, то все в два раза больше (для сглаживания)
if ($GDVERSION>=2) {
	$W *= 2; $H *= 2;
	$DX *= 2; $DY *= 2;
	$LW *= 2; $MB *= 2; $M *= 2; $ML *= 2;
	$THICK *= 2;
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

// Реальные размеры графика
$RW = $W-$ML-$M;
$RH = $H-$MB-$M;

// Координаты нуля
$X0 = $ML;
$Y0 = $H-$MB;

$step = $RH/$county;

imagefilledrectangle($im, $X0, $Y0-$RH, $X0+$RW, $Y0, $bg[1]);
imagerectangle($im, $X0, $Y0, $X0+$RW, $Y0-$RH, $c);

// Вывод сетки по оси Y
for ($i=1; $i<=$county; $i++) {
	$y = $Y0-$step*$i;
	imageline($im,$X0,$y,$X0+$RW,$y,$c);
	imageline($im,$X0,$y,$X0-($ML-$text_width)/4,$y,$text_color);
}

// Вывод сетки по оси X
// Вывод изменяемой сетки
for ($i=0; $i<$count; $i++) {
	imageline($im,$X0+$i*($RW/$count),$Y0,$X0+$i*($RW/$count),$Y0,$c);
	imageline($im,$X0+$i*($RW/$count),$Y0,$X0+$i*($RW/$count),$Y0-$RH,$c);
}

// Вывод столбцев
$dx = ($RW/$count)/2;

foreach($SESTATDATA as $k => $v)
    if (strcasecmp($k, 'x')!=0) $py[$k] = $Y0-($RH/$max*$v[0]);
$px = intval($X0+$dx);

for ($i=0; $i<$count; $i++) {
	$x = intval($X0+$i*($RW/$count)+$dx);

    foreach($SESTATDATA as $k => $v) {
        if (strcasecmp($k, 'x')!=0) {
            $y = $Y0-($RH/$max*$v[$i]);
	        imagelinethick($im, $px, $py[$k], $x, $y, $bar[$k][2], $THICK);
    	    $py[$k] = $y;
        }
    }
	$px = $x;
}

// Уменьшение и пересчет коррдинат
$ML -= $text_width;
if ($GDVERSION >= 2) {
	$im1 = imagecreatetruecolor($W/2,$H/2);
	imagecopyresampled($im1,$im,0,0,0,0,$W/2,$H/2,$W,$H);
	imagedestroy($im);
	$im = $im1;

	$W /= 2; $H /= 2;
	$DX /= 2; $DY /= 2;
	$LW /= 2; $MB /= 2; $M /= 2; $ML /= 2;
	$X0 /= 2; $Y0 /= 2; $step /= 2;
	$RW /= 2; $RH /= 2;
}

// Цвет текста
$text_color = imagecolorallocate($im,0,78,155);

// Вывод подписей по оси Y
for ($i=1; $i<=$county; $i++) {
	$str = se_stat_formatNumber(($max/$county)*$i);
	imagestring($im, 2, $X0-strlen($str)*$LW-$ML/4-2, $Y0-$step*$i-imagefontheight(2)/2, $str, $text_color);
}

// Вывод подписей по оси X
$prev = 100000;
$twidth = $LW*strlen($SESTATDATA['x'][0])+6;
$i = $X0+$RW;

while ($i > $X0) {
	if ($prev-$twidth > $i) {
		$drawx = $i-($RW/$count)/2;
		if ($drawx > $X0) {
            $k = ceil(ceil($i-$X0)/ceil($RW/$count))-1;
            $str = @$SESTATDATA['x'][$k];
			imageline($im, $drawx, $Y0, $i-($RW/$count)/2, $Y0+5, $text_color);
			imagestring($im, 2, $drawx-(strlen($str)*$LW)/2, $Y0+7, $str, $text_color);
		}
		$prev = $i;
	}
	$i -= $RW/$count;
}

header("Content-Type: image/png");
imagepng($im);
imagedestroy($im);
?>