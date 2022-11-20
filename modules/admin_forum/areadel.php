<?php

Global $_page;

    $word[1] = "Вы действительно хотите удалить раздел";
    $word[2] = "Раздел не пустой и не может быть удален";
    $word[3] = "Да";
    $word[4] = "Нет";
    $word[5] = "Назад";

// если кнопочка "Да" не нажата
if (!isset($_POST['yes']))
{
    $area_id=htmlspecialchars($_GET['id'], ENT_QUOTES);

    $result = mysql_query("SELECT name FROM forum_area WHERE id=$area_id");
    $res = mysql_fetch_array($result);
    $res = $res['name'];

    $rootforum_echo .= '<form action="?act=areadel" method=POST><div id=adm_Del><div id=adm_DelMess>
                       '.$word[1].' "'.$res.'"
                       </div><input type=hidden name="area" value="'.$area_id.'">
                       <input class=buttonForum id=adm_bYes type=submit name="yes" value="'.$word[3].'">
                       <input class=buttonForum id=adm_bNo type=button value="'.$word[4].'" onclick="javascript:history.back(-1)">
                       </div>
                       </form>';
}

// и если нажата
else
{
    $area_id = htmlspecialchars($_POST['area'], ENT_QUOTES);

    $result = mysql_query("SELECT name FROM forum_forums WHERE id_area='$area_id'");
    $res = mysql_fetch_array($result);
    if ($res != ''){
        $rootforum_echo .= "<div id=message_warning>$word[2]</div>
        					<div id=butlayer>
                            <input class=buttonForum id=mess_btnBack type=button value='$word[5]' onclick='javascript:history.go(-2)'>
                            </div>";
        }
    else{
        mysql_query("DELETE FROM forum_area WHERE id='$area_id'");
        Header("Location: http://".$_SERVER['HTTP_HOST']."/$_page?act=main");
        exit();
        }
}

?>