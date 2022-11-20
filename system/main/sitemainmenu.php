<?php

function fmainmenu($typmenu = 0)
{
  	//$se = seData::getInstance();
	$menulist = simplexml_load_file(SE_ROOT.'/projects/' . SE_DIR . 'mainmenu.xml');
  	//$menulist = $se->menu[0];
  	$menu = new seMenu(seData::getInstance()->getPageName(), $menulist, false, $typmenu);
  	$result = $menu->execute();
	if (seData::getInstance()->editor->editorAccess() && !$_SESSION['siteediteditor']) {
	 	$result = '<div data-menu="mainmenu">'.$result.'</div>';
	}
	return $result;
}

