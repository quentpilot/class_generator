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


}
