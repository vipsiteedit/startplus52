<div id="dialog-modal-confirm<?php echo $this->unique; ?>" title="<?php echo $this->getTextLanguage('confirmation') ?>" class="modal-dialog">
	<div class="modal-content">
		<div class="modal-header text-center">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        	<h4 class="modal-title"><?php echo $this->getTextLanguage('confirmation', 'mes') ?></h4>
		</div>
		<div class="modal-body text-center">
			<form action="" method="post">
			<input type="hidden" name="hashcode" value="<?php echo $hashcode; ?>">
			<div class="callout callout-double callout-<?php echo $status; ?>">
				<h4><?php echo $this->getTextLanguage($status, 'mes') ?></h4>
				<p><?php echo $this->getTextLanguage($message, 'mes') ?><?php if (!is_null($subject)) echo ' '.$this->getTextLanguage($subject, 'mes'); ?></p>
			</div>
			</form>
		</div>
		<div class="modal-footer">
			<div class="text-center">
				<button data-dismiss="modal" type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->getTextLanguage('no') ?></button>
				<button data-action="save" type="button" class="btn btn-<?php echo $status; ?>" data-loading-text="<?php echo $this->getTextLanguage('saving_process') ?>"><?php echo $this->getTextLanguage('yes') ?></button>
			</div>
		</div>
	</div>
</div>