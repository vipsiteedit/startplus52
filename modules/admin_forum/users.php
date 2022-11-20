<?php

    $word[1]  = "Выберите пользователя";
    $word[4]  = "Управление статусами";
    $word[5]  = "Добавить";
    $word[6]  = "Изменить";
    $word[7]  = "Удалить";
    $word[8]  = "Назначение статусов";
    $word[9]  = "Установить";
    $word[10] = "Черный список";
    $word[11] = "Добавить в список";
    $word[12] = "Удалить из списка";

$result = mysql_query("SELECT id, name FROM forum_status ORDER BY id");

$statuslist = "<select id=adm_slcStNm name=status_list>";
while ($status = mysql_fetch_array($result)){
    $id   = $status['id'];
    $name = $status['name'];
    $statuslist .= "<option value='$id'>$name";
}
$statuslist .= "</select>";

$result = mysql_query("SELECT id, nick FROM forum_users WHERE enabled = 'N'");
$banlist = "<select id=adm_slcBnNmLs name=ban_list><option>-- $word[1] --";
while ($ban = mysql_fetch_array($result)){
    $id   = $ban['id'];
    $nick = $ban['nick'];
    $banlist .= "<option value='$id'>$nick";
}
$banlist .= "</select>";


// Назначение модераторов
$rootforum_echo .= '<div id=adm_StsMk><h3 class=forumTitle id=adm_StsMng>'.$word[4].'</h3>
                    <form action="?act=userstatus" method=POST>
                    <input class=buttonForum id=adm_bAdd type=submit value='.$word[5].' name=add>
                    <input class=inputForum id=adm_inpNmSt type=input name=status_name>
                    <input class=buttonForum id=adm_bEd type=submit name=edit value='.$word[6].'>
                    '.$statuslist.'
                    <input class=buttonForum id=adm_bDel type=submit name=del value='.$word[7].'>
                    </form>
                    </div>

                    <div id=adm_StsSg><h3 class=forumTitle id=adm_StsBc>'.$word[8].'</h3>
                    <form action="?act=userstatus" method=POST>
                    '.$statuslist.'
                    <input class=inputForum id=adm_inpNNmSt type=text name=user_name>
                    <input class=buttonForum id=adm_bSet type=submit name=save_status value='.$word[9].'>
                    <input class=buttonForum id=adm_bUnset type=submit name=del_status value='.$word[7].'>
                    </form>
                    </div>

                    <div id=adm_BlLst><h3 class=forumTitle id=adm_BlcLst>'.$word[10].'</h3>
                    <form action="?act=userban" method=POST>
                    <input class=inputForum id=adm_inpBnNm type=text name=banan_to>
                    <input class=buttonForum id=adm_bAddBn type=submit name=add_banan value='.$word[11].'>
                    '.$banlist.'
                    <input class=buttonForum id=adm_bDelBn type=submit name=del_banan value='.$word[12].'>
                    </form>';


?>