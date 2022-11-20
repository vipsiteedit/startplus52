<?php
$action = $_POST["action"];

if ($action == 1) {

    // Для доменных имен
    if (!empty($_POST['deldm'])) {
        $deldm = $_POST['deldm'];
        mysql_query("DELETE FROM stat_datasuser WHERE (`id`='".$deldm."')AND(`type`='dm');");
    }
    if (!empty($_POST['adddm'])) {
        $adddmname = trim($_POST['adddmname']);
        if (!empty($adddmname))
            mysql_query("INSERT INTO stat_datasuser (`type`, `name`) VALUES ('dm', '".$adddmname."');");
    }

    // Для партнеров
    if (!empty($_POST['delpr'])) {
        $delpr = $_POST['delpr'];
        mysql_query("DELETE FROM stat_datasuser WHERE (`id`='".$delpr."')AND(`type`='pr');");
    }
    if (!empty($_POST['addpr'])) {
        $addprname = trim($_POST['addprname']);
        $addprd1 = trim(htmlspecialchars($_POST['addprd1'], ENT_QUOTES));
        $addprd1 = preg_replace("/\,/", " ", $addprd1);
        $addprd1 = preg_replace("/ +/", "|", trim($addprd1));
        $addprd1 = preg_replace("/\|+/", "|", $addprd1);
        if (!empty($addprname) && !empty($addprd1))
            mysql_query("INSERT INTO stat_datasuser (`type`, `name`, `d1`) VALUES ('pr', '".$addprname."', '".$addprd1."');");
    }

    // Для целей
    if (!empty($_POST['delcl'])) {
        $delpr = $_POST['delcl'];
        mysql_query("DELETE FROM stat_datasuser WHERE (`id`='".$delpr."')AND(`type`='cl');");
    }
    if (!empty($_POST['addcl'])) {
        $addclname = trim($_POST['addclname']);
        $addcld1 = trim($_POST['addcld1']);
        $addcld1 = preg_replace("/\,/", " ", $addcld1);
        $addcld1 = preg_replace("/ +/", "|", trim($addcld1));
        $addcld1 = preg_replace("/\|+/", "|", $addcld1);
        if (!empty($addclname) && !empty($addcld1))
            mysql_query("INSERT INTO stat_datasuser (`type`, `name`, `d1`) VALUES ('cl', '".$addclname."', '".$addcld1."');");
    }

}

$rd = mysql_query("SELECT type, COUNT(id) AS `cn` FROM stat_datas GROUP BY type;");
while ($rowd = mysql_fetch_array($rd, MYSQL_BOTH)) $datas[$rowd['type']] = $rowd['cn'];

$rdus = mysql_query("SELECT * FROM stat_datasuser;");
while ($rowus = mysql_fetch_array($rdus, MYSQL_BOTH)) {
    $datasus[$rowus['type']]['id'][] = $rowus['id'];
    $datasus[$rowus['type']]['name'][] = htmlspecialchars($rowus['name'], ENT_QUOTES);
    $datasus[$rowus['type']]['d1'][] = htmlspecialchars($rowus['d1'], ENT_QUOTES);
}

$hiddens = '
<input type="hidden" name="action" value="1">
';
?>

<table border=0 width=100% cellspacing=0 cellpadding=0><tr>
<td width=50% valign='top'>

<table class='tblval_report' border=0 width=100%>
<tr class="tbltitle"><td colspan="2" align="center"><b>Системные словари</b></td></tr>
<?php
$inf = array('br' => 'Браузеры',
             'os' => 'Операционные системы',
             'ss' => 'Поисковые системы',
             'ct' => 'Каталоги',
             'tp' => 'Рейтинги',
             'rb' => 'Роботы',);

foreach ($datas as $key => $value) {
    if ($class != "trodd") $class = "trodd"; else $class = "treven";
    print "<tr class=".$class.">
           <td width=50% valign='top'>".$inf[$key].":</td>
           <td width=50% valign='top'><b>".$datas[$key]."</b></td></tr>";
}
?>
</table>

</td><td width=5 valign='top'>&nbsp;
</td><td width=50% valign='top'>

<table class='tblval_report' border=0 width=100%>
<tr class="tbltitle"><td colspan="2" align="center"><b>Обновление</b></td></tr>
<tr class="trodd"><td align="left" width=150>Последнее обновление:</td><td align="left"><?= date($CONFIG["datetime_format"], strtotime($CONFIG["dataupdate"])) ?></td></tr>
<?php
?>
</table>

</td></tr></table>
<br>
<a name="domains"></a>
<table class='tblval_report' border=0 width=100%>
<tr class="tbltitle"><td colspan="2" align="center"><b>Доменные имена сайта</b></td></tr>
<?php
$i=0;
if (!empty($datasus['dm']['id'])) {
    for ($i=0; $i < count($datasus['dm']['id']); $i++) {
        if ($class != "trodd") $class = "trodd"; else $class = "treven";
	    print "<tr class=".$class.">
               <td width=90% valign='top'>".$datasus['dm']['name'][$i]."</td>
               <form action='index.php?".se_stat_sqs("","")."#domains' method='post' enctype='multipart/form-data'>
                   <td width=10% valign='top'><input type='submit' name='del' value='' title='Удалить' style='border:0px; background:url(img/del.gif); width:15;height:15; cursor:pointer;'></td>
                   <input type='hidden' name='deldm' value='".$datasus['dm']['id'][$i]."'>
                   ".$hiddens."
               </form>
               </tr>";
    }
}

