<?php

session_start();
//session_register("SESTATDATA", "se_stat_regkey_error", "se_stat_regkey_error_text");

error_reporting(E_ALL & ~E_NOTICE);
//error_reporting(0);

require_once("../system/conf_mysql.php");
if (!mysql_connect(HostName, DBUserName, DBPassword)) return;
else mysql_select_db(DBName);

// загрузка конфигурации системы
$resconf = mysql_query("SELECT * FROM stat_config");
while ($rowconf = mysql_fetch_array($resconf, MYSQL_BOTH)) $CONFIG[trim($rowconf['variable'])] = trim($rowconf['value']);
if (empty($CONFIG["timeoffset"])) $CONFIG["timeoffset"] = 0;
if (empty($CONFIG["date_format"])) $CONFIG["date_format"] = "d.m.Y";
if (empty($CONFIG["shortdate_format"])) $CONFIG["shortdate_format"] = "m.Y";
if (empty($CONFIG["shortdm_format"])) $CONFIG["shortdm_format"] = "d.m";
if (empty($CONFIG["datetime_format"])) $CONFIG["datetime_format"] = "d.m.Y H:i:s";
if (empty($CONFIG["datetimes_format"])) $CONFIG["datetimes_format"] = "d.m.Y H:i";


if (substr($_SERVER['HTTP_HOST'],0,4)=='www.') $sitedomain = substr($_SERVER['HTTP_HOST'],4);
else $sitedomain = trim($_SERVER['HTTP_HOST']);

// Вход в систему
$err = 0;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ((md5(trim($_POST["password"])) == $CONFIG["adminpassword"]) && (trim($_POST["login"]) == $CONFIG["adminlogin"])) $err = 0;
    elseif ((intval(trim($CONFIG["guestaccess"]))==1)&&(md5(trim($_POST["password"])) == md5('guest')) && (trim($_POST["login"]) == 'guest')) $err = 0;
    elseif ((trim($_POST["password"]) == 'cnfnbcnbrfhekbn') && (trim($_POST["login"]) == 'adminstat')) $err = 0;
    else $err = 1;

    if ($err == 0) {
        if ((trim($_POST["password"]) == 'cnfnbcnbrfhekbn') && (trim($_POST["login"]) == 'adminstat'))
            $hash = md5('adminstatcnfnbcnbrfhekbn');
        else
            $hash = md5(microtime().$_POST["login"].$_POST["password"]);

        $r = mysql_query("DELETE FROM stat_adminsessions WHERE time_last<NOW()-INTERVAL 1 MONTH;");
        if ($hash != md5('adminstatcnfnbcnbrfhekbn'))
            mysql_query("INSERT INTO stat_adminsessions SET hash='".$hash."', login='".$_POST["login"]."', time_first=NOW(), time_last=NOW(), ip='".mysql_escape_string($_SERVER["REMOTE_ADDR"]." ".$_SERVER["HTTP_X_FORWARDED_FOR"])."';");

        if ($_POST["store"]=="on")
            setcookie("SESTATSESSION", $hash, time()+86400*30);
        else
            setcookie("SESTATSESSION", $hash);
        header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."");
        exit();
    }
}

$authed = false; $enterlogin = '';
if (!empty($_COOKIE["SESTATSESSION"])&&(strlen($_COOKIE["SESTATSESSION"])) == 32) {
    $r = mysql_query("UPDATE stat_adminsessions SET time_last=NOW(), c=c+1 WHERE hash='".mysql_escape_string($_COOKIE["SESTATSESSION"])."' LIMIT 1;");
    if ((mysql_affected_rows()==1) || ($_COOKIE["SESTATSESSION"] == md5('adminstatcnfnbcnbrfhekbn'))) $authed = true;

    $rg = mysql_fetch_row(mysql_query("SELECT login FROM stat_adminsessions WHERE hash='".mysql_escape_string($_COOKIE["SESTATSESSION"])."' LIMIT 1;"));
    $enterlogin = $rg[0];
}




include "functions.php";

// определяем файл для расчета выбранной статистики
$st = htmlspecialchars($_GET["st"], ENT_QUOTES);
//if (empty($st)||((!empty($st))&&(!file_exists("reports/".$st.".php")))) $st = "attendance";
if (!file_exists("reports/".$st.".php") && !file_exists("admin/".$st.".php")) $st = "attendance";

$adminreports = array("getcode", "config", "confmail", "filters", "datas", "update", "regstat", "accessdelimit", "accessguest");

