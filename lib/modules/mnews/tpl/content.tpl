<div class="content cont_news" <?php echo $section->style ?>>
<?php if($section->parametrs->param7=='N'): ?><noindex><?php endif; ?>
    <?php if(!empty($section->title)): ?>
        <h3 class="contentTitle" <?php echo $section->style_title ?>>
            <span class="contentTitleTxt"><?php echo $section->title ?></span>
        </h3>
    <?php endif; ?>
    <?php if(!empty($section->image)): ?>
        <img alt="<?php echo $section->image_alt ?>" border="0" class="contentImage" <?php echo $section->style_image ?> src="<?php echo $section->image ?>">
    <?php endif; ?>
    <?php if(!empty($section->text)): ?>
        <div class="contentText" <?php echo $section->style_text ?>><?php echo $section->text ?></div>
    <?php endif; ?>
<?php $__data->recordsWrapperStart($section->id) ?><?php echo $__data->linkAddRecord($section->id) ?>
    <?php foreach($__data->limitObjects($section, $section->objectcount) as $record): ?>

        <div class="object record-item" <?php echo $__data->editItemRecord($section->id, $record->id) ?>>
                <h4 class="objectTitle">
                <?php echo $__data->linkEditRecord($section->id, $record->id,'') ?>
                <?php if(!empty($record->field)): ?>
                    <span id="dataType_date"><?php echo $record->field ?>&nbsp;</span>
                <?php endif; ?>
                <?php if(!empty($record->title)): ?>
<?php if($section->parametrs->param9=='N'): ?><noindex><?php endif; ?>
                <a class="objectTitleTxt" href="<?php echo $record->link_detail ?><?php if($section->parametrs->param10=='Y'): ?>#show<?php echo $section->id ?>_<?php echo $record->id ?><?php endif; ?>" <?php if($section->parametrs->param9=='N'): ?>rel="nofollow"<?php endif; ?> ><?php echo $record->title ?></a>
<?php if($section->parametrs->param9=='N'): ?></noindex><?php endif; ?>
                <?php endif; ?>
            </h4> 
            <?php if(!empty($record->image)): ?>
<?php if($section->parametrs->param9=='N'): ?><noindex><?php endif; ?>
                <a href="<?php echo $record->link_detail ?>" <?php if($section->parametrs->param9=='N'): ?>rel="nofollow"<?php endif; ?> >
                    <img border="0" class="objectImage record-pimage" src="<?php echo $record->image_prev ?>" border="0" alt="<?php echo $record->image_alt ?>" title="<?php echo $record->image_alt ?>">
                </a>
<?php if($section->parametrs->param9=='N'): ?></noindex><?php endif; ?>
            <?php endif; ?>
            <?php if(!empty($record->note)): ?>
                <div class="objectNote record-note"><?php echo $record->note ?></div>
            <?php endif; ?>
            <?php if($section->parametrs->param6=='Y'): ?>
                <?php if(!empty($record->text)): ?>
<?php if($section->parametrs->param9=='N'): ?><noindex><?php endif; ?>
                    <a class="newslink" href="<?php echo $record->link_detail ?><?php if($section->parametrs->param10=='Y'): ?>#show<?php echo $section->id ?>_<?php echo $record->id ?><?php endif; ?>" <?php if($section->parametrs->param9=='N'): ?>rel="nofollow"<?php endif; ?> ><?php echo $section->parametrs->param2 ?></a>
<?php if($section->parametrs->param9=='N'): ?></noindex><?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div> 
    
<?php endforeach; ?>
<?php $__data->recordsWrapperEnd() ?>




    <arhiv:link>
        <a id="linkArchive" href="<?php echo seMultiDir()."/".$_page."/".$razdel."/arhiv/" ?>"><?php echo $section->parametrs->param3 ?></a>
    </arhiv:link>
<?php if($section->parametrs->param7=='N'): ?></noindex><?php endif; ?>
</div>
