<?php
require_once dirname(__FILE__).'/lib_function.php';
//require_once dirname(__FILE__).'/plugins/plugin_image/plugin_imagewatermark.class.php';


function se_create_fileimage($source, $ext){
    switch ($ext){
        case "gif": $img = @imagecreatefromgif($source);break;
        case "png": $img = @imagecreatefrompng($source);break;
        default:    $img = @imagecreatefromjpeg($source);break;
    }
    return $img;
}

function se_set_image($width, $pathimage, $outname, $itemimage=0) {
   $width = intval($width);
   $flag     = true;
   $file     = false;
   $filename = "";
   $IMAGE_DIR="/images/".$pathimage."/";
    if (!is_dir(getcwd()."/images"))
          mkdir(getcwd()."/images");

    if (!is_dir(getcwd().$IMAGE_DIR))
          mkdir(getcwd().$IMAGE_DIR);

  // ���� ����������� ��������
  if (is_uploaded_file($_FILES['userfile']['tmp_name'][$itemimage])){

       $userfile=$_FILES['userfile']['tmp_name'][$itemimage];
       $userfile_size=$_FILES['userfile']['size'][$itemimage];
       $user=strtolower(htmlspecialchars($_FILES['userfile']['name'][$itemimage], ENT_QUOTES));

      //���������, ��� ����������� ���� - ��������
       $sz = GetImageSize($userfile);
       if (preg_match("/\.(gif|jpg|png|jpeg)$/", $user, $m) && ($sz[2]==1 || $sz[2]==2 || $sz[2]==3)) {
    	    $fileextens = $m[1];
       } else {
            $flag = false;
            return 'error type';
       }

      //���� ������ ����� ������ ���������
       if ($userfile_size > 3024000){
            $flag = false;
            return 'error size';
       }
       $file = true;
  }
  // ���� ��������� ����
  if ($file){

       $uploadfile     = getcwd() . $IMAGE_DIR.$outname . '.' . getExtFile($user);
       $filename       = $outname . "." . getExtFile($user);

       if ($sz[0]>$width) {
            $uploadfiletmp  = getcwd().$IMAGE_DIR.$outname.".temp";
             move_uploaded_file($userfile, $uploadfiletmp);
             ThumbCreate($uploadfile,$uploadfiletmp,'s', $width);
             @unlink($uploadfiletmp);
       }
       else {
             move_uploaded_file($userfile, $uploadfile);
       }
  };
  return $filename;
}

function se_set_image_prev($thumbwdth,$width,$pathimage,$outname,$itemimage=0) {

   $width = intval($width);
   $thumbwdth = intval($thumbwdth);
   $flag     = true;
   $file     = false;
   $filename = "";
   $IMAGE_DIR="/images/".$pathimage."/";
    if (!is_dir(getcwd()."/images"))
          mkdir(getcwd()."/images");

    if (!is_dir(getcwd().$IMAGE_DIR))
          mkdir(getcwd().$IMAGE_DIR);

  // ���� ����������� ��������
  if (is_uploaded_file(@$_FILES['userfile']['tmp_name'][$itemimage])){

       $userfile=$_FILES['userfile']['tmp_name'][$itemimage];
       $userfile_size=$_FILES['userfile']['size'][$itemimage];
       $user=strtolower(htmlspecialchars($_FILES['userfile']['name'][$itemimage], ENT_QUOTES));

      //���������, ��� ����������� ���� - ��������
       $sz = GetImageSize($userfile);
       $fileextens = '';

       if (preg_match("/\.(gif|jpg|jpeg|png)$/", $user) && ($sz[2]==1 || $sz[2]==2 || $sz[2]==3)) {
    	    $fileextens = $sz[1];
       } else {
            $flag = false;
            return 'error type';
       }

      //���� ������ ����� ������ ���������
       if ($userfile_size > 3024000){
            $flag = false;
            return 'error_size';
       }
       $file = true;
  }
  // ���� ��������� ����
  if ($file){

                $uploadfile     = getcwd().$IMAGE_DIR.$outname.".".getExtFile($user);
                $uploadfileprev = getcwd().$IMAGE_DIR.$outname."_prev.".getExtFile($user);
                $filename       = $outname.".".substr($user, -3);
                $fileextens     = getExtFile($user);


                if ($sz[0]>$width) {
                    $uploadfiletmp  = getcwd().$IMAGE_DIR.$outname.".temp";
                    move_uploaded_file($userfile, $uploadfiletmp);
                    ImgCreate($uploadfileprev,$uploadfile,$uploadfiletmp,$fileextens, intval($width), intval($thumbwdth));
                    @unlink($uploadfiletmp);
                }
                else {
                    move_uploaded_file($userfile, $uploadfile);
                    ThumbCreate($uploadfileprev,$uploadfile,'s', intval($thumbwdth));
                   @unlink($uploadfiletmp);
                }
  }
  return $filename;
}


