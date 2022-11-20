<?php

////////////////////////////////////////////////////////////////////////////////

$smilesURL="/skin/chat/";

function intToStrUser($n) {

$n= $n % 100;
if ($n>15) $n=$n %10;
switch ($n) {
case 0:
case 5:
case 6:
case 7:
case 8:
case 9:
case 10:
case 11:
case 12:
case 13:
case 14:
  $str="пользователей";
  break;
case 1:
  $str="пользователь";
  break;
case 2:
case 3:
case 4:
  $str="пользователя";
  break;
}
return $str;
}

////////////////////////////////////////////////////////////////////////////////

function se_getchat()
{
global $chatnick,$chatinfotext,$_page,$_razdel,
$chatusers,$chatrefresh,$chatinput,$chatexit, $chatmsg, $chatmyimg,
$_color,$_GoToChatExit,$chatsid,$_sid,$chatexit,$_regdate,$_lastdate,
$frmsmiles, $smilesURL, $style, $SESSION_VARS, $css,
$_nick, $_realname, $_sex, $_birth_date, $_town, $_email, $_icq, $_url, $_info;

$uid = $SESSION_VARS['IDUSER'];

if (empty($uid)) $uid=0;

$sid=session_id();
$time=time();

        mysql_query ("set character_set_client='cp1251'");
	mysql_query ("set character_set_results='cp1251'");
	mysql_query ("set collation_connection='cp1251_general_ci'");
			
if ($uid!=0) { //Если зарегистрированный пользователь
  //Ищем пользователя в базе чата

  $ru = mysql_query("
    SELECT id, id_author, nick
    FROM chat_users
    WHERE id_author=$uid;"
  );

  if  (mysql_num_rows($ru)==0) {  //Если пользователя нет, добавляем
    //Автовычисление ника
    $nick=$SESSION_VARS['AUTH_USER'];
    //Смотрим, есть ли такой ник
    $flag=false;
    $count=1;
    while (!$flag) {
      $ru = mysql_query("
        SELECT nick
        FROM chat_users
        WHERE nick='$nick'"
      );
      if (mysql_num_rows($ru)!=0) $nick=$SESSION_VARS['AUTH_USER'].$count; else $flag=true;
      $count++;
    }

    mysql_query("
    INSERT INTO chat_users (id_author, nick, registered, last)
    VALUES ('$uid', '$nick', '$time', '$time' )"
    );
  }
  else {//Если есть
    $user=mysql_fetch_array($ru);
    $uid=$user['id'];
    $nick=$user['nick'];
    
    //Обновляем время последнего захода
    mysql_query ("UPDATE `chat_users` SET
        `last` = '$time'
        WHERE `id`=$uid");
  }
}

if (@$_GET['sub']=="1")
{
 $chatsid=$sid;
 $ip=$_SERVER['REMOTE_ADDR'];
 $color=htmlspecialchars($_color, ENT_QUOTES);
 if (!mysql_connect(HostName, DBUserName, DBPassword)) exit("Невозможно соединиться с базой данных"); else mysql_select_db(DBName);
mysql_query ("set character_set_client='cp1251'"); 
mysql_query ("set character_set_results='cp1251'"); 
mysql_query ("set collation_connection='cp1251_general_ci'");

 $chatexit="?GoToChatExit=1&sid=$sid";

//Проверяем, есть ли такой пользователь

//Удаляем все другие сессии этого пользователя (если они есть), кроме текущей

  mysql_query(
  "DELETE FROM chat_session
  WHERE id_user=$uid AND sid<>'$sid';");

//Смотрим, нет ли пользователя в таблице модераториалов

  $rm=mysql_query(
  "SELECT *
  FROM chat_moderatorial
  WHERE id_user=$uid"
  );

if (mysql_num_rows($rm)!=0) $flagMod=true; else $flagMod=false;

// Если нет такой сессии, добавляем пользователя, иначе он нажал "обновить"
  $ru=mysql_query(
  "SELECT sid, id_user
  FROM chat_session
  WHERE id_user=$uid"
  );

  if (mysql_num_rows($ru)==0 && !$flagMod && $uid!=0) {

 //Вычисляем id последнего сообщения

 $rm=mysql_query(
  "SELECT MAX(id) AS id
  FROM chat_msg;"
 );

 $msg=mysql_fetch_array($rm);
 $max=$msg['id'];


 //Добавляем сессию

 mysql_query("
  INSERT INTO `chat_session`(sid, id_user, ip, time, last_id_message, color)
  VALUES('$sid', '$uid', '$ip', '$time-10', '$max', '$color');"
 );

//Пишем в чат, что пришел новый пользователь
 
 $msg="К нам приходит $nick!";
  mysql_query("
  INSERT INTO `chat_msg`(id_user_from, id_user_to, time, ip, text)
  VALUES('0', '-1', '$time', '127.0.0.1', '$msg');"
  );


  //Пишем в файл новое время обновления юзеров
  $f=fopen("modules/chat/users.dat", "w");
  fwrite($f, $time);
  flush();
  fclose($f);

}
  $chatusers="/modules/chat/users.php?sid=$sid";
  $chatinput="<IFRAME FRAMEBORDER=NO SRC='/modules/chat/input.php?sid=$sid' SCROLLING=NO name='frm_input' border='0' width='0' height='0' id='frm_input'></IFRAME>";
  $chatrefresh="<IFRAME FRAMEBORDER=NO SRC='/modules/chat/refresh.php?sid=$sid&razdel=$_razdel' SCROLLING=NO name='frm_refresh' border='0' width='0' height='0'></IFRAME>";
  $chatmsg="";

  //Нужно перечитать для него пользователей
  //Пишем в файл новое время обновления юзеров
  $f=fopen("modules/chat/users.dat", "w");
  fwrite($f, $time);
  flush();
  fclose($f);

$chatrefresh.="
<script>
function refresh() {
var now = new Date();
var time = now.getTime();
frm_refresh.location.href=\"/modules/chat/refresh.php?sid=$sid&razdel=$_razdel&\"+time
}
setInterval(\"refresh()\", 5000)
</script>
";

  $frmsmiles="";
  $i=1;

  while(file_exists("skin/chat/smile$i.gif")) {
    $frmsmiles.="<div class=itemSmil><a href=\"javascript:addSmile('[smile$i]')\";>
    <img border=\"0\" src=\"".$smilesURL."smile$i.gif\"></a></div>
    ";
    $i++;
  }

// Формируем список смайликов
} else
{

  if (isset($_GoToChatExit)) {  //////////Если нажат "выход"

   //Ищем пользователя в сессиях

   $rs=mysql_query(
   "SELECT id_user, time, last_id_message, nick
   FROM chat_session, chat_users
   WHERE sid='$_sid' AND chat_users.id=id_user;"
   );

   if (mysql_num_rows($rs)!=0) { //Если есть такая сессия
    $user=mysql_fetch_array($rs);
    $tu=$user['time'];
    $last_id_msg=$user['last_id_message'];
// !!    $nick=$user['a_nick'];

     //Удаляем пользователя из сессий
     mysql_query(
    "DELETE FROM chat_session
     WHERE sid='$_sid';");

     //Пишем это в чат

    $msg="$nick уходит из чата";
    mysql_query("
    INSERT INTO `chat_msg`(id_user_from, id_user_to, time, ip, text)
    VALUES('0', '-1', '$time', '127.0.0.1', '$msg');"
    );

     //Пишем в файл новое время обновления юзеров
    $f=fopen("modules/chat/users.dat", "w");
    fwrite($f, $time);
    flush();
    fclose($f);
  }
 };

  /////////////////////////////////////////////
  
  //Готовим список на удаление пользователей
  $time=time();
  $timemin=time()-90;
  $ru=mysql_query(
  "SELECT chat_users.nick
  FROM chat_users, chat_session
  WHERE chat_users.id=chat_session.id_user AND time<$timemin;"
  );

  //Удаляем их из таблицы сессий
  mysql_query(
  "DELETE FROM chat_session
  WHERE time<$timemin;");

  //Если такие есть
  if (mysql_num_rows($ru)!=0) {

    //Пишем это в чат
    while ($del=mysql_fetch_array($ru)) {
      $user=$del['nick'];
      $msg="$user уходит (таймаут)";
      mysql_query("
        INSERT INTO `chat_msg`(id_user_from, id_user_to, time, ip, text)
       VALUES('0', '-1', '$time', '127.0.0.1', '$msg');"
      );
    }

    //Пишем в файл новое время обновления юзеров
    $f=fopen("modules/chat/users.dat", "w");
    fwrite($f, $time);
    flush();
    fclose($f);
  }

  //Удаляем из таблицы модераториалов устаревшие записи
  mysql_query("
    DELETE FROM chat_moderatorial
    WHERE time+period<=$time;"
  );

  if ($uid!="0") $chatnick="Вы авторизованы. Ваш ник: ".$nick;
  else {
    $chatnick="Вы не авторизованы.";
    $style="style='display:none';";
    //return;
  }


  //Смотрим, нет ли пользователя в таблице модераториалов
  
  $ru=mysql_query(
  "SELECT *
  FROM chat_moderatorial
  WHERE id_user=$uid;"
  );

  if (mysql_num_rows($ru)!=0) {
    $moder=mysql_fetch_array($ru);
    $chatnick="Вы отключены от чата модератором до ".date("H:i (мск) d.m.Y года", $moder['time']+$moder['period'])."!";
    $style="style='display:none';";
    return;
  }


  $ru=mysql_query(
  "SELECT nick, chat_session.sid
  FROM chat_users, chat_session
  WHERE chat_users.id=chat_session.id_user
  ORDER BY nick;"
  );

  $chatusers="";
  if (mysql_num_rows($ru)==0) $chatusers= "В чате никого нет...";
  else {
    $chatusers.="В чате ".mysql_num_rows($ru)." ".intToStrUser(mysql_num_rows($ru)).":<br>";
    while ($user=mysql_fetch_array($ru)) $users[]=$user['nick'];
    $users=join(", ", $users);
    $chatusers.= $users;
  }

   //Выводим последние сообщения

   $rm=mysql_query(
   "SELECT chat_users.id, chat_users.nick, chat_msg.id_user_to, chat_msg.text, chat_msg.time, chat_msg.id
   FROM chat_users, chat_msg
   WHERE chat_users.id=chat_msg.id_user_from AND chat_msg.id_user_to=-1
   ORDER BY time DESC
   LIMIT 10;"
   );
   $out="";
   while ($msg=mysql_fetch_array($rm)) {
         $user_from=$msg['nick'];
         $id_user_to=$msg['id_user_to'];
         $timemsg=$msg['time'];
         $text=stripslashes(htmlspecialchars($msg['text'], ENT_QUOTES));
         if ($id_user_to==-1) {
         $str=date("H:i:s", $timemsg).", $user_from: $text";
         $out=$str."<br>".$out;
         }
   }
   $out=ereg_replace("\[[[:alnum:]]+\]", "[smile]", $out);
   $chatinfotext=$out;

}; // else not sub
}

function se_chat_moderatorial() {
global $tablechat, $tablemod, $_act, $_id, $_moder, $_period, $_value, $SESSION_VARS,
       $HTTP_HOST, $_page;

//!!if ($SESSION_VARS['AUTH_USER']!="") $id_moder=$SESSION_VARS['IDUSER']; else $id_moder="-1";

$uid = $SESSION_VARS['IDUSER'];
$time=time();

$ru = mysql_query("
  SELECT id, id_author, nick
  FROM chat_users
  WHERE id_author=$uid;"
);

$user=mysql_fetch_array($ru);
$id_moder=$user['id'];
$nick=$user['nick'];

if (isset($_act)) $act=htmlspecialchars($_act, ENT_QUOTES); else $act="";
if (isset($_id)) $id_user=htmlspecialchars($_id, ENT_QUOTES); else $id_user="";
if (isset($_moder)) $moder=htmlspecialchars($_moder, ENT_QUOTES); else $moder="";
if (isset($_moder)) $moder=htmlspecialchars($_moder, ENT_QUOTES); else $moder="";
if (isset($_period)) $period=htmlspecialchars($_period, ENT_QUOTES); else $period="";
if (isset($_value)) $value=htmlspecialchars($_value, ENT_QUOTES); else $value="";

///////////////////////////////////////////////////
//Если нажали "предупреждение"

//ищем пользователя в сессиях
if ($act=="notice") {
  $ru=mysql_query(
  "SELECT id_user, nick
  FROM chat_session, chat_users
  WHERE id_user=id AND id_user=$id_user"
  );
  
  if (mysql_num_rows($ru)!=0) {
    $user=mysql_fetch_array($ru);
    $msg=$user['nick']." получает предупреждение от модератора $nick!";
    mysql_query("
      INSERT INTO `chat_msg`(id_user_from, id_user_to, time, ip, text)
      VALUES('0', '-1', '$time', '127.0.0.1', '$msg');"
    );
  }
  Header("Location: http://".$_SERVER['HTTP_HOST']."/$_page/?".time());
  exit();
}
///////////////////////////////////////////////////
//Если нажали "отключение"

if ($act=="moderatorial") {
  $period=$period*$value;

  $ru=mysql_query(
  "SELECT chat_session.id_user, nick
  FROM chat_session, chat_users
  WHERE chat_session.id_user=id AND chat_session.id_user=$id_user;"
  );

  if (mysql_num_rows($ru)!=0) {
    $user=mysql_fetch_array($ru);
    $msg="Модератор $nick отключает пользователя ".$user['nick']." до ".date("H:i d.m.Y года", $time+$period)."!";
    mysql_query("
      INSERT INTO `chat_msg`(id_user_from, id_user_to, time, ip, text)
      VALUES('0', '-1', '$time', '127.0.0.1', '$msg');"
    );

    //Пишем модераториал в таблицу
    mysql_query("
      INSERT INTO `chat_moderatorial`(id_user, time, period)
      VALUES('$id_user', $time, $period);"
    );

    //Удаляем сессию пользователя
    mysql_query("
     DELETE FROM chat_session
     WHERE id_user=$id_user;"
    );

    //Пишем в файл новое время обновления юзеров
    $f=fopen("modules/chat/users.dat", "w");
    fwrite($f, $time);
    flush();
    fclose($f);
  }

  Header("Location: http://".$_SERVER['HTTP_HOST']."/$_page/?".time());
  exit();

}

///////////////////////////////////////////////////
//Если нажали "отмена модераториала"
if ($act=="cancel") {
  //Удаляем модераториал
    mysql_query("
      DELETE FROM chat_moderatorial
      WHERE id_user=$id_user;"
    );
}

//Смотрим, кто есть в чате

$ru=mysql_query(
  "SELECT chat_users.id, nick, a_login, ip
  FROM chat_session, author, chat_users
  WHERE chat_users.id_author=author.id AND chat_users.id=chat_session.id_user"
  );

if (mysql_num_rows($ru)!=0) {

$tablechat="<TABLE class=tableTable>
<TR class=tableRow id=tableHeader><TD class='title'>Ник</TD><TD class='title'>Логин</TD><TD class='title'>IP</TD><TD class='title'>Предупреждение</TD><TD class='title'>Отключение</TD></TR>";

while ($user=mysql_fetch_array($ru)) {
  $id=$user['id'];
  $tablechat.="<TR class=tableRow><TD>".$user['nick']."</TD><TD>".$user['a_login']."</TD><TD>".$user['ip']."</TD>
  <TD><A href='?act=notice&id=$id&moder=$id_moder'>Предупреждение</A></TD>
  <TD><FORM action='' method='get'>На <input type='text' name='period' value='10' maxlength='3' size='2'>
  <select name='value'>
  <option value='60'>минут</option>
  <option value='3600'>часов</option>
  <option value='86400'>дней</option></select>
  <input type='submit' value='Отключить'>
  <input type='hidden' name='act' value='moderatorial'>
  <input type='hidden' name='id' value='$id'>
  <input type='hidden' name='moder' value='$id_moder'>
  </FORM></TD></TR>";
}
$tablechat.="</TABLE>";
}
else
$tablechat.="В чате никого нет";


//Смотрим, кто отключен

$ru=mysql_query(
  "SELECT chat_users.id, nick, a_login, time, period
  FROM chat_moderatorial, chat_users, author
  WHERE chat_moderatorial.id_user=chat_users.id AND chat_users.id_author=author.id"
  );

if (mysql_num_rows($ru)!=0) {

$tablemod="<TABLE class=tableTable>
<TR class=tableRow id=tableHeader><TD class='title'>Ник</TD><TD class='title'>Логин</TD><TD class='title'>Отключен до</TD><TD class='title'>Отмена</TD></TR>";

while ($user=mysql_fetch_array($ru)) {
  $tablemod.="<TR><TD>".$user['nick']."</TD>
  <TD>".$user['a_login']."</TD>
  <TD>".date("d.m.Y, H:i", $user['time']+$user['period'])."</TD>
  <TD><A href='?act=cancel&id=".$user['id']."'>Отмена</A></TD></TR>";
}
$tablemod.="</TABLE>";
}
else
$tablemod.="Нет";
}

////////////////////////////////////////////////////////////////////////////////////////////
function se_chat_who() {
Global $chatwho;

  $ru=mysql_query(
  "SELECT nick, chat_session.sid
  FROM chat_users, chat_session
  WHERE id=chat_session.id_user
  ORDER BY nick;"
  );

  $chatusers="";
  if (mysql_num_rows($ru)==0) $chatusers= "В чате никого нет...";
  else {
    $chatusers.="В чате ".mysql_num_rows($ru)." ".intToStrUser(mysql_num_rows($ru)).":<br>";
    while ($user=mysql_fetch_array($ru)) $users[]=$user['nick'];
    $users=join(", ", $users);
    $chatusers.= $users;
  }

  $chatwho=$chatusers;
}


////////////////////////////////////////////////////////////////////////////////
function se_chat_my() // Смена личных данных
{
global $SESSION_VARS,$_realname,$_nick,$_birth_day,$_birth_month,$_birth_year, $_id, $page, $chatmyimg,
       $_email,$_icq,$_url,$_info,$chat_my_msg, $_sex, $_town, $_page, $_uploadimg, $_deleteimg, $sid;

$uid = $SESSION_VARS['IDUSER'];

if (empty($uid)) $uid=0;

$sid=session_id();
$time=time();

if (isset($_uploadimg) || isset($_deleteimg)) {
			require_once "chat/chatmyimg.php";
}



if ($uid!=0) { //Если зарегистрированный пользователь
  //Ищем пользователя в базе чата

  $rusers = mysql_query("
    SELECT *
    FROM chat_users
    WHERE id_author=$uid;"
  );

  if  (mysql_num_rows($rusers)==0) {  //Если пользователя нет, добавляем
    $nick = "NoName"; //=$struser['a_name'];
    //Здесь будет автовычисление ника

    mysql_query("
    INSERT INTO chat_users (id_author, nick, registered, last)
    VALUES ('$uid', '$nick', '$time', '$time' )"
    );
  }
  else {//Если есть
    $user=mysql_fetch_array($rusers);
    $uid=$user['id'];
    $nick=$user['nick'];
  }
}
else
  return;


$_id=$uid;
if ($user['img']=="")
  $chatmyimg="";
else {
  $sz=GetImageSize("modules/chat/images/".$user['img']);
  $chatmyimg="<img src=/modules/chat/images/".$user['img']."?".time().">
  <div>Размер: ".$sz[0]."x".$sz[1]."px</div><a href='?deleteimg&id=$_id&sid=$sid'>Удалить</a>";
}

if (isset($_POST['GoToPers']))
{
 $_realname=htmlspecialchars($_realname, ENT_QUOTES);
 $_email=htmlspecialchars($_email, ENT_QUOTES);
 $_icq=htmlspecialchars($_icq, ENT_QUOTES);
 $_url=htmlspecialchars($_url, ENT_QUOTES);
 $_info=htmlspecialchars($_info, ENT_QUOTES);
 $_town=htmlspecialchars($_town, ENT_QUOTES);
 $_birth_year=htmlspecialchars($_birth_year, ENT_QUOTES);
 $_birth_month=htmlspecialchars($_birth_month, ENT_QUOTES);
 $_birth_day=htmlspecialchars($_birth_day, ENT_QUOTES);
 $_nick=htmlspecialchars($_nick, ENT_QUOTES);
 $_sex=htmlspecialchars($_sex, ENT_QUOTES);

 while (strlen($_birth_year)==2)$_birth_year="19".$_birth_year;
 while (strlen($_birth_month)==1)$_birth_month="0".$_birth_month;
 while (strlen($_birth_day)==1)$_birth_day="0".$_birth_day;
 $birth_date=$_birth_year."-".$_birth_month."-".$_birth_day;

 //Проверяем, корректна ли дата
 if (!(checkdate($_birth_month, $_birth_day, $_birth_year) ||
       ($_birth_month=="00" && $_birth_day=="00" && $_birth_year=="0000"))) {
   $chat_my_msg="Некорректная дата!";
   return;
 }


//Проверяем ник на мат и на наличие пробела
 if (isMat($_nick) || (strstr ($_nick, " ")!==false)) {
   $chat_my_msg="Некорректный ник! Выберите другой ник.";
   return;
 }

//Проверяем город на мат
 if (isMat($_town)) {
   $chat_my_msg="Недопустимые символы или слова в поле &quot;Город&quot;.";
   return;
 }

//Проверяем e-mail на мат
 if (isMat($_email)) {
   $chat_my_msg="Недопустимые символы или слова в поле &quot;E-mail&quot;.";
   return;
 }

//Проверяем ICQ на мат
 if (isMat($_icq)) {
   $chat_my_msg="Недопустимые символы или слова в поле &quot;ICQ UIN&quot;.";
   return;
 }

//Проверяем URL на мат
 if (isMat($_url)) {
   $chat_my_msg="Недопустимые символы или слова в поле &quot;Личный сайт&quot;.";
   return;
 }

//Проверяем Инфу на мат
 if (isMat($_info)) {
   $chat_my_msg="Недопустимые символы или слова в поле &quot;Дополнительная информация&quot;.";
   return;
 }

 //Ищем, есть ли такой ник в базе

 $rn = mysql_query(
 "SELECT nick
 FROM chat_users
 WHERE nick='$_nick' AND id<>$uid");
 if (mysql_num_rows($rn)!=0) {
   $chat_my_msg="Ник &quot;$_nick&quot; уже занят! Выберите другой ник.";
   return;
 }
 $result=mysql_query ("UPDATE `chat_users` SET
        `realname` = '$_realname',
        `email` = '$_email',
        `icq` = '$_icq',
        `url` = '$_url',
        `info` = '$_info',
        `town` = '$_town',
        `nick` = '$_nick',
        `sex` = '$_sex',
        `birth_date` = '$birth_date'
         WHERE `id`='$uid'");
 if (!isset($result)){
    $chat_my_msg="Данные изменить не удалось"; return;
 };

  //Пишем в файл новое время обновления юзеров

  $f=fopen("modules/chat/users.dat", "w");
  fwrite($f, $time);
  flush();
  fclose($f);

  Header("Location: http://".$_SERVER['HTTP_HOST']."/$_page?".time());
  exit();
}

  $_realname=$user['realname'];
  $_email=$user['email'];
  $_icq=$user['icq'];
  $_url=$user['url'];
  $_info=$user['info'];
  $_nick=$user['nick'];
  $_town=$user['town'];
  $_sex=$user['sex'];
  list($_birth_year,$_birth_month,$_birth_day)=explode("-",$user['birth_date'],3);
 		
};

function se_chat_my_end($razdel) { // Смена личных данных
global $raz_end, $_sex;
  $raz_end[$razdel]=str_replace("<option value=\"".$_sex, "<option selected value=\"".$_sex, $raz_end[$razdel]);
}
////////////////////////////////////////////////////////////////////////////////

function se_chat_user() {
global $chatnick,$chatinfotext,$_page,
$chatusers,$chatrefresh,$chatinput,$chatexit, $chatmsg, $chatmyimg,
$_color,$_GoToChatExit,$chatsid,$_sid,$chatexit,
$frmsmiles, $smilesURL, $style, $SESSION_VARS, $css, $_id,$_regdate,$_lastdate,
$_nick, $_realname, $_sex, $_birth_date, $_town, $_email, $_icq, $_url, $_info;

$id=htmlspecialchars($_id, ENT_QUOTES);

$ru=mysql_query(
  "SELECT *
  FROM chat_users
  WHERE id=$id"
);

$user=mysql_fetch_array($ru);
$_realname=$user['realname'];
$_email=$user['email'];
$_icq=$user['icq'];
$_url=$user['url'];
$_info=$user['info'];
$_nick=$user['nick'];
$_town=$user['town'];
$_sex=$user['sex'];
$_regdate=date("d.m.Y, H:i", $user['registered']);
$_lastdate=date("d.m.Y, H:i", $user['last']);

switch ($_sex) {
  case "M":
    $_sex="М";
    break;
  case "F":
    $_sex="Ж";
    break;
  case "N":
    $_sex="Не указан";
    break;
}

list($_birth_year,$_birth_month,$_birth_day)=explode("-",$user['birth_date'],3);
$_birth_date="$_birth_day.$_birth_month.$_birth_year";

if ($user['img']=="")
  $chatmyimg="";
else {
  $sz=GetImageSize("modules/chat/images/".$user['img']);
  $sz[0]; //Ширина
  $sz[1]; //Высота
  $chatmyimg="<img src=/modules/chat/images/".$user['img']."?".time().">";
}
return;
}
?>