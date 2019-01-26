<?php
class gig_game_ig extends bdd implements iorm 
{
	
	public $name = 'my_class';

	public $type = 'game';

	
	public function get($property)
	{
		return $this->{$property};
	}

	public function set($property, $value = NULL)
	{
		$this->{$property} = $value;
	}

	
}
?>