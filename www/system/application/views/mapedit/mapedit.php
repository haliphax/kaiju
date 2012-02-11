<?php if(! defined('BASEPATH')) exit(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title><?=$map['mname']?> - map editor - kaiju!</title>
<?php include(BASEPATH . '../includes/header.inc.php'); ?>
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/mapedit.css" />
	<script type="text/javascript" src="<?=base_url()?>js/mapedit.js"></script>
	<script type="text/javascript">
		var h = <?=$map['h']?>;
		var w = <?=$map['w']?>;
		var pxsize = <?=$size?>;
		var tiles = <?=json_encode($tiles)?>;
	</script>
</head>
<body>
	<div id="wrapper">
	<div id="inner">
		<div style="height:20px;"></div>		
		<div id="map" style="background-color: black; width: 600px; height: 600px; float: left;">
		</div>
		<div id="view" class="ui-widget-content ui-corner-all">
			<table>
				<tr>
					<td><button class="button" id="nav_nw">NW</button></td><td><button class="button" id="nav_n">N</button></div></td><td><button class="button" id="nav_ne">NE</button></td>
				</tr>
				<tr>
					<td><button class="button" id="nav_w">W</button></td><td><input type="text" id="nav_int" value="3" /></td><td><button class="button" id="nav_e">E</button></td>
				</tr>
				<tr>
					<td><button class="button" id="nav_sw">SW</button></td><td><button class="button" id="nav_s">S</button></td><td><button class="button" id="nav_se">SE</button></td>
				</tr>
			</table>
			<button class="button ed_button" id="btn_minimap">Mini Map</button>
			<button class="ui-state-error ed_button button" onclick="window.location='<?=site_url('mapedit')?>';">Map List</button>
		</div>
		<div id="spacer"></div>
	</div>
	</div>
	<?php include(BASEPATH . '../includes/footer.inc.php'); ?>
	<div id="minimap" class="dialog" title="Mini Map">
		<img id="minimap_img" src="<?=site_url("/maped/mini/{$map['map']}/1/1")?>" />
	</div>
	<div id="chooser"></div>
	<div id="building_dialog" class="dialog" title="Edit building">
		<p><b>Classes:</b> <span id="building_classes">None</span></p>
		Add class:
		<select name="class" id="class">
		<?php foreach($bclasses as $bclass): ?>
			<option value="<?=$bclass['bclass']?>"><?=$bclass['abbrev'];?></option>
		<?php endforeach; ?>
		</select>
		<button class="button" type="button" id="btn_edit_building" onclick="return add_class();">Submit</button>
	</div>
</body>
</html>
