<?php

if ($uid==0) { //���� �����
  $forum_echo.= "<div id=message_warning>���������� ��������� ����� ������ ������������������ ������������!</div>";
  $forum_echo.= "<div id=butlayer><input class=buttonForum id=btBack type=button onclick='javascript:history.back()' value=\"�����\"></div>";
  return;
}

if (isset($_POST['doGo'])) {

$message=substr($_POST['message'], 0, 10000);
$message=str_replace("\n\r", "\n", $message);
$message=str_replace("\r\n", "\n", $message);
$nick=substr($_POST['userfrom'], 0, 50);
$mailfrom=substr($_POST['mailfrom'], 0, 100);
$subject=substr($_POST['subject'], 0, 100);

//���������� ����������

$ext_id=htmlspecialchars($_POST['idto'], ENT_QUOTES);

$rt=se_db_query(
  "SELECT a_email, forum_users.nick
  FROM author, forum_users
  WHERE forum_users.id_author=author.id AND forum_users.id='$ext_id'"
);

if (se_db_num_rows($rt)==0) return;

$to=se_db_fetch_array($rt);
$mailto=$to['a_email'];
$userto=$to['nick'];


//���������� ���������
$headers ="Content-Type: text/plain; charset=Windows-1251
From: \"$nick\" <$mailfrom>
Subject: ��������� � ������ $nameForum: $subject
X-Priority: 3
Return-Part: \"$nick\" <$mailfrom>
Content-Transfer-Encoding: 8bit
Content-Type: text/plain; charset=Windows-1251";

$message.="\n\n---
������� �������� � ���������� ������ SiteEdit
www.SiteEdit.ru";

mail("\"$userto\" <$mailto>", "", $message, $headers);

$forum_echo.= "<div id=message_warning>���� ��������� ���� ������� ����������!</div>";
$forum_echo.= "<div id=butlayer><input class=buttonForum id=btBack type=button onclick='javascript:history.go(-2)' value=\"�����\"></div>";

}
else {

if (!isset($_GET['id'])) return;
$ext_id=htmlspecialchars($_GET['id'], ENT_QUOTES);

if (!isset($_GET['topic'])) return;
$ext_topic=htmlspecialchars($_GET['topic'], ENT_QUOTES);

//���������� e-mail �����������
$rf=se_db_query(
  "SELECT a_email
  FROM author, forum_users
  WHERE forum_users.id_author=author.id AND forum_users.id='$uid'"
);

if (se_db_num_rows($rf)==0) return;

$from=se_db_fetch_array($rf);
$mailfrom=$from['a_email'];

//���������� ����������
$rt=se_db_query(
  "SELECT a_email, forum_users.nick
  FROM author, forum_users
  WHERE forum_users.id_author=author.id AND forum_users.id='$ext_id'"
);

if (se_db_num_rows($rt)==0) return;

$to=se_db_fetch_array($rt);
$mailto=$to['a_email'];
$userto=$to['nick'];

//���������� ���� ���������
$rs=se_db_query(
  "SELECT name
  FROM forum_topic
  WHERE id='$ext_topic'"
);

if (se_db_num_rows($rs)==0) return;

$subject=se_db_fetch_array($rs);
$subject=$subject['name'];


//������� �����
$forum_echo.="
<h3 class=forumTitle id=mess_Title>�������� ������� ���������</h3>
<form name='mailform' id='mailform' action='' method=POST>
<TABLE class=tableForum id=table_PvtMesg><TBODY class=tableBody>
<TR><TD class=title id=msg_titleFrom><div id=msg_tForm>�� ����</div></TD><TD class=field id=msg_fieldFrom><div id=msg_fFrom>$nick<input class=inputForum id=msg_inpFrom type=text value='$mailfrom'></div></TD></TR>
<TR><TD class=title id=msg_titleTo><div id=msg_tTo>����</div></TD><TD class=field id=msg_fieldTo><div id=msg_fTo>$userto</div></TD></TR>
<TR><TD class=title id=msg_titleTheme><div id=msg_tTheme>����</div></TD><TD class=field id=msg_fieldTheme><div id=msg_fTheme><input class=inputForum id=msg_inpTheme type=text value='$subject'></div></TD></TR>
<TR><TD class=title id=msg_titleMessage><div id=msg_tMessage>���������</div></TD><TD class=field id=msg_fieldMessage><div id=msg_fMessage><textarea class=areaForum id=msg_arMessage name='message'></textarea></div></TD></TR>
<tr><td>&nbsp;</td></tr>
<tr><td>
<div id=msg_ServiceButns><input class=buttonForum id=msg_btSend name=doGo type=submit value=���������><input class=buttonForum id=msg_btClear type=button value=��������></div>
<input type='hidden' name='userfrom' value='$nick'>
<input type='hidden' name='mailfrom' value='$mailfrom'>
<input type='hidden' name='idto' value='$ext_id'>
<input type='hidden' name='subject' value='$subject'>
</td></tr>
</TBODY></TABLE>
</form>
<script>document.all.mailform.msg_arMessage.focus();</script>";
}

?>