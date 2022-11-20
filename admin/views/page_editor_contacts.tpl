<div id="dialog-modal-contacts<?php echo $this->unique; ?>" title="<?php echo $this->getTextLanguage('contacts_editor', 'var'); ?>" class="modal-dialog">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        	<h4 class="modal-title"><?php echo $this->getTextLanguage('contacts_editor', 'var'); ?></h4>
		</div>
		<div class="modal-body">
			<form class="form" action="" method="post">
				<div class="form-group">
					<label class="control-label" for="sitecompany"><?php echo $this->getTextLanguage('contacts_fullname', 'var'); ?></label>
					<div>
						<input type="text" name="sitecompany" class="form-control" value="<?php echo htmlspecialchars($this->prj->vars->sitecompany) ?>">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label" for="sitesmallcompany"><?php echo $this->getTextLanguage('contacts_shortname', 'var'); ?></label>
					<div>
						<input type="text" name="sitesmallcompany" class="form-control" value="<?php echo htmlspecialchars($this->prj->vars->sitesmallcompany) ?>">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label" for="sitephone"><?php echo $this->getTextLanguage('contacts_phone', 'var'); ?></label>
					<div>
						<input type="text" name="sitephone" class="form-control" value="<?php echo htmlspecialchars($this->prj->vars->sitephone) ?>">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label" for="sitefax"><?php echo $this->getTextLanguage('contacts_fax', 'var'); ?></label>
					<div>
						<input type="text" name="sitefax" class="form-control" value="<?php echo htmlspecialchars($this->prj->vars->sitefax) ?>">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label" for="sitemail"><?php echo $this->getTextLanguage('contacts_email', 'var'); ?></label>
					<div>
						<input type="text" name="sitemail" class="form-control" value="<?php echo htmlspecialchars($this->prj->vars->sitemail) ?>">
            <p class="help-block"><?php echo $this->getTextLanguage('contacts_email_info', 'var'); ?></p>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label" for="sitepostcode"><?php echo $this->getTextLanguage('contacts_postindex', 'var'); ?></label>
					<div>
						<input type="text" name="sitepostcode" class="form-control" value="<?php echo htmlspecialchars($this->prj->vars->sitepostcode) ?>">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label" for="siteregion"><?php echo $this->getTextLanguage('contacts_country', 'var'); ?></label>
					<div>
						<input type="text" name="siteregion" class="form-control" value="<?php echo htmlspecialchars($this->prj->vars->siteregion) ?>">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label" for="sitelocality"><?php echo $this->getTextLanguage('contacts_city', 'var'); ?></label>
					<div>
						<input type="text" name="sitelocality" class="form-control" value="<?php echo htmlspecialchars($this->prj->vars->sitelocality) ?>">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label" for="siteaddr"><?php echo $this->getTextLanguage('contacts_address', 'var'); ?></label>
					<div>
						<input type="text" name="siteaddr" class="form-control" value="<?php echo htmlspecialchars($this->prj->vars->siteaddr) ?>">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label" for="adminmail"><?php echo $this->getTextLanguage('contacts_emailadmin', 'var'); ?></label>
					<div>
						<input type="email" name="adminmail" class="form-control" value="<?php echo htmlspecialchars($this->prj->vars->adminmail) ?>">
						<p class="help-block"><?php echo $this->getTextLanguage('contacts_emailadmin_info', 'var'); ?></p>
					</div>
				</div>
			</form>
		</div>
		<div class="modal-footer">
			<button data-dismiss="modal" type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->getTextLanguage('close') ?></button>
			<button data-action="save" type="button" class="btn btn-primary" data-loading-text="<?php echo $this->getTextLanguage('saving_process') ?>"><?php echo $this->getTextLanguage('save') ?></button>
		</div>
	</div>
</div>