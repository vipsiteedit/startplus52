<?php
if ($_POST["action"] == "save") {

	$email = $_POST["email"];
	$subject = $_POST["subject"];
	if ($_POST["daily"]=="daily") $day=0;

	else $day=intval($_POST["day"]);
	$repcontent=0;
	if ($_POST["repcontent1"]=="on") $repcontent+=1;
	if ($_POST["repcontent2"]=="on") $repcontent+=2;
	if ($_POST["repcontent3"]=="on") $repcontent+=4;

	mysql_query("UPDATE stat_config SET `value`='".$day."' WHERE (`variable`='mail_day');");
	mysql_query("UPDATE stat_config SET `value`='".$email."' WHERE (`variable`='mail_email');");
	mysql_query("UPDATE stat_config SET `value`='".$subject."' WHERE (`variable`='mail_subject');");
	mysql_query("UPDATE stat_config SET `value`='".$repcontent."' WHERE (`variable`='mail_content');");

}

$resconf = mysql_query("SELECT * FROM stat_config");
while ($rowconf = mysql_fetch_array($resconf, MYSQL_BOTH)) $a[$rowconf['variable']] = $rowconf['value'];

?>
<table class='tblval_report' border=0 width="100%" >
<form name="frm" action="" method='post' enctype='multipart/form-data'>
<tr class='tbltitle'><td colspan=3 align=center><b>Периодичность</b></td></tr>

<tr class='trodd'><td width='20'><input <?php if ($a["mail_day"]==0) print "checked"; ?> type=radio name=period value='daily' onClick="document.frm.day.disabled=true;"></td>
    <td colspan=2>Каждый день</td></tr>

<tr class='treven'><td width='20'><input <?php if ($a["mail_day"]!=0) print "checked"; ?> type=radio name=period value='weekly' onClick="document.frm.day.disabled=false;"></td>
    <td colspan=2>Раз в неделю, каждый&nbsp;
        <select name=day style='width:150px' <?php if ($a["mail_day"]==0) print "disabled"; ?>>
            <option value=1 <?php if ($a["mail_day"]==1) print "selected"; ?>>Понедельник
            <option value=2 <?php if ($a["mail_day"]==2) print "selected"; ?>>Вторник
            <option value=3 <?php if ($a["mail_day"]==3) print "selected"; ?>>Среда
            <option value=4 <?php if ($a["mail_day"]==4) print "selected"; ?>>Четверг
            <option value=5 <?php if ($a["mail_day"]==5) print "selected"; ?>>Пятница
            <option value=6 <?php if ($a["mail_day"]==6) print "selected"; ?>>Суббота
            <option value=7 <?php if ($a["mail_day"]==7) print "selected"; ?>>Воскресенье
        </select>
</td></tr>

<tr class=tbltitle><td colspan=3 align=center><b>Настройка почты</b></td></tr>
<tr class=trodd><td width=20>&nbsp;</td><td width=10%>E-Mail</td><td align="lift">
    <input value="<?= htmlspecialchars($a["mail_email"], ENT_QUOTES); ?>" type=text name=email style='width:100%'></td></tr>
<tr class=treven><td width=20>&nbsp;</td><td width=10%>Тема</td><td align="lift">
    <input value="<?= htmlspecialchars($a["mail_subject"], ENT_QUOTES); ?>" type=text name=subject style='width:100%;'></td></tr>

<tr class=tbltitle><td colspan=3 align=center><b>Сожержимое отчета</b></td></tr>
<tr class=trodd>
    <td width='20'><input <?php if (($a["mail_content"]&1)!=0) print "checked"; ?> type=checkbox name=repcontent1></td>
    <td colspan=2>Хиты, Хосты, Сессии по дням</td></tr>
<tr class=treven>
    <td width='20'><input <?php if (($a["mail_content"]&2)!=0) print "checked"; ?> type=checkbox name=repcontent2></td>
    <td colspan=2>20 самых популярных страниц</td></tr>
<tr class=trodd>
    <td width='20'><input <?php if (($a["mail_content"]&4)!=0) print "checked"; ?> type=checkbox name=repcontent3></td>
    <td colspan=2>20 самых популярных ссылающихся страниц</td></tr>

<tr class='trsel'><td colspan=3 align=center><input type=submit value='Сохранить'></td></tr>

<input type=hidden name='action' value='save'>
</form>
</table>

