<?php if(! defined('BASEPATH')) exit(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>clan list - kaiju!</title>
	<?php $this->load->view('parts/header'); ?>
	<link type="text/css" rel="stylesheet" href="<?=base_url()?>css/clans.css" />
	<script type="text/javascript" src="<?=base_url()?>js/clans.js"></script>
	<script type="text/javascript" src="<?=base_url()?>js/dialogs.js"></script>
</head>
<body>
	<div id="wrapper">
	<div id="inner">
		<div id="menu">
			<?php $this->load->view('parts/globalbuttons'); ?>
			<div class="right">
				<?php if($myclan): ?><button class="button" onclick="window.location = '<?=site_url('clans')?>';">My Clan</button>&nbsp;<?php endif; ?>
				<!--<button class="button" onclick="window.open('http://kaiju.roadha.us/forum', 'kaijuforum');">Forum</button>-->&nbsp;
				<button class="button" onclick="window.location = '<?=site_url('game')?>';">Return</button>
			</div>
		</div>
		<h2 class="ui-corner-all ui-state-highlight">Clan List</h2>
		<div id="clan" class="tabs">
			<ul>
				<?php if($my): ?><li><a href="#my_invites">My Invitations</a></li><?php endif; ?>
				<li><a href="#open_clans">Open</a></li>
				<li><a href="#closed_clans" id="closed_clans_tab">Closed</a></li>
			</ul>
			<?php if($my): ?>
			<div id="my_invites">
				<table style="width:100%" cellpadding="4" cellspacing="0">
					<tr class="ui-state-focus">
						<th>Clan</th>
						<th>Message</th>
						<th></th>
					</tr>
					<?php $odd = false; foreach($my as $i): ?>
					<tr<?=($odd ? ' class="ui-state-highlight"' : '')?>>
						<td>
							<span style="display:none;"><?=$i['clan']?></span>
							<a href="#"><?=$i['descr']?></a>
						</td>
						<td><?=$i['msg']?></td>
						<td></td>
					</tr>
					<?php $odd = ! $odd; endforeach; ?>
				</table>
			</div>
			<?php endif; ?>
			<div id="open_clans">
				<p>Anyone may petition for membership in these clans, provided they are the same faction.</p>
				<div id="open_clans_list"><i>Loading clans list...</i></div>
			</div>
			<div id="closed_clans">
				<p>To participate in these clans, you must be invited.</p>
				<div id="closed_clans_list"><i>Loading clans list...</i></div>
			</div>
		</div>
		<div id="spacer"></div>
	</div>
	</div>
	<?php $this->load->view('parts/footer'); ?>
	<div id="application_dialog" class="dialog" title="Application">
		<form id="clan_application">
			<table style="width:100%" cellspacing="0" cellpadding="4">
				<tr>
					<td class="bold tright">Message (optional):</td>
					<td style="width:100%"><textarea id="clan_application_msg" style="width:100%"></textarea></td>
				</tr>
			</table>
			<p style="text-align:center">
				<button class="button" type="submit">Submit</button></td>
			</p>
		</form>
	</div>
	<?php $this->load->view('parts/dialogs'); ?>
</body>
</html>
