<div id="dialog-modal-position<?php echo $this->unique; ?>" title="<?php echo $this->getTextLanguage('position', 'sec'); ?>" class="modal-dialog<?php if (count($sections)>8) echo ' modal-lg'; ?>">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        	<h4 class="modal-title"><?php echo $this->getTextLanguage('position', 'sec'); ?></h4>
		</div>
		<div class="modal-body">
			<div class="simple-sortable<?php if (count($sections)>8) echo ' sortable-grid'; ?>">
				<?php foreach ($sections as $section): ?>
				<div class="grid-item" data-id="<?php echo intval($section->id); ?>">
					<div class="panel panel-default">
					  <div class="panel-body">
					  	<h4 class="text-center">
					  	<?php echo intval($section->id); ?>
					  	<?php $title = htmlspecialchars(trim(strip_tags($section->title))); 
					  	if (!empty($title)) echo ': '.$title; ?>
					  	</h4>
					    <?php if (!empty($section->image)): ?>
					    	<img class="img-responsive center-block" src="<?php echo '/'.SE_DIR.$section->image; ?>">
					    <?php endif; ?>
					  </div>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="modal-footer">
			<button data-dismiss="modal" type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->getTextLanguage('close') ?></button>
			<button data-action="save" type="button" class="btn btn-primary" data-loading-text="<?php echo $this->getTextLanguage('saving_process') ?>"><?php echo $this->getTextLanguage('save') ?></button>
		</div>
	</div>
</div>