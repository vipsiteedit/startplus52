
    <div class="content cont_rattxt view record-item" id="view"<?php echo $__data->editItemRecord($section->id, $record->id) ?>>
        <?php if(!empty($record->title)): ?>
            <h4 class="objectTitle">
                <span class="objectTitleTxt record-title"><?php echo $record->title ?></span>
            </h4> 
        <?php endif; ?>
        <?php if(!empty($record->image)): ?>
            <div class="objimage record-image" id="objimage">
                <img class="objectImage" alt="<?php echo $record->image_alt ?>" src="<?php echo $record->image ?>" border="0">
            </div> 
        <?php endif; ?>
        <?php if(!empty($record->note)): ?>
            <div class="objectNote record-note"><?php echo $record->note ?></div> 
        <?php endif; ?>
        <div class="objectText record-text"><?php echo $record->text ?></div> 
            <input type="button" id="butfirst" class="buttonSend" onclick="document.location='<?php echo seMultiDir()."/".$_page."/" ?>';" value="<?php echo $section->language->lang001 ?>">
        </div> 
