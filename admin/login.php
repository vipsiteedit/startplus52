<?php

//Controller
$error = false;
if (isset($_POST['GoToAuthor'])) {
      if (file_exists(SE_ROOT . 'system/license.dat')){
	$license = join('', file(SE_ROOT . 'system/license.dat'));
      }
	$adm = (string)seData::getInstance()->prj->adminlogin;
	$pass = strtolower(seData::getInstance()->prj->adminpassw);
      
	$login = get('authorlogin', 3);
	if ($login && $login == $license) {
		$serial = get('authorpassword',3);
		$result = file_get_contents('http://e-stile.ru/admin/checksn?login=siteedit&password=sev350start&'
		.'serial='.$login.'&client_password='.$serial);
		if (trim($result=='key')) {
		        seAuthAdmin();
		        //define('EDITOR_ADMIN', true);
			header('Location: '.$url);
		}
	}
	elseif (GetLogin(1, get('authorlogin',0), md5(get('authorpassword',3)), $adm, $pass)) {
		header('Location: '.$url);
		exit;
	}
    else {
        $error = true;
    }
	
}

?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo lv('titleeditor'); ?> | <?php echo lv('noauthor','mes'); ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<script type="text/javascript" src="/lib/js/jquery/jquery.min.js"></script>
		<link rel="stylesheet" type="text/css" href="/admin/assets/skin/login.css">
		<link rel="stylesheet" type="text/css" href="/admin/assets/bootstrap/css/bootstrap.min.css">
		<script type="text/javascript" src="/admin/assets/bootstrap/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="/lib/js/ui/jquery.ui.core.js"></script>
		<script type="text/javascript" src="/lib/js/ui/jquery.ui.position.js"></script>
		<script type="text/javascript">
			var windowSize = function() {
				$('#login-cell').position({my: 'center center', at:'center center-200', of: window, collision: 'fit'});
			}
			$(window).load(function() {
				windowSize();
			});
			$(window).resize(function(){
				windowSize();
			});
		</script>
	</head>
	<body id="login-body">
		<div class="container">
			<div class="row" id="login-row">
				<div id="login-cell" class="col-xs-12 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 col-lg-4 col-lg-offset-4">
					<img id="logo" width="200" class="center-block img-responsive" src="/admin/assets/siteedit.png">
					<div class="panel panel-default">
						<form action="" method="post">
							<div class="panel-heading text-center">
						      <h3 class="panel-title"><?php echo lv('noauthor','mes'); ?></h3>
						    </div>
						  	<div class="panel-body">
						  	    <?php if ($error): ?>
						  	        <div class="alert alert-warning">
						  	            <?php echo lv('authorerror','mes'); ?>
						  	        </div>
						  	    <?php endif; ?>
						    	<input type="hidden" value="true" name="authorize">
						    	<div class="form-group">
						    		<div class="input-group">
									  <span class="input-group-addon">
									  	<img src="/admin/assets/icons/16x16/user.png">
									  </span>
									  <input type="text" class="form-control" name="authorlogin" placeholder="<?php echo lv('login','mes'); ?>">
									</div>
								</div>
								<div class="form-group">
									<div class="input-group">
									  <span class="input-group-addon">
									  	<img src="/admin/assets/icons/16x16/key.png">
									  </span>
									  <input type="password" class="form-control" name="authorpassword" placeholder="<?php echo lv('password','mes'); ?>">
									</div>
						    	</div>
						    
						  	</div>
						  	<div class="panel-footer text-center">
						  		<input class="btn btn-primary" id="GoToAuthor" type="submit" value="<?php echo lv('continue'); ?>" name="GoToAuthor">
						  	</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<!--<div class="container">
			<div class="modal fade" id="loginModal">
				<div class="modal-dialog modal-sm">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title"></h4>
						</div>
						<form action="<?php echo $uri; ?>" method="post" role="form">
						<div class="modal-body">
								<input type="hidden" value="true" name="authorize">
								<div class="form-group">
									<label for="authorlogin" class="control-label">Ваш логин</label>
									<input type="text" class="form-control" id="authorlogin" name="authorlogin">
								</div>
								<div class="form-group">
									<label for="authorpassword" class="control-label">Ваш пароль</label>
									<input type="password" class="form-control" id="authorpassword" name="authorpassword">
								</div>
						</div>
						<div class="modal-footer">
							<input class="btn btn-primary" id="GoToAuthor" type="submit" value="Вход" name="GoToAuthor">
					    </div>
						</form>
					</div>
				</div>
			</div>
		</div>-->
	</body>
</html>