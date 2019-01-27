<?php

/**
* La classe (lib) class_generator est conçue pour faciliter
* la conception d'une chaine représentenant une classe PHP
* afin de copier celle-ci dans un fichier ou de laisser la classe
* sauvegarder le fichier au bon vous semble
*
* @author Quentin Le Bian <quentin.lebian.pro@gmail.com>
*
* @version 2.0.0
*/

require_once('class_property.php');
require_once('class_method.php');

interface iclass_generator
{
  public function prepare();
  public function load();
  public function property();
  public function method();
  public function result();
  public function save();
  public function set_type();
  public function set_name();
  public function set_extends();
  public function set_implements();
  public function get_type();
  public function get_name();
  public function get_extends();
  public function get_implements();
}


class class_generator implements iclass_generator
{
  public $type = NULL;

  public $name = NULL;

  public $extends = NULL;

  public $implements = NULL;

  public $result = NULL;


  protected $property = NULL;

  protected $method = NULL;


  public function __construct($name = NULL, $extends = NULL, $implements = NULL, $type = NULL)
  {
    $this->type = $type;
    $this->name = $name;
    $this->extends = $extends;
    $this->implements = $implements;
    $this->property = new class_property();
    $this->method = new class_method();
  }

  public function prepare()
  {
    $status = FALSE;

    if (!empty($this->name))
    {
      $status = TRUE;

      $this->set_type($this->type);
      $this->set_extends($this->extends);
      $this->set_implements($this->implements);

      if (!($this->property instanceof class_property))
      {
        $status = FALSE;
      }
      if (!($this->method instanceof class_method))
      {
        $status = FALSE;
      }
    }

    return $status;
  }

  public function load()
  {
    $output = array();

    if ($this->prepare())
    {
      $output[0] = $this->type . $this->name;
      $output[0] .= $this->extends . $this->implements;
      $output[0] .= " \n{";

      $output[] = $this->property()->load();

      $output[] = $this->method()->load();

      $output[] = "\n}";
    }

    return $this->result(implode("\n\t", $output));
  }

  public function property($property = NULL)
  {
    if ($property instanceof class_property)
    {
      $this->property = $property;
    }
    return $this->property;
  }

  public function method($method = NULL)
  {
    if ($method instanceof class_method)
    {
      $this->method = $method;
    }
    return $this->method;
  }

  public function result($result = NULL)
  {
    if (!empty($result))
    {
      $this->result = $result;
    }
    return $this->result;
  }

  public function save($path = NULL)
  {
    $path = (empty($path)) ? __DIR__.'/../../examples/output/' : $path;
    $filename = $path . $this->name.'.php';

    return file_put_contents($filename, $this->result());
  }

  public function set_type($type = NULL)
  {
    if (!empty($type))
    {
      $this->type = $type . ' ';
    }
    else
    {
      $this->type = 'class ';
    }
    return $this;
  }

  public function set_name($name = NULL)
  {
    $this->name = $name;
    return $this;
  }

  public function set_extends($extends = NULL)
  {
    if (!empty($extends))
    {
      $this->extends = ' extends ' . $extends;
    }
    return $this;
  }

  public function set_implements($implements = NULL)
  {
    if (!empty($implements))
    {
      $this->implements = ' implements ' . $implements;
    }
    return $this;
  }

  public function get_type()
  {
    return $this->type;
  }

  public function get_name()
  {
    return $this->name;
  }

  public function get_extends()
  {
    return $this->extends;
  }

  public function get_implements()
  {
    return $this->implements;
  }
}
