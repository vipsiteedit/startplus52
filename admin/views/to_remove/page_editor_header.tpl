<div id="dialog-modal-page" title="Поисковая оптимизация страницы <?php echo $this->pagename ?>">
<form id="editpageheaderform" name="editpageheaderform" style="margin:0px;" method="post" action="/<?php echo $this->pagename ?>/?jqueryform=pageedit">
 <table border="0" cellpadding="0" cellspacing="0" width="100%" id="toptab">
  <tr valign="top" align="left"><td>
	<div class="ttltab">
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr valign="middle" align="left">
					<td width="130px" align="right" class="ttltd">Заголовок:&nbsp;</td>
					<td colspan="3"><input  type="text" name="titlepage" class="pinput" value="<?php echo htmlspecialchars($this->page->titlepage) ?>"></td>
				</tr>
				<tr valign="middle" align="left">
					<td width="130px" align="right" class="ttltd">Ключевые слова:&nbsp;</td>
					<td colspan="3"><input  type="text" name="keywords" class="pinput" value="<?php echo htmlspecialchars($this->page->keywords) ?>"></td>
				</tr>
				<tr valign="middle" align="left">
					<td width="130px" align="right" class="ttltd">Описание:&nbsp;</td>
					<td colspan="3"><input  type="text" name="description" class="pinput" value="<?php echo htmlspecialchars($this->page->description) ?>"></td>
				</tr>
			</table>   
	</div>
			
	</td></tr> 

 <tr valign="top" align="left">
  <td class="ttltd"><div>Блок HEAD</div>
		<textarea class="field_head" name="pagehead" rows="10" cols="40"><?php echo htmlspecialchars($this->page->head) ?></textarea>
  </td>
 </tr>
 <tr valign="top" align="left">
  <td class="ttltd"><div>Блок JavaScript</div>
		<textarea class="field_head" name="localjavascripthead" rows="10" cols="40"><?php echo htmlspecialchars($this->page->vars->localjavascripthead) ?></textarea>
  </td>
 </tr> 
 </table><input type="hidden" value="yes" name="GoToEditContent">
 </form>
</div> 