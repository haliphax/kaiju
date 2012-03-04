<?php if(! defined('BASEPATH')) exit(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>preload - kaiju!</title>
	<?php include(BASEPATH . '../includes/header.inc.php'); ?>
	<script type="text/javascript">
	var loadedImgs = 0;
	
	function preLoadImages()
	{
		$('#loadingmsg').dialog('open');
		var imgs = <?=$imgs?>;
		
		for(var a in imgs)
		{
			var i = new Image();
			i.src = '<?php echo base_url(); ?>images/' + imgs[a];			
			i.onload = function()
			{
				if(++loadedImgs >= imgs.length)
					window.location = '<?=site_url('game')?>';
			};
		}		
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
