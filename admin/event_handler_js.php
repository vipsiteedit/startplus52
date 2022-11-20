<?php

require_once __DIR__.'/minifier.php';

ob_start();
include __DIR__.'/js/parse_str.js';
echo "\n";
include __DIR__.'/js/handler.js';
echo "\n";
include __DIR__.'/js/section_actions.js';
echo "\n";
include __DIR__.'/js/record_actions.js';
echo "\n";
include __DIR__.'/js/page_actions.js';
echo "\n";
include __DIR__.'/js/menu_actions.js';
echo "\n";
include __DIR__.'/js/vars_actions.js';
echo "\n";
include __DIR__.'/js/image_actions.js';
echo "\n";


$f = ob_get_contents();
ob_end_clean();

$minifiedCode = Minifier::minify(
	$f
);

header('Content-type: application/javascript');
header('Expires: Mon, 1 Jan 2001 00:00:00 GMT');
echo $minifiedCode;
exit;

?>