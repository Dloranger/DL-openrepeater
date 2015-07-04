<?php
session_start();

include_once("/etc/openrepeater/database.php");

$getVer = $dbConnection->query("SELECT version_num FROM version_info;");
$vertData = $getVer->fetchArray(SQLITE3_ASSOC);

// ----------------------------------------------------------------------------------------------------------------------------------
// Change Password
// ----------------------------------------------------------------------------------------------------------------------------------
if (isset($_POST['action'])){
	if ($_POST['action'] == "setPassword"){

		$password1 = SQLite3::escapeString($_POST['password1']);
		$password2 = SQLite3::escapeString($_POST['password2']);

		// passwords don't match
		if ($password1 != $password2) {
			$dbConnection->close();
			header('location: login.php?action=setPassword&error=mismatch');
			die();
		}

		// password is too long
		if (strlen($password1) > 28) {
			$dbConnection->close();
			header('location: login.php?action=setPassword&error=toolong');
			die();
		}

		// password is too short
		if (strlen($password1) < 8) {
			$dbConnection->close();
			header('location: login.php?action=setPassword&error=tooshort');
			die();
		}

		$usr = $_SESSION['username'];
		$resetQuery = "SELECT username, salt FROM users WHERE username = '$usr';";
		$resetResult = $dbConnection->query($resetQuery);
		$resetData = $resetResult->fetchArray(SQLITE3_ASSOC);

		if (!$resetData['salt']){
			$dbConnection->close();
			header('location: login.php?action=setPassword');
		}

		$resetHash = hash('sha256', $salt . hash('sha256', $password1));
		$hash = hash('sha256', $password1);

		function createSalt(){
			$string = md5(uniqid(rand(), true));
			return substr($string, 0, 8);
		}

		$salt = createSalt();
		$hash = hash('sha256', $salt . $hash);

		$dbConnection->query("UPDATE users SET salt='$salt' WHERE username='$usr'") or die();
		$dbConnection->query("UPDATE users SET password='$hash' WHERE username='$usr'") or die();
		$dbConnection->close();

		$_SESSION = array();
		session_destroy();
		header('location: login.php?error=pwOK');
	}
}

// ----------------------------------------------------------------------------------------------------------------------------------
// Process User Login
// ----------------------------------------------------------------------------------------------------------------------------------
if ((isset($_POST['username'])) && (isset($_POST['password']))){
	$username = SQLite3::escapeString($_POST['username']);
	$password = SQLite3::escapeString($_POST['password']);

	$loginQuery = "SELECT UserID, password, salt FROM users WHERE username = '$username';";
	$loginResult = $dbConnection->query($loginQuery)or die();
	$loginData = $loginResult->fetchArray(SQLITE3_ASSOC);
	
	// User Doesn't Exist
	if (!$loginData['salt']){
		$dbConnection->close();
		header('location: login.php?error=incorrectLogin');
	}

	// User Exists - pull info from DB to compare to submitted credentials
	$loginHash = hash('sha256', $loginData['salt'] . hash('sha256', $password));
	if ($loginHash != $loginData['password']){
		$dbConnection->close();
		header('location: login.php?error=incorrectLogin');

	} else {
		session_regenerate_id();
		$_SESSION['username'] = $username;
		$_SESSION['userID'] = $loginData['userID'];
		$_SESSION['version_num'] = $vertData['version_num'];
		$dbConnection->close();
		header('location: dashboard.php');
	}
}

