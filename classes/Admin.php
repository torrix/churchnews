<?php
class Admin extends Base {
	function __construct($query = NULL) {
		parent::__construct($query);
	}
	function show() {
		$this->forcelogin();
		ob_start();
		$this->adminTabs();
		echo $this->getHTMLTemplate(ob_get_clean());
	}
	function adminTabs() {
		global $method;
		?>
		<ul class="nav nav-tabs">
			<li<?php if ($method == 'show') echo ' class="active"' ; ?>><a href="/admin">Dashboard</a></li>
			<li<?php if ($method == 'subscribers') echo ' class="active"' ; ?>><a href="/admin/subscribers">Subscribers</a></li>
			<li<?php if (($method == 'emails') || ($method == 'email')) echo ' class="active"' ; ?>><a href="/admin/emails">Date-based Content</a></li>
			<li<?php if ($method == 'revents') echo ' class="active"' ; ?>><a href="/admin/revents">Day-based Content</a></li>
			<li<?php if ($method == 'emailsettings') echo ' class="active"' ; ?>><a href="/admin/emailsettings">Static Content</a></li>
			<!--<li<?php if ($method == 'import') echo ' class="active"' ; ?>><a href="#">Import</a></li>
			<li<?php if ($method == 'reports') echo ' class="active"' ; ?>><a href="#">Reports</a></li>-->
			<li<?php if ($method == 'sitesettings') echo ' class="active"' ; ?>><a href="/admin/sitesettings">Site Settings</a></li>
			<li<?php if ($method == 'links') echo ' class="active"' ; ?>><a href="/admin/links">Links</a></li>
			<!--<li<?php if ($method == 'help') echo ' class="active"' ; ?>><a href="#">Help</a></li>-->
		</ul>
		<?php
	}
	function sitesettings() {
		$this->forcelogin();
		ob_start();
		$this->adminTabs();
		$sitesettings = $this->db->getRow("SELECT site_name,church_name,hero_unit,contact_details FROM sites WHERE id = '{$_SESSION['site']['id']}'");
		//printr($sitesettings);
		?>
		
		<label for="inputchurch_name">Church Name</label>
		<input class="input-block-level" type="text" placeholder="The full name of your Church" name="church_name" id="inputchurch_name" value="<?php echo htmlentities($sitesettings['church_name']) ; ?>">
		
		<label for="inputsite_name">Mailing Name</label>
		<input class="input-block-level" type="text" placeholder="A name for the daily email service" name="site_name" id="inputsite_name" value="<?php echo htmlentities($sitesettings['site_name']) ; ?>">
		
		<label for="inputhero_unit">Welcome Text</label>
		<div class="wysiwyg"><?php echo ($sitesettings['hero_unit']) ; ?></div>
		
		<label for="inputcontact_details">Contact Details</label>
		<div class="wysiwyg"><?php echo ($sitesettings['contact_details']) ; ?></div>
		
		<?php
		echo $this->getHTMLTemplate(ob_get_clean());
	}
	function email() {
		$this->forcelogin();
		ob_start();
		$this->adminTabs();
		$strDate = $_GET['date'];
		$dow = date('N',strtotime($strDate));
		echo "<h2>".date('D jS M Y',strtotime($strDate))."</h2>";
		$motd = $this->db->getRow("SELECT * FROM motds WHERE area_id = '1' AND site_id = '{$_SESSION['site']['id']}' AND motd_date = '$strDate'");
		if (!isset($motd['motd'])) $motd['motd'] = '';
		?>
		<h3>Header</h3>
		<div class="wysiwyg"><?php echo $motd['motd'] ; ?></div>
		<?php
		$motd = $this->db->getRow("SELECT * FROM motds WHERE area_id = '2' AND site_id = '{$_SESSION['site']['id']}' AND motd_date = '$strDate'");
		if (!isset($motd['motd'])) $motd['motd'] = '';
		?>
		<h3>Devotional</h3>
		<div class="wysiwyg"><?php echo $motd['motd'] ; ?></div>
		<?php
		$motd = $this->db->getRow("SELECT * FROM motds WHERE area_id = '4' AND site_id = '{$_SESSION['site']['id']}' AND motd_date = '$strDate'");
		if (!isset($motd['motd'])) $motd['motd'] = '';
		?>
		<h3>Prayer Text</h3>
		<div class="wysiwyg"><?php echo $motd['motd'] ; ?></div>
		<?php
		$motd = $this->db->getRow("SELECT * FROM motds WHERE area_id = '3' AND site_id = '{$_SESSION['site']['id']}' AND motd_date = '$strDate'");
		if (!isset($motd['motd'])) $motd['motd'] = '';
		?>
		<h3>Footer</h3>
		<div class="wysiwyg"><?php echo $motd['motd'] ; ?></div>
		<h3>Special Events</h3>
		<?php
		$events = $this->db->getArray("SELECT id,title FROM events WHERE site_id = '{$_SESSION['site']['id']}' AND event_date = '$strDate' ORDER BY sortable_time");
		foreach ($events as $event) {
			echo '<br><span class="label'.((1)?' label-success':'').'">'.substr($event['title'],0,16).((strlen($event['title'])>16)?'...':'').'</span>';
		}
		?>
		<h3>Regular Events</h3>
		<?php
		$revents = $this->db->getArray("SELECT id,title FROM revents WHERE site_id = '{$_SESSION['site']['id']}' AND dow = '$dow' ORDER BY sortable_time");
		foreach ($revents as $revent) {
			$x = $this->db->getOne("SELECT COUNT(id) FROM revents_exceptions WHERE exception_date = '$strDate' AND revent_id = '{$revent['id']}'");
			echo '<br><span class="label'.(($x)?' label-important':' label-success').'">'.substr($revent['title'],0,16).((strlen($revent['title'])>16)?'...':'').'</span>';
		}
		echo $this->getHTMLTemplate(ob_get_clean());
	}
	function revents() {
		$this->forcelogin();
		ob_start();
		$this->adminTabs();
		$days = array('','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
		for ($i = 1; $i <= 7 ; $i++) {
			echo "<h2>{$days[$i]}</h2>";
			?>
			<p>
				<a href="/admin/revent" class="btn">Add new event on <?php echo "{$days[$i]}" ; ?>s</a>
			</p>
			<?php
			$revents = $this->db->getArray("SELECT * FROM revents WHERE site_id = '{$_SESSION['site']['id']}' AND dow = '$i' ORDER BY sortable_time");
			foreach($revents as $revent) {
				?>
				<form class="form-horizontal">
					<fieldset>
						<legend><?php echo $revent['title'] ; ?></legend>
						<div class="row">
							
							<div class="span5">
								<div class="control-group">
									<label class="control-label" for="inputtitle<?php echo $revent['id'] ; ?>">Event Name</label>
									<div class="controls">
										<input class="input-xlarge" type="text" id="inputtitle<?php echo $revent['id'] ; ?>" name="title" placeholder="" value="<?php echo htmlentities($revent['title']) ; ?>">
									</div>
								</div>
								<div class="control-group">
									<label class="control-label" for="inputplace<?php echo $revent['id'] ; ?>">Place</label>
									<div class="controls">
										<input class="input-xlarge" type="text" id="inputplace<?php echo $revent['id'] ; ?>" name="place" placeholder="" value="<?php echo htmlentities($revent['place']) ; ?>">
									</div>
								</div>
								<div class="control-group">
									<label class="control-label" for="inputdow<?php echo $revent['id'] ; ?>">Day of Week</label>
									<div class="controls">
										<input class="input-xlarge" type="text" id="inputdow<?php echo $revent['id'] ; ?>" name="dow" placeholder="" value="<?php echo htmlentities($revent['dow']) ; ?>">
									</div>
								</div>
								<div class="control-group">
									<label class="control-label" for="inputsortable_time<?php echo $revent['id'] ; ?>">Start Time</label>
									<div class="controls">
										<input class="input-xlarge" type="text" id="inputsortable_time<?php echo $revent['id'] ; ?>" name="sortable_time" placeholder="HH:MM:SS" value="<?php echo htmlentities($revent['sortable_time']) ; ?>">
										<span class="help-block">e.g.: "10:00:00" - so we can sort events</span>
									</div>
								</div>
								<div class="control-group">
									<label class="control-label" for="inputrevent_time<?php echo $revent['id'] ; ?>">Readable Time</label>
									<div class="controls">
										<input class="input-xlarge" type="text" id="inputrevent_time<?php echo $revent['id'] ; ?>" name="revent_time" placeholder="In a readable format" value="<?php echo htmlentities($revent['revent_time']) ; ?>">
										<span class="help-block">e.g.: "7pm for a 7:15pm start"</span>
									</div>
								</div>
							</div>
						
							<div class="span7">
								<div class="control-group">
									<label class="control-label" for="inputdetails<?php echo $revent['id'] ; ?>">Description</label>
									<div class="controls">
										<textarea class="input-block-level" name="details" id="inputdetails<?php echo $revent['id'] ; ?>" rows="10"><?php echo htmlentities($revent['details']) ; ?></textarea>
									</div>
								</div>
								<div class="control-group">
									<div class="controls">
										<button type="submit" class="btn">Save Event</button>
									</div>
								</div>
							</div>
							
						</div>		
					</fieldset>
				</form>
				<?php
			}
		}
		echo $this->getHTMLTemplate(ob_get_clean());
	}
	function links() {
		$this->forcelogin();
		ob_start();
		$this->adminTabs();
		$links = $this->db->getArray("SELECT * FROM links WHERE site_id = '{$_SESSION['site']['id']}'");
		printr($links);
		echo $this->getHTMLTemplate(ob_get_clean());
	}
	function emailsettings() {
		$this->forcelogin();
		ob_start();
		$this->adminTabs();
		$emailsettings = $this->db->getRow("SELECT pre_text,motd_heading,prayer_heading,prayer,today_heading,ahead_heading,post_text FROM sites WHERE id = '{$_SESSION['site']['id']}'");
		?>
		
		<label for="inputpre_text">Welcome Text</label>
		<div class="wysiwyg"><?php echo ($emailsettings['pre_text']) ; ?></div>
		
		<label for="inputmotd_heading">Daily Message Title</label>
		<input class="input-block-level" type="text" placeholder="The title for your daily bible reading or thought for the day" name="motd_heading" id="inputmotd_heading" value="<?php echo htmlentities($emailsettings['motd_heading']) ; ?>">
		
		<label for="inputprayer_heading">Prayer Title</label>
		<input class="input-block-level" type="text" placeholder="The title for your 'Prayer' section" name="prayer_heading" id="inputprayer_heading" value="<?php echo htmlentities($emailsettings['prayer_heading']) ; ?>">
		
		<label for="inputprayer">Prayer Text</label>
		<div class="wysiwyg"><?php echo ($emailsettings['prayer']) ; ?></div>
		
		<label for="inputtoday_heading">Today's Events Title</label>
		<input class="input-block-level" type="text" placeholder="Title for today's events" name="today_heading" id="inputtoday_heading" value="<?php echo htmlentities($emailsettings['today_heading']) ; ?>">
		
		<label for="inputahead_heading">Future Events Title</label>
		<input class="input-block-level" type="text" placeholder="Title for future events" name="ahead_heading" id="inputahead_heading" value="<?php echo htmlentities($emailsettings['ahead_heading']) ; ?>">
		
		<label for="inputpost_text">Footer Text</label>
		<div class="wysiwyg"><?php echo ($emailsettings['post_text']) ; ?></div>
		<?php
		echo $this->getHTMLTemplate(ob_get_clean());
	}
	function emails() {
		
		function number_pad($number,$n) {
			return str_pad((int) $number,$n,"0",STR_PAD_LEFT);
		}

		$this->forcelogin();
		ob_start();
		$this->adminTabs();
		
		$monthNames = Array("January", "February", "March", "April", "May", "June", "July", 
		"August", "September", "October", "November", "December");
		
		if (!isset($_REQUEST["month"])) $_REQUEST["month"] = date("n");
		if (!isset($_REQUEST["year"])) $_REQUEST["year"] = date("Y");
		
		$cMonth = $_REQUEST["month"];
		$cYear = $_REQUEST["year"];
		 
		$prev_year = $cYear;
		$next_year = $cYear;
		$prev_month = $cMonth-1;
		$next_month = $cMonth+1;
		 
		if ($prev_month == 0 ) {
			$prev_month = 12;
			$prev_year = $cYear - 1;
		}
		if ($next_month == 13 ) {
			$next_month = 1;
			$next_year = $cYear + 1;
		}
		?>
		<h2><?php echo $monthNames[$cMonth-1].' '.$cYear; ?></h2>
		<p>
			<a class="btn" href="<?php echo "/admin/emails?month=". $prev_month . "&year=" . $prev_year; ?>">Previous</a>
			<a class="btn" href="<?php echo "/admin/emails?month=". $next_month . "&year=" . $next_year; ?>">Next</a>
		</p>
		
		<table class="table table-bordered">
			<tr>
				<th>Sunday</th>
				<th>Monday</th>
				<th>Tuesday</th>
				<th>Wednesday</th>
				<th>Thursday</th>
				<th>Friday</th>
				<th>Saturday</th>
			</tr>
			<?php 
			$timestamp = mktime(0,0,0,$cMonth,1,$cYear);
			$maxday = date("t",$timestamp);
			$thismonth = getdate($timestamp);
			$startday = $thismonth['wday'];
			function round_up_to_nearest_n($int, $n) {
				return ceil($int / $n) * $n;
			}
			$complete_cells = round_up_to_nearest_n($maxday+$startday,7);
			for ($i=0; $i<($complete_cells); $i++) {
				$strDate = $cYear .'-'.number_pad($cMonth,2).'-'.number_pad(($i - $startday + 1),2);
				if(($i % 7) == 0 ) echo "<tr>";
				if($i < $startday || $i >= $maxday+$startday) echo "<td></td>";
				else {
					$dow = ($i%7);
					if ($dow == 0) $dow = 7;
					if ($strDate == date('Y-m-d')) echo '<td style="background-color:yellow">' ;
					else echo "<td>";
					echo "<a href=\"/admin/email?date=". $strDate . "\">".date('D jS M Y',strtotime($strDate))."</a>";
					$motd = $this->db->getOne("SELECT id FROM motds WHERE area_id = '1' AND site_id = '{$_SESSION['site']['id']}' AND motd_date = '$strDate'");
					echo '<br><span class="label'.(($motd)?' label-success':'').'">Header</span>';
					$motd = $this->db->getOne("SELECT id FROM motds WHERE area_id = '2' AND site_id = '{$_SESSION['site']['id']}' AND motd_date = '$strDate'");
					echo '<br><span class="label'.(($motd)?' label-success':'').'">Devotional</span>';
					$motd = $this->db->getOne("SELECT id FROM motds WHERE area_id = '4' AND site_id = '{$_SESSION['site']['id']}' AND motd_date = '$strDate'");
					echo '<br><span class="label'.(($motd)?' label-success':'').'">Prayers</span>';
					$motd = $this->db->getOne("SELECT id FROM motds WHERE area_id = '3' AND site_id = '{$_SESSION['site']['id']}' AND motd_date = '$strDate'");
					echo '<br><span class="label'.(($motd)?' label-success':'').'">Footer</span>';
					//$this->startDebug();
					$events = $this->db->getArray("SELECT id,title FROM events WHERE site_id = '{$_SESSION['site']['id']}' AND event_date = '$strDate' ORDER BY sortable_time");
					foreach ($events as $event) {
						echo '<br><span class="label'.((1)?' label-success':'').'">'.substr($event['title'],0,16).((strlen($event['title'])>16)?'...':'').'</span>';
					}
					$revents = $this->db->getArray("SELECT id,title FROM revents WHERE site_id = '{$_SESSION['site']['id']}' AND dow = '$dow' ORDER BY sortable_time");
					foreach ($revents as $revent) {
						$x = $this->db->getOne("SELECT COUNT(id) FROM revents_exceptions WHERE exception_date = '$strDate' AND revent_id = '{$revent['id']}'");
						echo '<br><span class="label'.(($x)?' label-important':' label-success').'">'.substr($revent['title'],0,16).((strlen($revent['title'])>16)?'...':'').'</span>';
					}
					$this->stopDebug();
					echo "</td>";
				}
				if(($i % 7) == 6 ) {
				  echo "</tr>";
				}
			}
			?>	
		</table>
		<?php
		echo $this->getHTMLTemplate(ob_get_clean());
	}
	function subscribers() {
		$this->forcelogin();
		ob_start();
		$this->adminTabs();

		$rpp = 100;
		$page = (isset($_GET['page']))?$_GET['page']:1;

		if (isset($_GET['q'])) {
			$_GET['q'] = htmlentities($_GET['q']);
			$q = $this->db->qstr('%'.$_GET['q'].'%');
			$where = " AND (email LIKE $q OR CONCAT(first_name,' ',surname) LIKE $q OR post_code LIKE $q)";
		}
		else {
			$where = '';
			$_GET['q'] = '';
		}

		?>
		<form class="form-search" action="/admin/subscribers" method="get">
			<div class="input-append">
				<input type="text" class="input-xxlarge search-query" placeholder="Search by Email, Name or Post Code" name="q" value="<?php echo $_GET['q'] ; ?>">
				<button type="submit" class="btn btn-primary">Search</button>
			</div>
			<?php if ($_GET['q'] != '') { ?><a class="pull-right" href="/admin/subscribers">Reset Search</a><?php } ?>
		</form>
		<?php

		$count_subscribers = $this->db->getOne("SELECT COUNT(id) FROM users WHERE site_id = '{$_SESSION['site']['id']}' $where");
		if ($count_subscribers > $rpp) {
			?>
			<div class="pagination pagination-centered">
				<ul>
					<li class="disabled"><a href="/admin/subscribers?page=<?php echo $page-1 ; ?>&amp;q=<?php echo $_GET['q'] ; ?>">Prev</a></li>
					<?php
					for ($i = 1 ; (($i-1)*$rpp) <= $count_subscribers ; $i++) {
						?>
						<li <?php if ($i == $page) echo ' class="active" ' ; ?>>
							<a href="/admin/subscribers?page=<?php echo $i ; ?>&amp;q=<?php echo $_GET['q'] ; ?>"><?php echo $i ; ?></a>
						</li>
						<?php
					}
					?>
					<li><a href="/admin/subscribers?page=<?php echo $page+1 ; ?>&amp;q=<?php echo $_GET['q'] ; ?>">Next</a></li>
				</ul>
			</div>
			<?php
		}



		$subscribers = $this->db->getArray("
			SELECT id ,email ,first_name ,surname ,post_code ,UNIX_TIMESTAMP(date_created) AS date_created,UNIX_TIMESTAMP(date_last_logged_in) AS date_last_logged_in,login_count ,is_subscriber ,is_admin
			FROM users
			WHERE
				site_id = '{$_SESSION['site']['id']}'
				$where
			ORDER BY is_admin DESC,first_name,surname,email
			LIMIT ".(($page-1)*$rpp).",$rpp
		");

		if ($count_subscribers) {
			?>
			<table class="table table-striped table-condensed">
				<thead>
					<tr>
						<th>First Name</th>
						<th>Surname</th>
						<th>Email</th>
						<th>Post Code</th>
						<th>Edit</th>
						<th>Delete</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($subscribers as $user) {
						?>
						<tr class="
							<?php if ($user['is_admin']) echo 'info ' ; ?>
							<?php if (!$user['is_subscriber']) echo 'error ' ; ?>
						">
							<td><?php echo $user['first_name'] ; ?></td>
							<td><?php echo $user['surname'] ; ?></td>
							<td>
								<?php if ($user['is_admin']) echo '<span class="label">Administrator</span> ' ; ?>
								<?php echo $user['email'] ; ?>
							</td>
							<td><?php echo $user['post_code'] ; ?></td>
							<td>
								<a class="btn btn-mini btn-primary" href="/user/show/<?php echo $user['id'] ; ?>">Edit</a>
							</td>
							<td>
								<a class="btn btn-mini btn-danger" href="/user/delete/<?php echo $user['id'] ; ?>">Delete</a>
							</td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
			<?php
		}
		else echo '<p>No matches found.</p>';
		echo $this->getHTMLTemplate(ob_get_clean());
	}
	function youAreHere() {
		return '<a href="/">Home</a> &raquo; <a href="'.'search">Programmes Online</a>';
	}
}
