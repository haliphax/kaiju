<?php if(! defined('BASEPATH')) exit(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>facebook - kaiju!</title>
	<?php $this->load->view('parts/header'); ?>
</head>
<body>
	<div id="wrapper">
	<div id="inner">
		<div style="height:10px;"></div>
		<h2 class="ui-corner-all ui-state-highlight">Welcome to kaiju!</h2>
		<div class="tabs">
			<ul>
				<li><a href="#welcome">Welcome</a></li>
			</ul>
			<div id="welcome">
				<p>Eventually, a lengthy welcome message will be present here. In the mean time, however, all I really need to know is whether or not you truly wish to create a <i>kaiju!</i> account to play with in Facebook.</p>
				<ul>
					<li><a href="<?=site_url('fb/create/1')?>">You bet your buttons I do&mdash;sign me up!</a></li>
					<li><a href="<?=site_url('fb/nope')?>">No; having fun scares the ever-lovin' crap out of me.</a></li>
					<li><a href="<?=site_url('fb/link')?>">I've already got a kaiju! account. Can I use that one?</a></a></li>
				</ul>
			</div>
		</div>
		<div id="spacer"></div>
	</div>
	</div>
	<?php $this->load->view('parts/footer'); ?>
</body>
</html>
