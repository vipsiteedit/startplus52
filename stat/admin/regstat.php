<?php
$action = $_POST["action"];
$_SESSION['se_stat_regkey_error_text'] = "";

if (isset($_POST["action"])) {
    $regkey = $_POST["regkey"];
/*
    $datakey = split(";", se_stat_decoderregkey($regkey));
    $_regkey_domain = trim($datakey[0]);
    $_regkey_dateend = trim($datakey[1]);
    $_regkey_license = trim($datakey[2]);
    $_regkey_error = trim($datakey[3]);

    $_SESSION['se_stat_regkey_error_text'] = "";
    if (empty($_regkey_license) || empty($_regkey_dateend) || empty($_regkey_domain) || ($_regkey_error==1) ||
        (strcmp($_regkey_domain, $checkdomain) != 0)) {
        $_SESSION['se_stat_regkey_error_text'] = '<tr class="trodd"><td align="left"><font color="red"><b>Ошибочный регистрационный ключ.</b></font></td></tr>';
    }else {
        // заменяем лицензию
        if (!empty($regkey)) {
            // записываем регистрационный ключ в файл лицензии
            $fp = fopen($CONFIG['regkey_filename'], "w");
            fwrite($fp, $regkey);
            fclose($fp);
        }
    }

*/
    $datakey = explode(";", se_stat_decoderregkey($regkey));
    $_regkey_domain = explode("|", trim($datakey[0]));
    $_regkey_dateend = trim($datakey[1]);
    $_regkey_license = trim($datakey[2]);
    $_regkey_error = trim($datakey[3]);

    $CONFIG['regkey_domain'] = explode("|", trim($datakey[0]));
    $CONFIG['regkey_dateend'] = trim($datakey[1]);
    $CONFIG['regkey_license'] = trim($datakey[2]);
    $CONFIG['regkey_error'] = trim($datakey[3]);
	$CONFIG["dataupdate"] = date('Y-m-d H:i:s');

	
    $_SESSION['se_stat_regkey_error_text'] = "";
    if (empty($_regkey_license) || empty($_regkey_dateend) || empty($_regkey_domain) || ($_regkey_error==1) ||
       (!in_array($sitedomain, $_regkey_domain))) {
        $_SESSION['se_stat_regkey_error_text'] = '<tr class="trodd"><td align="left"><font color="red"><b>Ошибочный регистрационный ключ.</b></font></td></tr>';
    }elseif (date("Ymd") > $_regkey_dateend) {
        $_SESSION['se_stat_regkey_error_text'] = '<tr class="trodd"><td align="left"><font color="red"><b>Срок действия данного регистрационного ключа истек.</b></font></td></tr>';
    }else{
        // заменяем лицензию
        if (!empty($regkey)) {
            // записываем регистрационный ключ в файл лицензии
            $fp = fopen($CONFIG['regkey_filename'], "w+");
            fwrite($fp, $regkey);
            fclose($fp);
        }
    }

}

if (($_SESSION['se_stat_regkey_error']==0) || ($_SESSION['se_stat_regkey_error']==2) || date("Ymd") < $CONFIG['regkey_dateend'] ) {
?>
<table class='tblval_report' border=0 width=100%>
<tr class="trodd"><td align="left" valign="top" width=150><b>Версия системы</b></td><td align="left">SiteEdit Statistics <?= $CONFIG["version"] ?></td></tr>
<tr class="treven"><td align="left" valign="top" width=150><b>Домен</b></td><td align="left"><?= $CONFIG['regkey_domain'][0] ?></td></tr>
<tr class="trodd"><td align="left" valign="top" width=150><b>Лицензия</b></td>
    <td align="left"><b>"<?= $CONFIG['regkey_license'] ?>"</b> до <b><?= date($CONFIG["date_format"], strtotime($CONFIG['regkey_dateend'])) ?></b>
<?php
    $arrdays = array(1 => "день", 2 => "дня", 3 => "дня", 4 => "дня", 5 => "дней");
    $arrost = array(1 => "остался", 2 => "осталось", 3 => "осталось", 4 => "осталось", 5 => "осталось");

    $cndaykeyend = round((strtotime($CONFIG['regkey_dateend'])-strtotime(date("Ymd")))/60/60/24);
    if ($cndaykeyend >= 0 && $cndaykeyend <= 5)
        if ($cndaykeyend == 0)
            print "<br><font color='red'>ВНИМАНИЕ! Последний день действия лицензии.</font>";
        else
            print "<br><font color='red'>ВНИМАНИЕ! До окончания лицензии ".$arrost[$cndaykeyend]." <b>".$cndaykeyend."</b> ".$arrdays[$cndaykeyend]."</font>";
    elseif ($cndaykeyend < 0)
        print "<br><font color='red'>ВНИМАНИЕ! Срок лицензии истек.</font>";

?>
</td></tr>
<tr class="treven"><td align="left" valign="top" width=150><b>Последнее обновление</b></td><td align="left"><?= date($CONFIG["datetime_format"], strtotime($CONFIG["dataupdate"])) ?></td></tr>
</table>
<?php
}else{
?>
<table class='tblval_report' border=0 width=100%>
<tr class="trodd"><td align="left" valign="top">
    <font color="red">
        <b>ВНИМАНИЕ! У Вас ошибочный лицензионнный ключ!</b><br>
        Для корректной работы системы необходимо ввести новый лицензионный ключ!
    </font>
</td></tr>
</table>
<?php
}
?>
<br>
<table class='tblval_report' border=0 width=100%>
<form name="frmregsys" action="" method='post' enctype='multipart/form-data'>
<tr class="tbltitle"><td align="center"><b>Продление лицензии</b></td></tr>
<tr class="trodd"><td align="left">Для продления лицензии введите регистрационный ключ, полученный по e-mail.</td></tr>
<tr class="treven"><td align="left"><textarea name="regkey" rows="5" style="width:100%"></textarea></td></tr>
<?= $_SESSION['se_stat_regkey_error_text'] ?>
<tr class="trsel"><td align="right" width=150><input type="submit" value="Продолжить >>"></td></tr>
<input type=hidden name='action' value='regsys'>
</form>
</table>