<?php

if (isset($_POST['text'])) $_SESSION['forum_msgtext']=$_POST['text'];
if (isset($_POST['topic'])) $_SESSION['forum_msgtopic']=$_POST['topic'];

if (isset($_POST['upload'])) {
  require "attached.php";
  return;
};


Global $_page;
//if (!is_numeric($ext_id)) return;

if (!isset($_POST['doGo'])) { // ######### ���� �� ������ ������ "��������"

	//���������, �� �������� �� ������������ ������
	if ($uid==0) {
    	$forum_echo.= "<div id=message_warning>��������� ����� ���� ����� ������ ������������������ ������������!</div>";
    	$forum_echo.= "<div id=butlayer><input class=buttonForum id=btBack type=button onclick='javascript:history.go(-1)' value=\"�����\"></div>";
    	return;
  	}

	$ext_id=intval(htmlspecialchars($_GET['id'], ENT_QUOTES));

	//���������, �� ������ �� �����.
	$qtd = se_db_query(
  		"SELECT id
   		FROM forum_forums
   		WHERE id='$ext_id' AND enable='N'"
		);

	if (se_db_num_rows($qtd)!=0) {
  		$forum_echo.= "<div id=message_warning>���� ����� ������. ���������� ����� ��� ���������!</div>";
  		$forum_echo.= "<div id=butlayer><input class=buttonForum id=btBack type=button onclick='javascript:history.go(-1)' value=\"�����\"></div>";
  		return;
	}

	if (isset($_SESSION['forum_msgtext']))
		$text=$_SESSION['forum_msgtext'];

	if (isset($_SESSION['forum_msgtopic']))
		$topic=$_SESSION['forum_msgtopic'];
	else
		$topic="";

	$rf = se_db_query("
  		SELECT forum_forums.id AS id, forum_forums.name AS forum, forum_forums.img AS img,
         	forum_forums.moderator AS moduid, forum_area.name AS area, forum_users.nick AS moderator,
         	forum_area.id AS idArea
  		FROM forum_forums, forum_area, forum_users
  		WHERE forum_forums.id='$ext_id' AND forum_area.id=forum_forums.id_area
        AND forum_forums.visible='Y' AND forum_users.id=forum_forums.moderator"
		);

	$forum=se_db_fetch_array($rf);
	$moduid=$forum['moduid'];
	$moderator=$forum['moderator'];
	$areaId = $forum['idArea'];
	$forum_echo.="<div id=path><a href='?act=main' id=pathlink>".htmlspecialchars($nameForum)."</a><div id=path_strl>$btnPathStrl</div><a href='?act=showarea&id=$areaId' id=pathlink>".htmlspecialchars($forum['area'], ENT_QUOTES)."</a>";
	$forumName = htmlspecialchars($forum['forum']);
	$forumId = $forum['id'];
	$forum_echo.= "<div id=path_strl>$btnPathStrl</div><a href='?act=showforum&id=$forumId' id=pathlink>$forumName</a></div>";

	$forum_echo.= "<form action='?act=newtopic&forum=$forumId' method='post' id='form' name='form' enctype=multipart/form-data>";
	$forum_echo.= "<div id=erm_ThemeName>�������� ����: <input class=inputForum id=erm_ThemeText type='text' maxlength='$msgMaxLengthTopic' name='topic' value='$topic'></div>";
	require_once "msgform.php";

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
  		$forum_echo.="����������� ������: ".count($forum_attached).", ����� ������: ".round($allsize/1024, 2)." ��";
  		$forum_echo.="����������� ���������� ������: $maxFilesAttached, ����� ������: ".round($maxFilesAttachedSize/1024, 2)." ��</div></td></tr>";
	}
	else
  		$forum_echo.="<tr><td colspan=2>&nbsp;</td></tr><tr><td colspan=2><div id=erm_attach>������������� ������ ���</div></td><tr>";

  	$forum_echo.="<tr>
  					<td colspan=2>&nbsp;</td></tr><tr><td colspan=2><div id=erm_mkattach>���������� ���� ��� �����������</div>
                         <input id=erm_flattach type=file name='userfile'>
                         <input class=buttonForum id=erm_btnAttach name='upload' type=submit value='���������'>
                    </td>
                </tr>";

	$forum_echo.= "<tr><td colspan=2>&nbsp;</td></tr><tr><td colspan=2><div id=erm_NewMess>�������� ����������� ��� ���������� ��������� �� e-mail: <input id=erm_inpNewMess type='text' maxlength='50' name='email'></div></td></tr>";
	$forum_echo.= "<tr><td colspan=2>&nbsp;</td></tr><tr><td colspan=2><div id=erm_ServicesButtons><input class=buttonForum id=erm_Add type='submit' name='doGo' value='��������'>";
	$forum_echo.= "<input class=buttonForum id=erm_Clear type='reset' value='��������'></div>";
	$forum_echo.= "</form></td></tr></tbody></table>";

}
else { // ################ ������ ������ "��������"

	$ext_text=$_POST['text'];

	if (empty($ext_text)) {
  		$forum_echo.= "<div id=message_warning>���� ��������� ������!</div>";
  		$forum_echo.= "<div id=butlayer><input class=buttonForum id=btBack type=button onclick='javascript:history.go(-1)' value=\"�����\"></div>";
  		return;
	}

	$topic = substr($_POST['topic'], 0, $msgMaxLengthTopic);
	$topic = AddSlashes($topic);
	$email = AddSlashes($_POST['email']);

	if (empty($topic)) {
  		$forum_echo.= "<div id=message_warning>�� ������� ���� ���������!</div>";
  		$forum_echo.= "<div id=butlayer><input class=buttonForum id=btBack type=button onclick='javascript:history.go(-1)' value=\"�����\"></div>";
  		return;
	}

	$date = time();
	$forum = htmlspecialchars($_GET['forum'], ENT_QUOTES);

	//���������, �� �������� �� ������������ ������
	if ($uid==0) {
  		$forum_echo.= "<div id=message_warning>��������� ����� ���� ����� ������ ������������������ ������������!</div>";
  		$forum_echo.= "<div id=butlayer><input class=buttonForum id=btBack type=button onclick='javascript:history.go(-1)' value=\"�����\"></div>";
  	return;
	}

	$ext_id=intval(htmlspecialchars($_GET['id'], ENT_QUOTES));

	//���������, �� ������ �� �����.
	$qtd = se_db_query(
  		"SELECT id
   		FROM forum_forums
   		WHERE id='$ext_id' AND enable='N'"
		);

	if (se_db_num_rows($qtd)!=0) {
  		$forum_echo.= "<div id=message_warning>���� ����� ������. ���������� ����� ��� ���������!</div>";
  		$forum_echo.= "<div id=butlayer><input class=buttonForum id=btBack type=button onclick='javascript:history.go(-1)' value=\"�����\"></div>";
  		return;
	}


	//��������� ����

	$rmax = se_db_query("
  		SELECT max(id) AS id
  		FROM forum_topic;");

	$max=se_db_fetch_array($rmax);
	$max=$max['id']+1;


	se_db_query("
  		INSERT INTO forum_topic (id, id_forums, name, priority, date_time, id_users, email, date_time_new, id_user_new)
  		VALUES ('$max', '$forum', '$topic', '0', '$date', '$uid', '$email', '$date', '$uid');"
	);


	//��������� ���������
	$text = substr($ext_text, 0, $msgMaxLength);
	$text = AddSlashes($text);

	se_db_query("
  		INSERT INTO forum_msg (text, id_topic, date_time, id_users)
  		VALUES ('$text', '$max', '$date', '$uid');"
		);


	//���� ���� ������������� �����
	if (isset($_SESSION['forum_attached'])) {

 		$rmm=se_db_query("
    		SELECT MAX(id) AS maxid
    		FROM forum_msg");

  		$mm=se_db_fetch_array($rmm);
  		$mm=$mm['maxid'];

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
	$_SESSION['forum_msgtopic']=NULL;

	Header("Location: http://".$_SERVER['HTTP_HOST']."/$_page?act=showtopic&id=$max&new");
	exit();
}

?>