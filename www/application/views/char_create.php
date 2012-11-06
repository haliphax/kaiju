<?php if(! defined('BASEPATH')) exit(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>characters - kaiju!</title>
	<?php $this->load->view('parts/header'); ?>
</head>
<body>
	<div id="wrapper">
	<div id="inner">
		<div id="menu">
			<?php $this->load->view('parts/globalbuttons'); ?>
			<div class="right">
				<!--<button class="button" onclick="window.open('http://kaiju.roadha.us/forum', 'kaijuforum');">Forum</button>-->
				<?php if($this->session->userdata('actor')): ?>
				&nbsp;<button class="button" onclick="window.location = '<?=site_url('game')?>';">Return</button>
				<?php endif; ?>
			</div>
		</div>
		<h2 class="ui-corner-all ui-state-highlight">Create Character</h2>
		<div class="tabs">
			<ul>
				<li><a href="#create">Create</a></li>
			</ul>
			<div id="create" style="text-align:center">
				<?php if(isset($err)): ?>
					<div class="ui-corner-all ui-state-error bubble">
						<?=$err?>
					</div>
					<br />
				<?php endif; ?>
				<form method="POST">
					<table style="margin:0 auto" cellpadding="4">
						<tr>
							<td class="bold tright">
								Name:
							</td>
							<td>
								<input type="text" name="name" size="24" maxlength="24" />
							</td>
						</tr>
						<tr>
							<td class="bold tright">
								Faction:
							</td>
							<td class="tleft">
								<select name="faction">
									<option value="">Select a faction:</option>
								<?php foreach($factions as $f): ?>
									<option value="<?=$f['faction']?>"><?=$f['descr']?></option>
								<?php endforeach; ?>
								</select>
							</td>
						</tr>
					</table>
					<p>
						<button type="submit" class="button">Create</button>
					</p>
				</form>
			</div>
		</div>
		<div id="spacer"></div>
	</div>
	</div>
	<?php $this->load->view('parts/footer'); ?>
</body>
</html>
