<?php
class qlebian extends hcnx implements highco 
{
	public $name = NULL;

	public $value = NULL;

	public function __construct($name = NULL)
	{
			$this->name = $name;
	}

	public function get_name($name)
	{
			return $this->name;
	}

	public function get_value($value)
	{
			return $this->value;
	}

	
}