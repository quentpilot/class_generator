<?php

interface iclass_items
{
  public static function factory($storage = array(), $config = array());
  public function load();
  public function add($property, $value = NULL);
  public function delete($property);
  public function get($property);
  public function get_all();
}

class class_items implements iclass_items
{
  public $config = array();

  public $output = NULL;

  protected $_storage = array();

  public function __construct($storage = array(), $config = array())
  {
    $this->_storage = $storage;
    $this->config = $config;
  }

  public function load()
  {
    $output = array();
  }

  public static function factory($storage = array(), $config = array())
  {
    $classname = get_called_class();
    $entity = new $classname($storage, $config);
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
}
