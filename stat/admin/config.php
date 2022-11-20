<?php
$action = $_POST["action"];

if ($action == 1) {
	$gauge = htmlspecialchars($_POST["gauge"], ENT_QUOTES)=="on"?1:0;
	$percents = htmlspecialchars($_POST["percents"], ENT_QUOTES)=="on"?1:0;
	$hints = htmlspecialchars($_POST["hints"], ENT_QUOTES)=="on"?1:0;
	$graphic = intval(htmlspecialchars($_POST["graphic"], ENT_QUOTES));
	$date_format = htmlspecialchars($_POST["date_format"], ENT_QUOTES);
	$shortdate_format = htmlspecialchars($_POST["shortdate_format"], ENT_QUOTES);
	$shortdm_format = htmlspecialchars($_POST["shortdm_format"], ENT_QUOTES);
	$datetime_format = htmlspecialchars($_POST["datetime_format"], ENT_QUOTES);
	$datetimes_format = htmlspecialchars($_POST["datetimes_format"], ENT_QUOTES);

//	$adminlogin = htmlspecialchars($_POST["adminlogin"], ENT_QUOTES);
//	$adminpassword = htmlspecialchars($_POST["adminpassword"], ENT_QUOTES);
//	$adminemail = htmlspecialchars($_POST["adminemail"], ENT_QUOTES);
//	$disablepassword = htmlspecialchars($_POST["disablepassword"], ENT_QUOTES)=="on"?1:0;
	$savelogday = htmlspecialchars($_POST["savelogday"], ENT_QUOTES);
	$timeoffset = intval(htmlspecialchars($_POST["timeoffset"], ENT_QUOTES)+0);
	$senderrorsbymail = htmlspecialchars($_POST["senderrorsbymail"], ENT_QUOTES)=="on"?1:0;


    mysql_query("UPDATE stat_config SET `value` = '".$graphic."' WHERE CONVERT( `variable` USING utf8 ) = 'graphic' LIMIT 1;");
	mysql_query("UPDATE stat_config SET `value`='".$gauge."' WHERE CONVERT( `variable` USING utf8 ) = 'gauge' LIMIT 1;");
	mysql_query("UPDATE stat_config SET `value`='".$hints."' WHERE CONVERT( `variable` USING utf8 ) = 'hints' LIMIT 1;");
	mysql_query("UPDATE stat_config SET `value`='".$percents."' WHERE CONVERT( `variable` USING utf8 ) = 'percents' LIMIT 1;");
	mysql_query("UPDATE stat_config SET `value`='".$date_format."' WHERE CONVERT( `variable` USING utf8 ) = 'date_format' LIMIT 1;");
	mysql_query("UPDATE stat_config SET `value`='".$shortdate_format."' WHERE CONVERT( `variable` USING utf8 ) = 'shortdate_format' LIMIT 1;");
	mysql_query("UPDATE stat_config SET `value`='".$shortdm_format."' WHERE CONVERT( `variable` USING utf8 ) = 'shortdm_format' LIMIT 1;");
	mysql_query("UPDATE stat_config SET `value`='".$datetime_format."' WHERE CONVERT( `variable` USING utf8 ) = 'datetime_format' LIMIT 1;");
	mysql_query("UPDATE stat_config SET `value`='".$datetimes_format."' WHERE CONVERT( `variable` USING utf8 ) = 'datetimes_format' LIMIT 1;");

//	mysql_query("UPDATE stat_config SET `value`='".$adminlogin."' WHERE CONVERT( `variable` USING utf8 ) ='adminlogin' LIMIT 1;");
//	mysql_query("UPDATE stat_config SET `value`='".$adminpassword."' WHERE CONVERT( `variable` USING utf8 ) ='adminpassword' LIMIT 1;");
//	mysql_query("UPDATE stat_config SET `value`='".$adminemail."' WHERE CONVERT( `variable` USING utf8 ) ='adminemail' LIMIT 1;");
//	mysql_query("UPDATE stat_config SET `value`='".$disablepassword."' WHERE CONVERT( `variable` USING utf8 ) ='disablepassword' LIMIT 1;");
	mysql_query("UPDATE stat_config SET `value`='".$savelogday."' WHERE CONVERT( `variable` USING utf8 ) ='savelogday' LIMIT 1;");
	mysql_query("UPDATE stat_config SET `value`='".$timeoffset."' WHERE CONVERT( `variable` USING utf8 ) ='timeoffset' LIMIT 1;");
	mysql_query("UPDATE stat_config SET `value`='".$senderrorsbymail."' WHERE CONVERT( `variable` USING utf8 ) ='senderrorsbymail' LIMIT 1;");

}

function YesNo($name, $value, $disabled="", $def="") {
	if (!empty($disabled)) $value = $def;

	print "<SELECT name=\"".$name."\" ".$disabled." style='width:50px;'>\n
               <OPTION value=\"on\"".($value==1?" selected":"").">Да\n
               <OPTION value=\"off\"".($value==0?" selected":"").">Нет\n
	       </SELECT>\n";
}

$resconf = mysql_query("SELECT * FROM stat_config");
while ($rowconf = mysql_fetch_array($resconf, MYSQL_BOTH)) $a[$rowconf['variable']] = $rowconf['value'];

if (empty($a["date_format"])) $a["date_format"] = "d.m.Y";
if (empty($a["shortdate_format"])) $a["shortdate_format"] = "m.Y";
if (empty($a["shortdm_format"])) $a["shortdm_format"] = "d.m";
if (empty($a["datetime_format"])) $a["datetime_format"] = "d.m.Y H:i:s";
if (empty($a["datetimes_format"])) $a["datetimes_format"] = "d.m.Y H:i";

