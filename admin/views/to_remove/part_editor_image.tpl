<form id="editImageForm" style="margin:0px;" method="post" enctype="multipart/form-data" action="/<?php echo $this->pagename ?>/?jqueryform=partimage">
<input type="hidden" id="partid" name="partid" value="<?php echo $section->id ?>">
<table border=0>
<tr><td align="center" valign="middle">
	<img src="<?php echo '/'.SE_DIR.$section->image ?>" border=0>
</td></tr>
<tr><td>
	<div>&nbsp;</div>
</td></tr>
<tr><td>
	<input type="file" name="partimages[0]" id="add_img">
</td></tr>
<tr><td>
	<b>Alt-подсказка:</b><input  type="text" name="partimage_alt" class="pinput" value="<?php echo htmlspecialchars($section->image_alt) ?>">
</td></tr>

<tr><td>
	<input type="submit" value="Сохранить" name="GoToEditContent" id="sendData">
    <input type="button" value="Отмена" name="editData" id="editData" onClick="seLoadBox('','.groupItem#group_<?php echo $section->id ?> .contentImage','showsectionimage',<?php echo $section->id ?>)">
</td></tr>
</table>
</form> 