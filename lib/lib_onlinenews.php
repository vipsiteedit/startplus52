<?php
/*
Библиотека online новостей для модуля onlinenews.tlb
# include lib_images.php
Дата: 18/02/2006
Автор: компания EDGESTILE
Щелконогов С.Н.
Фоминых Евгений

Все права защищены 2004-2006 www.edgestile.ru
*/

function replase_teg_edittext($text) {
   $trans = array("[b]" => "<b>", "[/b]" => "</b>",
               "[em]" => "<em>", "[/em]" => "</em>",
               "[u]" => "<u>", "[/u]" => "</u>",
               "[ul]" => "<ul>", "[/ul]" => "</ul>",
               "[ol]" => "<ol>", "[/ol]" => "</ol>",
               "[center]" => "<center>", "[/center]" => "</center>",
               "[sup]" => "<sup>", "[/sup]" => "</sup>",
               "[sub]" => "<sub>", "[/sub]" => "</sub>",
               "[code]" => "<pre id=code>", "[/code]" => "</pre>",
               "[quote]" => "<div id=quote>", "[/quote]" => "</div>");
        $text=strtr($text, $trans);
	//Заменяем url
	preg_match_all("/\[a +href=([^]]+)\]([^]]+)\[\/a\]/i", $text, $match, PREG_PATTERN_ORDER);
	   //Заменяем url
	    	for($j=0; $j<count($match[1]); $j++) 
  		$text=str_replace($match[0][$j], '<a id=outlink href="/forward.php?link='.str_replace("&amp;", "&", $match[1][$j]).'" target=_blank>'.$match[2][$j].'</a>', $text);

	   //Заменяем mailto
	    	$text=eregi_replace("\[mailto=([^]]+)\]([^]]+)\[/mailto\]", '<a href="mailto:\\1">\\2</a>', $text);
    return $text;
};


