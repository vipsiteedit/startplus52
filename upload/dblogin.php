<?php
error_reporting(0);//125

date_default_timezone_set('Europe/Moscow');
require_once ("../system/config_db.php");
require_once ("../lib/lib_database.php");
se_db_dsn();
// Запустить базу данных
se_db_connect();
require_once ("../lib/lib.php");
require_once ("function.php");
upload_del_badfile();

$project = $_POST['project'];
$login = $_POST['login'];
$passw = $_POST['password'];;
$sol = '';//substr(md5('235rt'),0,5);
$sid = md5($login.date("U"));
if ($CONFIG['HostName'] == 'localhost') $hostname = 'siteedit.beget.ru';//getenv("SERVER_ADDR");//getenv("HTTP_X_Real_IP");
else $hostname = $CONFIG['HostName']; 
if ($project==$_SERVER['HTTP_HOST'] && !empty($login)){
    if (!file_exists(getcwd().'/data')) mkdir(getcwd().'/data');

    if ($login == md5($CONFIG['DBSerial'])){
	$res = explode(':',file_get_contents('http://e-stile.ru/admin/statusaccount.php?serial='.$CONFIG['DBSerial'].'&secret='.$passw));
	if ($res[0]=='host' && $res[1] == 'yes'){
	    $fp = fopen('data/'.$sid.'.sid', "w+");
	    fwrite($fp, $login);
	    fclose($fp);
	    echo base64_encode($CONFIG['DBName'].'|'.$CONFIG['DBUserName'].'|'.$CONFIG['DBPassword'].'|'.$hostname."|".date('d/m/Y H:i:s', time()).'|'.$sid).strtoupper($sol); exit;
	}
    }
    $uquery = se_db_query('SELECT u.username, u.password FROM user_admin ua INNER JOIN se_user u ON (u.id=ua.id_author)');
    while ($us = se_db_fetch_assoc($uquery)){
	if ($login == md5($us['username']) && $passw == $us['password']){
	    $fp = fopen('data/'.$sid.'.sid', "w+");
	    fwrite($fp, $login);
	    fclose($fp);
	    echo base64_encode($CONFIG['DBName'].'|'.$CONFIG['DBUserName'].'|'.$CONFIG['DBPassword'].'|'.$hostname."|".date('d/m/Y H:i:s', time()).'|'.$sid).strtoupper($sol); exit;
	}
    }
}

?>