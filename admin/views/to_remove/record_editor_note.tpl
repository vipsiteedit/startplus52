<form id="editTextForm" style="margin:0px; display: inline-block;" method="post" action="/<?php echo $this->pagename ?>/?jqueryform=recordnote">
<table border=0 width=100%>
<tr><td>
	<input type="hidden" id="partid" name="partid" value="<?php echo $section->id ?>">
	<input type="hidden" id="recid" name="recid" value="<?php echo $record->id ?>">
		<textarea mce_editable="true" id="editrectext" style="width:100%" class="field_note" name="recnote" rows="20" cols="60"><?php echo htmlspecialchars($record->note) ?></textarea>
	</td></tr>
	<tr><td>
	<input type="submit" value="Сохранить" name="GoToEditContent" id="sendData">
    <input type="button" value="Отмена" name="cancelEditData" id="cancelEditData">
	</td></tr></table>
</form> 