function se_onlinenews($razdel, $modername, $newsmnt, $width, $thumbwdth,$text){

    Global $add_news, $_day, $_month, $_year, $archiv,
    $col1, $col2, $col4, $errortext, $_text,$_short_text, $_title,
    $col3, $_page, $_sub, $news, $SESSION_VARS;


// массив используемых строк
// Подгружаем библиотеку
require_once("lib/lib_images.php");

    $nn        = 0;
    $moder     = 0;
    $IMAGE_DIR = "/images/news/";
    $limit     = "LIMIT $newsmnt";

    if (!is_dir(getcwd()."/images"))
          mkdir(getcwd()."/images");

    if (!is_dir(getcwd().$IMAGE_DIR))
          mkdir(getcwd().$IMAGE_DIR);

    if ($newsmnt == 0) $limit = " ";

    if ($SESSION_VARS['GROUPUSER']>1 || isModer($modername)) $moder=1;
    else $moder=0;

// если модератор показать ссылку добавления новостей
    if ($moder == 1)
    $add_news = "<a id=\"addlink\" href='?razdel=$razdel&sub=2'>$text[1]</a>";

// заполнить массив раздела объектами (новостями)
    $rnews = se_db_query("SELECT id, date, title, short_txt, text, img FROM news ORDER BY date DESC $limit");
    while ($news = mysql_fetch_array($rnews)){
        $id = $news['id'];
        if ($moder == 1) $data[$nn][0] = "<a id=\"editlink\" href='?razdel=$razdel&sub=3&obj=$id'>$text[11]</a>";
        else $data[$nn][0] = " ";
        $data[$nn][1] = date("d.m.Y",htmlspecialchars($news['date'],ENT_QUOTES));
        $data[$nn][2] = htmlspecialchars($news['title'],ENT_QUOTES);

        if (empty($news['img'])) $data[$nn][3] = " ";
        else
        {
            $_imnames = explode(".",$news['img']);
            $_image = $_imnames[0]."_prev.".$_imnames[1];
            $data[$nn][3] = "<IMG border=0 class=objectImage src=\"".$IMAGE_DIR.$_image."\" alt=\"\">";
        }
	 $short_txt=replase_teg_edittext($news['short_txt']);
        $data[$nn][4] = $short_txt;

        // ссылка на субстраницу, если есть подробный текст для новости
        if (empty($news['text'])) $data[$nn][5] = " ";
        else $data[$nn][5] = "<br><a id=newslink href='?razdel=$razdel&sub=1&object=$id'>$text[8]</a>";
        $nn++;
    }

// показать новости
    if (isset($data))
    se_show_fields($razdel,$data);

    if (mysql_num_rows(se_db_query("SELECT * FROM news")) >= $newsmnt)
    $archiv = "<a id=\"arclink\" href='?razdel=$razdel&sub=4'>Архив</a>";

// показать субстраницу для текущей новости
    if ($_sub == 1){
        if (isset($_GET['object'])){
            $Obj = htmlspecialchars($_GET['object'],ENT_QUOTES);
            $rnews = se_db_query("SELECT id, title, short_txt, text, img FROM news WHERE id='$Obj'");
            $news = mysql_fetch_array($rnews);
            $col1 = htmlspecialchars($news['title'],ENT_QUOTES);
            $col2 = htmlspecialchars($news['short_txt'],ENT_QUOTES);
            $col3 = htmlspecialchars($news['text'],ENT_QUOTES);
            $col2=replase_teg_edittext($col2);
            $col3=replase_teg_edittext($col3);

            if (empty($news['img'])) $col4 = " ";
            else $col4 = "<IMG class=viewImage alt=\"$col1\" src=\"".$IMAGE_DIR.htmlspecialchars($news['img'],ENT_QUOTES)."\" border=0>";
        }
    }


    // добавление новости
    if ($_sub == 2){

        // сформировать дату
        $_time = explode(".",date("d.m.Y",time()));
        $_day  = $_time[0];
        $_month  = $_time[1];
        $_year  = $_time[2];

        if (isset($_POST['Save'])){
            $flag     = true;
            $file     = false;
            $filename = "";

            if (empty($_POST['day']) && $flag){
                 $flag = false;
                 $errortext = $text[5];
            }

            if (empty($_POST['month']) && $flag){
                 $flag = false;
                 $errortext = $text[4];
            }

            if (empty($_POST['year']) && $flag){
                 $flag = false;
                 $errortext = $text[6];
            }

            if ($flag && !checkdate(intval($_POST['month']), intval($_POST['day']), intval($_POST['year']))){
                 $flag = false;
                 $errortext = $text[7];
            }

            if (empty($_POST['title']) && $flag){
                 $flag = false;
                 $errortext = $text[3];
            }

            if (empty($_POST['short_text']) && $flag){
                 $flag = false;
                 $errortext = $text[2];
            }

            // если загружается картинка
            if (is_uploaded_file($_FILES['userfile']['tmp_name'])){

                   $userfile=$_FILES['userfile']['tmp_name'];
                   $userfile_size=$_FILES['userfile']['size'];
                   $user=strtolower(htmlspecialchars($_FILES['userfile']['name'], ENT_QUOTES));

                   //Проверяем, что загруженный файл - картинка
                   $sz=GetImageSize($userfile);
                   if (!(ereg("^.+\.(gif|jpg|png)$", $user) && ($sz[2]==1 || $sz[2]==2 || $sz[2]==3))) {
                        $errortext = $text[9];
                        $flag = false;
                        return;
                   }

                   //Если размер файла больше заданного
                   if ($userfile_size > 1024000){
                       $errortext = $text[10];
                       $flag = false;
                       return;
                   }

                //   $sz[0]; //Ширина
                //   $sz[1]; //Высота

                   $file = true;

            }

            // если нет какого либо обязательного параметра
            if (!$flag){
                $_day        = htmlspecialchars($_POST['day']);
                $_month      = htmlspecialchars($_POST['month']);
                $_year       = htmlspecialchars($_POST['year']);
                $_title      = htmlspecialchars($_POST['title']);
                $_text       = htmlspecialchars($_POST['text']);
                $_short_text = htmlspecialchars($_POST['short_text']);
            }
            else
            {

              $time = mktime(date("G"),date("i"),date("s"), $_POST['month'],$_POST['day'],$_POST['year']);
              $title  = $_POST['title'];
              $sh_txt = $_POST['short_text'];
              $text   = $_POST['text'];

              $resmax = se_db_query("SELECT max(id) AS obid FROM news");
              $rmax   = mysql_fetch_array($resmax);
              $maxid  = $rmax['obid']+1;

              // если загружаем файл
              if ($file){

                $uploadfile     = getcwd().$IMAGE_DIR.$maxid.".".substr($user, -3);
                $uploadfileprev = getcwd().$IMAGE_DIR.$maxid."_prev.".substr($user, -3);
                $filename       = "$maxid.".substr($user, -3);
                $fileextens     = substr($user, -3);


                if ($sz[0]>$width) {
                    $uploadfiletmp  = getcwd().$IMAGE_DIR.$maxid.".temp";
                    move_uploaded_file($userfile, $uploadfiletmp);
                    ImgCreate($uploadfileprev,$uploadfile,$uploadfiletmp,$fileextens, $width, $thumbwdth);
                    @unlink($uploadfiletmp);
                }
                else {
                    move_uploaded_file($userfile, $uploadfile);
                    ThumbCreate($uploadfileprev,$uploadfile,$fileextens,$thumbwdth);
                }


              }

              se_db_query("INSERT INTO news
                           (id, date, title, short_txt, text, img)
                           VALUES ('$maxid', '$time', '$title', '$sh_txt', '$text', '$filename')");
              Header("Location: http://".$_SERVER['HTTP_HOST']."/".$_page);
            }
        }
    } // if ($_sub == 2)

    // редактирование новости
    if ($_sub == 3){
        if (isset($_GET['obj'])){
            $nid = $_GET['obj'];
            $redit = se_db_query("SELECT date, title, short_txt, text, img FROM news WHERE id = '$nid'");
            $edit = mysql_fetch_array($redit);
            // сформировать дату
            $_time = explode(".",date("d.m.Y", $edit['date']));
            $_day       = htmlspecialchars(stripslashes($_time[0]));
            $_month     = htmlspecialchars(stripslashes($_time[1]));
            $_year      = htmlspecialchars(stripslashes($_time[2]));
            $_title      = htmlspecialchars(stripslashes($edit['title']));
            $_short_text = htmlspecialchars(stripslashes($edit['short_txt']));
            $_text       = htmlspecialchars(stripslashes($edit['text']));
            $filename    = $edit['img'];

            if (isset($_POST['Save'])){
                $flag     = true;
                $file     = false;

                if (empty($_POST['day']) && $flag){
                     $flag = false;
                     $errortext = $text[5];
                }

                if (empty($_POST['month']) && $flag){
                     $flag = false;
                     $errortext = $text[4];
                }

                if (empty($_POST['year']) && $flag){
                     $flag = false;
                     $errortext = $text[6];
                }

                if (!checkdate(intval($_POST['month']), intval($_POST['day']), intval($_POST['year'])) && flag){
                     $flag = false;
                     $errortext = $text[7];
                }

                if (empty($_POST['title']) && $flag){
                     $flag = false;
                     $errortext = $text[3];
                }

                if (empty($_POST['short_text']) && $flag){
                     $flag = false;
                     $errortext = $text[2];
                }

                // если загружается картинка
                if (@is_uploaded_file($_FILES['userfile']['tmp_name'])){

                       $userfile=$_FILES['userfile']['tmp_name'];
                       $userfile_size=$_FILES['userfile']['size'];
                       $user=strtolower(htmlspecialchars($_FILES['userfile']['name'], ENT_QUOTES));

                       //Проверяем, что загруженный файл - картинка
                       $sz=@GetImageSize($userfile);

                       if (!(ereg("^.+\.(gif|jpg|png)$", $user) && ($sz[2]==1 || $sz[2]==2 || $sz[2]==3))) {
                            $errortext = $text[9];
                            $flag = false;
                            return;
                       }

                       //Если размер файла больше заданного
                       if ($userfile_size > 1024000) {
                           $errortext = $text[10];
                           $flag = false;
                           return;
                       }

                       $sz[0]; //Ширина
                       $sz[1]; //Высота

                       $file = true; // файл загружен

                }

                // если нет какого либо обязательного параметра
                if (!$flag){
                    $_day        = htmlspecialchars($_POST['day']);
                    $_month      = htmlspecialchars($_POST['month']);
                    $_year       = htmlspecialchars($_POST['year']);
                    $_title      = htmlspecialchars($_POST['title']);
                    $_text       = htmlspecialchars($_POST['text']);
                    $_short_text = htmlspecialchars($_POST['short_text']);
                }
                else
                {

                  $time = mktime(date("G"),date("i"),date("s"), $_POST['month'],$_POST['day'],$_POST['year']);
                  $title  = $_POST['title'];
                  $sh_txt = $_POST['short_text'];
                  $text   = $_POST['text'];

                  // если загружаем файл
                  if ($file)
                  {

                    $uploadfile     = getcwd().$IMAGE_DIR.$nid.".".substr($user, -3);
                    $uploadfileprev = getcwd().$IMAGE_DIR.$nid."_prev.".substr($user, -3);
                    //if (file_exists($uploadfileprev))
                    //@unlink($uploadfileprev);
                    //if (file_exists($uploadfile))
                    //@unlink($uploadfile);

                    $filename       = "$nid.".substr($user, -3);
                    $fileextens     = substr($user, -3);

                    if ($sz[0]>$width) {
                        $uploadfiletmp  = getcwd().$IMAGE_DIR.$nid.".temp";
                        move_uploaded_file($userfile, $uploadfiletmp);
                        ImgCreate($uploadfileprev,$uploadfile,$uploadfiletmp,$fileextens, $width, $thumbwdth);
                        @unlink($uploadfiletmp);
                    }
                    else {
                        move_uploaded_file($userfile, $uploadfile);
                        ThumbCreate($uploadfileprev,$uploadfile,$fileextens,$thumbwdth);
                    }
                  }

                  se_db_query("UPDATE news
                               SET   `date`      = '$time',
                                     `title`     = '$title',
                                     `short_txt` = '$sh_txt',
                                     `text`      = '$text',
                                     `img`       = '$filename'
                               WHERE id = '$nid'");
                  Header("Location: http://".$_SERVER['HTTP_HOST']."/".$_page);
                }
            } //if (isset($_POST['Save']))

            if (isset($_POST['Delete'])){
                if (!empty($filename)){
                    $temp = explode(".",$filename);
                    $delprevimg = $temp[0]."_prev.".$temp[1];
                    $delprevimg = getcwd().$IMAGE_DIR.$delprevimg;
                    $filename   = getcwd().$IMAGE_DIR.$filename;
                    if (file_exists($delprevimg)) @unlink($delprevimg);
                    if (file_exists($filename)) @unlink($filename);
                }
                se_db_query("DELETE FROM news WHERE id = '$nid'");
                Header("Location: http://".$_SERVER['HTTP_HOST']."/".$_page);
            }
        }
    }

    if ($_sub == 4){
        $col1 = " ";
        $rarchnews = se_db_query("SELECT id, date, title FROM news ORDER BY date DESC");
        while ($archnews  = mysql_fetch_array($rarchnews)){
            $id     = $archnews['id'];
            $time   = "<div>".date("d.m.Y",htmlspecialchars($archnews['date'],ENT_QUOTES))."</div>";
            $col1  .= "<H4 class=objectTitle>".$time."<a id=links href='?razdel=$razdel&sub=1&object=$id'>".htmlspecialchars($archnews['title'],ENT_QUOTES)."</a></H4>";
        }
    }
}

function isModer($name){
global $SESSION_VARS;
    if (($SESSION_VARS['AUTH_USER'] == $name) && ($SESSION_VARS['AUTH_USER']!="")){
        se_db_query("UPDATE author SET a_group = 2 WHERE a_login = '$name'");
        return true;
    }
    else
        return false;
}

?>