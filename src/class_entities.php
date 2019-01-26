<?php

require_once('class_entity.php');

interface iclass_entities
{
  public static function factory($storage = array());
  public function add($property, $value = NULL);
  public function get($property);
  public function get_all();
  public function delete($property);
  public function output($key, $value, $delim);
}

class class_entities implements iclass_entities
{
  public $config = array();

  public $output = NULL;

  protected $_storage = array();

  public function __construct($storage = array())
  {
    $this->_storage = $storage;
  }

  public static function factory($storage = array())
  {
    $classname = get_called_class();
    $entity = new $classname($storage);
    return $entity;
  }

  public function add($property, $value = NULL)
  {
    if (!empty($property) && is_string($property))
    {
      $this->_storage[$property] = $value;
    }
    return $this;
  }

  public function get($property)
  {
    if (isset($this->_storage[$property]))
    {
      return $this->_storage[$property];
    }

    return NULL;
  }

  public function get_all()
  {
    return $this->_storage;
  }

  public function delete($property)
  {
    if (array_key_exists($property, $this->get_all()))
    {
      unset($this->_storage[$property]);
      return TRUE;
    }
    return FALSE;
  }

  public function output($key, $value, $delim = NULL, $implode = NULL)
  {
    $output = '';
    $output = "$key $value $delim";
    $this->output = $output;
    return $output;
  }
}
