<?php

Global $_page;

    $word[1] = "Вы действительно хотите удалить форум";
    $word[2] = "Да";
    $word[3] = "Нет";
    $word[4] = "Форум не пустой. Удалить форум вместе со всеми темами";
    $word[5] = "Назад";

// если кнопочка "Да" не нажата и "Да, удалить вместе с темами" не нажата
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
// и если нажата
else
{
  if (!isset($_POST['yes_all'])) { // Если не нажата кнопка "Удалить вместе с темами"
    $forum_id = htmlspecialchars($_POST['forum_id'], ENT_QUOTES);

    $result = mysql_query("SELECT name FROM forum_topic WHERE id_forums='$forum_id';");
    $res = mysql_fetch_array($result);
    if ($res != ''){ // Если в форуме есть темы, т.е. он не пустой
        // Выводим вопрос "Удалить форум вместе со всеми темами"
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
    else { // Удаляем форум, он пустой
        mysql_query("DELETE FROM forum_forums WHERE id='$forum_id'");
        Header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."?act=main");
        exit();
    }
  }
  // Если нажата кнопка удалить вместе с темами
  else { // Удаляем форум вместе со всеми темами
      $forum_id = htmlspecialchars($_POST['forum_id'], ENT_QUOTES);

  	  // Удаляем темы, принадлежащие данному форуму
  	  $result = mysql_query("SELECT id FROM forum_topic WHERE id_forums='$forum_id';");
  	  if (!empty($result)) {  	  	while ($res = mysql_fetch_array($result)) { // $res['id'] - id строки таблицы топиков
			// Удаляем сообщения из данной темы
            $result_msg = mysql_query("SELECT id FROM forum_msg WHERE id_topic='".$res['id']."';");
            if (!empty($result_msg)) {            	mysql_query("DELETE FROM forum_msg WHERE id_toipc='".$res['id']."'");            }

			// Удаляем тему
			mysql_query("DELETE FROM forum_topic WHERE id='".$res['id']."'");  	  	}  	  }
  	  // Наконец удаляем форум, он пустой
      mysql_query("DELETE FROM forum_forums WHERE id='$forum_id'");
  	  Header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."?act=main");
      exit();  }
}

?>