<?php if(! defined('BASEPATH')) exit(); ?>
<!doctype html>
<html lang="en-US">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>yōkai!</title>
	<link rel="stylesheet" href="//code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css">
	<link rel="stylesheet" href="beta.css" />
</head>
<body>
	<input type="hidden" id="csrf" value="<?php echo $this->session->userdata('csrf'); ?>" />
	<input type="hidden" id="base-url" value="<?php echo base_url(); ?>" />
	<input type="hidden" id="site-url" value="<?php echo site_url('/'); ?>" />
	<div id="loading-screen"></div>

	<!-- main ui -->

	<div data-role="page" id="game-page" class="ui-responsive-panel">
		<!-- ko template: { name: 'header-template' } --><!-- /ko -->
		<ul data-role="listview" class="yk-desktop-menu hide-mobile">
			<li data-role="list-divider" data-theme="b"><h1>妖怪 - yōkai!</h1></li>
			<li data-role="list-divider">Game Menu</li>
			<!--ko template: { name: 'game-menu-list-template' } --><!-- /ko -->
			<li data-role="list-divider">System Menu</li>
			<!--ko template: { name: 'system-menu-list-template' } --><!-- /ko -->
		</ul>
		<div role="main" class="ui-content">
			<div class="ui-grid-a ui-responsive">
				<div class="yk-status-block ui-block-c">
					<div class="yk-block-wrapper yk-status-wrapper" data-bind="with: stat" data-role="collapsible" data-collapsed="false">
						<h4 class="ui-bar-a" data-bind="text: aname">[Character Name]</h4>
						<div>
							<div>
								<strong>Effects:</strong>
								<span class="yk-effects-list" data-bind="foreach: effects">
									<a href="#" data-bind="text: ename, click: function(){ $root.client.effect_describe(this); }"></a>
								</span>
							</div>
							<hr />
							<div class="ui-grid-c">
								<div class="ui-block-a">
									<div class="yk-progress-bar yk-hp-bar">
										<div class="yk-progress-bar-text" data-bind="text: 'HP ' + stat_hp() + '/' + stat_hpmax()">HP</div>
										<div class="yk-progress-bar-value" data-bind="attr: { 'style': 'width:' + (stat_hp() / stat_hpmax()) + '%' }"></div>
									</div>
								</div>
								<div class="ui-block-b">
									<div class="yk-progress-bar yk-mp-bar">
										<div class="yk-progress-bar-text" data-bind="text: 'MP ' + stat_mp() + '/' + stat_mpmax()">MP</div>
										<div class="yk-progress-bar-value" data-bind="attr: { 'style': 'width:' + (stat_mp() / stat_mpmax()) + '%' }"></div>
									</div>
								</div>
								<div class="ui-block-c">
									<div class="yk-progress-bar yk-ap-bar">
										<div class="yk-progress-bar-text" data-bind="text: 'AP ' + stat_ap() + '/' + stat_apmax()">AP</div>
										<div class="yk-progress-bar-value" data-bind="attr: { 'style': 'width:' + (stat_ap() / stat_apmax()) + '%' }"></div>
									</div>
								</div>
								<div class="ui-block-d">
									<div class="yk-progress-bar yk-xp-bar">
										<div class="yk-progress-bar-text" data-bind="text: 'XP ' + stat_xp() + '/' + stat_xplevel()">XP</div>
										<div class="yk-progress-bar-value" data-bind="attr: { 'style': 'width:' + (stat_xp() / stat_xplevel()) + '%' }"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="ui-block-c yk-log-block">
					<div class="yk-block-wrapper yk-log-wrapper" data-role="collapsible" data-collapsed="false">
						<h4 class="ui-bar-a">Event Log</h4>
						<div>
							<ul style="margin:0; padding:0; list-style-type: none; text-indent: 0;" data-bind="foreach: msg">
								<li>
									<span data-bind="text: '[' + $data[0] + ']'"></span>
									<span data-bind="html: $data[1]"></span>
								</li>
							</ul>
						</div>
					</div>
				</div>
				<div class="ui-block-a">
					<div class="yk-map-wrapper yk-block-wrapper" data-role="collapsible" data-collapsed="false">
						<h4 class="ui-bar-a">Map [<span data-bind="text: info.x + ',' + info.y">0,0</span>]</h4>
						<div>
							<div id="map" data-bind="foreach: cells">
								<div class="map_cell" data-bind="gameMapTile: $index, css: { 'ui-corner-tl': $index() === 0, 'ui-corner-tr': $index() == 4, 'tile': $data.x > 0 }">
									<div class="map_cell tile-box" data-bind="css: { 'ui-corner-tl': $index() === 0, 'ui-corner-tr': $index() == 4, 'tile': $data.x > 0 }">
										<div class="mvarrow" data-bind="
											css: {
												'ui-sprite': $data.x > 0 && [ 6, 7, 8, 11 ,12, 13, 16, 17, 18 ].indexOf($index()) != -1
												, 'move-nw': $data.x > 0 && $index() == 6
												, 'move-n': $data.x > 0 && $index() == 7
												, 'move-ne': $data.x > 0 && $index() == 8
												, 'move-w': $data.x > 0 && $index() == 11
												, 'door-icon': $parent.info().building > 0 && $index() == 12
												, 'move-e': $data.x > 0 && $index() == 13
												, 'move-sw': $data.x > 0 && $index() == 16
												, 'move-s': $data.x > 0 && $index() == 17
												, 'move-se': $data.x > 0 && $index() == 18
											}
											, click: $index() == 12 && $parent.info().building > 0
												? function(){ $('#actions').find('a:visible:contains(\'Enter\'), a:visible:contains(\'Exit\')')[0].click(); }
												: [6,7,8,11,13,16,17,18].indexOf($index()) != -1
													? function(){ $root.fn.move($data.x, $data.y); }
													: null
										">
											<div class="sh" data-bind="visible: $data.clan !== null && $data.x > 0"></div>
											<img src="<?php echo base_url(); ?>img/pawns/person.png" style="position:relative;top:15px;" data-bind="visible: $index() === 12" /><img src="<?php echo base_url(); ?>img/pawns/other.png" style="position:relative;top:15px;" data-bind="visible: $index() === 12 && occ > 1 || $index() !== 12 && occ > 0" /><span class="occ" data-bind="visible: $index() === 12 && occ > 2 || $index() !== 12 && occ > 1, text: '&nbsp;' + ($index() == 12 ? occ - 1 : occ) + '&nbsp;'"></span>
										</div>
									</div>
								</div>
							</div>
							<div class="location-info" data-bind="with: info">
								<hr />
								<span data-bind="text: $data.descr">Location Name</span>
								<em data-bind="if: typeof town !== 'undefined', text: typeof town !== 'undefined' ? town : null">(City Name)</em>
							</div>
						</div>
					</div>
				</div>
				<div class="ui-block-b">
					<div class="yk-block-wrapper" data-role="collapsible" data-collapsed="false">
						<h4 class="ui-bar-a">Actions</h4>
						<ul data-role="listview" data-inset="true" data-bind="foreach: actions">
							<li>
								<a href="javascript:void(0);" data-bind="
									visible: params == 1 && js != 1
									, click: function(){ $root.fn.action_params.call($data); }
									, text: descr
								"></a>
								<a href="javascript:void(0);" data-bind="
									visible: js == 1
									, click: function(){ $root.fn.action_customJS.call($data); }
									, text: descr
								"></a>
								<a href="javascript:void(0);" data-bind="
									visible: params != 1 && js != 1
									, click: function(){ $root.fn.action_fire.call($data); }
									, text: descr
								"></a>
								<a href="javascript:void(0);" data-bind="
									visible: params != 1 && rpt == 1
									, click: function(){ $root.fn.action_repeat.call($data); }
								">x5</a>
								<span class="cost" data-bind="
									visible: cost > 1
									, html: $root.fn.action_costText.call($data)
								"></span>
							</li>
						</ul>
					</div>
				</div>
				<div class="ui-block-b">
					<div class="yk-block-wrapper" data-role="collapsible" data-collapsed="false">
						<h4 class="ui-bar-a">Skills</h4>
						<ul data-role="listview" data-inset="true" data-bind="foreach: skills">
							<li>
								<a href="javascript:void(0);" data-bind="
									visible: params == 1
									, click: function(){ $root.fn.skill_params.call($data); }
									, text: sname
								"></a>
								<a href="javascript:void(0);" data-bind="
									visible: js == 1
									, click: function(){ $root.fn.skill_customJS.call($data); }
									, text: sname
								"></a>
								<a href="javascript:void(0);" data-bind="
									visible: params != 1 && js != 1
									, click: function(){ $root.fn.skill_fire.call($data); }
									, text: sname
								"></a>
								<a href="javascript:void(0);" data-bind="
									visible: params != 1 && rpt == 1
									, click: function(){ $root.fn.skill_repeat.call($data); }
								">x5</a>
								<span class="cost" data-bind="
									visible: cost_ap > 1 || cost_mp > 0
									, html: $root.fn.action_costText.call($data)
								"></span>
							</li>
						</ul>
					</div>
				</div>
				<div class="ui-block-b">
					<div class="yk-block-wrapper" data-role="collapsible" data-collapsed="false">
						<h4 class="ui-bar-a">Occupants</h4>
						<ul data-role="listview" data-inset="true">
							<li><a href="#">Test</a></li>
							<li><a href="#">Test</a></li>
						</ul>
					</div>
				</div>
				<div class="ui-block-b">
					<div class="yk-block-wrapper yk-surroundings-wrapper" data-role="collapsible" data-collapsed="false">
						<h4 class="ui-bar-a">Surroundings</h4>
						<div>
							<span data-bind="if: corpses() > 0">
								There
								<span data-bind="text: corpses() == 1 ? 'is' : 'are'"></span>
								<b>
									<span data-bind="text: corpses()"></span>
									<span data-bind="text: corpses() == 1 ? 'corpse' : 'corpses'"></span>
								</b>
								on the ground.
							</span>
							<span data-bind="html: surr()"></span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- ko template: { name: 'system-menu-panel-template' } --><!-- /ko -->
		<!-- ko template: { name: 'game-menu-panel-template' } --><!-- /ko -->
	</div>

	<!-- templates -->

	<script type="text/html" id="header-template">
		<div data-role="header" data-position="fixed" class="hide-desktop">
			<a href="#game-menu" data-icon="bars" data-iconpos="notext" class="hide-desktop">Game Menu</a>
			<h2>yokai!</h2>
			<a href="#system-menu" data-icon="gear" data-iconpos="notext" class="hide-desktop">System Menu</a>
		</div>
	</script>
	<script type="text/html" id="game-menu-list-template">
		<li data-icon="shop"><a href="#">Inventory</a></li>
		<li data-icon="grid"><a href="#">Map</a></li>
		<li data-icon="bullets"><a href="#">Skills</a></li>
		<li data-icon="star"><a href="#">Clan</a></li>
	</script>
	<script type="text/html" id="system-menu-list-template">
		<li data-icon="edit" data-theme="a"><a href="#">Administration</a></li>
		<li data-icon="user"><a href="#">Characters</a></li>
		<li data-icon="lock"><a href="#">Account</a></li>
		<li data-icon="power"><a href="#">Log Out</a></li>
	</script>
	<script type="text/html" id="game-menu-panel-template">
		<div class="ui-panel" data-role="panel" id="game-menu" data-position="left" data-display="overlay" data-position-fixed="true">
			<ul data-role="listview">
				<li data-role="list-divider">Game Menu</li>
				<li data-icon="delete" class="hide-desktop"><a href="#" data-rel="close">Close</a></li>
				<!-- ko template: { name: 'game-menu-list-template' } --><!-- /ko -->
			</ul>
		</div>
	</script>
	<script type="text/html" id="system-menu-panel-template">
		<div class="ui-panel" data-role="panel" id="system-menu" data-position="right" data-display="overlay" data-position-fixed="true">
			<ul data-role="listview">
				<li data-role="list-divider">System Menu</li>
				<li data-icon="delete" class="hide-desktop"><a href="#" data-rel="close">Close</a></li>
				<!-- ko template: { name: 'system-menu-list-template' } --><!-- /ko -->
			</ul>
		</div>
	</script>
	<script src="require.js" data-main="beta" async></script>
</body>
</html>
