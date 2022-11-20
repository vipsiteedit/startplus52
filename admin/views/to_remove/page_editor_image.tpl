<div id="dialog-modal-image" title="<?php echo $this->getTextLanguage('edit_image'); ?>">
	<form id="html5uploader" method="post" action="/" onsubmit="return false;">
	<input id="iefile" type="file" name="files[]" onchange="alert($('#iefile').val()); handleFilesIE(this.value);"> <!-- handleFiles(this.files, false) -->
	<input type="file" name="files[]" onchange="handleFiles(this.files);"> <!-- handleFiles(this.files, false) -->
	<button id="fileupload">Send</button>
	</form>
	<div id="selectedFiles"></div>	
	<button id="rotateImg">rotate</button>
</div> 