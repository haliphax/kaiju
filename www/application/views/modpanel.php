<?php if(! defined('BASEPATH')) exit(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>mod panel - kaiju!</title>
	<?php include(BASEPATH . '../includes/header.inc.php'); ?>
</head>
<body>
	<div id="wrapper">
	<div id="inner">
		<div id="menu">
			<?php include(BASEPATH . '../includes/globalbuttons.inc.php'); ?>
			<div class="right">
				<button class="button" onclick="window.open('http://kaiju.roadha.us/forum', 'kaijuforum');">Forum</button>&nbsp;
			</div>
		</div>
		<h2 class="ui-corner-all ui-state-highlight">Moderator Panel</h2>
		<div id="characters" class="tabs">
			<ul>
				<li><a href="#modules">Modules</a></li>
			</ul>
			<div id="modules">
				<ul>
					<li><a href="<?=site_url('mapedit')?>">Map editor</a></li>
				</ul>
			</div>
		</div>
		<div id="spacer"></div>
	</div>
	</div>
	<?php include(BASEPATH . '../includes/footer.inc.php'); ?>
</body>
</html>
