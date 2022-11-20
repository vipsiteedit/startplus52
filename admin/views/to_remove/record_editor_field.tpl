<form id="editFieldForm" style="margin:0px; display:block-inline;" method="post" action="/<?php echo $this->pagename ?>/?jqueryform=recordfield">
	<input type="hidden" id="partid" name="partid" value="<?php echo $section->id ?>">
	<input type="hidden" id="recid" name="recid" value="<?php echo $record->id ?>">
	<input onclick="focus();" autocomplete="off" name="recfield" value="<?php echo htmlspecialchars($record->field) ?>">
</form>