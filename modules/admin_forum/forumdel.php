<?php

Global $_page;

    $word[1] = "�� ������������� ������ ������� �����";
    $word[2] = "��";
    $word[3] = "���";
    $word[4] = "����� �� ������. ������� ����� ������ �� ����� ������";
    $word[5] = "�����";

// ���� �������� "��" �� ������ � "��, ������� ������ � ������" �� ������
if ( (!isset($_POST['yes'])) && (!isset($_POST['yes_all'])) )
{
    $forum_id=htmlspecialchars($_GET['id'], ENT_QUOTES);

    $result = mysql_query("SELECT name FROM forum_forums WHERE id='$forum_id';");
    $res = mysql_fetch_array($result);
    $res = $res['name'];

    $rootforum_echo .= "<form action='?act=forumdel' method=POST>
                       <div id=adm_Del><div id=adm_DelMess>
                       $word[1] \"".$res."\"?
                       </div>
                       <input type=hidden name='forum_id' value='$forum_id'>
                       <input class=buttonForum id=adm_bYes type=submit name='yes' value='$word[2]'>
                       <input class=buttonForum id=adm_bNo type=button value='$word[3]' onclick='javascript:history.back(-1)'>
                       </div></form>";
}
// � ���� ������
else
{
  if (!isset($_POST['yes_all'])) { // ���� �� ������ ������ "������� ������ � ������"
    $forum_id = htmlspecialchars($_POST['forum_id'], ENT_QUOTES);

    $result = mysql_query("SELECT name FROM forum_topic WHERE id_forums='$forum_id';");
    $res = mysql_fetch_array($result);
    if ($res != ''){ // ���� � ������ ���� ����, �.�. �� �� ������
        // ������� ������ "������� ����� ������ �� ����� ������"
        $rootforum_echo .= "<form action='?act=forumdel' method=POST>
             				<div id=adm_Del><div id=adm_DelMess>
             				$word[4]?
             				</div>
             				<input type=hidden name='forum_id' value='$forum_id'>
             				<input class=buttonForum id=adm_bYes type=submit name='yes_all' value='$word[2]'>
             				<input class=buttonForum id=adm_bNo type=button value='$word[3]' onclick='javascript:history.back(-3)'>
             				</div></form>";
      /*
        $rootforum_echo .= "<div id=message_warning>$word[4]</div>
        <div id=butlayer><input class=buttonForum id=mess_btnBack type=button value=\"$word[5]\" onclick='javascript:history.go(-2)'></div>"; */
    }
    else { // ������� �����, �� ������
        mysql_query("DELETE FROM forum_forums WHERE id='$forum_id'");
        Header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."?act=main");
        exit();
    }
  }
  // ���� ������ ������ ������� ������ � ������
  else { // ������� ����� ������ �� ����� ������
      $forum_id = htmlspecialchars($_POST['forum_id'], ENT_QUOTES);

  	  // ������� ����, ������������� ������� ������
  	  $result = mysql_query("SELECT id FROM forum_topic WHERE id_forums='$forum_id';");
  	  if (!empty($result)) {  	  	while ($res = mysql_fetch_array($result)) { // $res['id'] - id ������ ������� �������
			// ������� ��������� �� ������ ����
            $result_msg = mysql_query("SELECT id FROM forum_msg WHERE id_topic='".$res['id']."';");
            if (!empty($result_msg)) {            	mysql_query("DELETE FROM forum_msg WHERE id_toipc='".$res['id']."'");            }

			// ������� ����
			mysql_query("DELETE FROM forum_topic WHERE id='".$res['id']."'");  	  	}  	  }
  	  // ������� ������� �����, �� ������
      mysql_query("DELETE FROM forum_forums WHERE id='$forum_id'");
  	  Header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."?act=main");
      exit();  }
}

?>