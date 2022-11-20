<?php

$se = seData::getInstance();
$skin = '/'.$se->getSkinService();

$titlepage = str_replace('&#124;','|',(empty($se->page->titlepage)) ? $se->page->title : $se->page->titlepage);
$keywords = str_replace('&#124;','|',(empty($se->page->keywords)) ? $se->prj->gkeywords : $se->page->keywords);
$description = str_replace('&#124;','|',(empty($se->page->description)) ? $se->prj->gdescription : $se->page->description);


if (empty($se->prj->vars->documenttype) || 1 == $se->prj->vars->documenttype || $se->prj->documenttype == 1){
  echo '<!DOCTYPE html>' . "\n";
} else {
  echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">' . "\n";
}
echo '<html id="'.$se->getPageName().'"><head><title>' . str_replace('"', '&quot;', strip_tags($titlepage)) . '</title>' . "\n";

echo '<base href="'._HOST_.$_SERVER['REQUEST_URI'].'">' . "\n";
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . "\n";
if ($se->prj->adaptive == 1) {
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">'."\n";
}
echo '<meta name="keywords" content="' . str_replace('"', '&quot;', strip_tags($keywords)) . '"> 
<meta name="description" content="' . str_replace('"', '&quot;', strip_tags($description)) . '">
<meta name="generator" content="CMS EDGESTILE SiteEdit">
';

if (class_exists('plugin_router'))
	echo '<link rel="canonical" href="' . plugin_router::getInstance()->getCanonical() . '">';

foreach($se->modulesCss as $css){
//    echo '<link href="' .$css.'" id="defaultCSS" rel="stylesheet" type="text/css">' . "\n";
}
if ($se->headercss) {
   echo join("\n", $se->headercss) . "\n";
}
echo '<link href="' .$skin. '/' . $se->page->css . '.css" id="defaultCSS" rel="stylesheet" type="text/css">' . "\n";


if (file_exists(getcwd() .$skin . '/skin_' . $se->getPageName() . '.css'))
{
  echo '<link href="' .$skin. '/skin_' . $se->getPageName() . '.css" id="pageCSS" rel="stylesheet" type="text/css">' . "\n";
}

if (file_exists(getcwd() . '/system/main/semenu.js'))
{
  $se->footer[] = '<script type="text/javascript" src="/system/main/semenu.js"></script>';
}

if (strval($se->page->style)!="")
    echo '<style type="text/css">' . $se->page->style . '</style>';
if (strval($se->page->head)!="")
    echo replace_link(str_replace('&#10;', "\n", $se->page->head)) . "\n";


if (!empty($se->page->vars->localjavascripthead)){
    echo replace_link(str_replace('&#10;', "\n", $se->page->vars->localjavascripthead));
    echo "\n";
} elseif(!empty($se->prj->vars->globaljavascripthead)) {
    echo replace_link(str_replace('&#10;', "\n", $se->prj->vars->globaljavascripthead));
    echo "\n";
}

if (strval($se->page->style)!=""){
    echo '<style type="text/css">' . $se->page->style . '</style>';
}
if (!empty($se->header)) {
    echo replace_link(str_replace('&#10;', "\n", join("\n", $se->header)));
    echo "\n";
}
if ($se->footerhtml) {
    $se->footer = array_merge(array($se->footerhtml), $se->footer);
}
echo '</head>' . "\n";