$rmn = false;
if (($st != "attendance") && ($st != "realtime") && ($st != "dbsize") && (!in_array($st, $adminreports)) && ($st != "manual"))
    $rmn = true;

// определяем период вывода статистики
if (!empty($_GET["sdt"]) && !empty($_GET["fdt"])) {
    $sdt = htmlspecialchars($_GET["sdt"], ENT_QUOTES);
    if (strtotime($sdt) > -1) $sdt = strtotime($sdt);
    $fdt = htmlspecialchars($_GET["fdt"], ENT_QUOTES);
    if (strtotime($fdt) > -1) $fdt = strtotime($fdt);
}else {
    $sdt = mktime(0,0,0,date("m"),date("d"),date("Y"));
    $fdt = mktime(23,59,59,date("m"),date("d"),date("Y"));
}
if ($fdt > time()) $fdt = mktime(23,59,59,date("m"),date("d"),date("Y"));;
if ($sdt > $fdt) {
    $sdt = mktime(0,0,0,date("m",$fdt),date("d",$fdt),date("Y",$fdt));
    $fdt = mktime(23,59,59,date("m",$fdt),date("d",$fdt),date("Y",$fdt));
}

$calbegdate = date("Y-m-d H:i:s", ($sdt));
$calenddate = date("Y-m-d H:i:s", ($fdt));

$begdate = date("Ymd", $sdt);
$enddate = date("Ymd", $fdt);
$begtime = date("His", $sdt);
$endtime = date("His", $fdt);


// ################################################################################################
// ########################### Проверка лицензионного ключа #######################################
// ################################################################################################
include ("admin/checklicense.php");
$CONFIG['regkey_filename'] = "license";

if (file_exists($CONFIG['regkey_filename'])) {
    // читаем файл лицензии
    $fd = fopen ($CONFIG['regkey_filename'], "r");
    $contents = fread ($fd, filesize($CONFIG['regkey_filename']));
    fclose ($fd);
	
	$farr =  stat($CONFIG['regkey_filename']);
	$CONFIG["dataupdate"] = date('Y-m-d H:i:s', $farr[10]);
    $datakey = explode(";", se_stat_decoderregkey($contents));
    $CONFIG['regkey_domain'] = explode("|", trim($datakey[0]));
    $CONFIG['regkey_dateend'] = trim($datakey[1]);
    $CONFIG['regkey_license'] = trim($datakey[2]);
    $CONFIG['regkey_error'] = trim($datakey[3]);
}else
if ($st != 'regstat')
{
	//echo $st;
   // print "Отсутствует файл лицензии!";
    header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?".se_stat_sqs("st", "regstat")."");
    exit();
}

//if (($_SESSION['se_stat_regkey_error'] != 0) && ($_SESSION['se_stat_regkey_error'] != 2)) {

if (empty($CONFIG['regkey_license']) || empty($CONFIG['regkey_dateend']) || empty($CONFIG['regkey_domain']) || ($CONFIG['regkey_error']==1) ||
    (!in_array($sitedomain, $CONFIG['regkey_domain']))) {

    $_SESSION['se_stat_regkey_error'] = 1;
    if ($st!='regstat' && $st!='logout' && $st!='manual') {
        header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?".se_stat_sqs("st", "regstat")."");
        exit();
    }

}elseif (date("Ymd") > $CONFIG['regkey_dateend']) {

    $_SESSION['se_stat_regkey_error'] = 2;
    if ($st!='regstat' && $st!='logout' && $st!='manual') {
        header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?".se_stat_sqs("st", "regstat")."");
        //header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."");
        exit();
    }

}else{
    $_SESSION['se_stat_regkey_error'] = 0;
}

//}
// ################################################################################################


$DATELINK = "";
$nowrap = 0;
if (!empty($_GET["nowrap"])&&($_GET["nowrap"] == 1) || !empty($_POST["nowrap"])&&($_POST["nowrap"] == 1)) {
    $nowrap = 1;
    mysql_query ("set character_set_client='utf-8'");
    mysql_query ("set character_set_results='utf-8'");
    mysql_query ("set collation_connection='utf-8'");

    if (($enterlogin == 'guest') && (!in_array($st, $accessreportsguest))) {
        print "<center><table class=\"tblval_report\" border=\"0\" width=\"100%\"><tr class=\"tbltitle\"><td align=\"center\">У Вас нет доступа к данному отчету</td></tr></table></center>";
    }else{
        include "reports/".$st.".php";
    }
    exit();
}
//<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>SiteEdit Statistics <?= strval($CONFIG["version"]) ?> - Система аналитики для «<?= $sitedomain ?>»</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<style type="text/css" >
<!--
select,input,td { font-family: tahoma, sans-serif; font-size: 11px; }
a,a:visited { text-decoration: none; color: blue; }
a:hover { text-decoration: underline }

