<?php
//error_reporting(E_ALL);

function editor_getPartCont($section, $typeform, $typename = '', $unique = null){
	$editor = dataEditor::getInstance();
	$root = getcwd() . $editor->getFolderModule($typename) .'/'. strval($typename).'/property/param_'.$editor->lang.'.xml';
	if ($typeform == 'params'){
?>
		<div class="form-horizontal">
			<?php 
				//$typename = $section->type;
				//$root = getcwd() . $editor->getFolderModule($typename) .'/'. strval($typename).'/property/param_'.$lang.'.xml';
				if (file_exists($root)){
					$params = simplexml_load_file($root);
				}
			?>
			<?php if (!empty($params)) foreach($params as $value): ?>
			<div class="form-group">
				<?php
					$tit = strval($value['title']);
					if (strpos($tit,'@')===true) {
						continue;
					}
					if (strpos($tit,'::')!==false) {
						// Глобальный заголовок
						echo '<h4 class="col-xs-12"><span class="alert alert-info col-xs-12 labelparams">'.str_replace(':','',$tit).'</span></h4>';
					} else {
						echo '<label class="col-xs-6 control-label">'.$tit.'</label>';
					} 
				?> 
				<div class="col-xs-6">
					<?php if(strpos($tit,'::')===false && $tit != ''): ?>
					<?php if(strpos($value['list'],'|') !== false): ?>
					<select class="form-control" name="parametr[<?php echo strval($value['name']) ?>]">
					<?php $sl = explode(',', $value['list']); 
					foreach($sl as $vl): ?>
					<?php 
						$vname = strval($value['name']);
						list($val, $tit) = explode('|', $vl);
						$res = strval($section->parametrs->$vname);
						if(!empty($res)) 
						    $sel = (trim($val) == $res) ? 'selected' :'';
						else 
						    $sel = (trim($val) == strval($value)) ? 'selected' :'';
					?>
					<option value="<?php echo $val ?>" <?php echo $sel ?>><?php echo $tit ?></option>
					<?php endforeach; ?>
					</select>
					<?php else: ?>
					<?php $vname = strval($value['name']);
						$pval = strval($section->parametrs->$vname); 
						if (empty($pval)) $pval = strval($value); 
					?>
					<input class="form-control" style="width:100%;" name="parametr[<?php echo strval($value['name']) ?>]" type="text" value="<?php echo $pval ?>">
					<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>	
			<?php endforeach; ?>
		</div>
<?php
} elseif ($typeform == 'langs'){

} elseif ($typeform == 'access'){
	//echo '<input name="partaccessname" value="'.$section->accessnam.'"><br>';
//$section->accessgroup
?>
<div class="form">
	<div class="form-group">
		<label class="control-label"><?php echo lv('level','access'); ?></label>
		<select class="form-control" name="partaccesslevel">
			<option value="0"<?php if($section->accessgroup==0) echo ' selected'; ?>>
				<?php echo lv('all', 'access'); ?>
			</option>
			<option value="1"<?php if($section->accessgroup==1) echo ' selected'; ?>>
				<?php echo lv('user', 'access'); ?>
			</option>
			<option value="2"<?php if($section->accessgroup==2) echo ' selected'; ?>>
				<?php echo lv('suser', 'access'); ?>
			</option>
			<option value="3"<?php if($section->accessgroup==3) echo ' selected'; ?>>
				<?php echo lv('admin', 'access'); ?>
			</option>
			<option value="4"<?php if($section->accessgroup==4) echo ' selected'; ?>>
				<?php echo lv('nouser', 'access'); ?>
			</option>
		</select>
	</div>
	<div class="form-group">
		<label class="control-label"><?php echo lv('group', 'access'); ?></label>
		<select class="form-control" name="partaccess[]" multiple="multiple" size="10">
		<option value="0-"><?php echo lv('none','access'); ?></option>
		<?php
			$accessnamelist = explode(';', $section->accessname);
			$accessname = array();
			foreach($accessnamelist as $accname) {
				$accessname[$accname] = ' selected';
			}
			@$groupusers = explode('|', $editor->prj->groupusers);
			if (!empty($groupusers[0])){
				@$grouplist = explode(';', $groupusers[0]);
				foreach ($grouplist as  $group) {
					echo '<option value="1-'.$group.'"'.$accessname[$group].'>'.$group."</option>\n";
				}
			}
			if (!empty($groupusers[1])) {
				@$grouplist = explode(';', $groupusers[1]);
				foreach($grouplist as  $group){
					echo '<option value="2-'.$group.'"'.$accessname[$group].'>'.$group."</option>\n";
				}
			}
			if (!empty($groupusers[2])){
				@$grouplist = explode(';', $groupusers[2]);
				foreach($grouplist as  $group) {
					echo '<option value="3-'.$group.'"'.$accessname[$group].'>'.$group."</option>\n";
				}
			}
		?>
		</select>
	</div>
	<div class="form-group">
	    <label class="control-label"><?php echo lv('no_rights_action','sec'); ?></label>
		<div class="checkbox">
		  <label>
		    <input type="checkbox" name="partshowsection" <?php if (!intval($section->showsection)): ?>checked<?php endif; ?>>
		    <?php echo lv('hide','sec'); ?>
		  </label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" name="partshowobject" <?php if (!intval($section->showobject)): ?>checked<?php endif; ?>>
				<?php echo lv('hide_records','sec'); ?>
			</label>
		</div>
	</div>
</div>
<?php
} elseif ($typeform == 'records') {
		//print_r($section->objects);
  foreach($section->objects as $record) {
	$img_name = delExtFile($record->image) . '_prev.'.getExtFile($record->image);
	$img = ($record->image != '') ? '<img width=30 height=30 src="/'.SE_DIR.$img_name.'" alt="">': '';

	echo "<tr style=\"cursor: move; margin-bottom:2px;\" data-record=\"{$section->id}_{$record->id}\" data-id=\"recorder_{$record->id}\">
	<td class=\"part_record_number\">{$record->id}</td>
	<td class=\"part_record_photo\">".$img."</td>
	<td class=\"part_record_title\">{$record->title}</td>
	<td><button onchange=\"recLoad{$unique}();\" class=\"btn btn-default btn-sm\" title=\"Изменить запись\" data-event=\"frame_edit\" data-subject=\"record\" data-target=\"section\" data-id=\"{$section->id}_{$record->id}\" title=\"".lv('edit')."\">
		<img src=\"/admin/assets/icons/16x16/pencil.png\">
	</button></td>
	<td><button onchange=\"recRemove{$unique}($(this).attr('data-id'));\" class=\"btn btn-default btn-sm\" title=\"Удалить запись\" data-event=\"frame_remove\" data-target=\"section\" data-subject=\"record\" data-id=\"{$section->id}_{$record->id}\" title=\"".lv('delete')."\">
		<img src=\"/admin/assets/icons/16x16/cross.png\">
	</button></td>
	</tr>
	";
  }
}
}
if (!empty($_POST['get'])){
	//$sections = ($_POST['get'] =='grecords') ? $this->prj->sections : $this->page->sections;
	editor_getPartCont($section, 'records', '', $this->unique);
	//sectionList($sections, $this->unique, $_POST['get']);
	exit;
}