<?php
$pageslist = array();
foreach($this->pages as $page){
		$pageslist[] = array('name'=>strval($page['name']), 'title'=>$page->title);
}

?>
<div id="dialog-modal-page" title="Выбрать страницу сайта">
<table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%" id="toptab">
<tr valign="top" align="left">
  <td>
  <ul class="menuPages" style="width: 100%; height: 100%; overflow:auto;">
  <?php foreach($pageslist as $page): ?>	
		<li class="itemMenu">
		<?php if(strval($page['name']) == $this->getPageName()): ?>
			<a id="ui-active-menuitem" href="/<?php echo $page['name'] ?>/"><?php echo $page['title'] ?> (<?php echo $page['name'] ?>)</a>
		<?php else: ?>
		<a href="/<?php echo $page['name'] ?>/"><?php echo $page['title'] ?> (<?php echo $page['name'] ?>)</a>
		<?php endif; ?>
		</li>
  <?php endforeach; ?>
  </ul>
   </td>
</tr>
 </table> 
</div>