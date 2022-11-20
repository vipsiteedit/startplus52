<?php

Global $_page;

    $word[1] = "Невозможно поднять форум";
    $word[2] = "Назад";
    $word[3] = "Невозможно опустить форум";

$order_id = htmlspecialchars($_GET['order'], ENT_QUOTES);
$forum_id = htmlspecialchars($_GET['id'], ENT_QUOTES);
$action   = htmlspecialchars($_GET['do'], ENT_QUOTES);

// находит минимальное (если $do=1) либо максимальное ($do=2) значение порядка для форума в текущем разделе
function firstlast($id, $do)
{
    $result = mysql_query("SELECT id_area
                           FROM forum_forums
                           WHERE id = $id");
    $res = mysql_fetch_array($result);
    $area_id = $res['id_area'];

    if ($do == 1)
    {
            $result = mysql_query("SELECT min(order_id) AS oid
                                   FROM forum_forums
                                   WHERE id_area = '$area_id'");
            $res = mysql_fetch_array($result);
            return $res['oid'];
    }
    if ($do == 2)
    {
            $result = mysql_query("SELECT max(order_id) AS oid
                                   FROM forum_forums
                                   WHERE id_area = '$area_id'");
            $res = mysql_fetch_array($result);
            return $res['oid'];
    }
}

function ownarea($forum)
{
    $rownarea = mysql_query("SELECT id_area FROM forum_forums WHERE id = '$forum'");
    $ownar = mysql_fetch_array($rownarea);
    return $ownar['id_area'];
}

$area_id = ownarea($forum_id);

// поднять
if ($action == 1)
{
     if ($order_id != firstlast($forum_id, 1))
     {
        $result = mysql_query("SELECT id, order_id
                               FROM forum_forums
                               WHERE order_id < '$order_id'
                               AND id_area = '$area_id'
                               ORDER BY order_id
                               DESC LIMIT 1");

        $res = mysql_fetch_array($result);
        $prev_forum_id = $res['id'];
        $prev_ord_id   = $res['order_id'];

        mysql_query("UPDATE forum_forums
                    SET order_id = '$prev_ord_id'
                    WHERE id = $forum_id");
        mysql_query("UPDATE forum_forums
                     SET order_id = '$order_id'
                     WHERE id = '$prev_forum_id'");
        Header("Location: http://".$_SERVER['HTTP_HOST']."/".$_page."?act=main");
        exit();
     }
     else
        $rootforum_echo .= "<div id=message_warning>$word[1]</div>
                            <div id=butlayer><input class=buttonForum id=mess_btnBack type=button value='$word[2]' onclick='javascript:history.back()'></div>";
}


// опустить
if ($action == 2)
{
    if ($order_id != firstlast($forum_id, 2))
    {
        $result = mysql_query("SELECT id, order_id
                               FROM forum_forums
                               WHERE order_id > '$order_id'
                               AND id_area = '$area_id'
                               ORDER BY order_id
                               LIMIT 1");

        $res = mysql_fetch_array($result);
        $next_forum_id = $res['id'];
        $next_ord_id   = $res['order_id'];

        mysql_query("UPDATE forum_forums
                    SET order_id = '$next_ord_id'
                    WHERE id = $forum_id");
        mysql_query("UPDATE forum_forums
                     SET order_id = '$order_id'
                     WHERE id = '$next_forum_id'");
        Header("Location: http://".$_SERVER['HTTP_HOST']."/".$_page."?act=main");
        exit();
    }
    else
       $rootforum_echo .= "<div id=message_warning>$word[3]</div>
                           <div id=butlayer><input class=buttonForum id=mess_btnBack type=button value='$word[2]' onclick='javascript:history.back()'></div>";
}

?>