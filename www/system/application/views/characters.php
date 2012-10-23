<?php if(! defined('BASEPATH')) exit(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>characters - kaiju!</title>
	<?php include(BASEPATH . '../includes/header.inc.php'); ?>
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/characters.css" />
	<script type="text/javascript" src="<?=base_url()?>js/characters.js"></script>
</head>
<body>
	<div id="wrapper">
	<div id="inner">
		<div id="menu">
			<?php include(BASEPATH . '../includes/globalbuttons.inc.php'); ?>
			<div class="right">
				<button class="button" onclick="window.open('http://kaiju.roadha.us/forum', 'kaijuforum');">Forum</button>&nbsp;
				<?php if(isset($create)): ?>
					<button class="button" onclick="window.location = '<?=site_url('characters/create')?>';">Create Character</button>
				<?php endif; ?>
			</div>
		</div>
		<h2 class="ui-corner-all ui-state-highlight">Characters</h2>
		<div id="characters" class="tabs">
			<ul>
			<?php foreach($characters as $char): ?>
				<li><a href="#char<?=$char['actor']?>"><?=$char['aname']?></a></li>
			<?php endforeach; ?>
			</ul>
<?php
	$current = 0;
	
	foreach($characters as $char)
	{
		if($char['actor'] == -1)
		{
			echo '<div id="char-1">You have no characters.</div>';
			break;
		}
?>
			<div id="char<?=$char['actor']?>">
				<?php if($char['actor'] == $cur): ?>
					<div id="cur_char" style="display:none"><?=$current?></div>
				<?php endif; ?>
				<table class="stat">
					<tr>
						<td class="tright bold">Hit Points:</td>
						<td>
							<div id="bar_hp" class="progbar" progress="<?= (int) ($char['stat_hp'] / $char['stat_hpmax'] * 100) ?>">
								<span class="prog"><?=$char['stat_hp']?>/<?=$char['stat_hpmax']?></span>
							</div>
						</td>
						<td class="tright bold">Class:</td>
						<td style="width:auto !important;white-space:nowrap;"><?=$char['classes']?></td>
					</tr>
					<tr>
						<td class="tright bold">Action Points:</td>
						<td>
							<div id="bar_ap" class="progbar" progress="<?= (int) ($char['stat_ap'] / $char['stat_apmax'] * 100) ?>">
								<span class="prog"><?=$char['stat_ap']?>/<?=$char['stat_apmax']?></span>
							</div>
						</td>
						<td class="tright bold">Faction:</td>
						<td><a href="#"><?=$char['faction_name']?></a></td>
					</tr>
					<tr>
						<td class="tright bold">Mana Points:</td>
						<td>
							<div id="bar_mp" class="progbar" progress="<?= (int) ($char['stat_mp'] / $char['stat_mpmax'] * 100) ?>">
								<span class="prog"><?=$char['stat_mp']?>/<?=$char['stat_mpmax']?></span>
							</div>
						</td>
						<td class="tright bold">Clan:</td>
						<td class="tleft">
						<?php if($char['clan']): ?>
							<a href="#"><?=$char['clan_name']?></a>
						<?php else: ?>
							<i>None</i>
						<?php endif;?>
						</td>
					</tr>
					<tr>
						<td class="tright bold">Experience:</td>
						<td class="tleft">
							<div id="bar_xp" class="progbar" progress="<?= (int) ($char['stat_xp'] / $char['stat_xplevel'] * 100) ?>">
								<span class="prog"><?=$char['stat_xp']?>/<?=$char['stat_xplevel']?></span>
							</div>
						</td>
					</tr>
				</table>
				<br />
				<button class="button btn_connect" character="<?=$char['actor']?>">Connect</button>
			</div>
<?php
		$current++;
	}
?>
		</div>
		<div id="spacer"></div>
	</div>
	</div>
	<?php include(BASEPATH . '../includes/footer.inc.php'); ?>
</body>
</html>
