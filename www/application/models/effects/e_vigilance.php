<?php if(! defined('BASEPATH')) exit();

class e_vigilance extends EffectModel
{
	
	function __construct()
	{
		parent::__construct();
	}

	function move(&$new, &$actor)
	{
		$this->ci->load->model('map');
		$cell = $this->ci->map->getCellInfo($new['map'], $new['x'], $new['y']);
		
		if($cell['building'] && rand(1, 20) <= 3)
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
			$query = $this->db->query($sql, array($new['map'], $new['x'],
				$new['y'], $new['i'], $actor['actor'], $actor['faction']));
				
			if($query->num_rows() > 0)
			{
				$res = $query->row_array();
				$this->ci->map->setRadiusEvtM($new['map'], $new['x'], $new['y'],
					$cell['building']);
				$this->ci->actor->removeEffect('hiding',
					$this->ci->actor->getInfo($res['actor']));
				$this->ci->actor->sendEvent(
					"{$actor['aname']} discovered your location!",
					$res['actor']);
				return array(
					"You spotted {$res['aname']} lurking in the shadows!");
			}
		}
	}
}