<?php

error_reporting();
session_start();

function se_stat_randchr($cn, $f = 2)
{
  // Генерация случайного символа
  if ($f == 0)
    $sarr = array_merge(range(0, 9));
  elseif ($f == 1)
    $sarr = array_merge(range('a', 'z'), range('A', 'Z'));
  else
    $sarr = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));

  $s = "";
  for ($k = 1; $k <= $cn; $k++)
  {
    $num = rand(0, count($sarr) - 1);
    $s .= $sarr[$num];
  }
  return $s;
}

// Кодирование инфы
function se_stat_coderregkey($domain, $date, $license)
{
  $fill_d = array(1 => 5, 7, 4, 3, 9, 8, 5, 9, 2, 3, 7, 4, 7, 5, 4, 2, 6, 5, 7, 6, 4, 5, 2, 9, 4, 6, 8, 6, 3, 8, 9, 8, 4, 3, 7, 1, 3, 1, 5, 6, 3, 2, 3, 8, 2, 1, 5, 1, 9, 4, 5, 7, 4, 3, 9, 8, 5, 9, 2, 3, 7, 4, 7, 5, 4, 2, 6, 5, 7, 6, 4, 5, 2, 9, 4, 6, 8, 6, 3, 8, 9, 8, 4, 3, 7, 1, 3, 1, 5, 6, 3, 2, 3,
    8, 2, 1, 5, 1, 9, 4);
  $fill_p = array(1 => 8, 9, 5, 2, 9, 3, 4, 8);
  $fill_l = array(1 => 6, 5, 7, 9, 2, 1, 9, 8, 6, 5);
  $replaceinttostr = array(0 => "X", 1 => "Z", 2 => "s", 3 => "E", 4 => "t", 5 => "I", 6 => "O", 7 => "A", 8 => "q", 9 => "w");
  $znakstr = array("." => "T", "-" => "D", "|" => "I");

  $genregkey = se_stat_randchr(13);

  $cnd = strlen(trim($domain));
  $cnl = strlen(trim($license));

  // Кодируем домен
  $genregkey .= strtr($cnd, $replaceinttostr) . se_stat_randchr(1, 0);
  for ($i = 1; $i <= $cnd; $i++)
    $genregkey .= se_stat_randchr($fill_d[$i]) . strtr($domain[$i - 1], $znakstr);

  $genregkey .= se_stat_randchr(9);
  // Кодируем дату
  for ($i = 1; $i <= 8; $i++)
    $genregkey .= se_stat_randchr($fill_p[$i]) . strtr($date[$i - 1], $replaceinttostr);

  $genregkey .= se_stat_randchr(7);
  // Кодируем тип лицензии
  $genregkey .= strtr($cnl, $replaceinttostr) . se_stat_randchr(1, 0);
  for ($i = 1; $i <= $cnl; $i++)
    $genregkey .= se_stat_randchr($fill_l[$i]) . $license[$i - 1];

  // Дописываем ключ
  $genregkey .= se_stat_randchr(5);

  return $genregkey . "?" . strtr(strlen($genregkey), $replaceinttostr);
}

