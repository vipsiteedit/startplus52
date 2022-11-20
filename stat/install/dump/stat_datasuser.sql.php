<?php
if (substr($_SERVER['HTTP_HOST'],0,4)=='www.') $insdomain = substr($_SERVER['HTTP_HOST'],4);
else $insdomain = $_SERVER['HTTP_HOST'];
mysql_query("INSERT INTO `stat_datasuser` VALUES ('1', 'dm', 'www.".$insdomain."', '');") or die(mysql_error());
mysql_query("INSERT INTO `stat_datasuser` VALUES ('2', 'dm', '".$insdomain."', '');") or die(mysql_error());
?>