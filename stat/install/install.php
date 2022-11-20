<?php
/******************************************************************************/
/*                         (c) SiteEdit Statistics                            */
/******************************************************************************/
error_reporting(E_ALL & ~E_NOTICE);

$CCODE='
<script type="text/javascript" language="javascript">
date = new Date ();
time = date.getTime();
    var Name = "idse";
    var search = Name + "=";
    var se_cook = "";
    if (document.cookie.length > 0) {
        offset = document.cookie.indexOf(search)
        if (offset != -1) {
            offset += search.length
            end = document.cookie.indexOf(";", offset)
            if (end == -1) end = document.cookie.length
            se_cook= escape(document.cookie.substring(offset, end))
        }
    }
siteedit_js="1.0";
siteedit_f=""+time;
siteedit_f+="&amp;cooken="+(document.cookie?"Y":"N")+"&amp;idse="+se_cook;
</script>
<script type="text/javascript" language="javascript1.1">
siteedit_js="1.1";siteedit_f+="&amp;java="+(navigator.javaEnabled()?"Y":"N")
</script>
<script type="text/javascript" language="javascript1.2">
siteedit_js="1.2";
siteedit_f+="&amp;width="+screen.width+"&amp;height="+screen.height+"&amp;pix="+
(((navigator.appName.substring(1,4)=="icr"))?
screen.colorDepth:screen.pixelDepth)</script>
<script type="text/javascript" language="javascript1.3">siteedit_js="1.3"</script>
<script type="text/javascript" language="javascript1.4">siteedit_js="1.4"</script>
<script type="text/javascript" language="javascript1.5">siteedit_js="1.5"</script>
<script type="text/javascript" language="javascript">siteedit_f+="&amp;js="+siteedit_js;
document.write("<img src=\'/stat/count.php?"+siteedit_f+"&amp;timestart=[page_timestart]&idlog=[stat_idlog]\' border=0>");
</script>';


$SOFTTITLE = "SiteEdit Statistics 1.0";
$TOTAL = 2;
if (!empty($_POST["step"])) $step = intval(htmlspecialchars($_POST["step"], ENT_QUOTES));

$ispng = function_exists("imagepng")?1:0;

if (!function_exists('se_stat_checkmail')){
function se_stat_checkmail($email) {
    // Проверка синтаксиса имени почтового ящика
    if (preg_match("/[a-z,A-Z,0-9,\-,\_,\.]+\@[a-z,0-9,\-,\_]+\.[a-z]{2,6}/", $email)) return true;
    else return false;

}}

