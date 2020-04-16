<?php if(! defined('BASEPATH')) exit(); ?>
<style type="text/css">
#scan {
	width: 540px;
	height: 540px;
}
#scan_map {
	width: 540px;
	position: relative;
}
</style>
<div data-role="popup" id="scan" data-theme="a" data-overlay-theme="b">
	<a href="#" data-rel="back" data-role="button" data-icon="delete" data-iconpos="notext" class="ui-btn-right">Close</a>
	<div id="scan_map" data-bind="foreach: scan">
		<div class="map_cell tile" data-bind="gameMapTile: $index">
			<div class="map_cell tile tile-box">
				<div class="sh" data-bind="visible: $data.x > 0 && $data.clan !== null"></div>
				<img data-bind="visible: $index() === 40" src="<?php echo base_url(); ?>img/pawns/person.png" style="position:relative;top:15px;" /><img data-bind="visible: $index() === 40 && occ > 1 || $index() !== 40 && occ > 0" src="<?php echo base_url(); ?>img/pawns/other.png" style="position:relative;top:15px;" /><span data-bind="visible: $index() === 40 && occ > 2 || $index() !== 40 && occ > 1" class="occ">&nbsp;2&nbsp;</span>
			</div>
		</div>
	</div>
</div>
