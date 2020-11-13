<?php
class Home extends Base {
	function __construct($query = NULL) {
		parent::__construct($query);
	}
	function show() {
		ob_start();
		?>
		<!-- Main hero unit for a primary marketing message or call to action -->
		<div class="hero-unit hidden-phone">
			<h1>[SITE NAME]</h1>
			<p>[HERO UNIT]</p>
			<p><a href="/subscribe" class="btn btn-primary btn-large">Subscribe Now! &raquo;</a></p>
		</div>

		<!-- Example row of columns -->
		<div class="row">
			<div class="span4">
				<h2>Subscribe</h2>
				<p>To receive your own copy of [SITE NAME], click Subscribe below, fill in your details, and we'll do the rest.</p>
				<p><a class="btn" href="/subscribe">Subscribe &raquo;</a></p>
			</div>
			<!--<div class="span4">
				<h2>Missed One?</h2>
				<p>Don't worry if you missed an email. All our previous issues are safely stored online so you need never miss one.</p>
				<p><a class="btn" href="/issues">View previous issues &raquo;</a></p>
			</div>-->
			<div class="span4">
				<h2>Unsubscribe</h2>
				<p>If you wish to stop receiving [SITE NAME], you can unsubscribe easily and quickly. Click below to get started.</p>
				<p><a class="btn" href="/user/unsubscribe">Unsubscribe &raquo;</a></p>
			</div>
			<div class="span4">
				<h2>Links</h2>
				[SOCIAL ICONS]
			</div>
		</div>
		<?php
		echo $this->getHTMLTemplate(ob_get_clean());
	}
	function youAreHere() {
		return '<a href="/">Home</a> &raquo; <a href="'.'search">Programmes Online</a>';
	}
}
