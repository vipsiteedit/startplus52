<form id="editPartSmileForm" style="margin:0px;" method="post" action="/<?php echo $this->pagename ?>/?jqueryform=parttitle">
	<input id="partid" type="hidden" name="partid" value="<?php echo $section->id ?>">
	<input id="formeditname" type="hidden" name="formeditname" value="contentTitle">
	<input onclick="focus();" type="text" autocomplete="off" style="width:99%; height: 23px; font-size: 14px;" id="parttitle" name="parttitle" value="<?php echo htmlspecialchars($section->title) ?>">
</form>