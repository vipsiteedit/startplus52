<div id="se-login-modal-window"></div>
<script>
   $('.se-login-modal').on('click', function() {
		var t = $(this).data('target');
		if (t == undefined) t = '';
 		$('#se-login-modal-window').load('?login-AJAX&target='+t);
		return false;
	});
</script>
</html>
<?php
if ($se->footercss) {
   echo join("\n", $se->footercss) . "\n";
}
