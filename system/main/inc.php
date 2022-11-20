<?php
date_default_timezone_set('Europe/Moscow');
define('SE_INDEX_INCLUDED', true);
session_start();
error_reporting(0);
chdir($_SERVER['DOCUMENT_ROOT']);
require_once 'system/main/init.php';