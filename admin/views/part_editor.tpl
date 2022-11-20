<?php 
	require_once dirname(__FILE__)."/part_editor.php";
  	$title_dialog = $this->getTextLanguage('editor', 'sec').': ';

if ($cont_id != 'sections' && $cont_id != 'gsections'){
	$container =  ($cont_id < 100) ? ' [content-'.$cont_id.']' : ' [global-'.$cont_id.']';
} else {
	$container =  ($cont_id == 'sections') ? ' [content]' : ' [global]';
}
$title_dialog .= (strval($section->id)) ? $section->id .' ('.$section->type.')' :  $this->getTextLanguage('new', 'sec').$container;
?>
<style>
.labelparams {
	padding: 10px;
	text-align: center;
	font-size: 0.75em;
	font-weight: bold;
	margin: 0;
	text-transform: uppercase;
}
	.progress {
		display: none;
		height: 5px;
	}
</style>

<div id="dialog-modal-part<?php echo $this->unique; ?>" title="<?php echo $title_dialog ?>" class="modal-dialog modal-lg">
	<link rel="stylesheet" type="text/css" href="/admin/assets/imageselect/css/imgareaselect-default.css" />
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        	<h4 class="modal-title"><?php echo $title_dialog ?></h4>
		</div>
		<div class="modal-body">
			<div class="tabbable" id="tabs-part">
				<ul class="nav nav-tabs">
					<li class="active">
						<a href="#tabs-1<?php echo $this->unique; ?>" data-toggle="tab">
							<img src="/admin/assets/icons/16x16/text.png">
							<?php echo $this->getTextLanguage('content') ?>
						</a>
					</li>
					<?php if($this->getModuleProperty($section->type,'interface')): ?>
					<li>
						<a href="#tabs-7<?php echo $this->unique; ?>" data-toggle="tab">
							<img src="/admin/assets/icons/16x16/page_copy.png">
							<?php echo $this->getTextLanguage('records', 'sec') ?>
						</a>
					</li>
					<?php endif; ?>
					<li>
						<a href="#tabs-2<?php echo $this->unique; ?>" data-toggle="tab">
							<img src="/admin/assets/icons/16x16/image.png">
							<?php echo $this->getTextLanguage('image') ?>
						</a>
					</li>
					<li>
						<a href="#tabs-4<?php echo $this->unique; ?>" data-toggle="tab">
							<img src="/admin/assets/icons/16x16/application_cascade.png">
							<?php echo $this->getTextLanguage('params', 'sec') ?></a></li>
					<!--li><a href="#tabs-5"><?php echo $this->getTextLanguage('langs','sec') ?></a></li-->
					<?php if(SE_DB_ENABLE): ?>
					<li>
						<a href="#tabs-6<?php echo $this->unique; ?>" data-toggle="tab">
							<img src="/admin/assets/icons/16x16/category.png">
							<?php echo $this->getTextLanguage('access', 'sec') ?>
						</a>
					</li>
					<?php endif; ?>
				</ul>
					<script type="text/javascript">

						var pe_modal = $('#dialog-modal-part<?php echo $this->unique; ?>');

                        $(pe_modal).find("[type=range]").val(100);
						$(pe_modal).find("#priorityval").html(100);

                        function getSizeImage<?php echo $this->unique; ?>(nimg){
                            $(nimg).bindImageLoad(function(){
                                var img = $(this)[0];
                                    setTimeout(function () {
                                    var w = img.naturalWidth, h = img.naturalHeight;
                                        // передаем картинку и её размеры на обработку
                                    $(pe_modal).find('.img-responsive-width').val(w);
                                    $(pe_modal).find("[name=img-width]").attr("data-value", w);
                                    $(pe_modal).find('.img-responsive-width').data('width', w);
                                    $(pe_modal).find('.img-responsive-height').val(h);
                                    $(pe_modal).find('[name=img-height]').attr("data-value", h);
                                    $(pe_modal).find('.img-responsive-height').data('height', h);
                                    img = null;
                                }, 400);
                            });
                        }
                        function loadImage<?php echo $this->unique; ?>(img) {
                            if (img) img = '<?php echo "/".SE_DIR; ?>' + img;
                            var nimg = $('<img/>').attr('src', img + "?" + Date.now()).addClass('img-responsive').addClass('center-block');
                            var ids = '#partimagediv<?php echo $this->unique; ?>';
                            $(ids).html(nimg);
                            $(pe_modal).find(".pe-image-properties").removeClass("hidden");
                            $(pe_modal).find(".pe-image").removeClass("hidden");
                            getSizeImage<?php echo $this->unique; ?>(nimg);
                            onCrop<?php echo $this->unique; ?>(nimg);
                        }

                        function clearImage<?php echo $this->unique; ?>() {
                            $(pe_modal).find(".pe-image-properties").addClass("hidden");
                            $(pe_modal).find(".pe-image").addClass("hidden");
                            $(pe_modal).find('#partimagediv<?php echo $this->unique; ?>').html('');
                            $(pe_modal).find('#partimage<?php echo $this->unique; ?>').val('');
                        }

                        $('a[href=#tabs-2<?php echo $this->unique; ?>]').click(function(){
                            var img = $(pe_modal).find('#partimage<?php echo $this->unique; ?>').val();
                            if (img)
                                loadImage<?php echo $this->unique; ?>(img);
                        });

                        function onCrop<?php echo $this->unique; ?>(img) {
                            isImageSelect = true;

                            $(img).Jcrop({
                                boxWidth: 400
                            });

                            $(pe_modal).off('cropmove cropend', img);
                            $(pe_modal).on('cropmove cropend', img, function(e,s,c){
                                $('#cropimage').removeClass('hidden');
                                $('#cropimage input[name=x1]').val(Math.ceil(c.x));
                                $('#cropimage input[name=y1]').val(Math.ceil(c.y));
                                $('#cropimage input[name=x2]').val(Math.ceil(c.w + c.x));
                                $('#cropimage input[name=y2]').val(Math.ceil(c.h + c.y));
                                $('#cropimage input[name=w]').val(Math.ceil(c.w));
                                $('#cropimage input[name=h]').val(Math.ceil(c.h));
                            });
                        }

                        $(pe_modal).off("click submit", ".jcrop-selection");
                        $(pe_modal).on("click submit", ".jcrop-selection", function(e){
                            e.preventDefault();
                            e.stopPropagation();
                        });

                        var setFormScale<?php echo $this->unique; ?> = function (scale) {
                            $('#cropimage input[name=scale]').val(scale);
                            $('#cropimage').removeClass('hidden');
                        };

                        $(pe_modal).off("change", "[type=range]");
                        $(pe_modal).on("change", "[type=range]", function () {
                            var scale = $("[type=range]").val() / 100;
                            setFormScale<?php echo $this->unique; ?>(scale.toFixed(5));

                            function calc(index, value) {
                                return Math.ceil($(this).attr("data-value") * scale);
                            }

                            $(pe_modal).find("[name=img-width]").val(calc);
                            $(pe_modal).find("[name=img-height]").val(calc);
                        });


                        $(pe_modal).find('.img-responsive-height').keyup(function() {
                            if ($(this).val() === "0") $(this).val(1);
                            var h = $(this).data('height');
                            var k = ($(this).val() / h).toFixed(3);
                            var krange = (k * 100).toFixed(1);
                            var w = $(pe_modal).find('.img-responsive-width').data('width');
                            var size = Math.ceil(w * k);
                            $(pe_modal).find('.img-responsive-width').val(size);
                            $(pe_modal).find("[type=range]").val(krange);
                            $(pe_modal).find("#priorityval").html(krange);
                            setFormScale<?php echo $this->unique; ?>(k);
                        });

                        $(pe_modal).find('.img-responsive-width').keyup(function() {
                            if ($(this).val() === "0") $(this).val(1);
                            var w = $(this).data('width');
                            var k = ($(this).val() / w).toFixed(3);
                            var krange = (k * 100).toFixed(1);
                            var h = $(pe_modal).find('.img-responsive-height').data('height');
                            var size = Math.ceil(h * k);
                            $(pe_modal).find('.img-responsive-height').val(size);
                            $(pe_modal).find("[type=range]").val(krange);
                            $(pe_modal).find("#priorityval").html(krange);
                            setFormScale<?php echo $this->unique; ?>(k);
                        });

                        $(pe_modal).find('.nav-tabs li').click(function () {
                            if ($(this).index() == 2) {
                                $("#cropimage").find("[name]").each(function(i, val){
                                    $(val).val('');
                                });
                            }
                        });

					</script>
				<form name="partform" method="post" enctype="multipart/form-data" action="/admin.php?jqueryform=partedit">

				<input type="hidden" name="pagename" value="<?php echo $this->pagename ?>">
				<div class="tab-content">
					<div class="tab-pane active" id="tabs-1<?php echo $this->unique; ?>">
						<div class="form-horizontal">
							<?php if($cont_id == 'sections'): ?>
							<div class="form-group">
								<label class="control-label col-xs-2" for="parttitle">Контейнер</label>
								<div class="col-xs-10">
									<select name="contentid" class="form-control">
										<option value="0">Главный контейнер</option>
										<option value="1">content-1</option>
										<option value="2">content-2</option>
										<option value="3">content-3</option>
										<option value="4">content-4</option>
									</select>
								</div>
							</div>
							<?php elseif($cont_id == 'gsections'): ?>
							<div class="form-group">
								<label class="control-label col-xs-2" for="parttitle">Контейнер</label>
								<div class="col-xs-10">
									<select name="contentid" class="form-control">
										<option value="100">global-0</option>
										<option value="101">global-1</option>
									</select>
								</div>
							</div>
							<?php else: ?>
							<input type="hidden" name="partid" value="<?php echo $section->id ?>">
							<?php endif ?>
							<div class="form-group">
								<label class="control-label col-xs-2" for="parttitle"><?php echo $this->getTextLanguage('title') ?></label>
								<div class="col-xs-10">
									<input type="text" name="parttitle" class="form-control" value="<?php echo htmlspecialchars($section->title) ?>">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-xs-2" for="parttext"><?php echo $this->getTextLanguage('teaser') ?></label>
								<div class="col-xs-10">
									<?php if($this->hasHiddenTags($section->text)): ?>
										<div class="alert alert-warning">
										  <h4><?php echo $this->getTextLanguage('warning', 'mes') ?></h4><?php echo $this->getTextLanguage('hidden_tags', 'mes') ?>
										</div>
									<?php endif; ?>
									<textarea data-texteditor="true" id="parttext<?php echo $this->unique; ?>" class="form-control" name="parttext" rows="10"><?php echo htmlspecialchars($section->text) ?></textarea>
								</div>
							</div>
						</div>
					</div>
					<div class="tab-pane cont-image" id="tabs-2<?php echo $this->unique; ?>">
						<?php if(strval($section->image_alt) == '') $section->image_alt = $section->title; ?>
						<div class="form-inline">
							<div class="row">
								<div class="col-xs-6">
									<div class="form-group">
										<div class="input-group">
											<!-- onchange="loadImage<?php echo $this->unique; ?>(this.value);" -->
											<input type="text" class="form-control" id="partimage<?php echo $this->unique; ?>" name="partimage" onchange="loadImage<?php echo $this->unique; ?>(this.value);" value="<?php echo htmlspecialchars(strval($section->image)) ?>">
										    <span class="input-group-btn">
												<form id="imageform"><input type="file" id="contentfile" style="display: none"></form>
										        <button data-event="image_add" data-subject="image"
														data-response="#partimage<?php echo $this->unique; ?>"
														data-response-img="#partimagediv<?php echo $this->unique; ?>"
														data-folder="<?php echo 'news' ?>"
														data-progress-bar=".cont-image .progress-bar"
														data-id="partimage<?php echo $this->unique; ?>"
														data-form-id="#cropimage"
														data-modal-id="#dialog-modal-part<?php echo $this->unique; ?>"
														class="btn btn-default" id="imageadd"
														title="<?php echo $this->getTextLanguage('add_pc', 'img') ?>">
													<img src="/admin/assets/icons/16x16/image_add.png">
												</button>
										        <button data-event="image_select"
														data-subject="image"
														data-id="partimage<?php echo $this->unique; ?>"
														class="btn btn-default"
														id="imageadd"
														data-form-id="#cropimage"
														data-modal-id="#dialog-modal-part<?php echo $this->unique; ?>"
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
										<label for="partimage_alt"><?php echo $this->getTextLanguage('alt','img') ?></label>
										<input title="<?php echo $this->getTextLanguage('alt_title','img') ?>" type="text" name="partimage_alt" class="form-control" value="<?php echo htmlspecialchars($section->image_alt) ?>">
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
						<div class="row pe-image-properties<?php echo !empty($section->image) ? '' : ' hidden'; ?>">
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
										   onchange="$('#priorityval').html(this.value);">
								</div>
								<div class="form-control col-xs-1 hidden">
									<p class="form-control-static" style="margin: 0 auto;"
									   id="priorityval"><?php echo $page->priority ?></p>
								</div>
							</div>
							<div class="form-group col-xs-2">
                                <label>&nbsp;</label>
								<form id="cropimage" class="hidden" action="" method="post">
									<input type="hidden" name="x1" value="" />
									<input type="hidden" name="y1" value="" />
									<input type="hidden" name="x2" value="" />
									<input type="hidden" name="y2" value="" />
									<input type="hidden" name="w" value="" />
									<input type="hidden" name="h" value="" />
									<input type="hidden" name="scale" value="1" />
									<input type="submit" data-event="image_crop"
										   data-subject="image"
										   data-id="<?php echo $this->pagename.'_'.$section->id ?>"
										   data-response-img="#partimagediv<?php echo $this->unique; ?>"
										   data-form-id="#cropimage"
										   data-modal-id="#dialog-modal-part<?php echo $this->unique; ?>"
										   value="Применить"
                                           class="form-control" />
								</form>
							</div>
						</div>
						<div id="partimagediv<?php echo $this->unique; ?>"
							 class="text-center well pe-image<?php echo !empty($section->image) ? '' : ' hidden'; ?>"></div>

					</div>
					<div class="tab-pane tabsBox" id="tabs-4<?php echo $this->unique; ?>">
						<div class="form-group">
							<label class="control-label"><?php echo lv('type_section', 'sec') ?></label>
							<select class="form-control"
									name="parttype"
									data-event-change="type_select"
									data-subject="section">
								<?php $this->getModuleList($section->type) ?>
							</select>
						</div>
						<hr>
						<div class="form-group parametrs">

							<?php editor_getPartCont($section, 'params', $section->type); ?>
						</div>
					</div>
					<!--div class="tabsBox" id="tabs-5">
						<?php 
							//editor_getPartCont($section, 'langs');
						?>
					</div-->
					<?php if(SE_DB_ENABLE): ?>
					<div class="tab-pane tabsBox" id="tabs-6<?php echo $this->unique; ?>">
						<?php 
							editor_getPartCont($section, 'access', $section->type);
						?>
					</div>
					<?php endif; ?>
				
					<?php if($this->getModuleProperty($section->type,'interface')): ?>
					<div class="tab-pane tabsBox" id="tabs-7<?php echo $this->unique; ?>">
						<script>

							function recLoad<?php echo $this->unique; ?>(flNew) {
								if (typeof flNew === 'undefined') {
									flNew = false
								}
								var order = $('[data-order]').first();
								if (order.length && !flNew) {
									order = '&'+order.attr('data-order');
								} else {
									order = '';
								}
								var formdata = {name: 'editsection', value: "<?php echo $section->id; ?>", unique: "<?php echo $this->unique; ?>", get: "grecords"};
								$.ajax({
                                    url: seEvents.url+"?on_ajax_execute"+order+'&'+Date.now(),
                                    type: 'POST',
                                    data: formdata,
                                    success: function(data) {
										console.log(data);
										var reclist = $('#reclist<?php echo $this->unique; ?> tbody.simple-sortable');
                                        $(reclist).html(data);
										$(reclist).attr('data-order', $(reclist).sortable('serialize',{ attribute: 'data-id' }));
                                        $(reclist).sortable({
                                            forcePlaceholderSize: true,
                                            helper:	'clone',
                                            items: 'tr',
                                            opacity: .6,
                                            placeholder: 'placeholder',
                                            create: function() {
                                                var _this = $(this);
												console.log(this);
                                                _this.attr('data-order',_this.sortable('serialize',{ attribute: 'data-id' }));
                                            },
                                            stop: function() {
                                                var _this = $(this);
												console.log(this);
                                                _this.attr('data-order',_this.sortable('serialize',{ attribute: 'data-id' }));
                                            }
                                        });

                                    }
                                });
							}
							function recRemove<?php echo $this->unique; ?>(id) {
								$('#reclist<?php echo $this->unique; ?>').find('[data-record='+id+']').first().remove();
								var reclist = $('#reclist<?php echo $this->unique; ?> tbody.simple-sortable');
								$(reclist).attr('data-order', $(reclist).sortable('serialize',{ attribute: 'data-id' }));
							}
						</script>
						<button onchange="recLoad<?php echo $this->unique; ?>(true);" class="btn btn-success" data-event="frame_add" data-subject="record" data-target="section" data-id="<?php echo $section->id; ?>">
							<img class="glyphicon" src="/admin/assets/icons/add_object.png" alt="<?php echo $this->title_icon_addrecord; ?>">
							<?php echo $this->lv('addrecord','sec'); ?>
						</button>
						<table data-content="reclist" id="reclist<?php echo $this->unique; ?>" class="table table-stripped">
							<thead>
								<tr align="left">
									<th width="10%"><?php echo lv('id') ?></th>
									<th width="10%"><?php echo lv('image') ?></th>
									<th class="text-left"><?php echo lv('title') ?></th>
									<th width="5%">&nbsp</th>
									<th width="5%">&nbsp</th>
								</tr>
							</thead>
							<tbody class="simple-sortable">
								<?php editor_getPartCont($section, 'records', '', $this->unique); ?>
							</tbody>
						</table>
						<input type="hidden" name="recordgroup" value="">
					</div>
					<?php endif; ?>
				</div>
				</form>
			</div>
		</div>
		<div class="modal-footer">
			<button data-dismiss="modal" type="button" class="btn btn-default" data-dismiss="modal"><?php echo lv('close') ?></button>
			<button data-action="save" type="button" class="btn btn-primary" data-loading-text="<?php echo lv('saving_process') ?>"><?php echo $this->lv('save') ?></button>
		</div>
	</div>

</div>
