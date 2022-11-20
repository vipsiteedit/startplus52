<?php
$menulist = $this->pagemenu;

function isGroupMenu($menulist, $name){
  $result = false;
  foreach($menulist as $menu){
		if (strval($menu['name']) == $name){ 
			return true; 
		}
		if (!empty($menu->item)) $result = isGroupMenu($menu->item, $name);
		if ($result) return true;
  }
  return $result;
}

function getGroupMenu($menulist){
  $result = '';
  foreach($menulist as $page){
	$result .= '<li data-name="item_'.strval($page['name']).'"><div>'
		.'<button class="btn btn-default btn-xs pull-right" data-removeitem="true"><img src="/admin/assets/icons/16x16/cross.png"></button>'
		.'<h5>'.$page->title."\n"
		.'<span class="label label-default">'.strval($page['name']).'</span>'."\n"
		.'</h5></div>';
	if (!empty($page->item)) {
		$result .= '<ul>';
		$result .= getGroupMenu($page->item);
		$result .= '</ul>';
	}
	$result .= '</li>'."\n";
  }
  return $result;
}

function getGroupMainMenu($menulist){
  $pageslist = array();
  foreach($menulist as $page){
    $pageslist[] = array('name'=>strval($page['name']), 'title'=>$page->title);
    //$result .= "<li class=\"groupMainMenu\" style=\"cursor: move; margin-bottom:2px;\" id=\"group_".str_replace('-','I',strval($page['name']))."\"><span>{$page->title} (".strval($page['name']).")</span></li>\n";
  }
  return $pageslist;
  //return $result;
}



function getListMenuPages($pages) {
	//$this->pagemenu->item
	$pageslist = array();
	foreach($pages as $page){
		//if (!isGroupMenu($menu, strval($page['name']))){
			$pageslist[] = array('name'=>strval($page['name']), 'title'=>$page->title);
		//}
	}
	return $pageslist;
}
?>