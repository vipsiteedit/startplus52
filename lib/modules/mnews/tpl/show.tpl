
<?php if($section->parametrs->param8=='N'): ?><noindex><?php endif; ?>
    <div class="content cont_news record-item" <?php echo $__data->editItemRecord($section->id, $record->id) ?> <?php echo $section->style ?>>
        <?php if($section->parametrs->param10=='Y'): ?>
            <a name="show<?php echo $section->id ?>_<?php echo $record->id ?>"></a>
        <?php endif; ?>
        <div id="view">
            <?php if(!empty($record->title)): ?>
                <h4 class="objectTitle">
                    <span class="objectTitleTxt record-title"><?php echo $record->title ?></span>
                </h4>
            <?php endif; ?>
            <?php if(!empty($record->image)): ?>
                <div class="objimage record-image">
                    <img class="objectImage" alt="<?php echo $record->image_alt ?>" title="<?php echo $record->image_alt ?>" src="<?php echo $record->image ?>" border="0">
                </div>
            <?php endif; ?>
            <?php if($section->parametrs->param5=='Y'): ?>
                <?php if(!empty($record->note)): ?>
                    <div class="objectNote record-note"><?php echo $record->note ?></div>
                <?php endif; ?>
            <?php endif; ?>
            <div class="objectText record-text"><?php echo $record->text ?></div> 
            <input class="buttonSend" onclick="document.location = '<?php echo seMultiDir()."/".$_page."/" ?>';" type="button" value="<?php echo $section->parametrs->param4 ?>">
        </div> 
    </div> 
<?php if($section->parametrs->param8=='N'): ?></noindex><?php endif; ?>
