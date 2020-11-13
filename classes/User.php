<?php
class User extends Base {
	function __construct($query = NULL) {
		parent::__construct($query);
	}
	function show() {
		$this->show404();
	}
	function myaccount() {
		$this->forcelogin();
		ob_start();
		$this->showTitle('My Account');
		$this->showMessages();
		?>
		<form action="/user/domyaccount" method="post" class="form-horizontal">
			<fieldset>
				<legend>Edit Account Details</legend>
				<div class="control-group">
					<label required class="control-label" for="first_name">First Name</label>
					<div class="controls">
						<input required class="input-xlarge" name="first_name" type="text" id="first_name" placeholder="First Name" value="<?php echo $_SESSION['user']['first_name'] ; ?>">
					</div>
				</div>
				<div class="control-group">
					<label required class="control-label" for="surname">Surname</label>
					<div class="controls">
						<input required class="input-xlarge" name="surname" type="text" id="surname" placeholder="Surname" value="<?php echo $_SESSION['user']['surname'] ; ?>">
					</div>
				</div>
				<div class="control-group">
					<label required class="control-label" for="email">Email</label>
					<div class="controls">
						<input required class="input-xlarge" name="email" type="email" id="email" placeholder="Email" value="<?php echo $_SESSION['user']['email'] ; ?>">
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="inputPostCode">Post Code</label>
					<div class="controls">
					  <input required class="input-xlarge" name="post_code" type="text" id="inputPostCode" placeholder="Post Code" value="<?php echo $_SESSION['user']['post_code'] ; ?>">
					</div>
				  </div>
				<div class="control-group">
					<label class="control-label" for="is_subscriber">Subscribed</label>
					<div class="controls">
						<input class="input-xlarge" name="is_subscriber" type="checkbox" id="is_subscriber" value="1" <?php if ($_SESSION['user']['is_subscriber'] == 1) echo ' checked="checked" ' ; ?>">
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<button type="submit" class="btn btn-large btn-primary">Save</button>
					</div>
				</div>
			</fieldset>
		</form>
		<?php
		echo $this->getHTMLTemplate(ob_get_clean());
	}
	function domyaccount() {
		$this->forcelogin();
		ob_start();
		if (!isset($_POST['is_subscriber'])) $_POST['is_subscriber'] = '0';
		printr($_POST);


		unset($_SESSION['error']);
		# DID USER SUBMIT EMAIL ADDRESS?
		if (check_email_address($_POST['email'])) {
			# LOOK FOR MATCH BY EMAIL ADDRESS
			$user = $this->db->GetRow("SELECT * FROM users WHERE site_id = '{$_SESSION['site']['id']}' AND email = '{$_POST['email']}' AND id != '{$_SESSION['user']['id']}'");
			if (!$user) {
				# CHECK PASSWORD IS A MATCH
				if (($_POST['first_name'])&&($_POST['surname'])) {
					# UPDATE LAST LOGIN FIELD
					$this->db->Execute("UPDATE users SET email = '{$_POST['email']}' , first_name = '{$_POST['first_name']}' , surname = '{$_POST['surname']}' , is_subscriber = '{$_POST['is_subscriber']}' WHERE id = {$_SESSION['user']['id']}");
					# STORE SESSION DETAILS
					$_SESSION['user'] = $this->db->GetRow("SELECT * FROM users WHERE site_id = '{$_SESSION['site']['id']}' AND email = '{$_POST['email']}'");
				}
				else $_SESSION['error'] = 'That\'s not the right password for this email address.';
			}
			else $_SESSION['error'] = 'The email address you provided is already a subscriber.';
		}
		else {
			$_SESSION['error'] = 'The email address you provided doesn\'t look correct.';
		}

		if ($_SESSION['error']) {
			$_SESSION['error'] = '<strong>There was a problem updating your account:</strong> '.$_SESSION['error'];
		}
		header('Location:/user/myaccount');

		echo $this->getHTMLTemplate(ob_get_clean());
	}
	function unsubscribe() {
		$this->forcelogin();
		ob_start();
		$this->db->Execute("UPDATE users SET is_subscriber = '0' WHERE id = {$_SESSION['user']['id']}");
		# STORE SESSION DETAILS
		$_SESSION['user'] = $this->db->GetRow("SELECT * FROM users WHERE site_id = '{$_SESSION['site']['id']}' AND email = '{$_SESSION['user']['email']}'");
		echo '<p>You are now unsubsribed. You can subscribe again at any time by visiting the <a href="/user/myaccount">My Account</a> page.</p>';
		echo $this->getHTMLTemplate(ob_get_clean());
	}
	function upgrade() {
		$this->forcelogin();
		ob_start();
		?>
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
		<input type="hidden" name="cmd" value="_xclick-subscriptions">
		<input type="hidden" name="business" value="morecambe@gmail.com">
		<input type="hidden" name="lc" value="GB">
		<input type="hidden" name="item_name" value="Membership">
		<input type="hidden" name="item_number" value="uniquematt">
		<input type="hidden" name="src" value="1">
		<input type="hidden" name="currency_code" value="GBP">
		<input type="hidden" name="bn" value="PP-SubscriptionsBF:btn_subscribeCC_LG.gif:NonHosted">
		<table>
		<tr><td><input type="hidden" name="on0" value="Membership options">Membership options</td></tr><tr><td><select name="os0">
			<option value="Monthly membership">Monthly membership : &9.99 GBP - monthly</option>
			<option value="Annual Membership">Annual Membership : &99.00 GBP - yearly</option>
		</select> </td></tr>
		</table>
		<input type="hidden" name="currency_code" value="GBP">
		<input type="hidden" name="option_select0" value="Monthly membership">
		<input type="hidden" name="option_amount0" value="9.99">
		<input type="hidden" name="option_period0" value="M">
		<input type="hidden" name="option_frequency0" value="1">
		<input type="hidden" name="option_select1" value="Annual Membership">
		<input type="hidden" name="option_amount1" value="99.00">
		<input type="hidden" name="option_period1" value="Y">
		<input type="hidden" name="option_frequency1" value="1">
		<input type="hidden" name="option_index" value="0">
		<input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_subscribeCC_LG.gif" border="0" name="submit" alt="PayPal — The safer, easier way to pay online.">
		<img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
		</form>
		<?php
		echo $this->getHTMLTemplate(ob_get_clean());
	}
	function logout() {
		$this->forcelogin();
		session_start();
		unset($_SESSION);
		session_destroy();
		header('Location:../');
	}
	function login() {
		ob_start();
		$this->showMessages();
		?>
		<form action="/user/dologin" method="post" class="form-horizontal">
			<fieldset>
				<legend>Log In</legend>
				<div class="control-group">
				<label required class="control-label" for="inputEmail">Email</label>
				<div class="controls">
				  <input required class="input-xlarge" name="email" type="email" id="inputEmail" placeholder="Email">
				</div>
			  </div>
			  <div class="control-group">
				<label class="control-label" for="inputPassword">Password</label>
				<div class="controls">
				  <input class="input-xlarge" name="password" type="password" id="inputPassword" placeholder="Password">
				</div>
			  </div>
			  <div class="control-group">
				<div class="controls">
				  <button type="submit" class="btn btn-large btn-primary">Log In</button>
				  <a class="btn btn-large" href="/user/forgottenpassword">Forgotten your password?</a>
				</div>
			  </div>
			</fieldset>
		</form>
		<?php
		echo $this->getHTMLTemplate(ob_get_clean());
	}
	function dologin() {
		unset($_SESSION['error']);
		# DID USER SUBMIT EMAIL ADDRESS?
		if (check_email_address($_POST['email'])) {
			# LOOK FOR MATCH BY EMAIL ADDRESS
			$user = $this->db->GetRow("SELECT * FROM users WHERE site_id = '{$_SESSION['site']['id']}' AND email = '{$_POST['email']}'");
			if ($user) {
				# CHECK PASSWORD IS A MATCH
				if (md5($_POST['password']) == $user['password']) {
					# UPDATE LAST LOGIN FIELD
					$this->db->Execute("UPDATE users SET date_last_logged_in = NOW() , login_count = login_count+1 WHERE id = {$user['id']}");
					# STORE SESSION DETAILS
					$_SESSION['user'] = $this->db->GetRow("SELECT * FROM users WHERE site_id = '{$_SESSION['site']['id']}' AND email = '{$_POST['email']}'");
				}
				else $_SESSION['error'] = 'That\'s not the right password for this email address.';
			}
			else $_SESSION['error'] = 'The email address you provided isn\'t a subscriber.';
		}
		else {
			$_SESSION['error'] = 'The email address you provided doesn\'t look correct.';
		}
		# REDIRECT USER
		if ($_SESSION['error']) {
			$_SESSION['error'] = '<strong>There is a problem logging you in:</strong> '.$_SESSION['error'];
			header('Location:/user/login');
		}
		elseif ($_SESSION['user']['is_admin']) header('Location:/admin');
		else  header('Location:/user/myaccount');
	}
	function forgottenpassword() {
		ob_start();
		$this->showTitle('Reset Password');
		$this->showMessages();
		?>
		<form action="/user/doforgottenpassword" method="post" class="form-horizontal">
			<fieldset>
				<legend>Forgotten Password</legend>
				<div class="control-group">
				<label required class="control-label" for="inputEmail">Email</label>
				<div class="controls">
				  <input required class="input-xlarge" name="email" type="email" id="inputEmail" placeholder="Email">
				</div>
			  </div>
			  <div class="control-group">
				<div class="controls">
				  <button type="submit" class="btn btn-large btn-primary">Reset Password</button>
				</div>
			  </div>
			</fieldset>
		</form>
		<?php
		echo $this->getHTMLTemplate(ob_get_clean());
	}
	function doforgottenpassword() {
		unset($_SESSION['error']);
		# DID USER SUBMIT EMAIL ADDRESS?
		if (check_email_address($_POST['email'])) {
			# LOOK FOR MATCH BY EMAIL ADDRESS
			$user = $this->db->GetRow("SELECT * FROM users WHERE site_id = '{$_SESSION['site']['id']}' AND email = '{$_POST['email']}'");
			if ($user) {
				$auth_key = md5(createRandomPassword());
				$this->db->Execute("UPDATE users SET auth_key = '$auth_key' WHERE id = {$user['id']}");
				$link = 'http://'.$_SERVER['HTTP_HOST'].'/user/resetpassword/'.$auth_key;
				site_email($_POST['email'],'Reset Your Password',"<p>Someone requested that we reset the password for user {$user['email']} on {$_SESSION['site']['site_name']}</p><p>If you are expecting this email, please click the link below. Otherwise, ignore this email and no further action is necessary.</p><p><a href=\"$link\">$link</a></p>");
			}
			else $_SESSION['error'] = 'The email address you provided isn\'t a subscriber.';
		}
		else {
			$_SESSION['error'] = 'The email address you provided doesn\'t look correct.';
		}
		# REDIRECT USER
		if ($_SESSION['error']) {
			$_SESSION['error'] = '<strong>There was a problem:</strong> '.$_SESSION['error'];
			header('Location:/user/forgottenpassword');
		}
		else {
			$_SESSION['success'] = 'We\'ve sent you a password reset link. Please check your email to continue.';
			header('Location:/user/doneforgottenpassword');
		}
	}
	function doneforgottenpassword() {
		ob_start();
		$this->showTitle('Reset Password');
		$this->showMessages();
		echo $this->getHTMLTemplate(ob_get_clean());
	}
	function resetpassword() {
		ob_start();
		$this->showTitle('Reset Password');
		# LOOK FOR MATCH BY AUTH KEY
		$auth_key = $this->query;
		if (($auth_key != '')&&(!is_array($auth_key))) {
			$user = $this->db->GetRow("SELECT * FROM users WHERE site_id = '{$_SESSION['site']['id']}' AND auth_key = '$auth_key'");
			if ($user) {
				# UPDATE LAST LOGIN FIELD
				$this->db->Execute("UPDATE users SET date_last_logged_in = NOW() , login_count = login_count+1 WHERE id = {$user['id']}");
				# STORE SEESION DETAILS
				$_SESSION['user'] = $this->db->GetRow("SELECT * FROM users WHERE site_id = '{$_SESSION['site']['id']}' AND auth_key = '$auth_key'");
			}
			else $_SESSION['error'] = 'The authorisation key you provided - '.$auth_key.' - could not be found.';
		}
		else $_SESSION['error'] = 'The authorisation key you provided - '.$auth_key.' - isn\'t valid.';

		# REDIRECT USER
		if(isset($_SESSION['error'])) $_SESSION['error'] = '<strong>There was a problem:</strong> '.$_SESSION['error'];

		$this->showMessages(false);

		?>
		<form action="/user/doresetpassword" method="post" class="form-horizontal">
			<fieldset>
				<legend>Reset Password</legend>
				<div class="control-group">
					<label required class="control-label" for="newpassword1">New Password</label>
					<div class="controls">
						<input required class="input-xlarge" name="newpassword1" type="password" id="newpassword1" placeholder="New Password">
					</div>
				</div>
				<div class="control-group">
					<label required class="control-label" for="newpassword2">Re-Type New Password</label>
					<div class="controls">
						<input required class="input-xlarge" name="newpassword2" type="password" id="newpassword2" placeholder="Re-Type New Password">
					</div>
				</div>
			  <div class="control-group">
				<div class="controls">
				  <button type="submit" class="btn btn-large btn-primary">Reset Password</button>
				</div>
			  </div>
			</fieldset>
		</form>
		<?php
		echo $this->getHTMLTemplate(ob_get_clean());
	}
	function doresetpassword() {
		$this->forcelogin();
		unset($_SESSION['error']);
		if (($_POST['newpassword1'] == $_POST['newpassword2'])) {
			$this->db->Execute("UPDATE users SET password = MD5('{$_POST['newpassword1']}') , auth_key = '' , WHERE id = {$_SESSION['user']['id']}");
		}
		else {
			$_SESSION['error'] = 'The new passwords you provided did not match.';
		}
		# REDIRECT USER
		if ($_SESSION['error']) {
			$_SESSION['error'] = '<strong>There was a problem:</strong> '.$_SESSION['error'];
			header('Location:/user/resetpassword/'.$_SESSION['user']['authkey']);
		}
		else header('Location:/user/donechangepassword');
	}
	function changepassword() {
		$this->forcelogin();
		ob_start();
		$this->showTitle('Change Password');
		$this->showMessages();
		?>
		<form action="/user/dochangepassword" method="post" class="form-horizontal">
			<fieldset>
				<legend>Change Password</legend>
				<div class="control-group">
					<label required class="control-label" for="oldpassword">Old Password</label>
					<div class="controls">
						<input required class="input-xlarge" name="oldpassword" type="password" id="oldpassword" placeholder="Old Password">
					</div>
				</div>
				<div class="control-group">
					<label required class="control-label" for="newpassword1">New Password</label>
					<div class="controls">
						<input required class="input-xlarge" name="newpassword1" type="password" id="newpassword1" placeholder="New Password">
					</div>
				</div>
				<div class="control-group">
					<label required class="control-label" for="newpassword2">Re-Type New Password</label>
					<div class="controls">
						<input required class="input-xlarge" name="newpassword2" type="password" id="newpassword2" placeholder="Re-Type New Password">
					</div>
				</div>
			  <div class="control-group">
				<div class="controls">
				  <button type="submit" class="btn btn-large btn-primary">Change Password</button>
				</div>
			  </div>
			</fieldset>
		</form>
		<?php
		echo $this->getHTMLTemplate(ob_get_clean());
	}
	function dochangepassword() {
		$this->forcelogin();
		unset($_SESSION['error']);
		if (md5($_POST['oldpassword'] == $_SESSION['user']['password'])) {
			if (($_POST['newpassword1'] == $_POST['newpassword2'])) {
				$this->db->Execute("UPDATE users SET password = MD5('{$_POST['newpassword1']}') WHERE id = {$_SESSION['user']['id']}");
			}
			else {
				$_SESSION['error'] = 'The new passwords you provided did not match.';
			}
		}
		else {
			$_SESSION['error'] = 'The old password you provided is incorrect.';
		}
		# REDIRECT USER
		if ($_SESSION['error']) {
			$_SESSION['error'] = '<strong>There was a problem:</strong> '.$_SESSION['error'];
			header('Location:/user/changepassword');
		}
		else header('Location:/user/donechangepassword');
	}
	function donechangepassword() {
		$this->forcelogin();
		ob_start();
		$this->showTitle('Change Password');
		echo '<div class="alert alert-success">Your password has been changed.</div>';
		echo $this->getHTMLTemplate(ob_get_clean());
	}
	function youAreHere() {
		return '<a href="/">Home</a> &raquo; <a href="'.'search">Programmes Online</a>';
	}
}
