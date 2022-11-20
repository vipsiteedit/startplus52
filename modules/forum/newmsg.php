<?php

if (isset($_POST['text'])) $_SESSION['forum_msgtext']=$_POST['text'];

if (isset($_POST['upload'])) {
  require "attached.php";
  return;
};

Global $_page;

if (isset($_GET['id'])) $ext_id=htmlspecialchars($_GET['id'], ENT_QUOTES);

if (!isset($_POST['doGo'])) {

  //���������, �� �������� �� ������������ ������
  if ($uid==0) {
    $forum_echo.= "<div id=message_warning>��������� ��������� ����� ������ ������������������ ������������!</div>";
    $forum_echo.= "<div id=butlayer><input class=buttonForum id=btBack type=button onclick='javascript:history.go(-1)' value=\"�����\"></div>";
    return;
  }


  //���������, �� ������ �� �����.
  $qtd = se_db_query(
    "SELECT forum_topic.id
     FROM forum_forums, forum_topic
     WHERE forum_topic.id='$ext_id' AND forum_forums.id=forum_topic.id_forums AND forum_forums.enable='N'"
  );

  if (se_db_num_rows($qtd)!=0) {
    $forum_echo.= "<div id=message_warning>���� ����� ������. ���������� ����� ��� ���������!</div>";
    $forum_echo.= "<div id=butlayer><input class=buttonForum id=btBack type=button onclick='javascript:history.go(-1)' value=\"�����\"></div>";
    return;
  }

  //���������, �� ������� �� ����.
  $qtd = se_db_query(
    "SELECT id, name
     FROM forum_topic
     WHERE id='$ext_id' AND enable='N'"
  );

  if (se_db_num_rows($qtd)!=0) {
    $forum_echo.= "<div id=message_warning>��� ���� �������. ���������� ����� ��������� ���������!</div>";
    $forum_echo.= "<div id=butlayer><input class=buttonForum id=btBack type=button onclick='javascript:history.go(-1)' value=\"�����\"></div>";
    return;
  }


  if (isset($_SESSION['forum_msgtext'])) $text=$_SESSION['forum_msgtext'];

  if (isset($_GET['quote'])) { //���� ����� � ������������

    $ext_quote=htmlspecialchars($_GET['quote'], ENT_QUOTES);


  $rq=se_db_query("
    SELECT id_topic, forum_msg.id_users AS id_users, text, forum_msg.date_time AS date_time, date_time_edit, moderator_edit, date_time_moderator_edit, forum_topic.name AS topic, nick, forum_status.name AS status, origin, forum_users.img AS img, location
    FROM forum_msg, forum_topic, forum_users, forum_status
    WHERE forum_msg.id='$ext_quote' AND forum_topic.id=forum_msg.id_topic AND forum_users.id=forum_msg.id_users AND forum_status.id=forum_users.id_status
   ");

  $quote=se_db_fetch_array($rq);
  $qtext=htmlspecialchars(stripslashes($quote['text']), ENT_QUOTES);
  $qtopic=stripslashes($quote['topic']);
  $qdate = date("d", $quote['date_time'])." ".$month_R[date("m", $quote['date_time'])].date(" Y ����, H:i", $quote['date_time']);
  $q_id_users=$quote['id_users'];
  $qlocation="<div id=topic_showTopicLoc>".$quote['location']."</div>";


if (empty($quote['img'])) $img=""; else $img="<img id=mess_showTopicAuthorImg src=/modules/forum/images/".$quote['img'].">";
$forum_echo.= "
<TABLE class=tableForum id=tableTopic><tbody class=tableBody><tr><td colspan=2 id=mess_MessTheme><div id=mess_ThemeName>$qtopic</div></td></tr>
  <tr>
	<td class=title id=title_ShowUserMess>
		  <div id=mess_ShowUserMess>
		  <a id=mess_showTopicAuthorNick href='?act=showuser&id=$q_id_users'>".$quote['nick']."</a>$img
		  <div id=mess_showTopicAuthorStatus>".$quote['status']."</div>$qlocation</div>
	</td>
    <td class=field id=field_ShowUserMess>
    	<div id=mess_MessageText>
    	<div id=mess_showTopicMsgDate>$qdate</div>";

//�������� ����
$trans = array("[b]" => "<b>", "[/b]" => "</b>",
               "[em]" => "<em>", "[/em]" => "</em>",
               "[u]" => "<u>", "[/u]" => "</u>",
               "[ul]" => "<ul>", "[/ul]" => "</ul>",
               "[ol]" => "<ol>", "[/ol]" => "</ol>",
               "[center]" => "<center>", "[/center]" => "</center>",
               "[sup]" => "<sup>", "[/sup]" => "</sup>",
               "[sub]" => "<sub>", "[/sub]" => "</sub>",
               "[code]" => "<pre id=code>", "[/code]" => "</pre>",
               "[quote]" => "<div id=quote>", "[/quote]" => "</div>");

$qtext=strtr($qtext, $trans);

//�������� url
preg_match_all("/\[a +href=([^]]+)\]([^]]+)\[\/a\]/i", $qtext, $match, PREG_PATTERN_ORDER);

for($j=0; $j<count($match[1]); $j++)
  $qtext=str_replace($match[0][$j], '<a id=outlink href="?act=forward&link='.urlencode(str_replace("&amp;", "&", $match[1][$j])).'"target=_blank>'.$match[2][$j].'</a>', $qtext);

//�������� mailto
$qtext=eregi_replace("\[mailto=([^]]+)\]([^]]+)\[/mailto\]", '<a href="mailto:\\1">\\2</a>', $qtext);

//�������� img
$qtext=eregi_replace("\[img +src=([^]]+)\]", '<img src="\\1">', $qtext);

//�������� ��������
$qtext=eregi_replace("\[smile([[:digit:]]+)\]", "<img src='$smilesURL/smile\\1.gif'>", $qtext);

//�������� ����
$qtext=preg_replace("/\[color *= *(#[\d|A-F|a-f]+)\]([^\]]+)\[\/color\]/i", "<font color='$1'>$2</font>", $qtext);


//�������� ������������� ��������
$raf=se_db_query(
  "SELECT *
  FROM forum_attached
  WHERE id_user='$uid' AND id_msg='$ext_quote'");

while ($afile=se_db_fetch_array($raf)) {
  $fileP=substr($afile['file'], 0, strlen($afile['file'])-4)."-1".substr($afile['file'], -4);
  if (file_exists("modules/forum/upload/$fileP"))
    $replace="<a href='modules/forum/upload/".$afile['file']."' title='���������' target=_blank ><img id=forumimg src='/modules/forum/upload/$fileP' border=0></a>";
  else
    $replace="<img id=forumimg src='/modules/forum/upload/".$afile['file']."'>";

  $qtext=preg_replace("/\[attimg +src=(".$afile['realname'].")\]/", $replace, $qtext);
  $replace="<div id=topic_attach><a id=topic_linkat href=?act=download&file=".$afile['file']." target=_blank>".$afile['realname']." (".round($afile['size']/1024, 2)." ��)</a> ���������� ����������: <b id=topic_dnlnumb>".$afile['counter']."</b></div>";
  $qtext=preg_replace("/\[attfile +src=(".$afile['realname'].")\]/", $replace, $qtext);

}

if (!empty($quote['date_time_edit']))
  $qtext.="\n<div id=edit>��������� ���� ��������������� ".date("d", $quote['date_time_edit'])." ".$month_R[date("m", $quote['date_time_edit'])].date(" Y ���� � H:i", $quote['date_time_edit'])."</div>";

if ($quote['moderator_edit']=='Y')
  $qtext.="\n<div id=moder>��������� ���� ��������������� ����������� ".date("d", $quote['date_time_moderator_edit'])." ".$month_R[date("m", $quote['date_time_moderator_edit'])].date(" Y ���� � H:i", $quote['date_time_moderator_edit'])."</div>";

//$qtext=nl2br($qtext);
$qtext=str_replace("\n", "<br>", $qtext);


  $forum_echo.= "<div id=mess_showTopicMsgText onmouseup='quote();'>$qtext</div></div></td></tr>
  					<tr><td>&nbsp;</td></tr>
                    <tr><td><input type='button' class=buttonForum id='button_quote' value='����������' onclick='cp();'></td></tr>
                    </tbody></table>";

  }
else
  $forum_echo.="<div id='mess_showTopicMsgText'></div>";

//////////////////////////////////////


  $forum_echo.= "<a name='edit'></a><form action='?act=newmsg' method='post' id='form' name='form' enctype=multipart/form-data>";
  require_once "msgform.php";
  $forum_echo.= "<input type='hidden' name='topic' value='$ext_id'>";
  $forum_echo.= "<input type='hidden' name='doGo' value=''>";

  //������������ ������

  if (isset($_SESSION['forum_attached'])) {
    $forum_attached=$_SESSION['forum_attached'];
    $forum_echo.="<tr><td colspan=2>&nbsp;</td></tr><tr><td colspan=2><div id=erm_attach>������������ �����: ";
    $allsize=0;
    foreach($forum_attached as $af) {
      $flist[]=$af['name']." (".round($af['size']/1024, 2)." ��)";
      $allsize+=$af['size'];
    }
    $forum_echo.=join(", ", $flist);
    $forum_echo.=". ����������� ������: ".count($forum_attached).", ����� ������: ".round($allsize/1024, 2)." ��";
    $forum_echo.=" ����������� ���������� ������: $maxFilesAttached, ����� ������: ".round($maxFilesAttachedSize/1024, 2)." ��</div></td></tr>";
  }
else
  $forum_echo.="<tr><td colspan=2>&nbsp;</td></tr><tr><td colspan=2><div id=erm_attach>������������� ������ ���</div></td><tr>";

  $forum_echo.="<tr>
  					<td colspan=2>&nbsp;</td></tr><tr><td colspan=2><div id=erm_mkattach>���������� ���� ��� �����������</div>
						<input id=erm_flattach type=file name='userfile'>
                        <input class=buttonForum id=erm_btnAttach name='upload' type=submit value='���������'></td></tr>";

  $forum_echo.= "<tr><td colspan=2>&nbsp;</td></tr>
  				 <tr><td colspan=2>
  					<div id=erm_ServicesButtons>
                    <input class=buttonForum id=erm_Add type='submit' name='doGo' value='��������' onclick='javascript:asubmit(); return false;'>";
  $forum_echo.= "<input class=buttonForum id=erm_Clear type='reset' value='��������'></div>";
  $forum_echo.= "</form></td></tr></tbody></table>";

  $forum_echo.="<script>document.all.erm_AreaForText.focus();</script>";
  }
else {

if ($uid==0) return;


$ext_topic=htmlspecialchars($_POST['topic'], ENT_QUOTES);


 //���������, �� ������ �� �����.
  $qtd = se_db_query(
    "SELECT forum_topic.id
     FROM forum_forums, forum_topic
     WHERE forum_topic.id='$ext_topic' AND forum_forums.id=forum_topic.id_forums AND forum_forums.enable='N'"
  );

  if (se_db_num_rows($qtd)!=0) {
    $forum_echo.= "<div id=message_warning>���� ����� ������. ���������� ����� ��� ���������!</div>";
    $forum_echo.= "<div id=butlayer><input class=buttonForum id=btBack type=button onclick='javascript:history.go(-1)' value=\"�����\"></div>";
    return;
  }

//���������, �� ������� �� ����.
$qtd = se_db_query(
  "SELECT id
   FROM forum_topic
   WHERE id=$ext_topic AND enable='N'"
);

if (se_db_num_rows($qtd)!=0) {
  $forum_echo.= "<div id=message_warning>��� ���� �������. ���������� ����� ��������� ���������!</div>";
  $forum_echo.= "<div id=butlayer><input class=buttonForum id=btBack type=button onclick='javascript:history.go(-1)' value=\"�����\"></div>";
  return;
}

$text = substr($_POST['text'], 0, $msgMaxLength);
$text = AddSlashes($text);

$date = time();
$ip = $_SERVER['REMOTE_ADDR'];

se_db_query("
  insert into `forum_msg`(text, id_topic, date_time, id_users, ip)
  values('$text', '$ext_topic', '$date', '$uid', '$ip')"
);

$rmm=se_db_query("
  SELECT MAX(id) AS maxid
  FROM forum_msg");

$mm=se_db_fetch_array($rmm);
$mm=$mm['maxid'];

//�������� ������ ���������� ���� � ������� ���
se_db_query(
  "UPDATE forum_topic
   SET date_time_new='$date', id_user_new='$uid'
   WHERE id='$ext_topic'"
);

//���� ���� ������������� �����
if (isset($_SESSION['forum_attached'])) {
  $forum_attached=$_SESSION['forum_attached'];
  foreach($forum_attached as $k=>$af) {
    se_db_query(
    "UPDATE forum_attached
    SET id_msg='$mm'
    WHERE file='$k'");
  }
}

$_SESSION['forum_attached']=NULL;
$_SESSION['forum_msgtext']=NULL;

//�������� ��������� ������

$rt=se_db_query("
  SELECT email, name, id_users
  FROM forum_topic
  WHERE id='$ext_topic'"
);

$topic=se_db_fetch_array($rt);

if (!empty($topic['email'])) {

$headers ="Content-Type: text/plain; charset=Windows-1251\n";
$headers .="From: \"����� ����� ".$_SERVER['HTTP_HOST']."\" <Postmaster>\n";
$headers .="Subject: ����������� �� ������ � ���� \"".$topic['name']."\"\n";
$headers .="X-Priority: 3\n";
$headers .="Return-Part: \"����� ����� ".$_SERVER['HTTP_HOST']."\"\n";
$headers .="Content-Transfer-Encoding: 8bit\n";
$headers .="Content-Type: text/plain; charset=Windows-1251\n";

$code=md5($topic['email'].$topic['id_users']."topic");

$message =
"������������!

�� �������� ��� ������, ��� ��� ������� �� �����
\"".stripslashes($topic['name'])."\" �� ����� ".$_SERVER['HTTP_HOST'].".

� ���� ���� �� ������� ������ ���������� ������
��������� ����� ���������. ����� ���������� ���,
�� ������ ������� �� ������:

http://".$_SERVER['HTTP_HOST']."/$page?act=showtopic&id=$ext_topic&new&last

���� �� ������ �� ������� ������� �� �����, �� ���������
�� ��������� ������:

http://".$_SERVER['HTTP_HOST']."/$page?act=unsubscribe&id=$ext_topic&code=$code


---
������� �������� � ���������� ������ EDGESTILE SiteEdit
www.SiteEdit.ru";

mail($topic['email'], "", $message, $headers);

};


Header("Location: http://".$_SERVER['HTTP_HOST']."/".$_page."?act=showtopic&id=$ext_topic&new&last&".time());
exit();

}


?>