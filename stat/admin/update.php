<?php
$action = $_POST["action"];

// Обновление словарей данных
if ($action == 'upddatas') {
    if ($CONFIG['regkey_license'] == 'demo') {
       	$messupddatas = "<font color=red><b>Вы используете бесплатную demo версию системы.<br>Обновление словарей данных недоступно при использовании бесплатного регистрационного ключа</b></font>";

    }else{

    $updd_qstr = "";
    $fp = fsockopen(gethostbyname('www.siteedit.ru'), 80, $errno, $errstr, 30);
    if (!$fp) {
        $messupddatas = "$errstr ($errno) \n";
    }else{
        fputs ($fp, "GET /modules/statisticsupdate/updates.php?upd=datas HTTP/1.0\r\nHost: www.siteedit.ru\r\n\r\n");
        while (!feof($fp)) $updd_qstr .= fgets($fp);
        fclose ($fp);
    }
    if (!empty($updd_qstr)) {
        preg_match("/\<upddatas\>.+\<\/upddatas\>/", substr($updd_qstr, strpos($updd_qstr, "\r\n\r\n")), $updd_arrstr);
        $updd_str = str_replace(array("\<upddatas\>","\<\/upddatas\>"), array("",""), $updd_arrstr[0]);
        $updd_data = explode(";;", $updd_str);
        for ($i=0; $i < count($updd_data); $i++) $updd_tmp_data[] = explode("::", trim($updd_data[$i]));
        for ($i=0; $i < count($updd_tmp_data)-1; $i++){
            $updd_datas_new[$i]['id'] = trim($updd_tmp_data[$i][0]);
            $updd_datas_new[$i]['type'] = trim($updd_tmp_data[$i][1]);
            $updd_datas_new[$i]['name'] = trim($updd_tmp_data[$i][2]);
            $updd_datas_new[$i]['d1'] = trim(mysql_escape_string($updd_tmp_data[$i][3]));
            $updd_datas_new[$i]['d2'] = trim($updd_tmp_data[$i][4]);
            $updd_datas_new[$i]['d3'] = trim($updd_tmp_data[$i][5]);
        }
    }

    if (!empty($updd_datas_new)&&(count($updd_datas_new)>0)) {
        mysql_query("TRUNCATE TABLE `stat_datas`");
        for ($i=0; $i < count($updd_datas_new); $i++){
            mysql_query("INSERT INTO `stat_datas` ( `id` , `type` , `name` , `d1` , `d2` , `d3` )
                         VALUES ('".$updd_datas_new[$i]['id']."', '".$updd_datas_new[$i]['type']."', '".$updd_datas_new[$i]['name']."',
                                 '".$updd_datas_new[$i]['d1']."', '".$updd_datas_new[$i]['d2']."', '".$updd_datas_new[$i]['d3']."');");
        	if (mysql_errno()!=0) {
	        	$messupddatas = "<font color=red><b>Во время обновления произошла ошибка:</b> ".mysql_error()."<br>
                                 <b>Попробуйте обновить еще раз</b></font>";
    		    die();
        	}else $messupddatas = "<b><font color=green>Словари данных успешно обновлены!</font></b>";
        }
        mysql_query("UPDATE `stat_config` SET `value` = '".date("Y-m-d H:i:s")."' WHERE CONVERT( `variable` USING utf8 ) = 'dataupdate' LIMIT 1;");
    }

    }

}

// Обновлений системы
if ($action == 'updsystem') {
    $upds_qstr = "";
    $fp = fsockopen(gethostbyname('www.siteedit.ru'), 80, $errno, $errstr, 30);
    if (!$fp) {
        $messupdsys = "$errstr ($errno) \n";
    }else{
        fputs ($fp, "GET /modules/statisticsupdate/updates.php?upd=system HTTP/1.0\r\nHost: www.siteedit.ru\r\n\r\n");
        while (!feof($fp)) $upds_qstr .= fgets($fp);
        fclose ($fp);
    }
    if (!empty($upds_qstr)) {
        preg_match("/\<updsystem\>.+\<\/updsystem\>/", substr($upds_qstr, strpos($upds_qstr, "\r\n\r\n")), $upds_arrstr);
        $upds_str = str_replace(array("\<updsystem\>","\<\/updsystem\>"), array("",""), $upds_arrstr[0]);

        $upds_text = $upds_str;
    }


}

// Проверка обновлений словарей данных
$qstr = "";
@$fp = fsockopen(gethostbyname('www.siteedit.ru'), 80, $errno, $errstr, 30);
if (!$fp) {
    $messupd = "$errstr ($errno) \n";
}else{
    fputs ($fp, "GET /modules/statisticsupdate/updates.php HTTP/1.0\r\nHost: www.siteedit.ru\r\n\r\n");
    while (!feof($fp)) $qstr .= fgets($fp);
    fclose ($fp);
}
if (!empty($qstr)) {
    substr($qstr, strpos($qstr, "\r\n\r\n"));

    preg_match("/\<datas\>.+\<\/datas\>/", substr($qstr, strpos($qstr, "\r\n\r\n")), $arrstr);
    $str = str_replace(array("<datas>","</datas>"), array("",""), $arrstr[0]);
    $data = explode(";;", $str);
    for ($i=0; $i < count($data); $i++) $tmp_data[] = explode("::", trim($data[$i]));
    for ($i=0; $i < count($tmp_data)-1; $i++) $datas_new[trim($tmp_data[$i][0])] = intval(trim($tmp_data[$i][1]));
}
// ---

// загрузка конфигурации системы
$resconf = mysql_query("SELECT * FROM stat_config");
while ($rowconf = mysql_fetch_array($resconf, MYSQL_BOTH)) $CONFIG[$rowconf['variable']] = $rowconf['value'];

$rd = mysql_query("SELECT type, COUNT(id) AS `cn` FROM stat_datas GROUP BY type;");
while ($rowd = mysql_fetch_array($rd, MYSQL_BOTH)) $datas[$rowd['type']] = $rowd['cn'];


?>
<table class='tblval_report' border=0 width=100%>
<tr class="trodd"><td align="left" width=150><b>Версия системы:</b></td><td align="left">SiteEdit Statistics <?= $CONFIG["version"] ?></td></tr>
<tr class="treven"><td align="left" width=150><b>Последнее обновление:</b></td><td align="left"><?= date($CONFIG["datetime_format"], strtotime($CONFIG["dataupdate"])) ?></td></tr>
<?php
?>
</table>
<br>
<table class='tblval_report' border=0 width=100%>
<form name="frmupdsys" action="" method='post' enctype='multipart/form-data'>
<tr class="tbltitle"><td colspan="2" align="center"><b>Обновление системы</b></td></tr>
<?php
if (!empty($upds_text)) {
    print "<tr class='trodd'><td colspan=2 align='left'>".$upds_text."</td></tr>
           <tr class='trsel'><td colspan=2 align='right' width=150><input type='submit' value='Загрузить обновления'></td></tr>";
}else{
    print "<tr class='trodd'>
    <td align='left'>Вы можете проверить наличие новой версии системы на сайте разработчиков</td>
    <td align='right' width=150><input type='submit' value='Проверить обновления'></td>
    </tr>";
}

?>
<input type=hidden name='action' value='updsystem'>
</form>
</table>
<br>
<table class='tblval_report' border=0 width=100%>
<form name="frmupddatas" action="" method='post' enctype='multipart/form-data'>
<tr class="tbltitle"><td colspan="3" align="center"><b>Обновление словарей данных</b></td></tr>
<tr class="tbltitle"><td align="center"><b>Категория данных</b></td><td align="center"><b>Сейчас в системе</b></td><td align="center"><b>Доступно для обновления</b></td></tr>
<?php
$inf = array('br' => 'Браузеры',
             'os' => 'Операционные системы',
             'ss' => 'Поисковые системы',
             'ct' => 'Каталоги',
             'tp' => 'Рейтинги',
             'rb' => 'Роботы',);

if (!empty($datas))
foreach ($datas as $key => $value) {
    if ($class != "trodd") $class = "trodd"; else $class = "treven";
    print "<tr class=".$class.">
           <td width=40% valign='top'>".$inf[$key]."</td>
           <td width=30% valign='top'><b>".$datas[$key]."</b></td>
           <td width=30% valign='top'>";
    if ((!empty($datas_new[$key]))&&($datas_new[$key] > $datas[$key]))
        print "<b><font color=#009100>".$datas_new[$key]."</font></b></td></tr>";
    else
        print "<b>".$datas_new[$key]."</b></td></tr>";
}

if (!empty($messupddatas))
    print "<tr class='trsel'><td colspan=3 align='center'><b><font color=green>".$messupddatas."</font></b></td></tr>";
if (!empty($messupd))
    print "<tr class='trsel'><td colspan=3 align='center'><b><font color=green>".$messupd."</font></b></td></tr>";

?>
<tr class="trsel"><td colspan="3" align="right"><input type="submit" value="Обновить"></td></tr>
<input type=hidden name='action' value='upddatas'>
</form>
</table>
<?php
/*
<br>
<table class='tblval_report' border=0 width=100%>
<form name="frmupdall" action="" method='post' enctype='multipart/form-data'>
<tr class="tbltitle"><td colspan="2" align="center"><b>Пересчитать все данные в соответствии с новыми словарями</b><br><font class="hint">Может занять много времени!</font></td></tr>
<tr class="trsel"><td colspan="3" align="right"><input type="submit" value="Обновить"></td></tr>
<input type=hidden name='action' value='updall'>
</form>
</table>
*/
?>


<?php


/*
include('_functions.php');

require_once("../../system/conf_mysql.php");
if (!mysql_connect(HostName, DBUserName, DBPassword)) return;
else mysql_select_db(DBName);
/*
$link = mysql_connect("localhost", "admin", "") or die ("Нет соединения с хостом");
mysql_select_db('dbstatistics');
*/

/*///// ВАЖНО!!! НЕ УДАЛЯТЬ!!! проваодим обновление таблиц с учетом новых роботов ////////////////////////////////////////////////
$result = mysql_query("SELECT id, ip, agent, date, time, domain, request_uri, razdel, page, subpage, object, titlepage, existspage
                       FROM stat_log
                       WHERE agent='Research WEB crawler'
                       LIMIT 1000");
echo mysql_affected_rows();
if ($result) echo " добавлено";
else echo " нифига не сработало";

    mysql_query ("set character_set_client='cp1251'");
    mysql_query ("set character_set_results='cp1251'");
    mysql_query ("set collation_connection='cp1251_general_ci'");

while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    //print_r($row);echo "<br>";
    $trobot = getrobot($row['agent']);
    //print_r($trobot);echo "<br>";
    mysql_query("
    INSERT INTO stat_logrobots
           (`id_robot`, `ip`, `agent`,
            `date`, `time`,
            `domain`, `request_uri`, `razdel`, `page`, `subpage`, `object`, `titlepage`,
            `existspage`)
    VALUES ('".$trobot['id']."', '".$row['ip']."', '".$row['agent']."',
            '".$row['date']."', '".$row['time']."',
            '".$row['domain']."', '".$row['request_uri']."', '".$row['razdel']."', '".$row['page']."', '".$row['subpage']."', '".$row['object']."', '".$row['titlepage']."',
            '".$row['existspage']."');");
    mysql_query("DELETE FROM stat_log WHERE id='".$row['id']."'");
}
//////////////////////////////////////////////////////////////////////////////////*/

?>