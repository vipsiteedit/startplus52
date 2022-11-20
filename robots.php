<?php
/*
//
//	manager.php
//	adapted for robots.txt output
//	30.06.2011
//	zqaz
//
*/
function output($getfile) {
$ls = @file($getfile);
foreach ($ls as $line)
	echo $line;
}
$language = '';
$sitedir = '';
//$filename = preg_replace('\/','',$_GET['file']);
if (!isset($_GET['file'])) exit;

$filename = end(explode('/',$_GET['file']));

if (file_exists('hostname.dat') && filesize('hostname.dat') > 0) {
    if ($datastr = @file('hostname.dat')) {
	if (file_exists('sitelang.dat')) {
	    list($def) = file('sitelang.dat');
	} else $def = 'rus';
	
	foreach ($datastr as $line) {
	    if (trim($line) == '') continue;
	    list($host, $site) = explode("\t", $line);
	    if (trim($host) == ''){
	    	 $langswitch = trim($def);
	    }
	    if ($_SERVER['HTTP_HOST'] == trim($host) || $_SERVER['HTTP_HOST'] == 'www.' . trim($host)) {
		$langswitch = trim($site);
		break;
	    }
	}
	if (!empty($langswitch)) {
	    $sitedir = trim($langswitch) . '/';
	}
    }
} else {
   $langswitch = '';
}
$restrict = array('php','dat','htaccess','tar','gz','zip','tpl');
$ext = end(explode('.',$filename));

$getfile=0;
if (in_array($ext,$restrict)) {echo 'access denied'; exit;}
if (file_exists($sitedir.$filename)) $getfile = $sitedir.$filename;
elseif (file_exists($filename)) $getfile = $filename;
//elseif ($ext!='html') exit;

switch ($ext) {
case 'txt':header("Content-type: text/plain");output($getfile);break;
case 'ico':header("Content-type: image/x-icon");output($getfile);break;
case 'xml':		$fname = explode('.',$filename);
				if ($fname[0]=='sitemap') {
					header("Content-type: text/xml");
					output($getfile);
				}
				else echo 'access denied';exit;
				break;
case 'html':	if($getfile) {
				output($getfile);
				} else {
				$fname = explode('.',$filename);
				header('Location: http://'.$_SERVER['HTTP_HOST'].'/'.$fname[0].'/');
				}	
				break;
default:header("Content-type: text/html");break;
}


