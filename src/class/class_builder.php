<?php

require_once('class_generator.php');

interface iclass_builder
{
  public function load();
  public function name();
  public function type();
  public function extends();
  public function implements();
  public function property();
  public function method();
  public function generator();
  public function output();
}

class class_builder implements iclass_builder
{
  protected $generator = NULL;

  public $output = NULL;

  public function __construct($generator = NULL)
  {
    $this->generator = ($generator instanceof iclass_generator)
                     ? $generator
                     : new class_generator();
  }

  public function load()
  {
    $this->generator()->load();
    return $this;
  }

  public function name($name = NULL)
  {
    if (!empty($name))
    {
      $this->generator()->name = $name;
    }
    return $this;
  }

  public function type($type = NULL)
  {
    if (!empty($type))
    {
      $this->generator()->type = $type;
    }
    return $this;
  }

  public function extends($extends = NULL)
  {
    if (!empty($extends))
    {
      $this->generator()->extends = $extends;
    }
    return $this;
  }

  public function implements($implements = NULL)
  {
    if (!empty($implements))
    {
      $this->generator()->implements = $implements;
    }
    return $this;
  }

  public function property($property = NULL)
  {
    if (!empty($property))
    {
      $this->generator()->property($property);
    }
    return $this;
  }

  public function method($method = NULL)
  {
    if (!empty($method))
    {
      $this->generator()->method($method);
    }
    return $this;
  }

  public function generator($generator = NULL)
  {
    if ($generator instanceof iclass_generator)
    {
      $this->generator = $generator;
    }
    return $this->generator;
  }

  public function output()
  {
    $this->output = $this->generator()->result();
    return $this->output;
  }
}