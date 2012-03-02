<?php if(! defined('BASEPATH')) exit(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title><?=$aname?> - kaiju!</title>
	<?php include(BASEPATH . '../includes/header.inc.php'); ?>
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/game.css" />
	<script type="text/javascript" src="<?=base_url()?>js/dialogs.js"></script>
	<script type="text/javascript" src="<?=base_url()?>js/client.js"></script>
</head>
<body>
	<div id="wrapper">
	<div id="inner">
		<div id="ui_body">
			<div id="menu">
				<?php include(BASEPATH . '../includes/globalbuttons.inc.php'); ?>
				<div class="right">
					<button id="btn_minimap" class="button" onclick="minimap();">Map</button>&nbsp;
					<button id="btn_inventory" class="button">Inventory</button>&nbsp;
					<button id="btn_skills" class="button" onclick="window.location = '<?=site_url('skilltree')?>';">Skills</button>&nbsp;
					<button id="btn_clan" class="button" onclick="window.location = '<?=site_url('clans')?>';">Clan</button>
				</div>
			</div>
			<div id="status" class="ui-corner-all ui-state-highlight">
				<div id="trans_icon" title="Transmission status" class="ui-corner-all ui-icon ui-state-highlight ui-icon-signal-diag"></div>
				<table>
					<tr>
						<td class="bold tleft"><a href="#"><?=$aname?></a></td>
						<td>&nbsp;</td>
						<td class="bold" title="Hit points">HP:</td>
						<td>
							<div id="bar_hp" class="progbar smallbar">
								<span class="prog"></span>
							</div>
						</td>
						<td class="bold tright" title="Mana points">MP:</td>
						<td>
							<div id="bar_mp" class="progbar smallbar">
								<span class="prog">0/0</span>
							</div>
						</td>
						<td class="bold tright" title="Action points">AP:</td>
						<td>
							<div id="bar_ap" class="progbar smallbar">
								<span class="prog"></span>
							</div>
						</td>
						<td class="bold tright" title="Experience points">XP:</td>
						<td>
							<div id="bar_xp" class="progbar smallbar">
								<span class="prog"></span>
							</div>
						</td>
					</tr>
				</table>
				<div id="effects"><b>Effects:</b> <i>Unknown</i></div>
			</div>
			<div id="log_accordion_wrapper">
				<div id="log_accordion" class="accordion">
					<h3><a>Event Log</a></h3>
					<div id="log">
						<form id="chat_form" action="" method="post" onsubmit="chatSubmit(); return false;">
							<input id="chat_input" type="text" maxlength="160" value="Type chat text or commands here" />
						</form>
						<ul id="log_text">
							<li>kaiju! v<?=$this->config->item('version')?> - "<?=$this->config->item('codename')?>"</li>
							<li>Remember to visit the <a target="_blank" href="/forum">forum</a>!</li>
						</ul>
					</div>
				</div>
			</div>
			<div id="ui_middle">
				<div id="rightcol">
					<div id="map"></div>
					<div id="location" class="ui-corner-bl ui-corner-br ui-state-active ui-widget">
						[<span id="coord_x">0</span>,<span id="coord_y">0</span>]
						<span id="coord_desc">Unknown</span>
					</div>
				</div>
				<div id="leftcol">
					<div id="skills" class="accordion">
						<h3><a>Skills</a></h3>
						<div><div class="block">&nbsp;</div></div>
					</div>
					<div id="actions" class="accordion">
						<h3><a>Actions</a></h3>
						<div><div class="block">&nbsp;</div></div>
					</div>
					<div id="occupants" class="accordion">
						<h3><a>Occupants</a></h3>
						<div><div class="block">&nbsp;</div></div>
					</div>
					<div id="surroundings" class="accordion">
						<h3><a>Surroundings</a></h3>
						<div><div class="block">&nbsp;</div></div>
					</div>
				</div>
			</div>
		</div>
		<div id="spacer"></div>
	</div>
	</div>
	<?php include(BASEPATH . '../includes/footer.inc.php'); ?>
	<div id="minimap" class="dialog" title="Map">
		<img id="minimap_img" src="" />
	</div>
	<div id="inventory" class="dialog" title="Inventory">
		<select id="inv_multi">
			<option value="">With selected items:</option>
			<option value="drop">Drop</option>
			<option value="dropstack">Drop stack</option>
			<option value="equip">Equip</option>
			<option value="remove">Remove</option>
		</select>
		<span style="float:right">
			Total encumbrance: <span id="inventory_enc">??</span>
		</span>
		<p />
		<div>
			<table cellspacing="0" cellpadding="4" id="inventory_tbl">
				<tr id="inv_header" class="ui-state-focus">
					<th>&nbsp;</th>
					<th style="width:100%">Item</th>
					<th>Wt.</th>
					<th>&nbsp;</th>
				</tr>
			</table>
		</div>
	</div>
	<div id="profile" class="dialog" title="Profile">
		<p id="profile_descr"></p>
		<table class="stat">
			<tr>
				<td class="tright bold">Status:</td>
				<td><span id="profile_status"></span></td>
			</tr>
			<tr>
				<td class="tright bold">Health:</td>
				<td><span id="profile_health"></span></td>
			</tr>
			<tr>
				<td class="tright bold">Distance:</td>
				<td><span id="profile_distance"></span></td>
			</tr>
			<tr>
				<td class="tright bold">Faction:</td>
				<td><span id="profile_faction"></span></td>
			</tr>
			<tr>
				<td class="tright bold">Clan:</td>
				<td><span id="profile_clan"></span></td>
			</tr>
		</table>
		<p id="profile_spacer" />
		<div id="profile_actions"></div>
	</div>
	<div id="item_desc" class="dialog" title="Item Description">
		<div id="item_desc_main"></div>
		<div id="item_desc_weapon"></div>
		<div id="item_desc_ammo"></div>
		<div id="item_desc_armor"></div>
		<div id="item_desc_classes"></div>
	</div>
	<div id="skillparams" class="dialog">
		Option: <select></select>
		<p>
			<button class="button" type="button" id="btn_skillparam">Use Skill</button>&nbsp;
			<button class="button" type="button" id="btn_skillparam_rpt">x5</button>
		</p>
	</div>
	<div id="actparams" class="dialog">
		Option: <select></select>
		<p>
			<button class="button" type="button" id="btn_actparam">Use Action</button>&nbsp;
			<button class="button" type="button" id="btn_actparam_rpt">x5</button>
		</p>
	</div>
	<div id="effect_desc" class="dialog"></div>
	<div id="whisper_opts" class="dialog" title="Whisper">
		<input type="text" id="whisper_txt" style="width:98%" />
		<p style="text-align:center;">
			<button class="button" id="whisper_opts_submit" onclick="whisper()">Send</button>
		</p>
	</div>
	<div id="hideme" style="display:none !important"></div>
	<?php include(BASEPATH . '../includes/dialogs.inc.php'); ?>
</body>
</html>
