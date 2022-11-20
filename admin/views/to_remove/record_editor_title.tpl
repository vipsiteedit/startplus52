<form id="editTitleForm" style="margin:0px;" method="post" action="/<?php echo $this->pagename ?>/?jqueryform=recordtitle">
	<input type="hidden" id="partid" name="partid" value="<?php echo $section->id ?>">
	<input type="hidden" id="recid" name="recid" value="<?php echo $record->id ?>">
	<input onclick="focus();" autocomplete="off" style="width:100%" name="rectitle" id="rectitle" value="<?php echo htmlspecialchars($record->title) ?>">
</form>