function install_title($c, $t, $text) {
global $SOFTTITLE;
	$ttl = "Установка ".$SOFTTITLE." :: Шаг ".$c."/".$t." - ".$text;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//RU">
<HTML>
<HEAD>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8" >
<title><?php echo $ttl; ?></title>
<style type="text/css">
<!--
select,input,td { font-family: tahoma, sans-serif; font-size: 11px; }
a,a:visited { text-decoration: none; color: blue; }
a:hover { text-decoration: underline }
form { margin:0px; }

.tblmain { background-color: #FFFFFF; background-position:top center; border: solid 1px #0E6BB7; border-spacing: 0px; padding: 0px; }
.t0 { background-color: #F9FCFF; }
.t1 { background-color: #FFFFFF; padding-top:5px; padding-bottom:5px; padding-left:5px; padding-right:3px; }
.t2 { background-color: #F9FCFF; padding-top:5px; padding-bottom:5px; padding-left:5px; padding-right:3px; }
.ttl { width:100%; background:#1A77BC; color: #FFFFFF; font-weight: bold; padding-top:5px; padding-bottom:5px; padding-left:5px; padding-right:3px; }
.trsel { background: #F0F4F8; padding-top:5px; padding-bottom:5px; padding-left:5px; padding-right:3px; }

.m0 { margin:0px; }

//-->
</style>
</head>
<body bgcolor="white">
<table class=tblmain cellspacing=0 cellpadding=5 border=0 width=100%><tr><th class="ttl" style="text-align:left;"><?php echo $ttl; ?></th></tr></table>
<?php
}

function printvars($A) {
	while (list($key, $val) = each($A)) {
		$val = StripSlashes(htmlspecialchars($val));
		print "<input type=hidden name=\"$key\" value=\"$val\">\n";
	}
}

if (!empty($step) && $step == 1) {
	$step = 2;
	$error = "";
	if (empty($_POST["adminpassword1"])) $error .= "<LI>Не введен пароль администратора";
	if ($_POST["adminpassword2"] != $_POST["adminpassword1"]) $error .= "<LI>Пароли администратора не совпадают";
	$_POST["savelogday"] = intval($_POST["savelogday"]);
	$_POST["adminpassword"] = $_POST["adminpassword2"];

	$STAT_CONF["adminemail"] = $_POST["adminemail"];
    if (!empty($STAT_CONF["adminemail"])&&!se_stat_checkmail($STAT_CONF["adminemail"])) $error .= "<LI>Имя почтового ящика введено не верно";

    $STAT_CONF["adminlogin"] = $_POST["adminlogin"];
	$STAT_CONF["adminpassword"] = $_POST["adminpassword"];
	$STAT_CONF["adminpassword1"] = $_POST["adminpassword1"];
	$STAT_CONF["adminpassword2"] = $_POST["adminpassword2"];
	$STAT_CONF["savelogday"] = $_POST["savelogday"];

	if (empty($error)) {
        require_once("../../system/conf_mysql.php");
		require_once "../../lib/yaml/seYaml.class.php";
		require_once "../../lib/lib_database.php";
		se_db_dsn('mysql');
		if (se_db_connect())
		{
       	$sqllist =   explode(';', se_yaml_to_sql('schema.yml'));

 
            set_time_limit(200);

			install_title(2, $TOTAL, "Построение конфигурации");
			print "<UL>";

		/*)	$r = mysql_query("SHOW tables") or die(mysql_error());
			$f = 0;
            while ($a = mysql_fetch_array($r)) if ($a[0] == "stat_config" && $f == 0) $f = 1;

            if ($f == 1) {
				$resconf = mysql_query("SELECT * FROM stat_config") or die(mysql_error()."a");
                while ($rowconf = mysql_fetch_array($resconf, MYSQL_BOTH)) $CONFIG[$rowconf['variable']] = $rowconf['value'];
                if ((!empty($CONFIG['version'])) && ($CONFIG['version'] == "1.0")) $f = 2;
			}

			if ($f == 2) {
				print "<LI>Эта база данных соответствует версии SiteEdit Statistics 1.1\n";
			}
			elseif ($f == 1) {
				print "<LI>Обновление таблицы <B>stat_config</B>\n";flush();
				se_db_query("UPDATE stat_config SET `value`='1.1' WHERE `variable`='version';");
				se_db_query("UPDATE stat_config SET `value`='".date("Y-m-d H:i:s")."' WHERE `variable`='dataupdate';");

			}*/
            /* Установка */
			//else */
			{	$counter = 1;
				foreach($sqllist as $sqltable) 
				{
					if (preg_match("/CREATE TABLE IF NOT EXISTS `(.*)` \(/", $sqltable, $m))
					{
						print "<LI>".$counter++.". Создание таблицы {$m[1]}\n"; flush();
					}
					se_db_query($sqltable);
					echo mysql_error();
				}

				print "<LI>Заполнение таблиц данными по умолчанию\n";
                print "<UL>";

				print "<LI>Заполнение таблицы stat_config\n";flush();
                include("dump/stat_config.sql.php");

				print "<LI>Заполнение таблицы stat_datas\n";flush();
                include("dump/stat_datas.sql.php");

				print "<LI>Заполнение таблицы stat_datasuser\n";flush();
                include("dump/stat_datasuser.sql.php");

				print "<LI>Заполнение таблицы stat_cities\n";flush();
                include("dump/stat_cities.sql.php");

				print "<LI>Заполнение таблицы stat_countries\n";flush();
                include("dump/stat_countries.sql.php");

				print "<LI>Заполнение таблицы stat_ip2city\n";flush();
                include("dump/stat_ip2city.sql.php");

				print "<LI>Заполнение таблицы stat_ip2country\n";flush();
                for ($cnc=1; $cnc<=8; $cnc++)
                include("dump/stat_ip2country.".$cnc.".sql.php");

				print "<LI>Заполнение таблицы stat_languages\n";flush();
                include("dump/stat_languages.sql.php");

                print "</UL>";
			}

			print "<LI>Все необходимые изменения успешно завершены.\n";
			print "</UL>";

?>
<br>
<table cellspacing=1 cellpadding=6 border=0 align=left width=600>
<form action='' method=post>
<tr><th class='ttl' colspan=2>Установка счетчика</th></tr>
<tr class=t1><td>
<P>Следующий код должен быть включен в блок "Статистика и счетчики" Вашего web-сайта</P>
<textarea readonly name=config style='width:100%;height:250px;'><?=$CCODE;?></textarea></td></tr>
</td></tr>
</form>
</table>

</BODY></HTML>
<?php
            exit();
        } /* of else */
    } /* if (empty($error)) */
} /* of if ($_POST["step"]) */


if (intval($STAT_CONF["savelogday"]) == 0) $STAT_CONF["savelogday"] = 30;

if (substr($_SERVER['HTTP_HOST'],0,4)=='www.') $domain = substr($_SERVER['HTTP_HOST'],4);
else $domain = $_SERVER['HTTP_HOST'];
if (substr($domain,strlen($domain)-3,3)=='.ru') $domain = substr($domain,0,strlen($domain)-3);

if (empty($STAT_CONF["dbname"])) $STAT_CONF["dbname"] = "a_".$domain;

install_title(1, $TOTAL, "Ввод данных");

if (!empty($error)) {
	print "<P><b><font color=red>При заполнении формы произошли ошибки</font></B>\n<UL>".$error."</UL>";
}
?>
<script language="JavaScript" type="text/javascript">
<!--
function upd(v) {
	var i=document.getElementById("adminlogin");
	i.value=v;
}
//-->
</script>

<br>
<form action='' method=post>
<table class=tblmain cellspacing=1 cellpadding=5 border=0 align=center width=600>
<input type=hidden name=step value='1'>
<tr class=ttl><td colspan=2>Данные администрирования</td></tr>
<tr class=t1><td>Логин администратора</td><td>
    <input style='width:300px;' id='adminlogin' type=text name=adminlogin value='admin'></td></tr>
<tr class=t2><td>Пароль администратора</td><td>
    <input style='width:300px;' type=password name=adminpassword1 value="<?=htmlspecialchars($STAT_CONF["adminpassword1"]);?>"></td></tr>
<tr class=t1><td>Повторите пароль</td><td>
    <input style='width:300px;' type=password name=adminpassword2 value="<?=htmlspecialchars($STAT_CONF["adminpassword2"]);?>"></td></tr>
<tr class=t2><td>E-mail администратора</td><td>
    <input style='width:300px;' type=text name=adminemail value="<?=htmlspecialchars($STAT_CONF["adminemail"]);?>"></td></tr>
<tr class=ttl><td colspan=2>Хранение статистики</td></tr>
<tr class=t1><td>Сохранять статистику на период (дней):</td><td>
    <select style='width:300px;' name=savelogday>
        <OPTION value='30' <?= (30==$STAT_CONF["savelogday"]?"selected":""); ?>>30</option>\n";
        <OPTION value='60' <?= (60==$STAT_CONF["savelogday"]?"selected":""); ?>>60</option>\n";
        <OPTION value='90' <?= (90==$STAT_CONF["savelogday"]?"selected":""); ?>>90</option>\n";
        <OPTION value='120' <?= (120==$STAT_CONF["savelogday"]?"selected":""); ?>>120</option>\n";
        <OPTION value='150' <?= (150==$STAT_CONF["savelogday"]?"selected":""); ?>>150</option>\n";
        <OPTION value='180' <?= (180==$STAT_CONF["savelogday"]?"selected":""); ?>>180</option>\n";
    </select>
</td></tr>

<tr class=trsel><td colspan=2 align=right>
    <input type=submit value='Продолжать &gt;&gt;'></td></tr>
</table></form>
</body></html>