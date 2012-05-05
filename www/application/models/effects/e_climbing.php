<?php if(! defined('BASEPATH')) exit();

class e_climbing extends CI_Model
{
	private $ci;
	
	function __construct()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->load->database();
	}
	
	function on(&$actor)
	{
		return array('You climb up.');
	}
	
	function off(&$actor)
	{
		return array('You climb down.');
	}
	
	function move(&$new, &$actor)
	{
		$this->ci->load->model('actor');
		$this->ci->load->model('map');
		
		if($this->ci->map->cellHasClass('climb', $new['map'], $new['x'],
			$new['y'], 0, $cell))
		{
			$cf = 2; # 10% chance to fall
			$roll = rand(1, 20);
			
			# actor fell
			if($roll <= $cf)
			{
				$this->ci->actor->removeEffect('climbing', &$actor);
				$this->ci->actor->damage(3, &$actor);
				$this->ci->actor->setStatFlag($actor['actor']);
				return array(
					'You fall short of your mark and come crashing to the ground.'
					);
			}
			
			if($cell['building'])
				return array('You leap impressively onto the rooftop.');
			return;
		}
		
		$this->ci->actor->removeEffect('climbing', &$actor);
		$this->ci->actor->setStatFlag($actor['actor']);
		
		# jumped to a water cell
		if($this->ci->map->cellHasClass('water', $new['map'], $new['x'],
			$new['y'], 0, $cell))
		{
			return array(
				"You leap through the air and land in the water with a resounding splash."
				);
		}
		
		# jumped to a cell with no climbable structure
		$this->ci->actor->damage(3, &$actor);
		return array(
			"You go flying through the air toward a landing that doesn't exist, and injure yourself in the fall."
			);
	}
}