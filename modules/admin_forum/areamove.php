<?php

Global $_page;

    $word[1] = "Невозможно поднять раздел";
    $word[2] = "Невозможно опустить раздел";
    $word[3] = "Назад";

$order_id = htmlspecialchars($_GET['order'],ENT_QUOTES);
$area_id  = htmlspecialchars($_GET['id'],ENT_QUOTES);
$action   = htmlspecialchars($_GET['do'],ENT_QUOTES);

if ($action == 1){
    $result = mysql_query("SELECT min(order_id) AS oid FROM forum_area;");
    $res = mysql_fetch_array($result);
    if ($res['oid'] == $order_id)
        $rootforum_echo .= "<div id=message_warning>$word[1]</div>
        <div id=butlayer><input class=buttonForum id=mess_btnBack type=button value=\"$word[3]\" onclick='javascript:history.back(-1)'></div>";

    else {
        $result = mysql_query("SELECT id,order_id
                               FROM forum_area
                               WHERE order_id < '$order_id'
                               ORDER BY order_id
                               DESC LIMIT 1");
        $res = mysql_fetch_array($result);
        $area_ord_prev = $res['order_id'];
        $area_id_prev  = $res['id'];
        mysql_query("UPDATE forum_area
                     SET order_id = '$area_ord_prev'
                     WHERE id='$area_id'");
        mysql_query("UPDATE forum_area
                     SET order_id = '$order_id'
                     WHERE id='$area_id_prev'");

        Header("Location: http://".$_SERVER['HTTP_HOST']."/".$_page."?act=main");
        exit();
    }

}

if ($action == 2){
    $result = mysql_query("SELECT max(order_id) AS oid FROM forum_area;");
    $res = mysql_fetch_array($result);
    if ($res['oid'] == $order_id)
        $rootforum_echo .= "<div id=message_warning>$word[2]</div>
                            <div id=butlayer>
                            <input class=buttonForum id=mess_btnBack type=button value=\"$word[3]\" onclick='javascript:history.back(-1)'></div>";
    else {
        $result = mysql_query("SELECT id,order_id FROM forum_area WHERE order_id > '$order_id' ORDER BY order_id LIMIT 1");
        $res = mysql_fetch_array($result);
        $area_ord_next = $res['order_id'];
        $area_id_next  = $res['id'];
        mysql_query("UPDATE forum_area
                     SET order_id = '$area_ord_next'
                     WHERE id='$area_id';");
        mysql_query("UPDATE forum_area
                     SET order_id = '$order_id'
                     WHERE id='$area_id_next'");

        Header("Location: http://".$_SERVER['HTTP_HOST']."/".$_page."?act=main");
        exit();
    }
}

?>