function ImgCreate($thumbdest, $dest, $source, $ext, $wdth, $twdth){
   $wdth = intval($wdth);
   $twdth = intval($twdth);
   $ext = getExtFile($dest);
   $img = se_create_fileimage($source, $ext);

    // ������� ���������������� ������� ��� �������
    $f_getimagesize = getimagesize($source);
    $width = $f_getimagesize[0];
    $height = $f_getimagesize[1];

    if ($wdth > $width) {
     $wdth = intval($width); $destHeight = intval($height);
    } else $destHeight = intval(round($height * $wdth/$width));

    if ($twdth > $width) {
     $twdth = intval($width); $thdestHeight = intval($height);
    } else $thdestHeight = intval(round($height * $twdth/$width));

    $d_im   = imagecreatetruecolor($wdth, $destHeight);
    $thd_im = imagecreatetruecolor($twdth, $thdestHeight);
    if ($f_getimagesize['mime'] == 'image/gif' || $f_getimagesize['mime'] == 'image/png') 
    { 
        // �������� ������ ����� 
        $tr = imagecolorallocate($d_im, 255, 255, 255); 
        $thtr = imagecolorallocate($thd_im, 255, 255, 255); 
        // ������� ����������� ����� 
        imagefill($d_im, 0, 0, $tr); 
        imagefill($thd_im, 0, 0, $thtr); 
        // �������� ������ ����� 
        imagecolortransparent($d_im, $tr); 
        imagecolortransparent($thd_im, $thtr); 
        imagetruecolortopalette($d_im, false, 256); 
        imagetruecolortopalette($thd_im, false, 256); 
    } 
    else 
    { 
        imagecolorallocate($d_im, 0, 0, 0); 
        imagecolorallocate($thd_im, 0, 0, 0); 
    }


    imagecopyresampled($d_im,$img,0,0,0,0,$wdth,$destHeight,$width,$height);
    imagecopyresampled($thd_im,$img,0,0,0,0,$twdth,$thdestHeight,$width,$height);

    switch ($ext){
        case "gif": imagegif($d_im,$dest);imagegif($thd_im,$thumbdest); break;
        case "png": imagepng($d_im,$dest);imagepng($thd_im,$thumbdest); break;
        default:    imagejpeg($d_im,$dest, 90); imagejpeg($thd_im,$thumbdest, 90); break;
    }
    imagedestroy($d_im);
    imagedestroy($thd_im);
    imagedestroy($img);
}

function setTransparency($new_image, $type){
	imagealphablending($new_image, false);
	imagesavealpha($new_image, true);
	$transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
	imagefilledrectangle($new_image, 0, 0, $destWidth, $destHeight, $transparent);
	imagecolortransparent  ($new_image, $transparent);
}

