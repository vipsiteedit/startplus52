<?php

import_request_variables("p", "ext_");

if (!(isset($ext_session, $ext_domain, $ext_numpkt))) {
  header('HTTP/1.0 404');
  exit();
}

require_once "function.php";

$session = htmlspecialchars(addslashes($ext_session));
$domain =addslashes($ext_domain);
$numpkt = htmlspecialchars(addslashes($ext_numpkt));
if (!checkSID($session)) exit("no");

$path=getcwd()."/data/";

//���� ��������������
$fname = $path.$session.".sid";

$param=file($fname);

$pktsize=$param[2]."<br>";
$pktcol=$param[3];

if ($numpkt > $pktcol) exit("no"); //���� ����� ������ ������ ���������� �������

$userfile=$_FILES['userfile']['tmp_name'];
$userfile_size=$_FILES['userfile']['size'];
$user=$_FILES['userfile']['name'];

if ($userfile_size > $pktsize) exit("no"); //���� ������ ������ ������ ���������

$uploadfile=$path."pkt_".$numpkt.".tmp";

if (move_uploaded_file($userfile, $uploadfile)) echo "ok"; else exit("no");

//������ ���� ������
$fname = $uploadfile;
$f = fopen($fname, "rb");
$st=fread($f, $pktsize);
fclose($f);

//����� ����� � ���� ������

//��������� ��������:
$s=$numpkt*$pktsize;

$fnamedat = $path.$session.".dat";
$f = fopen($fnamedat, "r+b");
fseek($f, $s);
flock($f, LOCK_EX);
$st=fwrite($f, $st);
fflush($f);
flock($f, LOCK_UN);
fclose($f);

unlink($fname);

?>