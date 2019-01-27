<?php

require_once(__DIR__.'/../../class/class_generator.php');
require_once(__DIR__.'/../../class/class_drivers.php');

class json_class_generator extends class_generator implements iclass_driver
{
  public function load()
  {
    $generator = class_drivers::factory('php')->php;

    $generator->feed($this);
    $generator->load();
    $generator->save();

    $classpath = $this->path.$this->name.'.php';

    if (file_exists($classpath))
    {
      include($classpath);

      $classname = $this->name;
      $class = new $classname();

      return $this->result(json_encode($class));
    }

    return $this->result();
  }
}