function ThumbCreate($dest, $source, $res='s', $size = 0,  $x1 = 0, $y1 = 0, $w = 0, $h = 0, $quality = 75){
	if(empty($res) || strlen($res) > 1) $res='s';
    //$ext = getExtFile($source);
    $extarr = array(0=>'',1=>'gif', 2=>'jpg', 3=>'png');
    $f_getimagesize = getimagesize($source);
    $ext = $extarr[$f_getimagesize[2]]; 

    $width = $f_getimagesize[0];
    $height = $f_getimagesize[1];
    if (intval($w) > 0) {
		$width = intval($w);
    }
    if (intval($h) > 0) {
		$height = intval($h);
    }
	
    if (strpos($size, 'x')!==false) {
		list($wdth, $hght) = explode('x', $size);
		if (empty($wdth) && empty($hght)) {
			$wdth = $width;
			$hght = $height;
		}
		else {
			$ratio_w = $ratio_h = 0;
			if ($wdth > 0)
				$ratio_w = $width/$wdth;
			if ($hght > 0)
				$ratio_h = $height/$hght;
			if ($res == 'm') {
			    if ($ratio_w > $ratio_h) {
				   $hght = (int)($height/$ratio_h);
				   $wdth = (int)($width/$ratio_h); 
				} else {
				   $hght = (int)($height/$ratio_w);
				   $wdth = (int)($width/$ratio_w); 
				}
			} else {
				$ratio = max($ratio_w, $ratio_h);
				$wdth = (int)($width/$ratio); 
				$hght = (int)($height/$ratio);
			}
		}
		/*
		if ($width >= $wdth) {	
			$hght = intval(round($height * $wdth/$width));
		}
		if ($height >= $hght) {
			$wdth = intval(round($width * $hght/$height));
		}
		*/
    } else {
		if (($width >= $height && $res == 's') || $res == 'w'){
			if (intval($size) > $width) $size = $width;
			$wdth = intval($size);
			$hght = intval(round($height * $wdth/$width));
		} 
		if (($width <= $height && $res == 's') || $res == 'h'){
			if (intval($size) > $height) $size = $height;
			$hght = intval($size);
			$wdth = intval(round($width * $hght/$height));
		}
    }

    if (($wdth >= $width && $hght >= $height) && !$x1 && !$y1 && !$w && !$h) {
        $wdth=$width; $hght = intval($height);
        if ($dest != ''){
    	    copy($source, $dest);
        }
	return;
    }

    if (class_exists('Imagick')) {
       $thumb = new Imagick($source);
       $thumb->thumbnailImage($wdth, $hght);
       $thumb->setImagePage($thumb->getImageWidth(), $thumb->getImageHeight(), 0, 0 );
       $thumb->writeImage($dest);
       $thumb->destroy();
    } 
	else {
		$img = se_create_fileimage($source, $ext);
		$d_im   = imagecreatetruecolor($wdth, $hght);

		if ($f_getimagesize['mime'] == 'image/gif' || $f_getimagesize['mime'] == 'image/png') {
			$type = ($f_getimagesize['mime'] == 'image/gif') ? 'gif' : 'png';
			setTransparency($d_im, $type);
		}
		else {
			imagecolorallocate($d_im, 0, 0, 0);
		}

		imagecopyresampled($d_im, $img,0,0,$x1,$y1,$wdth,$hght,$width,$height);
		if ($dest == '') header ("Content-type: " . $f_getimagesize['mime']);

		switch ($ext){
			case "gif": {
				@imagegif($d_im, $dest);break;
			}
			case "png": {
				@imagepng($d_im, $dest);break;
			}
			default: {
			   @imagejpeg($d_im, $dest, $quality);break;
			}
		}
		imagedestroy($d_im);
		imagedestroy($img);
    }
    if ($dest == '') return;
}

function SiteScreenshot($url, $resolution='1024x768', $size='100', $format='png') {
    $Filename = md5($url.$size.$resolution).".jpg";
    // ���������� ��� ����� ������
    $ScreenshotDirectory = "/images/screens/";
    if (!file_exists($ScreenshotDirectory)) mkdir($ScreenshotDirectory);
    // ��������� ���� ���� ����� ���� � ����� �������
    if(@is_file($ScreenshotDirectory.$Filename)) {
        return $ScreenshotDirectory.$Filename;
    } else {
        $Image = @file_get_contents("http://mini.s-shot.ru/".$resolution."/".$size."/".$format."/?http://".$url);
        $OpenFile = fopen($ScreenshotDirectory.$Filename, "w+");
        // ��������� �����������
        $Write = fwrite($OpenFile, $Image);
        return $ScreenshotDirectory.$Filename;
    }
}

