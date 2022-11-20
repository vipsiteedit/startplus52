<?php
import_request_variables("p", "ext_");
if (!(isset($ext_session, $ext_domain, $ext_fullsize, $ext_pktsize))) {
  header('HTTP/1.0 404');
  exit();
}

require_once "function.php";

$session = htmlspecialchars(addslashes($ext_session));
$domain = addslashes($ext_domain);
$fullsize = htmlspecialchars(addslashes($ext_fullsize));
$pktsize = htmlspecialchars(addslashes($ext_pktsize));

if (!checkSID($session)) exit("no");

$path=getcwd()."/data/";

$pktnum=ceil($fullsize / $pktsize);

//Файл идентификатора
$fname = $path.$session.".sid";
$f = fopen($fname, "a");
flock($f, LOCK_EX);
fputs($f, $fullsize."\n".$pktsize."\n".$pktnum);
fflush($f);
flock($f, LOCK_UN);
fclose($f);

//Файл данных
//$st = str_repeat(" ", $fullsize);
$st ="";
$fname = $path.$session.".dat";
$f = fopen($fname, "w");
flock($f, LOCK_EX);
fwrite($f, $st);
fflush($f);
flock($f, LOCK_UN);
fclose($f);

echo "ok";
?>