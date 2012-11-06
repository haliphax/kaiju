<?php if(! defined('BASEPATH')) exit(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>forgotten password - kaiju!</title>
	<?php $this->load->view('parts/header'); ?>
</head>
<body>
	<div id="wrapper">
	<div id="inner">
		<div id="menu">
			<div class="right">
				<button class="button" onclick="window.open('http://kaiju.roadha.us/forum', 'kaijuforum');">Forum</button>
			</div>
		</div>
		<h2 class="ui-corner-all ui-state-highlight">Forgotten Password</h2>
		<?php if(isset($err)): ?>
			<div class="bubble ui-corner-all ui-state-error" style="margin-bottom:10px;"><?=$err?></div>
		<?php endif; ?>
		<?php if(isset($success)): ?>
			<div class="bubble ui-corner-all ui-state-focus" style="margin-bottom:10px;">
				A link to reset your password has been emailed to your address on file.
			</div>
			<meta http-equiv="refresh" content="5;url=<?=site_url('login')?>" />
		<?php else: ?>
		<div id="forgotpw" class="tabs">
			<ul>			
				<li><a href="#forgot">Forgot</a></li>
			</ul>
			<div id="forgot">
				<form action="" method="POST">
					<table class="stat">
						<tr>
							<td class="bold tright">
								User name:
							</td>
							<td>
							<input tabindex="1" type="text" name="username" />
							</td>
						</tr>
						<tr>
							<td class="bold tright">
								Email:
							</td>
							<td>
								<input tabindex="2" type="text" name="email" />
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
	<?php $this->load->view('parts/footer'); ?>
</body>
</html>