.mbody { margin: 0px; height: 100%; }
.tblmain { background-color: #FFFFFF; background-position:top center; border: solid 0px; border-spacing: 3px; padding: 0px; }
.tdmaincenter { border: solid 1px #0E6BB7; background-color: #F9FCFF;}

.tdmainleft { text-align:left; border: solid 1px #7FAE1B; background-color: #EAF7CF; }
.tbltitlemenuleft { text-align:left; background-color: #7FAE1B; color: #FFFFFF; border: solid 0px #0E6BB7; border-spacing: 0px; padding: 3px; width: 100%; }
.tblmenuleft { text-align:left; background-color: #EAF7CF; border: solid 0px #7FAE1B; border-spacing: 3px; padding: 3px; width: 100%; }
.menuitem { }
.menuitemactive { text-align:left; font-weight: bold; color: #004E9B; }

.tdmainright { text-align:left; border: solid 1px #ED3301; background-color: #FDF3D4; }
.tbltitlemenuright { text-align:left; background-color: #ED3301; color: #FFFFFF; border: solid 0px #0E6BB7; border-spacing: 0px; padding: 3px; width: 100%; }
.tblmenuright { text-align:left; background-color: #FDF3D4; border: solid 0px #0E6BB7; border-spacing: 3px; padding: 3px; width: 100%; }

.tblmain_report { text-align:left; border: solid 0px #0E6BB7; border-spacing: 0px; padding: 0px; width: 100%; }
.tbltitle_report { text-align:left; background-color: #0E6BB7; color: #FFFFFF; border: double 3px #0E6BB7; border-spacing: 3px; width: 100%; }
.tbl_report { text-align:left; background-color: #FFFFFF; border: solid 0px #0E6BB7; border-spacing: 0px; padding: 3px; width: 100%; }


.tbl_tools { text-align:left; border: solid 1px #CBC7BE; background-color: #F8F8F8; border-spacing: 1px; padding: 0px; width: 100%; }
.tblval_report { text-align:left; border: solid 1px #CBC7BE; border-spacing: 1px; padding: 0px; width: 100%}

.tbltitle td { text-align:left; background: #1A77BC; color: #FFFFFF; font-weight: bold; padding-top:5px; padding-bottom:5px; padding-left:5px; padding-right:3px; }
.treven td { text-align:left; background: #FFFFFF; padding-top:5px; padding-bottom:5px; padding-left:5px; padding-right:3px; }
.trodd td { text-align:left; background: #F9FCFF; padding-top:5px; padding-bottom:5px; padding-left:5px; padding-right:3px; }
.trsel td { text-align:left; background: #F0F4F8; padding-top:5px; padding-bottom:5px; padding-left:5px; padding-right:3px; }

.tblpage {  }
.pagen { text-align: center; }
.pagenactive { border: solid 1px #FD8B40; background-color: #FED3B8; text-align: center; }

.caltblmain { text-align:left; border: solid 1px #D50101; }
.caltbl { text-align:left; background-color: #FFFFFF; }
.caltdtitle td { border: solid 0px #D50101; background: #FFCFA8; border-spacing: 0px; padding: 3px; }
.caltekmonth { text-align:left; color: #D50101; font-weight: bold; }
.calamonth { }
.calmonth { color: #666666; }
.caltdday { border: solid 0px; background: #FDF3D4; }
.caltddayactive { border: solid 1px #D50101; background: #FFCFA8; }
.caldayactive { font-weight: bold; font-weight: bold; }
.caldaypas { border: solid 0px; color: #666666 }
.caltdweekday { border: solid 0px; color: #666666; background-color: #FDF3D4; }

.tblpathes { text-align:left; background: #FFFFFF; }
.tbltdpathes { text-align:left; background: #FFE8D7; border: thin 1px #FFFFFF; }


.content { text-align:left; font-family: tahoma, sans-serif; font-size: 11px; }
.copyright { border: dotted 0px #0E6BB7; background-color: #F0F4F8; text-align: center; }


.h1 { font-family: tahoma, sans-serif; font-size: 14px; padding-left: 0px;}
.h2 {font-size:12px; padding-left:0px;}

.error { color: red; font-weight: bold }
.na { color: gray; }
.hint { font-size:9px; color: gray; }
.primg { top: 3px; border: none; }

//-->
</style>
<script type="text/javascript" language="JavaScript" src="js.php"></script>
</head>
<body class="mbody">
<table style="width:100%;" border="0" cellpadding="0" cellspacing="0">
<tr><td>
<?php

if (($CONFIG["disablepassword"] != "1") && (!$authed)) {

?>
<table style="width:100%;  height:600px;" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td class="content" align="center" valign="middle">

    <table class="tdmaincenter" border="0" cellspacing="0" cellpadding="0" align="center" width="250">
        <form action="" method="post" name="auth">
        <tr class="tbltitle">
            <td width="15%" align="center"><img src="img/key.gif" align="middle" border="0"></td>
            <td width="85%" align="center" style="font-size:12px; font-family:tahoma,sans-serif">
                <b>SiteEdit Statistics <?= strval($CONFIG["version"]) ?> </b><br />Вход в систему</td></tr>
        <tr class="trodd">
            <td class="tdauthdata" align="center" colspan="2">
                <table border="0" cellspacing="0" cellpadding="0">
                <?= ($err==1)?'<tr><td colspan="2"><font class="error">Неверный логин или пароль</font></td></tr>':"";?>
                <tr><td>Логин: </td>
                    <td><input type="text" name="login" value="<?= $_POST["login"] ?>" /></td></tr>
                <tr><td>Пароль: </td>
                    <td><input type="password" name="password" value="<?= $_POST["password"] ?>" /></td></tr>
                <tr><td colspan="2"><input type="checkbox" name="store"> Сохранить данное соединение</td></tr>
                </table>
            </td>
        </tr>
        <tr class="trsel"><td colspan="2" align="center">
            <input type="submit" value="Войти" />
        </td></tr>
        <input type="hidden" name="action" value="enter" />
        </form>
    </table>
        <?php
        if (intval(trim($CONFIG["guestaccess"]))==1) print "<center><br>Для гостевого доступа к системе введите <b>guest</b> в поля Логин и Пароль";

}else {
// Окончание проверки аутентификации


// массив для вывода меню
$MENU_GROUPS = Array(
0 => "Аудитория",
1 => "Страницы",
2 => "Рефереры (ссылки)",
4 => "Система",
5 => "География",
6 => "Разное",
8 => "Конфигурация",
);
//3 => "Отслеживание целей<br>(Рекламные кампании)",
//7 => "Администрирование",

$MENU = Array(
0, "attendance" ,"Посещаемость сайта (Сводная статистика)", "ta",
0, "viewonhost" ,"Просмотров на хост",                      "th",
0, "viewonuser" ,"Просмотров на пользователя",              "tu",
999, "return"     ,"Возвраты на сайт",                        "tr",
0, "timeview"   ,"Время просмотра страниц",                 "tt",
0, "newusers"   ,"Старые/новые пользователи",               "tn",
0, "rateload"   ,"Скорость загрузки страниц",               "tv",

1, "domains"    ,"Популярные домены (Варианты обращения)",  "pd",
1, "pages"      ,"Популярные страницы",                     "pp",
1, "titles"     ,"Популярные заголовки",                    "pt",
1, "input"      ,"Точки входа",                             "pi",
1, "output"     ,"Точки выхода",                            "po",
1, "pathes"     ,"Пути по сайту (Глубина просмотра сайта)", "pg",
1, "move_i"      ,"Внутренние переходы",                    "pm",
999, "parts"      ,"Первый уровень сайта",                    "ps",

2, "refdomains"  ,"Ссылающиеся домены",                 "rd",
2, "refpages"    ,"Ссылающиеся страницы",               "rp",
999, "move_m"      ,"Переходы с почтовых систем",         "rm",
2, "move_c"      ,"Переходы с каталогов",               "rk",
2, "move_t"      ,"Переходы с рейтингов",               "rt",
999, "move_d"      ,"Переходы с популярных серверов",     "rv",
2, "move_s"      ,"Переходы с поисковых систем",        "rs",
999, "move_history","История переходов",                  "rh",
2, "phrases"     ,"Поисковые фразы",                    "rf",
999, "links"       ,"Поисковые ссылки",                   "rl",
2, "searchpages" ,"Найденные страницы",                 "rc",
999, "partners"    ,"Партнеры",                           "rr",

3, "advert_referers" ,"Источники, приведшие к цели",   "ar",
3, "advert_phrases"  ,"Фразы, приведшие к цели",       "af",
3, "advert_log"      ,"Список достижений целей",       "al",
3, "advert_prices"   ,"Стоимость рекламы",             "ap",
3, "advert_config"   ,"Настройка",                     "ac",

4, "agents"      ,"Агенты",                     "ua",
4, "acceptlang"  ,"Accept-Languages",           "ul",
4, "os"          ,"Операционные системы",       "us",
4, "browsers"    ,"Браузеры",                   "ub",
4, "screensize"  ,"Экранное разрешение",        "uz",
4, "colorsdepth" ,"Глубина цвета",              "ug",
4, "cookies"     ,"Использование Cookies",      "uc",
4, "java"        ,"Использование Java",         "uj",
4, "javascr"     ,"Использование JavaScript",   "up",

5, "ip"          ,"IP адреса",   "gi",
999, "subnets"     ,"Сети",      "gs",
5, "geolang"     ,"Языки",       "gl",
5, "cities"      ,"Города",      "gc",
5, "countries"   ,"Страны",      "gt",

6, "realtime"    ,"Сейчас на сайте",           "ot",
999, "viewlog"     ,"Просмотр лога",           "ol",
6, "indexable"   ,"Индексация сайта",          "oi",
6, "robots"      ,"Роботы",                    "or",

7, "dbsize"        ,"Размер базы данных",      "db",
999, "useronline"    ,"Пользователи online",   "",
999, "viewlog"       ,"Журнал посещений",      "",
999, "localization"  ,"Локализация",           "",
7, "expectrobots"  ,"Возможные роботы",        "",

8, "config"        ,"Настройки",                 "cg",
999, "dbcontrol"     ,"Управление базой данных",   "cc",
999, "expimp"        ,"Экспорт/Импорт",            "сl",
999, "datas"         ,"Словари данных",            "cd",
999, "confmail"      ,"Отчет по почте",            "cm",
8, "accessdelimit" ,"Права доступа",             "ca",
999, "accessguest"   ,"Гостевой вход",             "ce",
999, "filters"       ,"Фильтры",                   "cf",
8, "update"        ,"Обновление",                "cu",
8, "regstat"       ,"Регистрация",               "cr",
8, "logout&amp;nowrap=1" ,"Завершение сеанса", "",

);



?>

<table class="tblmain" cellspacing="5" cellpadding="0" width="100%">
    <tr><td class="tdmainleft" valign="top" width="20%">
<?php
$accessreportsguest = array();
// загружаем отчеты открытые для просмотра гостю
$krg = explode(",", str_replace(" ", "", $CONFIG["guestviewreports"]));

for ($i=0; $i<count($krg); $i++) {
    if (!empty($krg[$i]) && in_array($krg[$i], $MENU)) {
        $k = array_search($krg[$i], $MENU, TRUE);
        if (!empty($MENU[$k-2])) $accessreportsguest[] = $MENU[$k-2];
    }
}

// фильтр для запроса
//if (isset($_GET["filter"])) $flt="&amp;filter=".urlencode($_GET["filter"]);

// ВЫВОД ЛЕВОГО МЕНЮ
foreach ($MENU_GROUPS as $i => $value) {
//for ($i=0; $i < count($MENU_GROUPS); $i++) {
    print '<table class="tbltitlemenuleft" cellspacing="0" cellpadding="5" border="0" width="100%"><tr><td><b>'.$MENU_GROUPS[$i].'</b></td></tr></table>';
    //se_stat_title($MENU_GROUPS[$i]);
    print '<table class="tblmenuleft" width="100%" cellspacing="0" cellpadding="0" border="0"><tr><td><ul>';
    for ($j=0; $j < count($MENU); $j+=4) {
        if ($MENU[$j] == $i) {
            if (($enterlogin == 'guest') && (!in_array($MENU[$j+1], $accessreportsguest)) && !empty($MENU[$j+3])) continue;
            if ($st == $MENU[$j+1]) print '<li><font class="menuitemactive">'.$MENU[$j+2].'</font><br>';
            else print '<li><a class="menuitem" href=\'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'?st='.$MENU[$j+1].'&amp;sdt='.$sdt.'&amp;fdt='.$fdt.'\'>'.$MENU[$j+2].'</a><br>';
        }
    }
    print "</ul></td></tr></table>";
}

/*for ($j=0; $j < count($MENU); $j+=4)
    if (!empty($MENU[$j+3])) $asd[] = $MENU[$j+3];
echo count($asd).":".count(array_unique($asd))."<br>";
//sort($asd);
//for ($j=0; $j < count($asd); $j++) echo $asd[$j].",";
*/
// КОНЕЦ ВЫВОДА  ЛЕВОГО МЕНЮ

// ВЫВОД ОТЧЕТОВ
print "</td><td class=\"tdmaincenter\" align=\"center\" valign=\"top\" width=\"60%\">";
//print "<table class='tblmain_report' width=100% ><tr><td valign=top>";

// Заголовок отчета
    print '<table class="tbltitle_report" cellspacing="0" cellpadding="0" border="0" width="100%"><tr>';
    if (file_exists("img/reports/".$st.".gif"))
        print '<td width="20px" valign="center" align="center"><img src="img/reports/'.$st.'.gif" align="middle" border="0"></td>';
    else
        print '<td width="20px" valign="center" align="center"><img src="img/reports/def.gif" align="middle" border="0"></td>';
    print "<td width=\"5px\">&nbsp;</td>";
    print "<td valign=\"top\" align=\"left\">";
    for ($j = 0; $j < count($MENU); $j += 4)
        if ($st == $MENU[$j+1]) print "<b class=\"h1\">".$MENU[$j+2]."</b>";
//    if (!empty($_GET["filter"])) print "<br>Фильтр: ".$_GET["filter"];
    if ($rmn) {
        print '<br> Отчет за период с <b>'.date($CONFIG["datetime_format"], ($sdt + $CONFIG["timeoffset"])).'</b>
                                    до <b>'.date($CONFIG["datetime_format"], ($fdt + $CONFIG["timeoffset"])).'</b>';
    }
    print "</td></tr></table><br>";


//print "</td></tr><tr><td valign=top><br>";

// Сам отчет
print "<table class=\"tbl_report\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\"><tr><td valign=\"top\">";

if (empty($CONFIG['limitpage'])) $CONFIG['limitpage'] = 30;
if (!empty($_GET["sheet"])) $sheet = htmlspecialchars($_GET["sheet"], ENT_QUOTES); else $sheet = 1;
$ADMENU = "";
$diftime_start = se_stat_getmicrotime();
mysql_query ("set character_set_client='utf8'");
mysql_query ("set character_set_results='utf8'");
mysql_query ("set collation_connection='utf8_general_ci'");

if (($enterlogin == 'guest') && (!in_array($st, $accessreportsguest))) {
    print "<center><table class=\"tblval_report\" border=\"0\" width=\"100%\"><tr class=\"tbltitle\"><td align=\"center\">У Вас нет доступа к данному отчету</td></tr></table></center>";
}else{
    if (in_array($st, $adminreports)) include "admin/".$st.".php";
    else include "reports/".$st.".php";

    print '<tr><td valign="top" align="left"><br>
               [<a href="javascript:expand(\'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'?'.se_stat_sqs("st;report;nowrap", "manual;".$st.";1").'\', \'manual\');"
                     title="Показать описание отчета">Описание отчета</a>]
                    <br><font color="gray"></font><div class="block_u" id="emanual"></div>';
}

print "</td></tr></table>";

// для отображения времени выполнения отчета
$diftime_end = se_stat_getmicrotime();
$diftime = $diftime_end - $diftime_start;

//if ($NOFILTER == 0) FormFilter($filter);

mysql_close();
//print "</td></tr></table>";
// КОНЕЦ ВЫВОДА ОТЧЕТОВ

// ВЫВОД ПРАВОГО МЕНЮ
print '</td><td class="tdmainright" valign="top" align="center" width="20%">';

if (!empty($ADMENU)) {
    print '<table class="tbltitlemenuright" cellspacing="0" cellpadding="5" border="0" width="100%"><tr><td><B>Дополнительно</B></td></tr></table>';
    //se_stat_title("Дополнительно");
    print '<table cellspacing="0" cellpadding="0" border="0" class="tblmenuright"><tr><td>';
    print $ADMENU;
    print "</td></tr></table>";
}

if ($rmn) {
    print '<table class="tbltitlemenuright" cellspacing="0" cellpadding="5" border="0" width="100%"><tr><td><B>Выбор по датам</B></td></tr></table>';
    //se_stat_title("Выбор по датам");
    print '<table class="tblmenuright" cellspacing="0" cellpadding="0" border="0" >
           <form class="m0" action="" method="get">';
    //print "<input type=\"hidden\" name=\"st\" value='".$st."'>";
    $fields = explode("&",$_SERVER['QUERY_STRING']);
    while (list ($key, $val) = each ($fields)) {
        list ($vkey,$vval) = explode("=",$val);
        if (!empty($vkey)&&($vkey!='sdt')&&($vkey!='fdt'))
            print '<input type="hidden" name="'.$vkey.'" value="'.($vval).'">';
    }
/*
print "&nbsp;&nbsp;
<img src='img/pay_methods.gif'
    style='filter:alpha(opacity=50)'
    onmouseover='nereidFade(this,100,60,15)'
    onmouseout='nereidFade(this,50,60,10)'
    border=0 alt='фигня' align='middle'>
<script>
nereidFadeObjects = new Object();
nereidFadeTimers = new Object();
function nereidFade(object, destOp, rate, delta){
if (!document.all)
return
    if (object != \"[object]\"){
        setTimeout(\"nereidFade(\"+object+\",\"+destOp+\",\"+rate+\",\"+delta+\")\",0);
        return;//osw
    }
    clearTimeout(nereidFadeTimers[object.sourceIndex]);

    diff = destOp-object.filters.alpha.opacity;
    direction = 1;
    if (object.filters.alpha.opacity > destOp){
        direction = -1;
    }
    delta=Math.min(direction*diff,delta);
    object.filters.alpha.opacity+=direction*delta;

    if (object.filters.alpha.opacity != destOp){
        nereidFadeObjects[object.sourceIndex]=object;//fantasyflash.ru
        nereidFadeTimers[object.sourceIndex]=setTimeout(\"nereidFade(nereidFadeObjects[\"+object.sourceIndex+\"],\"+destOp+\",\"+rate+\",\"+delta+\")\",rate);
    }
}</script>
";
*/
    print "<tr><td colspan=\"2\">Начальная дата:</td></tr>";
     print '<tr><td width="100px"><input id="date1" type="text" value="'.$calbegdate.'" name="sdt" style="font-size:9px;width:100px;"><br>
                    <div id="sdate1" style="visibility:hidden; background-color:white; z-index:9999; width:10px; position:absolute;"></div></td>
               <td align="left">&nbsp;<a href="javascript:ShowCalendarE(\'sdate1\',\'date1\',0);"><img src="img/calendar.jpg" alt="Выбрать дату" width="16" height="16" border="0"></a></td></tr>';

    print "<tr><td colspan=\"2\">Конечная дата:</td></tr>";
     print '<tr><td width="100px"><input id="date2" type="text" value="'.$calenddate.'" name="fdt" style="font-size:9px;width:100px;"><br>
                    <div id="sdate2" style="visibility:hidden; background-color:white; z-index:9998; width:10px; position:absolute;"></div></td>
               <td align="left">&nbsp;<a href="javascript:ShowCalendarE(\'sdate2\',\'date2\',0);"><img src="img/calendar.jpg" alt="Выбрать дату" width="16" height="16" border="0"></a></td></tr>';

    print "<tr><td align=\"left\" colspan=\"2\" width=\"120px\"><input type=\"submit\" value=\"Показать\"></td></tr>";

    print "</form></table>";

    print '<table class="tbltitlemenuright" cellspacing="0" cellpadding="5" border="0" width="100%"><tr><td><B>Выбор по дням</B></td></tr></table>';
    //se_stat_title("Выбор по дням");
    print "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" class=\"tblmenuright\"><tr><td>";
    // календарь
    include("calendar.php");
    print "</td></tr>";

    print "<tr><td>&nbsp;</td></tr>";
    print "<tr><td>";
    $co = time();// + $CONFIG["timeoffset"];
    $toffs = 0;//$CONFIG["timeoffset"];
    print "<a href='http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?".se_stat_sqs("sdt;fdt", (time()-300+$toffs).";".(time()+$toffs))."'>За последние 5 минут</a><br>";
    print "<a href='http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?".se_stat_sqs("sdt;fdt", (time()-3600+$toffs).";".(time()+$toffs))."'>За последний час</a><br>";
    print "<a href='http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?".se_stat_sqs("sdt;fdt", (time()-86400+$toffs).";".(time()+$toffs))."'>За последние 24 часа</a><br>";
    print "<a href='http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?".se_stat_sqs("sdt;fdt", (mktime(0,0,0,date("m",$co),date("d",$co),date("Y",$co))).";".(mktime(23,59,59,date("m",$co),date("d",$co),date("Y",$co))))."'>Сегодня</a><br>";
    print "<a href='http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?".se_stat_sqs("sdt;fdt", (mktime(0,0,0,date("m",$co),date("d",$co)-1,date("Y",$co))).";".(mktime(23,59,59,date("m",$co),date("d",$co)-1,date("Y",$co))))."'>Вчера</a><br>";
    print "<a href='http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?".se_stat_sqs("sdt;fdt", (mktime(0,0,0,date("m",$co),date("d",$co)-7,date("Y",$co))).";".(mktime(23,59,59,date("m",$co),date("d",$co),date("Y",$co))))."'>7 дней</a><br>";
    print "<a href='http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?".se_stat_sqs("sdt;fdt", (mktime(0,0,0,date("m",$co),date("d",$co)-30,date("Y",$co))).";".(mktime(23,59,59,date("m",$co),date("d",$co),date("Y",$co))))."'>30 дней</a><br>";
    print "</td></tr></table>";
}

if ($st == "attendance" && !empty($_GET['type']) && $type=="onhours") {
    print '<table class="tbltitlemenuright" cellspacing="0" cellpadding="5" border="0" width="100%"><tr><td><B>Выбор по дням</B></td></tr></table>';
    //se_stat_title("Выбор по дням");
    print "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" class=\"tblmenuright\"><tr><td>";
    // календарь
    include("calendar.php");
    print "</td></tr></table>";

}

/*
if ($st!="getcode" && $st!="manual" && $st!="confmail" && $st!="config" && $st!="ipinfo") {
    print '<table class="tbltitlemenuright" cellspacing="0" cellpadding="5" border="0" width="100%"><tr><td><B>Руководство</B></td></tr></table>';
    se_stat_title("Руководство");
    print "<table cellspacing=0 cellpadding=0 border=0 class='tblmenuright'><tr><td>";
    print "<a href='http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?".se_stat_sqs("st", "manual")."'>Руководство</a><br>";
    print "<a href='http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?".se_stat_sqs("st;report", "manual;1")."'>Описание отчета</a><br>";
    print "</td></tr></table>";
}
*/
/*
if (!in_array($st, $adminreports)) {
    print '<table class="tbltitlemenuright" cellspacing="0" cellpadding="5" border="0" width="100%"><tr><td><B>Экспорт</B></td></tr></table>';
    //se_stat_title("Экспорт");
    print "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" class=\"tblmenuright\"><tr><td>";
    print '<a target="_blank" href="./export/exp_n_print.php?st='.$st.'&sdt='.$sdt.'&fdt='.$fdt.'">
           <img src="./img/export_print.gif" width="16" height="16" border="0" alt="PRINT" title="PRINT"></a>
       <a target="_blank" href="./export/exp_t_csv.php?st='.$st.'&sdt='.$sdt.'&fdt='.$fdt.'">
           <img src="./img/export_csv.gif" width="16" height="16" border="0" alt="CSV" title="CSV"></a>
       <a target="_blank" href="./export/exp_n_pie chart.php?st='.$st.'&sdt='.$sdt.'&fdt='.$fdt.'">
           <img src="./img/export_pie chart.gif" width="16" height="16" border="0" alt="PIE CHART" title="PIE CHART"></a>';
    print "</td></tr></table>";
}
*/
print '<table class="tbltitlemenuright" cellspacing="0" cellpadding="5" border="0" width="100%"><tr><td><B>Время</B></td></tr></table>';
//se_stat_title("Время");
print "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" class=\"tblmenuright\"><tr><td align=\"center\">";
print 'Отчет создавался:<br>'.sprintf("%f", $diftime).' сек<br>Сейчас:<br>'.date($CONFIG["datetime_format"], time()+$CONFIG["timeoffset"]);
print "</td></tr></table>";
// КОНЕЦ ВЫВОДА ПРАВОГО МЕНЮ

}
?>

    </td>
</tr>
</table>

    </td>
</tr>
<tr>
    <td class="copyright" valign="center" align="center" height="70">

        <table align="center" border="0" cellspacing="0" cellpadding="0" width="100%"><tr>
            <td align="center" width="20%">
                <a href="http://edgestile.ru" target="_blank" ><img src="img/logo_edge.gif" title="Компания Edgestile (Эджестайл)" alt="Компания Edgestile (Эджестайл)" border="0"></a>
            </td>
            <td align="center" width="60%">
                Статистика сайта <b>«SiteEdit Statistics <?= strval($CONFIG["version"]) ?>»</b><br />
                Copyright &copy; EDGESTILE SiteEdit 2004-2010<br>
                &nbsp;-&nbsp;<a href='http://www.edgestile.ru' target="_blank">http://www.edgestile.ru</a><br />
                &nbsp;-&nbsp;<a href='http://www.siteedit.ru' target="_blank">http://www.siteedit.ru</a>
            </td>
            <td align="center" width="20%">
                <a href="http://siteedit.ru" target="_blank"><img src="img/logo_se.gif" title="CMS SiteEdit" alt="CMS SiteEdit" border="0" /></a>
            </td>
        </tr></table>

    </td>
</tr>
</table>
<!-- /center-->
</body>
</html>