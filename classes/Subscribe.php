<?php
class Subscribe extends Base {
	function __construct($query = NULL) {
		parent::__construct($query);
	}
	function show() {
		# REDIRECT THOSE ALREADY LOGGED IN TO MY ACCOUNT PAGE, WEHRE THEY CAN MAANGE THEIR SUBSCRIPTION
		if (isset($_SESSION['user'])) {
			header('Location:/user/myaccount');
			die();
		}
		ob_start();
		?>
		<form action="/subscribe/dosubscribe" method="post" class="form-horizontal">
			<fieldset>
				<legend>Subcribe to [SITE NAME]</legend>
				<div class="control-group">
					<label class="control-label" for="inputFirstName">First Name</label>
					<div class="controls">
					  <input required class="input-xlarge" name="firstname" type="text" id="inputFirstName" placeholder="First Name">
					</div>
				  </div>
				<div class="control-group">
					<label class="control-label" for="inputSurname">Surname</label>
					<div class="controls">
					  <input required class="input-xlarge" name="surname" type="text" id="inputSurname" placeholder="Surname">
					</div>
				  </div>
				<div class="control-group">
					<label class="control-label" for="inputEmail">Email</label>
					<div class="controls">
					  <input required class="input-xlarge" name="email" type="email" id="inputEmail" placeholder="Email">
					</div>
				  </div>
				<div class="control-group">
					<label class="control-label" for="inputPostCode">Post Code</label>
					<div class="controls">
					  <input required class="input-xlarge" name="postcode" type="text" id="inputPostCode" placeholder="Post Code">
					</div>
				  </div>
			  <div class="control-group">
				<div class="controls">
				  <button type="submit" class="btn btn-large btn-primary">Subscribe</button>
				</div>
			  </div>
			</fieldset>
		</form>
		<?php
		echo $this->getHTMLTemplate(ob_get_clean());
	}
	function dosubscribe() {
		ob_start();
		$this->db->execute("INSERT INTO users SET date_created = NOW() , date_last_logged_in = NOW() , login_count = 1, post_code = '{$_POST['postcode']}' , email = '{$_POST['email']}' , first_name = '{$_POST['firstname']}' , surname = '{$_POST['surname']}' , `password` = MD5('stanleyroad') , is_subscriber = '1' , site_id = '{$_SESSION['site']['id']}'");
		# STORE SEESION DETAILS
		$_SESSION['user'] = $this->db->GetRow("SELECT * FROM users WHERE site_id = '{$_SESSION['site']['id']}' AND email = '{$_POST['email']}'");
		header('Location:/user/myaccount');
	}
	function youAreHere() {
		return '<a href="/">Home</a> &raquo; <a href="'.'search">Programmes Online</a>';
	}
}
