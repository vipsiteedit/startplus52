<form id="editPartSmileForm" style="margin:0px; display:inline-block;" method="post" action="/<?php echo $this->pagename ?>/?jqueryform=parttext">
	<input id="partid" type="hidden" name="partid" value="<?php echo $section->id ?>">
	<input id="formeditname" type="hidden" name="formeditname" value="contentText">
<table border=0 width="100%">
<tr><td>
		<textarea style="width:100%" id="editparttext" class="field_note" name="parttext" rows="20" cols="80"><?php echo htmlspecialchars($section->text) ?></textarea>
	</td></tr>
	<tr><td>
	<input type="submit" value="Сохранить" name="GoToEditContent" id="sendData">
    <input type="button" value="Отмена" name="editData" id="editData" onClick="seLoadBox('','.groupItem#group_<?php echo $section->id ?> .contentText','showsectiontext',<?php echo $section->id ?>)">
	</td></tr></table>
</form>