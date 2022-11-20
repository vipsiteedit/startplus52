<?php

Global
    $_page;

    $word[1] = "Нет такого пользователя. Внимательно проверьте правильность написания";
    $word[2] = "Назад";
    $word[3] = "Введите ник пользователя";
    $word[4] = 'Назначение модератора для форума';
    $word[5] = "Модератор";
    $word[6] = 'На данном форуме нет супермодераторов';
    $word[7] = 'Назначение супермодератора для Форума';

    if (isset($_GET['id']) && !empty($_GET['id'])){
    	$forum_id = (int)$_GET['id'];
    	$result = mysql_query("SELECT forum_forums.name AS name,
        	                          forum_users.nick AS nick
	    					   FROM `forum_forums`, `forum_users`
	                           WHERE forum_forums.id = '$forum_id'
	                                 AND forum_users.id = forum_forums.moderator");
    	$forums = mysql_fetch_array($result);

	    $rootforum_echo .= '<div id=adm_Mdr><h3 class=forumTitle id=adm_MdrMk>'.$word[4].'"'.$forums['name'].'"</h3>
	    				<div id=curmoder>Текущий модератор: '.$forums['nick'].'</div>
	                    <form action="/'.$_page.'/?act=usermoder&id='.$forum_id.'" method=POST>
	                    <input class=inputForum id=adm_inpMdrNm type=text name=moder_name>
	                    <input class=buttonForum id=adm_bMd type=submit name=save_moder value='.$word[5].'>
                        <input class=buttonForum id=adm_bBack type=submit value="'.$word[2].'" name=Back>
	                    </form>
	                    </div>';

		if (isset($_POST['save_moder'])){
	    	if (!empty($_POST['moder_name'])){
	           $nickname = htmlspecialchars($_POST['moder_name'], ENT_QUOTES);
	           $sql_res = mysql_query("SELECT * FROM forum_users WHERE `nick` = '$nickname' LIMIT 1");
    	       if (@mysql_num_rows($sql_res) != 0){
               		$res = mysql_fetch_array($sql_res);
                    $moder_id = $res['id'];
				  	mysql_query("UPDATE forum_forums SET moderator = '$moder_id' WHERE id = '$forum_id' ");
					Header("Location: http://".$_SERVER['HTTP_HOST']."/".$_page."/");
	                exit();
	           }
	           else
					$rootforum_echo .= "<div id=adm_erMess>$word[1]</div>";
	        }
	        else
				$rootforum_echo .= "<div id=adm_erMess>$word[3]</div>";
    	}
        if (isset($_POST['Back'])){
        	Header('Location: http://'.$_SERVER['HTTP_HOST'].'/'.$_page.'/');
            exit();
        }
    }
    else {
    	$result = mysql_query("SELECT * FROM `forum_users` WHERE `smoderator` = 'Y'");
        $rootforum_echo .= '<div id=adm_Mdr><h3 class=forumTitle id=adm_MdrMk>'.$word[7].'</h3>';
        if (mysql_num_rows($result) != 0){
        	$rootforum_echo .= '<form method=POST><div id=adm_delSu><select id=adm_slDlsu name=supusr>';
        	while ($res = mysql_fetch_array($result))
                $rootforum_echo .= '<option value="'.$res['id'].'">'.$res['nick'];
            $rootforum_echo .= '</select><input class=buttonForum id=adm_bDelsu type=submit name=delsu value="Удалить"></div>';
        }
        else
        	$rootforum_echo .= '<form method=POST><div id=adm_erMess>'.$word[6].'</div>';

        $rootforum_echo .= '<div id=adm_addSu><input class=inputForum id=adm_inAdsu type=text name=suname>
        					<input class=buttonForum id=adm_bAddsu type=submit value="Сохранить" name=savesu>
        				    </div><input class=buttonForum id=adm_bdaBck type=submit name=back value="Назад"></form>';

        if (isset($_POST['delsu'])){
        	if (isset($_POST['supusr'])){
            	$id_su = (int)$_POST['supusr'];
                mysql_query("UPDATE `forum_users` SET `smoderator` = 'N' WHERE id = '$id_su'");
                Header('Location: http://'.$_SERVER['HTTP_HOST'].'/'.$_page.'/?act=usermoder');
            }
        }

        if (isset($_POST['savesu'])){
        	if (!empty($_POST['suname'])){
            	$name = htmlspecialchars($_POST['suname'],ENT_QUOTES);
                if (!empty($name)) $where = "`nick` = '$name'";
                else $where = "`id_author` = '-1'";
                
                $result = mysql_query("SELECT * FROM `forum_users` WHERE $where LIMIT 1");
                if (mysql_num_rows($result) != 0){
                	$res = mysql_fetch_array($result);
                    $id_su = $res['id'];
                    mysql_query("UPDATE `forum_users` SET `smoderator` = 'Y' WHERE id = '$id_su'");
                    Header('Location: http://'.$_SERVER['HTTP_HOST'].'/'.$_page.'/?act=usermoder');
                }
                else
                	$rootforum_echo .= '<div id=adm_erMess>'.$word[1].'</div>';
            }
            else
            	$rootforum_echo .= '<div id=adm_erMess>'.$word[3].'</div>';
        }

        if (isset($_POST['back'])){
        	Header('Location: http://'.$_SERVER['HTTP_HOST'].'/'.$_page.'/');
            exit();
        }
    }

?>