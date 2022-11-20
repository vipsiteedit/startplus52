<?php 
	require_once dirname(__FILE__)."/page_editor_menu.php";
?>
<div id="dialog-modal-menu<?php echo $this->unique; ?>" title="<?php echo $this->getTextLanguage('editor','menu') ?>" class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        	<h4 class="modal-title"><?php echo $this->getTextLanguage('editor','menu') ?></h4>
		</div>
		<div class="modal-body">
			<div class="tabbable">
				<ul class="nav nav-tabs">
					<li<?php if($editable=='mainmenu'): ?> class="active"<?php endif; ?>>
						<a href="#tabs-1<?php echo $this->unique; ?>" data-toggle="tab">
							<img src="/admin/assets/icons/16x16/text.png">
							<?php echo $this->getTextLanguage('main', 'menu') ?>
						</a>
					</li>
					<li<?php if($editable=='pagemenu'): ?> class="active"<?php endif; ?>>
						<a href="#tabs-2<?php echo $this->unique; ?>" data-toggle="tab">
							<img src="/admin/assets/icons/16x16/image.png">
							<?php echo $this->getTextLanguage('universal', 'menu') ?>
						</a>
					</li>
				</ul>
				<form action="" method="post">
					<form>
					<input name="mainmenutype" id="mainmenutype" type="hidden" value="<?php $this->mainmeny_type ?>">
					</form>
					<div class="row">
						<div class="col-xs-6">
							<div class="panel panel-default" data-type="pageMenuList">
								<div class="panel-heading"><?php echo $this->getTextLanguage('all_pages', 'menu') ?></div>
								<div class="panel-body">
									<ul class="draggable sortable">
										<?php foreach(getListMenuPages($this->pages) as $page): ?>
											<li data-name="item_<?php echo strval($page['name']) ?>">
												<div>
													<button class="btn btn-default btn-xs pull-right hidden" data-removeitem="true"><img src="/admin/assets/icons/16x16/cross.png"></button>
													<h5><?php echo $page['title'] ?>
														<span class="label label-default"><?php echo strval($page['name']) ?></span>
													</h5>
												</div>
											</li>
										<?php endforeach; ?>
									</ul>
								</div>
							</div>
						</div>
						<div class="col-xs-6">
							<div class="tab-content">
								<div class="tab-pane<?php if($editable=='mainmenu'): ?> active<?php endif; ?>" id="tabs-1<?php echo $this->unique; ?>">
									<div class="panel panel-default" data-type="pageMenuList">
										<div class="panel-heading"><?php echo $this->getTextLanguage('main', 'menu') ?></div>
										<div class="panel-body">
											<ul class="simple-sortable sortable">
												<?php foreach(getGroupMainMenu($this->mainmenu->item) as $page): ?>
													<li data-name="item_<?php echo strval($page['name']) ?>">
														<div>
															<button class="btn btn-default btn-xs pull-right" data-removeitem="true"><img src="/admin/assets/icons/16x16/cross.png"></button>
															<h5><?php echo $page['title'] ?>
																<span class="label label-default"><?php echo strval($page['name']) ?></span>
															</h5>
														</div>
													</li>
												<?php endforeach; ?>
											</ul>
										</div>
									</div>
								</div>
								<div class="tab-pane<?php if($editable=='pagemenu'): ?> active<?php endif; ?>" id="tabs-2<?php echo $this->unique; ?>">
									<div class="panel panel-default" data-type="pageMenuList">
										<div class="panel-heading"><?php echo $this->getTextLanguage('universal', 'menu') ?></div>
										<div class="panel-body">
											<ul class="nested-sortable sortable">
												<?php echo getGroupMenu($this->pagemenu->item) ?>
											</ul>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
		<div id="mess_dialog" class="modal-footer">
			<button data-dismiss="modal" type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->getTextLanguage('close') ?></button>
			<button data-action="save" type="button" class="btn btn-primary" data-loading-text="<?php echo $this->getTextLanguage('saving_process') ?>"><?php echo $this->getTextLanguage('save') ?></button>
		</div>
	</div>
</div>
