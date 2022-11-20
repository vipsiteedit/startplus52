<link rel="stylesheet" type="text/css" href="/admin/assets/imageselect/css/imgareaselect-default.css" />
<div id="dialog-modal-image<?php echo $this->unique; ?>" title="<?php echo $this->getTextLanguage('image_manager') ?>" class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        	<h4 class="modal-title"><?php echo $this->getTextLanguage('image_editor') ?></h4>
		</div>
		<div class="modal-body">
			<p>
				<img id="photo" src="/images/ic_cat1.jpg" class="img-thumbnail" alt="" title="" style="margin: 0 0 0 10px;" />
			</p>
			<form action="crop.php" method="post">
				<input type="hidden" name="x1" value="" />
				<input type="hidden" name="y1" value="" />
				<input type="hidden" name="x2" value="" />
				<input type="hidden" name="y2" value="" />
				<input type="hidden" name="w" value="" />
				<input type="hidden" name="h" value="" />
				<input type="submit" value="Crop" />
			</form>
		</div>
    <div class="modal-footer">
			<div class="text-center">
				<button data-dismiss="modal" type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->getTextLanguage('close') ?></button>
			</div>
		</div>
	</div>
</div>
<!--script type="text/javascript" src="/admin/assets/imageselect/jquery-1.5.1.min.js"></script-->
<script type="text/javascript" src="/admin/assets/imageselect/jquery.imgareaselect.pack.js"></script>
<script type="text/javascript">
/*	function preview(img, selection) {
		var scaleX = 100 / (selection.width || 1);
		var scaleY = 100 / (selection.height || 1);
		$('#photo + div > img').css({
			width: Math.round(scaleX * 600) + 'px',
			height: Math.round(scaleY * 400) + 'px',
			marginLeft: '-' + Math.round(scaleX * selection.x1) + 'px',
			marginTop: '-' + Math.round(scaleY * selection.y1) + 'px'
		});
	}
*/
	$(document).ready(function () {
	/*	$('<div><img src="/images/ic_cat1.jpg" style="position: relative;" /><div>') .css({
			float: 'left',
			position: 'relative',
			overflow: 'hidden',
			width: '100px',
			height: '100px'
		}) .insertAfter($('#photo'));
		*/

		$('#photo').imgAreaSelect({
			//aspectRatio: '1:1',
			handles: true,
			//onSelectChange: preview,
			onSelectEnd: function ( image, selection ) {
				$('input[name=x1]').val(selection.x1);
				$('input[name=y1]').val(selection.y1);
				$('input[name=x2]').val(selection.x2);
				$('input[name=y2]').val(selection.y2);
				$('input[name=w]').val(selection.width);
				$('input[name=h]').val(selection.height);
			}
		});
	});
</script>