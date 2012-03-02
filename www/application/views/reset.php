<?php if(! defined('BASEPATH')) exit(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>reset password - kaiju!</title>
	<?php include(BASEPATH . '../includes/header.inc.php'); ?>
</head>
<body>
	<div id="wrapper">
	<div id="inner">
		<div id="menu">
			<div class="right">
				<button class="button" onclick="window.open('http://kaiju.roadha.us/forum', 'kaijuforum');">Forum</button>
			</div>
		</div>
		<h2 class="ui-corner-all ui-state-highlight">Reset Password</h2>
		<?php if(isset($err)): ?>
			<div class="bubble ui-corner-all ui-state-error" style="margin-bottom:10px;"><?=$err?></div>
		<?php endif; ?>
		<?php if(isset($success)): ?>
			<div class="bubble ui-corner-all ui-state-focus" style="margin-bottom:10px;">
				Your password has been successfully changed.
			</div>
			<meta http-equiv="refresh" content="5;url=<?=site_url('login')?>" />
		<?php else: ?>
		<div id="resetpw" class="tabs">
			<ul>			
				<li><a href="#reset">Reset</a></li>
			</ul>
			<div id="reset">
				<form action="" method="POST">
					<table class="stat">
						<tr>
							<td class="bold tright">
								New password:
							</td>
							<td>
							<input tabindex="1" type="password" name="password" />
							</td>
						</tr>
						<tr>
							<td class="bold tright">
								Confirm:
							</td>
							<td>
								<input tabindex="2" type="password" name="confirm" />
							</td>
						</tr>
						<tr>
							<td colspan="2">&nbsp;</td>
						</tr>
						<tr>
							<td colspan="2" class="center">
								<button tabindex="3" class="button ui-button ui-corner-all" type="submit">Submit</button>
							</td>
						</tr>
					</table>
				</form>
			</div>
		</div>
		<?php endif; ?>
		<div id="spacer"></div>
	</div>
	</div>
	<?php include(BASEPATH . '../includes/footer.inc.php'); ?>
</body>
</html>
