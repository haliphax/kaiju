<?php exit(header("Location: " . site_url())); ?>
<?php if(! defined('BASEPATH')) exit(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>account - kaiju!</title>
	<?php include(BASEPATH . '../includes/header.inc.php'); ?>
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/account.css" />
	<script type="text/javascript" src="<?=base_url()?>js/account.js"></script>
	<?php if(isset($tab)): ?>
	<script type="text/javascript">
		$(function() { $('#account').tabs('select', '#<?=$tab?>'); });
	</script>
	<?php endif; ?>
</head>
<body>
	<div id="wrapper">
	<div id="inner">
		<div id="menu">
			<div class="left">
				<button class="button" onclick="window.location='<?=site_url()?>';">Back</button>
			</div>
			<div class="right">
				<button class="button" onclick="window.open('http://kaiju.roadha.us/forum', 'kaijuforum');">Forum</button>
			</div>
		</div>
		<h2 class="ui-corner-all ui-state-highlight">Account</h2>
		<?php if(isset($msg)): ?>
			<div class="bubble ui-corner-all ui-state-focus" style="margin-bottom:10px"><?=$msg?></div>
		<?php endif; if(isset($err)): ?>
			<div class="bubble ui-corner-all ui-state-error" style="margin-bottom:10px;"><?=$err?></div>
		<?php endif; ?>
		<div id="account" class="tabs">
			<ul>			
				<li><a href="#details">Details</a></li>
			</ul>
			<div id="details">
				<form action="<?=site_url("signup/submit")?>" method="POST">
					<table class="stat">
						<tr>
							<td class="bold tright">
								Username:
							</td>
							<td>
							<input tabindex="1" type="text" name="username" value="<?=$this->input->post('username')?>" />
							</td>
						</tr>
						<tr>
							<td class="bold tright">
								E-mail address:
							</td>
							<td>
								<input tabindex="2" type="text" name="email" value="<?=$this->input->post('email')?>" />
							</td>
						</tr>
						<tr>
							<td class="bold tright">
								Password:
							</td>
							<td>
								<input tabindex="3" type="password" name="pass" />
							</td>
						</tr>
						<tr>
							<td class="bold tright">
								Confirm:
							</td>
							<td>
								<input tabindex="4" type="password" name="confirm" />
							</td>
						</tr>
						<tr>
							<td colspan="2">&nbsp;</td>
						</tr>
						<tr>
							<td colspan="2" class="center">
								<button tabindex="5" class="button ui-button ui-corner-all" type="submit">Submit</button>
							</td>
						</tr>
					</table>
				</form>
			</div>
		</div>
		<div id="spacer"></div>
	</div>
	</div>
	<?php include(BASEPATH . '../includes/footer.inc.php'); ?>
</body>
</html>
