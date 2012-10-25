<?php if(! defined('BASEPATH')) exit();

class e_song_luminescentsoul extends Model
{
	private $ci;
	
	function e_song_luminescentsoul()
	{
		parent::Model();
		$this->ci =& get_instance();
		$this->ci->load->model('map');
		$this->ci->load->model('actor');
	}
	
	function on(&$actor)
	{
		$this->ci->map->sendCellEvent("{$actor['aname']} begins singing.",
			array($actor['actor']), $actor['map'], $actor['x'], $actor['y'],
			$actor['indoors']);
		$this->ci->actor->addEffect('song_luminescentsoul', $actor);
		return array("You begin the Song of the Luminescent Soul, pushing back the darkness to reveal its secrets.");
	}
	
	function tick()
	{
		$this->ci->load->model('effects');
		$actors = $this->ci->effects->getActorsWith('song_luminescentsoul');
		$last = array('map' => 0, 'x' => 0, 'y' => 0, 'i' => 2);
		
		foreach($actors as $actor)
		{
			if($actor['map'] == $last['map'] && $actor['x'] == $last['x']
				&& $actor['y'] == $last['y']
				&& $actor['indoors'] == $last['i'])
			{
				continue;
			}
			
			$cell = $this->ci->map->getCellInfo($actor['map'], $actor['x'],
				$actor['y']);
			
			if($cell['building'])# && rand(1, 10) == 1)
			{
				$sql = <<<SQL
					select a.actor as actor, aname from actor a
					join actor_effect ae on a.actor = ae.actor
					join effect_hide eh on ae.effect = eh.effect
					where map = ? and x = ? and y = ? and indoors = ?
						and a.actor != ? and faction != ?
					order by rand()
					limit 1
SQL;
				$query = $this->db->query($sql, array($actor['map'],
					$actor['x'], $actor['y'], $actor['indoors'],
					$actor['actor'], $actor['faction']));
					
				if($query->num_rows() > 0)
				{
					$res = $query->row_array();
					$this->ci->map->setRadiusEvtM($actor['map'], $actor['x'],
						$actor['y'], $cell['building']);
					$this->ci->actor->removeEffect('hiding',
						$this->ci->actor->getInfo($res['actor']));
					$this->ci->actor->sendEvent(
						"{$actor['aname']}'s song has revealed your location!",
						$res['actor']);
					$this->ci->actor->sendEvent(
						"Your song has revealed {$res['aname']}, who was lurking in the shadows!",
						$actor['actor']);
				}
			}
			
			$last = $actor;
		}
	}
}