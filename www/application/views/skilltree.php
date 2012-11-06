<?php if(! defined('BASEPATH')) exit(); ?>
<?php

function iterateSkills($abbrev, $class, $skill, &$who, $allow = 1)
{
	$kids = '';
	foreach($skill['kids'] as $k)
		$kids .= iterateSkills($abbrev, $class, $k, $who, $skill['got']);
	if($kids) $kids = "<ul>{$kids}</ul>";
	$tag = 'i';
	$buy = '';
	if($skill['got'] == 1)
		$tag = 'b';
	else if($allow)
		$buy =
			" <small><a class='" . ($who['stat_xp'] < $skill['xp'] ? 'no' : '')
			. "purchase' abbrev='{$abbrev}' aclass='{$class}' "
			. "skill='{$skill['skill']}' href='#'>"
			. "[Purchase: {$skill['xp']}XP]</a></small>";
	return 
		"<li><{$tag}><a href='#' onclick='describeSkill({$skill['skill']});'>"
		. "{$skill['sname']}</a></{$tag}>{$buy}{$kids}</li>";
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>skill tree - kaiju!</title>
	<?php $this->load->view('parts/header'); ?>
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/skilltree.css" />
	<script type="text/javascript" src="<?=base_url()?>js/skilltree.js"></script>
</head>
<body>
	<div id="wrapper">
	<div id="inner">
		<div id="menu">
			<?php $this->load->view('parts/globalbuttons'); ?>
			<div class="right">
				<!--<button class="button" onclick="window.open('http://kaiju.roadha.us/forum', 'kaijuforum');">Forum</button>-->
				&nbsp;<button class="button" onclick="window.location = '<?=site_url('game')?>';">Return</button>
			</div>
		</div>
		<h2 class="ui-corner-all ui-state-highlight">Skill Tree</h2>
		<div id="classes" class="tabs">
			<ul>
				<?php foreach($classes as $c): ?>
				<li><a href="#class-<?=$c['abbrev']?>"><?=$c['descr']?></a></li>
				<?php endforeach; ?>
			</ul>
			<?php foreach($classes as $c): ?>
			<div id="class-<?=$c['abbrev']?>">
				<p style="text-align:center;">
					You have <b><?=$who['stat_xp']?>XP</b> to train with.
					<br />
					You have spent <b><?=$who['stat_xpspent']?>XP</b> so far.
				</p>
				<div style="margin:0 auto;display:table;text-align:left">
					<ul>
					<?php foreach($c['skills'] as $s): ?>
						<?= iterateSkills($c['abbrev'], $c['aclass'], $s, $who) ?>
					<?php endforeach; ?>
					</ul>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
		<div id="spacer"></div>
	</div>
	</div>
	<?php $this->load->view('parts/footer'); ?>
	<div id="skilldesc" class="dialog"></div>
</body>
</html>
