<?php if(! defined('BASEPATH')) exit(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>login - kaiju!</title>
<?php include(BASEPATH . '../includes/header.inc.php'); ?>
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/login.css" />
	<script type="text/javascript" src="<?=base_url()?>js/login.js"></script>
</head>
<body>
	<div id="wrapper">
	<div id="inner">
		<img src="<?=base_url()?>images/ui/kaiju.png" alt="kaiju!" id="logo" />
		<div id="news" class="accordion-fixed" title="News">
			<h3>News</h3>
			<div><?=$news?></div>
		</div>
		<div id="login" class="accordion-fixed" title="Login">
			<h3>Login</h3>
			<div>
<?php if($error != ''): ?>
				<div class="ui-corner-all ui-state-error bubble" style="margin: 0px;" id="error">
					<?=$error?>
				</div>
				<br />
<?php endif; ?>
<?php if(!isset($maint)): ?>
				<form id="frm_login" action="<?=site_url('login/check')?>" method="POST">
					<table style="width: 100%;">
						<tbody><tr>
							<td class="tright bold">Username:</td>
							<td><input name="user" id="user" type="text" tabindex="1" /></td>
						</tr>
						<tr>
							<td class="tright bold">Password:</td>
							<td><input name="pass" type="password" tabindex="2" /></td>
						</tr>
						<tr>
							<td> </td>
						</tr>
						<tr>
							<td colspan="2" class="center">
								<button id="btn_login" type="submit" class="button ui-state-default ui-corner-all" tabindex="3">Login</button>
							</td>
						</tr>
						<!--<tr>
							<td colspan="2" class="center">
								<br />Don't have an account? <a href="<?=site_url('signup')?>">Sign up for one!</a>
							</td>
						</tr>-->
					</tbody></table>
				</form>
<?php endif; ?>
			</div>
		</div>
		<div id="mailinglist" class="accordion-fixed" title="Mailing List">
			<h3>Mailing List</h3>
			<div>
				Subscribe to the kaiju! Closed Alpha Newsletter to be considered for upcoming alpha test phases.
				<p style="text-align:center"><button id="btn_mailinglist" class="button" onclick="window.open('http://eepurl.com/dpAZP');">Subscribe</button></p>
			</div>
		</div>
		<div id="media" class="accordion-fixed" title="Media">
			<h3>Media</h3>
			<div>
				<br />
				<a title="Facebook" href="http://www.facebook.com/pages/kaiju/343579685443" target="_blank"><img src="<?=base_url()?>images/homepage/facebook-small.png" alt="Facebook" /></a>
				<a title="IndieDB" href="http://www.indiedb.com/games/kaiju" target="_blank"><img src="<?=base_url()?>images/homepage/indiedb-small.png" alt="IndieDB" /></a>
				<a title="Twitter" href="http://twitter.com/kaijugame" target="_blank"><img src="<?=base_url()?>images/homepage/twitter-small.png" alt="Twitter" /></a>
				<a title="Photobucket" href="http://s282.photobucket.com/albums/kk267/oddboyd/kaiju" target="_blank"><img src="<?=base_url()?>images/homepage/photobucket-small.png" alt="Photobucket" /></a>
				<a title="RSS" href="http://rss.indiedb.com/games/kaiju/news/feed/rss.xml" target="_blank"><img src="<?=base_url()?>images/homepage/rss-small.png" alt="RSS" /></a>
			</div>		
		</div>
		<div id="buttons">
			<button id="btn_forum" class="button homepage-button" onclick="window.location = '<?=base_url()?>forum';">Forum</button>
			<button id="btn_wiki" class="button homepage-button">Wiki</button>
		</div>
		<div id="spacer">&nbsp;</div>
	</div>
	</div>
	<?php include(BASEPATH . '../includes/footer.inc.php'); ?>
</body>
</html>
