<?php

require_once('class_generator.php');

interface iclass_builder
{
  public function load_all();
  public function load();
  public function name();
  public function type();
  public function extend();
  public function implement();
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

  public function load(&$classes = array())
  {
    if (!empty($classes) && is_array($classes))
    {
      foreach ($classes as $it => &$class)
      {
        if (is_array($class) || $class instanceof iclass_generator)
        {
          $this->generator()->factory($class);
          $this->output .= $class->load();
        }
      }
    }
    else
    {
      $this->generator()->load();
    }
    return $this;
  }

  public function load_all(&$classes = array())
  {
    if (!empty($classes) && is_array($classes))
    {
      foreach ($classes as $it => &$class)
      {
        if (is_array($class) || $class instanceof iclass_generator)
        {
          $this->generator()->factory($class);
          $this->output .= $class->load();
        }
      }
      return TRUE;
    }
    return FALSE;
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

  public function extend($extends = NULL)
  {
    if (!empty($extends))
    {
      $this->generator()->extends = $extends;
    }
    return $this;
  }

  public function implement($implements = NULL)
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
    if (empty($this->output))
    {
      $this->output = $this->generator()->result();
    }
    return $this->output;
  }
}
