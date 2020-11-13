<?php
class Base {
	function __construct($query = '') {
		$this->query = $query;
		# GET DB HANDLE
		include('db.php');
		$this->db = $db;
		//$this->startDebug();

		# GRAB SETTINGS FOR SITE
		list($site,$discard) = explode('.',$_SERVER['HTTP_HOST']);
		$_SESSION['site'] = $this->db->getRow("SELECT * FROM sites WHERE hostname = '$site'");

	}
	function startDebug() {
		$this->db->debug = 1;
	}
	function stopDebug() {
		$this->db->debug = 0;
	}
	function show() {
		$this->show404();
	}
	function debug() {
		# DEBUGGING
		printr($class);
		printr($method);
		printr($args);
		printr($_GET);
		printr($_SESSION);
	}
	function forcelogin() {
		if (!isset($_SESSION['user'])) {
			$_SESSION['error'] = 'You must be logged in to view that page.';
			header('Location:/user/login');
			die();
		}
	}
	function showTitle($title) {
		echo '<h2>'.$title.'</h2>';
	}
	function showMessages($unset = true) {
		if(isset($_SESSION['error'])) {
			echo '<div class="alert alert-error">'.$_SESSION['error'].'</div>';
			if ($unset) unset($_SESSION['error']);
		}
		if(isset($_SESSION['success'])) {
			echo '<div class="alert alert-success">'.$_SESSION['success'].'</div>';
			if ($unset) unset($_SESSION['success']);
		}
	}
	function issues() {
		?>
        <li class="nav-header">Last 7 Days</li>

        <?php
        for ($i=0;$i<=6;$i++) {
	        $date = strtotime("-$i days");
	        echo "<li>";
	        echo "<a href=\"/issues/";
	        echo date('Y/m/d', $date);
	        echo "\">";
	        echo date('l jS F Y', $date);
	        echo "</a>";
	        echo "</li>";
        }
		?>
        <li class="nav-header">Previous Issues by Month</li>
        <?php
        for($i=date('n');$i>0;$i--) {
	        echo "<li>";
	        echo "<a href=\"/issues/";
			echo date('Y').'/'.date('m', mktime(0, 0, 0, ($i), 2, date('Y')));
	        echo "\">";
			echo date('F', mktime(0, 0, 0, ($i), 2, date('Y')))." ".date('Y');
	        echo "</a>";
	        echo "</li>";
		}
        ?>
        <li class="nav-header">Previous Issues by Year</li>
        <li><a href="#">2013</a></li>
        <li><a href="#">2012</a></li>
		<?php
	}
	function showLoggedInNav() {
		ob_start();
		?>
        <ul class="nav">
            <!--<li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Previous Issues <b class="caret"></b></a>
                <ul class="dropdown-menu">
                	<?php $this->issues(); ?>
                </ul>
            </li>-->
			<?php
			if ($_SESSION['user']['is_admin']) echo $this->showAdminNav();
			?>
        </ul>
        <ul class="nav pull-right">
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $_SESSION['user']['first_name'] ; ?> <?php echo $_SESSION['user']['surname'] ; ?> <b class="caret"></b></a>
                <ul class="dropdown-menu">
                    <li><a href="/user/myaccount">My Account</a></li>
                    <li><a href="/user/changepassword">Change Password</a></li>
                    <li><a href="/user/unsubscribe">Unsubscribe</a></li>
                    <li><a href="/user/logout">Log Out</a></li>
                </ul>
            </li>
        </ul>
		<?php
		return ob_get_clean();
	}
	function showAdminNav() {
		ob_start();
		?>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin <b class="caret"></b></a>
                <ul class="dropdown-menu">
                    <li><a href="/admin">Admin Dashboard</a></li>
					<li><a href="/admin/subscribers">Subscribers</a></li>
					<li><a href="/admin/emails">Date-based-Content</a></li>
					<li><a href="/admin/revents">Day-based-Content</a></li>
					<li><a href="/admin/emailsettings">Static Content</a></li>
					<!--<li><a href="/admin/import">Import</a></li>
					<li><a href="/admin/reports">Reports</a></li>-->
					<li><a href="/admin/sitesettings">Site Settings</a></li>
					<li><a href="/admin/links">Links</a></li>
					<!--<li><a href="/admin/help">Help</a></li>-->
                </ul>
            </li>
        </ul>
		<?php
		return ob_get_clean();
	}
	function showLoggedOutNav() {
		ob_start();
		?>
        <ul class="nav">
            <li><a href="/subscribe">Subscribe</a></li>
            <!--<li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Previous Issues <b class="caret"></b></a>
                <ul class="dropdown-menu">
                	<?php $this->issues(); ?>
                </ul>
            </li>-->
        </ul>
        <ul class="nav pull-right">
            <li><a href="/user/login">Log In</a></li>
        </ul>
		<?php
		return ob_get_clean();
	}
	function showNav() {
		if (isset($_SESSION['user'])) return $this->showLoggedInNav();
		else return $this->showLoggedOutNav();
	}
	function show404() {
		header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
		echo $this->getHTMLTemplate('404 Error: Page not found');
	}
	function getHTMLTemplate($html) {

		$template = file_get_contents('template.html');

		$template = str_replace('[CONTENT]',$html,$template);
		$template = str_replace('[URH]',$this->youAreHere(),$template);
		$template = str_replace('[NAV]',$this->showNav(),$template);

		$template = str_replace('[SITE NAME]',$_SESSION['site']['site_name'],$template);
		$template = str_replace('[HERO UNIT]',$_SESSION['site']['hero_unit'],$template);
		$template = str_replace('[CHURCH NAME]',$_SESSION['site']['church_name'],$template);

		# SOCIAL ICONS
		$links = $this->db->getArray("SELECT * FROM links WHERE site_id = '{$_SESSION['site']['id']}'");
		$social_icons = '<table id="social-icons"><tbody>';
		foreach ($links as $link) {
			$social_icons .= '<tr><td><a href="';
			$social_icons .= $link['link'];
			$social_icons .= '"><img alt="';
			$social_icons .= $link['text'];
			$social_icons .= '" src="../img/social-icons/';
			$social_icons .= $link['icon'];
			$social_icons .= '.png" width="24" height="24"></a></td><td><a href="';
			$social_icons .= $link['link'];
			$social_icons .= '">';
			$social_icons .= $link['text'];
			$social_icons .= '</a></td></tr>';
		}
		$social_icons .= '</tbody></table>';
		$template = str_replace('[SOCIAL ICONS]',$social_icons,$template);

		# REPLACE "hostname" in template file with first part of server hostname
		$template = str_replace('hostname',$_SESSION['site']['hostname'],$template);

		return $template;
	}
	function youAreHere() {
		return '<a href="/">Home</a> &raquo; <a href="'.'search">Programmes Online</a>';
	}
}
