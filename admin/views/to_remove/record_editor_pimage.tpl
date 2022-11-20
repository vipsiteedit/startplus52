<form id="editImageForm" style="margin:0px;" method="post" enctype="multipart/form-data" action="/<?php echo $this->pagename ?>/?jqueryform=recordimage_prev">
<input type="hidden" id="partid" name="partid" value="<?php echo $section->id ?>">
<input type="hidden" id="recid" name="recid" value="<?php echo $record->id ?>">
<table border=0>
<tr><td align="center" valign="middle">
    <?php list($fname,$ext)  = explode('.', $record->image); ?>
	<img src="<?php echo '/'.SE_DIR.$fname.'_prev.'.$ext ?>" border=0>
</td></tr>
<tr><td>
	<div>&nbsp;</div>
</td></tr>
<tr><td>
	<input type="file" name="recimages[0]" id="add_img">
</td></tr>
<tr><td>
	<b>Alt-подсказка:</b><input  type="text" name="recimage_alt" class="pinput" value="<?php echo htmlspecialchars($record->image_alt) ?>">
</td></tr>

<tr><td>
	<input type="submit" value="Сохранить" name="GoToEditContent" id="sendData">
    <input type="button" value="Отмена" name="cancelEditData" id="cancelEditData">
</td></tr>
</table>
</form>