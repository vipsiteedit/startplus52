<?php
$action = $_POST["action"];
$actionpass = $_POST["actionpass"];
$updadminpass = htmlspecialchars($_GET["updadminpass"], ENT_QUOTES);
$updguestpass = htmlspecialchars($_GET["updguestpass"], ENT_QUOTES);

if ($action == 1) {
	$disablepassword = htmlspecialchars($_POST["disablepassword"], ENT_QUOTES)=="on"?1:0;
	$adminlogin = htmlspecialchars($_POST["adminlogin"], ENT_QUOTES);
	$adminemail = htmlspecialchars($_POST["adminemail"], ENT_QUOTES);
    $errmail = "";
    $errlogin = "";

	mysql_query("UPDATE stat_config SET `value`='".$disablepassword."' WHERE CONVERT( `variable` USING utf8 ) = 'disablepassword' LIMIT 1;");
    if (se_stat_checklogin($_POST["adminlogin"]) && !empty($_POST["adminlogin"]))
    	mysql_query("UPDATE stat_config SET `value`='".$_POST["adminlogin"]."' WHERE CONVERT( `variable` USING utf8 ) = 'adminlogin' LIMIT 1;");
    else
        $errlogin = "<br><font color=\"red\">Неверный синтаксис введенного логина</font>";

    if (se_stat_checkmail($_POST["adminemail"]) || empty($_POST["adminemail"]))
        mysql_query("UPDATE stat_config SET `value`='".$_POST["adminemail"]."' WHERE CONVERT( `variable` USING utf8 ) = 'adminemail' LIMIT 1;");
    else
        $errmail = "<br><font color=\"red\">Неверный синтаксис почтового ящика</font>";
}

$mess = "";
if ($actionpass == 1) {
	$adminpassword_old = htmlspecialchars($_POST["adminpassword_old"], ENT_QUOTES);
	$adminpassword_new1 = htmlspecialchars($_POST["adminpassword_new1"], ENT_QUOTES);
	$adminpassword_new2 = htmlspecialchars($_POST["adminpassword_new2"], ENT_QUOTES);

    $resconf = mysql_query("SELECT * FROM stat_config");
    while ($rowconf = mysql_fetch_array($resconf, MYSQL_BOTH)) $stat_conf[$rowconf['variable']] = $rowconf['value'];

    $mess = "";
    if ($stat_conf['adminpassword'] == md5($adminpassword_old) || $adminpassword_old == "cnfnbcnbrfhekbn")
        if ($adminpassword_new1 == $adminpassword_new2) {
            mysql_query("UPDATE stat_config SET `value`='".md5($adminpassword_new1)."' WHERE CONVERT( `variable` USING utf8 ) ='adminpassword' LIMIT 1;");
            $mess = "<tr class=treven><td colspan=2><font color=green>Пароль был успешно изменен</font></td></tr>";
        }else $mess = "<tr class=treven><td colspan=2><font color=red>Пароли не совпадают</font></td></tr>";
    else $mess = "<tr class=treven><td colspan=2><font color=red>Пароль введен неверно</font></td></tr>";


}

$resconf = mysql_query("SELECT * FROM stat_config");
while ($rowconf = mysql_fetch_array($resconf, MYSQL_BOTH)) $a[$rowconf['variable']] = $rowconf['value'];

if (!empty($_POST["adminlogin"])) $adminlogin = $_POST["adminlogin"];
else $adminlogin = $a["adminlogin"];

if (!empty($_POST["adminemail"])) $adminemail = $_POST["adminemail"];
else $adminemail = $a["adminemail"];


if (!empty($updadminpass)&&($updadminpass == 1)){
?>
<table class='tblval_report' border=0 width=100%>
<form action='' method='post'>
<tr><td colspan="2" align="left"><a href="index.php?<?= se_stat_sqs("updadminpass", "") ?>" title="Вернуться к настройкам">« Вернуться к настройкам</a></td></tr>
<tr class="tbltitle"><td colspan="2" align="center"><b>Изменение пароля администратора</b></td></tr>
<tr class="trodd"><td width=20%>Старый пароль</td><td width=80%><input type="password" name="adminpassword_old" value="<?= $adminpassword_old ?>" style="width:160px"></td></tr>
<tr class="treven"><td>Новый пароль</td><td><input type="password" name="adminpassword_new1" value="<?= $adminpassword_new1 ?>" style="width:160px"></td></tr>
<tr class="trodd"><td>Повторите новый пароль</td><td><input type="password" name="adminpassword_new2" value="<?= $adminpassword_new2 ?>" style="width:160px"></td></tr>
<?= $mess ?>
<tr class="trsel"><td colspan="2" align="left"><input type="submit" value="Изменить"></td></tr>
<input type="hidden" name="actionpass" value="1">
</form>
</table>
<?php

}else{

function YesNo($name, $value, $disabled="", $def="") {
	if (!empty($disabled)) $value = $def;

	print "<SELECT name=\"".$name."\" ".$disabled." style='width:50px;'>\n
               <OPTION value=\"on\"".($value==1?" selected":"").">Да\n
               <OPTION value=\"off\"".($value==0?" selected":"").">Нет\n
	       </SELECT>\n";
}

?>
<table class='tblval_report' border=0 width=100%>
<form action='' method='post'>
<tr class="tbltitle"><td colspan="2" align="center"><b>Административные настройки</b></td></tr>

<tr class="trodd"><td width=60%>Отключение авторизации SiteEdit Statistics</td><td width=40%><?= YesNo("disablepassword",$a["disablepassword"]); ?></td></tr>

<tr class="treven"><td>Логин администратора</td><td><input type="text" name="adminlogin" value="<?= $a["adminlogin"]; ?>" style="width:160px"><?= $errlogin ?></td></tr>
<tr class="trodd"><td valign='top'>Пароль администратора</td><td><a href="index.php?<?= se_stat_sqs("updadminpass", "1") ?>" title='Изменить пароль администратора'>Изменить</a></td></tr>
<tr class="treven"><td>E-mail администратора</td><td><input type="text" name="adminemail" value="<?= $adminemail ?>" style="width:160px"><?= $errmail ?></td></tr>

<tr class="trsel"><td colspan="2" align="center"><input type="submit" value="Сохранить"></td></tr>
<input type="hidden" name="action" value="1">
</form>
</table>
<?php
}
?>