// Декодирование инфы
function se_stat_decoderregkey($regkey)
{
  $_domain = "";
  $_date = "";
  $_license = "";
  $_error = "";

  $fill_d = array(1 => 5, 7, 4, 3, 9, 8, 5, 9, 2, 3, 7, 4, 7, 5, 4, 2, 6, 5, 7, 6, 4, 5, 2, 9, 4, 6, 8, 6, 3, 8, 9, 8, 4, 3, 7, 1, 3, 1, 5, 6, 3, 2, 3, 8, 2, 1, 5, 1, 9, 4, 5, 7, 4, 3, 9, 8, 5, 9, 2, 3, 7, 4, 7, 5, 4, 2, 6, 5, 7, 6, 4, 5, 2, 9, 4, 6, 8, 6, 3, 8, 9, 8, 4, 3, 7, 1, 3, 1, 5, 6, 3, 2, 3,
    8, 2, 1, 5, 1, 9, 4);
  $fill_p = array(1 => 8, 9, 5, 2, 9, 3, 4, 8);
  $fill_l = array(1 => 6, 5, 7, 9, 2, 1, 9, 8, 6, 5);
  $replacestrtoint = array("X" => '0', "Z" => '1', "s" => '2', "E" => '3', "t" => '4', "I" => '5', "O" => '6', "A" => '7', "q" => '8', "w" => '9');
  $strznak = array("T" => ".", "D" => "-", "I" => "|");

  $pos_ks = strrpos($regkey, "?");
  $_KS = substr($regkey, $pos_ks + 1);
  $KS = "";
  for ($i = 0; $i < strlen($_KS); $i++)
    $KS .= @$replacestrtoint[$_KS[$i]];

  $fk = preg_match("/[^a-z,^A-Z,^0-9,^?]+/i", $regkey);

  if (($pos_ks === false) || ((strlen($regkey) - 1 - strlen($_KS)) != $KS) || $fk > 0)
  {
    $_error = 1;
  }
  else
  {

    // Декодируем домен
    preg_match("/[a-z,A-Z]+/i", substr($regkey, 13), $dmatches);
    $cnd = "";
    for ($i = 0; $i < strlen($dmatches[0]); $i++)
      $cnd .= @$replacestrtoint[$dmatches[0][$i]];

    $l = 13 + strlen($dmatches[0]);
    $_domain = "";
    if (intval($cnd) <= 100)
      for ($i = 1; $i <= intval($cnd); $i++)
      {
        $l += $fill_d[$i] + 1;
        $_domain .= strtr(substr($regkey, $l, 1), $strznak);
      }

    // Декодируем дату
    $_date = "";
    $l += 9;
    for ($i = 1; $i <= 8; $i++)
    {
      $l += $fill_p[$i] + 1;
      $_date .= @$replacestrtoint[substr($regkey, $l, 1)];
    }

    // Декодируем лицензию
    $l += 7 + 1;
    preg_match("/[a-z,A-Z]+/i", substr($regkey, $l), $lmatches);

    $cnl = "";
    for ($i = 0; $i < strlen($lmatches[0]); $i++)
      $cnl .= @$replacestrtoint[$lmatches[0][$i]];

    $l += strlen($lmatches[0]);
    $_license = "";
    if (intval($cnl) <= 10)
      for ($i = 1; $i <= intval($cnl); $i++)
      {
        $l += $fill_l[$i] + 1;
        $_license .= substr($regkey, $l, 1);
      }

    $f = @strtotime($_date);
    if (($f == -1) || (!in_array($_license, array("demo", "lease", "owned"))))
    {
      $_domain = "";
      $_date = "";
      $_license = "";
      $_error = 1;
    }

  }

  return $_domain . ";" . $_date . ";" . $_license . ";" . $_error;
}

if (!empty($_POST["datetec"]))
  $datetec = @trim($_POST["datetec"]);
else
  $datetec = date("Ymd");

$domain = @trim($_POST["domain"]);
$period = @intval(trim($_POST["period"]));
$license = @trim($_POST["license"]);
$datefin = @date("Ymd", mktime(0, 0, 0, date("m", strtotime($datetec)) + $period, date("d", strtotime($datetec)), date("Y", strtotime($datetec))));
$regkey = @trim($_POST["regkey"]);
$test_regkey = @trim($_POST["test_regkey"]);

if (!empty($_POST["action"]) && ($_POST["action"] == "generate"))
{
  $generateregkey = se_stat_coderregkey($domain, $datefin, $license);
}

if (!empty($_POST["action"]) && ($_POST["action"] == "testkey"))
{

  if (!empty($test_regkey))
  {
    $data = split(";", se_stat_decoderregkey($test_regkey));
    $ldomain = $data[0];
    $ldate = $data[1];
    $llicense = $data[2];

    $errorkey = "";
    if (!empty($data[3]) && ($data[3] == 1))
    {
      $errorkey = "<tr><td colspan=2><font style=\"error\"><b>Не верный регистрационный ключ</b></font></td></tr>";
    }
  }
}


