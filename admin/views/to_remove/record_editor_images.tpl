<a class="editorLinkAddPhotos block<?php echo $section->id ?>" href="javascript:addPhotos(true, <?php echo $section->id ?>);">Добавить фото</a>
<div class="editorRecordsPhotos block<?php echo $section->id ?>" style="display: none;">
<form method="post" enctype="multipart/form-data" action="/<?php echo $this->pagename ?>/?jqueryform=parteditphotos">
	<input type="hidden" name="partid" value="<?php echo $section->id ?>">
	<input class="addfilephoto" type="file" name="addrecimages[]">&nbsp;<input title="Заголовок фото" type="text" name="recimage_alt[]"><br>
	<input class="addfilephoto" type="file" name="addrecimages[]">&nbsp;<input title="Заголовок фото" type="text" name="recimage_alt[]"><br>
	<input class="addfilephoto" type="file" name="addrecimages[]">&nbsp;<input title="Заголовок фото" type="text" name="recimage_alt[]"><br>
	<input class="addfilephoto" type="file" name="addrecimages[]">&nbsp;<input title="Заголовок фото" type="text" name="recimage_alt[]"><br>
	<input class="addfilephoto" type="file" name="addrecimages[]">&nbsp;<input title="Заголовок фото" type="text" name="recimage_alt[]"><br>
	<input class="addfilephoto" type="file" name="addrecimages[]">&nbsp;<input title="Заголовок фото" type="text" name="recimage_alt[]"><br><br>
	<input class="addFilesPhotoBtn" type="submit" name="GoToEditContent" value="Загрузить">
	<input class="closeFilesPhotoBtn" onClick="addPhotos(false, <?php echo $section->id ?>);" type="button" value="Закрыть">
</form>
</div>