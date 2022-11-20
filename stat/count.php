<?php
// Вывод изображения
Header("Content-type: image/gif");
$im = imagecreatefromgif("se_stat_01.gif");
$background_color = imagecolorallocate($im, 0, 0, 0);
$cbg = imagecolorclosest($im, 0, 0, 0);
imagecolortransparent($im, $cbg);

require_once "../system/conf_mysql.php";

if (!mysql_connect(HostName, DBUserName, DBPassword)) {
    ImageGif($im);
    ImageDestroy($im);
	    return;
}else mysql_select_db(DBName);

if (isset($_COOKIE['statidus'])) {
//  imagettftext ($im, 20, 0, 10, 10, imagecolorallocate($im, 0, 0, 0), "arial.ttf", "!!!");

    $iduser = intval($_COOKIE['statidus']);
    $query = $_SERVER['QUERY_STRING'];
    parse_str($query);
    //echo $iduser."<br>".$width."X".$height."<br>".$pix."<br>".$java."<br>".$js."<br>".$timestart."<br>".$idlog."<br>";

    // --- Определяем скорось загрузки страницы ---
    list($usec, $sec) = explode(" ", microtime());
    $timestop = ((float)$usec + (float)$sec);
    if (!empty($timestart)) $page_rateload = floatval($timestop - $timestart);
    else $page_rateload = 0;

    // --- Обновляем запись в таблице посетителей ---
    mysql_query("
    UPDATE stat_users
    SET screensize = '".$width."x".$height."',
        colorsdepth = '".$pix."',
        java = '".$java."',
        javascript = '".$js."'
    WHERE id = '".$iduser."'");

    mysql_query("
    UPDATE stat_log
    SET page_rateload = '".$page_rateload."'
    WHERE id = '".$idlog."'");

    //imagettftext($im, 5, 0, 5, 28, imagecolorallocate($im, 0, 0, 0), "arial.ttf", $idlog);

}
$cn =  mysql_fetch_array(mysql_query("SELECT `views`,(SELECT SUM(`views`) FROM `stat_total` ) as `sumviews` FROM `stat_total` WHERE date = '".date("Ymd")."'"));
imagettftext($im, 6, 0, 5, 22, imagecolorallocate($im, 98, 98, 98), "arial.ttf", $cn[0]);
imagettftext($im, 6, 0, 5, 29, imagecolorallocate($im, 98, 98, 98), "arial.ttf", $cn[1]);
ImageGif($im);
ImageDestroy($im);
?>