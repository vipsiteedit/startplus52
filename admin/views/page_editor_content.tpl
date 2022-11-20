<?php
   include 'page_editor_content.php';
?>
<script>
	function secLoad<?php echo $this->unique; ?>(tab) {
		var order = $('[data-order]').first();
		if (order.length) {
			order = '&'+order.attr('data-order');
		} else {
			order = '';
		}
		var formdata = {name: 'editpage', get: tab, unique: "<?php echo $this->unique; ?>"};
//		console.log(formdata);
		$.ajax({
			url: seEvents.url+"?on_ajax_execute"+order,
			type: 'POST',
			data: formdata,
			success: function(data) {
//				console.log(data);
				//var sectlist = $(data).find('#'+tab+'<?php echo $this->unique; ?>').html();
				$('#'+tab+'<?php echo $this->unique; ?> .simple-sortable').html(data);
			}
		});
	}
	function secRemove<?php echo $this->unique; ?>(id, tab) {
		$('#'+tab+'<?php echo $this->unique; ?>').find('[data-id='+id+']').first().remove();
	}
</script>
<div id="dialog-modal-page<?php echo $this->unique; ?>" title="<?php echo $this->getTextLanguage('editor', 'page'); ?>" class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        	<h4 class="modal-title"><?php echo $this->getTextLanguage('editor', 'page'); ?>
			<?php if($this->pagename) echo ' "'.$this->pagename.'"' ?></h4>
		</div>
		<div class="modal-body">
			<div class="tabbable">
				<ul class="nav nav-tabs">
					<li<?php if(empty($this->pagename)): ?> class="active"<?php endif ?>>
						<a href="#tabs-1<?php echo $this->unique; ?>" data-toggle="tab">
							<img src="/admin/assets/icons/16x16/text.png">
							<?php echo $this->getTextLanguage('options', 'page'); ?>
						</a>
					</li>
					<?php if(!empty($this->pagename)): ?>
					<li class="active">
						<a href="#tabs-5<?php echo $this->unique; ?>" data-toggle="tab">
							<img src="/admin/assets/icons/16x16/modules.png">
							<?php echo $this->getTextLanguage('sections', 'page'); ?>
						</a>
					</li><?php endif ?>
					<li>
						<a href="#tabs-6<?php echo $this->unique; ?>" data-toggle="tab">
							<img src="/admin/assets/icons/16x16/modules.png">
							<?php echo $this->getTextLanguage('gsections', 'page'); ?>
						</a>
					</li>
					<li>
						<a href="#tabs-2<?php echo $this->unique; ?>" data-toggle="tab">
							<img src="/admin/assets/icons/16x16/text.png">
							<?php echo $this->getTextLanguage('seo', 'page'); ?>
						</a>
					</li>
					<li>
						<a href="#tabs-3<?php echo $this->unique; ?>" data-toggle="tab">
							<img src="/admin/assets/icons/16x16/image.png">
							<?php echo $this->getTextLanguage('meta', 'page'); ?>
						</a>
					</li>
					<li>
						<a href="#tabs-4<?php echo $this->unique; ?>" data-toggle="tab">
							<img src="/admin/assets/icons/16x16/image.png">
							<?php echo $this->getTextLanguage('vars', 'page'); ?>
						</a>
					</li>
				</ul>
				<form id="pageeditform" action="<?php echo $pagelink ?>" method="post">
				<input  type="hidden" name="thisnamepage" value="<?php echo $this->pagename ?>">
				<div class="tab-content">
					<div class="tab-pane<?php if(empty($this->pagename)): ?>  active<?php endif ?>" id="tabs-1<?php echo $this->unique; ?>">
						<div class="form-horizontal">
							<div class="form-group">
								<label class="control-label col-xs-4" for="title"><?php echo $this->getTextLanguage('title', 'page'); ?></label>
								<div class="col-xs-8">
									<input type="text" name="title" id="pagetitle<?php echo $this->unique; ?>" class="form-control" value="<?php echo htmlspecialchars($this->page->title) ?>">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-xs-4" for="namepage"><?php echo $this->getTextLanguage('name', 'page'); ?></label>
								<script>
									function transliteTitle<?php echo $this->unique; ?>() {
										var value = $('#pagetitle<?php echo $this->unique; ?>').val();
										var namepage = $("#namepage<?php echo $this->unique; ?>");
										namepage.val(translite(value));
									}
								</script>
								<div class="col-xs-8">
									<?php if($this->pagename != 'home'): ?>
									<div class="input-group">
										<input type="text" name="namepage" id="namepage<?php echo $this->unique; ?>" class="form-control" value="<?php echo $this->pagename ?>">
										<span class="input-group-btn">
											<button class="btn btn-default" type="button" onclick="transliteTitle<?php echo $this->unique; ?>();">
												<?php echo $this->getTextLanguage('transliterate', 'page'); ?>
											</button>
										</span>
									</div>
									
									<p class="help-block"><?php echo $this->getTextLanguage('transliterate_title', 'page'); ?></p>
									<?php else: ?>
										<p class="form-control-static"><?php echo $this->pagename ?></p>
										<input type="hidden" name="namepage" value="<?php echo $this->pagename ?>">
									<?php endif;?>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-xs-4"><?php echo $this->getTextLanguage('redirect', 'page'); ?></label>
								<div class="col-xs-8">
									<div class="input-group">
										<input type="text" name="urlpage" id="urlpage<?php echo $this->unique; ?>" class="form-control" value="<?php echo $this->page->url ?>">
										<div class="input-group-btn">
									        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><?php echo $this->getTextLanguage('select'); ?> <span class="caret"></span></button>
									        <ul class="dropdown-menu pull-right" id="pageselect<?php echo $this->unique; ?>">
									        	<?php foreach ($this->data->pages as $pagelink): ?>
									        		<li><a href="#" data-link="<?php echo SE_MULTI_DIR.'/'.$pagelink['name'].'/'; ?>"><?php echo $pagelink->title; ?></a></li>
									        	<?php endforeach; ?>
									        </ul>
									        <script>
									        	$('#pageselect<?php echo $this->unique; ?> [data-link]').click(function(e){
									        		e.preventDefault();
									        		var link = $(this).attr('data-link');
									        		$('#urlpage<?php echo $this->unique; ?>').val(link);
									        	});
									        </script>
									      </div>
									</div>
									<p class="help-block"><?php echo $this->getTextLanguage('redirect_title', 'page'); ?></p>
								</div>
							</div>
							<div class="form-group">
								<script>
									function loadImage<?php echo $this->unique; ?>(img, id) {
										var nimg = $('<img/>').attr('src','<?php echo '/'.SE_DIR; ?>'+img).addClass('img-responsive').addClass('center-block');
										$('#'+id).html(nimg);
									}
								</script>
								<label class="control-label col-xs-4"><?php echo $this->getTextLanguage('menu', 'page'); ?></label>
								<div class="col-xs-4">
									<div class="checkbox">
										<label>
											<input type="checkbox" name="checkmainmenu" <?php echo $mainmenu_checked ?>>
											<?php echo $this->getTextLanguage('menu_main', 'page'); ?>
										</label>
									</div>
									<div class="input-group">
										<input class="form-control" type="text" id="mainimage<?php echo $this->unique; ?>" name="mainimage" onchange="loadImage(this.value,'tt1<?php echo $this->unique; ?>');" value="<?php echo $page->mainimage ?>" align="left">
										<span class="input-group-btn">
											<button class="btn btn-default" type="button" data-event="image_select" data-subject="image" data-id="mainimage<?php echo $this->unique; ?>" class="btn btn-default" >
												<img src="/admin/assets/icons/16x16/image_edit.png">
											</button>
										</span>
									</div>
									<div id="tt1<?php echo $this->unique; ?>" class="well">
									<?php if(!empty($page->mainimage)): ?>
										<img class="center-block img-responsive" src="<?php echo '/'.SE_DIR ?><?php echo $page->mainimage ?>">
									<?php endif; ?>
									</div>
								</div>
								<div class="col-xs-4">
									<div class="checkbox">
										<label>
											<input type="checkbox" name="checkpagemenu" <?php echo $pagemenu_checked ?>>
											<?php echo $this->getTextLanguage('menu_universal', 'page'); ?>
										</label>
									</div>
									<div class="input-group">
										<input class="form-control" type="text" id="addsimage<?php echo $this->unique; ?>" name="addsimage" onchange="loadImage<?php echo $this->unique; ?>(this.value,'tt2<?php echo $this->unique; ?>');" value="<?php echo $page->addsimage ?>">
										<span class="input-group-btn">
											<button class="btn btn-default" type="button" data-event="image_select" data-subject="image" data-id="addsimage<?php echo $this->unique; ?>" class="btn btn-default" >
												<img src="/admin/assets/icons/16x16/image_edit.png">
											</button>
										</span>
									</div>
									<div id="tt2<?php echo $this->unique; ?>" class="well">
									<?php if(!empty($page->addsimage)): ?>
										<img class="center-block img-responsive" src="<?php echo '/'.SE_DIR ?><?php echo $page->addsimage ?>">
									<?php endif; ?>
									</div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-xs-4"><?php echo $this->getTextLanguage('index', 'page'); ?></label>
								<div class="col-xs-4">
									<div class="checkbox">
										<label>
											<input  type="checkbox" name="indexes" <?php echo ($page->indexes == '1')? 'checked':'' ?> value="on">
											<?php echo $this->getTextLanguage('index_enable', 'page'); ?>
										</label>
									</div>
								</div>
								<div class="col-xs-4">
									<div class="checkbox">
										<label>
											<input  type="checkbox" name="startpage" <?php echo ($this->prj->vars->startpage == $this->pagename && $this->pagename != '') ? 'checked': '' ?>>
											<?php echo $this->getTextLanguage('homepage', 'page'); ?>
										</label>
									</div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-xs-4"><?php echo $this->getTextLanguage('priority', 'page'); ?></label>
								<div class="col-xs-4">
									<input class="form-control" type="range" name="priority" value="<?php echo $page->priority ?>" min="1" max="5" step="0.1" onchange="$('#priorityval').html(this.value);">
								</div>
								<div class="col-xs-4">
									<p class="form-control-static" id="priorityval"><?php echo $page->priority ?></p>
								</div>
							</div>
							<?php if(SE_DB_ENABLE): ?>
							<div class="form-group">
								<label class="control-label col-xs-4"><?php echo $this->getTextLanguage('access'); ?></label>
								<div class="col-xs-4">
									<script>
										if('<?php echo intval($this->data->page->vars->groupslevel) ?>'=='0') $('#pageaccess').css('visibility','hidden'); else $('#pageaccess').css('visibility','visible');
									</script>
									<select class="form-control" name="pageaccesslevel" onchange="if(this.value==0) $('#pageaccess<?php echo $this->unique; ?>').css('visibility','hidden'); else $('#pageaccess<?php echo $this->unique; ?>').css('visibility','visible');">
										<option value="0"><?php echo $this->getTextLanguage('access_all'); ?></option>
										<option value="1"<?php if($this->data->page->vars->groupslevel==1) echo ' selected'; ?>><?php echo $this->getTextLanguage('user', 'access'); ?></option>
										<option value="2"<?php if($this->data->page->vars->groupslevel==2) echo ' selected'; ?>><?php echo $this->getTextLanguage('suser', 'access'); ?></option>
										<option value="3"<?php if($this->data->page->vars->groupslevel==3) echo ' selected'; ?>><?php echo $this->getTextLanguage('admin', 'access'); ?></option>
										<option value="4"<?php if($this->data->page->vars->groupslevel==4) echo ' selected'; ?>><?php echo $this->getTextLanguage('nouser', 'access'); ?></option>
									</select>
								</div>
								<div class="col-xs-4">
									<select class="form-control" id="pageaccess<?php echo $this->unique; ?>" name="pageaccess[]" multiple="multiple">
										<option value="0-"><?php echo $this->getTextLanguage('none', 'access'); ?></option>
											<?php
												$accessnamelist = explode(';', $this->data->page->vars->groupsname);
												$accessname = array();
												foreach($accessnamelist as $accname){
													$accessname[$accname] = ' selected';
												}
												@$groupusers = explode('|', $this->prj->groupusers);
												if (!empty($groupusers[0])){
												@$grouplist = explode(';', $groupusers[0]);
												foreach($grouplist as  $group){
													echo '<option value="1-'.$group.'"'.$accessname[$group].'>'.$group."</option>\n";
												}}
												if (!empty($groupusers[1])){
												@$grouplist = explode(';', $groupusers[1]);
												foreach($grouplist as  $group){
													echo '<option value="2-'.$group.'"'.$accessname[$group].'>'.$group."</option>\n";
												}}
												if (!empty($groupusers[2])){
												@$grouplist = explode(';', $groupusers[2]);
												foreach($grouplist as  $group){
													echo '<option value="3-'.$group.'"'.$accessname[$group].'>'.$group."</option>\n";
												}}
											?>
										</select>
								</div>
							</div>
							<?php else: ?>
								<input type="hidden" name="pageaccesslevel" value=""> 
							<?php endif; ?>
						</div>
					</div><!-- end tab-1 -->
					<div class="tab-pane" id="tabs-2<?php echo $this->unique; ?>">
						<div class="form-horizontal">
								<div class="form-group">
									<label class="control-label col-xs-2"><?php echo $this->getTextLanguage('title_tag', 'page'); ?></label>
									<div class="col-xs-10">
										<input type="text" name="titlepage" class="form-control" value="<?php echo htmlspecialchars($this->page->titlepage) ?>">
										<p class="help-block"><?php echo $this->getTextLanguage('title_tag_title', 'page'); ?></p>
									</div>
								</div>
								<div class="form-group">
									<label class="control-label col-xs-2"><?php echo $this->getTextLanguage('keywords', 'page'); ?></label>
									<div class="col-xs-10">
										<input type="text" name="keywords" class="form-control" value="<?php echo htmlspecialchars($this->page->keywords) ?>">
										<p class="help-block"><?php echo $this->getTextLanguage('keywords_title', 'page'); ?></p>
									</div>
								</div>
								<div class="form-group">
									<label class="control-label col-xs-2"><?php echo $this->getTextLanguage('description', 'page'); ?></label>
									<div class="col-xs-10">
										<textarea class="form-control" name="description" rows="4"><?php echo htmlspecialchars($this->page->description) ?></textarea>
										<p class="help-block"><?php echo $this->getTextLanguage('description_title', 'page'); ?></p>
									</div>
								</div>
								<div class="form-group">
									<label class="control-label col-xs-2"><?php echo $this->getTextLanguage('domain_name', 'page'); ?></label>
									<div class="col-xs-10">
										<input type="text" name="domainname" class="form-control" value="<?php echo htmlspecialchars($this->prj->domainname) ?>">
									</div>
								</div>
						</div>
					</div><!-- end tabs-2 -->
					<div class="tab-pane" id="tabs-3<?php echo $this->unique; ?>">
						<div class="form">
							<div class="form-group">
								<label class="control-label"><?php echo $this->getTextLanguage('block_head', 'page'); ?></label>
								<textarea title="<?php echo $this->getTextLanguage('block_head_title', 'page'); ?>" class="form-control code" name="pagehead" rows="10"><?php echo htmlspecialchars($this->page->head) ?></textarea>
							</div>
							<div class="form-group">
								<label class="control-label"><?php echo $this->getTextLanguage('block_javascript', 'page'); ?></label>
								<textarea title="<?php echo $this->getTextLanguage('block_javascript_title', 'page'); ?>" class="form-control code" name="localjavascripthead" rows="10"><?php echo htmlspecialchars($this->page->vars->localjavascripthead) ?></textarea>
							</div>
						</div>
					</div><!-- end tab-3 -->

					<script>

						function recLoadVars<?php echo $this->unique; ?>() {
							var formdata = {name: 'editpage', value: "", unique: "<?php echo $this->unique; ?>", getVars: "vars"};
							$.ajax({
								url: seEvents.url+"?on_ajax_execute",
								type: 'POST',
								data: formdata,
								success: function(data) {
									$('.vars-items').html(data);
								}
							});
						}
						function recRemoveVars<?php echo $this->unique; ?>(id) {
							$('.vars-items').find('[data-vars='+id+']').first().remove();
						}
					</script>

					<div class="tab-pane" id="tabs-4<?php echo $this->unique; ?>">
						<div class="form-horizontal">
							<div class="form-group">
								<div class="col-xs-12">
									<button onchange="recLoadVars<?php echo $this->unique; ?>()" class="btn btn-success" data-id="page_" data-event="edit_var" data-subject="vars" >
										<img class="glyphicon" src="/admin/assets/icons/add_object.png" alt="">
										Добавить
									</button>
									<table class="table table-stripped">
										<thead>
										<tr class="fixed" align="left">
											<th class="text-left">Name</th>
											<th style="width:5%;">&nbsp;</th>
											<th style="width:5%;">&nbsp;</th>
										</tr>
										</thead>
										<tbody class="vars-items">
										<?php getVarsList($this->unique); ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div><?php if(!empty($this->pagename)): ?>
					<div class="tab-pane active" id="tabs-5<?php echo $this->unique; ?>">
						<div class="form">
							<div class="form-group">
								<button onchange="secLoad<?php echo $this->unique; ?>('sections');" class="btn btn-success" data-event="frame_add" data-subject="section" data-id="sections">
									<img class="glyphicon" src="/admin/assets/icons/add_content.png" alt="<?php echo $this->lv('addsection', 'page'); ?>">
									<?php echo $this->lv('addsection', 'page'); ?>
								</button>
								<table data-content="sections" id="sections<?php echo $this->unique; ?>" class="table table-stripped">
									<thead>
									<tr align="left">
										<th width="10%"><?php echo $this->getTextLanguage('id') ?></th>
										<th class="text-left"><?php echo $this->getTextLanguage('title') ?></th>
										<th class="text-left">Модуль</th>
										<th class="text-left">Записей</th>
										<th width="5%">&nbsp</th>
										<th width="5%">&nbsp</th>
									</tr>
									</thead>
									<tbody class="simple-sortable">
									<?php sectionList($this->page->sections, $this->unique, 'sections'); ?>
									</tbody>
								</table>
							</div>
						</div>
					</div><?php endif ?>
					<div class="tab-pane" id="tabs-6<?php echo $this->unique; ?>">
						<div class="form">
							<div class="form-group">
								<button onchange="secLoad<?php echo $this->unique; ?>('gsections');" class="btn btn-success" data-event="frame_add" data-subject="section" data-id="gsections">
									<img class="glyphicon" src="/admin/assets/icons/add_content.png" alt="<?php echo $this->lv('addsection', 'page'); ?>">
									<?php echo $this->lv('addsection', 'page'); ?>
								</button>
								<table data-content="gsections" id="gsections<?php echo $this->unique; ?>" class="table table-stripped">
									<thead>
									<tr align="left">
										<th width="10%"><?php echo $this->lv('id') ?></th>
										<th class="text-left"><?php echo $this->lv('title') ?></th>
										<th class="text-left">Модуль</th>
										<th class="text-left">Записей</th>
										<th width="5%">&nbsp</th>
										<th width="5%">&nbsp</th>
									</tr>
									</thead>
									<tbody class="simple-sortable">
									<?php sectionList($this->prj->sections, $this->unique, 'gsections'); ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<input type="hidden" name="GoToEditContent" value="true">
				</div><!-- end tabs-content -->
				</form>
			</div>
		</div>
		<div class="modal-footer">
			<button data-dismiss="modal" type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lv('close', 'page') ?></button>
			<button data-action="save" type="button" class="btn btn-primary" data-loading-text="<?php echo $this->lv('saving_process', 'page') ?>"><?php echo $this->lv('save', 'page') ?></button>
		</div>
	</div>
</div>