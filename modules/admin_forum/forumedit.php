<?php

Global $_page;

    $word[1]  = "Видимый";
    $word[2]  = "Невидимый";
    $word[3]  = "Параметры форума";
    $word[4]  = "Сохранить";
    $word[5]  = "Назад";
    $word[6]  = "Следует ввести имя форума";
    $word[7]  = "Название форума";
    $word[8]  = "Краткое описание форума";
    $word[9]  = "Состояние форума";
    $word[10] = "Раздел";



$forum_id = 0;
$forname  = '';
$descr    = '';
$area     = "";

if (isset($_GET['id']))
    $forum_id=htmlspecialchars($_GET['id'], ENT_QUOTES);

//Основная таблица для параметров
if (!isset($_POST['save_name']))
{
        $result = mysql_query("SELECT name, id_area, description, visible FROM forum_forums WHERE id=$forum_id");
        $res = mysql_fetch_array($result);
        $forname = stripslashes(htmlspecialchars($res['name'], ENT_QUOTES));
        $descr = stripslashes(htmlspecialchars($res['description'], ENT_QUOTES));
        $visible = htmlspecialchars($res['visible'], ENT_QUOTES);
        $area_id = $res['id_area'];
        if ($visible == 'Y')
            $status = "<option selected value=\"Y\">$word[1]<option value=\"N\">$word[2]";
        else $status = "<option selected value=\"Y\">$word[1]<option value=\"N\">$word[2]";


    $result = mysql_query("SELECT id, name FROM forum_area ORDER BY id");
    while ($res = mysql_fetch_array($result))
    {
        if ($res['id'] == $area_id) $area .= "<option selected value=".$res['id'].">".htmlspecialchars($res['name']);
        else $area .= "<option value=".$res['id'].">".htmlspecialchars($res['name']);
    }

    $rootforum_echo .= "<h3 class=forumTitle id=adm_fparTitle>$word[3]</h3>
                        <form action='?act=forumedit' method='post'>
                        <table class=tableForum id=table_forumParam>
                        <tbody class=tableBody>
                        <tr><td class=title id=title_admName>
                        <div id=adm_tName>$word[7]</div></td>
                        <td class=field id=field_admName><input type=hidden value=\"$forum_id\" name=fid>
                        <input class=inputForum id=adm_inpName type=text name=forum_name value='$forname'></td></tr>
                        <tr><td class=title id=title_admShDesc><div id=adm_tShDesc>$word[8]</div></td>
                        <td class=field id=field_admShDesc><textarea class=areaForum id=adm_arShDesc name=descript>$descr</textarea></td></tr>
                        <tr><td class=title id=title_admState><div id=adm_tState>$word[9]</div></td>
                        <td class=field id=field_admState><select id=adm_slcState name=\"status\">".$status."
                        </select></td></tr>
                        <tr><td class=title id=title_admSect><div id=adm_tSect>$word[10]</div>
                        <td class=field id=field_forumSect><select id=adm_slcSect name=\"area_id\">".$area."</select></td></tr>
                        <tr><td>&nbsp;</td></tr>
                        <tr><td>
                        <div id=adm_srvcBtn>
                        <input class=buttonForum id=adm_btnSave type=submit name=save_name value=\"$word[4]\">
                        <input class=buttonForum id=adm_bBkTMn type=button value=\"$word[5]\" onclick='javascript:history.back(-1)'>
                        </div>
                        </td></tr>
                        <tr><td>&nbsp;</td></tr>
                        </tbody></table></form>";
}

// а теперь отправляем все в базу
else
{
    $forum_id   = htmlspecialchars($_POST['fid'], ENT_QUOTES);
    $forum_name = $_POST['forum_name'];
    $descr      = $_POST['descript'];
    $status     = htmlspecialchars($_POST['status'], ENT_QUOTES);
    $area_id    = htmlspecialchars($_POST['area_id'], ENT_QUOTES);

    if ($forum_name != '')
    {
        // Обновляем данные форума
        if ($forum_id != 0)
        {
           mysql_query("UPDATE forum_forums
                        SET name='$forum_name', description='$descr', visible='$status', id_area='$area_id'
                        WHERE id='$forum_id'");
           Header("Location: http://".$_SERVER['HTTP_HOST']."/".$_page."?act=main");
           exit();
        }

        // добавляем новый форум
        else
        {
            $rmax   = mysql_query("
                             SELECT max(id) AS id, max(order_id) AS oid
                             FROM forum_forums;");

            $max    = mysql_fetch_array($rmax);
            $maxid  = $max['id']  + 1;
            $maxord = $max['oid'] + 1;

            mysql_query("INSERT INTO forum_forums
                         (id, order_id, name, description, visible, id_area, `moderator`)
                         VALUES ('$maxid', '$maxord', '$forum_name', '$descr', '$status', '$area_id', '1');");
            Header("Location: http://".$_SERVER['HTTP_HOST']."/".$_page."?act=main");
            exit();
        }
    }

    // если не ввели имя форума
    else
        $rootforum_echo .= "<div id=message_warning>$word[6]</div>
                           <div id=butlayer><input class=buttonForum id=mess_btnBack type=button value=\"$word[5]\" onclick='javascript:history.back(-1)'></div>";

}

?>