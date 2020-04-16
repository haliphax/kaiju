<!doctype html>
<html lang="en-US">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>yōkai!</title>
	<link rel="stylesheet" href="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css">
	<link rel="stylesheet" href="<?php echo base_url(); ?>css/beta.css" />
</head>
<body>
	<input type="hidden" id="csrf" value="<?php echo $this->session->userdata('csrf'); ?>" />
	<input type="hidden" id="base-url" value="<?php echo base_url(); ?>" />
	<input type="hidden" id="site-url" value="<?php echo site_url('/'); ?>" />
	<div id="loading-screen">
		<table>
			<tbody>
				<tr>
					<td>
						<img src="<?php echo base_url(); ?>img/loading.gif" alt="Loading..." />
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<!-- main ui -->
	<div data-role="page" id="game-page" class="ui-responsive-panel">
		<div data-role="header" class="hide-desktop">
			<a href="#game-menu" data-icon="bars" data-iconpos="notext" class="hide-desktop">Game Menu</a>
			<h2>yokai!</h2>
			<a href="#system-menu" data-icon="gear" data-iconpos="notext" class="hide-desktop">System Menu</a>
		</div>
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
					<div class="yk-block-wrapper yk-status-wrapper" data-role="collapsible" data-collapsed="false">
						<h4 class="ui-bar-a"><span data-bind="text: stat.aname">[Character Name]</span></h4>
						<div>
							<div>
								<strong>Effects:</strong>
								<span class="yk-effects-list" data-bind="visible: effects().length > 0, foreach: effects">
									<a href="#" data-bind="text: ename, click: function(){ $root.client.effect_describe(this); }"></a>
									&nbsp;
								</span>
								<span class="yk-effects-list" data-bind="visible: effects().length === 0">
									<em>None</em>
								</span>
							</div>
							<hr />
							<div class="ui-grid-c" data-bind="with: stat">
								<div class="ui-block-a">
									<div class="yk-progress-bar yk-hp-bar">
										<div class="yk-progress-bar-text" data-bind="text: 'HP ' + stat_hp() + '/' + stat_hpmax()">HP</div>
										<div class="yk-progress-bar-value" data-bind="attr: { 'style': 'width:' + (stat_hp() / stat_hpmax() * 100) + '%' }"></div>
									</div>
								</div>
								<div class="ui-block-b">
									<div class="yk-progress-bar yk-mp-bar">
										<div class="yk-progress-bar-text" data-bind="text: 'MP ' + stat_mp() + '/' + stat_mpmax()">MP</div>
										<div class="yk-progress-bar-value" data-bind="attr: { 'style': 'width:' + (stat_mp() / stat_mpmax() * 100) + '%' }"></div>
									</div>
								</div>
								<div class="ui-block-c">
									<div class="yk-progress-bar yk-ap-bar">
										<div class="yk-progress-bar-text" data-bind="text: 'AP ' + stat_ap() + '/' + stat_apmax()">AP</div>
										<div class="yk-progress-bar-value" data-bind="attr: { 'style': 'width:' + (stat_ap() / stat_apmax() * 100) + '%' }"></div>
									</div>
								</div>
								<div class="ui-block-d">
									<div class="yk-progress-bar yk-xp-bar">
										<div class="yk-progress-bar-text" data-bind="text: 'XP ' + stat_xp() + '/' + stat_xplevel()">XP</div>
										<div class="yk-progress-bar-value" data-bind="attr: { 'style': 'width:' + (stat_xp() / stat_xplevel() * 100) + '%' }"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="ui-block-a">
					<div class="yk-block-wrapper yk-log-wrapper" data-role="collapsible" data-collapsed="false">
						<h4 class="ui-bar-a">Event Log</h4>
						<form>
							<input id="chat_input" type="text" placeholder="Say something..." />
							<input type="submit" data-bind="click: $root.client.chat" style="display: none;" />
						</form>
						<div id="log_text">
							<ul data-bind="foreach: msg">
								<li>
									<code data-bind="text: '[' + $data[0] + ']'"></code>
									<span data-bind="html: $data[1]"></span>
								</li>
							</ul>
						</div>
					</div>
					<div class="yk-map-wrapper yk-block-wrapper" data-role="collapsible" data-collapsed="false">
						<h4 class="ui-bar-a">Map [<span data-bind="text: info().x + ',' + info().y">0,0</span>]</h4>
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
								<em data-bind="if: typeof town !== 'undefined', text: typeof town !== 'undefined' ? '(' + town + ')' : null">(City Name)</em>
							</div>
						</div>
					</div>
					<div class="yk-block-wrapper yk-surroundings-wrapper" data-role="collapsible" data-collapsed="false">
						<h4 class="ui-bar-a">Surroundings</h4>
						<div>
							<span data-bind="if: corpses() > 0">
								There
								<span data-bind="text: corpses() == 1 ? 'is' : 'are'"></span>
								<strong>
									<span data-bind="text: corpses"></span>
									<span data-bind="text: corpses() == 1 ? 'corpse' : 'corpses'"></span>
								</strong>
								here.
							</span>
							<span data-bind="html: surr()"></span>
						</div>
					</div>
				</div>
				<div class="ui-block-b">
					<div class="yk-block-wrapper" data-role="collapsible" data-collapsed="false">
						<h4 class="ui-bar-a">Actions</h4>
						<ul id="actions" data-role="listview" data-inset="true" data-bind="foreach: actions">
							<li>
								<a href="#" data-bind="
									click: function(){ $root.fn.action_click.call($data); }
									, html: descr + ' ' + $root.fn.action_costText.call($data)
								"></a>
								<a href="#" data-bind="
									css: { 'ui-icon-recycle': rpt == 1 && params != 1 }
									, text: rpt == 1 && params != 1 ? 'Repeat' : 'Use'
									, click: rpt == 1 && params != 1
										? function(){ $root.fn.action_repeat.call($data); }
										: function(){ $root.fn.action_click.call($data); }
								"></a>
							</li>
						</ul>
					</div>
					<div class="yk-block-wrapper" data-role="collapsible" data-collapsed="false">
						<h4 class="ui-bar-a">Skills</h4>
						<ul id="skills" data-role="listview" data-inset="true" data-bind="foreach: skills">
							<li>
								<a href="#" data-bind="
									click: function(){ $root.fn.skill_click.call($data); }
									, html: sname + ' ' + $root.fn.action_costText($data)
								"></a>
								<a href="#" data-bind="
									css: { 'ui-icon-recycle': rpt == 1 && params != 1 }
									, text: rpt == 1 && params != 1 ? 'Repeat' : 'Use'
									, click: rpt == 1 && params != 1
										? function(){ $root.fn.skill_repeat.call($data); }
										: function(){ $root.fn.skill_click.call($data); }
								"></a>
							</li>
						</ul>
					</div>
					<div class="yk-block-wrapper" data-role="collapsible" data-collapsed="false">
						<h4 class="ui-bar-a">Occupants</h4>
						<ul id="occupants" data-role="listview" data-inset="true">
							<!-- ko foreach: occ_near -->
								<li>
									<a href="#" data-bind="
										style: { fontWeight: (typeof enemy != 'undefined' || typeof ally != 'undefined') ? 'bold' : 'normal' }
										, css: {
											badguy: faction !== $root.stat.faction()
											, goodguy: faction === $root.stat.faction()
										}
										, visible: actor !== $root.stat.actor()
										, click: function(){ $root.fn.actor_menu(actor, 1); }
										, text: aname
									"></a>
								</li>
							<!-- /ko -->
							<li data-role="list-divider" data-bind="visible: occ_far().length > 0, text: elev() == 0 ? 'Above' : 'Below'"></li>
							<!-- ko foreach: occ_far -->
								<li>
									<a href="#" data-bind="
										style: { fontWeight: (typeof enemy != 'undefined' || typeof ally != 'undefined') ? 'bold' : 'normal' }
										, css: {
											badguy: faction !== $root.stat.faction()
											, goodguy: faction === $root.stat.faction()
										}
										, visible: actor !== $root.stat.actor()
										, click: function(){ $root.fn.actor_menu(actor, 1); }
										, text: aname
									"></a>
								</li>
							<!-- /ko -->
						</ul>
					</div>
				</div>
			</div>
		</div>
		<!-- ko template: { name: 'system-menu-panel-template' } --><!-- /ko -->
		<!-- ko template: { name: 'game-menu-panel-template' } --><!-- /ko -->
	</div>
	<!-- loading zone for AJAX content -->
	<div id="loadingzone">
		<!-- mini-map -->
		<div id="minimap" data-role="popup" data-theme="a" data-overlay-theme="b">
			<a href="#" data-rel="back" data-role="button" data-icon="delete" data-iconpos="notext" class="ui-btn-right">Close</a>
			<img />
		</div>
		<!-- effect description -->
		<div id="effect_desc" data-role="popup" data-theme="a" data-overlay-theme="b">
			<a href="#" data-rel="back" data-role="button" data-icon="delete" data-iconpos="notext" class="ui-btn-right">Close</a>
			<h4 data-bind="text: ename"></h4>
			<p data-bind="html: descr"></p>
		</div>
		<!-- action parameters -->
		<div id="actparams" data-role="popup" data-theme="a" data-overlay-theme="b" class="ui-content">
			<a href="#" data-rel="back" data-role="button" data-icon="delete" data-iconpos="notext" class="ui-btn-right">Close</a>
			<h4 data-bind="text: actname"></h4>
			<select data-bind="foreach: params">
				<option data-bind="attr: { 'value': $data[0] }, text: $data[1]"></option>
			</select>
			<button type="button" data-bind="click: function(){ $root.vm.fn.action_fire.call($data, $data.target()); }">Act</button>
		</div>
		<!-- actor menu -->
		<div id="profile" data-role="popup" data-theme="a" data-overlay-theme="b">
			<a href="#" data-rel="back" data-role="button" data-icon="delete" data-iconpos="notext" class="ui-btn-right">Close</a>
			<h4 data-bind="text: aname"></h4>
			<p data-bind="visible: descr().length > 0" >
				<strong>Equipped:</strong>
				<span data-bind="foreach: descr">
					<span data-bind="text: iname + ($index() < $root.descr().length - 1 ? ',' : '')"></span>
				</span>
			</p>
			<table>
				<tbody>
					<tr>
						<td>Status:</td>
						<td><span id="profile_status" data-bind="text: status() == 1 ? 'Online' : 'Offline'"></span></td>
					</tr>
					<tr>
						<td>Health:</td>
						<td><span id="profile_health" data-bind="html: health"></span></td>
					</tr>
					<tr>
						<td>Distance:</td>
						<td><span id="profile_distance" data-bind="text: dist() > 0 ? 'Ranged' : 'Melee'"></span></td>
					</tr>
					<tr>
						<td>Faction:</td>
						<td><span id="profile_faction" data-bind="text: faction_name"><a href="#"></a></span></td>
					</tr>
					<tr>
						<td>Clan:</td>
						<td>
							<a href="#" data-bind="click: function(){ $root.vm.fn.describe_clan(clan()); }, text: clan_name() ? clan_name() : 'none'"></a>
							<small data-bind="visible: typeof rel != 'undefined'"><em data-bind="text: typeof rel != 'undefined' ? rel : ''"></em></small>
						</td>
					</tr>
				</tbody>
			</table>
			<ul data-role="listview">
				<li data-bind="visible: attack() == 1">
					<a href="#" data-bind="click: function(){ $root.fn.attack(actor()); }">Attack <small data-bind="text: cth() + '%'"></small></a>
				</li>
				<!-- ko foreach: skills() -->
					<li>
						<a href="#" data-bind="
							click: function(){ $root.vm.fn.skill_click.call($data, $root.actor()); }
							, html: sname + ' ' + $root.vm.fn.action_costText.call($data)
						"></a>
					</li>
				<!-- /ko -->
				<!-- ko foreach: acta() -->
					<li>
						<a href="#" data-bind="
							click: function(){ $root.vm.fn.action_click.call($data, $root.actor()); }
							, html: descr + ' ' + $root.vm.fn.action_costText.call($data)
						"></a>
					</li>
				<!-- /ko -->
			</div>
		</div>
	</div>
	<script type="text/html" id="game-menu-list-template">
		<li data-icon="shop"><a href="#">Inventory</a></li>
		<li data-icon="grid"><a href="#" id="minimap-button" data-bind="click: $root.fn.minimap_show">Map</a></li>
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
	<script src="<?php echo base_url(); ?>js/lib/require.js" data-main="<?php echo base_url(); ?>js/beta" async></script>
</body>
</html>
