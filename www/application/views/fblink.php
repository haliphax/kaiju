<?php if(! defined('BASEPATH')) exit(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>link account  - kaiju!</title>
	<?php include(BASEPATH . '../includes/header.inc.php'); ?>
</head>
<body>
	<div id="wrapper">
	<div id="inner">
		<div style="height:10px;"></div>
		<h2 class="ui-corner-all ui-state-highlight">Link Your Account</h2>
		<div class="tabs">
			<ul>
				<li><a href="#link">Link</a></li>
			</ul>
			<div id="link">
				<p>Enter your existing account's credentials below to link it with your Facebook account:</p>
				<form action="<?=site_url("fb/link/1")?>" method="POST">
					<table class="stat">
						<tr>
							<td class="bold tright">
								Username:
							</td>
							<td>
								<input type="text" name="user" />
							</td>
						</tr>
						<tr>
							<td class="bold tright">
								Password:
							</td>
							<td>
								<input type="password" name="pass" />
							</td>
						</tr>
						<tr>
							<td colspan="2" class="center">
								<button class="button ui-button ui-corner-all" type="submit">Submit</button>
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
