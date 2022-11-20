<?php
session_start();
session_register("SESTATDATA");
$SESTATDATA=$_SESSION["SESTATDATA"];

function circle($im,$x,$y,$r,$color) {
	for ($i=1;$i<=$r;$i++) {
		imagearc($im,$x,$y,$i,$i,0,360,$color);
		}
	}

$W=465;
$H=348;

function cpixel($im,$x,$y,$r,$color) {
	GLOBAL $W,$H;
	if ($x==0 && $y==0) return;

	$PH=$H/2;
	$PW=$W/2;

	$x=225+$x*1.22; //222
	$y=213-$y*1.48;

	if ($r==1) imagesetpixel($im,$x,$y,$color);
	else circle($im,$x-$r,$y-$r,$r,$color);
	}

$im=imagecreate($W,$H);
$white=imagecolorallocate($im,255,255,255);
$red=imagecolorallocate($im,255,0,0);
$blue=imagecolorallocate($im,0,0,255);
imagecolortransparent($im,$white);

while (list ($key, $val) = each($SESTATDATA)) {
	list($cy,$cx)=explode("|",$key);
	$w=1;
	if ($val>5) $w=2;
	if ($val>10) $w=3;
	if ($val>100) $w=4;
	if ($val>1000) $w=5;
	if ($val>10000) $w=6;
	if ($val>100000) $w=7;

	cpixel($im,$cx/10,$cy/10,$w,$red);
	}


header("Content-Type: image/png");
imagepng($im);
imagedestroy($im);
?>