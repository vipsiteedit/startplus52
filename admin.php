<?php

$url = '/admin.php';

session_start();
define('SE_INDEX_INCLUDED',1);
define('SE_ADMIN_INCLUDED',1);
error_reporting(0);
if (isset($_GET['err_rep'])) {
    error_reporting(E_ALL);
}

if (isset($_GET['clearhost']) || (isset($_GET['access']) && !isset($_GET['forcehost']))) {
	unset($_SESSION['forcehost'], $_SESSION['editor_page']);
} elseif (isset($_GET['forcehost'])) {
	define('SE_FORCE_HOST',$_GET['forcehost']);
	$_SESSION['forcehost'] = SE_FORCE_HOST;
	header('Location: '.$url);
	exit;
} elseif (isset($_SESSION['forcehost'])) {
	define('SE_FORCE_HOST',$_SESSION['forcehost']);
}

include 'system/main/init.php';

if (isset($_GET['access'])){
  $serial = join('', file('system/license.dat'));
    if (md5($serial.date('Y-m-d')) == $_GET['access']){
        seAuthAdmin();
        $_SESSION['editor_access'] = true;
        header("Location: /admin.php");
    }
    exit;
}



if (isset($_GET['logout'])) {
	check_session(true);
	header('Location: '.$url);
}
check_session(false);
if (isset($_GET['startpage'])) {
	$page = $_GET['startpage'];
}
elseif (isset($_SESSION['editor_page']) && $_SESSION['editor_page'] != '1') {
	$page = $_SESSION['editor_page'];
} else {
	$page = '';
}

if (isset($_GET['err_rep'])) {
	error_reporting(E_ALL);
}

$user_gr = seUserGroup();

//var_dump($_SESSION['EDITOR_ADMIN']);
$d = seData::getInstance($page);

if (!function_exists('lv')) {
    function lv($var, $key = 'ed') {
        global $d;
        return $d->editor->getTextLanguage($var, $key);
    }
}

if (isset($_GET['interfacelang'])) {
    header('Location: '.$url.'?'.time());
    exit;
}

if (empty($user_gr)) {
	include 'admin/login.php';
	exit;
} elseif ($user_gr!=3) {
	$message = 'insufficient_rights';
	include 'admin/login.php';
	exit;
} elseif (!$_SESSION['EDITOR_ADMIN']) {
	$_SESSION['EDITOR_ADMIN'] = 1;
	header('Location: '.$url.'?'.time());
	exit;
}

$activelang = $activelangname = $d->editor->getLang();
$langfile = SE_ROOT.'admin/i18n/langs.dat';
$langs = array();
if (file_exists($langfile)) {
    $langfile = file_get_contents($langfile);
    $langfile = explode(',',$langfile);
    foreach ($langfile as $v) {
        $v = explode('=',$v);
        if (count($v)==1) {
            $langs[$v[0]] = $v[0];
        } elseif (count($v)>1) {
            $langs[$v[0]] = $v[1];
        } else continue;
        if (array_key_exists($activelang,$langs)) {
            $activelangname = $langs[$activelang];
            unset($langs[$activelang]);
        }
    }
}

$page = $d->pagename;

if ($page == $d->prj->vars->startpage) {
    $content_url = SE_MULTI_DIR.'/';
} else {
    $content_url = SE_MULTI_DIR.'/'.$page.'/';
}

if (isset($_GET['editor_on_off']) && $d->editor->editorAccess()) {
    $_SESSION['siteediteditor'] = ($_SESSION['siteediteditor']) ? false : true;
    echo ($d->editorAccess() && !$_SESSION['siteediteditor']) ? 1 : 0;
    exit;
}
if (isset($_GET['editorstatus'])) {
    echo ($d->editorAccess() && !$_SESSION['siteediteditor']) ? 1 : 0;
    exit;
}

?>
<!DOCTYPE HTML>
<html>
<head>
	<title><?php echo lv('titleeditor'); ?> | <?php echo $_SERVER['HTTP_HOST']; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="/admin/assets/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="/admin/assets/skin/style.css">
	
