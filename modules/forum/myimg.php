<?php

Global $_page;
//
// �������� ������
@$ext_sid=htmlspecialchars($_GET['sid']);

$rs=se_db_query(
  "SELECT id_users
  FROM forum_session
  WHERE sid='$ext_sid'");

$s=se_db_fetch_array($rs);

if ($s['id_users']!=$uid) return;

if (isset($_GET['delete'])) {
  //������� �������� �� ����
  $ru = se_db_query("
  UPDATE forum_users
  SET `img`=''
  WHERE id='$uid'"
  );
  //������� ����� ��������
  @unlink("modules/forum/images/$uid.gif");
  @unlink("modules/forum/images/$uid.jpg");
  @unlink("modules/forum/images/$uid.png");

  Header("Location: http://".$_SERVER['HTTP_HOST']."/".$_page."?act=my&".time());
  exit();
}

$userfile=$_FILES['userfile']['tmp_name']; // ��������� ������������� ����
$userfile_size=$_FILES['userfile']['size'];
$origfilename=strtolower(htmlspecialchars($_FILES['userfile']['name'], ENT_QUOTES));

if (!empty($userfile)) {
	$extimg = explode('.',$origfilename);
	$fnam = $extimg[0]; // ��� ����� �� ����������
	$ext = $extimg[1]; // ����������
	$previmg = @$fnam.'_prev.'.@$ext; // ��� ����� ����������� ���������
}
else
	return;

//���������, ��� ����������� ���� - ��������
list($width, $height, $mimetype) = GetImageSize($userfile);
if (!(ereg("^[^\.]+\.(gif|jpg|png)$", $origfilename) && ($mimetype==1 || $mimetype==2 || $mimetype==3))) {
  $forum_echo.= "<div id=message_warning>��������� ���� �� �������� ��������� � ������� GIF/JPG/PNG!</div>
  <div id=butlayer><input class=buttonForum id=btBack type=button onclick='javascript:history.go(-1)' value=\"�����\"></div>";
  return;
}

if ($width<=100 && $height<=100) {// ���� ������� �������� �� ��������� 100�100// ### �������� �� ����� ��������, ������� �������� ������������
	move_uploaded_file($userfile, getcwd().'/modules/forum/images/'.$previmg);
	//if file_exists(getcwd().'/modules/forum/images/'.$previmg)
}else { // ���� ������� ����������� �������� ��������� ����������
// ### ������� ��������� ��������
 	// ��������� ����������� ������ � ������
 	if ($width > $height) {
 		// ���� ������ ������ �����
 		$wdth = 100;
 		$hght = $height * 100 / $width;
 	}
 	else {
		// ���� ����� ������ ������
		$hght = 100;
		$wdth = $width * 100 / $height;
	}

    switch ($ext) {
       	case "gif": $img = imagecreatefromgif($userfile);break;
       	case "png": $img = imagecreatefrompng($userfile);break;
       	default:    $img = imagecreatefromjpeg($userfile);break;
	}

    @$d_im   = imagecreatetruecolor($wdth, $hght);

	@imagecopyresampled($d_im,$img,0,0,0,0,$wdth,$hght,$width,$height);

    switch ($ext){
       	case "gif": @imagegif($d_im, getcwd().'/modules/forum/images/'.$previmg);break;
       	case "png": @imagepng($d_im, getcwd().'/modules/forum/images/'.$previmg);break;
       	default:    @imagejpeg($d_im, getcwd().'/modules/forum/images/'.$previmg, 75);break;
    }

    @imagedestroy($d_im);
    @imagedestroy($img);
}


//���� ������ ����� ������ ���������
//if ($userfile_size > 50024) {
//  $forum_echo.= "<div id=message_warning>��������� ���� ��������� ������ 50 ��!</div>
//  <div id=butlayer><input class=buttonForum id=btBack type=button onclick='javascript:history.go(-1)' value=\"�����\"></div>";
//  return;
//}

//���������� ������ ��������

//���� ������ �������� ������ 100x100
//if ($sz[0]>100 || $sz[1]>100) {
//  $forum_echo.= "<div id=message_warning>������ �������� ��������� 100x100px!</div>
//  <div id=butlayer><input class=buttonForum id=btBack type=button onclick='javascript:history.go(-1)' value=\"�����\"></div>";
//  return;
//}

$uploadfile=getcwd()."/modules/forum/images/$uid.".@$ext; // ������ ��� �������������� �����
$filename="$uid.".@$ext; // ������� ��� �������������� �����

if (se_rename(getcwd()."/modules/forum/images/".$previmg, $uploadfile)) {
  //��������� ������ �������� � ����
  $ru = se_db_query("
  UPDATE forum_users
  SET `img`='$filename'
  WHERE id='$uid'"
  );
}

Header("Location: http://".$_SERVER['HTTP_HOST']."/".$_page."?act=my&".time());
exit();

?>