<div id="dialog-modal-logo<?php echo $this->unique; ?>" title="<?php $this->getTextLanguage('logo_edit', 'var'); ?>" class="modal-dialog">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        	<h4 class="modal-title"><?php $this->getTextLanguage('logo_edit', 'var'); ?></h4>
		</div>
		<div class="modal-body">
			<form class="form" action="" method="post">
						<div class="form-group">
							<script>
								function loadImage<?php echo $this->unique; ?>(img) {
									var nimg = $('<img/>').attr('src','<?php echo '/'.SE_DIR; ?>'+img).addClass('img-responsive').addClass('center-block');
									$('#logoimagediv<?php echo $this->unique; ?>').html(nimg);
								}
								function clearImage<?php echo $this->unique; ?>() {
									$('#logoimagediv<?php echo $this->unique; ?>').html('');
									$('#logoimage<?php echo $this->unique; ?>').val('');
								}
							</script>
							<div class="input-group">
								<input type="text" class="form-control" id="logoimage<?php echo $this->unique; ?>" name="logoimage" onchange="loadImage<?php echo $this->unique; ?>(this.value);" value="<?php echo htmlspecialchars(strval($textfield)) ?>">
							    <span class="input-group-btn">
							        <button data-event="image_select" data-subject="image" data-id="logoimage<?php echo $this->unique; ?>" class="btn btn-default" title="<?php echo $this->getTextLanguage('add','img') ?>">
										<img src="/admin/assets/icons/16x16/image_add.png">
										<?php echo $this->getTextLanguage('select') ?>
									</button>	
									<?php if($textfield != ''): ?>
									<button onclick="clearImage<?php echo $this->unique; ?>();" class="btn btn-warning" title="<?php echo $this->getTextLanguage('delete','img') ?>">
										<img src="/admin/assets/icons/16x16/image_delete.png">
										<?php echo $this->getTextLanguage('delete') ?>
									</button>
									<?php endif; ?>
							    </span>
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="col-xs-4 col-xs-offset-2">
							<input type="text" class="form-control input-sm" name="logo_width" placeholder="<?php echo $this->getTextLanguage('width', 'img') ?>">
						</div>
						<div class="col-xs-4">
							<input type="text" class="form-control input-sm" name="logo_height" placeholder="<?php echo $this->getTextLanguage('height', 'img') ?>">
						</div>
					</div>
			</form>				
			<hr>
			<div id="logoimagediv<?php echo $this->unique; ?>" class="text-center well">
			<?php if(!empty($textfield)): ?>
				<img class="img-responsive center-block" src="<?php echo '/'.SE_DIR.$textfield ?>" alt="<?php echo htmlspecialchars($section->image_alt) ?>">
			<?php endif; ?>
		</div>
		<div class="modal-footer">
			<button data-dismiss="modal" type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->getTextLanguage('close') ?></button>
			<button data-action="save" type="button" class="btn btn-primary" data-loading-text="<?php echo $this->getTextLanguage('saving_process') ?>"><?php echo $this->getTextLanguage('save') ?></button>
		</div>
	</div>

</div>