<?php
session_start();
session_register("SESTATDATA");
$SESTATDATA = $_SESSION["SESTATDATA"];


include "../functions.php";


// Зададим значение и подписи
foreach($SESTATDATA as $k => $v)
    if (strcasecmp($k, 'x')!=0) {
        $VALUES[] = array_sum($v);
        $LEGEND[] = $k;
    }

// Максимальное значение
$max = 0;

// Разрешение
$W = $_GET["width"];
$H = $_GET["height"];

// ################# Настройки графика #################
$im = imagecreate($W,$H);

include "shared.php";


	$black = ImageColorAllocate($im,0,0,0);

	// Получим размеры изображения
	$W=ImageSX($im);
	$H=ImageSY($im);

	// Вывод легенды #####################################

	// Посчитаем количество пунктов, от этого зависит высота легенды
	$legend_count=count($LEGEND);

	// Посчитаем максимальную длину пункта, от этого зависит ширина легенды
	$max_length=0;
	foreach($LEGEND as $v) if ($max_length<strlen($v)) $max_length=strlen($v);

	// Номер шрифта, котором мы будем выводить легенду
	$FONT=2;
	$font_w=ImageFontWidth($FONT);
	$font_h=ImageFontHeight($FONT);

	// Вывод прямоугольника - границы легенды ----------------------------

	$l_width=($font_w*$max_length)+$font_h+10+5+10;
	$l_height=$font_h*$legend_count+10+10;


	// Получим координаты верхнего левого угла прямоугольника - границы легенды
	$l_x1=$W-10-$l_width;
	$l_y1=($H-$l_height)/2;

	// Выводя прямоугольника - границы легенды
	ImageRectangle($im, $l_x1, $l_y1, $l_x1+$l_width, $l_y1+$l_height, $black);

	// Вывод текст легенды и цветных квадратиков
	$text_x=$l_x1+10+5+$font_h;
	$square_x=$l_x1+10;
	$y=$l_y1+10;

	$i=0;
	foreach($LEGEND as $v) {
		$dy=$y+($i*$font_h);
		ImageString($im, $FONT, $text_x, $dy, $v, $black);
		ImageFilledRectangle($im,
                             $square_x+1,$dy+1,$square_x+$font_h-1,$dy+$font_h-1,
                             $bar[$LEGEND[$i]][1]);
		ImageRectangle($im,
                       $square_x+1,$dy+1,$square_x+$font_h-1,$dy+$font_h-1,
                       $black);
		$i++;
		}

	// Вывод круговой диаграммы ----------------------------------------

	$total=array_sum($VALUES);
	$anglesum=$angle=Array(0);
	$i=1;

	// Расчет углов
	while ($i<count($VALUES)) {
		$part=$VALUES[$i-1]/$total;
		$angle[$i]=floor($part*360);
		$anglesum[$i]=array_sum($angle);
		$i++;
		}
	$anglesum[]=$anglesum[0];

	// Расчет диаметра
	$diametr=$l_x1-10-10;

	// Расчет координат центра эллипса
	$circle_x=($diametr/2)+10;
	$circle_y=$H/2-10;

	// Поправка диаметра, если эллипс не помещается по высоте
	if ($diametr>($H*2)-10-10) $diametr=($H*2)-20-20-40;

	// Вывод тени
	for ($j=20;$j>0;$j--)
		for ($i=0;$i<count($anglesum)-1;$i++)
			ImageFilledArc($im,$circle_x,$circle_y+$j,
                               $diametr,$diametr/2,
                               $anglesum[$i],$anglesum[$i+1],
                               $bar[$LEGEND[$i]][2],IMG_ARC_PIE);

	// Вывод круговой диаграммы
	for ($i=0;$i<count($anglesum)-1;$i++)
		ImageFilledArc($im,$circle_x,$circle_y,
                           $diametr,$diametr/2,
                           $anglesum[$i],$anglesum[$i+1],
                           $bar[$LEGEND[$i]][1],IMG_ARC_PIE);



// Генерация изображения
header("Content-Type: image/png");
imagepng($im);
imagedestroy($im);
?>