<?php

Global
    $_page;

    $word[1] = "Нет такого пользователя";
    $word[2] = "Введите имя";
    $word[3] = "Назад";

if (isset($_POST['add_banan'])){
    if (!empty($_POST['banan_to'])){
        $user_name = htmlspecialchars($_POST['banan_to'], ENT_QUOTES);
        $result    = mysql_query("SELECT id FROM forum_users WHERE nick = '$user_name'");

        if (mysql_num_rows($result)){
            $user = mysql_fetch_array($result);
            $user_id = $user['id'];
            mysql_query("UPDATE forum_users SET enabled = 'N' WHERE id = '$user_id' ");
            Header("Location: http://".$_SERVER['HTTP_HOST']."/".$_page."?act=users");
            exit();
        }
        else
            $rootforum_echo .= "<div id=message_warning>$word[1]</div>
                           <div id=butlayer><input class=forumButton id=mess_btnBack type=button value=\"$word[3]\" onclick='javascript:history.back(-1)'></div>";
    }
    else
        $rootforum_echo .= "<div id=message_warning>$word[2]<.вшм>
                           <div id=butlayer><input class=forumButton id=mess_btnBack type=button value=\"$word[3]\" onclick='javascript:history.back(-1)'></div>";

}

if (isset($_POST['del_banan'])){
    $user_id = htmlspecialchars(@$_POST['ban_list'], ENT_QUOTES);
    mysql_query("UPDATE forum_users SET enabled = 'Y' WHERE id = '$user_id'");
    Header("Location: http://".$_SERVER['HTTP_HOST']."/".$_page."?act=users");
    exit();
}

?>