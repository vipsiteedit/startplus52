<?php
function getPagesSelector($pages, $sel, $opt){
	$select = '<option value="">'.$opt.'</option>';
	foreach($pages as $page){
		$selected = ($sel == strval($page['name'])) ? ' selected' : '';
		$select .= '<option value="'.$page['name'].'"'.$selected.'>'.$page->title.'</option>';
	}
	return $select;
}

$title_dialog = $this->getTextLanguage('editor','rec').': ';
$title_dialog .= ((int)$record->id) ? $record->id.' ('.$section->id.')' : $this->getTextLanguage('new');

?>
<div id="dialog-modal-rec<?php echo $this->unique; ?>" title="<?php echo $title_dialog ?>" class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        	<h4 class="modal-title"><?php echo $title_dialog ?></h4>
		</div>
		<div class="modal-body">
			<form name="recordsform" method="post" enctype="multipart/form-data" action="/<?php echo $this->pagename ?>/?jqueryform=partedit">
			<div class="tabbable" id="tabs-part">
				<ul class="nav nav-tabs">
					<li class="active">
						<a href="#tabs-1<?php echo $this->unique; ?>" data-toggle="tab">
							<img src="/admin/assets/icons/16x16/text.png">
							<?php echo $this->getTextLanguage('content') ?>
						</a>
					</li>
					<li>
						<a href="#tabs-2<?php echo $this->unique; ?>" data-toggle="tab">
							<img src="/admin/assets/icons/16x16/text.png">
							<?php echo $this->getTextLanguage('details') ?>
						</a>
					</li>
					<li>
						<a href="#tabs-3<?php echo $this->unique; ?>" data-toggle="tab">
							<img src="/admin/assets/icons/16x16/image.png">
							<?php echo $this->getTextLanguage('image') ?>
						</a>
					</li>
				</ul>
				<input type="hidden" name="partid" value="<?php echo $section->id ?>">
				<input type="hidden" name="recid" value="<?php echo $record->id ?>">
				<div class="tab-content">
					<div class="tab-pane active" id="tabs-1<?php echo $this->unique; ?>">
						<div class="form-horizontal">
							<div class="form-group">
								<label class="control-label col-xs-2" for="rectitle"><?php echo $this->getTextLanguage('title') ?></label>
								<div class="col-xs-10">
									<input type="text" name="rectitle" class="form-control" value="<?php echo htmlspecialchars($record->title) ?>">
								</div>
							</div>
							<?php if(list($iobj) = $this->getModuleInterface($section->type, 'field', 'text')): ?>
							<div class="form-group">
								<label class="control-label col-xs-2" for="recfield">
								<?php
								foreach($iobj->title as $datetitle) {
									if (strval($datetitle['lang']=='rus')) {
										echo strval($datetitle);
									}
								}
								list($act) = $this->getModuleInterface($section->type, 'field', 'btn');
								$act = strval($act['act']);
								$val = $record->field;
								$thisdate = date('d.m.Y');
								if ($val == '' && $act == 'date' ) $val = $thisdate; 
								?>
								</label>
								<div class="col-xs-10">
								<?php if ($act=='date'): ?>
									<div class="input-group">
										<input class="form-control" type="text" id="recfield<?php echo $this->unique; ?>" name="recfield" value="<?php echo $val ?>">
										<span class="input-group-btn">
											<button class="btn btn-default" type="button" onclick=" $('#recfield<?php echo $this->unique; ?>').val('<?php echo $thisdate; ?>');">
												<img src="/admin/assets/icons/16x16/date_go.png">
											</button>
										</span>
									</div>
								<?php elseif ($act=='url'): ?> 
									<div class="input-group">
										<input type="text" name="recfield" id="recfield<?php echo $this->unique; ?>" class="form-control" value="<?php echo htmlspecialchars($record->field) ?>">
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
									        		$('#recfield<?php echo $this->unique; ?>').val(link);
									        	});
									        </script>
									      </div>
									</div>
								<?php else: ?>
									<input class="form-control" type="text" name="recfield" value="<?php echo $val ?>">
								<?php endif; ?>
								</div>
							</div>
							<?php endif; ?>
					<?php for($i = 1; $i <= 6; $i++):
						list($iobj) = $this->getModuleInterface($section->type, 'text'.$i, 'menu,text');
						if(!empty($iobj)): ?>
						<div class="form-group">
							<label class="control-label col-xs-2"><?php echo strval($iobj->title) ?></label>
							<div class="col-xs-10">
								<?php if (strval($iobj['type']=='menu')): ?>
									<select name="rectext<?php echo $i; ?>" class="form-control">
										<?php
										$sellist = explode(',', strval($iobj->list));
										foreach($sellist as $sel){
											$sel = explode('|', $sel);
											$selected = ($sel[0] == $record->{'text'.$i})?'selected':'';
											echo '<option value="'.$sel[0].'"'.$selected.'>'.$sel[1].'</option>';
										}
										?>
									</select>
								<?php else: ?>
									<input class="form-control" type="text" name="rectext<?php echo $i; ?>" value="<?php echo $record->{'text'.$i}; ?>">
								<?php endif; ?>
							</div>
						</div>
					<?php endif; endfor; ?>
						<div class="form-group">
							<label  class="control-label col-xs-2" for="recnote"><?php echo $this->getTextLanguage('teaser'); ?></label>
							<div class="col-xs-10">
								<?php if($this->hasHiddenTags($record->note)): ?>
									<div class="alert alert-warning">
										 <h4><?php echo $this->getTextLanguage('warning', 'mes') ?></h4><?php echo $this->getTextLanguage('hidden_tags','mes') ?>
									</div>
								<?php endif; ?>
								<textarea data-texteditor="true" id="recnote<?php echo $this->unique; ?>" class="form-control" name="recnote" rows="10"><?php echo htmlspecialchars($record->note) ?></textarea>
							</div>
						</div>
					</div>
				</div>
				<div class="tab-pane" id="tabs-2<?php echo $this->unique; ?>">
					<div class="form-horizontal">
						<div class="form-group">
							<label  class="control-label col-xs-2" for="rectext"><?php echo $this->getTextLanguage('details'); ?></label>
							<div class="col-xs-10">
								<?php if($this->hasHiddenTags($record->text)): ?>
									<div class="alert alert-warning">
										 <h4><?php echo $this->getTextLanguage('warning', 'mes') ?></h4><?php echo $this->getTextLanguage('hidden_tags','mes') ?>
									</div>
								<?php endif; ?>
								<textarea data-texteditor="true" id="rectext<?php echo $this->unique; ?>" class="form-control" name="rectext" rows="10"><?php echo htmlspecialchars($record->text) ?></textarea>
							</div>
						</div>
					</div>
				</div>
					<script type="text/javascript">

						var re_modal = $('#dialog-modal-rec<?php echo $this->unique;?>');
						console.log(re_modal);

						$(re_modal).find("[type=range]").val(100);
						$(re_modal).find("#priorityval2").html(100);

						function getSizeImage<?php echo $this->unique; ?>(nimg){
							$(nimg).bindImageLoad(function(){
								var img = $(this)[0];
								setTimeout(function () {
									var w = img.naturalWidth, h = img.naturalHeight;
									// передаем картинку и её размеры на обработку
									$(re_modal).find('.img-responsive-width').val(w);
									$(re_modal).find("[name=img-width]").attr("data-value", w);
									$(re_modal).find('.img-responsive-width').data('width', w);
									$(re_modal).find('.img-responsive-height').val(h);
									$(re_modal).find('[name=img-height]').attr("data-value", h);
									$(re_modal).find('.img-responsive-height').data('height', h);
									img = null;
								}, 400);
							});
						}
						function loadImage<?php echo $this->unique; ?>(img) {
							if (img) img = '<?php echo "/".SE_DIR; ?>' + img;
							var nimg = $('<img/>').attr('src', img + "?" + Date.now()).addClass('img-responsive').addClass('center-block');
							var ids = '#recordimagediv<?php echo $this->unique; ?>';
							$(ids).html(nimg);
							$(re_modal).find(".pe-image-properties").removeClass("hidden");
							$(re_modal).find(".pe-image").removeClass("hidden");
							getSizeImage<?php echo $this->unique; ?>(nimg);
							onCrop<?php echo $this->unique; ?>(nimg);
						}

						function clearImage<?php echo $this->unique; ?>() {
							$(re_modal).find(".pe-image-properties").addClass("hidden");
							$(re_modal).find(".pe-image").addClass("hidden");
							$(re_modal).find('#recordimagediv<?php echo $this->unique; ?>').html('');
							$(re_modal).find('#recordimage<?php echo $this->unique; ?>').val('');
						}

						$(re_modal).find('a[href=#tabs-2<?php echo $this->unique; ?>]').click(function(){
							var img = $(re_modal).find('#recordimage<?php echo $this->unique; ?>').val();
							if (img)
								loadImage<?php echo $this->unique; ?>(img);
						});

						function onCrop<?php echo $this->unique; ?>(img) {
							isImageSelect = true;

							$(img).Jcrop({
								boxWidth: 400
							});

							$(re_modal).off('cropmove cropend', img);
							$(re_modal).on('cropmove cropend', img, function(e,s,c){
								$('#cropimage2').removeClass('hidden');
								$('#cropimage2 input[name=x1]').val(Math.ceil(c.x));
								$('#cropimage2 input[name=y1]').val(Math.ceil(c.y));
								$('#cropimage2 input[name=x2]').val(Math.ceil(c.w + c.x));
								$('#cropimage2 input[name=y2]').val(Math.ceil(c.h + c.y));
								$('#cropimage2 input[name=w]').val(Math.ceil(c.w));
								$('#cropimage2 input[name=h]').val(Math.ceil(c.h));
							});
						}

						$(re_modal).off("click submit", ".jcrop-selection");
						$(re_modal).on("click submit", ".jcrop-selection", function(e){
							e.preventDefault();
							e.stopPropagation();
						});

						var setFormScale<?php echo $this->unique; ?> = function (scale) {
							$('#cropimage2 input[name=scale]').val(scale);
							$('#cropimage2').removeClass('hidden');
						};

						$(re_modal).off("change", "[type=range]");
						$(re_modal).on("change", "[type=range]", function () {
							var scale = $(re_modal).find("[type=range]").val() / 100;
							setFormScale<?php echo $this->unique; ?>(scale.toFixed(5));

							function calc(index, value) {
								return Math.ceil($(this).attr("data-value") * scale);
							}

							$(re_modal).find("[name=img-width]").val(calc);
							$(re_modal).find("[name=img-height]").val(calc);
						});


						$(re_modal).find('.img-responsive-height').keyup(function() {
							if ($(this).val() === "0") $(this).val(1);
							var h = $(this).data('height');
							var k = ($(this).val() / h).toFixed(3);
							var krange = (k * 100).toFixed(1);
							var w = $(re_modal).find('.img-responsive-width').data('width');
							var size = Math.ceil(w * k);
							$(re_modal).find('.img-responsive-width').val(size);
							$(re_modal).find("[type=range]").val(krange);
							$(re_modal).find("#priorityval2").html(krange);
							setFormScale<?php echo $this->unique; ?>(k);
						});

						$(re_modal).find('.img-responsive-width').keyup(function() {
							if ($(this).val() === "0") $(this).val(1);
							var w = $(this).data('width');
							var k = ($(this).val() / w).toFixed(3);
							var krange = (k * 100).toFixed(1);
							var h = $(re_modal).find('.img-responsive-height').data('height');
							var size = Math.ceil(h * k);
							$(re_modal).find('.img-responsive-height').val(size);
							$(re_modal).find("[type=range]").val(krange);
							$(re_modal).find("#priorityval2").html(krange);
							setFormScale<?php echo $this->unique; ?>(k);
						});

						$(re_modal).find('.nav-tabs li').click(function () {
							if ($(this).index() == 2) {
								$("#cropimage2").find("[name]").each(function(i, val){
									$(val).val('');
								});
							}
						});


					</script>
				<div class="tab-pane" id="tabs-3<?php echo $this->unique; ?>">
					<?php
					if(strval($record->image_alt) == '') {
					$record->image_alt = $record->title;
					}
					?>
					<div class="form-inline">
						<div class="row">
							<div class="col-xs-6">
								<div class="form-group">
									<div class="input-group">
										<!-- onchange="loadImage<?php echo $this->unique; ?>(this.value);" -->
										<input type="text" class="form-control" id="recordimage<?php echo $this->unique; ?>" name="recordimage" onchange="loadImage<?php echo $this->unique; ?>(this.value);" value="<?php echo htmlspecialchars(strval($record->image)) ?>">
										    <span class="input-group-btn">
												<form id="imageform"><input type="file" id="contentfile" style="display: none"></form>
										        <button data-event="image_add" data-subject="image"
														data-response="#recordimage<?php echo $this->unique; ?>"
														data-response-img="#recordimagediv<?php echo $this->unique; ?>"
														data-folder="<?php echo 'news' ?>"
														data-progress-bar=".cont-image .progress-bar"
														data-id="recordimage<?php echo $this->unique; ?>"
														class="btn btn-default" id="imageadd"
														data-form-id="#cropimage2"
														data-modal-id="#dialog-modal-rec<?php echo $this->unique; ?>"
														title="<?php echo $this->getTextLanguage('add_pc', 'img') ?>">
													<img src="/admin/assets/icons/16x16/image_add.png">
												</button>
										        <button data-event="image_select"
														data-subject="image"
														data-id="recordimage<?php echo $this->unique; ?>"
														class="btn btn-default"
														id="imageadd"
														data-form-id="#cropimage2"
														data-modal-id="#dialog-modal-rec<?php echo $this->unique; ?>"
														title="<?php echo $this->getTextLanguage('add_lib', 'img') ?>">
													<img src="/admin/assets/icons/16x16/image_add.png">
													<?php echo $this->getTextLanguage('select') ?>
												</button>
												<?php if($section->image != ''): ?>
												<button onclick="clearImage<?php echo $this->unique; ?>(); return false;" class="btn btn-warning" title="<?php echo $this->getTextLanguage('delete', 'img') ?>">
													<img src="/admin/assets/icons/16x16/image_delete.png">
													<?php echo $this->getTextLanguage('delete') ?>
												</button>
												<?php endif; ?>
										    </span>
									</div>
								</div>
							</div>
							<div class="col-xs-6">
								<div class="form-group">
									<label for="recordimage_alt"><?php echo $this->getTextLanguage('alt','img') ?></label>
									<input title="<?php echo $this->getTextLanguage('alt_title','img') ?>" type="text" name="recordimage_alt" class="form-control" value="<?php echo htmlspecialchars($record->image_alt) ?>">
								</div>
							</div>
						</div>
					</div>
					<hr>
					<div class="progress">
						<div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="0"
							 aria-valuemin="0" aria-valuemax="100" style="width:0">
							<span class="sr-only">0% Complete</span>
						</div>
					</div>
					<div class="row pe-image-properties<?php echo !empty($record->image) ? '' : ' hidden'; ?>">
						<div class="form-group col-xs-2">
							<label>Высота</label><input class="form-control img-responsive-height" name="img-height" value="0">
						</div>
						<div class="form-group col-xs-2">
							<label>Ширина</label><input class="form-control img-responsive-width" name="img-width" value="0">
						</div>
						<div class="form-group col-xs-2">
							<label>Масштаб</label>
							<div class="form-control col-xs-1">
								<input class="" type="range" name="priority" value="<?php echo $page->priority ?>"
									   min="1" max="100" step="0.1" value="100"
									   >
							</div>
							<div class="form-control col-xs-1 hidden">
								<p class="form-control-static" style="margin: 0 auto;"
								   id="priorityval2"><?php echo $page->priority ?></p>
							</div>
						</div>
						<div class="form-group col-xs-2">
							<label>&nbsp;</label>
							<form id="cropimage2" class="hidden" action="" method="post">
								<input type="hidden" name="x1" value="" />
								<input type="hidden" name="y1" value="" />
								<input type="hidden" name="x2" value="" />
								<input type="hidden" name="y2" value="" />
								<input type="hidden" name="w" value="" />
								<input type="hidden" name="h" value="" />
								<input type="hidden" name="scale" value="1" />
								<input type="submit" data-event="image_crop"
									   data-subject="image"
									   data-id="<?php echo $this->pagename.'_'.$section->id.'_'.$record->id ?>"
									   data-response-img="#recordimagediv<?php echo $this->unique; ?>"
									   data-form-id="#cropimage2"
									   data-modal-id="#dialog-modal-rec<?php echo $this->unique; ?>"
									   value="Применить"
									   class="form-control" />
							</form>
						</div>
					</div>
					<div id="recordimagediv<?php echo $this->unique; ?>"
						 class="text-center well pe-image<?php echo !empty($record->image) ? '' : ' hidden'; ?>"></div>
				</div>
					<script>
						loadImage<?php echo $this->unique; ?>("<?php echo htmlspecialchars(strval($record->image)) ?>");
					</script>
			<div id="er_checkshow"><?php echo $this->getTextLanguage('show', 'rec') ?>: <input type="checkbox" <?php echo ($record->visible == 'on' || empty($record->visible))?'checked':'' ?> name="recvisible" value="on"></div>
			</form>
		</div>
		</div>
		<div class="modal-footer">
			<button data-dismiss="modal" type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->getTextLanguage('close') ?></button>
			<button data-action="save" type="button" class="btn btn-primary" data-loading-text="<?php echo $this->getTextLanguage('saving_process') ?>"><?php echo $this->getTextLanguage('save') ?></button>
		</div>
	</div>
</div>