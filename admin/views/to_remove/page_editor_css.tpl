<?php 
	$csslist = array();
	$d=opendir(getCwd() . SE_DIR . "/skin/");
	if (!empty($d))
	while(($f=readdir($d))!==false) {
		if ($f=='.'||$f=='..' || strpos($f, '.map')===false) continue;
		list($csslist[],) = explode('.', $f);
	}
	closedir($d);
	$csslist = array_unique($csslist);
?>

<div id="dialog-modal-page" title="Настройка CSS страницы">
<form id="editpagecssform" style="margin:0px;" method="post" action="/<?php echo $this->pagename ?>/?jqueryform=pageedit">
<input type="hidden" id="thisPageDir" value="<?php echo SE_DIR ?>">
 <table border="0" cellpadding="0" cellspacing="0" width="100%" id="toptab">

 <tr valign="top" align="left">
  <td colspan=2 class="ttltd"><div>Редактор CSS</div>
		<textarea id="sitestyle" class="field_arr" name="style" rows="10" cols="40"><?php echo htmlspecialchars($this->page->style) ?></textarea>
  </td>
 </tr> 
 <tr><td colspan=2>&nbsp;</td></tr>
 <tr valign="middle" align="left">
					<td width="130px" align="right" class="ttltd">Выбрать шаблон:&nbsp;</td>
					<td><select name="css" id="pageCss">
						<?php foreach($csslist as $opt):?>
						<option value="<?php echo $opt ?>" <?php echo ($opt == $this->page->css) ? 'selected' : ''; ?>><?php echo $opt ?></option>
						<?php endforeach; ?>
						</select>
					</td>
 </tr> 
 </table>

 
 </form> 
</div>