<?php

Global $_page;

if (isset($_GET['id'])){

    $forum_id = htmlspecialchars($_GET['id'], ENT_QUOTES);
    $result = mysql_query("SELECT id, name FROM forum_forums ORDER BY id");

    $forumlist = "<select id=adm_slcNames name=forum_list>";
    while ($forums = mysql_fetch_array($result)){
        $id   = $forums['id'];
        $name = $forums['name'];
        if ($forum_id == $id)
            $forumlist .= "<option selected value='$id'>$name";
        else
            $forumlist .= "<option value='$id'>$name";
    }
    $forumlist .= "</select>";

    $rootforum_echo .= '<h3 class=forumTitle id=adm_moveTitle>Перенос тем</h3>
                        <form method=post>
                        <table class=forumTable id=adm_moveTable>
                        <tr><td colspan=2>'.$forumlist.'</td></tr>
                        <tr><td class=title id=adm_titleMvCln>&nbsp;</td>
                        <td class=title id=adm_fieldMvTh><div id=adm_fMvTh>Тема</div>
                        </td></tr>';

    $result = mysql_query("SELECT id, name FROM forum_topic WHERE id_forums = $forum_id");

    while ($topics = mysql_fetch_array($result)){
        $rootforum_echo .= "<tr><td class=field id=adm_titleCh><input type=checkbox name='checks[]' value='".$topics['id']."'></td>
                            <td class=field id=adm_fieldMvThN><div id=adm_fMvThN>".$topics['name']."</div></td></tr>";
    }

    $rootforum_echo .= "
    					<tr><td colspan=2>&nbsp;</td></tr>
                        <tr><td colspan=2><div id=adm_mvSrvBtn>
                        <input class=buttonForum id=adm_bMvOut type=submit name='Move' value='Перенести'>
                        <input class=buttonForum id=adm_bMvBack type=button value='Назад' onclick='javascript:history.back()'></div></td></tr>
                        <tr><td colspan=2>&nbsp;</td></tr>
                        </tbody></table>
                        </form>";
}

if (isset($_POST['checks'])){

    $forum_id = htmlspecialchars($_POST['forum_list'], ENT_QUOTES);
    $str = "";

    foreach($_POST['checks'] as $id){
        $str ? $str .= ',' : $str;
        $str .= "'".$id."'";
    }

    mysql_query ("UPDATE forum_topic SET id_forums = '$forum_id' WHERE id IN (".$str.")");
    Header("Location: http://".$_SERVER['HTTP_HOST']."/".$_page."?act=main");
    exit();
}
?>