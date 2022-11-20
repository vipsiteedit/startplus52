<?php

Global $_page;

    $word[1] = "¬ведите новое им€ раздела";
    $word[2] = "—охранить";
    $word[3] = "Ќазад";
    $word[4] = "¬ведите им€ раздела";
    $word[5] = "ƒобавить/»зменить им€ раздела";

$area_id = 0;
if (isset($_GET['id']))
    $area_id=htmlspecialchars($_GET['id'], ENT_QUOTES);

//ќсновна€ таблица дл€ параметров
if (!isset($_POST['save_name']))
{
    $result = se_db_query("SELECT name FROM forum_area WHERE id=$area_id");
    if (!empty($result)) {
        $res = se_db_fetch_array($result);
        $res = stripslashes(htmlspecialchars($res['name'], ENT_QUOTES));
    }

    $rootforum_echo .= '<h3 class=forumTitle id=adm_fparTitle>'.$word[5].'</h3>
                        <form action="?act=areaedit" method="post">';

    $rootforum_echo .= '<table class=tableForum id=table_forumParam><tbody id=tableBody>
    					<tr><td class=title id=title_admName>
    					<div id=adm_tName>'.$word[1].'</div></td>
                        <td class=field id=field_admName>
                        <input type=hidden value='.$area_id.' name=aid>
                        <input class=inputForum id=adm_inpName type=text name=area_name value='.$res.'></td></tr>
                        <tr><td>&nbsp;</td></tr>
                        <tr><td>
                        <div id=adm_srvcBtn>
                        <input class=buttonForum id=adm_btnSave type=submit name=save_name value='.$word[2].'>
                        <input class=buttonForum id=adm_bBkTMn type=button value='.$word[3].' onclick="javascript:history.back(-1)">
                        </div></td></tr>
                        <tr><td>&nbsp;</td></tr>
                        </tbody></table></form>';
}

//а здесь уже их обработка - либо изменение текущего раздела либо добавление нового
else
{
    $area_id   = htmlspecialchars($_POST['aid'], ENT_QUOTES);
    $area_name = $_POST['area_name'];

    //если не указано им€ раздела
    if (empty($area_name))
    {
         $rootforum_echo .= '<div id=message_warning>'.$word[4].'</div>
                            <div id=butlayer><input class=buttonForum id=btnBack type=button value="'.$word[3].'" onclick="javascript:history.back(-1)"></div>';
    }

    else
    {
        if ($area_id == 0) // если создаетс€ новый раздел
        {
            $rmax = se_db_query("
                  SELECT max(id) AS id, max(order_id) AS oid
                  FROM forum_area;");

            $max    = se_db_fetch_array($rmax);
            $maxid  = $max['id']+1;
            $maxord = $max['oid']+1;

            se_db_query("INSERT INTO forum_area
                        (id,order_id, name)
                         VALUES ('$maxid','$maxord', '$area_name')");
            Header("Location: "._HOST_.'/'.$_page."?act=main");
            exit();
        }
        else // если редактируетс€ существующий
        {
            se_db_query("UPDATE forum_area
                         SET name='$area_name'
                         WHERE id='$area_id'");
            Header("Location: http://".$_SERVER['HTTP_HOST']."/".$_page."?act=main");
            exit();
        }
    }

}

?>