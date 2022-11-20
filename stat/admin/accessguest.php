<?php
$action = $_POST["action"];

if ($action == 1) {
	$guestaccess = htmlspecialchars($_POST["guestaccess"], ENT_QUOTES)=="on"?1:0;
	mysql_query("UPDATE stat_config SET `value`='".$guestaccess."' WHERE CONVERT( `variable` USING utf8 ) = 'guestaccess' LIMIT 1;");

    $str_access_report = "";
    foreach ($_POST as $k => $v)
        if ((substr($k, 0, 13) == 'accessreport_') && ($v == '1'))
            $str_access_report .= substr($k, 13, 2).",";
	mysql_query("UPDATE stat_config SET `value`='".$str_access_report."' WHERE CONVERT( `variable` USING utf8 ) = 'guestviewreports' LIMIT 1;");

}

$resconf = mysql_query("SELECT * FROM stat_config");
while ($rowconf = mysql_fetch_array($resconf, MYSQL_BOTH)) $stat_conf[$rowconf['variable']] = $rowconf['value'];

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
<tr class="tbltitle"><td colspan="2" align="center"><b>Гостевой вход</b></td></tr>
<tr class="trodd"><td width="60%">Разрешить гостевой вход SiteEdit Statistics</td><td width="40%"><?= YesNo("guestaccess",$stat_conf["guestaccess"]); ?></td></tr>

<tr class="tbltitle"><td><b>Название отчета</b></td><td><b>Доступ</b></td></tr>
<?php
$arr_access_report = explode(",", str_replace(" ", "", $stat_conf["guestviewreports"]));

foreach ($MENU_GROUPS as $i => $value) {
    for ($j=0; $j < count($MENU); $j+=4) {
        if (($MENU[$j] == $i) && ($MENU[$j+3] != "") && (!in_array($MENU[$j+1], $adminreports))) {
            if ($class != "trodd") $class = "trodd"; else $class = "treven";

            if (in_array($MENU[$j+3], $arr_access_report)) { $yes = "checked=\"checked\""; $no = ""; }
            else { $yes = ""; $no = "checked=\"checked\""; }

            print '<tr class="'.$class.'"><td>'.$MENU[$j+2].'</td><td>
                <input type="radio" name="accessreport_'.$MENU[$j+3].'" value="1" '.$yes.'>&nbsp;Да&nbsp;
                <input type="radio" name="accessreport_'.$MENU[$j+3].'" value="0" '.$no.'>&nbsp;Нет
            </td></tr>';
        }
    }
}
/*
for ($j=0; $j < count($MENU); $j+=4) {
    if (!empty($MENU[$j+3])) {
        if ($class != "trodd") $class = "trodd"; else $class = "treven";
        print '<tr class="'.$class.'"><td>'.$MENU[$j+2].'</td><td>
        <input type="radio" name="'.$MENU[$j+3].'" value="y" checked="checked">&nbsp;Да&nbsp;<input type="radio" name="'.$MENU[$j+3].'" value="n">&nbsp;Нет
        </td></tr>';
    }
}
*/
?>

<tr class="trsel"><td colspan="2" align="center"><input type="submit" value="Сохранить"></td></tr>
<input type="hidden" name="action" value="1">
</form>
</table>
