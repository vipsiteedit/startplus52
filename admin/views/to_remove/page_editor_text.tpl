<div id="dialog-modal" title="<?php echo $this->getTextLanguage('Edit'); ?> &quot;<?php echo $this->getTextLanguage($namefield) ?>&quot;">
	<form style="margin:0px;" action="/<?php echo $this->pagename ?>/?jqueryform=sitevars">
	<fieldset>
		<input id="namefield" type="hidden" name="edittext" value="<?php echo $typefield.'_'.$namefield ?>">
		<textarea mce_editable="false" style="width:100%;" id="textfield" name="textfield" rows="10" cols="40"><?php echo htmlspecialchars($textfield) ?></textarea>
	</fieldset>	
	</form>
</div>