<?php if(! defined('BASEPATH')) exit(); ?>
<?php $ci =& get_instance(); $ci->load->model('user'); ?>
<div class="left">
<?php if(! $this->session->userdata('fb_user')) { ?>
	<button id="btn_logout" class="button">Log Out</button>
	<?php if($this->uri->segment(1) != 'account') { ?>
		&nbsp;<button id="btn_account" class="button">Account</button>
	<?php } else { ?>
		&nbsp;<button id="btn_return" class="button">Return</button>
	<?php } ?>
<?php } ?>
<?php if($this->uri->segment(1) != 'characters'
	|| ($this->uri->segment(1) == 'characters' && $this->uri->segment(2) != ''))
	{ ?>
	&nbsp;<button id="btn_characters" class="button">Characters</button>
<?php } else if($this->session->userdata('actor')) {?>
	&nbsp;<button id="btn_return" class="button">Return</button>
<?php } ?>
<?php if($ci->user->isMod($this->session->userdata('user')) && $this->uri->segment(1) != 'modpanel') { ?>
	&nbsp;<button class="ui-state-error button" onclick="window.location = '<?=site_url('modpanel')?>';">Mod Panel</button>
<?php } else if($ci->user->isMod($this->session->userdata('user')) && $this->uri->segment(1) == 'modpanel') { ?>
	&nbsp;<button id="btn_return" class="button">Return</button>
<?php } ?>
</div>
