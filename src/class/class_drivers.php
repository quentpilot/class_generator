<?php

interface iclass_drivers
{
  public static function factory($drivers = array());
  public function load($drivers = array());
  public function add($name, $driver);
  public function driver($name);
  public function delete($name);
  public function modify($name, $driver);
  public function exists($name);
  public function scan(&$files = array());
}

interface iclass_driver {}

class class_drivers implements iclass_drivers
{
  protected $path = NULL;
  protected $drivers = array();
  protected $forbidden = array();

  public function __construct($drivers = array(), $path = NULL)
  {
    $this->drivers = $drivers;
    $this->path = empty($path) ? __DIR__.'/../drivers/' : $path;
    $this->forbidden = array('drivers', 'path', 'forbidden');
  }

  public static function factory($drivers = array())
  {
    $class_manager = get_called_class();
    $manager = new $class_manager($drivers);
    $manager->load();
    return $manager;
  }

  public function load($drivers = array())
  {
    $drivers = empty($drivers) ? $this->drivers : $drivers;
    $drivers = is_array($drivers) ? $drivers : array($drivers);
    $status = FALSE;

    if (empty($drivers))
    {
      $this->scan($drivers);
    }

    if (is_array($drivers) && !empty($drivers))
    {
      $status = TRUE;

      foreach ($drivers as $key => $type)
      {
        if (is_string($key) && $this->is_driver($type))
        {
          if (!$this->add($key, $type))
          {
            $status = FALSE;
          }
        }
        elseif (is_numeric($key) && is_string($type))
        {
          $this->_load($type, $driver);

          if (!$this->add($type, $driver))
          {
            $status = FALSE;
          }
        }
      }
    }

    return $status;
  }

  protected function _load($type, &$driver = NULL)
  {
    $path = $this->path;
    $suffix = '_class_generator';
    $ext = '.php';
    $filepath = $path . $type . '/';
    $filename = $filepath . $type . $suffix . $ext;
    $classname = $type . $suffix;

    if (file_exists($filename))
    {
      include_once($filename);
      $driver = new $classname();
      return $driver;
    }

    return NULL;
  }

  public function add($name, $driver)
  {
    if (!empty($name) && !empty($driver))
    {
      if (!$this->exists($name) && $this->is_driver($driver))
      {
        return $this->set($name, $driver);
      }
    }
    return FALSE;
  }

  public function driver($name)
  {
    if ($this->exists($name))
    {
      if ($this->is_driver($this->{$name}))
      {
        return $this->{$name};
      }
    }
    return NULL;
  }

  public function delete($name)
  {
    if ($this->exists($name) && !$this->is_forbidden($name))
    {
      unset($this->{$name});
      return TRUE;
    }
    return FALSE;
  }

  public function modify($name, $driver)
  {
    if ($this->exists($name) && $this->is_driver($driver))
    {
      return $this->set($name, $driver);
    }
    return FALSE;
  }

  public function exists($name)
  {
    return property_exists($this, $name);
  }

  public function scan(&$files = array())
  {
    $files = scandir($this->path);
    $forbidden = array('.', '..');

    if (!empty($files))
    {
      foreach ($files as &$file)
      {
        if (in_array($file, $forbidden))
        {
          unset($file);
        }
      }
    }

    return $files;
  }

  protected function is_driver($driver)
  {
    if ($driver instanceof iclass_driver && $driver instanceof iclass_generator)
    {
      return TRUE;
    }
    return FALSE;
  }

  protected function is_forbidden($name)
  {
    return in_array($name, $this->forbidden);
  }

  protected function set($name, $driver)
  {
    if (!$this->is_forbidden($name))
    {
      $this->{$name} = $driver;
      return TRUE;
    }
    return FALSE;
  }
}