// Вход в систему
$err = 0;
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
  if ((trim(@$_POST["password"]) == 'cnfnbcnbrfhekbn') && (trim(@$_POST["login"]) == 'adminstat'))
    $err = 0;
  else
    $err = 1;

  if ($err == 0)
  {
    if ((trim(@$_POST["password"]) == 'cnfnbcnbrfhekbn') && (trim(@$_POST["login"]) == 'adminstat'))
      $hash = md5('adminstatcnfnbcnbrfhekbn');
    else
      $hash = md5(microtime() . @$_POST["login"] . @$_POST["password"]);

    if (@$_POST["store"] == "on")
      @setcookie("SESTATSESSION", $hash, time() + 86400 * 30);
    else
      @setcookie("SESTATSESSION", $hash);
    header("Location: http://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . "");
    exit();
  }
}

$authed = false;
$enterlogin = '';
if (!empty($_COOKIE["SESTATSESSION"]) && (strlen($_COOKIE["SESTATSESSION"])) == 32)
{
  if ($_COOKIE["SESTATSESSION"] == md5('adminstatcnfnbcnbrfhekbn'))
  {
    $authed = true;
    $enterlogin = md5('adminstatcnfnbcnbrfhekbn');
  }
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Статистика сайта SiteEdit Statistics - Генерация ключа</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
<!--
body,select,input,td { font-family: tahoma, sans-serif; font-size: 11px; }
a,a:visited { text-decoration: none; color: blue; }
a:hover { text-decoration: underline }
input,textarea { border: 1px solid #7F7F7F; }

.mbody { margin: 0px; }
.tdmaincenter { border: solid 1px #0E6BB7; background-color: #F9FCFF; }
.tblmain { background-color: #FFFFFF; background-position:top center; border: solid 1px #1A77BC; border-spacing: 0px; padding: 0px; width: 100%; height: 100%; }
.tbltitle td { background: #1A77BC; color: #FFFFFF; font-weight: bold; padding-top:5px; padding-bottom:5px; padding-left:5px; padding-right:3px; }
.treven td { background: #FFFFFF; padding-top:5px; padding-bottom:5px; padding-left:5px; padding-right:3px; }
.trodd td { background: #F9FCFF; padding-top:5px; padding-bottom:5px; padding-left:5px; padding-right:3px; }
.trsel td { background: #F0F4F8; padding-top:5px; padding-bottom:5px; padding-left:5px; padding-right:3px; }
.buttonsend { background-color: #7FAE1B; color: #FFFFFF; border: solid 1px #699216; font-weight: bold }
.hint { font-size:9px; color: #6E6E6E; }
.error { font-size:10px; color: red; }


//-->
</style>
</head>
<body>
<?php

if (!$authed)
{

?>
    <table class="tdmaincenter" border="0" cellspacing="0" cellpadding="0" align="center" width="300">
        <form action="" method="post" name="auth">
        <tr class="tbltitle">
            <td width="15%" align="center"><img src="img/key.gif" align="middle" border="0"></td>
            <td width="85%" align="center" style="font-size:12px; font-family:tahoma,sans-serif">
                <B>SiteEdit Statistics - Генерация ключа</B><br>Вход в систему</td></tr>
        <tr class="trodd">
            <td class="tdauthdata" align="center" colspan="2">
                <table border="0" cellspacing="0" cellpadding="0">
                <?=

  ($err == 1) ? '<tr><td colspan="2"><font class="error">Неверный логин или пароль</font></td></tr>' : "";

?>
                <tr><td>Логин: </td>
                    <td><input type="text" name="login" value="<?=

  @$_POST["login"]

?>"></td></tr>
                <tr><td>Пароль: </td>
                    <td><input type="password" name="password" value="<?=

  @$_POST["password"]

?>" /></td></tr>
                <tr><td colspan="2"><input type="checkbox" name="store" /> Сохранить на этом компьютере</td></tr>
                </table>
            </td>
        </tr>
        <tr class="trsel"><td colspan="2" align="center">
            <input class="buttonsend" type="submit" value="Войти">
        </td></tr>
        <input type="hidden" name="action" value="enter">
        </form>
    </table>
        <?php

}
else
{
  // Окончание проверки аутентификации


?>

<table align="left" width="100%" border="0" cellspacing="0" cellpadding="0">
<tr><td width="50%" align="left" valign="top">

<table class="tblmain" align="left" width="100%" border="0" cellspacing="5" cellpadding="1" >
<form name="frmgenerate" action="" method="post">
  <tr class="tbltitle"><td colspan=2><b>Данные для регистрации:</b></td></tr>
  <tr class="trodd">
    <td width="30%">Домен</td>
    <td><input name="domain" type="text" style="width:100%" value="<?=

  $domain

?>" />
        <br /><font class="hint">Домены записываются без "www".<br>Несколько доменов разделяются символом "|"</font></td>
  </tr>
  <tr class="treven">
    <td>Период (мес.)</td>
    <td><input name="period" type="text" style="width:100%" value="<?=

  $period

?>" /></td>
  </tr>
  <tr class="trodd">
    <td>Вид лицензии</td>
    <td>
        <select name="license" size="1" style="width:100%">
            <option value="demo" <?

  if ($license == 'demo')
    print "selected=\"selected\"";

?> >demo (пробный)</option>
            <option value="lease" <?

  if ($license == 'lease')
    print "selected=\"selected\"";

?> >lease (арендный)</option>
            <option value="owned" <?

  if ($license == 'owned')
    print "selected=\"selected\"";

?> >owned (собственный)</option>
        </select>
    </td>
  </tr>
  <tr class="treven">
    <td>Дата оплаты (yyyymmdd)</td>
    <td><input name="datetec" type="text" style="width:100%" value="<?=

  $datetec

?>"></td>
  </tr>
  <tr class="trsel">
    <td>&nbsp;</td>
    <td colspan="2"><input class="buttonsend" type="submit" name="generate" value="Генерировать ключ" /></td>
  </tr>
<input type="hidden" name="action" value="generate" />
</form>
  <tr class="trodd"><td colspan="2"><textarea name="regkey" rows="6" style="width:100%"><?

  if (!empty($generateregkey))
    print $generateregkey;
  else
    print $regkey;

?></textarea></td></tr>
</table>
<br />
<table class="tblmain" align="left" width="100%" border="0" cellspacing="5" cellpadding="1" >
<form name="frmtest" action="" method="post">
  <tr class="tbltitle"><td colspan="2"><b>Проверка ключа</b></td></tr>
  <tr class="trodd"><td colspan="2">
      <font class="hint">Для изменения ключа используйте данные старого ключа</font></td></tr>
  <tr class="trodd"><td colspan="2">
      <textarea name="test_regkey" rows="6" style="width:100%"><?=

  @$test_regkey

?></textarea></td></tr>
  <tr class="trsel"><td colspan="2"><input class="buttonsend" type="submit" name="test" value="Проверить"></td></tr>
  <tr ><td colspan="2"><hr align="center" noshade="noshade" size="1" color="#1A77BC"></td></tr>
  <?=

  @$errorkey

?>
  <tr class="trodd">
    <td width="30%">Домен</td>
    <td><?=

  !empty($ldomain) ? $ldomain : ''

?></td>
  </tr>
  <tr class="treven">
    <td>Тип лицензии</td>
    <td><?=

  !empty($llicense) ? $llicense : ''

?></td>
  </tr>
  <tr class="trodd">
    <td>Действителен до</td>
    <td><?=

  !empty($ldate) ? $ldate : ''

?></td>
  </tr>
<input type="hidden" name="action" value="testkey">
<input type="hidden" name="domain" value="<?=

  $domain

?>" />
<input type="hidden" name="period" value="<?=

  $period

?>" />
<input type="hidden" name="license" value="<?=

  $license

?>" />
<input type="hidden" name="regkey" value="<?

  if (!empty($generateregkey))
    print $generateregkey;
  else
    print $regkey;

?>" />
</form>
</table>

<?php

}

?>
</body>
</html>