if ($a["timeoffset"]==1) $a["timeoffset"] = date("Z")/3600;
?>
<form action='' method='post'>
<table class='tblval_report' border=0 width=100%>
<tr class="tbltitle"><td colspan="2" align="center"><b>Основные настройки</b></td></tr>

<tr class="trodd"><td width="90%">Отображать диаграммы в таблицах</td><td width="10%"><?= YesNo("gauge",$a["gauge"]); ?></td></tr>
<tr class="treven"><td>Отображать проценты в таблицах</td><td><?= YesNo("percents",$a["percents"]); ?></td></tr>
<tr class="trodd"><td>Показывать подсказки</td><td><?= YesNo("hints",$a["hints"]); ?></td></tr>
</table><br>

<table class='tblval_report' border=0 width=100%>
<tr class="tbltitle"><td colspan="2" align="center"><b>Настройки графиков</b></td></tr>

<tr class="trodd"><td>График по умолчанию <font class='hint'>(Требует GD 1.0 и выше)</font></td></tr><tr class="trodd"><td colspan=2>

<table width=100% border=0 width=100%>
<tr><td width=15><input <?= (se_stat_gdVersion()==0?"disabled":""); ?> type="radio" name="graphic" value="1" <?= ($a["graphic"]==1?"checked":""); ?>></td><td>3D график <br><img src="img/graph3d.gif" vspace="2" width="130" height="68" border=0></td>
<td width=15><input <?= (se_stat_gdVersion()==0?"disabled":""); ?> type="radio" name="graphic" value="2" <?= ($a["graphic"]==2?"checked":""); ?>></td><td>Линейный <br><img src="img/graphline.gif" vspace="2" width="130" height="68" border=0></td>
<td width=15><input <?= (se_stat_gdVersion()==0?"disabled":""); ?> type="radio" name="graphic" value="3" <?= ($a["graphic"]==3?"checked":""); ?>></td><td>Гистограмма <br><img src="img/graphgist.gif" vspace="2" width="130" height="68" border=0></td></tr>
</table>
</td></tr>
</table><br>

<table class='tblval_report' border=0 width=100%>
<tr class="tbltitle"><td colspan="2" align="center"><b>Настройки даты *</b></td></tr>

<tr class="trodd"><td width=70%>Формат даты</td><td width=30%><input type="text" name="date_format" value="<?= $a["date_format"]; ?>" style="width:160px"></td></tr>
<tr class="treven"><td>Сокращенный формат даты (месяц и год)</td><td><input type="text" name="shortdate_format" value="<?= $a["shortdate_format"]; ?>" style="width:160px"></td></tr>
<tr class="trodd"><td>Сокращенный формат даты (день и месяц)</td><td><input type="text" name="shortdm_format" value="<?= $a["shortdm_format"]; ?>" style="width:160px"></td></tr>
<tr class="treven"><td>Формат даты и времени</td><td><input type="text" name="datetime_format" value="<?= $a["datetime_format"]; ?>" style="width:160px"></td></tr>
<tr class="trodd"><td>Формат даты и времени без секунд</td><td><input type="text" name="datetimes_format" value="<?= $a["datetimes_format"]; ?>" style="width:160px"></td></tr>
<tr class="treven"><td colspan=2><font class='hint'>* Дата записывается в формате PHP-функции date()</font></td></tr>
</table><br>

<table class='tblval_report' border=0 width=100%>
<tr class="tbltitle"><td colspan="2" align="center"><b>Настройки параметров системы</b></td></tr>

<!--<tr class="trodd"><td width=70%>Логин администратора</td><td width=30%><input type="text" name="adminlogin" value="<?= $a["adminlogin"]; ?>" style="width:160px"></td></tr>
<tr class="treven"><td>Пароль администратора</td><td><input type="password" name="adminpassword" value="" style="width:160px"></td></tr>
<tr class="trodd"><td width=70%>E-mail администратора</td><td width=30%><input type="text" name="adminemail" value="<?= $a["adminemail"]; ?>" style="width:160px"></td></tr>
<tr class="trodd"><td>Отключение авторизации для SiteEdit Statistics</td><td><?= YesNo("disablepassword",$a["disablepassword"]); ?></td></tr>-->
<tr class="treven"><td>Период хранения статистики (дней)</td><td>
    <select style='width:50px;' name=savelogday>
        <OPTION value='30' <?= (30==$a["savelogday"]?"selected":""); ?>>30</option>\n";
        <OPTION value='60' <?= (60==$a["savelogday"]?"selected":""); ?>>60</option>\n";
        <OPTION value='90' <?= (90==$a["savelogday"]?"selected":""); ?>>90</option>\n";
        <OPTION value='120' <?= (120==$a["savelogday"]?"selected":""); ?>>120</option>\n";
        <OPTION value='150' <?= (150==$a["savelogday"]?"selected":""); ?>>150</option>\n";
        <OPTION value='180' <?= (180==$a["savelogday"]?"selected":""); ?>>180</option>\n";
    </select>
<tr class="trodd"><td>Разница во времени с сервером (в секундах)</td><td><input type="text" name="timeoffset" value="<?= $a["timeoffset"]; ?>" style="width:160px"></td></tr>
<tr class="treven"><td>Присылать сообщения об ошибках на E-Mail</td><td><?= YesNo("senderrorsbymail",$a["senderrorsbymail"]); ?></td></tr>

</table><br>

<table class='tblval_report' border=0 width=100%>
<tr class="trsel"><td colspan="2" align="center"><input type="submit" value="Сохранить"></td></tr>
</table>
<input type="hidden" name="action" value="1">
</form>
