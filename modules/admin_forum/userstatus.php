<?php

Global
    $_page;

    $word[1] = "Введите статус";
    $word[2] = "Нельзя удалить статус, определенный по умолчанию";
    $word[3] = "Введите новое имя статуса";
    $word[4] = "Назад";
    $word[5] = "Введите имя";
    $word[6] = "Нет такого пользователя";

// добавление статусов
if (isset($_POST['add'])){
    if (empty($_POST['status_name']))
        $rootforum_echo .= "<div id=message_warning>$word[1]</div>
                           <div id=butlayer><input class=forumButton id=mess_btnBack type=button value=\"$word[4]\" onclick='javascript:history.back(-1)'></div>";
    else
    {
        $status_name = htmlspecialchars($_POST['status_name'], ENT_QUOTES);
        $max    = mysql_fetch_array(mysql_query("SELECT max(id) AS id FROM forum_status;"));
        $maxid  = $max['id']+1;
        mysql_query("INSERT INTO forum_status (id, name) VALUES ('$maxid', '$status_name')");
        Header("Location: http://".$_SERVER['HTTP_HOST']."/".$_page."?act=users");
        exit();
    }
}

// удаляем статус
if (isset($_POST['del'])){
    $status_id = $_POST['status_list'];
    if ($status_id != 1){
        mysql_query("UPDATE forum_users SET id_status = '1' WHERE id_status = '$status_id'");
        mysql_query("DELETE FROM forum_status WHERE id = '$status_id'");
        Header("Location: http://".$_SERVER['HTTP_HOST']."/".$_page."?act=users");
        exit();
    }
    else
        $rootforum_echo .= "<div id=message_warning>$word[2]</div>
                           <div id=butlayer><input class=forumButton id=mess_btnBack type=button value=\"$word[4]\" onclick='javascript:history.back(-1)'></div>";
}

if (isset($_POST['edit'])){
    $status_id = $_POST['status_list'];
    if (empty($_POST['status_name']))
           $rootforum_echo .= "<div id=message_warning>$word[3]</div>
                              <div id=butlayer><input class=forumButton id=mess_btnBack type=button value=\"$word[4]\" onclick='javascript:history.back(-1)'></div>";
    else
    {
        $status_name = htmlspecialchars($_POST['status_name'], ENT_QUOTES);
        mysql_query("UPDATE forum_status SET name = '$status_name' WHERE id = '$status_id'");
        Header("Location: http://".$_SERVER['HTTP_HOST']."/".$_page."?act=users");
        exit();
    }
}

if (isset($_POST['save_status'])){
    if (!empty($_POST['user_name'])){
        $user_name = htmlspecialchars($_POST['user_name'], ENT_QUOTES);
        $status    = htmlspecialchars($_POST['status_list'], ENT_QUOTES);
        $result    = mysql_query("SELECT id FROM forum_users WHERE nick = '$user_name'");

        if (mysql_num_rows($result)){
            $user = mysql_fetch_array($result);
            $user_id = $user['id'];
            mysql_query("UPDATE forum_users SET id_status = '$status' WHERE id = '$user_id' ");
            Header("Location: http://".$_SERVER['HTTP_HOST']."/".$_page."?act=users");
            exit();
        }
        else
            $rootforum_echo .= "<div id=message_warning>$word[6]</div>
                           <div id=butlayer><input class=forumButton id=mess_btnBack type=button value=\"$word[4]\" onclick='javascript:history.back(-1)'></div>>";
    }
    else
        $rootforum_echo .= "<div id=message_warning>$word[5]</div>
                           <div id=butlayer><input class=forumButton id=mess_btnBack type=button value=\"$word[4]\" onclick='javascript:history.back(-1)'></div>";

}

if (isset($_POST['del_status'])){

    if (!empty($_POST['user_name'])){
        $user_name = htmlspecialchars($_POST['user_name'], ENT_QUOTES);
        $result    = mysql_query("SELECT id FROM forum_users WHERE nick = '$user_name'");

        if (mysql_num_rows($result)){
            $user = mysql_fetch_array($result);
            $user_id = $user['id'];
            mysql_query("UPDATE forum_users SET id_status = '1' WHERE id = '$user_id' ");
            Header("Location: http://".$_SERVER['HTTP_HOST']."/".$_page."?act=users");
            exit();
        }
        else
            $rootforum_echo .= "<div id=message_warning>$word[6]</div>
                           <div id=butlayer><input class=forumButton id=mess_btnBack type=button value=\"$word[4]\" onclick='javascript:history.back(-1)'></div>";
    }
    else
        $rootforum_echo .= "<div id=message_warning>$word[5]</div>
                           <div id=butlayer><input class=forumButton id=mess_btnBack type=button value=\"$word[4]\" onclick='javascript:history.back(-1)'></div>";
}

?>