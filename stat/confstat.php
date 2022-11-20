<?php
$STAT_CONF["dbname"] = "";
$STAT_CONF["sqlhost"] = "";
$STAT_CONF["sqluser"] = "";
$STAT_CONF["sqlpassword"] = "";

//$STAT_CONF["adminpassword"]="21232f297a57a5a743894a0e4a801fc3";
//$STAT_CONF["adminlogin"] = "admin";
//$STAT_CONF["adminpassword"] = "4a3b9b8de6489c966f211b94c7cbaadd";
$STAT_CONF["sqlserver"] = "MySql";

// Хранение и резервирование статистики.
$STAT_CONF["savelogday"] = "365";

// Параметры для графического счетчика.

// Тип счетчика:
// 0 - Невидимый GIF (1x1 pixel)
// 1 - PNG картинка. Фон картинки хранится в файле counter.png.
//     (Требуется наличие библиотеки GD, смотри http://www.php.net/manual/en/ref.image.php)
$STAT_CONF["graphtype"] = "0";

// Если картинка не прозрачная, то следующие три параметра определяют цвет символов в счетчике (соответственно компоненты R, G и B)
$STAT_CONF["graphinkR"] = "0";
$STAT_CONF["graphinkG"] = "0";
$STAT_CONF["graphinkB"] = "0";

// Не считать переходы для сетей excludeip/excludemask
$STAT_CONF["excludeip"] = "0.0.0.0";
$STAT_CONF["excludemask"] = "255.255.255.255";

// Разница во времени в часах
$STAT_CONF["timeoffset"] = "0";

// Отключение авторизации для SiteEdit Statistics
// yes - выключить
// no - не выключать
$STAT_CONF["disablepassword"] = "no";

// Присылать сообщения об ошибках на E-Mail (E-Mail установлен в переменной $STAT_CONF["reportmail"]
$STAT_CONF["senderrorsbymail"] = "yes";

?>