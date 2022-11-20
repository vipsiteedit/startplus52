<?php
error_reporting(0);
chdir('../');

function ClearCache($dir) {
  if (is_dir($dir)) {
    $d=opendir($dir);
    while(($f=readdir($d))!==false) {
          if ($f=='.'||$f=='..' || !is_file($dir. $f)) continue;
              unlink($dir. $f);
    }
   closedir($d);
  }
  return;
}

$root = $_SERVER['DOCUMENT_ROOT'];
if (substr($root, -1) != '/') $root .= '/';
$path = $root . 'images/cache/';

if (isset($_GET['deletecache'])){
	ClearCache($root . 'images/cache/');
	ClearCache($root . 'images/prev/');
	exit;
}


require_once 'lib/lib_images.php';
$image = urldecode($_GET['img']);
@$size = intval($_GET['size']);
//if (empty($size)) = intval($_GET['amp;size']);
@$x1 = intval($_GET['x1']);
@$y1 = intval($_GET['y1']);
@$w = intval($_GET['w']);
@$h = intval($_GET['h']);

if ($size < 50 || $size > 800) return;

if (empty($size)) $size = 500;
$dest = md5($image).'_'.$size.'_'.$x1.'_'.$y1.'_'.$w.'_'.$h;

if (substr($image, 0, 1) == '/') $image = substr($image, 1);

if (!is_dir($path)){
   mkdir($path);
} else {
    $flist = glob($path . md5($image). '*');
    if (!empty($flist) && count($flist) > 3){
	foreach($flist as  $f){
    	    if ($f != $path . $dest) unlink($f);
	}
    }
}

if (strpos($image, '://') === false){
    $image = $root . str_replace('//', '/',  $image);
}

if (!file_exists($path . $dest)){
    thumbCreate($path . $dest, $image, '', $size, $x1, $y1, $w, $h, 75);
}

if (file_exists($path . $dest)){
    $s = getimagesize($path . $dest);
    header('Content-type: '.$s['mime']);
//echo $path . $dest;
    echo join('', file($path . $dest)).chr(254).(255);
}