// ----------------------------------------------------------------------------------------------------------------------------------
//Display Login Form
// ----------------------------------------------------------------------------------------------------------------------------------
if ((!isset($_SESSION['username'])) || (!isset($_SESSION['userID']))){
	if ($_GET['error'] == 'incorrectLogin') {
		$login_msg = '<div class="alert alert-error">Sorry, the Username and Password combination did not match</div>';
	} elseif ($_GET['error'] == 'pwOK') {
		$login_msg = '<div class="alert alert-info">Your Password has been updated successfully!<br>Please login with your Username and Password.</div>';
	} else {
		$login_msg = '<div class="alert alert-info">Please login with your Username and Password.</div>';
	}

	$no_visible_elements=true;
	include('includes/header.php');
	print '
	<div class="row-fluid">
		<div class="span12 center login-header">
			<img src="theme/img/OpenRepeaterLogo-Login.png" title="OpenRepeater" class="login-logo">
		</div><!--/span-->
	</div><!--/row-->
	
	<div class="row-fluid">
		<div class="well span5 center login-box">
			'.$login_msg.'
			<form name="login" action="login.php" method="post" class="form-horizontal">
				<fieldset>
					<div class="input-prepend" title="Username" data-rel="tooltip">
						<span class="add-on"><i class="icon-user"></i></span><input autofocus class="input-large span10" name="username" id="username" type="text" placeholder="Username" />
					</div>
					<div class="clearfix"></div>

					<div class="input-prepend" title="Password" data-rel="tooltip">
						<span class="add-on"><i class="icon-lock"></i></span><input class="input-large span10" name="password" id="password" type="password" placeholder="Password" />
					</div>
					<div class="clearfix"></div>

					<div class="input-prepend">
					<label class="remember" for="remember"><input type="checkbox" id="remember" />Remember me</label>
					</div>
					<div class="clearfix"></div>

					<p class="center span5">
					<button type="submit" class="btn btn-primary">Login</button>
					</p>
				</fieldset>
			</form>
		</div><!--/span-->

		<center><p><a href="http://openrepeater.com" target="_blank">OpenRepeater</a> ver: ' . $vertData['version_num'] . '</p></center>

	</div><!--/row-->
	';
	include('includes/footer.php');
	die();
}

// ----------------------------------------------------------------------------------------------------------------------------------
// Logout and Destroy Session
// ----------------------------------------------------------------------------------------------------------------------------------
if (isset($_GET['action'])){
	if ($_GET['action'] == "logout"){
		$_SESSION = array();
		session_destroy();
		header('Location: login.php');

// ----------------------------------------------------------------------------------------------------------------------------------
// Display Change Password Form
// ----------------------------------------------------------------------------------------------------------------------------------
	} else if ($_GET['action'] == "setPassword"){

	if ($_GET['error'] == 'mismatch') {
		$login_msg = '<div class="alert alert-error">Sorry, Your passwords do not match. Please try again!</div>';
	} else if ($_GET['error'] == 'toolong') {
		$login_msg = '<div class="alert alert-error">Your password must be 28 characters or shorter. Please try again!</div>';
	} else if ($_GET['error'] == 'tooshort') {
		$login_msg = '<div class="alert alert-error">Your password must be at least 8 characters long. Please try again!</div>';
	} else {
		$login_msg = '<div class="alert alert-info">Please Enter Your New Password and Confirm.</div>';
	}

	$no_visible_elements=true;
	include('includes/header.php');
	print '

	<div class="row-fluid">
		<div class="span12 center login-header">
			<img src="theme/img/OpenRepeaterLogo-Login.png" title="OpenRepeater" class="login-logo">
		</div><!--/span-->
	</div><!--/row-->

	<div class="row-fluid">
		<div class="well span5 center login-box">
			'.$login_msg.'
			<form name="changePassword" action="login.php" method="post" class="form-horizontal">
			<h3>Change Password</h3><br>
				<fieldset>

					<input type="hidden" name="action" value="setPassword">		
					<div class="input-prepend" title="Username" data-rel="tooltip">
						<span class="add-on"><i class="icon-lock"></i></span><input autofocus class="input-large span10" name="password1" id="username" type="password" placeholder="New Password" />
					</div>
					<div class="clearfix"></div>

					<div class="input-prepend" title="Password" data-rel="tooltip">
						<span class="add-on"><i class="icon-lock"></i></span><input class="input-large span10" name="password2" id="password" type="password" placeholder="Confirm New Password" />
					</div>
					<div class="clearfix"></div>

					<p class="center span5">
					<button type="submit" class="btn btn-primary">Change Password</button>
					</p>
				</fieldset>
			</form>
		</div><!--/span-->

		<center><p><a href="http://openrepeater.com" target="_blank">OpenRepeater</a> ver: ' . $vertData['version_num'] . '</p></center>

	</div><!--/row-->
	';
	include('includes/footer.php');
	die();
	}
}
?>
