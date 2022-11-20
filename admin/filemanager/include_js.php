<?php
ob_start();

include __DIR__.'/js/include.js';

$f = ob_get_contents();
ob_end_clean();

$f = preg_replace("/\"([a-zA-Z0-9\_]+)?\.php\?(.+?)\"/","\"?filemanager=$1&$2\"",$f); 
$f = preg_replace("/\"([a-zA-Z0-9\_]+)\/([a-zA-Z0-9\_]+)?\.php\?(.+?)\"/","\"?filemanager=$1&filemanageraction=$2&$3\"",$f);

header('Content-type: application/javascript');
header('Expires: Mon, 1 Jan 2001 00:00:00 GMT');

echo $f;
exit;