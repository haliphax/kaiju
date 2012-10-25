<?php if(! defined('BASEPATH')) exit(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>map list - map editor - kaiju!</title>
<?php include(BASEPATH . '../includes/header.inc.php'); ?>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>maplist.css" />
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
		<h2 class="ui-state-highlight ui-corner-all">Map List</h2>
		<div class="tabs">
			<ul>
				<li><a href="#list">List</a></li>
			</ul>
			<div id="list">
				<ul>
				<?php foreach($maps as $map): ?>
					<li>
						<a href="<?=site_url("mapedit/edit/{$map['map']}")?>"><?=$map['mname']?></a>
					</li>
				<?php endforeach; ?>
				</ul>
			</div>
		</div>
		<div id="spacer"></div>
	</div>
	</div>
	<?php include(BASEPATH . '../includes/footer.inc.php'); ?>
</body>
</html>
