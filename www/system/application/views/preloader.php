<?php if(! defined('BASEPATH')) exit(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>preload - kaiju!</title>
	<?php include(BASEPATH . '../includes/header.inc.php'); ?>
	<script type="text/javascript">
	var imgObjs = new Array();
	
	function preLoadImages()
	{
		$('#loadingmsg').dialog('open');
		var imgs = <?=$imgs?>;
		
		for(a = 0; a < imgs.length; a++)
		{
			imgObjs.push(new Image());
			imgObjs[a].src = '/images/' + imgs[a];
		}
		
		window.location = '<?=site_url('game')?>';
	}
	</script>
</head>
<body onload="preLoadImages();">
	<div id="wrapper">
	<div id="inner">
		<div style="height: 20px;"></div>
		<div id="spacer"></div>
	</div>
	</div>
	<?php include(BASEPATH . '../includes/footer.inc.php'); ?>
	<div id="loadingmsg" class="dialog" title="Loading">
		Pre-loading interface images...
	</div>
</body>
</html>
