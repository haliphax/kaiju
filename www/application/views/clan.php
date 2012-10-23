<?php if(! defined('BASEPATH')) exit(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>clan - kaiju!</title>
	<?php include(BASEPATH . '../includes/header.inc.php'); ?>
	<script type="text/javascript" src="<?=base_url()?>js/dialogs.js"></script>
	<script type="text/javascript" src="<?=base_url()?>js/clan.js"></script>
	<?php if(isset($isleader)): ?><script type="text/javascript" src="<?=base_url()?>js/clan_leader.js"></script><?php endif; ?>
</head>
<body>
	<div id="wrapper">
	<div id="inner">
		<div id="menu">
			<?php include(BASEPATH . '../includes/globalbuttons.inc.php'); ?>
			<div class="right">
				<button class="button" onclick="window.location = '<?=site_url('clans/all')?>';">List Clans</button>&nbsp;
				<button class="button" onclick="window.open('http://kaiju.roadha.us/forum', 'kaijuforum');">Forum</button>&nbsp;
				<button class="button" onclick="window.location = '<?=site_url('game')?>';">Return</button>
			</div>
		</div>
		<h2 class="ui-corner-all ui-state-highlight">Clan: <?=$descr?></h2>
		<div id="clan" class="tabs">
			<ul>
				<li><a href="#clan_info">Information</a></li>
				<li><a id="clan_roster_tab" href="#clan_roster">Roster</a></li>
				<li><a id="clan_relations_tab" href="#clan_relations">Relations</a></li>
			<?php if(isset($isleader)): ?>
				<li><a href="#clan_opts">Options</a></li>
				<li><a id="clan_applications_tab" href="#clan_applications">Applications</a></li>
				<li><a id="clan_invitations_tab" href="#clan_invitations">Invitations</a></li>
			<?php else: ?>
				<li><a href="#quit_clan">Quit</a></li>
			<?php endif; ?>
			</ul>
			<div id="clan_info">
				<table style="width: 100%" class="stat">
					<tr>
						<td class="bold tright">Leader:</td>
						<td class="tleft"><a href="#"><?=$leader_name?></a></td>
						<td class="bold tright">Faction:</td>
						<td class="tleft"><a href="#"><?=$faction_name?></a></td>
					</tr>
					<tr>
						<td class="bold tright">Stronghold:</td>
						<td style="width:auto !important;white-space:nowrap;" class="tleft">
						<?php if($stronghold): ?>
							<?=$stronghold['descr']?> [<?=$stronghold['x']?>,<?=$stronghold['y']?>]</td>
						<?php else: ?>
							<i>None</i>
						<?php endif; ?>
						<td class="bold tright">Members:</td>
						<td class="tleft"><?=$members?></td>
					</tr>
				</table>
			</div>
			<div id="clan_roster">
				<div id="clan_roster_list"><p style="font-style:italic">Loading clan roster...</p></div>
			</div>
			<div id="clan_relations">
				<div id="clan_relations_list"><p style="font-style:italic">Loading relations list...</p></div>
			</div>
			<?php if(isset($isleader)): ?>
			<div id="clan_opts">
				<form id="clan_opts_form">
					<table cellpadding="4" cellspacing="4" style="margin:0 auto">
						<tr>
							<td class="tright bold">Recruitment policy:</td>
							<td>
								<select id="opt_policy">
									<option value="open"<?php if($policy == 'open'): ?> selected="selected"<?php endif; ?>>Open</option>
									<option value="closed"<?php if($policy == 'closed'): ?> selected="selected"<?php endif; ?>>Closed</option>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan="2" style="text-align:center">
								<button class="button" type="submit">Submit</button></td>
							</td>
						</tr>
					</table>
				</form>
				<br />
				<form id="clan_stepdown_form">
					<table cellpadding="4" cellspacing="4" style="margin:0 auto">
						<tbody>
							<tr>
								<th class="bold tright">Successor:</th>
								<td class="tleft">
									<select id="clan_stepdown_successor">
										<option value="0">[Disband]</option>
									<?php foreach($successors as $successor): ?>
										<option value="<?=$successor['actor']?>"><?=$successor['aname']?></option>
									<?php endforeach; ?>
									</select>
								</td>
							</tr>
							<tr>
								<td colspan="2" style="text-align:center">
									<button type="submit" class="button">Step down</button>
								</td>
							</tr>
						</tbody>
					</table>
				</form>
			</div>
			<div id="clan_applications">
				<div id="clan_applications_list"><p style="font-style:italic">Loading applications list...</p></div>
			</div>
			<div id="clan_invitations">
				<p id="send_invitation_link"><a href="#">Send an invitation</a></p>
				<div id="clan_invitations_list"><p style="font-style:italic">Loading invitations list...</p></div>
			</div>
			<?php else: ?>
			<div id="quit_clan">
				<p>To quit this clan, click the button below.</p>
				<p><button type="button" id="quit_clan_btn" class="button">Quit</button></p>
			</div>
			<?php endif; ?>
		</div>
		<div id="spacer"></div>
	</div>
	</div>
	<?php include(BASEPATH . '../includes/footer.inc.php'); ?>
	<div class="dialog" id="invitation_dialog" title="Send invitation">
		<form id="send_invitation">
			<table cellpadding="4" cellspacing="4">
				<tr>
					<td class="bold tright">Send an invitation to:</td>
					<td>
						<input type="text" id="invitation_recipient" />
					</td>
				</tr>
				<tr>
					<td class="bold tright">Message (optional):</td>
					<td>
						<textarea id="invitation_msg" style="width:100%"></textarea>
					</td>
				</tr>
			</table>
			<p style="text-align:center">
				<button type="submit" class="button">Send</button>
			</p>
		</form>
	</div>
	<?php include(BASEPATH . '../includes/dialogs.inc.php'); ?>
</body>
</html>
