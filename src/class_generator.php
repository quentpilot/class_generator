<?php

/**
* La classe (lib) CI controller Models_generator est conçue pour faciliter
* la conception d'une chaine représentenant une classe PHP
* afin de copier celle-ci dans un fichier ou de laisser la classe
* sauvegarder le fichier au bon vous semble
*
* @author qlebian <q.lebian@highconnexion.com>
*                 <quentin.lebian.pro@gmail.com>
*
* @version 1.0.0
*
* @see hcnxigniter::Models_generator
*/

require_once(__DIR__ . '/../../../lib/generator/class/class_entity.php');

interface iclass_generator
{

}

class class_generator implements iclass_generator
{
  public $prepare = array();

  public $class = NULL;

  public $entities = array();

  public function __construct($classname = NULL, $extends = NULL, $implements = NULL, $type = NULL, $attributes = array(), $methods = array())
  {
    $this->class = (is_object($classname) || is_array($classname))
                 ? $this->class($classname)
                 : self::new_entity($classname, $extends, $implements, $type, $attributes, $methods);

    $this->prepare = array('extends', 'implements', 'type');
  }

  public static function new_entity($name = NULL, $extends = NULL, $implements = NULL, $type = NULL, $attributes = array(), $methods = array())
  {
    $entity = new class_entity($name, $extends, $implements, $type, $attributes, $methods);
    return $entity;
  }

  public function load()
  {
    $status = TRUE;

    if (is_array($this->class))
    {
      $classes = $this->class;
      $this->class = NULL;
      foreach ($classes as $it => $class)
      {
        if ($class instanceof iclass_entity)
        {
          $this->class = $class;



          if (!$this->build())
          {
            $status = FALSE;
          }
        }
        else
        {
          return FALSE;
        }
      }
    }
    else
    {
      $status = $this->build();
    }

    return $status;
  }

  protected function build()
  {
    $status = FALSE;

    if ($this->prepare())
    {
      if (is_array($this->class->name))
      {
        $status = TRUE;
        $classes = $this->class->name;
        $this->class->name = NULL;

        foreach ($classes as $class)
        {
          $this->class->name = $class;

          if (!$this->generate())
          {
            $status = FALSE;
          }
        }
      }
      elseif (is_string($this->class->name))
      {
        $status = TRUE;

        if (!$this->generate())
        {
          $status = FALSE;
        }
      }
    }

    return $status;
  }

  protected function prepare()
  {
    $status = FALSE;
    $routines = $this->prepare;

    if ($this->class instanceof iclass_entity)
    {
      if (!empty($this->class->name))
      {
        if (is_string($this->class->name) || is_array($this->class->name))
        {
          $status = TRUE;

          if (!empty($routines) && is_array($routines))
          {
            foreach ($routines as $routine)
            {
              $callback = 'prepare_' . $routine;

              if (method_exists($this, $callback))
              {
                $this->$callback();
              }
            }
          }
        }
      }
    }

    return $status;
  }

  protected function prepare_extends()
  {
    if (!empty($this->class->extends))
    {
      $this->class->extends = ' extends ' . $this->class->extends;
    }
    return $this;
  }

  protected function prepare_implements()
  {
    if (!empty($this->class->implements))
    {
      $this->class->implements = ' implements ' . $this->class->implements . ' ';
    }
    return $this;
  }

  protected function prepare_type()
  {
    if (!empty($this->class->type))
    {
      $this->class->type = $this->class->type . ' ';
    }
    return $this;
  }

  protected function generate()
  {
    if (!empty($this->class->name))
    {
      $this->class()->load();

      if ($this->add($this->class()))
      {
        return TRUE;
      }
    }

    return FALSE;
  }

  protected function class($class = NULL)
  {
    if (is_array($class) && !empty($class))
    {
      $status = TRUE;

      foreach ($class as $entity)
      {
        if (!($entity instanceof iclass_entity))
        {
          $status = FALSE;
        }
      }

      $this->class = ($status) ? $class : $this->class;
    }
    elseif ($class instanceof iclass_entity)
    {
      $this->class = $class;
    }

    return $this->class;
  }

  public function add($entity = NULL)
  {
    $entity = is_object($entity) ? $entity : $this->class;

    if ($entity instanceof iclass_entity)
    {
      $this->entities[$entity->name] = $entity;
      return $this;
    }

    return FALSE;
  }

  public function get($name)
  {
    if (!empty($this->entities[$name]))
    {
      return $this->entities[$name];
    }
    return NULL;
  }

  public function result()
  {
    return $this->entities;
  }

  public function results()
  {

  }

}