</head>
<body>
	<div id="loader_overlay">
		</div>
	<div id="editor_header">
		<nav class="navbar navbar-inverse navbar-static-top" role="navigation">
			<div class="container-fluid">
				<div class="navbar-header">
			      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
			        <span class="sr-only"><?php echo lv('navbar_navigation'); ?></span>
			        <span class="icon-bar"></span>
			        <span class="icon-bar"></span>
			        <span class="icon-bar"></span>
			      </button>
			      <a class="navbar-brand" href="#"><img class="img-responsive" src="/admin/assets/icons/siteedit.png" alt="<?php echo lv('titleeditor'); ?>"></a>
			    </div>
			    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
			    	<div class="navbar-input-group col-xs-3 navbar-left">
			    		<div class="input-group">
			    			    <?php if(empty($_SESSION['editor_access'])): ?>
						      <div class="input-group-btn">
						        <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown"><?php echo SE_DIR; ?> <span class="caret"></span></button>
						        <ul class="dropdown-menu">
						          <?php foreach ($se_hostname as $host=>$path): ?>
						          	<li><a href="<?php echo $url; ?>?forcehost=<?php echo $path; ?>">
						          		<?php echo (!is_numeric($host)) ? $host : $path; ?>
						          	</a></li>
						          <?php endforeach; ?>
						        </ul>
						      </div><!-- /btn-group -->
						      <?php endif ?>
						      <!--<span class="input-group-addon">Страница:</span>-->
				        	<select disabled="disabled" 
				        		class="form-control" 
				        		data-event-change="page_select" 
				        		data-subject="page">
				        		<?php foreach ($d->pages as $pagelink): ?>
									<option value="<?php echo SE_MULTI_DIR.'/'.$pagelink['name'].'/'; ?>"<?php if ($pagelink['name']==$page) echo 'selected'; ?>><?php echo $pagelink->title; ?></option>
								<?php endforeach; ?>
				        	</select>
				        	</div>
				        </div>
			    	<form class="navbar-form navbar-left">
				    	<?php
							$editor_on = '/admin/assets/icons/editor_view.png';
							$editor_off = '/admin/assets/icons/editor_view_off.png';
						?>
						<button disabled="disabled"
							class="btn btn-default btn-image" 
							data-on="<?php echo $editor_on; ?>" 
							data-off="<?php echo $editor_off; ?>"
							data-event="editor_switch" data-subject="page"
							data-toggle="tooltip" data-placement="bottom" 
							title="<?php echo ($_SESSION['siteediteditor']) ? lv('hide') : lv('show'); ?>"
						>
							<img src="<?php echo ($_SESSION['siteediteditor']) ? $editor_off : $editor_on; ?>">					
						</button>
						<button disabled="disabled"
							class="btn btn-default btn-image" 
							data-event="image_select" data-subject="image"
							data-toggle="tooltip" data-placement="bottom" 
							title="<?php echo lv('editor','img'); ?>">
							<img src="/admin/assets/icons/images.png">				
						</button>
						<button disabled="disabled"
							class="btn btn-default btn-image" id="editor_menu" 
							data-event="editor_menu" 
							data-subject="menu"
							data-toggle="tooltip" data-placement="bottom" 
							title="<?php echo lv('editor','menu'); ?>"
						>
							<img src="/system/main/editor/images/editor_menu.png">					
						</button>
						<button disabled="disabled" 
						      class="btn btn-default btn-image" id="editor_addpage" 
						      data-event="editor_addpage" data-subject="page"
						      data-toggle="tooltip" data-placement="bottom" 
                              title="<?php echo lv('add','page'); ?>"
                        >
							<img src="/system/main/editor/images/editor_addpage.png">	
						</button>
						<button disabled="disabled" 
						      class="btn btn-default btn-image" id="editor_editpage" 
						      data-event="editor_editpage" data-subject="page"
						      data-toggle="tooltip" data-placement="bottom" 
                              title="<?php echo lv('edit','page'); ?>"
                        >
							<img src="/system/main/editor/images/editor_editpage.png">	
						</button>
						<button disabled="disabled" class="btn btn-default btn-image" id="editor_editcontacts" data-event="editor_editcontacts" data-subject="page"
						      data-toggle="tooltip" data-placement="bottom" 
                              title="<?php echo lv('contacts','var'); ?>"
                        >
							<img src="/system/main/editor/images/editor_contacts.png">	
						</button>
				    </form>
				    <form class="navbar-form navbar-right">
				    	<button disabled="disabled" class="btn btn-success" id="editor_save" data-event="editor_save" data-subject="page">
				    	    <?php echo lv('save'); ?>
				    	</button>
						<button disabled="disabled" class="btn btn-default" id="editor_cancel" data-event="editor_cancel" data-subject="page">
						    <?php echo lv('cancel'); ?>
						</button>
				    </form>
				    <ul class="nav navbar-nav navbar-right">
				        <li class="dropdown">
				          <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo seUserName(); ?> <b class="caret"></b></a>
				          <ul class="dropdown-menu">
				            <li><a href="?logout"><?php echo lv('logout', 'mes'); ?></a></li>
				          </ul>
				        </li>
				    </ul>
				    <ul class="nav navbar-nav navbar-right">
                        <?php if (!empty($langs)): ?>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $activelangname; ?> <b class="caret"></b></a>
                                <ul class="dropdown-menu">
                                    <?php foreach ($langs as $k=>$v): ?>
                                        <li><a href="?interfacelang=<?php echo $k; ?>"><?php echo $v; ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li><a href="#"><?php echo $activelang; ?></a></li>
                        <?php endif; ?>
                    </ul>
			    </div>
			</div>
		</nav>
	</div>
	<div id="editor_content">
		<div id="editor_frame_window"></div>
	</div>
	<div id="editor_modal"></div>
	<script type="text/javascript" src="/admin/assets/jquery-1.11.0.js"></script>
	<script type="text/javascript" src="/admin/assets/jquery.regex.js"></script>
	<script type="text/javascript" src="/admin/assets/jquery.regex.js"></script>
	<script type="text/javascript" src="/admin/event_handler_js.php?<?php echo time(); ?>"></script>
	<script type="text/javascript" src="/admin/js/function.js"></script>
	<script type="text/javascript" src="/admin/assets/bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="/admin/assets/jquery/jquery.splitter.js"></script>
	<script type="text/javascript" src="/admin/assets/tinymce/tinymce.min.js"></script>
	<script type="text/javascript" src="/admin/assets/tinymce/jquery.tinymce.min.js"></script> 
	<script type="text/javascript" src="/lib/js/ui/jquery.ui.core.js"></script>
	<script type="text/javascript" src="/lib/js/ui/jquery.ui.widget.js"></script>
	<script type="text/javascript" src="/lib/js/ui/jquery.ui.mouse.js"></script>
	<script type="text/javascript" src="/lib/js/ui/jquery.ui.draggable.js"></script>
	<script type="text/javascript" src="/lib/js/ui/jquery.ui.droppable.js"></script>
	<script type="text/javascript" src="/lib/js/ui/jquery.ui.sortable.js"></script>
	<script type="text/javascript" src="/lib/js/ui/jquery.ui.position.js"></script>
	<script type="text/javascript" src="/admin/assets/jquery.ui.nestedSortable.js"></script>
	<script type="text/javascript">
		var windowSize = function() {
			var h1 = $('#editor_header').outerHeight();//+$('#editor_line').outerHeight();
			var h2 = $(window).height();
			$('#editor_content').css('height',(h2-h1-5)+"px");
		}
		$(window).load(function() {
			windowSize();
			$('#editor_frame_window').html('<iframe frameborder=0 name="content" src="<?php echo $content_url; ?>" width="100%" height="100%"></iframe>');
			$('#editor_frame_window').find('iframe').load(function() {
				$('#editor_frame_window').find('iframe').unbind('load');
				seEvents.init({
					viewer: $('#editor_frame_window').find('iframe'),
					modal: '#editor_modal',
					loader: '#loader_overlay',
					url: "<?php echo $url; ?>",
					content_url: "<?php echo $content_url; ?>",
					se_dir: '<?php echo SE_MULTI_DIR; ?>',
				});
			});
		});
		$(window).resize(function(){
			windowSize();
		})
	</script>
</body>
</html>