function drawWatermark($image, $watermark, $padding = 0, $opacity = 1)  {
  // Check if the watermark is bigger than the image
    $image_width            = $image->getImageWidth();
    $image_height           = $image->getImageHeight();
    $watermark_width        = $watermark->getImageWidth();
    $watermark_height       = $watermark->getImageHeight();
           
    if ($image_width < $watermark_width + $padding || $image_height < $watermark_height + $padding) {
         return false;
    }
    // Calculate each position
    $positions    = array();
    $positions[]  = array(0 + $padding, 0 + $padding);
    $positions[]  = array($image_width - $watermark_width - $padding, 0 + $padding);
    $positions[]  = array($image_width - $watermark_width - $padding, $image_height - $watermark_height - $padding);
    $positions[]  = array(0 + $padding, $image_height - $watermark_height - $padding);
    // Initialization
    $min      = null;
    $min_colors   = 0;
    // Calculate the number of colors inside each region and retrieve the min
    foreach($positions as $position)  {
       $colors = $image->getImageRegion(
        $watermark_width,
        $watermark_height,
        $position[0],
        $position[1])->getImageColors();
       if ($min === null || $colors <= $min_colors)  {
            $min  = $position;
            $min_colors = $colors;
       }
    }
    // Draw the watermark
    $watermark->setImageOpacity($opacity);
    $image->compositeImage(
      $watermark,
      Imagick::COMPOSITE_OVER,
      $min[0],
      $min[1]);
    return true;
}

//images/logo_korobki.png
function se_getDImage($img, $size = 200, $res='s', $water='', $color = 0x0000FF, $pos = 'center', $defimage = '', $waterquality = 50){
	$res = ($res == 'w' || $res == 'h') ? $res : 's';
	if ($water && getExtFile($img) == 'gif') $water = '';
	
	if (empty($size)) $size = 200;
	$wat =  ($water) ? 'w' : '';
	$ext = getExtFile($img);
	
	$dest = md5($img).'_'.$wat.$res.$size.'.'.$ext;
	$root = $_SERVER['DOCUMENT_ROOT'];
	if (substr($root, -1) != '/') $root .= '/';
	if (!is_dir($root.'images')){
		mkdir($root.'images');
	}

	$path =  'images/prev/';
	
	if (substr($img, 0, 1) == '/') $img = substr($img, 1);

	if (!is_dir($root.$path)){
		mkdir($root.$path);
	}

	if (strpos($img , '://') === false){
		$img = $root . str_replace('//', '/',  $img);
	}
	if (!$defimage) {
			$defimage = '/lib/plugins/plugin_shop/no_foto.gif';
	}
	
	if (file_exists($img)) {
	    if (file_exists($root . $path . $dest) && filemtime($img) > filemtime($root . $path . $dest)){
		$flist = glob($root . $path . md5($image). '*.*');
		if (!empty($flist)){
	    	    foreach($flist as  $f){
	        	if ($f != $path . $dest) unlink($f);
	    	    }
		}
	    }
		if (!file_exists($root . $path . $dest)){
			if (trim($water)== '' ) {
				thumbCreate($root . $path . $dest, $img, $res, $size, 0, 0, 0, 0, 75);
			} else {
				if (file_exists($root. $water)) {
					$watermark = $root. $water;
					$watermarktype = 'image';
				} else {
					$watermarktype = 'text';
					$watermark = $water;
				}
				/*if (class_exists('Imagick') && $watermarktype == 'image'){
				    thumbCreate($root . $path . $dest, $img, $res, $size, 0, 0, 0, 0, 75);
				    $image = new Imagick($root . $path . $dest);
				    $watermark = new Imagick($watermark);
				    drawWatermark($image, $watermark, 5, 1);
				    $image->writeImage($path . $dest);
				} else 
				*/
				{
				    $imgobj = new plugin_imagewatermark($path . $dest);
				    $imgobj->execute($img, $watermark, $watermarktype, 0x0000FF, $waterquality , $path . $dest, $size, $res, $pos);
				    unset($imgobj);
				}
			}
		}
		if (file_exists($root . $path . $dest)){
			return '/'.$path . $dest;
		} else {
			return $defimage;
		}
	} else {
		return $defimage;
	}
}
?>