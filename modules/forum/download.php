<?php

@$fname=htmlspecialchars($_GET['file'], ENT_QUOTES);

//���� ����� �� ����������, ���������� ������

$fname=str_replace("/", "", $fname);

if (!file_exists("modules/forum/upload/$fname")) {
  $forum_echo.= "<div id=message_warning>���� ����������� �� �������! ��������, �� ��� ������ ���������������.</div>";
  $forum_echo.= "<div id=butlayer><input class=buttonForum id=btBack type=button onclick='javascript:history.go(-1)' value=\"�����\"></div>";
  return;
}

$rq=se_db_query(
  "SELECT realname, counter
  FROM forum_attached
  WHERE file='$fname'");

$file=se_db_fetch_array($rq);
$realname=$file['realname'];

//������� ������ ������
chdir("modules/forum/download");
$d=opendir(".");
while(($f=readdir($d))!==false) {
  if ($f=='.'||$f=='..' || !is_file($f)) continue;
  if (se_filemtime($f)<(time()-3600)) unlink($f);
}
closedir($d);

chdir("..");

if (!file_exists("download/$realname")) se_symlink("../upload/$fname", "download/$realname");

$flink=$_SERVER['HTTP_HOST']."/modules/forum/download/$realname";

$counter=$file['counter']+1;

//����������� ������� ����������
se_db_query(
"UPDATE forum_attached
SET counter='$counter'
WHERE file='$fname'");

Header("Location: http://$flink");
exit();

?>