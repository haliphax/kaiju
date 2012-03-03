<?php if(! defined('BASEPATH')) exit(); ?>
<div class="ui-corner-all ui-state-highlight bubble" id="footer">
	kaiju! v<?=$this->config->item('version')?> - "<?=$this->config->item('codename')?>" &nbsp;<small>//</small>&nbsp; &copy; 2009-<?=date('Y')?> Todd Boyd &nbsp;<small>//</small>&nbsp; <a href="http://www.rememberthemilk.com/home/haliphax" target="_blank">Task list</a> &nbsp;<small>//</small>&nbsp; <a href="http://kaiju.roadha.us/forum" target="_blank">Forum</a>
</div>
<div style="display:none;" id="base_url"><?=site_url();?></div>
