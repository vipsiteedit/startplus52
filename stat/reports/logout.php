<?php
//$r = mysql_query("DELETE FROM stat_adminsessions WHERE hash='".mysql_escape_string($_COOKIE["SESESSION"])."';");
setcookie("SESTATSESSION","");
header("Location: http://".$_SERVER['HTTP_HOST']);
exit();
?>