print "<tr class='trsel'><td colspan=2 align='left'><u>Новое доменное имя</u></td></tr>";
if ($class != "trodd") $class = "trodd"; else $class = "treven";
print "<tr class=".$class.">
       <form action='index.php?".se_stat_sqs("","")."#domains' method='post' enctype='multipart/form-data'>
           <td width=90% valign='top'>Адрес домена:&nbsp;<input name='adddmname' type='text' value='' size=50></td>
           <td width=10% valign='top'><input type='submit' name='' value='' title='Добавить' style='border:0px; background:url(img/add.gif); width:15;height:15; cursor:pointer;'></td>
           <input type='hidden' name='adddm' value='1'>
           ".$hiddens."
       </form>
       </tr>";
?>
</table>
<br>
<a name="partners"></a>
<table class='tblval_report' border=0 width=100%>
<tr class="tbltitle"><td colspan="3" align="center"><b>Партнеры</b></td></tr>
<?php
$class = "";
$i=0;
if (!empty($datasus['pr']['id'])) {
    for ($i=0; $i < count($datasus['pr']['id']); $i++) {
        if ($class != "trodd") $class = "trodd"; else $class = "treven";
	    print "<tr class=".$class.">
               <td width=30% valign='top'>".$datasus['pr']['name'][$i]."</td>
               <td width=60% valign='top'>переход с сайта \"".str_replace("|", "\" или \"", $datasus['pr']['d1'][$i])."\"</td>
               <form action='index.php?".se_stat_sqs("","")."#partners' method='post' enctype='multipart/form-data'>
                   <td width=10% valign='top'><input type='submit' name='' value='' title='Удалить' style='border:0px; background:url(img/del.gif); width:15;height:15; cursor:pointer;'></td>
                   <input type='hidden' name='delpr' value='".$datasus['pr']['id'][$i]."'>
                   ".$hiddens."
               </form>
               </tr>";
    }
}

print "<tr class='trsel'><td colspan=3 align='left'><u>Новый партнер</u></td></tr>";
if ($class != "trodd") $class = "trodd"; else $class = "treven";
print "<tr class=".$class.">
       <form action='index.php?".se_stat_sqs("","")."#partners' method='post' enctype='multipart/form-data'>
           <td width=30% valign='top'>Имя партнера:<br><input name='addprname' type='text' value='' size=35></td>
           <td width=60% valign='top'>Сайт партнера:<br><input name='addprd1' type='text' value='' size=50>
               <br><font class='hint'>Укажите все варианты адреса сайта Вашего партнера через запятую или пробел (напр., partsite.ru, www.partsite.ru)</font></td>
           <td width=10% valign='top'><input type='submit' name='' value='' title='Добавить' style='border:0px; background:url(img/add.gif); width:15;height:15; cursor:pointer;'></td>
               <input type='hidden' name='addpr' value='1'>
               ".$hiddens."
       </form>
       </tr>";
?>
</table>
<br>
<a name="targets"></a>
<table class='tblval_report' border=0 width=100%>
<tr class="tbltitle"><td colspan="3" align="center"><b>Список целей</b></td></tr>
<?php
$class = "";
$i=0;
if (!empty($datasus['cl']['id'])) {
    for ($i=0; $i < count($datasus['cl']['id']); $i++) {
        if ($class != "trodd") $class = "trodd"; else $class = "treven";
	    print "<tr class=".$class.">
               <td width=30% valign='top'>".$datasus['cl']['name'][$i]."</td>
               <td width=60% valign='top'>переход на страницу \"".str_replace("|", "\" или \"", $datasus['cl']['d1'][$i])."\"</td>
               <form action='index.php?".se_stat_sqs("","")."#targets' method='post' enctype='multipart/form-data'>
                   <td width=10% valign='top'><input type='submit' name='' value='' title='Удалить' style='border:0px; background:url(img/del.gif); width:15;height:15; cursor:pointer;'></td>
                   <input type='hidden' name='delcl' value='".$datasus['cl']['id'][$i]."'>
                   ".$hiddens."
               </form>
               </tr>";
    }
}

print "<tr class='trsel'><td colspan=3 align='left'><u>Новая цель</u></td></tr>";
if ($class != "trodd") $class = "trodd"; else $class = "treven";
print "<tr class=".$class.">
       <form action='index.php?".se_stat_sqs("","")."#targets' method='post' enctype='multipart/form-data'>
           <td width=30% valign='top'>Название цели:<br><input name='addclname' type='text' value='' size=35></td>
           <td width=60% valign='top'>Переход на страницу:<br><input name='addcld1' type='text' value='' size=50>
               <br><font class='hint'>Укажите все варианты адреса страницы (через запятую или пробел), на которую посетитель должен перейти для
                                      достижения данной цели (напр., Название цели: Регистрация -> Переход на страницу: myregistration, registration?razdel=1&sub=1)</font></td>
           <td width=10% valign='top'><input type='submit' name='' value='' title='Добавить' style='border:0px; background:url(img/add.gif); width:15;height:15; cursor:pointer;'></td>
               <input type='hidden' name='addcl' value='1'>
               ".$hiddens."
       </form>
       </tr>";
?>
</table>