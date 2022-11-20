<?php

	Global $_page;

    $word[1] = 'Изменить';
    $word[2] = 'Удалить';
    $word[3] = 'Выше';
    $word[4] = 'Ниже';
    $word[5] = 'Добавить форум';
    $word[6] = 'Добавить раздел';
    $word[7] = 'Список форумов';


$ra = mysql_query("
  SELECT id, order_id, name
  FROM forum_area
  ORDER BY order_id;"
);



$rootforum_echo .= '<h3 class=forumTitle id=forumList>'.$word[7].'</h3>
					<div id=sumoders><a id=sulink href="/'.$_page.'/?act=usermoder">Список супермодераторов</a></div>
					<TABLE class=tableForum id=table_main><TBODY class=tableBody>
                    <TR vAlign=top>
                    <TD class="title" id=title_MainForum><div id=main_MainForum>Форум</div></TD>
                    <TD class="title" id=title_MainTopic><div id=main_MainTopic>Тем</div></TD>
                    <TD class="title" id=title_MainUpdate><div id=main_MainUpdate>Управление</div></TD></TR>';

if (!empty($ra))
while ($adm_forum = se_db_fetch_array($ra))
        {
            $area_id = htmlspecialchars($adm_forum['id'], ENT_QUOTES);
            $ar_order_id = $adm_forum['order_id'];
            $rootforum_echo.= "<tr><td id=field_Topic class=field colspan=2>
                               <div id=forumrazdel>".htmlspecialchars($adm_forum['name'], ENT_QUOTES)."</div></td>
                               <td class=field id=fld_Menu><div id=adm_commands>
                               <a class=foru_Edit id=adm_areaEdit href='?act=areaedit&id=$area_id'>$word[1]</a>
                               <a class=foru_Edit id=adm_areaDel href='?act=areadel&id=$area_id'>$word[2]</a>
                               <a class=foru_Edit id=adm_areaUp href='?act=areamove&id=$area_id&order=$ar_order_id&do=1'>$word[3]</a>
                               <a class=foru_Edit id=adm_areaDown href='?act=areamove&id=$area_id&order=$ar_order_id&do=2'>$word[4]</a>
                               </div></td></tr>";


            $rf = mysql_query("SELECT id, order_id, name, description
                               FROM forum_forums
                               WHERE id_area='$area_id'
                               ORDER BY order_id");

            while ($result = mysql_fetch_array($rf))
            {
                $id_forum=$result['id'];
                $name=htmlspecialchars($result['name'], ENT_QUOTES);
                $forder_id = $result['order_id'];
                $description=htmlspecialchars($result['description'], ENT_QUOTES);

                $rt = mysql_query("
                            SELECT id_forums
                            FROM forum_topic
                            WHERE forum_topic.id_forums=$id_forum;"
                            );

                  $count=mysql_num_rows($rt);

                  $rootforum_echo .= "<tr><td class=\"field\" id=field_MainForum><div id=main_ForumName>
                  <a href='?act=mvtpc&id=$id_forum' id=main_linkForum>$name</a>
                  <div id=main_ShDescr>$description</div></div></TD>
                  <td class=\"field\" id=field_MainTopic><div id=main_MessMount>$count</div></td>
                  <TD class=\"field\" id=fld_Menu><div id=adm_cmdTopic>
                  <a class=foru_Edit href='?act=forumedit&id=$id_forum' id=adm_topicEdit>$word[1]</a>
                  <a class=foru_Edit href='?act=forumdel&id=$id_forum' id=adm_topicDel>$word[2]</a>
                  <a class=foru_Edit href='?act=forummove&id=$id_forum&order=$forder_id&do=1' id=adm_topicUp>$word[3]</a>
                  <a class=foru_Edit href='?act=forummove&id=$id_forum&order=$forder_id&do=2' id=adm_topicDown>$word[4]</a>
                  <a class=foru_Edit href='?act=usermoder&id=$id_forum'>Модератор</div>
                  </td>
                  </tr>";
            }
            $rootforum_echo .= "<TR><TD colspan=3>&nbsp;</TD>
            				   <tr><td colspan=3 id=field_addTopic><div id=main_addTopic>
                               <form action=\"?act=forumedit\" method=POST>
                               <input class=buttonForum id=main_btnAddForum type=submit value=\"$word[5]\"></form>
                               </div></td></tr>
                               <TR><TD colspan=3>&nbsp;</TD>";
        }

$rootforum_echo .= "<TD colspan=3>&nbsp;</TD>
					<TR><TD id=field_AddArea colspan=3><div id=main_addArea>
                    <form action=\"?act=areaedit\" method=POST><input class=buttonForum id=main_btnAddArea type=submit value=\"$word[6]\"></form>
                    </div></TD></TR>
                    <TD colspan=3>&nbsp;</TD>
                    </tbody></table>";

?>