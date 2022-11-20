<?php
mysql_query("INSERT INTO `stat_config` VALUES ('language', 'russian');") or die(mysql_error());
mysql_query("INSERT INTO `stat_config` VALUES ('mail_day', '0');") or die(mysql_error());
mysql_query("INSERT INTO `stat_config` VALUES ('mail_email', '".$STAT_CONF["adminemail"]."');") or die(mysql_error());
mysql_query("INSERT INTO `stat_config` VALUES ('mail_subject', 'SiteEdit Satistics Report [%d.%m.%Y]');") or die(mysql_error());
mysql_query("INSERT INTO `stat_config` VALUES ('mail_content', '7');") or die(mysql_error());
mysql_query("INSERT INTO `stat_config` VALUES ('version', '1.0');") or die(mysql_error());
mysql_query("INSERT INTO `stat_config` VALUES ('hints', '1');") or die(mysql_error());
mysql_query("INSERT INTO `stat_config` VALUES ('gauge', '1');") or die(mysql_error());
mysql_query("INSERT INTO `stat_config` VALUES ('percents', '1');") or die(mysql_error());
mysql_query("INSERT INTO `stat_config` VALUES ('graphic', '2');") or die(mysql_error());
mysql_query("INSERT INTO `stat_config` VALUES ('antialias', '1');") or die(mysql_error());
mysql_query("INSERT INTO `stat_config` VALUES ('date_format', 'd.m.Y');") or die(mysql_error());
mysql_query("INSERT INTO `stat_config` VALUES ('shortdate_format', 'm.Y');") or die(mysql_error());
mysql_query("INSERT INTO `stat_config` VALUES ('datetime_format', 'd.m.Y H:i:s');") or die(mysql_error());
mysql_query("INSERT INTO `stat_config` VALUES ('datetimes_format', 'd.m.Y H:i');") or die(mysql_error());
mysql_query("INSERT INTO `stat_config` VALUES ('shortdm_format', 'd.m');") or die(mysql_error());
mysql_query("INSERT INTO `stat_config` VALUES ('dataupdate', '".date("Y-m-d H:i:s")."');") or die(mysql_error());
mysql_query("INSERT INTO `stat_config` VALUES ('adminlogin', '".$STAT_CONF["adminlogin"]."');") or die(mysql_error());
mysql_query("INSERT INTO `stat_config` VALUES ('adminpassword', '".md5($STAT_CONF["adminpassword"])."');") or die(mysql_error());
mysql_query("INSERT INTO `stat_config` VALUES ('disablepassword', '0');") or die(mysql_error());
mysql_query("INSERT INTO `stat_config` VALUES ('savelogday', '".$STAT_CONF["savelogday"]."');") or die(mysql_error());
mysql_query("INSERT INTO `stat_config` VALUES ('timeoffset', '0');") or die(mysql_error());
mysql_query("INSERT INTO `stat_config` VALUES ('senderrorsbymail', '0');") or die(mysql_error());
mysql_query("INSERT INTO `stat_config` VALUES ('adminemail', '".$STAT_CONF["adminemail"]."');") or die(mysql_error());
?>