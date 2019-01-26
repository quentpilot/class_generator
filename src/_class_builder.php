<?php

require_once('class_generator.php');

class class_builder
{
  public $engines = NULL;
  public $entity = NULL;
  public $generator = NULL;

  public function __construct($generator = NULL, $entity = NULL, $engines = NULL)
  {
    $this->generator = $generator;
    $this->entity = $entity;
    $this->$engines = $engines;
  }

  public function run()
  {
    if ($this->isset())
    {

    }
  }

  public function isset()
  {
    $status = TRUE;

    if (empty($this->generator))
    {
      $status = FALSE;
    }

    if ($status && !empty($this->entity))
    {
      $entity = $this->entity;

      if (!($entity instanceof iclass_entity))
      {
        return FALSE;
      }
    }

    if ($status && !empty($this->engines))
    {
      if (!is_array($this->engines))
      {
        $status = FALSE;
      }

      if ($status)
      {
        foreach ($$this->engines as $key => $value)
        {
          if (!($value instanceof iclass_entities))
          {
            $status = FALSE;
          }
        }
      }
    }

    return $status;
  }
}
