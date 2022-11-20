<?php 
	$title_dialog = $this->getTextLanguage('editor', 'var').': '.$namefield;
?>
<div id="dialog-modal-vars<?php echo $this->unique; ?>" title="<?php echo $title_dialog ?>" class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        	<h4 class="modal-title"><?php echo $title_dialog ?></h4>
		</div>
		<div class="modal-body">
			<form action="" method="post">
				<div class="form-group">
					<label class="control-label" for="newvalue"><?php echo $namefield; ?></label>
					<?php if($this->hasHiddenTags($textfield)): ?>
						<div class="alert alert-warning">
						  <h4><?php echo $this->getTextLanguage('warning', 'mes') ?></h4><?php echo $this->getTextLanguage('hidden_tags', 'mes') ?>
						</div>
					<?php endif; ?>
					<textarea data-texteditor="true" name="newvalue" id="newvalue<?php echo $this->unique; ?>"><?php echo htmlspecialchars(replace_values($textfield, false)); ?></textarea>
				</div>
			</form>
		</div>
		<div id="mess_dialog" class="modal-footer">
			<button data-dismiss="modal" type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->getTextLanguage('close') ?></button>
			<button data-action="save" type="button" class="btn btn-primary" data-loading-text="<?php echo $this->getTextLanguage('saving_process') ?>"><?php echo $this->getTextLanguage('save') ?></button>
		</div>
	</div>
</div>